<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Support\ArtisanVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the rendered public artisan profile.
 *
 * The companion ArtisanProfileTest guards the data layer: that a figure the
 * platform cannot measure comes back marked unknown. This test guards the last
 * hop, which is where that care is most easily thrown away — a Blade template
 * can take an honest `['known' => false, 'value' => null]` and print "0", and
 * every assertion in the data layer still passes while the page tells a buyer
 * this artisan has sold nothing. So the assertions here are made against the
 * visible text of the response, not against arrays.
 *
 * The larger part of the file is a list of things that must NOT appear. The
 * supplied design carries a trust bar promising secure payments, worldwide
 * shipping and a money-back guarantee; a review average of 4.9 over 128
 * reviews; and a row of SIARC, UNESCO and ministry honours. The platform
 * processes no payments for sales, ships nothing, is not party to the sale and
 * holds no register of national honours — config/legal.php says all four in as
 * many words. A promise the operator cannot honour is worse than a blank
 * space, because a buyer acts on it.
 */
class ArtisanProfilePageTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /**
     * Claims present in the design that the platform is in no position to make.
     * Matched against the page's visible text in both languages.
     */
    private const FORBIDDEN = [
        'Money-back',
        'money-back',
        'Remboursement garanti',
        'Secure payments',
        'Paiements sécurisés',
        '100% secure',
        '100% sécurisé',
        'Worldwide shipping',
        'Livraison mondiale',
        'Buyer protection',
        'Protection acheteur',
        'UNESCO',
        'SIARC Excellence',
    ];

    private function publishedBusiness(array $attrs = []): Business
    {
        return $this->makeBusiness(null, array_merge([
            'status'            => 'published',
            'name_fr'           => 'Atelier Daouda Garga',
            'gan'               => 'AH237-GAN-CM-0000000579',
            'verification_tier' => 'verified',
            'id_verified_at'    => now(),
            'year_established'  => now()->year - 12,
            'address_fr'        => 'Quartier Ndogbong, Douala',
        ], $attrs));
    }

    /** The page as a reader sees it: tags and entities resolved away. */
    private function visibleText(string $html): string
    {
        // Script and style bodies are not visible text, and the Tailwind and
        // Lucide bundles inlined into every page in this codebase contain most
        // English words by accident — including "shipping". Stripping tags
        // without dropping those first produces false failures.
        $html = preg_replace('#<(script|style|template)\b[^>]*>.*?</\1>#is', ' ', $html);

        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);
    }

    private function page(Business $business, string $lang = 'fr'): string
    {
        $response = $this->get("/galerie/entreprises/{$business->slug}?lang={$lang}");
        $response->assertOk();

        return $this->visibleText($response->getContent());
    }

    /* ─────────────────────────── Reachability ──────────────────────────── */

    public function test_a_published_profile_renders(): void
    {
        $business = $this->publishedBusiness();

        $this->get("/galerie/entreprises/{$business->slug}")->assertOk();
    }

    public function test_a_draft_profile_is_not_public(): void
    {
        // Every SIARC import sits in this state, claimable but unclaimed. A
        // draft profile carries a real person's name and trade before they have
        // agreed to be listed, so it must 404 rather than merely look empty.
        $business = $this->makeBusiness(null, ['status' => 'draft']);

        $this->get("/galerie/entreprises/{$business->slug}")->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $business = $this->publishedBusiness();

        foreach (['fr', 'en'] as $lang) {
            $this->get("/galerie/entreprises/{$business->slug}?lang={$lang}")->assertOk();
        }
    }

    /**
     * Most of what this page says is prose returned by ArtisanProfile — the
     * reason a figure is unknown, the name of a certificate, why a piece carries
     * no price. Each of those methods takes a language and defaults to French,
     * so a single missed argument leaves French sentences scattered through the
     * English page. That is invisible to a status-code check and obvious to a
     * reader, which is a bad combination to leave untested.
     */
    public function test_the_english_page_does_not_leak_french_platform_prose(): void
    {
        $business = $this->publishedBusiness();
        $this->makeProduct($business);

        // Scoped to the desktop half. The phone design is a sibling document in
        // pages/businesses/partials/show-mobile.blade.php, owned and tested
        // elsewhere; both halves are present in the DOM at once, and asserting
        // over the whole response would make this test fail for the other file's
        // reasons.
        $html = $this->get("/galerie/entreprises/{$business->slug}?lang=en")->getContent();
        $at   = mb_strpos($html, 'hidden lg:block');
        $this->assertNotFalse($at, 'The desktop wrapper was not found — this test can no longer find what it guards.');

        $text = $this->visibleText(mb_substr($html, $at));

        // Wording owned by the platform, not by the artisan. The artisan's own
        // description and business name are theirs to write in any language and
        // are deliberately not matched here.
        // The month name is included because Carbon's translatedFormat follows
        // the application locale rather than the page's, which is a separate
        // way for the same bug to reappear.
        foreach (["Certificat de vérification", 'Aucun certificat', 'Aucune pièce', "n'a publié d'avis", 'Fiches produits', 'janvier', 'juillet', 'décembre'] as $french) {
            $this->assertStringNotContainsString(
                $french,
                $text,
                "The English page carries the French platform string \"{$french}\" — a lang argument was dropped."
            );
        }
    }

    /* ──────────────────────── What the page must show ──────────────────── */

    public function test_the_page_shows_the_register_s_own_identifiers(): void
    {
        $business = $this->publishedBusiness();

        $text = $this->page($business);

        $this->assertStringContainsString('Atelier Daouda Garga', $text);
        $this->assertStringContainsString('AH237-GAN-CM-0000000579', $text);
    }

    public function test_an_issued_certificate_number_is_printed_verbatim(): void
    {
        $business = $this->publishedBusiness();
        $this->makeProduct($business);

        $avc = ArtisanVerification::forBusiness($business->fresh());

        if (! $avc) {
            $this->markTestSkipped('The verification register declined to issue for this fixture.');
        }

        $text = $this->page($business->fresh());

        $this->assertStringContainsString(
            $avc->certificate_no,
            $text,
            'The certificates strip did not print the number the register issued.'
        );
    }

    /* ────────────────────── Untracked is not the same as zero ──────────── */

    /**
     * The mechanism, asserted where it actually lives.
     *
     * An earlier version of this test read a window of visible text around each
     * caption, and could not tell one tile's figure from its neighbour's — the
     * tiles put the value above the caption, so a window in either direction
     * straddles a boundary. That ambiguity is the same one the page itself has
     * to resolve, so the assertion is made against the markup: the value node
     * for an untracked figure must carry the class the view uses for a stated
     * absence, and must contain no digit at all.
     */
    public function test_an_untracked_statistic_is_named_as_untracked_and_never_printed_as_zero(): void
    {
        $business = $this->publishedBusiness();

        $html = $this->get("/galerie/entreprises/{$business->slug}?lang=en")->getContent();

        // These four have no source: the platform records no orders, no
        // customers, no destination countries and no message response times.
        // "Countries reached" is deliberately absent from this list: the register
        // can measure it (no piece on record has travelled), so zero there is a
        // real count, not a gap. These five have no source at all.
        $untracked = ['Products sold', 'Happy customers', 'Response rate', 'Repeat buyers', 'Last active'];
        $seen = 0;

        foreach ($untracked as $label) {
            $elements = $this->elementsContaining($html, $label);

            if ($elements === []) {
                continue; // The tile may legitimately be omitted entirely.
            }

            foreach ($elements as $element) {
                $seen++;

                $this->assertStringContainsString(
                    'ap-absent',
                    $element,
                    "\"{$label}\" rendered its value as an ordinary figure rather than as a stated absence."
                );

                // Text nodes only. A `basis` tooltip may legitimately mention a
                // year or a count while explaining why nothing is measured, and
                // the class attributes are full of pixel sizes; neither is the
                // figure a reader sees.
                $visible = preg_replace('/\s+/', ' ', strip_tags($element));

                $this->assertDoesNotMatchRegularExpression(
                    '/\d/',
                    $visible,
                    "\"{$label}\" rendered a digit ({$visible}). A counter reading zero is a claim about this "
                    . "artisan's business; a counter the platform does not keep is a claim about the platform, "
                    . 'and the two must not look alike.'
                );
            }
        }

        $this->assertGreaterThan(0, $seen, 'None of the untracked counters were found, so nothing was actually checked.');
    }

    /**
     * Every leaf element (`<li>` or `<div>` with no nested element of its own
     * kind) whose text contains $label, with its attributes stripped.
     *
     * The page uses two shapes for a counter — a tile with the figure above the
     * caption, and a table row with the figure after it — so a directional text
     * window cannot tell one counter's value from its neighbour's. Taking the
     * whole enclosing element removes the ambiguity.
     */
    private function elementsContaining(string $html, string $label): array
    {
        $quoted = preg_quote($label, '/');
        $found  = [];

        foreach (['li', 'div'] as $tag) {
            $pattern = '/<' . $tag . '\b[^>]*>((?:(?!<' . $tag . '\b|<\/' . $tag . '>).)*'
                . $quoted . '(?:(?!<' . $tag . '\b|<\/' . $tag . '>).)*)<\/' . $tag . '>/is';

            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[0] as $match) {
                    $found[] = $match;
                }
            }
        }

        // A wrapper whose children are <section>s satisfies the "no nested tag
        // of my own kind" rule above while being the entire band, so it would be
        // checked as though it were the counter itself. A counter never contains
        // a section, a list or a heading.
        $found = array_values(array_filter(
            $found,
            fn ($el) => ! preg_match('/<(section|ul|h2)\b/i', $el)
        ));

        usort($found, fn ($a, $b) => mb_strlen($a) <=> mb_strlen($b));

        return $found;
    }

    /* ───────────────────────── Claims we cannot make ───────────────────── */

    public function test_the_trust_bar_promises_nothing_the_operator_cannot_honour(): void
    {
        $business = $this->publishedBusiness();

        foreach (['fr', 'en'] as $lang) {
            $text = $this->page($business, $lang);

            foreach (self::FORBIDDEN as $claim) {
                $this->assertStringNotContainsString(
                    $claim,
                    $text,
                    "The {$lang} page carries \"{$claim}\", which config/legal.php states the platform does not do."
                );
            }
        }
    }

    public function test_no_rating_is_shown_while_the_review_table_is_empty(): void
    {
        $business = $this->publishedBusiness();

        $this->assertSame(0, DB::table('business_reviews')->where('business_id', $business->id)->count());

        $text = preg_replace('/\s+/', ' ', $this->page($business, 'en'));

        // The design's figures, and the shape of any star average at all.
        $this->assertStringNotContainsString('4.9', $text);
        $this->assertStringNotContainsString('128 reviews', $text);
        $this->assertDoesNotMatchRegularExpression(
            '/\b[0-5]\.[0-9]\s*\/\s*5\b/',
            $text,
            'A star average was rendered with no rows behind it.'
        );
    }

    public function test_no_external_honour_is_invented_while_the_awards_table_is_empty(): void
    {
        $business = $this->publishedBusiness();

        $this->assertSame(0, DB::table('business_awards')->where('business_id', $business->id)->count());

        $text = $this->page($business, 'en');

        foreach (['Craft Excellence Award', 'Heritage Expo', 'Ministry of Arts'] as $invented) {
            $this->assertStringNotContainsString($invented, $text);
        }
    }

    /* ──────────────────────────── Physical safety ──────────────────────── */

    public function test_the_exact_workshop_coordinates_are_never_rendered(): void
    {
        $business = $this->publishedBusiness([
            'gps_lat' => 4.0510999,
            'gps_lng' => 9.7678700,
        ]);

        $html = $this->get("/galerie/entreprises/{$business->slug}")->getContent();

        // Asserted against the raw HTML, not the visible text: a coordinate
        // hidden in a map URL or a data attribute is just as published as one
        // printed in the heading.
        $this->assertStringNotContainsString('4.0510999', $html);
        $this->assertStringNotContainsString('9.76787', $html);
        $this->assertStringNotContainsString('4.0510', $html);
    }

    public function test_contact_details_absent_from_the_record_are_not_synthesised(): void
    {
        // These belong to a real person. The design shows a phone number and an
        // email address unconditionally; a fabricated one sends a buyer to a
        // stranger, and a plausible one is the most damaging kind.
        $business = $this->publishedBusiness(['phone' => null, 'email' => null]);

        $text = $this->page($business);

        $this->assertDoesNotMatchRegularExpression('/\+237\s?[\d\s]{8,}/', $text);
        $this->assertStringNotContainsString('@mbatchouwoodstudio.com', $text);
    }
}
