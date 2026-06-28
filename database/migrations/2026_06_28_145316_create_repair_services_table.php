<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_services', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20)->nullable();
            $table->string('device_type', 20); // hp, laptop
            $table->string('device_model', 100)->nullable();
            $table->text('issue_description')->nullable();
            $table->integer('service_fee');
            $table->integer('sparepart_cost')->default(0);
            $table->string('sparepart_description', 255)->nullable();
            $table->integer('total'); // service_fee + sparepart_cost
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('income_id')->nullable()->constrained('incomes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_services');
    }
};
