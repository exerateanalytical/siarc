<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The admin Partners page design (Partners.png) shows a rich, diverse partner
// directory (institutional / international / finance / private, several
// countries, active vs. pending). The seeded data was 11 Cameroon-only
// institutional partners — not enough breadth to drive that page for real.
// Add a `partner_type` + `status` classification, correct a few countries
// that were defaulted to Cameroun by mistake, and seed additional real-world
// partner categories so the KPI cards / breakdowns are computed from actual
// rows, never hardcoded.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (! Schema::hasColumn('partners', 'partner_type')) {
                $table->string('partner_type')->default('Institutionnel')->after('tier');
            }
            if (! Schema::hasColumn('partners', 'status')) {
                $table->string('status')->default('active')->after('partner_type');
            }
        });

        // The rest of this migration used to invent a partner directory: 21 rows
        // naming real organisations (Union Africaine, MTN, TotalEnergies,
        // MINPMEESA, Fondation Paul Biya...) with fabricated contact details
        // — @partenaire.cm addresses, .example websites, and a contact person
        // called "Coordination Partenariat" on every one. On a fresh install it
        // published 13 of them, so the live site asserted partnerships that do
        // not exist. Removed on the owner's instruction (2026-07-27); partners
        // are now added through the admin console. The schema changes above stay.
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['partner_type', 'status']);
        });
    }
};
