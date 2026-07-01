<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SiacRolesSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'sanctum';

        foreach ([
            'super_admin', 'admin', 'moderator', 'business_owner', 'buyer',
            'regional_rep', 'ministry', 'technical_reviewer',
        ] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => $guard]);
        }

        $this->command->info('  Roles seeded.');
    }
}
