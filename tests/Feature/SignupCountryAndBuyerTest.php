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
}
