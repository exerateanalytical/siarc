<?php

namespace Tests\Feature\Api;

use App\Modules\Api\Models\Webhook;
use App\Modules\Api\Models\WebhookDelivery;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    private function makeWebhook(User $owner): Webhook
    {
        return Webhook::create([
            'owner_id'    => $owner->id,
            'owner_type'  => 'user',
            'url'         => 'https://example.com/hook',
            'events'      => ['offering.created'],
            'secret_hash' => Hash::make('secret'),
            'is_active'   => true,
        ]);
    }

    public function test_user_can_register_webhook(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/webhooks', [
            'url'    => 'https://example.com/webhook',
            'events' => ['offering.created', 'offering.closed'],
        ])
            ->assertCreated()
            ->assertJsonPath('data.url', 'https://example.com/webhook')
            ->assertJsonStructure(['data' => ['id', 'secret']]);

        $this->assertDatabaseHas('webhooks', [
            'owner_id' => $user->id,
            'url'      => 'https://example.com/webhook',
        ]);
    }

    public function test_register_webhook_requires_valid_url_and_events(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->postJson('/api/v1/webhooks', ['url' => 'not-a-url', 'events' => []])
            ->assertStatus(422);
    }

    public function test_user_can_list_own_webhooks(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->makeWebhook($user);
        $this->makeWebhook($other);

        Passport::actingAs($user);

        $this->getJson('/api/v1/webhooks')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_delete_webhook(): void
    {
        $user = User::factory()->create();
        $webhook = $this->makeWebhook($user);
        Passport::actingAs($user);

        $this->deleteJson("/api/v1/webhooks/{$webhook->id}")
            ->assertOk();

        $this->assertSoftDeleted('webhooks', ['id' => $webhook->id]);
    }

    public function test_user_cannot_delete_others_webhook(): void
    {
        $owner = User::factory()->create();
        $webhook = $this->makeWebhook($owner);

        Passport::actingAs(User::factory()->create());

        $this->deleteJson("/api/v1/webhooks/{$webhook->id}")
            ->assertForbidden();
    }

    public function test_user_can_list_webhook_deliveries(): void
    {
        $user = User::factory()->create();
        $webhook = $this->makeWebhook($user);

        WebhookDelivery::create([
            'webhook_id'      => $webhook->id,
            'event'           => 'offering.created',
            'payload'         => ['id' => 1],
            'response_status' => 200,
            'status'          => 'delivered',
            'delivered_at'    => now(),
        ]);

        Passport::actingAs($user);

        $this->getJson("/api/v1/webhooks/{$webhook->id}/deliveries")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.event', 'offering.created');
    }

    public function test_unauthenticated_cannot_access_webhooks(): void
    {
        $this->getJson('/api/v1/webhooks')->assertUnauthorized();
    }
}
