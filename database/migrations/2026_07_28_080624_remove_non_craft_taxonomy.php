<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leaves only craft categories in the taxonomy.
 *
 * The platform is a handicraft marketplace, but the category tree had collected
 * two kinds of thing that are not crafts:
 *
 *  - Food and fish branches whose product categories read as a produce market —
 *    Tilapia, Carpe, Silure, Crevettes, Maïs, Banane Plantain. An artisan
 *    listing a carved mask had to scroll past them.
 *  - Generic business sectors on older databases — Banque & Finance,
 *    Télécommunications, Transport & Logistique and eight more — which held no
 *    trades, no shops and no product categories, yet appeared in the directory's
 *    browse menu as filters that could never return a result.
 *
 * Both are removed here on the owner's instruction (2026-07-28). The seeder no
 * longer creates the food branches, so this only clears databases that already
 * ran it.
 *
 * Narrow by design: a row is deleted only when nothing points at it, so an
 * artisan's shop or product can never lose its category.
 */
return new class extends Migration
{
    /** Industry slugs whose whole subtree is produce rather than craft. */
    private const NON_CRAFT_SLUGS = ['aquaculture', 'agriculture', 'agroalimentaire'];

    /**
     * Food and fish product categories, by slug.
     *
     * Listed explicitly rather than matched on the branch above, because on
     * databases where the `sectors` bridge was never populated these rows have
     * a null sector_id and no branch to follow. Naming them is honest about
     * which rows are being removed, and the guard below still refuses to delete
     * any that a real product uses.
     */
    private const NON_CRAFT_CATEGORY_SLUGS = [
        'cacao', 'cafe', 'carpe', 'conserves-poisson', 'crevettes',
        'huiles-vegetales', 'mais', 'manioc-derives', 'piments', 'plantain',
        'poisson-fume', 'poissons-marins', 'poivre-penja', 'silure-poisson-chat',
        'tilapia',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('industries')) {
            return;
        }

        // ── 1. Food and fish branches, with their sectors and categories ──
        $ids = DB::table('industries')->whereIn('slug', self::NON_CRAFT_SLUGS)->pluck('id');

        if ($ids->isNotEmpty()) {
            $sectorIds = Schema::hasTable('sectors')
                ? DB::table('sectors')->whereIn('industry_id', $ids)->pluck('id')
                : collect();

            if ($sectorIds->isNotEmpty() && Schema::hasTable('product_categories')) {
                // Never orphan a real listing: if a product uses one of these,
                // leave the whole branch alone and let a human decide.
                $inUse = DB::table('products')
                    ->whereIn('category_id', function ($q) use ($sectorIds) {
                        $q->select('id')->from('product_categories')->whereIn('sector_id', $sectorIds);
                    })
                    ->exists();

                if (! $inUse) {
                    DB::table('product_categories')->whereIn('sector_id', $sectorIds)->delete();
                    DB::table('sectors')->whereIn('id', $sectorIds)->delete();
                    DB::table('industries')->whereIn('id', $ids)
                        ->whereNotIn('id', function ($q) {
                            $q->select('industry_id')->from('businesses')->whereNotNull('industry_id');
                        })
                        ->delete();
                }
            }
        }

        // ── 1b. The same categories where the sector bridge was never filled ──
        if (Schema::hasTable('product_categories')) {
            $orphans = DB::table('product_categories')
                ->whereIn('slug', self::NON_CRAFT_CATEGORY_SLUGS)
                ->whereNotIn('id', function ($q) {
                    $q->select('category_id')->from('products')->whereNotNull('category_id');
                })
                ->pluck('id');

            if ($orphans->isNotEmpty()) {
                DB::table('product_categories')->whereIn('id', $orphans)->delete();
            }
        }

        // ── 2. Generic business sectors that were never craft ──
        // Identified by shape rather than by name: a top-level row with no
        // children, no shops, no sectors and no product categories has nothing
        // to offer a visitor who clicks it.
        // The parent ids are pulled out first rather than sub-queried: MySQL
        // refuses to read `industries` inside a DELETE against `industries`
        // (error 1093), and silently doing nothing would be worse than failing.
        $hasChildren = DB::table('industries')->whereNotNull('parent_id')
            ->distinct()->pluck('parent_id')->filter()->all();

        $empty = DB::table('industries')->where('level', 1)
            ->when($hasChildren, fn ($q) => $q->whereNotIn('id', $hasChildren))
            ->whereNotIn('id', function ($q) {
                $q->select('industry_id')->from('businesses')->whereNotNull('industry_id');
            });

        if (Schema::hasTable('sectors')) {
            $empty->whereNotIn('id', function ($q) {
                $q->select('industry_id')->from('sectors')->whereNotNull('industry_id');
            });
        }
        if (Schema::hasTable('events')) {
            $empty->whereNotIn('id', function ($q) {
                $q->select('industry_id')->from('events')->whereNotNull('industry_id');
            });
        }

        $empty->delete();
    }

    public function down(): void
    {
        // Nothing to restore: these rows held no trades, no shops and no
        // products, and recreating them would put dead filters back in the
        // directory. Re-seed if a branch is genuinely wanted again.
    }
};
