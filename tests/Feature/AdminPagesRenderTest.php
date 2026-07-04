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
}
