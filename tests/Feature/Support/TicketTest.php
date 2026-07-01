<?php

namespace Tests\Feature\Support;

use App\Modules\Auth\Models\User;
use App\Modules\Support\Models\Ticket;
use App\Modules\Support\Models\TicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_user_can_create_ticket(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->postJson('/api/v1/support/tickets', [
            'subject' => 'Problème de connexion',
            'body'    => 'Je ne peux pas me connecter.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.subject', 'Problème de connexion')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonCount(1, 'data.messages');

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'subject' => 'Problème de connexion',
        ]);
    }

    public function test_create_ticket_validation_fails_without_subject(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->postJson('/api/v1/support/tickets', ['body' => 'x'])
            ->assertStatus(422);
    }

    public function test_user_can_view_own_tickets(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        Ticket::factory()->count(2)->create(['user_id' => $user->id]);
        Ticket::factory()->create(); // someone else's

        $this->getJson('/api/v1/support/tickets')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_user_can_view_single_ticket_with_messages(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'author_id' => $user->id,
            'body'      => 'Hello',
        ]);

        $this->getJson("/api/v1/support/tickets/{$ticket->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $ticket->id)
            ->assertJsonCount(1, 'data.messages');
    }

    public function test_user_cannot_view_someone_elses_ticket(): void
    {
        Passport::actingAs(User::factory()->create());
        $ticket = Ticket::factory()->create();

        $this->getJson("/api/v1/support/tickets/{$ticket->id}")
            ->assertForbidden();
    }

    public function test_user_can_reply_to_ticket(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->postJson("/api/v1/support/tickets/{$ticket->id}/messages", [
            'body' => 'Une question supplémentaire.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.is_from_staff', false);

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'author_id' => $user->id,
        ]);
    }

    public function test_admin_can_view_all_tickets(): void
    {
        Passport::actingAs($this->admin());
        Ticket::factory()->count(3)->create();

        $this->getJson('/api/v1/admin/support/tickets')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_reply(): void
    {
        $admin = $this->admin();
        Passport::actingAs($admin);
        $ticket = Ticket::factory()->create();

        $this->postJson("/api/v1/admin/support/tickets/{$ticket->id}/messages", [
            'body' => 'Nous examinons votre demande.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.is_from_staff', true);

        $this->assertNotNull($ticket->fresh()->first_response_at);
    }

    public function test_admin_can_close_ticket(): void
    {
        Passport::actingAs($this->admin());
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->postJson("/api/v1/admin/support/tickets/{$ticket->id}/close")
            ->assertOk()
            ->assertJsonPath('data.status', 'closed');

        $this->assertDatabaseHas('tickets', [
            'id'     => $ticket->id,
            'status' => 'closed',
        ]);
    }

    public function test_regular_user_cannot_access_admin_tickets(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/support/tickets')->assertForbidden();
    }

    public function test_unauthenticated_cannot_create_ticket(): void
    {
        $this->postJson('/api/v1/support/tickets', [
            'subject' => 'x',
            'body'    => 'y',
        ])->assertUnauthorized();
    }
}
