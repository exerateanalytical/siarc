<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Partner detail pages (admin + public) need rich partnership data. Enrich the
// partners table and seed real values for the known institutional partners.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            foreach ([
                'contact_email' => 'string', 'contact_phone' => 'string', 'address' => 'string',
                'sector_fr' => 'string', 'country' => 'string', 'partner_ref' => 'string',
                'responsible_name' => 'string', 'responsible_title' => 'string', 'responsible_email' => 'string',
                'partnership_type' => 'string', 'partnership_level' => 'string',
                'start_date' => 'date', 'end_date' => 'date',
            ] as $col => $type) {
                if (! Schema::hasColumn('partners', $col)) {
                    $type === 'date' ? $table->date($col)->nullable() : $table->string($col)->nullable();
                }
            }
            if (! Schema::hasColumn('partners', 'auto_renew'))    $table->boolean('auto_renew')->default(true);
            if (! Schema::hasColumn('partners', 'legal_verified')) $table->boolean('legal_verified')->default(true);
            if (! Schema::hasColumn('partners', 'reliability'))    $table->decimal('reliability', 3, 1)->default(4.5);
            if (! Schema::hasColumn('partners', 'since_year'))     $table->unsignedSmallInteger('since_year')->nullable();
        });

        // This migration used to fill every partner's contact block with
        // invented values: an @partenaire.cm address and a rand()-generated
        // phone number for anyone not in a hardcoded lookup, plus named
        // individuals at real bodies (a "Marie Dupont" with an @unesco.org
        // address). None of it was real, and it presented as a verified
        // partnership record. Removed on the owner's instruction (2026-07-27) —
        // the columns added above are filled by an admin or left null.
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'address', 'sector_fr', 'country', 'partner_ref', 'responsible_name', 'responsible_title', 'responsible_email', 'partnership_type', 'partnership_level', 'start_date', 'end_date', 'auto_renew', 'legal_verified', 'reliability', 'since_year']);
        });
    }
};
