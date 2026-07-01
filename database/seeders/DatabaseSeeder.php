<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Siac\SiacSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            TaxonomySeeder::class,
            SystemSettingsSeeder::class,
            SampleCompaniesSeeder::class,
            SampleOfferingsSeeder::class,
        ]);

        // SIAC platform — the active product. Runs after the legacy seeders above
        // (kept only because some still-reachable legacy pages depend on that data).
        $this->call(SiacSeeder::class);
    }
}
