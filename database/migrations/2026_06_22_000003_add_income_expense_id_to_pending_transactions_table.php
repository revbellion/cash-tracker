<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('income_id')->nullable()->after('completed_account_id');
            $table->unsignedBigInteger('expense_id')->nullable()->after('income_id');
        });
    }

    public function down(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->dropColumn(['income_id', 'expense_id']);
        });
    }
};
