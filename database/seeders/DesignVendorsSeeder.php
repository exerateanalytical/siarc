<?php

namespace Database\Seeders;

use App\Modules\Businesses\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds the 8 vendors shown on the official "Annuaire des Artisans & Entreprises"
 * design (vendors directory.png) so every card on the replica page opens a real,
 * admin-editable business profile. Idempotent — safe to re-run.
 */
class DesignVendorsSeeder extends Seeder
{
    public function run(): void
    {
        // [slug, name_fr, industry_slug, city_id, region_id, ownership, tagline_fr, tagline_en, image]
        $rows = [
            ['ceramiques-du-noun', 'Céramiques du Noun', 'poterie-ceramique', 13, 3, 'private',
             'Spécialistes de la poterie traditionnelle et de la céramique artisanale.',
             'Specialists in traditional pottery and artisanal ceramics.', 'vendor-1.png'],
            ['afrik-cuir-excellence', 'Afrik Cuir Excellence', 'cuir-maroquinerie', 6, 2, 'private',
             'Conception et fabrication d\'articles en cuir de haute qualité.',
             'Design and manufacture of high-quality leather goods.', 'vendor-2.png'],
            ['sawa-wood-art', 'Sawa Wood Art', 'bois-sculpture', 42, 9, 'private',
             'Sculptures et objets d\'art en bois inspirés de la tradition Sawa.',
             'Wooden sculptures and works of art inspired by the Sawa tradition.', 'vendor-3.png'],
            ['tressage-bamenda', 'Tressage Bamenda', 'arts-decoration', 16, 4, 'cooperative',
             'Créations en fibres naturelles faites à la main par nos artisans.',
             'Natural-fibre creations handmade by our artisans.', 'vendor-4.png'],
            ['perles-du-sahel', 'Perles du Sahel', 'bijouterie-accessoires', 46, 10, 'private',
             'Bijoux et accessoires fabriqués avec des perles africaines.',
             'Jewellery and accessories made with African beads.', 'vendor-5.png'],
            ['tissus-racines', 'Tissus & Racines', 'textile-mode', 1, 1, 'private',
             'Tissus traditionnels et créations inspirées du patrimoine africain.',
             'Traditional fabrics and creations inspired by African heritage.', 'vendor-6.png'],
            ['rythmes-dafrique', 'Rythmes d\'Afrique', 'musique-instruments', 6, 2, 'private',
             'Instruments de musique traditionnels et accessoires.',
             'Traditional musical instruments and accessories.', 'vendor-7.png'],
            ['nature-bienfaits', 'Nature & Bienfaits', 'produits-naturels', 11, 3, 'private',
             'Produits naturels et cosmétiques faits à partir d\'ingrédients locaux.',
             'Natural products and cosmetics made from local ingredients.', 'vendor-8.png'],
        ];

        $ownerId = Business::where('status', 'published')->orderBy('id')->value('user_id');
        if (! $ownerId) {
            $this->command?->warn('No published business found to borrow an owner from — aborting.');
            return;
        }

        $industryIds = \DB::table('industries')->pluck('id', 'slug');
        $landing = public_path('images/landing');
        $storageBase = storage_path('app/public/businesses');

        foreach ($rows as [$slug, $nameFr, $industrySlug, $cityId, $regionId, $ownership, $tagFr, $tagEn, $image]) {
            $relPath = "businesses/{$slug}/cover.png";
            $absPath = "{$storageBase}/{$slug}/cover.png";
            if (! File::exists($absPath) && File::exists("{$landing}/{$image}")) {
                File::ensureDirectoryExists(dirname($absPath));
                File::copy("{$landing}/{$image}", $absPath);
            }

            Business::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id'           => $ownerId,
                    'industry_id'       => $industryIds[$industrySlug] ?? null,
                    'region_id'         => $regionId,
                    'city_id'           => $cityId,
                    'name_fr'           => $nameFr,
                    'name_en'           => $nameFr,
                    'tagline_fr'        => $tagFr,
                    'tagline_en'        => $tagEn,
                    'description_fr'    => $tagFr,
                    'description_en'    => $tagEn,
                    'cover_image'       => $relPath,
                    'ownership_type'    => $ownership,
                    'verification_tier' => 'verified',
                    'status'            => 'published',
                    'deleted_at'        => null,
                ]
            );
        }

        $this->command?->info('Design vendors seeded: ' . count($rows));
    }
}
