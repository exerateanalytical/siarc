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
    <title>{{ $isFr ? 'Actualités — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'News — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#164C28', gold: '#D9A439', goldbt: '#E9A830' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[1024px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Actualités' : 'News' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'Foires, salons, expositions et nouveautés de l\'artisanat camerounais.' : 'Fairs, shows, exhibitions and news from Cameroonian craftsmanship.' }}</p>
    </div>
</section>

<main class="max-w-[1024px] mx-auto px-5 py-10 pb-20">
    @if($newsEvents->isEmpty())
    <div class="bg-white border border-[#ECECEA] rounded-2xl text-center py-14 px-5">
        <i data-lucide="newspaper" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-[13.5px] text-[#55524A]">{{ $isFr ? 'Aucune actualité pour le moment. Revenez bientôt !' : 'No news for now. Come back soon!' }}</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($newsEvents as $ev)
        <article class="bg-white border border-[#ECECEA] rounded-2xl p-5 flex flex-col">
            <p class="flex items-center gap-2 text-[11.5px] font-semibold text-[#14652F]">
                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                {{ $newsDate($ev->starts_at) }}
            </p>
            <h2 class="mt-2.5 text-[14.5px] font-bold text-[#1B1B18] leading-snug">
                <a href="{{ route('events.show', ['slug' => $ev->slug, 'lang' => $lang]) }}" class="hover:text-leaf transition-colors">{{ $isFr ? $ev->name_fr : ($ev->name_en ?? $ev->name_fr) }}</a>
            </h2>
            @if($ev->location_fr)
            <p class="mt-1.5 flex items-center gap-1.5 text-[12px] text-[#6F6B60]">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                {{ $isFr ? $ev->location_fr : ($ev->location_en ?? $ev->location_fr) }}
            </p>
            @endif
            @if($ev->description_fr)
            <p class="mt-2.5 text-[12.5px] text-[#55524A] leading-relaxed line-clamp-3" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $isFr ? $ev->description_fr : ($ev->description_en ?? $ev->description_fr) }}
            </p>
            @endif
            <a href="{{ route('events.show', ['slug' => $ev->slug, 'lang' => $lang]) }}" class="mt-auto pt-4 inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#14652F] hover:text-[#14532D]">
                {{ $isFr ? 'Lire la suite' : 'Read more' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </article>
        @endforeach
    </div>
    @endif

    <p class="mt-8 text-center">
        <a href="{{ route('events.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-6 py-3 rounded-lg transition-colors">
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
