<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class CentresRenderTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_centre_admin_detail_and_public_pages_render(): void
    {
        // The migration seeds artisan_centres; grab a real one.
        $centre = DB::table('artisan_centres')->first();
        $this->assertNotNull($centre, 'artisan_centres should be seeded by migration');

        // Admin detail (requires admin session)
        $admin = $this->makeUser();
        $this->withSession(['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]])->get('/tableau-de-bord/admin/centres/' . $centre->id)
            ->assertOk()
            ->assertSee($centre->name_fr);

        // Public centre page (no auth)
        $this->get('/centres-artisanat/' . $centre->slug)
            ->assertOk()
            ->assertSee($centre->name_fr);
    }
}
