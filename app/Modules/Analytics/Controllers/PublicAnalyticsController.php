<?php

namespace App\Modules\Analytics\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicAnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function platformStats(): JsonResponse
    {
        $stats = cache()->remember('platform_stats', 300, fn () => $this->analytics->platformSummary());

        return response()->json(['data' => $stats]);
    }

    public function popularSearches(): JsonResponse
    {
        $searches = DB::table('popular_searches_cache')
            ->orderByDesc('count')
            ->limit(20)
            ->get(['query', 'count']);

        return response()->json(['data' => $searches]);
    }
}
