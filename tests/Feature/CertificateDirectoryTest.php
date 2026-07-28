<?php

namespace Tests\Feature;

use App\Support\ArtisanVerification;
use App\Support\CertificateDirectory;
use App\Support\CertificationAuthority;
use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the one lookup that stands behind every verification address the
 * platform prints.
 *
 * The failure this defends against is quiet and specific: a certificate that
 * names a verification URL a holder cannot resolve. Four registers issue
 * numbered documents; a single page has to answer for all four, and answer
 * "no" honestly for a reference that belongs to none of them.
 *
 * Two properties get the most attention here. The first is that a mistyped
 * reference comes back notfound rather than being coerced into whichever type
 * its prefix resembles — a wrong-type match is worse than no match, because it
 * shows a stranger's real document in answer to a forged number. The second is
 * that a wrong PIN and an unknown number are not distinguishable ways of
 * probing the register: the PIN is compared with hash_equals, so the comparison
 * itself leaks nothing about how much of the guess was right.
 */
class CertificateDirectoryTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function product()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    /** A published product carrying a live Certificate of Authenticity. */
    private function coa(): array
    {
        $product = $this->product();

        return [$product, ProductCertificate::forProduct($product)];
    }

    private function transfer(): object
    {
        [$product] = $this->coa();

        return ProvenanceRegistry::transfer($product, [
            'legal_name'  => 'Galerie MAM',
            'entity_type' => 'gallery',
        ]);
    }

    private function verification(): object
    {
        $business = $this->makeBusiness(null, [
            'verification_tier' => 'verified',
            'id_verified_at'    => now(),
        ]);

        return ArtisanVerification::forBusiness($business->fresh());
    }

    private function consignment(): object
    {
        [$product] = $this->coa();

        $c = ExportRegister::open($product, [
            'name'    => 'Musée du quai Branly',
            'type'    => 'museum',
            'country' => 'FR',
        ]);

        return ExportRegister::issue($c->id);
    }

    /* ────────────────────── Resolution across the four ─────────────────── */

    public function test_each_type_resolves_by_certificate_number(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            'coa' => $coa->certificate_no,
            'otc' => $this->transfer()->certificate_no,
            'avc' => $this->verification()->certificate_no,
            'eac' => $this->consignment()->certificate_no,
        ] as $type => $no) {
            $r = CertificateDirectory::resolve($no);

            $this->assertSame($type, $r['type'], "{$no} resolved as {$r['type']}");
            $this->assertNotSame('notfound', $r['status']);
            $this->assertNotNull($r['certificate']);
            $this->assertNotNull($r['document_url']);
        }
    }

    public function test_each_type_resolves_by_uuid(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            'coa' => $coa->uuid,
            'otc' => $this->transfer()->uuid,
            'avc' => $this->verification()->uuid,
            'eac' => $this->consignment()->uuid,
        ] as $type => $uuid) {
            $r = CertificateDirectory::resolve($uuid);

            $this->assertSame($type, $r['type'], "{$uuid} resolved as {$r['type']}");
            $this->assertNotSame('notfound', $r['status']);
        }
    }

    /* ──────────────────────────── Wrong answers ────────────────────────── */

    public function test_an_unknown_reference_is_notfound(): void
    {
        foreach (['', 'nonsense', 'AHC', '../../etc/passwd', str_repeat('A', 200)] as $ref) {
            $r = CertificateDirectory::resolve($ref);

            $this->assertSame('notfound', $r['status'], "[{$ref}] was not notfound");
            $this->assertSame('unknown', $r['type']);
            $this->assertNull($r['certificate']);
        }
    }

    /**
     * The case that matters most. These references have the exact shape of a
     * real number of each type — right prefix, right year, right padding — and
     * no row behind them. Anything other than notfound would mean the lookup
     * had matched on the prefix and gone looking in a table for whatever it
     * could find.
     */
    public function test_a_valid_shape_with_no_record_is_notfound_not_a_wrong_type_match(): void
    {
        $this->coa();
        $this->transfer();
        $this->verification();
        $this->consignment();

        foreach ([
            'AHC-COA-2026-000000999',
            'AH237-OTC-CM-2026-000000999999',
            'AH237-AVC-CM-2026-0000999999',
            'AH237-EAC-CM-2026-000000999999',
            '00000000-0000-4000-8000-000000000000',
        ] as $ref) {
            $r = CertificateDirectory::resolve($ref);

            $this->assertSame('notfound', $r['status'], "[{$ref}] was not notfound");
            $this->assertSame('unknown', $r['type'], "[{$ref}] matched type {$r['type']}");
        }
    }

    public function test_a_wrong_pin_returns_pin_mismatch_and_no_certificate(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            $coa->certificate_no                 => $coa->verification_pin,
            $this->transfer()->certificate_no    => null,
            $this->verification()->certificate_no => null,
            $this->consignment()->certificate_no => null,
        ] as $no => $ignored) {
            $r = CertificateDirectory::resolve($no, 'WRONGPIN');

            $this->assertSame('pin_mismatch', $r['status'], "[{$no}] did not report pin_mismatch");
            $this->assertNull($r['certificate'], "[{$no}] handed back the certificate anyway");
        }
    }

    public function test_the_right_pin_still_resolves(): void
    {
        [, $coa] = $this->coa();

        $r = CertificateDirectory::resolve($coa->certificate_no, $coa->verification_pin);

        $this->assertSame('valid', $r['status']);
        $this->assertSame('coa', $r['type']);
    }

    /**
     * Timing: two wrong PINs of the same length must behave identically, and
     * neither may behave differently from the other because of how much of the
     * guess was correct. Asserted structurally — the source uses hash_equals —
     * because a wall-clock assertion on a test runner is noise.
     */
    public function test_pin_comparison_is_constant_time(): void
    {
        [, $coa] = $this->coa();

        $source = file_get_contents(app_path('Support/CertificateDirectory.php'));
        $this->assertStringContainsString('hash_equals', $source);

        $pin   = strtoupper((string) $coa->verification_pin);
        $near  = substr($pin, 0, -1) . ($pin[strlen($pin) - 1] === 'X' ? 'Y' : 'X');
        $far   = str_repeat('X', strlen($pin));

        $this->assertSame(
            CertificateDirectory::resolve($coa->certificate_no, $near),
            CertificateDirectory::resolve($coa->certificate_no, $far)
        );
    }

    /* ───────────────────────────── Signature ───────────────────────────── */

    public function test_the_signature_state_matches_the_authority(): void
    {
        // A throwaway key under the test's own path. Never the configured one:
        // regenerating that would invalidate every certificate this
        // installation has ever issued, which a test run must not be able to do.
        config(['certificates.ca.key_path' => storage_path('framework/testing/ah237-test-ca.key')]);
        CertificationAuthority::generate(true);

        [, $coa] = $this->coa();
        $r = CertificateDirectory::resolve($coa->certificate_no);

        $this->assertSame('valid', $r['signature']['state']);
        $this->assertSame($coa->ca_kid, $r['signature']['kid']);
        $this->assertTrue(CertificationAuthority::verifyCertificate(
            'coa', $coa->certificate_no, $coa->content_hash,
            \Illuminate\Support\Carbon::parse($coa->issued_at)->toIso8601String(),
            $coa->ca_signature
        ));

        $transfer = $this->transfer();
        $t = CertificateDirectory::resolve($transfer->certificate_no);

        $this->assertSame(
            CertificationAuthority::verifyCertificate(
                'otc', $transfer->certificate_no, $transfer->content_hash,
                \Illuminate\Support\Carbon::parse($transfer->issued_at)->toIso8601String(),
                $transfer->ca_signature
            ) ? 'valid' : 'invalid',
            $t['signature']['state']
        );
    }

    /* ───────────────────────── Type-specific states ────────────────────── */

    public function test_a_transfer_superseded_by_a_later_one_reports_superseded(): void
    {
        [$product] = $this->coa();

        $first = ProvenanceRegistry::transfer($product, ['legal_name' => 'First Buyer']);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Second Buyer']);

        $r = CertificateDirectory::resolve($first->certificate_no);

        $this->assertSame('otc', $r['type']);
        $this->assertSame('superseded', $r['status']);
    }

    public function test_an_export_certificate_past_its_expiry_reports_expired(): void
    {
        $c = $this->consignment();

        DB::table('export_consignments')->where('id', $c->id)->update([
            'status'     => 'approved',
            'expires_at' => now()->subDay(),
        ]);

        $r = CertificateDirectory::resolve($c->certificate_no);

        $this->assertSame('eac', $r['type']);
        $this->assertSame('expired', $r['status']);
    }

    public function test_a_revoked_transfer_reports_revoked_not_expired_or_superseded(): void
    {
        $t = $this->transfer();

        DB::table('ownership_transfers')->where('id', $t->id)->update(['status' => 'revoked']);

        $this->assertSame('revoked', CertificateDirectory::resolve($t->certificate_no)['status']);
    }

    /* ────────────────────────────── The page ───────────────────────────── */

    public function test_the_verification_page_names_the_right_document_type(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            $coa->certificate_no                  => 'Certificate of Authenticity',
            $this->transfer()->certificate_no     => 'Ownership Transfer Certificate',
            $this->verification()->certificate_no => 'Artisan Verification Certificate',
            $this->consignment()->certificate_no  => 'Export Authenticity Certificate',
        ] as $no => $label) {
            $this->get('/verifier?lang=en&ref=' . urlencode($no))
                ->assertOk()
                ->assertSee($label);
        }
    }

    public function test_the_page_keeps_its_honesty_about_what_a_certificate_proves(): void
    {
        [, $coa] = $this->coa();

        $this->get('/verifier?lang=en&ref=' . urlencode($coa->certificate_no))
            ->assertOk()
            ->assertSee('does not prove that the object in front of you is the one in the photographs');
    }

    public function test_the_page_points_at_the_published_key(): void
    {
        [, $coa] = $this->coa();

        $this->get('/verifier?lang=en&ref=' . urlencode($coa->certificate_no))
            ->assertOk()
            ->assertSee('/.well-known/jwks.json');
    }
}
