<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Report on the services this deployment is actually configured to use.
     *
     * It used to probe Redis and Meilisearch unconditionally. artisanhub237.com
     * runs on shared hosting with neither: the endpoint answered 503 on every
     * request forever, which is worse than no health check, because an uptime
     * monitor pointed at a permanently-red endpoint gets muted and then cannot
     * tell you when the database really does go away. Which services count is
     * read from config — the same config the app itself dispatches on — so the
     * check follows the deployment instead of asserting a topology.
     */
    public function __invoke(): JsonResponse
    {
        $checks = ['database' => $this->checkDatabase()];

        if ($this->usesRedis()) {
            $checks['redis'] = $this->checkRedis();
        }

        if (config('scout.driver') === 'meilisearch') {
            $checks['meilisearch'] = $this->checkMeilisearch();
        }

        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'healthy');

        return $this->success($checks, 'ok', $healthy ? 200 : 503);
    }

    /**
     * A failed check reports that it failed and nothing more.
     *
     * /api/health is unauthenticated, so the driver's exception message is
     * published to the internet — and a connection exception carries the host,
     * the port, the database name and often the username. The detail belongs in
     * the log, where an operator can read it and a stranger cannot.
     */
    private function down(\Throwable $e): array
    {
        report($e);

        return array_filter([
            'status' => 'down',
            'error'  => config('app.debug') ? $e->getMessage() : null,
        ], fn($v) => $v !== null);
    }

    /** True when any driver the app dispatches on is backed by Redis. */
    private function usesRedis(): bool
    {
        return in_array('redis', [
            config('cache.default'),
            config('queue.default'),
            config('session.driver'),
            config('broadcasting.default'),
        ], true);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return $this->down($e);
        }
    }

    private function checkRedis(): array
    {
        try {
            $start = microtime(true);
            \Illuminate\Support\Facades\Redis::ping();
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return $this->down($e);
        }
    }

    private function checkMeilisearch(): array
    {
        try {
            $start  = microtime(true);
            $client = new \MeiliSearch\Client(
                config('scout.meilisearch.host', 'http://localhost:7700'),
                config('scout.meilisearch.key'),
            );
            $client->health();
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return $this->down($e);
        }
    }
}
