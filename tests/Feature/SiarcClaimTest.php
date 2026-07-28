<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Support\SiarcClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the SIARC 2026 import and the claim that hands a profile to its artisan.
 *
 * The rules that matter here are about other people's data: 510 real artisans
 * were loaded from competition records without their involvement, so nothing may
 * be published, nobody may be emailed, and no one may take a profile that is not
 * theirs.
 */
class SiarcClaimTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    /** An imported profile: a placeholder owner with no email, and a draft shop. */
    private function importedProfile(string $code, string $name, ?string $phone = null): Business
    {
        $placeholder = User::create([
            'name'     => $name,
            'email'    => null,
            'phone'    => null,   // the import keeps phone on the business only
            'password' => Hash::make(Str::random(40)),
            'status'   => 'active',
        ]);

        return Business::create([
            'uuid'        => (string) Str::uuid(),
            'slug'        => Str::slug($name) . '-' . Str::slug($code),
            'siarc_code'  => $code,
            'user_id'     => $placeholder->id,
            'name_fr'     => $name,
            'phone'       => $phone,
            'status'      => 'draft',
            'vendor_type' => 'artisan',
        ]);
    }

    private function asMember(User $u): static
    {
        return $this->withSession(['siac_user' => [
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]]);
    }

    public function test_imported_profiles_are_never_publicly_visible(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');

        $this->assertSame('draft', $b->status);
        $this->assertNull($b->claimed_at);

        // A draft must 404 rather than render, and must not list.
        $this->get('/galerie/entreprises/' . $b->slug)->assertNotFound();
        $this->get('/galerie/entreprises')->assertOk()->assertDontSee('NOURIDINE HAMADOU');
    }

    /**
     * The phone stays on the business. Putting it on the placeholder user would
     * reserve the artisan's own number against an account they cannot sign into,
     * and they would be refused at signup for "phone already taken".
     */
    public function test_the_placeholder_account_does_not_hold_the_artisans_phone(): void
    {
        $b = $this->importedProfile('AD-2', 'DAOUDA GARGA', '+237690607060');

        $this->assertSame('+237690607060', $b->phone);
        $this->assertNull(User::find($b->user_id)->phone);

        // So the real artisan can register with that very number.
        $this->assertDatabaseMissing('users', ['phone' => '+237690607060']);
    }

    public function test_an_artisan_is_offered_their_own_profile_by_name(): void
    {
        $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $this->importedProfile('AD-9', 'AUTRE PERSONNE');

        $user = User::create([
            'name' => 'Nouridine  Hamadou', 'email' => 'n@example.test',
            'password' => Hash::make('secret12'), 'status' => 'active',
        ]);

        $found = SiarcClaim::candidatesFor($user->name, null);

        $this->assertCount(1, $found, 'Accents, case and spacing must not defeat the match.');
        $this->assertSame('AD-1', $found->first()->siarc_code);
    }

    public function test_matching_is_by_full_name_or_phone_never_partial(): void
    {
        $this->importedProfile('AD-1', 'NOURIDINE HAMADOU', '+237655531122');

        // A shared surname is not enough to hand over someone's identity.
        $this->assertCount(0, SiarcClaim::candidatesFor('HAMADOU', null));
        $this->assertCount(0, SiarcClaim::candidatesFor('NOURIDINE', null));
        // The phone alone is.
        $this->assertCount(1, SiarcClaim::candidatesFor(null, '655531122'));
    }

    public function test_claiming_transfers_ownership_and_removes_the_placeholder(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $placeholderId = $b->user_id;

        $artisan = User::create([
            'name' => 'NOURIDINE HAMADOU', 'email' => 'real@example.test',
            'password' => Hash::make('secret12'), 'status' => 'active',
        ]);

        $this->asMember($artisan)
            ->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr'])
            ->assertRedirect();

        $b->refresh();
        $this->assertSame($artisan->id, $b->user_id);
        $this->assertNotNull($b->claimed_at);
        // Still a draft: publishing is the artisan's call, not the import's.
        $this->assertSame('draft', $b->status);

        $this->assertNull(User::find($placeholderId), 'The password-less placeholder must not survive the claim.');
    }

    public function test_a_stranger_cannot_claim_someone_elses_profile(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');

        $stranger = User::create([
            'name' => 'QUELQU\'UN DAUTRE', 'email' => 'other@example.test',
            'password' => Hash::make('secret12'), 'status' => 'active',
        ]);

        $this->asMember($stranger)
            ->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr'])
            ->assertForbidden();

        $this->assertNull($b->fresh()->claimed_at);
    }

    public function test_a_profile_cannot_be_claimed_twice(): void
    {
        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');

        $first = User::create(['name' => 'NOURIDINE HAMADOU', 'email' => 'a@example.test', 'password' => Hash::make('secret12'), 'status' => 'active']);
        $second = User::create(['name' => 'NOURIDINE HAMADOU', 'email' => 'b@example.test', 'password' => Hash::make('secret12'), 'status' => 'active']);

        $this->asMember($first)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr'])->assertRedirect();
        // The second person finds nothing on offer, and the route refuses them.
        $this->assertCount(0, SiarcClaim::candidatesFor($second->name, null));
        $this->asMember($second)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr'])->assertNotFound();

        $this->assertSame($first->id, $b->fresh()->user_id);
    }

    /** The whole point of the exercise: these artisans must not be contacted. */
    public function test_claiming_sends_no_mail(): void
    {
        Mail::fake();

        $b = $this->importedProfile('AD-1', 'NOURIDINE HAMADOU');
        $artisan = User::create(['name' => 'NOURIDINE HAMADOU', 'email' => 'real@example.test', 'password' => Hash::make('secret12'), 'status' => 'active']);

        $this->asMember($artisan)->post('/tableau-de-bord/revendiquer/' . $b->id, ['lang' => 'fr']);

        Mail::assertNothingSent();
        $this->assertSame(0, DB::table('jobs')->count(), 'No notification job may be queued either.');
    }
}
