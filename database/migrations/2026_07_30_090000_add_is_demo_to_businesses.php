<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks a business as a demonstration record.
 *
 * The platform holds 510 imported SIARC artisans and, from now on, one
 * fabricated profile built to match the two profile mockups. Those two kinds of
 * row must never be confusable: everything about the demo artisan — the awards,
 * the reviews, the exhibition history — is invented, and a query that reports on
 * "the artisans" needs a single, cheap way to leave it out.
 *
 * Nullable with a false default so every existing row is, correctly, not a demo,
 * and so the column can be added without rewriting 527 rows' worth of meaning.
 *
 * The second change is unrelated but belongs to the same feature: the social
 * link platform enum has no `whatsapp` member, and the mockup shows WhatsApp as
 * one of the six channels on the profile. Only applied on MySQL — SQLite renders
 * an enum as a CHECK constraint that cannot be altered in place, and the test
 * database never stores a social link at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('businesses', 'is_demo')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->boolean('is_demo')->nullable()->default(false)->after('vendor_type');
                $table->index('is_demo');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE business_social_links MODIFY platform "
                . "ENUM('linkedin','facebook','instagram','twitter','youtube','tiktok','whatsapp') NOT NULL"
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('businesses', 'is_demo')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropIndex(['is_demo']);
                $table->dropColumn('is_demo');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('business_social_links')->where('platform', 'whatsapp')->delete();
            DB::statement(
                "ALTER TABLE business_social_links MODIFY platform "
                . "ENUM('linkedin','facebook','instagram','twitter','youtube','tiktok') NOT NULL"
            );
        }
    }
};
