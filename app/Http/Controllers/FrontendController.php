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
            'regions'    => DB::table('regions')->count(),
        ];

        $partners = \App\Modules\Cms\Models\Partner::active()->orderBy('tier')->orderBy('sort_order')->limit(9)->get();

        // Spotlight: prefer the flagship SIAC event, else the next upcoming one
        $currentEvent = \App\Modules\Events\Models\Event::published()
            ->with('industry')
            ->where('ends_at', '>=', now())
            ->where('slug', 'like', 'siac%')
            ->orderBy('starts_at')
            ->first()
            ?? \App\Modules\Events\Models\Event::published()
                ->with('industry')
                ->where('ends_at', '>=', now())
                ->orderBy('starts_at')
                ->first()
            ?? \App\Modules\Events\Models\Event::published()->with('industry')->orderByDesc('starts_at')->first();

        $upcomingEvents = \App\Modules\Events\Models\Event::published()
            ->where('ends_at', '>=', now())
            ->when($currentEvent, fn ($q) => $q->where('id', '!=', $currentEvent->id))
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return response(
            view('pages.home', compact('lang', 'industries', 'featured', 'aquaculture', 'stats', 'partners', 'currentEvent', 'upcomingEvents'))
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

        $business = Business::with(['industry', 'city', 'region', 'products.primaryImage', 'events' => fn ($q) => $q->orderByDesc('starts_at')])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $business->increment('views_count');

        // Analytics row — must never break the page
        try {
            DB::table('business_views')->insert([
                'business_id' => $business->id,
                'viewer_ip'   => $request->ip(),
                'device_type' => $this->deviceType($request),
                'referrer'    => substr((string) $request->header('referer'), 0, 255) ?: null,
                'viewed_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        return response(
            view('pages.businesses.show', compact('lang', 'business'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    private function deviceType(Request $request): string
    {
        $ua = strtolower((string) $request->userAgent());
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
        if (str_contains($ua, 'mobi') || str_contains($ua, 'android')) return 'mobile';
        return 'desktop';
    }

    public function productsIndex(Request $request)
    {
        $lang = $this->lang($request);

        $sort = in_array($request->query('sort'), ['recents', 'name']) ? $request->query('sort') : 'recents';
        $categorie = (string) $request->query('categorie', '');
        $region = (string) $request->query('region', '');

        // Live public product count stays available if the display ever switches off design numbers
        $liveCount = DB::table('products')
            ->join('businesses', 'products.business_id', '=', 'businesses.id')
            ->where('products.status', 'published')
            ->whereNull('products.deleted_at')
            ->where('businesses.status', 'published')
            ->count();

        return response(
            view('pages.products.index', compact('lang', 'sort', 'categorie', 'region', 'liveCount'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function productShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $product = Product::with([
                'images', 'documents', 'videos', 'attributes.template',
                'category.sector.industry', 'originRegion', 'harvestDates',
                'business.industry', 'business.region', 'business.city',
                'business.reviews.reviewer',
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'))
            ->firstOrFail();

        $product->increment('views_count');

        // Analytics row — must never break the page
        try {
            DB::table('product_views')->insert([
                'product_id'  => $product->id,
                'viewer_ip'   => $request->ip(),
                'device_type' => $this->deviceType($request),
                'viewed_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        $otherProducts = Product::where('business_id', $product->business_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->with('primaryImage')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('business_id', '!=', $product->business_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->whereNotNull('category_id')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'))
            ->with('primaryImage')
            ->orderByDesc('views_count')
            ->limit(4)
            ->get();

        $business = $product->business;
        $sellerStats = [
            'avg_rating'       => $business->averageRating(),
            'reviews_count'    => $business->reviewsCount(),
            'repeat_customers' => $business->repeatCustomersCount(),
            'deals_reported'   => $business->dealsReportedCount(),
        ];

        $qualityScore = $product->computeQualityScore();
        $complaintRate = $product->complaintRate();

        $siacUser  = session('siac_user');
        $myReview  = $siacUser
            ? \App\Modules\Businesses\Models\BusinessReview::where('business_id', $business->id)->where('reviewer_id', $siacUser['id'])->first()
            : null;
        $isSaved = $siacUser
            ? DB::table('saved_products')->where('user_id', $siacUser['id'])->where('product_id', $product->id)->exists()
            : false;

        return response(
            view('pages.products.show', compact(
                'lang', 'product', 'otherProducts', 'similarProducts', 'sellerStats',
                'myReview', 'isSaved', 'qualityScore', 'complaintRate'
            ))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function industriesIndex(Request $request)
    {
        $lang = $this->lang($request);

        $industries = Industry::withCount('businesses')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Publicly browsable products per industry (published product + published business)
        $productCounts = DB::table('products')
            ->join('businesses', 'products.business_id', '=', 'businesses.id')
            ->where('products.status', 'published')
            ->whereNull('products.deleted_at')
            ->where('businesses.status', 'published')
            ->groupBy('businesses.industry_id')
            ->selectRaw('businesses.industry_id, count(*) as total')
            ->pluck('total', 'industry_id');

        $sort = $request->query('sort');
        if ($sort === 'name') {
            $industries = $industries->sortBy($lang === 'fr' ? 'name_fr' : 'name_en', SORT_NATURAL | SORT_FLAG_CASE)->values();
        } elseif ($sort === 'products') {
            $industries = $industries->sortByDesc(fn ($i) => $productCounts[$i->id] ?? 0)->values();
        }

        return response(
            view('pages.industries.index', compact('lang', 'industries', 'productCounts', 'sort'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function search(Request $request)
    {
        $lang = $this->lang($request);
        $q = trim((string) $request->query('q', ''));

        $businesses = collect();
        $products = collect();

        if (mb_strlen($q) >= 2) {
            $like = "%{$q}%";

            $businesses = Business::with(['industry', 'city', 'region'])
                ->where('status', 'published')
                ->where(fn ($qb) => $qb->where('name_fr', 'like', $like)
                    ->orWhere('name_en', 'like', $like)
                    ->orWhere('tagline_fr', 'like', $like)
                    ->orWhere('description_fr', 'like', $like))
                ->orderByDesc('is_featured')
                ->limit(12)
                ->get();

            $products = Product::published()
                ->with(['primaryImage', 'category', 'business'])
                ->whereHas('business', fn ($qb) => $qb->where('status', 'published'))
                ->where(fn ($qb) => $qb->where('name_fr', 'like', $like)
                    ->orWhere('name_en', 'like', $like)
                    ->orWhere('description_fr', 'like', $like))
                ->limit(12)
                ->get();

            DB::table('search_queries')->insert([
                'query'         => $q,
                'results_count' => $businesses->count() + $products->count(),
                'ip'            => $request->ip(),
                'searched_at'   => now(),
            ]);
        }

        return response(
            view('pages.search', compact('lang', 'q', 'businesses', 'products'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }
}
