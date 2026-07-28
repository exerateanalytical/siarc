<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the mobile app-shell replica of the public artisan profile.
 *
 * The partial is rendered directly rather than through the page route. That is
 * deliberate: the desktop view is owned by another change in flight, and a test
 * that went through the route would fail or pass for reasons that have nothing
 * to do with this file. Rendering the partial with an explicit scope also
 * documents the include contract — `$business`, `$profile` and `$lang` are the
 * whole of what it needs.
 *
 * Most of the assertions below are absences, because the mobile design is where
 * the unsupportable claims are densest. It prints a trust score of 92, a
 * customer rating of 4.9 over 128 reviews, a per-product rating under every
 * card, and an ACHIEVEMENTS tab of SIARC and UNESCO honours. `business_reviews`
 * and `business_awards` are both empty, and the platform has never stored a
 * per-product rating at all — reviews attach to a business. A number invented to
 * fill a slot in a mockup is read by a buyer as a measurement, so each of these
 * must render as an honest absence or not render.
 */
class ArtisanProfileMobileTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** Figures and promises from the mockup that no row in this database supports. */
    private const FORBIDDEN = [
        '4.9',
        '(128 Reviews)',
        '128 Reviews',
        '128 avis',
        '92/100',
        'UNESCO',
        'SIARC Excellence',
        'Money-back',
        'Remboursement garanti',
    ];

    private function publishedBusiness(array $attrs = []): Business
    {
        return $this->makeBusiness(null, array_merge([
            'status'            => 'published',
            'name_fr'           => 'Atelier Daouda Garga',
            'verification_tier' => 'verified',
            'year_established'  => now()->year - 12,
            'address_fr'        => 'Quartier Ndogbong, Douala',
            'gps_lat'           => 4.0511,
            'gps_lng'           => 9.7679,
        ], $attrs));
    }

    /** Renders the partial exactly as the parent page is contracted to do. */
    private function render(Business $business, string $lang = 'fr'): string
    {
        $business->loadMissing(['industry', 'city', 'region', 'products.primaryImage']);

        return view('pages.businesses.partials.show-mobile', [
            'business' => $business,
            'profile'  => null,
            'lang'     => $lang,
        ])->render();
    }

    /** The page as a reader sees it: markup, scripts and entities resolved away. */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(script|style|template)\b[^>]*>.*?</\1>#is', ' ', $html);

        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
    }

    /* ─────────────────────────── Reachability ──────────────────────────── */

    public function test_the_partial_renders_in_french(): void
    {
        $html = $this->render($this->publishedBusiness());

        $this->assertStringContainsString('Atelier Daouda Garga', $html);
        $this->assertStringContainsString('data-mobile-profile', $html);
    }

    public function test_the_partial_renders_in_english(): void
    {
        $business = $this->publishedBusiness(['name_en' => 'Daouda Garga Workshop']);
        $html = $this->render($business, 'en');

        $this->assertStringContainsString('Daouda Garga Workshop', $html);
        $this->assertStringContainsString('CERTIFICATES', strtoupper($this->visibleText($html)));
    }

    /* ───────────────────────── Unsupportable claims ─────────────────────── */

    public function test_no_invented_rating_or_award_appears_in_either_language(): void
    {
        $business = $this->publishedBusiness();
        $this->makeProduct($business, ['name_fr' => 'Masque Fang', 'price_amount' => 180000]);

        foreach (['fr', 'en'] as $lang) {
            $text = $this->visibleText($this->render($business, $lang));

            foreach (self::FORBIDDEN as $claim) {
                $this->assertStringNotContainsString(
                    $claim,
                    $text,
                    "[{$lang}] the mobile profile prints “{$claim}”, which no row in this database supports."
                );
            }
        }
    }

    public function test_the_rating_panel_is_an_honest_empty_state_while_no_reviews_exist(): void
    {
        $text = $this->visibleText($this->render($this->publishedBusiness()));

        // An empty review register is not a rating of zero, and must not be
        // dressed as a star row either.
        $this->assertStringNotContainsString('0/5', $text);
        $this->assertStringNotContainsString('0,0', $text);
        $this->assertMatchesRegularExpression('/Aucun avis|Pas encore/iu', $text);
    }

    public function test_a_statistic_the_platform_does_not_track_says_so_rather_than_zero(): void
    {
        $text = $this->visibleText($this->render($this->publishedBusiness()));

        // The design's STATS tab counts products sold, happy customers,
        // countries reached and a response rate. None of the four is stored.
        $this->assertMatchesRegularExpression('/Non suivi|Non mesuré/iu', $text);
        $this->assertStringNotContainsString('Produits vendus', $text);
        $this->assertStringNotContainsString('Clients satisfaits', $text);
    }

    public function test_no_per_product_rating_is_printed(): void
    {
        $business = $this->publishedBusiness();
        $this->makeProduct($business, ['name_fr' => 'Masque Fang', 'price_amount' => 180000]);

        $text = $this->visibleText($this->render($business));

        // Reviews are held per business. A star under a product card would be
        // an average of a set that does not exist.
        $this->assertDoesNotMatchRegularExpression('/\d[.,]\d\s*\(\d+\)/u', $text);
    }

    /* ────────────────────────────── Privacy ─────────────────────────────── */

    public function test_no_exact_gps_coordinate_is_rendered(): void
    {
        $business = $this->publishedBusiness(['gps_lat' => 4.0511, 'gps_lng' => 9.7679]);
        $html = $this->render($business);

        $this->assertStringNotContainsString('4.0511', $html);
        $this->assertStringNotContainsString('9.7679', $html);
    }

    /* ───────────────────────────── Navigation ───────────────────────────── */

    public function test_the_bottom_navigation_and_tabs_use_resolvable_links(): void
    {
        $html = $this->render($this->publishedBusiness());

        foreach ([route('home'), route('industries.index'), route('certificate.verify'), route('products.index')] as $url) {
            $this->assertStringContainsString($url, $html, "The bottom navigation is missing {$url}.");
        }

        // In-page tabs are anchors to panels that exist; nothing points nowhere.
        $this->assertStringNotContainsString('href="#"', $html);
        preg_match_all('/href="#([A-Za-z0-9\-_]+)"/', $html, $anchors);
        foreach (array_unique($anchors[1]) as $id) {
            $this->assertStringContainsString("id=\"{$id}\"", $html, "Tab anchor #{$id} has no panel.");
        }
    }

    public function test_no_cart_or_fabricated_notification_badge_is_drawn(): void
    {
        $html = $this->render($this->publishedBusiness());
        $text = $this->visibleText($html);

        // The platform holds no cart table and is not party to the sale; a cart
        // icon promises a checkout that does not exist.
        $this->assertStringNotContainsString('shopping-cart', $html);
        // The design's notification bell carries a hardcoded "3".
        $this->assertDoesNotMatchRegularExpression('/\bnotification[^<]{0,20}3\b/i', $text);
    }
}
