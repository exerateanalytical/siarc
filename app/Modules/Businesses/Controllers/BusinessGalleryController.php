<?php

namespace App\Modules\Businesses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\BusinessGallery;
use App\Modules\Businesses\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessGalleryController extends Controller
{
    public function __construct(private readonly ImageUploadService $imageUpload) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image'      => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp'],
            'caption_fr' => ['nullable', 'string', 'max:255'],
            'caption_en' => ['nullable', 'string', 'max:255'],
        ]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        if ($business->gallery()->count() >= 20) {
            return response()->json(['message' => 'Gallery limit of 20 images reached.'], 422);
        }

        $path = $this->imageUpload->uploadGalleryImage($request->file('image'), $business->slug);
        $maxOrder = $business->gallery()->max('sort_order') ?? 0;

        $item = BusinessGallery::create([
            'business_id' => $business->id,
            'image_path'  => $path,
            'caption_fr'  => $request->caption_fr,
            'caption_en'  => $request->caption_en,
            'sort_order'  => $maxOrder + 1,
        ]);

        return response()->json(['data' => ['id' => $item->id, 'url' => $item->url]], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $item = $business->gallery()->findOrFail($id);
        $this->imageUpload->delete($item->image_path);
        $item->delete();

        return response()->json(null, 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        foreach ($request->order as $position => $id) {
            $business->gallery()->where('id', $id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['message' => 'Gallery reordered.']);
    }
}
