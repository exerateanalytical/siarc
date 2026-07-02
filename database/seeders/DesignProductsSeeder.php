<?php

namespace Database\Seeders;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds the 12 products shown on the official "Annuaire des produits" design
 * (Product diretory.png) so every card on the replica page opens a real,
 * admin-editable product. Idempotent — safe to re-run.
 */
class DesignProductsSeeder extends Seeder
{
    public function run(): void
    {
        // [slug, name_fr, name_en, business_slug, category_id, image, description_fr, description_en]
        $rows = [
            ['panier-africain-tresse', 'Panier africain tressé', 'African woven basket', 'atelier-nguema-sculptures', 1, 'product-1.png',
             'Panier tressé à la main selon les techniques traditionnelles camerounaises, en fibres naturelles.',
             'Hand-woven basket made with traditional Cameroonian techniques from natural fibres.'],
            ['sculpture-en-bois-sawa', 'Sculpture en bois Sawa', 'Sawa wood sculpture', 'atelier-nguema-sculptures', 5, 'product-2.png',
             'Sculpture en bois massif inspirée de l\'art Sawa, entièrement taillée à la main.',
             'Solid wood sculpture inspired by Sawa art, entirely hand-carved.'],
            ['sac-a-main-traditionnel', 'Sac à main traditionnel', 'Traditional handbag', 'mode-africaine-sandrine', 27, 'product-3.png',
             'Sac à main en tissu traditionnel aux motifs géométriques, confectionné par nos artisans.',
             'Handbag in traditional fabric with geometric patterns, made by our artisans.'],
            ['vase-en-terre-cuite', 'Vase en terre cuite', 'Terracotta vase', 'poteries-de-maroua', 7, 'product-4.png',
             'Vase en terre cuite décoré de motifs traditionnels, façonné et cuit artisanalement.',
             'Terracotta vase decorated with traditional patterns, hand-shaped and kiln-fired.'],
            ['collier-perles-africaines', 'Collier perles africaines', 'African bead necklace', 'bijoux-bronze-du-grassland', 12, 'product-5.png',
             'Collier de perles africaines aux couleurs du Cameroun avec pendentif artisanal.',
             'African bead necklace in the colours of Cameroon with a handcrafted pendant.'],
            ['sac-en-cuir-veritable', 'Sac en cuir véritable', 'Genuine leather bag', 'maroquinerie-foumban', 9, 'product-6.png',
             'Sac en cuir véritable tanné et cousu à la main dans nos ateliers de Foumban.',
             'Genuine leather bag, tanned and hand-stitched in our Foumban workshops.'],
            ['djembe-traditionnel', 'Djembé traditionnel', 'Traditional djembe', 'atelier-nguema-sculptures', null, 'product-7.png',
             'Djembé traditionnel sculpté dans un tronc massif avec peau naturelle tendue à la corde.',
             'Traditional djembe carved from a solid trunk with rope-tensioned natural skin.'],
            ['miel-naturel-du-cameroun', 'Miel naturel du Cameroun', 'Natural Cameroon honey', 'epices-du-cameroun-epicam', null, 'product-8.png',
             'Miel naturel récolté dans les forêts camerounaises, pur et non pasteurisé.',
             'Natural honey harvested in Cameroonian forests, pure and unpasteurised.'],
            ['feves-de-cacao-premium', 'Fèves de cacao premium', 'Premium cocoa beans', 'plantation-cacao-fin-mungo', 20, 'product-9.png',
             'Fèves de cacao premium fermentées et séchées au soleil, qualité export.',
             'Premium cocoa beans, fermented and sun-dried, export grade.'],
            ['savon-naturel-artisanal', 'Savon naturel artisanal', 'Natural handmade soap', 'cooperative-test-bio', null, 'product-10.png',
             'Savon naturel saponifié à froid à base d\'huiles végétales locales.',
             'Cold-process natural soap made from local vegetable oils.'],
            ['lampe-solaire-artisanale', 'Lampe solaire artisanale', 'Handcrafted solar lamp', 'atelier-nguema-sculptures', null, 'product-11.png',
             'Lampe solaire au corps en bois artisanal, alliant savoir-faire local et innovation.',
             'Solar lamp with a handcrafted wooden body, combining local know-how and innovation.'],
            ['beurre-de-karite-pur', 'Beurre de karité pur', 'Pure shea butter', 'epices-du-cameroun-epicam', 30, 'product-12.png',
             'Beurre de karité pur pressé artisanalement, sans additif ni conservateur.',
             'Pure shea butter, artisanally pressed, with no additives or preservatives.'],
        ];

        $businessIds = \App\Modules\Businesses\Models\Business::pluck('id', 'slug');
        $landing = public_path('images/landing');
        $storageBase = storage_path('app/public/products');

        foreach ($rows as [$slug, $nameFr, $nameEn, $bizSlug, $categoryId, $image, $descFr, $descEn]) {
            $businessId = $businessIds[$bizSlug] ?? null;
            if (! $businessId) {
                $this->command?->warn("Skipping {$slug}: business {$bizSlug} not found");
                continue;
            }

            $product = Product::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'business_id'    => $businessId,
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

            // Cover image: copy the design crop into the public storage disk
            $relPath = "products/{$slug}/images/design.png";
            $absPath = "{$storageBase}/{$slug}/images/design.png";
            if (! File::exists($absPath) && File::exists("{$landing}/{$image}")) {
                File::ensureDirectoryExists(dirname($absPath));
                File::copy("{$landing}/{$image}", $absPath);
            }

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'file_path' => $relPath],
                ['is_cover' => true, 'sort_order' => 0]
            );
        }

        $this->command?->info('Design products seeded: ' . count($rows));
    }
}
