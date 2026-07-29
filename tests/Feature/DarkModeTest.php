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
 * The failure this test exists to prevent: a page whose `dark:` variants are a
 * silent no-op — it renders perfectly in light and simply never switches, which
 * is the one way this work can look finished while being broken.
 *
 * How that used to happen, and how it can happen now
 * --------------------------------------------------
 * The site used to compile Tailwind in the browser from the Play CDN, and 47
 * views carried their own inline `tailwind.config`; one without
 * `darkMode: 'class'` killed every dark variant on that page. That whole
 * mechanism is gone. `public/vendor/app.css` is built ahead of time by
 * `tailwind.config.cjs`, which sets `darkMode: 'class'` once, so the variants
 * are in the stylesheet before the page is ever served.
 *
 * The remaining ways to break it are asserted below, enumerated from disk so a
 * page added next month is checked without anyone remembering to list it here:
 * a page that renders utilities but never loads the stylesheet, a page that
 * loads it but not the `--t-*` palette the tokens resolve against, a stylesheet
 * rebuilt from a config that lost `darkMode`, and a stale reference to either
 * of the two deleted CDN bundles.
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
     * A shell that renders Tailwind utilities has to load the one built
     * stylesheet, and it has to load the `--t-*` palette those utilities resolve
     * against. The palette rides on the ui-kit, so both are checked together.
     * The stale CDN bundles are checked here too: a page still pointing at
     * `vendor/tailwindcss.js` would 404 and render completely unstyled.
     */
    public function test_every_view_that_renders_utilities_loads_the_built_stylesheet(): void
    {
        $withStylesheet = [];
        $offenders      = [];

        foreach ($this->bladeFiles() as $path) {
            $source = file_get_contents($path);
            $rel    = ltrim(str_replace(str_replace('\\', '/', base_path()), '', $path), '/');

            foreach (['vendor/tailwindcss.js', 'vendor/lucide.min.js'] as $dead) {
                if (str_contains($source, $dead)) {
                    $offenders[] = "$rel — still loads $dead, which no longer exists";
                }
            }

            if (! str_contains($source, "asset('vendor/app.css')")) {
                continue;
            }

            $withStylesheet[] = $rel;

            if (! str_contains($source, 'pages.partials.ui-kit')) {
                $offenders[] = "$rel — loads vendor/app.css but not the ui-kit, so the --t-* palette "
                    . 'every colour token resolves against is never defined';
            }
        }

        // Sanity: if the enumeration silently found nothing, the assertion below
        // would pass vacuously and the guard would be worthless.
        $this->assertGreaterThan(
            40,
            count($withStylesheet),
            'Expected to find the platform\'s shells loading vendor/app.css; the enumeration found only '
            . count($withStylesheet) . '.'
        );

        $this->assertSame([], $offenders, implode("\n  - ", array_merge([''], $offenders)) . "\n");
    }

    /**
     * The stylesheet is a build artefact committed to the repo, because the
     * production host has no Node. Nothing at request time can notice that it
     * was rebuilt from a config that lost `darkMode`, or never rebuilt at all —
     * so the file itself is inspected.
     */
    public function test_the_built_stylesheet_carries_the_dark_variants(): void
    {
        $config = file_get_contents(base_path('tailwind.config.cjs'));
        $this->assertMatchesRegularExpression(
            "/darkMode\s*:\s*'class'/",
            $config,
            "tailwind.config.cjs must set darkMode: 'class', or no dark: variant is emitted at all."
        );

        $cssPath = public_path('vendor/app.css');
        $this->assertFileExists($cssPath, 'Run `npm run build:assets` and commit public/vendor/app.css.');
        $css = file_get_contents($cssPath);

        // Tailwind 3.4 compiles `dark:x` to `.dark\:x:is(.dark *)`.
        $this->assertStringContainsString(
            ':is(.dark *)',
            $css,
            'The stylesheet contains no class-based dark variants, so nothing switches when .dark is set.'
        );

        // The contract tokens have to survive compilation as the custom
        // properties the theme partial re-points, or `bg-surface` would be
        // frozen in one theme. Only the tokens the markup actually uses are
        // emitted — Tailwind writes no rule for an unused class — so these two,
        // which the markup does use, are what proves the wiring.
        foreach (['var(--t-surface)', 'var(--t-gold)'] as $token) {
            $this->assertStringContainsString(
                $token,
                $css,
                "The stylesheet never references $token; the contract palette is not wired into the build."
            );
        }

        $this->assertStringContainsString(
            "@include('pages.partials.theme')",
            file_get_contents(base_path('resources/views/pages/partials/ui-kit.blade.php')),
            'The ui-kit is what carries the theme partial onto every page.'
        );
    }

    /**
     * Colour tokens whose meaning differed per page are compiled to `--c-*`
     * custom properties and declared by each page in place of the inline
     * `tailwind.config` it used to carry. A page that lost its block renders the
     * fallback shade — visibly wrong, and silent.
     */
    public function test_pages_declare_their_own_colour_tokens(): void
    {
        $declaring = [];

        foreach ($this->bladeFiles() as $path) {
            $source = file_get_contents($path);
            if (preg_match('/:root\s*\{[^}]*--c-[a-z0-9-]+\s*:/s', $source)) {
                $declaring[] = $path;
            }
        }

        // 40 of the 47 shells; the other seven (the quote documents) only ever
        // used tokens that mean the same thing everywhere, and those are plain
        // literals in the build config.
        $this->assertGreaterThan(
            35,
            count($declaring),
            'Expected the platform\'s pages to declare their own --c-* tokens; found only ' . count($declaring) . '.'
        );

        $home = file_get_contents(base_path('resources/views/pages/home.blade.php'));
        $this->assertStringContainsString('--c-leaf: 22 76 40;', $home, 'The home page lost its own leaf green.');
        $this->assertStringContainsString('--c-gold: 229 168 46;', $home, 'The home page lost its own gold.');

        $dashboard = file_get_contents(base_path('resources/views/layouts/dashboard.blade.php'));
        $this->assertStringContainsString(
            '--c-leaf: 20 101 47;',
            $dashboard,
            'The dashboard shell must keep its own, different, leaf green — that collision is why the tokens exist.'
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

        $partial     = $this->themePartial();
        $buildConfig = file_get_contents(base_path('tailwind.config.cjs'));

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

            /* And each is registered as a Tailwind colour in the build config,
               so `bg-surface`, `text-ink-2`, `border-border-strong` resolve.

               Two names are shared with page-level tokens and so are wired
               differently, both deliberately:
                 · `brand` — 4 shells own a 50–900 ramp under that name, so the
                   contract colour is the ramp's DEFAULT (`bg-brand`), exactly
                   as the old runtime merge injected it.
                 · `gold`  — 32 pages declare a gold of their own, so it is the
                   *fallback* of `--c-gold`: declare one and yours wins, declare
                   none and you get the contract's, still theme-aware. */
            $expected = match ($token) {
                'brand' => "/DEFAULT:\s*t\('brand'\)/",
                'gold'  => "/gold:\s*v\('gold',\s*'var\(--t-gold\)'\)/",
                default => "/'?" . preg_quote($token, '/') . "'?:\s*t\('" . preg_quote($token, '/') . "'\)/",
            };

            $this->assertMatchesRegularExpression(
                $expected,
                $buildConfig,
                "Token '$token' is in the contract but is not registered as a Tailwind colour in tailwind.config.cjs."
            );
        }

        // Channel triplets rather than hex, so `/alpha` modifiers keep working.
        $this->assertStringContainsString(
            "`rgb(var(--t-\${name}) / <alpha-value>)`",
            $buildConfig,
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
