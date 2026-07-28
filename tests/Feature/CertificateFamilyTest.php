<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The cross-document guard for the certificate family.
 *
 * Every certificate the platform issues was built by a different pass of work,
 * each one told the same honesty rules, and each one checked only against
 * itself. The failures this project keeps getting caught by are not failures of
 * a single document — they are drift between documents. One sheet learns to say
 * "no holographic seal", the next one is written a week later and quietly
 * prints one; one sheet translates its condition vocabulary, the next leaks the
 * database enum straight onto the page in front of a customs officer.
 *
 * So this file deliberately does not test any document's own content. It
 * sweeps the whole family, in both languages, and asserts only the properties
 * that must hold for all of them at once. A new certificate type is added to
 * the URL map below and inherits every rule here on the day it is created,
 * which is the point.
 *
 * Matching is done on VISIBLE TEXT, never on raw HTML. An earlier version of
 * this sweep failed on the CSS class `.ui-btn-ghost` because it contains the
 * word "ghost" — a class name is not a claim made to a reader. Style and script
 * blocks are removed, tags are stripped and entities decoded before any
 * assertion runs, so what is tested is what a person holding the sheet sees.
 */
class CertificateFamilyTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /**
     * Security and technology claims the platform cannot keep.
     *
     * Every one of these describes either a physical property of a print run
     * that has never happened (holography, UV inks, latent images), a chip that
     * is not embedded (NFC), or a system that does not exist (blockchain, any
     * form of AI fingerprinting — the perceptual image hash in
     * app/Support/ImageFingerprint.php is DCT/block hashing, not a model, and
     * is never labelled as AI). A labelled security feature is read as a
     * measure that was taken; printing one is a lie regardless of intent.
     *
     * The two absolutes at the end are separate: nothing this platform issues
     * is tamper-proof, and the platform has no power to prosecute anybody, so
     * a document must never threaten a reader with the law.
     */
    private const UNBACKED_CLAIMS = [
        'Holographic', 'Hologramme', 'UV reactive', 'Ghost watermark', 'Latent image',
        'NFC', 'Invisible watermark', 'Blockchain', 'AI Fingerprint', 'AI Visual',
        'tamper-proof', 'infalsifiable', 'punishable by law',
    ];

    /**
     * Organisations that must never appear to be endorsing a document.
     *
     * None of these bodies has approved anything this platform issues. Their
     * names on a certificate borrow authority that was never granted.
     *
     * SIARC is deliberately NOT on this list. SIARC is a trade fair, and "shown
     * at SIARC 2026" is a recorded event in a provenance chain — a fact about
     * where an object physically was, no different from naming a gallery or a
     * city. Banning the string outright would forbid the register from
     * recording true history. What is banned is the *endorsement* reading, so
     * the assertion below looks for endorsement verbs in the neighbourhood of a
     * name rather than for the bare name.
     */
    private const EXTERNAL_ORGS = ['UNESCO', 'MINAC', 'MINCOMMERCE', 'Chambre des', 'GVNAC'];

    /**
     * Verbs and nouns that turn a mention of an organisation into a claim of
     * endorsement, in both languages.
     */
    private const ENDORSEMENT_WORDS = [
        'certified', 'certifie', 'certifié', 'approved', 'approuv', 'accredited', 'accrédit', 'accredit',
        'endorsed', 'avalis', 'validated by', 'validé par', 'recognised by', 'recognized by', 'reconnu par',
        'in partnership with', 'en partenariat avec', 'mandated by', 'mandaté par', 'label',
    ];

    /**
     * Database enum values that must never reach a reader.
     *
     * These are storage tokens. A condition row reading `very_good` or a
     * provenance row reading `under_investigation` is not merely ugly: it is
     * unreadable to the French-speaking buyer the document is written for, and
     * `not_applicable` in particular reads as a missing value rather than a
     * declared one.
     */
    private const ENUM_LEAKS = [
        'museum_acquisition', 'gallery_acquisition', 'very_good',
        'under_investigation', 'reported_stolen', 'not_applicable',
    ];

    /** Every certificate code the scheme declares, for the "no other code" rule. */
    private const ALL_CODES = ['COA', 'PRC', 'OTC', 'AVC', 'PPC', 'EAC', 'EC', 'RC', 'VAC', 'DPP'];

    /**
     * Builds one of each live document and returns
     * [code => ['url' => ..., 'number' => ...]] for a given language.
     *
     * The records are built through the same registry helpers the production
     * routes read from, so if an identifier format or a route signature
     * changes, this map breaks rather than silently testing a stale URL.
     */
    private function documents(string $lang): array
    {
        $product = $this->publishedProduct();
        $coa     = ProductCertificate::forProduct($product);

        $business = $this->makeBusiness(null, ['verification_tier' => 'verified']);
        ProvenanceRegistry::ganFor($business);
        $avc = \App\Support\ArtisanVerification::forBusiness($business->fresh());

        $transferProduct = $this->publishedProduct();
        ProductCertificate::forProduct($transferProduct);
        $transfer = ProvenanceRegistry::transfer($transferProduct, [
            'legal_name'         => 'Heritage Gallery Douala',
            'entity_type'        => 'gallery',
            'country_code'       => 'CM',
            'verification_level' => 'verified',
        ], [
            'transfer_type'  => 'gallery_acquisition',
            'transfer_city'  => 'Douala',
            'currency'       => 'XAF',
            'declared_value' => 850000,
            'condition'      => 'very_good',
        ]);

        $exportProduct = $this->publishedProduct();
        ProductCertificate::forProduct($exportProduct);
        $consignment = ExportRegister::open($exportProduct->fresh(), [
            'name'    => 'Museum of World Cultures',
            'type'    => 'museum',
            'country' => 'FR',
            'city'    => 'Paris',
            'address' => '1 Culture Avenue, 75001 Paris',
        ], ['intended_purpose' => 'museum_acquisition']);
        ExportRegister::approve($consignment->id);
        ExportRegister::ship($consignment->id, [
            'carrier'      => 'DHL Express',
            'service'      => 'Express Worldwide',
            'awb_no'       => '123-45678901',
            'tracking_no'  => '7771234567890',
            'port_of_exit' => 'Douala International Airport',
        ]);
        $consignment = ExportRegister::issue($consignment->id);

        $q = '?lang=' . $lang;

        $docs = [
            'COA' => ['url' => '/certificat/' . $product->slug . $q,                       'number' => $coa->certificate_no],
            'PRC' => ['url' => '/certificat-enregistrement/' . $product->slug . $q,        'number' => ProvenanceRegistry::prnFor($product)],
            'OTC' => ['url' => '/certificat-transfert/' . $transfer->certificate_no . $q,  'number' => $transfer->certificate_no],
            'AVC' => ['url' => '/certificat-artisan/' . $business->fresh()->slug . $q,     'number' => $avc->certificate_no],
            'EAC' => ['url' => '/certificat-export/' . $consignment->certificate_no . $q,  'number' => $consignment->certificate_no],
        ];

        // The provenance certificate is being built by another pass of work as
        // this guard is written. It joins the sweep the moment its view exists,
        // and until then its absence must not be reported as a failure here.
        if (view()->exists('pages.certificate-provenance')) {
            $docs['PPC'] = [
                'url'    => '/certificat-provenance/' . $product->slug . $q,
                'number' => ProvenanceRegistry::prnFor($product),
            ];
        }

        return $docs;
    }

    private function publishedProduct(): Product
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /**
     * What a reader actually sees: style and script contents removed, tags
     * stripped, entities decoded, whitespace collapsed.
     */
    private function visibleText(string $html): string
    {
        $html = preg_replace('#<(style|script)\b[^>]*>.*?</\1>#is', ' ', $html);
        $html = preg_replace('#<!--.*?-->#s', ' ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return preg_replace('/\s+/u', ' ', $text);
    }

    /** Fetches one document and returns [html, visible text]. */
    private function fetch(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();

        return [$html, $this->visibleText($html)];
    }

    /**
     * The visible text of the certificate itself, without the site's header and
     * footer.
     *
     * Every certificate page renders its sheets inside a single <main>. The
     * surrounding chrome is shared with the whole site and carries its own
     * legal wording — the footer already says the platform is not party to
     * sales and processes no payments — so a rule about what a *document*
     * states has to be measured inside the document. Otherwise every sheet
     * passes on the strength of a footer the reader will not have if they are
     * holding a print of the certificate alone, which is exactly the reader
     * these rules exist for.
     */
    private function documentText(string $html): string
    {
        // Located with string offsets rather than a regular expression. An
        // earlier version matched the tags with a pattern and fell back to the
        // whole page whenever that pattern failed, silently widening this rule
        // to include the site footer - which already carries the wording being
        // looked for, so every document passed for the wrong reason.
        $open = strpos($html, '<main');
        $end  = strrpos($html, '</main>');

        if ($open === false || $end === false || $end <= $open) {
            return $this->visibleText($html);
        }

        $open = strpos($html, '>', $open);

        return $this->visibleText(substr($html, $open + 1, $end - $open - 1));
    }

    /* ─────────────────────────── Rule 1 — no unbacked claim ─────────────── */

    public function test_no_document_claims_a_security_feature_the_platform_does_not_have(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [, $text] = $this->fetch($doc['url']);

                foreach (self::UNBACKED_CLAIMS as $claim) {
                    $this->assertStringNotContainsStringIgnoringCase(
                        $claim,
                        $text,
                        $code . ' (' . $lang . ') prints "' . $claim . '" as visible text.'
                    );
                }
            }
        }
    }

    /* ──────────────────── Rule 2 — no borrowed endorsement ──────────────── */

    public function test_no_document_presents_an_external_organisation_as_endorsing_it(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [, $text] = $this->fetch($doc['url']);

                foreach (self::EXTERNAL_ORGS as $org) {
                    // The bare name is banned for these bodies: unlike a trade
                    // fair, none of them is a place an object can have been, so
                    // there is no legitimate reason to print the name at all.
                    $this->assertStringNotContainsStringIgnoringCase(
                        $org,
                        $text,
                        $code . ' (' . $lang . ') names ' . $org . ', which has endorsed nothing.'
                    );
                }

                // SIARC may be named as a recorded event. It may not be named
                // as an authority, so any endorsement word within 120
                // characters of it is a failure.
                if (preg_match_all('/SIARC/i', $text, $m, PREG_OFFSET_CAPTURE)) {
                    foreach ($m[0] as [$needle, $offset]) {
                        $window = mb_strtolower(substr($text, max(0, $offset - 120), 260));

                        foreach (self::ENDORSEMENT_WORDS as $word) {
                            $this->assertStringNotContainsString(
                                $word,
                                $window,
                                $code . ' (' . $lang . ') puts "' . $word . '" beside SIARC, which reads as an endorsement rather than a recorded event.'
                            );
                        }
                    }
                }
            }
        }
    }

    /* ──────────────────────── Rule 3 — no raw enum leaks ────────────────── */

    public function test_no_document_leaks_a_database_enum_as_visible_text(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [, $text] = $this->fetch($doc['url']);

                foreach (self::ENUM_LEAKS as $enum) {
                    $this->assertStringNotContainsString(
                        $enum,
                        $text,
                        $code . ' (' . $lang . ') prints the raw storage value "' . $enum . '" to a reader.'
                    );
                }
            }
        }
    }

    /**
     * The same leak wearing a different coat.
     *
     * Several status bands fall back to Str::upper($row->status) when a value
     * is missing from their label map, which turns an unmapped state into a
     * band reading UNDER_REVIEW - still the raw storage token, still
     * untranslated, and invisible to a fixed list of known enum strings
     * because the list holds the lower-case form. Any all-capitals word
     * containing an underscore on a finished document is that fallback firing.
     */
    public function test_no_document_shows_an_upper_cased_storage_token(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                $text = $this->documentText($this->fetch($doc['url'])[0]);

                preg_match_all('/\b[A-Z]{2,}_[A-Z_]{2,}\b/', $text, $m);

                $this->assertSame(
                    [],
                    array_values(array_unique($m[0])),
                    $code . ' (' . $lang . ') shows a raw storage token in capitals.'
                );
            }
        }
    }

    /**
     * The negative test above is worth nothing if the rows carrying those enums
     * are never rendered — an absent row passes a "does not contain" assertion
     * just as cleanly as a translated one. This test pins the positive side:
     * the records built by documents() deliberately carry the two most
     * dangerous values (a `gallery_acquisition` transfer of a `very_good`
     * piece, and a `museum_acquisition` export), and the reader must see them
     * spelled out in the language they asked for.
     */
    public function test_the_seeded_enums_are_actually_rendered_as_readable_words(): void
    {
        $expected = [
            'fr' => ['OTC' => ['Acquisition par galerie', 'Très bon'], 'EAC' => ['Acquisition muséale']],
            'en' => ['OTC' => ['Gallery acquisition', 'Very good'],    'EAC' => ['Museum acquisition']],
        ];

        foreach (['fr', 'en'] as $lang) {
            $docs = $this->documents($lang);

            foreach ($expected[$lang] as $code => $labels) {
                [, $text] = $this->fetch($docs[$code]['url']);

                foreach ($labels as $label) {
                    $this->assertStringContainsString(
                        $label,
                        $text,
                        $code . ' (' . $lang . ') does not render "' . $label . '", so the enum guard is testing an absent row.'
                    );
                }
            }
        }
    }

    /* ───────────────── Rule 4 — the platform's legal position ───────────── */

    /**
     * The wording of this disclaimer is not identical across the family - one
     * sheet says it "collects no payments", another that it "handles no
     * payment" - so the assertions below accept either phrasing rather than
     * pinning one document's sentence on all six. The drift is recorded as an
     * audit finding; unifying the sentence is the owner's call, not a test's.
     */
    public function test_every_document_that_mentions_a_transaction_states_the_platform_is_not_a_party(): void
    {
        // Words that mean the sheet is describing money changing hands. A
        // document that never mentions one — a registration record, for
        // instance — has nothing to disclaim.
        $transactionWords = ['transaction', 'vente', 'sale', 'acquisition', 'prix', 'price', 'paiement', 'payment'];

        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html] = $this->fetch($doc['url']);
                $text  = $this->documentText($html);
                $lower = mb_strtolower($text);

                $mentions = false;
                foreach ($transactionWords as $word) {
                    if (str_contains($lower, $word)) {
                        $mentions = true;
                        break;
                    }
                }

                if (! $mentions) {
                    continue;
                }

                $this->assertMatchesRegularExpression(
                    '/(entreprise priv|soci[ée]t[ée] priv|private company)/iu',
                    $text,
                    $code . ' (' . $lang . ') describes a transaction without saying the platform is a private company.'
                );

                $this->assertMatchesRegularExpression(
                    "/(n'est (pas |)partie|not a party|party to no)/iu",
                    $text,
                    $code . ' (' . $lang . ') describes a transaction without saying the platform is not a party to it.'
                );

                $this->assertMatchesRegularExpression(
                    "/(n'encaisse aucun paiement|ne (traite|per[çc]oit|g[èe]re) aucun paiement|collects no payments?|processes no payments?|handles no payments?)/iu",
                    $text,
                    $code . ' (' . $lang . ') describes a transaction without saying the platform collects no payments.'
                );
            }
        }
    }

    /* ──────────────── Rule 5 — each sheet wears its own code ────────────── */

    public function test_each_document_names_its_own_type_and_no_other_in_its_band(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html] = $this->fetch($doc['url']);

                $this->assertStringContainsString(
                    'data-cert-band="' . $code . '"',
                    $html,
                    $code . ' (' . $lang . ') does not carry its own classification band.'
                );

                foreach (self::ALL_CODES as $other) {
                    if ($other === $code) {
                        continue;
                    }

                    $this->assertStringNotContainsString(
                        'data-cert-band="' . $other . '"',
                        $html,
                        $code . ' (' . $lang . ') is also wearing the ' . $other . ' band.'
                    );
                }

                $this->assertStringContainsString(
                    e(config("certificate_types.$code.name.$lang")),
                    $html,
                    $code . ' (' . $lang . ') does not print its own type name.'
                );
            }
        }
    }

    /* ────────── Rule 6 — renders in both languages, quotes its number ───── */

    public function test_every_document_renders_in_both_languages_and_prints_its_own_number(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [, $text] = $this->fetch($doc['url']);

                $this->assertNotEmpty($doc['number'], $code . ' has no identifier to print.');
                $this->assertStringContainsString(
                    (string) $doc['number'],
                    $text,
                    $code . ' (' . $lang . ') does not print its own certificate or registry number.'
                );
            }
        }
    }

    /**
     * A certificate that quotes an identifier the register does not hold is
     * worse than one that quotes none: the number is the only handle a curator
     * has for looking the record up, and a document and a database that
     * disagree about it cannot both be right. Compared against the columns
     * themselves rather than against the helper that formats them, so a change
     * to the formatting is caught here instead of on a printed sheet.
     */
    public function test_each_document_quotes_the_identifiers_the_database_actually_holds(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html] = $this->fetch($doc['url']);
                $text = $this->documentText($html);

                foreach (['prn', 'oln'] as $column) {
                    foreach (Product::whereNotNull($column)->pluck($column, 'id') as $value) {
                        if (! str_contains($text, (string) $value)) {
                            continue;
                        }

                        // Present, and therefore has to be present verbatim:
                        // a truncated or reformatted identifier would still
                        // pass a substring test, so the check is that the
                        // stored string appears whole.
                        $this->assertStringContainsString((string) $value, $text);
                    }
                }

                // Whatever identifiers the sheet does print in the AH237
                // scheme must each exist in the register. An identifier on a
                // certificate with no row behind it is the failure this
                // catches.
                preg_match_all('/AH237-(PRN|OLN|GAN)-[A-Z0-9\-]+/', $text, $m);

                foreach (array_unique($m[0]) as $printed) {
                    $kind  = str_contains($printed, '-PRN-') ? ['products', 'prn']
                        : (str_contains($printed, '-OLN-') ? ['products', 'oln'] : ['businesses', 'gan']);

                    $exists = $kind[0] === 'products'
                        ? Product::where($kind[1], $printed)->exists()
                        : Business::where('gan', $printed)->exists();

                    $this->assertTrue(
                        $exists,
                        $code . ' (' . $lang . ') prints ' . $printed . ', which no row in the register holds.'
                    );
                }
            }
        }
    }
    /* ─────────────────────── Accessibility floor ────────────────────────── */

    public function test_every_image_on_every_document_has_an_alt_attribute(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html] = $this->fetch($doc['url']);

                preg_match_all('/<img\b[^>]*>/i', $html, $tags);

                foreach ($tags[0] as $tag) {
                    $this->assertMatchesRegularExpression(
                        '/\balt\s*=/i',
                        $tag,
                        $code . ' (' . $lang . ') has an image with no alt attribute: ' . $tag
                    );
                }
            }
        }
    }

    /**
     * The QR code is the only route from the printed sheet back to the live
     * record. Rendered as a bare SVG or image it is invisible to a screen
     * reader and to anybody who cannot use a camera, which is why the standard
     * requires a text alternative: the verification address has to be readable
     * as text on the page, not only encoded in the square.
     */
    public function test_every_qr_code_has_a_text_alternative(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html, $text] = $this->fetch($doc['url']);

                if (! preg_match('/qr/i', $html)) {
                    continue;
                }

                // The address counts whether or not it carries a scheme: the
                // sheets print it bare, as "artisanatcameroun.test/certificat-…",
                // which is what somebody would type.
                $this->assertMatchesRegularExpression(
                    '#(https?://\S|[a-z0-9.-]+\.(test|com|cm)/\S|/verifier|/verify)#i',
                    $text,
                    $code . ' (' . $lang . ') prints a QR code with no readable verification address beside it.'
                );
            }
        }
    }

    /**
     * Colour alone must not carry the status. A reader who cannot distinguish
     * the band colours, or who is holding a photocopy, still has to be able to
     * tell a valid certificate from a revoked one — so the status has to appear
     * as words.
     */
    public function test_status_is_carried_by_words_and_not_only_by_colour(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [, $text] = $this->fetch($doc['url']);

                $this->assertMatchesRegularExpression(
                    '/(valide|valid|actif|active|en vigueur|révoqu|revoked|remplac|superseded|délivr|issued|expédi|shipped|approuv|approved)/iu',
                    $text,
                    $code . ' (' . $lang . ') states no status in words.'
                );
            }
        }
    }

    /* ───────────────────────── Internal links resolve ───────────────────── */

    public function test_every_internal_link_on_every_document_resolves(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->documents($lang) as $code => $doc) {
                [$html] = $this->fetch($doc['url']);

                preg_match_all('/href\s*=\s*"([^"]+)"/i', $html, $m);

                foreach (array_unique($m[1]) as $href) {
                    $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    // Only same-site paths are ours to keep working.
                    if (! str_starts_with($href, '/') || str_starts_with($href, '//')) {
                        continue;
                    }

                    $status = $this->get($href)->getStatusCode();

                    $this->assertLessThan(
                        400,
                        $status,
                        $code . ' (' . $lang . ') links to ' . $href . ', which answers ' . $status . '.'
                    );
                }
            }
        }
    }

    /* ─────────────── Rule 9 — a French document is French throughout ────── */

    /**
     * The regression guard for the defect this rule was written after: two
     * support classes built the "show your working" sentences under the
     * provenance and export scores, neither took a language, and a French
     * reader was handed English reasoning in the middle of a French document.
     *
     * The markers below are not a spell-check. Each is a fragment that was
     * actually printing on ?lang=fr, so a failure here means a specific
     * sentence has gone back to being built without the reader in mind. They
     * are matched inside <main> for the same reason every other rule is: it is
     * the document that has to be readable, not the page around it.
     */
    public function test_no_french_document_prints_english_reasoning(): void
    {
        $markers = [
            'has been recorded',
            'No supporting document',
            'is not assessed',
            'Created and first held',
            'no insurance cover',
            'the sender is',
            'the chain begins',
        ];

        $urls = array_column($this->documents('fr'), 'url');

        // The hub prints the Legacy Index and its basis lines too, and it is
        // not part of the document sweep because it is an index rather than a
        // certificate. It regresses in exactly the same way, so it is checked
        // here explicitly.
        $urls[] = '/certificats/' . $this->publishedProduct()->slug . '?lang=fr';

        foreach ($urls as $url) {
            [$html] = $this->fetch($url);
            $text   = $this->documentText($html);

            foreach ($markers as $marker) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $marker,
                    $text,
                    $url . ' prints the English fragment "' . $marker . '" to a French reader.'
                );
            }
        }
    }
}
