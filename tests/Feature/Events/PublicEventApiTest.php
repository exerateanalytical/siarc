<?php

namespace Tests\Feature\Events;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

class PublicEventApiTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    public function test_index_lists_only_published_events(): void
    {
        $event = $this->makeEvent();
        $this->makeEvent(['is_published' => false]);

        $this->getJson('/api/v1/events')
             ->assertStatus(200)
             ->assertJsonCount(1, 'data')
             ->assertJsonPath('data.0.slug', $event->slug);
    }

    public function test_show_returns_event_with_attendee_count(): void
    {
        $event = $this->makeEvent();

        $this->getJson("/api/v1/events/{$event->slug}")
             ->assertStatus(200)
             ->assertJsonPath('data.slug', $event->slug)
             ->assertJsonPath('data.attendee_count', 0);
    }

    public function test_attend_requires_authentication(): void
    {
        $event = $this->makeEvent();

        $this->postJson("/api/v1/events/{$event->slug}/attend")->assertStatus(401);
    }

    public function test_user_can_attend_and_cancel(): void
    {
        $event = $this->makeEvent();
        Sanctum::actingAs($this->makeUser());

        $this->postJson("/api/v1/events/{$event->slug}/attend")
             ->assertStatus(201)
             ->assertJsonPath('data.status', 'registered');

        // Attending twice is idempotent
        $this->postJson("/api/v1/events/{$event->slug}/attend")->assertStatus(200);

        $this->deleteJson("/api/v1/events/{$event->slug}/attend")->assertStatus(200);
        $this->assertDatabaseCount('event_attendees', 0);
    }

    public function test_exhibit_requires_a_business(): void
    {
        $event = $this->makeEvent();
        Sanctum::actingAs($this->makeUser());

        $this->postJson("/api/v1/events/{$event->slug}/exhibit")->assertStatus(422);
    }

    public function test_business_owner_can_exhibit(): void
    {
        $event = $this->makeEvent();
        $owner = $this->makeUser();
        $this->makeBusiness($owner);
        Sanctum::actingAs($owner);

        $this->postJson("/api/v1/events/{$event->slug}/exhibit")
             ->assertStatus(201)
             ->assertJsonPath('data.status', 'confirmed');
    }
}
