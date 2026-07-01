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
    public function upload(UploadedFile $file, Product $product): ProductImage
    {
        $image = Image::read($file)->scaleDown(1200, 1200)->toWebp(85);
        $path  = "products/{$product->slug}/images/" . Str::uuid() . '.webp';
        Storage::disk('s3')->put($path, $image->toString(), 'public');

        $maxOrder = $product->images()->max('sort_order') ?? 0;

        return ProductImage::create([
            'product_id'  => $product->id,
            'image_path'  => $path,
            'sort_order'  => $maxOrder + 1,
        ]);
    }

    public function delete(ProductImage $image): void
    {
        Storage::disk('s3')->delete($image->image_path);
        $image->delete();
    }
}
