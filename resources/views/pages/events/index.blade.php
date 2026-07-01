@extends('layouts.app')

@php $title = ($lang === 'fr' ? 'Événements' : 'Events') . ' — SIAC Galerie'; @endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <i data-lucide="calendar-days" class="w-5 h-5 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Événements & Salons' : 'Events & Trade Shows' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $lang === 'fr' ? 'Découvrez les salons professionnels et inscrivez-vous pour y participer ou exposer.' : 'Discover trade shows and register to attend or exhibit.' }}
        </p>
    </div>

    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $lang === 'fr' ? 'À venir' : 'Upcoming' }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
        @forelse($upcoming as $event)
        <a href="{{ route('events.show', ['slug' => $event->slug]) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-forest-300 hover:shadow-sm transition-all flex flex-col sm:flex-row">
            <div class="bg-forest-600 text-white p-4 sm:w-28 shrink-0 flex sm:flex-col items-center justify-center gap-2 sm:gap-0 text-center">
                <p class="text-2xl font-bold leading-none">{{ $event->starts_at->format('d') }}</p>
                <p class="text-xs uppercase tracking-wide opacity-80">{{ $event->starts_at->translatedFormat('M Y') }}</p>
            </div>
            <div class="p-4 flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 mb-1">{{ $lang === 'fr' ? $event->name_fr : ($event->name_en ?? $event->name_fr) }}</h3>
                @if($event->location_fr)
                <p class="text-xs text-gray-400 flex items-center gap-1 mb-2">
                    <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $lang === 'fr' ? $event->location_fr : ($event->location_en ?? $event->location_fr) }}
                </p>
                @endif
                @if($event->description_fr)
                <p class="text-xs text-gray-500 line-clamp-2">{{ $lang === 'fr' ? $event->description_fr : ($event->description_en ?? $event->description_fr) }}</p>
                @endif
            </div>
        </a>
        @empty
        <div class="col-span-2 text-center py-12 text-gray-400">
            <i data-lucide="calendar-x" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
            <p>{{ $lang === 'fr' ? 'Aucun événement à venir.' : 'No upcoming events.' }}</p>
        </div>
        @endforelse
    </div>

    @if($past->isNotEmpty())
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ $lang === 'fr' ? 'Événements passés' : 'Past events' }}</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach($past as $event)
        <a href="{{ route('events.show', ['slug' => $event->slug]) }}" class="bg-white border border-gray-200 rounded-xl p-4 hover:border-forest-300 transition-all opacity-80 hover:opacity-100">
            <p class="text-xs text-gray-400 mb-1">{{ $event->starts_at->format('d/m/Y') }}</p>
            <p class="text-sm font-medium text-gray-800 truncate">{{ $lang === 'fr' ? $event->name_fr : ($event->name_en ?? $event->name_fr) }}</p>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
