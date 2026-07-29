<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The single account-deletion code path — extracted from
 * SecurityWebController::deleteAccount() so the admin bulk-delete action
 * (AdminBulkController::usersBulk) reuses the exact same semantics instead of
 * a second, drifting implementation. Behaviour is unchanged from the
 * self-delete flow it was lifted from:
 *
 *  - Every business the account owns is set to `draft` so it stops appearing
 *    live but stays verifiable.
 *  - A SIARC-imported profile the member had claimed is un-claimed
 *    (claimed_at cleared) so it returns to the unclaimed pool.
 *  - Passkeys, personal access tokens and role assignments are removed.
 *  - The `users` row is anonymised (name/email/phone/avatar cleared, password
 *    replaced with an unusable random hash, 2FA cleared) and soft-deleted via
 *    `deleted_at` / `status = 'deleted'`.
 *
 * Callers are responsible for: authorisation, the last-super-admin guard, and
 * writing the audit log entry (the shape differs between a single self-delete
 * and a batch, so this service does not write it).
 */
class AccountDeletionService
{
    /**
     * @param object $user Any object with an ->id — SecurityWebController's
     *   self-delete looks up the row via DB::table()->first() (a stdClass),
     *   while AdminBulkController passes an Eloquent User model. Only ->id is
     *   read here, so both work without forcing either caller to change how
     *   it fetches the row.
     */
    public function anonymiseAndSoftDelete(object $user): array
    {
        $businessesDrafted = 0;
        $siarcUnclaimed = 0;

        $owned = DB::table('businesses')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get(['id', 'status', 'siarc_code', 'claimed_at']);

        foreach ($owned as $b) {
            if ($b->siarc_code !== null && $b->claimed_at !== null) {
                DB::table('businesses')->where('id', $b->id)->update([
                    'claimed_at' => null,
                    'status'     => 'draft',
                    'updated_at' => now(),
                ]);
                $siarcUnclaimed++;
            } else {
                DB::table('businesses')->where('id', $b->id)->update([
                    'status'     => 'draft',
                    'updated_at' => now(),
                ]);
                $businessesDrafted++;
            }
        }

        DB::table('user_passkeys')->where('user_id', $user->id)->delete();
        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->where('tokenable_id', $user->id)
            ->delete();
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();

        DB::table('users')->where('id', $user->id)->update([
            'name'                      => '',
            'email'                     => null,
            'phone'                     => null,
            'avatar'                    => null,
            'password'                  => Hash::make(Str::random(48)),
            'remember_token'            => null,
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
            'two_factor_channel'        => null,
            'last_login_ip'             => null,
            'is_email_verified'         => 0,
            'is_phone_verified'         => 0,
            'status'                    => 'deleted',
            'deleted_at'                => now(),
            'updated_at'                => now(),
        ]);

        return [
            'businesses_set_draft' => $businessesDrafted,
            'siarc_unclaimed'      => $siarcUnclaimed,
        ];
    }
}
