@extends('layouts.admin')

@php $pageTitle = $lang === 'fr' ? 'Événements' : 'Events'; @endphp

@section('content')
<div class="max-w-3xl">

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

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-6">
        @forelse($events as $event)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-lg bg-forest-50 flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-4 h-4 text-forest-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $event->name_fr }}</p>
                <p class="text-xs text-gray-400">{{ $event->starts_at->format('d/m/Y') }} · {{ $event->exhibitors_count }} {{ $lang === 'fr' ? 'exposants' : 'exhibitors' }} · {{ $event->attendees_count }} {{ $lang === 'fr' ? 'inscrits' : 'attendees' }}</p>
            </div>
            <span @class(['px-2 py-0.5 rounded-full text-xs font-medium shrink-0', 'bg-green-100 text-green-700' => $event->is_published, 'bg-gray-100 text-gray-500' => !$event->is_published])>
                {{ $event->is_published ? ($lang === 'fr' ? 'Publié' : 'Published') : ($lang === 'fr' ? 'Brouillon' : 'Draft') }}
            </span>
            <a href="{{ route('events.show', ['slug' => $event->slug]) }}" target="_blank" class="p-2 rounded-lg hover:bg-gray-50 text-gray-400 shrink-0">
                <i data-lucide="external-link" class="w-4 h-4"></i>
            </a>
            <form method="POST" action="{{ route('admin.events.destroy', ['id' => $event->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer cet événement ?' : 'Delete this event?' }}')">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-500 shrink-0">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun événement pour l\'instant.' : 'No events yet.' }}</div>
        @endforelse
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">{{ $lang === 'fr' ? 'Créer un événement' : 'Create an event' }}</h2>
        <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input name="name_fr" required placeholder="{{ $lang === 'fr' ? 'Nom (français)' : 'Name (French)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                <input name="name_en" placeholder="{{ $lang === 'fr' ? 'Nom (anglais)' : 'Name (English)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Début' : 'Start' }} *</label>
                    <input type="datetime-local" name="starts_at" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Fin' : 'End' }}</label>
                    <input type="datetime-local" name="ends_at" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                </div>
            </div>
            <input name="location_fr" placeholder="{{ $lang === 'fr' ? 'Lieu' : 'Location' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
            <textarea name="description_fr" rows="3" placeholder="{{ $lang === 'fr' ? 'Description (français)' : 'Description (French)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400 resize-none"></textarea>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_published" value="1" checked class="rounded border-gray-300 text-forest-600">
                {{ $lang === 'fr' ? 'Publié (visible publiquement)' : 'Published (publicly visible)' }}
            </label>
            <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Créer' : 'Create' }}
            </button>
        </form>
    </div>
</div>
@endsection
