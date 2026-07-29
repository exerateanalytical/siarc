<?php

namespace App\Modules\Taxonomy\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The one definition of which `industries` rows are allowed to exist.
 *
 * A category survives if, and only if:
 *   (a) at least one row in a table with an industry_id FK references it —
 *       businesses, attribute_templates, certifications, events,
 *       popular_searches_cache, sectors;
 *   (b) it has a non-empty `image_icon` (the curated public browse tiles); or
 *   (c) it is an ancestor (via parent_id, however many levels up) of a row
 *       kept under (a) or (b) — a used leaf can never be left dangling from
 *       a deleted parent.
 *
 * Used by database/migrations/2026_07_30_100000_prune_unused_industries.php
 * and by tests/Feature/IndustryTaxonomyPruneTest.php so the rule has a
 * single, directly testable implementation instead of being duplicated (and
 * able to drift) between the migration and its test.
 */
class IndustryKeepSet
{
    /** Tables with a foreign key column pointing at industries.id (besides parent_id, handled separately). */
    public const REFERENCING_TABLES = [
        'businesses',
        'attribute_templates',
        'certifications',
        'events',
        'popular_searches_cache',
        'sectors',
    ];

    /** @return Collection<int, int> */
    public static function ids(): Collection
    {
        $keep = collect();

        foreach (self::REFERENCING_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $keep = $keep->merge(
                DB::table($table)->whereNotNull('industry_id')->distinct()->pluck('industry_id')
            );
        }

        $keep = $keep->merge(
            DB::table('industries')->whereNotNull('image_icon')->where('image_icon', '<>', '')->pluck('id')
        );

        $keep = $keep->unique()->values();

        // Pull in ancestors, iterating until a pass adds nothing new
        // (bounded by the taxonomy's depth, so it terminates in a handful
        // of passes on any real tree).
        $parentMap = DB::table('industries')->whereNotNull('parent_id')->pluck('parent_id', 'id');

        do {
            $before = $keep->count();
            $parents = $keep->map(fn ($id) => $parentMap->get($id))->filter();
            $keep = $keep->merge($parents)->unique()->values();
        } while ($keep->count() > $before);

        return $keep;
    }
}
