<?php

namespace App\Support;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * The register of what buyers have said about an artisan, and what the platform
 * is willing to vouch for when it repeats them.
 *
 * The profile design asks for a star average, a distribution and a quoted
 * review carrying a "Verified Buyer" badge. Two of those three the platform can
 * honestly supply. The third it cannot: this platform is not a party to sales.
 * It holds no orders, moves no money and never learns whether a transaction
 * happened. A badge reading "verified buyer" would therefore be the software
 * asserting a fact it has no way of knowing, which is precisely the kind of
 * decoration that had to be stripped off the certificates.
 *
 * So the badge is built on what the platform can genuinely check: that this
 * account opened a conversation with this artisan through the platform's own
 * messaging and actually sent a message in it. That is a real fact, held in the
 * platform's own tables, and it is useful to a reader in a way a star rating is
 * not — it separates somebody who talked to this workshop from somebody who
 * merely has an opinion about it. It is labelled for exactly that and nothing
 * more: "a contacté cet artisan via la plateforme" / "contacted this artisan
 * through the platform". It does not claim a purchase, and the surfaces must
 * never shorten it into one.
 *
 * The contact flag is computed at submission, never accepted from the caller.
 * A flag a reviewer can set is not a check, it is a text field with a tick
 * beside it.
 *
 * Nothing here is published by writing it. A review arrives pending and a named
 * moderator moves it, because the alternative — the default the table shipped
 * with — let a stranger put a sentence on somebody's livelihood with nobody in
 * between. Rejection and hiding both require a reason, for the same reason
 * rejecting a payment does: a decision somebody cannot see the grounds of is a
 * decision they cannot answer.
 *
 * And an artisan with no reviews has no rating. Not zero, not "5.0 (0)": the
 * summary reports emptiness as emptiness and the templates print a sentence
 * instead of a number. A default score is a fabricated one.
 */
class ArtisanReviews
{
    /** Waiting for a moderator. Everything starts here, including edits. */
    public const PENDING = 'pending';

    /** A moderator has read it and it is on the public profile. */
    public const PUBLISHED = 'published';

    /** A moderator refused it. It is not public and does not return without a fresh submission. */
    public const REJECTED = 'rejected';

    /** It was public and a moderator withdrew it. Kept, never deleted. */
    public const HIDDEN = 'hidden';

    /**
     * Where a review may go from where it is.
     *
     * A rejected review is a closed matter: the way back is the reviewer
     * writing again, which resets it to pending through submit(). Publishing
     * one straight from rejected would let a second moderator quietly overturn
     * the first without a new decision being recorded against the text.
     */
    private const TRANSITIONS = [
        self::PENDING   => [self::PUBLISHED, self::REJECTED],
        self::PUBLISHED => [self::HIDDEN],
        self::REJECTED  => [],
        self::HIDDEN    => [self::PUBLISHED],
    ];

    /**
     * Records or amends one person's review of one artisan.
     *
     * A second submission edits the first rather than stacking, and returns it
     * to the queue. Somebody who rewrites a published five-star review into a
     * one-star one has written something no moderator has read, and leaving the
     * old verdict attached to new words would make moderation meaningless.
     */
    public static function submit(Business|int $business, User $reviewer, array $data): object
    {
        $businessId = self::businessId($business);
        $model      = $business instanceof Business ? $business : Business::findOrFail($businessId);

        $verdict = self::canReview($model, $reviewer);
        if (! $verdict['allowed']) {
            throw new DomainException("This account may not review this business [{$verdict['reason']}].");
        }

        $rating = (int) ($data['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            throw new DomainException("A rating must be a whole number from 1 to 5; [{$rating}] is not one.");
        }

        $title = self::trimToNull($data['title'] ?? null, 150);
        $body  = self::trimToNull($data['body'] ?? null, 2000);

        $existing = DB::table('business_reviews')
            ->where('business_id', $businessId)
            ->where('reviewer_id', $reviewer->id)
            ->first();

        $row = [
            'rating' => $rating,
            'title'  => $title,
            'body'   => $body,
            // Checked here, every time, against the messaging tables. The
            // reviewer does not get a say in whether their own badge appears.
            'is_verified_contact' => self::verifiedContact($model, $reviewer),
            'status'              => self::PENDING,
            'published_at'        => null,
            'moderated_by'        => null,
            'moderated_at'        => null,
            'moderation_note'     => null,
            'updated_at'          => now(),
        ];

        if ($existing) {
            DB::table('business_reviews')->where('id', $existing->id)->update($row);

            return DB::table('business_reviews')->find($existing->id);
        }

        $id = DB::table('business_reviews')->insertGetId($row + [
            'business_id' => $businessId,
            'reviewer_id' => $reviewer->id,
            'created_at'  => now(),
        ]);

        return DB::table('business_reviews')->find($id);
    }

    /** Puts a review on the public profile, under the name of the person who decided it. */
    public static function publish(int $id, string $actor): object
    {
        $review = self::findOrFail($id);
        self::assertTransition($review, self::PUBLISHED);

        DB::table('business_reviews')->where('id', $id)->update([
            'status'          => self::PUBLISHED,
            'published_at'    => now(),
            'moderated_by'    => $actor,
            'moderated_at'    => now(),
            'moderation_note' => null,
            'updated_at'      => now(),
        ]);

        return self::findOrFail($id);
    }

    /**
     * Refuses a review. The reason is required and is not decoration: it is what
     * a moderator's colleague reads six months later when the same reviewer asks
     * why their words never appeared.
     */
    public static function reject(int $id, string $actor, string $reason): object
    {
        $review = self::findOrFail($id);
        self::assertTransition($review, self::REJECTED);

        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException("A review cannot be rejected without a reason.");
        }

        DB::table('business_reviews')->where('id', $id)->update([
            'status'          => self::REJECTED,
            'published_at'    => null,
            'moderated_by'    => $actor,
            'moderated_at'    => now(),
            'moderation_note' => mb_substr($reason, 0, 500),
            'updated_at'      => now(),
        ]);

        return self::findOrFail($id);
    }

    /**
     * Withdraws a review that was already public.
     *
     * published_at is cleared, so the row cannot drift back into a listing that
     * only checks for a date. The text itself stays: a review that was public
     * and was taken down is a thing that happened, and a register that can
     * silently erase one is a register nobody should trust in the other
     * direction either.
     */
    public static function hide(int $id, string $actor, string $reason): object
    {
        $review = self::findOrFail($id);
        self::assertTransition($review, self::HIDDEN);

        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException("A published review cannot be withdrawn without a reason.");
        }

        DB::table('business_reviews')->where('id', $id)->update([
            'status'          => self::HIDDEN,
            'published_at'    => null,
            'moderated_by'    => $actor,
            'moderated_at'    => now(),
            'moderation_note' => mb_substr($reason, 0, 500),
            'updated_at'      => now(),
        ]);

        return self::findOrFail($id);
    }

    /**
     * What the public profile is allowed to say about this artisan's rating.
     *
     * Published rows only, and `has_ratings` is the flag templates must branch
     * on. `average` is null and not 0.0 when there is nothing to average,
     * because 0.0 renders as a score and a score is the one thing an artisan
     * with no reviews must not be given.
     */
    public static function summaryFor(Business|int $business): array
    {
        $businessId = self::businessId($business);

        $counts = DB::table('business_reviews')
            ->where('business_id', $businessId)
            ->where('status', self::PUBLISHED)
            ->selectRaw('rating, count(*) as n')
            ->groupBy('rating')
            ->pluck('n', 'rating');

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $total = 0;
        $sum   = 0;

        foreach ($counts as $rating => $n) {
            $rating = (int) $rating;
            if (! array_key_exists($rating, $distribution)) {
                continue; // A rating outside 1–5 cannot be written; if one ever is, it is not averaged in.
            }
            $distribution[$rating] = (int) $n;
            $total += (int) $n;
            $sum   += $rating * (int) $n;
        }

        return [
            'count'        => $total,
            'has_ratings'  => $total > 0,
            'average'      => $total > 0 ? round($sum / $total, 1) : null,
            'distribution' => $distribution,
        ];
    }

    /** The published reviews themselves, newest decision first, with their author's name. */
    public static function publishedFor(Business|int $business, int $limit = 20): \Illuminate\Support\Collection
    {
        return DB::table('business_reviews as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.reviewer_id')
            ->where('r.business_id', self::businessId($business))
            ->where('r.status', self::PUBLISHED)
            ->orderByDesc('r.published_at')
            ->limit($limit)
            ->get(['r.*', 'u.name as reviewer_name']);
    }

    /**
     * Whether this person may write about this artisan, and if not, why.
     *
     * The reason code is returned rather than a bare false because the form has
     * to say something useful: "sign in", "confirm your address" and "this is
     * your own workshop" are three different sentences and only one of them is
     * a refusal the reader can do anything about.
     */
    public static function canReview(Business|int $business, ?User $user): array
    {
        if (! $user) {
            return ['allowed' => false, 'reason' => 'guest'];
        }

        // A review is a public statement attached to somebody's trade. An
        // account nobody has confirmed the address of is not a person yet, and
        // an unlimited supply of them is one signup form away.
        if (! $user->is_email_verified) {
            return ['allowed' => false, 'reason' => 'unverified'];
        }

        if ($user->status !== null && $user->status !== 'active') {
            return ['allowed' => false, 'reason' => 'account_inactive'];
        }

        $model = $business instanceof Business ? $business : Business::find(self::businessId($business));
        if (! $model) {
            return ['allowed' => false, 'reason' => 'unknown_business'];
        }

        // Nobody rates themselves. This is the single most obvious way to
        // manufacture a five-star profile and it is closed in the service, not
        // by hiding the form.
        if ((string) $model->user_id === (string) $user->id) {
            return ['allowed' => false, 'reason' => 'own_business'];
        }

        return ['allowed' => true, 'reason' => 'ok'];
    }

    /**
     * The one thing behind the badge: this account sent this artisan a message
     * through the platform.
     *
     * A conversation row on its own is not enough — one is created the moment
     * somebody opens a thread, so an empty conversation proves only that a
     * button was clicked. At least one message from the reviewer's own hand is
     * the minimum that makes the badge's sentence true.
     */
    public static function verifiedContact(Business|int $business, User $user): bool
    {
        return DB::table('conversations as c')
            ->join('messages as m', 'm.conversation_id', '=', 'c.id')
            ->where('c.business_id', self::businessId($business))
            ->where('c.buyer_id', $user->id)
            ->where('m.sender_id', $user->id)
            ->exists();
    }

    /** The reviewer-facing label for the badge, in both languages. */
    public static function contactBadgeLabel(string $lang = 'fr'): string
    {
        return $lang === 'fr'
            ? 'A contacté cet artisan via la plateforme'
            : 'Contacted this artisan through the platform';
    }

    /** The moderation queue, oldest first: the person waiting longest is why it exists. */
    public static function queue(string $status = self::PENDING, int $limit = 200): \Illuminate\Support\Collection
    {
        return DB::table('business_reviews as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.reviewer_id')
            ->leftJoin('businesses as b', 'b.id', '=', 'r.business_id')
            ->where('r.status', $status)
            ->orderBy('r.created_at')
            ->limit($limit)
            ->get([
                'r.*',
                'u.name as reviewer_name', 'u.email as reviewer_email',
                'b.name_fr as business_name', 'b.slug as business_slug',
            ]);
    }

    /** How many reviews sit in each state, so an empty-looking queue is not mistaken for an empty register. */
    public static function queueCounts(): array
    {
        $counts = DB::table('business_reviews')->selectRaw('status, count(*) as n')
            ->groupBy('status')->pluck('n', 'status')->all();

        return array_map(
            fn ($state) => (int) ($counts[$state] ?? 0),
            array_combine(array_keys(self::TRANSITIONS), array_keys(self::TRANSITIONS))
        );
    }

    public static function find(int $id): ?object
    {
        return DB::table('business_reviews')->find($id);
    }

    // ── internals ───────────────────────────────────────────────────────────

    private static function findOrFail(int $id): object
    {
        $review = DB::table('business_reviews')->find($id);
        if (! $review) {
            throw new DomainException("No review [{$id}].");
        }

        return $review;
    }

    private static function assertTransition(object $review, string $to): void
    {
        $from    = (string) $review->status;
        $allowed = self::TRANSITIONS[$from] ?? [];

        if (! in_array($to, $allowed, true)) {
            throw new DomainException("A review that is [{$from}] cannot become [{$to}].");
        }
    }

    private static function businessId(Business|int $business): int
    {
        return (int) ($business instanceof Business ? $business->id : $business);
    }

    private static function trimToNull(?string $value, int $max): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
