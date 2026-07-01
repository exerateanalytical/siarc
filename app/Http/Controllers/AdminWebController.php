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

        $partners = Partner::orderBy('tier')->orderBy('sort_order')->get();

        return view('pages.dashboard.admin-partners', compact('lang', 'partners'));
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
            ->with('business')
            ->orderByDesc('views_count')
            ->limit(5)->get();

        $registrationsOverTime = User::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')->orderBy('month')->get();

        return view('pages.dashboard.admin-reports', compact(
            'lang', 'stats', 'verificationFunnel', 'topRegions', 'topIndustries', 'topProducts', 'registrationsOverTime'
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
}
