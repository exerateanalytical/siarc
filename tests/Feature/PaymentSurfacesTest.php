<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Support\ManualPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The three human surfaces over manual settlement: how to pay, my payments,
 * and the reviewer's queue.
 *
 * ManualPaymentTest guards the state machine. This file guards the screens, and
 * the failure modes of a screen are different in kind. The engine can be
 * perfectly correct and the page still ruinous, in exactly two ways.
 *
 * The first is a number. If nobody has filled in the MTN account and the page
 * renders the field anyway — blank, or with a plausible-looking placeholder — a
 * member reads it off the screen and sends real money to a stranger or to
 * nobody. There is no safe fallback value, so the assertion here is an absence:
 * with no configured method, no digits reach the HTML at all.
 *
 * The second is a claim dressed as a fact. Everything the payer types is
 * unverified, and a dashboard that shows "paid" the moment they say so has
 * granted a membership on somebody's word. So reporting must leave the record
 * `reported`, and only a reviewer who is not the payer may move it on.
 */
class PaymentSurfacesTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private const INSTRUCTIONS = '/paiement';
    private const DASHBOARD    = '/tableau-de-bord/paiements';
    private const QUEUE        = '/tableau-de-bord/admin/paiements';

    /** A single, fully configured method. Set per test rather than read from the
     *  deployment's env, so the suite never passes merely because a real number
     *  happens to be present on the machine running it. */
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

    /** Every method present but none filled in — the deployed-but-unconfigured
     *  state, which is the default on a fresh install and therefore the one most
     *  likely to be seen by a real member. */
    private function withNoConfiguredMethod(): void
    {
        config()->set('payments.methods', [
            'mtn_momo' => [
                'code' => 'mtn_momo', 'kind' => 'mobile_money',
                'label_fr' => 'MTN Mobile Money', 'label_en' => 'MTN Mobile Money',
                'number' => '', 'holder' => '',
                'instructions_fr' => '', 'instructions_en' => '', 'active' => true,
            ],
        ]);
    }

    private function asUser(User $user, bool $isAdmin = false): static
    {
        return $this->withSession(['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name ?? 'Test User',
            'email'    => $user->email,
            'role'     => $isAdmin ? 'admin' : 'business_owner',
            'is_admin' => $isAdmin,
        ]]);
    }

    private function openFor(?object $business = null, ?User $user = null, array $overrides = []): object
    {
        return ManualPayment::open(array_merge([
            'purpose'     => 'membership',
            'amount'      => '25000.00',
            'method_code' => 'mtn_momo',
            'business_id' => $business?->id,
            'user_id'     => $user?->id,
        ], $overrides));
    }

    /* ─────────────────── 1. How to pay ─────────────────── */

    public function test_with_no_configured_method_the_page_says_so_and_shows_no_number(): void
    {
        $this->withNoConfiguredMethod();

        $response = $this->get(self::INSTRUCTIONS);
        $html     = $response->getContent();

        $response->assertOk();
        $response->assertSee('data-payment-methods="none"', false);

        // The load-bearing assertion. Not "the box is empty" — the box is absent,
        // because an empty account line invites a member to fill the gap with a
        // guess, which is worse than showing nothing.
        $this->assertStringNotContainsString('data-account-number', $html);
    }

    public function test_with_a_configured_method_the_number_and_the_reference_both_appear(): void
    {
        $this->withConfiguredMethod();
        $payment = $this->openFor();

        $response = $this->get(self::INSTRUCTIONS . '/' . $payment->reference);

        $response->assertOk();
        $response->assertSee('670000000');
        $response->assertSee('Artisan Hub 237');
        // Without the reference in the operator's reason field the money cannot
        // be matched to this row, so it must be on the page the payer is reading.
        $response->assertSee($payment->reference);
    }

    public function test_the_page_states_that_reporting_is_a_claim_not_a_confirmation(): void
    {
        $this->withConfiguredMethod();
        $payment = $this->openFor();

        $response = $this->get(self::INSTRUCTIONS . '/' . $payment->reference);

        $response->assertOk();
        $response->assertSee('data-claim-notice', false);
        // The duration is read from config, never invented in the copy.
        $response->assertSee((string) config('payments.confirmation_window_days'));
    }

    public function test_an_unknown_reference_is_a_404_not_a_blank_instruction_sheet(): void
    {
        $this->withConfiguredMethod();

        $this->get(self::INSTRUCTIONS . '/AH237-PAY-NOPE')->assertNotFound();
    }

    public function test_reporting_a_payment_leaves_it_reported_never_confirmed(): void
    {
        $this->withConfiguredMethod();
        $payment = $this->openFor();

        $this->post(self::INSTRUCTIONS . '/' . $payment->reference . '/signaler', [
            'payer_name'      => 'Ada Ngu',
            'payer_number'    => '670000001',
            'payer_reference' => 'MP2607.1234.A56789',
        ])->assertRedirect();

        $row = DB::table('payments')->where('id', $payment->id)->first();

        $this->assertSame('reported', $row->status);
        $this->assertNull($row->reviewed_by);
        $this->assertNull($row->reviewed_at);
    }

    public function test_reporting_without_a_payer_name_fails_validation(): void
    {
        $this->withConfiguredMethod();
        $payment = $this->openFor();

        $this->post(self::INSTRUCTIONS . '/' . $payment->reference . '/signaler', [])
            ->assertSessionHasErrors('payer_name');

        $this->assertSame('awaiting_payment', DB::table('payments')->where('id', $payment->id)->value('status'));
    }

    /* ─────────────────── 2. My payments ─────────────────── */

    public function test_a_guest_cannot_reach_the_dashboard(): void
    {
        $this->get(self::DASHBOARD)->assertRedirect('/login');
    }

    public function test_an_artisan_sees_only_their_own_payments(): void
    {
        $this->withConfiguredMethod();

        $mine   = $this->makeUser();
        $theirs = $this->makeUser();
        $mineBusiness   = $this->makeBusiness($mine);
        $theirsBusiness = $this->makeBusiness($theirs);

        $ours    = $this->openFor($mineBusiness, $mine);
        $foreign = $this->openFor($theirsBusiness, $theirs);

        $response = $this->asUser($mine)->get(self::DASHBOARD);

        $response->assertOk();
        $response->assertSee($ours->reference);
        // A reference is the key to somebody else's money. It must never leak
        // onto a page belonging to a different business.
        $response->assertDontSee($foreign->reference);
    }

    public function test_a_rejected_payment_shows_its_reason(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $reviewer = $this->makeUser();

        $payment = $this->openFor($business, $user);
        ManualPayment::report($payment->id, ['payer_name' => 'Ada Ngu']);
        ManualPayment::reject($payment->id, $reviewer->id, 'no transfer found for that reference');

        $this->asUser($user)->get(self::DASHBOARD)
            ->assertOk()
            ->assertSee('no transfer found for that reference');
    }

    public function test_a_reported_payment_is_not_shown_as_confirmed(): void
    {
        $this->withConfiguredMethod();

        $user     = $this->makeUser();
        $business = $this->makeBusiness($user);
        $payment  = $this->openFor($business, $user);
        ManualPayment::report($payment->id, ['payer_name' => 'Ada Ngu']);

        $html = $this->asUser($user)->get(self::DASHBOARD)->getContent();

        $this->assertStringContainsString('data-payment-status="reported"', $html);
        $this->assertStringNotContainsString('data-payment-status="confirmed"', $html);
    }

    /* ─────────────────── 3. The reviewer's queue ─────────────────── */

    public function test_a_guest_cannot_reach_the_admin_queue(): void
    {
        $this->get(self::QUEUE)->assertRedirect();
    }

    public function test_a_non_admin_cannot_reach_the_admin_queue(): void
    {
        $this->asUser($this->makeUser())->get(self::QUEUE)->assertRedirect('/tableau-de-bord');
    }

    public function test_a_non_admin_cannot_post_a_confirmation(): void
    {
        $this->withConfiguredMethod();

        $user    = $this->makeUser();
        $payment = $this->openFor(null, $user);
        ManualPayment::report($payment->id, ['payer_name' => 'Ada Ngu']);

        $this->asUser($user)->post(self::QUEUE . '/' . $payment->id . '/confirmer', ['note' => 'go on then'])
            ->assertRedirect('/tableau-de-bord');

        $this->assertSame('reported', DB::table('payments')->where('id', $payment->id)->value('status'));
    }

    public function test_an_admin_cannot_confirm_a_payment_they_are_themselves_the_payer_of(): void
    {
        $this->withConfiguredMethod();

        // The dangerous case the role check alone does not cover: an
        // administrator who also owns a shop and owes the platform a fee. The
        // separation being protected is payer-from-reviewer, not user-from-admin.
        $admin   = $this->makeUser();
        $payment = $this->openFor($this->makeBusiness($admin), $admin);
        ManualPayment::report($payment->id, ['payer_name' => 'The Admin']);

        $this->asUser($admin, true)->post(self::QUEUE . '/' . $payment->id . '/confirmer', [])
            ->assertForbidden();

        $this->assertSame('reported', DB::table('payments')->where('id', $payment->id)->value('status'));
    }

    public function test_confirming_as_an_admin_moves_the_record_and_records_the_reviewer(): void
    {
        $this->withConfiguredMethod();

        $payer   = $this->makeUser();
        $admin   = $this->makeUser();
        $payment = $this->openFor($this->makeBusiness($payer), $payer);
        ManualPayment::report($payment->id, ['payer_name' => 'Ada Ngu']);

        $this->asUser($admin, true)
            ->post(self::QUEUE . '/' . $payment->id . '/confirmer', ['note' => 'seen on the MTN statement'])
            ->assertRedirect();

        $row = DB::table('payments')->where('id', $payment->id)->first();

        $this->assertSame('confirmed', $row->status);
        $this->assertSame($admin->id, $row->reviewed_by);
        $this->assertNotNull($row->reviewed_at);
    }

    public function test_rejecting_without_a_reason_fails_validation(): void
    {
        $this->withConfiguredMethod();

        $payer   = $this->makeUser();
        $admin   = $this->makeUser();
        $payment = $this->openFor($this->makeBusiness($payer), $payer);
        ManualPayment::report($payment->id, ['payer_name' => 'Ada Ngu']);

        $this->asUser($admin, true)
            ->post(self::QUEUE . '/' . $payment->id . '/rejeter', ['reason' => '   '])
            ->assertSessionHasErrors('reason');

        // A rejection with no reason is a dead end for the payer, so nothing moves.
        $this->assertSame('reported', DB::table('payments')->where('id', $payment->id)->value('status'));
    }

    public function test_the_queue_shows_the_claim_and_the_event_trail(): void
    {
        $this->withConfiguredMethod();

        $payer   = $this->makeUser();
        $admin   = $this->makeUser();
        $payment = $this->openFor($this->makeBusiness($payer), $payer);
        ManualPayment::report($payment->id, [
            'payer_name'      => 'Ada Ngu',
            'payer_number'    => '670000001',
            'payer_reference' => 'MP2607.1234.A56789',
        ]);

        $response = $this->asUser($admin, true)->get(self::QUEUE);

        $response->assertOk();
        $response->assertSee('Ada Ngu');
        $response->assertSee('MP2607.1234.A56789');
        $response->assertSee('data-event="reported"', false);
        // The whole integrity of manual settlement rests on the reviewer going
        // and looking at the operator's records, so the page must say that.
        $response->assertSee('data-verify-reminder', false);
    }

    /* ─────────────────── Both languages ─────────────────── */

    public function test_both_languages_render_on_every_surface(): void
    {
        $this->withConfiguredMethod();

        $payer   = $this->makeUser();
        $admin   = $this->makeUser();
        $payment = $this->openFor($this->makeBusiness($payer), $payer);

        foreach (['fr', 'en'] as $lang) {
            $this->get(self::INSTRUCTIONS . '?lang=' . $lang)->assertOk();
            $this->get(self::INSTRUCTIONS . '/' . $payment->reference . '?lang=' . $lang)->assertOk();
            $this->asUser($payer)->get(self::DASHBOARD . '?lang=' . $lang)->assertOk();
            $this->asUser($admin, true)->get(self::QUEUE . '?lang=' . $lang)->assertOk();
        }
    }
}
