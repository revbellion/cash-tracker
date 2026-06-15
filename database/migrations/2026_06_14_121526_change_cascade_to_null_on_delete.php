<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop all cascade FK constraints
        Schema::table('mutations', function ($table) {
            $table->dropForeign(['from_account_id']);
            $table->dropForeign(['to_account_id']);
        });
        Schema::table('opening_balances', function ($table) {
            $table->dropForeign(['account_id']);
        });
        Schema::table('expenses', function ($table) {
            $table->dropForeign(['account_id']);
        });
        Schema::table('receivable_payments', function ($table) {
            $table->dropForeign(['receivable_id']);
            $table->dropForeign(['account_id']);
        });
        Schema::table('bill_payments', function ($table) {
            $table->dropForeign(['recurring_bill_id']);
        });
        Schema::table('products', function ($table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('stock_transactions', function ($table) {
            $table->dropForeign(['product_id']);
        });

        // Step 2: Make columns nullable
        Schema::table('mutations', function ($table) {
            $table->unsignedBigInteger('from_account_id')->nullable()->change();
            $table->unsignedBigInteger('to_account_id')->nullable()->change();
        });
        Schema::table('opening_balances', function ($table) {
            $table->unsignedBigInteger('account_id')->nullable()->change();
        });
        Schema::table('expenses', function ($table) {
            $table->unsignedBigInteger('account_id')->nullable()->change();
        });
        Schema::table('receivable_payments', function ($table) {
            $table->unsignedBigInteger('receivable_id')->nullable()->change();
            $table->unsignedBigInteger('account_id')->nullable()->change();
        });
        Schema::table('bill_payments', function ($table) {
            $table->unsignedBigInteger('recurring_bill_id')->nullable()->change();
        });
        Schema::table('products', function ($table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
        Schema::table('stock_transactions', function ($table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        // Step 3: Re-add FK constraints with nullOnDelete
        Schema::table('mutations', function ($table) {
            $table->foreign('from_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('to_account_id')->references('id')->on('accounts')->nullOnDelete();
        });
        Schema::table('opening_balances', function ($table) {
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
        Schema::table('expenses', function ($table) {
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
        Schema::table('receivable_payments', function ($table) {
            $table->foreign('receivable_id')->references('id')->on('receivables')->nullOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
        Schema::table('bill_payments', function ($table) {
            $table->foreign('recurring_bill_id')->references('id')->on('recurring_bills')->nullOnDelete();
        });
        Schema::table('products', function ($table) {
            $table->foreign('category_id')->references('id')->on('product_categories')->nullOnDelete();
        });
        Schema::table('stock_transactions', function ($table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mutations', function ($table) {
            $table->dropForeign(['from_account_id']);
            $table->dropForeign(['to_account_id']);
        });
        Schema::table('opening_balances', function ($table) {
            $table->dropForeign(['account_id']);
        });
        Schema::table('expenses', function ($table) {
            $table->dropForeign(['account_id']);
        });
        Schema::table('receivable_payments', function ($table) {
            $table->dropForeign(['receivable_id']);
            $table->dropForeign(['account_id']);
        });
        Schema::table('bill_payments', function ($table) {
            $table->dropForeign(['recurring_bill_id']);
        });
        Schema::table('products', function ($table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('stock_transactions', function ($table) {
            $table->dropForeign(['product_id']);
        });

        Schema::table('mutations', function ($table) {
            $table->unsignedBigInteger('from_account_id')->nullable(false)->change();
            $table->unsignedBigInteger('to_account_id')->nullable(false)->change();
        });
        Schema::table('opening_balances', function ($table) {
            $table->unsignedBigInteger('account_id')->nullable(false)->change();
        });
        Schema::table('expenses', function ($table) {
            $table->unsignedBigInteger('account_id')->nullable(false)->change();
        });
        Schema::table('receivable_payments', function ($table) {
            $table->unsignedBigInteger('receivable_id')->nullable(false)->change();
            $table->unsignedBigInteger('account_id')->nullable(false)->change();
        });
        Schema::table('bill_payments', function ($table) {
            $table->unsignedBigInteger('recurring_bill_id')->nullable(false)->change();
        });
        Schema::table('products', function ($table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
        Schema::table('stock_transactions', function ($table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
        });

        Schema::table('mutations', function ($table) {
            $table->foreign('from_account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('to_account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
        Schema::table('opening_balances', function ($table) {
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
        Schema::table('expenses', function ($table) {
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
        Schema::table('receivable_payments', function ($table) {
            $table->foreign('receivable_id')->references('id')->on('receivables')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });
        Schema::table('bill_payments', function ($table) {
            $table->foreign('recurring_bill_id')->references('id')->on('recurring_bills')->cascadeOnDelete();
        });
        Schema::table('products', function ($table) {
            $table->foreign('category_id')->references('id')->on('product_categories')->cascadeOnDelete();
        });
        Schema::table('stock_transactions', function ($table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
