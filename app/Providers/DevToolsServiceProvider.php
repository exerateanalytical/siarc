<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Registers the developer-only packages, and only where they can work.
 *
 * WHY THIS EXISTS
 *
 * Laravel Horizon self-registers through composer's package discovery, and its
 * service provider unconditionally binds ~23 routes under /horizon. On this
 * deployment that was 11 publicly reachable URLs returning 500: Horizon is a
 * dashboard over a *Redis* queue, artisanhub237.com runs on Namecheap shared
 * hosting with no Redis daemon and QUEUE_CONNECTION=sync, so every endpoint
 * that touches the queue store throws the moment it is called. Broken pages
 * are the mild half of the problem — the routes also publish job payloads,
 * queue topology and worker hostnames to anyone who types the URL, and the
 * shipped gate ('viewHorizon' against an empty allow-list) only applies to
 * the non-local environments where the package is *supposed* to be running.
 *
 * THE MECHANISM, AND WHY THIS ONE
 *
 * Three things had to be true at once, and only this arrangement gets all
 * three:
 *
 *   1. `laravel/horizon` is a require-dev dependency, so `composer install
 *      --no-dev` — which is what scripts/package-release.sh runs — does not
 *      ship it at all. Nothing to route to, nothing to exploit, no vendor
 *      weight. This is the real fix; the rest is belt and braces.
 *
 *   2. composer.json sets extra.laravel.dont-discover for the package, so it
 *      never registers itself. Without this, a developer machine that happens
 *      to have APP_ENV=production set — or a staging box built from a dev
 *      install — would still expose the dashboard, and point (1) would be the
 *      only thing standing between the queue internals and the internet.
 *
 *   3. Because of (1) the class is *absent* in production, so nothing may
 *      reference it unguarded. A provider listed in bootstrap/providers.php
 *      is instantiated during boot; naming a missing class there is a fatal
 *      error on the first request, i.e. the whole site down. Hence the
 *      installed-package guard in register() rather than a conditional array
 *      entry, and hence this file — App\Providers\HorizonServiceProvider
 *      cannot do the job itself because it *extends* a Horizon class and
 *      cannot even be autoloaded when the package is gone.
 *
 * The environment gate is the right shape for the decision: Horizon is only
 * ever useful against a Redis queue with a supervised worker, which is a
 * local-development fact here and not a production one. If that changes, the
 * honest change is to move the package back to `require` and widen this
 * condition — not to reintroduce silent auto-discovery.
 *
 * Covered by tests/Feature/DevToolsNotExposedTest.php.
 */
class DevToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        // Horizon reads its every panel out of Redis. Pointed at any other queue
        // driver its own endpoints throw, so a dashboard that cannot work is
        // just eleven 500s in the local route sweep too. Config decides, not a
        // guess about what the developer has running.
        if (config('queue.default') !== 'redis') {
            return;
        }

        // Guarded: after `composer install --no-dev` the package is not there.
        //
        // Asking composer rather than class_exists() on purpose. class_exists()
        // triggers the autoloader, and an optimised classmap built while the
        // package *was* installed still maps the class to a path — so on a tree
        // where the files are gone but the map is stale, the "safe" check is
        // itself a fatal include error. InstalledVersions reads the manifest
        // composer actually wrote for this install and cannot disagree with it.
        if (! \Composer\InstalledVersions::isInstalled('laravel/horizon')) {
            return;
        }

        $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
        $this->app->register(\App\Providers\HorizonServiceProvider::class);
    }
}
