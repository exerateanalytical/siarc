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
        DB::statement("ALTER TABLE product_documents MODIFY COLUMN type ENUM(
            'spec_sheet', 'lab_report', 'certificate', 'catalogue',
            'health_certificate', 'phytosanitary_certificate', 'invoice_sample', 'other'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE product_documents MODIFY COLUMN type ENUM(
            'spec_sheet', 'lab_report', 'certificate', 'catalogue', 'other'
        ) NOT NULL");
    }
};
