<?php

namespace Tests\Feature\Compliance;

use App\Modules\Auth\Models\User;
use App\Modules\Compliance\Models\ComplianceAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_admin_can_screen_a_user(): void
    {
        Passport::actingAs($this->admin());
        $subject = User::factory()->create();

        $this->postJson('/api/v1/admin/compliance/screen-user', [
            'user_id' => $subject->id,
            'type'    => 'sanctions',
        ])
            ->assertCreated()
            ->assertJsonPath('data.result', 'clear')
            ->assertJsonPath('data.subject_type', 'user');

        $this->assertDatabaseHas('aml_screenings', [
            'subject_id'   => $subject->id,
            'subject_type' => 'user',
            'result'       => 'clear',
        ]);
    }

    public function test_admin_can_list_compliance_alerts(): void
    {
        Passport::actingAs($this->admin());

        $ruleId = DB::table('compliance_rules')->insertGetId([
            'code'       => 'RULE_TEST',
            'name_fr'    => 'Règle test',
            'category'   => 'aml',
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ComplianceAlert::create([
            'rule_id'      => $ruleId,
            'subject_id'   => (string) \Illuminate\Support\Str::uuid(),
            'subject_type' => 'user',
            'result'       => 'failed',
            'details'      => ['reason' => 'test'],
            'checked_at'   => now(),
        ]);
        ComplianceAlert::create([
            'rule_id'      => $ruleId,
            'subject_id'   => (string) \Illuminate\Support\Str::uuid(),
            'subject_type' => 'user',
            'result'       => 'passed',
            'checked_at'   => now(),
        ]);

        $this->getJson('/api/v1/admin/compliance/alerts')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_file_sar_report(): void
    {
        Passport::actingAs($this->admin());
        $subject = User::factory()->create();

        $this->postJson('/api/v1/admin/compliance/sar', [
            'subject_id'     => $subject->id,
            'subject_type'   => 'user',
            'description_fr' => 'Activité suspecte détectée.',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonStructure(['data' => ['sar_number', 'filed_at']]);

        $this->assertDatabaseHas('suspicious_activity_reports', [
            'subject_id' => $subject->id,
            'status'     => 'submitted',
        ]);
    }

    public function test_non_admin_cannot_access_compliance(): void
    {
        $user = User::factory()->create();
        $user->assignRole('investor');
        Passport::actingAs($user);

        $this->getJson('/api/v1/admin/compliance/alerts')->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_compliance(): void
    {
        $this->getJson('/api/v1/admin/compliance/alerts')->assertUnauthorized();
    }
}
