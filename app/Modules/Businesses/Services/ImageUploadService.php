<?php

namespace App\Modules\Businesses\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    public function uploadLogo(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::decode($file)->scaleDown(400, 400)->encode(new WebpEncoder(quality: 85));
        $path  = "businesses/{$businessSlug}/logo/" . Str::uuid() . '.webp';
        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->put($path, $image->toString());
        return $path;
    }

    public function uploadCover(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::decode($file)->scaleDown(1200, 600)->encode(new WebpEncoder(quality: 85));
        $path  = "businesses/{$businessSlug}/cover/" . Str::uuid() . '.webp';
        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->put($path, $image->toString());
        return $path;
    }

    public function uploadGalleryImage(UploadedFile $file, string $businessSlug): string
    {
        $image = Image::decode($file)->scaleDown(1200, 900)->encode(new WebpEncoder(quality: 82));
        $path  = "businesses/{$businessSlug}/gallery/" . Str::uuid() . '.webp';
        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->put($path, $image->toString());
        return $path;
    }

    public function delete(string $path): void
    {
        Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'public')->delete($path);
    }
}
