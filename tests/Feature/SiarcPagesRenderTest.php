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

    public function test_public_siarc_chrome_and_home_render(): void
    {
        $this->seed(\Database\Seeders\SiarcSalonSeeder::class);

        // V4 home hero + SIARC public chrome (not the artisan-directory chrome).
        $this->get('/siarc')->assertOk()
            ->assertSee('CAMEROUNAIS')
            ->assertSee('MON ESPACE')
            ->assertSee('UNE PLATEFORME COMPLÈTE À VOTRE SERVICE');

        // Public directories render their bespoke bodies.
        $this->get('/siarc/exposants')->assertOk();
        $this->get('/siarc/pavillons')->assertOk();
        $this->get('/siarc/intervenants')->assertOk();
        $this->get('/siarc/programme')->assertOk();
    }

    public function test_admin_siarc_chrome_renders(): void
    {
        $this->seed(\Database\Seeders\SiarcSalonSeeder::class);
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'SIARC Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        // SIARC admin shell: sidebar items + dashboard body.
        $this->withSession($session)->get(route('siarc.admin.dashboard'))->assertOk()
            ->assertSee('B2B Matchmaking')
            ->assertSee('Compte à rebours');
    }

    public function test_siarc_detail_pages_render_with_seeded_data(): void
    {
        $this->seed(\Database\Seeders\SiarcSalonSeeder::class);
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'SIARC Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        $eid = \Illuminate\Support\Facades\DB::table('events')->value('id');
        $ee = \Illuminate\Support\Facades\DB::table('event_exhibitors')->where('event_id', $eid)->first();
        $pav = \Illuminate\Support\Facades\DB::table('pavilions')->where('event_id', $eid)->first();
        $spk = \Illuminate\Support\Facades\DB::table('speakers')->where('event_id', $eid)->first();
        $sess = \Illuminate\Support\Facades\DB::table('programme_sessions')->where('event_id', $eid)->first();
        $biz = \Illuminate\Support\Facades\DB::table('businesses')->where('id', $ee->business_id ?? 0)->first();

        // Public detail pages.
        if ($biz) {
            $this->get('/siarc/exposants/'.$biz->slug)->assertOk();
        }
        if ($spk) {
            $this->get(route('siarc.speaker', ['id' => $spk->id]))->assertOk();
        }

        // Admin detail pages.
        if ($ee) {
            $this->withSession($session)->get(route('siarc.admin.exhibitor', ['id' => $ee->id]))->assertOk();
        }
        if ($pav) {
            $this->withSession($session)->get(route('siarc.admin.pavilion', ['id' => $pav->id]))->assertOk();
        }
        if ($spk) {
            $this->withSession($session)->get(route('siarc.admin.speaker', ['id' => $spk->id]))->assertOk();
        }
        if ($sess) {
            $this->withSession($session)->get(route('siarc.admin.session', ['id' => $sess->id]))->assertOk();
            $this->withSession($session)->get(route('siarc.admin.workshop', ['id' => $sess->id]))->assertOk();
        }
    }
}
