@extends('layouts.app')

@php
$isFr = $lang === 'fr';
$title = ($isFr ? 'Centres d\'Artisanat' : 'Craft Centres') . ' — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun';
$typeLabels = ['principal' => $isFr ? 'Centre principal' : 'Main centre', 'secondaire' => $isFr ? 'Centre secondaire' : 'Secondary centre'];
$typeCls = ['principal' => 'bg-[#E2F3E8] text-[#157A43]', 'secondaire' => 'bg-[#FDF3E0] text-[#C97A16]'];
@endphp

@section('content')
{{-- Heritage hero band --}}
<section class="relative overflow-hidden" style="background: linear-gradient(120deg, #0A2E18, #0F4824 55%, #0A2E18)">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 top-0 h-[10px] w-full object-cover" style="background-repeat:repeat-x" aria-hidden="true">
    <div class="max-w-6xl mx-auto px-4 py-12 relative">
        <div class="max-w-2xl">
            <h1 class="text-[34px] font-bold text-white leading-tight">{{ $isFr ? 'Centres d\'Artisanat' : 'Craft Centres' }}</h1>
            <p class="mt-3 text-[14px] text-[#D7E4DA] leading-relaxed">
                {{ $isFr
                    ? 'Découvrez les centres d\'artisanat du Cameroun — pôles vivants où se transmettent les savoir-faire, région par région.'
                    : 'Discover Cameroon\'s craft centres — living hubs where know-how is passed on, region by region.' }}
            </p>
            <div class="mt-7 flex flex-wrap gap-8">
                @foreach([
                    ['map-pin', $centreStats['total'], $isFr ? 'Centres actifs' : 'Active centres'],
                    ['users', number_format($centreStats['artisans']), $isFr ? 'Artisans' : 'Artisans'],
                    ['map', $centreStats['regions'], $isFr ? 'Régions couvertes' : 'Regions covered'],
                ] as [$sIcon, $sVal, $sLabel])
                <div class="flex items-center gap-2.5">
                    <span class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="{{ $sIcon }}" class="w-4 h-4 text-[#E9C25A]"></i></span>
                    <div><p class="text-[20px] font-extrabold text-white leading-none">{{ $sVal }}</p><p class="text-[11px] text-[#C9D6CD] mt-0.5">{{ $sLabel }}</p></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="hidden lg:block absolute right-6 bottom-0 h-[92%] w-auto opacity-90 pointer-events-none" aria-hidden="true">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 bottom-0 h-[10px] w-full object-cover" style="background-repeat:repeat-x" aria-hidden="true">
</section>

<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap items-center gap-2.5 mb-6">
        <input type="hidden" name="lang" value="{{ $lang }}">
        <div class="flex items-center gap-2 bg-white border border-[#E7E7E5] rounded-lg px-3.5 h-[42px] flex-1 min-w-[220px]">
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher un centre...' : 'Search a centre...' }}" class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none">
            <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
        </div>
        <select name="type" onchange="this.form.submit()" class="h-[42px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
            <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
            <option value="principal" @selected($type==='principal')>{{ $typeLabels['principal'] }}</option>
            <option value="secondaire" @selected($type==='secondaire')>{{ $typeLabels['secondaire'] }}</option>
        </select>
    </form>

    <h2 class="text-[14px] font-bold text-[#1B1B18] mb-4">{{ $isFr ? 'Tous les centres' : 'All centres' }}</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($centres as $centre)
        <a href="{{ route('centres.show', ['slug' => $centre->slug, 'lang' => $lang]) }}" class="group bg-white border border-[#ECECEA] rounded-2xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:shadow-[0_6px_20px_rgba(15,72,36,0.10)] transition-shadow flex flex-col">
            <div class="relative h-[150px] bg-[#0F4824] overflow-hidden">
                @if($centre->cover_image)
                <img src="{{ asset('storage/' . $centre->cover_image) }}" alt="{{ $centre->name_fr }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform">
                @else
                <img src="{{ asset('images/landing/centre-hero.png') }}" alt="" class="w-full h-full object-cover opacity-90 group-hover:scale-[1.03] transition-transform">
                @endif
                <span class="absolute top-3 left-3 text-[10.5px] font-semibold px-2 py-0.5 rounded-full {{ $typeCls[$centre->type] ?? 'bg-white/90 text-[#0F4824]' }}">{{ $typeLabels[$centre->type] ?? $centre->type }}</span>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="text-[15px] font-bold text-[#1D1B16] group-hover:text-[#14652F]">{{ $isFr ? $centre->name_fr : ($centre->name_en ?? $centre->name_fr) }}</h3>
                <p class="mt-1 flex items-center gap-1.5 text-[12px] text-[#6F6B60]">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#8A857A]"></i>{{ $centre->city ?? '—' }}{{ $centre->region_fr ? ', ' . ($isFr ? $centre->region_fr : ($centre->region_en ?? $centre->region_fr)) : '' }}
                </p>
                @if($centre->specialties_fr)
                <p class="mt-2 text-[12px] text-[#3B382F] line-clamp-2 flex-1">{{ $isFr ? $centre->specialties_fr : ($centre->specialties_en ?? $centre->specialties_fr) }}</p>
                @endif
                <div class="mt-3 pt-3 border-t border-[#F1EDE4] flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43]"><i data-lucide="users" class="w-3.5 h-3.5"></i>{{ number_format($centre->artisans_count) }} {{ $isFr ? 'artisans' : 'artisans' }}</span>
                    <span class="flex items-center gap-1 text-[12px] font-semibold text-[#14652F] group-hover:gap-1.5 transition-all">{{ $isFr ? 'Découvrir' : 'Discover' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-16 text-[#8A857A]">
            <i data-lucide="map-pin" class="w-12 h-12 mx-auto mb-3 text-[#DCE7DF]"></i>
            <p class="text-[13.5px]">{{ $isFr ? 'Aucun centre trouvé.' : 'No centre found.' }}</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
