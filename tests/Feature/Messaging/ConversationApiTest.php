<?php

namespace Tests\Feature\Messaging;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class ConversationApiTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_conversations_require_authentication(): void
    {
        $this->getJson('/api/v1/conversations')->assertStatus(401);
    }

    public function test_buyer_can_start_a_conversation(): void
    {
        $business = $this->makeBusiness();
        Sanctum::actingAs($this->makeUser());

        $this->postJson('/api/v1/conversations', [
            'business_slug' => $business->slug,
            'subject'       => 'Demande de devis',
            'body'          => 'Bonjour, je souhaite un devis pour 50 unités.',
        ])->assertStatus(201)
          ->assertJsonStructure(['data' => ['conversation_id', 'message_id']]);

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_owner_cannot_message_own_business(): void
    {
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);
        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/conversations', [
            'business_slug' => $business->slug,
            'subject'       => 'Test',
            'body'          => 'Bonjour',
        ])->assertStatus(422);
    }

    public function test_participants_can_view_and_reply_others_cannot(): void
    {
        $buyer    = $this->makeUser();
        $owner    = $this->makeUser();
        $business = $this->makeBusiness($owner);

        Sanctum::actingAs($buyer);
        $conversationId = $this->postJson('/api/v1/conversations', [
            'business_slug' => $business->slug,
            'subject'       => 'RFQ',
            'body'          => 'Prix pour 100 kg ?',
        ])->json('data.conversation_id');

        // The business owner is a participant: can view and reply
        Sanctum::actingAs($owner);
        $this->getJson("/api/v1/conversations/{$conversationId}")
             ->assertStatus(200)
             ->assertJsonPath('data.subject', 'RFQ');
        $this->postJson("/api/v1/conversations/{$conversationId}/reply", [
            'body' => 'Le prix est de 500 000 FCFA.',
        ])->assertStatus(201);

        // A stranger is not
        Sanctum::actingAs($this->makeUser());
        $this->getJson("/api/v1/conversations/{$conversationId}")->assertStatus(403);
        $this->postJson("/api/v1/conversations/{$conversationId}/reply", ['body' => 'Intrus'])
             ->assertStatus(403);
    }

    public function test_index_only_lists_own_conversations(): void
    {
        $buyer    = $this->makeUser();
        $business = $this->makeBusiness();

        Sanctum::actingAs($buyer);
        $this->postJson('/api/v1/conversations', [
            'business_slug' => $business->slug,
            'subject'       => 'Ma conversation',
            'body'          => 'Bonjour',
        ])->assertStatus(201);

        Sanctum::actingAs($this->makeUser());
        $this->getJson('/api/v1/conversations')
             ->assertStatus(200)
             ->assertJsonPath('meta.total', 0);
    }
}
