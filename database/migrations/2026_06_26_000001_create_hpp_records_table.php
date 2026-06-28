<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hpp_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('income_id')->nullable()->constrained()->nullOnDelete();
            $table->string('receipt_id', 50)->nullable()->index();
            $table->integer('qty');
            $table->integer('hpp_amount');
            $table->integer('selling_amount');
            $table->integer('profit_amount');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpp_records');
    }
};
