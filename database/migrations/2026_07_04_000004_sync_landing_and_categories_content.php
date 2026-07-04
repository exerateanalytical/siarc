<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Synchronize the landing/categories frontend with the backend: the illustrated
// category tiles (cropped from the official design) become industry attributes,
// design names win over legacy industry names, and the design vendors that
// actually have cover artwork become the featured businesses.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            $table->string('image_icon')->nullable()->after('icon');
            $table->string('side_icon')->nullable()->after('image_icon');
        });

        // slug => [tile icon, sidebar icon, design name_fr, design name_en, sort]
        $tiles = [
            'arts-decoration'         => ['cat-icon-1.png',  'cat-side-1.png',  'Arts & Décoration',         'Arts & Decoration',         10],
            'textile-mode'            => ['cat-icon-2.png',  'cat-side-2.png',  'Mode & Textile',            'Fashion & Textile',         20],
            'bois-sculpture'          => ['cat-icon-3.png',  'cat-side-3.png',  'Bois & Sculpture',          'Wood & Sculpture',          30],
            'poterie-ceramique'       => ['cat-icon-4.png',  'cat-side-4.png',  'Poterie & Céramique',       'Pottery & Ceramics',        40],
            'bijouterie-accessoires'  => ['cat-icon-5.png',  'cat-side-5.png',  'Bijouterie & Accessoires',  'Jewelry & Accessories',     50],
            'cuir-maroquinerie'       => ['cat-icon-6.png',  'cat-side-6.png',  'Cuir & Maroquinerie',       'Leather & Leather Goods',   60],
            'musique-instruments'     => ['cat-icon-7.png',  'cat-side-7.png',  'Musique & Instruments',     'Music & Instruments',       70],
            'produits-naturels'       => ['cat-icon-8.png',  'cat-side-8.png',  'Produits Naturels',         'Natural Products',          80],
            'agroalimentaire'         => ['cat-icon-9.png',  'cat-side-9.png',  'Agroalimentaire',           'Agri-food',                 90],
            'technologies-innovation' => ['cat-icon-10.png', 'cat-side-10.png', 'Technologies & Innovation', 'Technologies & Innovation', 100],
        ];

        foreach ($tiles as $slug => [$icon, $side, $fr, $en, $sort]) {
            DB::table('industries')->where('slug', $slug)->update([
                'image_icon' => $icon,
                'side_icon'  => $side,
                'name_fr'    => $fr,
                'name_en'    => $en,
                'sort_order' => $sort,
            ]);
        }

        // Feature the vendors that have real cover artwork; unfeature the ones without
        DB::table('businesses')->whereNull('cover_image')->update(['is_featured' => false]);
        DB::table('businesses')
            ->whereNotNull('cover_image')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->update(['is_featured' => true]);
    }

    public function down(): void
    {
        Schema::table('industries', function (Blueprint $table) {
            $table->dropColumn(['image_icon', 'side_icon']);
        });
    }
};
