<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class SeoAndHardeningTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_sitemap_lists_public_pages_and_published_content(): void
    {
        $business = $this->makeBusiness();
        $product  = $this->makeProduct($business);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $xml = $response->getContent();
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString(url('/'), $xml);
        $this->assertStringContainsString(url('/galerie/entreprises'), $xml);
        $this->assertStringContainsString(url('/galerie/produits'), $xml);
        $this->assertStringContainsString($business->slug, $xml);
        $this->assertStringContainsString($product->slug, $xml);
    }

    public function test_sitemap_excludes_unpublished_businesses(): void
    {
        $hidden = $this->makeBusiness(null, ['status' => 'draft']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee($hidden->slug);
    }

    public function test_robots_txt_allows_crawling_and_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $body = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Sitemap: ' . url('/sitemap.xml'), $body);
        $this->assertStringContainsString('Disallow: /tableau-de-bord', $body);
    }

    public function test_newsletter_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/newsletter', ['email' => "user{$i}@example.com"])
                ->assertRedirect();
        }

        $this->post('/newsletter', ['email' => 'blocked@example.com'])
            ->assertStatus(429);
    }
}
