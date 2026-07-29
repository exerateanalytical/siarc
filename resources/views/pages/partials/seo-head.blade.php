{{--
    Shared SEO / AEO / GEO <head> block: canonical + hreflang, Open Graph,
    and JSON-LD structured data.

    Include this near the end of a page's <head> (it needs nothing above it).
    A calling view may set, before the @include:

      $seoCanonical      string  full canonical URL. Defaults to the current
                                  URL stripped of its query string, so a page
                                  varying only by ?lang=/?page=/?sort= always
                                  canonicalises to the clean path.
      $seoHreflangQuery  array   query params (besides lang) to keep on the
                                  fr/en alternate links — e.g. ['cat' => $slug]
                                  on the category browse page. Omit for none.
      $seoHreflang       false   pass exactly `false` to suppress hreflang
                                  tags entirely (pages with no fr/en pair).
      $seoOgType         string  Open Graph type, default 'website'.
      $seoOgTitle        string  defaults to the page's own <title> content.
      $seoOgDescription  string  defaults to the page's own meta description.
      $seoOgImage        string  absolute image URL, when the page has one.
      $seoJsonLd         array   list of extra schema.org arrays (Breadcrumb,
                                  Product, Person/LocalBusiness, FAQPage...).
                                  The site-wide Organization schema is always
                                  added on top of these, on every page.

    Every field funnelled into a schema block below is either passed in by
    the calling view from real database columns, or computed here from
    config/legal.php / brand_asset() — nothing is invented in this file.
--}}
@php
    $__seoRequest = request();
    $__seoCanonical = $seoCanonical ?? \App\Support\Seo::canonical($__seoRequest);
    $__seoHreflang = ($seoHreflang ?? true) === false
        ? null
        : \App\Support\Seo::hreflang($__seoRequest, $seoHreflangQuery ?? []);
    $__seoOgType = $seoOgType ?? 'website';
    $__seoOgTitle = $seoOgTitle ?? ($title ?? null);
    $__seoOgDescription = $seoOgDescription ?? ($description ?? null);
    $__seoJsonLd = collect($seoJsonLd ?? [])->filter()->push(\App\Support\Seo::organizationSchema());
@endphp
<link rel="canonical" href="{{ $__seoCanonical }}">
@if($__seoHreflang)
    <link rel="alternate" hreflang="fr" href="{{ $__seoHreflang['fr'] }}">
    <link rel="alternate" hreflang="en" href="{{ $__seoHreflang['en'] }}">
    <link rel="alternate" hreflang="x-default" href="{{ $__seoHreflang['x-default'] }}">
@endif

<meta property="og:type" content="{{ $__seoOgType }}">
<meta property="og:url" content="{{ $__seoCanonical }}">
<meta property="og:site_name" content="Artisan Hub 237">
@if($__seoOgTitle)
    <meta property="og:title" content="{{ $__seoOgTitle }}">
@endif
@if($__seoOgDescription)
    <meta property="og:description" content="{{ $__seoOgDescription }}">
@endif
@if(!empty($seoOgImage))
    <meta property="og:image" content="{{ $seoOgImage }}">
@endif

@foreach($__seoJsonLd as $__schema)
<script type="application/ld+json">{!! \App\Support\Seo::ld($__schema) !!}</script>
@endforeach
