<?php

namespace Tests\Feature\Products;

use App\Modules\Auth\Models\User;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class ProductImageOrderingTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function actingAsSeller(User $user): static
    {
        return $this->withSession(['siac_user' => [
            'id'    => $user->id,
            'name'  => $user->name ?? 'Vendeur',
            'email' => $user->email,
        ]]);
    }

    /** @return array{0: User, 1: Product, 2: ProductImage[]} */
    private function sellerWithImages(int $count = 3): array
    {
        Storage::fake('public');

        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        $product  = $this->makeProduct($business);

        $images = [];
        for ($i = 1; $i <= $count; $i++) {
            $images[] = ProductImage::create([
                'product_id' => $product->id,
                'file_path'  => "products/{$product->slug}/images/{$i}.webp",
                'category'   => 'main',
                'is_cover'   => $i === 1,
                'sort_order' => $i,
            ]);
        }

        return [$owner, $product, $images];
    }

    public function test_seller_can_set_a_new_cover(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[1]->id}/couverture")
             ->assertRedirect(route('products.web-edit', ['slug' => $product->slug]));

        $this->assertTrue($images[1]->fresh()->is_cover);
        $this->assertEquals($images[1]->id, $product->fresh()->primaryImage->id);
    }

    public function test_setting_a_cover_clears_the_previous_one(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[2]->id}/couverture");

        $this->assertFalse($images[0]->fresh()->is_cover);
        $this->assertSame(1, ProductImage::where('product_id', $product->id)->where('is_cover', true)->count());
    }

    public function test_products_without_an_explicit_cover_still_resolve_a_primary_image(): void
    {
        [, $product, $images] = $this->sellerWithImages();
        ProductImage::where('product_id', $product->id)->update(['is_cover' => false]);

        $this->assertEquals($images[0]->id, $product->fresh()->primaryImage->id);
    }

    public function test_seller_can_reorder_images(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[2]->id}/ordre", ['direction' => 'up'])
             ->assertRedirect(route('products.web-edit', ['slug' => $product->slug]));

        $this->assertSame(
            [$images[0]->id, $images[2]->id, $images[1]->id],
            $product->fresh()->images->pluck('id')->all()
        );
    }

    public function test_reordering_persists_across_moves(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[0]->id}/ordre", ['direction' => 'down']);
        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[0]->id}/ordre", ['direction' => 'down']);

        $this->assertSame(
            [$images[1]->id, $images[2]->id, $images[0]->id],
            $product->fresh()->images->pluck('id')->all()
        );
    }

    public function test_moving_past_the_edge_is_a_no_op(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[0]->id}/ordre", ['direction' => 'up'])
             ->assertRedirect(route('products.web-edit', ['slug' => $product->slug]));

        $this->assertSame(
            [$images[0]->id, $images[1]->id, $images[2]->id],
            $product->fresh()->images->pluck('id')->all()
        );
    }

    public function test_the_cover_survives_a_reorder(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[1]->id}/couverture");
        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[1]->id}/ordre", ['direction' => 'down']);

        $this->assertEquals($images[1]->id, $product->fresh()->primaryImage->id);
    }

    public function test_deleting_the_cover_promotes_the_next_image(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $this->actingAsSeller($owner)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[0]->id}/supprimer");

        $this->assertTrue($images[1]->fresh()->is_cover);
    }

    public function test_another_seller_cannot_set_the_cover(): void
    {
        [, $product, $images] = $this->sellerWithImages();

        $intruder = $this->makeUser();
        $this->makeBusiness($intruder);

        $this->actingAsSeller($intruder)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[1]->id}/couverture")
             ->assertNotFound();

        $this->assertTrue($images[0]->fresh()->is_cover);
    }

    public function test_another_seller_cannot_reorder_images(): void
    {
        [, $product, $images] = $this->sellerWithImages();

        $intruder = $this->makeUser();
        $this->makeBusiness($intruder);

        $this->actingAsSeller($intruder)
             ->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[2]->id}/ordre", ['direction' => 'up'])
             ->assertNotFound();

        $this->assertSame(
            [$images[0]->id, $images[1]->id, $images[2]->id],
            $product->fresh()->images->pluck('id')->all()
        );
    }

    public function test_a_signed_out_visitor_is_sent_to_login(): void
    {
        [, $product, $images] = $this->sellerWithImages();

        $this->post("/tableau-de-bord/produits/{$product->slug}/images/{$images[1]->id}/couverture")
             ->assertRedirectContains('/login');
    }

    public function test_the_edit_form_exposes_the_cover_and_order_controls(): void
    {
        [$owner, $product, $images] = $this->sellerWithImages();

        $response = $this->actingAsSeller($owner)
                         ->get("/tableau-de-bord/produits/{$product->slug}/modifier")
                         ->assertOk();

        $response->assertSee(route('products.web-set-cover-image', ['slug' => $product->slug, 'imageId' => $images[1]->id]), false);
        $response->assertSee(route('products.web-move-image', ['slug' => $product->slug, 'imageId' => $images[1]->id]), false);
    }
}
