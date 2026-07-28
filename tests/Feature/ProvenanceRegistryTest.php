<?php

namespace Tests\Feature;

use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the register the certificates are drawn from.
 *
 * A provenance register earns its keep through two properties: the identifiers
 * it hands out never change, and the ownership chain is always exactly one
 * unbroken sequence with exactly one open end. A document quoting a register
 * that can drift on either count is worth nothing, so both are asserted here
 * rather than trusted.
 */
class ProvenanceRegistryTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    public function test_the_permanent_numbers_are_assigned_once_and_never_change(): void
    {
        $product  = $this->product();
        $business = $product->business;

        $prn = ProvenanceRegistry::prnFor($product);
        $oln = ProvenanceRegistry::olnFor($product);
        $gan = ProvenanceRegistry::ganFor($business);

        $this->assertMatchesRegularExpression('/^AH237-PRN-CM-\d{4}-\d{12}$/', $prn);
        $this->assertMatchesRegularExpression('/^AH237-OLN-\d{10}$/', $oln);
        $this->assertMatchesRegularExpression('/^AH237-GAN-CM-\d{10}$/', $gan);

        // Renaming the shop, re-slugging the product, even transferring the
        // work must not move these.
        $business->update(['name_fr' => 'Une toute autre raison sociale']);
        $product->update(['slug' => 'un-tout-autre-slug', 'name_fr' => 'Autre nom']);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Galerie Douala', 'entity_type' => 'gallery']);

        $this->assertSame($prn, ProvenanceRegistry::prnFor($product->fresh()));
        $this->assertSame($oln, ProvenanceRegistry::olnFor($product->fresh()));
        $this->assertSame($gan, ProvenanceRegistry::ganFor($business->fresh()));
    }

    public function test_provenance_starts_at_the_maker(): void
    {
        $product = $this->product();

        $owner = ProvenanceRegistry::currentOwner($product);

        $this->assertNotNull($owner);
        $this->assertTrue((bool) $owner->is_original_creator);
        $this->assertSame(1, (int) $owner->sequence);
        $this->assertNull($owner->owned_until);

        // Asking twice must not open a second founding entry.
        ProvenanceRegistry::currentOwner($product->fresh());
        $this->assertSame(1, DB::table('product_ownerships')->where('product_id', $product->id)->count());
    }

    /**
     * The one state a provenance register must never reach is two current
     * owners. Four transfers are run and the invariant checked after each.
     */
    public function test_a_chain_of_transfers_always_leaves_exactly_one_current_owner(): void
    {
        $product = $this->product();

        $steps = [
            ['legal_name' => 'Heritage Gallery Douala', 'entity_type' => 'gallery',  'country_code' => 'CM'],
            ['legal_name' => 'Private Collector',       'entity_type' => 'individual', 'country_code' => 'FR'],
            ['legal_name' => 'Global Heritage Museum',  'entity_type' => 'museum',   'country_code' => 'FR'],
            ['legal_name' => 'Fondation du Patrimoine', 'entity_type' => 'foundation', 'country_code' => 'FR'],
        ];

        foreach ($steps as $i => $to) {
            ProvenanceRegistry::transfer($product->fresh(), $to);

            $open = DB::table('product_ownerships')
                ->where('product_id', $product->id)->whereNull('owned_until')->get();

            $this->assertCount(1, $open, 'A transfer left the product with ' . $open->count() . ' current owners.');
            $this->assertSame($to['legal_name'], $open->first()->legal_name);
            $this->assertSame($i + 2, DB::table('product_ownerships')->where('product_id', $product->id)->count());
        }

        // The chain is a contiguous sequence with no gap and no repeat.
        $seqs = DB::table('product_ownerships')->where('product_id', $product->id)
            ->orderBy('sequence')->pluck('sequence')->all();
        $this->assertSame(range(1, 5), array_map('intval', $seqs));

        // Exactly one live certificate; the rest are superseded, not deleted.
        $this->assertSame(1, DB::table('ownership_transfers')->where('product_id', $product->id)->where('status', 'active')->count());
        $this->assertSame(3, DB::table('ownership_transfers')->where('product_id', $product->id)->where('status', 'superseded')->count());
    }

    public function test_a_transfer_certificate_is_numbered_hashed_and_signed(): void
    {
        $product = $this->product();
        $cert = ProvenanceRegistry::transfer($product, [
            'legal_name'   => 'Global Heritage Museum',
            'entity_type'  => 'museum',
            'country_code' => 'FR',
        ], ['transfer_type' => 'museum_acquisition', 'currency' => 'EUR', 'declared_value' => 4800]);

        $this->assertMatchesRegularExpression('/^AH237-OTC-CM-\d{4}-\d{12}$/', $cert->certificate_no);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $cert->content_hash);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $cert->signature);
        $this->assertSame('museum_acquisition', $cert->transfer_type);

        // The signature must recompute from the certified values, and not under
        // a different key.
        $this->assertSame(
            \App\Support\ProductCertificate::signatureFor($cert->certificate_no, $cert->content_hash, $product->fresh()->oln),
            $cert->signature
        );
    }

    /**
     * The audit trail a certificate prints must be rows, not decoration.
     */
    public function test_the_audit_trail_records_issuance(): void
    {
        $product = $this->product();
        $cert = ProvenanceRegistry::transfer($product, ['legal_name' => 'Galerie Douala', 'entity_type' => 'gallery']);

        $trail = ProvenanceRegistry::trail('otc', $cert->id);

        $this->assertNotEmpty($trail);
        $this->assertSame('issued', $trail[0]->event);
    }

    /**
     * A register that can only describe its own account holders is not a
     * provenance register — most institutional holders will never sign up.
     */
    public function test_an_owner_need_not_hold_an_account(): void
    {
        $product = $this->product();

        $cert = ProvenanceRegistry::transfer($product, [
            'legal_name'         => 'Musée du quai Branly',
            'entity_type'        => 'museum',
            'country_code'       => 'FR',
            'verification_level' => 'institution',
        ]);

        $to = DB::table('product_ownerships')->find($cert->to_ownership_id);

        $this->assertNull($to->owner_user_id);
        $this->assertSame('institution', $to->verification_level);
        $this->assertSame('FR', $to->country_code);
    }
}
