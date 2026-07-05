<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The SIARC salon module must render (public 200, admin 200 behind the guard) even
 * on an empty database with no SIARC event seeded — every route is null-safe.
 */
class SiarcPagesRenderTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_public_siarc_pages_render_for_guests(): void
    {
        foreach ([
            '/siarc', '/siarc/exposants', '/siarc/pavillons',
            '/siarc/programme', '/siarc/intervenants', '/siarc/inscription',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_admin_siarc_pages_render_for_an_admin(): void
    {
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'SIARC Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        foreach ([
            'siarc.admin.dashboard', 'siarc.admin.exhibitors', 'siarc.admin.pavilions',
            'siarc.admin.floorplan', 'siarc.admin.stands', 'siarc.admin.visitors',
            'siarc.admin.entry', 'siarc.admin.badges', 'siarc.admin.checkin',
            'siarc.admin.b2b', 'siarc.admin.matchmaking', 'siarc.admin.programme',
            'siarc.admin.speakers', 'siarc.admin.calendar', 'siarc.admin.analytics',
            'siarc.admin.attendance', 'siarc.admin.live', 'siarc.admin.reports',
            'siarc.admin.incidents', 'siarc.admin.vip',
            'siarc.mobile.scanner', 'siarc.mobile.exhibitor-checkin',
        ] as $name) {
            $this->withSession($session)->get(route($name))->assertOk();
        }
    }

    public function test_admin_siarc_pages_render_with_seeded_data(): void
    {
        $this->seed(\Database\Seeders\SiarcSalonSeeder::class);
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'SIARC Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        $this->withSession($session)->get(route('siarc.admin.dashboard'))->assertOk()->assertSee('SIARC');
        $this->withSession($session)->get(route('siarc.admin.exhibitors'))->assertOk();
    }
}
