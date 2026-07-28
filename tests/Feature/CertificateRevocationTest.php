<?php

namespace Tests\Feature;

use App\Support\ArtisanVerification;
use App\Support\CertificateRevocation;
use App\Support\CertificationAuthority;
use App\Support\ProductCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the one thing that makes "check the live register" mean anything.
 *
 * Every certificate this platform issues prints an instruction to verify it
 * before relying on it. That instruction is only worth the paper if a
 * withdrawn or forged number can actually be found to be withdrawn — by the
 * holder, publicly, without asking us. So the properties under test here are
 * not really about a table. They are: a revocation reaches both the
 * certificate and the public list or neither; the list is searchable by the
 * number a reader is looking at; and the list says the minimum a reader needs
 * and not one word more about the artisan behind it.
 *
 * That last one gets the most attention. `fraud` and `forgery` published beside
 * a certificate number are already a grave public statement about a named
 * person's work. The reason note, the officer who signed it and the subject's
 * details stay out of the response shape entirely — not merely unrendered in
 * the view, absent from what publicList() hands back, so that a future
 * template cannot leak them by accident.
 */
class CertificateRevocationTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** A published product carrying a live Certificate of Authenticity. */
    private function coa(): object
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return ProductCertificate::forProduct($product->fresh());
    }

    private function avc(): object
    {
        $business = $this->makeBusiness(null, [
            'verification_tier' => 'verified',
            'id_verified_at'    => now(),
        ]);

        return ArtisanVerification::forBusiness($business->fresh());
    }

    /* ───────────────────────── Writing a revocation ────────────────────── */

    public function test_revoking_flips_the_certificate_and_writes_the_row(): void
    {
        $cert = $this->coa();

        $revocation = CertificateRevocation::revoke('coa', $cert->id, 'forgery', [
            'certificate_no' => $cert->certificate_no,
            'note'           => 'Internal file reference only.',
        ]);

        $this->assertNotNull($revocation->uuid);

        // Both halves, or the record is in the worst possible state: withdrawn
        // in one place and live in the other.
        $row = DB::table('product_certificates')->find($cert->id);
        $this->assertNotNull($row->revoked_at, 'the certificate itself was not marked revoked');

        $this->assertDatabaseHas('certificate_revocations', [
            'certificate_type' => 'coa',
            'certificate_id'   => $cert->id,
            'reason'           => 'forgery',
        ]);

        $this->assertTrue(CertificateRevocation::isRevoked('coa', $cert->id));
    }

    public function test_a_revoked_certificate_verifies_as_revoked(): void
    {
        $cert = $this->coa();

        CertificateRevocation::revoke('coa', $cert->id, 'fraud');

        $result = ProductCertificate::verify($cert->certificate_no, null);
        $this->assertSame('revoked', $result['status']);

        $avc = $this->avc();
        CertificateRevocation::revoke('avc', $avc->id, 'court_order');

        $this->assertSame('revoked', ArtisanVerification::verify($avc->certificate_no, null)['status']);
    }

    public function test_revoking_twice_does_not_duplicate(): void
    {
        $cert = $this->coa();

        CertificateRevocation::revoke('coa', $cert->id, 'fraud');
        CertificateRevocation::revoke('coa', $cert->id, 'forgery');

        $this->assertSame(1, DB::table('certificate_revocations')
            ->where('certificate_type', 'coa')->where('certificate_id', $cert->id)->count());
    }

    /* ──────────────────────────── The public list ──────────────────────── */

    public function test_the_public_list_shows_the_facts_and_withholds_the_rest(): void
    {
        $officer = $this->makeUser(['name' => 'Inspecteur Ondoa']);
        $cert    = $this->coa();

        CertificateRevocation::revoke('coa', $cert->id, 'fraud', [
            'note'  => 'Signalé par la brigade de Douala, dossier 44/2026.',
            'actor' => $officer->id,
        ]);

        $list = CertificateRevocation::publicList();
        $this->assertCount(1, $list);

        $entry = (array) $list[0];

        $this->assertSame($cert->certificate_no, $entry['certificate_no']);
        $this->assertSame('coa', $entry['certificate_type']);
        $this->assertSame('fraud', $entry['reason']);
        $this->assertNotNull($entry['revoked_at']);

        // Absent from the shape, not merely unrendered.
        $serialised = json_encode($list);
        foreach (['reason_note', 'revoked_by_user_id', 'brigade de Douala', '44/2026', $officer->id, $officer->name] as $secret) {
            $this->assertStringNotContainsString((string) $secret, $serialised, "the public list leaked [{$secret}]");
        }
    }

    public function test_the_public_page_renders_the_list_without_personal_data(): void
    {
        $cert = $this->coa();
        CertificateRevocation::revoke('coa', $cert->id, 'forgery', [
            'note' => 'Nom du plaignant: Awono.',
        ]);

        $response = $this->get('/certificats-revoques');

        $response->assertOk();
        $response->assertSee($cert->certificate_no);
        $response->assertDontSee('Awono');
        $response->assertDontSee('plaignant');
    }

    public function test_search_by_certificate_number_finds_it(): void
    {
        $cert  = $this->coa();
        $other = $this->coa();

        CertificateRevocation::revoke('coa', $cert->id, 'security_breach');
        CertificateRevocation::revoke('coa', $other->id, 'owner_request');

        $found = CertificateRevocation::publicList(['q' => $cert->certificate_no]);
        $this->assertCount(1, $found);
        $this->assertSame($cert->certificate_no, ((array) $found[0])['certificate_no']);

        $this->assertNotNull(CertificateRevocation::forCertificateNo($cert->certificate_no));
        $this->assertNull(CertificateRevocation::forCertificateNo('AHC-COA-2026-999999999'));

        $this->get('/certificats-revoques?q=' . urlencode($cert->certificate_no))
            ->assertOk()
            ->assertSee($cert->certificate_no);
    }

    public function test_an_empty_list_renders_an_honest_empty_state(): void
    {
        $this->assertSame([], CertificateRevocation::publicList());

        $this->get('/certificats-revoques')->assertOk();
    }

    /* ───────────────────────────── Reinstatement ───────────────────────── */

    public function test_reinstating_keeps_the_row_and_records_an_event(): void
    {
        $cert = $this->coa();

        $revocation = CertificateRevocation::revoke('coa', $cert->id, 'administrative_error');

        CertificateRevocation::reinstate($revocation->id, 'Wrong certificate number keyed in.');

        // Never deleted: the fact of a public withdrawal is itself history.
        $this->assertDatabaseHas('certificate_revocations', ['id' => $revocation->id]);

        $this->assertFalse(CertificateRevocation::isRevoked('coa', $cert->id));
        $this->assertSame([], CertificateRevocation::publicList());

        $this->assertNull(DB::table('product_certificates')->find($cert->id)->revoked_at);

        $this->assertTrue(DB::table('certificate_events')
            ->where('certificate_type', 'coa')->where('certificate_id', $cert->id)
            ->where('event', 'reinstated')->exists());
    }

    /* ────────────────────────────── The chain ──────────────────────────── */

    public function test_the_hash_chain_records_the_revocation(): void
    {
        $cert = $this->coa();

        CertificateRevocation::revoke('coa', $cert->id, 'court_order');

        $this->assertTrue(DB::table('certificate_events')
            ->where('certificate_type', 'coa')->where('certificate_id', $cert->id)
            ->where('event', 'revoked')->exists());

        $this->assertTrue(CertificationAuthority::verifyChain()['ok'], 'the event chain no longer verifies');
    }
}
