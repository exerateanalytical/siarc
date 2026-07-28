<?php

namespace Tests\Feature;

use App\Modules\Products\Models\Product;
use App\Support\ProvenanceDossier;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the Product Provenance Certificate — the three-page dossier every
 * other certificate in the family feeds into.
 *
 * The stakes here are different from the single-sheet certificates. This is the
 * document a researcher or an auction house reads end to end, and it is long
 * enough that a fabricated line would sit unnoticed among two hundred real
 * ones. So the assertions come in two halves.
 *
 * The first half is completeness: all three sheets, each identifiable on its
 * own, every holder in the chain in the register's order, and the identity
 * numbers a reader would use to pull the file. A loose page 2 with no
 * certificate number on it is not evidence of anything.
 *
 * The second half is refusal. The artwork this view replicates advertises a
 * rainbow holographic shield, UV reactive artwork, a ghost portrait watermark,
 * a latent image, NFC, an AI feature vector and a blockchain, and hangs two
 * named officers' handwritten signatures at the foot of page 3. None of those
 * exist. They are asserted absent by string, because the failure mode of a
 * certificate is not a crash — it is a page that renders beautifully and says
 * something untrue about a real person's work.
 */
class ProvenanceCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product(): Product
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /** A piece with a documented life: a gallery holder and a spread of events. */
    private function documented(): Product
    {
        $product = $this->product();

        ProvenanceRegistry::transfer($product, [
            'legal_name'         => 'Heritage Gallery Douala',
            'entity_type'        => 'gallery',
            'country_code'       => 'CM',
            'verification_level' => 'verified',
        ], ['transfer_type' => 'gallery_acquisition', 'transfer_city' => 'Douala']);

        $product = $product->fresh();

        ProvenanceDossier::record($product, 'exhibition', [
            'title'        => 'SIARC 2026 — 9th Edition',
            'organisation' => 'SIARC',
            'venue'        => 'Douala',
            'country'      => 'CM',
            'started_on'   => '2026-07-27',
            'reference_no' => 'EXH-0001',
        ]);
        ProvenanceDossier::record($product, 'publication', [
            'title'        => 'The Art of the Fang',
            'organisation' => 'Presses Universitaires',
            'started_on'   => '2026-08-04',
        ]);

        return $product->fresh();
    }

    private function certificate(Product $product, string $lang = 'fr')
    {
        return $this->get('/certificat-provenance/' . $product->slug . '?lang=' . $lang);
    }

    /* ───────────────────────────── Reachability ────────────────────────── */

    public function test_a_published_product_renders_and_a_draft_does_not(): void
    {
        $product = $this->documented();
        $this->certificate($product)->assertOk();

        $draft = $this->product();
        $draft->update(['status' => 'draft']);

        $this->certificate($draft->fresh())->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $product = $this->documented();

        $this->certificate($product, 'fr')->assertOk()->assertSee('CERTIFICAT DE PROVENANCE', false);
        $this->certificate($product, 'en')->assertOk()->assertSee('PRODUCT PROVENANCE CERTIFICATE', false);
    }

    /* ──────────────────────────── Three sheets ─────────────────────────── */

    public function test_all_three_sheets_are_present_and_each_carries_the_identity(): void
    {
        $product = $this->documented();
        $coa     = \App\Support\ProductCertificate::forProduct($product);

        $html = $this->certificate($product, 'en')->assertOk()->getContent();

        foreach (['Page 1 of 3', 'Page 2 of 3', 'Page 3 of 3'] as $marker) {
            $this->assertStringContainsString($marker, $html, "Sheet marker [{$marker}] is missing.");
        }

        // The certificate number must appear at least once per sheet: a page
        // that has come loose from the dossier still has to be identifiable.
        $this->assertGreaterThanOrEqual(
            3,
            substr_count($html, $coa->certificate_no),
            'The certificate number does not appear on every sheet.'
        );
    }

    public function test_it_prints_the_registry_ledger_and_artisan_numbers(): void
    {
        $product = $this->documented();

        $response = $this->certificate($product)->assertOk();

        $response->assertSee(ProvenanceRegistry::prnFor($product));
        $response->assertSee(ProvenanceRegistry::olnFor($product));
        $response->assertSee(ProvenanceRegistry::ganFor($product->business));
    }

    public function test_every_holder_appears_in_the_registers_order(): void
    {
        $product = $this->documented();

        ProvenanceRegistry::transfer($product, [
            'legal_name'   => 'Musee National Yaounde',
            'entity_type'  => 'museum',
            'country_code' => 'CM',
        ], ['transfer_type' => 'donation']);

        $product = $product->fresh();
        $chain   = ProvenanceRegistry::chain($product);
        $this->assertCount(3, $chain);

        $html   = $this->certificate($product)->assertOk()->getContent();
        $cursor = 0;

        foreach ($chain as $holder) {
            $at = strpos($html, $holder->legal_name, $cursor);
            $this->assertNotFalse($at, "Holder [{$holder->legal_name}] is missing from the dossier.");
            $cursor = $at;
        }
    }

    /* ────────────────────────────── Refusals ───────────────────────────── */

    public function test_it_claims_nothing_the_platform_cannot_deliver(): void
    {
        $html = $this->certificate($this->documented(), 'en')->assertOk()->getContent();

        foreach ([
            'Holographic', 'Hologramme', 'UV ', 'Ghost', 'Latent',
            'NFC', 'AI Fingerprint', 'AI Feature', 'punishable',
            'Blockchain', 'UNESCO',
        ] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $html,
                "The dossier claims [{$forbidden}], which nothing behind it supports."
            );
        }
    }

    public function test_the_french_sheet_makes_no_unsupported_claim_either(): void
    {
        $html = $this->certificate($this->documented(), 'fr')->assertOk()->getContent();

        foreach (['Hologramme', 'Holographic', 'NFC', 'Blockchain', 'UNESCO'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $html);
        }
    }

    /* ──────────────────────────── Legacy Index ─────────────────────────── */

    public function test_the_legacy_index_prints_the_registers_own_total_and_denominator(): void
    {
        $product = $this->documented();
        $legacy  = ProvenanceDossier::legacyIndex($product);

        $html = $this->certificate($product, 'en')->assertOk()->getContent();

        // The mockup designs in "96/100". The denominator here is whatever the
        // assessable categories add up to, and it must be the printed one.
        $this->assertStringContainsString(
            $legacy['total'] . ' / ' . $legacy['max'],
            $html,
            'The printed Legacy Index does not match legacyIndex().'
        );
        $this->assertLessThan(100, $legacy['max'], 'This fixture should have unassessable categories.');
    }

    public function test_an_unassessed_category_is_not_printed_as_a_zero_score(): void
    {
        $product = $this->documented();
        $legacy  = ProvenanceDossier::legacyIndex($product);

        // The fixture has no valuation and no conservation, so both come back
        // with max 0 — the exact case the mockup would have rendered as 0/20.
        $unassessed = array_filter($legacy['categories'], fn ($c) => $c['max'] === 0);
        $this->assertNotEmpty($unassessed);

        $html = $this->certificate($product, 'en')->assertOk()->getContent();

        foreach ($unassessed as $category) {
            $this->assertStringNotContainsString(
                $category['score'] . ' / 20',
                $html,
                'An unassessed category was rendered against a denominator it never had.'
            );
        }

        $this->assertStringContainsString('Not assessed', $html);

        // And the sentence that says what the number is about at all.
        $this->assertStringContainsString('completeness of the documented record', $html);
    }

    public function test_every_assessed_category_shows_the_basis_it_scored_on(): void
    {
        $product = $this->documented();
        $legacy  = ProvenanceDossier::legacyIndex($product);

        $html = $this->certificate($product, 'en')->assertOk()->getContent();

        foreach ($legacy['categories'] as $key => $category) {
            $this->assertStringContainsString(
                e($category['basis']),
                $html,
                "The basis for [{$key}] is missing, so the score cannot be argued with."
            );
        }
    }

    /* ───────────────────────────── Empty states ────────────────────────── */

    public function test_a_product_with_no_journey_says_so_rather_than_naming_a_country(): void
    {
        // No transfer, no events: the maker's founding row carries no country
        // on this fixture, so journey() is genuinely empty.
        $product = $this->product();
        $this->assertSame([], ProvenanceDossier::journey($product));

        $html = $this->certificate($product, 'en')->assertOk()->getContent();

        $this->assertStringContainsString('No country has been recorded', $html);
        $this->assertStringNotContainsString('Cameroon (CM)</td>', $html);
    }

    public function test_a_product_with_no_events_renders_honest_empty_sections(): void
    {
        $product = $this->product();
        $this->assertSame([], ProvenanceDossier::events($product));

        $this->certificate($product, 'en')->assertOk()->assertSee('No exhibition has been recorded');
    }

    /* ────────────────────────────── Events ─────────────────────────────── */

    public function test_recorded_events_are_printed_as_facts_about_the_object(): void
    {
        $product = $this->documented();

        $response = $this->certificate($product, 'en')->assertOk();

        // An exhibition row states where the piece was shown. It must not be
        // dressed as an endorsement, which is why no "certified by" wording is
        // rendered near it — the organisation prints as a plain record entry.
        $response->assertSee('SIARC 2026 — 9th Edition', false);
        $response->assertSee('EXH-0001');
        $response->assertSee('The Art of the Fang', false);
    }
}
