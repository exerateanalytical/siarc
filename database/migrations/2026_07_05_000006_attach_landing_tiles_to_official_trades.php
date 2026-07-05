<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The public landing + /galerie/secteurs render their illustrated category grid
// from industries that carry an `image_icon` tile. The official-taxonomy
// replacement wiped the old ad-hoc industries, leaving image_icon NULL on every
// row (empty category grid). Re-attach the 10 illustrated tiles to the official
// métier rows they best represent. Businesses are already tagged to these métiers
// (businesses.industry_id), so card counts and gallery filters work directly.
return new class extends Migration
{
    public function up(): void
    {
        // official métier name_fr => [tile icon, sidebar icon, card order]
        $tiles = [
            'Sculpteur/décorateur sur tous matériaux' => ['cat-icon-1.png',  'cat-side-1.png',  1],
            'Tailleur/couturier'                      => ['cat-icon-2.png',  'cat-side-2.png',  2],
            'Ebéniste'                                => ['cat-icon-3.png',  'cat-side-3.png',  3],
            'Céramiste (Potier)'                      => ['cat-icon-4.png',  'cat-side-4.png',  4],
            'Bijoutier-joaillier'                     => ['cat-icon-5.png',  'cat-side-5.png',  5],
            'Maroquinier'                             => ['cat-icon-6.png',  'cat-side-6.png',  6],
            'Fabricant d\'instruments de musique'     => ['cat-icon-7.png',  'cat-side-7.png',  7],
            'Fabricant de cosmétiques et assimilés'   => ['cat-icon-8.png',  'cat-side-8.png',  8],
            'Transformateur de cacao'                 => ['cat-icon-9.png',  'cat-side-9.png',  9],
            'Grapheur'                                => ['cat-icon-10.png', 'cat-side-10.png', 10],
        ];

        foreach ($tiles as $name => [$icon, $side, $sort]) {
            DB::table('industries')->where('name_fr', $name)->where('level', 4)
                ->update(['image_icon' => $icon, 'side_icon' => $side, 'sort_order' => $sort]);
        }

        // The homepage "Aquaculture" spotlight (and the featured-exclusion) query
        // industries by slug 'aquaculture'. The two aquaculture vendors are tagged
        // to the fish-processing métier, so give it that stable slug — the existing
        // controller queries then light the section back up with no code change.
        DB::table('industries')
            ->where('name_fr', 'Sécheur/fumeur de poisson, de crustacées, de mollusques')
            ->where('level', 4)
            ->update(['slug' => 'aquaculture']);
    }

    public function down(): void
    {
        foreach (['cat-icon-1.png','cat-icon-2.png','cat-icon-3.png','cat-icon-4.png','cat-icon-5.png','cat-icon-6.png','cat-icon-7.png','cat-icon-8.png','cat-icon-9.png','cat-icon-10.png'] as $icon) {
            DB::table('industries')->where('image_icon', $icon)->update(['image_icon' => null, 'side_icon' => null]);
        }
    }
};
