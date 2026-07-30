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

    /* Share image. Until 30 July this only rendered when a page set
       $seoOgImage, and no page ever did — so every link posted to WhatsApp or
       Facebook showed a blank grey box. It now falls back to the site card at
       public/images/og-cover.png (1200x630, built by scripts/build-og-image.php).
       A page with a better image of its own still wins. */
    $__seoOgImage = $seoOgImage ?? asset('images/og-cover.png');
    $__seoOgImageIsDefault = ! isset($seoOgImage);
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
<meta property="og:image" content="{{ $__seoOgImage }}">
<meta property="og:image:alt" content="Artisan Hub 237 — {{ $__seoOgImageIsDefault ? 'l\'artisanat camerounais authentique' : ($__seoOgTitle ?? 'Artisan Hub 237') }}">
@if($__seoOgImageIsDefault)
    {{-- Only declared for the card we built and therefore know the size of.
         Wrong dimensions are worse than none: a scraper that trusts them
         renders a stretched or cropped preview. --}}
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endif

{{-- Twitter reads og:* for most fields but needs its own card type, or a link
     renders as a small thumbnail beside the text instead of a full-width
     preview. --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $__seoOgImage }}">
@if($__seoOgTitle)
    <meta name="twitter:title" content="{{ $__seoOgTitle }}">
@endif
@if($__seoOgDescription)
    <meta name="twitter:description" content="{{ $__seoOgDescription }}">
@endif

@foreach($__seoJsonLd as $__schema)
<script type="application/ld+json">{!! \App\Support\Seo::ld($__schema) !!}</script>
@endforeach
