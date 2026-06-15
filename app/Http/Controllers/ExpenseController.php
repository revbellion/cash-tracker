<?php

namespace App\Http\Controllers;

use App\Exports\ExpensesExport;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Services\ExpenseService;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->expenseService->getAll($filters);

        return view('expenses.index', [
            'expenses' => $result['expenses'],
            'accounts' => Account::active()->get(),
            'categories' => $this->expenseService->getCategories(),
            'totalAmount' => $result['totalAmount'],
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        $this->expenseService->create($request->validated());

        return redirect()->back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(UpdateExpenseRequest $request, $id)
    {
        $this->expenseService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Pengeluaran berhasil diubah.');
    }

    public function destroy($id)
    {
        $this->expenseService->delete($id);

        return redirect()->back()->with('success', 'Pengeluaran berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);

        return Excel::download(new ExpensesExport($filters), 'pengeluaran.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'category', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'category' => 'nullable|string|max:100',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
