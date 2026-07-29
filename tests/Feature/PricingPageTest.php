<?php

namespace Tests\Feature;

use App\Support\ManualPayment;
use App\Support\PlatformFees;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The public /tarifs page, the footer link to it, and the vendor's own plan
 * picker on /tableau-de-bord/paiements.
 *
 * Every price asserted here is read from `subscription_plans` at test time,
 * never hardcoded to the numbers the owner confirmed — a later price change
 * must not make this suite lie about being green. The one hard rule that is
 * hardcoded is the *count*: exactly four active plans, and the deactivated
 * fifth (`personnalise`) must appear nowhere a member can see it.
 */
class PricingPageTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function withConfiguredMethod(): void
    {
        config()->set('payments.methods', [
            'mtn_momo' => [
                'code' => 'mtn_momo', 'kind' => 'mobile_money',
                'label_fr' => 'MTN Mobile Money', 'label_en' => 'MTN Mobile Money',
                'number' => '670000000', 'holder' => 'Artisan Hub 237',
                'instructions_fr' => '', 'instructions_en' => '', 'active' => true,
            ],
        ]);
    }

    private function asUser(\App\Modules\Auth\Models\User $user): static
    {
        return $this->withSession(['siac_user' => [
            'id' => $user->id, 'name' => $user->name, 'email' => $user->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]]);
    }

    /* ─────────────────────── The public pricing page ───────────────────── */

    public function test_the_pricing_page_lists_every_active_plan_and_its_real_price(): void
    {
        $active = DB::table('subscription_plans')->where('is_active', true)->orderBy('sort_order')->get();
        $this->assertCount(4, $active, 'Fixture assumption: exactly four plans are active.');

        $response = $this->get('/tarifs?lang=fr')->assertOk();

        foreach ($active as $plan) {
            $response->assertSee($plan->name_fr);
            $response->assertSee(number_format((int) $plan->price_yearly, 0, ',', ' '), false);
        }
    }

    public function test_the_deactivated_fifth_plan_never_appears_on_the_pricing_page(): void
    {
        $custom = DB::table('subscription_plans')->where('slug', 'personnalise')->first();
        $this->assertNotNull($custom);
        $this->assertSame(0, (int) $custom->is_active, 'Fixture assumption: personnalise is deactivated.');

        $this->get('/tarifs?lang=fr')->assertOk()->assertDontSee($custom->name_fr);
    }

    public function test_the_pricing_page_renders_in_both_languages(): void
    {
        $this->get('/tarifs?lang=fr')->assertOk();
        $this->get('/tarifs?lang=en')->assertOk();
    }

    public function test_the_english_pricing_alias_redirects_to_the_canonical_route(): void
    {
        $this->get('/pricing')->assertRedirect(route('pricing'));
    }

    /* ─────────────────────────── The footer link ───────────────────────── */

    public function test_the_footer_carries_a_working_pricing_link(): void
    {
        $html = $this->get('/contact?lang=fr')->assertOk()->getContent();

        $this->assertStringContainsString(route('pricing', ['lang' => 'fr']), $html);
        $this->get(route('pricing', ['lang' => 'fr']))->assertOk();
    }

    /* ───────────────────── The vendor's self-service picker ────────────── */

    public function test_a_vendor_with_no_subscription_sees_the_plan_picker(): void
    {
        $user     = $this->makeUser();
        $business = $this->makeBusiness($user, ['status' => 'draft']);

        $html = $this->asUser($user)->get('/tableau-de-bord/paiements')->assertOk()->getContent();

        $this->assertStringContainsString('data-plan-panel="picker"', $html);
        foreach (PlatformFees::plans('fr') as $plan) {
            $this->assertStringContainsString('data-plan-option="' . $plan['slug'] . '"', $html);
        }
    }

    public function test_selecting_a_plan_opens_an_intent_priced_exactly_off_the_database(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user, ['status' => 'draft']);
        $plan     = DB::table('subscription_plans')->where('slug', 'standard')->first();

        $this->asUser($user)
            ->post('/tableau-de-bord/paiements/formule', ['plan' => 'standard', 'lang' => 'fr'])
            ->assertRedirect();

        $intent = DB::table('payments')->where('business_id', $business->id)->where('purpose', 'registration')->first();

        $this->assertNotNull($intent);
        $this->assertSame(0, bccomp((string) $intent->amount, (string) $plan->price_yearly, 2));
        $this->assertSame('awaiting_payment', $intent->status, 'Opening an intent settles nothing.');
    }

    public function test_selecting_a_plan_reuses_platform_fees_open_registration_not_a_duplicate(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user, ['status' => 'draft']);

        // Open once through PlatformFees directly, exactly as the picker does.
        $direct = PlatformFees::openRegistration($business, 'basic');

        $this->asUser($user)->post('/tableau-de-bord/paiements/formule', ['plan' => 'basic', 'lang' => 'fr']);

        $this->assertSame(1, DB::table('payments')->where('business_id', $business->id)->count(),
            'Selecting the same plan a live intent already exists for must reuse it, not open a second.');
        $this->assertSame($direct->id, DB::table('payments')->where('business_id', $business->id)->value('id'));
    }

    public function test_a_vendor_with_an_active_subscription_does_not_see_a_fresh_registration_picker(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user, ['status' => 'published']);
        $plan     = DB::table('subscription_plans')->where('slug', 'basic')->first();

        DB::table('business_subscriptions')->insert([
            'business_id'          => $business->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'amount'               => $plan->price_yearly,
            'started_at'           => now()->subMonth(),
            'next_payment_at'      => now()->addMonths(11),
            'created_at'           => now()->subMonth(),
            'updated_at'           => now()->subMonth(),
        ]);

        $this->assertTrue(PlatformFees::isActive($business));

        $html = $this->asUser($user)->get('/tableau-de-bord/paiements')->assertOk()->getContent();

        $this->assertStringContainsString('data-plan-panel="active"', $html);
        $this->assertStringNotContainsString('name="plan"', $html, 'An already-subscribed vendor must not be shown a fresh-registration form.');
    }

    public function test_posting_a_plan_choice_while_already_subscribed_does_not_open_a_second_intent(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user, ['status' => 'published']);
        $plan     = DB::table('subscription_plans')->where('slug', 'basic')->first();

        DB::table('business_subscriptions')->insert([
            'business_id'          => $business->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'amount'               => $plan->price_yearly,
            'started_at'           => now()->subMonth(),
            'next_payment_at'      => now()->addMonths(11),
            'created_at'           => now()->subMonth(),
            'updated_at'           => now()->subMonth(),
        ]);

        $this->asUser($user)->post('/tableau-de-bord/paiements/formule', ['plan' => 'standard', 'lang' => 'fr'])
            ->assertRedirect(route('dashboard.payments', ['lang' => 'fr']));

        $this->assertSame(0, DB::table('payments')->where('business_id', $business->id)->count());
    }

    public function test_a_guest_cannot_post_a_plan_choice(): void
    {
        $this->post('/tableau-de-bord/paiements/formule', ['plan' => 'basic', 'lang' => 'fr'])
            ->assertRedirect('/login');
    }
}
