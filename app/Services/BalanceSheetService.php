<?php

namespace App\Services;

use App\Models\Account;
use App\Models\HppRecord;
use App\Models\Income;
use App\Models\Expense;
use App\Models\OpeningBalance;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceSheetService
{
    public function getData(string $date): array
    {
        $targetDate = Carbon::parse($date)->endOfDay();
        $period = $targetDate->format('Y-m');
        $monthStart = $targetDate->copy()->startOfMonth();

        // === ASET LANCAR ===

        // 1. Saldo per akun
        $accounts = Account::active()->orderBy('type')->orderBy('name')->get();
        $accountBalances = $this->calculateBalances($accounts, $targetDate);

        $totalCash = 0;
        $cashAccounts = [];
        $ewalletAccounts = [];
        $bankAccounts = [];
        $ppobAccounts = [];

        foreach ($accounts as $account) {
            $balance = $accountBalances[$account->id] ?? 0;
            $entry = [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => $balance,
            ];

            if ($account->type === 'cash') {
                $cashAccounts[] = $entry;
            } elseif ($account->type === 'bank') {
                $bankAccounts[] = $entry;
            } elseif ($account->type === 'ewallet') {
                $ewalletAccounts[] = $entry;
            } elseif ($account->type === 'ppob') {
                $ppobAccounts[] = $entry;
            }

            $totalCash += $balance;
        }

        // 2. Piutang (belum dibayar)
        $totalReceivables = Receivable::unpaid()->sum('amount');

        // 3. Persediaan barang (stock value)
        $totalInventory = Product::active()->select(DB::raw('SUM(stock * purchase_price) as total'))->value('total') ?? 0;

        // 4. Total Aset Lancar
        $totalCurrentAssets = $totalCash + $totalReceivables + $totalInventory;

        // === KEWAJIBAN ===
        // Belum ada modul hutang, placeholder 0
        $totalLiabilities = 0;

        // === EKUITAS ===

        // 1. Modal Awal (total opening balance periode pertama atau dari periode awal)
        $openingBalances = OpeningBalance::where('period', '<=', $period)
            ->select('account_id', DB::raw('SUM(amount) as total'))
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $totalModalAwal = $openingBalances->sum();

        // 2. Laba ditahan (profit from start of year until before this month)
        $retainedEarnings = $this->calculateRetainedEarnings($targetDate);

        // 3. Laba periode berjalan
        $currentProfit = $this->calculatePeriodProfit($monthStart, $targetDate);

        $totalEquity = $totalModalAwal + $retainedEarnings + $currentProfit;

        return [
            'date' => $targetDate->format('Y-m-d'),
            'dateFormatted' => Carbon::parse($date)->translatedFormat('d F Y'),
            'accounts' => [
                'cash' => $cashAccounts,
                'bank' => $bankAccounts,
                'ewallet' => $ewalletAccounts,
                'ppob' => $ppobAccounts,
            ],
            'totalCash' => $totalCash,
            'totalReceivables' => $totalReceivables,
            'totalInventory' => $totalInventory,
            'totalCurrentAssets' => $totalCurrentAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalModalAwal' => $totalModalAwal,
            'retainedEarnings' => $retainedEarnings,
            'currentProfit' => $currentProfit,
            'totalEquity' => $totalEquity,
            'balanceCheck' => $totalCurrentAssets === ($totalLiabilities + $totalEquity),
            'balanceDiff' => $totalCurrentAssets - ($totalLiabilities + $totalEquity),
        ];
    }

    private function calculateBalances($accounts, Carbon $targetDate): array
    {
        $period = $targetDate->format('Y-m');

        $openingBalances = OpeningBalance::where('period', $period)
            ->pluck('amount', 'account_id');

        $mutationsIn = Mutation::where('date', '<=', $targetDate)
            ->selectRaw('to_account_id, SUM(amount) as total')
            ->groupBy('to_account_id')
            ->pluck('total', 'to_account_id');

        $mutationsOut = Mutation::where('date', '<=', $targetDate)
            ->selectRaw('from_account_id, SUM(amount) as total')
            ->groupBy('from_account_id')
            ->pluck('total', 'from_account_id');

        $totalIncomes = Income::where('date', '<=', $targetDate)
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $totalExpenses = Expense::where('date', '<=', $targetDate)
            ->whereNotNull('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->groupBy('account_id')
            ->pluck('total', 'account_id');

        $balances = [];
        foreach ($accounts as $account) {
            $balances[$account->id] = (int) (
                ($openingBalances[$account->id] ?? 0)
                + ($mutationsIn[$account->id] ?? 0)
                - ($mutationsOut[$account->id] ?? 0)
                + ($totalIncomes[$account->id] ?? 0)
                - ($totalExpenses[$account->id] ?? 0)
            );

            if ($balances[$account->id] < 0) {
                $balances[$account->id] = 0;
            }
        }

        return $balances;
    }

    private function calculateRetainedEarnings(Carbon $targetDate): int
    {
        $yearStart = $targetDate->copy()->startOfYear();
        $monthStart = $targetDate->copy()->startOfMonth();

        // Profit from year start until before this month
        if ($monthStart->eq($yearStart)) {
            return 0;
        }

        $periodEnd = $monthStart->copy()->subDay()->endOfDay();

        return $this->calculateProfitBetween($yearStart, $periodEnd);
    }

    private function calculatePeriodProfit(Carbon $periodStart, Carbon $periodEnd): int
    {
        return $this->calculateProfitBetween($periodStart, $periodEnd);
    }

    private function calculateProfitBetween(Carbon $start, Carbon $end): int
    {
        // System categories yang bukan pendapatan operasional
        $systemIncomeCategories = ['Transfer Masuk', 'Piutang', 'Pending EDC'];
        $systemExpenseCategories = ['Stok Masuk', 'Piutang', 'Cash Keluar'];
        $pendingPrefix = 'Pending %';

        $totalIncome = Income::whereBetween('date', [$start, $end])
            ->whereNotIn('category', $systemIncomeCategories)
            ->where('category', 'not like', $pendingPrefix)
            ->sum('amount') ?? 0;

        $totalExpense = Expense::whereBetween('date', [$start, $end])
            ->whereNotIn('category', $systemExpenseCategories)
            ->where('category', 'not like', $pendingPrefix)
            ->sum('amount') ?? 0;

        $totalHpp = HppRecord::whereBetween('date', [$start, $end])
            ->sum('hpp_amount') ?? 0;

        return $totalIncome - $totalHpp - $totalExpense;
    }

    public function getAvailableDates(): array
    {
        $dates = collect();

        Income::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m-d'));
        });
        Expense::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m-d'));
        });

        return $dates
            ->unique()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }
}
