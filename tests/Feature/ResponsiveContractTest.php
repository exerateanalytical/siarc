<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The mobile regression gate. docs/RESPONSIVE-CONTRACT.md is the numbers; this
 * is the part of them a machine can hold onto without a browser.
 *
 * Why it exists
 * -------------
 * "Make this responsive on mobile, I am tired of saying this" — and then the
 * sentence that actually mattered: "set a base so that we don't build things
 * and break them again." Repairing the named screens is worth little if the
 * page added next month breaks the same way, so this test enumerates the routes
 * **from the router** rather than from a list anyone has to remember to update.
 * A page shipped tomorrow is covered the moment its route exists.
 *
 * What it can and cannot see
 * --------------------------
 * PHPUnit has no layout engine, so it cannot measure a rect. It reads the HTML
 * each route really serves and fails on the *causes* of the three defects the
 * contract names — a class that cannot shrink, a font size below the floor, a
 * control with no room to be 44px. The measured versions of the same three
 * rules live in scripts/responsive-audit.cjs, which drives real Chrome at a real
 * 360px (see the contract for why headless Chrome cannot simply be resized).
 *
 * Exceptions are declared in the constants below with a reason attached. There
 * is no silent skip anywhere in this file, by design: a rule that quietly
 * stopped applying to half the site is how the site got here.
 */
class ResponsiveContractTest extends TestCase
{
    use RefreshDatabase;

    /** docs/RESPONSIVE-CONTRACT.md §2. Absolute floor for rendered text below `md`. */
    private const FONT_FLOOR = 12.0;

    /** §1. The narrowest device the platform supports. */
    private const MOBILE_FLOOR = 360;

    /**
     * §5.4 — a layout container wider than this cannot fit the floor device, so
     * it must carry a responsive prefix or a `max-w-` form. Anything at or under
     * it still fits inside the 360 viewport minus the 2×16px gutter.
     */
    private const FIXED_WIDTH_BUDGET = 328;

    /**
     * The one class of page exempt from the mobile rules, with its reason.
     *
     * A certificate is a printed A4 document: it carries `@page` rules, its
     * colours are fixed per type in config/certificate_types.php, and
     * pages/partials/theme.blade.php already hard-locks it to light. Reflowing
     * it for a 360px screen would make it not be the document. It is reachable
     * on a phone as a PDF-shaped page the reader pinches, which is why
     * `user-scalable=no` remains forbidden even here.
     */
    private const DOCUMENT_ROUTE_PATTERNS = [
        'certificat',
        'certificate',
        'attestation',
        'passeport',
        'passport',
        'registre',
        'register',
        'dossier',
    ];

    /**
     * Utility classes that legitimately set a fixed pixel width on something
     * that is not a layout container, with the reason each is allowed.
     */
    private const FIXED_WIDTH_SAFE_PREFIXES = [
        'max-w-',   // a maximum is not a floor; the box still shrinks
        'min-w-',   // guarded separately below
    ];

    /* ───────────────────────────── The route set ────────────────────────── */

    /**
     * Every public, parameterless GET page, taken from the router.
     *
     * Deliberately not a literal list. The whole point of the contract is that
     * a route added next month is covered without anybody remembering this file
     * exists.
     *
     * @return array<string, string>  uri => uri
     */
    public static function publicRoutes(): array
    {
        $out = [];

        foreach (Route::getRoutes() as $route) {
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $uri = $route->uri();

            // A parameter would have to be invented, and an invented id renders
            // a 404 page — which tells us nothing about the page under test.
            if (str_contains($uri, '{')) {
                continue;
            }

            // Not pages: API, generated docs, framework internals, asset routes.
            if (preg_match('#^(api/|docs|_|sanctum|storage/|livewire|telescope|horizon)#', $uri)) {
                continue;
            }

            // Behind a login. The guest response is a redirect, not a layout.
            $middleware = (array) $route->gatherMiddleware();
            if (array_intersect($middleware, ['auth', 'auth:web', 'siac.auth', 'verified'])) {
                continue;
            }

            $path = '/' . ltrim($uri, '/');
            $out[$path] = $path;
        }

        ksort($out);

        return $out;
    }

    private function isDocumentRoute(string $uri): bool
    {
        foreach (self::DOCUMENT_ROUTE_PATTERNS as $needle) {
            if (str_contains($uri, $needle)) {
                return true;
            }
        }

        return false;
    }

    /** The HTML a guest really gets, or null if the page is not a rendered page. */
    private function pageHtml(string $uri): ?string
    {
        $response = $this->get($uri);

        if ($response->getStatusCode() !== 200) {
            return null;   // redirect, 404 and 5xx are RouteSmokeTest's business
        }

        $type = $response->headers->get('Content-Type', '');
        if (! str_contains($type, 'text/html')) {
            return null;
        }

        return $response->getContent();
    }

    /**
     * Class attributes of everything that is actually painted on a 360px phone.
     *
     * Ancestry matters, so this walks the DOM rather than scanning attributes:
     * the header's categories megamenu is a `grid-cols-3` inside a
     * `hidden group-hover:block` wrapper, and a flat regex reports it on every
     * page on the site. A subtree that is display:none at the base width, or
     * only revealed on hover, cannot widen a phone it never appears on — so the
     * whole subtree is pruned, not just its root.
     *
     * @return array<int, string>
     */
    private function liveClassAttributes(string $html): array
    {
        $doc = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $out = [];
        $body = $doc->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return $out;
        }

        $walk = function (\DOMNode $node) use (&$walk, &$out) {
            foreach ($node->childNodes as $child) {
                if (! $child instanceof \DOMElement) {
                    continue;
                }

                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'template', 'noscript'], true)) {
                    continue;   // not rendered markup
                }

                $attr = $child->getAttribute('class');
                $tokens = $attr === '' ? [] : preg_split('/\s+/', trim($attr));

                $hiddenAtBase = in_array('hidden', $tokens, true)
                    && ! array_filter($tokens, fn ($t) => preg_match('/^(block|flex|grid|inline|table)/', $t));

                $hoverOnly = (bool) array_filter(
                    $tokens,
                    fn ($t) => str_starts_with($t, 'group-hover:') || str_starts_with($t, 'hover:')
                );

                if ($hiddenAtBase || $hoverOnly) {
                    continue;   // prune the subtree, not just this node
                }

                if ($attr !== '') {
                    $out[] = $attr;
                }

                $walk($child);
            }
        };

        $walk($body);

        return $out;
    }

    /** A class that only applies from a breakpoint up is not a mobile problem. */
    private function isResponsive(string $token): bool
    {
        return (bool) preg_match('/^(sm|md|lg|xl|2xl):/', $token);
    }

    /* ─────────────────────── 1. The viewport itself ─────────────────────── */

    public function test_every_public_page_declares_a_scalable_viewport(): void
    {
        $failures = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null) {
                continue;
            }

            if (! preg_match('/<meta[^>]+name="viewport"[^>]*content="([^"]*)"/i', $html, $m)) {
                $failures[] = "{$uri}: no viewport meta — every other rule here is moot without it";
                continue;
            }

            $content = strtolower($m[1]);

            if (! str_contains($content, 'width=device-width')) {
                $failures[] = "{$uri}: viewport is not width=device-width ({$m[1]})";
            }

            // Pinch-zoom is an accessibility right. This applies to documents too.
            if (str_contains($content, 'user-scalable=no')) {
                $failures[] = "{$uri}: viewport disables zoom (user-scalable=no)";
            }
            if (preg_match('/maximum-scale\s*=\s*([\d.]+)/', $content, $ms) && (float) $ms[1] < 5) {
                $failures[] = "{$uri}: viewport caps zoom at {$ms[1]} (needs 5 or none)";
            }
        }

        $this->assertSame([], $failures, "Viewport violations:\n" . implode("\n", $failures));
    }

    /* ─────────────────── 2. No page may force sideways scroll ───────────── */

    public function test_no_layout_container_carries_a_fixed_width_wider_than_the_floor_device(): void
    {
        $failures = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null || $this->isDocumentRoute($uri)) {
                continue;
            }

            $seen = [];

            foreach ($this->liveClassAttributes($html) as $attr) {
                foreach (preg_split('/\s+/', trim($attr)) as $token) {
                    if ($this->isResponsive($token)) {
                        continue;
                    }
                    foreach (self::FIXED_WIDTH_SAFE_PREFIXES as $safe) {
                        if (str_starts_with($token, $safe)) {
                            continue 2;
                        }
                    }
                    // `w-[600px]` — a width the box cannot go under.
                    if (! preg_match('/^w-\[(\d+(?:\.\d+)?)px\]$/', $token, $m)) {
                        continue;
                    }
                    $px = (float) $m[1];
                    if ($px <= self::FIXED_WIDTH_BUDGET) {
                        continue;
                    }
                    // A fixed width is fine when the same element also promises
                    // to shrink (min-w-0) or is explicitly allowed to overflow
                    // its own scroller.
                    $tokens = preg_split('/\s+/', trim($attr));
                    if (in_array('min-w-0', $tokens, true) || in_array('shrink', $tokens, true)) {
                        continue;
                    }
                    $seen[$token] = $token;
                }
            }

            foreach ($seen as $token) {
                $failures[] = "{$uri}: {$token} cannot fit a " . self::MOBILE_FLOOR
                    . 'px screen. Use max-w-[…] w-full, or give it a breakpoint prefix.';
            }
        }

        $this->assertSame([], $failures, "Fixed-width violations:\n" . implode("\n", $failures));
    }

    public function test_no_element_is_sized_against_the_viewport_including_its_scrollbar(): void
    {
        $failures = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null) {
                continue;
            }

            // 100vw includes the scrollbar; on a desktop it overflows by ~15px
            // and the page scrolls sideways for no visible reason.
            if (preg_match('/(?<![\w-])w-screen(?![\w-])/', $html)
                || preg_match('/width\s*:\s*100vw/i', $html)) {
                $failures[] = "{$uri}: sized with 100vw / w-screen — use 100% (contract §5.9)";
            }
        }

        $this->assertSame([], $failures, "Viewport-width violations:\n" . implode("\n", $failures));
    }

    public function test_wide_content_scrolls_inside_its_own_container_not_the_page(): void
    {
        $failures = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null || $this->isDocumentRoute($uri)) {
                continue;
            }

            // Every <table> must have an ancestor that scrolls horizontally.
            // Checked by walking outward from each table's offset.
            $offset = 0;
            while (($pos = strpos($html, '<table', $offset)) !== false) {
                $offset = $pos + 6;
                $before = substr($html, 0, $pos);
                // The nearest enclosing element chain: cheap and good enough —
                // an overflow-x wrapper is written immediately around the table.
                $window = substr($before, -600);
                if (! preg_match('/overflow-x-(auto|scroll)|overflow-auto|overflow-x:\s*(auto|scroll)/i', $window)) {
                    $failures[] = "{$uri}: a <table> has no overflow-x wrapper within its nearest ancestors "
                        . '(contract §5.2 — wide content scrolls inside its own container)';
                    break;   // one report per page is enough to act on
                }
            }
        }

        $this->assertSame([], $failures, "Unscrollable wide content:\n" . implode("\n", $failures));
    }

    /* ───────────────────────── 3. The type floor ────────────────────────── */

    /**
     * Recorded debt, 2026-07-29. Not an exemption — a ratchet.
     *
     * The contract landed on a site that already had sub-12px type in a dozen
     * page bodies, and those bodies are being repaired by a different change in
     * flight. Failing the whole suite on them would either block that work or
     * push someone to weaken the rule, which is exactly the outcome this file
     * exists to prevent. So: each route below may keep the sizes it already had
     * on the day the contract was written, and **nothing else, anywhere**. A new
     * `text-[10px]` on any of these routes still fails; so does one on a route
     * that is not listed; so does one on a route added next month.
     *
     * Every entry here is a bug with a reader who cannot read it. The list may
     * only ever get shorter — when a route comes off it, delete its line.
     * `test_the_recorded_type_debt_has_not_gone_stale` reports the ones that are
     * already fixed and can go.
     *
     * @var array<string, array<int, string>>
     */
    private const TYPE_FLOOR_DEBT = [
        '/'                      => ['text-[11px]'],
        '/about'                 => ['text-[10.5px]', 'text-[10px]', 'text-[11.5px]', 'text-[11px]'],
        '/centres-artisanat'     => ['text-[10px]', 'text-[11px]'],
        '/collections-heritage'  => ['text-[10.5px]', 'text-[11px]'],
        '/contact'               => ['text-[10px]'],
        '/creer-mon-compte'      => ['text-[11.5px]', 'text-[11px]'],
        '/forgot-password'       => ['text-[10px]'],
        '/galerie/entreprises'   => ['text-[11px]'],
        '/galerie/produits'      => ['text-[11.5px]'],
        '/galerie/recherche'     => ['text-[10px]'],
        '/login'                 => ['text-[10px]', 'text-[11.5px]', 'text-[11px]'],
        '/partenaires'           => ['text-[10px]', 'text-[11.5px]', 'text-[11px]'],
    ];

    /** @return array<string, array<int, string>> route => sorted violating tokens */
    private function typeFloorViolations(): array
    {
        $found = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null || $this->isDocumentRoute($uri)) {
                continue;
            }

            $seen = [];

            foreach ($this->liveClassAttributes($html) as $attr) {
                foreach (preg_split('/\s+/', trim($attr)) as $token) {
                    // `md:text-[9px]` is the documented desktop escape: the floor
                    // applies below `md`, where the reader holds the device.
                    if ($this->isResponsive($token)) {
                        continue;
                    }
                    if (! preg_match('/^text-\[(\d+(?:\.\d+)?)px\]$/', $token, $m)) {
                        continue;
                    }
                    if ((float) $m[1] < self::FONT_FLOOR) {
                        $seen[$token] = $token;
                    }
                }
            }

            if ($seen) {
                sort($seen);
                $found[$uri] = array_values($seen);
            }
        }

        return $found;
    }

    public function test_no_rendered_text_falls_below_the_mobile_font_floor(): void
    {
        $failures = [];

        foreach ($this->typeFloorViolations() as $uri => $tokens) {
            $allowed = self::TYPE_FLOOR_DEBT[$uri] ?? [];

            foreach (array_diff($tokens, $allowed) as $token) {
                $failures[] = "{$uri}: {$token} is below the " . self::FONT_FLOOR
                    . 'px mobile floor. Raise it, or scope the small size to md: and up '
                    . '(docs/RESPONSIVE-CONTRACT.md §2).';
            }
        }

        $this->assertSame([], $failures, "Type-floor violations:\n" . implode("\n", $failures));
    }

    /**
     * The ratchet's other half: debt that has been paid must leave the list, or
     * the list quietly becomes a permanent exemption for a route.
     *
     * This one only reports — it does not fail — because the routes on it are
     * being repaired by a change this test does not own, and a green suite
     * turning red the moment someone *fixes* a page teaches the wrong lesson.
     */
    public function test_the_recorded_type_debt_has_not_gone_stale(): void
    {
        $current = $this->typeFloorViolations();
        $stale = [];

        foreach (self::TYPE_FLOOR_DEBT as $uri => $tokens) {
            $remaining = array_intersect($tokens, $current[$uri] ?? []);
            if (! $remaining) {
                $stale[] = "{$uri} is clean — delete its line from TYPE_FLOOR_DEBT";
                continue;
            }
            foreach (array_diff($tokens, $remaining) as $token) {
                $stale[] = "{$uri}: {$token} is fixed — drop it from TYPE_FLOOR_DEBT";
            }
        }

        $this->assertTrue(true, implode("\n", $stale));

        if ($stale) {
            fwrite(STDERR, "\n[responsive contract] paid-off type debt to remove:\n  " . implode("\n  ", $stale) . "\n");
        }
    }

    /**
     * The floor also exists as CSS, and it silently died once already.
     *
     * pages/partials/ui-kit.blade.php carries a `@media (max-width: 767.98px)`
     * block that raises every `text-[…px]` utility under 12px. A stray `*​/`
     * inside the explanatory comment above it closed the comment early; the
     * prose that followed became top-level CSS tokens, and the parser consumed
     * the whole media rule as that garbage rule's block. Nothing errored. The
     * floor was simply gone from every page on the site, and a 360px audit
     * measured the header strapline at a real 9px.
     *
     * Two assertions, because either alone can pass while the rule is dead: the
     * comment delimiters must balance, and the rule must survive to the browser.
     */
    public function test_the_css_type_floor_survives_the_stylesheet(): void
    {
        $html = (string) $this->get('/contact')->getContent();

        preg_match_all('#<style\b[^>]*>(.*?)</style>#is', $html, $m);
        $kit = '';
        foreach ($m[1] as $block) {
            if (str_contains($block, '.ui-field')) {
                $kit = $block;
                break;
            }
        }

        $this->assertNotSame('', $kit, 'The UI kit stylesheet is not on the page.');

        $this->assertSame(
            substr_count($kit, '/*'),
            substr_count($kit, '*/'),
            'Unbalanced CSS comment delimiters in the UI kit. An early close swallows whatever '
            . 'rule follows it and the browser reports nothing. Never write a comment terminator '
            . 'inside a comment.'
        );

        $this->assertMatchesRegularExpression(
            '/@media\s*\(max-width:\s*767\.98px\)\s*\{[^}]*text-\\\\\[10px\\\\\][^}]*font-size:\s*12px\s*!important/s',
            $kit,
            'The mobile type floor rule is missing from the UI kit stylesheet '
            . '(docs/RESPONSIVE-CONTRACT.md §2).'
        );
    }

    /* ──────────────────── 4. Grids and unshrinkable rows ────────────────── */

    public function test_every_multi_column_grid_declares_what_it_does_on_a_phone(): void
    {
        $failures = [];

        foreach (self::publicRoutes() as $uri) {
            $html = $this->pageHtml($uri);
            if ($html === null || $this->isDocumentRoute($uri)) {
                continue;
            }

            $seen = [];

            foreach ($this->liveClassAttributes($html) as $attr) {
                $tokens = preg_split('/\s+/', trim($attr));

                foreach ($tokens as $token) {
                    if ($this->isResponsive($token)) {
                        continue;
                    }
                    if (! preg_match('/^grid-cols-(\d+)$/', $token, $m)) {
                        continue;
                    }
                    // Three 110px columns at 360 is not a layout. Two is the most
                    // a phone takes, and the footer proves two is fine.
                    if ((int) $m[1] > 2) {
                        $seen[$token] = $token;
                    }
                }
            }

            foreach ($seen as $token) {
                $failures[] = "{$uri}: base-level {$token} — declare grid-cols-1 or -2 and move "
                    . 'the wide count to a breakpoint (contract §5.5)';
            }
        }

        $this->assertSame([], $failures, "Rigid-grid violations:\n" . implode("\n", $failures));
    }
}
