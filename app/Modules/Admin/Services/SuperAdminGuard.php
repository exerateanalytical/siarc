<?php

namespace App\Modules\Admin\Services;

use Illuminate\Support\Facades\DB;

/**
 * The one place that answers "would touching this user leave the platform
 * with zero active super_admins?" — used by the single-record role/status
 * change guards (AdminWebController), the self-delete guard
 * (SecurityWebController), and every bulk action (AdminBulkController) so a
 * batch operation can never do in aggregate what a single edit already
 * refuses to do one row at a time.
 */
class SuperAdminGuard
{
    public static function isLastSuperAdmin(string $userId): bool
    {
        $isSuper = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $userId)
            ->where('roles.name', 'super_admin')
            ->exists();
        if (! $isSuper) {
            return false;
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('roles.name', 'super_admin')
            ->where('users.id', '!=', $userId)
            ->whereNull('users.deleted_at')
            ->where('users.status', 'active')
            ->count() === 0;
    }
}
