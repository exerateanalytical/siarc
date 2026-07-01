<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\PlatformEvent;
use App\Modules\Analytics\Models\SearchQuery;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function recordSearch(string $query, int $resultsCount, ?int $userId, string $ip): void
    {
        SearchQuery::create([
            'query'         => mb_strtolower(trim($query)),
            'results_count' => $resultsCount,
            'user_id'       => $userId,
            'ip_address'    => $ip,
            'searched_at'   => now(),
        ]);

        DB::table('popular_searches_cache')
            ->upsert(
                [['query' => mb_strtolower(trim($query)), 'count' => 1, 'last_searched_at' => now()]],
                ['query'],
                ['count' => DB::raw('count + 1'), 'last_searched_at' => now()]
            );
    }

    public function recordEvent(string $type, ?int $userId, string $entityType, ?int $entityId, array $meta, string $ip, string $ua): void
    {
        PlatformEvent::create([
            'event_type'  => $type,
            'user_id'     => $userId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'meta'        => $meta,
            'ip_address'  => $ip,
            'user_agent'  => mb_substr($ua, 0, 255),
            'occurred_at' => now(),
        ]);
    }

    public function platformSummary(): array
    {
        return [
            'businesses' => [
                'total'      => DB::table('businesses')->whereNull('deleted_at')->count(),
                'published'  => DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count(),
                'verified'   => DB::table('businesses')->whereIn('verification_tier', ['verified', 'certified'])->whereNull('deleted_at')->count(),
            ],
            'products' => [
                'total'     => DB::table('products')->whereNull('deleted_at')->count(),
                'published' => DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count(),
            ],
            'users' => [
                'total'           => DB::table('users')->whereNull('deleted_at')->count(),
                'business_owners' => DB::table('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', 'business_owner')
                    ->count(),
            ],
            'conversations' => [
                'total'  => DB::table('conversations')->count(),
                'active' => DB::table('conversations')->where('status', 'active')->count(),
            ],
            'popular_searches' => DB::table('popular_searches_cache')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('query'),
        ];
    }
}
