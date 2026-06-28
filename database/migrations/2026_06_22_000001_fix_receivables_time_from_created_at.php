<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('receivables')
            ->whereRaw("TIME(date) = '00:00:00'")
            ->whereNotNull('created_at')
            ->update([
                'date' => DB::raw("CONCAT(DATE(date), ' ', TIME(created_at))"),
            ]);

        DB::table('receivables')
            ->whereRaw("TIME(due_date) = '00:00:00'")
            ->whereNotNull('created_at')
            ->update([
                'due_date' => DB::raw("DATE_ADD(CONCAT(DATE(date), ' ', TIME(created_at)), INTERVAL 3 DAY)"),
            ]);
    }

    public function down(): void
    {
        DB::table('receivables')
            ->update([
                'date' => DB::raw("CONCAT(DATE(date), ' 00:00:00')"),
                'due_date' => DB::raw("CONCAT(DATE(due_date), ' 00:00:00')"),
            ]);
    }
};
