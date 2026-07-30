@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $frMonths = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $enMonths = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $newsDate = function ($date) use ($isFr, $frMonths, $enMonths) {
        if (! $date) return '';
        $ts = strtotime($date);
        $m = (int) date('n', $ts);
        return date('j', $ts) . ' ' . ($isFr ? $frMonths[$m] : $enMonths[$m]) . ' ' . date('Y', $ts);
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Actualités — Artisan Hub 237' : 'News — Artisan Hub 237' }}</title>
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 217 164 57;
            --c-goldbt: 233 168 48;
            --c-leaf: 22 76 40;
        }
    </style>
    @include('pages.partials.icons')
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
@include('pages.partials.seo-head')
</head>
<body class="bg-[#F8F6F2] dark:bg-[#0A0C09] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[1024px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Actualités' : 'News' }}</h1>
        <p class="mt-2 text-[14px] md:text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'Foires, salons, expositions et nouveautés de l\'artisanat camerounais.' : 'Fairs, shows, exhibitions and news from Cameroonian craftsmanship.' }}</p>
    </div>
</section>

<main class="max-w-[1024px] mx-auto px-5 py-10 pb-20">
    @if($articles->isEmpty())
    <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-2xl text-center py-14 px-5">
        <i data-lucide="newspaper" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-[14px] md:text-[13.5px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Aucune actualité pour le moment. Revenez bientôt !' : 'No news for now. Come back soon!' }}</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($articles as $a)
        <article class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-2xl p-5 flex flex-col">
            <p class="flex items-center gap-2 text-[14px] md:text-[11.5px] font-semibold text-[#14652F] dark:text-[#339B56]">
                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                {{ $newsDate($a->published_at) }}
            </p>
            <h2 class="mt-2.5 text-[14.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-snug">
                <a href="{{ route('news.show', ['slug' => $a->slug, 'lang' => $lang]) }}" class="hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? $a->title_fr : ($a->title_en ?? $a->title_fr) }}</a>
            </h2>
            @if($a->excerpt_fr)
            <p class="mt-2.5 text-[14px] md:text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed line-clamp-3" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $isFr ? $a->excerpt_fr : ($a->excerpt_en ?? $a->excerpt_fr) }}
            </p>
            @endif
            <a href="{{ route('news.show', ['slug' => $a->slug, 'lang' => $lang]) }}" class="mt-auto pt-4 inline-flex items-center gap-2 text-[14px] md:text-[12.5px] font-semibold text-[#14652F] dark:text-[#339B56] hover:text-[#14532D] hover:dark:text-[#339B56]">
                {{ $isFr ? 'Lire la suite' : 'Read more' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </article>
        @endforeach
    </div>
    @endif

    <p class="mt-8 text-center">
        <a href="{{ route('events.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[14px] md:text-[13px] font-semibold px-6 py-3 rounded-lg transition-colors">
            {{ $isFr ? 'Voir tous les événements' : 'See all the events' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </p>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
