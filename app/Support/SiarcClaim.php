<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Matches a newly registered member to their imported SIARC 2026 profile.
 *
 * 510 artisans were loaded from the competition dataset as unpublished shops
 * owned by placeholder accounts that nobody can sign into. When the real artisan
 * registers, this finds the profile that is plainly theirs and hands it over.
 *
 * Deliberately conservative. A wrong match would give someone another artisan's
 * identity, their commune and their registration number, so a candidate is only
 * offered on a full normalised name match or an exact phone match — never on a
 * partial or fuzzy one. Everything found is *offered*, never auto-assigned: the
 * member confirms it is them.
 */
class SiarcClaim
{
    /** Same normalisation the importer used, so both sides agree on a name. */
    public static function key(?string $s): string
    {
        $s = strtr((string) $s, [
            "\u{2019}" => "'", "\u{2018}" => "'", "\u{00A0}" => ' ',
        ]);
        $s = \Normalizer::isNormalized($s) ? $s : \Normalizer::normalize($s);
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($s))));
    }

    /** Cameroon numbers, in the same E.164 shape the importer stored. */
    public static function normalisePhone(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '') {
            return null;
        }
        $d = preg_replace('/^237/', '', $d);

        return strlen($d) === 9 ? '+237' . $d : null;
    }

    /**
     * Unclaimed profiles that plausibly belong to this person.
     *
     * @return Collection<int, Business>
     */
    public static function candidatesFor(?string $name, ?string $phone): Collection
    {
        $nameKey = self::key($name);
        $phoneE164 = self::normalisePhone($phone);

        if ($nameKey === '' && $phoneE164 === null) {
            return collect();
        }

        // Names are compared in PHP rather than SQL: the column holds accents and
        // mixed case, and no index would help across 510 rows anyway.
        return Business::whereNotNull('siarc_code')
            ->whereNull('claimed_at')
            ->get()
            ->filter(function (Business $b) use ($nameKey, $phoneE164) {
                if ($phoneE164 !== null && $b->phone === $phoneE164) {
                    return true;
                }

                return $nameKey !== '' && self::key($b->name_fr) === $nameKey;
            })
            ->values();
    }

    /**
     * Hand a profile to its artisan.
     *
     * Re-points ownership, stamps the claim, and deletes the placeholder account
     * the importer created — leaving it behind would be a second, password-less
     * account carrying this person's name.
     *
     * The profile stays a draft. Publishing is the artisan's decision, made from
     * their dashboard once they have checked what we typed in for them.
     */
    public static function assign(Business $business, string $userId): bool
    {
        if ($business->claimed_at !== null || $business->siarc_code === null) {
            return false;
        }

        return DB::transaction(function () use ($business, $userId) {
            $placeholderId = $business->user_id;

            $business->update([
                'user_id'    => $userId,
                'claimed_at' => now(),
            ]);

            // Only ever remove the import's own placeholder: no email, no
            // password anyone holds, and no other shop attached to it. A
            // soft-deleted row is NOT a placeholder — it is the anonymised
            // tombstone of a member who deleted their account (which also has
            // email NULL), still referenced by audit rows, reviews and the
            // certificate register, and must never be hard-deleted.
            if ($placeholderId && $placeholderId !== $userId) {
                $placeholder = DB::table('users')->where('id', $placeholderId)->first();
                $stillOwns = Business::where('user_id', $placeholderId)->exists();

                if ($placeholder && $placeholder->email === null && $placeholder->deleted_at === null && ! $stillOwns) {
                    DB::table('model_has_roles')->where('model_id', $placeholderId)->delete();
                    DB::table('users')->where('id', $placeholderId)->delete();
                }
            }

            return true;
        });
    }
}
