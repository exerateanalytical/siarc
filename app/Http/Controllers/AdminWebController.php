<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Services\VerificationService;
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

    public function updateBusinessStatus(Request $request, int $id): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;

        $data = $request->validate(['status' => ['required', 'in:draft,published,suspended,rejected']]);
        $business = Business::findOrFail($id);
        $business->update(['status' => $data['status']]);

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

        return view('pages.dashboard.admin-users', compact('lang', 'users'));
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

        return back()->with('success', $lang === 'fr' ? 'Statut mis à jour.' : 'Status updated.');
    }
}
