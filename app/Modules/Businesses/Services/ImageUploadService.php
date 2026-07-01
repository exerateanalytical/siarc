<?php

namespace App\Modules\Businesses\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    public function uploadLogo(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::read($file)->scaleDown(400, 400)->toWebp(85);
        $path  = "businesses/{$businessSlug}/logo/" . Str::uuid() . '.webp';
        Storage::disk('s3')->put($path, $image->toString(), 'public');
        return $path;
    }

    public function uploadCover(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::read($file)->scaleDown(1200, 600)->toWebp(85);
        $path  = "businesses/{$businessSlug}/cover/" . Str::uuid() . '.webp';
        Storage::disk('s3')->put($path, $image->toString(), 'public');
        return $path;
    }

    public function uploadGalleryImage(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::read($file)->scaleDown(1200, 900)->toWebp(82);
        $path  = "businesses/{$businessSlug}/gallery/" . Str::uuid() . '.webp';
        Storage::disk('s3')->put($path, $image->toString(), 'public');
        return $path;
    }

    public function delete(string $path): void
    {
        Storage::disk('s3')->delete($path);
    }
}
