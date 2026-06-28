<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['sales', 'purchase'])->default('sales');
            $table->string('receipt_id', 50)->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('qty');
            $table->integer('price')->default(0);
            $table->integer('total')->default(0);
            $table->text('reason')->nullable();
            $table->date('return_date');
            $table->timestamps();

            $table->index('receipt_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_returns');
    }
};
