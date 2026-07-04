<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Businesses\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the 8 subscriber rows shown on the "Abonnements" admin design
 * (Subscriptions.png) as real users + businesses + subscriptions so the
 * table's page 1 renders the design verbatim while every action button
 * opens a real admin business-detail page. Also points the subscription
 * plans at the design's cropped diamond icons and legend colors.
 * Idempotent — safe to re-run.
 */
class DesignSubscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        // Plans: design's illustrated diamond icons + legend dot colors
        $planPatch = [
            'basic'        => ['sub-plan-basic.png',      '#AAABAE'],
            'standard'     => ['sub-plan-standard.png',   '#D38613'],
            'premium'      => ['sub-plan-premium.png',    '#B66309'],
            'entreprise'   => ['sub-plan-entreprise.png', '#6864BB'],
            'personnalise' => [null,                      '#8C2126'],
        ];
        foreach ($planPatch as $slug => [$icon, $color]) {
            DB::table('subscription_plans')->where('slug', $slug)->update(array_filter([
                'icon'  => $icon,
                'color' => $color,
            ], fn ($v) => $v !== null));
        }

        // [sort, slug, display name, email, phone suffix, vendor_type, plan, status,
        //  amount, started_at, next_payment_at, avatar crop]
        $rows = [
            [1, 'jean-paul-nguemga',     'Jean Paul Nguemga',     'jeanpaul.artisan@gmail.com',      '101', 'artisan',     'premium',    'active',    120000, '2025-05-12 10:30:00', '2026-05-12 10:30:00', 'sub-av-1.png'],
            [2, 'marie-claire-boutique', 'Marie Claire Boutique', 'marieclaire.boutique@gmail.com',  '102', 'cooperative', 'standard',   'active',     60000, '2025-05-11 09:15:00', '2026-05-11 09:15:00', 'sub-av-2.png'],
            [3, 'cameroon-craft-sarl',   'Cameroon Craft SARL',   'contact@camcraft.cm',             '103', 'entreprise',  'entreprise', 'active',    300000, '2025-05-10 16:45:00', '2026-05-10 16:45:00', 'sub-av-3.png'],
            [4, 'sophie-mbarga',         'Sophie Mbarga',         'sophie.mbarga@gmail.com',         '104', 'artisan',     'standard',   'pending',    60000, '2025-05-08 08:20:00', null,                  'sub-av-4.png'],
            [5, 'bamileke-arts',         'Bamileke Arts',         'contact@bamilekearts.cm',         '105', 'cooperative', 'premium',    'expired',   120000, '2024-05-05 14:20:00', '2025-05-05 14:20:00', 'sub-av-5.png'],
            [6, 'atangana-paul',         'Atangana Paul',         'atangana.art@gmail.com',          '106', 'artisan',     'basic',      'cancelled',  30000, '2025-03-03 10:10:00', null,                  'sub-av-6.png'],
            [7, 'duala-creations',       'Duala Creations',       'info@dualacreations.cm',          '107', 'entreprise',  'entreprise', 'active',    300000, '2025-02-02 11:30:00', '2026-02-02 11:30:00', 'sub-av-7.png'],
            [8, 'ndefo-atelier',         'Ndefo Atelier',         'ndefo.atelier@gmail.com',         '108', 'cooperative', 'basic',      'pending',    30000, '2025-05-01 17:50:00', null,                  'sub-av-8.png'],
        ];

        $planIds = DB::table('subscription_plans')->pluck('id', 'slug');
        $landing = public_path('images/landing');
        $storageBase = storage_path('app/public/businesses');

        foreach ($rows as [$sort, $slug, $name, $email, $phoneSuffix, $vendorType, $plan, $status, $amount, $started, $nextPay, $avatar]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'id'                  => (string) \Illuminate\Support\Str::uuid(),
                    'name'                => $name,
                    'phone'               => '+237 670 416 ' . $phoneSuffix,
                    'password'            => Hash::make('password'),
                    'status'              => 'active',
                    'is_email_verified'   => true,
                    'language_preference' => 'fr',
                ]
            );
            // users.id is a uuid but the User model doesn't declare a string key,
            // so $user->id int-casts to 0 — read the real key from the table.
            $userId = DB::table('users')->where('email', $email)->value('id');

            $relPath = "businesses/{$slug}/logo.png";
            $absPath = "{$storageBase}/{$slug}/logo.png";
            if (! File::exists($absPath) && File::exists("{$landing}/{$avatar}")) {
                File::ensureDirectoryExists(dirname($absPath));
                File::copy("{$landing}/{$avatar}", $absPath);
            }

            $business = Business::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id'        => $userId,
                    'name_fr'        => $name,
                    'name_en'        => $name,
                    'logo'           => $relPath,
                    'vendor_type'    => $vendorType,
                    'status'         => 'draft', // subscriber profile, not a public directory entry
                    'deleted_at'     => null,
                ]
            );

            DB::table('business_subscriptions')->updateOrInsert(
                ['business_id' => $business->id],
                [
                    'subscription_plan_id' => $planIds[$plan],
                    'status'               => $status,
                    'amount'               => $amount,
                    'started_at'           => $started,
                    'next_payment_at'      => $nextPay,
                    'sort_order'           => $sort,
                    'created_at'           => $started,
                    'updated_at'           => $started,
                ]
            );
        }

        $this->command?->info('Design subscriptions seeded: ' . count($rows));
    }
}
