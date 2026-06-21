<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 20); // edc, qris, transfer, other
            $table->string('description', 255);
            $table->integer('amount');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->dateTime('pending_date');
            $table->dateTime('completed_date')->nullable();
            $table->enum('completed_type', ['masuk', 'keluar'])->nullable(); // uang masuk atau cash keluar
            $table->foreignId('completed_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_transactions');
    }
};
