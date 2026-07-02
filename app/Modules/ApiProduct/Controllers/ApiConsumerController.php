<?php

namespace App\Modules\ApiProduct\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ApiProduct\Models\ApiConsumer;
use App\Modules\ApiProduct\Services\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiConsumerController extends Controller
{
    public function __construct(private readonly ApiKeyService $keyService) {}

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'use_case' => ['required', 'string', 'max:1000'],
            'website'  => ['nullable', 'url', 'max:255'],
            'company'  => ['nullable', 'string', 'max:255'],
            'country'  => ['nullable', 'string', 'size:2'],
        ]);

        $existing = ApiConsumer::forEmail($request->user()->email);
        if ($existing) {
            return response()->json(['message' => 'You already have an API consumer account.', 'data' => ['id' => $existing->id, 'status' => $existing->status]], 422);
        }

        $consumer = ApiConsumer::create([
            'name'    => $request->app_name,
            'email'   => strtolower($request->user()->email),
            'purpose' => $request->use_case,
            'website' => $request->website,
            'company' => $request->company,
            'country' => $request->country ? strtoupper($request->country) : null,
            'status'  => 'pending',
        ]);

        return response()->json(['data' => [
            'id'      => $consumer->id,
            'status'  => $consumer->status,
            'message' => 'Application submitted for review.',
        ]], 201);
    }

    public function show(Request $request): JsonResponse
    {
        $consumer = ApiConsumer::where('email', strtolower($request->user()->email))->with('keys')->firstOrFail();

        return response()->json(['data' => [
            'id'       => $consumer->id,
            'app_name' => $consumer->name,
            'status'   => $consumer->status,
            'keys'     => $consumer->keys->map(fn ($k) => [
                'id'         => $k->id,
                'name'       => $k->name,
                'prefix'     => $k->key_prefix,
                'is_active'  => $k->is_active,
                'last_used_at' => $k->last_used_at?->toIso8601String(),
                'expires_at' => $k->expires_at?->toIso8601String(),
            ]),
        ]]);
    }

    public function createKey(Request $request): JsonResponse
    {
        $request->validate(['name' => ['required', 'string', 'max:100']]);

        $consumer = ApiConsumer::where('email', strtolower($request->user()->email))
            ->where('status', 'approved')
            ->firstOrFail();

        if ($consumer->keys()->where('is_active', true)->count() >= 5) {
            return response()->json(['message' => 'Maximum 5 active keys allowed.'], 422);
        }

        $result = $this->keyService->issue($consumer, $request->name);

        return response()->json([
            'data'    => $result,
            'warning' => 'Store your API key securely — it will not be shown again.',
        ], 201);
    }

    public function revokeKey(Request $request, int $keyId): JsonResponse
    {
        $consumer = ApiConsumer::where('email', strtolower($request->user()->email))->firstOrFail();
        $key      = $consumer->keys()->findOrFail($keyId);

        $this->keyService->revoke($key);

        return response()->json(['message' => 'Key revoked.']);
    }

    public function usage(Request $request): JsonResponse
    {
        $consumer = ApiConsumer::where('email', strtolower($request->user()->email))->firstOrFail();

        $keyIds = $consumer->keys()->pluck('id');

        $daily = DB::table('api_usage_logs')
            ->whereIn('key_id', $keyIds)
            ->where('called_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(called_at) as date, COUNT(*) as requests, AVG(response_time_ms) as avg_ms')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json(['data' => [
            'total_30d' => $daily->sum('requests'),
            'daily'     => $daily,
        ]]);
    }
}
