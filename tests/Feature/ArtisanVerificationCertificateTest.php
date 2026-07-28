<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Support\ArtisanVerification;
use App\Support\ProvenanceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the Artisan Verification Certificate.
 *
 * This document is the one that names a real person, so the cost of a
 * fabricated line is highest here: an invented national award, a printed
 * identity number, a "biometric reference" the platform never took. The design
 * being replicated carries all three. These tests exist so that a later pass at
 * making the page look more like the artwork cannot quietly put them back.
 *
 * The second property is the ladder. Showing an artisan the seven rungs and
 * where they stand is useful; colouring in a rung they have not earned is a
 * forgery of a credential, so an unattained rung must never read as attained.
 */
class ArtisanVerificationCertificateTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** A business standing at rung 1: a reviewer confirmed who they are. */
    private function verifiedBusiness(): Business
    {
        $business = $this->makeBusiness(null, ['verification_tier' => 'verified']);

        ProvenanceRegistry::ganFor($business);

        return $business->fresh();
    }

    private function url(Business $business, string $lang = 'fr'): string
    {
        return '/certificat-artisan/' . $business->slug . '?lang=' . $lang;
    }

    public function test_it_renders_for_a_business_that_has_a_certificate(): void
    {
        $this->get($this->url($this->verifiedBusiness()))->assertOk();
    }

    public function test_a_business_with_nothing_verified_has_no_certificate(): void
    {
        $business = $this->makeBusiness();

        $this->assertSame(0, ArtisanVerification::levelFor($business));
        $this->get($this->url($business))->assertNotFound();
    }

    public function test_it_prints_the_gan_and_the_certificate_number(): void
    {
        $business = $this->verifiedBusiness();
        $cert     = ArtisanVerification::forBusiness($business);
        $gan      = ProvenanceRegistry::ganFor($business);

        $this->get($this->url($business))
            ->assertOk()
            ->assertSee($gan)
            ->assertSee($cert->certificate_no);
    }

    public function test_both_languages_render(): void
    {
        $business = $this->verifiedBusiness();

        $this->get($this->url($business, 'fr'))->assertOk();
        $this->get($this->url($business, 'en'))->assertOk();
    }

    public function test_it_names_the_issuing_platform(): void
    {
        $body = $this->get($this->url($this->verifiedBusiness()))->assertOk()->getContent();

        $this->assertTrue(
            str_contains($body, 'Artisan Hub 237') || str_contains($body, 'ArtisanHub237'),
            'The certificate must say who issued it.'
        );
    }

    /**
     * Awards conferred by bodies outside the platform, systems that were never
     * built, and physical print properties that cannot exist on a screen.
     */
    public function test_it_claims_nothing_the_platform_cannot_back(): void
    {
        $business = $this->verifiedBusiness();

        foreach (['fr', 'en'] as $lang) {
            $body = $this->get($this->url($business, $lang))->assertOk()->getContent();

            foreach ([
                'SIARC', 'UNESCO', 'MINAC',
                'Fraud Risk', 'Portrait Match', 'Biometric', 'Biométrique',
                'UV ', 'Holographic', 'Hologramme', 'Holographique',
                'Blockchain', 'C2PA', 'NFC',
            ] as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $body,
                    "[{$lang}] The certificate must not print \"{$forbidden}\"."
                );
            }
        }
    }

    /**
     * The identity document is the sharpest case: the platform holds the number
     * encrypted, and the only form that may ever reach a page is the last four.
     */
    public function test_the_identity_document_number_is_never_printed(): void
    {
        $business = $this->verifiedBusiness();
        ArtisanVerification::recordIdentityDocument($business, 'national_id', 'CM8805143567891');

        $body = $this->get($this->url($business->fresh(), 'en'))->assertOk()->getContent();

        $this->assertStringNotContainsString('CM8805143567891', $body);
        $this->assertStringContainsString('•••• 7891', $body);
    }

    /**
     * A check the platform never attempts is absent from the map, and an absent
     * key must render as nothing — neither a tick nor a cross. A cross would
     * read as "we looked and they failed", which is a claim with no evidence.
     */
    public function test_a_check_the_platform_never_attempts_renders_neither_way(): void
    {
        $business = $this->verifiedBusiness();

        $this->assertArrayNotHasKey('criminal_record_checked', ArtisanVerification::checksFor($business));

        $body = $this->get($this->url($business, 'en'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Criminal record', $body);
        $this->assertStringNotContainsString('Tax compliance', $body);
        $this->assertStringNotContainsString('Site visit', $body);
    }

    /**
     * The seeded shop sits at rung 1. Rungs 2 to 7 must appear as the pathway
     * ahead, never as standing already held.
     */
    public function test_an_unattained_rung_is_not_shown_as_attained(): void
    {
        $business = $this->verifiedBusiness();
        $cert     = ArtisanVerification::forBusiness($business);

        $this->assertSame(1, (int) $cert->level);

        $body = $this->get($this->url($business, 'en'))->assertOk()->getContent();

        // The attained rung is announced; the unattained ones are not.
        $this->assertStringContainsString('data-rung="1" data-attained="1"', $body);

        foreach ([2, 3, 4, 5, 6, 7] as $rung) {
            $this->assertStringContainsString("data-rung=\"{$rung}\" data-attained=\"0\"", $body);
        }

        // The headline standing is the rung actually held, and the artwork's
        // own "CERTIFIED ARTISAN" banner (its rung 4) must not appear over an
        // artisan standing at rung 1.
        $this->assertStringContainsString('data-standing="1"', $body);
        $this->assertStringNotContainsString('CERTIFIED ARTISAN', strtoupper($body));
    }

    /** The audit trail is the platform's own record of what happened, so it shows. */
    public function test_it_shows_the_certificate_hash_and_signature_key(): void
    {
        $business = $this->verifiedBusiness();
        $cert     = ArtisanVerification::forBusiness($business);

        $body = $this->get($this->url($business, 'en'))->assertOk()->getContent();

        $this->assertStringContainsString(strtoupper(substr($cert->content_hash, 0, 20)), strtoupper($body));
        $this->assertStringContainsString((string) $cert->verification_pin, $body);
    }
}
