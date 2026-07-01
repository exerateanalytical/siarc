<?php

namespace Tests\Feature\Admin;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }

    private function seedSetting(string $key = 'platform_name', string $value = 'Cameroon Company'): void
    {
        DB::table('system_settings')->insertOrIgnore([
            'key'        => $key,
            'value'      => $value,
            'type'       => 'string',
            'group'      => 'general',
            'is_public'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_super_admin_can_view_dashboard_stats(): void
    {
        Passport::actingAs($this->admin());

        $this->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['users', 'companies', 'offerings', 'pending_verifications']]);
    }

    public function test_admin_can_list_all_users(): void
    {
        Passport::actingAs($this->admin());
        User::factory()->count(3)->create();

        $this->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    }

    public function test_admin_can_suspend_a_user(): void
    {
        Passport::actingAs($this->admin());
        $target = User::factory()->create(['status' => 'active']);

        $this->postJson("/api/v1/admin/users/{$target->id}/suspend", ['reason' => 'Fraude'])
            ->assertOk()
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'suspended']);
    }

    public function test_admin_can_activate_a_user(): void
    {
        Passport::actingAs($this->admin());
        $target = User::factory()->create(['status' => 'suspended']);

        $this->postJson("/api/v1/admin/users/{$target->id}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => 'active']);
    }

    public function test_admin_can_get_system_settings(): void
    {
        Passport::actingAs($this->admin());
        $this->seedSetting();

        $this->getJson('/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.platform_name.key', 'platform_name');
    }

    public function test_admin_can_update_system_setting(): void
    {
        Passport::actingAs($this->admin());
        $this->seedSetting('platform_name', 'Old Name');

        $this->putJson('/api/v1/admin/settings/platform_name', ['value' => 'New Name'])
            ->assertOk();

        $this->assertDatabaseHas('system_settings', ['key' => 'platform_name', 'value' => 'New Name']);
    }

    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('investor');
        Passport::actingAs($user);

        $this->getJson('/api/v1/admin/dashboard')->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_admin_endpoints(): void
    {
        $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
    }
}
