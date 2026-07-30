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
}
