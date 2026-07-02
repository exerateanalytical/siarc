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
        ]);

        // SIAC platform — the active product
        $this->call(SiacSeeder::class);
    }
}
