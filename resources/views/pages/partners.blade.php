@extends('layouts.app')

@php
$isFr = $lang === 'fr';
$title = ($isFr ? 'Partenaires & Sponsors' : 'Partners & Sponsors') . ' — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun';

$typeCls = [
    'Institutionnel' => 'bg-[#E2F3E8] text-[#157A43]', 'International' => 'bg-[#E8EFFB] text-[#3565DE]',
    'Finance' => 'bg-[#FDF3E0] text-[#C97A16]', 'Privé' => 'bg-[#F0EAFB] text-[#7C4FE0]',
];

$partnerTiles = [
    'MINCOMMERCE' => 'partner-mincommerce.png', 'MINAC' => 'partner-minac.png', 'UNESCO' => 'partner-unesco.png',
    'ITC' => 'partner-itc.png', 'CEPII Cameroun' => 'partner-cepii.png', 'OAPI' => 'partner-oapi.png',
    'Banque Africaine de Développement' => 'partner-bad.png', 'AFD' => 'partner-afd.png', 'Union Européenne' => 'partner-ue.png',
];
@endphp

@section('content')
{{-- Hero band: heritage motif (kente border + statue accent) --}}
<section class="relative overflow-hidden" style="background: linear-gradient(120deg, #0A2E18, #0F4824 55%, #0A2E18)">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 top-0 h-[10px] w-full object-cover" style="background-repeat:repeat-x" aria-hidden="true">
    <div class="max-w-6xl mx-auto px-4 py-12 relative">
        <div class="max-w-2xl">
            <h1 class="text-[34px] font-bold text-white leading-tight">{{ $isFr ? 'Nos Partenaires' : 'Our Partners' }}</h1>
            <p class="mt-3 text-[14px] text-[#D7E4DA] leading-relaxed">
                {{ $isFr
                    ? 'Ensemble, valorisons l\'artisanat camerounais et bâtissons un écosystème durable au service de nos artisans et de notre patrimoine.'
                    : 'Together, let\'s promote Cameroonian craftsmanship and build a sustainable ecosystem for our artisans and heritage.' }}
            </p>
            <div class="mt-7 grid grid-cols-2 md:grid-cols-4 gap-5">
                @foreach(($isFr ? [
                    ['users', 'Institutions publiques, organisations internationales, entreprises et associations engagées à nos côtés.'],
                    ['shield', 'Soutien technique, financier et stratégique pour un artisanat compétitif.'],
                    ['handshake', 'Partenariats durables pour la promotion de notre culture et de notre économie.'],
                    ['globe', 'Réseau local et international au service des artisans et des communautés.'],
                ] : [
                    ['users', 'Public institutions, international organizations, businesses and associations by our side.'],
                    ['shield', 'Technical, financial and strategic support for competitive craftsmanship.'],
                    ['handshake', 'Lasting partnerships to promote our culture and economy.'],
                    ['globe', 'Local and international network serving artisans and communities.'],
                ]) as [$fIcon, $fText])
                <div class="flex items-start gap-2.5">
                    <span class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="{{ $fIcon }}" class="w-4 h-4 text-[#E9C25A]"></i></span>
                    <p class="text-[11.5px] text-[#C9D6CD] leading-snug">{{ $fText }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="hidden lg:block absolute right-6 bottom-0 h-[92%] w-auto opacity-90 pointer-events-none" aria-hidden="true">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 bottom-0 h-[10px] w-full object-cover" style="background-repeat:repeat-x" aria-hidden="true">
</section>

{{-- KPI stat row --}}
<section class="bg-white border-b border-[#EFEBE2]">
    <div class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([
            ['handshake', '#157A43', '#E2F3E8', $pubKpis['active'], $isFr ? 'Partenaires Actifs' : 'Active Partners'],
            ['globe', '#3565DE', '#E8EFFB', $pubKpis['international'], $isFr ? 'Partenaires Internationaux' : 'International Partners'],
            ['building-2', '#7C4FE0', '#F0EAFB', $pubKpis['national'], $isFr ? 'Partenaires Nationaux' : 'National Partners'],
            ['star', '#8A5A1F', '#F5EEDD', $pubKpis['premium'], $isFr ? 'Partenariats Premium' : 'Premium Partnerships'],
        ] as [$kIcon, $kColor, $kTile, $kVal, $kLabel])
        <div class="flex items-center gap-3">
            <span class="w-[46px] h-[46px] rounded-full flex items-center justify-center shrink-0" style="background-color: {{ $kTile }}"><i data-lucide="{{ $kIcon }}" class="w-5 h-5" style="color: {{ $kColor }}"></i></span>
            <div class="min-w-0"><p class="text-[18px] font-extrabold text-[#1B1B18] leading-none">{{ $kVal }}</p><p class="text-[11px] text-[#8A857A] mt-0.5">{{ $kLabel }}</p></div>
        </div>
        @endforeach
        <div class="flex items-center gap-3">
            <span class="w-[46px] h-[46px] rounded-full bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="map" class="w-5 h-5 text-[#157A43]"></i></span>
            <div class="min-w-0"><p class="text-[15px] font-extrabold text-[#1B1B18] leading-none">Cameroun</p><p class="text-[11px] text-[#8A857A] mt-0.5">{{ $isFr ? 'Couverture' : 'Coverage' }} {{ $pubRegionsCovered }} {{ $isFr ? 'régions' : 'regions' }}</p></div>
        </div>
    </div>
</section>

<div class="max-w-6xl mx-auto px-4 py-8">
    {{-- Filters toolbar --}}
    <form method="GET" class="flex flex-wrap items-center gap-2.5 mb-6">
        <input type="hidden" name="lang" value="{{ $lang }}">
        <div class="flex items-center gap-2 bg-white border border-[#E7E7E5] rounded-lg px-3.5 h-[42px] flex-1 min-w-[200px]">
            <input type="text" name="q" value="{{ $pubQ }}" placeholder="{{ $isFr ? 'Rechercher un partenaire...' : 'Search a partner...' }}" class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none">
            <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
        </div>
        <select name="type" onchange="this.form.submit()" class="h-[42px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
            <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
            @foreach($pubTypes as $t)<option value="{{ $t }}" @selected($pubType===$t)>{{ $t }}</option>@endforeach
        </select>
        <select name="sector" onchange="this.form.submit()" class="h-[42px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
            <option value="">{{ $isFr ? 'Tous les secteurs' : 'All sectors' }}</option>
            @foreach($pubSectors as $s)<option value="{{ $s }}" @selected($pubSector===$s)>{{ $s }}</option>@endforeach
        </select>
        <select name="country" onchange="this.form.submit()" class="h-[42px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
            <option value="">{{ $isFr ? 'Tous les pays' : 'All countries' }}</option>
            @foreach($pubCountries as $c)<option value="{{ $c }}" @selected($pubCountry===$c)>{{ $c }}</option>@endforeach
        </select>
    </form>

    <h2 class="text-[14px] font-bold text-[#1B1B18] mb-4">{{ $isFr ? 'Tous nos partenaires' : 'All our partners' }}</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($partners as $partner)
        @php $partnerTile = $partnerTiles[$partner->name_fr] ?? null; @endphp
        <div class="bg-white border border-[#ECECEA] rounded-xl p-5 flex flex-col shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <div class="flex items-start justify-between mb-3">
                <div class="w-[52px] h-[52px] rounded-xl bg-[#F8F6F2] border border-[#F0EDE6] flex items-center justify-center overflow-hidden shrink-0">
                    @if($partnerTile)<img src="{{ asset('images/landing/' . $partnerTile) }}" alt="{{ $partner->name_fr }}" class="w-full h-full object-contain">
                    @elseif($partner->logo)<img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name_fr }}" class="w-full h-full object-contain">
                    @else<i data-lucide="building-2" class="w-6 h-6 text-[#D9CFBB]"></i>@endif
                </div>
                <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $typeCls[$partner->partner_type] ?? 'bg-[#EEECE6] text-[#6F6B60]' }}">{{ $partner->partner_type }}</span>
            </div>
            <a href="{{ route('partners.show', ['id' => $partner->id, 'lang' => $lang]) }}" class="text-[13.5px] font-bold text-[#1D1B16] hover:text-[#14652F]">{{ $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}</a>
            <p class="text-[11px] text-[#8A857A] mt-0.5 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>{{ $partner->address ?? $partner->country }}</p>
            @if($partner->description_fr)
            <p class="text-[11.5px] text-[#6F6B60] mt-2 line-clamp-3 flex-1">{{ $isFr ? $partner->description_fr : ($partner->description_en ?? $partner->description_fr) }}</p>
            @endif
            <a href="{{ route('partners.show', ['id' => $partner->id, 'lang' => $lang]) }}" class="mt-3 block text-center border border-[#CFE0D4] text-[#14652F] hover:bg-[#E2F3E8] text-[12px] font-semibold py-2 rounded-lg">{{ $isFr ? 'Voir le profil' : 'View profile' }}</a>
        </div>
        @empty
        <div class="col-span-full text-center py-16 text-[#8A857A]">
            <i data-lucide="handshake" class="w-12 h-12 mx-auto mb-3 text-[#DCE7DF]"></i>
            <p class="text-[13.5px]">{{ $isFr ? 'Aucun partenaire trouvé.' : 'No partners found.' }}</p>
        </div>
        @endforelse
    </div>

    @if($partners->hasPages())
    <div class="mt-8 flex justify-center">{{ $partners->onEachSide(1)->links() }}</div>
    @endif

    <div class="mt-8 bg-[#EFF5F0] rounded-2xl px-6 py-5 flex flex-wrap items-center gap-4">
        <i data-lucide="heart-handshake" class="w-[28px] h-[28px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
        <p class="flex-1 min-w-[240px] text-[13px] text-[#3B382F]">
            <span class="font-bold">{{ $isFr ? 'Devenir partenaire ?' : 'Become a partner?' }}</span>
            {{ $isFr ? 'Rejoignez les institutions qui soutiennent l\'artisanat camerounais.' : 'Join the institutions supporting Cameroonian craftsmanship.' }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
</div>
@endsection
