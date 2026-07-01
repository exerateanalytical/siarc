<?php

namespace Tests\Feature\Trading;

use App\Modules\Auth\Models\User;
use App\Modules\Trading\Models\Order;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    public function test_public_can_view_the_open_order_book(): void
    {
        $offering = ShareOffering::factory()->open()->create();
        Order::factory()->count(2)->create([
            'offering_id' => $offering->id, 'status' => 'pending',
        ]);

        $this->getJson("/api/v1/offerings/{$offering->id}/orders")
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'type', 'quantity', 'unit_price', 'status']]]);
    }

    public function test_investor_can_place_a_buy_order(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create(['share_price' => 5000, 'min_investment' => 10000]);

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", [
            'type' => 'buy', 'quantity' => 10,
        ])->assertCreated()
          ->assertJsonPath('data.type', 'buy')
          ->assertJsonPath('data.quantity', 10)
          ->assertJsonPath('data.total_amount', 50000);

        $this->assertDatabaseHas('orders', [
            'offering_id' => $offering->id, 'investor_id' => $investor->id, 'status' => 'pending',
        ]);
    }

    public function test_investor_can_place_a_sell_order(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create(['share_price' => 5000, 'min_investment' => 10000]);

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", [
            'type' => 'sell', 'quantity' => 4,
        ])->assertCreated()
          ->assertJsonPath('data.type', 'sell')
          ->assertJsonPath('data.quantity', 4);
    }

    public function test_placing_order_requires_investor_pledge_scope(): void
    {
        Passport::actingAs($this->investor(), []); // no scope
        $offering = ShareOffering::factory()->open()->create();

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", ['quantity' => 5])
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_place_an_order(): void
    {
        $offering = ShareOffering::factory()->open()->create();

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", ['quantity' => 5])
            ->assertUnauthorized();
    }

    public function test_cannot_place_order_on_a_closed_offering(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->create(['status' => 'draft']);

        $this->postJson("/api/v1/offerings/{$offering->id}/orders", ['quantity' => 10])
            ->assertStatus(422);
    }

    public function test_investor_can_cancel_their_own_order(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $order = Order::factory()->create([
            'offering_id' => $offering->id, 'investor_id' => $investor->id, 'status' => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_investor_cannot_cancel_someone_elses_order(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $order = Order::factory()->create([
            'offering_id' => $offering->id, 'status' => 'pending', // owned by someone else
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/orders/{$order->id}")
            ->assertStatus(422);
    }
}
