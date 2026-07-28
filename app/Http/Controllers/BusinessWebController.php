<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Services\BusinessService;
use App\Modules\Businesses\Services\ImageUploadService;
use App\Modules\Taxonomy\Models\City;
use App\Modules\Taxonomy\Models\Industry;
use App\Modules\Taxonomy\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusinessWebController extends Controller
{
    public function __construct(
        private readonly BusinessService $service,
        private readonly ImageUploadService $imageUpload,
    ) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    /**
     * The trades an artisan can pick, grouped by their corps de métier.
     *
     * This field used to render every active row — all 424, across four
     * taxonomy levels — as one flat, unsorted list, so "Artisanat de
     * Production" sat between individual trades with nothing to distinguish
     * them. It is the first required field on the first form a new artisan
     * meets, and it was unusable.
     *
     * Only the leaf métiers are offered, because that is what someone
     * identifies as: "Céramiste (Potier)", not "Artisanat de Production". They
     * are grouped under their corps de métier so the native select stays
     * browsable — the browse pages roll counts up the tree, so a leaf choice
     * still surfaces under its sector.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection>
     */
    private function tradesGroupedByCorps()
    {
        $rows = Industry::where('is_active', true)
            ->whereIn('level', [3, 4])
            ->orderBy('sort_order')
            ->get(['id', 'parent_id', 'level', 'name_fr', 'name_en']);

        $corps = $rows->where('level', 3)->keyBy('id');

        return $rows->where('level', 4)
            ->groupBy(fn ($trade) => $corps[$trade->parent_id]->name_fr ?? '—')
            ->sortKeys();
    }

    private function requireUser(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }
        return $siacUser;
    }

    public function create(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $existing = Business::where('user_id', $siacUser['id'])->first();
        if ($existing) {
            return redirect()->route('business.edit');
        }

        $industries = $this->tradesGroupedByCorps();
        $regions = Region::orderBy('name_fr')->get();

        return view('pages.dashboard.business-form', [
            'lang' => $lang, 'siacUser' => $siacUser, 'business' => null,
            'industries' => $industries, 'regions' => $regions, 'cities' => collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        if (Business::where('user_id', $siacUser['id'])->exists()) {
            return redirect()->route('business.edit');
        }

        $data = $this->validated($request);
        $user = User::findOrFail($siacUser['id']);

        $business = $this->service->create($user, $data);
        $this->handleUploads($request, $business);

        /*
         * The shop is created as a draft and stays invisible until a
         * registration payment is confirmed, because publication is what the
         * subscription buys. Publishing here unconditionally used to be
         * harmless; once BusinessService::publish() began refusing unpaid
         * businesses it would have thrown on every new signup, which is a far
         * worse failure than an unlisted shop.
         *
         * A missing plan or an unconfigured payment method must not cost the
         * member their shop either: the record is theirs regardless, and they
         * are simply told it is awaiting payment.
         */
        $payment = null;

        try {
            $payment = \App\Support\PlatformFees::openRegistration($business, $request->input('plan'));
        } catch (\Throwable $e) {
            report($e);
        }

        // Ensure the session reflects the business_owner role for immediate dashboard access
        session(['siac_user' => array_merge($siacUser, ['role' => 'business_owner'])]);

        $fr = $this->lang($request) === 'fr';

        if ($payment) {
            return redirect()->route('payment.instructions', ['reference' => $payment->reference, 'lang' => $this->lang($request)])
                ->with('success', $fr
                    ? "Votre entreprise a été créée. Elle restera hors ligne jusqu'à la confirmation de votre paiement."
                    : 'Your business has been created. It stays offline until your payment is confirmed.');
        }

        return redirect()->route('dashboard.entrepreneur')
            ->with('success', $fr
                ? "Votre entreprise a été créée. Elle sera publiée dès qu'un abonnement aura été réglé et confirmé."
                : 'Your business has been created. It will be published once a subscription has been paid and confirmed.');
    }

    public function edit(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $business = Business::where('user_id', $siacUser['id'])->firstOrFail();
        $industries = $this->tradesGroupedByCorps();
        $regions = Region::orderBy('name_fr')->get();
        $cities = $business->region_id ? City::where('region_id', $business->region_id)->orderBy('name_fr')->get() : collect();

        return view('pages.dashboard.business-form', compact('lang', 'siacUser', 'business', 'industries', 'regions', 'cities'));
    }

    public function update(Request $request): RedirectResponse
    {
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $business = Business::where('user_id', $siacUser['id'])->firstOrFail();
        $data = $this->validated($request);

        $this->service->update($business, $data);
        $this->handleUploads($request, $business);

        return redirect()->route('business.edit')
            ->with('success', $this->lang($request) === 'fr' ? 'Profil mis à jour.' : 'Profile updated.');
    }

    public function citiesForRegion(Request $request, int $regionId)
    {
        $cities = City::where('region_id', $regionId)->orderBy('name_fr')->get(['id', 'name_fr', 'name_en']);
        return response()->json($cities);
    }

    private function handleUploads(Request $request, Business $business): void
    {
        if ($request->hasFile('logo')) {
            $this->service->updateLogo($business, $request->file('logo'));
        }
        if ($request->hasFile('cover_image')) {
            $this->service->updateCover($business, $request->file('cover_image'));
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'industry_id'      => ['required', 'exists:industries,id'],
            'region_id'        => ['nullable', 'exists:regions,id'],
            'city_id'          => ['nullable', 'exists:cities,id'],
            'name_fr'          => ['required', 'string', 'max:255'],
            'name_en'          => ['nullable', 'string', 'max:255'],
            'tagline_fr'       => ['nullable', 'string', 'max:255'],
            'tagline_en'       => ['nullable', 'string', 'max:255'],
            'description_fr'   => ['nullable', 'string', 'max:5000'],
            'description_en'   => ['nullable', 'string', 'max:5000'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'whatsapp'         => ['nullable', 'string', 'max:30'],
            'email'            => ['nullable', 'email', 'max:255'],
            'website'          => ['nullable', 'url', 'max:255'],
            'address_fr'       => ['nullable', 'string', 'max:255'],
            'address_en'       => ['nullable', 'string', 'max:255'],
            'year_established' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'employee_count'   => ['nullable', 'integer', 'min:0'],
            // Same reasoning as product images: phone photos are large, and both
            // of these are downscaled on the way in.
            'logo'             => ['nullable', 'image', 'max:12288'],
            'cover_image'      => ['nullable', 'image', 'max:12288'],
        ]);
    }
}
