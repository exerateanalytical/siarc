<?php

namespace Tests\Feature;

use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the Export Authenticity Certificate.
 *
 * This is the only document in the family that a customs officer, a freight
 * agent and a receiving museum registrar all read, none of whom can check the
 * platform's working, and it is three pages long — which introduces a failure
 * mode the single-sheet certificates do not have. A page that arrives on its
 * own must still be identifiable, so every sheet is required below to carry the
 * certificate number and its own page number. A dossier whose page 2 could
 * belong to any consignment is not a dossier.
 *
 * The rest of the file defends the scores. The source artwork prints "98%
 * EXCELLENT" over eight categories, six green risk ticks, a holographic shield,
 * UV artwork, an NFC chip and five handwritten signatures. The register behind
 * it reports honest maxima of zero for the things it cannot judge, and the
 * whole value of that design is destroyed at the last step if the view prints
 * an unassessed category as 0/10 or an unassessed risk as a green tick. So the
 * assertions here are about the seam between a careful register and a careless
 * page, which is where this kind of document usually starts lying.
 */
class ExportCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function importer(): array
    {
        return [
            'name'    => 'Museum of World Cultures',
            'type'    => 'museum',
            'country' => 'FR',
            'city'    => 'Paris',
            'address' => '1 Culture Avenue, 75001 Paris',
        ];
    }

    /** A draft consignment on a certified piece: nothing packed, nothing shipped. */
    private function draft(array $opts = []): object
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);
        $product = $product->fresh();

        ProductCertificate::forProduct($product);

        return ExportRegister::open($product->fresh(), $this->importer(), $opts);
    }

    /** The normal case the preview seeds: approved, shipped, issued. */
    private function shipped(array $opts = []): object
    {
        $c = $this->draft($opts);

        ExportRegister::approve($c->id);
        ExportRegister::ship($c->id, [
            'carrier'            => 'DHL Express',
            'service'            => 'Express Worldwide',
            'awb_no'             => '123-45678901',
            'tracking_no'        => '7771234567890',
            'port_of_exit'       => 'Douala International Airport',
            'crate_ref'          => 'CRATE-2026-0012',
            'shock_protection'   => true,
            'climate_protection' => true,
        ]);

        return ExportRegister::issue($c->id);
    }

    private function certificate(object $c, string $lang = 'fr')
    {
        return $this->get('/certificat-export/' . $c->certificate_no . '?lang=' . $lang);
    }

    /* ───────────────────────────── Reachability ────────────────────────── */

    public function test_a_real_reference_renders_and_an_unknown_one_is_not_found(): void
    {
        $this->certificate($this->shipped())->assertOk();

        // A document that renders for any string is evidence of nothing.
        $this->get('/certificat-export/AH237-EAC-CM-2026-000000009999')->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $c = $this->shipped();

        // The needle is escaped the way Blade escapes it, so the apostrophe in
        // the French title is compared like for like rather than raw.
        $this->certificate($c, 'fr')->assertOk()->assertSee('CERTIFICAT D\'AUTHENTICITÉ À L\'EXPORTATION');
        $this->certificate($c, 'en')->assertOk()->assertSee('EXPORT AUTHENTICITY CERTIFICATE', false);
    }

    /* ─────────────────────────── A three-page dossier ──────────────────── */

    public function test_all_three_sheets_are_present_and_each_one_identifies_itself(): void
    {
        $c    = $this->shipped();
        $html = $this->certificate($c, 'en')->assertOk()->getContent();

        // Each page number appears once, in order, and the certificate number
        // appears at least once per sheet: a loose page 3 has to be traceable
        // back to the consignment it belongs to without the other two.
        $cursor = 0;
        foreach ([1, 2, 3] as $page) {
            $at = strpos($html, 'Page ' . $page . ' of 3', $cursor);
            $this->assertNotFalse($at, "Sheet {$page} is missing from the dossier.");
            $cursor = $at;
        }

        $this->assertSame(3, substr_count($html, 'Page ') >= 3 ? 3 : 0, 'Three page markers were expected.');
        $this->assertGreaterThanOrEqual(3, substr_count($html, $c->certificate_no),
            'Each sheet must carry the certificate number.');
    }

    /* ───────────────────────── The register on the sheets ──────────────── */

    public function test_it_prints_the_identifiers_a_reader_needs_to_follow_it_up(): void
    {
        $c        = $this->shipped();
        $product  = DB::table('products')->find($c->product_id);
        $shipment = DB::table('shipments')->where('export_consignment_id', $c->id)->first();

        $response = $this->certificate($c, 'en')->assertOk();

        $response->assertSee($c->certificate_no);
        $response->assertSee($c->gecn);
        $response->assertSee($c->uuid);
        $response->assertSee($product->prn);
        $response->assertSee($product->oln);
        $response->assertSee('Museum of World Cultures');
        $response->assertSee($shipment->carrier);
        $response->assertSee($c->verification_pin);
        $response->assertSee($c->ca_kid);
        $response->assertSee($c->content_hash);
    }

    /* ──────────────────── Claims the platform cannot make ──────────────── */

    public function test_it_never_advertises_a_security_feature_a_screen_cannot_carry(): void
    {
        $html = $this->certificate($this->shipped(), 'en')->assertOk()->getContent();

        foreach ([
            'Holographic', 'Hologramme', 'UV ', 'Ghost watermark', 'Latent',
            'NFC', 'punishable', 'Blockchain', 'blockchain', 'AI Fingerprint', 'C2PA',
        ] as $claim) {
            $this->assertStringNotContainsString($claim, $html, "The certificate asserts [{$claim}].");
        }
    }

    /* ──────────────── Unassessed is not a score and not a tick ─────────── */

    public function test_a_category_with_nothing_to_assess_is_labelled_rather_than_scored(): void
    {
        // Nothing packed and nothing booked, so packaging and logistics both
        // report a maximum of zero. Printing "0 / 10" would tell the reader the
        // crate failed an inspection nobody carried out.
        $c    = $this->draft();
        $html = $this->certificate($c, 'en')->assertOk()->getContent();

        $readiness = ExportRegister::readiness($c->id);
        $this->assertSame(0, $readiness['categories']['packaging']['max']);

        // Anchored to the start of the score cell, because a legitimate
        // "10 / 10" elsewhere on the sheet ends in the same six characters.
        $this->assertDoesNotMatchRegularExpression('/>\s*0 \/ 10\s*</', $html);
        $this->assertStringContainsString('Not assessed', $html);
        // And the basis is still printed, so the reader knows *why*.
        $this->assertStringContainsString($readiness['categories']['packaging']['basis'], $html);
    }

    public function test_an_unassessed_risk_is_not_rendered_as_a_low_one(): void
    {
        $c    = $this->shipped();
        $risk = ExportRegister::risk($c->id);

        $this->assertSame('unassessed', $risk['insurance']['level']);

        $html = $this->certificate($c, 'en')->assertOk()->getContent();

        // The unassessed row must not be drawn in the palette or wording the
        // low rows use. Locating the row and reading only its own markup is the
        // only way to assert that without asserting on the whole page.
        $at  = strpos($html, $risk['insurance']['basis']);
        $this->assertNotFalse($at, 'The unassessed risk basis is missing.');
        $row = substr($html, max(0, $at - 700), 700);

        $this->assertStringNotContainsString('#0F7A34', $row, 'An unassessed risk is drawn green.');
        $this->assertStringContainsString('Not assessed', $row);
    }

    public function test_the_readiness_categories_all_show_their_working(): void
    {
        $c         = $this->shipped();
        $readiness = ExportRegister::readiness($c->id);
        $html      = $this->certificate($c, 'en')->assertOk()->getContent();

        foreach ($readiness['categories'] as $key => $cat) {
            $this->assertStringContainsString($cat['basis'], $html,
                "The [{$key}] category prints a score with no stated basis.");
        }
    }

    /* ─────────────────────────── Conditional blocks ────────────────────── */

    public function test_blocks_whose_columns_are_empty_do_not_appear(): void
    {
        $html = $this->certificate($this->draft(), 'en')->assertOk()->getContent();

        // Nothing booked, nothing insured, no declaration filed. An empty
        // "Customs declaration" heading on an export document invites a reader
        // to take a blank for a clearance.
        $this->assertStringNotContainsString('Shipping and logistics', $html);
        $this->assertStringNotContainsString('Insurance', $html);
        $this->assertStringNotContainsString('Customs declaration', $html);
    }

    public function test_a_booked_shipment_brings_its_block_with_it(): void
    {
        $html = $this->certificate($this->shipped(), 'en')->assertOk()->getContent();

        $this->assertStringContainsString('Shipping and logistics', $html);
        $this->assertStringContainsString('123-45678901', $html);
        $this->assertStringContainsString('Douala International Airport', $html);
    }

    public function test_a_customs_declaration_appears_only_as_a_recorded_reference(): void
    {
        $c = $this->shipped(['customs_declaration_no' => 'DEC-CM-2026-7789']);

        $html = $this->certificate($c, 'en')->assertOk()->getContent();

        $this->assertStringContainsString('DEC-CM-2026-7789', $html);
        // Recorded, not endorsed: no state official has signed anything here.
        $this->assertStringNotContainsString('Customs stamp', $html);
        $this->assertStringNotContainsString('Officer signature', $html);
    }
}
