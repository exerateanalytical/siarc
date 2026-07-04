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

    public function test_partner_admin_detail_and_public_pages_render(): void
    {
        // Seed a partner (partners aren't migration-seeded in tests)
        $id = DB::table('partners')->insertGetId([
            'name_fr' => 'Partenaire Test', 'name_en' => 'Test Partner', 'tier' => 'institutional',
            'is_active' => true, 'sort_order' => 1, 'contact_email' => 'test@partner.cm',
            'contact_phone' => '+237 600000000', 'sector_fr' => 'Culture', 'country' => 'Cameroun',
            'partner_ref' => 'PART-2024-9999', 'responsible_name' => 'Test Resp',
            'responsible_title' => 'Directeur', 'responsible_email' => 'resp@partner.cm',
            'partnership_type' => 'Institutionnel', 'partnership_level' => 'Premium',
            'start_date' => '2024-05-12', 'end_date' => '2027-05-11',
            'auto_renew' => true, 'legal_verified' => true, 'reliability' => 4.8, 'since_year' => 2024,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $admin = $this->makeUser();
        $this->withSession(['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]])->get('/tableau-de-bord/admin/partenaires/' . $id . '/detail')
            ->assertOk()->assertSee('Partenaire Test');

        $this->get('/partenaires/' . $id)->assertOk()->assertSee('Partenaire Test');
    }

    public function test_news_admin_detail_and_public_pages_render(): void
    {
        // announcements are seeded by migration
        $article = DB::table('announcements')->where('status', 'published')->first();
        $this->assertNotNull($article);

        $admin = $this->makeUser();
        $this->withSession(['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]])->get('/tableau-de-bord/admin/actualites/' . $article->id)
            ->assertOk()->assertSee($article->title_fr);

        $this->get('/actualites/' . $article->slug)->assertOk()->assertSee($article->title_fr);
    }

    public function test_collection_create_form_renders_and_stores(): void
    {
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        $this->withSession($session)->get('/tableau-de-bord/admin/collections/creer')
            ->assertOk()->assertSee('Ajouter une Collection');

        $before = \Illuminate\Support\Facades\DB::table('heritage_collections')->count();
        $this->withSession($session)->post('/tableau-de-bord/admin/collections', [
            'name_fr' => 'Collection Test Vannerie', 'status' => 'published',
            'visibility' => 'public', 'description_fr' => 'Une collection de test.',
        ])->assertRedirect();
        $this->assertSame($before + 1, \Illuminate\Support\Facades\DB::table('heritage_collections')->count());

        // Public collections gallery
        $this->get('/collections-heritage')->assertOk();
    }
}
