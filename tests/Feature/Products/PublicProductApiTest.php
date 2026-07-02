<?php

namespace Tests\Feature\Products;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class PublicProductApiTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_index_lists_only_published_products(): void
    {
        $business = $this->makeBusiness();
        $published = $this->makeProduct($business);
        $this->makeProduct($business, ['status' => 'draft']);

        $this->getJson('/api/v1/products')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.slug', $published->slug);
    }

    public function test_show_returns_published_product(): void
    {
        $product = $this->makeProduct();

        $this->getJson("/api/v1/products/{$product->slug}")
             ->assertStatus(200)
             ->assertJsonPath('data.slug', $product->slug);
    }

    public function test_show_hides_product_of_unpublished_business(): void
    {
        $draftBusiness = $this->makeBusiness(null, ['status' => 'draft']);
        $product = $this->makeProduct($draftBusiness);

        $this->getJson("/api/v1/products/{$product->slug}")->assertStatus(404);
    }

    public function test_products_by_business(): void
    {
        $business = $this->makeBusiness();
        $this->makeProduct($business);
        $this->makeProduct($business);
        $this->makeProduct(); // other business

        $this->getJson("/api/v1/businesses/{$business->slug}/products")
             ->assertStatus(200)
             ->assertJsonCount(2, 'data');
    }

    public function test_per_page_is_capped_at_100(): void
    {
        $this->makeProduct();

        $this->getJson('/api/v1/products?per_page=9999')
             ->assertStatus(200)
             ->assertJsonPath('meta.per_page', 100);
    }
}
