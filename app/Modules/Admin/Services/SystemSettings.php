<?php

namespace App\Modules\Admin\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Admin-editable platform settings backed by the system_settings table.
 *
 * Values set here override .env defaults: resolve credentials with
 * SystemSettings::get('twilio.sid') ?: config('services.twilio.sid').
 * Secret rows (is_secret) are stored Crypt-encrypted under APP_KEY.
 */
class SystemSettings
{
    private const CACHE_KEY = 'system_settings.all';
    private const CACHE_TTL = 300;

    /** Typed value for a key, or $default when absent/empty. */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::all()[$key] ?? null;
        if ($row === null || $row['value'] === null || $row['value'] === '') {
            return $default;
        }

        return $row['value'];
    }

    /** All settings, keyed: ['key' => ['value' => cast+decrypted, 'type', 'group', 'is_secret']] */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return DB::table('system_settings')->get()->mapWithKeys(function ($row) {
                $value = $row->value;
                if ($row->is_secret && $value !== null && $value !== '') {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (DecryptException) {
                        $value = null; // APP_KEY rotated: treat as unset
                    }
                }

                $value = match ($row->type) {
                    'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => $value === null ? null : (int) $value,
                    'json'    => $value === null ? null : json_decode($value, true),
                    default   => $value,
                };

                return [$row->key => [
                    'value'     => $value,
                    'type'      => $row->type,
                    'group'     => $row->group,
                    'is_secret' => (bool) $row->is_secret,
                ]];
            })->all();
        });
    }

    /** Upsert one setting. Secret values are encrypted before storage. */
    public static function set(string $key, ?string $value, string $type = 'string', string $group = 'general', bool $isSecret = false, ?string $updatedBy = null): void
    {
        if ($isSecret && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        $attrs = [
            'value'      => $value,
            'type'       => $type,
            'group'      => $group,
            'is_secret'  => $isSecret,
            'updated_by' => $updatedBy,
            'updated_at' => now(),
        ];

        if (DB::table('system_settings')->where('key', $key)->exists()) {
            DB::table('system_settings')->where('key', $key)->update($attrs);
        } else {
            DB::table('system_settings')->insert($attrs + ['key' => $key, 'created_at' => now()]);
        }

        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
