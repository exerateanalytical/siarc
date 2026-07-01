<?php

namespace Tests\Feature\Payments;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_mtn_payment(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['payments:initiate']);

        $response = $this->postJson('/api/v1/payments/initiate', [
            'amount_xaf'  => 50000,
            'provider'    => 'mtn_momo',
            'phone'       => '+237677000000',
            'purpose'     => 'pledge_payment',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['data' => ['reference', 'status', 'fee_breakdown']]);
    }

    public function test_user_can_initiate_orange_payment(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['payments:initiate']);

        $response = $this->postJson('/api/v1/payments/initiate', [
            'amount_xaf'  => 25000,
            'provider'    => 'orange_money',
            'phone'       => '+237655000000',
            'purpose'     => 'pledge_payment',
        ]);

        $response->assertStatus(201);
    }

    public function test_fee_breakdown_is_correct(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['payments:initiate']);

        $response = $this->postJson('/api/v1/payments/initiate', [
            'amount_xaf' => 100000,
            'provider'   => 'mtn_momo',
            'phone'      => '+237677000000',
            'purpose'    => 'pledge_payment',
        ]);

        $fees = $response->json('data.fee_breakdown');
        $this->assertEquals(2500, $fees['platform_fee_xaf']);
        $this->assertEquals(481,  $fees['vat_xaf']);
        $this->assertEquals(97019, $fees['net_amount_xaf']);
    }

    public function test_unauthenticated_cannot_initiate_payment(): void
    {
        $this->postJson('/api/v1/payments/initiate', ['amount_xaf' => 50000])
             ->assertStatus(401);
    }

    public function test_user_can_view_payment_history(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['payments:view']);

        $response = $this->getJson('/api/v1/payments');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_mtn_callback_is_processed(): void
    {
        $response = $this->postJson('/api/v1/payments/callbacks/mtn', [
            'externalId'    => 'PAY-TESTREF',
            'status'        => 'SUCCESSFUL',
            'financialTransactionId' => 'FT123456',
        ]);

        // Callback endpoint is public and always returns 200
        $response->assertStatus(200);
    }
}
