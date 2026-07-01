@extends('layouts.app')

@php
$name = $lang === 'fr' ? $event->name_fr : ($event->name_en ?? $event->name_fr);
$title = $name . ' — SIAC Galerie';
$description = $lang === 'fr' ? $event->description_fr : ($event->description_en ?? $event->description_fr);
$location = $lang === 'fr' ? $event->location_fr : ($event->location_en ?? $event->location_fr);
$isPast = $event->starts_at->isPast();
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    <div class="text-xs text-gray-500 flex items-center gap-1.5 mb-4">
        <a href="{{ route('events.index') }}" class="hover:text-forest-600">{{ $lang === 'fr' ? 'Événements' : 'Events' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-800 truncate">{{ $name }}</span>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3.5 mb-4">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <div class="bg-gradient-to-br from-forest-600 to-forest-700 text-white rounded-2xl p-6 sm:p-8 mb-6">
        @if($isPast)
        <span class="inline-block bg-white/15 text-xs font-medium px-2.5 py-1 rounded-full mb-3">{{ $lang === 'fr' ? 'Événement passé' : 'Past event' }}</span>
        @endif
        <h1 class="text-2xl font-bold mb-3">{{ $name }}</h1>
        <div class="flex flex-wrap gap-4 text-sm text-white/80">
            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4"></i>
                {{ $event->starts_at->format('d/m/Y') }}@if($event->ends_at) – {{ $event->ends_at->format('d/m/Y') }}@endif
            </span>
            @if($location)
            <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4"></i>{{ $location }}</span>
            @endif
            <span class="flex items-center gap-1.5"><i data-lucide="users" class="w-4 h-4"></i>{{ $attendeeCount }} {{ $lang === 'fr' ? 'inscrits' : 'attending' }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="store" class="w-4 h-4"></i>{{ $event->exhibitingBusinesses->count() }} {{ $lang === 'fr' ? 'exposants' : 'exhibitors' }}</span>
        </div>
    </div>

    @if(! $isPast)
    <div class="flex flex-wrap gap-2 mb-6">
        @if($siacUser)
            @if($isAttending)
            <form method="POST" action="{{ route('events.cancel-attend', ['slug' => $event->slug]) }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                    <i data-lucide="calendar-x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Annuler ma participation' : 'Cancel my attendance' }}
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('events.attend', ['slug' => $event->slug]) }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-medium">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Je participe' : 'I\'ll attend' }}
                </button>
            </form>
            @endif

            @if($myBusiness && ! $isExhibiting)
            <form method="POST" action="{{ route('events.exhibit', ['slug' => $event->slug]) }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-sm font-medium">
                    <i data-lucide="store" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Inscrire mon entreprise comme exposant' : 'Register my business as exhibitor' }}
                </button>
            </form>
            @elseif($isExhibiting)
            <span class="flex items-center gap-2 px-4 py-2.5 bg-green-50 text-green-700 rounded-lg text-sm font-medium">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Votre entreprise est inscrite comme exposant' : 'Your business is registered as an exhibitor' }}
            </span>
            @endif
        @else
        <a href="/login?next={{ urlencode(url()->current()) }}" class="flex items-center gap-2 px-4 py-2.5 bg-forest-600 hover:bg-forest-700 text-white rounded-lg text-sm font-medium">
            <i data-lucide="log-in" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Se connecter pour participer' : 'Log in to attend' }}
        </a>
        @endif
    </div>
    @endif

    @if($description)
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
        <p class="text-sm text-gray-600 leading-relaxed">{{ $description }}</p>
    </div>
    @endif

    <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <i data-lucide="store" class="w-4 h-4 text-forest-500"></i>
        {{ $lang === 'fr' ? 'Entreprises exposantes' : 'Exhibiting businesses' }}
        <span class="text-sm font-normal text-gray-400">({{ $event->exhibitingBusinesses->count() }})</span>
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @forelse($event->exhibitingBusinesses as $business)
        <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" class="bg-white border border-gray-200 rounded-xl p-4 hover:border-forest-300 hover:shadow-sm transition-all text-center">
            <div class="w-10 h-10 bg-forest-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-5 h-5 text-forest-600"></i>
            </div>
            <p class="text-sm font-medium text-gray-800 truncate">{{ $business->name_fr }}</p>
            @if($business->pivot->booth_number)
            <p class="text-xs text-gray-400 mt-0.5">{{ $lang === 'fr' ? 'Stand' : 'Booth' }} {{ $business->pivot->booth_number }}</p>
            @endif
        </a>
        @empty
        <div class="col-span-3 text-center py-10 text-gray-400">
            <p class="text-sm">{{ $lang === 'fr' ? 'Aucun exposant inscrit pour l\'instant.' : 'No exhibitors registered yet.' }}</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
