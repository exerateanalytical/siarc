<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clears the last of the seeded content that spoke for people and institutions.
 *
 * Three migrations were still publishing invented rows on public, nav-linked
 * pages, and the earlier partners/events cleanup missed them:
 *
 *  - artisan_centres: twelve invented centres whose "contact" numbers came from
 *    rand(), so the site listed random Cameroonian mobile numbers — which belong
 *    to real people — as the switchboard for named institutions.
 *  - announcements: eight articles announcing a National Craft Museum and an
 *    International Craft Festival that do not exist, bylined to invented
 *    authors.
 *  - heritage_collections: eight collections advertising hardcoded artisan and
 *    visit counts over an empty product pivot, so each opened to nothing.
 *
 * Their seeding is gone from the source migrations; this removes the rows from
 * databases that already ran them. Schema and CRUD stay — an administrator
 * enters this content for real.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Narrow on purpose: only rows bearing the seeders' own fingerprints go,
        // so anything an administrator has since entered survives.
        if (Schema::hasTable('artisan_centres')) {
            DB::table('artisan_centres')
                ->where('description_fr', 'like', 'Centre d\'artisanat regroupant des artisans de la région%')
                ->delete();
        }

        if (Schema::hasTable('announcements')) {
            // All five invented bylines the seeder used. Listing a subset leaves
            // fabricated articles behind, which is how the first pass at this
            // migration missed two of them.
            DB::table('announcements')
                ->whereIn('author_name', [
                    'Admin Super',
                    'Annie Hadidja',
                    'Bernard Ndongo',
                    'Marie Ngoa',
                    'Michel Kange',
                ])
                ->delete();
        }

        if (Schema::hasTable('heritage_collections')) {
            $ids = DB::table('heritage_collections')->where('artisans_count', '>', 0)->pluck('id');

            if ($ids->isNotEmpty()) {
                if (Schema::hasTable('heritage_collection_product')) {
                    DB::table('heritage_collection_product')->whereIn('collection_id', $ids)->delete();
                }
                DB::table('heritage_collections')->whereIn('id', $ids)->delete();
            }
        }
    }

    public function down(): void
    {
        // Nothing to restore: these rows were fabricated, and recreating them
        // would put invented phone numbers and institutions back on the site.
    }
};
