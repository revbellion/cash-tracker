<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function getAll(array $filters = []): array
    {
        $query = Customer::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $query->orderBy('name');

        $customers = $query->paginate(20);
        $totalActive = Customer::where('is_active', true)->count();
        $totalCustomers = Customer::count();

        return [
            'customers' => $customers,
            'totalActive' => $totalActive,
            'totalCustomers' => $totalCustomers,
        ];
    }

    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = Customer::findOrFail($id);
        $customer->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        return $customer;
    }

    public function delete(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $totalReceivables = $customer->receivables()->count();

        if ($totalReceivables > 0) {
            $customer->update(['is_active' => false]);
        } else {
            $customer->delete();
        }
    }

    public function getHistory(int $id): array
    {
        $customer = Customer::findOrFail($id);

        $receivables = $customer->receivables()
            ->with(['receivablePayments', 'account'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        $totalPiutang = $customer->receivables()->sum('amount');
        $totalPaid = 0;
        foreach ($customer->receivables as $rec) {
            $totalPaid += $rec->receivablePayments->sum('amount');
        }
        $sisaPiutang = $totalPiutang - $totalPaid;

        return [
            'customer' => $customer,
            'receivables' => $receivables,
            'totalPiutang' => $totalPiutang,
            'totalPaid' => $totalPaid,
            'sisaPiutang' => $sisaPiutang,
        ];
    }

    public function search(string $query): array
    {
        return Customer::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->toArray();
    }
}
