<?php

namespace Tests\Feature;

use App\Support\ArtisanVerification;
use App\Support\CertificationAuthority;
use App\Support\ExportRegister;
use App\Support\ProductCertificate;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the machine-readable half of verification.
 *
 * The people the certificates are actually for — museums, insurers, customs
 * offices — do not verify by loading a web page. They verify from inside their
 * own accessioning or clearance system, which speaks HTTP and JSON and nothing
 * else. An HTML verification page that only a human can read is, from their
 * side, the same as no verification at all.
 *
 * Three properties get the weight here. The first is that a status is an
 * answer, not an error: a revoked certificate must come back 200 saying
 * "revoked", because a 404 is indistinguishable from an outage and lets a
 * seller claim the service was merely down. The second is that the answer
 * carries no personal data — the question is "is this document good", never
 * "who owns it", and an unauthenticated endpoint that named owners would be a
 * directory of who holds valuable objects. The third is that the response
 * contains the exact bytes that were signed, so a caller can verify the
 * Ed25519 signature against the published key without trusting this server's
 * own verdict. That last one is the whole reason the API is worth having, and
 * it is asserted here with raw libsodium rather than our own helper.
 */
class VerificationApiTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A signing key has to exist for the offline-verification claim to mean
        // anything; without one every certificate is honestly reported unsigned.
        // A throwaway key under the test's own path. Never the configured one:
        // regenerating that would invalidate every certificate this
        // installation has ever issued, which a test run must not be able to do.
        config(['certificates.ca.key_path' => storage_path('framework/testing/ah237-test-ca.key')]);
        CertificationAuthority::generate(true);
    }

    private function product()
    {
        $product = $this->makeProduct($this->makeBusiness());
        $product->update(['status' => 'published']);

        return $product->fresh();
    }

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

    private function verify(string $reference, array $query = [])
    {
        return $this->getJson('/api/v1/verify/' . rawurlencode($reference)
            . ($query ? '?' . http_build_query($query) : ''));
    }

    /* ───────────────────────── Resolution, all four ─────────────────────── */

    public function test_every_type_resolves_by_certificate_number(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            'coa' => $coa->certificate_no,
            'otc' => $this->transfer()->certificate_no,
            'avc' => $this->verification()->certificate_no,
            'eac' => $this->consignment()->certificate_no,
        ] as $type => $no) {
            $response = $this->verify($no);

            $response->assertOk()
                ->assertHeader('content-type', 'application/json')
                ->assertJsonPath('type', $type)
                ->assertJsonPath('found', true);

            $this->assertNotSame('notfound', $response->json('status'));
        }
    }

    public function test_every_type_resolves_by_uuid(): void
    {
        [, $coa] = $this->coa();

        foreach ([
            'coa' => $coa->uuid,
            'otc' => $this->transfer()->uuid,
            'avc' => $this->verification()->uuid,
            'eac' => $this->consignment()->uuid,
        ] as $type => $uuid) {
            $this->verify($uuid)->assertOk()->assertJsonPath('type', $type);
        }
    }

    public function test_the_answer_carries_the_fields_an_institution_integrates_against(): void
    {
        [, $coa] = $this->coa();

        $body = $this->verify($coa->certificate_no)->assertOk()->json();

        foreach ([
            'reference', 'type', 'type_name', 'status', 'found', 'issued_at',
            'expires_at', 'verification_count', 'signature', 'links',
            'issuer', 'checked_at',
        ] as $field) {
            $this->assertArrayHasKey($field, $body, "the response omits [{$field}]");
        }

        $this->assertSame('EdDSA', $body['signature']['algorithm']);
        $this->assertSame('Ed25519', $body['signature']['curve']);
        $this->assertSame('valid', $body['signature']['state']);
        $this->assertSame($coa->ca_kid, $body['signature']['kid']);
        $this->assertIsInt($body['verification_count']);

        // ISO 8601, because a date rendered in a local format is a date another
        // system has to guess at.
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $body['issued_at']
        );

        // ISO 3166-1 alpha-2, likewise.
        $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $body['issuer']['country']);

        $this->assertNotEmpty($body['links']['document']);
        $this->assertNotEmpty($body['links']['verification_page']);
        $this->assertNotEmpty($body['links']['jwks']);
    }

    /* ─────────────────────────── Status semantics ───────────────────────── */

    public function test_an_unknown_reference_is_404(): void
    {
        $response = $this->verify('AHC-COA-2026-000000999');

        $response->assertNotFound()
            ->assertJsonPath('status', 'notfound')
            ->assertJsonPath('found', false)
            ->assertJsonPath('type', 'unknown');
    }

    public function test_a_malformed_reference_is_400(): void
    {
        foreach (['not a reference', '@@@@', str_repeat('A', 200), 'ref;drop'] as $ref) {
            $this->verify($ref)
                ->assertStatus(400)
                ->assertJsonPath('status', 'malformed');
        }
    }

    /**
     * The heart of it. Each of these is a certificate that exists and should
     * not be relied on, and each has to say so with a 200 — an error code here
     * would let the holder of a revoked document blame the network.
     */
    public function test_revoked_superseded_and_expired_all_answer_200_with_their_status(): void
    {
        $revoked = $this->transfer();
        DB::table('ownership_transfers')->where('id', $revoked->id)->update(['status' => 'revoked']);

        $this->verify($revoked->certificate_no)
            ->assertOk()
            ->assertJsonPath('status', 'revoked')
            ->assertJsonPath('found', true);

        [$product] = $this->coa();
        $first = ProvenanceRegistry::transfer($product, ['legal_name' => 'First Buyer']);
        ProvenanceRegistry::transfer($product->fresh(), ['legal_name' => 'Second Buyer']);

        $this->verify($first->certificate_no)
            ->assertOk()
            ->assertJsonPath('status', 'superseded');

        $c = $this->consignment();
        DB::table('export_consignments')->where('id', $c->id)->update([
            'status'     => 'approved',
            'expires_at' => now()->subDay(),
        ]);

        $this->verify($c->certificate_no)
            ->assertOk()
            ->assertJsonPath('status', 'expired');
    }

    public function test_a_wrong_pin_reports_pin_mismatch_and_withholds_the_contents(): void
    {
        [, $coa] = $this->coa();

        $body = $this->verify($coa->certificate_no, ['pin' => 'WRONGPIN'])
            ->assertOk()
            ->assertJsonPath('status', 'pin_mismatch')
            ->assertJsonPath('found', false)
            ->json();

        $this->assertNull($body['issued_at']);
        $this->assertNull($body['verification_count']);
        $this->assertNull($body['links']['document']);
        $this->assertArrayNotHasKey('content_hash', $body);
        $this->assertStringNotContainsString(
            (string) $coa->content_hash,
            json_encode($body, JSON_UNESCAPED_UNICODE)
        );
    }

    public function test_the_right_pin_still_resolves(): void
    {
        [, $coa] = $this->coa();

        $this->verify($coa->certificate_no, ['pin' => $coa->verification_pin])
            ->assertOk()
            ->assertJsonPath('status', 'valid');
    }

    /* ──────────────────────────── Personal data ─────────────────────────── */

    /**
     * Nothing in here is a secret in isolation. Together, over a namespace of
     * guessable numbers, they would be a list of who owns which valuable object
     * and where to find them — which is a shopping list, not a verification
     * service.
     */
    public function test_no_response_contains_personal_data(): void
    {
        $owner    = $this->makeUser(['name' => 'Adamou Njoya Personne']);
        $business = $this->makeBusiness($owner, [
            'name_fr'           => 'Atelier Adamou Njoya Personne',
            'verification_tier' => 'verified',
            'id_verified_at'    => now(),
        ]);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $coa      = ProductCertificate::forProduct($product->fresh());
        $avc      = ArtisanVerification::forBusiness($business->fresh());
        $transfer = ProvenanceRegistry::transfer($product->fresh(), [
            'legal_name'  => 'Galerie MAM',
            'entity_type' => 'gallery',
        ]);

        $c = ExportRegister::open($product->fresh(), [
            'name'    => 'Musée du quai Branly',
            'type'    => 'museum',
            'country' => 'FR',
        ]);
        $eac = ExportRegister::issue($c->id);

        $forbidden = [
            'Adamou', 'Njoya', 'Personne', 'Galerie MAM',
            'quai Branly', $owner->email,
        ];

        // The artisan verification certificate's own document page is addressed
        // by the business slug, which for a sole trader spells out the artisan's
        // name. That link is withheld rather than published.
        $this->assertNull($this->verify($avc->certificate_no)->json('links.document'));
        $this->assertNotEmpty($this->verify($avc->certificate_no)->json('links.verification_page'));

        foreach ([
            $coa->certificate_no, $avc->certificate_no,
            $transfer->certificate_no, $eac->certificate_no,
        ] as $no) {
            $body = $this->verify($no)->assertOk()->getContent();

            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsStringIgnoringCase(
                    $needle,
                    $body,
                    "[{$no}] leaked [{$needle}]"
                );
            }
        }
    }

    /* ───────────────────── Offline verification recipe ──────────────────── */

    /**
     * The claim the API exists to support: hand the response to raw libsodium
     * with the published key and the signature verifies, with no further call
     * to this server. If this ever fails, every "digitally signed" on a printed
     * certificate is an overstatement.
     */
    public function test_the_published_recipe_verifies_the_signature_offline(): void
    {
        [, $coa] = $this->coa();

        $body = $this->verify($coa->certificate_no)->assertOk()->json();

        $payload   = $body['signature']['payload'];
        $signature = $body['signature']['value'];

        $this->assertNotEmpty($payload);
        $this->assertNotEmpty($signature);

        // Straight from the JWKS the response points at, exactly as a museum's
        // own tooling would fetch it.
        $jwks = $this->getJson($body['links']['jwks'])->assertOk()->json();
        $x    = $jwks['keys'][0]['x'];

        $publicKey = base64_decode(strtr($x, '-_', '+/'));
        $raw       = base64_decode(strtr($signature, '-_', '+/'));

        $this->assertTrue(
            sodium_crypto_sign_verify_detached($raw, $payload, $publicKey),
            'the published payload and signature did not verify against the published key'
        );

        // And the payload really is the pinned recipe, not something reshaped
        // for the wire.
        $this->assertSame(
            CertificationAuthority::payload(
                'coa',
                $coa->certificate_no,
                $coa->content_hash,
                \Illuminate\Support\Carbon::parse($coa->issued_at)->toIso8601String()
            ),
            $payload
        );

        $this->assertNotEmpty($body['signature']['payload_recipe']);
    }

    public function test_the_jwks_mirror_matches_the_well_known_document(): void
    {
        $this->assertSame(
            $this->getJson('/.well-known/jwks.json')->assertOk()->json(),
            $this->getJson('/api/v1/jwks.json')->assertOk()->json()
        );
    }

    /* ──────────────────────────────── Spec ──────────────────────────────── */

    public function test_the_openapi_document_describes_exactly_the_routes_that_exist(): void
    {
        $spec = $this->getJson('/api/v1/openapi.json')->assertOk()->json();

        $this->assertSame('3.1.0', $spec['openapi']);
        $this->assertNotEmpty($spec['info']['title']);

        $declared = array_map(
            fn ($p) => ltrim($p, '/'),
            array_keys($spec['paths'])
        );

        $registered = collect(Route::getRoutes())
            ->filter(fn ($r) => str_starts_with($r->uri(), 'api/v1/')
                && in_array('GET', $r->methods(), true)
                && str_contains($r->getName() ?? '', 'api.verification'))
            ->map(fn ($r) => $r->uri())
            ->values()
            ->all();

        sort($declared);
        sort($registered);

        $this->assertSame($registered, $declared);
        $this->assertNotEmpty($declared);
    }

    public function test_the_openapi_schema_names_the_fields_actually_returned(): void
    {
        [, $coa] = $this->coa();

        $spec = $this->getJson('/api/v1/openapi.json')->assertOk()->json();
        $body = $this->verify($coa->certificate_no)->assertOk()->json();

        $schema = $spec['components']['schemas']['VerificationResult']['properties'] ?? [];

        $this->assertNotEmpty($schema);
        $this->assertSame(
            [],
            array_diff(array_keys($schema), array_keys($body)),
            'the spec declares properties the endpoint does not return'
        );
        $this->assertSame(
            [],
            array_diff(array_keys($body), array_keys($schema)),
            'the endpoint returns properties the spec does not declare'
        );
    }

    /**
     * The checking tool is often a page served from the institution's own
     * intranet, so the browser has to be allowed to read the answer.
     */
    public function test_cross_origin_get_is_allowed(): void
    {
        [, $coa] = $this->coa();

        $this->getJson('/api/v1/verify/' . $coa->certificate_no, ['Origin' => 'https://museum.example'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    /* ───────────────────────────── Rate limiting ────────────────────────── */

    public function test_the_verification_route_is_rate_limited(): void
    {
        $route = collect(Route::getRoutes())
            ->first(fn ($r) => $r->uri() === 'api/v1/verify/{reference}');

        $this->assertNotNull($route, 'the verification route is not registered');

        $this->assertTrue(
            collect($route->gatherMiddleware())
                ->contains(fn ($m) => is_string($m) && str_starts_with($m, 'throttle:')),
            'an unauthenticated lookup over a guessable namespace is not throttled'
        );
    }
}
