<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Platform-wide smoke test: every parameterless GET web route must render
 * without a server error, both as a guest and (for dashboard/admin pages)
 * behind their auth redirects. Catches missing views, undefined variables
 * and broken Blade in one sweep.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_parameterless_get_routes_do_not_error_for_guests(): void
    {
        $failures = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods())) {
                continue;
            }
            $uri = $route->uri();
            // Only parameterless web pages (skip API, docs generator and wildcards)
            if (str_contains($uri, '{') || str_starts_with($uri, 'api/') || str_starts_with($uri, 'docs') || str_starts_with($uri, '_')) {
                continue;
            }

            $response = $this->get('/' . ltrim($uri, '/'));

            if ($response->getStatusCode() >= 500) {
                $failures[] = $uri . ' → ' . $response->getStatusCode();
            }
        }

        $this->assertSame([], $failures, 'Routes returned server errors: ' . implode(', ', $failures));
    }
}
