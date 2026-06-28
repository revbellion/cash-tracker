<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_orders', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('service_type', 20); // cetak_foto, fotokopi, print
            $table->integer('quantity');
            $table->integer('price_per_unit');
            $table->integer('total'); // quantity * price_per_unit
            $table->string('description', 255)->nullable();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('income_id')->nullable()->constrained('incomes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_orders');
    }
};
