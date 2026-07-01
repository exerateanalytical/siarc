<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiacTaxonomySeeder extends Seeder
{
    public function run(): void
    {
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
                'slug' => 'aquaculture', 'icon' => 'fish', 'sort_order' => 2,
                'name_fr' => 'Aquaculture & Pêche',
                'name_en' => 'Aquaculture & Fishing',
                'description_fr' => 'Élevage de poissons, crevettes, huîtres et produits halieutiques du Cameroun.',
                'description_en' => 'Fish farming, shrimp, oysters and fishery products from Cameroon.',
                'sectors' => [
                    ['slug' => 'pisciculture', 'name_fr' => 'Pisciculture', 'name_en' => 'Fish Farming', 'categories' => [
                        ['slug' => 'tilapia', 'name_fr' => 'Tilapia', 'name_en' => 'Tilapia'],
                        ['slug' => 'silure-poisson-chat', 'name_fr' => 'Silure & Poisson-chat', 'name_en' => 'Catfish'],
                        ['slug' => 'carpe', 'name_fr' => 'Carpe', 'name_en' => 'Carp'],
                    ]],
                    ['slug' => 'peche-maritime', 'name_fr' => 'Pêche Maritime', 'name_en' => 'Marine Fishing', 'categories' => [
                        ['slug' => 'crevettes', 'name_fr' => 'Crevettes', 'name_en' => 'Shrimp'],
                        ['slug' => 'poissons-marins', 'name_fr' => 'Poissons Marins', 'name_en' => 'Marine Fish'],
                    ]],
                    ['slug' => 'transformation-poisson', 'name_fr' => 'Transformation & Conserve', 'name_en' => 'Processing & Preservation', 'categories' => [
                        ['slug' => 'poisson-fume', 'name_fr' => 'Poisson Fumé & Séché', 'name_en' => 'Smoked & Dried Fish'],
                        ['slug' => 'conserves-poisson', 'name_fr' => 'Conserves de Poisson', 'name_en' => 'Canned Fish'],
                    ]],
                ],
            ],
            [
                'slug' => 'agriculture', 'icon' => 'sprout', 'sort_order' => 3,
                'name_fr' => 'Agriculture & Agro-industrie',
                'name_en' => 'Agriculture & Agro-industry',
                'description_fr' => 'Cacao, café, banane plantain, manioc et produits agricoles transformés.',
                'description_en' => 'Cocoa, coffee, plantain, cassava and processed agricultural products.',
                'sectors' => [
                    ['slug' => 'cultures-rente', 'name_fr' => 'Cultures de Rente', 'name_en' => 'Cash Crops', 'categories' => [
                        ['slug' => 'cacao', 'name_fr' => 'Cacao & Chocolat', 'name_en' => 'Cocoa & Chocolate'],
                        ['slug' => 'cafe', 'name_fr' => 'Café', 'name_en' => 'Coffee'],
                    ]],
                    ['slug' => 'cultures-vivrieres', 'name_fr' => 'Cultures Vivrières', 'name_en' => 'Food Crops', 'categories' => [
                        ['slug' => 'manioc-derives', 'name_fr' => 'Manioc & Dérivés', 'name_en' => 'Cassava & Derivatives'],
                        ['slug' => 'plantain', 'name_fr' => 'Banane Plantain', 'name_en' => 'Plantain'],
                        ['slug' => 'mais', 'name_fr' => 'Maïs', 'name_en' => 'Corn'],
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
            [
                'slug' => 'agroalimentaire', 'icon' => 'utensils', 'sort_order' => 5,
                'name_fr' => 'Agroalimentaire & Épices',
                'name_en' => 'Food & Spices',
                'description_fr' => 'Épices, condiments, huiles essentielles et produits agroalimentaires artisanaux.',
                'description_en' => 'Spices, condiments, essential oils and artisanal food products.',
                'sectors' => [
                    ['slug' => 'epices-condiments', 'name_fr' => 'Épices & Condiments', 'name_en' => 'Spices & Condiments', 'categories' => [
                        ['slug' => 'poivre-penja', 'name_fr' => 'Poivre de Penja', 'name_en' => 'Penja Pepper'],
                        ['slug' => 'piments', 'name_fr' => 'Piments & Sauces', 'name_en' => 'Peppers & Sauces'],
                        ['slug' => 'huiles-vegetales', 'name_fr' => 'Huiles Végétales', 'name_en' => 'Vegetable Oils'],
                    ]],
                ],
            ],
        ];

        foreach ($industries as $sort => $ind) {
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

            foreach ($ind['sectors'] as $sSort => $sec) {
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

                foreach ($sec['categories'] as $cSort => $cat) {
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

        foreach ($templates as $t) {
            DB::table('attribute_templates')->insert(array_merge($t, [
                'options_fr' => isset($t['options_fr']) ? json_encode($t['options_fr']) : null,
                'options_en' => isset($t['options_en']) ? json_encode($t['options_en']) : null,
                'is_required' => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]));
        }

        $this->command->info('  5 industries, 14 sectors, 27 categories, 6 attribute templates seeded.');
    }
}
