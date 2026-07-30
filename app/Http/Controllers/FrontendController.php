<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use App\Modules\Taxonomy\Models\Industry;
use App\Support\SearchQuery;
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

        // Admin-configurable landing page: the site root can show either the
        // marketing home or the artisan directory, set via Paramètres Généraux.
        $landingPage = DB::table('platform_settings')->where('key', 'landing_page')->value('value') ?? 'directory';
        if ($landingPage === 'directory') {
            return $this->businessIndex($request);
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

        // Spotlight: next upcoming published event
        $currentEvent = \App\Modules\Events\Models\Event::published()
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

    public function collectionShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $collection = DB::table('heritage_collections')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->first();
        abort_unless($collection, 404);

        $productIds = DB::table('heritage_collection_product')
            ->where('collection_id', $collection->id)
            ->pluck('product_id');

        $products = Product::with(['images', 'business.industry', 'business.region'])
            ->whereIn('id', $productIds)
            ->where('status', 'published')
            ->whereHas('business', fn ($q) => $q->where('status', 'published'))
            ->orderByDesc('created_at')
            ->paginate(24)
            ->withQueryString();

        return response(
            view('pages.collection-show', compact('lang', 'collection', 'products'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function businessIndex(Request $request)
    {
        $lang     = $this->lang($request);
        $q        = $request->query('q');
        $industry = $request->query('industry');
        $tier     = $request->query('tier');
        $region   = $request->query('region');

        $query = Business::with(['industry', 'city', 'region', 'country'])
            ->where('status', 'published');

        if ($q) {
            SearchQuery::apply($query, $q, SearchQuery::BUSINESS_COLUMNS, SearchQuery::BUSINESS_RELATIONS);
        }

        if ($industry) {
            // Subtree-aware: filtering by a sector/filière/corps slug matches every
            // business tagged to any métier beneath it; a leaf métier matches itself.
            $ids = $this->descendantIndustryIds($industry);
            if ($ids) {
                $query->whereIn('industry_id', $ids);
            }
            // Unknown slug (e.g. legacy link): fall back to the unfiltered directory
            // rather than an empty page.
        }

        if ($tier) {
            $query->where('verification_tier', $tier);
        }

        if ($region) {
            $query->whereHas('region', fn($qb) => $qb->where('code', $region));
        }

        // Country filter, added when Cote d'Ivoire and Algeria were opened for
        // signup. Matched on the ISO code rather than the id so the URL stays
        // readable and stable: ?pays=CI, not ?pays=2.
        if ($countryCode = $request->query('pays')) {
            $query->whereHas('country', fn ($qb) => $qb->where('code', $countryCode));
        }

        if ($request->query('featured')) {
            $query->where('is_featured', true);
        }

        $query->withCount(['products' => fn ($qb) => $qb->where('status', 'published')]);

        // Relevance leads when the user typed something; the chosen sort then
        // breaks ties inside each relevance band.
        if ($q) {
            SearchQuery::orderByRelevance($query, $q, SearchQuery::BUSINESS_NAMES, SearchQuery::BUSINESS_SECONDARY);
        }

        if ($request->query('sort') === 'name') {
            $query->orderBy('name_fr');
        } else {
            $query->orderByDesc('is_featured')->orderByDesc('views_count');
        }

        $businesses = $query->paginate(12)->withQueryString();

        $industries = Industry::withCount('businesses')->where('is_active', true)->orderBy('sort_order')->get();
        $regions    = DB::table('regions')->orderBy('name_fr')->get();
        // Only countries this directory can actually show something for. Signup
        // accepts buyers from all 212 countries, but a filter offering 209 that
        // return an empty page is a worse directory, not a bigger one.
        $countries  = \App\Modules\Taxonomy\Models\Country::active()
            ->whereHas('businesses', fn ($qb) => $qb->where('status', 'published'))
            ->get();

        // Real directory stats for the hero band.
        //
        // Every figure here counts the *same population as the listing below it*
        // — published businesses — so a visitor can never read a number the page
        // then fails to show them. The businesses table holds 515 rows, but 512
        // are unclaimed SIARC imports sitting in `draft`; counting those would
        // advertise a directory that does not exist yet. `regions` is therefore
        // "regions with at least one listed business", not "regions in Cameroon"
        // (that is 10, and lives in the regions table). `verified` is the count
        // of listed businesses that actually carry a verified/certified tier —
        // it replaces a hardcoded "100% Authentiques", which measured nothing.
        $dirStats = [
            'businesses' => Business::where('status', 'published')->count(),
            'categories' => $industries->count(),
            'regions'    => Business::where('status', 'published')->whereNotNull('region_id')->distinct()->count('region_id'),
            'verified'   => Business::where('status', 'published')->whereIn('verification_tier', ['verified', 'certified'])->count(),
        ];

        $vendorTypeCounts = $this->vendorTypeCounts();

        return response(
            view('pages.businesses.index', compact('lang', 'businesses', 'industries', 'regions', 'countries', 'dirStats', 'vendorTypeCounts'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function businessShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        // 509 of the 512 artisan profiles are unpublished SIARC imports awaiting a
        // claim, and the admin console links to every one of them. Sending an
        // administrator who is reviewing a claim to a 404 tells them nothing and
        // reads as a broken console. An admin — and only an admin — may therefore
        // open an unpublished profile as a preview; the view carries a banner
        // saying so, so a preview is never mistaken for a live page.
        $isAdminPreview = ! empty(session('siac_user')['is_admin']);

        $business = Business::with(['industry', 'city', 'region', 'products.primaryImage', 'events' => fn ($q) => $q->orderByDesc('starts_at')])
            ->where('slug', $slug)
            ->when(! $isAdminPreview, fn ($q) => $q->where('status', 'published'))
            ->firstOrFail();

        // A preview is not a visit. Counting it would inflate the artisan's own
        // view count with staff traffic and put staff IPs in the analytics table.
        $isPreview = $isAdminPreview && $business->status !== 'published';

        if (! $isPreview) {
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

        // Real business stats for the hero band and tab labels
        $publishedProductsCount = $business->products->where('status', 'published')->count();
        $ordersCount = DB::table('purchase_orders as po')
            ->join('quote_proposals as qp', 'qp.id', '=', 'po.quote_proposal_id')
            ->join('quote_requests as qr', 'qr.id', '=', 'qp.quote_request_id')
            ->where('qr.business_id', $business->id)
            ->count();
        $reviewsCount = $business->reviews()->count();
        $satisfiedPct = $reviewsCount
            ? round($business->reviews()->where('rating', '>=', 4)->count() / $reviewsCount * 100)
            : null;
        $tenureYears = $business->created_at ? max(0, $business->created_at->diffInYears(now())) : null;

        return response(
            view('pages.businesses.show', compact(
                'lang', 'business', 'featuredProducts',
                'publishedProductsCount', 'ordersCount', 'satisfiedPct', 'tenureYears',
                'isPreview'
            ))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    /**
     * Published-business counts per vendor type, keyed by vendor_type. Backs the
     * identical "type de profil / vendeur" facet on the directory and the product
     * listing, so both read the same numbers.
     */
    private function vendorTypeCounts()
    {
        return Business::where('status', 'published')
            ->groupBy('vendor_type')
            ->selectRaw('vendor_type, count(*) as total')
            ->pluck('total', 'vendor_type');
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
        $q = trim((string) $request->query('q', ''));

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

        // Same treatment as the directory so a q= link from anywhere on the
        // platform lands on the same ranked result set.
        if ($q !== '') {
            SearchQuery::apply($query, $q, SearchQuery::PRODUCT_COLUMNS, SearchQuery::PRODUCT_RELATIONS);
            SearchQuery::orderByRelevance($query, $q, SearchQuery::PRODUCT_NAMES, SearchQuery::PRODUCT_SECONDARY);
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

        $vendorTypeCounts = $this->vendorTypeCounts();

        return response(
            view('pages.products.index', compact('lang', 'sort', 'categorie', 'region', 'q', 'liveCount', 'products', 'industries', 'sideCounts', 'regions', 'vendorTypeCounts', 'vendorTypes'))
        )->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function productShow(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        // A published product whose business is still draft is a real, common
        // state — a vendor's business starts in draft and they can publish a
        // product before publishing the business itself. The public correctly
        // gets a 404 for it: the platform never shows a product for a business
        // it has not itself confirmed. But the vendor's own dashboard links
        // straight to this URL under "View public page", and a bare 404 there
        // reads as the platform being broken rather than as the true state
        // (product live, business not yet). The vendor — and an admin, same
        // preview pattern as FrontendController::businessShow — may therefore
        // open it as an explicitly-labelled preview; nobody else can.
        $siacUser = session('siac_user');
        $isAdminPreview = ! empty($siacUser['is_admin']);

        $product = Product::with([
                'images', 'documents', 'videos', 'attributes.template',
                'category.sector.industry', 'originRegion', 'harvestDates',
                'business.industry', 'business.region', 'business.city',
                'business.reviews.reviewer',
            ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $isOwnerPreview = $siacUser && $product->business && $product->business->user_id === $siacUser['id'];
        $isPreview = ($isAdminPreview || $isOwnerPreview) && $product->business?->status !== 'published';

        // Everyone else keeps the original rule exactly: product AND business
        // both published, or a 404 — never leak an unpublished business's
        // product to a visitor who isn't its owner or an admin.
        if (! $isPreview && $product->business?->status !== 'published') {
            abort(404);
        }

        if (! $isPreview) {
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
        $reviewsCount = $business->reviewsCount();
        $sellerStats = [
            'avg_rating'       => $business->averageRating(),
            'reviews_count'    => $reviewsCount,
            'repeat_customers' => $business->repeatCustomersCount(),
            'deals_reported'   => $business->dealsReportedCount(),
            // The vendor strip on this page used to show a fixed 156 products /
            // 98% / 2 yrs for every artisan. Same figures as businesses/show.
            'products_count'   => $business->products()->where('status', 'published')->count(),
            'satisfied_pct'    => $reviewsCount
                ? (int) round($business->reviews()->where('status', 'published')->where('rating', '>=', 4)->count() / $reviewsCount * 100)
                : null,
            'tenure_years'     => $business->created_at ? max(0, (int) $business->created_at->diffInYears(now())) : null,
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
                'myReview', 'isSaved', 'qualityScore', 'complaintRate', 'isPreview'
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

        // Root view mode: 'sectors' (default, the 3 sectors) or 'filieres' (every
        // filière at once, grouped by sector). Ignored once you drill into a node.
        $view = $request->query('view') === 'filieres' ? 'filieres' : 'sectors';
        if ($current) {
            $children = $childrenByParent->get($current->id, collect())->sortBy('sort_order')->values();
        } elseif ($view === 'filieres') {
            $children = $all->where('level', 2)
                ->sortBy(fn ($f) => sprintf('%08d%04d', $f->parent_id, $f->sort_order))->values();
        } else {
            // Craft sectors only — those with filières beneath them. The other
            // level-1 rows exist to parent the product-category tree, or are
            // generic business sectors left over on older databases; either way
            // they hold no artisans, so listing them offers a dead end. Same
            // rule as the nav in AppServiceProvider.
            $withChildren = $childrenByParent->keys()->flip();
            $children = $all->where('level', 1)
                ->filter(fn ($s) => $withChildren->has($s->id))
                ->sortBy('sort_order')->values();
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
            $businessQuery = Business::with(['industry', 'city', 'region'])
                ->where('status', 'published');
            SearchQuery::apply($businessQuery, $q, SearchQuery::BUSINESS_COLUMNS, SearchQuery::BUSINESS_RELATIONS);
            SearchQuery::orderByRelevance($businessQuery, $q, SearchQuery::BUSINESS_NAMES, SearchQuery::BUSINESS_SECONDARY);
            $businesses = $businessQuery->orderByDesc('is_featured')->limit(12)->get();

            $productQuery = Product::published()
                ->with(['primaryImage', 'category', 'business'])
                ->whereHas('business', fn ($qb) => $qb->where('status', 'published'));
            SearchQuery::apply($productQuery, $q, SearchQuery::PRODUCT_COLUMNS, SearchQuery::PRODUCT_RELATIONS);
            SearchQuery::orderByRelevance($productQuery, $q, SearchQuery::PRODUCT_NAMES, SearchQuery::PRODUCT_SECONDARY);
            $products = $productQuery->limit(12)->get();

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
