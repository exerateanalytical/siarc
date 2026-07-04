<?php

namespace Tests\Feature;

use App\Modules\Taxonomy\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class LandingSyncTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function makeIndustry(array $attrs = []): Industry
    {
        $name = 'Secteur ' . fake()->unique()->word();

        return Industry::forceCreate(array_merge([
            'slug'       => 'secteur-' . fake()->unique()->word(),
            'name_fr'    => $name,
            'name_en'    => $name,
            'is_active'  => true,
            'sort_order' => 1,
        ], $attrs));
    }

    public function test_landing_sector_cards_come_from_the_industries_table(): void
    {
        $ind = $this->makeIndustry([
            'name_fr'    => 'Vannerie & Rotin',
            'name_en'    => 'Basketry & Rattan',
            'image_icon' => 'cat-icon-3.png',
        ]);
        // An industry without a tile icon must not appear as a landing card
        $hidden = $this->makeIndustry(['name_fr' => 'Secteur Sans Icone', 'image_icon' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Vannerie', false)
            ->assertSee('cat-icon-3.png')
            ->assertDontSee('Secteur Sans Icone');
    }

    public function test_categories_page_shows_real_product_counts_from_the_backend(): void
    {
        $ind = $this->makeIndustry(['name_fr' => 'Poterie Test', 'image_icon' => 'cat-icon-4.png', 'side_icon' => 'cat-side-4.png']);
        $biz = $this->makeBusiness(null, ['industry_id' => $ind->id]);
        $this->makeProduct($biz);
        $this->makeProduct($biz);
        $this->makeProduct($biz);

        $this->get('/galerie/secteurs')
            ->assertOk()
            ->assertSee('Poterie Test')
            ->assertSee('cat-icon-4.png')
            ->assertSeeInOrder(['Poterie Test'], false)
            ->assertSee('3 produits');
    }

    public function test_business_directory_supports_the_featured_filter(): void
    {
        $ind      = $this->makeIndustry();
        $featured = $this->makeBusiness(null, ['name_fr' => 'Vedette Artisanat', 'industry_id' => $ind->id, 'is_featured' => true]);
        $plain    = $this->makeBusiness(null, ['name_fr' => 'Entreprise Ordinaire Xyz', 'industry_id' => $ind->id, 'is_featured' => false]);

        $this->get('/galerie/entreprises?featured=1')
            ->assertOk()
            ->assertSee('Vedette Artisanat')
            ->assertDontSee('Entreprise Ordinaire Xyz');
    }

    public function test_products_directory_lists_real_products_with_working_category_filter(): void
    {
        $indA = $this->makeIndustry(['slug' => 'poterie-ceramique', 'image_icon' => 'cat-icon-4.png']);
        $indB = $this->makeIndustry(['slug' => 'bois-sculpture', 'image_icon' => 'cat-icon-3.png']);

        $bizA = $this->makeBusiness(null, ['industry_id' => $indA->id]);
        $bizB = $this->makeBusiness(null, ['industry_id' => $indB->id]);

        $this->makeProduct($bizA, ['name_fr' => 'Vase Royal Bamoun']);
        $this->makeProduct($bizB, ['name_fr' => 'Masque Sculpte Fang']);

        // Unfiltered: both real products and the real total
        $this->get('/galerie/produits')
            ->assertOk()
            ->assertSee('Vase Royal Bamoun')
            ->assertSee('Masque Sculpte Fang')
            ->assertSee('2 produits');

        // Category filter narrows to the matching industry only
        $this->get('/galerie/produits?categorie=poterie-ceramique')
            ->assertOk()
            ->assertSee('Vase Royal Bamoun')
            ->assertDontSee('Masque Sculpte Fang');
    }

    public function test_events_page_lists_real_events_with_working_type_filter(): void
    {
        $this->makeEvent([
            'name_fr'    => 'Salon Test des Tisserands',
            'event_type' => 'salons',
            'city_fr'    => 'Bafoussam, Ouest',
            'price_fr'   => 'Entrée libre',
            'starts_at'  => now()->addDays(5),
            'ends_at'    => now()->addDays(5)->addHours(8),
        ]);
        $this->makeEvent([
            'name_fr'    => 'Atelier Test du Cuir',
            'event_type' => 'ateliers',
            'starts_at'  => now()->addDays(9),
            'ends_at'    => now()->addDays(9)->addHours(6),
        ]);

        $this->get('/evenements')
            ->assertOk()
            ->assertSee('Salon Test des Tisserands')
            ->assertSee('Atelier Test du Cuir');

        $this->get('/evenements?type=salons')
            ->assertOk()
            ->assertSee('Salon Test des Tisserands')
            ->assertDontSee('Atelier Test du Cuir');
    }

    public function test_featured_businesses_on_landing_come_from_the_database(): void
    {
        $ind = $this->makeIndustry();
        $this->makeBusiness(null, [
            'name_fr'     => 'Atelier Vitrine Reel',
            'industry_id' => $ind->id,
            'is_featured' => true,
            'cover_image' => 'businesses/atelier-vitrine/cover.png',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Atelier Vitrine Reel')
            ->assertSee('businesses/atelier-vitrine/cover.png');
    }
}
