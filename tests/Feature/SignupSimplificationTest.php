<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Owner directive (2026-07-29): merge and simplify account verification and
 * company listing; never show French+English form fields at once; mobile
 * signup is the fast form only; email verification comes immediately after
 * the minimal identity fields, before any business/KYC step.
 *
 * These tests guard the concrete defects fixed for that directive rather than
 * re-testing everything the pages already covered.
 */
class SignupSimplificationTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function asWebUser(\App\Modules\Auth\Models\User $user): static
    {
        return $this->withSession(['siac_user' => [
            'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]]);
    }

    // ── No French+English field pair shown at once ──────────────────────────

    public function test_business_form_never_shows_a_name_fr_and_name_en_input_pair(): void
    {
        $user = $this->makeUser();

        foreach (['fr', 'en'] as $lang) {
            $html = $this->asWebUser($user)
                ->withCookie('lang', $lang)
                ->get('/tableau-de-bord/entreprise/creer')
                ->assertOk()->getContent();

            // The old defect: two separate text inputs, one per language, for the
            // same fact (business name / tagline / description). One field now.
            $this->assertStringNotContainsString('name="name_fr"', $html);
            $this->assertStringNotContainsString('name="name_en"', $html);
            $this->assertStringNotContainsString('name="tagline_fr"', $html);
            $this->assertStringNotContainsString('name="tagline_en"', $html);
            $this->assertStringNotContainsString('name="description_fr"', $html);
            $this->assertStringNotContainsString('name="description_en"', $html);
            $this->assertStringContainsString('name="business_name"', $html);
        }
    }

    public function test_business_name_submitted_once_is_stored_in_both_locale_columns(): void
    {
        $user = $this->makeUser();
        $industryId = DB::table('industries')->where('level', 4)->value('id');

        $this->asWebUser($user)->post('/tableau-de-bord/entreprise/creer', [
            'industry_id'           => $industryId,
            'business_name'         => 'Atelier Ndop',
            'business_tagline'      => 'Fait main',
            'business_description' => 'Poterie traditionnelle.',
        ]);

        $business = DB::table('businesses')->where('user_id', $user->id)->first();
        $this->assertNotNull($business);
        $this->assertSame('Atelier Ndop', $business->name_fr);
        $this->assertSame('Atelier Ndop', $business->name_en);
        $this->assertSame('Fait main', $business->tagline_fr);
        $this->assertSame('Fait main', $business->tagline_en);
    }

    // ── The merge preserves every previously-required field ─────────────────

    public function test_merged_business_step_still_requires_industry_and_name(): void
    {
        $user = $this->makeUser();

        $this->asWebUser($user)->post('/tableau-de-bord/entreprise/creer', [])
            ->assertSessionHasErrors(['industry_id', 'business_name']);
    }

    public function test_merged_verification_step_still_requires_a_document(): void
    {
        $user = $this->makeUser();
        $business = $this->makeBusiness($user);

        $this->asWebUser($user)
            ->post('/tableau-de-bord/entreprise/verification', ['tier_requested' => 'basic'])
            ->assertSessionHasErrors(['documents']);
    }

    public function test_business_and_verification_pages_read_as_one_two_step_journey(): void
    {
        $user = $this->makeUser();

        $this->asWebUser($user)->get('/tableau-de-bord/entreprise/creer')
            ->assertOk()->assertSee('1 sur 2', false)->assertSeeText('sur 2');

        $business = $this->makeBusiness($user);
        $this->asWebUser($user)->get('/tableau-de-bord/entreprise/verification')
            ->assertOk()->assertSeeText('sur 2');
    }

    // ── Mobile signup is the fast form only ──────────────────────────────────

    public function test_mobile_signup_cta_points_at_the_fast_form(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $pos = strpos($html, 'Vendre ici');
        $this->assertNotFalse($pos, 'Expected the mobile "Vendre ici" CTA to be present.');
        $snippet = substr($html, max(0, $pos - 400), 400);

        $this->assertStringContainsString(route('register.quick', ['lang' => 'fr']), $snippet);
        $this->assertStringNotContainsString(route('onboarding', ['lang' => 'fr']), $snippet);
    }

    public function test_full_wizard_route_still_exists_for_desktop(): void
    {
        // Not deleted — just no longer the phone default (owner: "keep both
        // existing... but ensure the mobile entry point only surfaces the fast
        // form").
        $this->get('/creer-mon-compte')->assertOk();
    }

    // ── Fast signup collects category + account type and verifies email first ──

    public function test_fast_signup_form_offers_a_trade_category_field(): void
    {
        $html = $this->get('/inscription-rapide')->assertOk()->getContent();
        $this->assertStringContainsString('name="industry_id"', $html);
        $this->assertStringContainsString('name="first_name"', $html);
        $this->assertStringContainsString('name="account_type"', $html);
    }
}
