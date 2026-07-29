<?php

namespace App\Http\Controllers;

use App\Modules\Admin\Models\AuditLog;
use App\Modules\Admin\Services\SuperAdminGuard;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\AccountDeletionService;
use App\Modules\Businesses\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Bulk actions for the admin list screens (users, businesses). Every action
 * here:
 *
 *  - is capped at self::MAX_BATCH rows per submit (a select-all-of-hundreds
 *    accident cannot fire unbounded — the admin is told to page through);
 *  - excludes the last remaining super_admin and, for account deletion, the
 *    acting admin's own row (self-delete keeps its dedicated password +
 *    confirmation-phrase flow; it is not reachable through this bulk path);
 *  - writes exactly one audit_logs row per batch summarising actor, action,
 *    affected count and the target ids — not one row per record;
 *  - reuses existing single-record semantics: bulk user deletion calls the
 *    same AccountDeletionService the self-delete flow was refactored to use,
 *    never a second deletion code path.
 */
class AdminBulkController extends Controller
{
    public const MAX_BATCH = 200;

    private function requireAdmin(Request $request): array|RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser || empty($siacUser['is_admin'])) {
            return redirect('/login');
        }
        return $siacUser;
    }

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    /**
     * Validate the common shape (bulk_action + ids[]) and enforce the batch cap.
     * Returns either the validated ids array or a RedirectResponse to bail out with.
     */
    private function resolveIds(Request $request, string $isFr): array|RedirectResponse
    {
        $ids = array_values(array_unique(array_filter((array) $request->input('ids', []))));

        if (empty($ids)) {
            return back()->withErrors(['bulk' => $isFr ? 'Aucune ligne sélectionnée.' : 'No rows selected.']);
        }

        if (count($ids) > self::MAX_BATCH) {
            return back()->withErrors(['bulk' => $isFr
                ? sprintf('Trop de lignes sélectionnées (%d). Maximum %d par lot : filtrez ou paginez pour traiter le reste.', count($ids), self::MAX_BATCH)
                : sprintf('Too many rows selected (%d). Maximum %d per batch: filter or page through to process the rest.', count($ids), self::MAX_BATCH)]);
        }

        return $ids;
    }

    // ─────────────────────────────────────────
    // Users
    // ─────────────────────────────────────────
    public function usersBulk(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;
        $isFr = $this->lang($request) === 'fr';

        $data = $request->validate([
            'bulk_action' => ['required', 'in:role,status,delete'],
            'value'       => ['nullable', 'string', 'max:100'],
        ]);

        $ids = $this->resolveIds($request, $isFr);
        if ($ids instanceof RedirectResponse) return $ids;

        $users = User::whereIn('id', $ids)->whereNull('deleted_at')->get();

        // Rows a batch is never allowed to touch, whatever the action:
        // the last active super_admin, and the acting admin themselves.
        $skippedSuperAdmin = [];
        $skippedSelf = [];
        $targets = $users->filter(function ($u) use ($admin, &$skippedSuperAdmin, &$skippedSelf) {
            if ($u->id === $admin['id']) {
                $skippedSelf[] = $u->id;
                return false;
            }
            if (SuperAdminGuard::isLastSuperAdmin($u->id)) {
                $skippedSuperAdmin[] = $u->id;
                return false;
            }
            return true;
        });

        if ($targets->isEmpty()) {
            return back()->withErrors(['bulk' => $isFr
                ? 'Aucune ligne éligible : sélection limitée à vous-même et/ou au dernier super-administrateur.'
                : 'No eligible rows: selection was limited to yourself and/or the last super administrator.']);
        }

        $affected = 0;
        $targetIds = $targets->pluck('id')->all();

        switch ($data['bulk_action']) {
            case 'role':
                $assignableRoles = ['buyer', 'business_owner', 'regional_rep', 'technical_reviewer', 'moderator', 'admin'];
                if (! in_array($data['value'] ?? null, $assignableRoles, true)) {
                    return back()->withErrors(['bulk' => $isFr ? 'Rôle invalide.' : 'Invalid role.']);
                }
                DB::transaction(function () use ($targets, $data, &$affected) {
                    foreach ($targets as $user) {
                        if ($data['value'] === 'buyer') {
                            $user->syncRoles([]);
                        } else {
                            $user->syncRoles([$data['value']]);
                        }
                        if ($data['value'] !== 'regional_rep') {
                            $user->update(['assigned_region_id' => null]);
                        }
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'user.bulk_role_changed', 'user', null, null, [
                    'role'                  => $data['value'],
                    'count'                 => $affected,
                    'target_user_ids'       => $targetIds,
                    'skipped_last_super_admin' => $skippedSuperAdmin,
                    'skipped_self'          => $skippedSelf,
                ]);
                break;

            case 'status':
                if (! in_array($data['value'] ?? null, ['active', 'suspended'], true)) {
                    return back()->withErrors(['bulk' => $isFr ? 'Statut invalide.' : 'Invalid status.']);
                }
                DB::transaction(function () use ($targets, $data, &$affected) {
                    foreach ($targets as $user) {
                        $user->update(['status' => $data['value']]);
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'user.bulk_status_changed', 'user', null, null, [
                    'status'                => $data['value'],
                    'count'                 => $affected,
                    'target_user_ids'       => $targetIds,
                    'skipped_last_super_admin' => $skippedSuperAdmin,
                    'skipped_self'          => $skippedSelf,
                ]);
                break;

            case 'delete':
                $service = new AccountDeletionService();
                DB::transaction(function () use ($targets, $service, &$affected) {
                    foreach ($targets as $user) {
                        $service->anonymiseAndSoftDelete($user);
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'user.bulk_deleted', 'user', null, null, [
                    'count'                 => $affected,
                    'target_user_ids'       => $targetIds,
                    'skipped_last_super_admin' => $skippedSuperAdmin,
                    'skipped_self'          => $skippedSelf,
                ]);
                break;
        }

        $skippedCount = count($skippedSuperAdmin) + count($skippedSelf);
        $msg = $isFr
            ? sprintf('%d utilisateur(s) traité(s).', $affected) . ($skippedCount ? sprintf(' %d ignoré(s) (protégé(s)).', $skippedCount) : '')
            : sprintf('%d user(s) processed.', $affected) . ($skippedCount ? sprintf(' %d skipped (protected).', $skippedCount) : '');

        return back()->with('success', $msg);
    }

    // ─────────────────────────────────────────
    // Businesses
    // ─────────────────────────────────────────
    public function businessesBulk(Request $request): RedirectResponse
    {
        $admin = $this->requireAdmin($request);
        if ($admin instanceof RedirectResponse) return $admin;
        $isFr = $this->lang($request) === 'fr';

        $data = $request->validate([
            'bulk_action' => ['required', 'in:status,category,region,delete'],
            'value'       => ['nullable', 'string', 'max:100'],
        ]);

        $ids = $this->resolveIds($request, $isFr);
        if ($ids instanceof RedirectResponse) return $ids;

        $businesses = Business::whereIn('id', $ids)->get();
        if ($businesses->isEmpty()) {
            return back()->withErrors(['bulk' => $isFr ? 'Aucune ligne éligible.' : 'No eligible rows.']);
        }

        $affected = 0;
        $targetIds = $businesses->pluck('id')->all();

        switch ($data['bulk_action']) {
            case 'status':
                if (! in_array($data['value'] ?? null, ['draft', 'published', 'suspended', 'rejected'], true)) {
                    return back()->withErrors(['bulk' => $isFr ? 'Statut invalide.' : 'Invalid status.']);
                }
                DB::transaction(function () use ($businesses, $data, &$affected) {
                    foreach ($businesses as $b) {
                        $b->update(['status' => $data['value']]);
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'business.bulk_status_changed', 'business', null, null, [
                    'status' => $data['value'], 'count' => $affected, 'target_business_ids' => $targetIds,
                ]);
                break;

            case 'category':
                if (! $data['value'] || ! DB::table('industries')->where('id', $data['value'])->exists()) {
                    return back()->withErrors(['bulk' => $isFr ? 'Catégorie invalide.' : 'Invalid category.']);
                }
                DB::transaction(function () use ($businesses, $data, &$affected) {
                    foreach ($businesses as $b) {
                        $b->update(['industry_id' => $data['value']]);
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'business.bulk_category_changed', 'business', null, null, [
                    'industry_id' => $data['value'], 'count' => $affected, 'target_business_ids' => $targetIds,
                ]);
                break;

            case 'region':
                if (! $data['value'] || ! DB::table('regions')->where('id', $data['value'])->exists()) {
                    return back()->withErrors(['bulk' => $isFr ? 'Région invalide.' : 'Invalid region.']);
                }
                DB::transaction(function () use ($businesses, $data, &$affected) {
                    foreach ($businesses as $b) {
                        $b->update(['region_id' => $data['value']]);
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'business.bulk_region_changed', 'business', null, null, [
                    'region_id' => $data['value'], 'count' => $affected, 'target_business_ids' => $targetIds,
                ]);
                break;

            case 'delete':
                // Soft delete only (SoftDeletes on Business) — reversible, never
                // a hard delete. The owning user account is untouched: this
                // removes the listing, not the person.
                DB::transaction(function () use ($businesses, &$affected) {
                    foreach ($businesses as $b) {
                        $b->update(['status' => 'draft']);
                        $b->delete();
                        $affected++;
                    }
                });
                AuditLog::record($admin['id'], 'business.bulk_deleted', 'business', null, null, [
                    'count' => $affected, 'target_business_ids' => $targetIds,
                ]);
                break;
        }

        $msg = $isFr ? sprintf('%d entreprise(s) traitée(s).', $affected) : sprintf('%d business(es) processed.', $affected);

        return back()->with('success', $msg);
    }
}
