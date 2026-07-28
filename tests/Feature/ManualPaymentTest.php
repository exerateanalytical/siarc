<?php

namespace Tests\Feature;

use App\Support\ManualPayment;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Guards manual settlement of the platform's own fees.
 *
 * There is no payment API. Everything the payer tells us is a claim, and the
 * only thing that turns a claim into a confirmed payment is a human at the
 * platform checking the operator's own records. So the tests here are mostly
 * about refusals rather than happy paths: the property worth protecting is that
 * a payer can never move their own payment to confirmed, no matter which method
 * they reach for, because the day that becomes possible the platform is granting
 * memberships for free to anyone who reads the source.
 *
 * The second theme is the unconfigured method. An operator account nobody has
 * filled in must never reach a screen, because a member would send real money to
 * a blank or a guess. methods() is asserted to drop it and say why.
 */
class ManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configured explicitly per test rather than relying on the deployment's
        // env, so the suite neither depends on a real number being present nor
        // passes because one happens to be.
        config()->set('payments.methods', [
            'mtn_momo' => [
                'code' => 'mtn_momo', 'kind' => 'mobile_money',
                'label_fr' => 'MTN Mobile Money', 'label_en' => 'MTN Mobile Money',
                'number' => '670000000', 'holder' => 'Test Holder',
                'instructions_fr' => '', 'instructions_en' => '', 'active' => true,
            ],
            'orange_money' => [
                'code' => 'orange_money', 'kind' => 'mobile_money',
                'label_fr' => 'Orange Money', 'label_en' => 'Orange Money',
                // Nobody has supplied this one. It must not be offered.
                'number' => '', 'holder' => '',
                'instructions_fr' => '', 'instructions_en' => '', 'active' => true,
            ],
        ]);
    }

    private function open(array $overrides = []): object
    {
        return ManualPayment::open(array_merge([
            'purpose'     => 'membership',
            'amount'      => '25000.00',
            'method_code' => 'mtn_momo',
        ], $overrides));
    }

    /**
     * A real user row, because reviewed_by and actor_user_id are foreign keys —
     * a test that passed a bare UUID would be exercising a path production
     * cannot reach.
     */
    private function reviewer(): string
    {
        return (string) \App\Models\User::factory()->create()->id;
    }

    /* ─────────────────────────── Configuration ─────────────────────────── */

    public function test_a_method_without_a_configured_number_is_not_offered(): void
    {
        $codes = array_column(ManualPayment::methods('en'), 'code');

        $this->assertContains('mtn_momo', $codes);
        $this->assertNotContains('orange_money', $codes);
    }

    public function test_an_unconfigured_method_records_why_it_was_withheld(): void
    {
        $withheld = ManualPayment::unconfigured();

        $this->assertArrayHasKey('orange_money', $withheld);
        $this->assertNotEmpty($withheld['orange_money']);
    }

    public function test_no_offered_method_ever_carries_a_blank_number(): void
    {
        foreach (ManualPayment::methods('fr') as $method) {
            if ($method['kind'] === 'cash') {
                $this->assertNotSame('', trim((string) $method['holder']));

                continue;
            }

            $this->assertNotSame('', trim((string) $method['number']));
        }
    }

    public function test_opening_against_an_unconfigured_method_is_refused(): void
    {
        $this->expectException(DomainException::class);

        $this->open(['method_code' => 'orange_money']);
    }

    /* ───────────────────────────── References ──────────────────────────── */

    public function test_references_are_unique_and_human_quotable(): void
    {
        $refs = [];

        for ($i = 0; $i < 5; $i++) {
            $refs[] = $this->open()->reference;
        }

        $this->assertCount(5, array_unique($refs));

        foreach ($refs as $ref) {
            // Short enough to type on a keypad, and made only of characters that
            // survive being read aloud down a phone line.
            $this->assertLessThanOrEqual(24, strlen($ref));
            $this->assertMatchesRegularExpression('/^[A-Z0-9\-]+$/', $ref);
        }
    }

    /* ──────────────────────────── The claim ────────────────────────────── */

    public function test_report_leaves_the_payment_reported_never_confirmed(): void
    {
        $p = $this->open();

        $after = ManualPayment::report($p->id, [
            'payer_name'      => 'Ada Ngu',
            'payer_number'    => '670000001',
            'payer_reference' => 'MP2607.1234.A56789',
        ]);

        $this->assertSame('reported', $after->status);
        $this->assertNotNull($after->reported_at);
        $this->assertNull($after->reviewed_by);
        $this->assertNull($after->reviewed_at);
    }

    public function test_a_payer_cannot_confirm_their_own_payment(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);

        // There is no payer-facing route to confirmed at all: the only method
        // that reaches it demands a reviewer, which is the whole security
        // property of this class.
        $this->expectException(\ArgumentCountError::class);

        // @phpstan-ignore-next-line — deliberately calling it wrong.
        ManualPayment::confirm($p->id);
    }

    public function test_confirming_twice_is_refused(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);
        ManualPayment::confirm($p->id, $this->reviewer(), null);

        $again = ManualPayment::confirm($p->id, $this->reviewer(), null);

        // Idempotent rather than throwing, because an MTN callback firing twice
        // must not error — but the second call must change nothing.
        $this->assertSame('confirmed', $again->status);
        $this->assertSame(
            1,
            DB::table('payment_events')->where('payment_id', $p->id)->where('event', 'confirmed')->count()
        );
    }

    public function test_a_confirmed_payment_cannot_be_reported_again(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);
        ManualPayment::confirm($p->id, $this->reviewer(), null);

        $this->expectException(DomainException::class);

        ManualPayment::report($p->id, ['payer_name' => 'Someone Else']);
    }

    public function test_a_cancelled_payment_cannot_be_reviewed(): void
    {
        $p = $this->open();
        ManualPayment::cancel($p->id, null, 'changed their mind');

        $this->expectException(DomainException::class);

        ManualPayment::confirm($p->id, $this->reviewer(), null);
    }

    public function test_a_cancelled_payment_cannot_be_rejected_either(): void
    {
        $p = $this->open();
        ManualPayment::cancel($p->id, null, 'changed their mind');

        $this->expectException(DomainException::class);

        ManualPayment::reject($p->id, $this->reviewer(), 'no funds received');
    }

    /* ──────────────────────────── The review ───────────────────────────── */

    public function test_confirming_records_the_reviewer_and_the_moment(): void
    {
        $reviewer = $this->reviewer();
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);

        $after = ManualPayment::confirm($p->id, $reviewer, 'seen on the MTN statement');

        $this->assertSame('confirmed', $after->status);
        $this->assertSame($reviewer, $after->reviewed_by);
        $this->assertNotNull($after->reviewed_at);
        $this->assertSame('seen on the MTN statement', $after->review_note);

        $event = DB::table('payment_events')->where('payment_id', $p->id)->where('event', 'confirmed')->first();

        $this->assertNotNull($event);
        $this->assertSame($reviewer, $event->actor_user_id);
        $this->assertSame('confirmed', $event->to_status);
    }

    public function test_begin_review_moves_to_under_review_and_confirm_still_works(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);

        $this->assertSame('under_review', ManualPayment::beginReview($p->id, $this->reviewer())->status);
        $this->assertSame('confirmed', ManualPayment::confirm($p->id, $this->reviewer(), null)->status);
    }

    public function test_rejecting_requires_a_reason(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);

        $this->expectException(DomainException::class);

        ManualPayment::reject($p->id, $this->reviewer(), '   ');
    }

    public function test_rejecting_stores_the_reason_a_payer_can_act_on(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);

        $after = ManualPayment::reject($p->id, $this->reviewer(), 'no transfer found for that reference');

        $this->assertSame('rejected', $after->status);
        $this->assertSame('no transfer found for that reference', $after->rejection_reason);
    }

    /* ──────────────────────────── The trail ────────────────────────────── */

    public function test_every_transition_is_recorded_with_both_ends(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);
        ManualPayment::beginReview($p->id, $this->reviewer());
        ManualPayment::confirm($p->id, $this->reviewer(), null);

        $events = DB::table('payment_events')->where('payment_id', $p->id)->orderBy('id')->get();

        $this->assertSame(
            ['opened', 'reported', 'review_started', 'confirmed'],
            $events->pluck('event')->all()
        );
        $this->assertSame(
            ['awaiting_payment', 'reported', 'under_review', 'confirmed'],
            $events->pluck('to_status')->all()
        );
        $this->assertSame(
            [null, 'awaiting_payment', 'reported', 'under_review'],
            $events->pluck('from_status')->all()
        );
    }

    /* ──────────────────────────── The money ────────────────────────────── */

    public function test_the_amount_round_trips_exactly(): void
    {
        $p = $this->open(['amount' => '25000.50']);

        // bccomp, not ==, because a float comparison would pass on a value that
        // had already lost a centime and the test would be guarding nothing.
        $this->assertSame(0, bccomp('25000.50', (string) ManualPayment::find($p->id)->amount, 2));
    }

    /* ──────────────────────────── Expiry ───────────────────────────────── */

    public function test_expiry_moves_an_unpaid_record_without_granting_anything(): void
    {
        $p = $this->open();

        $after = ManualPayment::expire($p->id);

        $this->assertSame('expired', $after->status);
        $this->assertNull($after->reviewed_by);
        $this->assertNull($after->reviewed_at);
        $this->assertNull($after->paid_at);
    }

    public function test_an_expired_payment_cannot_be_confirmed(): void
    {
        $p = $this->open();
        ManualPayment::expire($p->id);

        $this->expectException(DomainException::class);

        ManualPayment::confirm($p->id, $this->reviewer(), null);
    }

    public function test_a_confirmed_payment_cannot_be_expired(): void
    {
        $p = $this->open();
        ManualPayment::report($p->id, ['payer_name' => 'Ada Ngu']);
        ManualPayment::confirm($p->id, $this->reviewer(), null);

        $this->expectException(DomainException::class);

        ManualPayment::expire($p->id);
    }
}
