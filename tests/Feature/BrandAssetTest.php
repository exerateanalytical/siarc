<?php

namespace Tests\Feature;

use App\Mail\VerificationCodeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Keeps the logo and favicon consistent across the platform.
 *
 * Nineteen views referenced the logo path directly and none declared a favicon,
 * so every tab fell back to a 0-byte /favicon.ico. Both now resolve through
 * shared partials; these assertions stop a new page reintroducing its own.
 */
class BrandAssetTest extends TestCase
{
    use RefreshDatabase;

    private const MARK = 'images/brand/logo-mark.png';
    private const FULL = 'images/brand/logo-full.png';

    /** Views that own a <head> and therefore must declare the icon themselves. */
    private function standalonePages(): array
    {
        $found = [];
        $root  = resource_path('views');
        $it    = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            // Emails carry their own <head> but must never pull in site partials.
            if (str_starts_with($rel, 'emails/')) {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            if (str_contains($source, '</head>')) {
                $found[$rel] = $source;
            }
        }

        return $found;
    }

    public function test_every_standalone_page_declares_the_favicon(): void
    {
        $missing = [];

        foreach ($this->standalonePages() as $rel => $source) {
            if (! str_contains($source, 'partials.favicon')) {
                $missing[] = $rel;
            }
        }

        $this->assertSame([], $missing, sprintf(
            "%d page(s) declare no favicon, so the browser falls back to /favicon.ico:\n  %s\n"
            . "Add @include('pages.partials.favicon') before </head>.",
            count($missing),
            implode("\n  ", $missing)
        ));
    }

    public function test_no_view_references_the_retired_logo_path(): void
    {
        $offenders = [];

        foreach ($this->standalonePages() as $rel => $_) {
            // checked below across all views, not just standalone ones
        }

        $root = resource_path('views');
        $it   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            if (str_contains(file_get_contents($file->getPathname()), 'images/landing/logo.png')) {
                $offenders[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            }
        }

        $this->assertSame([], $offenders, sprintf(
            "These views still point at the old logo asset:\n  %s\nUse %s.",
            implode("\n  ", $offenders),
            self::MARK
        ));
    }

    public function test_the_logo_reaches_rendered_pages(): void
    {
        foreach (['/login', '/contact'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('rel="icon"', $html, "No favicon declared on {$url}.");
            $this->assertStringContainsString(self::MARK, $html, "The brand mark is missing from {$url}.");
        }
    }

    /**
     * Email images must be absolute. A relative src resolves against the mail
     * client's own host, so the logo silently fails to load in every inbox.
     */
    public function test_email_logo_is_an_absolute_url_with_a_text_fallback(): void
    {
        $html = (new VerificationCodeMail('482913', 'fr'))->render();
        $site = rtrim(config('app.url'), '/');

        $this->assertStringContainsString($site . '/' . self::FULL, $html);
        // Clients block remote images by default, so the alt has to carry the brand.
        $this->assertStringContainsString('alt="Artisan Hub 237"', $html);
    }
}
