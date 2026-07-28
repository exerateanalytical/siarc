<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What the platform charges a member, and what the money buys.
 *
 * SCOPE. Everything here is the platform's own fee for listing an artisan:
 * registration, which is the first year of a subscription, and the renewals
 * after it. It is not connected to sales between a buyer and an artisan; the
 * platform is not a party to those and receives none of that money. If a
 * purpose ever appears here that names an order or a product, something has
 * gone badly wrong.
 *
 * THERE IS NO REGISTRATION FEE SEPARATE FROM THE SUBSCRIPTION. Registering is
 * the first year of a plan and nothing else, which is why openRegistration()
 * reads price_yearly straight off the plan row rather than composing a fee out
 * of parts. One number, in one place, in the database.
 *
 * NO PRICE IS EVER INVENTED HERE. Every amount comes from `subscription_plans`.
 * If the plan a caller names is missing or inactive this class throws rather
 * than falling back to a default, because a default price is a wrong price
 * shown to somebody about to send real money, and a loud failure that an
 * administrator fixes in a minute is cheaper than a member paying the wrong
 * amount and waiting a fortnight for a refund nobody has a process for.
 *
 * PAYMENT AND OWNERSHIP ARE DELIBERATELY SEPARATE. The subscription buys
 * *publication* — appearing in the public directory. It does not buy control of
 * a record. That distinction is what lets the 510 imported SIARC artisans, who
 * never signed up and never consented to being listed, claim their profile for
 * nothing and immediately correct or withdraw what we hold about them, while
 * still paying to be listed. A fee gate on the claim itself would mean charging
 * a person for access to their own name, and that is not a position this
 * codebase will take. See the SIARC claim route for the member-facing wording.
 *
 * Settlement itself belongs to ManualPayment and is not reimplemented here.
 * This class opens intents and reacts to confirmations; it never decides that
 * money arrived.
 */
class PlatformFees
{
    /**
     * Statuses on `payments` that mean "this intent is still live" — the money
     * has not been confirmed, but nothing has killed the intent either.
     * Reopening registration while one of these is outstanding must reuse it,
     * because two references for the same fee means a member pays one, an
     * administrator confirms the other, and the trail no longer says what
     * happened.
     */
    private const LIVE_INTENT = ['awaiting_payment', 'reported', 'under_review'];

    /* ───────────────────────────── Plans ───────────────────────────────── */

    /**
     * The active plans, priced from the database.
     *
     * Returned as plain arrays rather than a model so callers cannot lazily
     * mutate a price on the way to a view.
     *
     * @return array<int, array{id:int,slug:string,name:string,price_yearly:int,currency:string}>
     */
    public static function plans(?string $lang = null): array
    {
        $lang = self::lang($lang);

        return DB::table('subscription_plans')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($p) => [
                'id'           => (int) $p->id,
                'slug'         => $p->slug,
                'name'         => $lang === 'en' ? ($p->name_en ?: $p->name_fr) : $p->name_fr,
                'price_yearly' => (int) $p->price_yearly,
                'currency'     => $p->currency,
            ])
            ->all();
    }

    /** The raw plan row, or null. Not public: callers get prices via plans(). */
    /**
     * A plan by slug, or the cheapest paid plan when none was named.
     *
     * The fallback exists because the business-creation form does not yet ask
     * which plan the member wants, and throwing there would cost them their
     * shop over a field nobody showed them. Free and quoted plans are excluded
     * from the fallback: a zero-priced plan would open a payment for nothing
     * and then activate on confirmation of a payment that was never owed.
     */
    private static function plan(?string $slug): ?object
    {
        $query = DB::table('subscription_plans')->where('is_active', true);

        if ($slug !== null && $slug !== '') {
            return $query->where('slug', $slug)->first();
        }

        return $query->where('price_yearly', '>', 0)->orderBy('price_yearly')->first();
    }

    /* ──────────────────────── A business's standing ────────────────────── */

    /**
     * The most recent subscription row for a business, whatever its state.
     *
     * Newest first rather than "the active one", because a caller showing a
     * member their standing needs to see an expired or cancelled row too — a
     * page that renders nothing for a lapsed membership tells the member they
     * never had one.
     */
    public static function subscriptionFor(Business|int $business): ?object
    {
        return DB::table('business_subscriptions')
            ->where('business_id', self::id($business))
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->first();
    }

    /** The plan behind that subscription, or null when there is none. */
    public static function planFor(Business|int $business): ?object
    {
        $sub = self::subscriptionFor($business);

        return $sub ? DB::table('subscription_plans')->find($sub->subscription_plan_id) : null;
    }

    /**
     * Is this business paid up, right now?
     *
     * Two conditions, and the second one is the one that gets forgotten. The
     * status must be `active`, and the date the next payment falls due must
     * still be ahead of us. A row left at `active` with a date twelve months in
     * the past is a membership that ran out and that nobody ran a job over; it
     * is not a paid membership, and treating it as one would keep a listing in
     * the public directory for years after the last payment.
     *
     * A missing next_payment_at is treated as not active for the same reason:
     * an unbounded membership is not something anyone paid for.
     */
    public static function isActive(Business|int $business): bool
    {
        $sub = self::subscriptionFor($business);

        if (! $sub || $sub->status !== 'active' || $sub->next_payment_at === null) {
            return false;
        }

        return Carbon::parse($sub->next_payment_at)->isFuture();
    }

    /** The gate the publication path asks about, named for what it means. */
    public static function requiresPaymentToPublish(Business|int $business): bool
    {
        return ! self::isActive($business);
    }

    /* ───────────────────────── Registration ────────────────────────────── */

    /**
     * Open an intent to pay the first year for this business.
     *
     * Grants nothing and publishes nothing. All it produces is a reference the
     * member types into the mobile-money reason field, and a row a reviewer
     * will later look at against the operator's statement.
     *
     * An existing live intent for the same plan is returned unchanged rather
     * than superseded. A member who reloads the claim page, or who claims and
     * then revisits their payments, must keep seeing the one reference they may
     * already have written on a piece of paper — minting a second would mean the
     * money arrives against a reference the reviewer is not looking at.
     *
     * A live intent for a *different* plan is left alone and a new one opened
     * beside it: choosing a different plan is a real decision, and silently
     * repricing somebody's outstanding intent would change what they owe after
     * they had already been told a number.
     */
    public static function openRegistration(Business|int $business, ?string $planSlug = null, ?string $methodCode = null): object
    {
        $businessId = self::id($business);
        $plan = self::plan($planSlug);

        if (! $plan) {
            throw new DomainException(
                "No active subscription plan [{$planSlug}]; refusing to open a registration payment rather than "
                . 'inventing a price.'
            );
        }

        $existing = DB::table('payments')
            ->where('business_id', $businessId)
            ->where('purpose', 'registration')
            ->whereIn('status', self::LIVE_INTENT)
            ->orderByDesc('id')
            ->get()
            ->first(fn ($p) => bccomp((string) $p->amount, (string) $plan->price_yearly, 2) === 0);

        if ($existing) {
            return $existing;
        }

        return ManualPayment::open([
            'business_id' => $businessId,
            'user_id'     => self::ownerId($businessId),
            'purpose'     => 'registration',
            // String, never float: the column is decimal and the amount must
            // survive the round trip to the centime.
            'amount'      => (string) $plan->price_yearly,
            'currency'    => $plan->currency,
            'method_code' => $methodCode ?? self::defaultMethod(),
            'note'        => "First year, plan [{$plan->slug}].",
        ]);
    }

    /**
     * Turn a confirmed registration payment into a live subscription.
     *
     * The refusal below is the whole security property of this class. A payer's
     * own report reaches `reported` and no further, so if activation accepted
     * anything short of `confirmed`, publication would be free to anyone willing
     * to claim they had paid. It throws rather than returning false because a
     * caller who forgets to check a boolean would publish a listing nobody paid
     * for, and that is not a failure mode to leave to caller discipline.
     *
     * Idempotent in effect: activating twice on the same confirmed payment
     * re-stamps the same window rather than granting a second year, which
     * matters because ManualPayment::confirm() is itself safe to retry and a
     * future MTN callback will retry it.
     */
    public static function activate(Business|int $business, int $paymentId): void
    {
        $businessId = self::id($business);
        $payment = ManualPayment::find($paymentId);

        if (! $payment) {
            throw new DomainException("No payment [{$paymentId}] to activate a subscription against.");
        }

        if ((int) $payment->business_id !== $businessId) {
            throw new DomainException(
                "Payment {$payment->reference} belongs to another business; activating it here would credit one "
                . "member's money to another's listing."
            );
        }

        if ($payment->status !== 'confirmed') {
            throw new DomainException(
                "Payment {$payment->reference} is [{$payment->status}], not confirmed. A subscription may only be "
                . 'activated after a reviewer has stated the money arrived.'
            );
        }

        $plan = DB::table('subscription_plans')
            ->where('currency', $payment->currency)
            ->get()
            ->first(fn ($p) => bccomp((string) $p->price_yearly, (string) $payment->amount, 2) === 0);

        if (! $plan) {
            throw new DomainException(
                "Payment {$payment->reference} does not match the price of any plan; refusing to guess which "
                . 'subscription it bought.'
            );
        }

        // The year runs from the confirmation, not from the day the intent was
        // opened. A member who takes three weeks to get to a mobile-money agent
        // should not lose three weeks of what they paid for.
        $started = $payment->reviewed_at ? Carbon::parse($payment->reviewed_at) : Carbon::now();

        DB::transaction(function () use ($businessId, $plan, $payment, $started) {
            $existing = DB::table('business_subscriptions')
                ->where('business_id', $businessId)
                ->orderByDesc('id')
                ->first();

            $values = [
                'business_id'          => $businessId,
                'subscription_plan_id' => $plan->id,
                'status'               => 'active',
                'amount'               => $payment->amount,
                'started_at'           => $started,
                'next_payment_at'      => $started->copy()->addYear(),
                'updated_at'           => now(),
            ];

            if ($existing) {
                DB::table('business_subscriptions')->where('id', $existing->id)->update($values);
            } else {
                DB::table('business_subscriptions')->insert($values + ['created_at' => now()]);
            }
        });
    }

    /* ───────────────────────────── Internals ───────────────────────────── */

    private static function id(Business|int $business): int
    {
        return is_object($business) ? (int) $business->id : $business;
    }

    private static function ownerId(int $businessId): ?string
    {
        return DB::table('businesses')->where('id', $businessId)->value('user_id');
    }

    /**
     * The method an intent is opened against when the caller does not pick one.
     *
     * ManualPayment refuses to open against a method that is inactive or has no
     * account number configured, and that refusal is correct — an intent
     * pointing at an unconfigured account is an instruction to send money
     * nowhere. So this reads the first configured method and lets the absence of
     * one surface as an exception the caller can decide what to do about,
     * rather than picking a plausible-looking code that would fail later and
     * further from the cause.
     */
    private static function defaultMethod(): string
    {
        $methods = ManualPayment::methods();

        if ($methods === []) {
            throw new DomainException(
                'No payment method is both active and configured, so no payment intent can be opened. '
                . 'See ManualPayment::unconfigured() for which are missing what.'
            );
        }

        return $methods[0]['code'];
    }

    private static function lang(?string $lang): string
    {
        $lang ??= app()->getLocale();

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }
}
