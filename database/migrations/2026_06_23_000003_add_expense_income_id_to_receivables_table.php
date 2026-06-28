<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('account_id');
            $table->unsignedBigInteger('income_id')->nullable()->after('expense_id');
        });
    }

    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dropColumn(['expense_id', 'income_id']);
        });
    }
};
