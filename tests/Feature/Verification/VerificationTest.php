<?php

namespace Tests\Feature\Verification;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Verification\Models\VerificationApplication;
use App\Modules\Verification\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private function seedTiers(): void
    {
        foreach ([
            ['name' => 'Basique', 'slug' => 'basic', 'level' => 1],
            ['name' => 'Vérifié', 'slug' => 'verified', 'level' => 2],
            ['name' => 'Certifié', 'slug' => 'certified', 'level' => 3],
        ] as $t) {
            VerificationTier::create($t);
        }
    }

    private function linkOwner(Company $company, User $user): void
    {
        $company->members()->create([
            'user_id'   => $user->id,
            'role'      => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);
    }

    public function test_company_owner_can_submit_verification(): void
    {
        $this->seedTiers();

        $user    = User::factory()->create(['status' => 'active']);
        $company = Company::factory()->create();
        $this->linkOwner($company, $user);

        Passport::actingAs($user, ['companies:verify']);

        $response = $this->postJson("/api/v1/companies/{$company->id}/verification/submit", [
            'tier_requested' => 'basic',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.tier_requested', 'basic');

        $this->assertDatabaseHas('verification_applications', [
            'company_id'   => $company->id,
            'submitted_by' => $user->id,
            'status'       => 'submitted',
        ]);
    }

    public function test_non_member_cannot_submit_verification(): void
    {
        $this->seedTiers();

        $user    = User::factory()->create(['status' => 'active']);
        $company = Company::factory()->create(); // user is not a member

        Passport::actingAs($user, ['companies:verify']);

        $this->postJson("/api/v1/companies/{$company->id}/verification/submit", [
            'tier_requested' => 'basic',
        ])->assertForbidden();
    }

    public function test_guest_cannot_submit_verification(): void
    {
        $company = Company::factory()->create();

        $this->postJson("/api/v1/companies/{$company->id}/verification/submit", [
            'tier_requested' => 'basic',
        ])->assertStatus(401);
    }

    public function test_owner_can_view_verification_status(): void
    {
        $this->seedTiers();

        $user    = User::factory()->create(['status' => 'active']);
        $company = Company::factory()->create();
        $this->linkOwner($company, $user);

        Passport::actingAs($user, ['companies:verify']);

        $this->postJson("/api/v1/companies/{$company->id}/verification/submit", [
            'tier_requested' => 'basic',
        ])->assertStatus(201);

        $this->getJson("/api/v1/companies/{$company->id}/verification/status")
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'unverified')
            ->assertJsonPath('data.latest_application.status', 'submitted');
    }

    public function test_admin_can_approve_verification(): void
    {
        $this->seedTiers();

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('govt_reviewer');

        $owner   = User::factory()->create(['status' => 'active']);
        $company = Company::factory()->create(['verification_status' => 'unverified']);
        $this->linkOwner($company, $owner);

        $tier = VerificationTier::where('slug', 'basic')->first();
        $application = VerificationApplication::create([
            'company_id'     => $company->id,
            'submitted_by'   => $owner->id,
            'target_tier_id' => $tier->id,
            'status'         => 'submitted',
            'submitted_at'   => now(),
        ]);

        Passport::actingAs($admin, ['admin:compliance']);

        $this->postJson("/api/v1/admin/verifications/{$application->id}/approve", [
            'notes' => 'Documents verified.',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('verification_applications', [
            'id'     => $application->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('companies', [
            'id'                  => $company->id,
            'verification_status' => 'basic',
        ]);
    }

    public function test_admin_can_reject_verification(): void
    {
        $this->seedTiers();

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('govt_reviewer');

        $tier = VerificationTier::where('slug', 'basic')->first();
        $company = Company::factory()->create();
        $application = VerificationApplication::create([
            'company_id'     => $company->id,
            'submitted_by'   => User::factory()->create()->id,
            'target_tier_id' => $tier->id,
            'status'         => 'submitted',
            'submitted_at'   => now(),
        ]);

        Passport::actingAs($admin, ['admin:compliance']);

        $this->postJson("/api/v1/admin/verifications/{$application->id}/reject", [
            'reason' => 'RCCM number does not match registry.',
        ])->assertOk();

        $this->assertDatabaseHas('verification_applications', [
            'id'     => $application->id,
            'status' => 'rejected',
        ]);
    }

    public function test_admin_can_list_applications(): void
    {
        $this->seedTiers();

        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('govt_reviewer');

        $tier = VerificationTier::where('slug', 'basic')->first();
        VerificationApplication::create([
            'company_id'     => Company::factory()->create()->id,
            'submitted_by'   => User::factory()->create()->id,
            'target_tier_id' => $tier->id,
            'status'         => 'submitted',
            'submitted_at'   => now(),
        ]);

        Passport::actingAs($admin, ['admin:compliance']);

        $this->getJson('/api/v1/admin/verifications')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_non_reviewer_cannot_list_applications(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['admin:compliance']);

        $this->getJson('/api/v1/admin/verifications')->assertForbidden();
    }
}
