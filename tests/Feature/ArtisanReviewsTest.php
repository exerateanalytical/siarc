<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Support\ArtisanAwards;
use App\Support\ArtisanReviews;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * What a rating on this platform is allowed to mean.
 *
 * The profile designs show a star average, a distribution and quoted reviews.
 * None of that existed, and the temptation when a design shows a number is to
 * make the number appear. These tests exist to make that impossible: nothing
 * reaches the public average without a moderator, an empty register reports
 * itself as empty rather than as a score, and the badge beside a review says
 * only what the platform can actually check.
 */
class ArtisanReviewsTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function makeBuyer(array $attrs = []): User
    {
        return $this->makeUser(array_merge(['is_email_verified' => true, 'status' => 'active'], $attrs));
    }

    private function adminSession(User $admin): array
    {
        return ['siac_user' => [
            'id'       => $admin->id,
            'name'     => 'Admin',
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]];
    }

    // ── Submission and moderation ───────────────────────────────────────────

    public function test_a_submitted_review_is_pending_and_is_not_in_the_public_summary(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();

        $review = ArtisanReviews::submit($business, $buyer, ['rating' => 5, 'body' => 'Travail soigné.']);

        $this->assertSame('pending', $review->status);
        $this->assertNull($review->published_at);

        $summary = ArtisanReviews::summaryFor($business);
        $this->assertSame(0, $summary['count']);
        $this->assertFalse($summary['has_ratings']);
    }

    public function test_publishing_moves_a_review_into_the_summary_and_hiding_takes_it_out(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();
        $admin    = $this->makeBuyer();

        $review = ArtisanReviews::submit($business, $buyer, ['rating' => 4]);

        ArtisanReviews::publish($review->id, $admin->id);
        $summary = ArtisanReviews::summaryFor($business);
        $this->assertSame(1, $summary['count']);
        $this->assertTrue($summary['has_ratings']);
        $this->assertSame(4.0, $summary['average']);

        ArtisanReviews::hide($review->id, $admin->id, 'Propos hors sujet.');
        $summary = ArtisanReviews::summaryFor($business);
        $this->assertSame(0, $summary['count']);
        $this->assertFalse($summary['has_ratings']);
    }

    public function test_an_illegal_moderation_transition_is_refused(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();
        $admin    = $this->makeBuyer();

        $review = ArtisanReviews::submit($business, $buyer, ['rating' => 3]);
        ArtisanReviews::reject($review->id, $admin->id, 'Aucun échange constaté.');

        $this->expectException(\DomainException::class);
        ArtisanReviews::publish($review->id, $admin->id);
    }

    public function test_an_artisan_cannot_review_their_own_business(): void
    {
        $owner    = $this->makeBuyer();
        $business = $this->makeBusiness($owner);

        $verdict = ArtisanReviews::canReview($business, $owner);
        $this->assertFalse($verdict['allowed']);
        $this->assertSame('own_business', $verdict['reason']);

        $this->expectException(\DomainException::class);
        ArtisanReviews::submit($business, $owner, ['rating' => 5]);
    }

    public function test_a_guest_or_an_unverified_account_cannot_review(): void
    {
        $business = $this->makeBusiness();

        $this->assertFalse(ArtisanReviews::canReview($business, null)['allowed']);
        $this->assertSame('guest', ArtisanReviews::canReview($business, null)['reason']);

        $unverified = $this->makeUser(['is_email_verified' => false]);
        $verdict    = ArtisanReviews::canReview($business, $unverified);
        $this->assertFalse($verdict['allowed']);
        $this->assertSame('unverified', $verdict['reason']);
    }

    public function test_a_second_submission_by_the_same_person_edits_rather_than_duplicates(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();

        ArtisanReviews::submit($business, $buyer, ['rating' => 2, 'body' => 'Premier avis.']);
        $second = ArtisanReviews::submit($business, $buyer, ['rating' => 5, 'body' => 'Avis corrigé.']);

        $this->assertSame(1, DB::table('business_reviews')
            ->where('business_id', $business->id)->where('reviewer_id', $buyer->id)->count());
        $this->assertSame(5, (int) $second->rating);
        $this->assertSame('Avis corrigé.', $second->body);
    }

    public function test_an_edit_after_publication_returns_the_review_to_moderation(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();
        $admin    = $this->makeBuyer();

        $review = ArtisanReviews::submit($business, $buyer, ['rating' => 5]);
        ArtisanReviews::publish($review->id, $admin->id);

        ArtisanReviews::submit($business, $buyer, ['rating' => 1, 'body' => 'Je change d’avis.']);

        $this->assertSame(0, ArtisanReviews::summaryFor($business)['count']);
    }

    // ── The summary ─────────────────────────────────────────────────────────

    public function test_an_empty_register_reports_empty_and_offers_no_rating_at_all(): void
    {
        $business = $this->makeBusiness();
        $summary  = ArtisanReviews::summaryFor($business);

        $this->assertSame(0, $summary['count']);
        $this->assertFalse($summary['has_ratings']);
        // Not 0.0, not 5.0, not any number a template could print as a score.
        $this->assertNull($summary['average']);
        $this->assertSame([5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0], $summary['distribution']);
    }

    public function test_the_distribution_sums_to_the_count_and_the_mean_is_to_one_decimal(): void
    {
        $business = $this->makeBusiness();
        $admin    = $this->makeBuyer();

        foreach ([5, 5, 4, 3, 1] as $rating) {
            $review = ArtisanReviews::submit($business, $this->makeBuyer(), ['rating' => $rating]);
            ArtisanReviews::publish($review->id, $admin->id);
        }

        $summary = ArtisanReviews::summaryFor($business);

        $this->assertSame(5, $summary['count']);
        $this->assertSame(5, array_sum($summary['distribution']));
        $this->assertSame([5 => 2, 4 => 1, 3 => 1, 2 => 0, 1 => 1], $summary['distribution']);
        $this->assertSame(3.6, $summary['average']);
    }

    public function test_a_rating_outside_one_to_five_is_rejected(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();

        foreach ([0, 6, -1] as $bad) {
            try {
                ArtisanReviews::submit($business, $buyer, ['rating' => $bad]);
                $this->fail("Rating {$bad} was accepted.");
            } catch (\DomainException $e) {
                $this->assertStringContainsString('1', $e->getMessage());
            }
        }

        $this->assertSame(0, DB::table('business_reviews')->count());
    }

    // ── The badge ───────────────────────────────────────────────────────────

    public function test_the_contact_badge_is_true_only_when_a_message_really_passed_through_the_platform(): void
    {
        $business = $this->makeBusiness();
        $stranger = $this->makeBuyer();
        $enquirer = $this->makeBuyer();

        $this->assertFalse(ArtisanReviews::verifiedContact($business, $stranger));

        // A conversation row on its own is not contact: it is an empty thread.
        $conversationId = DB::table('conversations')->insertGetId([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'buyer_id'    => $enquirer->id,
            'business_id' => $business->id,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $this->assertFalse(ArtisanReviews::verifiedContact($business, $enquirer));

        DB::table('messages')->insert([
            'conversation_id' => $conversationId,
            'sender_id'       => $enquirer->id,
            'body'            => 'Bonjour, quel est le délai pour une commande de dix pièces ?',
            'type'            => 'text',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->assertTrue(ArtisanReviews::verifiedContact($business, $enquirer));
        $this->assertFalse(ArtisanReviews::verifiedContact($business, $stranger));

        // And the flag stored on the review is the checked value, not a claim.
        $review = ArtisanReviews::submit($business, $enquirer, ['rating' => 5]);
        $this->assertTrue((bool) $review->is_verified_contact);

        $other = ArtisanReviews::submit($business, $stranger, ['rating' => 5]);
        $this->assertFalse((bool) $other->is_verified_contact);
    }

    // ── The moderation surface ──────────────────────────────────────────────

    public function test_a_guest_cannot_reach_the_moderation_page(): void
    {
        $this->get('/tableau-de-bord/admin/avis')->assertRedirect();
        $this->get('/tableau-de-bord/admin/avis')->assertRedirectContains('/login');
    }

    public function test_a_non_admin_cannot_publish_a_review(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();
        $review   = ArtisanReviews::submit($business, $buyer, ['rating' => 5]);

        $session = ['siac_user' => [
            'id' => $buyer->id, 'name' => 'Buyer', 'email' => $buyer->email,
            'role' => 'buyer', 'is_admin' => false,
        ]];

        $this->withSession($session)
            ->post('/tableau-de-bord/admin/avis/' . $review->id . '/publier')
            ->assertRedirect('/tableau-de-bord');

        $this->assertSame('pending', DB::table('business_reviews')->where('id', $review->id)->value('status'));
    }

    public function test_an_administrator_sees_the_queue_and_can_publish(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();
        $admin    = $this->makeBuyer();
        $review   = ArtisanReviews::submit($business, $buyer, ['rating' => 5, 'body' => 'Excellent travail du raphia.']);

        $this->withSession($this->adminSession($admin))
            ->get('/tableau-de-bord/admin/avis')->assertOk();

        $this->withSession($this->adminSession($admin))
            ->post('/tableau-de-bord/admin/avis/' . $review->id . '/publier')
            ->assertRedirect();

        $this->assertSame(1, ArtisanReviews::summaryFor($business)['count']);
    }

    // ── Awards ──────────────────────────────────────────────────────────────

    public function test_an_award_records_the_body_that_gave_it(): void
    {
        $business = $this->makeBusiness();
        $admin    = $this->makeBuyer();

        $award = ArtisanAwards::record($business, [
            'title_fr'     => 'Prix régional du tissage',
            'title_en'     => 'Regional weaving prize',
            'issuer'       => 'Chambre des métiers du Littoral',
            'year'         => 2024,
            'recorded_by'  => $admin->id,
            'evidence_url' => 'https://example.test/palmares-2024',
        ]);

        $this->assertSame('Chambre des métiers du Littoral', $award->issuer);
        $this->assertSame($admin->id, $award->recorded_by);

        $rows = ArtisanAwards::forBusiness($business, 'en');
        $this->assertCount(1, $rows);
        $this->assertSame('Regional weaving prize', $rows[0]['title']);
    }

    public function test_an_award_cannot_be_self_declared_by_the_artisan(): void
    {
        $owner    = $this->makeBuyer();
        $business = $this->makeBusiness($owner);

        $this->expectException(\DomainException::class);
        ArtisanAwards::record($business, [
            'title_fr'    => 'Prix inventé',
            'issuer'      => 'UNESCO',
            'year'        => 2025,
            'recorded_by' => $owner->id,
        ]);
    }

    public function test_an_award_without_a_named_issuer_is_refused(): void
    {
        $business = $this->makeBusiness();
        $admin    = $this->makeBuyer();

        $this->expectException(\DomainException::class);
        ArtisanAwards::record($business, [
            'title_fr'    => 'Distinction',
            'issuer'      => '',
            'year'        => 2024,
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_a_non_admin_cannot_record_an_award_through_the_admin_surface(): void
    {
        $business = $this->makeBusiness();
        $buyer    = $this->makeBuyer();

        $this->withSession(['siac_user' => [
            'id' => $buyer->id, 'name' => 'Buyer', 'email' => $buyer->email,
            'role' => 'buyer', 'is_admin' => false,
        ]])->post('/tableau-de-bord/admin/distinctions', [
            'business_id' => $business->id,
            'title_fr'    => 'Prix',
            'issuer'      => 'Ministère',
            'year'        => 2024,
        ])->assertRedirect('/tableau-de-bord');

        $this->assertSame(0, DB::table('business_awards')->count());
    }
}
