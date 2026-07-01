<?php

namespace Database\Seeders\Siac;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiacAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@artisanatcameroun.cm'],
            [
                'name'                => 'Administrateur SIAC',
                'phone'               => '+237 699 00 00 01',
                'password'            => Hash::make('Admin@SIAC2026'),
                'status'              => 'active',
                'is_email_verified'   => true,
                'language_preference' => 'fr',
            ]
        );
        $admin->assignRole('super_admin');

        $moderator = User::firstOrCreate(
            ['email' => 'moderateur@artisanatcameroun.cm'],
            [
                'name'                => 'Modérateur SIAC',
                'phone'               => '+237 699 00 00 02',
                'password'            => Hash::make('Modo@SIAC2026'),
                'status'              => 'active',
                'is_email_verified'   => true,
                'language_preference' => 'fr',
            ]
        );
        $moderator->assignRole('moderator');

        // Demo system settings
        $settings = [
            ['key' => 'platform_name_fr', 'value' => "Galerie Virtuelle Nationale de l'Artisanat", 'type' => 'string'],
            ['key' => 'platform_name_en', 'value' => 'National Virtual Gallery of Crafts', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => 'contact@artisanatcameroun.cm', 'type' => 'string'],
            ['key' => 'max_products_per_business', 'value' => '50', 'type' => 'integer'],
            ['key' => 'max_gallery_images', 'value' => '20', 'type' => 'integer'],
            ['key' => 'featured_businesses_count', 'value' => '12', 'type' => 'integer'],
            ['key' => 'siac_event_date', 'value' => '2026-11-15', 'type' => 'string'],
            ['key' => 'siac_event_location', 'value' => 'Palais des Congrès, Yaoundé', 'type' => 'string'],
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

        // Certification types
        $certs = [
            ['name_fr' => 'IGP Cameroun', 'name_en' => 'Cameroon PGI', 'issuing_body_fr' => 'MINCOMMERCE', 'industry_id' => null],
            ['name_fr' => 'Label Artisanat Camerounais', 'name_en' => 'Cameroonian Craft Label', 'issuing_body_fr' => 'MINIMIDT', 'industry_id' => null],
            ['name_fr' => 'Certification CICC Cacao Fin', 'name_en' => 'CICC Fine Cocoa Certification', 'issuing_body_fr' => 'CICC', 'industry_id' => null],
            ['name_fr' => 'Certification FAO Pisciculture', 'name_en' => 'FAO Aquaculture Certification', 'issuing_body_fr' => 'FAO/MINEPIA', 'industry_id' => null],
        ];

        foreach ($certs as $c) {
            DB::table('certifications')->insertOrIgnore(array_merge($c, [
                'description_fr' => null, 'description_en' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $this->command->info("  Admin: admin@artisanatcameroun.cm / Admin@SIAC2026");
        $this->command->info("  Moderator: moderateur@artisanatcameroun.cm / Modo@SIAC2026");
    }
}
