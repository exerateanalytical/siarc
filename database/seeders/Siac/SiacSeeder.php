<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;

class SiacSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('=== SIAC Platform Seeder ===');

        $this->command->info('[1/5] Roles...');
        $this->call(SiacRolesSeeder::class);

        $this->command->info('[2/5] Regions & Cities...');
        $this->call(SiacRegionsSeeder::class);

        $this->command->info('[3/6] Taxonomy (Industries, Sectors, Categories)...');
        $this->call(SiacTaxonomySeeder::class);

        $this->command->info('[4/6] Attribute templates (industry-specific specs)...');
        $this->call(SiacAttributeTemplatesExpansionSeeder::class);

        $this->command->info('[5/6] Businesses & Products...');
        $this->call(SiacBusinessesSeeder::class);

        $this->command->info('[6/6] Admin & Demo Users, Settings...');
        $this->call(SiacAdminSeeder::class);
        $this->call(SiacDemoSeeder::class);

        $this->command->info('');
        $this->command->info('=== Seeding complete! ===');
        $this->command->info('Platform: http://artisanatcameroun.test/');
        $this->command->info('API:      http://artisanatcameroun.test/api/v1');
        $this->command->info('Docs:     http://artisanatcameroun.test/docs/api');
        $this->command->info('');
    }
}
