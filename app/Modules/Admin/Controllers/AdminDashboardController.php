<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(): JsonResponse
    {
        $stats = $this->analytics->platformSummary();

        $stats['pending_verifications'] = DB::table('verification_applications')
            ->where('status', 'pending')->count();

        $stats['open_tickets'] = DB::table('support_tickets')
            ->where('status', 'open')->count();

        $stats['pending_api_applications'] = DB::table('api_consumers')
            ->where('status', 'pending')->count();

        $stats['recent_businesses'] = DB::table('businesses')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name_fr', 'slug', 'verification_tier', 'status', 'created_at']);

        return response()->json(['data' => $stats]);
    }
}
