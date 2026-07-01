<?php

namespace Tests\Feature\Directory;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    private function ownerUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('company_owner');

        return $user;
    }

    private function companyPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Douala Tech SARL',
            'trade_name'     => 'DoualaTech',
            'description_fr' => 'Une entreprise technologique.',
            'description_en' => 'A technology company.',
            'legal_form'     => 'sarl',
            'email'          => 'contact@doualatech.cm',
            'phone'          => '+237600000000',
        ], $overrides);
    }

    public function test_public_can_list_companies(): void
    {
        Company::factory()->count(3)->create(['status' => 'active']);

        $response = $this->getJson('/api/v1/companies');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'verification_status']],
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_owner_can_create_a_company(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $response = $this->postJson('/api/v1/companies', $this->companyPayload());

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Douala Tech SARL');

        $this->assertDatabaseHas('companies', ['name' => 'Douala Tech SARL']);
    }

    public function test_creating_a_company_requires_write_scope(): void
    {
        Passport::actingAs($this->ownerUser(), []); // no scope

        $this->postJson('/api/v1/companies', $this->companyPayload())
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_create_a_company(): void
    {
        $this->postJson('/api/v1/companies', $this->companyPayload())
            ->assertUnauthorized();
    }

    public function test_validation_fails_without_required_fields(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $this->postJson('/api/v1/companies', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'legal_form']);
    }

    public function test_public_can_view_a_single_company(): void
    {
        $company = Company::factory()->create(['status' => 'active']);

        $this->getJson("/api/v1/companies/{$company->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $company->id);
    }

    public function test_owner_can_update_their_company(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        $this->putJson("/api/v1/companies/{$company->id}", ['trade_name' => 'NewName'])
            ->assertOk()
            ->assertJsonPath('data.trade_name', 'NewName');
    }

    public function test_non_owner_cannot_update_a_company(): void
    {
        Passport::actingAs($this->ownerUser(), ['companies:write']);

        $company = Company::factory()->create(); // current user is not a member

        $this->putJson("/api/v1/companies/{$company->id}", ['trade_name' => 'Hijack'])
            ->assertForbidden();
    }

    public function test_owner_can_soft_delete_a_company(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        $this->deleteJson("/api/v1/companies/{$company->id}")->assertNoContent();

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_search_returns_matching_companies(): void
    {
        Company::factory()->create(['name' => 'Douala Logistics', 'status' => 'active']);
        Company::factory()->create(['name' => 'Yaounde Foods', 'status' => 'active']);

        $response = $this->getJson('/api/v1/companies?search=Douala');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Douala Logistics'));
    }

    public function test_owner_can_list_and_upload_documents(): void
    {
        $user = $this->ownerUser();
        Passport::actingAs($user, ['companies:write']);

        $company = Company::factory()->create();
        $company->members()->create([
            'user_id' => $user->id, 'role' => 'owner', 'is_active' => true, 'joined_at' => now(),
        ]);

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->postJson("/api/v1/companies/{$company->id}/documents", [
            'type'  => 'rccm',
            'title' => 'RCCM Certificate',
            'file'  => \Illuminate\Http\UploadedFile::fake()->create('rccm.pdf', 120, 'application/pdf'),
        ])->assertCreated();

        $this->getJson("/api/v1/companies/{$company->id}/documents")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
