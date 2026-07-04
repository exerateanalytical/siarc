<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The Roles & Permissions admin page shows a real permission matrix, but the
// permissions table was empty. Seed a real RBAC catalog (module × action) with
// guard 'sanctum' and assign sensible permission sets per existing role.
return new class extends Migration
{
    // module key => [fr, en, icon]
    private array $modules = [
        'content'    => ['Gestion du Contenu', 'Content Management', 'folder'],
        'artisans'   => ['Artisans', 'Artisans', 'users'],
        'products'   => ['Produits & Services', 'Products & Services', 'package'],
        'collections'=> ['Collections Héritage', 'Heritage Collections', 'layers'],
        'media'      => ['Médias & Documents', 'Media & Documents', 'image'],
        'events'     => ['Événements & Festivals', 'Events & Festivals', 'calendar-days'],
        'news'       => ['Actualités & Annonces', 'News & Announcements', 'megaphone'],
        'users'      => ['Utilisateurs', 'Users', 'user-cog'],
        'commerce'   => ['Commerce & Transactions', 'Commerce & Transactions', 'shopping-cart'],
        'reports'    => ['Analyses & Rapports', 'Analytics & Reports', 'chart-column'],
        'kyc'        => ['Vérifications KYC', 'KYC Verifications', 'shield-check'],
        'partners'   => ['Partenaires', 'Partners', 'handshake'],
        'siarc'      => ['SIARC 2026', 'SIARC 2026', 'store'],
        'moderation' => ['Modération', 'Moderation', 'flag'],
        'settings'   => ['Paramètres', 'Settings', 'settings'],
    ];

    private array $actions = ['view', 'create', 'edit', 'delete', 'export', 'settings'];

    public function up(): void
    {
        $now = now();
        $permIds = []; // "module.action" => id

        foreach ($this->modules as $mod => $meta) {
            foreach ($this->actions as $act) {
                $name = $mod . '.' . $act;
                $existing = DB::table('permissions')->where('name', $name)->where('guard_name', 'sanctum')->value('id');
                if ($existing) { $permIds[$name] = $existing; continue; }
                $permIds[$name] = DB::table('permissions')->insertGetId([
                    'name' => $name, 'guard_name' => 'sanctum',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $allModules = array_keys($this->modules);
        $contentModules = ['content', 'artisans', 'products', 'collections', 'media', 'events', 'news'];

        // role name => rules. '*' = every module/action.
        $grants = [
            'super_admin' => ['modules' => $allModules, 'actions' => $this->actions],
            'admin'       => ['modules' => array_diff($allModules, ['settings']), 'actions' => $this->actions],
            'moderator'   => ['modules' => array_merge($contentModules, ['moderation']), 'actions' => ['view', 'create', 'edit', 'delete']],
            'technical_reviewer' => ['modules' => ['kyc', 'artisans', 'users'], 'actions' => ['view', 'edit']],
            'regional_rep'=> ['modules' => ['artisans', 'reports', 'events'], 'actions' => ['view', 'export']],
            'ministry'    => ['modules' => ['reports', 'artisans', 'commerce'], 'actions' => ['view', 'export']],
            'business_owner' => ['modules' => ['products', 'collections'], 'actions' => ['view', 'create', 'edit']],
            'buyer'       => ['modules' => [], 'actions' => []],
        ];

        foreach ($grants as $roleName => $rule) {
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'sanctum')->value('id');
            if (! $roleId) continue;

            foreach ($rule['modules'] as $mod) {
                foreach ($rule['actions'] as $act) {
                    $pid = $permIds[$mod . '.' . $act] ?? null;
                    if (! $pid) continue;
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['permission_id' => $pid, 'role_id' => $roleId]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $names = [];
        foreach (array_keys($this->modules) as $mod) {
            foreach ($this->actions as $act) $names[] = $mod . '.' . $act;
        }
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
