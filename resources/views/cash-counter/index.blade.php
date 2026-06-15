@php
    $title = 'Cash Counter';
@endphp
@extends('layouts.app')

@section('content')
<div class="cash-counter">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card card-modern mb-3">
                <div class="card-body p-3">
                    <ul class="nav nav-tabs border-0 mb-3" id="denomTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="banknotes-tab" data-bs-toggle="tab" data-bs-target="#banknotes" type="button" role="tab" style="font-size:0.85rem;font-weight:600;">
                                <i class="fas fa-money-bill-wave me-1"></i> Uang Kertas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="coins-tab" data-bs-toggle="tab" data-bs-target="#coins" type="button" role="tab" style="font-size:0.85rem;font-weight:600;">
                                <i class="fas fa-coins me-1"></i> Uang Logam
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="denomTabsContent">
                        <div class="tab-pane fade show active" id="banknotes" role="tabpanel">
                            <div class="row g-2" id="banknotes-container"></div>
                        </div>
                        <div class="tab-pane fade" id="coins" role="tabpanel">
                            <div class="row g-2" id="coins-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-modern mb-3" style="background:var(--bg-card);border:none;border-radius:12px;box-shadow:var(--stat-card-shadow);">
                <div class="card-body text-center p-4">
                    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);font-weight:600;">Total Uang Dihitung</div>
                    <div style="font-size:2rem;font-weight:800;color:var(--text-primary);letter-spacing:-1px;" id="grand-total">Rp 0</div>
                    <div class="d-flex justify-content-center gap-4 mt-2">
                        <div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">Uang Kertas</div>
                            <div style="font-size:0.95rem;font-weight:700;color:var(--text-primary);" id="total-banknotes">Rp 0</div>
                        </div>
                        <div style="width:1px;background:var(--border-subtle);"></div>
                        <div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">Uang Logam</div>
                            <div style="font-size:0.95rem;font-weight:700;color:var(--text-primary);" id="total-coins">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-modern mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-bullseye" style="color:var(--theme-primary);font-size:0.9rem;"></i>
                        <span style="font-weight:600;font-size:0.85rem;color:var(--text-primary);">Target Kas</span>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text" style="background:var(--bg-card);border-color:var(--border-subtle);color:var(--text-muted);font-size:0.8rem;">Rp</span>
                        <input type="number" id="target-amount" class="form-control form-control-sm" min="0" placeholder="Masukkan target..." style="font-size:0.85rem;" oninput="updateTotal()">
                    </div>
                    <div id="target-result-panel" class="mt-2 d-none">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span style="font-size:0.8rem;color:var(--text-muted);">Status:</span>
                            <span id="target-status" class="badge rounded-pill" style="font-size:0.75rem;">Sesuai</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span style="font-size:0.8rem;color:var(--text-muted);">Selisih:</span>
                            <span id="target-diff" class="fw-bold" style="font-size:0.85rem;">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-modern mb-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-chart-pie" style="color:var(--theme-primary);font-size:0.9rem;"></i>
                        <span style="font-weight:600;font-size:0.85rem;color:var(--text-primary);">Distribusi Denominasi</span>
                    </div>
                    <div class="chart-container" style="position:relative;height:180px;">
                        <canvas id="distribution-chart"></canvas>
                        <div id="chart-placeholder" class="d-flex flex-column align-items-center justify-content-center" style="position:absolute;inset:0;color:var(--text-muted);font-size:0.8rem;">
                            <i class="fas fa-info-circle mb-1" style="font-size:1.2rem;"></i>
                            <span>Masukkan beberapa lembar untuk melihat distribusi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button class="btn btn-modern btn-secondary flex-fill" onclick="resetCalculator()">
                    <i class="fas fa-undo-alt me-1"></i> Reset
                </button>
                <button class="btn btn-modern btn-primary flex-fill" onclick="copySummary()">
                    <i class="fas fa-copy me-1"></i> Salin
                </button>
                <button class="btn btn-modern btn-success flex-fill" onclick="openSaveModal()">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>

            <div class="card card-modern">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-history" style="color:var(--theme-primary);font-size:0.9rem;"></i>
                            <span style="font-weight:600;font-size:0.85rem;color:var(--text-primary);">Riwayat Sesi</span>
                        </div>
                        <button class="btn btn-sm btn-modern btn-danger" onclick="clearHistory()" style="font-size:0.7rem;">
                            <i class="fas fa-trash me-1"></i> Hapus Semua
                        </button>
                    </div>
                    <div id="history-list-container">
                        <div class="text-center text-muted py-3" style="font-size:0.85rem;">Belum ada sesi yang disimpan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-size:1rem;">Simpan Sesi Perhitungan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama / Catatan Sesi</label>
                    <input type="text" id="session-title" class="form-control" placeholder="Contoh: Kas Toko Pagi, Setoran Bank...">
                </div>
                <div class="p-3 rounded-3" style="background:var(--border-subtle);">
                    <div class="d-flex justify-content-between">
                        <span style="font-size:0.85rem;color:var(--text-muted);">Total yang akan disimpan:</span>
                        <strong id="modal-total-display" style="font-size:0.95rem;">Rp 0</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-modern btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-modern btn-primary" onclick="saveSession()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast-notification d-none"></div>
@endsection

@push('styles')
<style>
.cash-counter .form-control:focus { box-shadow: none; }
.cash-counter .nav-tabs .nav-link { color: var(--text-muted); border: none; border-bottom: 2px solid transparent; padding: 0.4rem 0.8rem; }
.cash-counter .nav-tabs .nav-link.active { color: var(--theme-primary); background: none; border-bottom-color: var(--theme-primary); }
.cash-counter .nav-tabs .nav-link:hover { color: var(--text-primary); border-bottom-color: transparent; }

.denom-card {
    background: var(--bg-card);
    border: 1px solid var(--border-subtle);
    border-radius: 10px;
    padding: 0.75rem;
    transition: box-shadow 0.15s;
    border-left: 4px solid var(--denom-color, var(--theme-primary));
}
.denom-card:hover { box-shadow: var(--card-shadow-hover); }

.denom-label {
    font-weight: 700;
    font-size: 0.8rem;
    color: var(--text-primary);
}

.denom-controls {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 6px;
}

.denom-controls .btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.12s;
    padding: 0;
}
.denom-controls .btn-icon:hover { background: var(--border-subtle); }
.denom-controls .btn-icon:active { transform: scale(0.95); }
.denom-controls .btn-dec:hover { background: rgba(239,68,68,0.1); color: #ef4444; }
.denom-controls .btn-inc:hover { background: rgba(16,185,129,0.1); color: #10b981; }

.denom-controls .count-input {
    width: 56px;
    text-align: center;
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    background: var(--bg-card);
    color: var(--text-primary);
    font-size: 0.85rem;
    font-weight: 700;
    padding: 4px;
    outline: none;
}
.denom-controls .count-input:focus { border-color: var(--theme-primary); }

.denom-shortcuts {
    display: flex;
    gap: 3px;
    margin-top: 4px;
}
.denom-shortcuts .btn-shortcut {
    padding: 2px 8px;
    border: 1px solid var(--border-subtle);
    border-radius: 6px;
    background: var(--bg-card);
    color: var(--text-muted);
    font-size: 0.65rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.1s;
}
.denom-shortcuts .btn-shortcut:hover { background: var(--theme-primary); color: #fff; border-color: var(--theme-primary); }

.denom-subtotal {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-top: 4px;
    text-align: right;
}

.dark-mode .denom-card { border-color: #3b4a5c; }
.dark-mode .denom-controls .btn-icon { border-color: #3b4a5c; background: #1e293b; }
.dark-mode .denom-controls .btn-icon:hover { background: #334155; }
.dark-mode .denom-controls .count-input { border-color: #3b4a5c; background: #1e293b; }
.dark-mode .denom-shortcuts .btn-shortcut { border-color: #3b4a5c; background: #1e293b; }

.toast-notification {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--bg-card);
    color: var(--text-primary);
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 9999;
    border: 1px solid var(--border-subtle);
}
.dark-mode .toast-notification { border-color: #3b4a5c; }
</style>
@endpush

@push('scripts')
<script>
const denoms = [
    { key: 100000, label: 'Rp 100.000', color: '#f43f5e', group: 'banknotes' },
    { key: 50000,  label: 'Rp 50.000',  color: '#3b82f6', group: 'banknotes' },
    { key: 20000,  label: 'Rp 20.000',  color: '#10b981', group: 'banknotes' },
    { key: 10000,  label: 'Rp 10.000',  color: '#8b5cf6', group: 'banknotes' },
    { key: 5000,   label: 'Rp 5.000',   color: '#f59e0b', group: 'banknotes' },
    { key: 2000,   label: 'Rp 2.000',   color: '#6b7280', group: 'banknotes' },
    { key: 1000,   label: 'Rp 1.000',   color: '#84cc16', group: 'banknotes' },
    { key: 'c1000', value: 1000, label: 'Koin Rp 1.000', color: '#f97316', group: 'coins' },
    { key: 'c500',  value: 500,  label: 'Koin Rp 500',   color: '#a855f7', group: 'coins' },
    { key: 'c200',  value: 200,  label: 'Koin Rp 200',   color: '#14b8a6', group: 'coins' },
    { key: 'c100',  value: 100,  label: 'Koin Rp 100',   color: '#eab308', group: 'coins' },
];

let chartInstance = null;
const DENOM_KEYS = denoms.map(d => d.key);

function getDenomValue(key) {
    const d = denoms.find(x => x.key === key);
    return d ? (d.value || d.key) : 0;
}

function buildCards() {
    denoms.forEach(d => {
        const col = document.createElement('div');
        col.className = 'denom-grid-item';
        col.innerHTML = `
            <div class="denom-card" style="--denom-color:${d.color};">
                <div class="denom-label">${d.label}</div>
                <div class="denom-controls">
                    <button class="btn-icon btn-dec" onclick="adjustCount('${d.key}',-1)"><i class="fas fa-minus"></i></button>
                    <input type="number" id="count-${d.key}" class="count-input" min="0" placeholder="0" oninput="updateTotal()">
                    <button class="btn-icon btn-inc" onclick="adjustCount('${d.key}',1)"><i class="fas fa-plus"></i></button>
                </div>
                <div class="denom-shortcuts">
                    <button class="btn-shortcut" onclick="adjustCount('${d.key}',10)">+10</button>
                    <button class="btn-shortcut" onclick="adjustCount('${d.key}',50)">+50</button>
                    <button class="btn-shortcut" onclick="adjustCount('${d.key}',100)">+100</button>
                </div>
                <div class="denom-subtotal" id="subtotal-${d.key}">Rp 0</div>
            </div>
        `;
        document.getElementById(d.group + '-container').appendChild(col);
    });
}

function adjustCount(key, change) {
    const input = document.getElementById('count-' + key);
    let val = parseInt(input.value) || 0;
    val = Math.max(0, val + change);
    input.value = val;
    updateTotal();
}

function formatRupiah(num) {
    return 'Rp ' + num.toLocaleString('id-ID');
}

function updateTotal() {
    let totalBanknotes = 0, totalCoins = 0;
    DENOM_KEYS.forEach(key => {
        const input = document.getElementById('count-' + key);
        const count = parseInt(input.value) || 0;
        const value = getDenomValue(key);
        const subtotal = count * value;
        document.getElementById('subtotal-' + key).textContent = formatRupiah(subtotal);
        if (denoms.find(d => d.key === key).group === 'banknotes') {
            totalBanknotes += subtotal;
        } else {
            totalCoins += subtotal;
        }
    });
    const grandTotal = totalBanknotes + totalCoins;

    document.getElementById('grand-total').textContent = formatRupiah(grandTotal);
    document.getElementById('total-banknotes').textContent = formatRupiah(totalBanknotes);
    document.getElementById('total-coins').textContent = formatRupiah(totalCoins);
    document.getElementById('modal-total-display').textContent = formatRupiah(grandTotal);

    updateTargetCash(grandTotal);
    updateChart(grandTotal);
}

function updateTargetCash(grandTotal) {
    const targetInput = document.getElementById('target-amount');
    const target = parseInt(targetInput.value) || 0;
    const panel = document.getElementById('target-result-panel');
    if (target <= 0) { panel.classList.add('d-none'); return; }
    panel.classList.remove('d-none');

    const statusEl = document.getElementById('target-status');
    const diffEl = document.getElementById('target-diff');
    const diff = grandTotal - target;

    if (grandTotal === target) {
        statusEl.textContent = 'Pas / Sesuai';
        statusEl.className = 'badge rounded-pill bg-success';
        diffEl.textContent = 'Rp 0';
        diffEl.style.color = 'var(--text-primary)';
    } else if (grandTotal > target) {
        statusEl.textContent = 'Lebih';
        statusEl.className = 'badge rounded-pill bg-warning text-dark';
        diffEl.textContent = formatRupiah(diff);
        diffEl.style.color = '#f59e0b';
    } else {
        statusEl.textContent = 'Kurang';
        statusEl.className = 'badge rounded-pill bg-danger';
        diffEl.textContent = formatRupiah(Math.abs(diff));
        diffEl.style.color = '#ef4444';
    }
}

function updateChart(grandTotal) {
    const labels = [];
    const data = [];
    const colors = [];
    const placeholder = document.getElementById('chart-placeholder');

    denoms.forEach(d => {
        const input = document.getElementById('count-' + d.key);
        const count = parseInt(input.value) || 0;
        const value = getDenomValue(d.key);
        const subtotal = count * value;
        if (subtotal > 0) {
            labels.push(d.label);
            data.push(subtotal);
            colors.push(d.color);
        }
    });

    if (data.length === 0) {
        placeholder.style.display = 'flex';
        if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
        return;
    }
    placeholder.style.display = 'none';

    const ctx = document.getElementById('distribution-chart').getContext('2d');
    if (chartInstance) { chartInstance.destroy(); }

    chartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, padding: 8, font: { size: 10 }, color: getComputedStyle(document.body).getPropertyValue('--text-primary').trim() || '#1e293b' }
                }
            }
        }
    });
}

function resetCalculator() {
    if (!confirm('Reset semua input?')) return;
    DENOM_KEYS.forEach(key => {
        document.getElementById('count-' + key).value = '';
    });
    document.getElementById('target-amount').value = '';
    document.getElementById('target-result-panel').classList.add('d-none');
    updateTotal();
    showToast('Semua input direset');
}

function copySummary() {
    let text = '=== RINGKASAN KAS ===\n\n';
    denoms.forEach(d => {
        const input = document.getElementById('count-' + d.key);
        const count = parseInt(input.value) || 0;
        if (count > 0) {
            const value = getDenomValue(d.key);
            text += d.label + ' : ' + count + ' x Rp ' + value.toLocaleString('id-ID') + ' = Rp ' + (count * value).toLocaleString('id-ID') + '\n';
        }
    });
    const totalEl = document.getElementById('grand-total');
    text += '\nTOTAL: ' + totalEl.textContent;

    const targetInput = document.getElementById('target-amount');
    const target = parseInt(targetInput.value) || 0;
    if (target > 0) {
        text += '\nTarget Kas: Rp ' + target.toLocaleString('id-ID');
        const grandTotal = parseInt(document.getElementById('grand-total').textContent.replace(/[^\d]/g, '')) || 0;
        const diff = grandTotal - target;
        text += '\nSelisih: Rp ' + Math.abs(diff).toLocaleString('id-ID') + (diff >= 0 ? ' (Lebih)' : ' (Kurang)');
    }

    navigator.clipboard.writeText(text).then(() => showToast('Ringkasan disalin ke clipboard'));
}

const saveModal = new bootstrap.Modal(document.getElementById('saveModal'));
function openSaveModal() {
    document.getElementById('session-title').value = '';
    saveModal.show();
}

function saveSession() {
    const title = document.getElementById('session-title').value.trim();
    if (!title) { showToast('Masukkan nama sesi'); return; }

    const denominations = {};
    DENOM_KEYS.forEach(key => {
        const input = document.getElementById('count-' + key);
        denominations[key] = parseInt(input.value) || 0;
    });
    const totalAmount = parseInt(document.getElementById('grand-total').textContent.replace(/[^\d]/g, '')) || 0;
    const targetAmount = parseInt(document.getElementById('target-amount').value) || null;

    fetch('{{ route("cash-counter.sessions.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ title, denominations, target_amount: targetAmount, total_amount: totalAmount })
    })
    .then(r => r.json())
    .then(() => { saveModal.hide(); showToast('Sesi disimpan'); loadHistory(); })
    .catch(() => showToast('Gagal menyimpan sesi'));
}

function loadHistory() {
    fetch('{{ route("cash-counter.history") }}')
    .then(r => r.json())
    .then(sessions => {
        const container = document.getElementById('history-list-container');
        if (sessions.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3" style="font-size:0.85rem;">Belum ada sesi yang disimpan</div>';
            return;
        }
        container.innerHTML = sessions.map(s => `
            <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid var(--border-subtle);">
                <div>
                    <div class="fw-semibold" style="font-size:0.85rem;color:var(--text-primary);">${escapeHtml(s.title)}</div>
                    <div style="font-size:0.7rem;color:var(--text-muted);">${formatRupiah(s.total_amount)} &middot; ${new Date(s.created_at).toLocaleDateString('id-ID')}</div>
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-modern btn-primary" style="font-size:0.65rem;padding:0.2rem 0.5rem;" onclick="loadSession(${s.id})"><i class="fas fa-folder-open"></i></button>
                    <button class="btn btn-sm btn-modern btn-danger" style="font-size:0.65rem;padding:0.2rem 0.5rem;" onclick="deleteSession(${s.id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `).join('');
    });
}

function loadSession(id) {
    fetch(`{{ url("cash-counter/sessions") }}/${id}`)
    .then(r => r.json())
    .then(s => {
        const data = s.denominations || {};
        DENOM_KEYS.forEach(key => {
            document.getElementById('count-' + key).value = data[key] || 0;
        });
        if (s.target_amount) document.getElementById('target-amount').value = s.target_amount;
        updateTotal();
        showToast('Sesi "' + escapeHtml(s.title) + '" dimuat');
    });
}

function deleteSession(id) {
    if (!confirm('Hapus sesi ini?')) return;
    fetch(`{{ url("cash-counter/sessions") }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(() => { showToast('Sesi dihapus'); loadHistory(); });
}

function clearHistory() {
    if (!confirm('Hapus semua sesi?')) return;
    fetch('{{ route("cash-counter.history") }}')
    .then(r => r.json())
    .then(sessions => {
        let done = 0;
        sessions.forEach(s => {
            fetch(`{{ url("cash-counter/sessions") }}/${s.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => { done++; if (done === sessions.length) { showToast('Semua sesi dihapus'); loadHistory(); }});
        });
        if (sessions.length === 0) showToast('Tidak ada sesi');
    });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showToast(msg) {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(() => el.classList.add('d-none'), 2500);
}

document.addEventListener('DOMContentLoaded', function() {
    buildCards();
    updateTotal();
    loadHistory();
});
</script>
@endpush
