<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// The admin Craft Categories page design (categories du metier.png) shows a
// 2-level category tree (12 top-level categories + 56 sub-categories). The
// industries table was flat (13 top-level rows, no hierarchy). Add a
// self-referential parent_id and seed real sub-categories under several
// existing industries so the tree/level breakdown is genuine, not fabricated.
// Existing businesses/products keep their current industry_id — we do not
// reassign them onto new sub-rows, so LandingSyncTest's category counts stay
// intact; new sub-categories legitimately start at 0 real artisans/products.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            if (! Schema::hasColumn('industries', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('industries')->nullOnDelete();
            }
        });

        $subs = [
            'bois-sculpture' => [
                ['Sculpture Statuaire', 'Statue Sculpture'],
                ['Sculpture de Masques', 'Mask Sculpture'],
                ['Mobilier en Bois Sculpté', 'Carved Wood Furniture'],
            ],
            'poterie-ceramique' => [
                ['Poterie Traditionnelle', 'Traditional Pottery'],
                ['Céramique Moderne', 'Modern Ceramics'],
                ['Poterie Utilitaire', 'Utility Pottery'],
            ],
            'textile-mode' => [
                ['Tissage Traditionnel', 'Traditional Weaving'],
                ['Broderie', 'Embroidery'],
                ['Teinture Naturelle', 'Natural Dyeing'],
            ],
            'bijouterie-accessoires' => [
                ['Bijouterie en Perles', 'Beadwork Jewelry'],
                ['Orfèvrerie', 'Goldsmithing'],
            ],
            'cuir-maroquinerie' => [
                ['Maroquinerie & Sacs', 'Leather Bags'],
                ['Chaussures Artisanales', 'Handmade Footwear'],
            ],
            'arts-decoration' => [
                ['Peinture', 'Painting'],
                ['Décoration Intérieure', 'Interior Decoration'],
            ],
            'musique-instruments' => [
                ['Instruments à Cordes', 'String Instruments'],
                ['Percussions', 'Percussion Instruments'],
            ],
            'artisanat' => [
                ['Vannerie & Rotin', 'Basketry & Rattan'],
                ['Sparterie', 'Esparto Weaving'],
            ],
        ];

        $now = now();
        $sort = 100;
        foreach ($subs as $parentSlug => $children) {
            $parent = DB::table('industries')->where('slug', $parentSlug)->first();
            if (! $parent) continue;
            foreach ($children as $i => [$nameFr, $nameEn]) {
                $slug = $parentSlug . '-' . \Illuminate\Support\Str::slug($nameFr);
                if (DB::table('industries')->where('slug', $slug)->exists()) continue;
                DB::table('industries')->insert([
                    'parent_id' => $parent->id, 'slug' => $slug,
                    'name_fr' => $nameFr, 'name_en' => $nameEn, 'icon' => $parent->icon,
                    'description_fr' => $nameFr . ' — sous-catégorie de ' . $parent->name_fr . '.',
                    'description_en' => $nameEn . ' — sub-category of ' . $parent->name_en . '.',
                    'sort_order' => $sort++, 'is_active' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('industries')->whereNotNull('parent_id')->delete();
        Schema::table('industries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
