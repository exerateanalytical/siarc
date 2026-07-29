<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The PWA surface: manifest, icons, service worker, head wiring, offline page.
 *
 * These files ship by FTP to shared hosting with no build step, so the suite
 * is what proves the pieces exist and agree with each other — a manifest
 * pointing at a missing icon, or a worker precaching a route that 404s,
 * installs fine locally and breaks only on someone's phone.
 */
class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_exists_parses_and_carries_the_install_contract(): void
    {
        $path = public_path('manifest.json');
        $this->assertFileExists($path);

        $manifest = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($manifest, 'manifest.json is not valid JSON.');

        $this->assertSame('Artisan Hub 237', $manifest['name']);
        $this->assertSame('ArtisanHub237', $manifest['short_name']);
        $this->assertSame('/?source=pwa', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#02411D', $manifest['theme_color'], 'theme_color must be the brand deep green from docs/DARK-MODE-CONTRACT.md.');
        $this->assertSame('#FCF9F6', $manifest['background_color'], 'background_color must be the page cream from docs/DARK-MODE-CONTRACT.md.');
        $this->assertArrayHasKey('lang', $manifest);
        $this->assertArrayHasKey('dir', $manifest);
    }

    /** Chrome's install criteria: a 192 and a 512, plus a maskable icon. */
    public function test_manifest_icons_exist_on_disk_at_their_declared_sizes(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('manifest.json')), true);
        $sizes    = [];

        foreach ($manifest['icons'] as $icon) {
            $file = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($file, "Manifest icon {$icon['src']} is missing from disk.");

            [$w, $h] = getimagesize($file);
            $this->assertSame($icon['sizes'], "{$w}x{$h}", "{$icon['src']} declares {$icon['sizes']} but is {$w}x{$h}.");

            $sizes[($icon['purpose'] ?? 'any') . ':' . $icon['sizes']] = true;
        }

        $this->assertArrayHasKey('any:192x192', $sizes);
        $this->assertArrayHasKey('any:512x512', $sizes);
        $this->assertArrayHasKey('maskable:512x512', $sizes, 'Without a maskable icon Android shrinks the mark inside a white plate.');
    }

    public function test_service_worker_exists_and_never_caches_documents_or_sessions(): void
    {
        $path = public_path('sw.js');
        $this->assertFileExists($path);
        $sw = (string) file_get_contents($path);

        // The update path — the part people ship broken.
        $this->assertStringContainsString('skipWaiting', $sw);
        $this->assertStringContainsString('clients.claim', $sw);
        $this->assertStringContainsString('CACHE_VERSION', $sw);

        // The exemptions that make cached content safe to serve at all.
        foreach (['/certificat', '/verification-certificat', '/verifier', '/.well-known/', '/tableau-de-bord', '/login', '/api/'] as $never) {
            $this->assertStringContainsString($never, $sw, "sw.js no longer exempts {$never} from caching.");
        }

        // The offline fallback it precaches must actually exist as a route.
        $this->assertStringContainsString("'/hors-ligne'", $sw);
    }

    public function test_pages_carry_the_manifest_link_and_registration(): void
    {
        foreach (['/', '/contact'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('rel="manifest"', $html, "No manifest link on {$url}.");
            $this->assertStringContainsString('serviceWorker', $html, "No service-worker registration on {$url}.");
            $this->assertStringContainsString('apple-touch-icon', $html, "No apple-touch-icon on {$url}.");
            // Both schemes, so the Android address bar matches the theme.
            $this->assertStringContainsString('media="(prefers-color-scheme: dark)"', $html, "No dark theme-color on {$url}.");
        }
    }

    public function test_offline_page_renders_in_both_languages_and_is_self_contained(): void
    {
        $html = $this->get('/hors-ligne')->assertOk()->getContent();

        // One cached copy serves everyone, so both languages ride together.
        $this->assertStringContainsString('Vous êtes hors ligne', $html);
        $this->assertStringContainsString('You are offline', $html);

        $en = $this->get('/hors-ligne?lang=en')->assertOk()->getContent();
        $this->assertStringContainsString('You are offline', $en);

        // Self-contained: the worker serves this with an empty cache, so it
        // may not depend on the Tailwind runtime or any /vendor script.
        $this->assertStringNotContainsString('vendor/tailwindcss.js', $html);
        $this->assertStringNotContainsString('vendor/lucide.min.js', $html);
        // Nor on the built stylesheet, which is a /vendor asset like any other:
        // with an empty cache it would not be there either.
        $this->assertStringNotContainsString('vendor/app.css', $html);
    }
}
