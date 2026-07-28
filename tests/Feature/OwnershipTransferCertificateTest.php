<?php

namespace Tests\Feature;

use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the ownership transfer certificate.
 *
 * This is the document a museum registrar, an insurer or a customs officer
 * reads to decide whether a chain of title holds together, so two things are
 * defended here. First, that everything the chain contains is actually on the
 * sheet and in order — a provenance document that silently drops a holder is
 * worse than none, because it looks complete. Second, that nothing on the sheet
 * is a promise the platform cannot keep: the artwork this view replicates
 * advertises a blockchain, an AI fingerprint match, UV ink and a holographic
 * seal, none of which exist. A certificate is read as a statement of fact by
 * people who cannot check it, so the assertions below are deliberately blunt.
 */
class OwnershipTransferCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** A published piece that has moved from its maker to a gallery. */
    private function transfer(array $details = [], array $to = []): object
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return ProvenanceRegistry::transfer(
            $product->fresh(),
            array_merge([
                'legal_name'         => 'Heritage Gallery Douala',
                'entity_type'        => 'gallery',
                'country_code'       => 'CM',
                'verification_level' => 'verified',
            ], $to),
            array_merge([
                'transfer_type'   => 'gallery_acquisition',
                'transfer_city'   => 'Douala',
                'currency'        => 'XAF',
                'declared_value'  => 850000,
                'condition'       => 'excellent',
            ], $details)
        );
    }

    private function get_certificate(object $transfer, string $lang = 'fr')
    {
        return $this->get('/certificat-transfert/' . $transfer->certificate_no . '?lang=' . $lang);
    }

    /* ───────────────────────────── Reachability ────────────────────────── */

    public function test_a_real_transfer_reference_renders_and_an_unknown_one_is_not_found(): void
    {
        $transfer = $this->transfer();

        $this->get_certificate($transfer)->assertOk();

        // An unknown reference must not be a soft "certificate not found" page:
        // a document that renders for any string is not evidence of anything.
        $this->get('/certificat-transfert/AH237-OTC-CM-2026-000000009999')->assertNotFound();
    }

    public function test_both_languages_render(): void
    {
        $transfer = $this->transfer();

        $this->get_certificate($transfer, 'fr')->assertOk()->assertSee('CERTIFICAT DE TRANSFERT', false);
        $this->get_certificate($transfer, 'en')->assertOk()->assertSee('OWNERSHIP TRANSFER CERTIFICATE', false);
    }

    /* ────────────────────────── The register on the sheet ──────────────── */

    public function test_it_prints_the_certificate_number_the_ledger_number_and_the_signing_key(): void
    {
        $transfer = $this->transfer();
        $product  = DB::table('products')->find($transfer->product_id);

        $response = $this->get_certificate($transfer);

        $response->assertSee($transfer->certificate_no);
        $response->assertSee($transfer->uuid);
        // The OLN is the whole point of the document: it is the one identifier
        // that lets a holder of this single sheet pull the rest of the chain.
        $response->assertSee($product->oln);
        $response->assertSee($transfer->verification_pin);
        $response->assertSee($transfer->ca_kid);
    }

    public function test_every_holder_appears_in_chronological_order(): void
    {
        $transfer = $this->transfer();
        $product  = \App\Modules\Products\Models\Product::find($transfer->product_id);

        // A third holder, so "in order" is a real constraint and not two names
        // that happen to sort correctly.
        ProvenanceRegistry::transfer($product->fresh(), [
            'legal_name'  => 'Global Heritage Museum',
            'entity_type' => 'museum',
            'country_code' => 'FR',
        ], ['transfer_type' => 'sale']);

        $latest = DB::table('ownership_transfers')->where('product_id', $product->id)
            ->orderByDesc('id')->first();

        $html  = $this->get_certificate($latest)->assertOk()->getContent();
        $chain = ProvenanceRegistry::chain($product->fresh());

        $this->assertCount(3, $chain);

        $cursor = 0;
        foreach ($chain as $holder) {
            $at = strpos($html, $holder->legal_name, $cursor);
            $this->assertNotFalse($at, "Holder [{$holder->legal_name}] is missing from the certificate.");
            $this->assertGreaterThanOrEqual($cursor, $at, 'The chain is rendered out of sequence.');
            $cursor = $at;
        }
    }

    /* ──────────────────────── Claims the platform cannot make ──────────── */

    public function test_it_never_claims_a_capability_the_platform_does_not_have(): void
    {
        $html = $this->get_certificate($this->transfer())->assertOk()->getContent();

        // There is no chain of blocks, no model, and no physical print run.
        // Each of these appears on the source artwork and each would be a lie.
        foreach (['Blockchain', 'blockchain', 'AI Fingerprint', 'AI Visual',
                  'UV ', 'Holographic', 'Hologramme', 'SIARC', 'UNESCO'] as $claim) {
            $this->assertStringNotContainsString($claim, $html, "The certificate asserts [{$claim}].");
        }
    }

    /* ─────────────────────────── Conditional blocks ────────────────────── */

    public function test_a_private_value_is_not_printed(): void
    {
        $open   = $this->transfer(['declared_value' => 850000, 'currency' => 'XAF']);
        $closed = $this->transfer(['declared_value' => 4800000, 'currency' => 'XAF', 'value_is_private' => true]);

        $this->get_certificate($open)->assertSee('850 000');
        $this->get_certificate($closed)->assertDontSee('4 800 000');
    }

    public function test_export_and_insurance_blocks_appear_only_when_those_columns_hold_something(): void
    {
        $bare = $this->transfer();

        $html = $this->get_certificate($bare, 'en')->assertOk()->getContent();
        $this->assertStringNotContainsString('Export information', $html);
        $this->assertStringNotContainsString('Insurance', $html);

        $filled = $this->transfer();
        DB::table('ownership_transfers')->where('id', $filled->id)->update([
            'export_permit_no'      => 'EXP-CM-2026-8842',
            'country_of_export'     => 'CM',
            'country_of_destination' => 'FR',
            'customs_reference'     => 'CUS-FR-2026-55321',
            'insurer_name'          => 'AXA Art',
            'insurance_policy_no'   => 'AXA-ART-2026-778899',
        ]);

        $html = $this->get_certificate((object) ['certificate_no' => $filled->certificate_no], 'en')
            ->assertOk()->getContent();

        $this->assertStringContainsString('Export information', $html);
        $this->assertStringContainsString('EXP-CM-2026-8842', $html);
        $this->assertStringContainsString('Insurance', $html);
        $this->assertStringContainsString('AXA-ART-2026-778899', $html);
    }

    public function test_a_field_the_register_never_recorded_leaves_no_empty_row(): void
    {
        // Nothing was supplied for the invoice, payment or transaction refs, so
        // their labels must be absent rather than printed against a blank.
        $html = $this->get_certificate($this->transfer(), 'en')->assertOk()->getContent();

        $this->assertStringNotContainsString('Invoice number', $html);
        $this->assertStringNotContainsString('Payment reference', $html);
    }
}
