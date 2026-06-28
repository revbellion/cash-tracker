<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->datetime('expired_at')->nullable()->after('date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->datetime('expired_at')->nullable()->after('is_active');
        });
    }
};
