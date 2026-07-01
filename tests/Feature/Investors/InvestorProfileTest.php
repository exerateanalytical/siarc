<?php

namespace Tests\Feature\Investors;

use App\Modules\Auth\Models\User;
use App\Modules\Investors\Models\KycApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class InvestorProfileTest extends TestCase
{
    use RefreshDatabase;

    private function investor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('investor');

        return $user;
    }

    private function reviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cmf_reviewer');

        return $user;
    }

    public function test_investor_can_view_own_profile(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);

        $this->getJson('/api/v1/investor/profile')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'user_id', 'investor_type', 'accreditation_level', 'risk_tolerance'],
            ])
            ->assertJsonPath('data.user_id', $investor->id);

        $this->assertDatabaseHas('investor_profiles', ['user_id' => $investor->id]);
    }

    public function test_investor_can_update_own_profile(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);

        $this->putJson('/api/v1/investor/profile', [
            'investor_type'  => 'institutional',
            'risk_tolerance' => 'aggressive',
            'occupation'     => 'Fund Manager',
            'annual_income'  => 50000000,
        ])
            ->assertOk()
            ->assertJsonPath('data.investor_type', 'institutional')
            ->assertJsonPath('data.risk_tolerance', 'aggressive')
            ->assertJsonPath('data.occupation', 'Fund Manager');

        $this->assertDatabaseHas('investor_profiles', [
            'user_id'        => $investor->id,
            'investor_type'  => 'institutional',
            'risk_tolerance' => 'aggressive',
        ]);
    }

    public function test_investor_can_submit_kyc(): void
    {
        $investor = $this->investor();
        Passport::actingAs($investor, ['investor:profile']);

        $this->postJson('/api/v1/investor/kyc/submit', [
            'tier' => 'standard',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.tier', 'standard');

        $this->assertDatabaseHas('kyc_applications', [
            'user_id' => $investor->id,
            'status'  => 'submitted',
        ]);
    }

    public function test_investor_can_check_kyc_status(): void
    {
        $investor = $this->investor();
        KycApplication::create([
            'user_id'      => $investor->id,
            'tier'         => 'basic',
            'status'       => KycApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
        Passport::actingAs($investor, ['investor:profile']);

        $this->getJson('/api/v1/investor/kyc/status')
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    public function test_kyc_status_is_null_when_no_application(): void
    {
        Passport::actingAs($this->investor(), ['investor:profile']);

        $this->getJson('/api/v1/investor/kyc/status')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_admin_can_list_pending_kyc(): void
    {
        $investor = $this->investor();
        KycApplication::create([
            'user_id'      => $investor->id,
            'tier'         => 'basic',
            'status'       => KycApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Passport::actingAs($this->reviewer(), []);

        $this->getJson('/api/v1/admin/kyc')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'user_id', 'status', 'tier']], 'meta']);
    }

    public function test_admin_can_approve_kyc(): void
    {
        $investor = $this->investor();
        $application = KycApplication::create([
            'user_id'      => $investor->id,
            'tier'         => 'basic',
            'status'       => KycApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Passport::actingAs($this->reviewer(), []);

        $this->postJson("/api/v1/admin/kyc/{$application->id}/approve", [
            'notes' => 'All documents verified.',
        ])->assertOk();

        $this->assertDatabaseHas('kyc_applications', [
            'id'     => $application->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_kyc(): void
    {
        $investor = $this->investor();
        $application = KycApplication::create([
            'user_id'      => $investor->id,
            'tier'         => 'basic',
            'status'       => KycApplication::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        Passport::actingAs($this->reviewer(), []);

        $this->postJson("/api/v1/admin/kyc/{$application->id}/reject", [
            'reason' => 'Document illegible.',
        ])->assertOk();

        $this->assertDatabaseHas('kyc_applications', [
            'id'     => $application->id,
            'status' => 'rejected',
        ]);
    }

    public function test_non_reviewer_cannot_list_pending_kyc(): void
    {
        Passport::actingAs($this->investor(), ['investor:profile']);

        $this->getJson('/api/v1/admin/kyc')->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_profile(): void
    {
        $this->getJson('/api/v1/investor/profile')->assertUnauthorized();
    }
}
