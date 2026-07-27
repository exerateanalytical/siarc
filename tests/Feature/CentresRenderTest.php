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
        // Build the centre this test describes. It used to read one the migration
        // seeded, which quietly made twelve invented centres — with rand()
        // generated phone numbers — a thing the suite required to exist.
        $centreId = DB::table('artisan_centres')->insertGetId([
            'slug' => 'centre-test', 'name_fr' => 'Centre Test', 'name_en' => 'Test Centre',
            'region_id' => DB::table('regions')->value('id'),
            'type' => 'centre', 'city' => 'Yaoundé', 'status' => 'active', 'sort_order' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $centre = DB::table('artisan_centres')->find($centreId);

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
        // Same reasoning as the centre above: create the article rather than
        // depending on eight fabricated ones being seeded.
        $articleId = DB::table('announcements')->insertGetId([
            'slug' => 'article-test', 'title_fr' => 'Article Test', 'title_en' => 'Test Article',
            'excerpt_fr' => 'Extrait.', 'body_fr' => 'Contenu de test.',
            'status' => 'published', 'published_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $article = DB::table('announcements')->find($articleId);

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

    public function test_batch_d_admin_detail_pages_render(): void
    {
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        // Create a support ticket (migration seed needs pre-existing users)
        $ticketId = DB::table('support_tickets')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(), 'user_id' => $admin->id,
            'subject_fr' => 'Ticket Test', 'subject_en' => 'Ticket Test',
            'status' => 'in_progress', 'priority' => 'high',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('support_ticket_replies')->insert([
            'ticket_id' => $ticketId, 'user_id' => $admin->id, 'body_fr' => 'Bonjour', 'body_en' => 'Hello',
            'is_staff' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession($session)->get('/tableau-de-bord/admin/support/' . $ticketId)->assertOk()->assertSee('Ticket Test');

        // Verification detail — build a business + application
        $biz = $this->makeBusiness();
        $appId = DB::table('verification_applications')->insertGetId([
            'business_id' => $biz->id, 'tier_requested' => 'verified', 'status' => 'under_review',
            'submitted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession($session)->get('/tableau-de-bord/admin/verifications/' . $appId . '/detail')->assertOk();
        $this->withSession($session)->get('/tableau-de-bord/admin/verifications/' . $appId . '/revue')->assertOk();

        // Notifications centre + detail
        $notifId = DB::table('user_notifications')->insertGetId([
            'user_id' => $admin->id, 'type' => 'support', 'title' => 'Notif Test', 'body' => 'Corps',
            'link' => '/actualites', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->withSession($session)->get('/tableau-de-bord/admin/notifications')->assertOk();
        $this->withSession($session)->get('/tableau-de-bord/admin/notifications/' . $notifId)->assertOk()->assertSee('Notif Test');

        // Staff reply persists
        $before = DB::table('support_ticket_replies')->where('ticket_id', $ticketId)->count();
        $this->withSession($session)->post('/tableau-de-bord/admin/support/' . $ticketId . '/repondre', ['body' => 'Réponse de test.'])->assertRedirect();
        $this->assertSame($before + 1, DB::table('support_ticket_replies')->where('ticket_id', $ticketId)->count());
    }
}
