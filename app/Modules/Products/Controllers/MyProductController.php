<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Requests\CreateProductRequest;
use App\Modules\Products\Resources\ProductListResource;
use App\Modules\Products\Resources\ProductResource;
use App\Modules\Products\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    public function index(Request $request): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $products = $business->products()
            ->withoutGlobalScopes()
            ->with(['primaryImage', 'category'])
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 20), 100)));

        return response()->json([
            'data' => ProductListResource::collection($products->items()),
            'meta' => ['total' => $products->total(), 'last_page' => $products->lastPage()],
        ]);
    }

    public function store(CreateProductRequest $request): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $this->service->create($business, $request->validated());

        return response()->json(['data' => new ProductResource($product)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()
            ->with(['images', 'attributes', 'videos', 'category'])
            ->findOrFail($id);

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function update(CreateProductRequest $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($id);
        $product = $this->service->update($product, $request->validated());

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($id);
        $product->delete();

        return response()->json(null, 204);
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($id);
        $this->service->publish($product);

        return response()->json(['status' => 'published']);
    }

    public function unpublish(Request $request, int $id): JsonResponse
    {
        $business = $request->user()->business;
        if (! $business) {
            return response()->json(['message' => 'No business profile found.'], 404);
        }

        $product = $business->products()->withoutGlobalScopes()->findOrFail($id);
        $this->service->unpublish($product);

        return response()->json(['status' => 'draft']);
    }
}
