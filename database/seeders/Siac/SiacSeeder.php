<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;

class SiacSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('=== Artisan Hub 237 Seeder ===');
        $this->command->info('Reference data + the platform administrator only — no demo');
        $this->command->info('businesses, products or people are created.');

        $this->command->info('[1/5] Roles...');
        $this->call(SiacRolesSeeder::class);

        $this->command->info('[2/5] Regions & Cities...');
        $this->call(SiacRegionsSeeder::class);

        $this->command->info('[3/5] Taxonomy (Industries, Sectors, Categories)...');
        $this->call(SiacTaxonomySeeder::class);

        $this->command->info('[4/5] Attribute templates (industry-specific specs)...');
        $this->call(SiacAttributeTemplatesExpansionSeeder::class);

        $this->command->info('[5/5] Administrator, settings, feature flags, certifications...');
        $this->call(SiacAdminSeeder::class);

        $this->command->info('');
        $this->command->info('=== Seeding complete! ===');
        $this->command->info('');
    }
}
