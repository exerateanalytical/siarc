<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class ViewIntegrityTest extends TestCase
{
    /**
     * Every route('name') referenced anywhere in a Blade view must exist —
     * a missing name means a dead link or button somewhere in the UI.
     */
    public function test_every_route_name_referenced_in_views_exists(): void
    {
        $known = collect(Route::getRoutes()->getRoutesByName())->keys()->all();

        $missing = [];
        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            preg_match_all("/route\\(\\s*'([^']+)'/", $file->getContents(), $m);
            foreach (array_unique($m[1]) as $name) {
                if (! in_array($name, $known, true)) {
                    $missing[$name][] = $file->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $missing, 'Views reference route names that do not exist: '
            . json_encode($missing, JSON_PRETTY_PRINT));
    }

    /**
     * No anchor in any view may point at a bare "#" — that is a dead link.
     * (Real in-page anchors like "#regions" are fine.)
     */
    public function test_no_view_contains_dead_hash_links(): void
    {
        $offenders = [];
        foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            if (preg_match('/href="#"/', $file->getContents())) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Views contain dead href="#" links: ' . implode(', ', $offenders));
    }
}
