<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Products\Models\Product;
use App\Modules\Products\Resources\ProductListResource;
use App\Modules\Products\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::published()
            ->with(['primaryImage', 'category', 'business'])
            ->whereHas('business', fn ($q) => $q->published());

        if ($request->filled('business')) {
            $query->whereHas('business', fn ($q) => $q->where('slug', $request->business));
        }
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('industry')) {
            $query->whereHas('category.sector.industry', fn ($q) => $q->where('slug', $request->industry));
        }
        if ($request->filled('region')) {
            $query->whereHas('business.region', fn ($q) => $q->where('id', $request->region));
        }
        if ($request->boolean('exported')) {
            $query->where('is_export_ready', true);
        }
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name_fr', 'like', $search)
                                       ->orWhere('name_en', 'like', $search)
                                       ->orWhere('description_fr', 'like', $search));
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'views'  => $query->orderByDesc('views_count'),
            'name'   => $query->orderBy('name_fr'),
            default  => $query->latest(),
        };

        $products = $query->paginate($request->integer('per_page', 24));

        return response()->json([
            'data' => ProductListResource::collection($products->items()),
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)
            ->with(['images', 'attributes.template', 'documents', 'videos', 'category', 'business.region'])
            ->published()
            ->whereHas('business', fn ($q) => $q->published())
            ->firstOrFail();

        $product->increment('views_count');

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function byBusiness(string $businessSlug, Request $request): JsonResponse
    {
        $products = Product::published()
            ->with(['primaryImage', 'category'])
            ->whereHas('business', fn ($q) => $q->where('slug', $businessSlug)->published())
            ->orderBy('sort_order')
            ->paginate($request->integer('per_page', 24));

        return response()->json([
            'data' => ProductListResource::collection($products->items()),
            'meta' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }
}
