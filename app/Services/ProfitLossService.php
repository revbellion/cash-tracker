<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\HppRecord;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    /**
     * Kategori income yang merupakan pendapatan operasional (bukan sistem).
     */
    private array $systemIncomeCategories = [
        'Transfer Masuk',
        'Piutang',
        'Pending EDC',
    ];

    /**
     * Kategori expense yang merupakan biaya operasional (bukan sistem/stok).
     */
    private array $systemExpenseCategories = [
        'Stok Masuk',
        'Piutang',
        'Cash Keluar',
    ];

    public function getData(string $period): array
    {
        $dateStart = sprintf('%04d-%02d-01', ...array_map('intval', explode('-', $period)));
        $dateEnd = Carbon::parse($dateStart)->endOfMonth();

        // === PENDAPATAN (Revenue) ===
        $revenueQuery = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('category')
            ->whereNotIn('category', $this->systemIncomeCategories)
            ->where('category', 'not like', 'Pending %')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $revenueByCategory = $revenueQuery->get();
        $totalRevenue = $revenueByCategory->sum('total');

        // === PENDAPATAN LAIN (Other Income - system generated yang tetap jadi income) ===
        $otherIncomeQuery = Income::whereBetween('date', [$dateStart, $dateEnd])
            ->where(function ($q) {
                $q->whereIn('category', $this->systemIncomeCategories)
                  ->orWhere('category', 'like', 'Pending %');
            })
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $otherIncomeByCategory = $otherIncomeQuery->get();
        $totalOtherIncome = $otherIncomeByCategory->sum('total');

        // === HPP (Cost of Goods Sold) ===
        $hppData = HppRecord::whereBetween('date', [$dateStart, $dateEnd])
            ->select(
                DB::raw('SUM(hpp_amount) as total_hpp'),
                DB::raw('SUM(selling_amount) as total_selling'),
                DB::raw('SUM(profit_amount) as total_profit'),
                DB::raw('SUM(qty) as total_qty')
            )
            ->first();

        $totalHpp = (int) ($hppData->total_hpp ?? 0);
        $totalSelling = (int) ($hppData->total_selling ?? 0);
        $totalProfitHpp = (int) ($hppData->total_profit ?? 0);

        // === HPP per kategori produk ===
        $hppByCategory = HppRecord::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('product_category_id')
            ->select('product_category_id', DB::raw('SUM(hpp_amount) as total_hpp'), DB::raw('SUM(selling_amount) as total_selling'), DB::raw('SUM(profit_amount) as total_profit'), DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_category_id')
            ->with('category')
            ->orderBy('total_hpp', 'desc')
            ->get();

        // === BIAYA OPERASIONAL ===
        $expenseQuery = Expense::whereBetween('date', [$dateStart, $dateEnd])
            ->whereNotNull('category')
            ->whereNotIn('category', $this->systemExpenseCategories)
            ->where('category', 'not like', 'Pending %')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc');

        $expensesByCategory = $expenseQuery->get();
        $totalExpenses = $expensesByCategory->sum('total');

        // === Hitung Laba/Rugi ===
        $labaKotor = $totalRevenue - $totalHpp;
        $labaBersih = $labaKotor - $totalExpenses;

        // === Data untuk ringkasan ===
        $summary = [
            'total_revenue' => $totalRevenue,
            'total_hpp' => $totalHpp,
            'total_selling' => $totalSelling,
            'total_profit_hpp' => $totalProfitHpp,
            'total_other_income' => $totalOtherIncome,
            'total_expenses' => $totalExpenses,
            'laba_kotor' => $labaKotor,
            'laba_bersih' => $labaBersih,
            'total_qty' => (int) ($hppData->total_qty ?? 0),
        ];

        return [
            'period' => $period,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'revenueByCategory' => $revenueByCategory,
            'otherIncomeByCategory' => $otherIncomeByCategory,
            'hppByCategory' => $hppByCategory,
            'expensesByCategory' => $expensesByCategory,
            'summary' => $summary,
        ];
    }

    /**
     * Mendapatkan daftar periode yang tersedia (bulan-bulan yang ada transaksinya).
     */
    public function getAvailablePeriods(): array
    {
        $dates = collect();

        Income::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m'));
        });
        Expense::select('date')->each(function ($item) use ($dates) {
            $dates->push($item->date->format('Y-m'));
        });

        return $dates
            ->unique()
            ->sort()
            ->reverse()
            ->values()
            ->toArray();
    }
}
