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

    /**
     * Load the full official craft taxonomy as a tree with published business and
     * product counts ROLLED UP from the leaf métiers to every ancestor (corps →
     * filière → secteur). Returns ['all' => id-keyed rows, 'children' => grouped by
     * parent_id, 'biz' => [id => count], 'prod' => [id => count]].
     */
    private function industryTree(): array
    {
        $all = DB::table('industries')->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'parent_id', 'level', 'slug', 'name_fr', 'name_en', 'image_icon', 'side_icon', 'sort_order'])
            ->keyBy('id');

        $bizDirect = DB::table('businesses')
            ->where('status', 'published')->whereNull('deleted_at')->whereNotNull('industry_id')
            ->groupBy('industry_id')->selectRaw('industry_id as iid, count(*) as c')->pluck('c', 'iid');

        $prodDirect = DB::table('products')
            ->join('businesses', 'products.business_id', '=', 'businesses.id')
            ->where('products.status', 'published')->whereNull('products.deleted_at')
            ->where('businesses.status', 'published')
            ->groupBy('businesses.industry_id')->selectRaw('businesses.industry_id as iid, count(*) as c')->pluck('c', 'iid');

        $biz = [];
        $prod = [];
        foreach ($all as $id => $n) {
            $biz[$id] = (int) ($bizDirect[$id] ?? 0);
            $prod[$id] = (int) ($prodDirect[$id] ?? 0);
        }
        // Deepest level first: each node adds its (already-summed) total to its parent.
        foreach ($all->sortByDesc('level') as $n) {
            if ($n->parent_id && isset($biz[$n->parent_id])) {
                $biz[$n->parent_id] += $biz[$n->id];
                $prod[$n->parent_id] += $prod[$n->id];
            }
        }

        return ['all' => $all, 'children' => $all->groupBy('parent_id'), 'biz' => $biz, 'prod' => $prod];
    }

    /** Every industry id in the subtree rooted at $slug (self + descendants), bounded to the 4 taxonomy levels. */
    private function descendantIndustryIds(string $slug): array
    {
        $root = DB::table('industries')->where('slug', $slug)->value('id');
        if (! $root) {
            return [];
        }
        $ids = [$root];
        $frontier = [$root];
        for ($i = 0; $i < 4 && $frontier; $i++) {
            $frontier = DB::table('industries')->whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }
        return array_values(array_unique($ids));
    }

    public function home(Request $request)
    {
        $lang = $this->lang($request);

        // SIARC "overall" mode: the whole platform becomes SIARC — land on the SIARC home.
        if (function_exists('siarcStandalone') && siarcStandalone()) {
            return redirect()->route('siarc.home', ['lang' => $lang]);
        }

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

        // Admin-editable display settings for the hero stats band
        $heroStats = DB::table('platform_settings')->pluck('value', 'key');

        $partners = \App\Modules\Cms\Models\Partner::active()->orderBy('tier')->orderBy('sort_order')->limit(9)->get();

        // Spotlight: prefer the flagship SIARC event, else the next upcoming one
        $currentEvent = \App\Modules\Events\Models\Event::published()
            ->with('industry')
            ->where('ends_at', '>=', now())
            ->where('slug', 'like', 'siarc%')
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
            view('pages.home', compact('lang', 'industries', 'featured', 'aquaculture', 'stats', 'heroStats', 'partners', 'currentEvent', 'upcomingEvents'))
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
            // Subtree-aware: filtering by a sector/filière/corps slug matches every
            // business tagged to any métier beneath it; a leaf métier matches itself.
            $ids = $this->descendantIndustryIds($industry);
            $query->whereIn('industry_id', $ids ?: [-1]);
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

        $query->withCount(['products' => fn ($qb) => $qb->where('status', 'published')]);

        if ($request->query('sort') === 'name') {
            $query->orderBy('name_fr');
        } else {
            $query->orderByDesc('is_featured')->orderByDesc('views_count');
        }

        $businesses = $query->paginate(12)->withQueryString();

        $industries = Industry::withCount('businesses')->where('is_active', true)->orderBy('sort_order')->get();
        $regions    = DB::table('regions')->orderBy('name_fr')->get();

        // Real directory stats for the hero band
        $dirStats = [
            'businesses' => Business::where('status', 'published')->count(),
            'categories' => $industries->count(),
            'regions'    => Business::where('status', 'published')->whereNotNull('region_id')->distinct()->count('region_id'),
        ];

        return response(
            view('pages.businesses.index', compact('lang', 'businesses', 'industries', 'regions', 'dirStats'))
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

        // "Produits phares" — the business's products, topped up to 6 with recent
        // public products from other vendors
        $featuredProducts = $business->products
            ->where('status', 'published')
            ->sortBy('sort_order')
            ->take(6)
            ->values();
        if ($featuredProducts->count() < 6) {
            $featuredProducts = $featuredProducts->concat(
                Product::whereNotIn('id', $featuredProducts->pluck('id')->push(0))
                    ->where('business_id', '!=', $business->id)
                    ->where('status', 'published')
                    ->whereHas('business', fn ($q) => $q->where('status', 'published'))
                    ->with(['primaryImage', 'category'])
                    ->latest()
                    ->limit(6 - $featuredProducts->count())
                    ->get()
            );
        }

        return response(
            view('pages.businesses.show', compact('lang', 'business', 'featuredProducts'))
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

        // Real, browsable products (published product + published business)
        $query = Product::with(['images', 'business.industry', 'business.region'])
            ->where('status', 'published')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'));

        if ($categorie) {
            $query->whereHas('business.industry', fn ($q) => $q->where('slug', $categorie));
        }

        if ($region) {
            $query->whereHas('business.region', fn ($q) => $q->where('name_fr', $region)->orWhere('code', $region));
        }

        $vendorTypes = array_intersect((array) $request->query('vendeur', []), ['artisan', 'entreprise', 'cooperative']);
        if ($vendorTypes) {
            $query->whereHas('business', fn ($q) => $q->whereIn('vendor_type', $vendorTypes));
        }

        if ($request->boolean('dispo')) {
            $query->where('is_available', true);
        }

        if ($sort === 'name') {
            $query->orderBy('name_fr');
        } else {
            $query->orderByDesc('created_at');
        }

        $products = $query->paginate(24)->withQueryString();

        $liveCount = Product::where('status', 'published')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'))
            ->count();

        // Sidebar categories with real per-industry product counts
        $industries = Industry::where('is_active', true)->orderBy('sort_order')->get();
        $sideCounts = DB::table('products')
            ->join('businesses', 'products.business_id', '=', 'businesses.id')
            ->where('products.status', 'published')
            ->whereNull('products.deleted_at')
            ->where('businesses.status', 'published')
            ->groupBy('businesses.industry_id')
            ->selectRaw('businesses.industry_id, count(*) as total')
            ->pluck('total', 'industry_id');

        $regions = DB::table('regions')->orderBy('name_fr')->get();

        $vendorTypeCounts = Business::where('status', 'published')
            ->groupBy('vendor_type')
            ->selectRaw('vendor_type, count(*) as total')
            ->pluck('total', 'vendor_type');

        return response(
            view('pages.products.index', compact('lang', 'sort', 'categorie', 'region', 'liveCount', 'products', 'industries', 'sideCounts', 'regions', 'vendorTypeCounts', 'vendorTypes'))
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

        // Fill "You may also like" up to 6 with recent public products when the
        // category/business yield too few
        $relatedCount = $otherProducts->count() + $similarProducts->count();
        if ($relatedCount < 6) {
            $excluded = $otherProducts->pluck('id')
                ->concat($similarProducts->pluck('id'))
                ->push($product->id);
            $similarProducts = $similarProducts->concat(
                Product::whereNotIn('id', $excluded)
                    ->where('status', 'published')
                    ->whereHas('business', fn ($q) => $q->where('status', 'published'))
                    ->with('primaryImage')
                    ->latest()
                    ->limit(6 - $relatedCount)
                    ->get()
            );
        }

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

        $tree = $this->industryTree();
        $all = $tree['all'];
        $childrenByParent = $tree['children'];
        $biz = $tree['biz'];
        $prod = $tree['prod'];

        // Current node from ?cat=<slug> (null = root = the sectors).
        $catSlug = $request->query('cat');
        $current = $catSlug ? $all->firstWhere('slug', $catSlug) : null;

        // Root view mode: 'sectors' (default, the 3 sectors) or 'filieres' (all 14
        // filières at once, grouped by sector). Ignored once you drill into a node.
        $view = $request->query('view') === 'filieres' ? 'filieres' : 'sectors';
        if ($current) {
            $children = $childrenByParent->get($current->id, collect())->sortBy('sort_order')->values();
        } elseif ($view === 'filieres') {
            $children = $all->where('level', 2)
                ->sortBy(fn ($f) => sprintf('%08d%04d', $f->parent_id, $f->sort_order))->values();
        } else {
            $children = $all->where('level', 1)->sortBy('sort_order')->values();
        }

        // Breadcrumb trail: root → current.
        $trail = [];
        for ($n = $current; $n; $n = ($n->parent_id ? $all->get($n->parent_id) : null)) {
            array_unshift($trail, $n);
        }

        // The 10 illustrated tiles kept as a "featured trades" shortcut on the root.
        $featured = $all->filter(fn ($i) => $i->image_icon)->sortBy('sort_order')->values();

        $sort = $request->query('sort');
        if ($sort === 'name') {
            $children = $children->sortBy(fn ($c) => $lang === 'fr' ? $c->name_fr : ($c->name_en ?? $c->name_fr), SORT_NATURAL | SORT_FLAG_CASE)->values();
        } elseif ($sort === 'products') {
            $children = $children->sortByDesc(fn ($c) => $prod[$c->id] ?? 0)->values();
        }

        return response(
            view('pages.industries.index', compact('lang', 'all', 'childrenByParent', 'biz', 'prod', 'current', 'children', 'trail', 'featured', 'sort', 'view'))
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
