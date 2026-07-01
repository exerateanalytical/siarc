<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Companies
            'companies.create', 'companies.edit', 'companies.delete', 'companies.view',
            'companies.verify', 'companies.feature', 'companies.suspend',
            // Offerings
            'offerings.create', 'offerings.edit', 'offerings.publish', 'offerings.close',
            'offerings.cmf_review', 'offerings.cmf_approve', 'offerings.cmf_reject',
            // Investors
            'investors.kyc_review', 'investors.kyc_approve', 'investors.kyc_reject',
            'investors.pledge', 'investors.portfolio_view',
            // Payments
            'payments.view', 'payments.refund', 'payments.payout_approve',
            // Compliance
            'compliance.view', 'compliance.sar_create', 'compliance.sar_file',
            'compliance.aml_screen', 'compliance.rule_manage',
            // Users
            'users.view', 'users.suspend', 'users.impersonate',
            // Admin
            'admin.dashboard', 'admin.settings', 'admin.reports', 'admin.feature_flags',
            // Support
            'tickets.view', 'tickets.reply', 'tickets.close', 'tickets.assign',
            // CMS
            'cms.pages', 'cms.blog', 'cms.announcements', 'cms.media',
            // API
            'api.consumers_manage', 'api.webhooks_manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // Roles
        $roles = [
            'super_admin'    => $permissions,
            'govt_reviewer'  => ['companies.view','companies.verify','compliance.view','compliance.aml_screen'],
            'cmf_reviewer'   => ['offerings.cmf_review','offerings.cmf_approve','offerings.cmf_reject','companies.view','compliance.view'],
            'company_owner'  => ['companies.create','companies.edit','offerings.create','offerings.edit','offerings.publish','payments.view'],
            'company_member' => ['companies.edit'],
            'investor'       => ['investors.pledge','investors.portfolio_view','payments.view'],
            'public'         => [],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'api']);
            if ($rolePerms) {
                $role->syncPermissions($rolePerms);
            }
        }
    }
}
