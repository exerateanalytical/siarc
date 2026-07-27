<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the platform's single visual language.
 *
 * Every page here began as a separate pixel replica of a PNG, which is how the
 * codebase ended up with 19 different field border colours and 9 different
 * field heights across 446 inputs in 74 files. `pages/partials/ui-kit.blade.php`
 * is now the one definition of a field, card, button and label.
 *
 * This test reads the Blade sources rather than rendered pages: drift is
 * introduced in source, and catching it here names the offending file and line
 * instead of a mystery colour on a screenshot.
 */
class UiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /** Bespoke field styling the kit replaces. Any of these on a form control is drift. */
    private const BANNED_ON_FIELDS = [
        'border-gray-'      => 'raw Tailwind grey — not in the heritage palette',
        'border-\[#E9E4D8\]' => 'legacy field border',
        'border-\[#E7E7E5\]' => 'legacy field border',
        'border-\[#E5E7E5\]' => 'legacy field border',
        'border-\[#E2DDD0\]' => 'legacy field border',
        'border-\[#E0DCD5\]' => 'legacy field border',
        'border-\[#E4E2DD\]' => 'legacy field border',
        'border-\[#EAE5D8\]' => 'field border — use ui-field',
        'border-\[#EFEBE2\]' => 'card border — use ui-field on controls',
        'border-\[#CFC9BF\]' => 'legacy checkbox border — use ui-check',
        'border-\[#C9CFC9\]' => 'legacy field border',
        'border-\[#EDEEED\]' => 'legacy field border',
        'h-\[3[0-9]px\]'     => 'hand-set field height — the kit owns this',
        'h-\[4[0-9]px\]'     => 'hand-set field height — the kit owns this',
    ];

    /**
     * Chrome that is deliberately not a plain form field: composite search bars
     * whose parts share one box, and the admin header's gold-bordered search,
     * which is sized to the kente band beside it. Documented exceptions, so a
     * new one has to be added here consciously.
     */
    private const EXEMPT = [
        'pages/partials/admin-heritage-header.blade.php',
        'pages/partials/ui-kit.blade.php',
    ];

    private function bladeFiles(): array
    {
        $root  = resource_path('views');
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            foreach (self::EXEMPT as $exempt) {
                if ($rel === $exempt) {
                    continue 2;
                }
            }
            $files[$rel] = $file->getPathname();
        }

        ksort($files);
        return $files;
    }

    public function test_no_form_control_carries_its_own_styling(): void
    {
        $offences = [];

        foreach ($this->bladeFiles() as $rel => $path) {
            $source = file_get_contents($path);

            // Match across newlines: most controls here wrap their attributes
            // over several lines, so a per-line scan would miss almost all of
            // them and pass vacuously.
            preg_match_all('/<(input|select|textarea)\b[^>]*>/is', $source, $tags, PREG_OFFSET_CAPTURE);

            foreach ($tags[0] as [$tag, $offset]) {
                // Inspect the control's OWN class attribute. A line can also hold
                // siblings — the divider span inside a ui-field-group, or the
                // visible label of an sr-only radio — whose borders are legitimate.
                if (str_contains($tag, 'type="hidden"') || str_contains($tag, 'sr-only')) {
                    continue;
                }
                if (! preg_match('/class="([^"]*)"/', $tag, $m)) {
                    continue;
                }

                foreach (self::BANNED_ON_FIELDS as $pattern => $why) {
                    if (preg_match('/' . $pattern . '/', $m[1])) {
                        $line = substr_count(substr($source, 0, $offset), "\n") + 1;
                        $offences[] = sprintf('%s:%d — %s', $rel, $line, $why);
                        continue 2;
                    }
                }
            }
        }

        $this->assertSame([], $offences, sprintf(
            "%d form control(s) bypass the UI kit.\nUse ui-field / ui-field ui-select / ui-field ui-textarea / ui-check "
            . "from pages/partials/ui-kit.blade.php instead of hand-set borders and heights:\n  %s",
            count($offences),
            implode("\n  ", $offences)
        ));
    }

    /** The kit only works if the page actually loads it. */
    public function test_every_standalone_page_includes_the_ui_kit(): void
    {
        $missing = [];

        foreach ($this->bladeFiles() as $rel => $path) {
            $source = file_get_contents($path);
            // Only pages that own their <head> need the include; the rest inherit
            // it from their layout.
            if (! str_contains($source, '<!DOCTYPE html>')) {
                continue;
            }
            if (! str_contains($source, 'partials.ui-kit')) {
                $missing[] = $rel;
            }
        }

        $this->assertSame([], $missing, sprintf(
            "%d standalone page(s) don't load the UI kit, so their fields fall back to unstyled:\n  %s\n"
            . "Add @include('pages.partials.ui-kit') before </head>.",
            count($missing),
            implode("\n  ", $missing)
        ));
    }

    /** A page that renders fields should be rendering kit fields. */
    public function test_the_kit_reaches_rendered_pages(): void
    {
        foreach (['/login', '/contact', '/creer-mon-compte'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('.ui-field', $html, "The kit's CSS is missing from {$url}.");
            $this->assertMatchesRegularExpression(
                '/<(input|select|textarea)[^>]*class="[^"]*ui-(field|check)/',
                $html,
                "No kit-styled form control rendered on {$url}."
            );
        }
    }
}
