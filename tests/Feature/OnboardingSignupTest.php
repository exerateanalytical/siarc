<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OnboardingSignupTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_wizard_creates_a_real_account_and_logs_the_user_in(): void
    {
        $response = $this->post('/creer-mon-compte', [
            'first_name'            => 'Aristide',
            'last_name'             => 'Ndop',
            'email'                 => 'aristide.ndop@example.cm',
            'phone'                 => '6 90 12 34 56',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ]);

        $response->assertRedirect('/creer-mon-compte?submitted=1');

        $user = DB::table('users')->where('email', 'aristide.ndop@example.cm')->first();
        $this->assertNotNull($user);
        $this->assertSame('Aristide Ndop', $user->name);
        $this->assertTrue(Hash::check('MotDePasse#2026', $user->password));

        // Logged in as a business owner
        $response->assertSessionHas('siac_user');
        $this->assertSame($user->id, session('siac_user')['id']);
        $this->assertSame('business_owner', session('siac_user')['role']);
    }

    public function test_duplicate_email_is_rejected_with_an_error(): void
    {
        DB::table('users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Existing',
            'email' => 'taken@example.cm', 'password' => Hash::make('x'),
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->post('/creer-mon-compte', [
            'first_name'            => 'A',
            'last_name'             => 'B',
            'email'                 => 'taken@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, DB::table('users')->where('email', 'taken@example.cm')->count());
    }

    public function test_the_success_screen_shows_after_submission(): void
    {
        $this->post('/creer-mon-compte', [
            'first_name'            => 'Suzanne',
            'last_name'             => 'Mbarga',
            'email'                 => 'suzanne@example.cm',
            'password'              => 'MotDePasse#2026',
            'password_confirmation' => 'MotDePasse#2026',
        ]);

        $this->get('/creer-mon-compte?submitted=1')
            ->assertOk()
            ->assertSee('goToStep(11)', false); // boot script jumps straight to the success screen
    }

    public function test_the_old_signup_routes_redirect_to_the_wizard(): void
    {
        $this->get('/inscription')->assertRedirect();
        $this->assertStringContainsString('/creer-mon-compte', $this->get('/inscription')->headers->get('Location'));

        $this->get('/register')->assertRedirect();
        $this->assertStringContainsString('/creer-mon-compte', $this->get('/register')->headers->get('Location'));
    }
}
