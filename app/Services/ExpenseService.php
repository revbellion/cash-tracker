<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    private array $cashMovementCategories = [
        'Stok Masuk', 'Piutang', 'Cash Keluar', 'Biaya MDR',
    ];

    public function create(array $data): Expense
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($data) {
            return Expense::create($data);
        });
    }

    public function update(int $id, array $data): Expense
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . now()->format('H:i:s');

        return DB::transaction(function () use ($id, $data) {
            $expense = Expense::findOrFail($id);

            // Blokir edit expense sistem
            $systemCategories = ['Piutang', 'Cash Keluar', 'Stok Opname Minus', 'Penyesuaian Kas', 'Biaya MDR'];
            if (in_array($expense->category, $systemCategories)) {
                throw new \DomainException('Pengeluaran sistem tidak bisa diedit.');
            }

            // Blokir edit expense dari stok masuk
            if ($expense->stock_transaction_id !== null) {
                throw new \DomainException('Pengeluaran stok masuk tidak bisa diedit.');
            }

            // Blokir edit expense dari pending transaction
            if ($expense->category && str_starts_with($expense->category, 'Pending ')) {
                throw new \DomainException('Pengeluaran pending transaction tidak bisa diedit.');
            }

            $expense->update($data);
            return $expense;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = Expense::findOrFail($id);

            // Blokir hapus expense sistem
            $systemCategories = ['Piutang', 'Cash Keluar', 'Stok Opname Minus', 'Penyesuaian Kas', 'Biaya MDR'];
            if (in_array($expense->category, $systemCategories)) {
                throw new \DomainException('Pengeluaran sistem tidak bisa dihapus.');
            }

            // Blokir hapus expense dari stok masuk
            if ($expense->stock_transaction_id !== null) {
                throw new \DomainException('Pengeluaran stok masuk tidak bisa dihapus langsung.');
            }

            // Blokir hapus expense dari pending transaction
            if ($expense->category && str_starts_with($expense->category, 'Pending ')) {
                throw new \DomainException('Pengeluaran pending transaction tidak bisa dihapus langsung.');
            }

            // Hapus bill_payment terkait jika ada
            \App\Models\BillPayment::where('expense_id', $expense->id)->delete();

            $expense->delete();
            return true;
        });
    }

    public function getAll(array $filters = []): array
    {
        $query = Expense::with('account');

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'real') {
                $query->whereNotIn('category', $this->cashMovementCategories)
                      ->where('category', 'not like', 'Pending %');
            } elseif ($filters['type'] === 'cash_movement') {
                $query->where(function ($q) {
                    $q->whereIn('category', $this->cashMovementCategories)
                      ->orWhere('category', 'like', 'Pending %');
                });
            }
        }

        if (!empty($filters['search'])) {
            $s = addcslashes($filters['search'], '%_');
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('category', 'like', "%{$s}%");
            });
        }

        $totalAmount = (clone $query)->sum('amount');
        $expenses = $query->latest()->paginate(20);

        return compact('expenses', 'totalAmount');
    }

    public function getCategories(): \Illuminate\Support\Collection
    {
        return Expense::select('category')->distinct()->pluck('category');
    }
}
