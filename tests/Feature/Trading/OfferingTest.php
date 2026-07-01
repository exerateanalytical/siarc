<?php

namespace Tests\Feature\Trading;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Trading\Models\ShareOffering;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OfferingTest extends TestCase
{
    use RefreshDatabase;

    private function owner(Company $company): User
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        return $user;
    }

    private function cmfReviewer(): User
    {
        $user = User::factory()->create();
        $user->assignRole('cmf_reviewer');

        return $user;
    }

    private function payload(Company $company, array $overrides = []): array
    {
        return array_merge([
            'company_id'      => $company->id,
            'title_fr'        => 'Levee de fonds Serie A',
            'title_en'        => 'Series A raise',
            'summary_fr'      => 'Resume.',
            'instrument_type' => 'ordinary_shares',
            'target_amount'   => 50000000,
            'share_price'     => 5000,
            'total_shares'    => 10000,
            'equity_offered'  => 20.0,
            'min_investment'  => 10000,
        ], $overrides);
    }

    public function test_public_can_list_open_offerings(): void
    {
        ShareOffering::factory()->open()->count(2)->create();
        ShareOffering::factory()->create(['status' => 'draft']); // hidden

        $this->getJson('/api/v1/offerings')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'title_fr', 'status', 'share_price']], 'meta']);
    }

    public function test_owner_can_create_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);

        $this->postJson('/api/v1/offerings', $this->payload($company))
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.title_fr', 'Levee de fonds Serie A');

        $this->assertDatabaseHas('share_offerings', [
            'title_fr' => 'Levee de fonds Serie A', 'status' => 'draft',
        ]);
    }

    public function test_creating_offering_requires_companies_write_scope(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), []); // no scope

        $this->postJson('/api/v1/offerings', $this->payload($company))
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_create_an_offering(): void
    {
        $company = Company::factory()->create();

        $this->postJson('/api/v1/offerings', $this->payload($company))
            ->assertUnauthorized();
    }

    public function test_create_validation_fails_without_required_fields(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);

        $this->postJson('/api/v1/offerings', ['company_id' => $company->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title_fr', 'target_amount', 'share_price', 'total_shares']);
    }

    public function test_public_can_view_a_single_offering(): void
    {
        $offering = ShareOffering::factory()->open()->create();

        $this->getJson("/api/v1/offerings/{$offering->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $offering->id);
    }

    public function test_owner_can_update_a_draft_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->putJson("/api/v1/offerings/{$offering->id}", ['title_fr' => 'Modifie'])
            ->assertOk()
            ->assertJsonPath('data.title_fr', 'Modifie');
    }

    public function test_non_owner_cannot_update_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $other = ShareOffering::factory()->create(); // different company

        $this->putJson("/api/v1/offerings/{$other->id}", ['title_fr' => 'Hijack'])
            ->assertForbidden();
    }

    public function test_owner_can_soft_delete_an_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->deleteJson("/api/v1/offerings/{$offering->id}")->assertNoContent();
        $this->assertSoftDeleted('share_offerings', ['id' => $offering->id]);
    }

    public function test_owner_can_upload_an_offering_document(): void
    {
        Storage::fake('local');
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->postJson("/api/v1/offerings/{$offering->id}/documents", [
            'type'     => 'prospectus',
            'title_fr' => 'Prospectus',
            'file'     => UploadedFile::fake()->create('prospectus.pdf', 200, 'application/pdf'),
        ])->assertCreated();

        $this->assertDatabaseHas('offering_documents', [
            'offering_id' => $offering->id, 'type' => 'prospectus',
        ]);
    }

    public function test_owner_can_submit_offering_to_cmf(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->create(['company_id' => $company->id]);

        $this->postJson("/api/v1/offerings/{$offering->id}/submit-cmf")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_cmf');
    }

    public function test_cmf_reviewer_can_approve_a_pending_offering(): void
    {
        Passport::actingAs($this->cmfReviewer(), []);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-approve", [
            'notes' => 'Looks good.',
        ])->assertOk()->assertJsonPath('data.status', 'cmf_approved');

        $this->assertDatabaseHas('cmf_approvals', [
            'offering_id' => $offering->id, 'decision' => 'approved',
        ]);
    }

    public function test_non_reviewer_cannot_approve_offering(): void
    {
        $company = Company::factory()->create();
        Passport::actingAs($this->owner($company), ['companies:write']);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-approve")
            ->assertForbidden();
    }

    public function test_cmf_reviewer_can_reject_a_pending_offering(): void
    {
        Passport::actingAs($this->cmfReviewer(), []);
        $offering = ShareOffering::factory()->pendingCmf()->create();

        $this->postJson("/api/v1/admin/offerings/{$offering->id}/cmf-reject", [
            'reason' => 'Incomplete prospectus.',
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('cmf_approvals', [
            'offering_id' => $offering->id, 'decision' => 'rejected',
        ]);
    }
}
