@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $siacUser = session('siac_user');
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
            colors: { cream:'#F7F2EC', leaf:'#164C28', gold:'#C9942E' },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif} html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FEFDFC] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header', ['dirNavActive' => $sNavActive ?? null])

<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-6 pb-14">
    <nav class="flex items-center gap-2 text-[13px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#166534] hover:underline">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
        <span class="text-[#6F6B60]">SIARC 2026</span>
        @if(!empty($sCrumb))
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
        <span class="text-[#6F6B60]">{{ $sCrumb }}</span>
        @endif
    </nav>

    <h1 class="font-serif text-[30px] sm:text-[36px] font-bold text-[#1D1B16] leading-tight">{{ $sTitle ?? 'SIARC 2026' }}</h1>
    <div class="mt-2.5 h-[3.5px] w-[112px] bg-gradient-to-r from-[#D9991F] via-[#E9C989] to-transparent rounded-full mb-7"></div>

    @include('pages.siarc._blocks')
</div>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
