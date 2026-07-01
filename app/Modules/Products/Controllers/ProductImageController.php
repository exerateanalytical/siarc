<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $imageService) {}

    public function store(Request $request, int $productId): JsonResponse
    {
        $request->validate(['image' => ['required', 'image', 'max:5120', 'mimes:jpg,jpeg,png,webp']]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($productId);

        if ($product->images()->count() >= 10) {
            return response()->json(['message' => 'Maximum 10 images per product.'], 422);
        }

        $image = $this->imageService->upload($request->file('image'), $product);

        return response()->json(['data' => ['id' => $image->id, 'url' => $image->url]], 201);
    }

    public function destroy(Request $request, int $productId, int $imageId): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($productId);
        $image   = $product->images()->findOrFail($imageId);

        $this->imageService->delete($image);

        return response()->json(null, 204);
    }

    public function reorder(Request $request, int $productId): JsonResponse
    {
        $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']]);

        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($productId);

        foreach ($request->order as $position => $id) {
            $product->images()->where('id', $id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['message' => 'Images reordered.']);
    }
}
