<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Events\Models\Event;
use App\Modules\Products\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionalRepWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireRegionalRep(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || ($siacUser['role'] ?? null) !== 'regional_rep') {
            return redirect('/login');
        }
        return $siacUser;
    }

    public function dashboard(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireRegionalRep($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $regionId = \App\Modules\Auth\Models\User::find($siacUser['id'])->assigned_region_id;

        if (! $regionId) {
            return view('pages.dashboard.regional-rep', [
                'lang' => $lang, 'siacUser' => $siacUser, 'region' => null, 'events' => collect(),
            ]);
        }

        $region = \App\Modules\Taxonomy\Models\Region::findOrFail($regionId);

        $businessQuery = Business::where('region_id', $regionId);

        $stats = [
            'total'      => (clone $businessQuery)->count(),
            'published'  => (clone $businessQuery)->where('status', 'published')->count(),
            'draft'      => (clone $businessQuery)->where('status', 'draft')->count(),
            'suspended'  => (clone $businessQuery)->where('status', 'suspended')->count(),
            'certified'  => (clone $businessQuery)->where('verification_tier', 'certified')->count(),
            'verified'   => (clone $businessQuery)->where('verification_tier', 'verified')->count(),
            'products'   => Product::whereHas('business', fn ($q) => $q->where('region_id', $regionId))->count(),
            'views'      => (clone $businessQuery)->sum('views_count'),
        ];

        $businessesByIndustry = (clone $businessQuery)
            ->join('industries', 'industries.id', '=', 'businesses.industry_id')
            ->selectRaw("industries.name_fr, industries.name_en, count(*) as total")
            ->groupBy('industries.id', 'industries.name_fr', 'industries.name_en')
            ->orderByDesc('total')
            ->get();

        $businesses = Business::with(['industry', 'city'])
            ->where('region_id', $regionId)
            ->orderByDesc('created_at')
            ->paginate(15);

        $events = Event::whereHas('exhibitingBusinesses', fn ($q) => $q->where('region_id', $regionId))
            ->withCount(['attendees'])
            ->with(['exhibitingBusinesses' => fn ($q) => $q->where('region_id', $regionId)])
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        return view('pages.dashboard.regional-rep', compact('lang', 'siacUser', 'region', 'stats', 'businessesByIndustry', 'businesses', 'events'));
    }
}
