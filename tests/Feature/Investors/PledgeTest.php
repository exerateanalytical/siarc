<?php

namespace Tests\Feature\Investors;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\InvestmentPledge;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PledgeTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    public function test_investor_can_make_pledge_on_open_offering(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create([
            'share_price'    => 5000,
            'min_investment' => 10000,
        ]);

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", [
            'amount' => 50000,
        ])
            ->assertCreated()
            ->assertJsonPath('data.amount', 50000)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('investment_pledges', [
            'offering_id' => $offering->id,
            'investor_id' => $investor->id,
            'amount'      => 50000,
            'status'      => 'pending',
        ]);
    }

    public function test_investor_cannot_pledge_on_closed_offering(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->create(['status' => 'draft']);

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", [
            'amount' => 50000,
        ])->assertStatus(422);
    }

    public function test_investor_can_view_own_pledges(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:portfolio']);
        InvestmentPledge::factory()->count(2)->create(['investor_id' => $investor->id]);
        InvestmentPledge::factory()->create(); // someone else's

        $this->getJson('/api/v1/investor/pledges')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'offering_id', 'amount', 'status']]]);
    }

    public function test_investor_can_cancel_pending_pledge(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $pledge = InvestmentPledge::factory()->create([
            'investor_id' => $investor->id,
            'offering_id' => $offering->id,
            'status'      => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/pledges/{$pledge->id}")
            ->assertNoContent();

        $this->assertDatabaseHas('investment_pledges', [
            'id'     => $pledge->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_investor_cannot_cancel_someone_elses_pledge(): void
    {
        Passport::actingAs($this->investor(), ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        $pledge = InvestmentPledge::factory()->create([
            'offering_id' => $offering->id,
            'status'      => 'pending',
        ]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}/pledges/{$pledge->id}")
            ->assertForbidden();
    }

    public function test_admin_can_view_all_pledges_for_offering(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:pledge']);
        $offering = ShareOffering::factory()->open()->create();
        InvestmentPledge::factory()->count(3)->create(['offering_id' => $offering->id]);

        $this->getJson("/api/v1/offerings/{$offering->id}/pledges")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_investor_can_view_portfolio_summary(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:portfolio']);
        InvestmentPledge::factory()->create([
            'investor_id' => $investor->id,
            'status'      => 'pending',
        ]);

        $this->getJson('/api/v1/investor/portfolio')
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['total_invested', 'total_pledged', 'active_pledges', 'companies_count', 'pledges'],
            ]);
    }

    public function test_unauthenticated_cannot_pledge(): void
    {
        $offering = ShareOffering::factory()->open()->create();

        $this->postJson("/api/v1/offerings/{$offering->id}/pledges", [
            'amount' => 50000,
        ])->assertUnauthorized();
    }
}
