<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * A vendor's business starts in draft and can publish a product before
 * publishing the business itself — a common, legitimate sequence, not an
 * error state. Before this fix, FrontendController::productShow() required
 * both the product AND its business to be published, so the vendor's own
 * "View public page" link — which points at exactly this route — 404'd on
 * their own freshly-published product. The fix: the public still gets 404
 * (a product is never shown for a business the platform has not confirmed),
 * but the product's owner (and an admin) sees an explicitly-labelled preview
 * instead of a bare, unexplained 404.
 */
class ProductPublicPageTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_a_guest_gets_404_for_a_published_product_whose_business_is_draft(): void
    {
        $business = $this->makeBusiness(null, ['status' => 'draft']);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $this->get("/galerie/produits/{$product->slug}")->assertNotFound();
    }

    public function test_the_products_owner_sees_a_labelled_preview_instead_of_a_404(): void
    {
        $owner = $this->makeUser();
        $business = $this->makeBusiness($owner, ['status' => 'draft']);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $response = $this->withSession(['siac_user' => [
            'id' => $owner->id, 'name' => $owner->name, 'email' => $owner->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]])->get("/galerie/produits/{$product->slug}?lang=en");

        $response->assertOk();
        $response->assertSee($product->name_fr);
        // The banner, not silence — a preview must never look like a live page.
        $response->assertSee('Preview.', false);
    }

    public function test_an_admin_also_sees_the_preview_for_someone_elses_draft_business(): void
    {
        $admin = $this->makeUser();
        $business = $this->makeBusiness(null, ['status' => 'draft']);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $response = $this->withSession(['siac_user' => [
            'id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email,
            'role' => 'super_admin', 'is_admin' => true,
        ]])->get("/galerie/produits/{$product->slug}?lang=en");

        $response->assertOk();
        $response->assertSee('Preview.', false);
    }

    public function test_a_different_logged_in_member_still_gets_404_not_the_preview(): void
    {
        $someoneElse = $this->makeUser();
        $business = $this->makeBusiness(null, ['status' => 'draft']);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $response = $this->withSession(['siac_user' => [
            'id' => $someoneElse->id, 'name' => $someoneElse->name, 'email' => $someoneElse->email,
            'role' => 'buyer', 'is_admin' => false,
        ]])->get("/galerie/produits/{$product->slug}");

        $response->assertNotFound();
    }

    public function test_a_preview_visit_does_not_increment_the_view_counter(): void
    {
        $owner = $this->makeUser();
        $business = $this->makeBusiness($owner, ['status' => 'draft']);
        $product = $this->makeProduct($business, ['status' => 'published', 'views_count' => 0]);

        $this->withSession(['siac_user' => [
            'id' => $owner->id, 'name' => $owner->name, 'email' => $owner->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]])->get("/galerie/produits/{$product->slug}");

        $this->assertSame(0, $product->fresh()->views_count);
    }

    public function test_a_fully_published_product_still_renders_normally_for_a_guest(): void
    {
        $business = $this->makeBusiness(null, ['status' => 'published']);
        $product = $this->makeProduct($business, ['status' => 'published']);

        $response = $this->get("/galerie/produits/{$product->slug}");

        $response->assertOk();
        $response->assertDontSee('Preview', false);
    }
}
