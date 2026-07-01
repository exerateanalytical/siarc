<?php

namespace Tests\Feature\Cms;

use App\Modules\Auth\Models\User;
use App\Modules\Cms\Models\Announcement;
use App\Modules\Cms\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_public_can_list_published_pages(): void
    {
        Page::create(['slug' => 'about', 'title_fr' => 'À propos', 'body_fr' => 'Contenu', 'is_published' => true]);
        Page::create(['slug' => 'draft', 'title_fr' => 'Brouillon', 'body_fr' => 'Contenu', 'is_published' => false]);

        $this->getJson('/api/v1/cms/pages')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'about');
    }

    public function test_public_can_view_single_page_by_slug(): void
    {
        Page::create(['slug' => 'terms', 'title_fr' => 'Conditions', 'body_fr' => 'Texte', 'is_published' => true]);

        $this->getJson('/api/v1/cms/pages/terms')
            ->assertOk()
            ->assertJsonPath('data.slug', 'terms')
            ->assertJsonPath('data.title_fr', 'Conditions');
    }

    public function test_unpublished_page_is_not_viewable(): void
    {
        Page::create(['slug' => 'secret', 'title_fr' => 'Secret', 'body_fr' => 'Texte', 'is_published' => false]);

        $this->getJson('/api/v1/cms/pages/secret')->assertNotFound();
    }

    public function test_public_can_list_announcements(): void
    {
        Announcement::create(['title_fr' => 'Annonce', 'body_fr' => 'Corps', 'type' => 'info', 'is_published' => true]);
        Announcement::create(['title_fr' => 'Caché', 'body_fr' => 'Corps', 'type' => 'info', 'is_published' => false]);

        $this->getJson('/api/v1/cms/announcements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_page(): void
    {
        Passport::actingAs($this->admin());

        $this->postJson('/api/v1/admin/cms/pages', [
            'slug'         => 'privacy',
            'title_fr'     => 'Confidentialité',
            'body_fr'      => 'Politique',
            'is_published' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'privacy');

        $this->assertDatabaseHas('pages', ['slug' => 'privacy']);
    }

    public function test_admin_can_update_page(): void
    {
        Passport::actingAs($this->admin());
        $page = Page::create(['slug' => 'faq', 'title_fr' => 'Ancien', 'body_fr' => 'Texte', 'is_published' => false]);

        $this->putJson("/api/v1/admin/cms/pages/{$page->slug}", ['title_fr' => 'Nouveau'])
            ->assertOk()
            ->assertJsonPath('data.title_fr', 'Nouveau');

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title_fr' => 'Nouveau']);
    }

    public function test_admin_can_create_announcement(): void
    {
        Passport::actingAs($this->admin());

        $this->postJson('/api/v1/admin/cms/announcements', [
            'title_fr'     => 'Maintenance',
            'body_fr'      => 'Le site sera en maintenance.',
            'type'         => 'warning',
            'is_published' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'warning');

        $this->assertDatabaseHas('announcements', ['title_fr' => 'Maintenance']);
    }

    public function test_non_admin_cannot_create_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('investor');
        Passport::actingAs($user);

        $this->postJson('/api/v1/admin/cms/pages', [
            'slug' => 'x', 'title_fr' => 'X', 'body_fr' => 'Y',
        ])->assertForbidden();
    }
}
