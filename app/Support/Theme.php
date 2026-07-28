<?php

namespace App\Support;

/**
 * Which views are documents, and therefore never go dark.
 *
 * A certificate is printed. Its colours are specified per type in
 * config/certificate_types.php, it carries `@page` A4 rules, and a dark one is
 * either a wrong document or a wasted toner cartridge. Same for an event
 * ticket, which is scanned at a gate off paper or a phone screen.
 *
 * The list lives here rather than as a `$lockLightTheme = true` line repeated
 * across nine views, so a tenth document can be added in one place — and so
 * `DarkModeTest` can assert against the same list the runtime uses instead of
 * a copy of it. A view may still set `$lockLightTheme = true` itself; that is
 * the escape hatch for a document rendered outside these names (a PDF job, a
 * preview embedded elsewhere).
 *
 * `pages/partials/theme.blade.php` consumes both signals.
 */
final class Theme
{
    /**
     * Blade view names that must render light regardless of the toggle.
     *
     * Deliberately NOT wildcarded: `pages.certificate-hub`,
     * `pages.certificate-verify`, `pages.certificate-verify-product` and
     * `pages.certification-authority` are browsable pages *about* certificates,
     * not documents, and they take dark mode like any other page.
     *
     * @return list<string>
     */
    public static function documentViews(): array
    {
        return [
            'pages.certificate-of-authenticity',          // COA
            'pages.certificate-product-registration',     // PRC
            'pages.certificate-ownership-transfer',       // OTC
            'pages.certificate-artisan-verification',     // AVC
            'pages.certificate-provenance',               // PPC
            'pages.certificate-export-authenticity',      // EAC
            'pages.certificate-workshop-verification',    // WVC
            'pages.membership-certificate',
            'pages.events.ticket',
        ];
    }

    /**
     * True when the view currently being rendered is one of those documents.
     *
     * Used only as a fallback: the view composer registered in
     * AppServiceProvider already hands the document views `$lockLightTheme`.
     * This catches a document rendered without that composer having run.
     */
    public static function routeIsDocument(): bool
    {
        $name = optional(request()->route())->getName();

        if (! is_string($name) || $name === '') {
            return false;
        }

        // Route names for the nine documents above. A name that merely contains
        // "certificate" is not enough — `product.certificate.verify` is a
        // lookup form, not a certificate.
        $documentRoutes = [
            'product.certificate',
            'product.registration.certificate',
            'ownership.transfer.certificate',
            'artisan.verification.certificate',
            'provenance.certificate',
            'export.certificate',
            'workshop.verification.certificate',
            'membership.certificate',
            'events.ticket',
        ];

        return in_array($name, $documentRoutes, true);
    }
}
