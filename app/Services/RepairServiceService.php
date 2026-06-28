<?php

namespace App\Services;

use App\Models\RepairService;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RepairServiceService
{
    private array $deviceTypes = [
        'hp' => 'HP',
        'laptop' => 'Laptop',
    ];

    public function create(array $data): RepairService
    {
        return DB::transaction(function () use ($data) {
            $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');
            $data['sparepart_cost'] = $data['sparepart_cost'] ?? 0;
            $data['total'] = $data['service_fee'] + $data['sparepart_cost'];

            $deviceLabel = $this->deviceTypes[$data['device_type']] ?? $data['device_type'];
            $desc = "Servis {$deviceLabel}" . ($data['device_model'] ? " ({$data['device_model']})" : '') . " - {$data['customer_name']}";

            $income = Income::create([
                'account_id'  => $data['account_id'],
                'category'    => 'Jasa Servis',
                'amount'      => $data['total'],
                'description' => $data['issue_description']
                    ? "{$desc}: {$data['issue_description']}"
                    : $desc,
                'date'        => $data['date'],
            ]);

            $data['income_id'] = $income->id;

            return RepairService::create($data);
        });
    }

    public function update(int $id, array $data): RepairService
    {
        return DB::transaction(function () use ($id, $data) {
            $repairService = RepairService::findOrFail($id);
            $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');
            $data['sparepart_cost'] = $data['sparepart_cost'] ?? 0;
            $data['total'] = $data['service_fee'] + $data['sparepart_cost'];

            $deviceLabel = $this->deviceTypes[$data['device_type']] ?? $data['device_type'];
            $desc = "Servis {$deviceLabel}" . ($data['device_model'] ? " ({$data['device_model']})" : '') . " - {$data['customer_name']}";

            // Update income terkait
            if ($repairService->income_id) {
                $income = Income::findOrFail($repairService->income_id);
                $income->update([
                    'account_id'  => $data['account_id'],
                    'amount'      => $data['total'],
                    'description' => $data['issue_description']
                        ? "{$desc}: {$data['issue_description']}"
                        : $desc,
                    'date'        => $data['date'],
                ]);
            }

            $repairService->update($data);
            return $repairService;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $repairService = RepairService::findOrFail($id);

            if ($repairService->income_id) {
                Income::where('id', $repairService->income_id)->delete();
            }

            return $repairService->delete();
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = RepairService::with('account');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['device_type'])) {
            $query->where('device_type', $filters['device_type']);
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('device_model', 'like', "%{$s}%")
                  ->orWhere('issue_description', 'like', "%{$s}%");
            });
        }

        $totalAmount = (clone $query)->sum('total');
        $services = $query->latest()->paginate(20);

        return compact('services', 'totalAmount');
    }

    public function getDeviceTypes(): array
    {
        return $this->deviceTypes;
    }
}
