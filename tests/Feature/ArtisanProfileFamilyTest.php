<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\BusinessAward;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The cross-surface guard for the artisan profile.
 *
 * This file exists for the same reason CertificateFamilyTest does, and it was
 * written after the same lesson. The certificate family drifted because each
 * document was built by a separate pass of work, each pass was told the same
 * honesty rules, and each pass checked only its own document. The artisan
 * profile is being built the same way — a data layer, a desktop view, a mobile
 * partial and a reviews section, four hands, one page — so the same drift is
 * available to it, and the same kind of sweep is the answer.
 *
 * What makes the profile more dangerous than a certificate is who reads it.
 * A certificate is read after a purchase; the profile is read *before* one, by
 * somebody deciding whether to send money to a stranger in another country.
 * Every reassurance on it is load-bearing.
 *
 * The supplied designs (certificates/artisan profile v2 desktop.png and
 * certificates/artisan mpbile profile v2.png) contain a trust bar promising
 * secure payments, buyer protection with a money-back guarantee, and worldwide
 * shipping. The platform does none of those three things: it processes no
 * payment for a sale, it is not a party to the sale and holds no funds, and it
 * ships nothing. config/legal.php states this. The money-back guarantee is the
 * worst of the three, because unlike a decorative badge a buyer would actually
 * *rely* on it, and would discover it was never real only at the moment
 * something had already gone wrong.
 *
 * The designs also carry roughly twenty numbers — a trust score of 92, a 4.9
 * rating over 128 reviews with a star distribution, 156 products created, 128
 * sold, 96 happy customers, 18 countries, 12 exhibitions, 8 awards, 125
 * positive reviews, a 98% response rate — for a platform whose
 * `business_reviews` and `business_awards` tables are empty and which has no
 * orders and no sales at all. None of them can come from anywhere but a
 * designer's imagination.
 *
 * Matching is on VISIBLE TEXT, never raw HTML, and inside <main>. Both of
 * those are scars. The certificate sweep once passed because it matched the
 * CSS class `.ui-btn-ghost` — a class name is not a claim made to a reader —
 * and it once passed because it matched the shared site footer, which already
 * carries the "not a party to the sale" wording, instead of the page body it
 * was supposed to be judging. So style and script contents are removed, HTML
 * comments are removed, tags are stripped and entities decoded, and the region
 * examined is the page's own <main>, because a promise printed in the profile
 * body is the profile's promise and a disclaimer in the site chrome does not
 * excuse it.
 *
 * Finally: this guard is deliberately written to run before the page it guards
 * is finished. Four other passes hold the files it reads. Anything not yet
 * present is skipped with a stated reason rather than reported as a failure,
 * so that an unbuilt partial never masquerades as a broken one.
 */
class ArtisanProfileFamilyTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /**
     * Promises the platform has no power to keep.
     *
     * Each of these is a specific undertaking to a buyer, not a mood. "Secure
     * payments" and "100% secure" describe a payment rail the platform does not
     * operate for sales. "Buyer protection" and "money-back" describe a
     * remedy against a party that never held the money. "Worldwide shipping"
     * and "safe & reliable delivery" describe a logistics operation that does
     * not exist. A reader cannot tell a decorative badge from a term of
     * service, and should not have to.
     */
    private const UNSUPPORTABLE_PROMISES = [
        'Money-back', 'money back', 'Remboursement garanti', 'Satisfait ou remboursé',
        'Buyer protection', 'Protection acheteur', 'Protection de l\'acheteur',
        'Secure payments', 'Secure payment', 'Paiement sécurisé', 'Paiements sécurisés',
        '100% secure', '100% sécurisé', '100 % sécurisé',
        'Worldwide shipping', 'Livraison mondiale', 'Livraison internationale',
        'Safe & reliable delivery', 'Livraison rapide & fiable', 'Livraison sûre',
    ];

    /**
     * "Guarantee" is not banned as a word — an artisan may truthfully guarantee
     * their own work, and a French page may say "artisanat garanti fait main"
     * about a craft. What is banned is the platform guaranteeing something on
     * the artisan's behalf, so the test looks for the word only in the company
     * of a thing the platform cannot stand behind.
     */
    private const GUARANTEE_WORDS = ['guarantee', 'guaranteed', 'garantie', 'garanti', 'garantit'];

    private const GUARANTEE_OBJECTS = [
        'money', 'refund', 'remboursement', 'argent', 'payment', 'paiement',
        'delivery', 'livraison', 'shipping', 'expédition', 'satisfaction',
        'quality', 'qualité', 'premium', 'transaction', 'purchase', 'achat',
    ];

    /**
     * Bodies whose names on a profile borrow authority nobody granted.
     *
     * Unlike the certificate sweep, SIARC is included here in its endorsement
     * form only. "SIARC Excellence" is an award that does not exist; a plain
     * mention of SIARC as a fair an artisan attended is a fact and stays legal.
     * The rule below is therefore conditional in a way the others are not: a
     * name may appear IF and ONLY IF business_awards genuinely holds a row that
     * names it, which is why this test asserts both states of the world.
     */
    private const EXTERNAL_HONOURS = [
        'UNESCO', 'SIARC Excellence', 'MINAC', 'Ministry of Arts', 'Ministère des Arts',
        'National Craft Award', 'Prix National de l\'Artisanat',
    ];

    /**
     * The numbers the designs invent.
     *
     * These are matched as bare tokens on a page built from an empty database,
     * which is the only condition under which such a match is unambiguous: with
     * no reviews, no orders and no awards, a "4.9" or a "(128" on the page came
     * from a hard-coded fixture and from nowhere else.
     */
    private const FABRICATED_FIGURES = [
        '4.9', '(128', '128 avis', '128 reviews', '156 ', ' 96 ', ' 18 pays',
        '18 countries', '12 exhibitions', '12 expositions', '8 awards', '8 prix',
        '125 positive', '98%', '92/100', '92 / 100',
    ];

    /**
     * Statistics the platform does not track at all, with the label each design
     * gives them. A label that appears must be answered with untracked wording,
     * never with a number — least of all with 0, which is a claim in itself.
     */
    private const UNTRACKED_STAT_LABELS = [
        'Products sold', 'Produits vendus', 'Happy customers', 'Clients satisfaits',
        'Response rate', 'Taux de réponse', 'Last Active', 'Dernière activité',
        'Repeat buyers', 'Acheteurs fidèles',
        // Exhibitions and countries reached are deliberately absent, and this
        // was the third correction to this list. Both were assumed untracked
        // from reading the design, and both are in fact counted:
        // ArtisanProfile::statistics() derives exhibitions from
        // provenance_events of type `exhibition` and countriesReached() from
        // the countries those events name. A zero under either is therefore a
        // measurement — this artisan has been shown nowhere yet — and banning
        // it would have forced the page to hide a fact it legitimately knows.
        // The distinction this whole rule turns on is not "is the number
        // small" but "is there a query behind it".
        //
        // The trust score is deliberately absent from this list, and it took
        // two wrong drafts to get there. Banning the words outright was the
        // first, and the mobile partial failed it for printing "TRUST SCORE —
        // not tracked", which is an honest sentence. Listing it as untracked
        // was the second, and the desktop view failed that for printing
        // "Indice de confiance 10/85" beside "Comment il est calculé" and a
        // per-check breakdown.
        //
        // The desktop is right. ArtisanProfile::trustScore() genuinely computes
        // it out of the verification checks in ArtisanVerification::checksFor()
        // and hands back a `basis` string per line, so it is a measurement that
        // shows its working — the one kind of score this platform may print.
        // The design's 92/100 stays banned above, because that figure is not
        // that computation's output; it is a number from a mockup.
    ];

    /**
     * The vocabulary the rest of this codebase already uses to say "we do not
     * know this", established on the workshop-verification certificate and the
     * onboarding form. A statistic must land on one of these, not on a zero.
     */
    private const UNTRACKED_WORDING = [
        'Non renseigné', 'Non suivi', 'Non disponible', 'Not recorded', 'Not tracked',
        'Not available', 'Not provided', 'Non comptabilisé', 'Not counted',
    ];

    /* ─────────────────────────────── Fixtures ───────────────────────────── */

    /**
     * A published artisan with a real GAN and a real GPS fix.
     *
     * The coordinates are set deliberately: rule 5 is worthless against a
     * business that has no location to leak in the first place.
     */
    private function publishedArtisan(): Business
    {
        $business = $this->makeBusiness(null, [
            'status'  => 'published',
            'gps_lat' => 4.0511234,
            'gps_lng' => 9.7678912,
        ]);

        ProvenanceRegistry::ganFor($business);

        return $business->fresh();
    }

    private function url(Business $business, string $lang): string
    {
        return '/galerie/entreprises/' . $business->slug . '?lang=' . $lang;
    }

    /**
     * The surfaces this guard sweeps.
     *
     * The desktop page is fetched over HTTP because that is how a reader gets
     * it. The mobile partial is a Blade include with no route of its own, so
     * where it exists it is rendered directly against the same view data the
     * page uses; where it does not exist yet, it is skipped by name so the
     * reason is visible in the output rather than silently absent.
     */
    private function surfaces(Business $business, string $lang): array
    {
        $surfaces = ['desktop' => $this->documentText($this->get($this->url($business, $lang))->assertOk()->getContent())];

        // The desktop view includes the mobile partial in the same response on
        // every build seen so far, so rendering it separately would double the
        // assertions for nothing. It gets its own entry only if it exists and
        // the page does NOT already carry it — the case where the partial has
        // been written but not yet wired in, which is exactly when an
        // unreviewed banned string would hide there unnoticed.
        if (view()->exists('pages.businesses.partials.show-mobile')) {
            $included = str_contains(
                (string) @file_get_contents(resource_path('views/pages/businesses/show.blade.php')),
                'partials.show-mobile'
            );

            if (! $included) {
                // The partial exists but the desktop page does not include it,
                // so nothing else in this suite ever looks at it. It is
                // rendered here against the data its own published contract
                // asks for, exactly as the parent would render it.
                //
                // The first version of this branch read the Blade source as
                // text instead, and it was wrong twice over. The file opens
                // with a long {{-- --}} block honestly listing which of the
                // design's claims it refuses to print — so it quotes "UNESCO",
                // "4.9" and "128 reviews" in the course of refusing them — and
                // it carries a /* */ note about a trustScore() helper. Neither
                // reaches a browser. A guard that fails a file for documenting
                // its own discipline teaches the next author to delete the
                // documentation, which is the opposite of the point. Rendering
                // asks the only question that matters: what does a reader see.
                $surfaces['mobile-orphan'] = $this->visibleText(
                    view('pages.businesses.partials.show-mobile', [
                        'business' => $business,
                        'lang'     => $lang,
                        'profile'  => null,
                    ])->render()
                );
            }
        }

        return $surfaces;
    }

    /* ─────────────────────────────── Text tools ─────────────────────────── */

    /** What a reader actually sees. */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(style|script)\b[^>]*>.*?</\1>#is', ' ', $html);
        $html = preg_replace('#<!--.*?-->#s', ' ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/\s+/u', ' ', $text);
    }

    /**
     * The profile body, without the site's header and footer.
     *
     * Scoped with string offsets rather than a regular expression, and with no
     * silent fallback to the whole page. The certificate sweep was caught doing
     * exactly that: its pattern failed, it widened to the full document, and
     * every sheet then passed on the strength of the shared footer — which
     * already contains the "not a party to the sale, processes no payments"
     * wording these rules look for. A guard that passes because of chrome it
     * was not judging is worse than no guard.
     */
    private function documentText(string $html): string
    {
        $open = strpos($html, '<main');
        $end  = strrpos($html, '</main>');

        if ($open === false || $end === false || $end <= $open) {
            $this->fail('The artisan profile has no <main> to scope these rules to; every rule here would otherwise silently widen to the site chrome.');
        }

        $open = strpos($html, '>', $open);

        return $this->visibleText(substr($html, $open + 1, $end - $open - 1));
    }

    /* ───────────────── Rule 1 — no promise the platform cannot keep ─────── */

    public function test_no_surface_makes_a_promise_the_platform_cannot_keep(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            foreach ($this->surfaces($business, $lang) as $surface => $text) {
                foreach (self::UNSUPPORTABLE_PROMISES as $promise) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $promise,
                        $text,
                        $surface . ' (' . $lang . ') promises "' . $promise . '". The platform processes no payment for a sale, is not a party to it, holds no funds and ships nothing.'
                    );
                }
            }
        }
    }

    /**
     * The guarantee rule, separated because the word alone is innocent.
     *
     * A failure here means the page used "guarantee" or "garantie" within a
     * short reach of money, delivery, satisfaction or quality — the four things
     * a marketplace guarantee normally means and none of which this platform
     * can back.
     */
    public function test_no_surface_guarantees_something_the_platform_does_not_control(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            foreach ($this->surfaces($business, $lang) as $surface => $text) {
                $lower = mb_strtolower($text);

                foreach (self::GUARANTEE_WORDS as $word) {
                    $offset = 0;

                    while (($pos = mb_strpos($lower, $word, $offset)) !== false) {
                        $window = mb_substr($lower, max(0, $pos - 60), 140);
                        $offset = $pos + 1;

                        // A denied guarantee is the opposite of a made one, and
                        // is exactly the sentence this project wants on the
                        // page. The desktop profile prints "ce n'est ni une
                        // garantie de qualité des produits, ni une garantie de
                        // bonne exécution d'une commande" — the clearest
                        // statement of the platform's position anywhere on the
                        // profile — and the first draft of this rule failed it
                        // for containing "garantie" beside "qualité". Firing on
                        // a disclaimer would have pressured the next author to
                        // delete the disclaimer to make the build green, which
                        // is the single worst outcome this guard could cause.
                        if (preg_match('/(ni une|n\'est ni|n\'est pas|pas une|aucune|sans garantie|ne garantit|does not|is not a|no guarantee|not a guarantee|neither)/u', $window)) {
                            continue;
                        }

                        foreach (self::GUARANTEE_OBJECTS as $object) {
                            $this->assertStringNotContainsString(
                                $object,
                                $window,
                                $surface . ' (' . $lang . ') puts "' . $word . '" beside "' . $object . '", which reads as a platform guarantee: "…' . $window . '…"'
                            );
                        }
                    }
                }
            }
        }
    }

    /* ───────────────── Rule 2 — no borrowed external honour ─────────────── */

    /**
     * With business_awards empty, no external body may be named at all.
     */
    public function test_no_surface_names_an_external_honour_the_database_does_not_hold(): void
    {
        $this->assertSame(0, BusinessAward::count(), 'This rule is only meaningful against an empty awards table.');

        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            foreach ($this->surfaces($business, $lang) as $surface => $text) {
                foreach (self::EXTERNAL_HONOURS as $honour) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $honour,
                        $text,
                        $surface . ' (' . $lang . ') names "' . $honour . '" with no row in business_awards behind it.'
                    );
                }
            }
        }
    }

    /**
     * The other state of the world, and the reason the rule above is not a
     * blanket ban.
     *
     * A negative assertion on an empty table proves nothing about whether the
     * page can render a real award — an award section that was never built
     * passes the test above perfectly. So: give the artisan a genuine award row
     * naming a body, and require it to appear. If this fails while the rule
     * above passes, the profile is not refusing fabricated honours, it is
     * simply incapable of showing honours at all, and the first rule is
     * testing an absent section.
     */
    public function test_a_genuine_award_row_is_shown_so_the_refusal_above_is_not_testing_an_absent_section(): void
    {
        if (! view()->exists('pages.businesses.partials.awards') && ! $this->viewMentions('show.blade.php', 'award')) {
            $this->markTestSkipped('The awards section has not been built yet by the pass that holds it; its absence is not a failure of this guard.');
        }

        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            // Recorded through ArtisanAwards::record() rather than inserted, so
            // this fixture is subject to the same refusals a real award is: a
            // named issuing body, and a recorder who is not the beneficiary.
            // An award the register itself would reject is not evidence that
            // the profile renders real awards.
            \App\Support\ArtisanAwards::record($business, [
                'title_fr'     => 'Distinction du salon',
                'title_en'     => 'Fair distinction',
                'issuer'       => 'Chambre des Métiers de Douala',
                'year'         => 2025,
                'recorded_by'  => $this->makeUser()->id,
                'evidence_url' => 'https://example.test/attestation.pdf',
            ]);

            $text = $this->documentText($this->get($this->url($business, $lang))->assertOk()->getContent());

            $this->assertStringContainsString(
                $lang === 'fr' ? 'Distinction du salon' : 'Fair distinction',
                $text,
                'A real award row exists for this artisan but the profile (' . $lang . ') does not render it, so the refusal rule above is passing against an empty section rather than a disciplined one.'
            );
        }
    }

    /* ───────────────── Rule 3 — no fabricated statistic ─────────────────── */

    public function test_no_surface_prints_a_statistic_the_database_cannot_produce(): void
    {
        $this->assertSame(0, \App\Modules\Businesses\Models\BusinessReview::count(), 'This rule is only meaningful with no reviews on record.');

        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            foreach ($this->surfaces($business, $lang) as $surface => $text) {
                foreach (self::FABRICATED_FIGURES as $figure) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $figure,
                        $text,
                        $surface . ' (' . $lang . ') prints "' . $figure . '" for an artisan with no reviews, no orders and no awards. There is no query that returns it.'
                    );
                }

                // The star distribution from the designs: five rows, each a
                // star level and a count, packed together under the rating.
                // With an empty review table there is nothing to distribute.
                //
                // Matched on the shape rather than on a run of digits. A first
                // version of this assertion looked for five numbers in a row
                // after stripping everything non-numeric, and fired on a page
                // whose only sin was having a year, a product count and a
                // percentage scattered across four hundred characters of
                // unrelated prose — numbers far apart on a page are not a
                // distribution, and a rule that cries wolf gets deleted.
                preg_match_all('/\b[1-5]\s*(★|☆|étoiles?|stars?)\b\D{0,20}\d/iu', $text, $stars);

                $this->assertLessThan(
                    3,
                    count($stars[0]),
                    $surface . ' (' . $lang . ') shows a per-star review breakdown for an artisan with no reviews at all.'
                );
            }
        }
    }

    /* ───────────── Rule 4 — a thing not tracked is not a thing at zero ──── */

    /**
     * This is the rule the certificate family learned the hard way, in the
     * words the workshop certificate settled on: an unrecorded headcount is not
     * a headcount of nought. A profile that answers "Products sold" with 0
     * tells a buyer this artisan has never sold anything, which is a factual
     * claim about a person's livelihood that the platform has no basis for —
     * it has no orders table at all. The honest answer is that it is not
     * tracked, and the assertion has two halves because only one of them
     * catches the bug: the untracked wording must be there, AND a bare zero
     * must not be standing in for it.
     */
    public function test_a_statistic_the_platform_does_not_track_says_so_and_does_not_render_as_zero(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();

            foreach ($this->surfaces($business, $lang) as $surface => $text) {
                $present = array_values(array_filter(
                    self::UNTRACKED_STAT_LABELS,
                    fn ($label) => mb_stripos($text, $label) !== false
                ));

                if ($present === []) {
                    // The strongest possible answer to an untracked statistic
                    // is not to print the label at all, and a page that took
                    // that route has nothing to disclaim.
                    continue;
                }

                $valueFirst = $this->valueComesFirst($text, $present);

                foreach ($present as $label) {
                    $window = $this->statisticCell($text, $label, $valueFirst);

                    $saysUntracked = false;
                    foreach (self::UNTRACKED_WORDING as $wording) {
                        if (mb_stripos($window, $wording) !== false) {
                            $saysUntracked = true;
                            break;
                        }
                    }

                    $this->assertTrue(
                        $saysUntracked,
                        $surface . ' (' . $lang . ') shows the statistic "' . $label . '" without saying it is untracked: "…' . $window . '…"'
                    );

                    // No figure at all in the cell — not a zero, and not
                    // anything else either. Zero is the most misleading answer
                    // because it reads as a measurement of nothing rather than
                    // an absence of measurement, but a 92 or a 98% standing
                    // where the measurement would go is the same fabrication
                    // wearing a more plausible face.
                    $this->assertDoesNotMatchRegularExpression(
                        '/\d/u',
                        $window,
                        $surface . ' (' . $lang . ') answers the untracked statistic "' . $label . '" with a figure. A statistic the platform does not track is not a statistic at zero: "…' . $window . '…"'
                    );
                }
            }
        }
    }

    /* ──────────────────── Rule 5 — no exact GPS on a public page ────────── */

    /**
     * The workshop coordinates are a home address in most cases. They are
     * collected so the platform can place a business in a city and verify a
     * visit, not so a stranger reading a public page can drive to it. A city
     * and a region are the public granularity; a decimal fix is not.
     */
    public function test_no_surface_publishes_the_artisans_exact_coordinates(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();
            $html     = $this->get($this->url($business, $lang))->assertOk()->getContent();

            // Checked against the RAW html, not the visible text: a coordinate
            // leaked into a map data-attribute or a query string is just as
            // published as one printed in a paragraph.
            foreach ([$business->gps_lat, $business->gps_lng] as $coordinate) {
                $this->assertStringNotContainsString(
                    rtrim(rtrim(number_format((float) $coordinate, 4, '.', ''), '0'), '.'),
                    $html,
                    'The profile (' . $lang . ') publishes the artisan\'s exact coordinate ' . $coordinate . '.'
                );
            }
        }
    }

    /* ──────────────────── Rule 6 — no placeholder links ─────────────────── */

    /**
     * A nav item, tab or action button wired to href="#" looks live and does
     * nothing. On a profile these are the contact and enquiry controls, so a
     * dead one is a buyer who believes they made contact and did not.
     */
    public function test_no_surface_carries_a_link_that_goes_nowhere(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();
            $html     = $this->get($this->url($business, $lang))->assertOk()->getContent();

            $open = strpos($html, '<main');
            $end  = strrpos($html, '</main>');
            $body = substr($html, $open, $end - $open);

            preg_match_all('/<a\b[^>]*href\s*=\s*"#"[^>]*>/i', $body, $m);

            // A bare "#" is only acceptable on something that is genuinely
            // driven by script and announces itself as a control, so the
            // anchors keeping a role or an x-on/@click handler are allowed
            // through and everything else is a dead link.
            $dead = array_values(array_filter(
                $m[0],
                fn ($tag) => ! preg_match('/(@click|x-on:click|onclick|role\s*=\s*"button"|data-tab|aria-controls)/i', $tag)
            ));

            $this->assertSame(
                [],
                $dead,
                'The profile (' . $lang . ') has ' . count($dead) . ' link(s) inside <main> pointing at "#" with nothing behind them: ' . implode(' | ', $dead)
            );
        }
    }

    /* ───────────── Rule 7 — the page exists, in both languages ──────────── */

    public function test_the_profile_renders_published_and_refuses_draft_in_both_languages(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $published = $this->publishedArtisan();
            $this->get($this->url($published, $lang))->assertOk();

            $draft = $this->makeBusiness(null, ['status' => 'draft']);
            $this->get($this->url($draft, $lang))->assertNotFound();
        }
    }

    /**
     * The GAN is the one identifier on the profile that a reader can carry
     * elsewhere and check. It has to be the artisan's real one, out of the
     * database, not a formatted example.
     */
    public function test_the_profile_shows_the_artisans_real_gan(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();
            $text     = $this->documentText($this->get($this->url($business, $lang))->assertOk()->getContent());

            $this->assertStringContainsString(
                $business->gan,
                $text,
                'The profile (' . $lang . ') does not print the artisan\'s registered GAN ' . $business->gan . '.'
            );
        }
    }

    /* ───────────────────────── Accessibility floor ──────────────────────── */

    public function test_every_image_on_the_profile_has_an_alt_attribute(): void
    {
        foreach (['fr', 'en'] as $lang) {
            $business = $this->publishedArtisan();
            $html     = $this->get($this->url($business, $lang))->assertOk()->getContent();

            preg_match_all('/<img\b[^>]*>/i', $html, $tags);

            foreach ($tags[0] as $tag) {
                $this->assertMatchesRegularExpression(
                    '/\balt\s*=/i',
                    $tag,
                    'The profile (' . $lang . ') has an image with no alt attribute: ' . $tag
                );
            }
        }
    }

    /* ───────────────────────────── Helpers ──────────────────────────────── */

    /**
     * The stretch of text that belongs to one statistic, and to no other.
     *
     * Reading a fixed number of characters forward from the label was the first
     * attempt and it was wrong in a way worth recording, because it accused the
     * wrong statistic. The desktop stat strip renders value-then-label:
     *
     *     0 Produits créés  Non suivi Produits vendus  0 Expositions
     *
     * so a window running forward from "Produits vendus" swallowed the zero
     * that belongs to "Expositions" and reported an honest cell as a lying one.
     * Reading backwards instead would have failed the mirror-image layout on
     * the mobile card, where the label sits above its value.
     *
     * So the cell is bounded by its neighbours rather than by a character
     * count, AND it is read in the direction the surface actually lays its
     * statistics out. The orientation is not guessed: $valueFirst is worked out
     * per surface by looking at where the untracked wording physically sits
     * relative to the label it answers. A strip reading "Non suivi Produits
     * vendus" puts the value before the label, so only what precedes the label
     * belongs to it; a card reading "Products sold — Not tracked" is the other
     * way round. Getting this wrong does not merely miss a bug, it accuses an
     * honest cell of its neighbour's zero, and a guard that blames the innocent
     * is one somebody eventually switches off.
     */
    private function statisticCell(string $text, string $label, bool $valueFirst): string
    {
        $pos = mb_stripos($text, $label);
        $end = $pos + mb_strlen($label);

        $before = max(0, $pos - 45);
        $after  = min(mb_strlen($text), $end + 45);

        // Neighbours include the statistics the platform *does* compute. They
        // stand beside the untracked ones in the same strip, and their figures
        // are legitimate, so they have to bound the cell or an honest "Produits
        // créés 0" next door gets read as the answer to "Produits vendus".
        $neighbours = array_merge(self::UNTRACKED_STAT_LABELS, [
            'Produits créés', 'Products created', 'Produits', 'Products',
            'Distinctions', 'Awards', 'Avis', 'Reviews',
            'Membre depuis', 'Member since', 'Indice de confiance', 'Trust score',
        ]);

        foreach ($neighbours as $other) {
            if (mb_strtolower($other) === mb_strtolower($label)) {
                continue;
            }

            $offset = 0;
            while (($at = mb_stripos($text, $other, $offset)) !== false) {
                $offset = $at + 1;
                $otherEnd = $at + mb_strlen($other);

                if ($otherEnd <= $pos && $otherEnd > $before) {
                    $before = $otherEnd;
                }

                if ($at >= $end && $at < $after) {
                    $after = $at;
                }
            }
        }

        return trim($valueFirst
            ? mb_substr($text, $before, $pos - $before)
            : mb_substr($text, $end, $after - $end));
    }

    /**
     * Whether this surface prints the figure before its label.
     *
     * Decided from the page's own output rather than assumed: if any untracked
     * wording ends within a few characters of the start of a statistic label,
     * the value leads. Defaults to label-first, which is the more common
     * layout and the safer default because it reads forward into text the page
     * definitely owns.
     */
    private function valueComesFirst(string $text, array $labels): bool
    {
        foreach ($labels as $label) {
            $pos = mb_stripos($text, $label);

            if ($pos === false) {
                continue;
            }

            $preceding = mb_substr($text, max(0, $pos - 20), min(20, $pos));

            foreach (self::UNTRACKED_WORDING as $wording) {
                if (mb_stripos($preceding, $wording) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function viewMentions(string $file, string $needle): bool
    {
        $path = resource_path('views/pages/businesses/' . $file);

        return is_file($path) && stripos((string) file_get_contents($path), $needle) !== false;
    }
}
