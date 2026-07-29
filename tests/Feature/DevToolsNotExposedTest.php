<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Nothing built for a developer may be reachable by a visitor.
 *
 * The defect this locks down: laravel/horizon auto-registered through composer
 * package discovery and bound ~23 routes under /horizon on the live site. It is
 * a dashboard over a Redis queue; artisanhub237.com has no Redis and runs
 * QUEUE_CONNECTION=sync, so eleven of those URLs answered 500 to anyone who
 * asked, and the rest published queue internals. See
 * App\Providers\DevToolsServiceProvider for the fix and the reasoning.
 *
 * These assertions are deliberately about the *router*, not about one URL
 * returning 404 by luck: a route that does not exist cannot be re-exposed by a
 * middleware change, a gate typo, or a stale route cache.
 */
class DevToolsNotExposedTest extends TestCase
{
    // /sitemap.xml and the branded error pages read the database; without the
    // schema they fail for a reason that has nothing to do with what is asserted.
    use RefreshDatabase;

    /** The environment the test suite runs in is not 'local'. */
    public function test_the_suite_runs_outside_the_local_environment(): void
    {
        // Everything below asserts what a non-local boot looks like, so this
        // guards the guard: if phpunit.xml ever set APP_ENV=local the rest of
        // this file would pass while proving nothing.
        $this->assertNotSame('local', app()->environment());
    }

    public function test_no_horizon_routes_are_registered(): void
    {
        $horizon = collect(Route::getRoutes()->getRoutes())
            ->map(fn($r) => $r->uri())
            ->filter(fn($uri) => str_starts_with($uri, 'horizon'))
            ->values();

        $this->assertEmpty(
            $horizon->all(),
            'Horizon routes are registered outside local. Check bootstrap/providers.php '
            . 'and extra.laravel.dont-discover in composer.json.'
        );
    }

    public function test_horizon_urls_are_not_found(): void
    {
        foreach ([
            '/horizon',
            '/horizon/dashboard',
            '/horizon/api/stats',
            '/horizon/api/jobs/failed',
            '/horizon/api/masters',
            '/horizon/api/workload',
            '/horizon/api/metrics/queues',
            '/horizon/api/monitoring',
        ] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_the_horizon_provider_is_never_named_in_the_provider_manifest(): void
    {
        // App\Providers\HorizonServiceProvider extends a class from the package.
        // After `composer install --no-dev` the package is gone, so naming it in
        // bootstrap/providers.php is a fatal error on the first production
        // request — the whole site, not one page. It must only ever be reached
        // through DevToolsServiceProvider's installed-package guard.
        $manifest = file_get_contents(base_path('bootstrap/providers.php'));

        $this->assertStringNotContainsString('HorizonServiceProvider', $manifest);
        $this->assertStringContainsString('DevToolsServiceProvider', $manifest);
    }

    public function test_horizon_is_a_development_only_dependency(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $this->assertArrayNotHasKey(
            'laravel/horizon',
            $composer['require'],
            'Horizon must not ship to production; it belongs in require-dev.'
        );
        $this->assertArrayHasKey('laravel/horizon', $composer['require-dev']);
        $this->assertContains(
            'laravel/horizon',
            $composer['extra']['laravel']['dont-discover'] ?? [],
            'Without dont-discover the package re-registers itself on any machine that has it installed.'
        );
    }

    public function test_the_local_only_specimen_pages_are_not_found(): void
    {
        // Unlabelled specimen renderings of the certificate security artwork.
        $this->get('/apercu-securite')->assertNotFound();
    }

    public function test_the_developer_programme_is_disabled(): void
    {
        // Deliberately off for launch: the published docs contradicted the
        // routes and key signup self-approved. routes/web.php ~4155.
        $this->get('/developer')->assertNotFound();
    }

    public function test_the_api_documentation_is_not_public(): void
    {
        // Scramble's RestrictedDocsAccess middleware gates /docs/api to local.
        $this->get('/docs/api')->assertNotFound();
        $this->get('/docs/api.json')->assertNotFound();
    }

    public function test_debug_mode_cannot_be_switched_on_in_production(): void
    {
        // Not a preference in production: the Laravel error page prints the
        // whole environment, which here means the DB password and the APP_KEY.
        $this->assertFalse(
            $this->debugFlagWith('production', 'true'),
            'APP_DEBUG must not be honoured when APP_ENV=production.'
        );

        // …and the switch still works everywhere else, or local development
        // loses its stack traces.
        $this->assertTrue($this->debugFlagWith('local', 'true'));
        $this->assertFalse($this->debugFlagWith('local', 'false'));
    }

    /**
     * Evaluate config/app.php's debug expression under a given environment.
     *
     * In a child process, because Laravel's env repository is immutable: this
     * process already has APP_ENV=testing from phpunit.xml and will not let it
     * be reassigned, so an in-process attempt silently measures nothing. A
     * fresh interpreter with the variables in its environment is the only way
     * to read the expression as production will read it.
     */
    private function debugFlagWith(string $env, string $debug): bool
    {
        $code = 'require "vendor/autoload.php"; $c = require "config/app.php"; echo $c["debug"] ? "1" : "0";';

        $process = proc_open(
            [PHP_BINARY, '-r', $code],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            base_path(),
            ['APP_ENV' => $env, 'APP_DEBUG' => $debug, 'PATH' => getenv('PATH') ?: '', 'SystemRoot' => getenv('SystemRoot') ?: ''],
        );

        $this->assertIsResource($process, 'Could not start a PHP subprocess to evaluate config/app.php.');

        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        array_map('fclose', $pipes);
        proc_close($process);

        $this->assertContains($out, ['0', '1'], "config/app.php did not evaluate cleanly: {$err}");

        return $out === '1';
    }

    public function test_robots_txt_does_not_advertise_private_or_developer_urls(): void
    {
        $body = $this->get('/robots.txt')->assertOk()->getContent();

        foreach (['horizon', 'telescope', 'docs/api', 'apercu-', '/developer'] as $secret) {
            $this->assertStringNotContainsString($secret, $body);
        }
    }

    public function test_the_sitemap_lists_only_public_pages(): void
    {
        $body = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach (['horizon', 'telescope', 'docs/api', 'apercu-', '/developer', 'tableau-de-bord', '/admin'] as $secret) {
            $this->assertStringNotContainsString($secret, $body);
        }
    }

    public function test_security_headers_are_present_on_a_public_page(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString(
            "frame-ancestors 'none'",
            $response->headers->get('Content-Security-Policy') ?? ''
        );
    }
}
