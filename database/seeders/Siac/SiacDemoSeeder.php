<?php

namespace Database\Seeders\Siac;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiacDemoSeeder extends Seeder
{
    public function run(): void
    {
        $entrepreneurId = $this->upsertUser(
            email: 'entrepreneur@siarc2026.cm',
            name: 'Demo Entrepreneur',
            password: 'Demo@SIARC2026',
            lang: 'fr',
        );
        $this->assignRole($entrepreneurId, 'business_owner');

        // Assign "Pisciculture du Wouri" (slug=pisciculture-du-wouri) to the demo entrepreneur
        // Falls back to any published business if that one doesn't exist
        $targetBusiness = DB::table('businesses')
            ->whereNull('deleted_at')
            ->where('slug', 'pisciculture-du-wouri')
            ->first()
            ?? DB::table('businesses')->whereNull('deleted_at')->where('status', 'published')->first();

        if ($targetBusiness) {
            DB::table('businesses')->where('id', $targetBusiness->id)->update(['user_id' => $entrepreneurId]);
        }

        // Demo Buyer
        $this->upsertUser(
            email: 'acheteur@siarc2026.cm',
            name: 'Demo Acheteur',
            password: 'Demo@SIARC2026',
            lang: 'fr',
        );

        // Demo Regional Representative — assigned to the Centre region
        $regionalRepId = $this->upsertUser(
            email: 'regional@siarc2026.cm',
            name: 'Rep Centre',
            password: 'Demo@SIARC2026',
            lang: 'fr',
        );
        $this->assignRole($regionalRepId, 'regional_rep');
        $centreRegion = DB::table('regions')->where('name_fr', 'like', '%Centre%')->first();
        if ($centreRegion) {
            DB::table('users')->where('id', $regionalRepId)->update(['assigned_region_id' => $centreRegion->id]);
        }

        // Demo Ministry account
        $ministryId = $this->upsertUser(
            email: 'ministry@siarc2026.cm',
            name: 'Ministry User',
            password: 'Demo@SIARC2026',
            lang: 'fr',
        );
        $this->assignRole($ministryId, 'ministry');

        // Demo Technical Reviewer
        $technicalId = $this->upsertUser(
            email: 'technique@siarc2026.cm',
            name: 'Tech Reviewer',
            password: 'Demo@SIARC2026',
            lang: 'fr',
        );
        $this->assignRole($technicalId, 'technical_reviewer');

        $this->command->info('Demo accounts created:');
        $this->command->line('  entrepreneur@siarc2026.cm   / Demo@SIARC2026  (business_owner)');
        $this->command->line('  acheteur@siarc2026.cm       / Demo@SIARC2026  (buyer)');
        $this->command->line('  regional@siarc2026.cm       / Demo@SIARC2026  (regional_rep)');
        $this->command->line('  ministry@siarc2026.cm       / Demo@SIARC2026  (ministry)');
        $this->command->line('  technique@siarc2026.cm      / Demo@SIARC2026  (technical_reviewer)');
        $this->command->line('  admin@artisanatcameroun.cm / Admin@SIARC2026  (super_admin)');
    }

    private function upsertUser(string $email, string $name, string $password, string $lang): string
    {
        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing) {
            DB::table('users')->where('email', $email)->update(['password' => Hash::make($password), 'updated_at' => now()]);
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('users')->insert([
            'id'                  => $id,
            'name'                => $name,
            'email'               => $email,
            'password'            => Hash::make($password),
            'status'              => 'active',
            'language_preference' => $lang,
            'is_email_verified'   => 1,
            'is_phone_verified'   => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
        return $id;
    }

    private function assignRole(string $userId, string $roleName): void
    {
        $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'sanctum')->first();
        if (!$role) return;

        $exists = DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->where('role_id', $role->id)
            ->exists();

        if (!$exists) {
            DB::table('model_has_roles')->insert([
                'role_id'    => $role->id,
                'model_type' => 'App\\Modules\\Auth\\Models\\User',
                'model_id'   => $userId,
            ]);
        }
    }
}
