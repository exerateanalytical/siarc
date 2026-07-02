<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * MODIFY COLUMN ... ENUM is MySQL-only; on other drivers (SQLite in
     * tests) relax the column to a plain string, which also drops the
     * CHECK constraint SQLite derives from enum().
     */
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE business_certifications MODIFY COLUMN status ENUM('pending', 'verified', 'expired', 'rejected') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('business_certifications', fn (Blueprint $table) => $table->string('status', 20)->default('pending')->change());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE business_certifications MODIFY COLUMN status ENUM('pending', 'verified', 'expired') NOT NULL DEFAULT 'pending'");
        }
    }
};
