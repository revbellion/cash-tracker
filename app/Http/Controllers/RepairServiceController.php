<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairServiceRequest;
use App\Http\Requests\UpdateRepairServiceRequest;
use App\Models\Account;
use App\Services\RepairServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepairServiceController extends Controller
{
    public function __construct(
        protected RepairServiceService $repairServiceService
    ) {}

    public function index(Request $request)
    {
        $filters = $this->parseFilters($request);
        $result = $this->repairServiceService->getAll($filters);

        return view('repair-services.index', [
            'services' => $result['services'],
            'accounts' => Account::active()->where('type', '!=', 'ppob')->get(),
            'deviceTypes' => $this->repairServiceService->getDeviceTypes(),
            'totalAmount' => $result['totalAmount'],
            'defaultAccount' => Account::active()->where('name', config('accounts.cash_name'))->first(),
        ]);
    }

    public function store(StoreRepairServiceRequest $request)
    {
        try {
            $this->repairServiceService->create($request->validated());
            return redirect()->back()->with('success', 'Service berhasil dicatat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mencatat service: ' . $e->getMessage());
        }
    }

    public function update(UpdateRepairServiceRequest $request, $id)
    {
        try {
            $this->repairServiceService->update($id, $request->validated());
            return redirect()->back()->with('success', 'Data service berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data service: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->repairServiceService->delete($id);
            return redirect()->back()->with('success', 'Data service berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data service: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $this->repairServiceService->delete($id);
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->back()->with('success', "{$deleted} data berhasil dihapus.");
    }

    private function parseFilters(Request $request): array
    {
        $raw = $request->only(['date_from', 'date_to', 'device_type', 'search']);
        $raw = array_map(fn($v) => $v === '' ? null : $v, $raw);

        return array_filter(
            Validator::make($raw, [
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'device_type' => 'nullable|string|in:hp,laptop',
                'search' => 'nullable|string|max:100',
            ])->valid(),
            fn($v) => $v !== null
        );
    }
}
