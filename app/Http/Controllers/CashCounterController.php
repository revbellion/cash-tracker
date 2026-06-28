<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashCounterSession;
use App\Models\Expense;
use App\Models\Income;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashCounterController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $accounts = Account::active()->where('type', 'cash')->get();
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        $hasCashAccounts = $accounts->isNotEmpty();
        $period = now()->format('Y-m');
        $balances = $this->dashboardService->calculateAccountBalances($accounts, $period);

        return view('cash-counter.index', compact('accounts', 'cashAccount', 'balances', 'hasCashAccounts'));
    }

    public function history(): JsonResponse
    {
        $sessions = CashCounterSession::with('account')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'account_id', 'title', 'total_amount', 'created_at']);

        return response()->json($sessions);
    }

    public function show(CashCounterSession $session): JsonResponse
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403);
        }
        $session->load('account');
        return response()->json($session);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    if ($value && Account::where('id', $value)->where('type', '!=', 'cash')->exists()) {
                        $fail('Akun harus bertipe Cash.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'denominations.*' => 'integer|min:0',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session = CashCounterSession::create([
            'user_id' => auth()->id(),
            'account_id' => $data['account_id'] ?? null,
            'title' => $data['title'],
            'denominations' => $data['denominations'],
            'target_amount' => $data['target_amount'],
            'total_amount' => $data['total_amount'],
        ]);

        $session->load('account');

        return response()->json($session);
    }

    public function update(Request $request, CashCounterSession $session): JsonResponse
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'account_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    if ($value && Account::where('id', $value)->where('type', '!=', 'cash')->exists()) {
                        $fail('Akun harus bertipe Cash.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'denominations' => 'required|array',
            'denominations.*' => 'integer|min:0',
            'target_amount' => 'nullable|integer|min:0',
            'total_amount' => 'required|integer|min:0',
        ]);

        $session->update([
            'account_id' => $data['account_id'] ?? null,
            'title' => $data['title'],
            'denominations' => $data['denominations'],
            'target_amount' => $data['target_amount'],
            'total_amount' => $data['total_amount'],
        ]);

        return response()->json($session);
    }

    public function destroy(CashCounterSession $session): JsonResponse
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403);
        }

        // Hapus adjustment Income/Expense terkait session ini
        $pattern = 'Penyesuaian kas #' . $session->id . ':';
        Income::where('category', 'OMSET')
            ->where('description', 'like', $pattern . '%')
            ->delete();
        Expense::where('category', 'OMSET')
            ->where('description', 'like', $pattern . '%')
            ->delete();

        $session->delete();

        return response()->json(['ok' => true]);
    }

    public function adjust(Request $request, CashCounterSession $session): JsonResponse
    {
        if ((int) $session->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|integer|min:1',
            'account_id' => [
                'nullable',
                'exists:accounts,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $account = Account::where('id', $value)->first();
                        if (!$account || !$account->is_active) {
                            $fail('Akun tidak aktif.');
                        }
                    }
                },
            ],
        ]);

        $accountId = $data['account_id'] ?? $session->account_id;
        if (!$accountId) {
            return response()->json(['message' => 'Pilih akun terlebih dahulu'], 422);
        }

        try {
            DB::transaction(function () use ($data, $session, $accountId) {
                $label = $data['type'] === 'income' ? 'lebih' : 'kurang';
                $description = 'Penyesuaian kas #' . $session->id . ': ' . $session->title . ' (' . $label . ' Rp ' . number_format($data['amount'], 0, ',', '.') . ')';

                if ($data['type'] === 'income') {
                    Income::create([
                        'account_id' => $accountId,
                        'date' => now(),
                        'amount' => $data['amount'],
                        'description' => $description,
                        'category' => 'OMSET',
                    ]);
                } else {
                    Expense::create([
                        'account_id' => $accountId,
                        'date' => now(),
                        'amount' => $data['amount'],
                        'description' => $description,
                        'category' => 'OMSET',
                    ]);
                }
            });

            return response()->json(['ok' => true, 'message' => 'Penyesuaian berhasil dibuat']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat penyesuaian: ' . $e->getMessage()], 500);
        }
    }
}
