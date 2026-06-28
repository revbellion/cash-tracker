# STRUKTUR PROYEK

```
ADI CELL | POS/
├── app/
│   ├── Exports/
│   │   ├── ExpensesExport.php
│   │   ├── IncomesExport.php
│   │   ├── MutationsExport.php
│   │   └── ReceivablesExport.php
│   ├── helpers.php                    # rp() + tgl() functions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php  # login/logout + throttle
│   │   │   ├── AccountController.php     # CRUD akun
│   │   │   ├── BackupController.php      # download/restore/reset DB
│   │   │   ├── BillController.php        # CRUD tagihan + bayar
│   │   │   ├── DashboardController.php   # admin + kasir dashboard
│   │   │   ├── ExpenseController.php     # CRUD pengeluaran
│   │   │   ├── IncomeController.php      # CRUD pendapatan
│   │   │   ├── MutationController.php    # CRUD mutasi antar akun
│   │   │   ├── OpeningBalanceController.php  # CRUD modal awal
│   │   │   ├── ProductCategoryController.php # CRUD kategori barang
│   │   │   ├── ProductController.php     # CRUD produk + history
│   │   │   ├── ReceivableController.php  # CRUD piutang + bayar + WA
│   │   │   ├── StockController.php       # POS, stok in, opname, receipt
│   │   │   ├── SummaryController.php     # ringkasan bulanan
│   │   │   ├── UserController.php        # CRUD user + permissions
│   │   │   ├── PrintOrderController.php   # CRUD jasa cetak
│   │   │   └── RepairServiceController.php # CRUD jasa servis
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php       # admin check
│   │   │   └── CheckRole.php             # permission check
│   │   └── Requests/                     # 22 Form Request classes
│   ├── Models/
│   │   ├── Account.php                   # + active() scope
│   │   ├── BillPayment.php
│   │   ├── Expense.php
│   │   ├── Income.php
│   │   ├── Mutation.php
│   │   ├── OpeningBalance.php
│   │   ├── PrintOrder.php               # jasa cetak + auto income
│   │   ├── Product.php                   # + stock_value, is_low_stock
│   │   ├── ProductCategory.php
│   │   ├── Receivable.php                # + remaining, status_badge
│   │   ├── ReceivablePayment.php
│   │   ├── RecurringBill.php
│   │   ├── RepairService.php            # jasa servis + auto income
│   │   ├── StockTransaction.php
│   │   └── User.php                      # + isAdmin(), hasPermission()
│   ├── Providers/
│   │   └── AppServiceProvider.php        # force HTTPS + view composer
│   └── Services/
│       ├── BillService.php
│       ├── DashboardService.php          # equity, profit, chart
│       ├── ExpenseService.php
│       ├── IncomeService.php
│       ├── MutationService.php
│       ├── PrintOrderService.php         # jasa cetak + auto income
│       ├── ReceivableService.php
│       ├── RepairServiceService.php      # jasa servis + auto income
│       └── StockService.php              # stok in/out/opname/receipt
├── bootstrap/
│   └── app.php                           # middleware aliases + guest redirect
├── config/
│   ├── database.php                      # MySQL + PDO::MYSQL_ATTR_SSL_CA
│   └── ...
├── database/
│   └── migrations/                       # ~35 migration files
├── resources/
│   └── views/
│       ├── accounts/index.blade.php
│       ├── auth/login.blade.php          # light mode
│       ├── backups/index.blade.php
│       ├── bills/index.blade.php
│       ├── dashboard/
│       │   ├── index.blade.php           # admin dashboard
│       │   └── kasir.blade.php           # kasir dashboard
│       ├── errors/                       # 403, 404, 419, 500, 503
│       ├── expenses/index.blade.php
│       ├── incomes/index.blade.php
│       ├── layouts/app.blade.php         # sidebar + navbar + CSS ~650 baris
│       ├── mutations/index.blade.php
│       ├── opening-balances/index.blade.php
│       ├── print-orders/index.blade.php
│       ├── product-categories/index.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   └── history.blade.php
│       ├── receivables/index.blade.php
│       ├── repair-services/index.blade.php
│       ├── stock/
│       │   ├── in.blade.php
│       │   ├── opname.blade.php
│       │   ├── receipt.blade.php
│       │   ├── receipt-pdf.blade.php
│       │   ├── report.blade.php
│       │   └── sales.blade.php
│       ├── summary/index.blade.php
│       └── users/
│           ├── index.blade.php
│           └── form.blade.php
├── routes/
│   ├── web.php                           # ~60 routes
│   └── console.php                       # inspire (default)
├── .env.example                          # template production-ready
├── composer.json                         # laravel 13, maatwebsite/excel, dompdf
├── public/
│   ├── index.php
│   └── template/                         # Ninja Admin (sisa, tidak dipakai)
└── docs/                                 # dokumentasi
```

## Arsitektur

### Layer
- **Controller** → tipis, validasi input + delegasi ke service + return view/redirect
- **Service** → business logic, DB transactions, perhitungan
- **Model** → Eloquent ORM, relationships, accessors, scopes, casts
- **View** → Blade template, inline CSS, modal CRUD, filter form
- **Form Request** → validasi input + custom messages (22 classes)

### Middleware Pipeline
```
Request → ValidatePathEncoding → TrustProxies → HandleCors
  → PreventRequestsDuringMaintenance → ValidatePostSize → TrimStrings
  → ConvertEmptyStringsToNull → InvokeDeferredCallbacks
  → StartSession → ShareErrorsFromSession → PreventRequestForgery
  → Authenticate → SubstituteBindings → CheckRole/Admin
  → Controller
```

### CSS Architecture
- Semua inline di `<style>` layout (`app.blade.php`) — ~650 baris
- CSS custom properties untuk theming
- `.dark-mode` class override variabel
- Tidak ada Vite / Tailwind / build step
- Bootstrap 5.3, Font Awesome 6, jQuery 3.7 via CDN

### Key Dependencies (composer.json)
- `laravel/framework: ^13.15`
- `maatwebsite/laravel-excel: ^3.1`
- `barryvdh/laravel-dompdf: ^3.1`
- `laravel/sanctum` (default, tidak dipakai)

### Yang Tidak Ada
- Tidak ada API / Sanctum token
- Tidak ada queue / job
- Tidak ada Vite / Tailwind
- Tidak ada factory / seeder
- Tidak ada test (PHPUnit)
- Tidak ada email notification
