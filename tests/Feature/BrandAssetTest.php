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

    /**
     * No view may hardcode a logo path.
     *
     * Pointing them straight at public/images/brand/ broke every logo on the
     * platform the moment those files were not yet present — a 404 on each one.
     * brand_asset() resolves to whatever actually exists.
     */
    public function test_no_view_hardcodes_a_logo_path(): void
    {
        $offenders = [];
        $root = resource_path('views');
        $it   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $rel    = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $source = file_get_contents($file->getPathname());

            if (preg_match('/asset\(\s*[\'"][^\'"]*logo[^\'"]*[\'"]/i', $source)) {
                $offenders[] = $rel;
            }
        }

        $this->assertSame([], $offenders, sprintf(
            "These views build a logo URL themselves instead of calling brand_asset():\n  %s",
            implode("\n  ", $offenders)
        ));
    }

    /** The resolver must always return a file that is actually on disk. */
    public function test_brand_asset_never_returns_a_missing_file(): void
    {
        foreach (['mark', 'full'] as $variant) {
            $url  = brand_asset($variant);
            $path = public_path(parse_url($url, PHP_URL_PATH));

            $this->assertFileExists($path, "brand_asset('{$variant}') points at a file that does not exist, which renders as a broken image.");
        }
    }

    public function test_the_logo_reaches_rendered_pages(): void
    {
        foreach (['/login', '/contact'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('rel="icon"', $html, "No favicon declared on {$url}.");
            $this->assertMatchesRegularExpression('/<img[^>]+src="[^"]*logo[^"]*"/i', $html, "No logo rendered on {$url}.");
        }
    }

    /**
     * The email logo must never be a relative path.
     *
     * A relative src resolves against the mail client's own host, so the logo
     * silently fails in every inbox. The layout embeds the image in the message
     * instead — which surfaces as a cid: reference on a real send and as a data:
     * URI when the view is merely rendered. Either is self-contained; only a
     * bare path is broken.
     */
    public function test_email_logo_is_self_contained_with_a_text_fallback(): void
    {
        $html = (new VerificationCodeMail('482913', 'fr'))->render();

        $this->assertMatchesRegularExpression('/<img\s+src="([^"]+)"/', $html, 'No logo image in the email.');
        preg_match('/<img\s+src="([^"]+)"/', $html, $m);

        $this->assertMatchesRegularExpression(
            '/^(cid:|data:image\/|https?:\/\/)/',
            $m[1],
            'The email logo src must be embedded or absolute; a relative path resolves against the mail client and never loads.'
        );

        // Clients block remote images by default, so the alt has to carry the brand.
        $this->assertStringContainsString('alt="Artisan Hub 237"', $html);
    }

    /** The embedded copy has to stay small — it rides along in every message. */
    public function test_the_email_logo_build_exists_and_is_lightweight(): void
    {
        $path = public_path('images/brand/logo-email.png');

        $this->assertFileExists($path, 'The email-sized logo is missing; the layout embeds this exact file.');
        $this->assertLessThan(
            120 * 1024,
            filesize($path),
            'The email logo is heavy enough to bloat every message the platform sends.'
        );
    }
}
