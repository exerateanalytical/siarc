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
            '/tableau-de-bord/admin/abonnements',
            '/tableau-de-bord/admin/regions-centres',
            '/tableau-de-bord/admin/sauvegardes',
            '/tableau-de-bord/admin/sauvegardes/1',
            '/tableau-de-bord/admin/exports',
            '/tableau-de-bord/admin/collections',
            '/tableau-de-bord/admin/actualites',
            '/tableau-de-bord/admin/medias',
            '/tableau-de-bord/admin/produits',
            '/tableau-de-bord/admin/utilisateurs',
            '/tableau-de-bord/admin/parametres',
            '/tableau-de-bord/admin/paiements',
            '/tableau-de-bord/admin/analytique',
            '/tableau-de-bord/admin/rapports',
            '/tableau-de-bord/admin/evenements',
            '/tableau-de-bord/admin/categories',
        ] as $path) {
            $this->withSession($session)->get($path)->assertOk();
        }
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
        // industries base rows are seeded once in dev (not via migration), so
        // the test DB starts empty — create real parent + child rows here to
        // actually exercise the hierarchy/date-formatting code paths.
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

        $this->withSession($session)
            ->get('/tableau-de-bord/admin/categories')
            ->assertOk()
            ->assertSee('Industrie Test')
            ->assertSee('Sous-Industrie Test');
    }
}
