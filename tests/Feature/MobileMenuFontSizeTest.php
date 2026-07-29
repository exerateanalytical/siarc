<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards one universal mobile menu-item font size (owner directive, 2026-07-29:
 * "make font size in all of mobile 16px").
 *
 * There is no single "mobile menu" component in this codebase — there are at
 * least nine nav-item surfaces spread across as many files, each of which used
 * to carry its own size (a hamburger menu that was actually 15px despite an
 * assumption it was already 16, a 14px admin sidebar, 12px bottom tab bars,
 * and a flat 10px dashboard tab bar with no shared size at all). A future edit
 * to any ONE of them — say, someone "fixing" the admin sidebar in isolation —
 * must not silently reintroduce a mismatch. This test reads the Blade source
 * directly (not a rendered page) so an offending file:pattern is named
 * immediately rather than discovered as a mystery size on a screenshot.
 *
 * Deliberately excluded (supporting/incidental text, not menu items):
 * badges, group/section headers, taglines, captions, brand wordmarks.
 */
class MobileMenuFontSizeTest extends TestCase
{
    private const UNIVERSAL_SIZE = '16px';

    /**
     * [relative view path => [substring that must be present]].
     * Each substring pins both the universal size AND enough of the
     * surrounding class list that a drive-by find/replace of "16px"
     * elsewhere in the file can't accidentally satisfy the assertion.
     */
    private const NAV_ITEM_PATTERNS = [
        // Public hamburger slide-out menu (directory-header.blade.php) — the
        // shared partial every public page @includes. $mmRow is the row class
        // applied to every real nav link in the sheet body.
        'pages/partials/directory-header.blade.php' => [
            "rounded-lg text-[" . self::UNIVERSAL_SIZE . "]';",
        ],

        // Admin sidebar (admin-sidebar.blade.php) — active and inactive
        // nav-item links. Desktop (md:) sizes intentionally differ and are
        // NOT asserted here — only the mobile (unprefixed) value matters.
        'pages/partials/admin-sidebar.blade.php' => [
            "min-h-[44px] text-[" . self::UNIVERSAL_SIZE . "] md:text-[13px]",
            "min-h-[44px] text-[" . self::UNIVERSAL_SIZE . "] md:text-[12.5px]",
        ],

        // Buyer/business-owner/other-role dashboard sidebar
        // (dashboard-sidebar.blade.php) — a separate partial from
        // admin-sidebar.blade.php; doubles as the mobile off-canvas nav for
        // those roles. Nav-item links and footer actions (Back to site / Log
        // out) are real clickable navigation, not captions.
        'pages/partials/dashboard-sidebar.blade.php' => [
            "rounded-xl text-[" . self::UNIVERSAL_SIZE . "] md:text-[13px] mb-0.5",
            "rounded-xl text-[" . self::UNIVERSAL_SIZE . "] md:text-[13px] text-[#DCE7DF]",
            "rounded-xl text-[" . self::UNIVERSAL_SIZE . "] md:text-[13px] text-[#F2B8B8]",
        ],

        // Bottom tab bar — layouts/app.blade.php (8 low-traffic pages) and
        // its near-duplicates on home/contact/about/the auth replica, which
        // never used layouts/app.blade.php and so were missed by an earlier
        // "just fix the shared layout" pass. All five share the same flex
        // row; `min-w-0` + `self-stretch text-center break-words` lets a
        // label too wide for one column ("Messages") wrap to two centred
        // lines instead of bleeding into the next tab.
        'layouts/app.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] md:text-[10px] font-semibold self-stretch text-center leading-tight break-words",
        ],
        'pages/home.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] md:text-[10px] font-medium self-stretch text-center leading-tight break-words",
        ],
        'pages/contact.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] md:text-[10px] font-medium self-stretch text-center leading-tight break-words",
        ],
        'about.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] md:text-[10px] font-medium self-stretch text-center leading-tight break-words",
        ],
        'auth/partials/replica-bottom.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] md:text-[10px] font-medium self-stretch text-center leading-tight break-words",
        ],

        // Artisan profile page's own bottom nav (show-mobile.blade.php) — a
        // high-traffic, separate CSS-rule (not Tailwind-class) implementation.
        // `min-width: 0` overrides the grid item's implicit content-based
        // floor so a 5-column row can actually shrink to its 1fr share, and
        // `.mob-nav-label` carries the same wrap-and-centre behaviour as the
        // Tailwind `self-stretch text-center` pattern above.
        'pages/businesses/partials/show-mobile.blade.php' => [
            "min-width: 0; min-height: 44px; font-size: " . self::UNIVERSAL_SIZE . "; font-weight: 500; color: #E4F0E7;",
            ".mob-nav-label { align-self: stretch; text-align: center;",
        ],

        // Entrepreneur/buyer dashboard's own 5-tab bottom bar (a floating
        // add/quote button in the middle) — a third, distinct bottom-nav
        // implementation, previously a flat 10px with no shared size at all.
        'pages/dashboard/entrepreneur.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] font-semibold self-stretch text-center leading-tight break-words\">{{ \$isFr ? 'Accueil' : 'Home' }}",
        ],
        'pages/dashboard/buyer.blade.php' => [
            "flex-1 min-w-0 flex flex-col items-center justify-center",
            "text-[" . self::UNIVERSAL_SIZE . "] font-semibold self-stretch text-center leading-tight break-words\">{{ \$isFr ? 'Accueil' : 'Home' }}",
        ],
    ];

    public function test_every_mobile_menu_surface_shares_the_universal_font_size(): void
    {
        $viewRoot = resource_path('views');
        $offences = [];

        foreach (self::NAV_ITEM_PATTERNS as $relativePath => $expectedSubstrings) {
            $fullPath = $viewRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (! file_exists($fullPath)) {
                $offences[] = "{$relativePath} — file not found (moved/renamed?)";
                continue;
            }

            $source = file_get_contents($fullPath);

            foreach ($expectedSubstrings as $expected) {
                if (! str_contains($source, $expected)) {
                    $offences[] = "{$relativePath} — expected to contain: {$expected}";
                }
            }
        }

        $this->assertSame([], $offences, sprintf(
            "%d mobile menu-item surface(s) drifted from the universal %s font size:\n  %s",
            count($offences),
            self::UNIVERSAL_SIZE,
            implode("\n  ", $offences)
        ));
    }
}
