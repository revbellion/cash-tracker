<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill Expense → StockTransaction (stok masuk)
        DB::statement("
            UPDATE expenses e
            JOIN stock_transactions st ON st.type = 'in'
                AND e.date = st.date
                AND e.amount = st.qty * st.price
                AND e.description LIKE CONCAT('Pembelian ', (
                    SELECT name FROM products WHERE id = st.product_id
                ), '%')
            SET e.stock_transaction_id = st.id
            WHERE e.stock_transaction_id IS NULL
                AND e.category = 'Stok Masuk'
        ");

        // Backfill Income → StockTransaction (penjualan)
        DB::statement("
            UPDATE incomes i
            JOIN stock_transactions st ON st.type = 'out'
                AND i.date = st.date
                AND i.amount = st.qty * st.price
                AND i.description LIKE CONCAT('Penjualan ', (
                    SELECT name FROM products WHERE id = st.product_id
                ), '%')
            SET i.stock_transaction_id = st.id
            WHERE i.stock_transaction_id IS NULL
                AND i.category = 'Penjualan'
        ");
    }

    public function down(): void
    {
        // No rollback needed
    }
};
