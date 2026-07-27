<?php

namespace Tests\Concerns;

use App\Modules\ApiProduct\Models\ApiConsumer;
use App\Modules\ApiProduct\Services\ApiKeyService;

/**
 * /api/v1 is no longer readable without a key, so tests that exercise the
 * public endpoints have to present one the way a real integrator would.
 */
trait UsesApiKey
{
    protected function withApiKey(): static
    {
        $consumer = ApiConsumer::create([
            'name'        => 'Test integrator',
            'email'       => 'integrator+' . uniqid() . '@example.cm',
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        $issued = app(ApiKeyService::class)->issue($consumer, 'test');

        return $this->withHeaders(['X-API-Key' => $issued['key']]);
    }

    protected function withoutApiKey(): static
    {
        unset($this->defaultHeaders['X-API-Key']);

        return $this;
    }
}
