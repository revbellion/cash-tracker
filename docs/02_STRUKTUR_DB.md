# STRUKTUR DATABASE

Database: `cash_tracker` (MySQL 8, engine InnoDB, charset utf8mb4)

## Laravel Default Tables
- `users` — tabel user autentikasi (username-based)
- `cache`, `cache_locks` — cache Laravel
- `sessions` — session database driver (fallback)
- `jobs`, `job_batches`, `failed_jobs` — queue (tidak dipakai)
- `password_reset_tokens` — default Laravel (tidak dipakai)

## Tabel: `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | auto increment |
| name | varchar(255) | nama lengkap |
| username | varchar(50) nullable unique | **login field** |
| email | varchar(255) nullable unique | default Laravel, tidak dipakai |
| password | varchar(255) | hashed |
| permissions | json nullable | `null` = admin, `["pos","stock_in"]` = kasir |
| remember_token | varchar(100) nullable | |
| timestamps | | |

## Tabel: `accounts`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(100) | unique |
| type | enum('cash','bank','ewallet','ppob','other') | |
| is_active | boolean | default true (soft-delete: set false) |
| timestamps | | |

## Tabel: `opening_balances`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| account_id | bigint FK → accounts nullable | nullOnDelete |
| period | varchar(7) | YYYY-MM |
| amount | integer | nominal modal awal (rupiah) |
| timestamps | | |
| **UNIQUE** | | (account_id, period) |

## Tabel: `mutations`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | datetime | |
| from_account_id | bigint FK → accounts nullable | nullOnDelete |
| to_account_id | bigint FK → accounts nullable | nullOnDelete |
| amount | integer | |
| description | varchar(255) nullable | |
| timestamps | | |

## Tabel: `incomes`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | datetime | |
| account_id | bigint FK → accounts nullable | |
| category | varchar(100) nullable | dengan datalist dari history |
| amount | integer | |
| description | varchar(255) nullable | |
| stock_transaction_id | bigint FK → stock_transactions nullable | link ke penjualan POS |
| timestamps | | |

## Tabel: `expenses`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| date | datetime | |
| account_id | bigint FK → accounts nullable | |
| category | varchar(100) | dengan datalist dari history |
| amount | integer | |
| description | varchar(255) nullable | |
| stock_transaction_id | bigint FK → stock_transactions nullable | link ke stok masuk |
| timestamps | | |

## Tabel: `receivables`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(150) | nama pelanggan |
| phone | varchar(20) nullable | no HP utk WA |
| amount | integer | nominal total piutang |
| date | datetime | |
| due_date | datetime | auto +3 hari dari date |
| status | enum('unpaid','paid') | default unpaid |
| timestamps | | |

### Accessors
- `remaining` — amount - sum(payments)
- `status_badge` — HTML `<span class="badge">` (Lunas/Belum/Telat)

### Scopes
- `unpaid()` — status = unpaid
- `overdue()` — unpaid + due_date < now

## Tabel: `receivable_payments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| receivable_id | bigint FK → receivables nullable | nullOnDelete |
| account_id | bigint FK → accounts nullable | nullOnDelete |
| amount | integer | |
| date | datetime | |
| timestamps | | |

## Tabel: `recurring_bills`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(255) | nama tagihan |
| category | varchar(255) nullable | |
| account_id | bigint FK → accounts nullable | akun pembayaran default |
| amount | decimal(15,2) | nominal (decimal karena mungkin pecahan) |
| due_day | tinyint(1-31) | tanggal jatuh tempo setiap bulan |
| is_active | boolean | default true |
| timestamps | | |

## Tabel: `bill_payments`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| recurring_bill_id | bigint FK → recurring_bills nullable| nullOnDelete |
| period | varchar(7) | YYYY-MM |
| amount | decimal(15,2) | nominal bayar |
| paid_at | timestamp nullable | waktu bayar |
| expense_id | bigint FK → expenses nullable | link ke pengeluaran |
| timestamps | | |

## Tabel: `product_categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | varchar(100) | unique (not in migration, enforced in controller) |
| timestamps | | |

## Tabel: `products`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| category_id | bigint FK → product_categories nullable | nullOnDelete |
| name | varchar(100) | unique (via controller validation) |
| purchase_price | integer | harga beli (rupiah) |
| selling_price | integer | harga jual (rupiah) |
| stock | integer | stok saat ini |
| stock_min | integer | batas stok minimum |
| unit | varchar(20) | satuan (pcs, box, dll) |
| is_active | boolean | default true (soft-delete) |
| timestamps | | |

### Accessors
- `stock_value` — stock * purchase_price
- `is_low_stock` — stock <= stock_min

## Tabel: `stock_transactions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| product_id | bigint FK → products nullable | nullOnDelete |
| type | enum('in','out','opname') | |
| qty | integer | |
| price | integer | harga satuan |
| account_id | bigint FK → accounts nullable | nullOnDelete |
| description | varchar(255) nullable | |
| date | datetime | |
| income_id | bigint FK → incomes nullable | link ke income (untuk penjualan) |
| receipt_id | varchar(50) nullable indexed | nomor nota (INV-YYYYMMDD-xxxxx) |
| timestamps | | |

### Relasi Penting
- `income_id` — hanya terisi untuk transaksi type `out` (penjualan POS)
- `receipt_id` — grup transaksi dalam satu nota (satu penjualan bisa punya banyak item)

## Relasi Cascade
Semua FK menggunakan `nullOnDelete` (diubah via migration `2026_06_14_121526`):
- Delete account → FK jadi null (data financial tetap ada)
- Delete product → FK jadi null (riwayat transaksi tetap ada)
- Delete category → FK product.category_id jadi null
- Delete receivable → FK receivable_payments.receivable_id jadi null
