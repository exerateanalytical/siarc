<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_fee_pct',         'value' => '2.5',    'type' => 'decimal', 'group' => 'payments',    'is_public' => false],
            ['key' => 'vat_pct',                  'value' => '19.25',  'type' => 'decimal', 'group' => 'payments',    'is_public' => false],
            ['key' => 'min_pledge_amount',         'value' => '10000',  'type' => 'integer', 'group' => 'trading',     'is_public' => true],
            ['key' => 'max_pledge_amount',         'value' => '0',      'type' => 'integer', 'group' => 'trading',     'is_public' => true],  // 0 = unlimited
            ['key' => 'otp_expiry_minutes',        'value' => '10',     'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'max_login_attempts',        'value' => '5',      'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'lockout_minutes',           'value' => '30',     'type' => 'integer', 'group' => 'auth',        'is_public' => false],
            ['key' => 'default_locale',            'value' => 'fr',     'type' => 'string',  'group' => 'app',         'is_public' => true],
            ['key' => 'maintenance_mode',          'value' => 'false',  'type' => 'boolean', 'group' => 'app',         'is_public' => true],
            ['key' => 'registration_open',         'value' => 'true',   'type' => 'boolean', 'group' => 'app',         'is_public' => true],
            ['key' => 'cmf_required_for_offering', 'value' => 'true',   'type' => 'boolean', 'group' => 'trading',     'is_public' => false],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->insertOrIgnore(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
