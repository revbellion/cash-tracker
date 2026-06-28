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

<div class="bulk-action-bar mb-3 d-none" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2 p-2 rounded-3" style="background:rgba(var(--theme-primary-rgb),0.08);border:1px solid rgba(var(--theme-primary-rgb),0.2);">
        <span class="fw-semibold" style="font-size:0.85rem;"><span id="bulkCount">0</span> dipilih</span>
        <span class="fw-bold" style="font-size:0.85rem;color:var(--theme-primary);" id="bulkTotal"></span>
        <form autocomplete="off" id="bulkDeleteForm" method="POST" action="{{ route('users.bulk-delete') }}" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-modern btn-danger btn-sm" onclick="event.preventDefault(); confirmDelete('Hapus data yang dipilih?').then(ok => ok && this.closest('form').submit());">
                <i class="fas fa-trash me-1"></i>Hapus
            </button>
        </form>
        <button type="button" class="btn btn-modern btn-secondary btn-sm" onclick="clearBulkSelection()">
            <i class="fas fa-times me-1"></i>Batal
        </button>
    </div>
</div>

<div class="card card-modern">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th class="ps-3" style="width:40px;"><input type="checkbox" class="form-check-input bulk-select-all"></th>
                    <th class="sortable" data-sort="string">Username</th>
                    <th class="sortable" data-sort="string">Nama</th>
                    <th class="sortable" data-sort="string">Tipe</th>
                    <th class="sortable" data-sort="string">Akses</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-3"><input type="checkbox" class="form-check-input bulk-select-item" value="{{ $user->id }}"></td>
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
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="event.preventDefault(); confirmDelete('Hapus user ini?').then(ok => ok && this.form.submit());">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-modern btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada user.</td></tr>
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

@push('scripts')
<script>
// Bulk selection
document.querySelector('.bulk-select-all')?.addEventListener('change', function() {
    var checked = this.checked;
    document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
        cb.checked = checked;
    });
    updateBulkBar();
});

document.querySelectorAll('.bulk-select-item').forEach(function(cb) {
    cb.addEventListener('change', updateBulkBar);
});

function updateBulkBar() {
    var checked = document.querySelectorAll('.bulk-select-item:checked');
    var count = checked.length;
    var bar = document.getElementById('bulkActionBar');
    if (!bar) return;
    
    if (count > 0) {
        bar.classList.remove('d-none');
        document.getElementById('bulkCount').textContent = count;
        
        var ids = [];
        checked.forEach(function(cb) { ids.push(cb.value); });
        var form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(function(el) { el.remove(); });
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
    } else {
        bar.classList.add('d-none');
    }
}

function clearBulkSelection() {
    document.querySelectorAll('.bulk-select-item, .bulk-select-all').forEach(function(cb) {
        cb.checked = false;
    });
    updateBulkBar();
}
</script>
@endpush
