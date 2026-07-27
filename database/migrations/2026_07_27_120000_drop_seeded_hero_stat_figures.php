<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The hero stats band on the marketing home was seeded with three invented
     * marketing figures ("250+", "10 000+", "50 000+"). They were never entered by
     * an admin and have no source, so they are removed here for installs that
     * already ran the seeding migration. A key that is absent renders no tile.
     */
    public function up(): void
    {
        DB::table('platform_settings')
            ->whereIn('key', ['stat_communities', 'stat_artisans', 'stat_products'])
            ->whereIn('value', ['250+', '10 000+', '50 000+'])
            ->delete();
    }

    public function down(): void
    {
        // Deliberately irreversible: restoring the figures would reinstate invented data.
    }
};
