<?php

namespace App\Http\Controllers;

use App\Exports\MutationsExport;
use App\Http\Requests\StoreMutationRequest;
use App\Http\Requests\UpdateMutationRequest;
use App\Models\Account;
use App\Services\MutationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MutationController extends Controller
{
    public function __construct(
        protected MutationService $mutationService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->mutationService->getAll($filters);

        return view('mutations.index', [
            'mutations' => $result['mutations'],
            'accounts' => Account::active()->get(),
            'totalAmount' => $result['totalAmount'],
        ]);
    }

    public function store(StoreMutationRequest $request)
    {
        $this->mutationService->create($request->validated());

        return redirect()->back()->with('success', 'Mutasi berhasil dicatat.');
    }

    public function update(UpdateMutationRequest $request, $id)
    {
        $this->mutationService->update($id, $request->validated());

        return redirect()->back()->with('success', 'Mutasi berhasil diubah.');
    }

    public function destroy($id)
    {
        $this->mutationService->delete($id);

        return redirect()->back()->with('success', 'Mutasi berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);

        return Excel::download(new MutationsExport($filters), 'mutasi.xlsx');
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
