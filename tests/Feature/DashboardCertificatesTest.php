<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Support\ArtisanVerification;
use App\Support\ProductCertificate;
use App\Support\ProductFlags;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The artisan's own view of everything the platform asserts about them.
 *
 * The certificates, the registers and the authority were all built outward —
 * for the buyer scanning a QR code and for the institution auditing a register.
 * This page is the only one built inward, and the failure modes it has to be
 * held to are different from a public certificate's. A public document that
 * omits a fact is merely incomplete; this page telling an artisan they failed a
 * check nobody ever ran is a slander delivered to the person it is about.
 *
 * Hence the shape of the assertions below: they are mostly about what must NOT
 * appear — no fabricated certificate number, no unattained rung dressed as
 * attained, no cross against an unattempted check — plus the one side-effect
 * guarantee, that opening your own dashboard does not inflate the public
 * verification counter on your own certificate.
 */
class DashboardCertificatesTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private const URL = '/tableau-de-bord/certificats';

    private function asArtisan(User $user): static
    {
        return $this->withSession(['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name ?? 'Test Artisan',
            'email'    => $user->email,
            'role'     => 'business_owner',
            'is_admin' => false,
        ]]);
    }

    public function test_a_guest_is_redirected_rather_than_shown_the_page(): void
    {
        $this->get(self::URL)->assertRedirect('/login');
    }

    public function test_an_artisan_with_a_shop_sees_their_standing_and_their_products(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product  = $this->makeProduct($business, ['name_fr' => 'Masque Bamoun']);

        $response = $this->asArtisan($user)->get(self::URL);

        $response->assertOk();
        $response->assertSee('Masque Bamoun');
        // The ladder is the point of the page, so it must be on it in full.
        $response->assertSee('data-rung="1"', false);
        $response->assertSee('data-rung="7"', false);
        $response->assertSee($business->name_fr);
    }

    public function test_an_artisan_with_no_shop_sees_a_prompt_rather_than_an_error(): void
    {
        $user = $this->makeUser();

        $response = $this->asArtisan($user)->get(self::URL);

        $response->assertOk();
        $response->assertSee(route('business.create'), false);
        $response->assertDontSee('data-rung="1"', false);
    }

    public function test_an_unissued_verification_certificate_is_explained_not_invented(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);

        $this->assertNull(ArtisanVerification::forBusiness($business));

        $response = $this->asArtisan($user)->get(self::URL);
        $html     = $response->getContent();

        $response->assertOk();
        // No certificate number of any family may appear when none was issued.
        $this->assertDoesNotMatchRegularExpression('/AH237-AVC-/', $html);
        // And the page must say what would move the situation on.
        $response->assertSee(route('verification.show'), false);
    }

    public function test_opening_the_page_does_not_inflate_the_verification_counter(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product  = $this->makeProduct($business);

        // Issue a COA the normal way, then read the counter the public
        // verification endpoint increments.
        $certificate = ProductCertificate::forProduct($product);
        $this->assertNotNull($certificate);

        $before = DB::table('product_certificates')->where('id', $certificate->id)->value('verification_count');

        $this->asArtisan($user)->get(self::URL)->assertOk();

        $after = DB::table('product_certificates')->where('id', $certificate->id)->value('verification_count');

        $this->assertSame((int) $before, (int) $after, 'Viewing your own dashboard counted as a public verification.');
    }

    public function test_an_unattained_rung_is_not_shown_as_attained(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);   // nothing reviewed: level 0

        $this->assertSame(0, ArtisanVerification::levelFor($business));

        $html = $this->asArtisan($user)->get(self::URL)->getContent();

        preg_match_all('/data-rung="(\d)" data-attained="(\d)"/', $html, $m, PREG_SET_ORDER);

        $this->assertNotEmpty($m, 'The ladder did not render at all.');
        foreach ($m as [, $rung, $attained]) {
            $this->assertSame('0', $attained, "Rung {$rung} claims to be attained at level 0.");
        }
    }

    public function test_a_check_the_platform_never_runs_is_not_rendered_as_a_failure(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);

        $html = $this->asArtisan($user)->get(self::URL)->getContent();

        preg_match_all('/data-check="([a-z_]+)"/', $html, $m);
        $rendered = array_unique($m[1]);

        $this->assertNotEmpty($rendered);
        // Every rendered row must correspond to a check the platform performs.
        foreach ($rendered as $key) {
            $this->assertArrayHasKey($key, ArtisanVerification::checksFor($business),
                "Rendered a verdict for '{$key}', which the platform never assesses.");
        }
        // And the things we are explicitly not entitled to opine on stay off it.
        foreach (['criminal_record', 'tax_compliance', 'site_visit', 'quality_inspection'] as $absent) {
            $this->assertStringNotContainsString('data-check="' . $absent . '"', $html);
        }
    }

    public function test_a_flag_on_a_product_is_stated_factually(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $product  = $this->makeProduct($business);

        ProductFlags::raise($product, 'under_investigation', ['reference' => 'REF-42']);

        $html = $this->asArtisan($user)->get(self::URL)->getContent();

        $this->assertStringContainsString('data-flag="under_investigation"', $html);
        $this->assertStringContainsString('REF-42', $html);
    }

    public function test_both_languages_render(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $this->makeProduct($business);

        $this->asArtisan($user)->get(self::URL . '?lang=fr')->assertOk()->assertSee('Mes certificats');
        $this->asArtisan($user)->get(self::URL . '?lang=en')->assertOk()->assertSee('My certificates');
    }
}
