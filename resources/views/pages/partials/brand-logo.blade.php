{{--
    The one definition of the Artisan Hub 237 logo.

    Nineteen files referenced the logo directly, each sizing it their own way,
    which is how the platform ended up rendering a 52x58 source at a dozen
    different scales. Everything points here now.

    Usage:
        @include('pages.partials.brand-logo')                            mark only
        @include('pages.partials.brand-logo', ['variant' => 'full'])     full lockup
        @include('pages.partials.brand-logo', ['variant' => 'lockup'])   mark + text

    Options:
        variant  'mark' (default) | 'full' | 'lockup'
        class    extra classes on the <img>
        h        height utility for the mark, e.g. 'h-11' (default 'h-11 w-11')
        link     false to render without the wrapping <a>
        tagline  false to hide the strapline on the 'lockup' variant
--}}
@php
    $bLang    = $lang ?? request()->query('lang', request()->cookie('lang', 'fr'));
    $bIsFr    = $bLang !== 'en';
    $bVariant = $variant ?? 'mark';
    $bLink    = ($link ?? true) !== false;
    $bHref    = route('home', ['lang' => $bLang]);
    $bAlt     = 'Artisan Hub 237';

    // Falls back to the legacy asset so the platform still renders if the brand
    // files have not been dropped in yet.
    // brand_asset() handles the fallback: it returns the branded file when it
    // exists and the legacy asset otherwise, so nothing 404s while the brand
    // images are still being added.
    $bMark = brand_asset('mark');
    $bFull = brand_asset('full');
@endphp

@if($bVariant === 'full')
    @if($bLink)<a href="{{ $bHref }}" class="inline-block shrink-0" aria-label="{{ $bAlt }}">@endif
        <img src="{{ $bFull }}" alt="{{ $bAlt }}" class="{{ $class ?? 'h-11 w-auto' }} object-contain" width="1300" height="440">
    @if($bLink)</a>@endif

@elseif($bVariant === 'lockup')
    @if($bLink)<a href="{{ $bHref }}" class="flex items-center gap-3 shrink-0">@endif
        <img src="{{ $bMark }}" alt="{{ $bAlt }}" class="{{ $h ?? 'h-11 w-11' }} object-contain shrink-0" width="640" height="640">
        <span class="leading-tight">
            <span class="block text-[14px] md:text-[13px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">Artisan Hub 237</span>
            @if(($tagline ?? true) !== false)
            <span class="block text-[14px] leading-tight md:text-[10.5px] md:leading-normal font-semibold text-[#157A43] whitespace-normal md:whitespace-nowrap">{{ $bIsFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            @endif
        </span>
    @if($bLink)</a>@endif

@else
    @if($bLink)<a href="{{ $bHref }}" class="inline-block shrink-0" aria-label="{{ $bAlt }}">@endif
        <img src="{{ $bMark }}" alt="{{ $bAlt }}" class="{{ $class ?? ($h ?? 'h-11 w-11') }} object-contain" width="640" height="640">
    @if($bLink)</a>@endif
@endif
