<?php

namespace App\Modules\Taxonomy\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Taxonomy\Models\AttributeTemplate;
use App\Modules\Taxonomy\Models\Certification;
use App\Modules\Taxonomy\Models\Industry;
use App\Modules\Taxonomy\Models\ProductCategory;
use App\Modules\Taxonomy\Models\Region;
use App\Modules\Taxonomy\Models\Sector;
use App\Modules\Taxonomy\Resources\CategoryResource;
use App\Modules\Taxonomy\Resources\IndustryResource;
use App\Modules\Taxonomy\Resources\RegionResource;
use App\Modules\Taxonomy\Resources\SectorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    public function industries(): JsonResponse
    {
        $industries = Industry::active()
            ->withCount('businesses')
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => IndustryResource::collection($industries)]);
    }

    public function industry(string $slug): JsonResponse
    {
        $industry = Industry::where('slug', $slug)->with('sectors.categories')->firstOrFail();

        return response()->json(['data' => new IndustryResource($industry)]);
    }

    public function sectors(Request $request): JsonResponse
    {
        $query = Sector::active()->with('industry')->orderBy('sort_order');

        if ($request->has('industry')) {
            $query->whereHas('industry', fn ($q) => $q->where('slug', $request->industry));
        }

        return response()->json(['data' => SectorResource::collection($query->get())]);
    }

    public function categories(Request $request): JsonResponse
    {
        $query = ProductCategory::active()
            ->with('children.children')
            ->roots()
            ->orderBy('sort_order');

        if ($request->has('sector')) {
            $query->whereHas('sector', fn ($q) => $q->where('slug', $request->sector));
        }

        return response()->json(['data' => CategoryResource::collection($query->get())]);
    }

    public function category(string $slug): JsonResponse
    {
        $category = ProductCategory::where('slug', $slug)
            ->with(['children.children', 'sector.industry'])
            ->firstOrFail();

        return response()->json(['data' => new CategoryResource($category)]);
    }

    public function regions(): JsonResponse
    {
        $regions = Region::with('cities')->orderBy('name_fr')->get();

        return response()->json(['data' => RegionResource::collection($regions)]);
    }

    public function certifications(Request $request): JsonResponse
    {
        $query = Certification::query();

        if ($request->has('industry')) {
            $query->whereHas('industry', fn ($q) => $q->where('slug', $request->industry))
                  ->orWhereNull('industry_id');
        }

        $certs = $query->orderBy('name_fr')->get();

        return response()->json(['data' => $certs->map(fn ($c) => [
            'id'           => $c->id,
            'name_fr'      => $c->name_fr,
            'name_en'      => $c->name_en,
            'issuing_body' => $c->issuing_body_fr,
        ])]);
    }

    public function attributeTemplates(Request $request): JsonResponse
    {
        $query = AttributeTemplate::orderBy('sort_order');

        if ($request->has('industry')) {
            $query->whereHas('industry', fn ($q) => $q->where('slug', $request->industry))
                  ->orWhereNull('industry_id');
        }

        if ($request->has('category')) {
            // resolve industry from category
            $cat = ProductCategory::where('slug', $request->category)->with('sector.industry')->first();
            if ($cat && $cat->sector) {
                $query->where('industry_id', $cat->sector->industry_id)->orWhereNull('industry_id');
            }
        }

        return response()->json(['data' => $query->get()]);
    }
}
