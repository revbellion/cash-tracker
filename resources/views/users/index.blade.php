@php
    $title = 'Kelola User';
@endphp
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Kelola User</h5>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-modern btn-sm">
        <i class="fas fa-plus me-1"></i> Tambah User
    </a>
</div>

<div class="card card-modern">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Akses</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user->username }}</td>
                        <td>{{ $user->name }}</td>
                        <td>
                            @if($user->isAdmin())
                                <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Admin</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:0.7rem;">Kasir</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isAdmin())
                                <span class="text-muted" style="font-size:0.8rem;">Semua modul</span>
                            @else
                                @php
                                    $labels = ['dashboard'=>'Dashboard','pos'=>'POS','stock_in'=>'Stok Masuk','stock_opname'=>'Opname','products'=>'Barang','categories'=>'Kategori','stock_report'=>'Laporan','accounts'=>'Akun','mutations'=>'Mutasi','incomes'=>'Pemasukan','expenses'=>'Pengeluaran','receivables'=>'Piutang','bills'=>'Tagihan','summary'=>'Ringkasan','cash_counter'=>'Cash Counter'];
                                @endphp
                                @foreach($user->permissions ?? [] as $p)
                                    <span class="badge bg-info" style="font-size:0.65rem;margin:1px;">{{ $labels[$p] ?? $p }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-modern btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if(!$user->isAdmin())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-modern btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 summary-bar" style="border-top:2px solid var(--border-subtle);">
        <div>
            <span style="font-size:0.8rem;color:var(--text-muted);">Total {{ $totalUsers }} user</span>
        </div>
        <div></div>
    </div>
</div>
@endsection
