<?php

namespace Tests\Feature;

use App\Support\CertificationAuthority;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the claim that these certificates are digitally signed.
 *
 * The bar for that phrase on a provenance document is not "the server can
 * recognise its own work" — an HMAC does that, and it is worthless to a museum
 * or an insurer, who would have to ask us and believe the answer. The bar is
 * that a stranger holding only the printed fields and a public key they fetched
 * once can decide for themselves, offline, years later.
 *
 * So the central test here deliberately does not call our verifier. It rebuilds
 * the signed payload from what is printed on the certificate, pulls the key out
 * of the published JWKS, and checks the signature with raw libsodium — exactly
 * what an outside party would do.
 */
class CertificationAuthorityTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function publishedProduct()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

    public function test_the_authority_publishes_a_standard_jwks(): void
    {
        $jwks = $this->get('/.well-known/jwks.json')->assertOk()->json();

        $this->assertCount(1, $jwks['keys']);
        $key = $jwks['keys'][0];

        // RFC 8037: an Ed25519 public key is an OKP key, not RSA or EC.
        $this->assertSame('OKP', $key['kty']);
        $this->assertSame('Ed25519', $key['crv']);
        $this->assertSame('EdDSA', $key['alg']);
        $this->assertSame('sig', $key['use']);
        $this->assertNotEmpty($key['kid']);

        // 32 raw bytes, base64url, unpadded.
        $raw = base64_decode(strtr($key['x'], '-_', '+/'), true);
        $this->assertSame(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES, strlen($raw));
        $this->assertStringNotContainsString('=', $key['x']);
    }

    /** The private key must never be reachable over HTTP. */
    public function test_the_signing_key_is_not_published(): void
    {
        $jwks = $this->get('/.well-known/jwks.json')->assertOk()->getContent();

        $secret = base64_encode(sodium_crypto_sign_secretkey(
            sodium_crypto_sign_seed_keypair(str_repeat("\0", 32))
        ));

        $this->assertStringNotContainsString('key_path', $jwks);
        $this->assertStringNotContainsString(substr($secret, 0, 20), $jwks);
        $this->assertArrayNotHasKey('d', $this->get('/.well-known/jwks.json')->json('keys.0'));
    }

    /**
     * The test that earns the phrase "digitally signed": verification performed
     * the way an outsider would, with no help from our own code.
     */
    public function test_a_third_party_can_verify_a_certificate_without_us(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertNotNull($cert->ca_signature, 'The certificate carries no authority signature.');

        // Everything below is what a stranger has: the printed fields, and the
        // key from the public endpoint.
        $key = $this->get('/.well-known/jwks.json')->json('keys.0');
        $publicKey = base64_decode(strtr($key['x'], '-_', '+/'), true);

        $payload = implode("\n", [
            'ah237.certificate.v1',
            'coa',
            $cert->certificate_no,
            $cert->content_hash,
            Carbon::parse($cert->issued_at)->toIso8601String(),
        ]);

        $signature = base64_decode(strtr($cert->ca_signature, '-_', '+/'), true);

        $this->assertTrue(
            sodium_crypto_sign_verify_detached($signature, $payload, $publicKey),
            'An independent verifier could not check the certificate.'
        );

        // And the signature must not verify for a different certificate number,
        // or it would prove nothing about this one.
        $tampered = str_replace('coa', 'coa', $payload) . 'x';
        $this->assertFalse(sodium_crypto_sign_verify_detached($signature, $tampered, $publicKey));

        $this->assertSame($key['kid'], $cert->ca_kid);
    }

    public function test_transfer_certificates_are_signed_by_the_authority_too(): void
    {
        $product = $this->publishedProduct();
        $cert = ProvenanceRegistry::transfer($product, [
            'legal_name' => 'Global Heritage Museum', 'entity_type' => 'museum', 'country_code' => 'FR',
        ]);

        $this->assertNotNull($cert->ca_signature);
        $this->assertTrue(CertificationAuthority::verifyCertificate(
            'otc', $cert->certificate_no, $cert->content_hash,
            Carbon::parse($cert->issued_at)->toIso8601String(), $cert->ca_signature
        ));

        // A transfer certificate's signature must not validate as an authenticity
        // certificate: the document type is part of what is signed.
        $this->assertFalse(CertificationAuthority::verifyCertificate(
            'coa', $cert->certificate_no, $cert->content_hash,
            Carbon::parse($cert->issued_at)->toIso8601String(), $cert->ca_signature
        ));
    }

    public function test_tampering_with_a_stored_certificate_breaks_its_signature(): void
    {
        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertSame('valid', ProductCertificate::signatureState($cert)['state']);

        // Someone with database access rewrites the certified hash.
        DB::table('product_certificates')->where('id', $cert->id)
            ->update(['content_hash' => hash('sha256', 'a different product entirely')]);

        $this->assertSame('invalid', ProductCertificate::signatureState(
            DB::table('product_certificates')->find($cert->id)
        )['state']);
    }

    /* ───────────────────────── Tamper-evident log ──────────────────────── */

    public function test_the_event_log_is_a_hash_chain(): void
    {
        $product = $this->publishedProduct();
        ProductCertificate::forProduct($product);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Galerie Douala', 'entity_type' => 'gallery']);

        $rows = DB::table('certificate_events')->orderBy('id')->get();
        $this->assertGreaterThanOrEqual(2, $rows->count());

        // Each entry commits to the one before it.
        $this->assertSame(str_repeat('0', 64), $rows->first()->prev_hash);
        $prev = null;
        foreach ($rows as $row) {
            if ($prev) {
                $this->assertSame($prev->entry_hash, $row->prev_hash);
            }
            $prev = $row;
        }

        $this->assertTrue(CertificationAuthority::verifyChain()['ok']);
        $this->assertSame($rows->last()->entry_hash, CertificationAuthority::head());
    }

    /**
     * The property the specification wanted a blockchain for: history cannot be
     * altered or removed without the break being detectable.
     */
    public function test_rewriting_history_breaks_the_chain(): void
    {
        $product = $this->publishedProduct();
        ProductCertificate::forProduct($product);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Galerie Douala', 'entity_type' => 'gallery']);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Musée National', 'entity_type' => 'museum']);

        $this->assertTrue(CertificationAuthority::verifyChain()['ok']);

        $victim = DB::table('certificate_events')->orderBy('id')->skip(1)->first();
        DB::table('certificate_events')->where('id', $victim->id)->update(['event' => 'approved']);

        $result = CertificationAuthority::verifyChain();
        $this->assertFalse($result['ok'], 'An edited event went undetected.');
        $this->assertSame((int) $victim->id, $result['broken_at']);

        // Deleting an entry is caught as well.
        DB::table('certificate_events')->where('id', $victim->id)->update(['event' => $victim->event]);
        $this->assertTrue(CertificationAuthority::verifyChain()['ok']);
        DB::table('certificate_events')->where('id', $victim->id)->delete();
        $this->assertFalse(CertificationAuthority::verifyChain()['ok']);
    }

    /**
     * A missing key must degrade to unsigned rather than blocking issuance or,
     * worse, storing something that looks like a signature and verifies against
     * nothing.
     */
    public function test_an_absent_key_yields_no_signature_rather_than_a_fake_one(): void
    {
        config(['certificates.ca.key_path' => storage_path('app/ca/does-not-exist.key')]);
        $this->refreshAuthorityCache();

        $this->assertFalse(CertificationAuthority::isConfigured());
        $this->assertNull(CertificationAuthority::sign('anything'));
        $this->assertFalse(CertificationAuthority::verify('anything', null));
        $this->assertSame(['keys' => []], CertificationAuthority::jwks());

        $product = $this->publishedProduct();
        $cert    = ProductCertificate::forProduct($product);

        $this->assertNotNull($cert, 'Issuance must not be blocked by a missing key.');
        $this->assertNull($cert->ca_signature);
        $this->assertSame('unsigned', ProductCertificate::signatureState($cert)['state']);
    }

    /** The in-process key cache has to be cleared when the path changes. */
    private function refreshAuthorityCache(): void
    {
        $r = new \ReflectionClass(CertificationAuthority::class);
        $p = $r->getProperty('cache');
        $p->setAccessible(true);
        $p->setValue(null, null);
    }

    protected function tearDown(): void
    {
        $this->refreshAuthorityCache();
        parent::tearDown();
    }
}
