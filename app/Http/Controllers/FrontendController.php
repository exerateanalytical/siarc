<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Taxonomy\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->query('lang', $request->cookie('lang', 'fr'));
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    public function home(Request $request)
    {
        $lang = $this->lang($request);

        $industries = Industry::withCount('businesses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featured = Business::with(['industry', 'city', 'region'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->whereNotIn('industry_id', function ($q) {
                $q->select('id')->from('industries')->where('slug', 'aquaculture');
            })
            ->orderByDesc('views_count')
            ->limit(6)
            ->get();

        $aquaculture = Business::with(['industry', 'city', 'region'])
            ->where('status', 'published')
            ->whereHas('industry', fn($q) => $q->where('slug', 'aquaculture'))
            ->orderByDesc('views_count')
            ->limit(4)
            ->get();

        $stats = [
            'businesses' => Business::where('status', 'published')->count(),
            'products'   => DB::table('products')->where('status', 'published')->count(),
            'industries' => Industry::where('is_active', true)->count(),
        ];

        return response(
            view('pages.home', compact('lang', 'industries', 'featured', 'aquaculture', 'stats'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function businessIndex(Request $request)
    {
        $lang     = $this->lang($request);
        $q        = $request->query('q');
        $industry = $request->query('industry');
        $tier     = $request->query('tier');
        $region   = $request->query('region');

        $query = Business::with(['industry', 'city', 'region'])
            ->where('status', 'published');

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name_fr', 'like', "%{$q}%")
                   ->orWhere('name_en', 'like', "%{$q}%")
                   ->orWhere('tagline_fr', 'like', "%{$q}%")
                   ->orWhere('description_fr', 'like', "%{$q}%");
            });
        }

        if ($industry) {
            $query->whereHas('industry', fn($qb) => $qb->where('slug', $industry));
        }

        if ($tier) {
            $query->where('verification_tier', $tier);
        }

        if ($region) {
            $query->whereHas('region', fn($qb) => $qb->where('code', $region));
        }

        if ($request->query('featured')) {
            $query->where('is_featured', true);
        }

        $businesses = $query->orderByDesc('is_featured')
            ->orderByDesc('views_count')
            ->paginate(12)
            ->withQueryString();

        $industries = Industry::withCount('businesses')->where('is_active', true)->orderBy('sort_order')->get();
        $regions    = DB::table('regions')->orderBy('name_fr')->get();

        return response(
            view('pages.businesses.index', compact('lang', 'businesses', 'industries', 'regions'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function businessShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $business = Business::with(['industry', 'city', 'region', 'products.primaryImage'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $business->increment('views_count');

        return response(
            view('pages.businesses.show', compact('lang', 'business'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function productShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $product = Product::with(['images', 'documents', 'videos', 'attributes.template', 'category.sector.industry', 'originRegion', 'business.industry', 'business.region', 'business.city'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'))
            ->firstOrFail();

        $product->increment('views_count');

        $otherProducts = Product::where('business_id', $product->business_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->with('primaryImage')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return response(
            view('pages.products.show', compact('lang', 'product', 'otherProducts'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function industriesIndex(Request $request)
    {
        $lang = $this->lang($request);

        $industries = Industry::withCount('businesses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response(
            view('pages.industries.index', compact('lang', 'industries'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }
}
