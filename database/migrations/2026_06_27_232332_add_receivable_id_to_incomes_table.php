<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('receivable_id')->nullable()->after('stock_transaction_id')->constrained('receivables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['receivable_id']);
            $table->dropColumn('receivable_id');
        });
    }
};
