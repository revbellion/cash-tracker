<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->integer('remaining_qty')->nullable()->after('qty');
        });

        // Backfill: set remaining_qty = qty untuk semua transaksi type='in'
        DB::table('stock_transactions')
            ->where('type', 'in')
            ->update(['remaining_qty' => DB::raw('qty')]);

        // Kurangi remaining_qty untuk batch yang sudah terjual (type='out')
        // Kita hitung manual: untuk setiap product, kurangi dari batch tertua
        $products = DB::table('stock_transactions')
            ->where('type', 'in')
            ->whereNotNull('remaining_qty')
            ->select('product_id')
            ->distinct()
            ->pluck('product_id');

        foreach ($products as $productId) {
            // Ambil semua batch in (ordered by date ASC, id ASC)
            $batches = DB::table('stock_transactions')
                ->where('product_id', $productId)
                ->where('type', 'in')
                ->whereNotNull('remaining_qty')
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            // Ambil semua penjualan (type='out') untuk produk ini, ordered by date ASC
            $sales = DB::table('stock_transactions')
                ->where('product_id', $productId)
                ->where('type', 'out')
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            // FIFO: consume dari batch tertua
            foreach ($sales as $sale) {
                $qtyToConsume = $sale->qty;
                foreach ($batches as &$batch) {
                    if ($qtyToConsume <= 0) break;
                    if ($batch->remaining_qty <= 0) continue;

                    $consumed = min($batch->remaining_qty, $qtyToConsume);
                    $batch->remaining_qty -= $consumed;
                    $qtyToConsume -= $consumed;

                    DB::table('stock_transactions')
                        ->where('id', $batch->id)
                        ->update(['remaining_qty' => $batch->remaining_qty]);
                }
                unset($batch);
            }
        }
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('remaining_qty');
        });
    }
};
