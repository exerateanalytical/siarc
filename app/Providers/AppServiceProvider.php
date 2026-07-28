<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->shareNavTaxonomy();
        $this->lockDocumentsToLightTheme();
    }

    /**
     * Certificates and tickets are documents: they are printed, and they render
     * light whatever the dark-mode toggle says. The list of which views count
     * is App\Support\Theme; `pages/partials/theme.blade.php` acts on the flag.
     *
     * Done with a composer rather than a line in each of the nine views so the
     * set has one definition, and so the flag reaches the partial through the
     * view data it already inherits.
     */
    private function lockDocumentsToLightTheme(): void
    {
        View::composer(\App\Support\Theme::documentViews(), function ($view) {
            $view->with('lockLightTheme', true);
        });
    }

    /**
     * Share the official craft taxonomy (3 sectors, each with its filières) with the
     * shared nav header + the marketplace sidebar so both can offer a "browse by
     * sector" menu that drills into /galerie/secteurs?cat=<slug>.
     */
    private function shareNavTaxonomy(): void
    {
        View::composer(['pages.partials.directory-header', 'pages.partials.sector-browser'], function ($view) {
            $rows = DB::table('industries')->whereIn('level', [1, 2])->where('is_active', true)
                ->orderBy('sort_order')->get(['id', 'parent_id', 'level', 'slug', 'name_fr', 'name_en']);
            $byParent = $rows->groupBy('parent_id');
            // Only sectors that actually contain trades.
            //
            // The industries table carries two things: the official craft
            // nomenclature (Artisanat d'Art / de Production / de Service, which
            // hold all 349 métiers between them) and a handful of top-level rows
            // that exist only as parents for the product-category tree —
            // "Agriculture & Agro-industrie", "Textile & Mode Africaine" — plus,
            // on older databases, generic business sectors like "Banque &
            // Finance" that were never craft at all.
            //
            // Those have no filières beneath them, so offering them in a browse
            // menu gives the visitor a category that can never hold an artisan.
            // Requiring at least one filière selects the craft sectors without a
            // hardcoded name list, and keeps doing so as the taxonomy grows.
            $navSectors = $rows->where('level', 1)->map(function ($s) use ($byParent) {
                $s->filieres = $byParent->get($s->id, collect())->values();
                return $s;
            })->filter(fn ($s) => $s->filieres->isNotEmpty())->values();
            $view->with('navSectors', $navSectors);
        });
    }

    private function configureRateLimiting(): void
    {
        // Public API — no key: 60/min per IP. With Sanctum token: 120/min per user.
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Auth endpoints (login, register, OTP)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinutes(15, 5)->by($request->ip());
        });

        // Search
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Contact and quote forms
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        // File uploads
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
