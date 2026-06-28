<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hpp_records', function (Blueprint $table) {
            $table->json('fifo_batches')->nullable()->after('hpp_amount');
        });
    }

    public function down(): void
    {
        Schema::table('hpp_records', function (Blueprint $table) {
            $table->dropColumn('fifo_batches');
        });
    }
};
