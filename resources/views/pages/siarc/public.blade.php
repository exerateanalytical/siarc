@php
    $isFr = ($lang ?? 'fr') === 'fr';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $sIntro ?? 'SIARC — Salon International de l\'Artisanat du Cameroun' }}">
    <title>{{ ($sTitle ?? 'SIARC') }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { siarc:{green:'#157A43',dark:'#0B3A1E',darker:'#042B15',gold:'#E6B201',ochre:'#C97A16',red:'#C0010C'}, cream:'#F8F4EC' },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'], display:['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    <style>body{font-family:'Poppins',system-ui,sans-serif} html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FBFAF7] text-[#1D1B16] antialiased">

@include('pages.siarc.partials.siarc-header')

@php
    // Body dispatch: a per-route bespoke body under pages/siarc/bodies/ takes over the
    // content region; otherwise render the generic header-band + block scaffold.
    $rn = request()->route()?->getName() ?? '';
    $bodyKey = str_replace('.', '-', str_replace(['siarc.admin.','siarc.'], ['admin.','pub.'], $rn));
    $bodyView = 'pages.siarc.bodies.'.$bodyKey;
    $hasBody = view()->exists($bodyView);
@endphp

@if($hasBody)
    @include($bodyView)
@else
    {{-- inner-page header band --}}
    <section class="siarc-mud border-b border-[#EDE7DA]">
        <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8">
            <nav class="flex items-center gap-2 text-[12.5px] mb-3" aria-label="Breadcrumb">
                <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-siarc-green hover:underline font-medium">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                @if(!empty($sCrumb))
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
                <span class="text-[#8A857A]">{{ $sCrumb }}</span>
                @endif
            </nav>
            <h1 class="font-display text-[30px] sm:text-[38px] font-bold text-[#0F2E1A] leading-tight">{{ $sTitle ?? 'SIARC 2026' }}</h1>
            <div class="mt-3 h-[3.5px] w-[104px] bg-gradient-to-r from-siarc-gold via-[#F1D48A] to-transparent rounded-full"></div>
            @if(!empty($sIntro))
            <p class="mt-4 text-[14px] text-[#55524A] leading-relaxed max-w-[760px]">{{ $sIntro }}</p>
            @endif
        </div>
    </section>

    <main class="max-w-[1240px] mx-auto px-4 sm:px-6 py-9">
        @php $sIntro = null; @endphp {{-- already shown in the header band --}}
        @include('pages.siarc._blocks')
    </main>
@endif

@include('pages.siarc.partials.siarc-footer')

<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-mnav-btn'),m=document.getElementById('si-mnav');
        if(b&&m)b.addEventListener('click',function(){m.classList.toggle('hidden');});
    })();
</script>
@stack('scripts')
</body>
</html>
