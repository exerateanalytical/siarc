<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * SEO / AEO / GEO contract added 2026-07-29:
 *
 *  - every public page carries a canonical URL, fr/en hreflang alternates
 *    that actually resolve, Open Graph tags, and the site-wide Organization
 *    JSON-LD;
 *  - every JSON-LD block on a page must parse — a syntax error in a schema
 *    block is worse than no schema;
 *  - the Organization schema never claims a public/government affiliation
 *    (config/legal.php is the source of truth: private company, no ministry);
 *  - a Product schema never carries a rating or review: reviews on this
 *    platform attach to the artisan, not the piece, so a product-level
 *    aggregateRating would be schema for content the page does not show;
 *  - the sitemap's category URLs come from the live industries table, never
 *    a hardcoded list, so it always reflects the pruned taxonomy;
 *  - llms.txt exists, is factual, and states the private-company framing.
 */
class SeoStructuredDataTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** Every application/ld+json block on the page, decoded; fails the test on a parse error. */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $blocks = [];
        foreach ($m[1] as $raw) {
            $decoded = json_decode($raw, true);
            $this->assertNotNull($decoded, 'Un-parseable JSON-LD block: ' . substr($raw, 0, 200));
            $blocks[] = $decoded;
        }

        return $blocks;
    }

    private function blocksOfType(array $blocks, string $type): array
    {
        return array_values(array_filter($blocks, fn ($b) => ($b['@type'] ?? null) === $type));
    }

    public function test_home_page_has_canonical_hreflang_og_and_organization_schema(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('hreflang="fr"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:site_name"', $html);

        $org = $this->blocksOfType($this->jsonLdBlocks($html), 'Organization');
        $this->assertNotEmpty($org, 'Organization JSON-LD missing from home page');
        $this->assertSame('Artisan Hub 237', $org[0]['name']);
    }

    public function test_organization_schema_never_claims_government_affiliation(): void
    {
        $schema = \App\Support\Seo::organizationSchema();
        $json = strtolower(json_encode($schema));

        // The description must state independence; no field may claim a
        // ministry/government tie.
        $this->assertStringContainsString('private platform', $json);
        $this->assertStringContainsString('not a government body', $json);
        foreach (['ministry of', 'ministère', 'government of cameroon', 'affiliated with the government', 'parentorganization'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $json, "Organization schema must never contain '{$forbidden}'");
        }
    }

    public function test_canonical_strips_query_variance_and_hreflang_pair_resolves(): void
    {
        $html = $this->get('/galerie/entreprises?lang=en&sort=name&page=2')->assertOk()->getContent();

        // Canonical is the clean URL — no lang/sort/page.
        $this->assertMatchesRegularExpression(
            '#<link rel="canonical" href="[^"]*/galerie/entreprises">#',
            $html
        );

        // The declared alternates must actually resolve.
        preg_match('#<link rel="alternate" hreflang="fr" href="([^"]+)"#', $html, $fr);
        preg_match('#<link rel="alternate" hreflang="en" href="([^"]+)"#', $fr ? $html : '', $en);
        $this->assertNotEmpty($fr, 'fr hreflang missing');
        $this->assertNotEmpty($en, 'en hreflang missing');
        $this->get($fr[1])->assertOk();
        $this->get($en[1])->assertOk();
    }

    public function test_business_profile_carries_person_or_localbusiness_schema_without_gps(): void
    {
        $business = $this->makeBusiness(null, [
            'vendor_type'    => 'artisan',
            'description_fr' => 'Sculpteur sur bois à Douala.',
            'gps_lat'        => 4.0511,
            'gps_lng'        => 9.7679,
        ]);

        $html = $this->get('/galerie/entreprises/' . $business->slug)->assertOk()->getContent();
        $blocks = $this->jsonLdBlocks($html);

        $person = $this->blocksOfType($blocks, 'Person');
        $this->assertNotEmpty($person, 'artisan profile should emit Person schema');
        $this->assertSame($business->name_fr, $person[0]['name']);

        // The standing rule: no exact GPS anywhere — no geo property, and the
        // raw coordinates must not appear in any schema block.
        $allJson = json_encode($blocks);
        $this->assertStringNotContainsString('"geo"', $allJson);
        $this->assertStringNotContainsString('4.0511', $allJson);
        $this->assertStringNotContainsString('9.7679', $allJson);

        $breadcrumbs = $this->blocksOfType($blocks, 'BreadcrumbList');
        $this->assertNotEmpty($breadcrumbs, 'business profile should emit BreadcrumbList schema');
    }

    public function test_product_schema_has_real_price_and_never_a_rating(): void
    {
        $business = $this->makeBusiness();
        $product = $this->makeProduct($business, [
            'price_amount'   => 25000,
            'price_currency' => 'XAF',
            'is_available'   => true,
            'description_fr' => 'Masque sculpté à la main.',
        ]);

        $html = $this->get('/galerie/produits/' . $product->slug)->assertOk()->getContent();
        $blocks = $this->jsonLdBlocks($html);

        $schema = $this->blocksOfType($blocks, 'Product');
        $this->assertNotEmpty($schema, 'product page should emit Product schema');
        $schema = $schema[0];

        $this->assertSame($product->name_fr, $schema['name']);
        $this->assertSame(25000.0, (float) $schema['offers']['price']);
        $this->assertSame('XAF', $schema['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);

        // Reviews attach to the artisan, never the piece: a Product block may
        // not carry rating/review keys even when the business has reviews.
        $this->assertArrayNotHasKey('aggregateRating', $schema);
        $this->assertArrayNotHasKey('review', $schema);
    }

    public function test_product_without_price_emits_no_offer(): void
    {
        $product = $this->makeProduct(null, ['price_amount' => null]);

        $html = $this->get('/galerie/produits/' . $product->slug)->assertOk()->getContent();
        $schema = $this->blocksOfType($this->jsonLdBlocks($html), 'Product');

        $this->assertNotEmpty($schema);
        $this->assertArrayNotHasKey('offers', $schema[0], 'no price on record means no Offer in the schema');
    }

    public function test_faq_page_schema_matches_visible_questions(): void
    {
        $html = $this->get('/faq')->assertOk()->getContent();
        $faq = $this->blocksOfType($this->jsonLdBlocks($html), 'FAQPage');

        $this->assertNotEmpty($faq, 'FAQ page should emit FAQPage schema');
        $this->assertNotEmpty($faq[0]['mainEntity']);

        // Every question in the schema must be visible on the page itself.
        foreach ($faq[0]['mainEntity'] as $q) {
            $this->assertStringContainsString(
                e($q['name']),
                str_replace(['&nbsp;'], ' ', $html),
                'FAQPage schema contains a question the page does not show: ' . $q['name']
            );
        }
    }

    public function test_sitemap_category_urls_match_the_live_pruned_taxonomy(): void
    {
        $activeCount = DB::table('industries')->where('is_active', true)->count();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $categoryUrls = substr_count($xml, '/galerie/secteurs?cat=');

        $this->assertSame(
            $activeCount,
            $categoryUrls,
            'sitemap category URLs must equal the active industries count — after the prune this is the pruned count, never the stale 413'
        );
    }

    public function test_sitemap_carries_lastmod_for_published_content(): void
    {
        $business = $this->makeBusiness();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString($business->slug . '</loc><lastmod>', $xml);
    }

    public function test_llms_txt_is_factual_and_states_private_company_framing(): void
    {
        $response = $this->get('/llms.txt');
        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));

        $body = $response->getContent();
        $this->assertStringContainsString('# Artisan Hub 237', $body);
        $this->assertStringContainsString('not a government body', $body);
        $this->assertStringContainsString('processes no sale payment', $body);
        $this->assertStringContainsString(url('/galerie/entreprises'), $body);
        $this->assertStringContainsString(url('/sitemap.xml'), $body);
    }

    public function test_robots_txt_does_not_block_anything_the_sitemap_lists(): void
    {
        $robots = $this->get('/robots.txt')->assertOk()->getContent();
        $disallowed = [];
        foreach (explode("\n", $robots) as $line) {
            if (str_starts_with(trim($line), 'Disallow:')) {
                $disallowed[] = trim(substr(trim($line), 9));
            }
        }

        $xml = $this->get('/sitemap.xml')->getContent();
        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $locs);

        foreach ($locs[1] as $loc) {
            $path = parse_url(html_entity_decode($loc), PHP_URL_PATH) ?? '/';
            foreach ($disallowed as $rule) {
                if ($rule !== '' && $rule !== '/' && str_starts_with($path, $rule)) {
                    $this->fail("sitemap lists {$loc} but robots.txt disallows {$rule}");
                }
            }
        }
        $this->assertNotEmpty($locs[1]);
    }
}
