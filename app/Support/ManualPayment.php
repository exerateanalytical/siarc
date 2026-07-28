<?php

namespace App\Support;

use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual settlement of the platform's own fees.
 *
 * WHAT THIS COVERS, AND WHAT IT MUST NEVER COVER. Everything here concerns money
 * the platform charges for its own services — registration, membership, renewal,
 * verification, workshop inspection. It does not touch sales between buyers and
 * artisans. The platform is not a party to those, does not receive the price and
 * holds no funds, and nothing in this class may be extended to take money for a
 * product. The `purpose` enum is closed for exactly that reason.
 *
 * CONFIRMATION IS A HUMAN ACT TODAY. There is no payment API, no webhook, no
 * balance and no auto-confirmation, and their absence is the design rather than
 * an omission. Money moves by mobile money entirely outside our systems. A payer
 * tells us they sent it; that is a claim and nothing more. An administrator then
 * looks at the operator's own records and says whether it arrived. Until that
 * happens the platform holds an assertion, and no method here will let anything
 * read as settled on the strength of it.
 *
 * THE SEAM. When an MTN MoMo Collections API is connected, its callback handler
 * calls confirm() — this exact method, with the operator as the actor. Nothing
 * else changes: same state machine, same refusals, same event trail. That is why
 * confirm() is idempotent and why it takes an actor it does not interpret. A
 * callback that fires twice must not grant twice, and the trail must remain
 * continuous across the switchover so a payment confirmed in the manual era and
 * one confirmed by the API read the same way in an audit.
 *
 * THE SECURITY PROPERTY. Only a reviewer confirms. A payer's most powerful move
 * is report(), which reaches `reported` and stops there. There is no argument,
 * no flag and no shortcut in this class by which a payer can move their own
 * payment to `confirmed`, because that would hand out paid memberships to anyone
 * who reads the source. Every other refusal here is hygiene; this one is the
 * point.
 */
class ManualPayment
{
    /**
     * Statuses from which each move is legal. Anything else throws rather than
     * returning false: a caller who forgets to check a boolean would grant a
     * membership against a cancelled payment, and that is not a failure mode to
     * leave to caller discipline.
     */
    private const ALLOWED_FROM = [
        'report'      => ['awaiting_payment', 'rejected'],
        'beginReview' => ['reported'],
        // awaiting_payment is included because an administrator sometimes sees
        // the money land on the operator statement before the payer gets round
        // to reporting it, and refusing to record a payment we can actually see
        // would push the reviewer into faking a report on the payer's behalf.
        'confirm'     => ['awaiting_payment', 'reported', 'under_review'],
        'reject'      => ['reported', 'under_review'],
        'cancel'      => ['awaiting_payment', 'reported', 'under_review'],
        'expire'      => ['awaiting_payment', 'reported'],
    ];

    /* ───────────────────────────── Methods ─────────────────────────────── */

    /**
     * The methods that are both active and actually configured.
     *
     * Two conditions, kept apart on purpose. `active` is the operator saying
     * "we accept this". Configured is whether anybody filled in the number. A
     * method can be active and unusable, and the difference matters because a
     * deploy with a missing env var would otherwise render a payment
     * instruction with a blank account line — and a member confronted with a
     * blank will guess, or ask in a WhatsApp group, and send real money
     * somewhere we never named.
     *
     * So an unconfigured method is excluded here and the reason recorded in
     * unconfigured(), where an administrator can see it. It is never rendered.
     *
     * @return array<int, array{code:string,kind:string,label:string,number:?string,holder:?string,instructions:?string}>
     */
    public static function methods(?string $lang = null): array
    {
        $lang = self::lang($lang);
        $out  = [];

        foreach (self::configured() as $code => $m) {
            $out[] = [
                'code'         => $code,
                'kind'         => $m['kind'],
                'label'        => $lang === 'fr' ? $m['label_fr'] : $m['label_en'],
                'number'       => $m['number'] ?? null,
                'holder'       => $m['holder'] ?? null,
                'instructions' => ($lang === 'fr' ? $m['instructions_fr'] : $m['instructions_en']) ?: null,
            ];
        }

        return $out;
    }

    /**
     * The active methods that were withheld, and why.
     *
     * Not for members — for whoever has to notice that Orange Money has been
     * silently missing from the payment page for a fortnight. A method that
     * disappears with no explanation is a support ticket nobody can answer.
     *
     * @return array<string,string>
     */
    public static function unconfigured(): array
    {
        $out = [];

        foreach ((array) config('payments.methods', []) as $code => $m) {
            if (! ($m['active'] ?? false)) {
                continue;
            }

            if ($reason = self::missingConfig($m)) {
                $out[$code] = $reason;
            }
        }

        return $out;
    }

    /**
     * What is missing from a method's configuration, or null when it is usable.
     *
     * Cash is judged on the holder rather than the number, because a number is
     * meaningless for a hand-to-hand payment while a named desk or person is
     * the only thing that makes it chaseable afterwards.
     */
    private static function missingConfig(array $m): ?string
    {
        if (($m['kind'] ?? null) === 'cash') {
            return trim((string) ($m['holder'] ?? '')) === ''
                ? 'no holder or office is named for cash payments'
                : null;
        }

        if (trim((string) ($m['number'] ?? '')) === '') {
            return 'no account number is configured';
        }

        if (trim((string) ($m['holder'] ?? '')) === '') {
            // The holder name is what a payer checks against the name their own
            // phone shows them before confirming the transfer. Without it they
            // have no way to notice they are paying the wrong account.
            return 'no account holder name is configured';
        }

        return null;
    }

    /** @return array<string,array> the active, fully configured method definitions */
    private static function configured(): array
    {
        $out = [];

        foreach ((array) config('payments.methods', []) as $code => $m) {
            if (($m['active'] ?? false) && self::missingConfig($m) === null) {
                $out[$code] = $m;
            }
        }

        return $out;
    }

    private static function lang(?string $lang): string
    {
        $lang ??= app()->getLocale();

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    /* ───────────────────────────── Lifecycle ───────────────────────────── */

    /**
     * Opens an intent to pay. Grants nothing.
     *
     * The reference is generated now rather than at confirmation, because it is
     * the string the payer types into the mobile-money reason field — it must
     * exist before the money moves, or there is nothing tying the operator's
     * transaction record to this row. It is kept short and upper-case for the
     * same reason a certificate PIN is: it gets typed on a keypad and read
     * aloud down a phone line.
     *
     * The method is validated against the configured set here as well as at
     * render time, because a form can be replayed with any method_code at all
     * and an intent against an unconfigured account is an intent to send money
     * nowhere.
     */
    public static function open(array $data): object
    {
        $method = $data['method_code'] ?? null;

        if (! array_key_exists($method, self::configured())) {
            throw new DomainException("Payment method [{$method}] is not configured and active; no payment can be opened against it.");
        }

        return DB::transaction(function () use ($data, $method) {
            $days = (int) config('payments.confirmation_window_days', 7);

            $id = DB::table('payments')->insertGetId([
                'uuid'         => (string) Str::uuid(),
                'reference'    => self::nextReference(),
                'payable_type' => $data['payable_type'] ?? null,
                'payable_id'   => $data['payable_id'] ?? null,
                'business_id'  => $data['business_id'] ?? null,
                'user_id'      => $data['user_id'] ?? null,
                'purpose'      => $data['purpose'] ?? 'other',
                // Cast through string, never float: the column is decimal and
                // the value must survive the round trip to the centime.
                'amount'       => (string) $data['amount'],
                'currency'     => $data['currency'] ?? 'XAF',
                'method_code'  => $method,
                'status'       => 'awaiting_payment',
                'expires_at'   => $days > 0 ? now()->addDays($days) : null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            self::event($id, 'opened', null, null, 'awaiting_payment', $data['note'] ?? null);

            return self::require($id);
        });
    }

    /**
     * A reference in the shape AH237-PAY-2026-000123.
     *
     * Sequence is per year and derived from a count inside the opening
     * transaction, with a uniqueness loop behind it. The loop is not paranoia:
     * two members paying at the same second would otherwise collide, and a
     * collision means one person's money is credited against another person's
     * account — the worst outcome this whole class exists to prevent.
     */
    private static function nextReference(): string
    {
        $prefix = (string) config('payments.reference_prefix', 'AH237-PAY');
        $year   = now()->format('Y');
        $seq    = DB::table('payments')->whereYear('created_at', $year)->count() + 1;

        do {
            $reference = sprintf('%s-%s-%s', $prefix, $year, str_pad((string) $seq, 6, '0', STR_PAD_LEFT));
            $seq++;
        } while (DB::table('payments')->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * The payer states they have paid.
     *
     * THIS RECORDS A CLAIM, NOT A RECEIPT. Every value written here came from
     * the payer — their name, their number, the reference they say the operator
     * gave them, a screenshot they uploaded. None of it has been checked
     * against anything, and the status it produces is `reported`, which means
     * precisely "somebody says so". Nothing downstream may read a reported
     * payment as settled, and no argument to this method can reach `confirmed`.
     */
    public static function report(int $id, array $claim): object
    {
        $p = self::require($id);

        self::assertTransition($p, 'report');

        DB::table('payments')->where('id', $id)->update([
            'payer_name'      => $claim['payer_name'] ?? null,
            'payer_number'    => $claim['payer_number'] ?? null,
            'payer_reference' => $claim['payer_reference'] ?? null,
            'proof_path'      => $claim['proof_path'] ?? null,
            'paid_at'         => isset($claim['paid_at']) ? \Illuminate\Support\Carbon::parse($claim['paid_at']) : now(),
            'reported_at'     => now(),
            'status'          => 'reported',
            'updated_at'      => now(),
        ]);

        self::event($id, 'reported', $claim['actor'] ?? $p->user_id, $p->status, 'reported', $claim['note'] ?? null);

        return self::require($id);
    }

    /**
     * A reviewer picks the claim up.
     *
     * Its only purpose is to make the queue honest: two administrators checking
     * the same MTN statement line at once is wasted work, and a payer chasing a
     * claim deserves to know somebody has it.
     */
    public static function beginReview(int $id, string $actor): object
    {
        $p = self::require($id);

        self::assertTransition($p, 'beginReview');

        DB::table('payments')->where('id', $id)->update([
            'status'      => 'under_review',
            'reviewed_by' => $actor,
            'updated_at'  => now(),
        ]);

        self::event($id, 'review_started', $actor, $p->status, 'under_review');

        return self::require($id);
    }

    /**
     * A human at the platform states the money arrived.
     *
     * The actor is required and has no default. That is the security property
     * written into the signature rather than into a comment: there is no way to
     * call this method without naming somebody, so a confirmation can never be
     * anonymous and a payer-facing path can never reach it by accident.
     *
     * Idempotent. Calling it on an already-confirmed payment returns the record
     * unchanged and appends nothing — no second event, and no double-application
     * of whatever the payment grants. That is what makes it safe for a retrying
     * MTN callback to call, which is exactly what will happen when the API seam
     * is wired up.
     */
    public static function confirm(int $id, string $actor, ?string $note): object
    {
        $p = self::require($id);

        if ($p->status === 'confirmed') {
            return $p;
        }

        self::assertTransition($p, 'confirm');

        DB::table('payments')->where('id', $id)->update([
            'status'      => 'confirmed',
            'reviewed_by' => $actor,
            'reviewed_at' => now(),
            'review_note' => $note,
            'updated_at'  => now(),
        ]);

        self::event($id, 'confirmed', $actor, $p->status, 'confirmed', $note);

        return self::require($id);
    }

    /**
     * The reviewer could not find the money.
     *
     * The reason is mandatory and must be more than whitespace. A payer told
     * only "rejected" has no idea whether they mistyped the reference, paid the
     * wrong number, or are being accused of lying, and cannot fix any of them.
     */
    public static function reject(int $id, string $actor, string $reason): object
    {
        $p = self::require($id);

        self::assertTransition($p, 'reject');

        if (trim($reason) === '') {
            throw new DomainException("Payment {$p->reference} cannot be rejected without a reason the payer can act on.");
        }

        DB::table('payments')->where('id', $id)->update([
            'status'           => 'rejected',
            'reviewed_by'      => $actor,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
            'updated_at'       => now(),
        ]);

        self::event($id, 'rejected', $actor, $p->status, 'rejected', $reason);

        return self::require($id);
    }

    /** Withdrawn before settlement, by the payer or by the platform. Grants nothing. */
    public static function cancel(int $id, ?string $actor = null, ?string $reason = null): object
    {
        $p = self::require($id);

        self::assertTransition($p, 'cancel');

        DB::table('payments')->where('id', $id)->update([
            'status'     => 'cancelled',
            'updated_at' => now(),
        ]);

        self::event($id, 'cancelled', $actor, $p->status, 'cancelled', $reason);

        return self::require($id);
    }

    /**
     * The window closed with nothing confirmed.
     *
     * Expiry is bookkeeping and nothing else: it writes no reviewer, no
     * reviewed_at and no paid_at, and grants nothing. It exists so an intent
     * nobody ever paid stops sitting in the reviewer's queue, not to make any
     * statement about whether money moved. A confirmed payment can never expire.
     */
    public static function expire(int $id): object
    {
        $p = self::require($id);

        self::assertTransition($p, 'expire');

        DB::table('payments')->where('id', $id)->update([
            'status'     => 'expired',
            'updated_at' => now(),
        ]);

        self::event($id, 'expired', null, $p->status, 'expired');

        return self::require($id);
    }

    /**
     * Every intent that has not yet reached an end state, oldest first.
     *
     * `reported` counts as outstanding, and that is the point: a claim is not a
     * settlement, and a business showing nothing outstanding the moment it
     * reports a payment would be telling its owner the fee is dealt with when
     * nobody has looked at it.
     */
    public static function outstandingFor(int|object $business): \Illuminate\Support\Collection
    {
        return DB::table('payments')
            ->where('business_id', is_object($business) ? $business->id : $business)
            ->whereIn('status', ['awaiting_payment', 'reported', 'under_review'])
            ->orderBy('created_at')
            ->get();
    }

    /** Everything ever recorded for a business, newest first. Nothing is hidden. */
    public static function historyFor(int|object $business): \Illuminate\Support\Collection
    {
        return DB::table('payments')
            ->where('business_id', is_object($business) ? $business->id : $business)
            ->orderByDesc('created_at')
            ->get();
    }

    /** The append-only trail for one payment, in the order it happened. */
    public static function trail(int $id): \Illuminate\Support\Collection
    {
        return DB::table('payment_events')->where('payment_id', $id)->orderBy('id')->get();
    }

    public static function find(int $id): ?object
    {
        return DB::table('payments')->find($id);
    }

    public static function findByReference(string $reference): ?object
    {
        return DB::table('payments')->where('reference', trim($reference))->first();
    }

    /* ───────────────────────────── Internals ───────────────────────────── */

    private static function require(int $id): object
    {
        $p = self::find($id);

        if (! $p) {
            throw new DomainException("No payment [{$id}].");
        }

        return $p;
    }

    private static function assertTransition(object $p, string $move): void
    {
        if (! in_array($p->status, self::ALLOWED_FROM[$move], true)) {
            throw new DomainException(
                "Payment {$p->reference} cannot {$move} from status [{$p->status}]; "
                . 'permitted from [' . implode(', ', self::ALLOWED_FROM[$move]) . '].'
            );
        }
    }

    /**
     * Appends to the trail. Never updates, never deletes.
     *
     * Written as a plain insert with no accompanying update path anywhere in
     * this class, because the value of the trail is entirely in the fact that
     * nothing can be quietly rewritten after the argument starts.
     */
    private static function event(
        int $paymentId,
        string $event,
        ?string $actor = null,
        ?string $from = null,
        ?string $to = null,
        ?string $note = null,
    ): void {
        DB::table('payment_events')->insert([
            'payment_id'    => $paymentId,
            'event'         => $event,
            'actor_user_id' => $actor,
            'note'          => $note,
            'from_status'   => $from,
            'to_status'     => $to,
            'occurred_at'   => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
