<?php

namespace Database\Seeders;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Seeds what the official "vendors detail page" design shows for Céramiques du
 * Noun: profile details (tagline, 2018 creation, 8 artisans, 2021 membership,
 * response time, languages) and the Produits phares products that didn't exist
 * yet (Pot traditionnel Bamoun, Jarre décorative, Sac en cuir artisanal).
 * Idempotent — safe to re-run.
 */
class DesignVendorDetailSeeder extends Seeder
{
    public function run(): void
    {
        $ceramiques = Business::where('slug', 'ceramiques-du-noun')->first();
        $afrikCuir = Business::where('slug', 'afrik-cuir-excellence')->first();
        if (! $ceramiques || ! $afrikCuir) {
            $this->command?->warn('Run DesignVendorsSeeder first.');
            return;
        }

        $ceramiques->update([
            'tagline_fr'          => 'Poterie & Céramique Traditionnelle',
            'tagline_en'          => 'Traditional Pottery & Ceramics',
            'year_established'    => 2018,
            'employee_count'      => 8,
            'phone'               => '+237670416238',
            'whatsapp'            => '+237670416238',
            'email'               => 'contact@ceramiquesdunoun.cm',
            'website'             => 'https://ceramiquesdunoun.cm',
            'response_time_hours' => 2,
            'languages_spoken'    => ['Français', 'English'],
        ]);
        // "Membre depuis 2021" on the design
        DB::table('businesses')->where('id', $ceramiques->id)->update(['created_at' => '2021-03-15 10:00:00']);

        $potCategoryId = DB::table('product_categories')->where('slug', 'poterie-ceramique-design')->value('id');

        // [slug, name_fr, name_en, business, category_id, image, desc_fr, desc_en]
        $rows = [
            ['pot-traditionnel-bamoun', 'Pot traditionnel Bamoun', 'Traditional Bamoun pot', $ceramiques, $potCategoryId, 'vdetail-prod-pot.png',
             'Pot traditionnel Bamoun en terre cuite, tressé de motifs gravés à la main.',
             'Traditional Bamoun terracotta pot with hand-engraved patterns.'],
            ['jarre-decorative', 'Jarre décorative', 'Decorative jar', $ceramiques, $potCategoryId, 'vdetail-prod-jarre.png',
             'Jarre décorative en terre cuite ornée de motifs traditionnels de l\'Ouest Cameroun.',
             'Decorative terracotta jar adorned with traditional patterns from West Cameroon.'],
            ['sac-en-cuir-artisanal', 'Sac en cuir artisanal', 'Handcrafted leather bag', $afrikCuir, 9, 'vdetail-prod-sac.png',
             'Sac en cuir artisanal tanné et cousu à la main.',
             'Handcrafted leather bag, tanned and hand-stitched.'],
        ];

        $landing = public_path('images/landing');
        foreach ($rows as [$slug, $nameFr, $nameEn, $biz, $categoryId, $image, $descFr, $descEn]) {
            $product = Product::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'business_id'    => $biz->id,
                    'category_id'    => $categoryId,
                    'name_fr'        => $nameFr,
                    'name_en'        => $nameEn,
                    'description_fr' => $descFr,
                    'description_en' => $descEn,
                    'is_available'   => true,
                    'is_retail'      => true,
                    'status'         => 'published',
                    'deleted_at'     => null,
                ]
            );

            $relPath = "products/{$slug}/images/design.png";
            $absPath = storage_path("app/public/{$relPath}");
            if (! File::exists($absPath) && File::exists("{$landing}/{$image}")) {
                File::ensureDirectoryExists(dirname($absPath));
                File::copy("{$landing}/{$image}", $absPath);
            }
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'file_path' => $relPath],
                ['is_cover' => true, 'sort_order' => 0]
            );
        }

        $this->command?->info('Design vendor detail seeded.');
    }
}
