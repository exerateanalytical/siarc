<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminBulkController;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Bulk select + bulk actions on the admin users and businesses lists: the
 * batch-size cap, the last-super-admin guard applied under a batch (not just
 * a single edit), reuse of the exact single-record deletion semantics, and
 * one audit_logs row per batch rather than one per record.
 */
class AdminBulkActionsTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function adminSession(?User $admin = null): array
    {
        $admin = $admin ?? $this->makeUser();

        return ['siac_user' => [
            'id'       => $admin->id,
            'name'     => $admin->name,
            'email'    => $admin->email,
            'role'     => 'super_admin',
            'is_admin' => true,
        ]];
    }

    private function seedRole(string $name): int
    {
        $existing = DB::table('roles')->where('name', $name)->where('guard_name', 'sanctum')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => $name, 'guard_name' => 'sanctum',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function grantRole(User $user, int $roleId): void
    {
        DB::table('model_has_roles')->insert([
            'role_id'    => $roleId,
            'model_type' => 'App\Modules\Auth\Models\User',
            'model_id'   => $user->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Users — bulk status / role / delete
    // ─────────────────────────────────────────────────────────────────────

    public function test_bulk_suspend_updates_status_for_every_selected_user(): void
    {
        $admin = $this->makeUser();
        $targets = User::factory()->count(3)->create(['status' => 'active']);

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'status',
            'value'       => 'suspended',
            'ids'         => $targets->pluck('id')->all(),
        ]);

        $res->assertSessionHasNoErrors();
        foreach ($targets as $t) {
            $this->assertSame('suspended', DB::table('users')->where('id', $t->id)->value('status'));
        }

        $audit = DB::table('audit_logs')->where('action', 'user.bulk_status_changed')->latest('id')->first();
        $this->assertNotNull($audit);
        $new = json_decode($audit->new_values, true);
        $this->assertSame(3, $new['count']);
        $this->assertSame($targets->pluck('id')->sort()->values()->all(), collect($new['target_user_ids'])->sort()->values()->all());
    }

    public function test_bulk_role_change_syncs_roles_for_every_selected_user(): void
    {
        $admin = $this->makeUser();
        $targets = User::factory()->count(2)->create();

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'role',
            'value'       => 'moderator',
            'ids'         => $targets->pluck('id')->all(),
        ]);

        $res->assertSessionHasNoErrors();
        foreach ($targets as $t) {
            $this->assertTrue($t->fresh()->hasRole('moderator'));
        }
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.bulk_role_changed']);
    }

    public function test_bulk_delete_reuses_the_self_delete_anonymisation_semantics(): void
    {
        $admin = $this->makeUser();
        $target = $this->makeUser(['name' => 'Cible Test', 'email' => 'cible@example.cm', 'phone' => '+237690000099']);
        $own = $this->makeBusiness($target, ['status' => 'published']);
        $siarc = $this->makeBusiness($target, [
            'status' => 'draft', 'siarc_code' => 'SIARC-9001', 'claimed_at' => now()->subDay(),
        ]);

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'delete',
            'ids'         => [$target->id],
        ]);

        $res->assertSessionHasNoErrors();

        $row = DB::table('users')->where('id', $target->id)->first();
        // Same shape as self-delete: anonymised + soft-deleted, not hard-deleted.
        $this->assertSame('', $row->name);
        $this->assertNull($row->email);
        $this->assertNull($row->phone);
        $this->assertSame('deleted', $row->status);
        $this->assertNotNull($row->deleted_at);

        $this->assertSame('draft', DB::table('businesses')->where('id', $own->id)->value('status'));
        $siarcRow = DB::table('businesses')->where('id', $siarc->id)->first();
        $this->assertNull($siarcRow->claimed_at);
        $this->assertSame('draft', $siarcRow->status);

        // One batch audit row (not a per-user 'user.self_deleted' row — that
        // action name stays exclusive to the self-service flow).
        $audit = DB::table('audit_logs')->where('action', 'user.bulk_deleted')->first();
        $this->assertNotNull($audit);
        $this->assertSame($admin->id, $audit->user_id);
        $new = json_decode($audit->new_values, true);
        $this->assertSame(1, $new['count']);
        $this->assertSame([$target->id], $new['target_user_ids']);
    }

    public function test_bulk_delete_never_touches_the_last_super_admin(): void
    {
        $roleId = $this->seedRole('super_admin');
        $admin = $this->makeUser();
        $boss = $this->makeUser();
        $this->grantRole($boss, $roleId);
        $ordinary = $this->makeUser();

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'delete',
            'ids'         => [$boss->id, $ordinary->id],
        ]);

        $res->assertSessionHasNoErrors();

        // The last super_admin survives untouched...
        $this->assertNull(DB::table('users')->where('id', $boss->id)->value('deleted_at'));
        $this->assertNotNull(DB::table('users')->where('id', $boss->id)->value('email'));
        // ...while the ordinary user in the same batch is still processed.
        $this->assertNotNull(DB::table('users')->where('id', $ordinary->id)->value('deleted_at'));

        $audit = DB::table('audit_logs')->where('action', 'user.bulk_deleted')->first();
        $new = json_decode($audit->new_values, true);
        $this->assertSame([$boss->id], $new['skipped_last_super_admin']);
        $this->assertSame(1, $new['count']);
    }

    public function test_bulk_action_cannot_be_used_to_delete_the_acting_admins_own_account(): void
    {
        $admin = $this->makeUser();
        $other = $this->makeUser();

        $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'delete',
            'ids'         => [$admin->id, $other->id],
        ])->assertSessionHasNoErrors();

        $this->assertNull(DB::table('users')->where('id', $admin->id)->value('deleted_at'));
        $this->assertNotNull(DB::table('users')->where('id', $other->id)->value('deleted_at'));
    }

    public function test_bulk_action_is_capped_and_rejects_an_oversized_batch(): void
    {
        $admin = $this->makeUser();
        $ids = range(1, AdminBulkController::MAX_BATCH + 1);

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'status',
            'value'       => 'suspended',
            'ids'         => $ids,
        ]);

        $res->assertSessionHasErrors('bulk');
        $this->assertSame(0, DB::table('audit_logs')->where('action', 'user.bulk_status_changed')->count());
    }

    public function test_bulk_action_requires_admin_session(): void
    {
        $user = $this->makeUser();
        $target = $this->makeUser();

        $res = $this->withSession(['siac_user' => [
            'id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => 'buyer', 'is_admin' => false,
        ]])->post('/tableau-de-bord/admin/utilisateurs/bulk', [
            'bulk_action' => 'status', 'value' => 'suspended', 'ids' => [$target->id],
        ]);

        $res->assertRedirect('/login');
        $this->assertSame('active', DB::table('users')->where('id', $target->id)->value('status'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Businesses — bulk status / category / region / delete
    // ─────────────────────────────────────────────────────────────────────

    public function test_bulk_business_status_change_updates_every_selected_row(): void
    {
        $admin = $this->makeUser();
        $businesses = collect([$this->makeBusiness(null, ['status' => 'draft']), $this->makeBusiness(null, ['status' => 'draft'])]);

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/entreprises/bulk', [
            'bulk_action' => 'status',
            'value'       => 'published',
            'ids'         => $businesses->pluck('id')->all(),
        ]);

        $res->assertSessionHasNoErrors();
        foreach ($businesses as $b) {
            $this->assertSame('published', DB::table('businesses')->where('id', $b->id)->value('status'));
        }
        $this->assertDatabaseHas('audit_logs', ['action' => 'business.bulk_status_changed']);
    }

    public function test_bulk_business_category_reassignment(): void
    {
        $admin = $this->makeUser();
        $industryId = DB::table('industries')->insertGetId(['name_fr' => 'Test Industrie', 'name_en' => 'Test Industry', 'slug' => 'test-industrie-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        $business = $this->makeBusiness();

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/entreprises/bulk', [
            'bulk_action' => 'category',
            'value'       => (string) $industryId,
            'ids'         => [$business->id],
        ]);

        $res->assertSessionHasNoErrors();
        $this->assertSame($industryId, DB::table('businesses')->where('id', $business->id)->value('industry_id'));
    }

    public function test_bulk_business_delete_is_a_soft_delete_and_sets_draft(): void
    {
        $admin = $this->makeUser();
        $business = $this->makeBusiness(null, ['status' => 'published']);

        $res = $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/entreprises/bulk', [
            'bulk_action' => 'delete',
            'ids'         => [$business->id],
        ]);

        $res->assertSessionHasNoErrors();

        $row = DB::table('businesses')->where('id', $business->id)->first();
        $this->assertNotNull($row->deleted_at);
        $this->assertSame('draft', $row->status);
        // Reversible: the row is soft-deleted, not gone.
        $this->assertNotNull($row);

        $audit = DB::table('audit_logs')->where('action', 'business.bulk_deleted')->first();
        $this->assertNotNull($audit);
    }

    public function test_business_bulk_action_is_capped(): void
    {
        $admin = $this->makeUser();
        $ids = range(1, AdminBulkController::MAX_BATCH + 1);

        $this->withSession($this->adminSession($admin))->post('/tableau-de-bord/admin/entreprises/bulk', [
            'bulk_action' => 'status', 'value' => 'published', 'ids' => $ids,
        ])->assertSessionHasErrors('bulk');
    }
}
