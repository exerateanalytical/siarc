<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Taxonomy\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Country at signup, and the buyer account type that the wizard never offered.
 *
 * The wizard hardcoded its own four-seller list while the fast form read the
 * shared one, so the same question had two answers depending on which door you
 * used. These tests pin the shared list, the country round trip, and the guard
 * that stops a buyer creating a shop they could not administer.
 */
class SignupCountryAndBuyerTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_users_can_carry_a_country(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'country_id'));

        $cm = Country::where('code', 'CM')->firstOrFail();
        $user = User::factory()->create(['country_id' => $cm->id]);

        $this->assertSame($cm->id, $user->fresh()->country->id);
    }

    public function test_both_signup_doors_offer_every_account_type(): void
    {
        $wizard = $this->get('/creer-mon-compte')->assertOk()->getContent();
        $fast   = $this->get('/inscription-rapide')->assertOk()->getContent();

        foreach (\App\Support\AccountTypes::keys() as $key) {
            $this->assertStringContainsString(
                'value="' . $key . '"',
                $wizard,
                "The wizard does not offer the '{$key}' account type."
            );
            $this->assertStringContainsString(
                'value="' . $key . '"',
                $fast,
                "The fast form does not offer the '{$key}' account type."
            );
        }
    }

    public function test_the_wizard_offers_no_type_the_platform_does_not_know(): void
    {
        // Guards the reverse drift: a card left behind after a type is removed
        // would post a value the handler rejects.
        $wizard = $this->get('/creer-mon-compte')->assertOk()->getContent();

        preg_match_all('/name="account_type" value="([a-z_]+)"/', $wizard, $m);

        $this->assertNotEmpty($m[1], 'No account_type radios found — the markup changed.');
        foreach (array_unique($m[1]) as $offered) {
            $this->assertContains($offered, \App\Support\AccountTypes::keys(),
                "The wizard offers '{$offered}', which AccountTypes does not define.");
        }
    }

    public function test_signing_up_as_a_buyer_creates_no_business(): void
    {
        $response = $this->post('/creer-mon-compte', [
            'first_name'            => 'Awa',
            'last_name'             => 'Traore',
            'email'                 => 'awa.buyer@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'buyer',
        ]);

        $response->assertRedirect();

        $user = \App\Modules\Auth\Models\User::where('email', 'awa.buyer@example.test')->firstOrFail();

        $this->assertSame('buyer', $user->account_type);
        $this->assertSame(0, \App\Modules\Businesses\Models\Business::where('user_id', $user->id)->count());
    }

    public function test_every_african_country_can_sell_and_no_other_can(): void
    {
        $sellers = Country::forSellers()->get();

        $this->assertSame(54, $sellers->count(), 'Expected the 54 African countries to be seller-enabled.');
        $this->assertTrue($sellers->every(fn ($c) => $c->continent === 'AF'));

        // The reverse: nothing outside Africa slipped in.
        $this->assertSame(
            0,
            Country::where('seller_enabled', true)->where('continent', '!=', 'AF')->count()
        );
    }

    public function test_a_buyer_may_come_from_anywhere(): void
    {
        $all = Country::active()->count();

        $this->assertGreaterThan(200, $all, 'The world list looks truncated.');

        foreach (['JP', 'BR', 'DE', 'IN'] as $code) {
            $this->assertNotNull(
                Country::where('code', $code)->where('is_active', true)->first(),
                "{$code} should be offered to buyers."
            );
        }
    }

    public function test_signing_up_stores_the_country(): void
    {
        $japan = Country::where('code', 'JP')->firstOrFail();

        $this->post('/creer-mon-compte', [
            'first_name'            => 'Kenji',
            'last_name'             => 'Sato',
            'email'                 => 'kenji.buyer@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'buyer',
            'country_id'            => $japan->id,
        ])->assertRedirect();

        $user = User::where('email', 'kenji.buyer@example.test')->firstOrFail();

        $this->assertSame($japan->id, $user->country_id);
    }

    public function test_a_seller_cannot_sign_up_from_outside_africa(): void
    {
        $japan = Country::where('code', 'JP')->firstOrFail();

        $response = $this->from('/creer-mon-compte')->post('/creer-mon-compte', [
            'first_name'            => 'Kenji',
            'last_name'             => 'Sato',
            'email'                 => 'kenji.seller@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'artisan',
            'country_id'            => $japan->id,
        ]);

        $response->assertSessionHasErrors('country_id');
        $this->assertNull(User::where('email', 'kenji.seller@example.test')->first());
    }

    public function test_a_seller_can_sign_up_from_an_african_country_with_no_regions(): void
    {
        // Kenya has no regions seeded — only the three launch countries do. A
        // seller there must still be able to create an account; the region is
        // asked for later, on the business form, and is already nullable.
        $kenya = Country::where('code', 'KE')->firstOrFail();

        $this->assertSame(0, $kenya->regions()->count(), 'Kenya was expected to have no seeded regions.');

        $this->post('/creer-mon-compte', [
            'first_name'            => 'Amina',
            'last_name'             => 'Otieno',
            'email'                 => 'amina.seller@example.test',
            'password'              => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
            'account_type'          => 'artisan',
            'country_id'            => $kenya->id,
        ])->assertRedirect();

        $user = User::where('email', 'amina.seller@example.test')->firstOrFail();

        $this->assertSame($kenya->id, $user->country_id);
    }

    public function test_the_wizard_offers_the_world_and_marks_which_countries_may_sell(): void
    {
        $wizard = $this->get('/creer-mon-compte')->assertOk()->getContent();

        $japan = Country::where('code', 'JP')->firstOrFail();
        $kenya = Country::where('code', 'KE')->firstOrFail();

        $this->assertStringContainsString('value="' . $japan->id . '"', $wizard);
        $this->assertStringContainsString('value="' . $kenya->id . '"', $wizard);

        // The seller filter is driven by a data attribute, not a second list.
        $this->assertMatchesRegularExpression(
            '/value="' . $japan->id . '"[^>]*data-seller="0"/',
            $wizard,
            'Japan should be marked as not seller-enabled.'
        );
        $this->assertMatchesRegularExpression(
            '/value="' . $kenya->id . '"[^>]*data-seller="1"/',
            $wizard,
            'Kenya should be marked as seller-enabled.'
        );
    }
}
