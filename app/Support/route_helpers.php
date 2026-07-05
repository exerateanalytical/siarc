<?php

/**
 * Global route helper functions.
 *
 * These are autoloaded via composer's "files" directive so they are ALWAYS
 * defined — including when routes are cached (`php artisan route:cache`).
 * When routes are cached, routes/web.php is NOT re-included on each request,
 * so any helper defined there would be undefined inside the cached closures
 * (fatal "Call to undefined function ..."). Defining them here keeps
 * route:cache safe. The function_exists guards let the test harness boot the
 * app repeatedly in one PHP process without a redeclare fatal.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('webUser')) {
    function webUser(): ?object
    {
        $u = session('siac_user');
        return $u ? (object) $u : null;
    }
}

if (! function_exists('requireAuth')) {
    function requireAuth(Request $request)
    {
        if (!session('siac_user')) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }
        return null;
    }
}

/**
 * Establish the authenticated web session for a users row.
 * Single place where siac_user is written after a successful factor check —
 * regenerates the session id to prevent session fixation.
 */
if (! function_exists('establishSiacSession')) {
    function establishSiacSession(object $user, Request $request): void
    {
        $siacRole = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $user->id)
            ->orderByRaw("FIELD(roles.name,'super_admin','admin','ministry','technical_reviewer','regional_rep','moderator','business_owner') DESC")
            ->value('roles.name');

        $displayName = $user->name ?? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        DB::table('users')->where('id', $user->id)->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'updated_at'    => now(),
        ]);

        $request->session()->regenerate();

        session(['siac_user' => [
            'id'       => $user->id,
            'name'     => $displayName,
            'email'    => $user->email,
            'role'     => $siacRole,
            'is_admin' => in_array($siacRole, ['super_admin', 'admin', 'moderator']),
        ]]);
    }
}

if (! function_exists('dataExportDatasets')) {
    function dataExportDatasets(bool $isFr): array
    {
        return [
            'artisans'     => $isFr ? 'Artisans' : 'Artisans',
            'produits'     => $isFr ? 'Produits & Services' : 'Products & Services',
            'utilisateurs' => $isFr ? 'Utilisateurs & Activité' : 'Users & Activity',
            'transactions' => 'Transactions',
            'kyc'          => $isFr ? 'KYC & Vérifications' : 'KYC & Verifications',
            'rapports'     => $isFr ? 'Rapports de Vente' : 'Sales Reports',
            'medias'       => $isFr ? 'Médias & Ressources' : 'Media & Resources',
            'evenements'   => $isFr ? 'Événements & Actualités' : 'Events & News',
        ];
    }
    function dataExportRows(string $dataset): array
    {
        return match ($dataset) {
            'artisans'     => [['ID', 'Nom', 'Slug', 'Type', 'Statut', 'Créé le'],
                DB::table('businesses')->whereNull('deleted_at')->orderBy('id')->limit(5000)
                    ->get(['id', 'name_fr', 'slug', 'vendor_type', 'status', 'created_at'])->map(fn ($r) => (array) $r)->all()],
            'produits'     => [['ID', 'Nom', 'Slug', 'Statut', 'Créé le'],
                DB::table('products')->whereNull('deleted_at')->orderBy('id')->limit(5000)
                    ->get(['id', 'name_fr', 'slug', 'status', 'created_at'])->map(fn ($r) => (array) $r)->all()],
            'utilisateurs' => [['ID', 'Nom', 'Email', 'Statut', 'Créé le'],
                DB::table('users')->orderBy('created_at')->limit(5000)
                    ->get(['id', 'name', 'email', 'status', 'created_at'])->map(fn ($r) => (array) $r)->all()],
            'transactions' => [['ID', 'Entreprise', 'Plan', 'Statut', 'Montant (FCFA)', 'Début', 'Prochain paiement'],
                DB::table('business_subscriptions as bs')->join('businesses as b', 'b.id', '=', 'bs.business_id')
                    ->join('subscription_plans as p', 'p.id', '=', 'bs.subscription_plan_id')->orderBy('bs.id')
                    ->get(['bs.id', 'b.name_fr', 'p.name_fr as plan', 'bs.status', 'bs.amount', 'bs.started_at', 'bs.next_payment_at'])
                    ->map(fn ($r) => (array) $r)->all()],
            'kyc'          => [['ID', 'Entreprise', 'Niveau de vérification', 'Statut'],
                DB::table('businesses')->whereNull('deleted_at')->orderBy('id')->limit(5000)
                    ->get(['id', 'name_fr', 'verification_tier', 'status'])->map(fn ($r) => (array) $r)->all()],
            'rapports'     => [['Indicateur', 'Valeur'], [
                ['Entreprises publiées', DB::table('businesses')->where('status', 'published')->whereNull('deleted_at')->count()],
                ['Produits publiés', DB::table('products')->where('status', 'published')->whereNull('deleted_at')->count()],
                ['Utilisateurs', DB::table('users')->count()],
                ['Abonnements actifs', DB::table('business_subscriptions')->where('status', 'active')->count()],
                ['Événements', DB::table('events')->count()],
            ]],
            'medias'       => [['ID', 'Produit', 'Fichier'],
                DB::table('product_images as pi')->join('products as p', 'p.id', '=', 'pi.product_id')->orderBy('pi.id')->limit(5000)
                    ->get(['pi.id', 'p.name_fr', 'pi.file_path'])->map(fn ($r) => (array) $r)->all()],
            default        => [['ID', 'Titre', 'Début', 'Fin', 'Lieu'],
                DB::table('events')->orderBy('id')->limit(5000)
                    ->get(['id', 'title_fr', 'start_date', 'end_date', 'location_fr'])->map(fn ($r) => (array) $r)->all()],
        };
    }
}

if (! function_exists('developerConsumer')) {
    function developerConsumer(object $user, bool $createIfMissing = false): ?object
    {
        $consumer = DB::table('api_consumers')->where('email', $user->email)->first();
        if (!$consumer && $createIfMissing) {
            $id = DB::table('api_consumers')->insertGetId([
                'uuid'        => Str::uuid()->toString(),
                'name'        => $user->name,
                'email'       => $user->email,
                'status'      => 'approved',
                'approved_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $consumer = DB::table('api_consumers')->where('id', $id)->first();
        }
        return $consumer;
    }
}

if (! function_exists('webLang')) {
    function webLang(Request $request): string
    {
        $lang = $request->query('lang', $request->cookie('lang', 'fr'));
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }
}

if (! function_exists('requireAdmin')) {
    /** Guard for admin-only routes: redirect guests to login, non-admins to their dashboard. */
    function requireAdmin(Request $request)
    {
        $u = session('siac_user');
        if (! $u) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }
        if (empty($u['is_admin'])) {
            return redirect('/tableau-de-bord');
        }
        return null;
    }
}

if (! function_exists('siarcEvent')) {
    /** The current SIARC salon event (most recent one whose slug starts with "siarc"). */
    function siarcEvent(): ?object
    {
        static $event = null;
        static $loaded = false;
        if (! $loaded) {
            $event = DB::table('events')->where('slug', 'like', 'siarc%')->orderByDesc('starts_at')->first();
            $loaded = true;
        }
        return $event;
    }
}
