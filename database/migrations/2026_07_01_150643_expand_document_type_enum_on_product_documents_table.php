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
            DB::statement("ALTER TABLE product_documents MODIFY COLUMN type ENUM(
                'spec_sheet', 'lab_report', 'certificate', 'catalogue',
                'health_certificate', 'phytosanitary_certificate', 'invoice_sample', 'other'
            ) NOT NULL");
        } else {
            Schema::table('product_documents', fn (Blueprint $table) => $table->string('type', 40)->change());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE product_documents MODIFY COLUMN type ENUM(
                'spec_sheet', 'lab_report', 'certificate', 'catalogue', 'other'
            ) NOT NULL");
        }
    }
};
