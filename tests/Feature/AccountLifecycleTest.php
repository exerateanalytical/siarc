<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The account lifecycle: creation that survives a dead mail relay and a
 * logged-in session, self-service deletion that is honest about what dies and
 * what the register keeps, and admin identity edits that always leave a trail.
 */
class AccountLifecycleTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function adminSession(?User $admin = null): array
    {
        $admin = $admin ?? $this->makeUser();

        return ['siac_user' => [
            'id'       => $admin->id,
            'name'     => $admin->name,
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]];
    }

    private function memberSession(User $user, string $role = 'business_owner'): array
    {
        return ['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => $role,
            'is_admin' => false,
        ]];
    }

    private function seedRole(string $name): int
    {
        $existing = DB::table('roles')->where('name', $name)->where('guard_name', 'sanctum')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => $name, 'guard_name' => 'sanctum',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function grantRole(User $user, int $roleId): void
    {
        DB::table('model_has_roles')->insert([
            'role_id'    => $roleId,
            'model_type' => 'App\Modules\Auth\Models\User',
            'model_id'   => $user->id,
        ]);
    }

    /** Point the default mailer at a port nobody listens on: every send throws. */
    private function useDeadMailer(): void
    {
        config([
            'mail.default'              => 'smtp',
            'mail.mailers.smtp.host'    => '127.0.0.1',
            'mail.mailers.smtp.port'    => 59925,
            'mail.mailers.smtp.timeout' => 1,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Job 1 — signup must never lose the account
    // ─────────────────────────────────────────────────────────────────────

    public function test_wizard_signup_survives_a_throwing_mailer(): void
    {
        $this->useDeadMailer();

        $res = $this->post('/creer-mon-compte', [
            'first_name'            => 'Relais',
            'last_name'             => 'Mort',
            'email'                 => 'relais.mort@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ]);

        // The account row is the record; the email is a notification about it.
        // The redirect carries mail=failed so the success screen tells the truth
        // ("account created, the code did not go out") instead of claiming a
        // code was just sent.
        $res->assertRedirect('/creer-mon-compte?submitted=1&mail=failed');
        $this->assertNotNull(DB::table('users')->where('email', 'relais.mort@example.cm')->first());
        $res->assertSessionHas('siac_user');

        // The OTP row was still written: a later "resend code" can verify it.
        $this->assertSame(1, DB::table('otp_verifications')->where('identifier', 'relais.mort@example.cm')->count());

        // The confirmation screen is honest and offers the resend path.
        $screen = $this->get('/creer-mon-compte?submitted=1&mail=failed');
        $screen->assertOk()
            ->assertSee('Renvoyer le code')
            ->assertSee(route('email.verify', ['lang' => 'fr']), false);
    }

    public function test_wizard_signup_with_a_working_mailer_says_the_code_was_sent(): void
    {
        // Default test mailer is 'array': the send succeeds.
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Poste',
            'last_name'             => 'Vivant',
            'email'                 => 'poste.vivant@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertRedirect('/creer-mon-compte?submitted=1');

        $this->get('/creer-mon-compte?submitted=1')
            ->assertOk()
            ->assertSee('Pensez à vérifier vos spams')
            ->assertDontSee('Renvoyer le code');
    }

    public function test_quick_register_survives_a_throwing_mailer(): void
    {
        $this->useDeadMailer();

        $res = $this->post('/inscription-rapide', [
            'email'        => 'rapide@example.cm',
            'password'     => 'MotDePasse#2026',
            'account_type' => 'artisan',
        ]);

        $res->assertRedirect('/tableau-de-bord');
        $this->assertNotNull(DB::table('users')->where('email', 'rapide@example.cm')->first());
    }

    /**
     * THE reproduced bug: an admin following « Ajouter un utilisateur » used to
     * be shown the wizard's success screen with no POST ever sent — the account
     * never existed and was therefore "invisible" in the users list.
     */
    public function test_admin_walking_the_wizard_really_creates_the_account(): void
    {
        $admin = $this->makeUser();

        $res = $this->withSession($this->adminSession($admin))->post('/creer-mon-compte', [
            'first_name'            => 'Membre',
            'last_name'             => 'Ajouté',
            'email'                 => 'ajoute.par.admin@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ]);

        // The row exists, the admin keeps their own session, and they land back
        // on the users list where the new account is visible.
        $res->assertRedirect(route('admin.users'));
        $created = DB::table('users')->where('email', 'ajoute.par.admin@example.cm')->first();
        $this->assertNotNull($created);
        $this->assertSame($admin->id, session('siac_user')['id']);

        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created_by_admin']);

        $list = $this->withSession($this->adminSession($admin))->get('/tableau-de-bord/admin/utilisateurs');
        $list->assertOk()->assertSee('ajoute.par.admin@example.cm');
    }

    public function test_logged_in_member_is_refused_honestly_not_shown_a_fake_success(): void
    {
        $member = $this->makeUser();

        $res = $this->withSession($this->memberSession($member))->post('/creer-mon-compte', [
            'first_name'            => 'Deuxième',
            'last_name'             => 'Compte',
            'email'                 => 'deuxieme@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ]);

        $res->assertSessionHasErrors('email');
        $this->assertNull(DB::table('users')->where('email', 'deuxieme@example.cm')->first());
    }

    public function test_resubmitting_your_own_signup_is_idempotent(): void
    {
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Aline',
            'last_name'             => 'Owona',
            'email'                 => 'aline.owona@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertRedirect('/creer-mon-compte?submitted=1');

        // Back button + resubmit: no duplicate, no error, same confirmation.
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Aline',
            'last_name'             => 'Owona',
            'email'                 => 'aline.owona@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertRedirect('/creer-mon-compte?submitted=1');

        $this->assertSame(1, DB::table('users')->where('email', 'aline.owona@example.cm')->count());
    }

    // ─────────────────────────────────────────────────────────────────────
    // Job 2 — self-service account deletion
    // ─────────────────────────────────────────────────────────────────────

    public function test_deletion_requires_the_correct_password(): void
    {
        $user = $this->makeUser(['password' => 'MotDePasse#2026']);

        $res = $this->withSession($this->memberSession($user))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'wrong-password',
            'delete_confirm'  => 'SUPPRIMER',
        ]);

        $res->assertSessionHasErrors('delete_password');
        $this->assertNull(User::find($user->id)->deleted_at);
    }

    public function test_deletion_requires_the_confirmation_phrase(): void
    {
        $user = $this->makeUser(['password' => 'MotDePasse#2026']);

        $res = $this->withSession($this->memberSession($user))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'MotDePasse#2026',
            'delete_confirm'  => 'oui',
        ]);

        $res->assertSessionHasErrors('delete_confirm');
        $this->assertNull(User::find($user->id)->deleted_at);
    }

    public function test_deletion_soft_deletes_anonymises_and_locks_out(): void
    {
        $user = $this->makeUser(['password' => 'MotDePasse#2026', 'email' => 'partant@example.cm', 'phone' => '+237690000001']);
        DB::table('user_passkeys')->insert([
            'user_id' => $user->id, 'name' => 'PK', 'credential_id' => 'cred-x',
            'public_key' => 'pk', 'sign_count' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $res = $this->withSession($this->memberSession($user))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'MotDePasse#2026',
            'delete_confirm'  => 'SUPPRIMER',
        ]);

        $res->assertRedirect('/login?lang=fr');

        $row = DB::table('users')->where('id', $user->id)->first();
        $this->assertNotNull($row->deleted_at);
        $this->assertSame('deleted', $row->status);
        // Identifiers gone — and freed for a future account.
        $this->assertSame('', $row->name);
        $this->assertNull($row->email);
        $this->assertNull($row->phone);
        $this->assertNull($row->two_factor_secret);
        $this->assertSame(0, DB::table('user_passkeys')->where('user_id', $user->id)->count());

        // The one durable record: the audit row keeps the old email for disputes.
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.self_deleted']);

        // Login now refuses with the same neutral message as a ghost account.
        $login = $this->post('/login', ['email' => 'partant@example.cm', 'password' => 'MotDePasse#2026']);
        $login->assertSessionHasErrors('email');
        $this->assertNull(session('siac_user'));

        // And the email can be used to sign up again.
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Re',
            'last_name'             => 'Venu',
            'email'                 => 'partant@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertRedirect('/creer-mon-compte?submitted=1');
    }

    public function test_deletion_sets_owned_business_to_draft_and_unclaims_siarc(): void
    {
        $user = $this->makeUser(['password' => 'MotDePasse#2026', 'name' => 'Marthe Ngo Bell', 'phone' => '+237655443322']);
        $own = $this->makeBusiness($user, ['status' => 'published']);
        $siarc = $this->makeBusiness($user, [
            'status'     => 'draft',
            'siarc_code' => 'SIARC-0042',
            'claimed_at' => now()->subDays(3),
            'name_fr'    => 'Marthe Ngo Bell',
            'phone'      => '+237655443322',
        ]);

        $this->withSession($this->memberSession($user))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'MotDePasse#2026',
            'delete_confirm'  => 'DELETE',
        ]);

        // The authored business survives as a draft: certificates reference it.
        $this->assertSame('draft', DB::table('businesses')->where('id', $own->id)->value('status'));
        $this->assertNotNull(DB::table('businesses')->where('id', $own->id)->value('user_id'));

        // The SIARC profile returns to the unclaimed pool…
        $siarcRow = DB::table('businesses')->where('id', $siarc->id)->first();
        $this->assertNull($siarcRow->claimed_at);
        $this->assertSame('draft', $siarcRow->status);

        // …and is offered again to the artisan when they come back.
        $candidates = \App\Support\SiarcClaim::candidatesFor('Marthe Ngo Bell', null);
        $this->assertTrue($candidates->contains(fn ($b) => $b->id === $siarc->id));
    }

    public function test_reclaiming_after_deletion_never_hard_deletes_the_tombstone(): void
    {
        $user = $this->makeUser(['password' => 'MotDePasse#2026', 'name' => 'Paul Essomba']);
        $siarc = $this->makeBusiness($user, [
            'status'     => 'draft',
            'siarc_code' => 'SIARC-0077',
            'claimed_at' => now()->subDay(),
            'name_fr'    => 'Atelier Essomba',
        ]);

        $this->withSession($this->memberSession($user))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'MotDePasse#2026',
            'delete_confirm'  => 'SUPPRIMER',
        ]);

        // A new account claims the profile back.
        $successor = $this->makeUser();
        $this->assertTrue(\App\Support\SiarcClaim::assign($siarc->fresh(), $successor->id));

        $this->assertSame($successor->id, $siarc->fresh()->user_id);
        // The anonymised tombstone row still exists: audit rows, reviews and
        // the register may reference it. Only import placeholders are removed.
        $this->assertNotNull(DB::table('users')->where('id', $user->id)->first());
    }

    public function test_the_last_super_admin_cannot_delete_their_own_account(): void
    {
        $roleId = $this->seedRole('super_admin');
        $boss = $this->makeUser(['password' => 'MotDePasse#2026']);
        $this->grantRole($boss, $roleId);

        $res = $this->withSession($this->adminSession($boss))->post('/tableau-de-bord/securite/supprimer-compte', [
            'delete_password' => 'MotDePasse#2026',
            'delete_confirm'  => 'SUPPRIMER',
        ]);

        $res->assertSessionHasErrors('delete_password');
        $this->assertNull(DB::table('users')->where('id', $boss->id)->value('deleted_at'));
    }

    public function test_the_security_page_shows_the_deletion_card_in_both_languages(): void
    {
        $user = $this->makeUser();

        $fr = $this->withSession($this->memberSession($user))->withCookie('lang', 'fr')->get('/tableau-de-bord/securite');
        $fr->assertOk()->assertSee('Supprimer mon compte')->assertSee('SUPPRIMER');

        $en = $this->withSession($this->memberSession($user))->withCookie('lang', 'en')->get('/tableau-de-bord/securite');
        $en->assertOk()->assertSee('Delete my account')->assertSee('Type DELETE to confirm');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Job 3 — admin edit of account details, always audited
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_edit_name_email_phone_with_an_audit_trail(): void
    {
        $admin = $this->makeUser();
        $target = $this->makeUser(['name' => 'Ancien Nom', 'email' => 'ancien@example.cm', 'phone' => '+237699000001', 'is_email_verified' => true]);

        $res = $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$target->id}/coordonnees", [
            'name'  => 'Nouveau Nom',
            'email' => 'nouveau@example.cm',
            'phone' => '+237699000002',
        ]);

        $res->assertSessionHasNoErrors();

        $row = DB::table('users')->where('id', $target->id)->first();
        $this->assertSame('Nouveau Nom', $row->name);
        $this->assertSame('nouveau@example.cm', $row->email);
        $this->assertSame('+237699000002', $row->phone);
        // An admin typing a new address does not make it verified.
        $this->assertSame(0, (int) $row->is_email_verified);

        $audit = DB::table('audit_logs')->where('action', 'user.details_changed')->first();
        $this->assertNotNull($audit);
        $this->assertSame($admin->id, $audit->user_id);
        $old = json_decode($audit->old_values, true);
        $new = json_decode($audit->new_values, true);
        $this->assertSame('ancien@example.cm', $old['email']);
        $this->assertSame('nouveau@example.cm', $new['email']);
        $this->assertSame($target->id, $new['target_user_id']);
    }

    public function test_admin_edit_rejects_a_taken_email_or_phone(): void
    {
        $admin = $this->makeUser();
        $other = $this->makeUser(['email' => 'pris@example.cm', 'phone' => '+237677000001']);
        $target = $this->makeUser();

        $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$target->id}/coordonnees", [
            'name'  => $target->name,
            'email' => 'pris@example.cm',
        ])->assertSessionHasErrors('email');

        $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$target->id}/coordonnees", [
            'name'  => $target->name,
            'email' => $target->email,
            'phone' => '+237677000001',
        ])->assertSessionHasErrors('phone');

        $this->assertSame(0, DB::table('audit_logs')->where('action', 'user.details_changed')->count());
    }

    public function test_admin_edit_cannot_touch_the_password(): void
    {
        $admin = $this->makeUser();
        $target = $this->makeUser(['password' => 'MotDePasse#2026']);

        $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$target->id}/coordonnees", [
            'name'     => 'Nom Modifié',
            'email'    => $target->email,
            'password' => 'evil-new-password',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('MotDePasse#2026', DB::table('users')->where('id', $target->id)->value('password')));
    }

    public function test_the_last_super_admin_cannot_be_demoted_or_suspended(): void
    {
        $roleId = $this->seedRole('super_admin');
        $boss = $this->makeUser();
        $this->grantRole($boss, $roleId);
        $admin = $this->makeUser(); // a second admin doing the damage

        $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$boss->id}/role", [
            'role' => 'buyer',
        ])->assertSessionHasErrors('role');

        $this->withSession($this->adminSession($admin))->post("/tableau-de-bord/admin/utilisateurs/{$boss->id}/statut", [
            'status' => 'suspended',
        ])->assertSessionHasErrors('status');

        $this->assertSame('active', DB::table('users')->where('id', $boss->id)->value('status'));
        $this->assertSame(1, DB::table('model_has_roles')->where('model_id', $boss->id)->count());
    }

    public function test_admin_user_detail_page_renders_the_edit_form(): void
    {
        $admin = $this->makeUser();
        $target = $this->makeUser();

        $res = $this->withSession($this->adminSession($admin))->get("/tableau-de-bord/admin/utilisateurs/{$target->id}");
        $res->assertOk()
            ->assertSee('Modifier les coordonnées')
            ->assertSee('name="phone"', false);
    }
}
