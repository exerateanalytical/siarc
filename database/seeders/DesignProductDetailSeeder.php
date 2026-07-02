<?php

namespace Database\Seeders;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Seeds everything the official "Product detail page" design (Product detail page.png)
 * shows: the taxonomy chain Arts & Décoration › Poterie & Céramique, the design's
 * attribute labels (Matière/Technique/Origine/Couleur/Dimensions/Poids), and the
 * product "Vase en terre cuite gravé à la main" by Céramiques du Noun with its
 * 5-image gallery. Idempotent — safe to re-run.
 */
class DesignProductDetailSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('slug', 'ceramiques-du-noun')->first();
        if (! $business) {
            $this->command?->warn('Run DesignVendorsSeeder first (ceramiques-du-noun missing).');
            return;
        }

        // Taxonomy chain shown in the design breadcrumb: Arts & Décoration › Poterie & Céramique
        $artsIndustryId = DB::table('industries')->where('slug', 'arts-decoration')->value('id');
        $sectorId = DB::table('sectors')->where('slug', 'poterie-ceramique-arts')->value('id');
        if (! $sectorId) {
            $sectorId = DB::table('sectors')->insertGetId([
                'industry_id' => $artsIndustryId,
                'slug'        => 'poterie-ceramique-arts',
                'name_fr'     => 'Poterie & Céramique',
                'name_en'     => 'Pottery & Ceramics',
                'sort_order'  => 90,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
        $categoryId = DB::table('product_categories')->where('slug', 'poterie-ceramique-design')->value('id');
        if (! $categoryId) {
            $categoryId = DB::table('product_categories')->insertGetId([
                'sector_id'  => $sectorId,
                'slug'       => 'poterie-ceramique-design',
                'name_fr'    => 'Poterie & Céramique',
                'name_en'    => 'Pottery & Ceramics',
                'depth'      => 0,
                'sort_order' => 90,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attribute templates with the design's exact spec labels (scoped to the
        // business's industry so they appear in its dashboard forms)
        $tplIndustryId = $business->industry_id;
        $templates = [
            ['matiere',    'Matière',    'Material'],
            ['technique',  'Technique',  'Technique'],
            ['origine',    'Origine',    'Origin'],
            ['couleur-d',  'Couleur',    'Colour'],
            ['dimensions-d','Dimensions','Dimensions'],
            ['poids-d',    'Poids',      'Weight'],
        ];
        $tplIds = [];
        foreach ($templates as $i => [$key, $fr, $en]) {
            $id = DB::table('attribute_templates')
                ->where('industry_id', $tplIndustryId)->where('field_key', $key)->value('id');
            if (! $id) {
                $id = DB::table('attribute_templates')->insertGetId([
                    'industry_id' => $tplIndustryId,
                    'name_fr'     => $fr,
                    'name_en'     => $en,
                    'field_key'   => $key,
                    'field_type'  => 'text',
                    'sort_order'  => $i + 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
            $tplIds[$key] = $id;
        }

        // The product itself
        $product = Product::withTrashed()->updateOrCreate(
            ['slug' => 'vase-en-terre-cuite-grave-a-la-main'],
            [
                'business_id'      => $business->id,
                'category_id'      => $categoryId,
                'origin_region_id' => 3, // Ouest
                'name_fr'          => 'Vase en terre cuite gravé à la main',
                'name_en'          => 'Hand-engraved terracotta vase',
                'description_fr'   => 'Magnifique vase décoratif en terre cuite, façonné et gravé à la main par des artisans Bamoun. Chaque pièce est unique et reflète le riche patrimoine culturel camerounais.',
                'description_en'   => 'Magnificent decorative terracotta vase, shaped and hand-engraved by Bamoun artisans. Each piece is unique and reflects Cameroon\'s rich cultural heritage.',
                'is_available'     => true,
                'is_retail'        => true,
                'status'           => 'published',
                'deleted_at'       => null,
            ]
        );

        // Attribute values, verbatim from the design
        $values = [
            'matiere'      => ['Terre cuite naturelle', 'Natural terracotta'],
            'technique'    => ['Modelage et gravure manuelle', 'Hand modelling and engraving'],
            'origine'      => ['Foumban, Ouest – Cameroun', 'Foumban, West – Cameroon'],
            'couleur-d'    => ['Terre naturelle avec motifs noirs et blancs', 'Natural clay with black and white patterns'],
            'dimensions-d' => ['Hauteur 25 cm – Diamètre 18 cm', 'Height 25 cm – Diameter 18 cm'],
            'poids-d'      => ['~ 1.2 kg', '~ 1.2 kg'],
        ];
        foreach ($values as $key => [$fr, $en]) {
            DB::table('product_attributes')->updateOrInsert(
                ['product_id' => $product->id, 'attribute_template_id' => $tplIds[$key]],
                ['value_fr' => $fr, 'value_en' => $en, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        // Gallery: main photo + thumbnails 2..5 (thumb 1 is the main image itself)
        $landing = public_path('images/landing');
        $galleryFiles = [
            ['pdetail-main.png',    true],
            ['pdetail-thumb-2.png', false],
            ['pdetail-thumb-3.png', false],
            ['pdetail-thumb-4.png', false],
            ['pdetail-thumb-5.png', false],
        ];
        foreach ($galleryFiles as $i => [$file, $isCover]) {
            $relPath = "products/{$product->slug}/images/{$file}";
            $absPath = storage_path("app/public/{$relPath}");
            if (! File::exists($absPath) && File::exists("{$landing}/{$file}")) {
                File::ensureDirectoryExists(dirname($absPath));
                File::copy("{$landing}/{$file}", $absPath);
            }
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'file_path' => $relPath],
                ['is_cover' => $isCover, 'sort_order' => $i]
            );
        }

        // Artisan logo shown in the "À propos de l'artisan" card
        $logoRel = 'businesses/ceramiques-du-noun/logo.png';
        $logoAbs = storage_path("app/public/{$logoRel}");
        if (! File::exists($logoAbs) && File::exists("{$landing}/pdetail-artisan-logo.png")) {
            File::ensureDirectoryExists(dirname($logoAbs));
            File::copy("{$landing}/pdetail-artisan-logo.png", $logoAbs);
        }
        if (! $business->logo) {
            $business->update(['logo' => $logoRel]);
        }

        $this->command?->info('Design product detail seeded.');
    }
}
