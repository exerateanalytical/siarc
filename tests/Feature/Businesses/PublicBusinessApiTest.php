<?php

namespace Tests\Feature\Businesses;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class PublicBusinessApiTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_index_lists_only_published_businesses(): void
    {
        $published = $this->makeBusiness();
        $this->makeBusiness(null, ['status' => 'draft']);
        $this->makeBusiness(null, ['status' => 'suspended']);

        $this->getJson('/api/v1/businesses')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('meta.total', 1)
             ->assertJsonPath('data.0.slug', $published->slug);
    }

    public function test_show_returns_published_business_by_slug(): void
    {
        $business = $this->makeBusiness();

        $this->getJson("/api/v1/businesses/{$business->slug}")
             ->assertStatus(200)
             ->assertJsonPath('data.slug', $business->slug);
    }

    public function test_show_hides_draft_business(): void
    {
        $draft = $this->makeBusiness(null, ['status' => 'draft']);

        $this->getJson("/api/v1/businesses/{$draft->slug}")->assertStatus(404);
    }

    public function test_per_page_is_capped_at_100(): void
    {
        $this->makeBusiness();

        $this->getJson('/api/v1/businesses?per_page=5000')
             ->assertStatus(200)
             ->assertJsonPath('meta.per_page', 100);
    }

    public function test_featured_endpoint_returns_only_published(): void
    {
        $this->makeBusiness(null, ['is_featured' => true]);
        $this->makeBusiness(null, ['is_featured' => true, 'status' => 'draft']);

        $this->getJson('/api/v1/businesses/featured')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data');
    }
}
