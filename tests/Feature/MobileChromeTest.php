<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The header and footer are on every public page, so a mobile defect in either
 * is a mobile defect everywhere. docs/RESPONSIVE-CONTRACT.md §7.
 *
 * The one that hurt: the hamburger did nothing for months. Twenty page views
 * still carry a legacy `mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'))`
 * of their own, and the header partial had added a second, delegated handler
 * doing the same toggle. One tap fired both — the page's listener on the button
 * first, the document-level one last — so the panel was un-hidden and re-hidden
 * inside a single event and never painted.
 *
 * The fix was to stop toggling: the header reads the state it owns
 * (`data-open`), flips that, and *sets* the classes, so it wins regardless of
 * how many legacy handlers ran a microsecond earlier. That property is what the
 * assertions below protect. If someone "simplifies" it back to a toggle, the
 * hamburger dies on twenty pages at once and no other test in this suite
 * notices.
 */
class MobileChromeTest extends TestCase
{
    use RefreshDatabase;

    private function homeHtml(): string
    {
        $response = $this->get('/');
        $response->assertOk();

        return $response->getContent();
    }

    /** The header's inline handler block, isolated from the rest of the page. */
    private function headerScript(string $html): string
    {
        preg_match_all('#<script\b[^>]*>(.*?)</script>#is', $html, $m);

        foreach ($m[1] as $block) {
            if (str_contains($block, '#mobile-menu-btn')) {
                return $block;
            }
        }

        return '';
    }

    /* ───────────────────────── The hamburger ────────────────────────────── */

    public function test_the_menu_button_and_the_panel_it_controls_both_exist(): void
    {
        $html = $this->homeHtml();

        $this->assertStringContainsString('id="mobile-menu-btn"', $html);
        $this->assertStringContainsString('id="mobile-menu"', $html);
        $this->assertSame(1, substr_count($html, 'id="mobile-menu-btn"'), 'Two buttons with the same id: getElementById picks one and the other is dead.');
        $this->assertSame(1, substr_count($html, 'id="mobile-menu"'), 'Two panels with the same id.');
    }

    public function test_the_button_declares_the_panel_it_opens(): void
    {
        $html = $this->homeHtml();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*id="mobile-menu-btn"[^>]*aria-controls="mobile-menu"/s',
            $html,
            'The menu button must name the panel it controls for a screen reader.'
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*id="mobile-menu-btn"[^>]*aria-expanded="false"/s',
            $html,
            'The button must start closed and say so.'
        );
    }

    public function test_the_menu_button_meets_the_forty_four_pixel_tap_floor(): void
    {
        $html = $this->homeHtml();

        preg_match('/<button[^>]*id="mobile-menu-btn"[^>]*class="([^"]*)"/s', $html, $m);
        $classes = $m[1] ?? '';

        // `w-11 h-11` is 44px in Tailwind's scale — the contract's floor.
        $this->assertMatchesRegularExpression('/(^|\s)w-11(\s|$)/', $classes, "Menu button is not 44px wide: {$classes}");
        $this->assertMatchesRegularExpression('/(^|\s)h-11(\s|$)/', $classes, "Menu button is not 44px tall: {$classes}");
    }

    public function test_the_header_handler_sets_the_state_it_owns_rather_than_toggling_it(): void
    {
        $script = $this->headerScript($this->homeHtml());

        $this->assertNotSame('', $script, 'The header no longer ships the handler for its own button.');

        // The whole point: it reads and writes an explicit state, so it is
        // idempotent with respect to the twenty legacy page handlers.
        $this->assertStringContainsString('data-open', $script, 'The handler must own an explicit open/closed state.');
        $this->assertStringContainsString("classList.toggle('hidden', !open)", $script,
            'The class must be SET from the owned state (two-argument toggle), never flipped. '
            . 'A bare classList.toggle("hidden") is the bug: the legacy page handlers flip it too, '
            . 'and two flips in one event is no flip at all.');

        // aria-expanded has to move with it or the control lies to a screen reader.
        $this->assertStringContainsString('aria-expanded', $script);
    }

    public function test_the_panel_is_still_shown_and_hidden_by_the_hidden_class(): void
    {
        $html = $this->homeHtml();

        preg_match('/<div[^>]*id="mobile-menu"[^>]*class="([^"]*)"/s', $html, $m);
        $classes = $m[1] ?? '';

        // Twenty page views bind to this exact class. Moving the panel to a
        // data-attribute-only mechanism would make every one of them a no-op
        // that silently ADDS `hidden` back on the first tap.
        $this->assertStringContainsString('hidden', $classes, 'The panel must start hidden, via the class the legacy handlers use.');
    }

    /* ─────────────────── The sheet, not a dropped list ──────────────────── */

    public function test_the_menu_is_a_sheet_with_its_own_scroll_and_a_way_out(): void
    {
        $html = $this->homeHtml();

        preg_match('/<div[^>]*id="mobile-menu"[^>]*class="([^"]*)"/s', $html, $m);
        $classes = $m[1] ?? '';

        $this->assertStringContainsString('fixed', $classes, 'A menu that scrolls with the page is a dropped list, not a menu.');
        $this->assertStringContainsString('inset-0', $classes);

        // A modal the user cannot dismiss is a trap: backdrop and an explicit X.
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'data-mm-close'),
            'The sheet needs both a backdrop and a close button carrying data-mm-close.');
    }

    public function test_the_sheet_carries_the_controls_a_phone_cannot_otherwise_reach(): void
    {
        $html = $this->homeHtml();

        $start = strpos($html, 'id="mobile-menu"');
        $sheet = substr($html, $start, 14000);

        // The desktop utility row and the theme pill are `hidden` on a phone, so
        // if the sheet does not carry them they exist nowhere at 360px.
        $this->assertStringContainsString('theme-toggle', $sheet, 'Dark mode is unreachable on a phone without this.');
        $this->assertStringContainsString('lang=fr', str_replace('&amp;', '&', $sheet), 'The language switch must be in the sheet.');
        $this->assertStringContainsString('lang=en', str_replace('&amp;', '&', $sheet));
        $this->assertStringContainsString('min-h-[48px]', $sheet, 'Menu rows must clear the 44px tap floor.');
    }

    public function test_the_sheet_marks_the_page_the_reader_is_on(): void
    {
        $html = $this->get('/contact')->getContent();

        $this->assertStringContainsString('aria-current="page"', $html,
            'The mobile menu must show which page is active — the desktop bar does.');
    }

    /* ─────────────────────────── The footer ─────────────────────────────── */

    public function test_the_footer_is_two_columns_on_a_phone(): void
    {
        $html = $this->homeHtml();

        $start = strpos($html, '<footer');
        $this->assertNotFalse($start, 'No footer on the home page.');
        $footer = substr($html, $start);

        // A single stacked column turned the footer into fifteen screens of
        // one-word rows. Two columns is the base; the wide layout is a
        // breakpoint override.
        $this->assertMatchesRegularExpression('/class="grid grid-cols-2 /', $footer,
            'The footer grid must declare grid-cols-2 at the base width.');
        $this->assertStringNotContainsString('grid-cols-1 sm:grid-cols-2', $footer,
            'grid-cols-1 at the base is the stacked footer this replaced.');
    }

    public function test_the_footer_cannot_scroll_the_page_sideways(): void
    {
        $html = $this->homeHtml();
        $footer = substr($html, (int) strpos($html, '<footer'));

        // The backstop that stopped the 1280px right-edge clip from dragging the
        // whole page with it. `clip` rather than `hidden` so nothing inside can
        // create a scroll container by accident.
        $this->assertStringContainsString('overflow-x: clip', $footer);
    }

    public function test_the_footer_link_rows_and_the_bottom_bar_meet_the_tap_floor(): void
    {
        $html = $this->homeHtml();
        $footer = substr($html, (int) strpos($html, '<footer'));

        // Quick-links rows: 12px type in a 21px row is a 21px target.
        $this->assertStringContainsString('min-h-[44px] md:min-h-0', $footer,
            'Footer link rows must be 44px tall on a phone and may return to the artwork rhythm at md.');

        // The fixed bottom navigation, which is the only nav on a phone.
        $this->assertStringContainsString('min-h-[52px]', $html, 'The mobile bottom bar rows are under the tap floor.');
        $this->assertStringContainsString('env(safe-area-inset-bottom)', $html,
            'The bottom bar must clear the home indicator.');
    }
}
