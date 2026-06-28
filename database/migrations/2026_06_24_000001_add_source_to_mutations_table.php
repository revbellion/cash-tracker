<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->string('source', 20)->nullable()->after('description');
        });

        DB::table('mutations')->where('description', 'like', 'Opname saldo %')->update(['source' => 'opname']);
        DB::table('mutations')->whereNull('source')->update(['source' => 'manual']);
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
