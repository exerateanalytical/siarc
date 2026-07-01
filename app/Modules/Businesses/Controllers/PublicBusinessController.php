<?php

namespace App\Modules\Businesses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Resources\BusinessListResource;
use App\Modules\Businesses\Resources\BusinessResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicBusinessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Business::published()
            ->with(['industry', 'region', 'city', 'tags'])
            ->withCount('products');

        if ($request->filled('industry')) {
            $query->whereHas('industry', fn ($q) => $q->where('slug', $request->industry));
        }
        if ($request->filled('region')) {
            $query->whereHas('region', fn ($q) => $q->where('id', $request->region));
        }
        if ($request->filled('tier')) {
            $query->where('verification_tier', $request->tier);
        }
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name_fr', 'like', $search)
                                       ->orWhere('name_en', 'like', $search)
                                       ->orWhere('description_fr', 'like', $search));
        }
        if ($request->boolean('featured')) {
            $query->featured();
        }

        $sort = $request->get('sort', 'featured');
        match ($sort) {
            'views'   => $query->orderByDesc('views_count'),
            'newest'  => $query->latest(),
            'name'    => $query->orderBy('name_fr'),
            default   => $query->orderByDesc('is_featured')->orderByDesc('views_count'),
        };

        $businesses = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => BusinessListResource::collection($businesses->items()),
            'meta' => [
                'total'        => $businesses->total(),
                'per_page'     => $businesses->perPage(),
                'current_page' => $businesses->currentPage(),
                'last_page'    => $businesses->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)
            ->with(['industry', 'region', 'city', 'gallery', 'socialLinks', 'certifications', 'awards', 'tags'])
            ->published()
            ->firstOrFail();

        $business->increment('views_count');

        return response()->json(['data' => new BusinessResource($business)]);
    }

    public function featured(): JsonResponse
    {
        $businesses = Business::published()->featured()
            ->with(['industry', 'region', 'tags'])
            ->orderByDesc('views_count')
            ->limit(12)
            ->get();

        return response()->json(['data' => BusinessListResource::collection($businesses)]);
    }
}
