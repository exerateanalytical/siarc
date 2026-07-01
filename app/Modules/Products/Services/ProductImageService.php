<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ProductImageService
{
    public function upload(UploadedFile $file, Product $product, string $category = 'main'): ProductImage
    {
        $image = Image::read($file)->scaleDown(1200, 1200)->toWebp(85);
        $path  = "products/{$product->slug}/images/" . Str::uuid() . '.webp';
        Storage::disk(config('filesystems.default'))->put($path, $image->toString());

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
        Storage::disk(config('filesystems.default'))->delete($image->file_path);
        $image->delete();
    }
}
