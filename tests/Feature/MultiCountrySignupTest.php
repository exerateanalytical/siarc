<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Taxonomy\Models\Country;
use App\Modules\Taxonomy\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Artisans outside Cameroon can register and be found.
 *
 * The platform launched single-country: `regions` held Cameroon's ten regions
 * and nothing recorded which country a region belonged to. An Ivorian artisan
 * had to claim a Cameroonian region, and the directory card fell back to the
 * literal string 'Cameroun' whenever a business had no city or region.
 *
 * These tests pin the three things that would silently reintroduce that: the
 * country column tracking the region, the region list being scoped per country,
 * and the directory filtering on country rather than assuming one.
 */
class MultiCountrySignupTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function memberSession(User $user): array
    {
        return ['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => 'business_owner',
            'is_admin' => false,
        ]];
    }

    private function makeOwner(): User
    {
        return User::create([
            'id'                => (string) Str::uuid(),
            'name'              => 'Kouadio Yao',
            'email'             => 'kouadio' . Str::random(6) . '@example.test',
            'password'          => Hash::make('secret-password-123'),
            'account_type'      => 'artisan',
            'is_email_verified' => true,
        ]);
    }

    public function test_the_three_launch_countries_are_present_and_active(): void
    {
        $codes = Country::active()->pluck('code')->all();

        $this->assertContains('CM', $codes, 'Cameroon must remain available.');
        $this->assertContains('CI', $codes);
        $this->assertContains('DZ', $codes);
    }

    public function test_each_country_carries_its_own_dial_code_and_currency(): void
    {
        $expected = [
            'CM' => ['237', 'XAF'],
            'CI' => ['225', 'XOF'],
            'DZ' => ['213', 'DZD'],
        ];

        foreach ($expected as $code => [$dial, $currency]) {
            $country = Country::where('code', $code)->firstOrFail();
            $this->assertSame($dial, $country->dial_code, "Wrong dial code for {$code}.");
            $this->assertSame($currency, $country->currency_code, "Wrong currency for {$code}.");
        }
    }

    public function test_every_region_belongs_to_a_country(): void
    {
        // A region with no country cannot be shown in any country's dropdown,
        // so it would be silently unreachable at signup.
        $this->assertSame(0, Region::whereNull('country_id')->count());
    }

    public function test_regions_are_the_real_administrative_divisions(): void
    {
        $counts = [
            'CM' => 10,   // regions
            'CI' => 14,   // districts, including the two autonomous ones
            'DZ' => 58,   // wilayas, including the ten created in 2019
        ];

        foreach ($counts as $code => $n) {
            $country = Country::where('code', $code)->firstOrFail();
            $this->assertSame($n, $country->regions()->count(), "Wrong region count for {$code}.");
        }
    }

    public function test_the_region_endpoint_returns_only_the_asked_country(): void
    {
        $ci = Country::where('code', 'CI')->firstOrFail();
        $user = $this->makeOwner();

        $res = $this->withSession($this->memberSession($user))
            ->getJson('/api-interne/regions/' . $ci->id);

        $res->assertOk();
        $names = collect($res->json())->pluck('name_fr');

        $this->assertContains('Abidjan', $names->all());
        // A Cameroonian region leaking in is the exact bug this guards.
        $this->assertNotContains('Littoral', $names->all());
    }

    public function test_an_ivorian_artisan_gets_an_ivorian_country_on_their_business(): void
    {
        $user = $this->makeOwner();
        $ci = Country::where('code', 'CI')->firstOrFail();
        $abidjan = Region::where('country_id', $ci->id)->where('code', 'AB')->firstOrFail();

        $this->withSession($this->memberSession($user))->post('/tableau-de-bord/entreprise/creer', [
            'industry_id'   => $this->anIndustryId(),
            'country_id'    => $ci->id,
            'region_id'     => $abidjan->id,
            'business_name' => 'Atelier Yao',
        ]);

        $business = Business::where('user_id', $user->id)->firstOrFail();

        $this->assertSame($ci->id, $business->country_id);
        $this->assertSame($abidjan->id, $business->region_id);
    }

    public function test_country_is_derived_from_the_region_when_the_form_omits_it(): void
    {
        // The country column is denormalised, so it must never be able to drift
        // from the region: a business in an Algerian wilaya listed under
        // Cameroon would appear under the wrong flag in the directory.
        $user = $this->makeOwner();
        $dz = Country::where('code', 'DZ')->firstOrFail();
        $alger = Region::where('country_id', $dz->id)->where('code', '16')->firstOrFail();

        $this->withSession($this->memberSession($user))->post('/tableau-de-bord/entreprise/creer', [
            'industry_id'   => $this->anIndustryId(),
            'region_id'     => $alger->id,      // no country_id sent
            'business_name' => 'Zellige Alger',
        ]);

        $business = Business::where('user_id', $user->id)->firstOrFail();

        $this->assertSame($dz->id, $business->country_id);
    }

    public function test_the_directory_can_be_filtered_to_one_country(): void
    {
        $ci = Country::where('code', 'CI')->firstOrFail();
        $cm = Country::where('code', 'CM')->firstOrFail();

        $ivorian = $this->publishedBusinessIn($ci, 'Atelier Abidjan');
        $cameroonian = $this->publishedBusinessIn($cm, 'Atelier Douala');

        $this->get('/galerie/entreprises?pays=CI')
            ->assertOk()
            ->assertSee($ivorian->name_fr)
            ->assertDontSee($cameroonian->name_fr);

        $this->get('/galerie/entreprises?pays=CM')
            ->assertOk()
            ->assertSee($cameroonian->name_fr)
            ->assertDontSee($ivorian->name_fr);
    }

    public function test_an_unfiltered_directory_still_shows_every_country(): void
    {
        $ci = Country::where('code', 'CI')->firstOrFail();
        $cm = Country::where('code', 'CM')->firstOrFail();

        $a = $this->publishedBusinessIn($ci, 'Atelier Abidjan');
        $b = $this->publishedBusinessIn($cm, 'Atelier Douala');

        $this->get('/galerie/entreprises')
            ->assertOk()
            ->assertSee($a->name_fr)
            ->assertSee($b->name_fr);
    }

    /** A published business in the given country, with no city or region set. */
    private function publishedBusinessIn(Country $country, string $name): Business
    {
        $user = $this->makeOwner();

        return Business::create([
            'uuid'        => (string) Str::uuid(),
            'slug'        => Str::slug($name) . '-' . Str::random(5),
            'user_id'     => $user->id,
            'industry_id' => $this->anIndustryId(),
            'country_id'  => $country->id,
            'name_fr'     => $name,
            'status'      => 'published',
            'vendor_type' => 'artisan',
        ]);
    }

    private function anIndustryId(): int
    {
        return (int) \App\Modules\Taxonomy\Models\Industry::where('is_active', true)
            ->orderBy('id')->value('id');
    }
}
