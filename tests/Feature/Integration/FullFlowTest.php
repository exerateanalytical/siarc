<?php

namespace Tests\Feature\Integration;

use App\Modules\Auth\Models\User;
use App\Modules\Directory\Models\Company;
use App\Modules\Trading\Models\ShareOffering;
use App\Modules\Verification\Models\VerificationTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * End-to-end integration tests exercising complete business flows
 * across multiple modules (Auth, Directory, Verification, Trading,
 * Investors, Payments, Admin).
 */
class FullFlowTest extends TestCase
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

    /**
     * Full investor flow: register → browse companies → view company →
     * view open offering → pledge.
     */
    public function test_investor_full_flow(): void
    {
        // 1. Register as investor
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'first_name'            => 'Marie',
            'last_name'             => 'Fotso',
            'email'                 => 'marie@investor.cm',
            'phone'                 => '+237690000001',
            'password'              => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
        ]);
        $registerResponse->assertStatus(201);
        $this->assertNotEmpty($registerResponse->json('data.token'));

        // 2. Browse companies (public)
        $this->getJson('/api/v1/companies')->assertStatus(200);

        // 3. View a single company (public)
        $company = Company::factory()->create();
        $this->getJson("/api/v1/companies/{$company->id}")->assertStatus(200);

        // 4. View an open offering (public)
        $offering = ShareOffering::factory()->open()->create([
            'company_id'     => $company->id,
            'min_investment' => 10000,
        ]);
        $this->getJson("/api/v1/offerings/{$offering->id}")->assertStatus(200);

        // 5. Make a pledge (as the authenticated investor)
        $user = User::where('email', 'marie@investor.cm')->firstOrFail();
        $user->assignRole('investor');
        Passport::actingAs($user, ['investor:pledge']);

        $pledgeResponse = $this->postJson("/api/v1/offerings/{$offering->id}/pledges", [
            'amount'           => 500000,
            'shares_requested' => 100,
        ]);
        $pledgeResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('investment_pledges', [
            'offering_id' => $offering->id,
            'investor_id' => $user->id,
            'amount'      => 500000,
        ]);
    }

    /**
     * Company owner flow: create company → submit verification →
     * create share offering (draft).
     */
    public function test_company_owner_full_flow(): void
    {
        $this->seedTiers();

        $owner = User::factory()->create(['status' => 'active']);
        $owner->assignRole('company_owner');
        Passport::actingAs($owner, ['companies:write', 'companies:verify']);

        // 1. Create a company (creator becomes owner member automatically)
        $companyResponse = $this->postJson('/api/v1/companies', [
            'name'           => 'Agrobusiness Cameroun SA',
            'legal_form'     => 'sa',
            'description_fr' => 'Société agro-industrielle.',
            'phone'          => '+237677000001',
            'email'          => 'contact@agrobiz.cm',
        ]);
        $companyResponse->assertStatus(201);
        $companyId = $companyResponse->json('data.id');
        $this->assertNotEmpty($companyId);

        // 2. Submit the company for verification
        $verifyResponse = $this->postJson("/api/v1/companies/{$companyId}/verification/submit", [
            'tier_requested' => 'basic',
        ]);
        $verifyResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.tier_requested', 'basic');

        // 3. Create a share offering (draft)
        $offeringResponse = $this->postJson('/api/v1/offerings', [
            'company_id'      => $companyId,
            'title_fr'        => 'Levée de fonds Série A',
            'title_en'        => 'Series A raise',
            'summary_fr'      => 'Résumé de l\'offre.',
            'instrument_type' => 'ordinary_shares',
            'target_amount'   => 50000000,
            'share_price'     => 5000,
            'total_shares'    => 10000,
            'equity_offered'  => 20.0,
            'min_investment'  => 100000,
        ]);
        $offeringResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.title_fr', 'Levée de fonds Série A');

        $this->assertDatabaseHas('share_offerings', [
            'company_id' => $companyId,
            'status'     => 'draft',
        ]);
    }

    /**
     * Admin/reviewer flow: view dashboard → list verifications → list users.
     */
    public function test_admin_review_flow(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('govt_reviewer');
        Passport::actingAs($admin, ['admin:dashboard', 'admin:compliance']);

        // 1. View admin dashboard
        $this->getJson('/api/v1/admin/dashboard')->assertStatus(200);

        // 2. List pending verifications
        $this->getJson('/api/v1/admin/verifications')->assertStatus(200);

        // 3. List users
        $this->getJson('/api/v1/admin/users')->assertStatus(200);
    }

    /**
     * Payment flow: initiate payment → assert fee breakdown → view history.
     */
    public function test_payment_flow(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Passport::actingAs($user, ['payments:initiate', 'payments:view']);

        // 1. Initiate a 200,000 XAF payment.
        //    Platform fee 2.5% = 5,000 XAF.
        //    VAT 19.25% of 5,000 = 962.5 → rounds to 963 XAF.
        //    Net = 200,000 - (5,000 + 963) = 194,037 XAF.
        $response = $this->postJson('/api/v1/payments/initiate', [
            'amount_xaf' => 200000,
            'provider'   => 'mtn_momo',
            'phone'      => '+237677000000',
            'purpose'    => 'pledge_payment',
        ]);

        $response->assertStatus(201);
        $fees = $response->json('data.fee_breakdown');
        $this->assertEquals(200000, $fees['gross_amount_xaf']);
        $this->assertEquals(5000,   $fees['platform_fee_xaf']); // 2.5%
        $this->assertEquals(963,    $fees['vat_xaf']);          // 19.25% of 5000, rounded
        $this->assertEquals(5963,   $fees['total_fee_xaf']);
        $this->assertEquals(194037, $fees['net_amount_xaf']);

        // 2. View payment history
        $this->getJson('/api/v1/payments')->assertStatus(200);
    }
}
