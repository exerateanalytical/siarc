<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// "Collections Héritage" admin page (design: "collection heritage admin panel.png").
// The platform had no heritage-collections concept: this migration creates the table,
// a pivot to real products, and seeds the 8 collections shown in the design verbatim.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heritage_collections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_fr');
            $table->string('name_en')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('status', 20)->default('published'); // published | in_review | draft
            $table->integer('sort_order')->default(0);
            // Display attributes shown on the admin design (region/category/visibility/traffic)
            $table->string('region_fr', 40)->nullable();
            $table->string('region_en', 40)->nullable();
            $table->string('city', 60)->nullable();
            $table->string('category_fr', 60)->nullable();
            $table->string('category_en', 60)->nullable();
            $table->string('visibility', 20)->default('public'); // public | private
            $table->unsignedInteger('artisans_count')->default(0);
            $table->unsignedInteger('visits_count')->default(0);
            $table->timestamps();
        });

        Schema::create('heritage_collection_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('heritage_collections')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['collection_id', 'product_id']);
        });

        // ------------------------------------------------------------------
        // Seed: the 8 collections of the design (names/descriptions verbatim).
        // [slug, name_fr, name_en, desc_fr, desc_en, region_fr, region_en, city,
        //  cat_fr, cat_en, status, visibility, artisans, visits, created]
        // ------------------------------------------------------------------
        $rows = [
            ['bronzes-royaux-bamoun', 'Bronzes Royaux Bamoun', 'Royal Bamoun Bronzes',
                'Art royal et cérémoniel', 'Royal and ceremonial art',
                'Ouest', 'West', 'Foumban', 'Sculpture & Bronze', 'Sculpture & Bronze',
                'published', 'public', 12, 45280, '2025-06-02'],
            ['tissus-traditionnels-bamileke', 'Tissus Traditionnels Bamileke', 'Traditional Bamileke Fabrics',
                'Tissus tissés à la main', 'Hand-woven fabrics',
                'Ouest', 'West', 'Bafoussam', 'Textiles & Tissus', 'Textiles & Fabrics',
                'published', 'public', 18, 38560, '2025-05-30'],
            ['poteries-de-ladamaoua', 'Poteries de l\'Adamaoua', 'Adamawa Pottery',
                'Poteries décoratives et utilitaires', 'Decorative and utilitarian pottery',
                'Adamaoua', 'Adamawa', 'Ngaoundéré', 'Poterie & Céramique', 'Pottery & Ceramics',
                'published', 'public', 8, 22145, '2025-05-28'],
            ['masques-traditionnels-bassa', 'Masques Traditionnels Bassa', 'Traditional Bassa Masks',
                'Masques cérémoniels', 'Ceremonial masks',
                'Littoral', 'Littoral', 'Douala', 'Sculpture & Bois', 'Sculpture & Wood',
                'in_review', 'private', 14, 12450, '2025-05-25'],
            ['vannerie-du-nord', 'Vannerie du Nord', 'Northern Basketry',
                'Paniers et objets en fibres naturelles', 'Baskets and natural-fibre objects',
                'Nord', 'North', 'Garoua', 'Vannerie & Rotin', 'Basketry & Rattan',
                'published', 'public', 9, 18320, '2025-05-20'],
            ['bijoux-traditionnels-grassfields', 'Bijoux Traditionnels Grassfields', 'Traditional Grassfields Jewellery',
                'Parures et bijoux traditionnels', 'Traditional ornaments and jewellery',
                'Ouest', 'West', 'Bamendjou', 'Bijouterie & Métal', 'Jewellery & Metal',
                'draft', 'private', 11, 2156, '2025-05-18'],
            ['sculptures-sur-pierre-de-lest', 'Sculptures sur Pierre de l\'Est', 'Eastern Stone Carvings',
                'Sculptures et gravures sur pierre', 'Stone sculptures and engravings',
                'Est', 'East', 'Bertoua', 'Sculpture & Pierre', 'Sculpture & Stone',
                'published', 'public', 7, 9875, '2025-05-15'],
            ['cuirs-et-peaux-du-sud', 'Cuirs et Peaux du Sud', 'Southern Leathers and Hides',
                'Articles en cuir et peaux', 'Leather goods and hides',
                'Sud', 'South', 'Ebolowa', 'Cuir & Peaux', 'Leather & Hides',
                'in_review', 'private', 10, 7230, '2025-05-12'],
        ];

        foreach ($rows as $i => $r) {
            DB::table('heritage_collections')->insert([
                'slug'           => $r[0],
                'name_fr'        => $r[1],
                'name_en'        => $r[2],
                'description_fr' => $r[3],
                'description_en' => $r[4],
                'cover_image'    => null, // view falls back to the cropped design artwork
                'status'         => $r[10],
                'sort_order'     => $i,
                'region_fr'      => $r[5],
                'region_en'      => $r[6],
                'city'           => $r[7],
                'category_fr'    => $r[8],
                'category_en'    => $r[9],
                'visibility'     => $r[11],
                'artisans_count' => $r[12],
                'visits_count'   => $r[13],
                'created_at'     => $r[14] . ' 09:00:00',
                'updated_at'     => $r[14] . ' 09:00:00',
            ]);
        }

        // ------------------------------------------------------------------
        // Attach real published products to each collection by name keywords,
        // so the admin page can display REAL product counts via the pivot.
        // Collections with no matching product simply keep a count of 0.
        // ------------------------------------------------------------------
        $keywords = [
            'bronzes-royaux-bamoun'            => ['bronze', 'bamoun'],
            'tissus-traditionnels-bamileke'    => ['tissu', 'ndop', 'wax'],
            'poteries-de-ladamaoua'            => ['poterie', 'canari', 'vase', 'jarre', 'terre cuite', 'pot '],
            'masques-traditionnels-bassa'      => ['masque', 'statue', 'sculpture en bois'],
            'vannerie-du-nord'                 => ['panier', 'natte', 'raffia'],
            'bijoux-traditionnels-grassfields' => ['collier', 'bracelet', 'perle'],
            'sculptures-sur-pierre-de-lest'    => ['sur pierre', 'gravure'],
            'cuirs-et-peaux-du-sud'            => ['cuir', 'babouche'],
        ];

        if (! Schema::hasTable('products')) {
            return;
        }

        $now = now();
        foreach ($keywords as $slug => $terms) {
            $collectionId = DB::table('heritage_collections')->where('slug', $slug)->value('id');
            if (! $collectionId) {
                continue;
            }

            $productIds = DB::table('products')
                ->whereNull('deleted_at')
                ->where('status', 'published')
                ->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhereRaw('LOWER(name_fr) LIKE ?', ['%' . mb_strtolower($term) . '%']);
                    }
                })
                ->pluck('id')->unique();

            foreach ($productIds as $productId) {
                DB::table('heritage_collection_product')->insert([
                    'collection_id' => $collectionId,
                    'product_id'    => $productId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('heritage_collection_product');
        Schema::dropIfExists('heritage_collections');
    }
};
