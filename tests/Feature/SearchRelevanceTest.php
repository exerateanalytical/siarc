<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Support\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The suite runs on SQLite, whose LIKE folds ASCII case but not accents;
 * production is utf8mb4_unicode_ci, which folds both. Fixtures here therefore
 * stick to ASCII — accent folding is a property of the collation, asserted by
 * test_the_search_columns_use_an_accent_folding_collation on MySQL.
 */
class SearchRelevanceTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function ranked(string $q): array
    {
        $query = Business::query()->where('status', 'published');
        SearchQuery::apply($query, $q, SearchQuery::BUSINESS_COLUMNS, SearchQuery::BUSINESS_RELATIONS);
        SearchQuery::orderByRelevance($query, $q, SearchQuery::BUSINESS_NAMES, SearchQuery::BUSINESS_SECONDARY);

        return $query->pluck('name_fr')->all();
    }

    public function test_exact_name_match_outranks_prefix_contains_and_description(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Atelier mentions Poterie dans le texte', 'description_fr' => 'Nous vendons de la Poterie']);
        $this->makeBusiness(null, ['name_fr' => 'Grande Poterie du Nord']);
        $this->makeBusiness(null, ['name_fr' => 'Poterie Traditionnelle']);
        $this->makeBusiness(null, ['name_fr' => 'Poterie']);
        $this->makeBusiness(null, ['name_fr' => 'Vannerie', 'description_fr' => 'Un peu de poterie aussi']);

        $ranked = $this->ranked('Poterie');

        $this->assertSame('Poterie', $ranked[0], 'exact name match ranks first');
        $this->assertSame('Poterie Traditionnelle', $ranked[1], 'name prefix ranks second');
        // The two name-contains rows share a band; only their position relative
        // to the description-only hit is guaranteed.
        $this->assertSame(
            ['Atelier mentions Poterie dans le texte', 'Grande Poterie du Nord'],
            collect(array_slice($ranked, 2, 2))->sort()->values()->all()
        );
        $this->assertSame('Vannerie', $ranked[4], 'a description-only hit ranks last');
    }

    public function test_a_multi_word_query_requires_every_term(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Poterie de Bafoussam']);
        $this->makeBusiness(null, ['name_fr' => 'Poterie de Douala']);
        $this->makeBusiness(null, ['name_fr' => 'Vannerie de Bafoussam']);

        // The old literal '%poterie bafoussam%' matched nothing at all here.
        $this->assertSame(['Poterie de Bafoussam'], $this->ranked('poterie bafoussam'));
    }

    public function test_a_multi_word_query_matches_terms_spread_across_fields(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Atelier Kamdem', 'description_fr' => 'Sculpture sur bois']);
        $this->makeBusiness(null, ['name_fr' => 'Atelier Ngassa', 'description_fr' => 'Perlage']);

        $this->assertSame(['Atelier Kamdem'], $this->ranked('atelier sculpture'));
    }

    public function test_a_name_holding_every_term_outranks_a_description_hit(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Bijoux Ngando', 'description_fr' => 'Perles de Maroua fabriquees main']);
        $this->makeBusiness(null, ['name_fr' => 'Perles de Maroua Reunies']);

        $this->assertSame(['Perles de Maroua Reunies', 'Bijoux Ngando'], $this->ranked('perles maroua'));
    }

    public function test_search_reaches_the_city_and_region_of_a_business(): void
    {
        $regionId = \DB::table('regions')->insertGetId(['name_fr' => 'Region Test', 'name_en' => 'Test Region', 'code' => 'ZZ']);
        $cityId   = \DB::table('cities')->insertGetId(['region_id' => $regionId, 'name_fr' => 'Bafoussam', 'name_en' => 'Bafoussam', 'slug' => 'bafoussam-test']);

        $this->makeBusiness(null, ['name_fr' => 'Atelier Sans Ville']);
        $this->makeBusiness(null, ['name_fr' => 'Atelier Localise', 'city_id' => $cityId, 'region_id' => $regionId]);

        $this->assertSame(['Atelier Localise'], $this->ranked('Bafoussam'));
    }

    public function test_the_gallery_search_page_lists_the_exact_match_first(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Sculpture sur bois de Foumban']);
        $this->makeBusiness(null, ['name_fr' => 'Sculpture']);

        $html = $this->get('/galerie/recherche?q=Sculpture')->assertOk()->getContent();

        $this->assertLessThan(
            strpos($html, 'Sculpture sur bois de Foumban'),
            strpos($html, '>Sculpture<'),
            'the exact name match must be rendered before the partial one'
        );
    }

    public function test_product_search_is_ranked_too(): void
    {
        $business = $this->makeBusiness();
        $this->makeProduct($business, ['name_fr' => 'Masque en bois grave', 'description_fr' => 'Un masque']);
        $this->makeProduct($business, ['name_fr' => 'Masque']);

        $query = Product::published()->whereHas('business', fn ($q) => $q->where('status', 'published'));
        SearchQuery::apply($query, 'Masque', SearchQuery::PRODUCT_COLUMNS, SearchQuery::PRODUCT_RELATIONS);
        SearchQuery::orderByRelevance($query, 'Masque', SearchQuery::PRODUCT_NAMES, SearchQuery::PRODUCT_SECONDARY);

        $this->assertSame(['Masque', 'Masque en bois grave'], $query->pluck('name_fr')->all());
    }

    public function test_wildcards_typed_by_a_user_are_not_treated_as_operators(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Coton 100% Cameroun']);
        $this->makeBusiness(null, ['name_fr' => 'Autre Chose']);

        $this->assertSame(['Coton 100% Cameroun'], $this->ranked('100%'));
    }

    public function test_the_api_search_ranks_the_exact_match_first(): void
    {
        $this->makeBusiness(null, ['name_fr' => 'Tissage Bamileke Moderne']);
        $this->makeBusiness(null, ['name_fr' => 'Tissage']);

        $this->getJson('/api/v1/businesses?q=Tissage')
             ->assertOk()
             ->assertJsonPath('data.0.name', 'Tissage');
    }

    public function test_the_search_columns_use_an_accent_folding_collation(): void
    {
        // Accent folding is not implemented in PHP — it is the collation doing
        // the work, so the collation is what has to be pinned down.
        $configured = config('database.connections.mysql.collation');
        $this->assertSame('utf8mb4_unicode_ci', $configured, 'production tables must fold case and accents');

        if (\DB::connection()->getDriverName() !== 'mysql') {
            return; // the suite runs on SQLite; the live check below is for MySQL runs
        }

        foreach (['businesses', 'products', 'cities', 'regions', 'industries'] as $table) {
            $collation = \DB::selectOne(
                'SELECT TABLE_COLLATION c FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            )->c;

            $this->assertStringContainsString('_ci', $collation, "{$table} must use a case/accent-insensitive collation");
            $this->assertStringStartsWith('utf8mb4_unicode', $collation, "{$table} must use utf8mb4_unicode_ci");
        }
    }
}
