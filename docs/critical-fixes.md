# Cash Tracker — Catatan Perbaikan

Tanggal: 24 Juni 2026 (update)

---

## Ringkasan

Total **37 perbaikan** dilakukan selama 5 ronde scan.

---

## KRITIS (5 fix)

| # | Masalah | File |
|---|---------|------|
| 1 | Dashboard mutationsIn/Out query identik → digabung | `DashboardService.php:90-93` |
| 2 | Piutang parsial tidak buat Income → buat Income setiap bayar | `ReceivableService.php:92-99` |
| 3 | Bill re-payment orphans Expense → hapus Expense lama dulu | `BillService.php:58-64` |
| 4 | Opname delete hapus semua mutasi → filter tanggal+akun | `OpnameSaldoService.php:163-170` |
| 5 | Update piutang tidak sync Expense → sync saat update | `ReceivableService.php:60-63` |

## HIGH (7 fix)

| # | Masalah | File |
|---|---------|------|
| 6 | pay() race condition → lockForUpdate | `ReceivableService.php:72` |
| 7 | Pending delete guard di-comment → uncomment | `PendingTransactionService.php:145-148` |
| 8 | payBill() race condition → lockForUpdate | `BillService.php:59` |
| 9 | complete() race condition → lockForUpdate | `PendingTransactionService.php:81` |
| 10 | IncomeService lockForUpdate salah (model instance) → pakai relation builder | `IncomeService.php:50` |
| 11 | Income delete guards tidak lengkap → tambah 6 kategori sistem | `IncomeService.php:42-46` |
| 12 | Receipt ID colliding → pakai random_bytes | `StockService.php:48` |

## MEDIUM (12 fix)

| # | Masalah | File |
|---|---------|------|
| 13 | Pending create silent failure → throw exception | `PendingTransactionService.php:48-64` |
| 14 | deleteStockIn product null → throw exception | `StockService.php:136-139` |
| 15 | OpnameSaldo double-count payments → hapus dari formula | `OpnameSaldoService.php:62-64` |
| 16 | ExpenseService delete guards → tambah Stok Masuk/Pending/Cash Keluar/StokOpname | `ExpenseService.php:52-65` |
| 17 | ExpenseService update guards → tambah kategori sistem | `ExpenseService.php:27-40` |
| 18 | ReceivableService delete Income cleanup salah (subquery) → exact match dengan ID | `ReceivableService.php:129-131` |
| 19 | Dashboard daily profits double-count → hapus $dailyPayments | `DashboardService.php:149-158` |
| 20 | ProductController stock editable → hapus dari edit form | `ProductController.php:62-75` |
| 21 | ReceivableController missing try/catch → tambah try/catch | `ReceivableController.php:37,57,101` |
| 22 | OpeningBalanceController missing transaction → bungkus DB::transaction | `OpeningBalanceController.php:36-44` |
| 23 | PendingsExport tidak ada → buat class baru | `Exports/PendingsExport.php` |
| 24 | BillPayment/RecurringBill cast salah → integer | `BillPayment.php:21`, `RecurringBill.php:22` |

## LOW (8 fix)

| # | Masalah | File |
|---|---------|------|
| 25 | OpnameSaldo unused import → hapus | `OpnameSaldoService.php:8` |
| 26 | deleteSale product null → throw exception | `StockService.php:120-122` |
| 27 | PendingTransactionController missing try/catch → tambah | `PendingTransactionController.php:35` |
| 28 | Opname filter qty=0 → qty>=0 | `StockController.php:139` |
| 29 | bank_type tidak divalidasi → tambah validasi | `StorePendingTransactionRequest.php:22-26` |
| 30 | Receivable voided badge → tambah di model | `Receivable.php:65-67` |
| 31 | Hapus tombol hapus piutang paid → disabled button | `receivables/index.blade.php:102-112` |
| 32 | Opname desc vs description key → samakan | `StockController.php:62`, `opname.blade.php:42` |

## CLEANUP (5 fix)

| # | Masalah | File |
|---|---------|------|
| 33 | Export null-safe operator | `ExpensesExport.php:52`, `IncomesExport.php:55` |
| 34 | BackupController FK checks try/finally | `BackupController.php:96-108` |
| 35 | WhatsApp feature dihapus | Service + Controller + Route |
| 36 | ReceivableService receivable_id tidak di-set di Expense → set | `ReceivableService.php:30` |
| 37 | LIKE wildcards tidak di-escape → addcslashes di semua search | 5 services + 5 exports + ProductController |

---

## File yang diubah

| File | Perubahan |
|------|-----------|
| `app/Services/DashboardService.php` |mutations digabung, daily profits hapus double-count |
| `app/Services/ReceivableService.php` | partial payment, update sync, delete guard, exact match, ID description, receivable_id, whatsapp removed |
| `app/Services/BillService.php` | re-payment hapus Expense lama, payBill lockForUpdate |
| `app/Services/OpnameSaldoService.php` | delete filter, calculateBalance, unused import |
| `app/Services/PendingTransactionService.php` | create throw, delete guard, complete lock, search escaped |
| `app/Services/StockService.php` | deleteStockIn/deleteSale guard, receipt random, opname description |
| `app/Services/ExpenseService.php` | delete + update guards (7 kategori sistem) |
| `app/Services/IncomeService.php` | delete lockForUpdate + null check, 7 system categories, search escaped |
| `app/Services/MutationService.php` | search escaped |
| `app/Http/Controllers/ReceivableController.php` | try/catch, whatsapp removed |
| `app/Http/Controllers/ProductController.php` | stock field removed, search escaped |
| `app/Http/Controllers/OpeningBalanceController.php` | DB::transaction |
| `app/Http/Controllers/PendingTransactionController.php` | try/catch |
| `app/Http/Controllers/StockController.php` | desc→description, opname filter |
| `app/Http/Controllers/SummaryController.php` | filter piutang/opname |
| `app/Http/Controllers/BackupController.php` | try/finally FK checks |
| `app/Http/Requests/StorePendingTransactionRequest.php` | bank_type validation |
| `app/Models/Receivable.php` | voided badge, due_date copy() |
| `app/Models/BillPayment.php` | amount cast integer |
| `app/Models/RecurringBill.php` | amount cast integer |
| `app/Exports/PendingsExport.php` | class baru |
| `app/Exports/ExpensesExport.php` | null-safe, search escaped |
| `app/Exports/IncomesExport.php` | null-safe, search escaped |
| `app/Exports/MutationsExport.php` | search escaped |
| `app/Exports/ReceivablesExport.php` | search escaped |
| `resources/views/receivables/index.blade.php` | disabled delete button |
| `resources/views/products/index.blade.php` | stock field removed |
| `resources/views/stock/opname.blade.php` | desc→description |
| `routes/web.php` | whatsapp route removed |
| `docs/critical-fixes.md` | dokumentasi ini |

---

## Testing

PHPUnit tidak bisa jalan karena migrasi MySQL-only. Test manual via Laragon.

### Checklist

| # | Fitur | Cara test |
|---|-------|-----------|
| 1 | Dashboard | Buka dashboard, cek totalMutationsIn/Out, cek profit chart |
| 2 | Piutang parsial | Buat piutang, bayar sebagian, cek Income muncul |
| 3 | Tagihan 2x | Bayar tagihan yang sama 2x nominal beda, cek Expense tidak dobel |
| 4 | Opname delete | Buat 2 opname akun sama tanggal beda, hapus salah 1 |
| 5 | Update piutang | Ubah nominal piutang sebelum bayar, cek Expense ikut |
| 6 | Pending transaction | Coba hapus transaksi completed, harus error |
| 7 | Summary P&L | Cek summary tidak menampilkan Piutang/Opname |
| 8 | Delete piutang paid | Tombol hapus disabled, coba via API harus error |
| 9 | Export pending | Klik export transaksi pending, harus download |
| 10 | Stok opname qty=0 | Isi opname qty=0, cek bisa disimpan |
| 11 | POS sale | Buat penjualan, cek receipt ID unik |
| 12 | Search | Cari dengan karakter `%` atau `_`, hasil harus normal |
