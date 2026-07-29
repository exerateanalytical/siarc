<?php

use App\Modules\Taxonomy\Support\IndustryKeepSet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prunes the `industries` taxonomy down to categories the platform actually
 * uses, per the owner's instruction (2026-07-29): "delete those categories
 * and keep only the categories from the 511 artisans and the categories
 * with category Icon."
 *
 * A prior migration (2026_07_28_080624_remove_non_craft_taxonomy) removed a
 * first, narrowly-scoped batch of dead sectors and specific food/fish
 * slugs. This migration finishes the job with a general rule instead of a
 * slug list, because the owner's own example ("fish" categories) is not
 * uniformly dead — some fish leaves are used by real businesses and must
 * survive while their siblings do not.
 *
 * Keep-set (a row survives if ANY of these hold):
 *   (a) at least one row in a table that has an industry_id FK references it
 *       — businesses, attribute_templates, certifications, events,
 *       popular_searches_cache, sectors (checked against every FK found via
 *       information_schema.KEY_COLUMN_USAGE, not just `businesses`);
 *   (b) it is one of the curated `image_icon` tiles used on the public
 *       browse page;
 *   (c) it is an ancestor (via parent_id, recursively) of a row kept under
 *       (a) or (b) — otherwise a used leaf category would dangle from a
 *       deleted parent and break breadcrumb/parent lookups.
 *
 * Verified on a local copy of the production-shaped database on 2026-07-29:
 *   413 total -> 116 kept -> 297 deleted.
 *   Kept by level: 1×3 (all three filières survive), 2×10 (exactly the
 *   image_icon tiles), 3×28, 4×75.
 *   All 6 other FK tables were checked for rows pointing at a row that would
 *   otherwise be deleted: attribute_templates and certifications currently
 *   have industry_id NULL on every row, and events/sectors/
 *   popular_searches_cache are currently empty, so none of them extend the
 *   keep-set today — but the query still checks them live, so if that ever
 *   changes the real reference is honoured instead of silently orphaned.
 *
 * Guarded: the migration recomputes the keep-set at runtime and aborts
 * loudly (throwing, inside a transaction) if the delete-set it is about to
 * remove doesn't match what was verified above, rather than deleting an
 * unexpected number of rows on a database that has since drifted.
 */
return new class extends Migration
{
    /** Expected result of this prune, verified locally on 2026-07-29. */
    private const EXPECTED_TOTAL_BEFORE = 413;
    private const EXPECTED_KEEP_COUNT = 116;
    private const EXPECTED_DELETE_COUNT = 297;

    public function up(): void
    {
        if (! Schema::hasTable('industries')) {
            return;
        }

        DB::transaction(function () {
            $totalBefore = DB::table('industries')->count();

            $keepIds = IndustryKeepSet::ids();

            $deleteIds = DB::table('industries')
                ->whereNotIn('id', $keepIds->all() ?: [0])
                ->pluck('id');

            // The exact-count guard only applies to a database shaped like
            // production (the real SIARC import, ~511 artisans). A fresh
            // install or a test's RefreshDatabase seeds the same official
            // taxonomy (so $totalBefore legitimately is 413 too) but starts
            // with zero real businesses, so far fewer categories are "used"
            // — that is a different, smaller, and equally correct keep-set,
            // not drift to abort on. Below production scale, the deletion
            // is still safe by construction (delete-set is defined as
            // everything NOT in the dynamically computed keep-set, so it
            // can never remove more than that), so it proceeds without the
            // hardcoded-count assertion.
            $businessesWithIndustry = Schema::hasTable('businesses')
                ? DB::table('businesses')->whereNotNull('industry_id')->count()
                : 0;
            $looksLikeProduction = $businessesWithIndustry >= 400;

            if ($looksLikeProduction && ($totalBefore !== self::EXPECTED_TOTAL_BEFORE
                || $keepIds->count() !== self::EXPECTED_KEEP_COUNT
                || $deleteIds->count() !== self::EXPECTED_DELETE_COUNT)) {
                throw new \RuntimeException(sprintf(
                    'prune_unused_industries: computed counts do not match the verified baseline '.
                    '(total before %d vs expected %d, keep %d vs expected %d, delete %d vs expected %d). '.
                    'The taxonomy has drifted since this migration was written — aborting rather than '.
                    'deleting an unverified set. Recompute the keep-set and update the EXPECTED_* '.
                    'constants deliberately before re-running.',
                    $totalBefore, self::EXPECTED_TOTAL_BEFORE,
                    $keepIds->count(), self::EXPECTED_KEEP_COUNT,
                    $deleteIds->count(), self::EXPECTED_DELETE_COUNT
                ));
            }

            if ($deleteIds->isEmpty()) {
                return;
            }

            // Deepest level first: no FK requires this (parent_id, and every
            // referencing table, is nullOnDelete except sectors which is
            // cascadeOnDelete but currently empty) — ordered anyway so the
            // operation reads cleanly and never depends on FK behaviour.
            // Group manually (rather than Collection::groupBy(), which
            // discards the original id keys unless $preserveKeys is passed)
            // so each level's real industry ids are what gets deleted.
            $idsByLevel = DB::table('industries')
                ->whereIn('id', $deleteIds)
                ->pluck('level', 'id');

            $levels = $idsByLevel->unique()->sortDesc()->values();

            foreach ($levels as $level) {
                $idsAtLevel = $idsByLevel->filter(fn ($l) => $l === $level)->keys();
                DB::table('industries')->whereIn('id', $idsAtLevel)->delete();
            }
        });
    }

    public function down(): void
    {
        // Not reversible: these rows held no business, no icon tile, and no
        // real reference from any table in IndustryKeepSet::REFERENCING_TABLES.
        // Recreating them would put dead taxonomy back in the directory.
        // Re-seed deliberately if a branch is genuinely wanted again.
    }
};
