<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationWebController extends Controller
{
    public function __construct(private readonly VerificationService $service) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    public function show(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }

        $business = Business::where('user_id', $siacUser['id'])->firstOrFail();
        $applications = $business->verificationApplications()->with('documents')->get();

        return view('pages.dashboard.verification', compact('lang', 'business', 'applications'));
    }

    public function apply(Request $request): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }

        $business = Business::where('user_id', $siacUser['id'])->firstOrFail();

        $request->validate([
            'tier_requested'    => ['required', 'in:basic,verified,certified'],
            'documents.*.file'  => ['nullable', 'file', 'max:8192', 'mimes:pdf,jpg,jpeg,png'],
            'documents.*.type'  => ['nullable', 'in:rccm,niu,anor,cnps,cmf,id_director,financials,product_cert,other'],
        ]);

        $documents = [];
        foreach ($request->input('documents', []) as $i => $row) {
            $file = $request->file("documents.{$i}.file");
            if ($file && ! empty($row['type'])) {
                $documents[] = ['file' => $file, 'document_type' => $row['type']];
            }
        }

        if (empty($documents)) {
            return back()->withErrors(['documents' => $lang === 'fr' ? 'Veuillez téléverser au moins un document.' : 'Please upload at least one document.']);
        }

        $this->service->apply($business, $request->input('tier_requested'), $documents);

        return redirect()->route('verification.show')
            ->with('success', $lang === 'fr' ? 'Votre demande de vérification a été soumise.' : 'Your verification application has been submitted.');
    }
}
