<?php

namespace Tests\Feature;

use App\Modules\Businesses\Models\Business;
use App\Support\ArtisanProfile;
use App\Support\ArtisanVerification;
use App\Support\ProductCertificate;
use App\Support\ProvenanceDossier;
use App\Support\WorkshopRegister;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the data layer behind the public artisan profile.
 *
 * The two supplied designs display around thirty figures about a named person —
 * products sold, happy customers, countries reached, a response rate, a review
 * average, a trust score out of a hundred. Perhaps a third of them can be
 * measured from this database. The rest cannot, because the platform has no
 * orders, no customers and no message-response tracking, and never has.
 *
 * So the property under test here is not "the numbers are right". It is that a
 * figure the platform does not measure is reported as unmeasured, and never as
 * zero, and never as a plausible default. Those two failures look identical on
 * a rendered page and are completely different in kind: "0 products sold" is a
 * statement about this artisan's business, and it is one we are in no position
 * to make. The distinction has cost this project real bugs before, which is why
 * it is asserted rather than trusted.
 *
 * The trust score gets its own scrutiny. A number captioned out of a hundred
 * beside somebody's face is a public claim about their character, so either it
 * is unknown, or every point in it is attributable to a named input and the
 * inputs add up to the total. There is no third option where the score is
 * "roughly right".
 */
class ArtisanProfileTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** A shop with nothing on it but a name — the state most SIARC imports are in. */
    private function bare(): Business
    {
        return $this->makeBusiness();
    }

    /** Every leaf of a returned tree that looks like a statistic. */
    private function statsIn(array $tree): array
    {
        $found = [];

        $walk = function (array $node) use (&$walk, &$found) {
            if (array_key_exists('known', $node) && array_key_exists('value', $node)) {
                $found[] = $node;
            }
            foreach ($node as $child) {
                if (is_array($child)) {
                    $walk($child);
                }
            }
        };
        $walk($tree);

        return $found;
    }

    /** Everything the class can be asked for, for one business. */
    private function everything(Business $business): array
    {
        return [
            'identity'     => ArtisanProfile::identity($business),
            'certificates' => ArtisanProfile::certificates($business),
            'products'     => ArtisanProfile::products($business, 12),
            'reviews'      => ArtisanProfile::reviews($business),
            'awards'       => ArtisanProfile::awards($business),
            'statistics'   => ArtisanProfile::statistics($business),
            'trust'        => ArtisanProfile::trustScore($business),
            'workshop'     => ArtisanProfile::workshop($business) ?? [],
        ];
    }

    /* ────────────────────────────── Reviews ────────────────────────────── */

    public function test_no_reviews_means_no_rating_at_all_rather_than_a_default(): void
    {
        $reviews = ArtisanProfile::reviews($this->bare());

        $this->assertSame(0, $reviews['count']);
        $this->assertFalse($reviews['mean']['known'], 'A mean rating was reported over zero reviews.');
        $this->assertNull($reviews['mean']['value']);
        $this->assertNotSame('', $reviews['mean']['basis']);

        // The distribution is five real buckets, all genuinely empty. What must
        // not appear is a shape implying rows exist.
        $this->assertSame([5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0], $reviews['distribution']);
        $this->assertFalse($reviews['has_reviews']);
    }

    public function test_a_mean_is_reported_to_one_decimal_once_reviews_exist(): void
    {
        $business = $this->bare();

        foreach ([5, 4, 4, 2] as $rating) {
            DB::table('business_reviews')->insert([
                'reviewer_id' => $this->makeUser()->id,
                'business_id' => $business->id,
                'rating'      => $rating,
                'body'        => 'x',
                'status'      => 'published',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // A hidden review is not a review the public may count.
        DB::table('business_reviews')->insert([
            'reviewer_id' => $this->makeUser()->id,
            'business_id' => $business->id,
            'rating'      => 1,
            'body'        => 'x',
            'status'      => 'hidden',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $reviews = ArtisanProfile::reviews($business);

        $this->assertSame(4, $reviews['count']);
        $this->assertTrue($reviews['mean']['known']);
        $this->assertSame(3.8, $reviews['mean']['value']);
        $this->assertSame([5 => 1, 4 => 2, 3 => 0, 2 => 1, 1 => 0], $reviews['distribution']);
    }

    /* ─────────────────────────────── Awards ────────────────────────────── */

    public function test_no_awards_means_an_empty_list_naming_no_organisation(): void
    {
        $awards = ArtisanProfile::awards($this->bare());

        $this->assertSame([], $awards['items']);
        $this->assertSame(0, $awards['count']);

        // The designs show SIARC, UNESCO and ministry honours in this block.
        // They are conferred by bodies we hold no register of; printing one
        // would invent a national distinction for a real person.
        $serialised = json_encode($awards);
        foreach (['SIARC', 'UNESCO', 'Ministry', 'Ministère', 'National', 'Nationale', 'Excellence'] as $forbidden) {
            $this->assertStringNotContainsStringIgnoringCase($forbidden, $serialised);
        }
    }

    public function test_an_award_is_reported_exactly_as_the_row_holds_it(): void
    {
        $business = $this->bare();
        DB::table('business_awards')->insert([
            'business_id' => $business->id,
            'title_fr'    => 'Prix du salon régional',
            'title_en'    => 'Regional fair prize',
            'issuer'      => 'Chambre des métiers de Bafoussam',
            'year'        => 2024,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $awards = ArtisanProfile::awards($business, 'en');

        $this->assertSame(1, $awards['count']);
        $this->assertSame('Regional fair prize', $awards['items'][0]['title']);
        $this->assertSame('Chambre des métiers de Bafoussam', $awards['items'][0]['issuer']);
        $this->assertSame(2024, $awards['items'][0]['year']);
    }

    /* ───────────────────────────── Statistics ──────────────────────────── */

    public function test_the_untracked_counters_say_so_and_say_why(): void
    {
        $stats = ArtisanProfile::statistics($this->bare());

        foreach (['products_sold', 'happy_customers', 'response_rate', 'last_active'] as $key) {
            $this->assertArrayHasKey($key, $stats, "{$key} was dropped rather than reported as untracked.");
            $this->assertFalse($stats[$key]['known'], "{$key} claimed to be known.");
            $this->assertNull($stats[$key]['value'], "{$key} carried a value while unknown.");
            $this->assertNotSame('', trim($stats[$key]['basis']), "{$key} gave no reason for being unknown.");
        }
    }

    public function test_the_countable_counters_are_counted(): void
    {
        $business = $this->bare();
        $product  = $this->makeProduct($business);
        $this->makeProduct($business, ['status' => 'draft']);

        ProvenanceDossier::record($product, 'exhibition', [
            'title'        => 'Salon régional',
            'organisation' => 'Chambre des métiers',
            'country'      => 'CM',
            'started_on'   => '2025-03-01',
        ]);

        $stats = ArtisanProfile::statistics($business);

        $this->assertTrue($stats['products_created']['known']);
        $this->assertSame(2, $stats['products_created']['value']);
        $this->assertTrue($stats['products_published']['known']);
        $this->assertSame(1, $stats['products_published']['value']);
        $this->assertTrue($stats['exhibitions']['known']);
        $this->assertSame(1, $stats['exhibitions']['value']);
    }

    /* ───────────────────────── Years of experience ─────────────────────── */

    public function test_years_of_experience_is_absent_when_the_founding_year_is_not_known(): void
    {
        $identity = ArtisanProfile::identity($this->bare());

        $this->assertFalse($identity['years_experience']['known']);
        $this->assertNull($identity['years_experience']['value']);

        // The desktop design prints "18+ Years Experience" as a fixed string.
        $this->assertStringNotContainsString('18', json_encode($identity['years_experience']));
    }

    public function test_years_of_experience_is_the_arithmetic_when_the_founding_year_is_known(): void
    {
        $business = $this->makeBusiness(null, ['year_established' => now()->year - 12]);

        $identity = ArtisanProfile::identity($business);

        $this->assertTrue($identity['years_experience']['known']);
        $this->assertSame(12, $identity['years_experience']['value']);
    }

    /* ──────────────────────────── Certificates ─────────────────────────── */

    public function test_certificates_report_only_what_the_registers_actually_issued(): void
    {
        $business = $this->bare();

        $blocks = ArtisanProfile::certificates($business);

        foreach (['avc', 'coa', 'wvc', 'otc', 'eac'] as $type) {
            $this->assertArrayHasKey($type, $blocks);
            $this->assertFalse($blocks[$type]['issued'], "{$type} claimed an issued certificate for a bare shop.");
            $this->assertSame([], $blocks[$type]['items']);
        }
    }

    public function test_an_issued_certificate_is_reported_with_the_register_s_own_number(): void
    {
        $business = $this->makeBusiness(null, ['verification_tier' => 'verified', 'id_verified_at' => now()]);
        $product  = $this->makeProduct($business);

        $coa = ProductCertificate::issue($product->fresh());
        $avc = ArtisanVerification::forBusiness($business->fresh());

        $blocks = ArtisanProfile::certificates($business->fresh());

        $this->assertTrue($blocks['coa']['issued']);
        $this->assertSame(
            $coa->certificate_no,
            $blocks['coa']['items'][0]['number'],
            'The reported COA number is not the one in product_certificates.'
        );
        $this->assertSame(
            DB::table('product_certificates')->where('id', $coa->id)->value('certificate_no'),
            $blocks['coa']['items'][0]['number']
        );

        if ($avc) {
            $this->assertTrue($blocks['avc']['issued']);
            $this->assertSame($avc->certificate_no, $blocks['avc']['items'][0]['number']);
        }
    }

    /* ───────────────────────────── Trust score ─────────────────────────── */

    public function test_the_trust_score_is_unknown_when_nothing_has_been_established(): void
    {
        $score = ArtisanProfile::trustScore($this->bare());

        $this->assertFalse($score['known']);
        $this->assertNull($score['value']);
        $this->assertNotSame('', trim($score['basis']));
    }

    public function test_a_reported_trust_score_is_the_sum_of_its_named_inputs(): void
    {
        $business = $this->makeBusiness(null, [
            'verification_tier' => 'certified',
            'id_verified_at'    => now(),
            'address_fr'        => 'Quartier Ndogbong, Douala',
        ]);
        $this->makeProduct($business);

        $score = ArtisanProfile::trustScore($business->fresh());

        $this->assertTrue($score['known'], 'A verified shop produced no score at all.');
        $this->assertNotEmpty($score['breakdown']);

        $sum = 0;
        foreach ($score['breakdown'] as $key => $input) {
            $this->assertArrayHasKey('points', $input, "Input {$key} carries no points.");
            $this->assertArrayHasKey('max', $input, "Input {$key} carries no maximum.");
            $this->assertArrayHasKey('basis', $input, "Input {$key} cannot say why it scored.");
            $this->assertNotSame('', trim($input['basis']));
            $this->assertLessThanOrEqual($input['max'], $input['points']);
            $sum += $input['points'];
        }

        $this->assertSame($sum, $score['value'], 'The score is not the sum of its breakdown.');
        $this->assertSame(
            array_sum(array_column($score['breakdown'], 'max')),
            $score['max'],
            'The maximum is not the sum of the assessable inputs.'
        );
    }

    /* ────────────────────────────── Workshop ───────────────────────────── */

    public function test_a_shop_with_no_workshop_gets_null_rather_than_a_blank_workshop(): void
    {
        $this->assertNull(ArtisanProfile::workshop($this->bare()));
    }

    public function test_exact_gps_never_leaves_this_class(): void
    {
        $business = $this->makeBusiness(null, [
            'gps_lat'    => 4.0510999,
            'gps_lng'    => 9.7678700,
            'address_fr' => 'Douala',
        ]);
        $product = $this->makeProduct($business);
        $product->update(['gps_lat' => 4.0510999, 'gps_lng' => 9.7678700]);

        WorkshopRegister::openFor($business->fresh(), [
            'name'    => 'Atelier Ndogbong',
            'gps_lat' => 4.0510999,
            'gps_lng' => 9.7678700,
            'village' => 'Ndogbong',
        ]);

        $serialised = json_encode($this->everything($business->fresh()));

        // The passport withholds these for the artisan's physical safety; a
        // profile page is a more public surface than the passport, not a less
        // public one.
        $this->assertStringNotContainsString('4.0510999', $serialised);
        $this->assertStringNotContainsString('9.76787', $serialised);
        $this->assertStringNotContainsString('gps_lat', $serialised);
        $this->assertStringNotContainsString('gps_lng', $serialised);
    }

    /* ─────────────────────────── Nothing invented ──────────────────────── */

    public function test_a_shop_with_no_data_yields_no_non_zero_figure_anywhere(): void
    {
        $business = $this->bare();

        foreach ($this->statsIn($this->everything($business)) as $stat) {
            if (! is_int($stat['value']) && ! is_float($stat['value'])) {
                continue;
            }

            $this->assertSame(
                0,
                (int) $stat['value'],
                "A shop with no data produced the figure {$stat['value']} for basis \"{$stat['basis']}\"."
            );
        }
    }

    public function test_products_carry_no_rating_because_reviews_are_per_business(): void
    {
        $business = $this->bare();
        $this->makeProduct($business, ['price_amount' => 25000, 'price_currency' => 'XAF']);

        $products = ArtisanProfile::products($business, 12);

        $this->assertCount(1, $products['items']);
        $this->assertArrayNotHasKey('rating', $products['items'][0]);
        $this->assertArrayNotHasKey('reviews_count', $products['items'][0]);
        $this->assertSame(25000.0, $products['items'][0]['price']['amount']);
        $this->assertSame('XAF', $products['items'][0]['price']['currency']);
        $this->assertFalse($products['items'][0]['has_authenticity_certificate']);
        $this->assertNotSame('', trim($products['ratings_basis']));
    }
}
