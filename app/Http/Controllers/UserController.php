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
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
        ];
        return view('users.index', compact('users', 'permissionKeys'));
    }

    public function create(): View
    {
        $permissionKeys = [
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
        ];
        return view('users.form', compact('permissionKeys'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:pos,stock_in,stock_opname,products,categories,stock_report',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'permissions' => $request->permissions,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $permissionKeys = [
            ['key' => 'pos', 'label' => 'POS Penjualan'],
            ['key' => 'stock_in', 'label' => 'Stok Masuk'],
            ['key' => 'stock_opname', 'label' => 'Stok Opname'],
            ['key' => 'products', 'label' => 'Data Barang'],
            ['key' => 'categories', 'label' => 'Kategori Barang'],
            ['key' => 'stock_report', 'label' => 'Laporan Stok'],
        ];
        return view('users.form', compact('user', 'permissionKeys'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:pos,stock_in,stock_opname,products,categories,stock_report',
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'permissions' => $request->permissions,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['Tidak bisa menghapus user admin.']);
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
