@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Partenaires & Sponsors' : 'Partners & Sponsors') . ' — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun';
$tierOrder = ['institutional', 'platinum', 'gold', 'silver', 'partner'];
$tierLabels = [
    'institutional' => $lang === 'fr' ? 'Partenaires institutionnels' : 'Institutional Partners',
    'platinum'      => 'Partenaires Platinum',
    'gold'          => 'Partenaires Gold',
    'silver'        => 'Partenaires Silver',
    'partner'       => $lang === 'fr' ? 'Partenaires' : 'Partners',
];
$grouped = $partners->groupBy('tier');

// Same design logo tiles as the landing replica (mapped by name_fr)
$partnerTiles = [
    'MINCOMMERCE'                       => 'partner-mincommerce.png',
    'MINAC'                             => 'partner-minac.png',
    'UNESCO'                            => 'partner-unesco.png',
    'ITC'                               => 'partner-itc.png',
    'CEPII Cameroun'                    => 'partner-cepii.png',
    'OAPI'                              => 'partner-oapi.png',
    'Banque Africaine de Développement' => 'partner-bad.png',
    'AFD'                               => 'partner-afd.png',
    'Union Européenne'                  => 'partner-ue.png',
];
@endphp

@section('content')
<section class="bg-[#0B2C1E]">
    <div class="max-w-5xl mx-auto px-4 py-10 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 text-[#E5B54B] rounded-full px-3.5 py-1.5 text-[11.5px] font-semibold mb-3">
            <i data-lucide="handshake" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Ils soutiennent la plateforme' : 'They support the platform' }}
        </div>
        <h1 class="text-[26px] font-bold text-white">{{ $lang === 'fr' ? 'Nos partenaires & sponsors' : 'Our partners & sponsors' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC] max-w-xl mx-auto">
            {{ $lang === 'fr'
                ? 'La Galerie Virtuelle Nationale de l\'Artisanat du Cameroun est soutenue par des institutions et entreprises engagées pour le développement du secteur.'
                : 'The National Virtual Gallery of Cameroonian Crafts is supported by institutions and businesses committed to the sector\'s development.' }}
        </p>
    </div>
</section>

<div class="max-w-5xl mx-auto px-4 py-10">
    @foreach($tierOrder as $tier)
        @if($grouped->has($tier))
        <div class="mb-10">
            <h2 class="text-[12px] font-bold text-[#14652F] uppercase tracking-[0.14em] mb-4 text-center">{{ $tierLabels[$tier] }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($grouped[$tier] as $partner)
                @php $partnerTile = $partnerTiles[$partner->name_fr] ?? null; @endphp
                <div class="bg-white border border-[#ECECEA] rounded-xl p-5 flex flex-col items-center text-center shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div class="w-[72px] h-[72px] rounded-xl bg-[#F8F6F2] border border-[#F0EDE6] flex items-center justify-center mb-3 overflow-hidden">
                        @if($partnerTile)
                        <img src="{{ asset('images/landing/' . $partnerTile) }}" alt="{{ $partner->name_fr }}" class="w-full h-full object-contain">
                        @elseif($partner->logo)
                        <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name_fr }}" class="w-full h-full object-contain">
                        @else
                        <i data-lucide="building-2" class="w-7 h-7 text-[#D9CFBB]"></i>
                        @endif
                    </div>
                    <p class="text-[13px] font-bold text-[#1D1B16]">{{ $lang === 'fr' ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}</p>
                    @if($partner->description_fr)
                    <p class="text-[11.5px] text-[#6F6B60] mt-1 line-clamp-2">{{ $lang === 'fr' ? $partner->description_fr : ($partner->description_en ?? $partner->description_fr) }}</p>
                    @endif
                    @if($partner->website)
                    <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="text-[11.5px] font-semibold text-[#14652F] hover:text-[#14532D] mt-2 flex items-center gap-1">
                        {{ $lang === 'fr' ? 'Visiter le site' : 'Visit website' }}
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach

    @if($partners->isEmpty())
    <div class="text-center py-16 text-[#8A857A]">
        <i data-lucide="handshake" class="w-12 h-12 mx-auto mb-3 text-[#DCE7DF]"></i>
        <p class="text-[13.5px]">{{ $lang === 'fr' ? 'Aucun partenaire pour l\'instant.' : 'No partners yet.' }}</p>
    </div>
    @endif

    <div class="mt-4 bg-[#EFF5F0] rounded-2xl px-6 py-5 flex flex-wrap items-center gap-4">
        <i data-lucide="heart-handshake" class="w-[28px] h-[28px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
        <p class="flex-1 min-w-[240px] text-[13px] text-[#3B382F]">
            <span class="font-bold">{{ $lang === 'fr' ? 'Devenir partenaire ?' : 'Become a partner?' }}</span>
            {{ $lang === 'fr' ? 'Rejoignez les institutions qui soutiennent l\'artisanat camerounais.' : 'Join the institutions supporting Cameroonian craftsmanship.' }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
            {{ $lang === 'fr' ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
</div>
@endsection
