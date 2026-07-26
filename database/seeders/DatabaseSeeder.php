<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Siac\SiacSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: the platform RBAC (roles + permissions) is seeded by SiacSeeder ->
        // SiacRolesSeeder under the 'sanctum' guard, plus the permissions-catalog
        // migration. The old RolesAndPermissionsSeeder seeded a divergent, unused
        // 'api'-guard fintech RBAC and has been removed to keep one source of truth.
        // TaxonomySeeder (regions/cities/industries/verification_tiers against a
        // schema that no longer exists) was superseded by SiacSeeder's own
        // regions/taxonomy seeders and has been removed for the same reason.
        $this->call([
            SystemSettingsSeeder::class,
        ]);

        $this->call(SiacSeeder::class);
    }
}
