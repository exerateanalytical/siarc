<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Services\VerificationService;
use App\Modules\Cms\Models\Partner;
use App\Modules\Events\Models\Event;
use App\Modules\Messaging\Models\Conversation;
use App\Modules\Messaging\Models\Message;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function __construct(private readonly VerificationService $verificationService) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireAdmin(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || empty($siacUser['is_admin'])) {
            return redirect('/login');
        }
        return $siacUser;
    }

    public function businesses(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $query = Business::with(['industry', 'user'])->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name_fr', 'like', $search)->orWhere('name_en', 'like', $search));
        }
        $businesses = $query->paginate(20)->withQueryString();

        return view('pages.dashboard.admin-businesses', compact('lang', 'businesses'));
    }

    public function businessDetail(Request $request, int $id)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $business = Business::with([
            'industry', 'region', 'city', 'user',
            'products' => fn ($q) => $q->latest(),
            'certifications.certification',
            'reviews.reviewer',
            'verificationApplications',
        ])->findOrFail($id);

        $auditEntries = \App\Modules\Admin\Models\AuditLog::where('entity_type', 'business')
            ->where('entity_id', $business->id)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.dashboard.admin-business-detail', compact('lang', 'business', 'auditEntries'));
    }

    public function updateBusinessStatus(Request $request, int $id): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'in:draft,published,suspended,rejected']]);
        $business = Business::findOrFail($id);
        $oldStatus = $business->status;
        $business->update(['status' => $data['status']]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'business.status_changed', 'business', $business->id, ['status' => $oldStatus], ['status' => $data['status']]);

        if (in_array($data['status'], ['suspended', 'rejected'])) {
            \App\Modules\Notifications\Models\UserNotification::notify(
                $business->user_id,
                'business_' . $data['status'],
                $data['status'] === 'suspended' ? 'Entreprise suspendue' : 'Entreprise rejetée',
                'Votre entreprise "' . $business->name_fr . '" a été ' . ($data['status'] === 'suspended' ? 'suspendue' : 'rejetée') . '.',
                route('business.edit')
            );
        }

        return back()->with('success', $this->lang($request) === 'fr' ? 'Statut mis à jour.' : 'Status updated.');
    }

    public function verifications(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $applications = VerificationApplication::with(['business', 'documents'])
            ->whereIn('status', ['submitted', 'under_review'])
            ->oldest('submitted_at')
            ->paginate(20);

        return view('pages.dashboard.admin-verifications', compact('lang', 'applications'));
    }

    public function approveVerification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $application = VerificationApplication::findOrFail($id);
        $adminUser = User::findOrFail($admin['id']);
        $this->verificationService->approve($application, $adminUser, $request->input('notes'));
        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'verification.approved', 'verification_application', $application->id, null, ['tier' => $application->tier_requested]);

        return back()->with('success', $lang === 'fr' ? 'Vérification approuvée.' : 'Verification approved.');
    }

    public function rejectVerification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);
        $application = VerificationApplication::findOrFail($id);
        $adminUser = User::findOrFail($admin['id']);
        $this->verificationService->reject($application, $adminUser, $data['notes']);
        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'verification.rejected', 'verification_application', $application->id, null, ['reason' => $data['notes']]);

        return back()->with('success', $lang === 'fr' ? 'Vérification rejetée.' : 'Verification rejected.');
    }

    public function users(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $query = User::with('roles')->latest();
        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('email', 'like', $search));
        }
        $users = $query->paginate(25)->withQueryString();
        $regions = \App\Modules\Taxonomy\Models\Region::orderBy('name_fr')->get();

        return view('pages.dashboard.admin-users', compact('lang', 'users', 'regions'));
    }

    public function userDetail(Request $request, string $id)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $user = User::with(['roles', 'business', 'assignedRegion'])->findOrFail($id);

        $auditAsActor = \App\Modules\Admin\Models\AuditLog::where('user_id', $user->id)->latest()->limit(10)->get();
        $conversationCount = \App\Modules\Messaging\Models\Conversation::where('buyer_id', $user->id)->count();
        $reviewCount = \App\Modules\Businesses\Models\BusinessReview::where('reviewer_id', $user->id)->count();

        return view('pages.dashboard.admin-user-detail', compact('lang', 'user', 'auditAsActor', 'conversationCount', 'reviewCount'));
    }

    public function updateUserRole(Request $request, string $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $assignableRoles = ['buyer', 'business_owner', 'regional_rep', 'ministry', 'technical_reviewer', 'moderator', 'admin'];
        $data = $request->validate([
            'role'      => ['required', 'in:' . implode(',', $assignableRoles)],
            'region_id' => ['nullable', 'exists:regions,id'],
        ]);

        if ($id === $admin['id']) {
            return back()->withErrors(['role' => $lang === 'fr' ? 'Vous ne pouvez pas modifier votre propre rôle.' : 'You cannot change your own role.']);
        }

        $user = User::findOrFail($id);
        $oldRole = $user->roles->first()?->name ?? 'buyer';

        // "buyer" is the platform default (no explicit Spatie role) — every other
        // selection replaces whatever role the user currently has.
        if ($data['role'] === 'buyer') {
            $user->syncRoles([]);
        } else {
            $user->syncRoles([$data['role']]);
        }

        $user->update([
            'assigned_region_id' => $data['role'] === 'regional_rep' ? ($data['region_id'] ?? null) : null,
        ]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'user.role_changed', 'user', null, ['role' => $oldRole], ['role' => $data['role'], 'target_user' => $user->email]);

        return back()->with('success', $lang === 'fr' ? 'Rôle mis à jour.' : 'Role updated.');
    }

    public function updateUserStatus(Request $request, string $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);

        if ($id === $admin['id']) {
            return back()->withErrors(['status' => $lang === 'fr' ? 'Vous ne pouvez pas modifier votre propre statut.' : 'You cannot change your own status.']);
        }

        $user = User::findOrFail($id);
        $user->update(['status' => $data['status']]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'user.status_changed', 'user', null, null, ['status' => $data['status'], 'target_user' => $user->email]);

        return back()->with('success', $lang === 'fr' ? 'Statut mis à jour.' : 'Status updated.');
    }

    public function partners(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');
        $type = $request->query('type', '');
        $country = $request->query('country', '');

        $query = Partner::query();
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name_fr', 'like', "%{$q}%")
                    ->orWhere('sector_fr', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }
        if ($status !== '') $query->where('status', $status);
        if ($type !== '') $query->where('partner_type', $type);
        if ($country !== '') $query->where('country', $country);

        $partners = $query->orderByDesc('start_date')->paginate(8)->withQueryString();

        // KPI cards — computed from real rows, never hardcoded.
        $all = Partner::query();
        $partnerKpis = [
            'active' => (clone $all)->where('status', 'active')->count(),
            'pending' => (clone $all)->where('status', 'pending')->count(),
            'international' => (clone $all)->where('country', '!=', 'Cameroun')->count(),
            'national' => (clone $all)->where('country', 'Cameroun')->count(),
            'premium' => (clone $all)->where('partnership_level', 'Premium')->count(),
        ];
        $partnerTotal = max(1, (clone $all)->count());
        $partnerKpis['international_pct'] = round($partnerKpis['international'] / $partnerTotal * 100, 1);
        $partnerKpis['national_pct'] = round($partnerKpis['national'] / $partnerTotal * 100, 1);

        $byType = (clone $all)->select('partner_type', \DB::raw('count(*) as c'))
            ->groupBy('partner_type')->orderByDesc('c')->pluck('c', 'partner_type');
        $byTypePct = $byType->map(fn ($c) => round($c / $partnerTotal * 100, 1));

        $bySector = (clone $all)->select('sector_fr', \DB::raw('count(*) as c'))
            ->groupBy('sector_fr')->orderByDesc('c')->limit(5)->pluck('c', 'sector_fr');
        $bySectorPct = $bySector->map(fn ($c) => round($c / $partnerTotal * 100, 1));

        $partnerCountries = Partner::query()->distinct()->orderBy('country')->pluck('country');
        $partnerTypes = Partner::query()->distinct()->orderBy('partner_type')->pluck('partner_type');

        return view('pages.dashboard.admin-partners', compact(
            'lang', 'partners', 'partnerKpis', 'byType', 'byTypePct', 'bySector', 'bySectorPct',
            'partnerCountries', 'partnerTypes', 'q', 'status', 'type', 'country'
        ));
    }

    public function storePartner(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $this->validatePartner($request);
        $partner = Partner::create($data);
        $this->handlePartnerLogo($request, $partner);

        return back()->with('success', $lang === 'fr' ? 'Partenaire ajouté.' : 'Partner added.');
    }

    public function updatePartner(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $partner = Partner::findOrFail($id);
        $partner->update($this->validatePartner($request));
        $this->handlePartnerLogo($request, $partner);

        return back()->with('success', $lang === 'fr' ? 'Partenaire mis à jour.' : 'Partner updated.');
    }

    public function destroyPartner(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        Partner::findOrFail($id)->delete();

        return back()->with('success', $lang === 'fr' ? 'Partenaire supprimé.' : 'Partner removed.');
    }

    private function validatePartner(Request $request): array
    {
        return $request->validate([
            'name_fr'         => ['required', 'string', 'max:255'],
            'name_en'         => ['nullable', 'string', 'max:255'],
            'website'         => ['nullable', 'url', 'max:255'],
            'tier'            => ['required', 'in:institutional,platinum,gold,silver,partner'],
            'description_fr'  => ['nullable', 'string', 'max:1000'],
            'description_en'  => ['nullable', 'string', 'max:1000'],
            'sort_order'      => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'       => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function handlePartnerLogo(Request $request, Partner $partner): void
    {
        if (! $request->hasFile('logo')) {
            return;
        }
        $path = $request->file('logo')->store('partners', config('filesystems.default'));
        $partner->update(['logo' => $path]);
    }

    public function reports(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $stats = [
            'businesses'      => Business::count(),
            'published'       => Business::where('status', 'published')->count(),
            'products'        => Product::count(),
            'published_products' => Product::where('status', 'published')->count(),
            'users'           => User::count(),
            'conversations'   => Conversation::count(),
            'messages'        => Message::count(),
            'reviews'         => \App\Modules\Businesses\Models\BusinessReview::count(),
            'avg_rating'      => round((float) \App\Modules\Businesses\Models\BusinessReview::avg('rating'), 1),
        ];

        $verificationFunnel = VerificationApplication::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $topRegions = Business::where('status', 'published')
            ->join('regions', 'regions.id', '=', 'businesses.region_id')
            ->selectRaw('regions.name_fr, regions.name_en, count(*) as total')
            ->groupBy('regions.id', 'regions.name_fr', 'regions.name_en')
            ->orderByDesc('total')->limit(5)->get();

        $topIndustries = Business::where('status', 'published')
            ->join('industries', 'industries.id', '=', 'businesses.industry_id')
            ->selectRaw('industries.name_fr, industries.name_en, count(*) as total')
            ->groupBy('industries.id', 'industries.name_fr', 'industries.name_en')
            ->orderByDesc('total')->limit(5)->get();

        $topProducts = Product::where('status', 'published')
            ->with('business.region')
            ->orderByDesc('views_count')
            ->limit(5)->get();

        // Group in PHP (not SQL DATE_FORMAT) so this works on MySQL and SQLite alike.
        $registrationsOverTime = User::where('created_at', '>=', now()->subMonths(6))
            ->pluck('created_at')
            ->groupBy(fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('Y-m'))
            ->map->count();

        // Real revenue backbone: active subscription payments + paid invoices.
        $repKpis = [
            'revenue'    => (float) \DB::table('business_subscriptions')->where('status', 'active')->sum('amount')
                           + (float) \DB::table('invoices')->where('status', 'paid')->sum('total'),
            'orders'     => \DB::table('purchase_orders')->count(),
            'artisans'   => $stats['published'],
            'views'      => (int) Product::sum('views_count') + (int) Business::sum('views_count'),
            'avg_order'  => \DB::table('purchase_orders')->count() > 0 ? (float) \DB::table('purchase_orders')->avg('total') : null,
            'conversion' => \DB::table('quote_requests')->count() > 0
                ? round(\DB::table('purchase_orders')->count() / \DB::table('quote_requests')->count() * 100, 1) : null,
        ];

        // Monthly revenue trend (6 months) from real subscription start dates.
        $revRows = \DB::table('business_subscriptions')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->select('created_at', 'amount')->get()
            ->groupBy(fn ($r) => \Illuminate\Support\Carbon::parse($r->created_at)->format('Y-m'))
            ->map(fn ($rows) => (float) $rows->sum('amount'));
        $repRevenueSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $repRevenueSeries[] = ['label' => $m->translatedFormat('M'), 'value' => (float) ($revRows[$m->format('Y-m')] ?? 0)];
        }

        // Real business breakdown by craft category (count-based — no per-category
        // revenue is tracked, so this is honestly a distribution, not a revenue split).
        $repCategoryDist = Business::where('status', 'published')
            ->join('industries', 'industries.id', '=', 'businesses.industry_id')
            ->selectRaw('industries.name_fr, count(*) as total')
            ->groupBy('industries.id', 'industries.name_fr')
            ->orderByDesc('total')->limit(6)->get();

        return view('pages.dashboard.admin-reports', compact(
            'lang', 'stats', 'verificationFunnel', 'topRegions', 'topIndustries', 'topProducts',
            'registrationsOverTime', 'repKpis', 'repRevenueSeries', 'repCategoryDist'
        ));
    }

    public function events(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $events = Event::withCount(['exhibitors', 'attendees'])->orderByDesc('starts_at')->get();

        return view('pages.dashboard.admin-events', compact('lang', 'events'));
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $this->validateEvent($request);
        $data['created_by'] = $admin['id'];
        Event::create($data);

        return redirect()->route('admin.events')->with('success', $lang === 'fr' ? 'Événement créé.' : 'Event created.');
    }

    public function updateEvent(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        Event::findOrFail($id)->update($this->validateEvent($request));

        return redirect()->route('admin.events')->with('success', $lang === 'fr' ? 'Événement mis à jour.' : 'Event updated.');
    }

    public function destroyEvent(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        Event::findOrFail($id)->delete();

        return back()->with('success', $lang === 'fr' ? 'Événement supprimé.' : 'Event removed.');
    }

    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'name_fr'         => ['required', 'string', 'max:255'],
            'name_en'         => ['nullable', 'string', 'max:255'],
            'description_fr'  => ['nullable', 'string', 'max:5000'],
            'description_en'  => ['nullable', 'string', 'max:5000'],
            'location_fr'     => ['nullable', 'string', 'max:255'],
            'location_en'     => ['nullable', 'string', 'max:255'],
            'starts_at'       => ['required', 'date'],
            'ends_at'         => ['nullable', 'date', 'after_or_equal:starts_at'],
            'industry_id'     => ['nullable', 'exists:industries,id'],
            'is_published'    => ['nullable', 'boolean'],
        ]) + ['is_published' => $request->boolean('is_published')];
    }

    public function auditLog(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $query = \App\Modules\Admin\Models\AuditLog::with('user')->latest();
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        $logs = $query->paginate(30)->withQueryString();

        return view('pages.dashboard.admin-audit-log', compact('lang', 'logs'));
    }

    public function moderation(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $tab = $request->query('tab') === 'reviews' ? 'reviews' : 'reports';

        $reports = \App\Modules\Products\Models\ProductReport::with(['product.business', 'reporter'])
            ->where('status', 'open')
            ->latest()
            ->paginate(20, ['*'], 'reports_page')
            ->withQueryString();

        $reviews = \App\Modules\Businesses\Models\BusinessReview::with(['business', 'reviewer'])
            ->latest()
            ->paginate(20, ['*'], 'reviews_page')
            ->withQueryString();

        return view('pages.dashboard.admin-moderation', compact('lang', 'tab', 'reports', 'reviews'));
    }

    public function resolveReport(Request $request, int $id): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'in:resolved,dismissed']]);
        $report = \App\Modules\Products\Models\ProductReport::findOrFail($id);
        $oldStatus = $report->status;
        $report->update(['status' => $data['status']]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'product_report.' . $data['status'], 'product_report', $report->id, ['status' => $oldStatus], ['status' => $data['status']]);

        return back()->with('success', $this->lang($request) === 'fr' ? 'Signalement traité.' : 'Report handled.');
    }

    public function deleteReview(Request $request, int $id): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $review = \App\Modules\Businesses\Models\BusinessReview::findOrFail($id);
        $snapshot = ['business_id' => $review->business_id, 'reviewer_id' => $review->reviewer_id, 'rating' => $review->rating, 'title' => $review->title];
        $review->delete();

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'business_review.deleted', 'business_review', $id, $snapshot, null);

        return back()->with('success', $this->lang($request) === 'fr' ? 'Avis supprimé.' : 'Review deleted.');
    }

    public function apiConsumers(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $query = \App\Modules\ApiProduct\Models\ApiConsumer::withCount([
            'keys',
            'keys as active_keys_count' => fn ($q) => $q->where('is_active', true),
        ]);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $consumers = $query
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'suspended')")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $pendingCount = \App\Modules\ApiProduct\Models\ApiConsumer::where('status', 'pending')->count();

        return view('pages.dashboard.admin-api-consumers', compact('lang', 'consumers', 'pendingCount'));
    }

    public function updateApiConsumerStatus(Request $request, int $id): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'in:approved,suspended,pending']]);
        $consumer = \App\Modules\ApiProduct\Models\ApiConsumer::findOrFail($id);
        $oldStatus = $consumer->status;

        $consumer->update([
            'status'      => $data['status'],
            'approved_at' => $data['status'] === 'approved' ? ($consumer->approved_at ?? now()) : $consumer->approved_at,
        ]);

        \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'api_consumer.status_changed', 'api_consumer', $consumer->id, ['status' => $oldStatus], ['status' => $data['status']]);

        return back()->with('success', $this->lang($request) === 'fr' ? 'Statut du consommateur API mis à jour.' : 'API consumer status updated.');
    }

    // ─────────────────────────────────────────
    // Platform settings & integrations
    // ─────────────────────────────────────────

    public function settings(Request $request)
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $all = \App\Modules\Admin\Services\SystemSettings::all();

        // Editable platform settings, grouped; integrations rendered separately
        $groups = collect($all)
            ->reject(fn ($row) => $row['group'] === 'integrations')
            ->map(fn ($row, $key) => $row + ['key' => $key])
            ->groupBy('group');

        $twilio = \App\Modules\Auth\Services\Otp\TwilioWhatsAppOtpSender::credentials();
        $mask = fn (?string $v) => $v ? '••••••••' . substr($v, -4) : null;

        return view('pages.dashboard.admin-settings', [
            'lang'     => $lang,
            'groups'   => $groups,
            'twilio'   => [
                'sid_masked'   => $mask($twilio['sid']),
                'token_masked' => $mask($twilio['token']),
                'from'         => $twilio['from'],
                'configured'   => (bool) ($twilio['sid'] && $twilio['token'] && $twilio['from']),
                'from_env'     => ! \App\Modules\Admin\Services\SystemSettings::get('twilio.sid') && config('services.twilio.sid'),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $posted = (array) $request->input('settings', []);
        $all    = \App\Modules\Admin\Services\SystemSettings::all();

        $old = $new = [];
        foreach ($posted as $key => $value) {
            $row = $all[$key] ?? null;
            if (! $row || $row['group'] === 'integrations') continue; // only known, non-secret settings

            $value = is_string($value) ? trim($value) : $value;
            if ($row['type'] === 'boolean') {
                $value = in_array($value, ['1', 'true', 'on'], true) ? 'true' : 'false';
            } elseif ($row['type'] === 'integer') {
                if (! is_numeric($value)) {
                    return back()->withErrors(['settings' => ($lang === 'fr' ? 'Valeur numérique requise pour ' : 'Numeric value required for ') . $key]);
                }
                $value = (string) (int) $value;
            }

            $current = $row['type'] === 'boolean' ? ($row['value'] ? 'true' : 'false') : (string) ($row['value'] ?? '');
            if ($current === (string) $value) continue;

            $old[$key] = $row['value'];
            $new[$key] = $value;
            \App\Modules\Admin\Services\SystemSettings::set($key, (string) $value, $row['type'], $row['group'], false, $admin['id']);
        }

        if ($new) {
            \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'settings.updated', 'system_settings', null, $old, $new);
        }

        return back()->with('success', $lang === 'fr'
            ? (count($new) . ' paramètre(s) mis à jour.')
            : (count($new) . ' setting(s) updated.'));
    }

    public function saveTwilioSettings(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate([
            'sid'           => ['nullable', 'string', 'max:100'],
            'token'         => ['nullable', 'string', 'max:100'],
            'whatsapp_from' => ['nullable', 'string', 'max:30', 'regex:/^\+[0-9]{6,15}$/'],
        ], [
            'whatsapp_from.regex' => $lang === 'fr' ? 'Numéro au format international requis (ex. +14155238886).' : 'International format required (e.g. +14155238886).',
        ]);

        // Blank fields keep their current value (the form shows masked secrets)
        $changed = [];
        $set = function (string $key, ?string $value, bool $secret) use ($admin, &$changed) {
            if ($value === null || $value === '') return;
            \App\Modules\Admin\Services\SystemSettings::set($key, $value, 'string', 'integrations', $secret, $admin['id']);
            $changed[] = $key;
        };
        $set('twilio.sid', $data['sid'] ?? null, true);
        $set('twilio.token', $data['token'] ?? null, true);
        $set('twilio.whatsapp_from', $data['whatsapp_from'] ?? null, false);

        if ($changed) {
            // Never write credential values to the audit log — only which keys changed
            \App\Modules\Admin\Models\AuditLog::record($admin['id'], 'settings.twilio_updated', 'system_settings', null, null, ['keys' => $changed]);
        }

        return back()->with('success', $lang === 'fr' ? 'Identifiants Twilio enregistrés.' : 'Twilio credentials saved.');
    }

    public function testTwilio(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate([
            'test_phone' => ['required', 'string', 'regex:/^\+[0-9]{6,15}$/'],
        ], [
            'test_phone.regex' => $lang === 'fr' ? 'Numéro au format international requis.' : 'International format required.',
        ]);

        $creds = \App\Modules\Auth\Services\Otp\TwilioWhatsAppOtpSender::credentials();
        if (! $creds['sid'] || ! $creds['token'] || ! $creds['from']) {
            return back()->withErrors(['twilio' => $lang === 'fr'
                ? 'Twilio n\'est pas entièrement configuré (SID, token et numéro expéditeur requis).'
                : 'Twilio is not fully configured (SID, token and sender number required).']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->withBasicAuth($creds['sid'], $creds['token'])
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$creds['sid']}/Messages.json", [
                    'From' => 'whatsapp:' . $creds['from'],
                    'To'   => 'whatsapp:' . $data['test_phone'],
                    'Body' => $lang === 'fr'
                        ? 'Message de test — Galerie virtuelle de l\'artisanat du Cameroun.'
                        : 'Test message — Virtual gallery of Cameroonian crafts.',
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->withErrors(['twilio' => ($lang === 'fr' ? 'Connexion à Twilio impossible : ' : 'Could not reach Twilio: ') . $e->getMessage()]);
        }

        if ($response->successful()) {
            return back()->with('success', $lang === 'fr'
                ? 'Message de test envoyé à ' . $data['test_phone'] . '.'
                : 'Test message sent to ' . $data['test_phone'] . '.');
        }

        return back()->withErrors(['twilio' => 'Twilio: ' . ($response->json('message') ?? ('HTTP ' . $response->status()))]);
    }
}
