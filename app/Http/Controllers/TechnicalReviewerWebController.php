<?php

namespace App\Http\Controllers;

use App\Modules\Admin\Models\AuditLog;
use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\BusinessCertification;
use App\Modules\Businesses\Models\VerificationApplication;
use App\Modules\Businesses\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TechnicalReviewerWebController extends Controller
{
    public function __construct(private readonly VerificationService $verificationService) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireTechnicalReviewer(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || ($siacUser['role'] ?? null) !== 'technical_reviewer') {
            return redirect('/login');
        }
        return $siacUser;
    }

    public function dashboard(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $applications = VerificationApplication::with(['business', 'documents'])
            ->whereIn('status', ['submitted', 'under_review'])
            ->oldest('submitted_at')
            ->get();

        $certifications = BusinessCertification::with(['business', 'certification'])
            ->pending()
            ->oldest()
            ->get();

        return view('pages.dashboard.technical-reviewer', compact('lang', 'siacUser', 'applications', 'certifications'));
    }

    public function approveVerification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $application = VerificationApplication::findOrFail($id);
        $reviewer = User::findOrFail($siacUser['id']);
        $this->verificationService->approve($application, $reviewer, $request->input('notes'));
        AuditLog::record($siacUser['id'], 'verification.approved', 'verification_application', $application->id, null, ['tier' => $application->tier_requested]);

        return back()->with('success', $lang === 'fr' ? 'Vérification approuvée.' : 'Verification approved.');
    }

    public function rejectVerification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);
        $application = VerificationApplication::findOrFail($id);
        $reviewer = User::findOrFail($siacUser['id']);
        $this->verificationService->reject($application, $reviewer, $data['notes']);
        AuditLog::record($siacUser['id'], 'verification.rejected', 'verification_application', $application->id, null, ['reason' => $data['notes']]);

        return back()->with('success', $lang === 'fr' ? 'Vérification rejetée.' : 'Verification rejected.');
    }

    public function approveCertification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $cert = BusinessCertification::with('business')->findOrFail($id);
        $cert->update(['status' => 'verified']);
        AuditLog::record($siacUser['id'], 'certification.verified', 'business_certification', $cert->id, null, ['business' => $cert->business->name_fr]);

        \App\Modules\Notifications\Models\UserNotification::notify(
            $cert->business->user_id,
            'certification_verified',
            $lang === 'fr' ? 'Certification vérifiée' : 'Certification verified',
            $lang === 'fr' ? 'Votre certification a été vérifiée par le département technique.' : 'Your certification has been verified by the technical department.',
            route('business.edit')
        );

        return back()->with('success', $lang === 'fr' ? 'Certification vérifiée.' : 'Certification verified.');
    }

    public function rejectCertification(Request $request, int $id): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);
        $cert = BusinessCertification::with('business')->findOrFail($id);
        $cert->update(['status' => 'rejected']);
        AuditLog::record($siacUser['id'], 'certification.rejected', 'business_certification', $cert->id, null, ['business' => $cert->business->name_fr, 'reason' => $data['notes']]);

        \App\Modules\Notifications\Models\UserNotification::notify(
            $cert->business->user_id,
            'certification_rejected',
            $lang === 'fr' ? 'Certification rejetée' : 'Certification rejected',
            $data['notes'],
            route('business.edit')
        );

        return back()->with('success', $lang === 'fr' ? 'Certification rejetée.' : 'Certification rejected.');
    }

    public function history(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireTechnicalReviewer($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $decisions = AuditLog::where('user_id', $siacUser['id'])
            ->whereIn('action', ['verification.approved', 'verification.rejected', 'certification.verified', 'certification.rejected'])
            ->latest()
            ->paginate(20);

        return view('pages.dashboard.technical-history', compact('lang', 'siacUser', 'decisions'));
    }
}
