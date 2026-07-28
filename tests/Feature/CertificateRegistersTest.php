<?php

namespace Tests\Feature;

use App\Support\ArtisanVerification;
use App\Support\CertificationAuthority;
use App\Support\ProductFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the registers the certificate ticks are read from.
 *
 * Every assertion here exists because the alternative is a document that
 * reassures a buyer about something nobody ever checked. Two properties are
 * defended: a tick only appears when a row supports it, and a fact the platform
 * cannot determine is absent from the map rather than rendered as a reassuring
 * false — a printed "no" is itself a claim, and we are not entitled to it.
 */
class CertificateRegistersTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product()
    {
        return $this->makeProduct($this->makeBusiness())->fresh();
    }

    /** A business with enough real evidence behind it to be certified at all. */
    private function verifiedBusiness(array $attrs = [])
    {
        return $this->makeBusiness(null, array_merge([
            'verification_tier' => 'verified',
            'id_verified_at'    => now(),
        ], $attrs));
    }

    /* ─────────────────────────────── Flags ─────────────────────────────── */

    public function test_a_stolen_report_clears_the_tick_and_resolving_it_restores(): void
    {
        $product = $this->product();

        $this->assertTrue(ProductFlags::isClear($product));
        $this->assertTrue(ProductFlags::checks($product)['not_reported_stolen']);

        $flag = ProductFlags::raise($product, 'reported_stolen', [
            'reference' => 'PV-2026-00871',
            'notes'     => 'Reported to the Douala IIe judicial police.',
        ]);

        $this->assertFalse(ProductFlags::isClear($product));
        $this->assertFalse(ProductFlags::checks($product)['not_reported_stolen']);
        $this->assertCount(1, ProductFlags::active($product));

        ProductFlags::resolve($flag->id, 'Recovered; report closed.');

        $this->assertTrue(ProductFlags::isClear($product));
        $this->assertTrue(ProductFlags::checks($product)['not_reported_stolen']);
        $this->assertSame([], ProductFlags::active($product));

        // Resolved, not deleted: the history stays readable.
        $this->assertSame(1, DB::table('product_flags')->where('product_id', $product->id)->count());
    }

    public function test_each_flag_only_moves_its_own_tick(): void
    {
        $product = $this->product();

        ProductFlags::raise($product, 'disputed_ownership');
        $checks = ProductFlags::checks($product);

        $this->assertFalse($checks['ownership_undisputed']);
        $this->assertTrue($checks['not_reported_stolen']);
        $this->assertTrue($checks['not_under_investigation']);
    }

    /**
     * The platform holds no CITES register and issues no export permits, so it
     * cannot tell anyone a piece may leave the country. That check must be
     * absent from the map, never a false — a printed "not export ready" would be
     * an assertion we equally cannot make.
     */
    public function test_checks_omit_what_the_platform_cannot_determine(): void
    {
        $checks = ProductFlags::checks($this->product());

        $this->assertArrayNotHasKey('export_ready', $checks);
        $this->assertArrayNotHasKey('export_cleared', $checks);
        $this->assertSame(
            ['not_reported_stolen', 'not_under_investigation', 'ownership_undisputed'],
            array_keys($checks)
        );
    }

    /* ─────────────────────── Artisan verification ──────────────────────── */

    public function test_an_unverified_business_gets_no_certificate(): void
    {
        $business = $this->makeBusiness();     // verification_tier defaults to unverified

        $this->assertSame(0, ArtisanVerification::levelFor($business));
        $this->assertNull(ArtisanVerification::forBusiness($business));
        $this->assertSame(0, DB::table('artisan_verifications')->count());
    }

    public function test_a_verified_business_gets_one_certificate_numbered_hashed_and_signed(): void
    {
        $business = $this->verifiedBusiness();

        $cert = ArtisanVerification::forBusiness($business);

        $this->assertNotNull($cert);
        $this->assertMatchesRegularExpression('/^AH237-AVC-CM-\d{4}-\d{10}$/', $cert->certificate_no);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $cert->content_hash);
        $this->assertNotEmpty($cert->verification_pin);
        $this->assertNotNull($cert->issued_at);

        // The artisan's permanent number is assigned as part of issuing.
        $this->assertMatchesRegularExpression('/^AH237-GAN-CM-\d{10}$/', $business->fresh()->gan);

        // Issued once, then reused — a second request must not burn a number.
        $again = ArtisanVerification::forBusiness($business->fresh());
        $this->assertSame($cert->id, $again->id);
        $this->assertSame(1, DB::table('artisan_verifications')->count());

        // And issuance is on the audit trail.
        $this->assertSame('issued', \App\Support\ProvenanceRegistry::trail('avc', $cert->id)[0]->event);
    }

    public function test_the_authority_signature_verifies(): void
    {
        // A throwaway key under the test's own path. Never the configured one:
        // regenerating that would invalidate every certificate this
        // installation has ever issued, which a test run must not be able to do.
        config(['certificates.ca.key_path' => storage_path('framework/testing/ah237-test-ca.key')]);
        CertificationAuthority::generate(true);

        $cert = ArtisanVerification::forBusiness($this->verifiedBusiness());

        $this->assertNotNull($cert->ca_signature);
        $this->assertTrue(CertificationAuthority::verifyCertificate(
            'avc',
            $cert->certificate_no,
            $cert->content_hash,
            \Illuminate\Support\Carbon::parse($cert->issued_at)->toIso8601String(),
            $cert->ca_signature
        ));

        // A signature over a different certificate number must not verify.
        $this->assertFalse(CertificationAuthority::verifyCertificate(
            'avc',
            $cert->certificate_no . 'X',
            $cert->content_hash,
            \Illuminate\Support\Carbon::parse($cert->issued_at)->toIso8601String(),
            $cert->ca_signature
        ));

        $result = ArtisanVerification::verify($cert->certificate_no, $cert->verification_pin);
        $this->assertSame('valid', $result['status']);
        $this->assertSame('valid', $result['signature']['state']);
        $this->assertSame(1, (int) DB::table('artisan_verifications')->find($cert->id)->verification_count);

        $this->assertSame('pin_mismatch', ArtisanVerification::verify($cert->certificate_no, 'WRONGPIN')['status']);
        $this->assertSame('notfound', ArtisanVerification::verify('AH237-AVC-CM-2026-9999999999')['status']);
    }

    /**
     * The ladder is evidence, not flattery. A shop that has been verified and
     * nothing more is level 1; "master" means a third party certified them.
     */
    public function test_the_level_never_exceeds_the_evidence(): void
    {
        $bare = $this->verifiedBusiness();

        $this->assertSame(1, ArtisanVerification::levelFor($bare));
        $this->assertLessThan(5, ArtisanVerification::levelFor($bare), 'A bare verified shop came out as a master.');

        // Trade paperwork accepted by a reviewer takes it to professional.
        $app = DB::table('verification_applications')->insertGetId([
            'business_id' => $bare->id, 'tier_requested' => 'verified', 'status' => 'approved',
            'reviewed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('verification_documents')->insert([
            'application_id' => $app, 'type' => 'rccm', 'file_path' => 'docs/rccm.pdf',
            'original_name' => 'rccm.pdf', 'status' => 'accepted',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertSame(2, ArtisanVerification::levelFor($bare->fresh()));

        // A workshop address on record is the third rung, and no higher.
        $bare->update(['address_fr' => 'Quartier Bonabéri, Douala']);
        $this->assertSame(3, ArtisanVerification::levelFor($bare->fresh()));
    }

    public function test_checks_and_metrics_report_only_what_the_database_holds(): void
    {
        $business = $this->verifiedBusiness();
        $this->makeProduct($business);

        $checks  = ArtisanVerification::checksFor($business);
        $metrics = ArtisanVerification::metricsFor($business);

        $this->assertTrue($checks['identity_document_verified']);
        $this->assertFalse($checks['workshop_address_on_record']);
        $this->assertTrue($checks['portfolio_published']);

        // Checks the platform does not perform must not appear at all.
        foreach (['tax_compliance_checked', 'criminal_record_checked', 'site_visit_completed', 'quality_inspected'] as $never) {
            $this->assertArrayNotHasKey($never, $checks);
        }

        $this->assertSame(1, $metrics['products_registered']);
        $this->assertSame(1, $metrics['published_products']);

        // year_established is null on this shop, so there is no years_active.
        $this->assertArrayNotHasKey('years_active', $metrics);
        $this->assertArrayNotHasKey('average_rating', $metrics);
    }

    /* ───────────────────────── Identity document ───────────────────────── */

    public function test_the_identity_document_number_is_encrypted_at_rest(): void
    {
        $number   = '108234567891';
        $business = $this->makeBusiness();

        ArtisanVerification::recordIdentityDocument($business, 'national_id', $number);

        $row = DB::table('businesses')->find($business->id);

        // The raw number appears nowhere in the row — not in the column meant to
        // hold it, and not anywhere else on the record either.
        $this->assertNotSame($number, $row->id_document_encrypted);
        $this->assertStringNotContainsString($number, (string) $row->id_document_encrypted);
        $this->assertStringNotContainsString($number, json_encode((array) $row));

        // Only the last four are readable, and the ciphertext still decrypts for
        // the one reviewer screen entitled to it.
        $this->assertSame('7891', $row->id_document_last4);
        $this->assertSame($number, Crypt::decryptString($row->id_document_encrypted));
        $this->assertSame('•••• 7891', ArtisanVerification::maskedIdentityDocument($business->fresh()));

        // Filing a document is not verifying it.
        $this->assertNull($row->id_verified_at);
        $this->assertFalse(ArtisanVerification::checksFor($business->fresh())['identity_document_verified']);
    }
}
