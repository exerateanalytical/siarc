<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a shop record carry the SIARC 2026 identity it was imported from.
 *
 * The competition dataset holds 510 real artisans, and the platform seeds them
 * as unpublished profiles the real artisan later claims. Three things are
 * needed for that:
 *
 *  - siarc_code   the competition reference (AD-1, AD-2 …). Unique, so a
 *                 re-run of the importer updates rather than duplicates, and it
 *                 is the key the claim flow matches on.
 *  - claimed_at   null until the artisan signs up and takes ownership, which is
 *                 what separates "we typed this in for you" from "this person
 *                 has an account".
 *  - source_metier the artisan's own wording for their trade, kept verbatim.
 *                 About a fifth of the dataset's métiers do not exist as leaves
 *                 in the official taxonomy, so those profiles are filed under a
 *                 coarser node — without this column, what they actually said
 *                 they do would be lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'siarc_code')) {
                $table->string('siarc_code', 20)->nullable()->unique()->after('uuid');
            }
            if (! Schema::hasColumn('businesses', 'claimed_at')) {
                $table->timestamp('claimed_at')->nullable()->after('siarc_code');
            }
            if (! Schema::hasColumn('businesses', 'source_metier')) {
                $table->string('source_metier')->nullable()->after('claimed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (Schema::hasColumn('businesses', 'siarc_code')) {
                $table->dropUnique(['siarc_code']);
            }
            $columns = array_values(array_filter(
                ['siarc_code', 'claimed_at', 'source_metier'],
                fn ($c) => Schema::hasColumn('businesses', $c)
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
