<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ProductImageService
{
    public function upload(UploadedFile $file, Product $product, string $category = 'main'): ProductImage
    {
        $image = Image::decode($file)->scaleDown(1200, 1200)->encode(new WebpEncoder(quality: 85));
        $path  = "products/{$product->slug}/images/" . Str::uuid() . '.webp';
        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->put($path, $image->toString());

        $maxOrder = $product->images()->max('sort_order') ?? 0;

        return ProductImage::create([
            'product_id' => $product->id,
            'file_path'  => $path,
            'category'   => $category,
            'is_cover'   => $maxOrder === 0,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    public function delete(ProductImage $image): void
    {
        $productId = $image->product_id;
        $wasCover  = (bool) $image->is_cover;

        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->delete($image->file_path);
        $image->delete();

        // Never leave a product cover-less while it still has photos: the
        // fallback would silently change which image merchandises the product.
        if ($wasCover) {
            $next = ProductImage::where('product_id', $productId)
                ->orderBy('sort_order')->orderBy('id')->first();
            $next?->forceFill(['is_cover' => true])->save();
        }
    }

    /**
     * Promote one image to cover. The column is not a unique key, so the
     * previous cover has to be cleared in the same transaction.
     */
    public function setCover(Product $product, ProductImage $image): void
    {
        DB::transaction(function () use ($product, $image) {
            ProductImage::where('product_id', $product->id)
                ->where('id', '!=', $image->id)
                ->update(['is_cover' => false]);

            $image->forceFill(['is_cover' => true])->save();
        });
    }

    /** Move one image one slot earlier ('up') or later ('down') in the sequence. */
    public function move(Product $product, ProductImage $image, string $direction): void
    {
        $ids = $this->orderedIds($product);
        $at  = array_search($image->id, $ids, true);
        if ($at === false) {
            return;
        }

        $to = $direction === 'up' ? $at - 1 : $at + 1;
        if ($to < 0 || $to >= count($ids)) {
            return; // already at the edge — a no-op, not an error
        }

        [$ids[$at], $ids[$to]] = [$ids[$to], $ids[$at]];

        $this->reorder($product, $ids);
    }

    /**
     * Rewrite the whole sequence rather than patching the two rows that moved:
     * images uploaded before ordering existed can share a sort_order, which
     * makes a pairwise swap non-deterministic.
     */
    public function reorder(Product $product, array $orderedIds): void
    {
        $known = $this->orderedIds($product);
        $ids   = array_values(array_unique(array_filter(
            array_map('intval', $orderedIds),
            fn ($id) => in_array($id, $known, true)
        )));

        // Anything the caller omitted keeps its relative order at the end.
        foreach ($known as $id) {
            if (! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        DB::transaction(function () use ($product, $ids) {
            foreach ($ids as $position => $id) {
                ProductImage::where('product_id', $product->id)
                    ->where('id', $id)
                    ->update(['sort_order' => $position + 1]);
            }
        });
    }

    /** @return int[] image ids in their current display order */
    private function orderedIds(Product $product): array
    {
        return ProductImage::where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
