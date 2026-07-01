<?php

namespace Tests\Feature\Notifications;

use App\Modules\Auth\Models\User;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_notifications(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        Notification::factory()->count(3)->create(['user_id' => $user->id]);
        Notification::factory()->create(); // someone else's

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'type', 'title_fr', 'is_read']], 'meta' => ['total']]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_someone_elses_notification(): void
    {
        Passport::actingAs(User::factory()->create());
        $notification = Notification::factory()->create();

        $this->postJson("/api/v1/notifications/{$notification->id}/read")
            ->assertForbidden();
    }

    public function test_user_can_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        Notification::factory()->count(3)->create(['user_id' => $user->id]);

        $this->postJson('/api/v1/notifications/read-all')->assertOk();

        $this->assertSame(0, Notification::where('user_id', $user->id)->whereNull('read_at')->count());
    }

    public function test_user_gets_unread_count(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        Notification::factory()->count(2)->create(['user_id' => $user->id]);
        Notification::factory()->read()->create(['user_id' => $user->id]);

        $this->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 2);
    }

    public function test_unauthenticated_cannot_access_notifications(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
    }
}
