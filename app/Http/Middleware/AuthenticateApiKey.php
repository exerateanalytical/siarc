<?php

namespace App\Http\Middleware;

use App\Modules\ApiProduct\Models\ApiKey;
use App\Modules\ApiProduct\Services\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * API-key gate for the /api/v1 surface.
 *
 * The developer programme is not being advertised at launch, so there is no
 * reason to leave the directory readable in bulk by anonymous callers: an
 * unauthenticated request without a key is refused. Session/token holders
 * (the first-party web and mobile clients) still pass, because these route
 * groups also carry owner-scoped endpoints behind auth:sanctum.
 *
 * When a key is presented it must be valid: the request is then rate-limited
 * per key and recorded in api_usage_logs.
 */
class AuthenticateApiKey
{
    public function __construct(private readonly ApiKeyService $keyService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $rawKey = $request->header('X-API-Key');
        if (! $rawKey) {
            // First-party clients authenticate with a Sanctum token, not a key;
            // let them reach the auth:sanctum routes inside these groups.
            if ($request->user() || $request->user('sanctum')) {
                return $next($request);
            }

            return response()->json(['message' => 'API key required.'], 401);
        }

        $key = $this->keyService->resolveFromRequest($rawKey);
        if (! $key) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $consumer = $key->consumer;
        if (! $consumer || $consumer->status !== 'approved') {
            return response()->json(['message' => 'API consumer account is not approved.'], 403);
        }

        $limit = max(1, (int) $key->rate_limit_per_minute);
        if (RateLimiter::tooManyAttempts('api-key:' . $key->id, $limit)) {
            return response()->json(['message' => 'Rate limit exceeded.'], 429)
                ->header('Retry-After', RateLimiter::availableIn('api-key:' . $key->id));
        }
        RateLimiter::hit('api-key:' . $key->id, 60);

        $request->attributes->set('api_key', $key);
        $startedAt = microtime(true);

        $response = $next($request);

        $this->recordUsage($request, $key, $response->getStatusCode(), (int) round((microtime(true) - $startedAt) * 1000));

        return $response;
    }

    private function recordUsage(Request $request, ApiKey $key, int $statusCode, int $elapsedMs): void
    {
        // Analytics must never break the request
        try {
            DB::table('api_usage_logs')->insert([
                'key_id'           => $key->id,
                'endpoint'         => substr('/' . $request->path(), 0, 255),
                'method'           => substr($request->method(), 0, 10),
                'status_code'      => $statusCode,
                'response_time_ms' => $elapsedMs,
                'ip'               => $request->ip(),
                'called_at'        => now(),
            ]);
            $key->newQuery()->whereKey($key->id)->update(['last_used_at' => now()]);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
