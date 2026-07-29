<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Shared SEO / AEO / GEO building blocks: canonical + hreflang URLs and
 * schema.org JSON-LD fragments.
 *
 * Every field below either comes straight from config/legal.php, the
 * database, or a request's own URL — nothing here invents a value. That
 * matters because this project's standing rule is that a structured-data
 * field comes from the database or it does not render (see
 * pages/partials/seo-head.blade.php and the callers in the show/index
 * views), and because config/legal.php is the single source of truth for
 * what this platform is: a private company, not a public body.
 */
class Seo
{
    /**
     * The current request's URL with the scheme, host and path only — no
     * query string. Used as the canonical target for pages whose only query
     * variance is ?lang=/?page=/?sort=, so those variants are never read as
     * duplicate content.
     */
    public static function canonical(Request $request, ?string $path = null): string
    {
        $requestPath = $request->path();
        $path = $path ?? ($requestPath === '/' ? '/' : '/' . ltrim($requestPath, '/'));

        return rtrim($request->getSchemeAndHttpHost(), '/') . $path;
    }

    /**
     * fr/en hreflang alternates (+ x-default, pointed at the French page —
     * the platform's default language) for the current path. $extraQuery
     * carries any query parameter that changes the actual content rather
     * than just the language — e.g. ?cat= on the category browse page —
     * so the alternate still points at the same category, only in the
     * other language.
     */
    public static function hreflang(Request $request, array $extraQuery = []): array
    {
        $base = self::canonical($request);

        $fr = $base . '?' . http_build_query(array_merge($extraQuery, ['lang' => 'fr']));
        $en = $base . '?' . http_build_query(array_merge($extraQuery, ['lang' => 'en']));

        return ['fr' => $fr, 'en' => $en, 'x-default' => $fr];
    }

    /**
     * Site-wide Organization schema. Every field is pulled from
     * config/legal.php or brand_asset() — the same source of truth already
     * governing the legal pages — so it can never drift from what the
     * footer/legal pages say the company is. Deliberately carries no
     * 'sameAs' entries for social profiles left unset in config/legal.php,
     * and never an affiliation with a ministry or public body.
     */
    public static function organizationSchema(): array
    {
        $company = config('legal.company');

        $sameAs = collect(config('legal.social', []))->filter()->values()->all();

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => $company['name'] ?? 'Artisan Hub 237',
            'url'         => url('/'),
            'logo'        => brand_asset('full'),
            'description' => 'Artisan Hub 237 is a private platform connecting buyers with verified '
                . 'Cameroonian artisans, businesses and producers. It is not a government body, is not '
                . 'party to the sales it hosts, and processes no sale payments directly.',
        ];

        if (! empty($company['email'])) {
            $schema['email'] = $company['email'];
        }
        if (! empty($sameAs)) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * BreadcrumbList schema from an ordered list of ['name' => ..., 'url' => ...].
     */
    public static function breadcrumbSchema(array $items): array
    {
        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $i) => [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $item['name'],
                'item'     => $item['url'],
            ])->all(),
        ];
    }

    /** JSON-encode a schema array for a <script type="application/ld+json"> block. */
    public static function ld(array $schema): string
    {
        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
