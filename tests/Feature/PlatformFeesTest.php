<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Support\ManualPayment;
use App\Support\PlatformFees;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * What the platform charges, when it charges it, and what the money buys.
 *
 * Two rules are being defended here and they pull in opposite directions, which
 * is why they are written down as tests rather than left to judgement.
 *
 * The first is that the fee is real: nothing appears in the public directory
 * until somebody has confirmed that money arrived. A business with no
 * subscription, or an expired one, is not published, and activate() refuses to
 * run against a payment a reviewer has not confirmed.
 *
 * The second is that a fee may not stand between a person and their own data.
 * The 510 SIARC artisans never signed up — their names and trades were imported
 * from competition records — so claiming a profile is free and gives them
 * control of it immediately. What the subscription buys is publication, not
 * ownership. A claim that charged before letting someone correct their own
 * entry would be holding that entry hostage, and the assertion below is what
 * stops a later change from quietly introducing that.
 */
class PlatformFeesTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** A configured method, set per test so the suite never depends on the
     *  deployment's env holding a real mobile-money number. */
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

    /** An imported SIARC profile: placeholder owner with no email, draft shop. */
    private function importedProfile(string $code, string $name, ?string $phone = null): Business
    {
        $placeholder = User::create([
            'name'     => $name,
            'email'    => null,
            'phone'    => null,
            'password' => Hash::make(Str::random(40)),
            'status'   => 'active',
        ]);

        return Business::create([
            'uuid'        => (string) Str::uuid(),
            'slug'        => Str::slug($name) . '-' . Str::slug($code),
            'siarc_code'  => $code,
            'user_id'     => $placeholder->id,
            'name_fr'     => $name,
            'phone'       => $phone,
            'status'      => 'draft',
            'vendor_type' => 'artisan',
        ]);
    }

    private function asMember(User $u): static
    {
        return $this->withSession(['siac_user' => [
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]]);
    }

    private function artisanFor(Business $b): User
    {
        return User::create([
            'name' => $b->name_fr, 'email' => 'real@example.test',
            'password' => Hash::make('secret12'), 'status' => 'active',
        ]);
    }

    /** A real reviewer: `reviewed_by` is a foreign key, and a confirmation must
     *  always name somebody who exists. */
    private function reviewer(): string
    {
        return User::create([
            'name' => 'Reviewer', 'email' => 'reviewer@example.test',
            'password' => Hash::make('secret12'), 'status' => 'active',
        ])->id;
    }

    private function planRow(string $slug): object
    {
        return DB::table('subscription_plans')->where('slug', $slug)->first();
    }

    /* ─────────────────────────── The price ─────────────────────────────── */

    public function test_the_price_comes_from_the_database_and_is_never_hardcoded(): void
    {
        $this->withConfiguredMethod();

        // Move the price to a value no fixture or literal in the codebase could
        // have guessed. If the intent still matches it, the number was read.
        DB::table('subscription_plans')->where('slug', 'basic')->update(['price_yearly' => 47531]);

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $payment = PlatformFees::openRegistration($b, 'basic');

        $this->assertSame(0, bccomp((string) $payment->amount, '47531', 2));
        $this->assertSame($this->planRow('basic')->currency, $payment->currency);
    }

    public function test_amounts_round_trip_exactly(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $plan = $this->planRow('cooperative');

        $payment = PlatformFees::openRegistration($b, 'cooperative');

        // bccomp, not ==: a float round trip through the decimal column is
        // exactly the bug this assertion exists to catch.
        $this->assertSame(0, bccomp((string) $payment->amount, (string) $plan->price_yearly, 2));

        ManualPayment::confirm($payment->id, $this->reviewer(), null);
        PlatformFees::activate($b, $payment->id);

        $sub = PlatformFees::subscriptionFor($b);
        $this->assertSame(0, bccomp((string) $sub->amount, (string) $plan->price_yearly, 2));
    }

    public function test_an_unknown_plan_is_refused_rather_than_defaulted(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');

        $this->expectException(DomainException::class);
        PlatformFees::openRegistration($b, 'no-such-plan');
    }

    /* ────────────────────── Opening a registration ─────────────────────── */

    public function test_opening_registration_twice_for_the_same_plan_reuses_the_intent(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');

        $first  = PlatformFees::openRegistration($b, 'standard');
        $second = PlatformFees::openRegistration($b, 'standard');

        $this->assertSame($first->id, $second->id);
        $this->assertSame($first->reference, $second->reference);
        $this->assertSame(1, DB::table('payments')->where('business_id', $b->id)->count());
    }

    /* ───────────────────────── Activation ──────────────────────────────── */

    public function test_activate_refuses_an_unconfirmed_payment(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $payment = PlatformFees::openRegistration($b, 'basic');

        // A payer's own word reaches `reported` and no further. If that were
        // enough to activate, anyone could publish for free by claiming to pay.
        ManualPayment::report($payment->id, ['payer_name' => 'Nouridine', 'payer_number' => '670000000']);

        try {
            PlatformFees::activate($b, $payment->id);
            $this->fail('activate() must refuse a payment no reviewer has confirmed.');
        } catch (DomainException $e) {
            // expected
        }

        $this->assertNull(PlatformFees::subscriptionFor($b));
        $this->assertFalse(PlatformFees::isActive($b));
    }

    public function test_confirming_a_registration_activates_the_subscription_for_one_year(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $payment = PlatformFees::openRegistration($b, 'standard');

        ManualPayment::confirm($payment->id, $this->reviewer(), 'seen on the MTN statement');
        PlatformFees::activate($b, $payment->id);

        $sub = PlatformFees::subscriptionFor($b);
        $this->assertNotNull($sub);
        $this->assertSame('active', $sub->status);
        $this->assertNotNull($sub->started_at);
        $this->assertSame(
            \Illuminate\Support\Carbon::parse($sub->started_at)->addYear()->toDateString(),
            \Illuminate\Support\Carbon::parse($sub->next_payment_at)->toDateString(),
        );
        $this->assertTrue(PlatformFees::isActive($b));
        $this->assertFalse(PlatformFees::requiresPaymentToPublish($b));
        $this->assertSame('standard', PlatformFees::planFor($b)->slug);
    }

    public function test_an_expired_subscription_is_not_active(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $plan = $this->planRow('basic');

        DB::table('business_subscriptions')->insert([
            'business_id'          => $b->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'active',
            'amount'               => $plan->price_yearly,
            'started_at'           => now()->subYears(2),
            'next_payment_at'      => now()->subYear(),   // the year has run out
            'created_at'           => now()->subYears(2),
            'updated_at'           => now()->subYears(2),
        ]);

        $this->assertFalse(PlatformFees::isActive($b), 'A lapsed date is not a paid membership.');
        $this->assertTrue(PlatformFees::requiresPaymentToPublish($b));
    }

    public function test_a_subscription_marked_expired_is_not_active(): void
    {
        $b = $this->importedProfile('AD-2', 'DAOUDA GARGA');
        $plan = $this->planRow('basic');

        DB::table('business_subscriptions')->insert([
            'business_id'          => $b->id,
            'subscription_plan_id' => $plan->id,
            'status'               => 'expired',
            'amount'               => $plan->price_yearly,
            'started_at'           => now()->subYears(2),
            'next_payment_at'      => now()->addYear(),   // a future date cannot rescue it
            'created_at'           => now()->subYears(2),
            'updated_at'           => now()->subYears(2),
        ]);

        $this->assertFalse(PlatformFees::isActive($b));
    }

    /* ─────────────────────────── The claim ─────────────────────────────── */

    public function test_claiming_is_free_and_hands_over_control_immediately(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = $this->artisanFor($b);

        $this->asMember($artisan)
            ->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr'])
            ->assertRedirect();

        $b->refresh();

        // Control first: the person owns the record and can correct or remove it
        // without paying anybody.
        $this->assertSame($artisan->id, $b->user_id);
        $this->assertNotNull($b->claimed_at);

        // And nothing was settled in the process.
        $this->assertSame(
            0,
            DB::table('payments')->where('business_id', $b->id)->where('status', 'confirmed')->count(),
            'A claim must never require, nor pretend to have received, a payment.',
        );
    }

    public function test_a_claimed_but_unpaid_profile_stays_a_draft_and_off_the_directory(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = $this->artisanFor($b);

        $this->asMember($artisan)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr']);

        $b->refresh();
        $this->assertSame('draft', $b->status);
        $this->assertTrue(PlatformFees::requiresPaymentToPublish($b));

        $this->get('/galerie/entreprises/' . $b->slug)->assertNotFound();
        $this->get('/galerie/entreprises')->assertOk()->assertDontSee('NOURIDINE HAMADOU');
    }

    public function test_the_claim_opens_a_registration_intent_the_artisan_can_pay_when_they_choose(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = $this->artisanFor($b);

        $this->asMember($artisan)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr']);

        $intent = DB::table('payments')->where('business_id', $b->id)->first();
        $this->assertNotNull($intent, 'Publication is what the fee buys, so an intent is opened at the claim.');
        $this->assertSame('registration', $intent->purpose);
        $this->assertSame('awaiting_payment', $intent->status, 'Opening an intent settles nothing.');
    }

    public function test_the_claim_page_says_claiming_is_free_and_names_the_price(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = $this->artisanFor($b);

        $price = (int) $this->planRow('basic')->price_yearly;

        $this->asMember($artisan)
            ->get('/tableau-de-bord/revendiquer?lang=fr')
            ->assertOk()
            ->assertSee('gratuit', false)
            ->assertSee(number_format($price, 0, ',', ' '), false);
    }

    /** These artisans never asked to be here; nothing may contact them. */
    public function test_no_mail_is_sent_when_a_siarc_profile_is_claimed(): void
    {
        Mail::fake();
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = $this->artisanFor($b);

        $this->asMember($artisan)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr']);

        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('jobs')->count(), 'No notification job may be queued either.');
    }

    /* ──────────────────────── The publication gate ─────────────────────── */

    public function test_publishing_is_refused_without_a_paid_subscription(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $service = app(\App\Modules\Businesses\Services\BusinessService::class);

        try {
            $service->publish($b);
            $this->fail('A business with no confirmed payment must not reach the directory.');
        } catch (DomainException $e) {
            // expected
        }

        $this->assertSame('draft', $b->fresh()->status);
    }

    public function test_publishing_succeeds_once_the_subscription_is_active(): void
    {
        $this->withConfiguredMethod();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $payment = PlatformFees::openRegistration($b, 'basic');
        ManualPayment::confirm($payment->id, $this->reviewer(), null);
        PlatformFees::activate($b, $payment->id);

        app(\App\Modules\Businesses\Services\BusinessService::class)->publish($b);

        $this->assertSame('published', $b->fresh()->status);
    }

    /* ─────────────────────────── plans() ───────────────────────────────── */

    public function test_plans_are_listed_with_their_database_price_and_localised_name(): void
    {
        $plans = collect(PlatformFees::plans('en'));

        $basic = $plans->firstWhere('slug', 'basic');
        $row   = $this->planRow('basic');

        $this->assertNotNull($basic);
        $this->assertSame($row->name_en, $basic['name']);
        $this->assertSame(0, bccomp((string) $basic['price_yearly'], (string) $row->price_yearly, 2));
        $this->assertSame($row->currency, $basic['currency']);
    }
}
