<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The events listing showed type / city / region / price on the frontend only —
// promote them to real event attributes.
//
// This migration used to back-fill ten demo events with display values that were
// invented rather than sourced: ticket prices ("2 000 FCFA" for the Foumban
// festival, "5 000 FCFA" for the pottery workshop, "3 000 FCFA" for the
// sustainability conference), "Entrée libre" for the other seven, plus a venue
// city for each. 2026_07_27_160000 deleted those rows as fabricated, so the
// back-fill matched nothing from that point on — but the figures still sat here,
// ready to reappear for anyone who re-ran the migration against a restored
// database. Only the columns remain; every value in them is now entered by an
// administrator.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_type', 30)->default('autres')->after('industry_id');
            $table->string('region_key', 30)->nullable()->after('event_type');
            $table->string('city_fr')->nullable()->after('region_key');
            $table->string('price_fr')->nullable()->after('city_fr');
            $table->string('price_en')->nullable()->after('price_fr');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'region_key', 'city_fr', 'price_fr', 'price_en']);
        });
    }
};
