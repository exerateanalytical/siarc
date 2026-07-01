<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE business_certifications MODIFY COLUMN status ENUM('pending', 'verified', 'expired', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE business_certifications MODIFY COLUMN status ENUM('pending', 'verified', 'expired') NOT NULL DEFAULT 'pending'");
    }
};
