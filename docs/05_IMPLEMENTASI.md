# RENCANA IMPLEMENTASI (ARSIP — Aplikasi Sudah Jadi)

Dokumen ini mencatat **keputusan teknis** dan **konfigurasi manual** untuk referensi setup ulang atau deploy.

## Setup Awal

```bash
composer create-project laravel/laravel .
```

### .env Config
```
APP_NAME="ADI CELL | POS"
APP_ENV=production       # production → force HTTPS
APP_DEBUG=false
APP_URL=https://adicell.example.com

DB_DATABASE=cash_tracker
DB_USERNAME=root
DB_PASSWORD=

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
```

### Package Tambahan
```bash
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

### Auth Setup
- **Tidak pakai Breeze/Jetstream** — Bootstrap 5 vs Tailwind
- **Username-based login** — tambah kolom `username` + `permissions` ke tabel users via migration
- **Session driver**: default (file)

### Middleware Registration (`bootstrap/app.php`)
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'permission' => \App\Http\Middleware\CheckRole::class,
    ]);
    $middleware->redirectUsersTo(fn () => ...);   // guest redirect
})
```

### Database Migration
- `0001_01_01_000000_create_users_table.php` → default Laravel + edit migration untuk username & permissions
- 30+ migration files untuk semua tabel
- **PENTING**: `config/database.php` — ganti `Pdo\Mysql` → `PDO::MYSQL_ATTR_SSL_CA` untuk PHP 8.3+

### Form Request (14 files)
Semua dengan `authorize() = true`, custom attribute names (Indonesian), dan custom validation messages (Indonesian).

### Composer autoload
```json
"autoload": {
    "files": [
        "app/helpers.php"
    ]
}
```
Jangan lupa `composer dump-autoload` setelah helper ditambah/diubah.

## Model Configuration
### User
```php
protected function permissions(): Attribute
{
    return Attribute::make(
        get: fn($value) => json_decode($value, true) ?? [],
    );
}
// isAdmin(): return empty($this->permissions)
```
### Money / Amount
Semua kolom amount = **integer** (dalam rupiah, full number), kecuali:
- `recurring_bills.amount` dan `bill_payments.amount` → `decimal(15,2)` — kemungkinan nilai pecahan

### Date
Semua kolom date → `datetime` (diubah dari `date` via migration)
Cast: `'date' => 'datetime'`, `'due_date' => 'datetime'`

### Cascade → nullOnDelete
Migration `2026_06_14_121526`: mengubah SEMUA FK dari `cascadeOnDelete` ke `nullOnDelete` (9 FK).
Database financial tidak hilang saat parent (account/product/category) dihapus.

## Service Layer Pattern
- Controller hanya validasi + delegasi
- Service handle business logic + DB transaction
- Service tidak throw HTTP exception (kecuali `ReceivableService::update()` — TODO refactor)

## View Notes
### Layout (`layouts/app.blade.php`)
- ~650 baris inline CSS + HTML
- Sidebar: gradient blue `linear-gradient(180deg, #1e3a5f, #0a1628)`
- Dark mode via CSS custom properties + `.dark-mode` class
- View composer: hitung unpaid piutang count untuk badge sidebar
- CSRF logout via form (bukan link)

### Error Pages
- **403**: permission denied, tombol "Kembali ke Dashboard" (atau POS untuk kasir)
- **404**, **419**, **500**, **503**: light mode, info message + tombol kembali
- **419** khusus: "Sesi berakhir. Silakan login ulang."

### Receipt PDF
- DomPDF dengan kertas custom `[0, 0, 226, 500]` (thermal printer size)
- View: `stock/receipt-pdf.blade.php`

## RBAC Flow
1. User login → cek `isAdmin()` → redirect admin ke `/`, kasir ke `/stock/sales`
2. Route middleware: `permission:dashboard` dll
3. `CheckRole`: jika user admin → pass; jika tidak → cek in_array permission
4. `AdminMiddleware`: strict admin-only (users, backups, delete stock routes)

### 14 Permission Keys
```
dashboard, pos, stock_in, stock_opname, products, categories,
stock_report, accounts, mutations, incomes, expenses,
receivables, bills, summary
```

## Deployment Checklist
- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`
- [ ] `DB_MYSQL_DIR` — set path ke direktori bin MySQL server (untuk backup)
- [ ] Force HTTPS auto via `AppServiceProvider@boot` (cek `APP_ENV`)
- [ ] Login throttle: 5 attempt/menit via route middleware
- [ ] Login throttle is GLOBAL untuk route login (bisa diakses tanpa login)
- [ ] Semua form `autocomplete="off"`
- [ ] Hapus direktori `public/template/` jika tidak diperlukan (sisa template Ninja Admin)
- [ ] Hapus routes `auth.php` yang tidak dipakai
- [ ] Test backup/restore di environment production

## Manual Steps (seed)
Tidak ada seeder otomatis. Setelah migration:
1. Buat user admin via tinker atau UI:
   ```php
   User::create([
       'name' => 'Admin',
       'username' => 'admin',
       'password' => Hash::make('admin123'),
       'permissions' => null, // null = admin
   ]);
   ```
2. Buat akun keuangan via UI (`/accounts`): SHOPEEPAY, DANA, GOPAY, BCA, CASH, dll
3. Input saldo awal via `/opening-balances`

## Catatan Penting
- **Backup** via `exec('mysqldump ...')` — membutuhkan path MySQL binary di env `DB_MYSQL_DIR`
- **Reset data** — truncate 7 tabel, akun tetap aman
- **Produk soft-delete** via `is_active = false` (bukan hard delete)
- **Akun soft-delete** via `is_active = false`
- **User soft-delete** via model `delete()` (Eloquent soft delete tidak dipakai, hard delete saja)
- **Receipt ID** format: `INV-YYYYMMDD-xxxxx` (uniqid 5 char)
- Semua nilai uang dalam **integer Rupiah** (kecuali recurring bills)
