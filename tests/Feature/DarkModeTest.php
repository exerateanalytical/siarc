<?php

namespace Tests\Feature;

use App\Support\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * Guards the dark-mode foundation.
 *
 * The failure this test exists to prevent: the site loads Tailwind from a CDN
 * bundle and 47 views carry their own inline `tailwind.config`. A config
 * without `darkMode: 'class'` makes every `dark:` variant on that page a silent
 * no-op — the page renders perfectly in light and simply never switches, which
 * is the one way this work can look finished while being broken.
 *
 * The foundation solves it by merging `darkMode: 'class'` onto whatever config
 * the page set, from `pages/partials/theme.blade.php`, which the ui-kit partial
 * includes and every page includes. That only works while the ui-kit is
 * included *later in <head>* than the page's own `tailwind.config` assignment,
 * so that ordering is asserted per file, enumerated from disk — a page added
 * next month is checked without anyone remembering to list it here.
 *
 * The palette is docs/DARK-MODE-CONTRACT.md.
 */
class DarkModeTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private const THEME_PARTIAL = 'resources/views/pages/partials/theme.blade.php';

    /** Every .blade.php under resources/views, keyed by path relative to the project root. */
    private function bladeFiles(): array
    {
        $root  = resource_path('views');
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = str_replace('\\', '/', $file->getPathname());
            }
        }
        sort($files);

        return $files;
    }

    private function themePartial(): string
    {
        return file_get_contents(base_path(self::THEME_PARTIAL));
    }

    /**
     * THE regression guard. Enumerated from disk, never from a hardcoded list.
     *
     * A view that sets `tailwind.config` is covered if either
     *   (a) it declares `darkMode: 'class'` in that config itself, or
     *   (b) it includes the ui-kit — and does so after the config assignment,
     *       because the ui-kit's merge is what supplies `darkMode` and the
     *       palette, and a merge that runs first would be overwritten.
     */
    public function test_every_view_with_an_inline_tailwind_config_gets_dark_mode_class(): void
    {
        $configured = [];
        $offenders  = [];

        foreach ($this->bladeFiles() as $path) {
            $source = file_get_contents($path);

            if (! str_contains($source, 'tailwind.config')) {
                continue;
            }

            $rel   = ltrim(str_replace(str_replace('\\', '/', base_path()), '', $path), '/');
            $lines = explode("\n", $source);

            $configLine = null;
            $uiKitLine  = null;
            foreach ($lines as $i => $line) {
                if ($configLine === null && preg_match('/tailwind\.config\s*=/', $line)) {
                    $configLine = $i;
                }
                if ($uiKitLine === null && str_contains($line, "pages.partials.ui-kit")) {
                    $uiKitLine = $i;
                }
            }

            if ($configLine === null) {
                continue;   // mentions the name in prose only (this file, the partial's comments)
            }

            $configured[] = $rel;

            if (preg_match("/darkMode\s*:\s*['\"]class['\"]/", $source)) {
                continue;   // declares it itself
            }

            if ($uiKitLine === null) {
                $offenders[] = "$rel — sets tailwind.config, declares no darkMode, and does not include pages.partials.ui-kit";
                continue;
            }

            if ($uiKitLine < $configLine) {
                $offenders[] = "$rel — includes the ui-kit on line " . ($uiKitLine + 1)
                    . ', before its own tailwind.config on line ' . ($configLine + 1)
                    . '; the merge would be overwritten by the page config';
            }
        }

        // Sanity: if the enumeration silently found nothing, the assertion below
        // would pass vacuously and the guard would be worthless.
        $this->assertGreaterThan(
            40,
            count($configured),
            'Expected to find the platform\'s inline tailwind.config views; the enumeration found only ' . count($configured) . '.'
        );

        $this->assertSame(
            [],
            $offenders,
            "These views set their own tailwind.config but never get darkMode: 'class', so every dark: variant on them is a silent no-op:\n  - "
            . implode("\n  - ", $offenders) . "\n"
        );
    }

    public function test_the_shared_partial_is_what_supplies_dark_mode_class(): void
    {
        $partial = $this->themePartial();

        $this->assertMatchesRegularExpression(
            "/cfg\.darkMode\s*=\s*'class'/",
            $partial,
            self::THEME_PARTIAL . " must set darkMode: 'class' on the merged config."
        );
        $this->assertStringContainsString(
            'window.tailwind.config = cfg',
            $partial,
            'The merged config has to be assigned back, or the CDN never rebuilds with it.'
        );
        $this->assertStringContainsString(
            "@include('pages.partials.theme')",
            file_get_contents(base_path('resources/views/pages/partials/ui-kit.blade.php')),
            'The ui-kit is what carries the theme partial onto every page.'
        );
    }

    public function test_the_boot_script_is_inline_and_in_the_head_of_a_public_page(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $head = substr($html, 0, stripos($html, '</head>') ?: 0);
        $this->assertNotSame('', $head, 'Could not locate </head> on the home page.');

        $this->assertStringContainsString(
            'window.ArtisanTheme',
            $head,
            'The theme boot script must be inside <head>: after first paint is too late.'
        );
        $this->assertStringContainsString(
            "localStorage.getItem('theme')",
            $head,
            'The boot script reads the stored choice.'
        );
        $this->assertStringContainsString(
            "matchMedia('(prefers-color-scheme: dark)')",
            $head,
            'With nothing stored, the boot script falls back to the OS preference.'
        );

        // Blocking, not deferred: find the <script> that carries the boot code
        // and prove it declares neither defer nor async, and has no src.
        preg_match_all('/<script\b([^>]*)>(.*?)<\/script>/s', $head, $m, PREG_SET_ORDER);
        $boot = null;
        foreach ($m as $tag) {
            if (str_contains($tag[2], 'window.ArtisanTheme')) {
                $boot = $tag;
                break;
            }
        }
        $this->assertNotNull($boot, 'No inline <script> in <head> contains the boot code.');
        $this->assertStringNotContainsString('defer', $boot[1]);
        $this->assertStringNotContainsString('async', $boot[1]);
        $this->assertStringNotContainsString('src=', $boot[1]);

        // Comments stripped: the script's own prose explains why it does not
        // wait for DOMContentLoaded, and would otherwise trip this assertion.
        $code = preg_replace('#/\*.*?\*/#s', '', $boot[2]);
        $this->assertStringNotContainsString(
            'DOMContentLoaded',
            $code,
            'Applying the class on DOMContentLoaded flashes the wrong theme on every navigation.'
        );
        $this->assertStringNotContainsString(
            'x-data',
            $code,
            'The class must not wait for Alpine to boot.'
        );

        // And it runs before any body markup exists.
        $bodyAt = stripos($html, '<body');
        $this->assertGreaterThan(
            stripos($html, 'window.ArtisanTheme'),
            $bodyAt,
            'The boot script must precede <body>.'
        );
    }

    public function test_a_certificate_renders_with_no_active_dark_treatment(): void
    {
        $user = $this->makeUser();
        $biz  = $this->makeBusiness($user, ['name_fr' => 'Atelier Thème Sombre']);

        $session = ['siac_user' => [
            'id' => $user->id, 'name' => 'Owner', 'email' => $user->email,
            'role' => 'business_owner', 'is_admin' => false,
        ]];

        $html = $this->withSession($session)->get('/certificat-adhesion')->assertOk()->getContent();

        // The lock is present …
        $this->assertStringContainsString(
            "setAttribute('data-theme-locked', 'light')",
            $html,
            'A certificate must declare itself locked to light.'
        );
        $this->assertStringContainsString(
            "e.classList.remove('dark')",
            $html,
            'The lock strips the dark class rather than trusting the absence of dark: classes.'
        );
        // … the boot script that could set the class is not …
        $this->assertStringNotContainsString(
            'window.ArtisanTheme',
            $html,
            'A document must not ship the theme API — nothing may flip it.'
        );
        // … and no toggle is offered.
        $this->assertStringNotContainsString(
            'theme-toggle-template',
            $html,
            'A document must not mount a theme toggle.'
        );

        // No dark: variant anywhere in the document markup. An inherited rule is
        // handled by the lock; an authored one would be a mistake.
        $this->assertSame(
            0,
            preg_match_all('/\bdark:[a-z0-9[]/i', $html),
            'A certificate carries no dark: variants at all.'
        );

        $this->assertNotEmpty(Theme::documentViews());
        $this->assertContains('pages.membership-certificate', Theme::documentViews());
    }

    public function test_a_public_page_by_contrast_does_ship_the_toggle_and_the_api(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('window.ArtisanTheme', $html);
        $this->assertStringContainsString('theme-toggle-template', $html);
        $this->assertStringNotContainsString("setAttribute('data-theme-locked', 'light')", $html);
    }

    /**
     * The control is in the chrome itself, not only in the floating fallback.
     *
     * Asserted against the three shells by name, because "reachable from the
     * chrome" is the requirement and a page that merely gets the floating
     * fallback satisfies the rendered-HTML check without satisfying it.
     */
    public function test_each_shell_includes_the_toggle_partial(): void
    {
        $shells = [
            'resources/views/pages/partials/directory-header.blade.php'      => 'the public chrome',
            'resources/views/layouts/dashboard.blade.php'                    => 'the member dashboard shell',
            'resources/views/pages/partials/admin-heritage-header.blade.php' => 'the admin shell',
        ];

        foreach ($shells as $path => $what) {
            $this->assertStringContainsString(
                "@include('pages.partials.theme-toggle')",
                file_get_contents(base_path($path)),
                "$what ($path) must render the theme toggle: the floating fallback is for pages with no chrome, not a substitute for it."
            );
        }
    }

    /** And the rendered public page really carries a wired control, not just the template. */
    public function test_a_public_page_renders_the_toggle_in_its_header(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $header = substr($html, 0, stripos($html, 'id="mobile-menu"') ?: strlen($html));

        $this->assertStringContainsString(
            'data-theme-toggle-slot',
            $header,
            'The public header carries a slot for the control, which also stands the floating fallback down.'
        );
        $this->assertStringContainsString('role="switch"', $header);
        $this->assertGreaterThanOrEqual(
            2,
            substr_count($html, 'class="theme-toggle"'),
            'Desktop utility row and mobile menu each get one; delegation wires both.'
        );
    }

    /**
     * Seven certificate views include the shared public header, which now
     * carries the toggle. A document ships neither the theme API nor the click
     * delegation, so a control rendered there would be dead furniture on a
     * printed page — the partial must render nothing at all when locked.
     */
    public function test_a_certificate_that_includes_the_public_chrome_renders_no_toggle(): void
    {
        $user    = $this->makeUser();
        $biz     = $this->makeBusiness($user);
        $product = $this->makeProduct($biz, ['status' => 'published', 'slug' => 'theme-lock-probe']);

        // Sanity: this document really does pull in the chrome that carries the
        // control, so the assertion below is testing the guard and not an absence.
        $this->assertStringContainsString(
            'pages.partials.directory-header',
            file_get_contents(base_path('resources/views/pages/certificate-of-authenticity.blade.php')),
        );

        $html = $this->get('/certificat/' . $product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('data-theme-locked', $html, 'Sanity: this is a locked document.');
        $this->assertStringNotContainsString(
            'class="theme-toggle"',
            $html,
            'A locked document must render no toggle at all, even though it includes the shared header that carries one.'
        );
    }

    /**
     * Every token in the contract's table exists, in both themes, with the
     * documented value. A page that reaches for `bg-surface` must get the hex
     * the contract promises rather than an inherited Tailwind default.
     */
    public function test_the_contract_tokens_exist_in_both_themes(): void
    {
        // token => [light rgb triplet, dark rgb triplet] — docs/DARK-MODE-CONTRACT.md
        $tokens = [
            'bg'            => ['252 249 246', '10 12 9'],
            'surface'       => ['252 250 246', '18 21 15'],
            'surface-2'     => ['255 255 255', '26 30 22'],
            'inset'         => ['249 246 239', '7 8 5'],
            'border'        => ['231 226 216', '38 43 33'],
            'border-strong' => ['213 206 192', '57 64 47'],
            'ink'           => ['26 26 23',    '243 239 231'],
            'ink-2'         => ['87 87 78',    '180 181 166'],
            'ink-3'         => ['124 124 112', '134 135 120'],
            'brand'         => ['20 101 47',   '46 146 80'],
            'brand-ink'     => ['255 255 255', '4 21 10'],
            'brand-deep'    => ['2 65 29',     '12 59 30'],
            'gold'          => ['226 154 8',   '233 168 30'],
            'gold-ink'      => ['122 78 2',    '237 179 58'],
            'danger'        => ['204 6 14',    '240 85 92'],
            'success-bg'    => ['223 243 228', '12 61 29'],
            'success-ink'   => ['0 55 18',     '139 220 166'],
        ];

        $partial = $this->themePartial();

        [$light, $dark] = $this->splitThemeBlocks($partial);

        // The declarations are column-aligned for readability, so the triplet is
        // matched whitespace-insensitively rather than byte for byte.
        $pattern = fn (string $token, string $rgb) => '/--t-' . preg_quote($token, '/') . ':\s*'
            . implode('\s+', array_map(fn ($n) => preg_quote($n, '/'), preg_split('/\s+/', trim($rgb))))
            . '\s*;/';

        foreach ($tokens as $token => [$lightRgb, $darkRgb]) {
            $this->assertMatchesRegularExpression(
                $pattern($token, $lightRgb),
                $light,
                "Light token --t-$token must be '$lightRgb' per docs/DARK-MODE-CONTRACT.md."
            );
            $this->assertMatchesRegularExpression(
                $pattern($token, $darkRgb),
                $dark,
                "Dark token --t-$token must be '$darkRgb' per docs/DARK-MODE-CONTRACT.md."
            );

            // And each is registered as a Tailwind colour, so `bg-surface`,
            // `text-ink-2`, `border-border-strong` resolve.
            $this->assertStringContainsString(
                "'$token': v('$token')",
                $partial,
                "Token '$token' is in the contract but is not registered as a Tailwind colour."
            );
        }

        // Channel triplets rather than hex, so `/alpha` modifiers keep working.
        $this->assertStringContainsString(
            "'rgb(var(--t-' + n + ') / <alpha-value>)'",
            $partial,
            'Tokens must carry <alpha-value> or bg-surface/60 silently produces nothing.'
        );
    }

    public function test_the_toggle_respects_reduced_motion_and_persists(): void
    {
        $partial = $this->themePartial();

        $this->assertStringContainsString(
            'prefers-reduced-motion: reduce',
            $partial,
            'Someone who asked for less motion gets no cross-fade.'
        );
        $this->assertStringContainsString(
            "localStorage.setItem('theme', theme)",
            $partial,
            'A chosen theme is persisted.'
        );
        $this->assertFileExists(
            base_path('resources/views/pages/partials/theme-toggle.blade.php'),
            'The toggle is a partial so the shared chrome can include it once that file is free to edit.'
        );

        $toggle = file_get_contents(base_path('resources/views/pages/partials/theme-toggle.blade.php'));
        $this->assertStringContainsString('role="switch"', $toggle);
        $this->assertStringContainsString('aria-label', $toggle);
    }

    /** Splits the palette CSS into its :root block and its .dark block. */
    private function splitThemeBlocks(string $partial): array
    {
        preg_match('/:root\s*\{(.*?)\}/s', $partial, $l);
        preg_match('/\.dark\s*\{(.*?)\}/s', $partial, $d);

        $this->assertNotEmpty($l[1] ?? '', 'No :root palette block in ' . self::THEME_PARTIAL);
        $this->assertNotEmpty($d[1] ?? '', 'No .dark palette block in ' . self::THEME_PARTIAL);

        return [$l[1], $d[1]];
    }
}
