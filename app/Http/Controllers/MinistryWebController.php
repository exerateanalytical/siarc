<?php

namespace App\Http\Controllers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Events\Models\Event;
use App\Modules\Products\Models\Product;
use App\Modules\Taxonomy\Models\Industry;
use App\Modules\Taxonomy\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MinistryWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function requireMinistry(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || ($siacUser['role'] ?? null) !== 'ministry') {
            return redirect('/login');
        }
        return $siacUser;
    }

    public function dashboard(Request $request)
    {
        $lang = $this->lang($request);
        $siacUser = $this->requireMinistry($request);
        if ($siacUser instanceof RedirectResponse) return $siacUser;

        $stats = [
            'businesses'   => Business::where('status', 'published')->count(),
            'products'     => Product::where('status', 'published')->count(),
            'regions'      => Region::count(),
            'industries'   => Industry::where('is_active', true)->count(),
            'certified'    => Business::where('verification_tier', 'certified')->count(),
            'verified'     => Business::where('verification_tier', 'verified')->count(),
        ];

        $byRegion = Business::where('status', 'published')
            ->join('regions', 'regions.id', '=', 'businesses.region_id')
            ->selectRaw('regions.name_fr, regions.name_en, count(*) as total')
            ->groupBy('regions.id', 'regions.name_fr', 'regions.name_en')
            ->orderByDesc('total')
            ->get();

        $byIndustry = Business::where('status', 'published')
            ->join('industries', 'industries.id', '=', 'businesses.industry_id')
            ->selectRaw('industries.name_fr, industries.name_en, count(*) as total')
            ->groupBy('industries.id', 'industries.name_fr', 'industries.name_en')
            ->orderByDesc('total')
            ->get();

        $growth = Business::where('created_at', '>=', now()->subMonths(6))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $events = Event::withCount(['exhibitors', 'attendees'])->orderByDesc('starts_at')->limit(5)->get();

        return view('pages.dashboard.ministry', compact('lang', 'siacUser', 'stats', 'byRegion', 'byIndustry', 'growth', 'events'));
    }
}
