<?php

namespace Database\Seeders\Siac;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds the single platform administrator plus the reference rows a fresh
 * install cannot boot without (system settings, feature flags, certification
 * types).
 *
 * No demo people, businesses or products are created here. The administrator
 * credentials come from the environment so a deployment never ships with a
 * password that is written down in the repository; the defaults exist only so
 * `db:seed` works on a developer machine.
 */
class SiacAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = strtolower(trim((string) env('ADMIN_EMAIL', 'admin@artisanhub237.com')));
        $password = (string) env('ADMIN_PASSWORD', '');
        $generated = false;

        if ($password === '') {
            $password  = 'Admin@' . Str::random(16);
            $generated = true;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'                => (string) env('ADMIN_NAME', 'Administrateur Artisan Hub 237'),
                'phone'               => env('ADMIN_PHONE'),
                'password'            => Hash::make($password),
                'status'              => 'active',
                'is_email_verified'   => true,
                'language_preference' => 'fr',
            ]
        );
        $admin->assignRole('super_admin');

        $contactEmail = (string) config('legal.company.email', 'contact@artisanhub237.com');

        $settings = [
            ['key' => 'platform_name_fr', 'value' => 'Artisan Hub 237', 'type' => 'string'],
            ['key' => 'platform_name_en', 'value' => 'Artisan Hub 237', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => $contactEmail, 'type' => 'string'],
            ['key' => 'max_products_per_business', 'value' => '50', 'type' => 'integer'],
            ['key' => 'max_gallery_images', 'value' => '20', 'type' => 'integer'],
            ['key' => 'featured_businesses_count', 'value' => '12', 'type' => 'integer'],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->insertOrIgnore(array_merge($s, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Feature flags
        $flags = [
            ['key' => 'messaging_enabled',  'is_enabled' => true,  'description_fr' => 'Messagerie acheteur-vendeur', 'description_en' => 'Enable buyer-business messaging'],
            ['key' => 'registration_open',  'is_enabled' => true,  'description_fr' => 'Inscription des entreprises ouverte', 'description_en' => 'Allow new business registrations'],
            ['key' => 'api_product_enabled','is_enabled' => true,  'description_fr' => 'API en tant que produit', 'description_en' => 'Enable API-as-a-Product program'],
            ['key' => 'reverb_broadcast',   'is_enabled' => false, 'description_fr' => 'Diffusion WebSocket temps réel', 'description_en' => 'Enable real-time WebSocket broadcasts'],
        ];

        foreach ($flags as $f) {
            DB::table('feature_flags')->insertOrIgnore(array_merge($f, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        // Certification types (real Cameroonian / international schemes)
        $certs = [
            ['name_fr' => 'IGP Cameroun', 'name_en' => 'Cameroon PGI', 'issuing_body_fr' => 'MINCOMMERCE', 'industry_id' => null],
            ['name_fr' => 'Label Artisanat Camerounais', 'name_en' => 'Cameroonian Craft Label', 'issuing_body_fr' => 'MINIMIDT', 'industry_id' => null],
            ['name_fr' => 'Certification CICC Cacao Fin', 'name_en' => 'CICC Fine Cocoa Certification', 'issuing_body_fr' => 'CICC', 'industry_id' => null],
            ['name_fr' => 'Certification FAO Pisciculture', 'name_en' => 'FAO Aquaculture Certification', 'issuing_body_fr' => 'FAO/MINEPIA', 'industry_id' => null],
        ];

        // `certifications` has no unique index on name_fr, so insertOrIgnore would
        // duplicate the catalogue on every re-run — check first instead.
        foreach ($certs as $c) {
            if (DB::table('certifications')->where('name_fr', $c['name_fr'])->exists()) {
                continue;
            }
            DB::table('certifications')->insert(array_merge($c, [
                'description_fr' => null, 'description_en' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $this->command->info("  Administrator: {$email}");

        if ($admin->wasRecentlyCreated && $generated) {
            $this->command->warn("  Generated password (shown once): {$password}");
            $this->command->warn('  Set ADMIN_EMAIL / ADMIN_PASSWORD in .env before seeding to choose your own.');
        } elseif (! $admin->wasRecentlyCreated) {
            $this->command->line('  (already existed — password left untouched)');
        }
    }
}
