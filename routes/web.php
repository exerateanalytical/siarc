<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────
function webUser(): ?object
{
    $u = session('auth_user');
    return $u ? (object) $u : null;
}

function requireAuth(Request $request)
{
    if (!session('auth_user')) {
        return redirect('/login?next=' . urlencode($request->fullUrl()));
    }
    return null;
}

/**
 * Create an in-app notification for a recipient. No-ops when the recipient is
 * empty or is the actor themselves (don't notify people about their own action).
 */
/** Map a notification type to a preference category (null = always send, not user-controllable). */
function notificationCategory(string $type): ?string
{
    $map = [
        'message' => 'messages',
        'job_application_received' => 'job_updates', 'application_status' => 'job_updates',
        'application_withdrawn' => 'job_updates', 'job_alert' => 'job_updates',
        'tender_bid' => 'marketplace', 'invest_interest' => 'marketplace', 'supplier_review' => 'marketplace',
        'federation_join' => 'marketplace', 'event_registration' => 'marketplace', 'community_join' => 'marketplace',
        'innovation_interest' => 'marketplace', 'logistics_bid' => 'marketplace', 'asset_inquiry' => 'marketplace',
    ];
    return $map[$type] ?? null;
}

function notifyUser(?string $userId, string $type, string $titleEn, string $bodyEn, ?string $actionUrl = null, ?string $titleFr = null, ?string $bodyFr = null): void
{
    if (!$userId) return;
    $actor = session('auth_user')['id'] ?? null;
    if ($actor && (string) $actor === (string) $userId) return;
    // respect the recipient's notification preferences (default = all enabled)
    $category = notificationCategory($type);
    if ($category) {
        $pref = DB::table('notification_preferences')->where('user_id', $userId)->value($category);
        if ($pref !== null && (int) $pref === 0) return;
    }
    DB::table('notifications')->insert([
        'id'         => Str::uuid()->toString(),
        'user_id'    => $userId,
        'type'       => $type,
        'title_en'   => $titleEn,
        'body_en'    => $bodyEn,
        'title_fr'   => $titleFr ?: $titleEn,
        'body_fr'    => $bodyFr ?: $bodyEn,
        'action_url' => $actionUrl,
        'read_at'    => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/** Notify all active owners/admins of a company. */
function notifyCompanyOwners(?string $companyId, string $type, string $titleEn, string $bodyEn, ?string $actionUrl = null, ?string $titleFr = null, ?string $bodyFr = null): void
{
    if (!$companyId) return;
    $owners = DB::table('company_users')->where('company_id', $companyId)
        ->where('is_active', 1)->whereIn('role', ['owner', 'admin'])->pluck('user_id');
    foreach ($owners as $uid) {
        notifyUser($uid, $type, $titleEn, $bodyEn, $actionUrl, $titleFr, $bodyFr);
    }
}

// ─────────────────────────────────────────────
// SIAC Platform — API landing
// ─────────────────────────────────────────────
use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/galerie/entreprises', [FrontendController::class, 'businessIndex'])->name('businesses.index');
Route::get('/galerie/entreprises/{slug}', [FrontendController::class, 'businessShow'])->name('businesses.show');
Route::get('/galerie/secteurs', [FrontendController::class, 'industriesIndex'])->name('industries.index');
Route::get('/galerie/produits/{slug}', [FrontendController::class, 'productShow'])->name('products.show');

use App\Http\Controllers\MessagingWebController;

Route::post('/galerie/messages', [MessagingWebController::class, 'send'])->name('messages.send');
Route::get('/tableau-de-bord/messages', [MessagingWebController::class, 'inbox'])->name('messages.inbox');
Route::get('/tableau-de-bord/messages/{id}', [MessagingWebController::class, 'thread'])->name('messages.thread');
Route::post('/tableau-de-bord/messages/{id}/repondre', [MessagingWebController::class, 'reply'])->name('messages.reply');

use App\Http\Controllers\ReviewWebController;

Route::post('/galerie/avis', [ReviewWebController::class, 'store'])->name('reviews.store');
Route::post('/tableau-de-bord/messages/{id}/conclure', [ReviewWebController::class, 'markDeal'])->name('messages.mark-deal');

use App\Http\Controllers\ProductActionsWebController;

Route::post('/galerie/produits/{slug}/sauvegarder', [ProductActionsWebController::class, 'toggleSave'])->name('products.toggle-save');
Route::post('/galerie/produits/{slug}/signaler', [ProductActionsWebController::class, 'report'])->name('products.report');

use App\Http\Controllers\BusinessWebController;

Route::get('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'create'])->name('business.create');
Route::post('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'store'])->name('business.store');
Route::get('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'edit'])->name('business.edit');
Route::post('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'update'])->name('business.update');
Route::get('/api-interne/villes/{regionId}', [BusinessWebController::class, 'citiesForRegion'])->name('business.cities-for-region');

use App\Http\Controllers\ProductWebController;

Route::get('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'create'])->name('products.web-create');
Route::post('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'store'])->name('products.web-store');
Route::get('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'edit'])->name('products.web-edit');
Route::post('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'update'])->name('products.web-update');
Route::post('/tableau-de-bord/produits/{slug}/images/{imageId}/supprimer', [ProductWebController::class, 'destroyImage'])->name('products.web-delete-image');

// ─────────────────────────────────────────────
// Legacy — Company Directory (disabled)
// ─────────────────────────────────────────────
// Legacy root route removed — replaced by SIAC API landing above

// ─────────────────────────────────────────────
// Offerings listing
// ─────────────────────────────────────────────
Route::get('/offerings', function (Request $request) {
    $search  = $request->get('q', '');
    $type    = $request->get('type', '');
    $status  = $request->get('status', '');
    $perPage = 12;
    $page    = max(1, (int) $request->get('page', 1));

    $query = DB::table('share_offerings')
        ->join('companies', 'share_offerings.company_id', '=', 'companies.id')
        ->join('regions', 'companies.region_id', '=', 'regions.id')
        ->leftJoin('cities', 'companies.city_id', '=', 'cities.id')
        ->whereNull('share_offerings.deleted_at')
        ->whereNull('companies.deleted_at')
        ->select(
            'share_offerings.*',
            'companies.name as company_name',
            'companies.trade_name as company_trade',
            'companies.slug as company_slug',
            'companies.verification_status as company_vs',
            'regions.name_en as region_name',
            'cities.name_en as city_name'
        );

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('share_offerings.title_en', 'like', "%{$search}%")
              ->orWhere('companies.name', 'like', "%{$search}%");
        });
    }
    if ($type)   $query->where('share_offerings.instrument_type', $type);
    if ($status) $query->where('share_offerings.status', $status);

    $total      = $query->count();
    $offerings  = $query->orderByRaw("FIELD(share_offerings.status,'open','cmf_approved','pending_cmf','closed','cancelled','completed','paused','draft')")
                        ->orderByDesc('share_offerings.target_amount')
                        ->offset(($page - 1) * $perPage)->limit($perPage)->get();
    $totalPages = (int) ceil($total / $perPage);
    $activeTab  = 'offerings';

    return view('offerings', compact('offerings','search','type','status','total','page','totalPages','activeTab'));
})->name('offerings.index');

// ─────────────────────────────────────────────
// Offering detail
// ─────────────────────────────────────────────
Route::get('/offerings/{id}', function (string $id) {
    $offering = DB::table('share_offerings')
        ->join('companies', 'share_offerings.company_id', '=', 'companies.id')
        ->join('regions', 'companies.region_id', '=', 'regions.id')
        ->leftJoin('cities', 'companies.city_id', '=', 'cities.id')
        ->whereNull('share_offerings.deleted_at')
        ->where('share_offerings.id', $id)
        ->select(
            'share_offerings.*',
            'companies.name as company_name',
            'companies.trade_name as company_trade',
            'companies.slug as company_slug',
            'companies.verification_status as company_vs',
            'regions.name_en as region_name',
            'cities.name_en as city_name'
        )->first();

    if (!$offering) abort(404);

    $pledgeCount = DB::table('investment_pledges')
        ->where('offering_id', $offering->id)
        ->whereIn('status', ['confirmed','pending_payment'])
        ->count();

    $faqs = DB::table('offering_faqs')
        ->where('offering_id', $offering->id)
        ->orderBy('sort_order')->get();

    $updates = DB::table('offering_updates')
        ->where('offering_id', $offering->id)
        ->whereNotNull('published_at')
        ->orderByDesc('published_at')->get();

    $activeTab = 'offerings';
    return view('offering', compact('offering','pledgeCount','activeTab','faqs','updates'));
})->name('offering.show');

// ─────────────────────────────────────────────
// Invest / Pledge flow
// ─────────────────────────────────────────────
Route::get('/invest/{id}', function (string $id, Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $offering = DB::table('share_offerings')
        ->join('companies', 'share_offerings.company_id', '=', 'companies.id')
        ->whereNull('share_offerings.deleted_at')
        ->where('share_offerings.id', $id)
        ->where('share_offerings.status', 'open')
        ->select('share_offerings.*', 'companies.name as company_name', 'companies.slug as company_slug')
        ->first();

    if (!$offering) abort(404);

    $user            = webUser();
    $investorProfile = DB::table('investor_profiles')->where('user_id', $user->id)->first();
    $activeTab       = 'offerings';
    return view('invest', compact('offering', 'activeTab', 'investorProfile'));
})->name('invest.form');

Route::post('/invest/{id}', function (string $id, Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();
    if (!DB::table('investor_profiles')->where('user_id', $user->id)->exists()) {
        return redirect('/investor-profile')
            ->with('error', 'Please complete your investor profile before investing.');
    }

    $offering = DB::table('share_offerings')
        ->whereNull('deleted_at')
        ->where('id', $id)
        ->where('status', 'open')
        ->first();

    if (!$offering) abort(404);

    $data = $request->validate([
        'amount'         => ['required', 'numeric', 'min:' . $offering->min_investment],
        'payment_method' => ['required', 'in:mtn_momo,orange_money,bank_transfer'],
    ]);

    $user   = webUser();
    $shares = floor($data['amount'] / $offering->share_price);

    $pledgeId = Str::uuid()->toString();
    DB::table('investment_pledges')->insert([
        'id'              => $pledgeId,
        'investor_id'     => $user->id,
        'offering_id'     => $id,
        'amount'          => $data['amount'],
        'shares_requested'=> $shares,
        'status'          => 'pending_payment',
        'payment_method'  => $data['payment_method'],
        'expires_at'      => now()->addHours(24),
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    return redirect('/pay/' . $pledgeId)->with('success', 'Pledge created. Complete your payment below.');
})->name('invest.submit');

// ─────────────────────────────────────────────
// Payment page
// ─────────────────────────────────────────────
Route::get('/pay/{pledgeId}', function (string $pledgeId, Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user   = webUser();
    $pledge = DB::table('investment_pledges')
        ->join('share_offerings', 'investment_pledges.offering_id', '=', 'share_offerings.id')
        ->join('companies', 'share_offerings.company_id', '=', 'companies.id')
        ->where('investment_pledges.id', $pledgeId)
        ->where('investment_pledges.investor_id', $user->id)
        ->select('investment_pledges.*', 'share_offerings.title_en', 'companies.name as company_name', 'companies.slug as company_slug')
        ->first();

    if (!$pledge) abort(404);
    $activeTab = 'offerings';
    return view('pay', compact('pledge','activeTab'));
})->name('pay');

// ─────────────────────────────────────────────
// Company detail
// ─────────────────────────────────────────────
Route::get('/companies/{slug}', function (string $slug, Request $request) {
    $lang = session('lang', 'en');

    $company = DB::table('companies')
        ->join('regions', 'companies.region_id', '=', 'regions.id')
        ->leftJoin('cities', 'companies.city_id', '=', 'cities.id')
        ->whereNull('companies.deleted_at')
        ->where('companies.slug', $slug)
        ->select('companies.*', 'regions.name_en as region_name', 'cities.name_en as city_name')
        ->first();

    if (!$company) abort(404);

    DB::table('companies')->where('id', $company->id)->increment('view_count');

    $offerings = DB::table('share_offerings')
        ->whereNull('deleted_at')->where('company_id', $company->id)
        ->whereIn('status', ['open','cmf_approved','pending_cmf'])
        ->orderByRaw("FIELD(status,'open','cmf_approved','pending_cmf')")->get();

    $allOfferings = DB::table('share_offerings')
        ->whereNull('deleted_at')->where('company_id', $company->id)
        ->orderByDesc('created_at')->get();

    $documents = DB::table('company_documents')
        ->whereNull('deleted_at')->where('company_id', $company->id)
        ->where('visibility', 'public')->orderByDesc('created_at')->get();

    $members = DB::table('company_users')
        ->join('users', 'company_users.user_id', '=', 'users.id')
        ->where('company_users.company_id', $company->id)
        ->where('company_users.is_active', true)
        ->select('company_users.*', 'users.first_name', 'users.last_name', 'users.avatar_url')
        ->get();

    $verification = DB::table('verification_applications')
        ->join('verification_tiers', 'verification_applications.target_tier_id', '=', 'verification_tiers.id')
        ->where('verification_applications.company_id', $company->id)
        ->select('verification_applications.*', 'verification_tiers.name as tier_name')
        ->orderByDesc('verification_applications.created_at')->get();

    $reviews = DB::table('company_reviews')
        ->join('users', 'company_reviews.user_id', '=', 'users.id')
        ->whereNull('company_reviews.deleted_at')
        ->where('company_reviews.company_id', $company->id)
        ->where('company_reviews.is_approved', true)
        ->select('company_reviews.*', 'users.first_name', 'users.last_name')
        ->orderByDesc('company_reviews.created_at')->limit(10)->get();

    $related = DB::table('companies')
        ->join('regions', 'companies.region_id', '=', 'regions.id')
        ->whereNull('companies.deleted_at')->where('companies.status', 'active')
        ->where('companies.id', '!=', $company->id)
        ->where('companies.region_id', $company->region_id)
        ->select('companies.*', 'regions.name_en as region_name', 'cities.name_en as city_name')
        ->leftJoin('cities', 'companies.city_id', '=', 'cities.id')
        ->limit(4)->get();

    $industries = DB::table('company_industry')
        ->join('industries', 'company_industry.industry_id', '=', 'industries.id')
        ->where('company_industry.company_id', $company->id)
        ->select('industries.name_en', 'company_industry.is_primary')
        ->get();

    $products = DB::table('company_products')
        ->where('company_id', $company->id)
        ->where('is_active', true)
        ->orderBy('id')->get();

    // ── Cross-module activity (Phases 2-11) surfaced on the company hub ──
    $cid = $company->id;
    $actTenders     = DB::table('tenders')->where('company_id',$cid)->whereNull('deleted_at')
        ->orderByDesc('created_at')->limit(5)->get(['id','title','status','deadline']);
    $actInvest      = DB::table('invest_seeks')->where('company_id',$cid)->whereNull('deleted_at')
        ->orderByDesc('created_at')->limit(5)->get(['id','title','type','amount_sought','currency','status']);
    $actEvents      = DB::table('events')->where('organizer_company_id',$cid)
        ->orderByDesc('start_date')->limit(5)->get(['id','title','start_date','format']);
    $actInnovation  = DB::table('innovation_projects')->where('company_id',$cid)
        ->orderByDesc('created_at')->limit(5)->get(['slug','title','type','stage']);
    $actAssets      = DB::table('shared_assets')->where('company_id',$cid)->where('status','active')
        ->orderByDesc('created_at')->limit(5)->get(['slug','title','category','availability']);
    $actLogistics   = DB::table('logistics_listings')->where('company_id',$cid)->where('status','open')
        ->orderByDesc('created_at')->limit(5)->get(['id','title','type','origin_city','destination_city']);
    $actOpps        = DB::table('collabcam_opportunities')->where('company_id',$cid)->whereNull('deleted_at')
        ->orderByDesc('created_at')->limit(5)->get(['id','title_en','type','status']);
    $actFederations = DB::table('federation_members')
        ->join('federations','federation_members.federation_id','=','federations.id')
        ->where('federation_members.company_id',$cid)->where('federation_members.status','active')
        ->whereNull('federations.deleted_at')
        ->orderByDesc('federation_members.created_at')->limit(6)->get(['federations.slug','federations.name','federation_members.role']);
    $actEsg         = DB::table('esg_reports')->where('company_id',$cid)->whereIn('status',['published','verified'])
        ->orderByDesc('year')->first(['year','overall_esg_score','env_score','social_score','governance_score']);
    $supplierRev    = DB::table('supplier_reviews')->where('supplier_company_id',$cid)->where('status','published');
    $supplierReviewStats = ['count'=>(clone $supplierRev)->count(),'avg'=>(clone $supplierRev)->avg('score_overall')];
    $healthScore    = DB::table('company_health_scores')->where('company_id',$cid)->first();
    $activityCount  = $actTenders->count()+$actInvest->count()+$actEvents->count()+$actInnovation->count()
        +$actAssets->count()+$actLogistics->count()+$actOpps->count()+$actFederations->count()+($actEsg?1:0);

    // ── Jobs & Branches on the company profile ──
    $jobs = DB::table('job_postings')->where('company_id',$cid)->whereNull('deleted_at')
        ->orderByRaw("FIELD(status,'open','draft','closed')")->orderByDesc('created_at')
        ->get(['id','title_en','title_fr','location','type','department','salary_min','salary_max','currency','deadline','status']);
    $branches = DB::table('company_branches')->where('company_id',$cid)->where('status','active')
        ->orderByDesc('is_primary')->orderBy('name')->get();
    // Salary benchmarks reported at this company
    $companySalaries = DB::table('salary_reports')->where('company_id',$cid)->where('status','published')
        ->select('job_slug','job_title', DB::raw('COUNT(*) as reports'), DB::raw('ROUND(AVG(annual_amount)/12) as monthly_avg'))
        ->groupBy('job_slug','job_title')->orderByDesc('monthly_avg')->limit(8)->get();

    $authUser = session('auth_user');
    $inWatchlist = false;
    if ($authUser) {
        $watchlistEntry = DB::table('watchlist_items')
            ->join('watchlists', 'watchlist_items.watchlist_id', '=', 'watchlists.id')
            ->where('watchlists.investor_id', $authUser['id'])
            ->where('watchlist_items.company_id', $company->id)
            ->first();
        $inWatchlist = (bool)$watchlistEntry;
    }

    $tab = $request->get('tab', 'about');
    $activeTab = 'companies';

    return view('company', compact(
        'company','offerings','allOfferings','documents','members',
        'verification','reviews','related','industries','tab','lang','activeTab',
        'products','inWatchlist',
        'actTenders','actInvest','actEvents','actInnovation','actAssets','actLogistics',
        'actOpps','actFederations','actEsg','supplierReviewStats','healthScore','activityCount',
        'jobs','branches','companySalaries'
    ));
})->name('company.show');

// ─────────────────────────────────────────────
// Global search — spans every module
// ─────────────────────────────────────────────
Route::get('/search', function (Request $request) {
    $q = trim($request->get('q', ''));
    $groups = [];
    if (strlen($q) >= 2) {
        $like = "%$q%";
        $groups = [
            ['Companies', '🏢', DB::table('companies')->whereNull('deleted_at')->where('name','like',$like)
                ->limit(6)->get(['name as label','slug'])->map(fn($r)=>['label'=>$r->label,'url'=>'/companies/'.$r->slug,'meta'=>''])],
            ['Tenders', '🏗️', DB::table('tenders')->whereNull('deleted_at')->where('title','like',$like)
                ->limit(6)->get(['title as label','id','status'])->map(fn($r)=>['label'=>$r->label,'url'=>'/tenders/'.$r->id,'meta'=>ucfirst($r->status)])],
            ['Investment', '💰', DB::table('invest_seeks')->whereNull('deleted_at')->where('title','like',$like)
                ->limit(6)->get(['title as label','id'])->map(fn($r)=>['label'=>$r->label,'url'=>'/invest-hub/'.$r->id,'meta'=>''])],
            ['Events', '📅', DB::table('events')->where('title','like',$like)
                ->limit(6)->get(['title as label','id','start_date'])->map(fn($r)=>['label'=>$r->label,'url'=>'/events/'.$r->id,'meta'=>date('d M Y',strtotime($r->start_date))])],
            ['Knowledge', '📚', DB::table('knowledge_resources')->where('is_published',1)->where('title','like',$like)
                ->limit(6)->get(['title as label','slug','category'])->map(fn($r)=>['label'=>$r->label,'url'=>'/knowledge/'.$r->slug,'meta'=>ucfirst($r->category)])],
            ['Innovation', '💡', DB::table('innovation_projects')->where('title','like',$like)
                ->limit(6)->get(['title as label','slug','type'])->map(fn($r)=>['label'=>$r->label,'url'=>'/innovation/'.$r->slug,'meta'=>ucfirst($r->type)])],
            ['Assets', '🏭', DB::table('shared_assets')->where('status','active')->where('title','like',$like)
                ->limit(6)->get(['title as label','slug','category'])->map(fn($r)=>['label'=>$r->label,'url'=>'/assets/'.$r->slug,'meta'=>ucfirst($r->category)])],
            ['Logistics', '🚚', DB::table('logistics_listings')->where('status','open')->where('title','like',$like)
                ->limit(6)->get(['title as label','id','origin_city','destination_city'])->map(fn($r)=>['label'=>$r->label,'url'=>'/logistics/'.$r->id,'meta'=>$r->origin_city.' → '.$r->destination_city])],
            ['Federations', '🏛️', DB::table('federations')->whereNull('deleted_at')->where('name','like',$like)
                ->limit(6)->get(['name as label','slug'])->map(fn($r)=>['label'=>$r->label,'url'=>'/federations/'.$r->slug,'meta'=>''])],
            ['Communities', '💬', DB::table('communities')->where('status','active')->where('name','like',$like)
                ->limit(6)->get(['name as label','slug'])->map(fn($r)=>['label'=>$r->label,'url'=>'/communities/'.$r->slug,'meta'=>''])],
            ['Associations', '🤝', DB::table('associations')->where('is_active',1)->where('name_en','like',$like)
                ->limit(6)->get(['name_en as label','slug'])->map(fn($r)=>['label'=>$r->label,'url'=>'/associations/'.$r->slug,'meta'=>''])],
            ['Compliance', '🛡️', DB::table('compliance_requirements')->where('is_published',1)->where('title','like',$like)
                ->limit(6)->get(['title as label','slug','authority'])->map(fn($r)=>['label'=>$r->label,'url'=>'/compliance/'.$r->slug,'meta'=>$r->authority])],
            ['Business Cards', '📇', DB::table('digital_cards')->where('is_public',1)->where('display_name','like',$like)
                ->limit(6)->get(['display_name as label','slug','company_name'])->map(fn($r)=>['label'=>$r->label,'url'=>'/card/'.$r->slug,'meta'=>$r->company_name])],
        ];
    }
    $totalResults = collect($groups)->sum(fn($g) => $g[2]->count());
    return view('search', compact('q', 'groups', 'totalResults'));
})->name('search');

// ─────────────────────────────────────────────
// Language toggle
// ─────────────────────────────────────────────
Route::get('/lang/{locale}', function (string $locale, Request $request) {
    if (in_array($locale, ['en', 'fr'])) {
        session(['lang' => $locale]);
    }
    return redirect()->back()->withInput();
})->name('lang.switch');

// ─────────────────────────────────────────────
// Search autocomplete (JSON) — powers the nav search dropdown
// ─────────────────────────────────────────────
Route::get('/search/suggest', function (Request $request) {
    $q = trim($request->get('q', ''));
    if (strlen($q) < 2) return response()->json(['results' => []]);
    $like = "%$q%";
    $results = collect();
    $sources = [
        ['building-2', 'Company',     DB::table('companies')->whereNull('deleted_at')->where('name','like',$like)->limit(4)->get(['name as label','slug']), '/companies/'],
        ['file-text', 'Tender',       DB::table('tenders')->whereNull('deleted_at')->where('title','like',$like)->limit(3)->get(['title as label','id as slug']), '/tenders/'],
        ['lightbulb', 'Innovation',   DB::table('innovation_projects')->where('title','like',$like)->limit(3)->get(['title as label','slug']), '/innovation/'],
        ['calendar', 'Event',         DB::table('events')->where('title','like',$like)->limit(3)->get(['title as label','id as slug']), '/events/'],
        ['book-open', 'Knowledge',    DB::table('knowledge_resources')->where('is_published',1)->where('title','like',$like)->limit(3)->get(['title as label','slug']), '/knowledge/'],
        ['package', 'Asset',          DB::table('shared_assets')->where('status','active')->where('title','like',$like)->limit(3)->get(['title as label','slug']), '/assets/'],
        ['truck', 'Logistics',        DB::table('logistics_listings')->where('status','open')->where('title','like',$like)->limit(2)->get(['title as label','id as slug']), '/logistics/'],
        ['landmark', 'Federation',    DB::table('federations')->whereNull('deleted_at')->where('name','like',$like)->limit(2)->get(['name as label','slug']), '/federations/'],
    ];
    foreach ($sources as [$icon, $type, $rows, $prefix]) {
        foreach ($rows as $r) {
            $results->push(['icon' => $icon, 'type' => $type, 'label' => $r->label, 'url' => $prefix.$r->slug]);
        }
    }
    return response()->json(['results' => $results->take(10)->values()]);
})->name('search.suggest');

// Unread notification count (JSON) — polled by the nav bell
Route::get('/notifications/count', function (Request $request) {
    $user = webUser();
    if (!$user) return response()->json(['unread' => 0]);
    $unread = DB::table('notifications')->where('user_id', $user->id)->whereNull('read_at')->count();
    return response()->json(['unread' => $unread]);
})->name('notifications.count');

// ─────────────────────────────────────────────
// Dashboard (authenticated)
// ─────────────────────────────────────────────
Route::get('/dashboard', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();

    $pledges = DB::table('investment_pledges')
        ->join('share_offerings', 'investment_pledges.offering_id', '=', 'share_offerings.id')
        ->join('companies', 'share_offerings.company_id', '=', 'companies.id')
        ->where('investment_pledges.investor_id', $user->id)
        ->select('investment_pledges.*', 'share_offerings.title_en', 'companies.name as company_name', 'companies.slug as company_slug')
        ->orderByDesc('investment_pledges.created_at')
        ->get();

    $notifications = DB::table('notifications')
        ->where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->limit(10)
        ->get();

    $unreadCount = DB::table('notifications')
        ->where('user_id', $user->id)
        ->whereNull('read_at')
        ->count();

    $watchlist = DB::table('watchlist_items')
        ->join('watchlists', 'watchlist_items.watchlist_id', '=', 'watchlists.id')
        ->join('companies', 'watchlist_items.company_id', '=', 'companies.id')
        ->where('watchlists.investor_id', $user->id)
        ->whereNull('companies.deleted_at')
        ->select('companies.id', 'companies.name', 'companies.slug', 'companies.verification_status', 'companies.view_count')
        ->limit(6)->get();

    $totalInvested = $pledges->whereIn('status', ['confirmed','completed'])->sum('amount');

    // ── Business Hub: the user's cross-module footprint (Phases 2-11) ──
    $myCompany = DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$user->id)->where('company_users.is_active',true)
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name','companies.slug')->first();

    $hub = [
        'partners' => DB::table('partners')->where('user_id',$user->id)->count(),
        'cards'    => DB::table('digital_cards')->where('user_id',$user->id)->count(),
        'cardSlug' => DB::table('digital_cards')->where('user_id',$user->id)->orderByDesc('created_at')->value('slug'),
    ];
    $myCompanyHub = null;
    if ($myCompany) {
        $cid = $myCompany->id;
        $trkTotal = DB::table('compliance_tracker')->where('company_id',$cid)->count();
        $trkOk = DB::table('compliance_tracker')->where('company_id',$cid)->where('status','compliant')->count();
        $myCompanyHub = [
            'company'    => $myCompany,
            'tenders'    => DB::table('tenders')->where('company_id',$cid)->whereNull('deleted_at')->count(),
            'invest'     => DB::table('invest_seeks')->where('company_id',$cid)->count(),
            'events'     => DB::table('events')->where('organizer_company_id',$cid)->count(),
            'innovation' => DB::table('innovation_projects')->where('company_id',$cid)->count(),
            'assets'     => DB::table('shared_assets')->where('company_id',$cid)->count(),
            'logistics'  => DB::table('logistics_listings')->where('company_id',$cid)->count(),
            'compliance' => ['ok'=>$trkOk,'total'=>$trkTotal],
            'health'     => DB::table('company_health_scores')->where('company_id',$cid)->first(),
            'applications' => DB::table('job_applications')
                ->join('job_postings','job_applications.job_id','=','job_postings.id')
                ->where('job_postings.company_id',$cid)->whereNull('job_postings.deleted_at')
                ->where('job_applications.status','submitted')->count(),
        ];
    }
    $activeTab = '';

    return view('dashboard', compact('pledges','notifications','unreadCount','watchlist','totalInvested','activeTab','hub','myCompany','myCompanyHub'));
})->name('dashboard');

// ─────────────────────────────────────────────
// Notifications
// ─────────────────────────────────────────────
Route::get('/notifications', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();

    DB::table('notifications')
        ->where('user_id', $user->id)
        ->whereNull('read_at')
        ->update(['read_at' => now()]);

    $notifications = DB::table('notifications')
        ->where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->paginate(20);

    $activeTab = '';
    return view('notifications', compact('notifications','activeTab'));
})->name('notifications');

Route::get('/settings/notifications', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;
    $user = webUser();
    $prefs = DB::table('notification_preferences')->where('user_id', $user->id)->first();
    return view('notification-settings', compact('prefs'));
})->name('notifications.settings');

Route::post('/settings/notifications', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;
    $user = webUser();
    $data = [
        'messages'    => $request->has('messages') ? 1 : 0,
        'job_updates' => $request->has('job_updates') ? 1 : 0,
        'marketplace' => $request->has('marketplace') ? 1 : 0,
    ];
    if (DB::table('notification_preferences')->where('user_id', $user->id)->exists()) {
        DB::table('notification_preferences')->where('user_id', $user->id)->update($data + ['updated_at' => now()]);
    } else {
        DB::table('notification_preferences')->insert($data + ['user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
    }
    return back()->with('success', 'Notification preferences saved.');
})->name('notifications.settings.save');

// ─────────────────────────────────────────────
// Profile
// ─────────────────────────────────────────────
Route::get('/profile', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();
    $dbUser = DB::table('users')->where('id', $user->id)->first();
    $activeTab = '';
    return view('profile', compact('dbUser','activeTab'));
})->name('profile');

Route::post('/profile', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();
    $data = $request->validate([
        'first_name' => ['required', 'string', 'max:50'],
        'last_name'  => ['required', 'string', 'max:50'],
        'phone'      => ['nullable', 'string', 'max:30'],
    ]);

    DB::table('users')->where('id', $user->id)->update(array_merge($data, ['updated_at' => now()]));

    $updated = array_merge((array) $user, $data);
    session(['auth_user' => $updated]);

    return back()->with('success', 'Profile updated successfully.');
})->name('profile.update');

Route::post('/profile/password', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user   = webUser();
    $dbUser = DB::table('users')->where('id', $user->id)->first();

    $data = $request->validate([
        'current_password' => ['required'],
        'password'         => ['required', 'min:8', 'confirmed'],
    ]);

    if (!Hash::check($data['current_password'], $dbUser->password)) {
        return back()->withErrors(['current_password' => 'Current password is incorrect.']);
    }

    DB::table('users')->where('id', $user->id)->update([
        'password'   => Hash::make($data['password']),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Password changed successfully.');
})->name('profile.password');

// ─────────────────────────────────────────────
// Support tickets
// ─────────────────────────────────────────────
Route::get('/support', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();
    $tickets = DB::table('tickets')
        ->where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->get();

    $categories = DB::table('support_categories')->orderBy('name_en')->get();
    $activeTab = '';
    return view('support', compact('tickets','categories','activeTab'));
})->name('support');

Route::post('/support', function (Request $request) {
    if ($redir = requireAuth($request)) return $redir;

    $user = webUser();
    $data = $request->validate([
        'subject'     => ['required', 'string', 'max:200'],
        'body'        => ['required', 'string', 'max:5000'],
        'category_id' => ['nullable'],
    ]);

    $ticketId = Str::uuid()->toString();
    $number   = 'TKT-' . strtoupper(Str::random(6));

    DB::table('tickets')->insert([
        'id'          => $ticketId,
        'user_id'     => $user->id,
        'category_id' => $data['category_id'] ?: null,
        'ticket_number'=> $number,
        'subject'     => $data['subject'],
        'status'      => 'open',
        'priority'    => 'normal',
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    DB::table('ticket_messages')->insert([
        'id'           => Str::uuid()->toString(),
        'ticket_id'    => $ticketId,
        'author_id'    => $user->id,
        'body'         => $data['body'],
        'is_internal'  => false,
        'is_from_staff'=> false,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    return redirect('/support')->with('success', "Ticket {$number} created. We'll respond shortly.");
})->name('support.create');

// ─────────────────────────────────────────────
// Forgot / Reset password
// ─────────────────────────────────────────────
Route::get('/forgot-password', function () {
    if (session('auth_user')) return redirect('/dashboard');
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    if (session('auth_user')) return redirect('/dashboard');

    $request->validate(['email' => ['required', 'email']]);
    $email = strtolower(trim($request->input('email')));

    $user = DB::table('users')->where('email', $email)->whereNull('deleted_at')->first();

    // Always show the same message to prevent email enumeration
    $message = 'If an account with that email exists, a reset link has been sent.';

    if ($user) {
        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $plainToken = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $email,
            'token'      => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password/' . $plainToken . '?email=' . urlencode($email));

        // Send email (goes to log when MAIL_MAILER=log)
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Hello {$user->first_name},\n\nClick the link below to reset your Galerie virtuelle de l'artisanat du Cameroun password:\n\n{$resetUrl}\n\nThis link expires in 60 minutes. If you didn't request a reset, ignore this email.\n\n— Galerie virtuelle de l'artisanat du Cameroun",
                function ($mail) use ($email, $user) {
                    $mail->to($email)->subject('Reset your Galerie virtuelle de l\'artisanat du Cameroun password');
                }
            );
        } catch (\Exception $e) {
            // Mail failure is non-fatal; link is still logged
        }

        // In local dev: surface the reset URL so it can be tested without email
        if (app()->environment('local')) {
            return back()->with('status', $message)->with('dev_reset_url', $resetUrl);
        }
    }

    return back()->with('status', $message);
})->name('password.email');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    if (session('auth_user')) return redirect('/dashboard');

    $email = $request->query('email', '');
    $row   = DB::table('password_reset_tokens')->where('email', strtolower($email))->first();

    $tokenValid = $row
        && Hash::check($token, $row->token)
        && now()->diffInMinutes($row->created_at) <= 60;

    return view('auth.reset-password', compact('token', 'email', 'tokenValid'));
})->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $data = $request->validate([
        'token'                 => ['required'],
        'email'                 => ['required', 'email'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $email = strtolower(trim($data['email']));
    $row   = DB::table('password_reset_tokens')->where('email', $email)->first();

    if (!$row || !Hash::check($data['token'], $row->token) || now()->diffInMinutes($row->created_at) > 60) {
        return back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
    }

    $user = DB::table('users')->where('email', $email)->whereNull('deleted_at')->first();
    if (!$user) {
        return back()->withErrors(['email' => 'No account found with that email.']);
    }

    DB::table('users')->where('id', $user->id)->update([
        'password'   => Hash::make($data['password']),
        'updated_at' => now(),
    ]);

    DB::table('password_reset_tokens')->where('email', $email)->delete();

    return redirect('/login')->with('success', 'Password reset successfully. You can now log in.');
})->name('password.update');

// ─────────────────────────────────────────────
// Login
// ─────────────────────────────────────────────
Route::get('/login', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.login', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('login');

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    $user = DB::table('users')
        ->whereNull('deleted_at')
        ->where('email', strtolower(trim($data['email'])))
        ->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        return back()->withErrors(['email' => $request->lang === 'en' ? 'Email or password is incorrect.' : 'Email ou mot de passe incorrect.'])->withInput();
    }

    if (isset($user->status) && $user->status === 'suspended') {
        return back()->withErrors(['email' => 'Compte suspendu.'])->withInput();
    }

    DB::table('users')->where('id', $user->id)->update([
        'last_login_at' => now(),
        'last_login_ip' => $request->ip(),
        'updated_at'    => now(),
    ]);

    // Detect SIAC role
    $siacRole = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $user->id)
        ->orderByRaw("FIELD(roles.name,'super_admin','moderator','business_owner') DESC")
        ->value('roles.name');

    $displayName = $user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

    // Store SIAC session
    session(['siac_user' => [
        'id'       => $user->id,
        'name'     => $displayName,
        'email'    => $user->email,
        'role'     => $siacRole,
        'is_admin' => in_array($siacRole, ['super_admin', 'moderator']),
    ]]);

    // Also store legacy session for backward compat with other routes
    session(['auth_user' => [
        'id'                   => $user->id,
        'first_name'           => $user->first_name ?? $displayName,
        'last_name'            => $user->last_name ?? '',
        'email'                => $user->email,
        'status'               => $user->status ?? 'active',
        'locale'               => $user->locale ?? $user->language_preference ?? 'fr',
        'avatar_url'           => $user->avatar ?? $user->avatar_url ?? null,
        'user_type'            => $user->user_type ?? 'buyer',
        'onboarding_completed' => (bool) ($user->onboarding_completed ?? true),
        'is_admin'             => in_array($siacRole, ['super_admin', 'moderator']),
    ]]);

    // All SIAC platform users go to SIAC dashboard
    // (detect SIAC user by presence of 'name' column and absence of legacy first_name)
    $isSiacUser = !empty($user->name) && empty($user->first_name);
    if ($isSiacUser || $siacRole) {
        return redirect('/tableau-de-bord');
    }

    $next = $request->get('next', '/dashboard');
    return redirect($next);
})->name('login.post');

// ─────────────────────────────────────────────
// Register (legacy — kept for backward compat)
// ─────────────────────────────────────────────
Route::get('/register', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.register', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('register');

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'first_name'            => ['required', 'string', 'max:50'],
        'last_name'             => ['required', 'string', 'max:50'],
        'email'                 => ['required', 'email', 'max:255'],
        'phone'                 => ['nullable', 'string', 'max:30'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
        'user_type'             => ['nullable', 'string', 'in:investor,job_seeker,company_owner,developer'],
    ]);

    $data['email'] = strtolower(trim($data['email']));

    if (DB::table('users')->where('email', $data['email'])->exists()) {
        return back()->withErrors(['email' => 'An account with this email already exists. Try logging in instead.'])->withInput();
    }

    $userType = $data['user_type'] ?? 'investor';
    $userId = Str::uuid()->toString();
    try {
        DB::table('users')->insert([
            'id'                   => $userId,
            'first_name'           => $data['first_name'],
            'last_name'            => $data['last_name'],
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'password'             => Hash::make($data['password']),
            'status'               => 'active',
            'locale'               => 'fr',
            'user_type'            => $userType,
            'onboarding_completed' => 0,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        // Race condition (e.g. double form submit) — the email was taken between
        // the check above and this insert. Fail gracefully instead of a 500.
        return back()->withErrors(['email' => 'An account with this email already exists. Try logging in instead.'])->withInput();
    }

    session(['auth_user' => [
        'id'                   => $userId,
        'first_name'           => $data['first_name'],
        'last_name'            => $data['last_name'],
        'email'                => $data['email'],
        'status'               => 'active',
        'locale'               => 'fr',
        'avatar_url'           => null,
        'user_type'            => $userType,
        'onboarding_completed' => 0,
        'is_admin'             => 0,
    ]]);

    return redirect('/welcome');
})->name('register.post');

// ─────────────────────────────────────────────
// SIAC — Inscription (Register)
// ─────────────────────────────────────────────
Route::get('/inscription', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : 'fr';
    return response(view('auth.register', ['lang' => $lang]))->cookie('lang', $lang, 60 * 24 * 30);
})->name('inscription');

Route::post('/inscription', function (Request $request) {
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $data = $request->validate([
        'name'                  => ['required', 'string', 'max:255'],
        'email'                 => ['required', 'email', 'max:255'],
        'phone'                 => ['nullable', 'string', 'max:30'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
        'role'                  => ['nullable', 'in:buyer,business_owner'],
    ]);

    $email = strtolower(trim($data['email']));

    if (DB::table('users')->where('email', $email)->exists()) {
        return back()->withErrors(['email' => $lang === 'en' ? 'An account with this email already exists.' : 'Un compte avec cet email existe déjà.'])->withInput();
    }

    $userId = Str::uuid()->toString();
    DB::table('users')->insert([
        'id'                  => $userId,
        'name'                => $data['name'],
        'email'               => $email,
        'phone'               => $data['phone'] ?? null,
        'password'            => Hash::make($data['password']),
        'status'              => 'active',
        'language_preference' => $lang,
        'is_email_verified'   => 0,
        'is_phone_verified'   => 0,
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);

    // Assign Spatie role if business_owner
    $role = $data['role'] ?? 'buyer';
    if ($role === 'business_owner') {
        $roleRecord = DB::table('roles')->where('name', 'business_owner')->where('guard_name', 'sanctum')->first();
        if ($roleRecord) {
            DB::table('model_has_roles')->insert([
                'role_id'    => $roleRecord->id,
                'model_type' => 'App\\Modules\\Auth\\Models\\User',
                'model_id'   => $userId,
            ]);
        }
    }

    session(['siac_user' => [
        'id'       => $userId,
        'name'     => $data['name'],
        'email'    => $email,
        'role'     => $role === 'business_owner' ? 'business_owner' : null,
        'is_admin' => false,
    ]]);
    session(['auth_user' => [
        'id'       => $userId,
        'first_name' => $data['name'],
        'last_name'  => '',
        'email'    => $email,
        'status'   => 'active',
        'locale'   => $lang,
        'avatar_url' => null,
        'user_type' => $role === 'business_owner' ? 'company_owner' : 'investor',
        'onboarding_completed' => true,
        'is_admin' => false,
    ]]);

    return redirect('/tableau-de-bord');
})->name('inscription.post');

// ─────────────────────────────────────────────
// SIAC — Dashboards
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?lang=' . $request->cookie('lang', 'fr'));

    $role = $siacUser['role'] ?? null;
    if (in_array($role, ['super_admin', 'moderator'])) return redirect('/tableau-de-bord/admin');
    if ($role === 'business_owner') return redirect('/tableau-de-bord/entrepreneur');
    return redirect('/tableau-de-bord/acheteur');
})->name('dashboard.siac');

Route::get('/tableau-de-bord/admin', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $stats = [
        'businesses' => [
            'total'     => DB::table('businesses')->whereNull('deleted_at')->count(),
            'published' => DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count(),
            'verified'  => DB::table('businesses')->whereIn('verification_tier', ['verified', 'certified'])->whereNull('deleted_at')->count(),
        ],
        'products' => [
            'total'     => DB::table('products')->whereNull('deleted_at')->count(),
            'published' => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
        ],
        'users' => [
            'total'           => DB::table('users')->whereNull('deleted_at')->count(),
            'business_owners' => DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'business_owner')->count(),
        ],
    ];

    $recentBusinesses = DB::table('businesses')
        ->whereNull('deleted_at')
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    $pendingVerifications = DB::table('verification_applications')
        ->where('status', 'pending')
        ->count();

    return view('pages.dashboard.admin', compact('lang', 'siacUser', 'stats', 'recentBusinesses', 'pendingVerifications'));
})->name('dashboard.admin');

Route::get('/tableau-de-bord/entrepreneur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $business = DB::table('businesses')
        ->whereNull('deleted_at')
        ->where('user_id', $siacUser['id'])
        ->first();

    $productCount = $business
        ? DB::table('products')->where('business_id', $business->id)->whereNull('deleted_at')->count()
        : 0;

    $products = $business
        ? DB::table('products')->where('business_id', $business->id)->whereNull('deleted_at')->orderByDesc('created_at')->limit(6)->get()
        : collect();

    $messageCount = DB::table('conversations')
        ->where('buyer_id', $siacUser['id'])
        ->orWhere('business_id', $business->id ?? 0)
        ->count();

    return view('pages.dashboard.entrepreneur', compact('lang', 'siacUser', 'business', 'productCount', 'products', 'messageCount'));
})->name('dashboard.entrepreneur');

Route::get('/tableau-de-bord/acheteur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr';

    $savedBusinesses = DB::table('saved_businesses')
        ->join('businesses', 'businesses.id', '=', 'saved_businesses.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->where('saved_businesses.user_id', $siacUser['id'])
        ->whereNull('businesses.deleted_at')
        ->select(
            'saved_businesses.id',
            'saved_businesses.created_at',
            'businesses.id as business_id',
            'businesses.name_fr',
            'businesses.slug',
            'businesses.logo',
            'businesses.verification_tier',
            'industries.name_fr as industry_name'
        )
        ->orderByDesc('saved_businesses.created_at')
        ->limit(6)
        ->get();

    $conversations = DB::table('conversations')
        ->where('buyer_id', $siacUser['id'])
        ->orderByDesc('updated_at')
        ->limit(5)
        ->get();

    $stats = [
        'businesses' => DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count(),
        'products'   => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
        'industries' => DB::table('industries')->where('is_active', 1)->count(),
    ];

    return view('pages.dashboard.buyer', compact('lang', 'siacUser', 'savedBusinesses', 'conversations', 'stats'));
})->name('dashboard.buyer');

// ─────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────
Route::post('/logout', function () {
    session()->forget(['auth_user', 'siac_user', 'lang']);
    return redirect('/');
})->name('logout');

// ─────────────────────────────────────────────
// Onboarding welcome
// ─────────────────────────────────────────────
Route::get('/welcome', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    return view('welcome');
})->name('welcome');

Route::post('/welcome', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $validTypes = ['investor', 'job_seeker', 'company_owner', 'developer'];
    $userType = in_array($request->input('user_type'), $validTypes) ? $request->input('user_type') : 'investor';

    DB::table('users')->where('id', $user->id)->update([
        'user_type'            => $userType,
        'onboarding_completed' => 1,
        'updated_at'           => now(),
    ]);

    $sessionUser = session('auth_user', []);
    $sessionUser['user_type']            = $userType;
    $sessionUser['onboarding_completed'] = true;
    session(['auth_user' => $sessionUser]);

    $destinations = [
        'investor'      => '/investor-profile',
        'job_seeker'    => '/my-profile',
        'company_owner' => '/',
        'developer'     => '/developer',
    ];

    return redirect($destinations[$userType] ?? '/dashboard')
        ->with('success', 'Welcome aboard! Here\'s where to start.');
})->name('welcome.post');

// ─────────────────────────────────────────────
// Watchlist
// ─────────────────────────────────────────────
Route::get('/watchlist', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $wl = DB::table('watchlists')->where('investor_id', $user->id)->first();
    $watchlist = $wl ? DB::table('watchlist_items')
        ->join('companies', 'companies.id', '=', 'watchlist_items.company_id')
        ->leftJoin('regions', 'regions.id', '=', 'companies.region_id')
        ->where('watchlist_items.watchlist_id', $wl->id)
        ->whereNull('companies.deleted_at')
        ->select('companies.*', 'regions.name_en as region_name', 'watchlist_items.created_at as added_at')
        ->orderBy('watchlist_items.created_at', 'desc')
        ->get() : collect();
    return view('watchlist', compact('watchlist'));
})->name('watchlist');

Route::post('/watchlist/toggle', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $companyId = $request->input('company_id');
    $company = DB::table('companies')->where('id', $companyId)->whereNull('deleted_at')->first();
    if (!$company) return response()->json(['error' => 'Not found'], 404);

    $wl = DB::table('watchlists')->where('investor_id', $user->id)->where('is_default', 1)->first();
    if (!$wl) {
        $wlId = Str::uuid()->toString();
        DB::table('watchlists')->insert(['id' => $wlId, 'investor_id' => $user->id, 'name_en' => 'My Watchlist', 'name_fr' => 'Ma Liste', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now()]);
    } else {
        $wlId = $wl->id;
    }

    $existing = DB::table('watchlist_items')
        ->where('watchlist_id', $wlId)
        ->where('company_id', $companyId)
        ->first();

    $isAjax = $request->expectsJson() || $request->header('Accept') === 'application/json';

    if ($existing) {
        DB::table('watchlist_items')->where('id', $existing->id)->delete();
        if ($isAjax) return response()->json(['watching' => false, 'added' => false]);
        return back()->with('success', 'Removed from watchlist.');
    }
    DB::table('watchlist_items')->insert(['id' => Str::uuid()->toString(), 'watchlist_id' => $wlId, 'company_id' => $companyId, 'created_at' => now(), 'updated_at' => now()]);
    if ($isAjax) return response()->json(['watching' => true, 'added' => true]);
    return back()->with('success', 'Added to watchlist.');
})->name('watchlist.toggle');

// ─────────────────────────────────────────────
// Portfolio
// ─────────────────────────────────────────────
Route::get('/portfolio', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $pledges = DB::table('investment_pledges')
        ->join('share_offerings', 'share_offerings.id', '=', 'investment_pledges.offering_id')
        ->join('companies', 'companies.id', '=', 'share_offerings.company_id')
        ->where('investment_pledges.investor_id', $user->id)
        ->whereIn('investment_pledges.status', ['confirmed', 'completed', 'allocated'])
        ->select('investment_pledges.*', 'share_offerings.title_en as offering_title', 'share_offerings.instrument_type', 'share_offerings.share_price', 'companies.name as company_name', 'companies.slug as company_slug')
        ->orderBy('investment_pledges.created_at', 'desc')
        ->get();
    $totalInvested = $pledges->sum('amount');
    $totalShares   = $pledges->sum('shares_count');
    return view('portfolio', compact('pledges', 'totalInvested', 'totalShares'));
})->name('portfolio');

// ─────────────────────────────────────────────
// Wallet
// ─────────────────────────────────────────────
Route::get('/wallet', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
    if (!$wallet) {
        $wid = Str::uuid()->toString();
        DB::table('wallets')->insert(['id' => $wid, 'user_id' => $user->id, 'type' => 'investment', 'balance' => 0, 'pending_balance' => 0, 'currency' => 'XAF', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $wallet = DB::table('wallets')->where('id', $wid)->first();
    }
    $transactions = DB::table('wallet_transactions')->where('wallet_id', $wallet->id)->orderBy('created_at', 'desc')->limit(30)->get();
    return view('wallet', compact('wallet', 'transactions'));
})->name('wallet');

Route::post('/wallet/topup', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate(['amount' => 'required|integer|min:5000|max:10000000', 'method' => 'required|in:mtn_momo,orange_money,bank_transfer']);
    $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
    if (!$wallet) {
        $wid = Str::uuid()->toString();
        DB::table('wallets')->insert(['id' => $wid, 'user_id' => $user->id, 'type' => 'investment', 'balance' => 0, 'pending_balance' => 0, 'currency' => 'XAF', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $wallet = DB::table('wallets')->where('id', $wid)->first();
    }
    // Record as pending top-up (balance credited after payment confirmed in production)
    $newBalance = $wallet->pending_balance + $data['amount'];
    DB::table('wallets')->where('id', $wallet->id)->update(['pending_balance' => $newBalance, 'updated_at' => now()]);
    DB::table('wallet_transactions')->insert(['id' => Str::uuid()->toString(), 'wallet_id' => $wallet->id, 'type' => 'credit', 'amount' => $data['amount'], 'balance_after' => $wallet->balance, 'reference' => 'TOPUP-'.strtoupper(substr(Str::uuid()->toString(), 0, 8)), 'description' => 'Top-up via '.str_replace('_',' ',ucwords($data['method'],'_')), 'created_at' => now(), 'updated_at' => now()]);
    return back()->with('success', 'Top-up of '.number_format($data['amount']).' XAF is pending confirmation. It will appear in your balance once payment is verified.');
})->name('wallet.topup');

Route::get('/directory', fn() => redirect('/'))->name('directory');

Route::post('/notifications/mark-read', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    DB::table('notifications')->where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
    return back()->with('success', 'All notifications marked as read.');
})->name('notifications.mark-read');

// ─────────────────────────────────────────────
// Investor Profile / KYC
// ─────────────────────────────────────────────
Route::get('/investor-profile', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user    = webUser();
    $profile = DB::table('investor_profiles')->where('user_id', $user->id)->first();
    $kyc     = DB::table('kyc_applications')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    return view('investor-profile', compact('profile', 'kyc'));
})->name('investor.profile');

Route::post('/investor-profile', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate([
        'investor_type'   => 'required|in:individual,institutional',
        'national_id'     => 'required|string|max:50',
        'id_type'         => 'required|in:cni,passport,driving_licence',
        'dob'             => 'required|date',
        'nationality'     => 'required|string|max:60',
        'occupation'      => 'required|string|max:100',
        'risk_tolerance'  => 'required|in:conservative,moderate,aggressive',
        'bank_name'       => 'nullable|string|max:100',
        'bank_account'    => 'nullable|string|max:50',
    ]);
    $exists = DB::table('investor_profiles')->where('user_id', $user->id)->first();
    if ($exists) {
        DB::table('investor_profiles')->where('user_id', $user->id)->update(array_merge($data, ['updated_at' => now()]));
    } else {
        DB::table('investor_profiles')->insert(array_merge($data, ['id' => Str::uuid()->toString(), 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]));
    }
    return back()->with('success', 'Investor profile updated.');
})->name('investor.profile.update');

// ─────────────────────────────────────────────
// Company Claim
// ─────────────────────────────────────────────
Route::get('/companies/{slug}/claim', function (Request $request, $slug) {
    if ($r = requireAuth($request)) return $r;
    $company = DB::table('companies')
        ->join('regions', 'companies.region_id', '=', 'regions.id')
        ->leftJoin('cities', 'companies.city_id', '=', 'cities.id')
        ->where('companies.slug', $slug)->whereNull('companies.deleted_at')
        ->select('companies.*', 'regions.name_en as region_name', 'cities.name_en as city_name')
        ->first();
    if (!$company) abort(404);
    $user         = webUser();
    $existing     = DB::table('company_users')->where('company_id', $company->id)->where('user_id', $user->id)->first();
    $pendingClaim = DB::table('verification_applications')->where('company_id', $company->id)->where('submitted_by', $user->id)->where('status', 'pending')->first();
    return view('company-claim', compact('company', 'existing', 'pendingClaim'));
})->name('company.claim');

Route::post('/companies/{slug}/claim', function (Request $request, $slug) {
    if ($r = requireAuth($request)) return $r;
    $company = DB::table('companies')->where('slug', $slug)->whereNull('deleted_at')->first();
    if (!$company) abort(404);
    $user = webUser();
    $data = $request->validate(['authority_type' => 'required|string', 'notes' => 'nullable|string|max:500']);
    $cols = array_column(DB::select('SHOW COLUMNS FROM verification_applications'), 'Field');
    $row = ['id' => Str::uuid()->toString(), 'company_id' => $company->id, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()];
    if (in_array('submitted_by', $cols)) $row['submitted_by'] = $user->id;
    if (in_array('type', $cols)) $row['type'] = 'claim';
    if (in_array('notes', $cols)) $row['notes'] = $data['notes'] ?? null;
    DB::table('verification_applications')->insert($row);
    return redirect('/companies/' . $slug)->with('success', 'Claim submitted. Our team will review within 3 business days.');
})->name('company.claim.post');

// ─────────────────────────────────────────────
// Company Contact
// ─────────────────────────────────────────────
Route::post('/companies/{slug}/contact', function (Request $request, $slug) {
    if ($r = requireAuth($request)) return $r;
    $company = DB::table('companies')->where('slug', $slug)->whereNull('deleted_at')->first();
    if (!$company) abort(404);
    $user = webUser();
    $data = $request->validate(['subject' => 'required|string|max:120', 'message' => 'required|string|max:2000']);
    $cols = array_column(DB::select('SHOW COLUMNS FROM company_contact_requests'), 'Field');
    $row  = ['company_id' => $company->id, 'subject' => $data['subject'], 'message' => $data['message'], 'created_at' => now(), 'updated_at' => now()];
    if (in_array('id', $cols)) $row['id'] = Str::uuid()->toString();
    if (in_array('user_id', $cols)) $row['user_id'] = $user->id;
    if (in_array('status', $cols)) $row['status'] = 'pending';
    DB::table('company_contact_requests')->insert($row);
    return back()->with('success', 'Message sent to ' . $company->name . '. They will reply to your registered email.');
})->name('company.contact');

// ─────────────────────────────────────────────
// Blog
// ─────────────────────────────────────────────
Route::get('/blog', function (Request $request) {
    $category = $request->get('category', '');
    $q        = $request->get('q', '');
    $query    = DB::table('blog_posts')->whereNull('deleted_at')->where('is_published', 1)->where('published_at', '<=', now());
    if ($q) $query->where(function ($qq) use ($q) { $qq->where('title_en', 'like', "%$q%")->orWhere('excerpt_en', 'like', "%$q%"); });
    if ($category) $query->join('blog_categories', 'blog_categories.id', '=', 'blog_posts.category_id')->where('blog_categories.slug', $category)->select('blog_posts.*');
    $posts      = $query->orderBy('published_at', 'desc')->limit(20)->get();
    $categories = DB::table('blog_categories')->orderBy('name_en')->get();
    return view('blog', compact('posts', 'categories', 'category', 'q'));
})->name('blog.index');

Route::get('/blog/{slug}', function (Request $request, $slug) {
    $post = DB::table('blog_posts')->where('slug', $slug)->where('is_published', 1)->whereNull('deleted_at')->first();
    if (!$post) abort(404);
    DB::table('blog_posts')->where('slug', $slug)->increment('view_count');
    $related = DB::table('blog_posts')->where('is_published', 1)->whereNull('deleted_at')->where('id', '!=', $post->id)->orderBy('published_at', 'desc')->limit(3)->get();
    return view('blog-post', compact('post', 'related'));
})->name('blog.show');

// ─────────────────────────────────────────────
// Help / Knowledge Base
// ─────────────────────────────────────────────
Route::get('/help', function (Request $request) {
    $q          = $request->get('q', '');
    $query      = DB::table('knowledge_articles')->where('is_published', 1);
    if ($q) $query->where(function ($qq) use ($q) { $qq->where('title_en', 'like', "%$q%")->orWhere('body_en', 'like', "%$q%"); });
    $articles   = $query->orderBy('view_count', 'desc')->get();
    $categories = DB::table('knowledge_categories')->where('is_active', 1)->orderBy('sort_order')->get();
    return view('help', compact('articles', 'categories', 'q'));
})->name('help.index');

Route::get('/help/{slug}', function (Request $request, $slug) {
    $article = DB::table('knowledge_articles')->where('slug', $slug)->where('is_published', 1)->first();
    if (!$article) abort(404);
    DB::table('knowledge_articles')->where('slug', $slug)->increment('view_count');
    $related = DB::table('knowledge_articles')->where('is_published', 1)->where('id', '!=', $article->id)->orderBy('view_count', 'desc')->limit(4)->get();
    return view('help-article', compact('article', 'related'));
})->name('help.show');

// ─────────────────────────────────────────────
// Static Pages
// ─────────────────────────────────────────────
Route::get('/about',        fn () => view('about'))->name('about');
Route::get('/terms',        fn () => view('terms'))->name('terms');
Route::get('/privacy',      fn () => view('privacy'))->name('privacy');
Route::get('/how-it-works', fn () => view('how-it-works'))->name('how-it-works');

// ─────────────────────────────────────────────
// Job Board
// ─────────────────────────────────────────────
Route::get('/jobs', function (Request $request) {
    $type  = $request->get('type', '');
    $q     = $request->get('q', '');
    $query = DB::table('job_postings')
        ->join('companies', 'companies.id', '=', 'job_postings.company_id')
        ->leftJoin('regions', 'regions.id', '=', 'companies.region_id')
        ->whereNull('job_postings.deleted_at')
        ->whereNull('companies.deleted_at')
        ->where('job_postings.status', 'open')
        ->where(function ($qq) { $qq->whereNull('job_postings.deadline')->orWhere('job_postings.deadline', '>=', now()); });
    if ($type) $query->where('job_postings.type', $type);
    if ($q) $query->where(function ($qq) use ($q) {
        $qq->where('job_postings.title_en', 'like', "%$q%")
           ->orWhere('companies.name', 'like', "%$q%")
           ->orWhere('job_postings.location', 'like', "%$q%");
    });
    $jobs  = $query->select('job_postings.*', 'companies.name as company_name', 'companies.slug as company_slug', 'companies.verification_status', 'regions.name_en as region_name')
        ->orderBy('job_postings.created_at', 'desc')->paginate(15);
    $total = DB::table('job_postings')->where('status', 'open')->whereNull('deleted_at')->count();
    return view('jobs', compact('jobs', 'total', 'type', 'q'));
})->name('jobs.index');

// Post a job (recruiter) — registered before /jobs/{id} so "post" isn't read as an id
Route::get('/jobs/post', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $companies = DB::table('company_users')
        ->join('companies', 'company_users.company_id', '=', 'companies.id')
        ->where('company_users.user_id', $user->id)->where('company_users.is_active', 1)
        ->whereNull('companies.deleted_at')
        ->select('companies.id', 'companies.name')->get();
    return view('job-post', compact('companies'));
})->name('jobs.post');

Route::post('/jobs', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate([
        'company_id'  => 'required|string',
        'title_en'    => 'required|string|max:140',
        'description_en' => 'required|string|max:8000',
        'type'        => 'required|in:full_time,part_time,contract,internship,remote',
        'location'    => 'nullable|string|max:120',
        'department'  => 'nullable|string|max:120',
        'salary_min'  => 'nullable|numeric|min:0',
        'salary_max'  => 'nullable|numeric|min:0',
        'deadline'    => 'nullable|date',
    ]);
    // verify the user owns/admins the chosen company
    $owns = DB::table('company_users')->where('company_id', $data['company_id'])
        ->where('user_id', $user->id)->where('is_active', 1)->whereIn('role', ['owner', 'admin'])->exists();
    if (!$owns) return back()->with('error', 'You can only post jobs for a company you manage.');
    $id = Str::uuid()->toString();
    DB::table('job_postings')->insert([
        'id'          => $id,
        'company_id'  => $data['company_id'],
        'posted_by'   => $user->id,
        'title_en'    => $data['title_en'],
        'title_fr'    => $data['title_en'],
        'description_en' => $data['description_en'],
        'description_fr' => $data['description_en'],
        'type'        => $data['type'],
        'location'    => $data['location'] ?? null,
        'department'  => $data['department'] ?? null,
        'salary_min'  => $data['salary_min'] ?? null,
        'salary_max'  => $data['salary_max'] ?? null,
        'currency'    => 'XAF',
        'deadline'    => $data['deadline'] ?? null,
        'status'      => 'open',
        'created_at'  => now(), 'updated_at' => now(),
    ]);
    notifyMatchingJobAlerts($id, $data['title_en'], $data['description_en'], $data['location'] ?? '', $data['type'], $user->id);
    return redirect('/jobs/' . $id)->with('success', 'Job posted! Matching candidates with alerts have been notified.');
})->name('jobs.store');

/** Notify users whose active job alerts match a newly posted job. */
function notifyMatchingJobAlerts(string $jobId, string $title, string $desc, string $location, string $type, ?string $posterId): void
{
    $alerts = DB::table('job_alerts')->where('is_active', 1)->get();
    $haystack = mb_strtolower($title . ' ' . $desc);
    $locLower = mb_strtolower($location);
    foreach ($alerts as $a) {
        if ($a->user_id === $posterId) continue; // don't notify the poster
        if ($a->keyword && !str_contains($haystack, mb_strtolower($a->keyword))) continue;
        if ($a->location && (!$location || !str_contains($locLower, mb_strtolower($a->location)))) continue;
        if ($a->job_type !== 'any' && $a->job_type !== $type) continue;
        notifyUser($a->user_id, 'job_alert', 'New job matches your alert',
            'A new job "' . $title . '" matches your saved alert.', '/jobs/' . $jobId,
            'Nouvelle offre correspond à votre alerte', 'Une nouvelle offre « ' . $title . ' » correspond à votre alerte.');
    }
}

Route::get('/jobs/{id}', function (Request $request, $id) {
    $job = DB::table('job_postings')
        ->join('companies', 'companies.id', '=', 'job_postings.company_id')
        ->leftJoin('regions', 'regions.id', '=', 'companies.region_id')
        ->where('job_postings.id', $id)->whereNull('job_postings.deleted_at')
        ->select('job_postings.*', 'companies.name as company_name', 'companies.slug as company_slug', 'companies.description_en as company_desc', 'companies.verification_status', 'regions.name_en as region_name')
        ->first();
    if (!$job) abort(404);
    DB::table('job_postings')->where('id', $id)->increment('view_count');
    $user    = webUser();
    $applied = $user ? DB::table('job_applications')->where('job_id', $id)->where('user_id', $user->id)->first() : null;
    $similar = DB::table('job_postings')
        ->join('companies', 'companies.id', '=', 'job_postings.company_id')
        ->where('job_postings.type', $job->type)->where('job_postings.id', '!=', $id)
        ->where('job_postings.status', 'open')->whereNull('job_postings.deleted_at')
        ->select('job_postings.*', 'companies.name as company_name', 'companies.slug as company_slug')
        ->limit(3)->get();
    $isSaved = $user && DB::table('saved_jobs')->where('user_id', $user->id)->where('job_id', $id)->exists();
    return view('job', compact('job', 'applied', 'similar', 'isSaved'));
})->name('jobs.show');

// Save / unsave a job (toggle)
Route::post('/jobs/{id}/save', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $job = DB::table('job_postings')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$job) abort(404);
    $existing = DB::table('saved_jobs')->where('user_id', $user->id)->where('job_id', $id)->first();
    if ($existing) {
        DB::table('saved_jobs')->where('id', $existing->id)->delete();
        $saved = false;
    } else {
        DB::table('saved_jobs')->insert(['user_id' => $user->id, 'job_id' => $id, 'created_at' => now()]);
        $saved = true;
    }
    if ($request->expectsJson() || $request->ajax()) {
        return response()->json(['saved' => $saved]);
    }
    return back()->with('success', $saved ? 'Job saved.' : 'Job removed from saved.');
})->name('jobs.save');

// Saved jobs + job alerts
Route::get('/saved-jobs', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $saved = DB::table('saved_jobs')
        ->join('job_postings', 'saved_jobs.job_id', '=', 'job_postings.id')
        ->join('companies', 'job_postings.company_id', '=', 'companies.id')
        ->where('saved_jobs.user_id', $user->id)->whereNull('job_postings.deleted_at')
        ->select('job_postings.id', 'job_postings.title_en', 'job_postings.type', 'job_postings.location',
                 'job_postings.status', 'job_postings.salary_min', 'job_postings.salary_max', 'job_postings.deadline',
                 'companies.name as company_name', 'companies.slug as company_slug', 'saved_jobs.created_at as saved_at')
        ->orderByDesc('saved_jobs.created_at')->get();
    $alerts = DB::table('job_alerts')->where('user_id', $user->id)->orderByDesc('created_at')->get();
    return view('saved-jobs', compact('saved', 'alerts'));
})->name('saved.jobs');

Route::post('/job-alerts', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate([
        'keyword'  => 'nullable|string|max:80',
        'location' => 'nullable|string|max:80',
        'job_type' => 'required|in:any,full_time,part_time,contract,internship,remote',
    ]);
    if (empty($data['keyword']) && empty($data['location']) && $data['job_type'] === 'any') {
        return back()->with('error', 'Add at least a keyword, location, or job type for your alert.');
    }
    DB::table('job_alerts')->insert([
        'user_id'  => $user->id,
        'keyword'  => $data['keyword'] ?? null,
        'location' => $data['location'] ?? null,
        'job_type' => $data['job_type'],
        'is_active' => 1,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    return back()->with('success', 'Job alert created — we will notify you when matching jobs are posted.');
})->name('job.alerts.store');

Route::post('/job-alerts/{id}/delete', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    DB::table('job_alerts')->where('id', $id)->where('user_id', $user->id)->delete();
    return back()->with('success', 'Alert removed.');
})->name('job.alerts.delete');

Route::post('/jobs/{id}/apply', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $job  = DB::table('job_postings')->where('id', $id)->where('status', 'open')->whereNull('deleted_at')->first();
    if (!$job) abort(404);
    if (DB::table('job_applications')->where('job_id', $id)->where('user_id', $user->id)->exists()) {
        return back()->with('error', 'You have already applied for this position.');
    }
    $data = $request->validate(['cover_letter' => 'required|string|min:50|max:3000']);
    DB::table('job_applications')->insert(['id' => Str::uuid()->toString(), 'job_id' => $id, 'user_id' => $user->id, 'cover_letter' => $data['cover_letter'], 'status' => 'submitted', 'created_at' => now(), 'updated_at' => now()]);
    DB::table('notifications')->insert(['id' => Str::uuid()->toString(), 'user_id' => $user->id, 'type' => 'job_application', 'title_en' => 'Application submitted', 'body_en' => 'Your application for "' . $job->title_en . '" has been submitted.', 'title_fr' => 'Candidature soumise', 'body_fr' => 'Votre candidature pour "' . $job->title_fr . '" a été soumise.', 'read_at' => null, 'created_at' => now(), 'updated_at' => now()]);
    // Notify the employer (job poster, or company owners as fallback)
    $applicant = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'A candidate';
    $empTitleEn = 'New job application';
    $empBodyEn  = $applicant . ' applied for "' . $job->title_en . '".';
    $empTitleFr = 'Nouvelle candidature';
    $empBodyFr  = $applicant . ' a postulé pour « ' . ($job->title_fr ?: $job->title_en) . ' ».';
    if ($job->posted_by) {
        notifyUser($job->posted_by, 'job_application_received', $empTitleEn, $empBodyEn, "/jobs/$id", $empTitleFr, $empBodyFr);
    } else {
        notifyCompanyOwners($job->company_id, 'job_application_received', $empTitleEn, $empBodyEn, "/jobs/$id", $empTitleFr, $empBodyFr);
    }
    return back()->with('success', 'Application submitted successfully!');
})->name('jobs.apply');

// ─────────────────────────────────────────────
// Recruiter — manage job applications
// ─────────────────────────────────────────────

/** True if $user may manage applications for $job (poster or company owner/admin). */
function canManageJob(object $user, ?object $job): bool
{
    if (!$job) return false;
    if ($job->posted_by && (string) $job->posted_by === (string) $user->id) return true;
    return DB::table('company_users')->where('company_id', $job->company_id)
        ->where('user_id', $user->id)->where('is_active', 1)
        ->whereIn('role', ['owner', 'admin'])->exists();
}

Route::get('/recruiter', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    // companies the user owns/administers
    $companyIds = DB::table('company_users')->where('user_id', $user->id)->where('is_active', 1)
        ->whereIn('role', ['owner', 'admin'])->pluck('company_id')->all();
    // also jobs they personally posted
    $jobs = DB::table('job_postings')
        ->leftJoin('companies', 'job_postings.company_id', '=', 'companies.id')
        ->whereNull('job_postings.deleted_at')
        ->where(function ($q) use ($user, $companyIds) {
            $q->where('job_postings.posted_by', $user->id);
            if ($companyIds) $q->orWhereIn('job_postings.company_id', $companyIds);
        })
        ->select('job_postings.id', 'job_postings.title_en', 'job_postings.title_fr', 'job_postings.status',
                 'job_postings.created_at', 'companies.name as company_name', 'companies.slug as company_slug')
        ->orderByDesc('job_postings.created_at')->get();
    $jobIds = $jobs->pluck('id')->all();
    $counts = $jobIds ? DB::table('job_applications')->whereIn('job_id', $jobIds)
        ->select('job_id', DB::raw('COUNT(*) as total'),
                 DB::raw("SUM(status='submitted') as pending"),
                 DB::raw("SUM(status IN ('shortlisted','interview','offered')) as progressing"))
        ->groupBy('job_id')->get()->keyBy('job_id') : collect();
    return view('recruiter', compact('jobs', 'counts', 'user'));
})->name('recruiter');

// Business analytics — company owner's recruiting + marketplace metrics
Route::get('/analytics', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $company = DB::table('company_users')
        ->join('companies', 'company_users.company_id', '=', 'companies.id')
        ->where('company_users.user_id', $user->id)->where('company_users.is_active', 1)
        ->whereIn('company_users.role', ['owner', 'admin'])->whereNull('companies.deleted_at')
        ->select('companies.id', 'companies.name', 'companies.slug', 'companies.view_count', 'companies.verification_status')
        ->first();
    if (!$company) return view('analytics', ['company' => null]);
    $cid = $company->id;

    $jobIds = DB::table('job_postings')->where('company_id', $cid)->whereNull('deleted_at')->pluck('id');
    $funnel = ['submitted' => 0, 'shortlisted' => 0, 'interview' => 0, 'offered' => 0, 'rejected' => 0, 'withdrawn' => 0];
    if ($jobIds->count()) {
        $rows = DB::table('job_applications')->whereIn('job_id', $jobIds)
            ->select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status');
        foreach ($funnel as $k => $v) $funnel[$k] = (int) ($rows[$k] ?? 0);
    }
    $supplier = DB::table('supplier_reviews')->where('supplier_company_id', $cid)->where('status', 'published');

    $metrics = [
        'profile_views' => (int) $company->view_count,
        'health'        => DB::table('company_health_scores')->where('company_id', $cid)->first(),
        'jobs_total'    => $jobIds->count(),
        'jobs_open'     => DB::table('job_postings')->where('company_id', $cid)->where('status', 'open')->whereNull('deleted_at')->count(),
        'applications'  => array_sum($funnel),
        'funnel'        => $funnel,
        'tenders'       => DB::table('tenders')->where('company_id', $cid)->whereNull('deleted_at')->count(),
        'tender_bids'   => (int) DB::table('tenders')->where('company_id', $cid)->whereNull('deleted_at')->sum('bid_count'),
        'assets'        => DB::table('shared_assets')->where('company_id', $cid)->count(),
        'asset_inq'     => DB::table('asset_inquiries')->join('shared_assets', 'asset_inquiries.asset_id', '=', 'shared_assets.id')->where('shared_assets.company_id', $cid)->count(),
        'seeks'         => DB::table('invest_seeks')->where('company_id', $cid)->whereNull('deleted_at')->count(),
        'seek_interest' => DB::table('invest_interests')->join('invest_seeks', 'invest_interests.seek_id', '=', 'invest_seeks.id')->where('invest_seeks.company_id', $cid)->count(),
        'events'        => DB::table('events')->where('organizer_company_id', $cid)->count(),
        'event_regs'    => DB::table('event_registrations')->join('events', 'event_registrations.event_id', '=', 'events.id')->where('events.organizer_company_id', $cid)->count(),
        'reviews'       => (clone $supplier)->count(),
        'review_avg'    => (clone $supplier)->avg('score_overall'),
    ];
    // top jobs by application count
    $topJobs = $jobIds->count() ? DB::table('job_postings')
        ->leftJoin('job_applications', 'job_applications.job_id', '=', 'job_postings.id')
        ->where('job_postings.company_id', $cid)->whereNull('job_postings.deleted_at')
        ->select('job_postings.id', 'job_postings.title_en', 'job_postings.status', 'job_postings.view_count',
                 DB::raw('COUNT(job_applications.id) as apps'))
        ->groupBy('job_postings.id', 'job_postings.title_en', 'job_postings.status', 'job_postings.view_count')
        ->orderByDesc('apps')->limit(8)->get() : collect();

    // Trends derived from existing timestamps (no tracking table needed)
    $appTrend = [];
    for ($i = 7; $i >= 0; $i--) {
        $start = \Carbon\Carbon::now()->startOfWeek()->subWeeks($i);
        $end = (clone $start)->addWeek();
        $count = $jobIds->count() ? DB::table('job_applications')->whereIn('job_id', $jobIds)
            ->where('created_at', '>=', $start)->where('created_at', '<', $end)->count() : 0;
        $appTrend[] = ['label' => $start->format('d M'), 'count' => $count];
    }
    $jobTrend = [];
    for ($i = 5; $i >= 0; $i--) {
        $start = \Carbon\Carbon::now()->startOfMonth()->subMonths($i);
        $end = (clone $start)->addMonth();
        $count = DB::table('job_postings')->where('company_id', $cid)->whereNull('job_postings.deleted_at')
            ->where('created_at', '>=', $start)->where('created_at', '<', $end)->count();
        $jobTrend[] = ['label' => $start->format('M'), 'count' => $count];
    }

    return view('analytics', compact('company', 'metrics', 'topJobs', 'appTrend', 'jobTrend'));
})->name('analytics');

Route::get('/jobs/{id}/applications', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $job = DB::table('job_postings')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$job) abort(404);
    if (!canManageJob($user, $job)) abort(403);
    $company = DB::table('companies')->where('id', $job->company_id)->first();
    $applications = DB::table('job_applications')
        ->join('users', 'job_applications.user_id', '=', 'users.id')
        ->leftJoin('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
        ->where('job_applications.job_id', $id)
        ->select('job_applications.*', 'users.first_name', 'users.last_name', 'users.email',
                 'employee_profiles.headline', 'employee_profiles.location as emp_location')
        ->orderByRaw("FIELD(job_applications.status,'submitted','shortlisted','interview','offered','rejected','withdrawn')")
        ->orderByDesc('job_applications.created_at')->get();
    return view('job-applications', compact('job', 'company', 'applications', 'user'));
})->name('jobs.applications');

/** Build a CSV download response from a header row + array of rows. */
function csvDownload(string $filename, array $header, array $rows)
{
    $esc = function ($v) {
        $v = (string) ($v ?? '');
        $v = str_replace('"', '""', $v);
        return '"' . $v . '"';
    };
    $out = implode(',', array_map($esc, $header)) . "\r\n";
    foreach ($rows as $row) {
        $out .= implode(',', array_map($esc, $row)) . "\r\n";
    }
    return response("\xEF\xBB\xBF" . $out, 200, [   // BOM for Excel UTF-8
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}

// Export one job's applicants as CSV (owner only)
Route::get('/jobs/{id}/applications/export', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $job = DB::table('job_postings')->where('id', $id)->whereNull('deleted_at')->first();
    if (!$job) abort(404);
    if (!canManageJob($user, $job)) abort(403);
    $apps = DB::table('job_applications')
        ->join('users', 'job_applications.user_id', '=', 'users.id')
        ->where('job_applications.job_id', $id)
        ->select('users.first_name', 'users.last_name', 'users.email', 'job_applications.status',
                 'job_applications.created_at', 'job_applications.cover_letter')
        ->orderByDesc('job_applications.created_at')->get();
    $rows = $apps->map(fn($a) => [
        trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '')),
        $a->email, ucfirst($a->status), date('Y-m-d', strtotime($a->created_at)),
        preg_replace('/\s+/', ' ', $a->cover_letter ?? ''),
    ])->all();
    $slug = \Illuminate\Support\Str::slug($job->title_en ?: 'job');
    return csvDownload("applicants-{$slug}.csv", ['Candidate', 'Email', 'Status', 'Applied', 'Cover Letter'], $rows);
})->name('jobs.applications.export');

// Export all applicants across the owner's company jobs
Route::get('/recruiter/export', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $companyIds = DB::table('company_users')->where('user_id', $user->id)->where('is_active', 1)
        ->whereIn('role', ['owner', 'admin'])->pluck('company_id')->all();
    $jobIds = DB::table('job_postings')->whereNull('deleted_at')
        ->where(function ($q) use ($user, $companyIds) {
            $q->where('posted_by', $user->id);
            if ($companyIds) $q->orWhereIn('company_id', $companyIds);
        })->pluck('id');
    $apps = $jobIds->count() ? DB::table('job_applications')
        ->join('users', 'job_applications.user_id', '=', 'users.id')
        ->join('job_postings', 'job_applications.job_id', '=', 'job_postings.id')
        ->whereIn('job_applications.job_id', $jobIds)
        ->select('job_postings.title_en', 'users.first_name', 'users.last_name', 'users.email',
                 'job_applications.status', 'job_applications.created_at')
        ->orderByDesc('job_applications.created_at')->get() : collect();
    $rows = $apps->map(fn($a) => [
        $a->title_en, trim(($a->first_name ?? '') . ' ' . ($a->last_name ?? '')),
        $a->email, ucfirst($a->status), date('Y-m-d', strtotime($a->created_at)),
    ])->all();
    return csvDownload('all-applicants-' . date('Y-m-d') . '.csv', ['Job', 'Candidate', 'Email', 'Status', 'Applied'], $rows);
})->name('recruiter.export');

Route::post('/applications/{id}/status', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $app = DB::table('job_applications')->where('id', $id)->first();
    if (!$app) abort(404);
    $job = DB::table('job_postings')->where('id', $app->job_id)->first();
    if (!canManageJob($user, $job)) abort(403);
    $new = $request->input('status');
    $allowed = ['submitted', 'shortlisted', 'interview', 'offered', 'rejected'];
    if (!in_array($new, $allowed, true)) return back()->with('error', 'Invalid status.');
    DB::table('job_applications')->where('id', $id)->update([
        'status' => $new, 'notes' => $request->input('notes', $app->notes), 'updated_at' => now(),
    ]);
    // Notify the applicant of the status change
    $labels = ['shortlisted' => ['Shortlisted', 'présélectionnée'], 'interview' => ['Interview', 'entretien'],
               'offered' => ['Offer extended', 'offre'], 'rejected' => ['Not selected', 'non retenue'],
               'submitted' => ['Under review', 'en cours d\'examen']];
    [$en, $fr] = $labels[$new] ?? ['Updated', 'mise à jour'];
    $jobTitle = $job->title_en ?: $job->title_fr;
    notifyUser($app->user_id, 'application_status',
        'Application update: ' . $en,
        'Your application for "' . $jobTitle . '" is now: ' . $en . '.',
        '/my-profile',
        'Mise à jour de candidature : ' . $en,
        'Votre candidature pour « ' . ($job->title_fr ?: $job->title_en) . ' » est maintenant : ' . $fr . '.');
    return back()->with('success', 'Application marked as ' . $en . '. The candidate has been notified.');
})->name('applications.status');

// Candidate withdraws their own application
Route::post('/applications/{id}/withdraw', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $app = DB::table('job_applications')->where('id', $id)->where('user_id', $user->id)->first();
    if (!$app) abort(404);
    if (in_array($app->status, ['withdrawn', 'rejected'], true)) {
        return back()->with('error', 'This application can no longer be withdrawn.');
    }
    DB::table('job_applications')->where('id', $id)->update(['status' => 'withdrawn', 'updated_at' => now()]);
    $job = DB::table('job_postings')->where('id', $app->job_id)->first();
    $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'A candidate';
    if ($job) {
        $title = $job->title_en ?: $job->title_fr;
        $en = $name . ' withdrew their application for "' . $title . '".';
        $fr = $name . ' a retiré sa candidature pour « ' . $title . ' ».';
        if ($job->posted_by) {
            notifyUser($job->posted_by, 'application_withdrawn', 'Application withdrawn', $en, '/jobs/' . $job->id . '/applications', 'Candidature retirée', $fr);
        } else {
            notifyCompanyOwners($job->company_id, 'application_withdrawn', 'Application withdrawn', $en, '/jobs/' . $job->id . '/applications', 'Candidature retirée', $fr);
        }
    }
    return back()->with('success', 'Application withdrawn.');
})->name('applications.withdraw');

// ─────────────────────────────────────────────
// Employee Profile (My Career Profile)
// ─────────────────────────────────────────────
Route::get('/my-profile', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user         = webUser();
    $profile      = DB::table('employee_profiles')->where('user_id', $user->id)->first();
    $applications = DB::table('job_applications')
        ->join('job_postings', 'job_postings.id', '=', 'job_applications.job_id')
        ->join('companies', 'companies.id', '=', 'job_postings.company_id')
        ->where('job_applications.user_id', $user->id)
        ->select('job_applications.*', 'job_postings.title_en', 'job_postings.title_fr', 'companies.name as company_name', 'companies.slug as company_slug')
        ->orderBy('job_applications.created_at', 'desc')->get();
    $cvs = DB::table('user_cvs')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    return view('my-profile', compact('profile', 'applications', 'cvs'));
})->name('my.profile');

Route::post('/my-profile', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate([
        'headline'            => 'nullable|string|max:120',
        'summary'             => 'nullable|string|max:2000',
        'location'            => 'nullable|string|max:100',
        'phone'               => 'nullable|string|max:20',
        'linkedin_url'        => 'nullable|url|max:255',
        'github_url'          => 'nullable|url|max:255',
        'portfolio_url'       => 'nullable|url|max:255',
        'open_to_work'        => 'nullable|in:0,1',
        'job_type_preference' => 'nullable|in:full_time,part_time,contract,internship,remote,any',
        'salary_expectation'  => 'nullable|integer|min:0',
        'skills'              => 'nullable|string',
        'languages'           => 'nullable|string',
        'experience'          => 'nullable|string',
        'education'           => 'nullable|string',
        'certifications'      => 'nullable|string',
    ]);
    $data['open_to_work'] = (int)($data['open_to_work'] ?? 1);
    // Validate JSON fields or set to empty array
    foreach (['skills','languages','experience','education','certifications'] as $f) {
        if (isset($data[$f])) {
            $decoded = json_decode($data[$f], true);
            $data[$f] = is_array($decoded) ? json_encode($decoded) : '[]';
        }
    }
    $exists = DB::table('employee_profiles')->where('user_id', $user->id)->first();
    if ($exists) {
        DB::table('employee_profiles')->where('user_id', $user->id)->update(array_merge($data, ['updated_at' => now()]));
    } else {
        DB::table('employee_profiles')->insert(array_merge($data, ['id' => Str::uuid()->toString(), 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]));
    }
    return back()->with('success', 'Career profile updated.');
})->name('my.profile.update');

// ─────────────────────────────────────────────
// CV Builder
// ─────────────────────────────────────────────
Route::get('/cv', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user    = webUser();
    $cvs = DB::table('user_cvs')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    return view('cv', compact('cvs'));
})->name('cv.index');

Route::post('/cv', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate(['title' => 'required|string|max:80', 'template' => 'required|in:classic,modern,minimal', 'color_scheme' => 'required|in:green,blue,red,dark', 'language' => 'required|in:en,fr']);
    $cvId = Str::uuid()->toString();
    DB::table('user_cvs')->insert(array_merge($data, ['id' => $cvId, 'user_id' => $user->id, 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()]));
    return redirect('/cv/'.$cvId)->with('success', 'CV created. Preview it below.');
})->name('cv.create');

Route::get('/cv/{id}', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $authUser = webUser();
    $cv       = DB::table('user_cvs')->where('id', $id)->where('user_id', $authUser->id)->first();
    if (!$cv) abort(404);
    $profile  = DB::table('employee_profiles')->where('user_id', $authUser->id)->first();
    $user     = DB::table('users')->where('id', $authUser->id)->first();
    $canEdit  = true;
    return view('cv-preview', compact('cv', 'profile', 'user', 'canEdit'));
})->name('cv.preview');

Route::get('/cv/{id}/settings', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $authUser = webUser();
    $cv = DB::table('user_cvs')->where('id', $id)->where('user_id', $authUser->id)->first();
    if (!$cv) abort(404);
    return view('cv-settings', compact('cv'));
})->name('cv.settings');

Route::post('/cv/{id}', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $authUser = webUser();
    $cv = DB::table('user_cvs')->where('id', $id)->where('user_id', $authUser->id)->first();
    if (!$cv) abort(404);
    $data = $request->validate([
        'title'        => 'required|string|max:80',
        'template'     => 'required|in:classic,modern,minimal,professional,technical,ats',
        'color_scheme' => 'required|in:green,blue,red,dark',
        'language'     => 'required|in:en,fr',
        'is_public'    => 'nullable|in:0,1',
    ]);
    $data['is_public'] = (int)($data['is_public'] ?? 0);
    if ($data['is_public'] && !$cv->public_slug) {
        $data['public_slug'] = Str::slug($cv->title.'-'.substr($id, 0, 6));
    }
    DB::table('user_cvs')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));
    return redirect('/cv/'.$id)->with('success', 'CV settings saved.');
})->name('cv.update');

// CV template gallery — industry-standard layouts
Route::get('/cv-templates', function (Request $request) {
    $authUser = webUser();
    $myCvs = $authUser ? DB::table('user_cvs')->where('user_id', $authUser->id)->orderByDesc('updated_at')->get(['id','title','template']) : collect();
    return view('cv-templates', compact('authUser', 'myCvs'));
})->name('cv.templates');

// Public shareable CV — no auth required
Route::get('/cv/{slug}/view', function (Request $request, $slug) {
    $cv = DB::table('user_cvs')->where('public_slug', $slug)->where('is_public', 1)->first();
    if (!$cv) abort(404);
    $profile = DB::table('employee_profiles')->where('user_id', $cv->user_id)->first();
    $user    = DB::table('users')->where('id', $cv->user_id)->first();
    $authUser = webUser();
    $canEdit  = $authUser && (string) $authUser->id === (string) $cv->user_id;
    return view('cv-preview', compact('cv', 'profile', 'user', 'canEdit'));
})->name('cv.public');

// ─────────────────────────────────────────────
// Messaging / Inbox
// ─────────────────────────────────────────────

/** Find or create the conversation between two users (pair stored ordered). */
function getOrCreateConversation(string $a, string $b): int
{
    [$one, $two] = strcmp($a, $b) < 0 ? [$a, $b] : [$b, $a];
    $existing = DB::table('conversations')->where('user_one_id', $one)->where('user_two_id', $two)->first();
    if ($existing) return $existing->id;
    return DB::table('conversations')->insertGetId([
        'user_one_id' => $one, 'user_two_id' => $two,
        'last_message_at' => null, 'created_at' => now(), 'updated_at' => now(),
    ]);
}

Route::get('/messages', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $convs = DB::table('conversations')
        ->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id)
        ->orderByDesc('last_message_at')->get();
    $threads = [];
    foreach ($convs as $c) {
        $otherId = $c->user_one_id === $user->id ? $c->user_two_id : $c->user_one_id;
        $other = DB::table('users')->where('id', $otherId)->first(['id', 'first_name', 'last_name']);
        if (!$other) continue;
        $last = DB::table('messages')->where('conversation_id', $c->id)->orderByDesc('id')->first();
        $unread = DB::table('messages')->where('conversation_id', $c->id)
            ->where('sender_id', '!=', $user->id)->whereNull('read_at')->count();
        $threads[] = ['other' => $other, 'last' => $last, 'unread' => $unread, 'at' => $c->last_message_at];
    }
    return view('messages', compact('threads', 'user'));
})->name('messages');

Route::get('/messages/{userId}', function (Request $request, $userId) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    if ((string) $userId === (string) $user->id) return redirect('/messages');
    $other = DB::table('users')->where('id', $userId)->first(['id', 'first_name', 'last_name']);
    if (!$other) abort(404);
    $convId = getOrCreateConversation($user->id, $userId);
    // mark incoming as read
    DB::table('messages')->where('conversation_id', $convId)
        ->where('sender_id', '!=', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
    $messages = DB::table('messages')->where('conversation_id', $convId)->orderBy('id')->get();
    return view('message-thread', compact('messages', 'other', 'user'));
})->name('messages.thread');

Route::post('/messages/{userId}', function (Request $request, $userId) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    if ((string) $userId === (string) $user->id) return redirect('/messages');
    $other = DB::table('users')->where('id', $userId)->first(['id', 'first_name', 'last_name']);
    if (!$other) abort(404);
    $data = $request->validate(['body' => 'required|string|max:4000']);
    $convId = getOrCreateConversation($user->id, $userId);
    DB::table('messages')->insert([
        'conversation_id' => $convId, 'sender_id' => $user->id,
        'body' => $data['body'], 'read_at' => null, 'created_at' => now(),
    ]);
    DB::table('conversations')->where('id', $convId)->update(['last_message_at' => now(), 'updated_at' => now()]);
    $senderName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Someone';
    notifyUser($userId, 'message', 'New message from ' . $senderName,
        \Illuminate\Support\Str::limit($data['body'], 80), '/messages/' . $user->id,
        'Nouveau message de ' . $senderName, \Illuminate\Support\Str::limit($data['body'], 80));
    return redirect('/messages/' . $userId)->with('success', 'Message sent.');
})->name('messages.send');

// ─────────────────────────────────────────────
// Talent Directory — discover candidates open to work
// ─────────────────────────────────────────────
Route::get('/talent', function (Request $request) {
    $skill = trim($request->get('skill', ''));
    $loc   = trim($request->get('location', ''));
    $jobType = $request->get('job_type', '');
    $query = DB::table('employee_profiles')
        ->join('users', 'employee_profiles.user_id', '=', 'users.id')
        ->where('employee_profiles.open_to_work', 1)
        ->select('employee_profiles.*', 'users.first_name', 'users.last_name');
    if ($skill) $query->where('employee_profiles.skills', 'like', "%$skill%");
    if ($loc)   $query->where('employee_profiles.location', 'like', "%$loc%");
    if ($jobType) $query->where('employee_profiles.job_type_preference', $jobType);
    $candidates = $query->orderByDesc('employee_profiles.updated_at')->limit(60)->get();
    // public CVs keyed by user for "View CV" links
    $cvSlugs = DB::table('user_cvs')->where('is_public', 1)
        ->whereIn('user_id', $candidates->pluck('user_id'))
        ->pluck('public_slug', 'user_id');
    $total = DB::table('employee_profiles')->where('open_to_work', 1)->count();
    return view('talent', compact('candidates', 'cvSlugs', 'total', 'skill', 'loc', 'jobType'));
})->name('talent');

Route::get('/talent/{id}', function (Request $request, $id) {
    $profile = DB::table('employee_profiles')
        ->join('users', 'employee_profiles.user_id', '=', 'users.id')
        ->where('employee_profiles.user_id', $id)
        ->where('employee_profiles.open_to_work', 1)
        ->select('employee_profiles.*', 'users.first_name', 'users.last_name', 'users.email')
        ->first();
    if (!$profile) abort(404);
    $publicCv = DB::table('user_cvs')->where('user_id', $id)->where('is_public', 1)->value('public_slug');
    $viewer   = webUser();
    return view('talent-profile', compact('profile', 'publicCv', 'viewer'));
})->name('talent.profile');

// ─────────────────────────────────────────────
// Cover Letter Builder
// ─────────────────────────────────────────────

/** Generate a professional starter cover-letter body for a given tone. */
function coverLetterBody(string $tone, string $jobTitle, string $company, string $name, string $headline = ''): string
{
    $job  = $jobTitle ?: 'the advertised role';
    $co   = $company ?: 'your organisation';
    $intro = $headline ? "As {$headline}, " : 'As an experienced professional, ';
    switch ($tone) {
        case 'enthusiastic':
            return "I was thrilled to come across the {$job} opening at {$co}, and I am excited to apply. {$intro}I bring genuine passion and a track record of delivering results that I believe align perfectly with your team's goals.\n\n"
                . "Throughout my career I have thrived in fast-moving environments, taking ownership of challenges and turning them into measurable wins. What draws me to {$co} is your reputation and the opportunity to contribute to work that truly matters.\n\n"
                . "I would welcome the chance to discuss how my energy and experience can add value to your team. Thank you for considering my application — I look forward to the possibility of contributing to {$co}.";
        case 'concise':
            return "I am applying for the {$job} position at {$co}. {$intro}I am confident my skills are a strong match for your requirements.\n\n"
                . "In previous roles I have consistently delivered results, collaborated effectively across teams, and adapted quickly to new challenges. I am eager to bring the same focus and reliability to {$co}.\n\n"
                . "Thank you for your time and consideration. I would welcome the opportunity to discuss my application further.";
        case 'career_change':
            return "I am writing to apply for the {$job} role at {$co}. While my background spans a different field, {$intro}I am making a deliberate and well-prepared transition, bringing a fresh perspective and a strong, transferable skill set.\n\n"
                . "My experience has equipped me with problem-solving, communication, and project-delivery skills that translate directly to this role. I have invested in building the specific competencies required and am committed to contributing from day one.\n\n"
                . "I would be grateful for the opportunity to explain how my unique path makes me a strong fit for {$co}. Thank you for considering my application.";
        default: // formal
            return "I am writing to express my strong interest in the {$job} position at {$co}. {$intro}I am confident that my experience and skills make me a strong candidate for this opportunity.\n\n"
                . "In my previous roles I have developed a solid track record of delivering results, working effectively within teams, and meeting demanding objectives. I am particularly drawn to {$co} and the opportunity to contribute to your continued success.\n\n"
                . "I would welcome the opportunity to discuss my application in more detail. Thank you for your time and consideration; I look forward to hearing from you.";
    }
}

Route::get('/cover-letters', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $letters = DB::table('cover_letters')->where('user_id', $user->id)->orderByDesc('updated_at')->get();
    $prefill = ['job_title' => $request->get('job_title', ''), 'company_name' => $request->get('company', '')];
    return view('cover-letters', compact('letters', 'prefill'));
})->name('cover.letters');

Route::post('/cover-letters', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $data = $request->validate([
        'title'        => 'required|string|max:100',
        'recipient_name' => 'nullable|string|max:100',
        'company_name' => 'nullable|string|max:120',
        'job_title'    => 'nullable|string|max:120',
        'tone'         => 'required|in:formal,enthusiastic,concise,career_change',
    ]);
    $dbUser  = DB::table('users')->where('id', $user->id)->first();
    $name    = trim(($dbUser->first_name ?? '') . ' ' . ($dbUser->last_name ?? '')) ?: 'Applicant';
    $headline = DB::table('employee_profiles')->where('user_id', $user->id)->value('headline') ?? '';
    $body = coverLetterBody($data['tone'], $data['job_title'] ?? '', $data['company_name'] ?? '', $name, $headline);
    $id = Str::uuid()->toString();
    DB::table('cover_letters')->insert([
        'id'             => $id,
        'user_id'        => $user->id,
        'title'          => $data['title'],
        'recipient_name' => $data['recipient_name'] ?? null,
        'company_name'   => $data['company_name'] ?? null,
        'job_title'      => $data['job_title'] ?? null,
        'body'           => $body,
        'tone'           => $data['tone'],
        'template'       => 'classic',
        'accent_color'   => '#007a33',
        'created_at'     => now(), 'updated_at' => now(),
    ]);
    return redirect('/cover-letters/' . $id . '/edit')->with('success', 'Draft created — personalise it below.');
})->name('cover.letters.store');

Route::get('/cover-letters/{id}/edit', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $letter = DB::table('cover_letters')->where('id', $id)->where('user_id', $user->id)->first();
    if (!$letter) abort(404);
    return view('cover-letter-edit', compact('letter'));
})->name('cover.letters.edit');

Route::post('/cover-letters/{id}', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $letter = DB::table('cover_letters')->where('id', $id)->where('user_id', $user->id)->first();
    if (!$letter) abort(404);
    $data = $request->validate([
        'title'          => 'required|string|max:100',
        'recipient_name' => 'nullable|string|max:100',
        'company_name'   => 'nullable|string|max:120',
        'job_title'      => 'nullable|string|max:120',
        'body'           => 'required|string|max:6000',
        'template'       => 'required|in:classic,modern,minimal',
        'accent_color'   => 'required|in:#007a33,#0056b3,#ce1126,#1a1a2e',
        'is_public'      => 'nullable|in:0,1',
    ]);
    $data['is_public'] = (int) ($data['is_public'] ?? 0);
    if ($data['is_public'] && !$letter->public_slug) {
        $data['public_slug'] = Str::slug($data['title'] . '-' . substr($id, 0, 6));
    }
    DB::table('cover_letters')->where('id', $id)->update(array_merge($data, ['updated_at' => now()]));
    return redirect('/cover-letters/' . $id)->with('success', 'Cover letter saved.');
})->name('cover.letters.update');

Route::post('/cover-letters/{id}/delete', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $deleted = DB::table('cover_letters')->where('id', $id)->where('user_id', $user->id)->delete();
    if (!$deleted) abort(404);
    return redirect('/cover-letters')->with('success', 'Cover letter deleted.');
})->name('cover.letters.delete');

Route::get('/cover-letters/{id}', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $user = webUser();
    $letter = DB::table('cover_letters')->where('id', $id)->where('user_id', $user->id)->first();
    if (!$letter) abort(404);
    $dbUser = DB::table('users')->where('id', $user->id)->first();
    $profile = DB::table('employee_profiles')->where('user_id', $user->id)->first();
    $canEdit = true;
    return view('cover-letter-preview', compact('letter', 'dbUser', 'profile', 'canEdit'));
})->name('cover.letters.preview');

Route::get('/cover-letter/{slug}/view', function (Request $request, $slug) {
    $letter = DB::table('cover_letters')->where('public_slug', $slug)->where('is_public', 1)->first();
    if (!$letter) abort(404);
    $dbUser = DB::table('users')->where('id', $letter->user_id)->first();
    $profile = DB::table('employee_profiles')->where('user_id', $letter->user_id)->first();
    $authUser = webUser();
    $canEdit = $authUser && (string) $authUser->id === (string) $letter->user_id;
    return view('cover-letter-preview', compact('letter', 'dbUser', 'profile', 'canEdit'));
})->name('cover.letters.public');

// ─────────────────────────────────────────────
// Developer / API Keys
// ─────────────────────────────────────────────
Route::get('/developer', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user    = webUser();
    $keys     = DB::table('api_keys')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
    $keyCount = $keys->where('is_active', 1)->count();
    return view('developer', compact('keys', 'keyCount'));
})->name('developer');

Route::post('/developer/keys', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $user  = webUser();
    $data  = $request->validate(['name' => 'required|string|max:60']);
    $plain = 'cc_' . Str::random(40);
    DB::table('api_keys')->insert(['id' => Str::uuid()->toString(), 'user_id' => $user->id, 'name' => $data['name'], 'key' => hash('sha256', $plain), 'key_prefix' => substr($plain, 0, 8), 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
    return back()->with('success', 'API key created: ' . $plain . ' — copy it now, it will not be shown again.');
})->name('developer.keys.create');

Route::post('/developer/keys/{id}/revoke', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    DB::table('api_keys')->where('id', $id)->where('user_id', webUser()->id)->update(['is_active' => 0, 'updated_at' => now()]);
    return back()->with('success', 'API key revoked.');
})->name('developer.keys.revoke');

// ═════════════════════════════════════════════════════════════════════
// ADMIN PANEL
// ═════════════════════════════════════════════════════════════════════

function requireAdmin(Request $request)
{
    if (!session('auth_user')) return redirect('/login?next=' . urlencode($request->fullUrl()));
    if (!session('auth_user')['is_admin']) abort(403, 'Admin access required.');
    return null;
}

// ─────────────────────────────────────────────
// Admin Dashboard
// ─────────────────────────────────────────────
Route::get('/admin', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;

    $stats = [
        'users'        => DB::table('users')->whereNull('deleted_at')->count(),
        'companies'    => DB::table('companies')->whereNull('deleted_at')->count(),
        'offerings'    => DB::table('share_offerings')->whereNull('deleted_at')->count(),
        'pledges'      => DB::table('investment_pledges')->count(),
        'jobs'         => DB::table('job_postings')->where('status','open')->whereNull('deleted_at')->count(),
        'applications' => DB::table('job_applications')->count(),
        'tickets'      => DB::table('tickets')->where('status','open')->count(),
        'kyc_pending'  => DB::table('kyc_applications')->where('status','pending')->count(),
        'claims_pending'=> DB::table('verification_applications')->where('status','pending')->count(),
    ];

    $recentUsers = DB::table('users')->orderByDesc('created_at')->limit(8)->get();
    $recentPledges = DB::table('investment_pledges')
        ->join('users','users.id','=','investment_pledges.investor_id')
        ->join('share_offerings','share_offerings.id','=','investment_pledges.offering_id')
        ->select('investment_pledges.*','users.first_name','users.last_name','share_offerings.title_en')
        ->orderByDesc('investment_pledges.created_at')->limit(8)->get();

    return view('admin.dashboard', compact('stats','recentUsers','recentPledges'));
})->name('admin.dashboard');

// ─────────────────────────────────────────────
// Admin — Users
// ─────────────────────────────────────────────
Route::get('/admin/users', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $q = $request->get('q','');
    $query = DB::table('users')->whereNull('deleted_at');
    if ($q) $query->where(function($qq) use ($q) {
        $qq->where('email','like',"%$q%")->orWhere('first_name','like',"%$q%")->orWhere('last_name','like',"%$q%");
    });
    $users = $query->orderByDesc('created_at')->paginate(20);
    return view('admin.users', compact('users','q'));
})->name('admin.users');

Route::post('/admin/users/{id}/toggle-status', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $user = DB::table('users')->where('id',$id)->first();
    if (!$user) abort(404);
    $newStatus = $user->status === 'active' ? 'suspended' : 'active';
    DB::table('users')->where('id',$id)->update(['status'=>$newStatus,'updated_at'=>now()]);
    return back()->with('success', "User {$user->email} is now {$newStatus}.");
})->name('admin.users.toggle');

Route::post('/admin/users/{id}/toggle-admin', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $user = DB::table('users')->where('id',$id)->first();
    if (!$user) abort(404);
    DB::table('users')->where('id',$id)->update(['is_admin' => !$user->is_admin,'updated_at'=>now()]);
    return back()->with('success', "Admin status toggled for {$user->email}.");
})->name('admin.users.toggle-admin');

// ─────────────────────────────────────────────
// Admin — KYC Applications
// ─────────────────────────────────────────────
Route::get('/admin/kyc', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $status = $request->get('status','pending');
    $applications = DB::table('kyc_applications')
        ->join('users','users.id','=','kyc_applications.user_id')
        ->where('kyc_applications.status', $status)
        ->select('kyc_applications.*','users.first_name','users.last_name','users.email')
        ->orderByDesc('kyc_applications.created_at')->paginate(20);
    return view('admin.kyc', compact('applications','status'));
})->name('admin.kyc');

Route::post('/admin/kyc/{id}/approve', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $admin = webUser();
    $kyc = DB::table('kyc_applications')->where('id',$id)->first();
    if (!$kyc) abort(404);
    DB::table('kyc_applications')->where('id',$id)->update([
        'status'      => 'approved',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
        'updated_at'  => now(),
    ]);
    DB::table('notifications')->insert([
        'id'       => Str::uuid()->toString(),
        'user_id'  => $kyc->user_id,
        'type'     => 'kyc_approved',
        'title_en' => 'KYC Approved',
        'body_en'  => 'Your investor profile has been verified. You can now invest in share offerings.',
        'title_fr' => 'KYC Approuvé',
        'body_fr'  => 'Votre profil investisseur a été vérifié. Vous pouvez maintenant investir.',
        'read_at'  => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    return back()->with('success', 'KYC application approved.');
})->name('admin.kyc.approve');

Route::post('/admin/kyc/{id}/reject', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $admin = webUser();
    $kyc = DB::table('kyc_applications')->where('id',$id)->first();
    if (!$kyc) abort(404);
    $reason = $request->input('reason', 'Your application did not meet our requirements.');
    DB::table('kyc_applications')->where('id',$id)->update([
        'status'               => 'rejected',
        'rejection_reason_en'  => $reason,
        'reviewed_by'          => $admin->id,
        'reviewed_at'          => now(),
        'updated_at'           => now(),
    ]);
    DB::table('notifications')->insert([
        'id'       => Str::uuid()->toString(),
        'user_id'  => $kyc->user_id,
        'type'     => 'kyc_rejected',
        'title_en' => 'KYC Rejected',
        'body_en'  => 'Your investor KYC application was not approved. Reason: ' . $reason,
        'title_fr' => 'KYC Refusé',
        'body_fr'  => 'Votre demande KYC investisseur n\'a pas été approuvée. Raison: ' . $reason,
        'read_at'  => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    return back()->with('success', 'KYC application rejected.');
})->name('admin.kyc.reject');

// ─────────────────────────────────────────────
// Admin — Company Claims / Verifications
// ─────────────────────────────────────────────
Route::get('/admin/claims', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $status = $request->get('status','pending');
    $claims = DB::table('verification_applications')
        ->join('companies','companies.id','=','verification_applications.company_id')
        ->join('users','users.id','=','verification_applications.submitted_by')
        ->where('verification_applications.status', $status)
        ->select('verification_applications.*','companies.name as company_name','companies.slug as company_slug','users.first_name','users.last_name','users.email')
        ->orderByDesc('verification_applications.created_at')->paginate(20);
    return view('admin.claims', compact('claims','status'));
})->name('admin.claims');

Route::post('/admin/claims/{id}/approve', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $admin = webUser();
    $claim = DB::table('verification_applications')->where('id',$id)->first();
    if (!$claim) abort(404);
    DB::table('verification_applications')->where('id',$id)->update([
        'status'      => 'approved',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
        'updated_at'  => now(),
    ]);
    // Add claimant as company member
    if (!DB::table('company_users')->where('company_id',$claim->company_id)->where('user_id',$claim->submitted_by)->exists()) {
        DB::table('company_users')->insert(['id'=>Str::uuid()->toString(),'company_id'=>$claim->company_id,'user_id'=>$claim->submitted_by,'role'=>'owner','is_active'=>1,'joined_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
    }
    // Upgrade company verification status
    DB::table('companies')->where('id',$claim->company_id)->update(['verification_status'=>'verified','updated_at'=>now()]);
    DB::table('notifications')->insert(['id'=>Str::uuid()->toString(),'user_id'=>$claim->submitted_by,'type'=>'claim_approved','title_en'=>'Company Claim Approved','body_en'=>'Your company claim has been approved. You now have owner access.','title_fr'=>'Réclamation approuvée','body_fr'=>'Votre réclamation d\'entreprise a été approuvée.','read_at'=>null,'created_at'=>now(),'updated_at'=>now()]);
    return back()->with('success', 'Claim approved and company upgraded to verified.');
})->name('admin.claims.approve');

Route::post('/admin/claims/{id}/reject', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $admin = webUser();
    $claim = DB::table('verification_applications')->where('id',$id)->first();
    if (!$claim) abort(404);
    $reason = $request->input('reason','Your claim did not meet our verification requirements.');
    DB::table('verification_applications')->where('id',$id)->update(['status'=>'rejected','rejection_reason_en'=>$reason,'reviewed_by'=>$admin->id,'reviewed_at'=>now(),'updated_at'=>now()]);
    DB::table('notifications')->insert(['id'=>Str::uuid()->toString(),'user_id'=>$claim->submitted_by,'type'=>'claim_rejected','title_en'=>'Company Claim Rejected','body_en'=>'Your company claim was not approved. Reason: '.$reason,'title_fr'=>'Réclamation refusée','body_fr'=>'Votre réclamation n\'a pas été approuvée. Raison: '.$reason,'read_at'=>null,'created_at'=>now(),'updated_at'=>now()]);
    return back()->with('success', 'Claim rejected.');
})->name('admin.claims.reject');

// ─────────────────────────────────────────────
// Admin — Support Tickets
// ─────────────────────────────────────────────
Route::get('/admin/tickets', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $status = $request->get('status','open');
    $tickets = DB::table('tickets')
        ->join('users','users.id','=','tickets.user_id')
        ->where('tickets.status', $status)
        ->select('tickets.*','users.first_name','users.last_name','users.email')
        ->orderByDesc('tickets.created_at')->paginate(20);
    return view('admin.tickets', compact('tickets','status'));
})->name('admin.tickets');

Route::get('/admin/tickets/{id}', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $ticket = DB::table('tickets')->join('users','users.id','=','tickets.user_id')->where('tickets.id',$id)->select('tickets.*','users.first_name','users.last_name','users.email')->first();
    if (!$ticket) abort(404);
    $messages = DB::table('ticket_messages')->where('ticket_id',$id)->orderBy('created_at')->get();
    return view('admin.ticket-detail', compact('ticket','messages'));
})->name('admin.tickets.show');

Route::post('/admin/tickets/{id}/reply', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $admin = webUser();
    $ticket = DB::table('tickets')->where('id',$id)->first();
    if (!$ticket) abort(404);
    $data = $request->validate(['body' => 'required|string|min:5|max:5000']);
    DB::table('ticket_messages')->insert(['id'=>Str::uuid()->toString(),'ticket_id'=>$id,'author_id'=>$admin->id,'body'=>$data['body'],'is_internal'=>0,'is_from_staff'=>1,'created_at'=>now(),'updated_at'=>now()]);
    DB::table('tickets')->where('id',$id)->update(['status'=>'waiting_user','first_response_at'=>$ticket->first_response_at ?? now(),'updated_at'=>now()]);
    DB::table('notifications')->insert(['id'=>Str::uuid()->toString(),'user_id'=>$ticket->user_id,'type'=>'ticket_reply','title_en'=>'Support Reply','body_en'=>'A staff member replied to your support ticket #'.$ticket->ticket_number.'.','title_fr'=>'Réponse support','body_fr'=>'Un membre du staff a répondu à votre ticket #'.$ticket->ticket_number.'.','read_at'=>null,'created_at'=>now(),'updated_at'=>now()]);
    return back()->with('success', 'Reply sent.');
})->name('admin.tickets.reply');

Route::post('/admin/tickets/{id}/close', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    DB::table('tickets')->where('id',$id)->update(['status'=>'closed','resolved_at'=>now(),'updated_at'=>now()]);
    return redirect('/admin/tickets')->with('success', 'Ticket closed.');
})->name('admin.tickets.close');

// ─────────────────────────────────────────────
// Admin — Announcements
// ─────────────────────────────────────────────
Route::get('/admin/announcements', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $announcements = DB::table('announcements')->orderByDesc('created_at')->get();
    return view('admin.announcements', compact('announcements'));
})->name('admin.announcements');

Route::post('/admin/announcements', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $data = $request->validate([
        'body_en'    => 'required|string|max:300',
        'body_fr'    => 'nullable|string|max:300',
        'starts_at'  => 'required|date',
        'ends_at'    => 'required|date|after:starts_at',
    ]);
    DB::table('announcements')->insert(array_merge($data,[
        'body_fr'      => $data['body_fr'] ?? $data['body_en'],
        'is_published' => 1,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]));
    return back()->with('success', 'Announcement published.');
})->name('admin.announcements.create');

Route::post('/admin/announcements/{id}/delete', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    DB::table('announcements')->where('id',$id)->delete();
    return back()->with('success', 'Announcement deleted.');
})->name('admin.announcements.delete');

Route::post('/admin/announcements/{id}/toggle', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $ann = DB::table('announcements')->where('id',$id)->first();
    if (!$ann) abort(404);
    DB::table('announcements')->where('id',$id)->update(['is_published' => !$ann->is_published, 'updated_at' => now()]);
    return back()->with('success', 'Announcement toggled.');
})->name('admin.announcements.toggle');

// ─────────────────────────────────────────────
// Admin — Companies management
// ─────────────────────────────────────────────
Route::get('/admin/companies', function (Request $request) {
    if ($r = requireAdmin($request)) return $r;
    $q = $request->get('q','');
    $query = DB::table('companies')->whereNull('deleted_at');
    if ($q) $query->where(function($qq) use($q){ $qq->where('name','like',"%$q%")->orWhere('email','like',"%$q%"); });
    $companies = $query->orderByDesc('created_at')->paginate(20);
    return view('admin.companies', compact('companies','q'));
})->name('admin.companies');

Route::post('/admin/companies/{id}/verify', function (Request $request, $id) {
    if ($r = requireAdmin($request)) return $r;
    $level = $request->input('level','verified');
    if (!in_array($level,['basic','verified','certified'])) $level = 'verified';
    DB::table('companies')->where('id',$id)->update(['verification_status'=>$level,'updated_at'=>now()]);
    return back()->with('success', "Company set to $level.");
})->name('admin.companies.verify');

// ═════════════════════════════════════════════
// ASSOCIATIONS DIRECTORY
// ═════════════════════════════════════════════

Route::get('/associations', function (Request $request) {
    return view('associations');
})->name('associations.index');

Route::get('/associations/{slug}', function (Request $request, $slug) {
    $assoc = DB::table('associations')
        ->leftJoin('regions','associations.region_id','=','regions.id')
        ->where('associations.slug', $slug)
        ->where('associations.is_active', 1)
        ->whereNull('associations.deleted_at')
        ->select('associations.*', 'regions.name_en as region_name')
        ->first();
    if (!$assoc) abort(404);
    DB::table('associations')->where('id',$assoc->id)->increment('view_count');
    $related = DB::table('associations')
        ->leftJoin('regions','associations.region_id','=','regions.id')
        ->where('associations.sector', $assoc->sector)
        ->where('associations.id', '!=', $assoc->id)
        ->where('associations.is_active', 1)
        ->whereNull('associations.deleted_at')
        ->select('associations.*','regions.name_en as region_name')
        ->limit(4)->get();
    return view('association', compact('assoc','related'));
})->name('associations.show');

// ═════════════════════════════════════════════
// COLLABCAM
// ═════════════════════════════════════════════

Route::get('/collabcam', function (Request $request) {
    return view('collabcam');
})->name('collabcam.landing');

Route::get('/collabcam/explore', function (Request $request) {
    return view('collabcam-explore');
})->name('collabcam.explore');

Route::get('/collabcam/opportunities', function (Request $request) {
    return view('collabcam-opportunities');
})->name('collabcam.opportunities');

Route::post('/collabcam/opportunities', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $data = $request->validate([
        'company_id'    => 'required|string',
        'title_en'      => 'required|string|max:255',
        'type'          => 'required|string',
        'description_en'=> 'required|string',
        'sector'        => 'nullable|string|max:100',
        'budget_range'  => 'nullable|string|max:100',
        'location'      => 'nullable|string|max:100',
        'deadline'      => 'nullable|date',
    ]);
    $user = webUser();
    // Verify user owns this company
    $owns = DB::table('company_users')
        ->where('user_id', $user->id)
        ->where('company_id', $data['company_id'])
        ->where('is_active',1)
        ->exists();
    if (!$owns) abort(403);
    $id = Str::uuid()->toString();
    DB::table('collabcam_opportunities')->insert([
        'id'            => $id,
        'company_id'    => $data['company_id'],
        'title_en'      => $data['title_en'],
        'type'          => $data['type'],
        'description_en'=> $data['description_en'],
        'sector'        => $data['sector'] ?? null,
        'budget_range'  => $data['budget_range'] ?? null,
        'location'      => $data['location'] ?? null,
        'deadline'      => $data['deadline'] ?? null,
        'status'        => 'active',
        'view_count'    => 0,
        'response_count'=> 0,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect('/collabcam/opportunities/'.$id)->with('success', 'Opportunity posted successfully!');
})->name('collabcam.opportunities.store');

Route::get('/collabcam/opportunities/{id}', function (Request $request, $id) {
    $opp = DB::table('collabcam_opportunities')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$opp) abort(404);
    $company = DB::table('companies')->where('id',$opp->company_id)->first();
    if (!$company) abort(404);
    DB::table('collabcam_opportunities')->where('id',$id)->increment('view_count');
    return view('collabcam-opportunity', compact('opp','company'));
})->name('collabcam.opportunity.show');

Route::post('/collabcam/opportunities/{id}/respond', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $data = $request->validate([
        'company_id'    => 'required|string',
        'message'       => 'required|string',
        'proposed_terms'=> 'nullable|string|max:255',
    ]);
    $user = webUser();
    $opp = DB::table('collabcam_opportunities')->where('id',$id)->where('status','active')->whereNull('deleted_at')->first();
    if (!$opp) return back()->with('error','Opportunity not found or closed.');
    // Can't respond to your own opportunity
    if ($opp->company_id === $data['company_id']) return back()->with('error','You cannot respond to your own opportunity.');
    $owns = DB::table('company_users')
        ->where('user_id',$user->id)->where('company_id',$data['company_id'])->where('is_active',1)->exists();
    if (!$owns) abort(403);
    $exists = DB::table('collabcam_opportunity_responses')
        ->where('opportunity_id',$id)->where('company_id',$data['company_id'])->exists();
    if ($exists) return back()->with('error','You have already responded to this opportunity.');
    DB::table('collabcam_opportunity_responses')->insert([
        'id'            => Str::uuid()->toString(),
        'opportunity_id'=> $id,
        'company_id'    => $data['company_id'],
        'message'       => $data['message'],
        'proposed_terms'=> $data['proposed_terms'] ?? null,
        'status'        => 'pending',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    DB::table('collabcam_opportunities')->where('id',$id)->increment('response_count');
    return back()->with('success','Your response has been submitted!');
})->name('collabcam.opportunity.respond');

Route::post('/collabcam/opportunity-responses/{id}/shortlist', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    DB::table('collabcam_opportunity_responses')->where('id',$id)->update(['status'=>'shortlisted','updated_at'=>now()]);
    return back()->with('success','Response shortlisted.');
});
Route::post('/collabcam/opportunity-responses/{id}/reject', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    DB::table('collabcam_opportunity_responses')->where('id',$id)->update(['status'=>'rejected','updated_at'=>now()]);
    return back()->with('success','Response rejected.');
});

// Send a collaboration request
Route::post('/collabcam/request', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $data = $request->validate([
        'from_company_id' => 'required|string',
        'to_company_id'   => 'required|string',
        'subject'         => 'required|string|max:255',
        'message'         => 'required|string',
        'collab_type'     => 'required|string',
    ]);
    if ($data['from_company_id'] === $data['to_company_id'])
        return back()->with('error','You cannot collaborate with yourself.');
    $user = webUser();
    $owns = DB::table('company_users')
        ->where('user_id',$user->id)->where('company_id',$data['from_company_id'])->where('is_active',1)->exists();
    if (!$owns) abort(403);
    // Check no existing pending request between these companies
    $existing = DB::table('collabcam_requests')
        ->where('from_company_id',$data['from_company_id'])
        ->where('to_company_id',$data['to_company_id'])
        ->where('status','pending')->exists();
    if ($existing) return back()->with('error','You already have a pending request to this company.');
    DB::table('collabcam_requests')->insert([
        'id'              => Str::uuid()->toString(),
        'from_company_id' => $data['from_company_id'],
        'to_company_id'   => $data['to_company_id'],
        'initiated_by'    => $user->id,
        'subject'         => $data['subject'],
        'message'         => $data['message'],
        'collab_type'     => $data['collab_type'],
        'status'          => 'pending',
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    // Notify the target company owner(s)
    $toOwners = DB::table('company_users')->where('company_id',$data['to_company_id'])->where('is_active',1)->pluck('user_id');
    $fromCo = DB::table('companies')->where('id',$data['from_company_id'])->value('name');
    foreach($toOwners as $ownerId) {
        DB::table('notifications')->insert([
            'id'       => Str::uuid()->toString(),
            'user_id'  => $ownerId,
            'type'     => 'collabcam_request',
            'title_en' => "New collaboration request from $fromCo",
            'body_en'  => $data['subject'],
            'url'      => '/collabcam/hub',
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }
    return redirect('/collabcam/hub')->with('success','Collaboration request sent!');
})->name('collabcam.request');

// Accept a collaboration request
Route::post('/collabcam/requests/{id}/accept', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $req = DB::table('collabcam_requests')->where('id',$id)->where('status','pending')->first();
    if (!$req) return back()->with('error','Request not found.');
    $user = webUser();
    $owns = DB::table('company_users')
        ->where('user_id',$user->id)->where('company_id',$req->to_company_id)->where('is_active',1)->exists();
    if (!$owns) abort(403);
    // Create collaboration
    $fromCo = DB::table('companies')->where('id',$req->from_company_id)->value('name');
    $toCo   = DB::table('companies')->where('id',$req->to_company_id)->value('name');
    $collabId = Str::uuid()->toString();
    DB::table('collabcam_collaborations')->insert([
        'id'         => $collabId,
        'name'       => $fromCo.' × '.$toCo,
        'description'=> $req->subject,
        'type'       => $req->collab_type,
        'status'     => 'active',
        'start_date' => now()->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('collabcam_collaboration_members')->insert([
        ['collaboration_id'=>$collabId,'company_id'=>$req->from_company_id,'role'=>'initiator','status'=>'active','created_at'=>now(),'updated_at'=>now()],
        ['collaboration_id'=>$collabId,'company_id'=>$req->to_company_id,  'role'=>'partner',   'status'=>'active','created_at'=>now(),'updated_at'=>now()],
    ]);
    DB::table('collabcam_requests')->where('id',$id)->update(['status'=>'accepted','collaboration_id'=>$collabId,'updated_at'=>now()]);
    // Notify initiator
    $initiatorOwners = DB::table('company_users')->where('company_id',$req->from_company_id)->where('is_active',1)->pluck('user_id');
    foreach($initiatorOwners as $ownerId) {
        DB::table('notifications')->insert([
            'id'       => Str::uuid()->toString(),
            'user_id'  => $ownerId,
            'type'     => 'collabcam_accepted',
            'title_en' => "$toCo accepted your collaboration request",
            'body_en'  => "Your collaboration workspace is ready.",
            'url'      => '/collabcam/workspace/'.$collabId,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }
    return redirect('/collabcam/workspace/'.$collabId)->with('success','Collaboration accepted! Your workspace is ready.');
})->name('collabcam.request.accept');

// Reject a collaboration request
Route::post('/collabcam/requests/{id}/reject', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $req = DB::table('collabcam_requests')->where('id',$id)->where('status','pending')->first();
    if (!$req) return back()->with('error','Request not found.');
    $user = webUser();
    $owns = DB::table('company_users')
        ->where('user_id',$user->id)->where('company_id',$req->to_company_id)->where('is_active',1)->exists();
    if (!$owns) abort(403);
    DB::table('collabcam_requests')->where('id',$id)->update(['status'=>'rejected','updated_at'=>now()]);
    return back()->with('success','Request declined.');
})->name('collabcam.request.reject');

// My collaboration hub
Route::get('/collabcam/hub', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    return view('collabcam-hub');
})->name('collabcam.hub');

// Workspace
Route::get('/collabcam/workspace/{id}', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $collab = DB::table('collabcam_collaborations')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$collab) abort(404);
    $members = DB::table('collabcam_collaboration_members')
        ->join('companies','collabcam_collaboration_members.company_id','=','companies.id')
        ->where('collaboration_id',$id)
        ->select('collabcam_collaboration_members.*','companies.name as company_name','companies.slug as company_slug','collabcam_collaboration_members.status as member_status')
        ->get();
    $user = webUser();
    $myCompanyIds = DB::table('company_users')->where('user_id',$user->id)->where('is_active',1)->pluck('company_id')->toArray();
    $isMember = $members->whereIn('company_id', $myCompanyIds)->count() > 0;
    if (!$isMember) abort(403, 'You are not a member of this collaboration.');
    $contracts  = DB::table('collabcam_contracts')->where('collaboration_id',$id)->whereNull('deleted_at')->orderByDesc('created_at')->get();
    $milestones = DB::table('collabcam_milestones')->where('collaboration_id',$id)->orderBy('sort_order')->get();
    return view('collabcam-workspace', compact('collab','members','contracts','milestones'));
})->name('collabcam.workspace');

// Add contract to workspace
Route::post('/collabcam/workspace/{id}/contract', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $data = $request->validate(['title'=>'required|string|max:255','type'=>'required|string','effective_date'=>'nullable|date','expiry_date'=>'nullable|date']);
    $collab = DB::table('collabcam_collaborations')->where('id',$id)->first();
    if (!$collab) abort(404);
    $user = webUser();
    $myCompanyIds = DB::table('company_users')->where('user_id',$user->id)->where('is_active',1)->pluck('company_id')->toArray();
    $isMember = DB::table('collabcam_collaboration_members')
        ->where('collaboration_id',$id)->whereIn('company_id',$myCompanyIds)->where('status','active')->exists();
    if (!$isMember) abort(403);
    // Gather party company IDs
    $parties = DB::table('collabcam_collaboration_members')->where('collaboration_id',$id)->pluck('company_id')->toArray();
    DB::table('collabcam_contracts')->insert([
        'id'            => Str::uuid()->toString(),
        'collaboration_id'=> $id,
        'title'         => $data['title'],
        'type'          => $data['type'],
        'status'        => 'draft',
        'parties'       => json_encode($parties),
        'signatories'   => json_encode([]),
        'effective_date'=> $data['effective_date'] ?? null,
        'expiry_date'   => $data['expiry_date'] ?? null,
        'all_signed'    => false,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return back()->with('success','Contract added.');
})->name('collabcam.workspace.contract');

// Add milestone to workspace
Route::post('/collabcam/workspace/{id}/milestone', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $data = $request->validate(['title'=>'required|string|max:255','due_date'=>'nullable|date']);
    $user = webUser();
    $myCompanyIds = DB::table('company_users')->where('user_id',$user->id)->where('is_active',1)->pluck('company_id')->toArray();
    $isMember = DB::table('collabcam_collaboration_members')
        ->where('collaboration_id',$id)->whereIn('company_id',$myCompanyIds)->where('status','active')->exists();
    if (!$isMember) abort(403);
    $maxSort = DB::table('collabcam_milestones')->where('collaboration_id',$id)->max('sort_order') ?? 0;
    DB::table('collabcam_milestones')->insert([
        'id'             => Str::uuid()->toString(),
        'collaboration_id'=> $id,
        'title'          => $data['title'],
        'due_date'       => $data['due_date'] ?? null,
        'status'         => 'pending',
        'sort_order'     => $maxSort + 1,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
    DB::table('collabcam_collaborations')->where('id',$id)->update([
        'milestones_total' => DB::raw('milestones_total + 1'),
        'updated_at' => now(),
    ]);
    return back()->with('success','Milestone added.');
})->name('collabcam.workspace.milestone');

// Complete milestone
Route::post('/collabcam/milestones/{id}/complete', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $m = DB::table('collabcam_milestones')->where('id',$id)->first();
    if (!$m || $m->status === 'completed') return back();
    DB::table('collabcam_milestones')->where('id',$id)->update(['status'=>'completed','completed_at'=>now(),'updated_at'=>now()]);
    DB::table('collabcam_collaborations')->where('id',$m->collaboration_id)->update([
        'milestones_completed' => DB::raw('milestones_completed + 1'),
        'updated_at' => now(),
    ]);
    return back()->with('success','Milestone completed!');
})->name('collabcam.milestone.complete');

// Sign a contract
Route::post('/collabcam/contracts/{id}/sign', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $ct = DB::table('collabcam_contracts')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$ct) abort(404);
    $user = webUser();
    $myCompanyIds = DB::table('company_users')->where('user_id',$user->id)->where('is_active',1)->pluck('company_id')->toArray();
    $parties = json_decode($ct->parties??'[]',true);
    $myParty = array_values(array_intersect($myCompanyIds, $parties));
    if (empty($myParty)) abort(403,'Your company is not a party to this contract.');
    $signatories = json_decode($ct->signatories??'[]',true);
    $alreadySigned = collect($signatories)->pluck('company_id')->contains($myParty[0]);
    if ($alreadySigned) return back()->with('error','You have already signed this contract.');
    $myCoName = DB::table('companies')->where('id',$myParty[0])->value('name');
    $signatories[] = ['company_id'=>$myParty[0],'name'=>$myCoName,'role'=>'signatory','signed_at'=>now()->toISOString(),'signed_by'=>$user->first_name.' '.($user->last_name??'')];
    $allSigned = count($signatories) >= count($parties);
    DB::table('collabcam_contracts')->where('id',$id)->update([
        'signatories'=> json_encode($signatories),
        'all_signed' => $allSigned,
        'status'     => $allSigned ? 'signed' : 'pending_signatures',
        'updated_at' => now(),
    ]);
    return back()->with('success', $allSigned ? 'All parties have signed — contract is active!' : 'You have signed the contract. Waiting for other parties.');
})->name('collabcam.contract.sign');

// Pause/resume collaboration
Route::post('/collabcam/workspace/{id}/pause', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    DB::table('collabcam_collaborations')->where('id',$id)->where('status','active')->update(['status'=>'paused','updated_at'=>now()]);
    return back()->with('success','Collaboration paused.');
});
Route::post('/collabcam/workspace/{id}/resume', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    DB::table('collabcam_collaborations')->where('id',$id)->where('status','paused')->update(['status'=>'active','updated_at'=>now()]);
    return back()->with('success','Collaboration resumed.');
});

// ═════════════════════════════════════════════
// COLLABCAM + ASSOCIATIONS API (v1)
// ═════════════════════════════════════════════

// Helper: API auth middleware
function apiAuth(Request $request): ?object
{
    $header = $request->header('Authorization','');
    if (!str_starts_with($header,'Bearer ')) return null;
    $token = substr($header, 7);
    $key = DB::table('api_keys')->where('key_hash', hash('sha256',$token))->where('is_active',1)->whereNull('deleted_at')->first();
    if (!$key) return null;
    DB::table('api_keys')->where('id',$key->id)->update(['last_used_at'=>now()]);
    return DB::table('users')->where('id',$key->user_id)->first();
}

function apiJson($data, int $status = 200)
{
    return response()->json(['success'=>true,'data'=>$data], $status)
        ->header('Content-Type','application/json')
        ->header('X-Powered-By','Galerie virtuelle de l\'artisanat du Cameroun API v1');
}

function apiError(string $message, int $status = 400)
{
    return response()->json(['success'=>false,'error'=>$message], $status);
}

// Associations API
Route::prefix('api/v1/associations')->group(function () {
    Route::get('/', function (Request $request) {
        $q      = $request->get('q','');
        $sector = $request->get('sector','');
        $limit  = min((int)$request->get('limit',20),100);
        $offset = (int)$request->get('offset',0);
        $query  = DB::table('associations')->where('is_active',1)->whereNull('deleted_at');
        if($q) $query->where(function($x) use($q){ $x->where('name_en','like',"%$q%")->orWhere('acronym','like',"%$q%"); });
        if($sector) $query->where('sector',$sector);
        $total  = $query->count();
        $items  = $query->orderByRaw('is_featured DESC')->orderBy('name_en')->limit($limit)->offset($offset)->get();
        return apiJson(['total'=>$total,'limit'=>$limit,'offset'=>$offset,'items'=>$items]);
    });
    Route::get('/{slug}', function (Request $request, $slug) {
        $a = DB::table('associations')->leftJoin('regions','associations.region_id','=','regions.id')
            ->where('associations.slug',$slug)->where('is_active',1)->whereNull('associations.deleted_at')
            ->select('associations.*','regions.name_en as region_name')->first();
        if (!$a) return apiError('Association not found',404);
        return apiJson($a);
    });
});

// CollabCam API
Route::prefix('api/v1/collabcam')->group(function () {

    Route::get('/companies', function (Request $request) {
        $q      = $request->get('q','');
        $sector = $request->get('sector','');
        $limit  = min((int)$request->get('limit',20),100);
        $offset = (int)$request->get('offset',0);
        $query  = DB::table('companies')->whereNull('deleted_at')->select('id','name','slug','legal_form','verification_status','view_count','created_at');
        if($q) $query->where('name','like',"%$q%");
        $total = (clone $query)->count();
        $items = $query->orderByRaw("CASE verification_status WHEN 'verified' THEN 0 ELSE 1 END")->orderBy('name')->limit($limit)->offset($offset)->get();
        return apiJson(['total'=>$total,'limit'=>$limit,'offset'=>$offset,'items'=>$items]);
    });

    Route::get('/opportunities', function (Request $request) {
        $type   = $request->get('type','');
        $sector = $request->get('sector','');
        $limit  = min((int)$request->get('limit',20),100);
        $offset = (int)$request->get('offset',0);
        $query  = DB::table('collabcam_opportunities')
            ->join('companies','collabcam_opportunities.company_id','=','companies.id')
            ->where('collabcam_opportunities.status','active')
            ->whereNull('collabcam_opportunities.deleted_at')
            ->select('collabcam_opportunities.*','companies.name as company_name','companies.slug as company_slug');
        if($type) $query->where('collabcam_opportunities.type',$type);
        if($sector) $query->where('collabcam_opportunities.sector',$sector);
        $total = (clone $query)->count();
        $items = $query->orderByRaw('is_featured DESC')->orderByDesc('collabcam_opportunities.created_at')->limit($limit)->offset($offset)->get();
        return apiJson(['total'=>$total,'limit'=>$limit,'offset'=>$offset,'items'=>$items]);
    });

    Route::post('/opportunities', function (Request $request) {
        $user = apiAuth($request);
        if (!$user) return apiError('Unauthorized',401);
        $data = $request->validate(['company_id'=>'required','title_en'=>'required','type'=>'required','description_en'=>'required']);
        $owns = DB::table('company_users')->where('user_id',$user->id)->where('company_id',$data['company_id'])->where('is_active',1)->exists();
        if (!$owns) return apiError('Forbidden',403);
        $id = Str::uuid()->toString();
        DB::table('collabcam_opportunities')->insert(array_merge($data,[
            'id'=>$id,'status'=>'active','view_count'=>0,'response_count'=>0,'created_at'=>now(),'updated_at'=>now(),
        ]));
        return apiJson(['id'=>$id],201);
    });

    Route::get('/opportunities/{id}', function (Request $request, $id) {
        $o = DB::table('collabcam_opportunities')->join('companies','collabcam_opportunities.company_id','=','companies.id')
            ->where('collabcam_opportunities.id',$id)->whereNull('collabcam_opportunities.deleted_at')
            ->select('collabcam_opportunities.*','companies.name as company_name')->first();
        if (!$o) return apiError('Not found',404);
        return apiJson($o);
    });

    Route::get('/collaborations', function (Request $request) {
        $user = apiAuth($request);
        if (!$user) return apiError('Unauthorized',401);
        $myCoIds = DB::table('company_users')->where('user_id',$user->id)->where('is_active',1)->pluck('company_id')->toArray();
        $items = DB::table('collabcam_collaborations')
            ->join('collabcam_collaboration_members as m','collabcam_collaborations.id','=','m.collaboration_id')
            ->whereIn('m.company_id',count($myCoIds)?$myCoIds:['__none__'])
            ->where('m.status','active')
            ->whereNull('collabcam_collaborations.deleted_at')
            ->select('collabcam_collaborations.*')->distinct()->get();
        return apiJson(['total'=>$items->count(),'items'=>$items]);
    });

    Route::post('/request', function (Request $request) {
        $user = apiAuth($request);
        if (!$user) return apiError('Unauthorized',401);
        $data = $request->validate(['from_company_id'=>'required','to_company_id'=>'required','subject'=>'required','message'=>'required','collab_type'=>'required|string']);
        $owns = DB::table('company_users')->where('user_id',$user->id)->where('company_id',$data['from_company_id'])->where('is_active',1)->exists();
        if (!$owns) return apiError('Forbidden',403);
        $id = Str::uuid()->toString();
        DB::table('collabcam_requests')->insert(array_merge($data,[
            'id'=>$id,'initiated_by'=>$user->id,'status'=>'pending','created_at'=>now(),'updated_at'=>now(),
        ]));
        return apiJson(['id'=>$id,'status'=>'pending'],201);
    });

    Route::get('/collaborations/{id}', function (Request $request, $id) {
        $user = apiAuth($request);
        if (!$user) return apiError('Unauthorized',401);
        $c = DB::table('collabcam_collaborations')->where('id',$id)->whereNull('deleted_at')->first();
        if (!$c) return apiError('Not found',404);
        $members = DB::table('collabcam_collaboration_members')
            ->join('companies','collabcam_collaboration_members.company_id','=','companies.id')
            ->where('collaboration_id',$id)->select('collabcam_collaboration_members.*','companies.name as company_name')->get();
        $contracts  = DB::table('collabcam_contracts')->where('collaboration_id',$id)->whereNull('deleted_at')->get();
        $milestones = DB::table('collabcam_milestones')->where('collaboration_id',$id)->orderBy('sort_order')->get();
        return apiJson(['collaboration'=>$c,'members'=>$members,'contracts'=>$contracts,'milestones'=>$milestones]);
    });
});

// Phase 2-7 routes appended to web.php
// ══════════════════════════════════════════════════════════════════════════════
// PHASE 2: TENDER & PROCUREMENT PORTAL
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/tenders', function (Request $request) {
    return view('tenders');
});

Route::get('/tenders/{id}', function ($id) {
    $tender = DB::table('tenders')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$tender) abort(404);
    $company = DB::table('companies')->where('id',$tender->company_id)->first();
    if (!$company) abort(404);
    return view('tender', compact('tender','company'));
});

Route::post('/tenders', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    DB::table('tenders')->insert([
        'id'            => (string)\Illuminate\Support\Str::uuid(),
        'company_id'    => $request->input('company_id'),
        'posted_by'     => $authUser['id'],
        'title'         => $request->input('title'),
        'description'   => $request->input('description'),
        'category'      => $request->input('category'),
        'type'          => $request->input('type'),
        'status'        => 'open',
        'budget_estimate' => $request->input('budget_estimate') ?: null,
        'currency'      => 'XAF',
        'deadline'      => $request->input('deadline'),
        'location'      => $request->input('location'),
        'eligibility'   => $request->input('eligibility'),
        'contact_email' => $request->input('contact_email'),
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect('/tenders')->with('success', 'Tender published successfully.');
});

Route::post('/tenders/{id}/bid', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $tender = DB::table('tenders')->where('id',$id)->where('status','open')->first();
    if (!$tender) return redirect("/tenders/$id")->with('error','This tender is not accepting bids.');
    $alreadyBid = DB::table('tender_bids')->where('tender_id',$id)->where('company_id',$request->input('company_id'))->exists();
    if ($alreadyBid) return redirect("/tenders/$id")->with('error','Your company has already submitted a bid.');
    DB::table('tender_bids')->insert([
        'id'               => (string)\Illuminate\Support\Str::uuid(),
        'tender_id'        => $id,
        'company_id'       => $request->input('company_id'),
        'submitted_by'     => $authUser['id'],
        'proposal'         => $request->input('proposal'),
        'bid_amount'       => $request->input('bid_amount') ?: null,
        'currency'         => 'XAF',
        'technical_approach' => $request->input('technical_approach'),
        'status'           => 'submitted',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    DB::table('tenders')->where('id',$id)->increment('bid_count');
    notifyUser($tender->posted_by, 'tender_bid', 'New bid on your tender',
        'A company submitted a bid on "'.$tender->title.'".', "/tenders/$id",
        'Nouvelle offre sur votre appel', 'Une entreprise a soumis une offre sur « '.$tender->title.' ».');
    return redirect("/tenders/$id")->with('success','Your bid has been submitted.');
});

Route::post('/tenders/bids/{id}/shortlist', function ($id) {
    DB::table('tender_bids')->where('id',$id)->update(['status'=>'shortlisted','updated_at'=>now()]);
    return back()->with('success','Bid shortlisted.');
});

Route::post('/tenders/bids/{id}/reject', function ($id) {
    DB::table('tender_bids')->where('id',$id)->update(['status'=>'rejected','updated_at'=>now()]);
    return back()->with('success','Bid rejected.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 3: INVESTMENT MARKETPLACE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/invest-hub', function (Request $request) {
    return view('invest-hub');
});

Route::get('/invest-hub/{id}', function ($id) {
    $seek = DB::table('invest_seeks')->where('id',$id)->whereNull('deleted_at')->first();
    if (!$seek) abort(404);
    $company = DB::table('companies')->where('id',$seek->company_id)->first();
    $authUser = session('auth_user');
    $interests = DB::table('invest_interests')->where('seek_id',$id)->count();
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser['id'])
        ->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->get() : collect();
    $alreadyExpressed = $authUser && DB::table('invest_interests')
        ->where('seek_id',$id)->where('investor_user_id',$authUser['id'])->exists();
    $isOwner = $authUser && (string)$seek->company_id === (string)($myCompanies->first()->id??'');
    $interestList = $isOwner ? DB::table('invest_interests')
        ->join('users','invest_interests.investor_user_id','=','users.id')
        ->leftJoin('companies','invest_interests.investor_company_id','=','companies.id')
        ->where('invest_interests.seek_id',$id)
        ->select('invest_interests.*','users.first_name','users.last_name','companies.name as investor_company')
        ->orderByDesc('invest_interests.created_at')->get() : collect();
    DB::table('invest_seeks')->where('id',$id)->increment('view_count');
    return view('invest-hub-detail', compact('seek','company','authUser','interests','myCompanies','alreadyExpressed','isOwner','interestList'));
});

Route::post('/invest-hub', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    DB::table('invest_seeks')->insert([
        'id'            => (string)\Illuminate\Support\Str::uuid(),
        'company_id'    => $request->input('company_id'),
        'posted_by'     => $authUser['id'],
        'title'         => $request->input('title'),
        'description'   => $request->input('description'),
        'type'          => $request->input('type'),
        'sector'        => $request->input('sector'),
        'amount_sought' => $request->input('amount_sought'),
        'currency'      => 'XAF',
        'equity_offered' => $request->input('equity_offered') ?: null,
        'use_of_funds'  => $request->input('use_of_funds'),
        'traction'      => $request->input('traction'),
        'deadline'      => $request->input('deadline') ?: null,
        'status'        => 'open',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect('/invest-hub')->with('success','Investment opportunity listed.');
});

Route::post('/invest-hub/{id}/express', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    if (DB::table('invest_interests')->where('seek_id',$id)->where('investor_user_id',$authUser['id'])->exists()) {
        return redirect("/invest-hub/$id")->with('error','You have already expressed interest.');
    }
    DB::table('invest_interests')->insert([
        'id'                  => (string)\Illuminate\Support\Str::uuid(),
        'seek_id'             => $id,
        'investor_user_id'    => $authUser['id'],
        'investor_company_id' => $request->input('investor_company_id') ?: null,
        'message'             => $request->input('message'),
        'proposed_amount'     => $request->input('proposed_amount') ?: null,
        'status'              => 'expressed',
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);
    DB::table('invest_seeks')->where('id',$id)->increment('interest_count');
    $seek = DB::table('invest_seeks')->where('id',$id)->first();
    if ($seek) notifyUser($seek->posted_by, 'invest_interest', 'New investor interest',
        'An investor expressed interest in "'.$seek->title.'".', "/invest-hub/$id",
        'Nouvel intérêt investisseur', 'Un investisseur a manifesté son intérêt pour « '.$seek->title.' ».');
    return redirect("/invest-hub/$id")->with('success','Interest expressed successfully.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 4: SUPPLIER PERFORMANCE CENTER
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/supplier-reviews', function (Request $request) {
    return view('supplier-reviews');
});

Route::post('/supplier-reviews', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $supplierId  = $request->input('supplier_company_id');
    $reviewerId  = $request->input('reviewer_company_id');
    if ($supplierId === $reviewerId) return back()->with('error','You cannot review your own company.');
    if (DB::table('supplier_reviews')->where('supplier_company_id',$supplierId)->where('reviewer_company_id',$reviewerId)->exists()) {
        return back()->with('error','Your company has already reviewed this supplier.');
    }
    DB::table('supplier_reviews')->insert([
        'id'                  => (string)\Illuminate\Support\Str::uuid(),
        'supplier_company_id' => $supplierId,
        'reviewer_company_id' => $reviewerId,
        'reviewer_user_id'    => $authUser['id'],
        'score_delivery'      => (int)$request->input('score_delivery',3),
        'score_quality'       => (int)$request->input('score_quality',3),
        'score_communication' => (int)$request->input('score_communication',3),
        'score_pricing'       => (int)$request->input('score_pricing',3),
        'score_overall'       => (int)$request->input('score_overall',3),
        'review_text'         => $request->input('review_text'),
        'would_recommend'     => $request->has('would_recommend') ? 1 : 0,
        'status'              => 'published',
        'created_at'          => now(),
        'updated_at'          => now(),
    ]);
    // Reputation event
    DB::table('reputation_events')->insert([
        'company_id'   => $supplierId,
        'type'         => 'review_received',
        'points'       => (int)$request->input('score_overall',3) >= 4 ? 5 : 0,
        'description'  => 'Received a supplier review',
        'source_type'  => 'supplier_review',
        'source_id'    => $supplierId,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    $supplierName = DB::table('companies')->where('id',$supplierId)->value('name');
    notifyCompanyOwners($supplierId, 'supplier_review', 'New supplier review',
        'Your company received a new performance review.', '/supplier-reviews',
        'Nouvel avis fournisseur', 'Votre entreprise a reçu un nouvel avis de performance.');
    return redirect('/supplier-reviews')->with('success','Review submitted.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 5: FEDERATION MODE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/federations', function () {
    return view('federations');
});

Route::get('/federations/{slug}', function ($slug) {
    $fed = DB::table('federations')->where('slug',$slug)->whereNull('deleted_at')->first();
    if (!$fed) abort(404);
    return view('federation', compact('fed'));
});

Route::post('/federations/{slug}/join', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $fed = DB::table('federations')->where('slug',$slug)->first();
    if (!$fed) abort(404);
    $companyId = $request->input('company_id');
    if (DB::table('federation_members')->where('federation_id',$fed->id)->where('company_id',$companyId)->exists()) {
        return redirect("/federations/$slug")->with('error','Your company already has a membership request.');
    }
    DB::table('federation_members')->insert([
        'federation_id' => $fed->id,
        'company_id'    => $companyId,
        'role'          => 'member',
        'status'        => 'pending',
        'invited_by'    => $authUser['id'],
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    notifyUser($fed->admin_user_id, 'federation_join', 'New federation membership request',
        'A company requested to join '.$fed->name.'.', "/federations/$slug",
        'Nouvelle demande d\'adhésion', 'Une entreprise demande à rejoindre '.$fed->name.'.');
    return redirect("/federations/$slug")->with('success','Membership request submitted. Awaiting federation approval.');
});

Route::post('/federations/{slug}/post', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $fed = DB::table('federations')->where('slug',$slug)->first();
    if (!$fed) abort(404);
    DB::table('federation_posts')->insert([
        'federation_id' => $fed->id,
        'user_id'       => $authUser['id'],
        'company_id'    => $request->input('company_id'),
        'title'         => $request->input('title') ?: null,
        'body'          => $request->input('body'),
        'type'          => $request->input('type','discussion'),
        'is_pinned'     => 0,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect("/federations/$slug")->with('success','Post published.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 6: ESG & SUSTAINABILITY
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/esg', function () {
    return view('esg');
});

Route::get('/esg/submit', function () {
    return view('esg-submit');
});

Route::post('/esg/submit', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $companyId = $request->input('company_id');
    $year      = (int)$request->input('year', date('Y') - 1);
    if (DB::table('esg_reports')->where('company_id',$companyId)->where('year',$year)->exists()) {
        return back()->with('error',"An ESG report for $year already exists for this company.");
    }
    // Compute scores
    $env = 0; $envCount = 0;
    if ($request->input('co2_tonnes') !== null) { $env += 60; $envCount++; }
    if ($request->input('renewable_energy_pct') !== null) { $env += min((float)$request->input('renewable_energy_pct')*1.5, 100); $envCount++; }
    if ($request->input('recycled_pct') !== null) { $env += min((float)$request->input('recycled_pct'), 100); $envCount++; }
    if ($request->input('environmental_initiatives')) { $env += 20; $envCount++; }
    $envScore = $envCount > 0 ? min(round($env / max($envCount,1)), 100) : null;
    $soc = 0; $socCount = 0;
    if ($request->input('female_employees') && $request->input('total_employees')) {
        $femalePct = (float)$request->input('female_employees')/(float)$request->input('total_employees')*100;
        $soc += min($femalePct*2, 50); $socCount++;
    }
    if ($request->has('has_health_insurance')) { $soc += 30; $socCount++; }
    if ($request->input('community_initiatives')) { $soc += 20; $socCount++; }
    $socScore = $socCount > 0 ? min(round($soc / max($socCount,1) * 1.5), 100) : null;
    $gov = 0;
    if ($request->has('has_ethics_policy')) $gov += 25;
    if ($request->has('has_whistleblower_policy')) $gov += 25;
    if ($request->has('has_board_diversity')) $gov += 25;
    if ($request->has('anti_corruption_training')) $gov += 25;
    $govScore = $gov > 0 ? $gov : null;
    $scores = array_filter([$envScore, $socScore, $govScore]);
    $overallScore = count($scores) > 0 ? round(array_sum($scores)/count($scores)) : null;
    DB::table('esg_reports')->insert([
        'id'                      => (string)\Illuminate\Support\Str::uuid(),
        'company_id'              => $companyId,
        'submitted_by'            => $authUser['id'],
        'year'                    => $year,
        'status'                  => 'published',
        'co2_tonnes'              => $request->input('co2_tonnes') ?: null,
        'energy_kwh'              => $request->input('energy_kwh') ?: null,
        'renewable_energy_pct'    => $request->input('renewable_energy_pct') ?: null,
        'water_m3'                => $request->input('water_m3') ?: null,
        'waste_tonnes'            => $request->input('waste_tonnes') ?: null,
        'recycled_pct'            => $request->input('recycled_pct') ?: null,
        'environmental_initiatives' => $request->input('environmental_initiatives'),
        'total_employees'         => $request->input('total_employees') ?: null,
        'female_employees'        => $request->input('female_employees') ?: null,
        'local_employees_pct'     => $request->input('local_employees_pct') ?: null,
        'training_hours_per_employee' => $request->input('training_hours_per_employee') ?: null,
        'safety_incidents'        => $request->input('safety_incidents') !== null ? (int)$request->input('safety_incidents') : null,
        'has_health_insurance'    => $request->has('has_health_insurance') ? 1 : 0,
        'community_initiatives'   => $request->input('community_initiatives'),
        'has_ethics_policy'       => $request->has('has_ethics_policy') ? 1 : 0,
        'has_whistleblower_policy' => $request->has('has_whistleblower_policy') ? 1 : 0,
        'has_board_diversity'     => $request->has('has_board_diversity') ? 1 : 0,
        'anti_corruption_training' => $request->has('anti_corruption_training') ? 1 : 0,
        'governance_notes'        => $request->input('governance_notes'),
        'env_score'               => $envScore,
        'social_score'            => $socScore,
        'governance_score'        => $govScore,
        'overall_esg_score'       => $overallScore,
        'created_at'              => now(),
        'updated_at'              => now(),
    ]);
    return redirect('/esg')->with('success','ESG report submitted and published.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 7: EXPORT HUB
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/export-hub', function () {
    return view('export-hub');
});

Route::get('/export-hub/assessment', function () {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $myCompanies = DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser['id'])
        ->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->get();
    return view('export-assessment', compact('authUser','myCompanies'));
});

Route::post('/export-hub/assessment', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $answers  = $request->except(['_token','company_id','product_name','hs_code','target_market']);
    $score    = 0; $maxScore = 0;
    $scoreMap = ['registered'=>10,'has_product'=>10,'has_certifications'=>15,'has_export_docs'=>15,'has_packaging'=>10,'has_insurance'=>10,'has_bank_account'=>5,'knows_hs_code'=>5,'knows_target_market'=>5,'has_export_partner'=>10,'has_logistics'=>5];
    foreach ($scoreMap as $key => $pts) { $maxScore += $pts; if (!empty($answers[$key])) $score += $pts; }
    $pct = $maxScore > 0 ? round($score/$maxScore*100) : 0;
    $level = $pct >= 80 ? 'expert' : ($pct >= 65 ? 'ready' : ($pct >= 45 ? 'almost_ready' : ($pct >= 25 ? 'developing' : 'not_ready')));
    $recommendations = [];
    if (empty($answers['has_certifications'])) $recommendations[] = 'Obtain relevant product certifications (ISO, phytosanitary, halal, organic as applicable)';
    if (empty($answers['knows_hs_code'])) $recommendations[] = 'Identify the correct HS code for your product to determine import duties in target markets';
    if (empty($answers['has_export_docs'])) $recommendations[] = 'Prepare export documentation: Certificate of Origin, Commercial Invoice, Packing List, Bill of Lading';
    if (empty($answers['has_packaging'])) $recommendations[] = 'Ensure product packaging meets destination country labelling requirements';
    if (empty($answers['has_export_partner'])) $recommendations[] = 'Find an experienced export partner or freight forwarder in Cameroon';
    if (empty($answers['has_logistics'])) $recommendations[] = 'Establish reliable logistics arrangements — contact Port of Douala shipping agents';
    $id = (string)\Illuminate\Support\Str::uuid();
    DB::table('export_assessments')->insert([
        'id'              => $id,
        'company_id'      => $request->input('company_id'),
        'user_id'         => $authUser['id'],
        'product_name'    => $request->input('product_name'),
        'hs_code'         => $request->input('hs_code'),
        'target_market'   => $request->input('target_market'),
        'answers'         => json_encode($answers),
        'readiness_score' => $pct,
        'readiness_level' => $level,
        'recommendations' => json_encode($recommendations),
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    return redirect("/export-hub/assessment/$id");
});

Route::get('/export-hub/assessment/{id}', function ($id) {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $assessment = DB::table('export_assessments')->where('id',$id)->first();
    if (!$assessment) abort(404);
    $company = DB::table('companies')->where('id',$assessment->company_id)->first();
    return view('export-assessment-result', compact('assessment','company'));
});

Route::get('/export-hub/{slug}', function ($slug) {
    $resource = DB::table('export_resources')->where('slug',$slug)->where('is_published',1)->first();
    if (!$resource) abort(404);
    DB::table('export_resources')->where('id',$resource->id)->increment('view_count');
    $related = DB::table('export_resources')
        ->where('category',$resource->category)
        ->where('slug','!=',$slug)
        ->where('is_published',1)
        ->limit(3)->get();
    return view('export-resource', compact('resource','related'));
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 8: BUSINESS EVENTS
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/events', function () {
    return view('events');
});

Route::get('/events/{id}', function ($id) {
    $event = DB::table('events')->where('id',$id)->first();
    if (!$event) abort(404);
    DB::table('events')->where('id',$id)->increment('view_count');
    $company = $event->organizer_company_id
        ? DB::table('companies')->where('id',$event->organizer_company_id)->first()
        : null;
    $registrations = DB::table('event_registrations')
        ->join('users','event_registrations.user_id','=','users.id')
        ->leftJoin('companies','event_registrations.company_id','=','companies.id')
        ->where('event_registrations.event_id',$id)
        ->where('event_registrations.status','!=','cancelled')
        ->select('users.first_name','users.last_name','companies.name as company_name')
        ->orderByDesc('event_registrations.registered_at')->get();
    $authUser = webUser();
    $alreadyRegistered = $authUser && DB::table('event_registrations')
        ->where('event_id',$id)->where('user_id',$authUser->id)
        ->where('status','!=','cancelled')->exists();
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)
        ->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->get() : collect();
    return view('event', compact('event','company','registrations','alreadyRegistered','authUser','myCompanies'));
});

Route::post('/events', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $title = trim($request->input('title'));
    $slug = \Illuminate\Support\Str::slug($title);
    if (!$slug) $slug = 'event';
    if (DB::table('events')->where('slug',$slug)->exists()) $slug .= '-'.substr(md5(uniqid()),0,6);
    $price = (float)$request->input('ticket_price',0);
    $id = (string)\Illuminate\Support\Str::uuid();
    DB::table('events')->insert([
        'id'                   => $id,
        'title'                => $title,
        'slug'                 => $slug,
        'description'          => $request->input('description'),
        'organizer_company_id' => $request->input('company_id') ?: null,
        'organizer_user_id'    => $authUser['id'],
        'category'             => $request->input('category','conference'),
        'format'               => $request->input('format','in_person'),
        'status'               => 'open',
        'start_date'           => $request->input('start_date'),
        'end_date'             => $request->input('end_date'),
        'location_city'        => $request->input('location_city'),
        'location_country'     => $request->input('location_country','Cameroon'),
        'venue_name'           => $request->input('venue_name'),
        'is_paid'              => $price > 0 ? 1 : 0,
        'ticket_price'         => $price > 0 ? $price : null,
        'max_attendees'        => $request->input('max_attendees') ?: null,
        'created_at'           => now(),
        'updated_at'           => now(),
    ]);
    return redirect("/events/$id")->with('success','Event created successfully.');
});

Route::post('/events/{id}/register', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $event = DB::table('events')->where('id',$id)->first();
    if (!$event) abort(404);
    if (DB::table('event_registrations')->where('event_id',$id)->where('user_id',$authUser['id'])->exists()) {
        return redirect("/events/$id")->with('error','You are already registered for this event.');
    }
    DB::table('event_registrations')->insert([
        'event_id'      => $id,
        'user_id'       => $authUser['id'],
        'company_id'    => $request->input('company_id') ?: null,
        'status'        => 'registered',
        'registered_at' => now(),
    ]);
    DB::table('events')->where('id',$id)->increment('attendee_count');
    notifyUser($event->organizer_user_id, 'event_registration', 'New event registration',
        'Someone registered for "'.$event->title.'".', "/events/$id",
        'Nouvelle inscription', 'Quelqu\'un s\'est inscrit à « '.$event->title.' ».');
    return redirect("/events/$id")->with('success','You are registered! We will send reminders as the event approaches.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 8: BUSINESS COMMUNITIES
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/communities', function () {
    return view('communities');
});

Route::get('/communities/{slug}', function ($slug) {
    $community = DB::table('communities')->where('slug',$slug)->first();
    if (!$community) abort(404);
    $authUser = webUser();
    $isMember = $authUser && DB::table('community_members')
        ->where('community_id',$community->id)->where('user_id',$authUser->id)
        ->where('status','active')->exists();
    $posts = DB::table('community_posts')
        ->join('users','community_posts.user_id','=','users.id')
        ->where('community_posts.community_id',$community->id)
        ->select('community_posts.*','users.first_name','users.last_name')
        ->orderByDesc('community_posts.is_pinned')
        ->orderByDesc('community_posts.created_at')->get();
    $members = DB::table('community_members')
        ->join('users','community_members.user_id','=','users.id')
        ->where('community_members.community_id',$community->id)
        ->where('community_members.status','active')
        ->select('users.first_name','users.last_name','community_members.role')
        ->orderByRaw("FIELD(community_members.role,'admin','moderator','member')")
        ->limit(12)->get();
    return view('community', compact('community','isMember','posts','members','authUser'));
});

Route::post('/communities', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $name = trim($request->input('name'));
    $slug = \Illuminate\Support\Str::slug($name);
    if (!$slug) $slug = 'community';
    if (DB::table('communities')->where('slug',$slug)->exists()) $slug .= '-'.substr(md5(uniqid()),0,6);
    $cid = DB::table('communities')->insertGetId([
        'slug'          => $slug,
        'name'          => $name,
        'tagline'       => $request->input('tagline'),
        'description'   => $request->input('description'),
        'sector'        => $request->input('sector','general'),
        'category'      => $request->input('category','industry'),
        'status'        => 'active',
        'admin_user_id' => $authUser['id'],
        'cover_color'   => $request->input('cover_color') ?: '#007a33',
        'member_count'  => 1,
        'post_count'    => 0,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    DB::table('community_members')->insert([
        'community_id' => $cid,
        'user_id'      => $authUser['id'],
        'role'         => 'admin',
        'status'       => 'active',
        'joined_at'    => now(),
    ]);
    return redirect("/communities/$slug")->with('success','Community created! You are the admin.');
});

Route::post('/communities/{slug}/join', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $community = DB::table('communities')->where('slug',$slug)->first();
    if (!$community) abort(404);
    if (DB::table('community_members')->where('community_id',$community->id)->where('user_id',$authUser['id'])->exists()) {
        return redirect("/communities/$slug")->with('error','You are already a member.');
    }
    DB::table('community_members')->insert([
        'community_id' => $community->id,
        'user_id'      => $authUser['id'],
        'role'         => 'member',
        'status'       => 'active',
        'joined_at'    => now(),
    ]);
    DB::table('communities')->where('id',$community->id)->increment('member_count');
    notifyUser($community->admin_user_id, 'community_join', 'New community member',
        'A new member joined '.$community->name.'.', "/communities/$slug",
        'Nouveau membre', 'Un nouveau membre a rejoint '.$community->name.'.');
    return redirect("/communities/$slug")->with('success','Welcome! You have joined the community.');
});

Route::post('/communities/{slug}/post', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $community = DB::table('communities')->where('slug',$slug)->first();
    if (!$community) abort(404);
    $isMember = DB::table('community_members')->where('community_id',$community->id)
        ->where('user_id',$authUser['id'])->where('status','active')->exists();
    if (!$isMember) {
        return redirect("/communities/$slug")->with('error','You must be a member to post.');
    }
    DB::table('community_posts')->insert([
        'community_id'   => $community->id,
        'user_id'        => $authUser['id'],
        'company_id'     => $request->input('company_id') ?: null,
        'title'          => $request->input('title') ?: null,
        'body'           => $request->input('body'),
        'type'           => $request->input('type','text'),
        'link_url'       => $request->input('link_url') ?: null,
        'is_pinned'      => 0,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
    DB::table('communities')->where('id',$community->id)->increment('post_count');
    return redirect("/communities/$slug")->with('success','Post published.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 8: KNOWLEDGE CENTER
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/knowledge', function () {
    return view('knowledge-center');
});

Route::get('/knowledge/{slug}', function ($slug) {
    $resource = DB::table('knowledge_resources')->where('slug',$slug)->where('is_published',1)->first();
    if (!$resource) abort(404);
    DB::table('knowledge_resources')->where('id',$resource->id)->increment('view_count');
    $related = DB::table('knowledge_resources')
        ->where('category',$resource->category)
        ->where('slug','!=',$slug)
        ->where('is_published',1)
        ->limit(3)->get();
    return view('knowledge-resource', compact('resource','related'));
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 9: INNOVATION HUB
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/innovation', function () {
    return view('innovation');
});

Route::get('/innovation/{slug}', function ($slug) {
    $project = DB::table('innovation_projects')->where('slug',$slug)->first();
    if (!$project) abort(404);
    DB::table('innovation_projects')->where('id',$project->id)->increment('view_count');
    $company = $project->company_id ? DB::table('companies')->where('id',$project->company_id)->first() : null;
    $participants = DB::table('innovation_participants')
        ->join('users','innovation_participants.user_id','=','users.id')
        ->leftJoin('companies','innovation_participants.company_id','=','companies.id')
        ->where('innovation_participants.project_id',$project->id)
        ->where('innovation_participants.status','!=','declined')
        ->select('users.first_name','users.last_name','companies.name as company_name','innovation_participants.role','innovation_participants.user_id')
        ->orderByDesc('innovation_participants.created_at')->get();
    $authUser = webUser();
    $alreadyJoined = $authUser && DB::table('innovation_participants')
        ->where('project_id',$project->id)->where('user_id',$authUser->id)->exists();
    $isOwner = $authUser && (string)$project->user_id === (string)$authUser->id;
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')->select('companies.id','companies.name')->get() : collect();
    return view('innovation-project', compact('project','company','participants','alreadyJoined','isOwner','authUser','myCompanies'));
});

Route::post('/innovation', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $title = trim($request->input('title'));
    $slug = \Illuminate\Support\Str::slug($title) ?: 'project';
    if (DB::table('innovation_projects')->where('slug',$slug)->exists()) $slug .= '-'.substr(md5(uniqid()),0,6);
    $myCo = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    DB::table('innovation_projects')->insert([
        'id'           => (string)\Illuminate\Support\Str::uuid(),
        'company_id'   => $myCo,
        'user_id'      => $authUser['id'],
        'title'        => $title,
        'slug'         => $slug,
        'description'  => $request->input('description'),
        'type'         => $request->input('type','research'),
        'sector'       => $request->input('sector','ict'),
        'stage'        => $request->input('stage','idea'),
        'status'       => 'seeking_partners',
        'budget'       => $request->input('budget') ?: null,
        'prize_amount' => $request->input('prize_amount') ?: null,
        'deadline'     => $request->input('deadline') ?: null,
        'looking_for'  => $request->input('looking_for'),
        'tags'         => $request->input('tags'),
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    return redirect("/innovation/$slug")->with('success','Innovation project posted.');
});

Route::post('/innovation/{slug}/join', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $project = DB::table('innovation_projects')->where('slug',$slug)->first();
    if (!$project) abort(404);
    if (DB::table('innovation_participants')->where('project_id',$project->id)->where('user_id',$authUser['id'])->exists()) {
        return redirect("/innovation/$slug")->with('error','You have already expressed interest.');
    }
    DB::table('innovation_participants')->insert([
        'project_id' => $project->id,
        'company_id' => $request->input('company_id') ?: null,
        'user_id'    => $authUser['id'],
        'role'       => $request->input('role','participant'),
        'message'    => $request->input('message'),
        'status'     => 'pending',
        'created_at' => now(),
    ]);
    DB::table('innovation_projects')->where('id',$project->id)->increment('participant_count');
    notifyUser($project->user_id, 'innovation_interest', 'New interest in your project',
        'Someone wants to collaborate on "'.$project->title.'".', "/innovation/$slug",
        'Nouvel intérêt pour votre projet', 'Quelqu\'un souhaite collaborer sur « '.$project->title.' ».');
    return redirect("/innovation/$slug")->with('success','Interest submitted. The project lead will review it.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 9: LOGISTICS EXCHANGE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/logistics', function () {
    return view('logistics');
});

Route::get('/logistics/{id}', function ($id) {
    $listing = DB::table('logistics_listings')->where('id',$id)->first();
    if (!$listing) abort(404);
    DB::table('logistics_listings')->where('id',$id)->increment('view_count');
    $company = $listing->company_id ? DB::table('companies')->where('id',$listing->company_id)->first() : null;
    $authUser = webUser();
    $isOwner = $authUser && (string)$listing->user_id === (string)$authUser->id;
    $alreadyBid = $authUser && DB::table('logistics_bids')->where('listing_id',$id)->where('user_id',$authUser->id)->exists();
    $bids = $isOwner ? DB::table('logistics_bids')
        ->join('users','logistics_bids.user_id','=','users.id')
        ->leftJoin('companies','logistics_bids.company_id','=','companies.id')
        ->where('logistics_bids.listing_id',$id)
        ->select('logistics_bids.*','users.first_name','users.last_name','companies.name as company_name')
        ->orderByDesc('logistics_bids.created_at')->get() : collect();
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')->select('companies.id','companies.name')->get() : collect();
    return view('logistics-listing', compact('listing','company','authUser','isOwner','alreadyBid','bids','myCompanies'));
});

Route::post('/logistics', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $myCo = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    DB::table('logistics_listings')->insert([
        'id'               => (string)\Illuminate\Support\Str::uuid(),
        'company_id'       => $myCo,
        'user_id'          => $authUser['id'],
        'type'             => $request->input('type','load'),
        'title'            => trim($request->input('title')),
        'cargo_type'       => $request->input('cargo_type','general'),
        'vehicle_type'     => $request->input('vehicle_type','any'),
        'origin_city'      => $request->input('origin_city'),
        'destination_city' => $request->input('destination_city'),
        'weight_kg'        => $request->input('weight_kg') ?: null,
        'price'            => $request->input('price') ?: null,
        'available_date'   => $request->input('available_date') ?: null,
        'contact_phone'    => $request->input('contact_phone'),
        'description'      => $request->input('description'),
        'status'           => 'open',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
    return redirect('/logistics?type='.$request->input('type','load'))->with('success','Logistics listing posted.');
});

Route::post('/logistics/{id}/bid', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $listing = DB::table('logistics_listings')->where('id',$id)->first();
    if (!$listing) abort(404);
    if (DB::table('logistics_bids')->where('listing_id',$id)->where('user_id',$authUser['id'])->exists()) {
        return redirect("/logistics/$id")->with('error','You have already responded to this listing.');
    }
    DB::table('logistics_bids')->insert([
        'listing_id' => $id,
        'company_id' => $request->input('company_id') ?: null,
        'user_id'    => $authUser['id'],
        'bid_amount' => $request->input('bid_amount') ?: null,
        'message'    => $request->input('message'),
        'status'     => 'pending',
        'created_at' => now(),
    ]);
    DB::table('logistics_listings')->where('id',$id)->increment('bid_count');
    notifyUser($listing->user_id, 'logistics_bid', 'New response on your listing',
        'Someone responded to "'.$listing->title.'".', "/logistics/$id",
        'Nouvelle réponse à votre annonce', 'Quelqu\'un a répondu à « '.$listing->title.' ».');
    return redirect("/logistics/$id")->with('success','Your response has been submitted.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 9: DIGITAL BUSINESS CARDS
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/cards', function () {
    return view('cards');
});

Route::post('/cards', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $name = trim($request->input('display_name')) ?: 'card';
    $slug = \Illuminate\Support\Str::slug($name) ?: 'card';
    if (DB::table('digital_cards')->where('slug',$slug)->exists()) $slug .= '-'.substr(md5(uniqid()),0,6);
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(substr($parts[0],0,1).(isset($parts[1])?substr($parts[1],0,1):''));
    DB::table('digital_cards')->insert([
        'id'           => (string)\Illuminate\Support\Str::uuid(),
        'user_id'      => $authUser['id'],
        'company_id'   => $request->input('company_id') ?: null,
        'slug'         => $slug,
        'display_name' => $name,
        'job_title'    => $request->input('job_title'),
        'company_name' => $request->input('company_name'),
        'tagline'      => $request->input('tagline'),
        'email'        => $request->input('email'),
        'phone'        => $request->input('phone'),
        'whatsapp'     => $request->input('whatsapp'),
        'website'      => $request->input('website'),
        'city'         => $request->input('city'),
        'linkedin'     => $request->input('linkedin'),
        'theme_color'  => $request->input('theme_color') ?: '#007a33',
        'initials'     => $initials,
        'is_public'    => 1,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    return redirect("/card/$slug")->with('success','Your digital card is live! Share the link or QR code.');
});

Route::get('/card/{slug}', function ($slug) {
    $card = DB::table('digital_cards')->where('slug',$slug)->first();
    if (!$card) abort(404);
    DB::table('digital_cards')->where('id',$card->id)->increment('view_count');
    $company = $card->company_id ? DB::table('companies')->where('id',$card->company_id)->whereNull('deleted_at')->first() : null;
    return view('card', compact('card','company'));
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 10: ASSET SHARING MARKETPLACE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/assets', function () {
    return view('assets');
});

Route::get('/assets/{slug}', function ($slug) {
    $asset = DB::table('shared_assets')->where('slug',$slug)->first();
    if (!$asset) abort(404);
    DB::table('shared_assets')->where('id',$asset->id)->increment('view_count');
    $company = $asset->company_id ? DB::table('companies')->where('id',$asset->company_id)->first() : null;
    $authUser = webUser();
    $isOwner = $authUser && (string)$asset->user_id === (string)$authUser->id;
    $alreadyInquired = $authUser && DB::table('asset_inquiries')->where('asset_id',$asset->id)->where('user_id',$authUser->id)->exists();
    $inquiries = $isOwner ? DB::table('asset_inquiries')
        ->join('users','asset_inquiries.user_id','=','users.id')
        ->leftJoin('companies','asset_inquiries.company_id','=','companies.id')
        ->where('asset_inquiries.asset_id',$asset->id)
        ->select('asset_inquiries.*','users.first_name','users.last_name','companies.name as company_name')
        ->orderByDesc('asset_inquiries.created_at')->get() : collect();
    $myCompanies = $authUser ? DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')->select('companies.id','companies.name')->get() : collect();
    return view('asset', compact('asset','company','authUser','isOwner','alreadyInquired','inquiries','myCompanies'));
});

Route::post('/assets', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $title = trim($request->input('title'));
    $slug = \Illuminate\Support\Str::slug($title) ?: 'asset';
    if (DB::table('shared_assets')->where('slug',$slug)->exists()) $slug .= '-'.substr(md5(uniqid()),0,6);
    $myCo = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    DB::table('shared_assets')->insert([
        'id'            => (string)\Illuminate\Support\Str::uuid(),
        'company_id'    => $myCo,
        'user_id'       => $authUser['id'],
        'title'         => $title,
        'slug'          => $slug,
        'description'   => $request->input('description'),
        'category'      => $request->input('category','equipment'),
        'pricing_model' => $request->input('pricing_model','daily'),
        'price'         => $request->input('price') ?: null,
        'location_city' => $request->input('location_city'),
        'condition'     => $request->input('condition'),
        'capacity_spec' => $request->input('capacity_spec'),
        'contact_phone' => $request->input('contact_phone'),
        'availability'  => 'available',
        'status'        => 'active',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
    return redirect("/assets/$slug")->with('success','Asset listed successfully.');
});

Route::post('/assets/{slug}/inquire', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $asset = DB::table('shared_assets')->where('slug',$slug)->first();
    if (!$asset) abort(404);
    if (DB::table('asset_inquiries')->where('asset_id',$asset->id)->where('user_id',$authUser['id'])->exists()) {
        return redirect("/assets/$slug")->with('error','You have already sent an inquiry.');
    }
    DB::table('asset_inquiries')->insert([
        'asset_id'   => $asset->id,
        'company_id' => $request->input('company_id') ?: null,
        'user_id'    => $authUser['id'],
        'message'    => $request->input('message'),
        'start_date' => $request->input('start_date') ?: null,
        'end_date'   => $request->input('end_date') ?: null,
        'status'     => 'pending',
        'created_at' => now(),
    ]);
    DB::table('shared_assets')->where('id',$asset->id)->increment('inquiry_count');
    notifyUser($asset->user_id, 'asset_inquiry', 'New inquiry on your asset',
        'Someone is interested in "'.$asset->title.'".', "/assets/$slug",
        'Nouvelle demande pour votre actif', 'Quelqu\'un est intéressé par « '.$asset->title.' ».');
    return redirect("/assets/$slug")->with('success','Your inquiry has been sent to the owner.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 10: COMPLIANCE INTELLIGENCE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/compliance', function () {
    return view('compliance');
});

Route::get('/compliance/{slug}', function ($slug) {
    $requirement = DB::table('compliance_requirements')->where('slug',$slug)->where('is_published',1)->first();
    if (!$requirement) abort(404);
    DB::table('compliance_requirements')->where('id',$requirement->id)->increment('view_count');
    $authUser = webUser();
    $hasCompany = false; $alreadyTracking = false;
    if ($authUser) {
        $myCoId = DB::table('company_users')->where('user_id',$authUser->id)->where('is_active',1)->value('company_id');
        $hasCompany = (bool)$myCoId;
        if ($myCoId) {
            $alreadyTracking = DB::table('compliance_tracker')->where('company_id',$myCoId)->where('requirement_id',$requirement->id)->exists();
        }
    }
    return view('compliance-requirement', compact('requirement','authUser','hasCompany','alreadyTracking'));
});

Route::post('/compliance/{slug}/track', function (Request $request, $slug) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $requirement = DB::table('compliance_requirements')->where('slug',$slug)->first();
    if (!$requirement) abort(404);
    $myCoId = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    if (!$myCoId) return redirect("/compliance/$slug")->with('error','You need a company to track compliance.');
    if (!DB::table('compliance_tracker')->where('company_id',$myCoId)->where('requirement_id',$requirement->id)->exists()) {
        DB::table('compliance_tracker')->insert([
            'company_id'     => $myCoId,
            'user_id'        => $authUser['id'],
            'requirement_id' => $requirement->id,
            'title'          => $requirement->title,
            'category'       => $requirement->category,
            'status'         => 'pending',
            'due_date'       => $request->input('due_date') ?: null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
    return redirect("/compliance/$slug")->with('success','Added to your compliance tracker.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 10: PARTNER RELATIONSHIP MANAGEMENT (PRM)
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/prm', function () {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $filter = request('status','');
    $query = DB::table('partners')->where('user_id',$authUser['id']);
    if ($filter) $query->where('status',$filter);
    $partners = $query->orderByDesc('updated_at')->get();
    $all = DB::table('partners')->where('user_id',$authUser['id'])->get();
    $stats = [
        'total'     => $all->count(),
        'active'    => $all->where('status','active')->count(),
        'prospects' => $all->where('status','prospect')->count(),
        'value'     => $all->sum('value_estimate'),
    ];
    return view('prm', compact('partners','stats','authUser'));
});

Route::post('/prm', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $myCo = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    DB::table('partners')->insert([
        'owner_company_id'  => $myCo,
        'user_id'           => $authUser['id'],
        'partner_name'      => trim($request->input('partner_name')),
        'relationship_type' => $request->input('relationship_type','supplier'),
        'status'            => $request->input('status','prospect'),
        'tier'              => $request->input('tier','standard'),
        'contact_name'      => $request->input('contact_name'),
        'contact_email'     => $request->input('contact_email'),
        'contact_phone'     => $request->input('contact_phone'),
        'value_estimate'    => $request->input('value_estimate') ?: null,
        'notes'             => $request->input('notes'),
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);
    return redirect('/prm')->with('success','Partner added.');
});

Route::get('/prm/{id}', function ($id) {
    $authUser = requireAuth(request());
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $partner = DB::table('partners')->where('id',$id)->where('user_id',$authUser['id'])->first();
    if (!$partner) abort(404);
    $interactions = DB::table('partner_interactions')->where('partner_id',$id)
        ->orderByDesc('interaction_date')->orderByDesc('id')->get();
    $partnerCompany = $partner->partner_company_id ? DB::table('companies')->where('id',$partner->partner_company_id)->first() : null;
    return view('partner', compact('partner','interactions','partnerCompany','authUser'));
});

Route::post('/prm/{id}/interaction', function (Request $request, $id) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $partner = DB::table('partners')->where('id',$id)->where('user_id',$authUser['id'])->first();
    if (!$partner) abort(404);
    $date = $request->input('interaction_date') ?: date('Y-m-d');
    DB::table('partner_interactions')->insert([
        'partner_id'       => $id,
        'user_id'          => $authUser['id'],
        'type'             => $request->input('type','note'),
        'subject'          => $request->input('subject'),
        'summary'          => $request->input('summary'),
        'interaction_date' => $date,
        'created_at'       => now(),
    ]);
    DB::table('partners')->where('id',$id)->update(['last_interaction_date'=>$date,'updated_at'=>now()]);
    DB::table('partners')->where('id',$id)->increment('interaction_count');
    return redirect("/prm/$id")->with('success','Interaction logged.');
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 11: COLLABORATION HEALTH SCORE
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/health-score', function () {
    return view('health-score');
});

Route::get('/health-score/{slug}', function ($slug) {
    $company = DB::table('companies')->where('slug',$slug)->whereNull('deleted_at')->first();
    if (!$company) abort(404);
    $s = \App\Support\HealthScore::store($company->id);   // compute live + cache for leaderboard
    $computedAt = now()->format('d M Y H:i');
    return view('company-health', compact('company','s','computedAt'));
});

// ══════════════════════════════════════════════════════════════════════════════
// PHASE 12: SALARY INSIGHTS
// ══════════════════════════════════════════════════════════════════════════════

Route::get('/salaries', function () {
    return view('salaries');
});

Route::post('/salaries', function (Request $request) {
    $authUser = requireAuth($request);
    if ($authUser instanceof \Illuminate\Http\RedirectResponse) return $authUser;
    $title  = trim($request->input('job_title'));
    if ($title === '') return back()->with('error','Job title is required.');
    $amount = (float)$request->input('salary_amount',0);
    if ($amount <= 0) return back()->with('error','Enter a valid salary amount.');
    $period = $request->input('period','monthly');
    $annual = $period === 'annual' ? $amount : $amount * 12;
    $myCo = DB::table('company_users')->where('user_id',$authUser['id'])->where('is_active',1)->value('company_id');
    DB::table('salary_reports')->insert([
        'id'               => (string)\Illuminate\Support\Str::uuid(),
        'company_id'       => $myCo,
        'user_id'          => $authUser['id'],
        'job_title'        => $title,
        'job_slug'         => \Illuminate\Support\Str::slug($title),
        'sector'           => $request->input('sector','general'),
        'employment_type'  => $request->input('employment_type','full_time'),
        'experience_level' => $request->input('experience_level','mid'),
        'city'             => $request->input('city'),
        'salary_amount'    => $amount,
        'period'           => $period,
        'annual_amount'    => $annual,
        'currency'         => 'XAF',
        'years_experience' => $request->input('years_experience') ?: null,
        'source'           => 'employee',
        'is_anonymous'     => 1,
        'status'           => 'published',
        'created_at'       => now(),'updated_at'=>now(),
    ]);
    return redirect('/salaries/'.\Illuminate\Support\Str::slug($title))->with('success','Thank you! Your anonymous salary report has been added.');
});

Route::get('/salaries/{slug}', function ($slug) {
    $stats = DB::table('salary_reports')->where('job_slug',$slug)->where('status','published')
        ->select(DB::raw('COUNT(*) as reports'), DB::raw('MIN(annual_amount) as min_a'),
                 DB::raw('AVG(annual_amount) as avg_a'), DB::raw('MAX(annual_amount) as max_a'),
                 DB::raw('AVG(bonus_annual) as avg_bonus'))->first();
    if (!$stats || !$stats->reports) abort(404);
    $title  = DB::table('salary_reports')->where('job_slug',$slug)->value('job_title');
    $sector = DB::table('salary_reports')->where('job_slug',$slug)->value('sector');
    $byExperience = DB::table('salary_reports')->where('job_slug',$slug)->where('status','published')
        ->select('experience_level', DB::raw('AVG(annual_amount) as avg_a'))
        ->groupBy('experience_level')
        ->orderByRaw("FIELD(experience_level,'entry','junior','mid','senior','lead','executive')")->get();
    $reports = DB::table('salary_reports')->where('job_slug',$slug)->where('status','published')
        ->orderByDesc('created_at')->limit(20)->get();
    return view('salary-role', compact('stats','title','sector','byExperience','reports','slug'));
});

// ── API routes for all phases ──────────────────────────────────────────────
Route::prefix('api/v1')->group(function () {

    Route::get('/tenders', function (Request $request) {
        $q = $request->get('q',''); $status = $request->get('status','open');
        $query = DB::table('tenders')->join('companies','tenders.company_id','=','companies.id')
            ->where('tenders.is_public',1)->whereNull('tenders.deleted_at');
        if ($q) $query->where('tenders.title','like',"%$q%");
        if ($status) $query->where('tenders.status',$status);
        $total = (clone $query)->count();
        $items = $query->select('tenders.id','tenders.title','tenders.category','tenders.type','tenders.status','tenders.budget_estimate','tenders.currency','tenders.deadline','companies.name as company_name')
            ->orderBy('tenders.deadline')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/tenders/{id}', function ($id) {
        $t = DB::table('tenders')->where('id',$id)->first();
        if (!$t) return apiError('Not found',404);
        return apiJson(['tender'=>$t]);
    });

    Route::get('/invest-hub', function (Request $request) {
        $query = DB::table('invest_seeks')->join('companies','invest_seeks.company_id','=','companies.id')
            ->where('invest_seeks.status','open')->whereNull('invest_seeks.deleted_at');
        $total = (clone $query)->count();
        $items = $query->select('invest_seeks.id','invest_seeks.title','invest_seeks.type','invest_seeks.sector','invest_seeks.amount_sought','invest_seeks.currency','invest_seeks.equity_offered','invest_seeks.status','companies.name as company_name')
            ->orderByDesc('invest_seeks.created_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/federations', function () {
        $feds = DB::table('federations')->where('status','active')->where('is_public',1)->whereNull('deleted_at')
            ->orderByRaw('is_featured DESC')->orderBy('name')->get();
        return apiJson(['total'=>$feds->count(),'items'=>$feds]);
    });

    Route::get('/federations/{slug}', function ($slug) {
        $fed = DB::table('federations')->where('slug',$slug)->first();
        if (!$fed) return apiError('Not found',404);
        $members = DB::table('federation_members')->join('companies','federation_members.company_id','=','companies.id')
            ->where('federation_members.federation_id',$fed->id)->where('federation_members.status','active')
            ->select('companies.id','companies.name','companies.slug','federation_members.role')->get();
        return apiJson(['federation'=>$fed,'members'=>$members,'member_count'=>$members->count()]);
    });

    Route::get('/esg', function () {
        $reports = DB::table('esg_reports')->join('companies','esg_reports.company_id','=','companies.id')
            ->where('esg_reports.status','published')->whereNull('companies.deleted_at')
            ->select('esg_reports.id','esg_reports.year','esg_reports.env_score','esg_reports.social_score','esg_reports.governance_score','esg_reports.overall_esg_score','companies.name as company_name','companies.slug as company_slug')
            ->orderByDesc('overall_esg_score')->get();
        return apiJson(['total'=>$reports->count(),'avg_score'=>round($reports->avg('overall_esg_score')),'reports'=>$reports]);
    });

    Route::get('/export-resources', function () {
        $resources = DB::table('export_resources')->where('is_published',1)->orderByRaw('is_featured DESC')->orderBy('title')->get();
        return apiJson(['total'=>$resources->count(),'items'=>$resources]);
    });

    Route::get('/supplier-reviews', function (Request $request) {
        $supplierId = $request->get('supplier_id','');
        $query = DB::table('supplier_reviews')->where('status','published');
        if ($supplierId) $query->where('supplier_company_id',$supplierId);
        $reviews = $query->orderByDesc('created_at')->limit(50)->get();
        return apiJson(['total'=>$reviews->count(),'reviews'=>$reviews]);
    });

    Route::get('/events', function (Request $request) {
        $query = DB::table('events')
            ->leftJoin('companies','events.organizer_company_id','=','companies.id')
            ->whereIn('events.status',['open','full']);
        if ($cat = $request->get('category','')) $query->where('events.category',$cat);
        $total = (clone $query)->count();
        $items = $query->select('events.id','events.title','events.slug','events.category','events.format','events.start_date','events.end_date','events.location_city','events.is_paid','events.ticket_price','events.attendee_count','companies.name as company_name')
            ->orderBy('events.start_date')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/communities', function () {
        $items = DB::table('communities')->where('status','active')
            ->select('id','slug','name','tagline','sector','category','member_count','post_count')
            ->orderByDesc('member_count')->limit(50)->get();
        return apiJson(['total'=>$items->count(),'items'=>$items]);
    });

    Route::get('/knowledge', function (Request $request) {
        $query = DB::table('knowledge_resources')->where('is_published',1);
        if ($cat = $request->get('category','')) $query->where('category',$cat);
        $total = (clone $query)->count();
        $items = $query->select('id','slug','title','description','category','sector','format','is_free','is_featured','view_count','download_count')
            ->orderByRaw('is_featured DESC')->orderBy('title')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/innovation', function (Request $request) {
        $query = DB::table('innovation_projects')
            ->leftJoin('companies','innovation_projects.company_id','=','companies.id');
        if ($type = $request->get('type','')) $query->where('innovation_projects.type',$type);
        $total = (clone $query)->count();
        $items = $query->select('innovation_projects.id','innovation_projects.title','innovation_projects.slug','innovation_projects.type','innovation_projects.sector','innovation_projects.stage','innovation_projects.status','innovation_projects.budget','innovation_projects.prize_amount','innovation_projects.deadline','companies.name as company_name')
            ->orderByDesc('innovation_projects.created_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/logistics', function (Request $request) {
        $query = DB::table('logistics_listings')->where('status','open');
        if ($type = $request->get('type','')) $query->where('type',$type);
        $total = (clone $query)->count();
        $items = $query->select('id','type','title','cargo_type','vehicle_type','origin_city','destination_city','weight_kg','price','currency','available_date')
            ->orderByDesc('created_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/cards', function () {
        $items = DB::table('digital_cards')->where('is_public',1)
            ->select('slug','display_name','job_title','company_name','tagline','view_count')
            ->orderByDesc('view_count')->limit(50)->get();
        return apiJson(['total'=>$items->count(),'items'=>$items]);
    });

    Route::get('/assets', function (Request $request) {
        $query = DB::table('shared_assets')->where('status','active');
        if ($cat = $request->get('category','')) $query->where('category',$cat);
        $total = (clone $query)->count();
        $items = $query->select('id','slug','title','category','pricing_model','price','currency','location_city','availability','capacity_spec')
            ->orderByDesc('created_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/compliance', function (Request $request) {
        $query = DB::table('compliance_requirements')->where('is_published',1);
        if ($cat = $request->get('category','')) $query->where('category',$cat);
        $total = (clone $query)->count();
        $items = $query->select('id','slug','title','description','category','authority','frequency','applies_to','sector')
            ->orderBy('title')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/health-score', function () {
        $items = DB::table('company_health_scores')
            ->join('companies','company_health_scores.company_id','=','companies.id')
            ->whereNull('companies.deleted_at')
            ->select('companies.name','companies.slug','overall_score','grade','network_score','activity_score','reputation_score','sustainability_score','engagement_score')
            ->orderByDesc('overall_score')->limit(50)->get();
        return apiJson(['total'=>$items->count(),'items'=>$items]);
    });

    Route::get('/talent', function (Request $request) {
        $query = DB::table('employee_profiles')
            ->join('users','employee_profiles.user_id','=','users.id')
            ->where('employee_profiles.open_to_work',1);
        if ($skill = $request->get('skill','')) $query->where('employee_profiles.skills','like',"%$skill%");
        if ($loc = $request->get('location','')) $query->where('employee_profiles.location','like',"%$loc%");
        $total = (clone $query)->count();
        $items = $query->select('employee_profiles.user_id','users.first_name','users.last_name','employee_profiles.headline','employee_profiles.location','employee_profiles.skills','employee_profiles.job_type_preference')
            ->orderByDesc('employee_profiles.updated_at')->limit(50)->get();
        return apiJson(['total'=>$total,'items'=>$items]);
    });

    Route::get('/salaries', function (Request $request) {
        $query = DB::table('salary_reports')->where('status','published');
        if ($sec = $request->get('sector','')) $query->where('sector',$sec);
        $items = $query->select('job_slug','job_title','sector',
                DB::raw('COUNT(*) as reports'),
                DB::raw('ROUND(MIN(annual_amount)/12) as monthly_min'),
                DB::raw('ROUND(AVG(annual_amount)/12) as monthly_avg'),
                DB::raw('ROUND(MAX(annual_amount)/12) as monthly_max'))
            ->groupBy('job_slug','job_title','sector')
            ->orderByDesc('monthly_avg')->limit(100)->get();
        return apiJson(['total'=>$items->count(),'currency'=>'XAF','period'=>'monthly','items'=>$items]);
    });

});
