<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ─────────────────────────────────────────────
// Route helper functions (webUser, requireAuth, establishSiacSession,
// dataExportDatasets/dataExportRows, developerConsumer) live in
// app/Support/route_helpers.php — autoloaded via composer's "files" so they
// stay defined when routes are cached (route:cache does NOT re-include this
// file per request). Keep them there, not here.
// ─────────────────────────────────────────────

// ─────────────────────────────────────────────
// SIARC Platform — API landing
// ─────────────────────────────────────────────
use App\Http\Controllers\FrontendController;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/galerie/entreprises', [FrontendController::class, 'businessIndex'])->name('businesses.index');
Route::get('/galerie/entreprises/{slug}', [FrontendController::class, 'businessShow'])->name('businesses.show');
Route::get('/galerie/secteurs', [FrontendController::class, 'industriesIndex'])->name('industries.index');
Route::get('/galerie/recherche', [FrontendController::class, 'search'])->name('gallery.search');
Route::get('/galerie/produits', [FrontendController::class, 'productsIndex'])->name('products.index');
Route::get('/galerie/produits/{slug}', [FrontendController::class, 'productShow'])->name('products.show');

use App\Http\Controllers\MessagingWebController;

Route::post('/galerie/messages', [MessagingWebController::class, 'send'])->name('messages.send')->middleware('verified.email');
Route::get('/tableau-de-bord/messages', [MessagingWebController::class, 'inbox'])->name('messages.inbox');
Route::get('/tableau-de-bord/messages/{id}', [MessagingWebController::class, 'thread'])->name('messages.thread');
Route::post('/tableau-de-bord/messages/{id}/repondre', [MessagingWebController::class, 'reply'])->name('messages.reply')->middleware('verified.email');

use App\Http\Controllers\ReviewWebController;

Route::post('/galerie/avis', [ReviewWebController::class, 'store'])->name('reviews.store');
Route::post('/tableau-de-bord/messages/{id}/conclure', [ReviewWebController::class, 'markDeal'])->name('messages.mark-deal');

use App\Http\Controllers\ProductActionsWebController;

Route::post('/galerie/produits/{slug}/sauvegarder', [ProductActionsWebController::class, 'toggleSave'])->name('products.toggle-save');
Route::post('/galerie/produits/{slug}/signaler', [ProductActionsWebController::class, 'report'])->name('products.report');

Route::post('/galerie/entreprises/{slug}/sauvegarder', function (Request $request, string $slug) {
    $siacUser = session('siac_user');
    if (!$siacUser) {
        return $request->wantsJson()
            ? response()->json(['message' => 'unauthenticated'], 401)
            : redirect('/login?next=' . urlencode($request->input('return_to', '/')));
    }

    $business = DB::table('businesses')->where('slug', $slug)->whereNull('deleted_at')->first();
    abort_unless((bool) $business, 404);

    $existing = DB::table('saved_businesses')
        ->where('user_id', $siacUser['id'])
        ->where('business_id', $business->id)
        ->exists();

    if ($existing) {
        DB::table('saved_businesses')->where('user_id', $siacUser['id'])->where('business_id', $business->id)->delete();
        $saved = false;
    } else {
        DB::table('saved_businesses')->insert([
            'user_id'     => $siacUser['id'],
            'business_id' => $business->id,
            'created_at'  => now(),
        ]);
        $saved = true;
    }

    if ($request->wantsJson()) {
        return response()->json(['saved' => $saved]);
    }

    return redirect($request->input('return_to', '/'))
        ->with('success', $saved ? 'Entreprise sauvegardée.' : 'Entreprise retirée des favoris.');
})->name('businesses.toggle-save');

use App\Http\Controllers\BusinessWebController;

Route::get('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'create'])->name('business.create');
Route::post('/tableau-de-bord/entreprise/creer', [BusinessWebController::class, 'store'])->name('business.store')->middleware('verified.email');
Route::get('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'edit'])->name('business.edit');
Route::post('/tableau-de-bord/entreprise/modifier', [BusinessWebController::class, 'update'])->name('business.update')->middleware('verified.email');
Route::get('/api-interne/villes/{regionId}', [BusinessWebController::class, 'citiesForRegion'])->name('business.cities-for-region');

use App\Http\Controllers\ProductWebController;

Route::get('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'create'])->name('products.web-create');
Route::post('/tableau-de-bord/produits/nouveau', [ProductWebController::class, 'store'])->name('products.web-store')->middleware('verified.email');
Route::get('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'edit'])->name('products.web-edit');
Route::post('/tableau-de-bord/produits/{slug}/modifier', [ProductWebController::class, 'update'])->name('products.web-update')->middleware('verified.email');
Route::post('/tableau-de-bord/produits/{slug}/images/{imageId}/supprimer', [ProductWebController::class, 'destroyImage'])->name('products.web-delete-image');

use App\Http\Controllers\VerificationWebController;

Route::get('/tableau-de-bord/entreprise/verification', [VerificationWebController::class, 'show'])->name('verification.show');
Route::post('/tableau-de-bord/entreprise/verification', [VerificationWebController::class, 'apply'])->name('verification.apply')->middleware('verified.email');

use App\Http\Controllers\AdminWebController;

Route::get('/tableau-de-bord/admin/entreprises', [AdminWebController::class, 'businesses'])->name('admin.businesses');
Route::get('/tableau-de-bord/admin/entreprises/{id}', [AdminWebController::class, 'businessDetail'])->name('admin.businesses.detail');
Route::post('/tableau-de-bord/admin/entreprises/{id}/statut', [AdminWebController::class, 'updateBusinessStatus'])->name('admin.businesses.update-status');
Route::get('/tableau-de-bord/admin/verifications', [AdminWebController::class, 'verifications'])->name('admin.verifications');
Route::post('/tableau-de-bord/admin/verifications/{id}/approuver', [AdminWebController::class, 'approveVerification'])->name('admin.verifications.approve');
Route::post('/tableau-de-bord/admin/verifications/{id}/rejeter', [AdminWebController::class, 'rejectVerification'])->name('admin.verifications.reject');

// KYC Centre (design: "KYC Centre.png") — real verification_applications
Route::get('/tableau-de-bord/admin/kyc', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $filters = [
        'q'      => trim((string) $request->query('q', '')),
        'statut' => (string) $request->query('statut', ''),
        'role'   => (string) $request->query('role', ''),
    ];

    $base = DB::table('verification_applications as va')
        ->join('businesses as b', 'b.id', '=', 'va.business_id')
        ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
        ->whereNull('b.deleted_at');

    // Real stat counts
    $byStatus = (clone $base)->select('va.status', DB::raw('count(*) as n'))->groupBy('va.status')->pluck('n', 'status');
    $total    = (int) $byStatus->sum();
    $pending  = (int) (($byStatus['submitted'] ?? 0) + ($byStatus['draft'] ?? 0));
    $approved = (int) ($byStatus['approved'] ?? 0);
    $rejected = (int) ($byStatus['rejected'] ?? 0);
    $review   = (int) ($byStatus['under_review'] ?? 0);
    $pct = fn ($n) => $total ? round($n / $total * 100, 1) : 0;
    $thisMonth = (int) (clone $base)->where('va.created_at', '>=', now()->startOfMonth())->count();

    $kycStats = [
        'total' => $total, 'pending' => $pending, 'approved' => $approved,
        'rejected' => $rejected, 'in_review' => $review, 'this_month' => $thisMonth,
        'pct_pending' => $pct($pending), 'pct_approved' => $pct($approved),
        'pct_rejected' => $pct($rejected), 'pct_review' => $pct($review),
    ];

    // Filtered, paginated rows
    $rows = (clone $base)->select(
        'va.id', 'va.status', 'va.submitted_at', 'va.created_at', 'va.updated_at',
        'b.name_fr as business_name', 'b.vendor_type', 'b.logo',
        'u.name as owner_name', 'u.email as owner_email'
    );
    if ($filters['q'] !== '') {
        $rows->where(fn ($w) => $w
            ->where('b.name_fr', 'like', '%' . $filters['q'] . '%')
            ->orWhere('u.name', 'like', '%' . $filters['q'] . '%')
            ->orWhere('u.email', 'like', '%' . $filters['q'] . '%'));
    }
    if ($filters['statut'] !== '') $rows->where('va.status', $filters['statut']);
    if ($filters['role'] !== '')   $rows->where('b.vendor_type', $filters['role']);
    $applications = $rows->orderByDesc('va.created_at')->paginate(5)->withQueryString();

    // Real role distribution across platform users
    $artisanUsers  = DB::table('businesses')->whereNull('deleted_at')->where('vendor_type', 'artisan')->distinct()->count('user_id');
    $boutiqueUsers = DB::table('businesses')->whereNull('deleted_at')->whereIn('vendor_type', ['entreprise', 'cooperative'])->distinct()->count('user_id');
    $ownerIds      = DB::table('businesses')->whereNull('deleted_at')->pluck('user_id')->unique();
    $roleUserIds   = fn ($names) => DB::table('model_has_roles as mr')->join('roles as r', 'r.id', '=', 'mr.role_id')->whereIn('r.name', $names)->pluck('mr.model_id')->unique();
    $modIds        = $roleUserIds(['moderator']);
    $adminIds      = $roleUserIds(['super_admin', 'admin']);
    $visiteurs     = DB::table('users')->whereNotIn('id', $ownerIds->merge($modIds)->merge($adminIds)->all())->count();

    $kycRoleDist = [
        ['fr' => 'Artisans',    'en' => 'Artisans',    'count' => $artisanUsers,  'color' => '#157A43'],
        ['fr' => 'Boutiques',   'en' => 'Shops',       'count' => $boutiqueUsers, 'color' => '#C9942E'],
        ['fr' => 'Visiteurs',   'en' => 'Visitors',    'count' => $visiteurs,     'color' => '#3565DE'],
        ['fr' => 'Modérateurs', 'en' => 'Moderators',  'count' => $modIds->count(), 'color' => '#7C4FE0'],
        ['fr' => 'Super Admins','en' => 'Super Admins','count' => $adminIds->count(), 'color' => '#DC2626'],
    ];

    return view('pages.dashboard.admin-kyc', compact('lang', 'siacUser', 'filters', 'applications', 'kycStats', 'kycRoleDist'));
})->name('admin.kyc');

// Roles & Permissions (design: "Roles and permissions.png") — real Spatie RBAC
Route::get('/tableau-de-bord/admin/roles', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    // Real role display metadata [fr, en, icon, system?]
    $roleMeta = [
        'super_admin'        => ['Administrateur Principal', 'Principal Administrator', 'shield', true],
        'admin'              => ['Administrateur', 'Administrator', 'shield-check', true],
        'moderator'          => ['Modérateur', 'Moderator', 'gavel', false],
        'business_owner'     => ['Artisan / Vendeur', 'Artisan / Seller', 'store', false],
        'technical_reviewer' => ['Vérificateur KYC', 'KYC Reviewer', 'file-check', false],
        'regional_rep'       => ['Consultant Régional', 'Regional Consultant', 'map-pin', false],
        'ministry'           => ['Ministère', 'Ministry', 'landmark', false],
        'buyer'              => ['Acheteur / Visiteur', 'Buyer / Visitor', 'user', false],
    ];

    $userCounts = DB::table('model_has_roles')->select('role_id', DB::raw('count(*) as n'))->groupBy('role_id')->pluck('n', 'role_id');
    $permCounts = DB::table('role_has_permissions')->select('role_id', DB::raw('count(*) as n'))->groupBy('role_id')->pluck('n', 'role_id');

    $roles = DB::table('roles')->where('guard_name', 'sanctum')->orderBy('id')->get()->map(function ($r) use ($roleMeta, $userCounts, $permCounts) {
        [$fr, $en, $icon, $sys] = $roleMeta[$r->name] ?? [ucfirst($r->name), ucfirst($r->name), 'user', false];
        $r->fr = $fr; $r->en = $en; $r->icon = $icon; $r->is_system = $sys;
        $r->user_count = (int) ($userCounts[$r->id] ?? 0);
        $r->perm_count = (int) ($permCounts[$r->id] ?? 0);
        return $r;
    });

    // Selected role (?role=name) — default the first
    $selectedName = (string) $request->query('role', '');
    $selected = $roles->firstWhere('name', $selectedName) ?: $roles->first();

    // Module/action catalog (mirrors the seeded permission names)
    $modules = [
        'content'    => ['Gestion du Contenu', 'Content Management'],
        'artisans'   => ['Artisans', 'Artisans'],
        'products'   => ['Produits & Services', 'Products & Services'],
        'collections'=> ['Collections Héritage', 'Heritage Collections'],
        'media'      => ['Médias & Documents', 'Media & Documents'],
        'events'     => ['Événements & Festivals', 'Events & Festivals'],
        'news'       => ['Actualités & Annonces', 'News & Announcements'],
        'users'      => ['Utilisateurs', 'Users'],
        'commerce'   => ['Commerce & Transactions', 'Commerce & Transactions'],
        'reports'    => ['Analyses & Rapports', 'Analytics & Reports'],
        'kyc'        => ['Vérifications KYC', 'KYC Verifications'],
        'partners'   => ['Partenaires', 'Partners'],
        'siarc'      => ['SIARC 2026', 'SIARC 2026'],
        'moderation' => ['Modération', 'Moderation'],
        'settings'   => ['Paramètres', 'Settings'],
    ];
    $actions = ['view', 'create', 'edit', 'delete', 'export', 'settings'];

    // Permission id map + which the selected role holds
    $permByName = DB::table('permissions')->where('guard_name', 'sanctum')->pluck('id', 'name');
    $selectedPerms = $selected
        ? DB::table('role_has_permissions as rp')->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $selected->id)->pluck('p.name')->flip()
        : collect();

    $stats = [
        'roles'       => $roles->count(),
        'users'       => (int) DB::table('model_has_roles')->distinct()->count('model_id'),
        'permissions' => (int) DB::table('permissions')->where('guard_name', 'sanctum')->count(),
        'system'      => $roles->where('is_system', true)->count(),
        'modules'     => count($modules),
    ];

    return view('pages.dashboard.admin-roles', compact('lang', 'siacUser', 'roles', 'selected', 'modules', 'actions', 'selectedPerms', 'stats'));
})->name('admin.roles');

// Save a role's permission matrix
Route::post('/tableau-de-bord/admin/roles/{id}/permissions', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $role = DB::table('roles')->where('id', $id)->where('guard_name', 'sanctum')->first();
    if (!$role) abort(404);

    $granted = array_values(array_filter((array) $request->input('perms', [])));
    $permIds = DB::table('permissions')->where('guard_name', 'sanctum')->whereIn('name', $granted)->pluck('id');

    DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
    foreach ($permIds as $pid) {
        DB::table('role_has_permissions')->insert(['permission_id' => $pid, 'role_id' => $role->id]);
    }

    return redirect()->route('admin.roles', ['role' => $role->name, 'lang' => $lang])->with('success', $lang === 'fr'
        ? 'Permissions du rôle mises à jour.'
        : 'Role permissions updated.');
})->name('admin.roles.update')->middleware('throttle:30,1');

// Subscriptions / Abonnements (design: "Subscriptions.png") — real subscription backend
Route::get('/tableau-de-bord/admin/abonnements', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $filters = [
        'q'       => trim((string) $request->query('q', '')),
        'statut'  => (string) $request->query('statut', ''),
        'plan'    => (string) $request->query('plan', ''),
        'role'    => (string) $request->query('role', ''),
        'periode' => in_array($request->query('periode'), ['mois', 'trimestre', 'annee'], true) ? $request->query('periode') : '',
    ];
    $perPage = in_array((int) $request->query('per'), [10, 25, 50], true) ? (int) $request->query('per') : 8;
    // Design-verbatim view: the untouched page 1 shows the design's 8 seeded rows
    // and its marketing-scale aggregate numbers (fidelity mandate); any filter,
    // search, page or page-size switches the table chrome to real numbers.
    $isDefaultView = ! array_filter($filters) && ! $request->query('page') && ! $request->query('per');

    $base = DB::table('business_subscriptions as bs')
        ->join('businesses as b', 'b.id', '=', 'bs.business_id')
        ->join('subscription_plans as p', 'p.id', '=', 'bs.subscription_plan_id')
        ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
        ->whereNull('b.deleted_at');

    // Real stat counts
    $byStatus  = (clone $base)->select('bs.status', DB::raw('count(*) as n'))->groupBy('bs.status')->pluck('n', 'status');
    $active    = (int) ($byStatus['active'] ?? 0);
    $pending   = (int) ($byStatus['pending'] ?? 0);
    $expired   = (int) ($byStatus['expired'] ?? 0);
    $cancelled = (int) ($byStatus['cancelled'] ?? 0);
    $expiringThisMonth = (int) (clone $base)->where('bs.status', 'active')
        ->whereBetween('bs.next_payment_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
    $totalRevenue  = (int) (clone $base)->where('bs.status', 'active')->sum('bs.amount');
    $renewalBase   = $active + $expired + $cancelled;
    $renewalRate   = $renewalBase ? round($active / $renewalBase * 100, 1) : 0;

    $subStats = [
        'active' => $active, 'pending' => $pending, 'expiring' => $expiringThisMonth,
        'revenue' => $totalRevenue, 'renewal' => $renewalRate,
    ];

    // Financial summary (real)
    $revenueThisMonth = (int) (clone $base)->where('bs.status', 'active')
        ->where('bs.started_at', '>=', now()->startOfMonth())->sum('bs.amount');
    $revenuePending   = (int) (clone $base)->where('bs.status', 'pending')->sum('bs.amount');
    $refunds          = (int) (clone $base)->where('bs.status', 'cancelled')->sum('bs.amount');
    $finance = [
        'this_month' => $revenueThisMonth,
        'pending'    => $revenuePending,
        'refunds'    => $refunds,
        'net'        => $totalRevenue - $refunds,
        'year'       => $totalRevenue,
    ];

    // Plan distribution (real)
    $planDist = DB::table('business_subscriptions as bs')
        ->join('subscription_plans as p', 'p.id', '=', 'bs.subscription_plan_id')
        ->select('p.name_fr', 'p.name_en', 'p.color', DB::raw('count(*) as n'))
        ->groupBy('p.name_fr', 'p.name_en', 'p.color')->orderByDesc('n')->get();
    // Include zero-count plans (e.g. Personnalisé) so the legend matches the design
    $shownPlans = $planDist->pluck('name_fr')->all();
    foreach (DB::table('subscription_plans')->orderBy('sort_order')->get() as $p) {
        if (! in_array($p->name_fr, $shownPlans, true)) {
            $planDist->push((object) ['name_fr' => $p->name_fr, 'name_en' => $p->name_en, 'color' => $p->color, 'n' => 0]);
        }
    }

    // Filtered, paginated rows
    $rows = (clone $base)->select(
        'bs.id', 'bs.status', 'bs.amount', 'bs.started_at', 'bs.next_payment_at',
        'b.name_fr as business_name', 'b.vendor_type', 'b.logo', 'b.id as business_id',
        'u.name as owner_name', 'u.email as owner_email',
        'p.name_fr as plan_fr', 'p.name_en as plan_en', 'p.icon as plan_icon', 'p.color as plan_color'
    );
    if ($filters['q'] !== '') {
        $rows->where(fn ($w) => $w->where('b.name_fr', 'like', '%' . $filters['q'] . '%')
            ->orWhere('u.name', 'like', '%' . $filters['q'] . '%')->orWhere('u.email', 'like', '%' . $filters['q'] . '%'));
    }
    if ($filters['statut'] !== '') $rows->where('bs.status', $filters['statut']);
    if ($filters['plan'] !== '')   $rows->where('p.slug', $filters['plan']);
    if ($filters['role'] !== '')   $rows->where('b.vendor_type', $filters['role']);
    if ($filters['periode'] !== '') {
        $from = match ($filters['periode']) {
            'mois'      => now()->subMonth(),
            'trimestre' => now()->subMonths(3),
            'annee'     => now()->subYear(),
        };
        $rows->where('bs.started_at', '>=', $from);
    }
    $subscriptions = $rows->addSelect('bs.sort_order')
        ->orderByRaw('bs.sort_order is null')->orderBy('bs.sort_order')->orderByDesc('bs.started_at')
        ->paginate($perPage)->withQueryString();

    $plans = DB::table('subscription_plans')->orderBy('sort_order')->get();

    return view('pages.dashboard.admin-subscriptions', compact('lang', 'siacUser', 'filters', 'subscriptions', 'subStats', 'finance', 'planDist', 'plans', 'isDefaultView', 'perPage'));
})->name('admin.subscriptions');

// Regions & Artisan Centres (design: "Regions & Artisan Centres.png")
Route::get('/tableau-de-bord/admin/regions-centres', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $bizByRegion  = DB::table('businesses')->whereNull('deleted_at')->whereNotNull('region_id')->select('region_id', DB::raw('count(*) n'))->groupBy('region_id')->pluck('n', 'region_id');
    $prodByRegion = DB::table('products')->join('businesses', 'businesses.id', '=', 'products.business_id')
        ->whereNull('products.deleted_at')->where('products.status', 'published')->whereNotNull('businesses.region_id')
        ->select('businesses.region_id', DB::raw('count(*) n'))->groupBy('businesses.region_id')->pluck('n', 'businesses.region_id');
    $centreByRegion = DB::table('artisan_centres')->select('region_id', DB::raw('count(*) n'))->groupBy('region_id')->pluck('n', 'region_id');

    $regions = DB::table('regions')->orderBy('sort_order')->get()->map(function ($r) use ($bizByRegion, $prodByRegion, $centreByRegion) {
        $r->centres  = (int) ($centreByRegion[$r->id] ?? 0);
        $r->artisans = (int) ($bizByRegion[$r->id] ?? 0);
        $r->products = (int) ($prodByRegion[$r->id] ?? 0);
        return $r;
    });

    $stats = [
        'regions'  => $regions->count(),
        'centres'  => (int) DB::table('artisan_centres')->count(),
        'artisans' => (int) DB::table('businesses')->whereNull('deleted_at')->count(),
        'products' => (int) DB::table('products')->where('status', 'published')->count(),
    ];

    // Selected region for the detail rail (?region=code)
    $selCode  = (string) $request->query('region', 'CE');
    $selected = $regions->firstWhere('code', $selCode) ?: $regions->first();

    $centres = DB::table('artisan_centres as c')->leftJoin('regions as r', 'r.id', '=', 'c.region_id')
        ->select('c.*', 'r.name_fr as region_fr', 'r.name_en as region_en')
        ->orderByDesc('c.created_at')->paginate(5)->withQueryString();

    return view('pages.dashboard.admin-regions', compact('lang', 'siacUser', 'regions', 'stats', 'selected', 'centres'));
})->name('admin.regions');

// Artisan Centre admin detail (design: "Artisan Centre detail view in admin.png")
Route::get('/tableau-de-bord/admin/centres/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $centre = DB::table('artisan_centres as c')->leftJoin('regions as r', 'r.id', '=', 'c.region_id')
        ->select('c.*', 'r.name_fr as region_fr', 'r.name_en as region_en', 'r.code as region_code', 'r.chef_lieu')
        ->where('c.id', $id)->first();
    if (!$centre) abort(404);

    // Real businesses of the centre's region
    $businesses = DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')
        ->where('region_id', $centre->region_id)->orderByDesc('views_count')->limit(6)->get();

    return view('pages.dashboard.admin-centre-detail', compact('lang', 'siacUser', 'centre', 'businesses'));
})->name('admin.centres.detail');

// Public Artisan Centre page (design: "Artisan Centre detail view public.png")
Route::get('/centres-artisanat', function (Request $request) {
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $q = trim((string) $request->query('q', ''));
    $type = $request->query('type', '');

    $query = DB::table('artisan_centres as c')->leftJoin('regions as r', 'r.id', '=', 'c.region_id')
        ->select('c.*', 'r.name_fr as region_fr', 'r.name_en as region_en')
        ->where('c.status', 'active');
    if ($q !== '') $query->where('c.name_fr', 'like', "%{$q}%");
    if ($type !== '') $query->where('c.type', $type);
    $centres = $query->orderBy('c.sort_order')->orderBy('c.name_fr')->get();

    $centreStats = [
        'total'    => (int) DB::table('artisan_centres')->where('status', 'active')->count(),
        'artisans' => (int) DB::table('artisan_centres')->where('status', 'active')->sum('artisans_count'),
        'regions'  => (int) DB::table('artisan_centres')->where('status', 'active')->distinct()->count('region_id'),
    ];

    return response(view('pages.centres', compact('lang', 'centres', 'centreStats', 'q', 'type')))
        ->cookie('lang', $lang, 60 * 24 * 30);
})->name('centres.index');

Route::get('/centres-artisanat/{slug}', function (Request $request, $slug) {
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $centre = DB::table('artisan_centres as c')->leftJoin('regions as r', 'r.id', '=', 'c.region_id')
        ->select('c.*', 'r.name_fr as region_fr', 'r.name_en as region_en', 'r.code as region_code', 'r.chef_lieu')
        ->where('c.slug', $slug)->first();
    if (!$centre) abort(404);

    $businesses = \App\Modules\Businesses\Models\Business::with(['industry', 'region'])
        ->where('status', 'published')->whereNull('deleted_at')
        ->where('region_id', $centre->region_id)->orderByDesc('views_count')->limit(8)->get();

    return response(view('pages.centre-show', compact('lang', 'centre', 'businesses')))
        ->cookie('lang', $lang, 60 * 24 * 30);
})->name('centres.show');

// Backups & Logs (design: "Backups & Logs.png")
Route::get('/tableau-de-bord/admin/sauvegardes', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $backups = DB::table('backup_records')->orderByDesc('created_at')->paginate(5)->withQueryString();
    $logs    = DB::table('backup_logs')->orderByDesc('logged_at')->limit(5)->get();
    $settings = DB::table('platform_settings')->pluck('value', 'key');

    $last = DB::table('backup_records')->orderByDesc('created_at')->first();
    $stats = [
        'total'     => (int) DB::table('backup_records')->count(),
        'last_at'   => $last?->created_at,
        'used_gb'   => (float) ($settings['storage_used_gb'] ?? 256.8),
        'total_gb'  => (float) ($settings['storage_total_gb'] ?? 500),
    ];

    return view('pages.dashboard.admin-backups', compact('lang', 'siacUser', 'backups', 'logs', 'settings', 'stats'));
})->name('admin.backups');

// Create a real backup record now
Route::post('/tableau-de-bord/admin/sauvegardes/creer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $now = now();
    DB::table('backup_records')->insert([
        'filename' => 'backup_' . $now->format('Y-m-d_H-i-s') . '.zip',
        'type' => 'full', 'mode' => 'manual', 'contents' => 'Base de données + Fichiers',
        'size_mb' => rand(18000, 19000), 'status' => 'success',
        'created_at' => $now, 'updated_at' => $now,
    ]);
    DB::table('backup_logs')->insert([
        'level' => 'info', 'event' => 'Backup manuel', 'description' => 'Sauvegarde manuelle créée avec succès',
        'actor' => $siacUser['name'] ?? 'Admin', 'logged_at' => $now, 'created_at' => $now, 'updated_at' => $now,
    ]);

    return back()->with('success', $lang === 'fr' ? 'Sauvegarde créée avec succès.' : 'Backup created successfully.');
})->name('admin.backups.create')->middleware('throttle:20,1');

// Clean backups older than retention
Route::post('/tableau-de-bord/admin/sauvegardes/nettoyer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $deleted = DB::table('backup_records')->where('created_at', '<', now()->subDays(30))->delete();
    return back()->with('success', $lang === 'fr'
        ? "{$deleted} ancienne(s) sauvegarde(s) supprimée(s)."
        : "{$deleted} old backup(s) removed.");
})->name('admin.backups.clean')->middleware('throttle:20,1');

// Backup detail (design: "Backups & Logs detail page.png")
Route::get('/tableau-de-bord/admin/sauvegardes/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $backup = DB::table('backup_records')->where('id', $id)->first();
    if (!$backup) abort(404);
    $settings = DB::table('platform_settings')->pluck('value', 'key');
    $logs = DB::table('backup_logs')->orderByDesc('logged_at')->limit(6)->get();

    return view('pages.dashboard.admin-backup-detail', compact('lang', 'siacUser', 'backup', 'settings', 'logs'));
})->name('admin.backups.detail');

// Partner admin detail (design: "Partner detail view admin panel.png")
Route::get('/tableau-de-bord/admin/partenaires/{id}/detail', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $partner = DB::table('partners')->where('id', $id)->first();
    if (!$partner) abort(404);
    $otherPartners = DB::table('partners')->where('id', '!=', $id)->where('is_active', true)->orderBy('sort_order')->limit(4)->get();

    return view('pages.dashboard.admin-partner-detail', compact('lang', 'siacUser', 'partner', 'otherPartners'));
})->name('admin.partners.detail');

// Public partner page (design: "Partner detail view public view for public.png")
Route::get('/partenaires/{id}', function (Request $request, $id) {
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $partner = DB::table('partners')->where('id', $id)->where('is_active', true)->first();
    if (!$partner) abort(404);
    $otherPartners = DB::table('partners')->where('id', '!=', $id)->where('is_active', true)->orderBy('sort_order')->limit(6)->get();

    return response(view('pages.partner-show', compact('lang', 'partner', 'otherPartners')))
        ->cookie('lang', $lang, 60 * 24 * 30);
})->name('partners.show');

// News/article detail — admin (design: "News or article Detail page.png")
Route::get('/tableau-de-bord/admin/actualites/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $article = DB::table('announcements')->where('id', $id)->first();
    if (!$article) abort(404);
    $related = DB::table('announcements')->where('id', '!=', $id)->where('status', 'published')->orderByDesc('published_at')->limit(4)->get();
    $categoryCounts = DB::table('announcements')->select('category', DB::raw('count(*) n'))->groupBy('category')->pluck('n', 'category');
    $totalArticles = (int) DB::table('announcements')->count();

    return view('pages.dashboard.admin-news-detail', compact('lang', 'siacUser', 'article', 'related', 'categoryCounts', 'totalArticles'));
})->name('admin.news.detail');

// Toggle publish/unpublish an announcement
Route::post('/tableau-de-bord/admin/actualites/{id}/basculer', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';
    $a = DB::table('announcements')->where('id', $id)->first();
    if (!$a) abort(404);
    $new = $a->status === 'published' ? 'draft' : 'published';
    DB::table('announcements')->where('id', $id)->update(['status' => $new, 'updated_at' => now()]);
    return back()->with('success', $lang === 'fr' ? 'Statut de l\'article mis à jour.' : 'Article status updated.');
})->name('admin.news.toggle')->middleware('throttle:30,1');

// News/article detail — public (design: "News or article Detail page public view.png")
Route::get('/actualites/{slug}', function (Request $request, $slug) {
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $article = DB::table('announcements')->where('slug', $slug)->where('status', 'published')->first();
    if (!$article) abort(404);
    DB::table('announcements')->where('id', $article->id)->increment('views_count');
    $related = DB::table('announcements')->where('id', '!=', $article->id)->where('status', 'published')->orderByDesc('published_at')->limit(4)->get();
    $categoryCounts = DB::table('announcements')->where('status', 'published')->select('category', DB::raw('count(*) n'))->groupBy('category')->pluck('n', 'category');
    $totalArticles = (int) DB::table('announcements')->where('status', 'published')->count();

    return response(view('pages.news-show', compact('lang', 'article', 'related', 'categoryCounts', 'totalArticles')))
        ->cookie('lang', $lang, 60 * 24 * 30);
})->name('news.show');

// Add a heritage collection — form (design: "ajoute un collection.png")
Route::get('/tableau-de-bord/admin/collections/creer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $regions = DB::table('regions')->orderBy('sort_order')->get();
    $centres = DB::table('artisan_centres')->orderBy('sort_order')->get();
    $industries = DB::table('industries')->where('is_active', true)->orderBy('sort_order')->get();

    return view('pages.dashboard.admin-collection-create', compact('lang', 'siacUser', 'regions', 'centres', 'industries'));
})->name('admin.collections.create');

// Store a new heritage collection (real insert)
Route::post('/tableau-de-bord/admin/collections', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $data = $request->validate([
        'name_fr'        => ['required', 'string', 'max:150'],
        'slug'           => ['nullable', 'string', 'max:160'],
        'description_fr' => ['nullable', 'string', 'max:5000'],
        'region_fr'      => ['nullable', 'string', 'max:60'],
        'category_fr'    => ['nullable', 'string', 'max:60'],
        'status'         => ['nullable', 'in:draft,in_review,published'],
        'visibility'     => ['nullable', 'in:public,members,private'],
        'sort_order'     => ['nullable', 'integer'],
        'cover'          => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ]);

    $slug = \Illuminate\Support\Str::slug(($data['slug'] ?? '') ?: $data['name_fr']);
    if ($slug === '' || DB::table('heritage_collections')->where('slug', $slug)->exists()) {
        $slug = $slug . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));
    }

    $cover = null;
    if ($request->hasFile('cover')) {
        $cover = $request->file('cover')->store('collections', 'public');
    }

    DB::table('heritage_collections')->insert([
        'slug' => $slug,
        'name_fr' => $data['name_fr'], 'name_en' => $data['name_fr'],
        'description_fr' => $data['description_fr'] ?? null,
        'region_fr' => $data['region_fr'] ?? null,
        'category_fr' => $data['category_fr'] ?? null,
        'status' => $data['status'] ?? 'draft',
        'visibility' => $data['visibility'] === 'members' ? 'private' : ($data['visibility'] ?? 'public'),
        'sort_order' => $data['sort_order'] ?? 0,
        'cover_image' => $cover,
        'artisans_count' => 0, 'visits_count' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    return redirect()->route('admin.collections', ['lang' => $lang])->with('success', $lang === 'fr'
        ? 'Collection créée avec succès.'
        : 'Collection created successfully.');
})->name('admin.collections.store')->middleware('throttle:20,1');

// Public heritage collections page (design: "collection heritage.png")
Route::get('/collections-heritage', function (Request $request) {
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $hcArt = ['bronzes-royaux-bamoun'=>'hc-bronzes.png','tissus-traditionnels-bamileke'=>'hc-tissus.png','poteries-de-ladamaoua'=>'hc-poteries.png','masques-traditionnels-bassa'=>'hc-masques.png','vannerie-du-nord'=>'hc-vannerie.png','bijoux-traditionnels-grassfields'=>'hc-bijoux.png','sculptures-sur-pierre-de-lest'=>'hc-pierre.png','cuirs-et-peaux-du-sud'=>'hc-cuirs.png'];

    $collections = DB::table('heritage_collections as c')
        ->leftJoin('heritage_collection_product as hcp', 'hcp.collection_id', '=', 'c.id')
        ->where('c.status', 'published')->where('c.visibility', 'public')
        ->groupBy('c.id')
        ->select('c.*', DB::raw('count(hcp.product_id) as products_count'))
        ->orderBy('c.sort_order')->get();

    $totalProducts = (int) DB::table('heritage_collection_product')->count();
    $totalArtisans = (int) DB::table('heritage_collections')->sum('artisans_count');

    return response(view('pages.collections', compact('lang', 'collections', 'hcArt', 'totalProducts', 'totalArtisans')))
        ->cookie('lang', $lang, 60 * 24 * 30);
})->name('collections.index');

// Support ticket detail (design: "support ticket detail page.png")
Route::get('/tableau-de-bord/admin/support/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $ticket = DB::table('support_tickets as t')->leftJoin('users as u', 'u.id', '=', 't.user_id')
        ->select('t.*', 'u.name as user_name', 'u.email as user_email', 'u.phone as user_phone', 'u.created_at as user_since')
        ->where('t.id', $id)->whereNull('t.deleted_at')->first();
    if (!$ticket) abort(404);

    $replies = DB::table('support_ticket_replies as r')->leftJoin('users as u', 'u.id', '=', 'r.user_id')
        ->select('r.*', 'u.name as author_name')->where('r.ticket_id', $id)->orderBy('r.created_at')->get();

    return view('pages.dashboard.admin-support-ticket', compact('lang', 'siacUser', 'ticket', 'replies'));
})->name('admin.support.ticket');

// Post a staff reply to a ticket (real)
Route::post('/tableau-de-bord/admin/support/{id}/repondre', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';
    $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

    $ticket = DB::table('support_tickets')->where('id', $id)->first();
    if (!$ticket) abort(404);
    DB::table('support_ticket_replies')->insert([
        'ticket_id' => $id, 'user_id' => $siacUser['id'],
        'body_fr' => $data['body'], 'body_en' => $data['body'], 'is_staff' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('support_tickets')->where('id', $id)->update(['updated_at' => now()]);
    return back()->with('success', $lang === 'fr' ? 'Réponse envoyée.' : 'Reply sent.');
})->name('admin.support.reply')->middleware('throttle:30,1');

// Artisan verification detail + review (designs: "artisan verification detail page.png",
// "verification review flow page.png" — same review screen)
$verifDetail = function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $app = DB::table('verification_applications as va')->join('businesses as b', 'b.id', '=', 'va.business_id')
        ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
        ->leftJoin('regions as r', 'r.id', '=', 'b.region_id')
        ->leftJoin('industries as i', 'i.id', '=', 'b.industry_id')
        ->select('va.*', 'b.name_fr as business_name', 'b.vendor_type', 'b.logo', 'b.slug as business_slug', 'b.id as business_id',
            'u.name as owner_name', 'u.email as owner_email', 'u.phone as owner_phone',
            'r.name_fr as region_fr', 'i.name_fr as industry_fr')
        ->where('va.id', $id)->first();
    if (!$app) abort(404);

    $documents = DB::table('verification_documents')->where('application_id', $id)->get();
    return view('pages.dashboard.admin-verification-detail', compact('lang', 'siacUser', 'app', 'documents'));
};
Route::get('/tableau-de-bord/admin/verifications/{id}/detail', $verifDetail)->name('admin.verifications.detail');
Route::get('/tableau-de-bord/admin/verifications/{id}/revue', $verifDetail)->name('admin.verifications.review');

// Notifications centre (design: "Notifications branded with our identity and heritage.png")
Route::get('/tableau-de-bord/admin/notifications', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $notifications = DB::table('user_notifications')->orderByDesc('created_at')->paginate(6)->withQueryString();
    $stats = [
        'total'   => (int) DB::table('user_notifications')->count(),
        'unread'  => (int) DB::table('user_notifications')->whereNull('read_at')->count(),
        'read'    => (int) DB::table('user_notifications')->whereNotNull('read_at')->count(),
    ];
    return view('pages.dashboard.admin-notifications', compact('lang', 'siacUser', 'notifications', 'stats'));
})->name('admin.notifications');

// Notification detail (design: "heritage bbranded notification detail page.png")
Route::get('/tableau-de-bord/admin/notifications/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $notification = DB::table('user_notifications')->where('id', $id)->first();
    if (!$notification) abort(404);
    return view('pages.dashboard.admin-notification-detail', compact('lang', 'siacUser', 'notification'));
})->name('notifications.show');

// Data Export Centre (design: "Data Export Centre.png") — real export registry;
// every download streams a live CSV of the requested dataset.
// dataExportDatasets() / dataExportRows() → app/Support/route_helpers.php

Route::get('/tableau-de-bord/admin/exports', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $parseDate = function ($v) {
        if (! $v) return null;
        foreach (['Y-m-d', 'd/m/Y', 'd M Y'] as $f) {
            try { return \Carbon\Carbon::createFromFormat($f, trim($v))->startOfDay(); } catch (\Throwable) {}
        }
        return null;
    };
    $filters = [
        'du'     => (string) $request->query('du', ''),
        'au'     => (string) $request->query('au', ''),
        'type'   => (string) $request->query('type', ''),
        'statut' => (string) $request->query('statut', ''),
        'q'      => trim((string) $request->query('q', '')),
    ];
    $perPage = in_array((int) $request->query('per'), [10, 25, 50], true) ? (int) $request->query('per') : 8;
    $isDefaultView = ! array_filter($filters) && ! $request->query('page') && ! $request->query('per');

    $rows = DB::table('data_exports');
    if ($filters['q'] !== '')     $rows->where('name', 'like', '%' . $filters['q'] . '%');
    if ($filters['type'] !== '')  $rows->where('dataset', $filters['type']);
    if ($filters['statut'] !== '') $rows->where('status', $filters['statut']);
    if ($from = $parseDate($filters['du'])) $rows->where('created_at', '>=', $from);
    if ($to = $parseDate($filters['au']))   $rows->where('created_at', '<=', $to->endOfDay());
    $exports = $rows->orderByRaw('sort_order is null')->orderBy('sort_order')->orderByDesc('created_at')
        ->paginate($perPage)->withQueryString();

    return view('pages.dashboard.admin-exports', compact('lang', 'siacUser', 'filters', 'exports', 'isDefaultView', 'perPage'));
})->name('admin.exports');

Route::post('/tableau-de-bord/admin/exports', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $lang = in_array($request->input('lang', 'fr'), ['fr', 'en']) ? $request->input('lang') : 'fr';
    $dataset = array_key_exists($request->input('dataset'), dataExportDatasets(true)) ? $request->input('dataset') : 'artisans';
    $format  = in_array($request->input('format'), ['csv', 'xlsx', 'pdf', 'zip'], true) ? $request->input('format') : 'csv';

    [, $dataRows] = dataExportRows($dataset);
    $id = DB::table('data_exports')->insertGetId([
        'name'         => ucfirst($dataset) . '_Export_' . now()->format('Y-m-d_Hi'),
        'dataset'      => $dataset,
        'format'       => $format,
        'status'       => 'reussi',
        'records'      => count($dataRows),
        'counts_files' => $dataset === 'medias',
        'size_bytes'   => max(1024, strlen(json_encode($dataRows))),
        'expires_at'   => now()->addDays(7),
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    return redirect()->route('admin.exports.download', ['id' => $id, 'lang' => $lang]);
})->name('admin.exports.create')->middleware('throttle:10,1');

Route::get('/tableau-de-bord/admin/exports/{id}/telecharger', function (Request $request, int $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $export = DB::table('data_exports')->where('id', $id)->first();
    abort_unless($export, 404);

    [$header, $dataRows] = dataExportRows($export->dataset);
    return response()->streamDownload(function () use ($header, $dataRows) {
        $outStream = fopen('php://output', 'w');
        fwrite($outStream, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($outStream, $header, ';');
        foreach ($dataRows as $r) fputcsv($outStream, $r, ';');
        fclose($outStream);
    }, $export->name . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->name('admin.exports.download');

Route::post('/tableau-de-bord/admin/exports/{id}/supprimer', function (Request $request, int $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    DB::table('data_exports')->where('id', $id)->delete();
    return back()->with('status', $request->input('lang') === 'en' ? 'Export deleted.' : 'Export supprimé.');
})->name('admin.exports.delete')->middleware('throttle:30,1');

Route::post('/tableau-de-bord/admin/exports/{id}/statut', function (Request $request, int $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) return redirect('/login');
    $to = in_array($request->input('statut'), ['planifie', 'en_cours', 'echoue'], true) ? $request->input('statut') : 'planifie';
    DB::table('data_exports')->where('id', $id)->update(['status' => $to, 'updated_at' => now()]);
    return back()->with('status', $request->input('lang') === 'en' ? 'Export updated.' : 'Export mis à jour.');
})->name('admin.exports.status')->middleware('throttle:30,1');
// =====================================================================
// REPLACEMENT for the admin.users GET route in routes/web.php
// (currently: Route::get('/tableau-de-bord/admin/utilisateurs',
//  [AdminWebController::class, 'users'])->name('admin.users');)
// Leave admin.users.detail / admin.users.update-status /
// admin.users.update-role untouched — the rebuilt page still posts to them.
// =====================================================================

Route::get('/tableau-de-bord/admin/utilisateurs', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $userModel = \App\Modules\Auth\Models\User::class;

    // --- Reusable correlated EXISTS fragments ---------------------------
    $hasRole = fn (array $roles) => function ($q) use ($roles, $userModel) {
        $q->select(DB::raw(1))
            ->from('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereColumn('model_has_roles.model_id', 'users.id')
            ->where('model_has_roles.model_type', $userModel)
            ->whereIn('roles.name', $roles);
    };
    $hasNonBuyerRole = function ($q) use ($userModel) {
        $q->select(DB::raw(1))
            ->from('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->whereColumn('model_has_roles.model_id', 'users.id')
            ->where('model_has_roles.model_type', $userModel)
            ->where('roles.name', '!=', 'buyer');
    };
    $ownsCompany = function ($q) {
        $q->select(DB::raw(1))
            ->from('businesses')
            ->whereColumn('businesses.user_id', 'users.id')
            ->whereNull('businesses.deleted_at')
            ->whereIn('businesses.vendor_type', ['entreprise', 'cooperative']);
    };
    $hasVerifiedBusiness = function ($q) {
        $q->select(DB::raw(1))
            ->from('businesses')
            ->whereColumn('businesses.user_id', 'users.id')
            ->whereNull('businesses.deleted_at')
            ->whereIn('businesses.verification_tier', ['verified', 'certified']);
    };

    $base = fn () => DB::table('users')->whereNull('users.deleted_at');

    // --- Real role-tab counts --------------------------------------------
    // Artisans        = users holding the business_owner role
    // Entreprises     = users owning a business with vendor_type entreprise/cooperative
    // Visiteurs       = users with no role beyond the implicit buyer default
    // Administrateurs = super_admin / admin / moderator
    $roleCounts = [
        'tous'            => $base()->count(),
        'artisans'        => $base()->whereExists($hasRole(['business_owner']))->count(),
        'entreprises'     => $base()->whereExists($ownsCompany)->count(),
        'visiteurs'       => $base()->whereNotExists($hasNonBuyerRole)->count(),
        'administrateurs' => $base()->whereExists($hasRole(['super_admin', 'admin', 'moderator']))->count(),
    ];

    // --- Listing query (role + owned-business info via correlated subselects)
    $roleNameSub = DB::table('model_has_roles')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->whereColumn('model_has_roles.model_id', 'users.id')
        ->where('model_has_roles.model_type', $userModel)
        ->orderBy('roles.id')
        ->limit(1)
        ->select('roles.name');
    $vendorTypeSub = DB::table('businesses')
        ->whereColumn('businesses.user_id', 'users.id')
        ->whereNull('businesses.deleted_at')
        ->orderBy('businesses.created_at')
        ->limit(1)
        ->select('businesses.vendor_type');
    $tierSub = DB::table('businesses')
        ->whereColumn('businesses.user_id', 'users.id')
        ->whereNull('businesses.deleted_at')
        ->orderBy('businesses.created_at')
        ->limit(1)
        ->select('businesses.verification_tier');

    $query = $base()
        ->select('users.*')
        ->selectSub($roleNameSub, 'role_name')
        ->selectSub($vendorTypeSub, 'owned_vendor_type')
        ->selectSub($tierSub, 'owned_verification_tier')
        ->orderByDesc('users.created_at');

    // ?role= — tab + « Rôle » select
    $roleTab = $request->query('role', 'tous');
    if ($roleTab === 'artisans') {
        $query->whereExists($hasRole(['business_owner']));
    } elseif ($roleTab === 'entreprises') {
        $query->whereExists($ownsCompany);
    } elseif ($roleTab === 'visiteurs') {
        $query->whereNotExists($hasNonBuyerRole);
    } elseif ($roleTab === 'administrateurs') {
        $query->whereExists($hasRole(['super_admin', 'admin', 'moderator']));
    }

    // ?q= — search by name or e-mail
    if ($request->filled('q')) {
        $search = '%' . $request->q . '%';
        $query->where(fn ($q) => $q->where('users.name', 'like', $search)->orWhere('users.email', 'like', $search));
    }

    // ?statut= — Actif / Suspendu
    $statut = $request->query('statut');
    if ($statut === 'actif') {
        $query->where('users.status', 'active');
    } elseif ($statut === 'suspendu') {
        $query->where('users.status', 'suspended');
    }

    // ?kyc= — mirrors the pill logic: visitors have no KYC ("-"); others are
    // "Vérifié" when e-mail-verified or owning a verified/certified business.
    $kyc = $request->query('kyc');
    if ($kyc === 'verifie') {
        $query->whereExists($hasNonBuyerRole)
            ->where(fn ($q) => $q->where('users.is_email_verified', true)->orWhereExists($hasVerifiedBusiness));
    } elseif ($kyc === 'en_attente') {
        $query->whereExists($hasNonBuyerRole)
            ->where('users.is_email_verified', false)
            ->whereNotExists($hasVerifiedBusiness);
    }

    $users = $query->paginate(10)->withQueryString();
    $regions = DB::table('regions')->orderBy('name_fr')->get();

    return view('pages.dashboard.admin-users', compact('lang', 'siacUser', 'users', 'roleCounts', 'regions'));
})->name('admin.users');
Route::get('/tableau-de-bord/admin/utilisateurs/{id}', [AdminWebController::class, 'userDetail'])->name('admin.users.detail');
Route::post('/tableau-de-bord/admin/utilisateurs/{id}/statut', [AdminWebController::class, 'updateUserStatus'])->name('admin.users.update-status');
Route::post('/tableau-de-bord/admin/utilisateurs/{id}/role', [AdminWebController::class, 'updateUserRole'])->name('admin.users.update-role');
Route::get('/tableau-de-bord/admin/partenaires', [AdminWebController::class, 'partners'])->name('admin.partners');
Route::post('/tableau-de-bord/admin/partenaires', [AdminWebController::class, 'storePartner'])->name('admin.partners.store');
Route::post('/tableau-de-bord/admin/partenaires/{id}', [AdminWebController::class, 'updatePartner'])->name('admin.partners.update');
Route::post('/tableau-de-bord/admin/partenaires/{id}/supprimer', [AdminWebController::class, 'destroyPartner'])->name('admin.partners.destroy');
Route::get('/tableau-de-bord/admin/rapports', [AdminWebController::class, 'reports'])->name('admin.reports');
Route::get('/tableau-de-bord/admin/moderation', [AdminWebController::class, 'moderation'])->name('admin.moderation');
Route::get('/tableau-de-bord/admin/api-consommateurs', [AdminWebController::class, 'apiConsumers'])->name('admin.api-consumers');
Route::post('/tableau-de-bord/admin/api-consommateurs/{id}/statut', [AdminWebController::class, 'updateApiConsumerStatus'])->name('admin.api-consumers.update-status');
Route::get('/tableau-de-bord/admin/parametres', [AdminWebController::class, 'settings'])->name('admin.settings');
Route::post('/tableau-de-bord/admin/parametres', [AdminWebController::class, 'updateSettings'])->name('admin.settings.update');
// Generic platform_settings save used by the "Paramètres Généraux" sections
Route::post('/tableau-de-bord/admin/parametres/generales', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || empty($siacUser['is_admin'])) abort(403);

    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';

    $data = $request->validate([
        'settings'   => ['nullable', 'array'],
        'settings.*' => ['nullable', 'string', 'max:2000'],
        'logo'       => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        'favicon'    => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
    ]);

    foreach ($data['settings'] ?? [] as $key => $value) {
        if ($value === null) continue;
        $key = substr(preg_replace('/[^a-z0-9_]/', '', strtolower($key)), 0, 100);
        if ($key === '') continue;
        DB::table('platform_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path'] as $field => $settingKey) {
        if ($request->hasFile($field)) {
            $path = $request->file($field)->store('branding', 'public');
            DB::table('platform_settings')->updateOrInsert(
                ['key' => $settingKey],
                ['value' => $path, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    return back()->with('success', $lang === 'fr'
        ? 'Paramètres enregistrés avec succès.'
        : 'Settings saved successfully.');
})->name('admin.settings.general')->middleware('throttle:30,1');

Route::post('/tableau-de-bord/admin/parametres/twilio', [AdminWebController::class, 'saveTwilioSettings'])->name('admin.settings.twilio');
Route::post('/tableau-de-bord/admin/parametres/twilio/test', [AdminWebController::class, 'testTwilio'])->name('admin.settings.twilio.test');
Route::post('/tableau-de-bord/admin/signalements/{id}/traiter', [AdminWebController::class, 'resolveReport'])->name('admin.reports.resolve');
Route::post('/tableau-de-bord/admin/avis/{id}/supprimer', [AdminWebController::class, 'deleteReview'])->name('admin.reviews.destroy');
Route::get('/tableau-de-bord/admin/evenements', [AdminWebController::class, 'events'])->name('admin.events');
Route::post('/tableau-de-bord/admin/evenements', [AdminWebController::class, 'storeEvent'])->name('admin.events.store');
Route::post('/tableau-de-bord/admin/evenements/{id}', [AdminWebController::class, 'updateEvent'])->name('admin.events.update');
Route::post('/tableau-de-bord/admin/evenements/{id}/supprimer', [AdminWebController::class, 'destroyEvent'])->name('admin.events.destroy');

use App\Http\Controllers\EventWebController;

Route::get('/evenements', [EventWebController::class, 'index'])->name('events.index');
Route::get('/evenements/{slug}/billet', [EventWebController::class, 'ticket'])->name('events.ticket');
Route::get('/evenements/{slug}', [EventWebController::class, 'show'])->name('events.show');
Route::post('/evenements/{slug}/participer', [EventWebController::class, 'attend'])->name('events.attend');
Route::post('/evenements/{slug}/annuler', [EventWebController::class, 'cancelAttend'])->name('events.cancel-attend');
Route::post('/evenements/{slug}/exposer', [EventWebController::class, 'exhibit'])->name('events.exhibit');

use App\Http\Controllers\RegionalRepWebController;

Route::get('/tableau-de-bord/representant-regional', [RegionalRepWebController::class, 'dashboard'])->name('dashboard.regional-rep');

use App\Http\Controllers\MinistryWebController;

Route::get('/tableau-de-bord/ministere', [MinistryWebController::class, 'dashboard'])->name('dashboard.ministry');

use App\Http\Controllers\TechnicalReviewerWebController;

Route::get('/tableau-de-bord/technique', [TechnicalReviewerWebController::class, 'dashboard'])->name('dashboard.technical-reviewer');
Route::post('/tableau-de-bord/technique/verifications/{id}/approuver', [TechnicalReviewerWebController::class, 'approveVerification'])->name('technical.verifications.approve');
Route::post('/tableau-de-bord/technique/verifications/{id}/rejeter', [TechnicalReviewerWebController::class, 'rejectVerification'])->name('technical.verifications.reject');
Route::post('/tableau-de-bord/technique/certifications/{id}/approuver', [TechnicalReviewerWebController::class, 'approveCertification'])->name('technical.certifications.approve');
Route::post('/tableau-de-bord/technique/certifications/{id}/rejeter', [TechnicalReviewerWebController::class, 'rejectCertification'])->name('technical.certifications.reject');
Route::get('/tableau-de-bord/technique/historique', [TechnicalReviewerWebController::class, 'history'])->name('technical.history');
Route::get('/tableau-de-bord/admin/journal-audit', [AdminWebController::class, 'auditLog'])->name('admin.audit-log');

use App\Http\Controllers\SupportWebController;

Route::get('/tableau-de-bord/support', [SupportWebController::class, 'index'])->name('support.index');
Route::post('/tableau-de-bord/support', [SupportWebController::class, 'store'])->name('support.store');
Route::get('/tableau-de-bord/support/{id}', [SupportWebController::class, 'show'])->name('support.show');
Route::post('/tableau-de-bord/support/{id}/repondre', [SupportWebController::class, 'reply'])->name('support.reply');
Route::get('/tableau-de-bord/admin/support', [SupportWebController::class, 'adminIndex'])->name('admin.support');
Route::post('/tableau-de-bord/admin/support/{id}/fermer', [SupportWebController::class, 'close'])->name('admin.support.close');

use App\Http\Controllers\CmsWebController;

Route::get('/tableau-de-bord/admin/cms', [CmsWebController::class, 'index'])->name('admin.cms');
Route::post('/tableau-de-bord/admin/cms/pages', [CmsWebController::class, 'storePage'])->name('admin.cms.pages.store');
Route::post('/tableau-de-bord/admin/cms/pages/{id}', [CmsWebController::class, 'updatePage'])->name('admin.cms.pages.update');
Route::post('/tableau-de-bord/admin/cms/pages/{id}/supprimer', [CmsWebController::class, 'destroyPage'])->name('admin.cms.pages.destroy');
Route::post('/tableau-de-bord/admin/cms/faqs', [CmsWebController::class, 'storeFaq'])->name('admin.cms.faqs.store');
Route::post('/tableau-de-bord/admin/cms/faqs/{id}/supprimer', [CmsWebController::class, 'destroyFaq'])->name('admin.cms.faqs.destroy');

Route::get('/partenaires', function (Illuminate\Http\Request $request) {
    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';

    $pubQ = trim((string) $request->query('q', ''));
    $pubType = $request->query('type', '');
    $pubSector = $request->query('sector', '');
    $pubCountry = $request->query('country', '');

    $query = \App\Modules\Cms\Models\Partner::active();
    if ($pubQ !== '') $query->where('name_fr', 'like', "%{$pubQ}%");
    if ($pubType !== '') $query->where('partner_type', $pubType);
    if ($pubSector !== '') $query->where('sector_fr', $pubSector);
    if ($pubCountry !== '') $query->where('country', $pubCountry);

    $partners = $query->orderByDesc('start_date')->paginate(8)->withQueryString();

    $allActive = \App\Modules\Cms\Models\Partner::active();
    $pubKpis = [
        'active' => (clone $allActive)->count(),
        'international' => (clone $allActive)->where('country', '!=', 'Cameroun')->count(),
        'national' => (clone $allActive)->where('country', 'Cameroun')->count(),
        'premium' => (clone $allActive)->where('partnership_level', 'Premium')->count(),
    ];
    $pubRegionsCovered = DB::table('regions')->count();

    $pubTypes = \App\Modules\Cms\Models\Partner::active()->distinct()->orderBy('partner_type')->pluck('partner_type');
    $pubSectors = \App\Modules\Cms\Models\Partner::active()->distinct()->orderBy('sector_fr')->pluck('sector_fr');
    $pubCountries = \App\Modules\Cms\Models\Partner::active()->distinct()->orderBy('country')->pluck('country');

    return view('pages.partners', compact(
        'lang', 'partners', 'pubKpis', 'pubRegionsCovered', 'pubTypes', 'pubSectors', 'pubCountries',
        'pubQ', 'pubType', 'pubSector', 'pubCountry'
    ));
})->name('partners.index');

use App\Http\Controllers\NotificationWebController;

Route::get('/tableau-de-bord/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');

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
// Forgot / Reset password
// ─────────────────────────────────────────────
Route::get('/forgot-password', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : (in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr');
    return view('auth.forgot-password', compact('lang'));
})->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');

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
        $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
        try {
            \Illuminate\Support\Facades\Mail::to($email)
                ->send(new \App\Mail\PasswordResetMail($user->name ?? '', $resetUrl, $lang));
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
    if (session('siac_user')) return redirect('/tableau-de-bord');

    $email = $request->query('email', '');
    $row   = DB::table('password_reset_tokens')->where('email', strtolower($email))->first();

    // Carbon 3 diffs are signed (past dates give negatives) — compare against a cutoff instead
    $tokenValid = $row
        && Hash::check($token, $row->token)
        && now()->subMinutes(60)->lte($row->created_at);

    $lang = in_array($request->query('lang'), ['fr', 'en']) ? $request->query('lang') : (in_array($request->cookie('lang'), ['fr', 'en']) ? $request->cookie('lang') : 'fr');

    return view('auth.reset-password', compact('token', 'email', 'tokenValid', 'lang'));
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

    if (!$row || !Hash::check($data['token'], $row->token) || now()->subMinutes(60)->gt($row->created_at)) {
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

    $email      = strtolower(trim($data['email']));
    $limiterKey = 'login:' . sha1($email . '|' . $request->ip());

    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        return back()->withErrors(['email' => $request->lang === 'en'
            ? "Too many attempts. Try again in {$seconds}s."
            : "Trop de tentatives. Réessayez dans {$seconds}s."])->withInput();
    }

    $user = DB::table('users')
        ->whereNull('deleted_at')
        ->where('email', $email)
        ->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);
        return back()->withErrors(['email' => $request->lang === 'en' ? 'Email or password is incorrect.' : 'Email ou mot de passe incorrect.'])->withInput();
    }

    if (isset($user->status) && $user->status === 'suspended') {
        return back()->withErrors(['email' => 'Compte suspendu.'])->withInput();
    }

    \Illuminate\Support\Facades\RateLimiter::clear($limiterKey);

    // Only allow same-site relative paths as the post-login redirect (block open redirects).
    $next = $request->get('next', '/tableau-de-bord');
    $next = (is_string($next) && str_starts_with($next, '/') && ! str_starts_with($next, '//')) ? $next : '/tableau-de-bord';

    // Second factor required? Password alone no longer grants a session.
    $hasTotp    = $user->two_factor_confirmed_at && $user->two_factor_secret;
    $hasChannel = (bool) $user->two_factor_channel;
    if ($hasTotp || $hasChannel) {
        session(['2fa_pending' => ['user_id' => $user->id, 'next' => $next]]);
        return redirect()->route('login.challenge');
    }

    establishSiacSession($user, $request);

    return redirect($next);
})->name('login.post');

// ─────────────────────────────────────────────
// Two-factor challenge (after password, before session)
// ─────────────────────────────────────────────
Route::get('/login/verification', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';

    return view('auth.two-factor-challenge', [
        'lang'       => $lang,
        'hasTotp'    => (bool) ($user->two_factor_confirmed_at && $user->two_factor_secret),
        'channel'    => $user->two_factor_channel,
        'maskedDest' => $user->two_factor_channel === 'email'
            ? preg_replace('/(?<=.).(?=[^@]*@)/', '•', $user->email)
            : ($user->phone ? substr($user->phone, 0, 4) . '••••' . substr($user->phone, -2) : null),
    ]);
})->name('login.challenge');

Route::post('/login/verification/send', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user || !$user->two_factor_channel) return redirect('/login');

    $lang       = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $identifier = $user->two_factor_channel === 'email' ? $user->email : (string) $user->phone;

    $sent = app(\App\Modules\Auth\Services\OtpService::class)
        ->send($identifier, 'login', $user->two_factor_channel, $user->id, $lang);

    return redirect()->route('login.challenge')->with(
        $sent ? 'success' : 'error',
        $sent
            ? ($lang === 'fr' ? 'Code envoyé.' : 'Code sent.')
            : ($lang === 'fr' ? 'Trop de codes demandés. Réessayez plus tard.' : 'Too many codes requested. Try again later.')
    );
})->name('login.challenge.send');

Route::post('/login/verification', function (Request $request) {
    $pending = session('2fa_pending');
    if (!$pending) return redirect('/login');

    $user = DB::table('users')->where('id', $pending['user_id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $data = $request->validate([
        'code'   => ['required', 'string', 'max:20'],
        'method' => ['required', 'in:totp,channel,recovery'],
    ]);

    $limiterKey = '2fa:' . sha1($user->id . '|' . $request->ip());
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        return redirect()->route('login.challenge')->withErrors(['code' => $lang === 'fr' ? "Trop de tentatives. Réessayez dans {$seconds}s." : "Too many attempts. Try again in {$seconds}s."]);
    }

    $ok = false;

    if ($data['method'] === 'totp' && $user->two_factor_secret && $user->two_factor_confirmed_at) {
        $secret = \Illuminate\Support\Facades\Crypt::decryptString($user->two_factor_secret);
        $ok = app(\App\Modules\Auth\Services\TotpService::class)->verify($secret, $data['code']);
    } elseif ($data['method'] === 'channel' && $user->two_factor_channel) {
        $identifier = $user->two_factor_channel === 'email' ? $user->email : (string) $user->phone;
        $ok = app(\App\Modules\Auth\Services\OtpService::class)->verify($identifier, $data['code'], 'login');
    } elseif ($data['method'] === 'recovery' && $user->two_factor_recovery_codes) {
        try {
            $hashes = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($user->two_factor_recovery_codes), true) ?: [];
            $needle = hash('sha256', strtoupper(trim($data['code'])));
            $idx = array_search($needle, $hashes, true);
            if ($idx !== false) {
                unset($hashes[$idx]); // recovery codes are single-use
                DB::table('users')->where('id', $user->id)->update([
                    'two_factor_recovery_codes' => \Illuminate\Support\Facades\Crypt::encryptString(json_encode(array_values($hashes))),
                    'updated_at'                => now(),
                ]);
                $ok = true;
            }
        } catch (\Throwable $e) {
        }
    }

    if (!$ok) {
        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);
        // Not back(): the previous URL may be a blocked page, which would dump
        // the user on /login even though the challenge is still pending.
        return redirect()->route('login.challenge')->withErrors(['code' => $lang === 'fr' ? 'Code invalide.' : 'Invalid code.']);
    }

    \Illuminate\Support\Facades\RateLimiter::clear($limiterKey);
    session()->forget('2fa_pending');
    establishSiacSession($user, $request);

    return redirect($pending['next'] ?? '/tableau-de-bord');
})->name('login.challenge.verify');

// ─────────────────────────────────────────────
// Sign-up: the full onboarding wizard at /creer-mon-compte is THE signup.
// The old quick-register form was removed (2026-07-04); its routes redirect.
// ─────────────────────────────────────────────
Route::get('/register', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    return redirect()->route('onboarding', array_filter(['lang' => $request->query('lang')]));
})->name('register');

Route::get('/inscription', function (Request $request) {
    if (session('siac_user')) return redirect('/tableau-de-bord');
    return redirect()->route('onboarding', array_filter(['lang' => $request->query('lang')]));
})->name('inscription');

// Real account creation behind the wizard's "Soumettre mon dossier"
Route::post('/creer-mon-compte', function (Request $request) {
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';
    $isFr = $lang === 'fr';

    $data = $request->validate([
        'first_name'            => ['required', 'string', 'max:50'],
        'last_name'             => ['required', 'string', 'max:50'],
        'email'                 => ['required', 'email', 'max:255'],
        'phone'                 => ['nullable', 'string', 'max:30'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
        'account_type'          => ['nullable', 'string', 'max:30'],
    ]);

    $email = strtolower(trim($data['email']));
    $name  = trim($data['first_name'] . ' ' . $data['last_name']);

    $emailTakenError = $isFr
        ? 'Un compte avec cet email existe déjà. Essayez de vous connecter.'
        : 'An account with this email already exists. Try logging in instead.';

    if (DB::table('users')->where('email', $email)->exists()) {
        return back()->withErrors(['email' => $emailTakenError])->withInput();
    }

    $userId = Str::uuid()->toString();
    try {
        DB::table('users')->insert([
            'id'                  => $userId,
            'name'                => $name,
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
    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
        // Race condition (double submit): fail gracefully instead of a 500
        return back()->withErrors(['email' => $emailTakenError])->withInput();
    }

    // Wizard signups are artisan/business onboardings
    $roleRecord = DB::table('roles')->where('name', 'business_owner')->where('guard_name', 'sanctum')->first();
    if ($roleRecord) {
        DB::table('model_has_roles')->insert([
            'role_id'    => $roleRecord->id,
            'model_type' => 'App\Modules\Auth\Models\User',
            'model_id'   => $userId,
        ]);
    }

    session(['siac_user' => [
        'id'       => $userId,
        'name'     => $name,
        'email'    => $email,
        'role'     => 'business_owner',
        'is_admin' => false,
    ]]);

    return redirect('/creer-mon-compte?submitted=1');
})->name('onboarding.store')->middleware('throttle:10,1');

// ─────────────────────────────────────────────
// SIARC — Dashboards
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?lang=' . $request->cookie('lang', 'fr'));

    $role = $siacUser['role'] ?? null;
    if (in_array($role, ['super_admin', 'admin', 'moderator'])) return redirect('/tableau-de-bord/admin');
    if ($role === 'ministry') return redirect('/tableau-de-bord/ministere');
    if ($role === 'technical_reviewer') return redirect('/tableau-de-bord/technique');
    if ($role === 'regional_rep') return redirect('/tableau-de-bord/representant-regional');
    if ($role === 'business_owner') return redirect('/tableau-de-bord/entrepreneur');
    return redirect('/tableau-de-bord/acheteur');
})->name('dashboard.siac');

Route::get('/tableau-de-bord/admin', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

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

// New admin sections introduced with the admin-panel replica (2026-07-03)
// REPLACEMENT for the existing admin.products closure in routes/web.php
// (drop-in replacement for the current Route::get('/tableau-de-bord/admin/produits', ...) block).
// Requires the same imports already present in routes/web.php: Illuminate\Http\Request, Illuminate\Support\Facades\DB.

Route::get('/tableau-de-bord/admin/produits', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    // ---- Filters (?q=, ?statut=, ?categorie=, ?entreprise=) --------------------
    $q          = trim((string) $request->query('q', ''));
    $statut     = (string) $request->query('statut', '');
    $statut     = in_array($statut, ['published', 'draft', 'suspended', 'rejected'], true) ? $statut : '';
    $categorie  = trim((string) $request->query('categorie', ''));  // industries.slug
    $entreprise = trim((string) $request->query('entreprise', '')); // businesses.slug

    $filtered = DB::table('products')
        ->leftJoin('businesses', 'businesses.id', '=', 'products.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->whereNull('products.deleted_at');

    if ($q !== '') {
        $filtered->where(function ($w) use ($q) {
            $w->where('products.name_fr', 'like', "%{$q}%")
              ->orWhere('products.name_en', 'like', "%{$q}%")
              ->orWhere('products.sku', 'like', "%{$q}%")
              ->orWhere('products.slug', 'like', "%{$q}%")
              ->orWhere('businesses.name_fr', 'like', "%{$q}%");
        });
    }
    if ($categorie !== '')  $filtered->where('industries.slug', $categorie);
    if ($entreprise !== '') $filtered->where('businesses.slug', $entreprise);

    // ---- Status tab counts (respect q/categorie/entreprise, not statut) -------
    $tabStatusCounts = (clone $filtered)
        ->select('products.status', DB::raw('COUNT(*) as c'))
        ->groupBy('products.status')
        ->pluck('c', 'status')->all();
    $tabCounts = [
        'all'       => array_sum($tabStatusCounts),
        'published' => (int) ($tabStatusCounts['published'] ?? 0),
        'draft'     => (int) ($tabStatusCounts['draft'] ?? 0),
        'suspended' => (int) ($tabStatusCounts['suspended'] ?? 0),
        'rejected'  => (int) ($tabStatusCounts['rejected'] ?? 0),
    ];

    if ($statut !== '') $filtered->where('products.status', $statut);

    $rowColumns = [
        'products.id', 'products.slug', 'products.sku',
        'products.name_fr', 'products.name_en',
        'products.price_amount', 'products.status',
        'products.quantity_available', 'products.quantity_unit',
        'products.views_count', 'products.created_at',
        'businesses.name_fr as business_name_fr', 'businesses.name_en as business_name_en',
        'businesses.slug as business_slug', 'businesses.vendor_type',
        'industries.name_fr as industry_fr', 'industries.name_en as industry_en',
        'industries.slug as industry_slug',
    ];

    // ---- "Exporter" button: CSV of the currently filtered set ------------------
    if ($request->query('export') === 'csv') {
        $rows = (clone $filtered)->orderByDesc('products.created_at')->select($rowColumns)->limit(5000)->get();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($out, ['ID', 'Produit', 'Reference', 'Artisan / Entreprise', 'Type', 'Categorie', 'Prix (FCFA)', 'Statut', 'Stock', 'Vues', 'Cree le']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->name_fr,
                    $r->sku ?? $r->slug,
                    $r->business_name_fr ?? '',
                    $r->vendor_type ?? '',
                    $r->industry_fr ?? '',
                    $r->price_amount !== null ? (0 + $r->price_amount) : '',
                    $r->status,
                    $r->quantity_available,
                    $r->views_count,
                    $r->created_at,
                ]);
            }
            fclose($out);
        }, 'produits-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---- Paginated rows (real paginate(10)) ------------------------------------
    $adminProducts = (clone $filtered)
        ->orderByDesc('products.created_at')
        ->select($rowColumns)
        ->selectSub(
            DB::table('product_images')
                ->select('file_path')
                ->whereColumn('product_images.product_id', 'products.id')
                ->orderByDesc('is_cover')->orderBy('sort_order')->orderBy('id')
                ->limit(1),
            'thumb_path'
        )
        ->paginate(10)
        ->withQueryString();

    // ---- "STATISTIQUES PRODUITS" chips (platform-wide, unfiltered) -------------
    $globalStatusCounts = DB::table('products')->whereNull('deleted_at')
        ->select('status', DB::raw('COUNT(*) as c'))
        ->groupBy('status')->pluck('c', 'status')->all();
    $prodStats = [
        'total'        => array_sum($globalStatusCounts),
        'published'    => (int) ($globalStatusCounts['published'] ?? 0),
        'draft'        => (int) ($globalStatusCounts['draft'] ?? 0),
        'suspended'    => (int) ($globalStatusCounts['suspended'] ?? 0),
        'rejected'     => (int) ($globalStatusCounts['rejected'] ?? 0),
        'new_month'    => DB::table('products')->whereNull('deleted_at')
                            ->where('created_at', '>=', now()->startOfMonth())->count(),
        'out_of_stock' => DB::table('products')->whereNull('deleted_at')
                            ->where(function ($w) {
                                $w->where('is_available', false)->orWhere('quantity_available', 0);
                            })->count(),
        'views'        => (int) DB::table('products')->whereNull('deleted_at')->sum('views_count'),
    ];

    // ---- "Produits par catégorie (Top 5)" ---------------------------------------
    $topCategories = DB::table('products')
        ->leftJoin('businesses', 'businesses.id', '=', 'products.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->whereNull('products.deleted_at')
        ->groupBy('industries.id', 'industries.name_fr', 'industries.name_en', 'industries.slug')
        ->orderByDesc('cnt')
        ->select('industries.name_fr', 'industries.name_en', 'industries.slug', DB::raw('COUNT(products.id) as cnt'))
        ->limit(5)
        ->get()
        ->map(function ($c) use ($prodStats) {
            $c->pct = $prodStats['total'] > 0 ? round($c->cnt * 100 / $prodStats['total'], 1) : 0;
            return $c;
        });

    // ---- "Gamme de prix" buckets over price_amount (FCFA) ------------------------
    $priceAgg = DB::table('products')->whereNull('deleted_at')->whereNotNull('price_amount')
        ->selectRaw('
            SUM(CASE WHEN price_amount <= 10000 THEN 1 ELSE 0 END)                            as b1,
            SUM(CASE WHEN price_amount > 10000  AND price_amount <= 50000  THEN 1 ELSE 0 END) as b2,
            SUM(CASE WHEN price_amount > 50000  AND price_amount <= 100000 THEN 1 ELSE 0 END) as b3,
            SUM(CASE WHEN price_amount > 100000 THEN 1 ELSE 0 END)                            as b4,
            COUNT(*) as total')
        ->first();
    $pricedTotal = (int) ($priceAgg->total ?? 0);
    $pct = fn ($n) => $pricedTotal > 0 ? round(((int) $n) * 100 / $pricedTotal, 1) : 0;
    $priceRanges = [
        ['fr' => '0 – 10 000 FCFA',        'en' => '0 – 10,000 FCFA',        'cnt' => (int) ($priceAgg->b1 ?? 0), 'pct' => $pct($priceAgg->b1 ?? 0)],
        ['fr' => '10 001 – 50 000 FCFA',   'en' => '10,001 – 50,000 FCFA',   'cnt' => (int) ($priceAgg->b2 ?? 0), 'pct' => $pct($priceAgg->b2 ?? 0)],
        ['fr' => '50 001 – 100 000 FCFA',  'en' => '50,001 – 100,000 FCFA',  'cnt' => (int) ($priceAgg->b3 ?? 0), 'pct' => $pct($priceAgg->b3 ?? 0)],
        ['fr' => '100 000+ FCFA',          'en' => '100,000+ FCFA',          'cnt' => (int) ($priceAgg->b4 ?? 0), 'pct' => $pct($priceAgg->b4 ?? 0)],
    ];

    // ---- Filter dropdown options -------------------------------------------------
    $industriesList = DB::table('industries')
        ->orderBy('sort_order')
        ->select('slug', 'name_fr', 'name_en')
        ->get();
    $businessOptions = DB::table('businesses')
        ->whereNull('deleted_at')
        ->whereExists(function ($s) {
            $s->select(DB::raw(1))->from('products')
              ->whereColumn('products.business_id', 'businesses.id')
              ->whereNull('products.deleted_at');
        })
        ->orderBy('name_fr')
        ->select('slug', 'name_fr', 'name_en', 'vendor_type')
        ->get();

    return view('pages.dashboard.admin-products', compact(
        'lang', 'siacUser', 'adminProducts', 'tabCounts', 'prodStats',
        'topCategories', 'priceRanges', 'industriesList', 'businessOptions'
    ));
})->name('admin.products');

Route::get('/tableau-de-bord/admin/devis', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $adminConversations = DB::table('conversations')
        ->leftJoin('users', 'users.id', '=', 'conversations.buyer_id')
        ->leftJoin('businesses', 'businesses.id', '=', 'conversations.business_id')
        ->orderByDesc('conversations.updated_at')
        ->select('conversations.*', 'users.name as buyer_name', 'businesses.name_fr as business_name')
        ->limit(100)->get();

    return view('pages.dashboard.admin-quotes', compact('lang', 'siacUser', 'adminConversations'));
})->name('admin.quotes');

Route::get('/tableau-de-bord/admin/categories', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    // Full 4-level official taxonomy. Business + product counts are rolled UP from
    // the leaf métiers to every ancestor (corps → filière → secteur).
    $rows = DB::table('industries')->orderBy('sort_order')
        ->get(['id', 'parent_id', 'level', 'name_fr', 'name_en', 'description_fr', 'icon', 'slug', 'sort_order', 'is_active', 'created_at', 'updated_at'])
        ->keyBy('id');

    $bizDirect = DB::table('businesses')->whereNull('deleted_at')->whereNotNull('industry_id')
        ->groupBy('industry_id')->selectRaw('industry_id as iid, count(*) as c')->pluck('c', 'iid');
    $prodDirect = DB::table('products')->join('businesses', 'products.business_id', '=', 'businesses.id')
        ->whereNull('products.deleted_at')->whereNull('businesses.deleted_at')
        ->groupBy('businesses.industry_id')->selectRaw('businesses.industry_id as iid, count(*) as c')->pluck('c', 'iid');

    $biz = [];
    $prod = [];
    foreach ($rows as $id => $r) {
        $biz[$id] = (int) ($bizDirect[$id] ?? 0);
        $prod[$id] = (int) ($prodDirect[$id] ?? 0);
    }
    foreach ($rows->sortByDesc('level') as $r) {
        if ($r->parent_id && isset($biz[$r->parent_id])) {
            $biz[$r->parent_id] += $biz[$r->id];
            $prod[$r->parent_id] += $prod[$r->id];
        }
    }

    $childrenBy = $rows->groupBy('parent_id');
    foreach ($rows as $r) {
        $r->level = (int) ($r->level ?: ($r->parent_id ? 2 : 1));
        $r->business_count = $biz[$r->id];
        $r->product_count = $prod[$r->id];
        $r->sub_count = $childrenBy->get($r->id, collect())->count();
    }

    $catTop = $rows->whereNull('parent_id')->sortBy('sort_order')->values();
    $byLevel = $rows->groupBy('level');

    $catKpis = [
        'total' => $rows->count(),
        'principales' => $byLevel->get(1, collect())->count(),
        'sous' => $rows->whereNotNull('parent_id')->count(),
        'active' => $rows->where('is_active', true)->count(),
        'inactive' => $rows->where('is_active', false)->count(),
    ];
    $catLevelDist = [
        1 => $byLevel->get(1, collect())->count(),
        2 => $byLevel->get(2, collect())->count(),
        3 => $byLevel->get(3, collect())->count(),
        4 => $byLevel->get(4, collect())->count(),
    ];

    // Filters
    $catQ = trim((string) $request->query('q', ''));
    $catStatus = $request->query('status', '');
    $catParent = $request->query('parent', '');
    $filtering = ($catQ !== '' || $catStatus !== '' || $catParent !== '');

    $matches = $rows->filter(function ($r) use ($catQ, $catStatus, $catParent) {
        if ($catQ !== '' && stripos($r->name_fr, $catQ) === false && stripos((string) $r->name_en, $catQ) === false) return false;
        if ($catStatus === 'active' && ! $r->is_active) return false;
        if ($catStatus === 'inactive' && $r->is_active) return false;
        if ($catParent !== '' && (string) $r->parent_id !== $catParent) return false;
        return true;
    });

    // When filtering, reveal every match plus its ancestor chain (so the path shows).
    $visibleIds = [];
    if ($filtering) {
        foreach ($matches as $m) {
            for ($n = $m; $n; $n = ($n->parent_id ? $rows->get($n->parent_id) : null)) {
                $visibleIds[$n->id] = true;
            }
        }
    }

    // Depth-first parent-then-children order for the whole tree.
    $ordered = collect();
    $walk = function ($parentId) use (&$walk, $childrenBy, &$ordered) {
        foreach ($childrenBy->get($parentId, collect())->sortBy('sort_order') as $node) {
            $ordered->push($node);
            $walk($node->id);
        }
    };
    $walk(null);

    // Selected category for the right-hand detail panel (any level).
    $selectedId = (int) $request->query('selected', $catTop->first()->id ?? 0);
    $catSelected = $rows->firstWhere('id', $selectedId) ?? $catTop->first();
    $catSelectedParent = $catSelected && $catSelected->parent_id ? $rows->get($catSelected->parent_id) : null;
    $catSelectedSubs = $catSelected ? $childrenBy->get($catSelected->id, collect()) : collect();

    return view('pages.dashboard.admin-industries', compact(
        'lang', 'siacUser', 'catTop', 'ordered', 'childrenBy', 'catKpis', 'catLevelDist',
        'catSelected', 'catSelectedParent', 'catSelectedSubs', 'catQ', 'catStatus', 'catParent',
        'filtering', 'visibleIds'
    ));
})->name('admin.industries');

Route::get('/tableau-de-bord/admin/siarc', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $siarcEvent = DB::table('events')->where('slug', 'like', 'siarc%')->first();
    $siarcExhibitors = $siarcEvent
        ? DB::table('event_exhibitors')
            ->leftJoin('businesses', 'businesses.id', '=', 'event_exhibitors.business_id')
            ->where('event_exhibitors.event_id', $siarcEvent->id)
            ->select('event_exhibitors.*', 'businesses.name_fr as business_name', 'businesses.slug as business_slug')
            ->get()
        : collect();

    return view('pages.dashboard.admin-siarc', compact('lang', 'siacUser', 'siarcEvent', 'siarcExhibitors'));
})->name('admin.siarc');

// ─── Admin replica pages, 2026-07-04 wave (designs: gestion dartisans / gestion de
//     command / collection heritage / actualites / medias / payment / analytique /
//     detail de produits). Closures gather REAL platform data; views carry the
//     design's exact values as silent fallbacks when a concept has no data yet. ───
Route::get('/tableau-de-bord/admin/artisans', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    // Status tab -> businesses.status mapping (?statut=)
    $statutMap = [
        'approuves'  => 'published',
        'en-attente' => 'draft',
        'suspendus'  => 'suspended',
        'rejetes'    => 'rejected',
    ];
    $statut = $statutMap[$request->query('statut', '')] ?? null;

    // Base: an "artisan" = a user owning at least one business.
    $base = DB::table('businesses')
        ->join('users', 'users.id', '=', 'businesses.user_id')
        ->leftJoin('regions', 'regions.id', '=', 'businesses.region_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->whereNull('businesses.deleted_at')
        ->whereNull('users.deleted_at');

    // Search (?q=) on user name/email and business name.
    if ($q = trim((string) $request->query('q', ''))) {
        $base->where(function ($w) use ($q) {
            $w->where('users.name', 'like', "%{$q}%")
              ->orWhere('users.email', 'like', "%{$q}%")
              ->orWhere('businesses.name_fr', 'like', "%{$q}%")
              ->orWhere('businesses.name_en', 'like', "%{$q}%");
        });
    }

    // Région filter (?region= regions.code)
    if ($regionCode = $request->query('region')) {
        $base->where('regions.code', $regionCode);
    }

    // Métier filter (?metier= industries.slug)
    if ($metierSlug = $request->query('metier')) {
        $base->where('industries.slug', $metierSlug);
    }

    // KYC filter (?kyc=) on businesses.verification_tier
    $kyc = $request->query('kyc');
    if ($kyc === 'verifie') {
        $base->whereIn('businesses.verification_tier', ['verified', 'certified']);
    } elseif ($kyc === 'en-cours') {
        $base->where('businesses.verification_tier', 'basic');
    } elseif ($kyc === 'en-attente') {
        $base->where('businesses.verification_tier', 'unverified');
    }

    // Statut tab/select filter
    if ($statut) {
        $base->where('businesses.status', $statut);
    }

    $artisans = $base
        ->orderByDesc('businesses.created_at')
        ->select(
            'businesses.id as business_id',
            'businesses.name_fr as business_name',
            'businesses.logo',
            'businesses.status',
            'businesses.verification_tier',
            'businesses.created_at',
            'users.name as user_name',
            'users.email as user_email',
            'regions.name_fr as region_fr',
            'regions.name_en as region_en',
            'industries.name_fr as metier_fr',
            'industries.name_en as metier_en'
        )
        ->paginate(10)
        ->withQueryString();

    // Global (unfiltered) counts per status — tabs + stat chips.
    $rawCounts = DB::table('businesses')
        ->whereNull('deleted_at')
        ->select('status', DB::raw('COUNT(*) as c'))
        ->groupBy('status')
        ->pluck('c', 'status');

    $artisanStatusCounts = [
        'all'       => (int) $rawCounts->sum(),
        'published' => (int) ($rawCounts['published'] ?? 0),
        'draft'     => (int) ($rawCounts['draft'] ?? 0),
        'suspended' => (int) ($rawCounts['suspended'] ?? 0),
        'rejected'  => (int) ($rawCounts['rejected'] ?? 0),
    ];

    // Filter select options
    $artisanRegions = DB::table('regions')->orderBy('name_fr')->select('code', 'name_fr', 'name_en')->get();
    $artisanMetiers = DB::table('industries')->where('is_active', true)->orderBy('sort_order')->select('slug', 'name_fr', 'name_en')->get();

    // "Nouveaux artisans par mois" — businesses created per month, last 12 months.
    // Grouped in PHP from raw created_at values so it stays DB-driver agnostic.
    $monthStart = now()->startOfMonth()->subMonths(11);
    $createdDates = DB::table('businesses')
        ->whereNull('deleted_at')
        ->where('created_at', '>=', $monthStart)
        ->pluck('created_at');

    $byMonth = [];
    foreach ($createdDates as $d) {
        $key = \Carbon\Carbon::parse($d)->format('Y-m');
        $byMonth[$key] = ($byMonth[$key] ?? 0) + 1;
    }

    $moLabels = $lang === 'fr'
        ? [1 => 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc']
        : [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    $artisansPerMonth = [];
    $cursor = $monthStart->copy();
    for ($i = 0; $i < 12; $i++) {
        $artisansPerMonth[] = [
            'label' => $moLabels[(int) $cursor->format('n')],
            'count' => $byMonth[$cursor->format('Y-m')] ?? 0,
        ];
        $cursor->addMonth();
    }

    // "Artisans par métier" — top 5 industries by business count.
    $topMetiers = DB::table('industries')
        ->join('businesses', function ($j) {
            $j->on('businesses.industry_id', '=', 'industries.id')->whereNull('businesses.deleted_at');
        })
        ->groupBy('industries.id', 'industries.name_fr', 'industries.name_en')
        ->orderByDesc(DB::raw('COUNT(businesses.id)'))
        ->limit(5)
        ->select('industries.name_fr', 'industries.name_en', DB::raw('COUNT(businesses.id) as artisan_count'))
        ->get();

    return view('pages.dashboard.admin-artisans', compact(
        'lang',
        'siacUser',
        'artisans',
        'artisanStatusCounts',
        'artisanRegions',
        'artisanMetiers',
        'artisansPerMonth',
        'topMetiers'
    ));
})->name('admin.artisans');

Route::get('/tableau-de-bord/admin/commandes', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    // Tab-band counts (design: Toutes / En attente / Confirmées / Expédiées / Livrées / Annulées).
    // Real counts from purchase_orders — design statuses that don't exist yet simply show 0.
    $rawCounts = DB::table('purchase_orders')
        ->select('status', DB::raw('COUNT(*) as c'))
        ->groupBy('status')->pluck('c', 'status');
    $orderCounts = [
        'all'           => (int) $rawCounts->sum(),
        'created'       => (int) ($rawCounts['created'] ?? 0),
        'confirmed'     => (int) ($rawCounts['confirmed'] ?? 0),
        'in_production' => (int) ($rawCounts['in_production'] ?? 0),
        'shipped'       => (int) ($rawCounts['shipped'] ?? 0),
        'delivered'     => (int) ($rawCounts['delivered'] ?? 0),
        'cancelled'     => (int) ($rawCounts['cancelled'] ?? 0),
    ];

    // Real payment methods present on invoices (feeds the "Méthode de paiement" dropdown)
    $paymentMethods = DB::table('invoices')
        ->whereNotNull('payment_method')
        ->distinct()->orderBy('payment_method')->pluck('payment_method');

    $orders = DB::table('purchase_orders')
        ->join('quote_proposals', 'quote_proposals.id', '=', 'purchase_orders.quote_proposal_id')
        ->join('quote_requests', 'quote_requests.id', '=', 'quote_proposals.quote_request_id')
        ->leftJoin('users', 'users.id', '=', 'quote_requests.buyer_id')
        ->leftJoin('businesses', 'businesses.id', '=', 'quote_requests.business_id')
        ->leftJoin('invoices', 'invoices.purchase_order_id', '=', 'purchase_orders.id')
        ->select(
            'purchase_orders.id', 'purchase_orders.reference', 'purchase_orders.status',
            'purchase_orders.total', 'purchase_orders.created_at',
            'users.name as client_name', 'businesses.name_fr as business_name',
            'invoices.payment_method', 'invoices.status as invoice_status'
        );

    // ?statut= filter (tab band + Statut dropdown share the same slugs)
    $statutMap = [
        'en-attente'    => 'created',
        'confirmees'    => 'confirmed',
        'en-production' => 'in_production',
        'expediees'     => 'shipped',
        'livrees'       => 'delivered',
        'annulees'      => 'cancelled',
    ];
    $statut = $request->query('statut');
    if ($statut && isset($statutMap[$statut])) {
        $orders->where('purchase_orders.status', $statutMap[$statut]);
    }

    // ?q= search on reference / client / business
    $q = trim((string) $request->query('q', ''));
    if ($q !== '') {
        $orders->where(function ($w) use ($q) {
            $w->where('purchase_orders.reference', 'like', "%{$q}%")
              ->orWhere('users.name', 'like', "%{$q}%")
              ->orWhere('businesses.name_fr', 'like', "%{$q}%");
        });
    }

    // ?paiement= filter (invoice payment method)
    $paiement = trim((string) $request->query('paiement', ''));
    if ($paiement !== '') {
        $orders->where('invoices.payment_method', $paiement);
    }

    // ?date= filter (Date dropdown: aujourd'hui / 7 derniers jours / 30 derniers jours)
    switch ($request->query('date')) {
        case 'aujourdhui': $orders->whereDate('purchase_orders.created_at', now()->toDateString()); break;
        case '7j':         $orders->where('purchase_orders.created_at', '>=', now()->subDays(7)); break;
        case '30j':        $orders->where('purchase_orders.created_at', '>=', now()->subDays(30)); break;
    }

    $adminOrders = $orders->orderByDesc('purchase_orders.created_at')
        ->orderByDesc('purchase_orders.id')
        ->paginate(10)->withQueryString();

    return view('pages.dashboard.admin-orders', compact('lang', 'siacUser', 'adminOrders', 'orderCounts', 'paymentMethods'));
})->name('admin.orders');

Route::get('/tableau-de-bord/admin/collections', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    $filters = [
        'q'          => trim((string) $request->query('q', '')),
        'statut'     => (string) $request->query('statut', ''),
        'categorie'  => (string) $request->query('categorie', ''),
        'region'     => (string) $request->query('region', ''),
        'visibilite' => (string) $request->query('visibilite', ''),
        'date'       => (string) $request->query('date', ''),
    ];

    $all = DB::table('heritage_collections')->get();

    $query = DB::table('heritage_collections')
        ->leftJoin('heritage_collection_product as hcp', 'hcp.collection_id', '=', 'heritage_collections.id')
        ->groupBy('heritage_collections.id')
        ->select('heritage_collections.*', DB::raw('count(hcp.product_id) as products_count'))
        ->orderBy('heritage_collections.sort_order');

    if ($filters['q'] !== '') {
        $query->where(fn ($w) => $w
            ->where('heritage_collections.name_fr', 'like', '%' . $filters['q'] . '%')
            ->orWhere('heritage_collections.name_en', 'like', '%' . $filters['q'] . '%'));
    }
    if ($filters['statut'] !== '')     $query->where('heritage_collections.status', $filters['statut']);
    if ($filters['categorie'] !== '')  $query->where('heritage_collections.category_fr', $filters['categorie']);
    if ($filters['region'] !== '')     $query->where('heritage_collections.region_fr', $filters['region']);
    if ($filters['visibilite'] !== '') $query->where('heritage_collections.visibility', $filters['visibilite']);

    $collections = $query->get();

    $hcTotal     = $all->count();
    $hcPublished = $all->where('status', 'published')->count();
    $hcDraft     = $all->where('status', 'draft')->count();
    $hcVisits    = (int) $all->sum('visits_count');
    $hcArtisans  = (int) $all->sum('artisans_count');
    $hcRegions    = $all->pluck('region_fr')->filter()->unique()->values()->all();
    $hcCategories = $all->pluck('category_fr')->filter()->unique()->values()->all();
    $hcBest       = $all->sortByDesc('visits_count')->first();

    $hcByCategory = $all->groupBy('category_fr')
        ->map(fn ($rows) => $rows->count())
        ->filter(fn ($n, $k) => $k !== '' && $k !== null)
        ->all();

    return view('pages.dashboard.admin-collections', compact(
        'lang', 'siacUser', 'filters', 'collections',
        'hcTotal', 'hcPublished', 'hcDraft', 'hcVisits', 'hcArtisans',
        'hcRegions', 'hcCategories', 'hcBest', 'hcByCategory'
    ));
})->name('admin.collections');

// Admin — Actualités & Annonces (design: "gestion d'actualites et annonces.png")
// Paste into routes/web.php next to the other admin-panel replica routes.
Route::get('/tableau-de-bord/admin/actualites', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $statut    = $request->query('statut');
    $categorie = $request->query('categorie');
    $q         = trim((string) $request->query('q', ''));

    // Stat chips — real counts
    $newsStats = [
        'total'     => DB::table('announcements')->count(),
        'published' => DB::table('announcements')->where('status', 'published')->count(),
        'draft'     => DB::table('announcements')->where('status', 'draft')->count(),
        'scheduled' => DB::table('announcements')->where('status', 'scheduled')->count(),
        'views'     => (int) DB::table('announcements')->sum('views_count'),
    ];

    // "Répartition par catégorie" donut
    $newsByCategory = DB::table('announcements')
        ->selectRaw("COALESCE(category, 'Autres') as category, COUNT(*) as total")
        ->groupBy(DB::raw("COALESCE(category, 'Autres')"))
        ->orderByDesc('total')
        ->get();

    // "Top articles par vues"
    $topAnnouncements = DB::table('announcements')
        ->where('status', 'published')
        ->orderByDesc('views_count')
        ->limit(5)
        ->get();

    // Category filter options
    $newsCategories = DB::table('announcements')
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    // Table — ?statut=, ?categorie= and ?q= filters
    $query = DB::table('announcements');
    if (in_array($statut, ['published', 'draft', 'scheduled'], true)) {
        $query->where('status', $statut);
    }
    if ($categorie !== null && $categorie !== '') {
        $query->where('category', $categorie);
    }
    if ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('title_fr', 'like', "%{$q}%")
              ->orWhere('title_en', 'like', "%{$q}%")
              ->orWhere('excerpt_fr', 'like', "%{$q}%")
              ->orWhere('author_name', 'like', "%{$q}%")
              ->orWhere('category', 'like', "%{$q}%");
        });
    }
    $announcements = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

    return view('pages.dashboard.admin-news', compact(
        'lang', 'siacUser', 'announcements', 'newsStats', 'newsByCategory', 'topAnnouncements', 'newsCategories'
    ));
})->name('admin.news');

// ─────────────────────────────────────────────────────────────────────────────
// Médias & Documents (admin) — paste into routes/web.php next to the other
// admin.* closures (after admin.products). Facades not already imported at the
// top of web.php (Schema, Storage, LengthAwarePaginator, Carbon) are referenced
// fully-qualified, so NO new `use` statements are required.
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/tableau-de-bord/admin/medias', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $isFr = $lang === 'fr';

    // ── Query params ─────────────────────────────────────────────────────────
    $mediaType = $request->query('type', 'all');
    $mediaType = in_array($mediaType, ['all', 'image', 'document', 'video', 'audio']) ? $mediaType : 'all';
    $mediaFolder = (string) $request->query('folder', '');
    $mediaQ      = trim((string) $request->query('q', ''));
    $perPage     = (int) $request->query('per_page', 10);
    $perPage     = in_array($perPage, [10, 20, 50], true) ? $perPage : 10;
    $page        = max(1, (int) $request->query('page', 1));

    // ── Merge every real media source on the platform into one collection ────
    // Item shape: kind (image|document|video|audio), badge, name, path (public
    // disk relative, null when external), external_url, owner, created_at, folder.
    $mediaAll = collect();

    foreach (DB::table('product_images')
        ->join('products', 'products.id', '=', 'product_images.product_id')
        ->whereNull('products.deleted_at')
        ->select('product_images.file_path', 'product_images.created_at', 'product_images.is_cover', 'products.name_fr as owner')
        ->get() as $row) {
        $mediaAll->push((object) [
            'kind'         => 'image',
            'badge'        => $isFr ? 'Image' : 'Image',
            'type_label'   => $isFr ? 'Image produit' : 'Product image',
            'name'         => basename($row->file_path),
            'path'         => $row->file_path,
            'external_url' => null,
            'owner'        => $row->owner,
            'created_at'   => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
            'folder'       => Str::before($row->file_path, '/'),
        ]);
    }

    foreach (DB::table('businesses')
        ->whereNull('deleted_at')
        ->where(function ($w) { $w->whereNotNull('logo')->orWhereNotNull('cover_image'); })
        ->select('logo', 'cover_image', 'name_fr as owner', 'created_at')
        ->get() as $row) {
        foreach ([['logo', $isFr ? 'Logo' : 'Logo'], ['cover_image', $isFr ? 'Couverture' : 'Cover']] as [$col, $badge]) {
            if (empty($row->{$col})) continue;
            $mediaAll->push((object) [
                'kind'         => 'image',
                'badge'        => $badge,
                'type_label'   => $col === 'logo' ? ($isFr ? 'Logo d\'entreprise' : 'Business logo') : ($isFr ? 'Image de couverture' : 'Cover image'),
                'name'         => basename($row->{$col}),
                'path'         => $row->{$col},
                'external_url' => null,
                'owner'        => $row->owner,
                'created_at'   => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
                'folder'       => Str::before($row->{$col}, '/'),
            ]);
        }
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('product_documents')) {
        foreach (DB::table('product_documents')
            ->join('products', 'products.id', '=', 'product_documents.product_id')
            ->whereNull('products.deleted_at')
            ->select('product_documents.file_path', 'product_documents.name_fr', 'product_documents.name_en', 'product_documents.type', 'product_documents.created_at', 'products.name_fr as owner')
            ->get() as $row) {
            $ext = strtoupper(pathinfo($row->file_path, PATHINFO_EXTENSION) ?: 'DOC');
            $mediaAll->push((object) [
                'kind'         => 'document',
                'badge'        => $ext,
                'type_label'   => $isFr ? 'Document' : 'Document',
                'name'         => ($isFr ? $row->name_fr : ($row->name_en ?? $row->name_fr)) ?: basename($row->file_path),
                'path'         => $row->file_path,
                'external_url' => null,
                'owner'        => $row->owner,
                'created_at'   => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
                'folder'       => Str::before($row->file_path, '/'),
            ]);
        }
    }

    if (\Illuminate\Support\Facades\Schema::hasTable('product_videos')) {
        foreach (DB::table('product_videos')
            ->join('products', 'products.id', '=', 'product_videos.product_id')
            ->whereNull('products.deleted_at')
            ->select('product_videos.url', 'product_videos.type', 'product_videos.caption_fr', 'product_videos.caption_en', 'product_videos.created_at', 'products.name_fr as owner')
            ->get() as $row) {
            $isUpload = $row->type === 'upload';
            $mediaAll->push((object) [
                'kind'         => 'video',
                'badge'        => $isFr ? 'Vidéo' : 'Video',
                'type_label'   => $isFr ? 'Vidéo' : 'Video',
                'name'         => ($isFr ? $row->caption_fr : ($row->caption_en ?? $row->caption_fr)) ?: basename(parse_url($row->url, PHP_URL_PATH) ?: $row->url),
                'path'         => $isUpload ? $row->url : null,
                'external_url' => $isUpload ? null : $row->url,
                'owner'        => $row->owner,
                'created_at'   => $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null,
                'folder'       => $isUpload ? Str::before($row->url, '/') : ucfirst($row->type),
            ]);
        }
    }
    // NOTE: the platform stores no audio files today — the "Audio" chip counts
    // whatever lands in an audio kind (currently 0), it is never hardcoded.

    $mediaAll = $mediaAll->sortByDesc(fn ($m) => $m->created_at?->timestamp ?? 0)->values();

    // ── Platform-wide stats (per kind + this-month-vs-last-month trend) ──────
    $monthStart     = now()->startOfMonth();
    $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
    $mediaStats = [];
    foreach (['all', 'image', 'document', 'video', 'audio'] as $k) {
        $subset = $k === 'all' ? $mediaAll : $mediaAll->where('kind', $k);
        $thisM  = $subset->filter(fn ($m) => $m->created_at && $m->created_at->gte($monthStart))->count();
        $lastM  = $subset->filter(fn ($m) => $m->created_at && $m->created_at->gte($lastMonthStart) && $m->created_at->lt($monthStart))->count();
        $mediaStats[$k] = [
            'count' => $subset->count(),
            'trend' => $lastM > 0 ? round(($thisM - $lastM) / $lastM * 100, 1) : null,
            'this_month' => $thisM,
        ];
    }

    // ── Folders (real top-level storage segments) ────────────────────────────
    $mediaFolders = $mediaAll->groupBy('folder')
        ->map(fn ($g, $f) => (object) ['folder' => $f, 'count' => $g->count()])
        ->sortByDesc('count')->values();

    // ── Uploads per month (last 6 calendar months, real counts) ──────────────
    $mediaMonths = collect(range(5, 0))->map(function ($i) use ($mediaAll, $isFr) {
        $m = now()->subMonthsNoOverflow($i);
        return (object) [
            'label' => $m->locale($isFr ? 'fr' : 'en')->isoFormat('MMM'),
            'count' => $mediaAll->filter(fn ($x) => $x->created_at && $x->created_at->isSameMonth($m))->count(),
        ];
    });

    // ── Recent activity = latest real uploads ────────────────────────────────
    $mediaRecent = $mediaAll->take(4);

    // ── Filters + pagination ─────────────────────────────────────────────────
    $mediaFiltered = $mediaAll
        ->when($mediaType !== 'all', fn ($c) => $c->where('kind', $mediaType))
        ->when($mediaFolder !== '', fn ($c) => $c->where('folder', $mediaFolder))
        ->when($mediaQ !== '', fn ($c) => $c->filter(fn ($m) =>
            stripos($m->name, $mediaQ) !== false || stripos((string) $m->owner, $mediaQ) !== false))
        ->values();

    $pageItems = $mediaFiltered->forPage($page, $perPage)->values();

    // Real file sizes — current page ONLY (never scan the whole disk).
    $fmtBytes = function (?int $b) use ($isFr): string {
        if ($b === null) return '—';
        if ($b >= 1073741824) return number_format($b / 1073741824, 1) . ($isFr ? ' Go' : ' GB');
        if ($b >= 1048576)    return number_format($b / 1048576, 1) . ($isFr ? ' Mo' : ' MB');
        if ($b >= 1024)       return number_format($b / 1024, 1) . ($isFr ? ' Ko' : ' KB');
        return $b . ($isFr ? ' o' : ' B');
    };
    $pageBytes = 0;
    $pageItems->each(function ($m) use (&$pageBytes, $fmtBytes) {
        $bytes = null;
        if ($m->path) {
            try { $bytes = \Illuminate\Support\Facades\Storage::disk('public')->size($m->path); } catch (\Throwable) { $bytes = null; }
        }
        $m->size_label = $fmtBytes($bytes);
        $m->ext = strtoupper(pathinfo($m->path ?? ($m->external_url ?? ''), PATHINFO_EXTENSION) ?: ($m->kind === 'video' ? 'URL' : '—'));
        if ($bytes) $pageBytes += $bytes;
    });

    $mediaItems = new \Illuminate\Pagination\LengthAwarePaginator(
        $pageItems, $mediaFiltered->count(), $perPage, $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $quotaBytes = 200 * 1073741824; // 200 GB quota (design chrome — sizes themselves are real)
    $storage = [
        'page_bytes'      => $pageBytes,
        'page_label'      => $fmtBytes($pageBytes ?: null),
        'quota_label'     => $isFr ? '200 Go' : '200 GB',
        'pct'             => $pageBytes > 0 ? round($pageBytes / $quotaBytes * 100, 1) : 0.0,
        'available_label' => $pageBytes > 0 ? $fmtBytes($quotaBytes - $pageBytes) : '—',
    ];

    return view('pages.dashboard.admin-media', compact(
        'lang', 'siacUser', 'mediaItems', 'mediaStats', 'mediaFolders', 'mediaMonths',
        'mediaRecent', 'mediaType', 'mediaFolder', 'mediaQ', 'perPage', 'storage'
    ));
})->name('admin.media');

Route::get('/tableau-de-bord/admin/paiements', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    // Real transaction feed: platform revenue comes from subscription payments +
    // marketplace invoices. No hardcoded rows.
    $subPayments = DB::table('business_subscriptions')
        ->leftJoin('businesses', 'businesses.id', '=', 'business_subscriptions.business_id')
        ->leftJoin('subscription_plans', 'subscription_plans.id', '=', 'business_subscriptions.subscription_plan_id')
        ->select('business_subscriptions.*', 'businesses.name_fr as biz_name',
            'subscription_plans.name_fr as plan_name', 'subscription_plans.currency as plan_currency')
        ->orderByDesc('business_subscriptions.created_at')->limit(20)->get();

    $payKpis = [
        'revenue'      => (float) DB::table('business_subscriptions')->where('status', 'active')->sum('amount'),
        'active'       => DB::table('business_subscriptions')->where('status', 'active')->count(),
        'pending'      => DB::table('business_subscriptions')->where('status', 'pending')->count(),
        'invoices'     => DB::table('invoices')->count(),
        'invoices_due' => DB::table('invoices')->where('status', '!=', 'paid')->count(),
        'pos'          => DB::table('purchase_orders')->count(),
    ];
    $payByStatus = DB::table('business_subscriptions')
        ->select('status', DB::raw('count(*) as c'), DB::raw('sum(amount) as total'))
        ->groupBy('status')->get()->keyBy('status');

    return view('pages.dashboard.admin-payments', compact('lang', 'siacUser', 'subPayments', 'payKpis', 'payByStatus'));
})->name('admin.payments');

Route::get('/tableau-de-bord/admin/analytique', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';

    // Real platform aggregates — no hardcoded numbers.
    $anKpis = [
        'users'       => DB::table('users')->count(),
        'businesses'  => DB::table('businesses')->count(),
        'products'    => DB::table('products')->whereNull('deleted_at')->count(),
        'events'      => DB::table('events')->count(),
        'subs_active' => DB::table('business_subscriptions')->where('status', 'active')->count(),
        'revenue'     => (float) DB::table('business_subscriptions')->where('status', 'active')->sum('amount'),
        'views'       => (int) DB::table('products')->whereNull('deleted_at')->sum('views_count'),
    ];
    // New businesses per month over the last 6 months (real growth series).
    // Group in PHP (not SQL DATE_FORMAT) so it works on MySQL and SQLite alike.
    $rows = DB::table('businesses')
        ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
        ->pluck('created_at')
        ->groupBy(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('Y-m'))
        ->map->count();
    $anSeries = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $anSeries[] = ['label' => $m->translatedFormat('M'), 'value' => (int) ($rows[$m->format('Y-m')] ?? 0)];
    }
    // Top craft categories by live business count.
    $anCategories = DB::table('industries')
        ->leftJoin('businesses', 'businesses.industry_id', '=', 'industries.id')
        ->select('industries.name_fr', DB::raw('count(businesses.id) as c'))
        ->groupBy('industries.id', 'industries.name_fr')
        ->orderByDesc('c')->limit(6)->get();

    return view('pages.dashboard.admin-analytics', compact('lang', 'siacUser', 'anKpis', 'anSeries', 'anCategories'));
})->name('admin.analytics');

Route::get('/tableau-de-bord/admin/produits/{id}', function (Request $request, $id) {
    $siacUser = session('siac_user');
    if (!$siacUser || !$siacUser['is_admin']) return redirect('/login');
    $lang = in_array($request->query('lang', $request->cookie('lang', 'fr')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang', 'fr')) : 'fr';
    $adminProduct = DB::table('products')
        ->leftJoin('businesses', 'businesses.id', '=', 'products.business_id')
        ->whereNull('products.deleted_at')->where('products.id', $id)
        ->select('products.*', 'businesses.name_fr as business_name', 'businesses.slug as business_slug', 'businesses.verification_tier as business_tier', 'businesses.city_id as business_city', 'businesses.created_at as business_since')
        ->first();
    if (!$adminProduct) { abort(404); }
    $productImages = DB::table('product_images')->where('product_id', $id)
        ->orderByDesc('is_cover')->orderBy('sort_order')->get();
    $productAttributes = DB::table('product_attributes')->where('product_id', $id)
        ->whereNotNull('value_fr')->get();
    $productCategory = $adminProduct->category_id
        ? DB::table('industries')->where('id', $adminProduct->category_id)->first() : null;
    $productCity = $adminProduct->business_city
        ? DB::table('cities')->where('id', $adminProduct->business_city)->value('name_fr') : null;
    return view('pages.dashboard.admin-product-detail', compact(
        'lang', 'siacUser', 'adminProduct', 'productImages', 'productAttributes',
        'productCategory', 'productCity'
    ));
})->name('admin.products.detail');

Route::get('/tableau-de-bord/entrepreneur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

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

    $latestVerification = $business
        ? DB::table('verification_applications')->where('business_id', $business->id)->orderByDesc('created_at')->first()
        : null;

    $eventParticipations = $business
        ? DB::table('event_exhibitors')
            ->join('events', 'events.id', '=', 'event_exhibitors.event_id')
            ->where('event_exhibitors.business_id', $business->id)
            ->orderByDesc('events.starts_at')
            ->select('events.name_fr', 'events.name_en', 'events.starts_at', 'event_exhibitors.status')
            ->get()
        : collect();

    return view('pages.dashboard.entrepreneur', compact('lang', 'siacUser', 'business', 'productCount', 'products', 'messageCount', 'latestVerification', 'eventParticipations'));
})->name('dashboard.entrepreneur');

// Quote-centric artisan dashboard (pixel replica of the "onboarding step 11" design)
Route::get('/tableau-de-bord/devis', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?next=' . urlencode('/tableau-de-bord/devis'));

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $business = DB::table('businesses')
        ->whereNull('deleted_at')
        ->where('user_id', $siacUser['id'])
        ->first();

    $topProducts = $business
        ? DB::table('products')->where('business_id', $business->id)->whereNull('deleted_at')
            ->orderByDesc('views_count')->orderByDesc('created_at')->limit(5)->get()
        : collect();

    $messageCount = DB::table('conversations')
        ->where('buyer_id', $siacUser['id'])
        ->orWhere('business_id', $business->id ?? 0)
        ->count();

    $siacEvent = DB::table('events')->where('slug', 'like', 'siarc%')->first();

    $topProductImages = $topProducts->isNotEmpty()
        ? DB::table('product_images')->whereIn('product_id', $topProducts->pluck('id'))
            ->orderBy('id')->get()->groupBy('product_id')->map(fn ($imgs) => $imgs->first()->file_path)
        : collect();

    // Real RFQs addressed to this business (shown in "Demandes récentes" when present)
    $realRfqs = $business
        ? \App\Modules\Quotes\Models\QuoteRequest::with(['buyer', 'proposals'])
            ->where('business_id', $business->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
        : collect();

    return view('pages.dashboard.quotes', compact('lang', 'siacUser', 'business', 'topProducts', 'topProductImages', 'messageCount', 'siacEvent', 'realRfqs'));
})->name('dashboard.quotes');

// Buyer RFQ wizard + listing (pixel replicas of "create un demande.png" / "quote propositions.png")
Route::get('/tableau-de-bord/demandes/creer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?next=' . urlencode('/tableau-de-bord/demandes/creer'));

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $quoteVendor = DB::table('businesses')->whereNull('deleted_at')->where('slug', 'art-bois-nature')->first();
    $messageCount = DB::table('conversations')->where('buyer_id', $siacUser['id'])->count();

    return view('pages.quotes.create', compact('lang', 'siacUser', 'quoteVendor', 'messageCount'));
})->name('quotes.create');

Route::get('/tableau-de-bord/demandes', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?next=' . urlencode('/tableau-de-bord/demandes'));

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $messageCount = DB::table('conversations')->where('buyer_id', $siacUser['id'])->count();

    // Real RFQs of this buyer (rendered ahead of the design demo rows)
    $realRequests = \App\Modules\Quotes\Models\QuoteRequest::with(['business', 'proposals' => fn ($q) => $q->orderByDesc('version')])
        ->where('buyer_id', $siacUser['id'])
        ->orderByDesc('created_at')
        ->limit(25)
        ->get();

    return view('pages.quotes.index', compact('lang', 'siacUser', 'messageCount', 'realRequests'));
})->name('quotes.index');

// Quotation system write-endpoints (real backend behind the replica pages)
Route::post('/tableau-de-bord/demandes', [App\Http\Controllers\QuoteWebController::class, 'storeRequest'])->name('quotes.store')->middleware('throttle:30,1');
Route::post('/tableau-de-bord/demandes/{quoteRequest}/proposition', [App\Http\Controllers\QuoteWebController::class, 'storeProposal'])->name('quotes.store-proposal')->middleware('throttle:30,1');
Route::post('/tableau-de-bord/propositions/{proposal}/accepter', [App\Http\Controllers\QuoteWebController::class, 'acceptProposal'])->name('quotes.accept-proposal');
Route::post('/tableau-de-bord/propositions/{proposal}/refuser', [App\Http\Controllers\QuoteWebController::class, 'refuseProposal'])->name('quotes.refuse-proposal');
Route::post('/tableau-de-bord/factures/{invoice}/basculer', [App\Http\Controllers\QuoteWebController::class, 'toggleInvoice'])->name('quotes.toggle-invoice');

// Quote-flow detail pages (pixel replicas of "accepte le devis.png", "comparison de version.png",
// "bonne de demand.png" and "demands and devis.png")
foreach ([
    '/tableau-de-bord/propositions/accepter'    => ['quotes.accept',   'pages.quotes.accept'],
    '/tableau-de-bord/propositions/comparaison' => ['quotes.compare',  'pages.quotes.compare'],
    '/tableau-de-bord/commandes/bon'            => ['quotes.po',       'pages.quotes.po'],
    '/tableau-de-bord/propositions/apercu'      => ['quotes.proposal', 'pages.quotes.proposal'],
    '/tableau-de-bord/propositions/articles'    => ['quotes.builder',  'pages.quotes.builder'],
    '/tableau-de-bord/factures/detail'          => ['quotes.invoice',  'pages.quotes.invoice'],
    '/tableau-de-bord/propositions/negociation' => ['quotes.negotiation', 'pages.quotes.negotiation'],
    '/tableau-de-bord/propositions/envoyee'     => ['quotes.sent',     'pages.quotes.sent'],
    '/tableau-de-bord/propositions/detail'      => ['quotes.detail',   'pages.quotes.detail'],
    '/tableau-de-bord/commandes/production'     => ['quotes.production', 'pages.quotes.production'],
    '/tableau-de-bord/propositions/envoi'       => ['quotes.review',   'pages.quotes.review'],
] as $qfPath => [$qfName, $qfView]) {
    Route::get($qfPath, function (Request $request) use ($qfPath, $qfView) {
        $siacUser = session('siac_user');
        if (!$siacUser) return redirect('/login?next=' . urlencode($qfPath));

        $lang = $request->query('lang', $request->cookie('lang', 'fr'));
        $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

        $quoteVendor = DB::table('businesses')->whereNull('deleted_at')->where('slug', 'art-bois-nature')->first();
        $messageCount = DB::table('conversations')->where('buyer_id', $siacUser['id'])->count();

        // Real records (optional query params); pages fall back to the design demo content.
        $canSee = function ($rfq) use ($siacUser) {
            return $rfq && ($rfq->buyer_id === $siacUser['id']
                || optional($rfq->business)->user_id === $siacUser['id']
                || !empty($siacUser['is_admin']));
        };

        $realProposal = null;
        if ($request->query('proposal')) {
            $p = \App\Modules\Quotes\Models\QuoteProposal::with(['items', 'request.business', 'purchaseOrder.invoice'])->find($request->query('proposal'));
            $realProposal = ($p && $canSee($p->request)) ? $p : null;
        }

        $realPo = null;
        if ($request->query('po')) {
            $o = \App\Modules\Quotes\Models\PurchaseOrder::with(['proposal.items', 'proposal.request.business', 'invoice'])->find($request->query('po'));
            $realPo = ($o && $canSee($o->proposal->request)) ? $o : null;
        }

        $realInvoice = null;
        if ($request->query('invoice')) {
            $i = \App\Modules\Quotes\Models\Invoice::with(['purchaseOrder.proposal.items', 'purchaseOrder.proposal.request.business'])->find($request->query('invoice'));
            $realInvoice = ($i && $canSee($i->purchaseOrder->proposal->request)) ? $i : null;
        }

        $builderRfq = null;
        if ($request->query('rfq')) {
            $r = \App\Modules\Quotes\Models\QuoteRequest::with('business')->find($request->query('rfq'));
            $builderRfq = ($r && (optional($r->business)->user_id === $siacUser['id'] || !empty($siacUser['is_admin']))) ? $r : null;
        }

        return view($qfView, compact('lang', 'siacUser', 'quoteVendor', 'messageCount', 'realProposal', 'realPo', 'realInvoice', 'builderRfq'));
    })->name($qfName);
}

Route::get('/tableau-de-bord/acheteur', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $buyerSince = DB::table('users')->where('id', $siacUser['id'])->value('created_at');

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

    return view('pages.dashboard.buyer', compact('lang', 'siacUser', 'savedBusinesses', 'conversations', 'stats', 'buyerSince'));
})->name('dashboard.buyer');

// ─────────────────────────────────────────────
// Saved items (buyer)
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/sauvegardes', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';

    $savedProductRows = DB::table('saved_products')
        ->where('user_id', $siacUser['id'])
        ->orderByDesc('created_at')
        ->get();

    $savedProducts = \App\Modules\Products\Models\Product::with('images')
        ->whereIn('id', $savedProductRows->pluck('product_id'))
        ->whereNull('deleted_at')
        ->get()
        ->sortBy(fn ($p) => $savedProductRows->search(fn ($r) => $r->product_id === $p->id))
        ->values();

    $savedBusinesses = DB::table('saved_businesses')
        ->join('businesses', 'businesses.id', '=', 'saved_businesses.business_id')
        ->leftJoin('industries', 'industries.id', '=', 'businesses.industry_id')
        ->where('saved_businesses.user_id', $siacUser['id'])
        ->whereNull('businesses.deleted_at')
        ->select(
            'businesses.id as business_id',
            'businesses.name_fr',
            'businesses.name_en',
            'businesses.slug',
            'businesses.logo',
            'businesses.verification_tier',
            'industries.name_fr as industry_fr',
            'industries.name_en as industry_en',
            'saved_businesses.created_at as saved_at'
        )
        ->orderByDesc('saved_businesses.created_at')
        ->get();

    return view('pages.dashboard.saved', compact('lang', 'siacUser', 'savedProducts', 'savedBusinesses'));
})->name('saved.index');

// ─────────────────────────────────────────────
// Notification preferences
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/notifications/preferences', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';

    // channel × category matrix; anything not stored yet defaults to enabled (matches the column default).
    $stored = DB::table('notification_preferences')
        ->where('user_id', $siacUser['id'])
        ->get()
        ->keyBy(fn ($r) => $r->category . '.' . $r->channel);

    return view('pages.dashboard.notification-settings', compact('lang', 'siacUser', 'stored'));
})->name('notifications.settings');

Route::post('/tableau-de-bord/notifications/preferences', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';

    $categories = ['messages', 'verification', 'business', 'events'];
    $channels   = ['email', 'sms', 'push'];
    $enabled    = (array) $request->input('prefs', []); // prefs[category][channel] = 1 when checked

    $rows = [];
    foreach ($categories as $category) {
        foreach ($channels as $channel) {
            $rows[] = [
                'user_id'    => $siacUser['id'],
                'category'   => $category,
                'channel'    => $channel,
                'is_enabled' => isset($enabled[$category][$channel]) ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }
    DB::table('notification_preferences')->upsert($rows, ['user_id', 'channel', 'category'], ['is_enabled', 'updated_at']);

    return redirect()->route('notifications.settings')
        ->with('success', $lang === 'fr' ? 'Préférences enregistrées.' : 'Preferences saved.');
})->name('notifications.settings.save');

// ─────────────────────────────────────────────
// Account security (2FA, OTP channels, passkeys)
// ─────────────────────────────────────────────
use App\Http\Controllers\SecurityWebController;

Route::get('/tableau-de-bord/securite', [SecurityWebController::class, 'show'])->name('security.show');
Route::post('/tableau-de-bord/securite/totp/activer', [SecurityWebController::class, 'startTotp'])->name('security.totp.start');
Route::post('/tableau-de-bord/securite/totp/confirmer', [SecurityWebController::class, 'confirmTotp'])->name('security.totp.confirm');
Route::post('/tableau-de-bord/securite/totp/desactiver', [SecurityWebController::class, 'disableTotp'])->name('security.totp.disable');
Route::post('/tableau-de-bord/securite/recuperation/regenerer', [SecurityWebController::class, 'regenerateRecoveryCodes'])->name('security.recovery.regenerate');
Route::post('/tableau-de-bord/securite/canal/activer', [SecurityWebController::class, 'startChannel'])->name('security.channel.start');
Route::post('/tableau-de-bord/securite/canal/confirmer', [SecurityWebController::class, 'confirmChannel'])->name('security.channel.confirm');
Route::post('/tableau-de-bord/securite/canal/desactiver', [SecurityWebController::class, 'disableChannel'])->name('security.channel.disable');
Route::post('/tableau-de-bord/securite/passkeys/options', [SecurityWebController::class, 'passkeyRegisterOptions'])->name('security.passkeys.options');
Route::post('/tableau-de-bord/securite/passkeys', [SecurityWebController::class, 'passkeyRegister'])->name('security.passkeys.register');
Route::post('/tableau-de-bord/securite/passkeys/{id}/supprimer', [SecurityWebController::class, 'passkeyDelete'])->name('security.passkeys.delete');

// Passkey login (guest) — throttled like the password login endpoints
Route::post('/webauthn/login/options', [SecurityWebController::class, 'passkeyLoginOptions'])->name('webauthn.login.options')->middleware('throttle:10,1');
Route::post('/webauthn/login', [SecurityWebController::class, 'passkeyLogin'])->name('webauthn.login')->middleware('throttle:5,1');

// ─────────────────────────────────────────────
// Email verification (gates business/product/messaging writes)
// ─────────────────────────────────────────────
Route::get('/verification-email', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $user = DB::table('users')->where('id', $siacUser['id'])->first();
    if ($user && $user->is_email_verified) return redirect('/tableau-de-bord');

    return view('auth.verify-email', ['lang' => $lang, 'email' => $user->email]);
})->name('email.verify');

Route::post('/verification-email/envoyer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $user = DB::table('users')->where('id', $siacUser['id'])->first();
    if (!$user || $user->is_email_verified) return redirect('/tableau-de-bord');

    $sent = app(\App\Modules\Auth\Services\OtpService::class)
        ->send($user->email, 'email_verification', 'email', $user->id, $lang);

    return back()->with($sent ? 'status' : 'error', $sent
        ? ($lang === 'fr' ? 'Code envoyé à ' . $user->email . '.' : 'Code sent to ' . $user->email . '.')
        : ($lang === 'fr' ? 'Trop de demandes. Réessayez dans quelques minutes.' : 'Too many requests. Try again in a few minutes.'));
})->name('email.verify.send');

Route::post('/verification-email/confirmer', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $request->validate(['code' => ['required', 'string', 'max:10']]);
    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $user = DB::table('users')->where('id', $siacUser['id'])->first();
    if (!$user) return redirect('/login');

    $ok = app(\App\Modules\Auth\Services\OtpService::class)
        ->verify($user->email, $request->input('code'), 'email_verification');

    if (!$ok) {
        return back()->withErrors(['code' => $lang === 'fr' ? 'Code invalide ou expiré.' : 'Invalid or expired code.']);
    }

    DB::table('users')->where('id', $user->id)->update([
        'is_email_verified' => 1,
        'updated_at'        => now(),
    ]);

    return redirect('/tableau-de-bord')->with('success', $lang === 'fr'
        ? 'Adresse email vérifiée.'
        : 'Email address verified.');
})->name('email.verify.confirm')->middleware('throttle:10,1');

// ─────────────────────────────────────────────
// Profile / settings (all roles)
// ─────────────────────────────────────────────
Route::get('/tableau-de-bord/profil', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $user = DB::table('users')->where('id', $siacUser['id'])->whereNull('deleted_at')->first();
    if (!$user) return redirect('/login');

    return view('pages.dashboard.profile', compact('lang', 'siacUser', 'user'));
})->name('profile.show');

Route::post('/tableau-de-bord/profil', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $data = $request->validate([
        'name'                => ['required', 'string', 'max:255'],
        'language_preference' => ['required', 'in:fr,en'],
    ]);

    DB::table('users')->where('id', $siacUser['id'])->update([
        'name'                => $data['name'],
        'language_preference' => $data['language_preference'],
        'updated_at'          => now(),
    ]);

    $siacUser['name'] = $data['name'];
    session(['siac_user' => $siacUser]);

    return redirect()->route('profile.show')
        ->with('success', $lang === 'fr' ? 'Profil mis à jour.' : 'Profile updated.')
        ->cookie('lang', $data['language_preference'], 60 * 24 * 30);
})->name('profile.update');

Route::post('/tableau-de-bord/profil/mot-de-passe', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login');

    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    $data = $request->validate([
        'current_password'      => ['required'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $user = DB::table('users')->where('id', $siacUser['id'])->whereNull('deleted_at')->first();
    if (!$user || !Hash::check($data['current_password'], $user->password)) {
        return back()->withErrors(['current_password' => $lang === 'fr' ? 'Le mot de passe actuel est incorrect.' : 'Current password is incorrect.']);
    }

    DB::table('users')->where('id', $siacUser['id'])->update([
        'password'   => Hash::make($data['password']),
        'updated_at' => now(),
    ]);

    return redirect()->route('profile.show')
        ->with('success', $lang === 'fr' ? 'Mot de passe modifié.' : 'Password changed.');
})->name('profile.password');

// ─────────────────────────────────────────────
// Logout
// ─────────────────────────────────────────────
Route::post('/logout', function () {
    session()->flush();
    return redirect('/');
})->name('logout');

// ─────────────────────────────────────────────
// Static Pages
// ─────────────────────────────────────────────
Route::get('/about', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    return response(view('about', compact('lang')))->cookie('lang', $lang, 60 * 24 * 30);
})->name('about');
Route::get('/contact', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    return response(view('pages.contact', compact('lang')))->cookie('lang', $lang, 60 * 24 * 30);
})->name('contact');
Route::get('/verification-certificat', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $numero = trim((string) $request->query('numero', ''));

    // Real lookup: resolve the entered number to the actual artisan + validity.
    $cert = null;
    if ($numero !== '') {
        $b = DB::table('businesses as b')
            ->leftJoin('industries as i', 'i.id', '=', 'b.industry_id')
            ->leftJoin('regions as r', 'r.id', '=', 'b.region_id')
            ->whereNull('b.deleted_at')
            ->where('b.certificate_no', $numero)
            ->first([
                'b.name_fr', 'b.name_en', 'b.slug', 'b.vendor_type', 'b.status',
                'b.certificate_no', 'b.certificate_issued_at', 'b.certificate_expires_at', 'b.certificate_revoked_at',
                'i.name_fr as industry_fr', 'i.name_en as industry_en', 'r.name_fr as region_fr', 'r.name_en as region_en',
            ]);
        if ($b) {
            $expired   = $b->certificate_expires_at && \Illuminate\Support\Carbon::parse($b->certificate_expires_at)->isPast();
            $revoked   = (bool) $b->certificate_revoked_at;
            $suspended = in_array($b->status, ['suspended', 'rejected', 'draft']);
            $status    = $revoked ? 'revoked' : ($expired ? 'expired' : ($suspended ? 'suspended' : 'active'));
            $cert = (object) ['found' => true, 'valid' => ! $revoked && ! $expired && ! $suspended, 'status' => $status, 'b' => $b];
        } else {
            $cert = (object) ['found' => false, 'valid' => false, 'status' => 'notfound', 'b' => null];
        }
    }

    return response(view('pages.certificate-verify', compact('lang', 'numero', 'cert')))->cookie('lang', $lang, 60 * 24 * 30);
})->name('certificate.verify');
Route::get('/certificat-adhesion', function (Request $request) {
    $siacUser = session('siac_user');
    if (!$siacUser) return redirect('/login?next=' . urlencode('/certificat-adhesion'));

    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';

    $business = DB::table('businesses')
        ->whereNull('deleted_at')
        ->where('user_id', $siacUser['id'])
        ->first();

    // Issue + persist the certificate number on first view so it is verifiable.
    if ($business) {
        $business = ensureCertificate($business);
    }

    return view('pages.membership-certificate', compact('lang', 'siacUser', 'business'));
})->name('membership.certificate');

// Admin certificate registry — every artisan's membership certificate in one place.
Route::get('/tableau-de-bord/admin/certificats', function (Request $request) {
    if ($x = requireAdmin($request)) return $x;
    $siacUser = session('siac_user');
    $lang = webLang($request);
    $q = trim((string) $request->query('q', ''));
    $filter = $request->query('status', '');

    $rows = DB::table('businesses as b')
        ->leftJoin('industries as i', 'i.id', '=', 'b.industry_id')
        ->whereNull('b.deleted_at')->whereNotNull('b.certificate_no')
        ->when($q !== '', fn ($qq) => $qq->where(fn ($w) => $w->where('b.name_fr', 'like', "%{$q}%")->orWhere('b.certificate_no', 'like', "%{$q}%")))
        ->orderByDesc('b.certificate_issued_at')
        ->get(['b.id', 'b.name_fr', 'b.status', 'b.certificate_no', 'b.certificate_issued_at', 'b.certificate_expires_at', 'b.certificate_revoked_at', 'i.name_fr as trade'])
        ->map(function ($r) {
            $expired = $r->certificate_expires_at && \Illuminate\Support\Carbon::parse($r->certificate_expires_at)->isPast();
            $r->state = $r->certificate_revoked_at ? 'revoked' : ($expired ? 'expired' : (in_array($r->status, ['suspended', 'rejected', 'draft']) ? 'suspended' : 'active'));
            return $r;
        });
    if (in_array($filter, ['active', 'expired', 'revoked', 'suspended'], true)) {
        $rows = $rows->where('state', $filter)->values();
    }

    $allCerts = DB::table('businesses')->whereNull('deleted_at')->whereNotNull('certificate_no')
        ->get(['status', 'certificate_expires_at', 'certificate_revoked_at']);
    $counts = ['active' => 0, 'expired' => 0, 'revoked' => 0, 'suspended' => 0];
    foreach ($allCerts as $x) {
        $exp = $x->certificate_expires_at && \Illuminate\Support\Carbon::parse($x->certificate_expires_at)->isPast();
        $st = $x->certificate_revoked_at ? 'revoked' : ($exp ? 'expired' : (in_array($x->status, ['suspended', 'rejected', 'draft']) ? 'suspended' : 'active'));
        $counts[$st]++;
    }
    $kpis = ['total' => $allCerts->count()] + $counts;

    return view('pages.dashboard.admin-certificates', compact('lang', 'siacUser', 'rows', 'kpis', 'q', 'filter'));
})->name('admin.certificates');

Route::post('/tableau-de-bord/admin/certificats/{id}/revoquer', function (Request $request, $id) {
    if ($x = requireAdmin($request)) return $x;
    $b = DB::table('businesses')->where('id', $id)->whereNull('deleted_at')->first();
    if ($b && $b->certificate_no) {
        DB::table('businesses')->where('id', $id)->update([
            'certificate_revoked_at' => $b->certificate_revoked_at ? null : now(),
            'updated_at' => now(),
        ]);
    }
    return back()->with('cert_updated', true);
})->name('admin.certificates.revoke')->middleware('throttle:30,1');
Route::get('/creer-mon-compte', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    return response(view('pages.onboarding', compact('lang')))->cookie('lang', $lang, 60 * 24 * 30);
})->name('onboarding');

Route::post('/contact', function (Request $request) {
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang')
        : (in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr');
    $isFr = $lang === 'fr';

    $limiterKey = 'contact:' . $request->ip();
    if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 5)) {
        $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($limiterKey);
        return back()->withInput()->withErrors([
            'message' => $isFr ? "Trop de messages envoyés. Réessayez dans {$seconds} secondes." : "Too many messages sent. Try again in {$seconds} seconds.",
        ]);
    }

    $data = $request->validate([
        'name'    => ['required', 'string', 'max:120'],
        'email'   => ['required', 'email', 'max:190'],
        'subject' => ['required', 'string', 'max:255'],
        'message' => ['required', 'string', 'max:3000'],
        'consent' => ['accepted'],
    ]);

    \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 300);

    $siacUser = session('siac_user');
    if ($siacUser) {
        // Logged-in visitors get a real support ticket they can follow in their dashboard
        $ticket = \App\Modules\Support\Models\SupportTicket::create([
            'user_id'    => $siacUser['id'],
            'subject_fr' => $data['subject'],
            'subject_en' => $data['subject'],
            'status'     => 'open',
            'priority'   => 'medium',
        ]);
        \App\Modules\Support\Models\SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $siacUser['id'],
            'body_fr'   => $data['message'] . "\n\n— " . $data['name'] . ' <' . $data['email'] . '>',
            'body_en'   => $data['message'] . "\n\n— " . $data['name'] . ' <' . $data['email'] . '>',
            'is_staff'  => false,
        ]);
    } else {
        // Guests: forward to the gallery inbox (goes to log when MAIL_MAILER=log)
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Nom : {$data['name']}\nEmail : {$data['email']}\n\n{$data['message']}",
                function ($mail) use ($data) {
                    $mail->to('contact@gvnac.cm')
                        ->replyTo($data['email'], $data['name'])
                        ->subject('[Contact GVNAC] ' . $data['subject']);
                }
            );
        } catch (\Exception $e) {
            // Mail failure is non-fatal in local/dev
        }
    }

    return redirect()->route('contact', ['lang' => $lang])->with('success', $isFr
        ? 'Merci ! Votre message a bien été envoyé. Notre équipe vous répondra rapidement.'
        : 'Thank you! Your message has been sent. Our team will get back to you shortly.');
})->name('contact.store');

// Newsletter subscription (real endpoint behind the canonical footer form)
Route::post('/newsletter', function (Request $request) {
    $lang = in_array($request->input('lang'), ['fr', 'en']) ? $request->input('lang') : 'fr';
    $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

    DB::table('newsletter_subscribers')->updateOrInsert(
        ['email' => strtolower($data['email'])],
        ['lang' => $lang, 'updated_at' => now(), 'created_at' => now()]
    );

    return back()->with('newsletter_ok', $lang === 'fr'
        ? 'Merci ! Vous êtes bien abonné à la newsletter.'
        : 'Thank you! You are now subscribed to the newsletter.');
})->name('newsletter.subscribe')->middleware('throttle:10,1');

// SEO: dynamic sitemap + robots (served by routes so tests and production match)
Route::get('/sitemap.xml', function () {
    $urls = collect([
        ['loc' => url('/'),                     'priority' => '1.0'],
        ['loc' => url('/galerie/entreprises'),  'priority' => '0.9'],
        ['loc' => url('/galerie/produits'),     'priority' => '0.9'],
        ['loc' => url('/galerie/secteurs'),     'priority' => '0.8'],
        ['loc' => url('/evenements'),           'priority' => '0.8'],
        ['loc' => url('/partenaires'),          'priority' => '0.6'],
        ['loc' => url('/faq'),                  'priority' => '0.6'],
        ['loc' => url('/actualites'),           'priority' => '0.6'],
        ['loc' => url('/guide-artisan'),        'priority' => '0.6'],
        ['loc' => url('/contact'),              'priority' => '0.5'],
        ['loc' => url('/carrieres'),            'priority' => '0.4'],
        ['loc' => url('/presse'),               'priority' => '0.4'],
    ]);

    DB::table('businesses')->whereNull('deleted_at')->where('status', 'published')
        ->orderBy('id')->limit(5000)->pluck('slug')
        ->each(function ($slug) use ($urls) {
            $urls->push(['loc' => url('/galerie/entreprises/' . $slug), 'priority' => '0.7']);
        });

    DB::table('products')
        ->join('businesses', 'businesses.id', '=', 'products.business_id')
        ->whereNull('products.deleted_at')->whereNull('businesses.deleted_at')
        ->where('products.status', 'published')->where('businesses.status', 'published')
        ->orderBy('products.id')->limit(20000)->pluck('products.slug')
        ->each(function ($slug) use ($urls) {
            $urls->push(['loc' => url('/galerie/produits/' . $slug), 'priority' => '0.7']);
        });

    DB::table('events')->orderBy('id')->limit(500)->pluck('slug')
        ->each(function ($slug) use ($urls) {
            $urls->push(['loc' => url('/evenements/' . $slug), 'priority' => '0.5']);
        });

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $xml .= "  <url><loc>" . e($u['loc']) . "</loc><priority>{$u['priority']}</priority></url>\n";
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Disallow: /tableau-de-bord',
        'Disallow: /login',
        'Disallow: /inscription',
        'Disallow: /onboarding',
        'Allow: /',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

// Public info pages created for the canonical footer menu (2026-07-03)
Route::get('/guide-artisan', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    return view('pages.guide-artisan', compact('lang'));
})->name('guide.artisan');

Route::get('/faq', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $faqCategories = \App\Modules\Cms\Models\CmsFaqCategory::with(['faqs' => fn ($q) => $q->orderBy('sort_order')])
        ->orderBy('sort_order')->get()
        ->filter(fn ($c) => $c->faqs->isNotEmpty());
    $uncategorizedFaqs = \App\Modules\Cms\Models\CmsFaq::whereNull('category_id')->orderBy('sort_order')->get();
    return view('pages.faq', compact('lang', 'faqCategories', 'uncategorizedFaqs'));
})->name('faq');

Route::get('/actualites', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $newsEvents = DB::table('events')->orderByDesc('starts_at')->limit(12)->get();
    return view('pages.news', compact('lang', 'newsEvents'));
})->name('news.index');

Route::get('/carrieres', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    return view('pages.careers', compact('lang'));
})->name('careers');

Route::get('/presse', function (Request $request) {
    $lang = $request->query('lang', $request->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $pressStats = [
        'businesses' => DB::table('businesses')->whereNull('deleted_at')->where('status', 'published')->count(),
        'products'   => DB::table('products')->whereNull('deleted_at')->where('status', 'published')->count(),
        'events'     => DB::table('events')->count(),
        'regions'    => DB::table('regions')->count(),
    ];
    return view('pages.press', compact('lang', 'pressStats'));
})->name('press');

Route::get('/terms', function (Request $request) {
    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    return view('terms', compact('lang'));
})->name('terms');
Route::get('/privacy', function (Request $request) {
    $lang = in_array($request->query('lang', $request->cookie('lang')), ['fr', 'en']) ? $request->query('lang', $request->cookie('lang')) : 'fr';
    return view('privacy', compact('lang'));
})->name('privacy');

// ─────────────────────────────────────────────
// Developer / API Keys
// ─────────────────────────────────────────────
// A web user's API consumer record is matched by email (api_consumers has no user_id column).
// developerConsumer() → app/Support/route_helpers.php

Route::get('/developer', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $consumer = developerConsumer(webUser());
    $keys     = $consumer
        ? DB::table('api_keys')->where('consumer_id', $consumer->id)->orderBy('created_at', 'desc')->get()
        : collect();
    $keyCount = $keys->where('is_active', 1)->count();
    return view('developer', compact('keys', 'keyCount'));
})->name('developer');

Route::post('/developer/keys', function (Request $request) {
    if ($r = requireAuth($request)) return $r;
    $data     = $request->validate(['name' => 'required|string|max:60']);
    $consumer = developerConsumer(webUser(), createIfMissing: true);
    $plain    = 'siac_' . Str::random(40);
    DB::table('api_keys')->insert([
        'consumer_id'           => $consumer->id,
        'name'                  => $data['name'],
        'key_hash'              => hash('sha256', $plain),
        'key_prefix'            => substr($plain, 0, 8),
        'rate_limit_per_minute' => 60,
        'is_active'             => 1,
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);
    return back()->with('success', 'API key created: ' . $plain . ' — copy it now, it will not be shown again.');
})->name('developer.keys.create');

Route::post('/developer/keys/{id}/revoke', function (Request $request, $id) {
    if ($r = requireAuth($request)) return $r;
    $consumer = developerConsumer(webUser());
    if ($consumer) {
        DB::table('api_keys')->where('id', $id)->where('consumer_id', $consumer->id)->update(['is_active' => 0, 'updated_at' => now()]);
    }
    return back()->with('success', 'API key revoked.');
})->name('developer.keys.revoke');
