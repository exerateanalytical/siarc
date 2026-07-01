<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_returns_database_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        // Health check returns 200 if DB is up, 503 if any service is down
        // In test environment DB is up, Redis/Meilisearch may be down
        $this->assertContains($response->status(), [200, 503]);
        $response->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['database']]);
    }

    public function test_health_endpoint_has_database_check(): void
    {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json('data');
        $this->assertArrayHasKey('database', $data);
        $this->assertEquals('healthy', $data['database']['status']);
    }
}
