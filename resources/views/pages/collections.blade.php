@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $dirNavActive = 'collections';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Collections Héritage — le patrimoine artisanal du Cameroun par thème.' : 'Heritage Collections — Cameroon\'s craft heritage by theme.' }}">
    <title>{{ $isFr ? 'Collections Héritage — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Heritage Collections — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#164C28', gold:'#C9942E' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FBF8F2] text-[#1D1B16] antialiased">
@include('pages.partials.directory-header')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-[#0E2C1A] to-[#123D24] overflow-hidden">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 bottom-0 h-[16px] w-full object-cover opacity-70" aria-hidden="true">
    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-12 text-center">
        <span class="inline-block bg-[#0A3B22] border border-[#E9C25A]/40 rounded-md px-3 py-1 text-[10.5px] font-bold tracking-[0.12em] text-[#E9C25A] uppercase">{{ $isFr ? 'Patrimoine' : 'Heritage' }}</span>
        <h1 class="mt-4 font-serif text-[34px] sm:text-[44px] font-bold text-[#F3E7C9] leading-tight">{{ $isFr ? 'Collections Héritage' : 'Heritage Collections' }}</h1>
        <p class="mt-3 text-[13.5px] text-[#DCEAE0] max-w-[600px] mx-auto leading-relaxed">{{ $isFr ? 'Découvrez le patrimoine artisanal du Cameroun organisé par thèmes : masques, tissus, poteries, bronzes et bien plus encore.' : 'Explore Cameroon\'s craft heritage organised by theme: masks, textiles, pottery, bronzes and much more.' }}</p>
        <div class="mt-5 flex items-center justify-center gap-8 text-white">
            <div><p class="text-[26px] font-bold">{{ $collections->count() }}</p><p class="text-[11px] text-[#CFE3D5]">Collections</p></div>
            <div class="w-px h-10 bg-white/20"></div>
            <div><p class="text-[26px] font-bold">{{ number_format($totalProducts) }}</p><p class="text-[11px] text-[#CFE3D5]">{{ $isFr?'Objets':'Items' }}</p></div>
            <div class="w-px h-10 bg-white/20"></div>
            <div><p class="text-[26px] font-bold">{{ number_format($totalArtisans) }}</p><p class="text-[11px] text-[#CFE3D5]">Artisans</p></div>
        </div>
    </div>
</section>

<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($collections as $c)
        @php $cover = $c->cover_image ? asset('storage/'.$c->cover_image) : asset('images/landing/'.($hcArt[$c->slug] ?? 'hc-masques.png')); @endphp
        <article class="bg-white border border-[#EDE6D6] rounded-2xl overflow-hidden shadow-[0_1px_3px_rgba(30,25,15,0.06)] hover:shadow-md transition-shadow group">
            <div class="h-[180px] overflow-hidden bg-[#F1EDE2] relative">
                <img src="{{ $cover }}" alt="{{ $c->name_fr }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @if($c->category_fr)<span class="absolute top-3 left-3 bg-white/90 rounded-md px-2.5 py-1 text-[10.5px] font-semibold text-[#14652F]">{{ $c->category_fr }}</span>@endif
            </div>
            <div class="p-5">
                <h2 class="font-serif text-[18px] font-bold text-[#1D1B16] leading-snug">{{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</h2>
                @if($c->description_fr)<p class="mt-1.5 text-[12px] text-[#6F6B60] leading-relaxed line-clamp-2">{{ $isFr ? $c->description_fr : ($c->description_en ?? $c->description_fr) }}</p>@endif
                <div class="mt-3 flex items-center gap-4 text-[11.5px] text-[#8A857A]">
                    @if($c->region_fr)<span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#C9942E]"></i>{{ $c->region_fr }}</span>@endif
                    <span class="flex items-center gap-1.5"><i data-lucide="package" class="w-3.5 h-3.5 text-[#C9942E]"></i>{{ $c->products_count }} {{ $isFr?'objets':'items' }}</span>
                </div>
                <a href="{{ route('products.index', ['lang'=>$lang]) }}" class="mt-4 inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr?'Explorer la collection':'Explore collection' }}<i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
        </article>
        @empty
        <p class="col-span-full text-center py-12 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucune collection publiée pour le moment.' : 'No published collections yet.' }}</p>
        @endforelse
    </div>
</div>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
