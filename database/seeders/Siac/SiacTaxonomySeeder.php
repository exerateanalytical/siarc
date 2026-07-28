<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiacTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        // Handicraft only. The aquaculture, agriculture and agroalimentaire
        // branches were removed on the owner's instruction (2026-07-28): they
        // seeded product categories like Tilapia, Carpe, Crevettes, Maïs and
        // Banane Plantain, which are farm and fish produce rather than
        // something an artisan makes.
        $industries = [
            [
                'slug' => 'artisanat', 'icon' => 'hand', 'sort_order' => 1,
                'name_fr' => 'Artisanat & Arts Traditionnels',
                'name_en' => 'Crafts & Traditional Arts',
                'description_fr' => 'Vannerie, tissage, sculpture, poterie, maroquinerie et arts traditionnels camerounais.',
                'description_en' => 'Basketry, weaving, sculpture, pottery, leatherwork and Cameroonian traditional arts.',
                'sectors' => [
                    ['slug' => 'vannerie-tissage', 'name_fr' => 'Vannerie & Tissage', 'name_en' => 'Basketry & Weaving', 'categories' => [
                        ['slug' => 'paniers', 'name_fr' => 'Paniers & Corbeilles', 'name_en' => 'Baskets & Bowls'],
                        ['slug' => 'nattes', 'name_fr' => 'Nattes & Tapis', 'name_en' => 'Mats & Rugs'],
                        ['slug' => 'tissus-kaba', 'name_fr' => 'Tissus Traditionnels & Kaba', 'name_en' => 'Traditional Fabrics & Kaba'],
                    ]],
                    ['slug' => 'sculpture-bois', 'name_fr' => 'Sculpture & Travail du Bois', 'name_en' => 'Sculpture & Woodwork', 'categories' => [
                        ['slug' => 'masques', 'name_fr' => 'Masques Traditionnels', 'name_en' => 'Traditional Masks'],
                        ['slug' => 'statuettes', 'name_fr' => 'Statuettes & Figurines', 'name_en' => 'Statuettes & Figurines'],
                        ['slug' => 'mobilier-bois', 'name_fr' => 'Mobilier en Bois', 'name_en' => 'Wooden Furniture'],
                    ]],
                    ['slug' => 'poterie-ceramique', 'name_fr' => 'Poterie & Céramique', 'name_en' => 'Pottery & Ceramics', 'categories' => [
                        ['slug' => 'recipients-terre-cuite', 'name_fr' => 'Récipients en Terre Cuite', 'name_en' => 'Terracotta Vessels'],
                        ['slug' => 'ceramique-decorative', 'name_fr' => 'Céramique Décorative', 'name_en' => 'Decorative Ceramics'],
                    ]],
                    ['slug' => 'maroquinerie', 'name_fr' => 'Maroquinerie & Cuir', 'name_en' => 'Leatherwork', 'categories' => [
                        ['slug' => 'sacs-cuir', 'name_fr' => 'Sacs & Maroquinerie', 'name_en' => 'Bags & Leather Goods'],
                        ['slug' => 'chaussures-artisanales', 'name_fr' => 'Chaussures Artisanales', 'name_en' => 'Handcrafted Shoes'],
                    ]],
                    ['slug' => 'bijouterie', 'name_fr' => 'Bijouterie & Parure', 'name_en' => 'Jewelry & Adornments', 'categories' => [
                        ['slug' => 'bijoux-bronze', 'name_fr' => 'Bijoux en Bronze & Laiton', 'name_en' => 'Bronze & Brass Jewelry'],
                        ['slug' => 'perles-colliers', 'name_fr' => 'Perles & Colliers', 'name_en' => 'Beads & Necklaces'],
                    ]],
                ],
            ],
            [
                'slug' => 'textile-mode', 'icon' => 'shirt', 'sort_order' => 4,
                'name_fr' => 'Textile & Mode Africaine',
                'name_en' => 'Textile & African Fashion',
                'description_fr' => 'Couture, prêt-à-porter, broderie et mode africaine contemporaine.',
                'description_en' => 'Tailoring, ready-to-wear, embroidery and contemporary African fashion.',
                'sectors' => [
                    ['slug' => 'couture-pap', 'name_fr' => 'Couture & Prêt-à-Porter', 'name_en' => 'Tailoring & Ready-to-Wear', 'categories' => [
                        ['slug' => 'vetements-femme', 'name_fr' => 'Vêtements Femme', 'name_en' => "Women's Clothing"],
                        ['slug' => 'vetements-homme', 'name_fr' => 'Vêtements Homme', 'name_en' => "Men's Clothing"],
                        ['slug' => 'accessoires-mode', 'name_fr' => 'Accessoires Mode', 'name_en' => 'Fashion Accessories'],
                    ]],
                ],
            ],
        ];

        // Idempotent: the official-craft-taxonomy migration already seeds part of
        // this tree (the `artisanat` / `aquaculture` industries and the artisanat
        // sectors + categories), so plain inserts here broke `migrate && db:seed`
        // on a fresh install. Existing rows are reused as-is — the migration owns
        // their content — and only the missing ones are added.
        $newIndustries = 0;
        $newSectors    = 0;
        $newCategories = 0;

        foreach ($industries as $sort => $ind) {
            $industryId = DB::table('industries')->where('slug', $ind['slug'])->value('id');

            if (! $industryId) {
                $industryId = DB::table('industries')->insertGetId([
                    'slug'           => $ind['slug'],
                    'name_fr'        => $ind['name_fr'],
                    'name_en'        => $ind['name_en'],
                    'description_fr' => $ind['description_fr'],
                    'description_en' => $ind['description_en'],
                    'icon'           => $ind['icon'],
                    'sort_order'     => $ind['sort_order'],
                    'is_active'      => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $newIndustries++;
            }

            foreach ($ind['sectors'] as $sSort => $sec) {
                $sectorId = DB::table('sectors')->where('slug', $sec['slug'])->value('id');

                if (! $sectorId) {
                    $sectorId = DB::table('sectors')->insertGetId([
                        'industry_id' => $industryId,
                        'slug'        => $sec['slug'],
                        'name_fr'     => $sec['name_fr'],
                        'name_en'     => $sec['name_en'],
                        'sort_order'  => $sSort + 1,
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $newSectors++;
                }

                foreach ($sec['categories'] as $cSort => $cat) {
                    if (DB::table('product_categories')->where('slug', $cat['slug'])->exists()) {
                        continue;
                    }

                    DB::table('product_categories')->insert([
                        'sector_id'   => $sectorId,
                        'parent_id'   => null,
                        'slug'        => $cat['slug'],
                        'name_fr'     => $cat['name_fr'],
                        'name_en'     => $cat['name_en'],
                        'sort_order'  => $cSort + 1,
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                    $newCategories++;
                }
            }
        }

        // Attribute templates for aquaculture
        $aquacultureId = DB::table('industries')->where('slug', 'aquaculture')->value('id');
        $artisanatId   = DB::table('industries')->where('slug', 'artisanat')->value('id');

        $templates = [
            ['industry_id' => $aquacultureId, 'field_key' => 'poids', 'name_fr' => 'Poids', 'name_en' => 'Weight', 'field_type' => 'number', 'unit' => 'kg', 'sort_order' => 1],
            ['industry_id' => $aquacultureId, 'field_key' => 'taille_moyenne', 'name_fr' => 'Taille moyenne', 'name_en' => 'Average size', 'field_type' => 'text', 'unit' => 'cm', 'sort_order' => 2],
            ['industry_id' => $aquacultureId, 'field_key' => 'methode_elevage', 'name_fr' => 'Méthode d\'élevage', 'name_en' => 'Farming method', 'field_type' => 'select', 'unit' => null, 'sort_order' => 3,
                'options_fr' => ['Cage flottante', 'Bassin en terre', 'Aquaponie', 'Eau courante'],
                'options_en' => ['Floating cage', 'Earthen pond', 'Aquaponics', 'Running water'],
            ],
            ['industry_id' => $artisanatId, 'field_key' => 'materiau', 'name_fr' => 'Matériau', 'name_en' => 'Material', 'field_type' => 'text', 'unit' => null, 'sort_order' => 1],
            ['industry_id' => $artisanatId, 'field_key' => 'dimensions', 'name_fr' => 'Dimensions', 'name_en' => 'Dimensions', 'field_type' => 'text', 'unit' => 'cm', 'sort_order' => 2],
            ['industry_id' => $artisanatId, 'field_key' => 'origine_ethnique', 'name_fr' => 'Origine ethnique', 'name_en' => 'Ethnic origin', 'field_type' => 'text', 'unit' => null, 'sort_order' => 3],
        ];

        // attribute_templates has no unique index, so re-runnability is keyed on
        // the (industry, field_key) pair the rest of the app looks templates up by.
        $newTemplates = 0;

        foreach ($templates as $t) {
            $exists = DB::table('attribute_templates')
                ->where('industry_id', $t['industry_id'])
                ->where('field_key', $t['field_key'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('attribute_templates')->insert(array_merge($t, [
                'options_fr' => isset($t['options_fr']) ? json_encode($t['options_fr']) : null,
                'options_en' => isset($t['options_en']) ? json_encode($t['options_en']) : null,
                'is_required' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]));
            $newTemplates++;
        }

        $this->command->info("  {$newIndustries} industries, {$newSectors} sectors, {$newCategories} categories, {$newTemplates} attribute templates seeded.");
    }
}
