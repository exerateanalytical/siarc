<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The admin layout renders success, error and validation flashes once each, for
 * every admin screen. These blocks used to be hand-rolled per page — success on
 * 18 of them, $errors on 6 — so the guard here is as much against a page
 * re-adding its own copy (printing the alert twice) as against the layout
 * losing them.
 */
class AdminFlashTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function adminSession(array $extra = []): array
    {
        $admin = $this->makeUser();

        return array_merge(['siac_user' => [
            'id'       => $admin->id,
            'name'     => 'Admin Test',
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]], $extra);
    }

    public function test_success_renders_exactly_once(): void
    {
        $res = $this->withSession($this->adminSession(['success' => 'FLASH-OK-MARKER']))
            ->get('/tableau-de-bord/admin/utilisateurs');

        $res->assertOk();
        $this->assertSame(1, substr_count($res->getContent(), 'FLASH-OK-MARKER'));
        $this->assertStringContainsString('ui-alert ui-alert-ok', $res->getContent());
    }

    public function test_error_renders_exactly_once(): void
    {
        $res = $this->withSession($this->adminSession(['error' => 'FLASH-ERR-MARKER']))
            ->get('/tableau-de-bord/admin/utilisateurs');

        $res->assertOk();
        $this->assertSame(1, substr_count($res->getContent(), 'FLASH-ERR-MARKER'));
    }

    public function test_validation_errors_render_once_via_the_real_redirect(): void
    {
        $admin   = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin Test', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        // An admin changing their own role is refused with withErrors(), which
        // is the production path that puts a bag in the session.
        $post = $this->withSession($session)
            ->from('/tableau-de-bord/admin/utilisateurs')
            ->post('/tableau-de-bord/admin/utilisateurs/' . $admin->id . '/role', ['role' => 'moderator']);

        $post->assertRedirect('/tableau-de-bord/admin/utilisateurs');

        $res = $this->withSession($session)->followingRedirects()
            ->from('/tableau-de-bord/admin/utilisateurs')
            ->post('/tableau-de-bord/admin/utilisateurs/' . $admin->id . '/role', ['role' => 'moderator']);

        $res->assertOk();
        $body = $res->getContent();
        $this->assertStringContainsString('ui-alert ui-alert-danger', $body);
        $this->assertSame(1, substr_count($body, 'propre'), 'the validation message should render exactly once');
    }

    public function test_flash_reaches_a_page_that_never_rendered_it(): void
    {
        // admin-products was not one of the 18; it gets the alert now anyway.
        $res = $this->withSession($this->adminSession(['success' => 'FLASH-NEW-MARKER']))
            ->get('/tableau-de-bord/admin/produits');

        $res->assertOk();
        $this->assertSame(1, substr_count($res->getContent(), 'FLASH-NEW-MARKER'));
    }
}
