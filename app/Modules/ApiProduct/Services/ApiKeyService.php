<?php

namespace App\Modules\ApiProduct\Services;

use App\Modules\ApiProduct\Models\ApiConsumer;
use App\Modules\ApiProduct\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function issue(ApiConsumer $consumer, string $name): array
    {
        $raw    = 'siac_' . Str::random(40);
        $prefix = substr($raw, 0, 12);
        $hash   = hash('sha256', $raw);

        $key = ApiKey::create([
            'consumer_id'          => $consumer->id,
            'key_prefix'           => $prefix,
            'key_hash'             => $hash,
            'name'                 => $name,
            'rate_limit_per_minute' => 60,
            'is_active'            => true,
        ]);

        // Return raw key ONCE — not stored
        return ['key' => $raw, 'id' => $key->id, 'prefix' => $prefix];
    }

    public function revoke(ApiKey $key): void
    {
        $key->update(['is_active' => false]);
    }

    public function resolveFromRequest(string $rawKey): ?ApiKey
    {
        $hash = hash('sha256', $rawKey);

        return ApiKey::where('key_hash', $hash)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();
    }
}
