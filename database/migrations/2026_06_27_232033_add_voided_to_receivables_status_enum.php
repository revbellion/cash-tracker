<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE receivables MODIFY COLUMN status ENUM('unpaid', 'paid', 'voided') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE receivables MODIFY COLUMN status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid'");
    }
};
