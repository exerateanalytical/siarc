<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the classification band that runs down the left edge of every
 * certificate the platform issues.
 *
 * The band is the only part of these documents that is shared verbatim between
 * them, and that is precisely what it is for: a reader who has seen one sheet
 * should recognise the next one as a member of the same family and, in the same
 * glance, see which member it is. Two properties therefore have to hold and
 * cannot be left to a later visual check.
 *
 * First, a document must wear its own classification and nobody else's. A
 * transfer deed carrying the authenticity band is not a cosmetic slip; it is a
 * mislabelled legal document, and the mistake is invisible to the person the
 * document is shown to.
 *
 * Second, the scheme has to stay a scheme. Ten types are declared, six of which
 * have no page yet; if two of them ever share a colour the band stops carrying
 * information and becomes decoration. The colour-collision test is here so that
 * whoever adds the eleventh type finds out at the point of adding it rather
 * than after it is printed.
 */
class CertificateBandTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** Every code the scheme declares, in the order the config lists them. */
    private const CODES = ['COA', 'PRC', 'OTC', 'AVC', 'PPC', 'EAC', 'EC', 'RC', 'VAC', 'DPP'];

    /** The codes that have a live page today, and the rest, which do not. */
    private const LIVE = ['COA', 'PRC', 'OTC', 'AVC'];

    private function publishedProduct(): Product
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /**
     * The four live certificates, as [code => url]. Each one is built through
     * the same helpers the sister suites use, so the URLs here stay honest
     * about what the routes actually accept.
     */
    private function liveCertificateUrls(string $lang = 'fr'): array
    {
        $product = $this->publishedProduct();

        $business = $this->makeBusiness(null, ['verification_tier' => 'verified']);
        ProvenanceRegistry::ganFor($business);

        $transferProduct = $this->publishedProduct();
        $transfer        = ProvenanceRegistry::transfer($transferProduct, [
            'legal_name'         => 'Heritage Gallery Douala',
            'entity_type'        => 'gallery',
            'country_code'       => 'CM',
            'verification_level' => 'verified',
        ], [
            'transfer_type'  => 'gallery_acquisition',
            'transfer_city'  => 'Douala',
            'currency'       => 'XAF',
            'declared_value' => 850000,
            'condition'      => 'excellent',
        ]);

        $q = '?lang=' . $lang;

        return [
            'COA' => '/certificat/' . $product->slug . $q,
            'PRC' => '/certificat-enregistrement/' . $product->slug . $q,
            'OTC' => '/certificat-transfert/' . $transfer->certificate_no . $q,
            'AVC' => '/certificat-artisan/' . $business->fresh()->slug . $q,
        ];
    }

    /* ─────────────────────────── The scheme itself ─────────────────────── */

    public function test_all_ten_types_are_declared_with_a_colour_an_icon_and_both_languages(): void
    {
        $types = config('certificate_types');

        $this->assertIsArray($types);
        $this->assertSame(self::CODES, array_keys($types), 'The scheme declares exactly the ten documented types.');

        foreach ($types as $code => $type) {
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $type['colour'] ?? '', $code . ' needs a colour.');
            $this->assertMatchesRegularExpression('/^#[0-9A-Fa-f]{6}$/', $type['accent'] ?? '', $code . ' needs an accent.');
            $this->assertNotEmpty($type['icon'] ?? null, $code . ' needs an icon key.');
            $this->assertNotEmpty($type['name']['fr'] ?? null, $code . ' needs a French name.');
            $this->assertNotEmpty($type['name']['en'] ?? null, $code . ' needs an English name.');
        }
    }

    public function test_no_two_types_share_a_colour(): void
    {
        $colours = array_map(
            fn (array $t) => strtoupper($t['colour']),
            config('certificate_types')
        );

        $this->assertSame(
            count($colours),
            count(array_unique($colours)),
            'Two document types share a band colour, which defeats the point of colouring them.'
        );
    }

    public function test_every_declared_icon_is_actually_drawn(): void
    {
        foreach (config('certificate_types') as $code => $type) {
            $svg = View::make('pages.partials.certificate-band', [
                'code' => $code,
                'lang' => 'fr',
            ])->render();

            $this->assertStringContainsString('<path', $svg, $code . ' renders no icon geometry.');
        }
    }

    /* ─────────────────────── Types that have no page yet ───────────────── */

    public function test_the_band_renders_for_a_type_that_has_no_certificate_page_yet(): void
    {
        foreach (['PPC', 'EAC', 'EC', 'RC', 'VAC', 'DPP'] as $code) {
            foreach (['fr', 'en'] as $lang) {
                $html = View::make('pages.partials.certificate-band', [
                    'code' => $code,
                    'lang' => $lang,
                ])->render();

                $this->assertStringContainsString('data-cert-band="' . $code . '"', $html);
                $this->assertStringContainsString(config("certificate_types.$code.colour"), $html);
                $this->assertStringContainsString(e(config("certificate_types.$code.name.$lang")), $html);
            }
        }
    }

    public function test_an_unknown_code_does_not_render_a_band(): void
    {
        $html = View::make('pages.partials.certificate-band', ['code' => 'ZZZ', 'lang' => 'fr'])->render();

        $this->assertStringNotContainsString('data-cert-band', $html);
    }

    /* ───────────────────────── The four live sheets ────────────────────── */

    public function test_each_live_certificate_wears_its_own_band_and_no_other(): void
    {
        foreach ($this->liveCertificateUrls() as $code => $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString(
                'data-cert-band="' . $code . '"',
                $html,
                $code . ' does not carry its own classification band.'
            );

            foreach (self::CODES as $other) {
                if ($other === $code) {
                    continue;
                }

                $this->assertStringNotContainsString(
                    'data-cert-band="' . $other . '"',
                    $html,
                    $code . ' is wearing the ' . $other . ' band as well as its own.'
                );
            }
        }
    }

    public function test_each_live_certificate_prints_its_band_colour_and_name(): void
    {
        foreach ($this->liveCertificateUrls() as $code => $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString(config("certificate_types.$code.colour"), $html);
            $this->assertStringContainsString(e(config("certificate_types.$code.name.fr")), $html);
        }
    }

    public function test_both_languages_render_on_all_four(): void
    {
        foreach (['fr', 'en'] as $lang) {
            foreach ($this->liveCertificateUrls($lang) as $code => $url) {
                $html = $this->get($url)->assertOk()->getContent();

                $this->assertStringContainsString('data-cert-band="' . $code . '"', $html);
                $this->assertStringContainsString(e(config("certificate_types.$code.name.$lang")), $html);
            }
        }
    }

    /* ──────────────────────────── Family likeness ──────────────────────── */

    public function test_every_band_is_the_same_width(): void
    {
        // The band is the family resemblance. If one sheet renders it wider
        // than the others the set stops looking like a series, so the width is
        // asserted rather than left to the eye.
        foreach ($this->liveCertificateUrls() as $code => $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('--cert-band-w: 40px', $html, $code . ' redefines the band width.');
        }
    }
}
