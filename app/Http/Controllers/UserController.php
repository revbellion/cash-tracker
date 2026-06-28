<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('created_at', 'desc')->get();
        $permissionKeys = [
            ['key' => 'dashboard', 'label' => 'Dashboard'],
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
            ['key' => 'sales_report', 'label' => 'Laporan Penjualan'],
            ['key' => 'accounts', 'label' => 'Akun & Modal Awal'],
            ['key' => 'mutations', 'label' => 'Mutasi'],
            ['key' => 'incomes', 'label' => 'Pendapatan'],
            ['key' => 'expenses', 'label' => 'Pengeluaran'],
            ['key' => 'receivables', 'label' => 'Piutang'],
            ['key' => 'bills', 'label' => 'Tagihan'],
            ['key' => 'summary', 'label' => 'Ringkasan'],
            ['key' => 'cash_counter', 'label' => 'Cash Counter'],
            ['key' => 'customers', 'label' => 'Pelanggan'],
            ['key' => 'returns', 'label' => 'Retur Barang'],
            ['key' => 'print_orders', 'label' => 'Jasa Cetak'],
            ['key' => 'repair_services', 'label' => 'Jasa Servis'],
            ['key' => 'reports', 'label' => 'Laporan Keuangan (Laba Rugi & Neraca)'],
        ];
        $totalUsers = $users->count();
        return view('users.index', compact('users', 'permissionKeys', 'totalUsers'));
    }

    public function create(): View
    {
        $permissionKeys = [
            ['key' => 'dashboard', 'label' => 'Dashboard'],
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
            ['key' => 'sales_report', 'label' => 'Laporan Penjualan'],
            ['key' => 'accounts', 'label' => 'Akun & Modal Awal'],
            ['key' => 'mutations', 'label' => 'Mutasi'],
            ['key' => 'incomes', 'label' => 'Pendapatan'],
            ['key' => 'expenses', 'label' => 'Pengeluaran'],
            ['key' => 'receivables', 'label' => 'Piutang'],
            ['key' => 'bills', 'label' => 'Tagihan'],
            ['key' => 'summary', 'label' => 'Ringkasan'],
            ['key' => 'cash_counter', 'label' => 'Cash Counter'],
            ['key' => 'customers', 'label' => 'Pelanggan'],
            ['key' => 'returns', 'label' => 'Retur Barang'],
            ['key' => 'print_orders', 'label' => 'Jasa Cetak'],
            ['key' => 'repair_services', 'label' => 'Jasa Servis'],
            ['key' => 'reports', 'label' => 'Laporan Keuangan (Laba Rugi & Neraca)'],
        ];
        return view('users.form', compact('permissionKeys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:dashboard,pos,stock_in,stock_opname,print_orders,repair_services,products,categories,stock_report,sales_report,accounts,mutations,incomes,expenses,receivables,bills,summary,cash_counter,customers,returns,reports',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'is_admin' => $request->boolean('is_admin'),
            'permissions' => $request->boolean('is_admin') ? null : $request->permissions,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $permissionKeys = [
            ['key' => 'dashboard', 'label' => 'Dashboard'],
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
            ['key' => 'sales_report', 'label' => 'Laporan Penjualan'],
            ['key' => 'accounts', 'label' => 'Akun & Modal Awal'],
            ['key' => 'mutations', 'label' => 'Mutasi'],
            ['key' => 'incomes', 'label' => 'Pendapatan'],
            ['key' => 'expenses', 'label' => 'Pengeluaran'],
            ['key' => 'receivables', 'label' => 'Piutang'],
            ['key' => 'bills', 'label' => 'Tagihan'],
            ['key' => 'summary', 'label' => 'Ringkasan'],
            ['key' => 'cash_counter', 'label' => 'Cash Counter'],
            ['key' => 'customers', 'label' => 'Pelanggan'],
            ['key' => 'returns', 'label' => 'Retur Barang'],
            ['key' => 'print_orders', 'label' => 'Jasa Cetak'],
            ['key' => 'repair_services', 'label' => 'Jasa Servis'],
            ['key' => 'reports', 'label' => 'Laporan Keuangan (Laba Rugi & Neraca)'],
        ];
        return view('users.form', compact('user', 'permissionKeys'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'is_admin' => 'nullable|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:dashboard,pos,stock_in,stock_opname,print_orders,repair_services,products,categories,stock_report,sales_report,accounts,mutations,incomes,expenses,receivables,bills,summary,cash_counter,customers,returns,reports',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'is_admin' => $request->boolean('is_admin'),
            'permissions' => $request->boolean('is_admin') ? null : $request->permissions,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|array']);
        $deleted = 0;
        foreach ($request->ids as $id) {
            try {
                $user = User::findOrFail($id);
                if ($user->isAdmin() || $user->id === Auth::id()) {
                    continue;
                }
                $user->delete();
                $deleted++;
            } catch (\Exception $e) {
                // skip
            }
        }
        return redirect()->route('users.index')->with('success', "{$deleted} user berhasil dihapus.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['Tidak bisa menghapus user admin.']);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors(['Tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
