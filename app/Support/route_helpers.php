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
            ->orderByRaw("CASE roles.name WHEN 'super_admin' THEN 1 WHEN 'admin' THEN 2 WHEN 'technical_reviewer' THEN 3 WHEN 'regional_rep' THEN 4 WHEN 'moderator' THEN 5 WHEN 'business_owner' THEN 6 ELSE 7 END ASC")
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
            // 'transactions' and 'rapports' are gone with the subscriptions screens:
            // both were built on business_subscriptions, which nothing writes to, so
            // the export centre was offering downloads that are always empty.
            'kyc'          => [['ID', 'Entreprise', 'Niveau de vérification', 'Statut'],
                DB::table('businesses')->whereNull('deleted_at')->orderBy('id')->limit(5000)
                    ->get(['id', 'name_fr', 'verification_tier', 'status'])->map(fn ($r) => (array) $r)->all()],
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

if (! function_exists('certNumberFor')) {
    /** Deterministic membership-certificate number for a business (single source of truth). */
    function certNumberFor(int $businessId, $createdAt = null): string
    {
        $year = $createdAt ? \Illuminate\Support\Carbon::parse($createdAt)->year : (int) date('Y');
        $seed = md5('ah237-cert-' . $businessId);
        return 'AH237-' . $year . '-' . str_pad((string) (hexdec(substr($seed, 0, 6)) % 10000000), 7, '0', STR_PAD_LEFT);
    }
}

if (! function_exists('ensureCertificate')) {
    /** Issue + persist a certificate for a business if it doesn't have one yet; returns the fresh row. */
    function ensureCertificate(object $business): object
    {
        if (empty($business->certificate_no)) {
            $issued = $business->created_at ? \Illuminate\Support\Carbon::parse($business->created_at) : \Illuminate\Support\Carbon::now();
            $no = certNumberFor((int) $business->id, $issued);
            $expires = $issued->copy()->addYear();
            while ($expires->isPast()) {
                $expires->addYear();
            }
            DB::table('businesses')->where('id', $business->id)->update([
                'certificate_no' => $no,
                'certificate_issued_at' => $issued,
                'certificate_expires_at' => $expires,
                'updated_at' => \Illuminate\Support\Carbon::now(),
            ]);
            $business->certificate_no = $no;
            $business->certificate_issued_at = $issued;
            $business->certificate_expires_at = $expires;
        }
        return $business;
    }
}

if (! function_exists('brand_asset')) {
    /**
     * URL for a brand image, falling back to the legacy asset when the branded
     * file has not been added yet.
     *
     * The logo is referenced from ~20 views. Pointing them straight at
     * public/images/brand/ meant every one 404'd until those files existed —
     * a broken image on every page of the platform. Resolving here means the
     * site renders either way, and picks up the real logo the moment it lands
     * with no code change.
     *
     * @param  string  $variant  'mark' (circular emblem) or 'full' (lockup)
     */
    function brand_asset(string $variant = 'mark'): string
    {
        $candidates = $variant === 'full'
            ? ['images/brand/logo-full.png', 'images/brand/logo-mark.png', 'images/landing/logo.png']
            : ['images/brand/logo-mark.png', 'images/landing/logo.png'];

        foreach ($candidates as $path) {
            if (is_file(public_path($path))) {
                return asset($path);
            }
        }

        return asset('images/landing/logo.png');
    }
}
