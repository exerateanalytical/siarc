@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Partenaires & Sponsors' : 'Partners & Sponsors') . ' — SIAC Galerie';
$tierOrder = ['institutional', 'platinum', 'gold', 'silver', 'partner'];
$tierLabels = [
    'institutional' => $lang === 'fr' ? 'Partenaires institutionnels' : 'Institutional Partners',
    'platinum'      => 'Partenaires Platinum',
    'gold'          => 'Partenaires Gold',
    'silver'        => 'Partenaires Silver',
    'partner'       => $lang === 'fr' ? 'Partenaires' : 'Partners',
];
$grouped = $partners->groupBy('tier');
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 bg-forest-50 text-forest-700 rounded-full px-3 py-1 text-xs font-semibold mb-3">
            <i data-lucide="handshake" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Ils soutiennent la plateforme' : 'They support the platform' }}
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $lang === 'fr' ? 'Nos partenaires & sponsors' : 'Our partners & sponsors' }}</h1>
        <p class="text-sm text-gray-500 max-w-xl mx-auto">
            {{ $lang === 'fr'
                ? 'La Galerie Virtuelle de l\'Artisanat du Cameroun est soutenue par des institutions et entreprises engagées pour le développement du secteur.'
                : 'The Virtual Gallery of Cameroonian Crafts is supported by institutions and businesses committed to the sector\'s development.' }}
        </p>
    </div>

    @foreach($tierOrder as $tier)
        @if($grouped->has($tier))
        <div class="mb-10">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4 text-center">{{ $tierLabels[$tier] }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($grouped[$tier] as $partner)
                <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col items-center text-center hover:shadow-sm transition-shadow">
                    <div class="w-16 h-16 rounded-lg bg-gray-50 flex items-center justify-center mb-3 overflow-hidden">
                        @if($partner->logo_url)
                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name_fr }}" class="w-full h-full object-contain">
                        @else
                        <i data-lucide="building-2" class="w-7 h-7 text-gray-300"></i>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}</p>
                    @if($partner->description_fr)
                    <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ $lang === 'fr' ? $partner->description_fr : ($partner->description_en ?? $partner->description_fr) }}</p>
                    @endif
                    @if($partner->website)
                    <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="text-xs text-forest-600 hover:underline mt-2 flex items-center gap-1">
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
    <div class="text-center py-16 text-gray-400">
        <i data-lucide="handshake" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p>{{ $lang === 'fr' ? 'Aucun partenaire pour l\'instant.' : 'No partners yet.' }}</p>
    </div>
    @endif
</div>
@endsection
