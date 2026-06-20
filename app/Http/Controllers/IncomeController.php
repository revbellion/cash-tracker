<?php

namespace App\Http\Controllers;

use App\Exports\IncomesExport;
use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Models\Account;
use App\Services\IncomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class IncomeController extends Controller
{
    public function __construct(
        protected IncomeService $incomeService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->incomeService->getAll($filters);

        return view('incomes.index', [
            'incomes' => $result['incomes'],
            'categories' => $this->incomeService->getCategories(),
            'accounts' => Account::active()->get(),
            'totalAmount' => $result['totalAmount'],
        ]);
    }

    public function store(StoreIncomeRequest $request)
    {
        try {
            $this->incomeService->create($request->validated());
            return redirect()->back()->with('success', 'Pendapatan berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat pendapatan: ' . $e->getMessage());
        }
    }

    public function update(UpdateIncomeRequest $request, $id)
    {
        try {
            $this->incomeService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Pendapatan berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah pendapatan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->incomeService->delete($id);
            return redirect()->back()->with('success', 'Pendapatan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pendapatan: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);

        return Excel::download(new IncomesExport($filters), 'pendapatan.xlsx');
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
