<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database'    => $this->checkDatabase(),
            'redis'       => $this->checkRedis(),
            'meilisearch' => $this->checkMeilisearch(),
        ];

        $healthy = collect($checks)->every(fn($c) => $c['status'] === 'healthy');

        return $this->success($checks, 'ok', $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $ms = round((microtime(true) - $start) * 1000);
            return ['status' => 'healthy', 'ms' => $ms];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
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
            return ['status' => 'down', 'error' => $e->getMessage()];
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
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }
}
