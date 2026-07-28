<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_the_new_admin_pages_render_for_an_admin(): void
    {
        $admin = $this->makeUser();

        $session = ['siac_user' => [
            'id'       => $admin->id,
            'name'     => 'Admin Test',
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]];

        foreach ([
            '/tableau-de-bord/admin/artisans',
            '/tableau-de-bord/admin/commandes',
            '/tableau-de-bord/admin/kyc',
            '/tableau-de-bord/admin/roles',
            '/tableau-de-bord/admin/regions-centres',
            '/tableau-de-bord/admin/exports',
            '/tableau-de-bord/admin/collections',
            '/tableau-de-bord/admin/actualites',
            '/tableau-de-bord/admin/medias',
            '/tableau-de-bord/admin/produits',
            '/tableau-de-bord/admin/utilisateurs',
            '/tableau-de-bord/admin/parametres',
            '/tableau-de-bord/admin/analytique',
            '/tableau-de-bord/admin/rapports',
            '/tableau-de-bord/admin/evenements',
            '/tableau-de-bord/admin/categories',
        ] as $path) {
            $this->withSession($session)->get($path)->assertOk();
        }
    }

    public function test_the_surfaces_disabled_for_launch_are_unreachable(): void
    {
        $admin = $this->makeUser();

        $session = ['siac_user' => [
            'id'       => $admin->id,
            'name'     => 'Admin Test',
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]];

        // Subscriptions have no write path, the backup screen reported backups it
        // never took, and the developer programme is not being run.
        //
        // /tableau-de-bord/admin/paiements used to be on this list and no longer
        // is. It was disabled because its feed was built on business_subscriptions,
        // which nothing writes to — the screen showed transactions that had never
        // happened. It is now the reviewer's queue over the `payments` table, where
        // every row is a real record somebody opened, and PaymentSurfacesTest
        // guards it. The old dead route has been deleted rather than left in place,
        // because two registrations of the same URI resolve to whichever was
        // declared last, which is not a thing to leave lying around.
        foreach ([
            '/tableau-de-bord/admin/abonnements',
            '/tableau-de-bord/admin/sauvegardes',
            '/tableau-de-bord/admin/sauvegardes/1',
            '/tableau-de-bord/admin/api-consommateurs',
            '/developer',
        ] as $path) {
            $this->withSession($session)->get($path)->assertNotFound();
        }

        $this->withSession($session)->post('/tableau-de-bord/admin/sauvegardes/creer')->assertNotFound();
        $this->withSession($session)->post('/tableau-de-bord/admin/sauvegardes/nettoyer')->assertNotFound();
    }

    public function test_admin_product_detail_page_renders(): void
    {
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin Test', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        $product = $this->makeProduct();

        $this->withSession($session)
            ->get('/tableau-de-bord/admin/produits/' . $product->id)
            ->assertOk()
            ->assertSee($product->name_fr);
    }

    public function test_admin_categories_page_renders_with_real_rows(): void
    {
        // The official craft taxonomy is now seeded by migration (000004), so the
        // test DB already holds the real 4-level tree. We (a) assert a seeded
        // sector renders, and (b) create a parent + child and surface them via the
        // search filter to exercise the hierarchy/date-formatting code paths
        // without fighting the 10-per-page pagination.
        $admin = $this->makeUser();
        $session = ['siac_user' => [
            'id' => $admin->id, 'name' => 'Admin Test', 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]];

        $parentId = \Illuminate\Support\Facades\DB::table('industries')->insertGetId([
            'slug' => 'test-industrie', 'name_fr' => 'Industrie Test', 'name_en' => 'Test Industry',
            'icon' => 'shapes', 'description_fr' => 'Une industrie de test.', 'description_en' => 'A test industry.',
            'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('industries')->insert([
            'parent_id' => $parentId, 'slug' => 'test-sous-industrie', 'name_fr' => 'Sous-Industrie Test',
            'name_en' => 'Test Sub-Industry', 'icon' => 'shapes', 'description_fr' => 'Une sous-industrie de test.',
            'description_en' => 'A test sub-industry.', 'sort_order' => 2, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // (a) the seeded official taxonomy renders on the default page
        $this->withSession($session)
            ->get('/tableau-de-bord/admin/categories')
            ->assertOk()
            ->assertSee('Artisanat');

        // (b) the parent + child hierarchy renders (search keeps them on page 1)
        $this->withSession($session)
            ->get('/tableau-de-bord/admin/categories?q=' . urlencode('Test'))
            ->assertOk()
            ->assertSee('Industrie Test')
            ->assertSee('Sous-Industrie Test');
    }
}
