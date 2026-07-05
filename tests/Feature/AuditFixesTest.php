<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Regression guards for the platform-audit fixes — these routes/flows previously
 * 500'd on SQLite (the test backend) due to MySQL-only SQL, and were never covered.
 */
class AuditFixesTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_ministry_dashboard_renders_on_sqlite(): void
    {
        // Guards MinistryWebController DATE_FORMAT -> PHP/Carbon grouping.
        $this->makeBusiness(null, ['status' => 'published']);
        $u = $this->makeUser();
        $session = ['siac_user' => ['id' => $u->id, 'name' => 'M', 'email' => $u->email, 'role' => 'ministry', 'is_admin' => false]];
        $this->withSession($session)->get('/tableau-de-bord/ministere')->assertOk();
    }

    public function test_admin_api_consumers_renders_on_sqlite(): void
    {
        // Guards AdminWebController FIELD() -> CASE ordering.
        $u = $this->makeUser();
        $session = ['siac_user' => ['id' => $u->id, 'name' => 'A', 'email' => $u->email, 'role' => 'super_admin', 'is_admin' => true]];
        $this->withSession($session)->get('/tableau-de-bord/admin/api-consommateurs')->assertOk();
    }

    public function test_web_login_establishes_a_session_on_sqlite(): void
    {
        // Guards establishSiacSession FIELD() -> CASE (the web login path was untested).
        $this->makeUser(['email' => 'weblogin@test.cm', 'password' => Hash::make('Password1!')]);

        $resp = $this->post('/login', ['email' => 'weblogin@test.cm', 'password' => 'Password1!']);

        $resp->assertRedirect();
        $resp->assertSessionHas('siac_user');
    }

    public function test_login_next_param_cannot_open_redirect_off_site(): void
    {
        $this->makeUser(['email' => 'redir@test.cm', 'password' => Hash::make('Password1!')]);

        $resp = $this->post('/login?next=' . urlencode('//evil.example/phish'), [
            'email' => 'redir@test.cm', 'password' => 'Password1!', 'next' => '//evil.example/phish',
        ]);

        $resp->assertRedirect();
        $this->assertStringNotContainsString('evil.example', (string) $resp->headers->get('Location'));
    }
}
