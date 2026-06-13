<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('income_id')
                ->nullable()
                ->constrained('incomes')
                ->nullOnDelete();
            $table->string('receipt_id', 50)
                ->nullable()
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['income_id']);
            $table->dropColumn(['income_id', 'receipt_id']);
        });
    }
};
