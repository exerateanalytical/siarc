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

        $industries = Industry::where('is_active', true)->orderBy('sort_order')->get();
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
        $this->service->publish($business);
        $this->handleUploads($request, $business);

        // Ensure the session reflects the business_owner role for immediate dashboard access
        session(['siac_user' => array_merge($siacUser, ['role' => 'business_owner'])]);

        return redirect()->route('dashboard.entrepreneur')
            ->with('success', $this->lang($request) === 'fr' ? 'Votre entreprise a été créée et publiée.' : 'Your business has been created and published.');
    }

    public function edit(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireUser($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $business = Business::where('user_id', $siacUser['id'])->firstOrFail();
        $industries = Industry::where('is_active', true)->orderBy('sort_order')->get();
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
            'logo'             => ['nullable', 'image', 'max:4096'],
            'cover_image'      => ['nullable', 'image', 'max:6144'],
        ]);
    }
}
