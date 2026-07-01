@extends('layouts.app')

@php $title = ($lang === 'fr' ? 'Secteurs d\'activité' : 'Industry Sectors') . ' — SIAC'; @endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
            <i data-lucide="layers" class="w-5 h-5 text-brand-500"></i>
            {{ $lang === 'fr' ? 'Secteurs d\'activité' : 'Industry Sectors' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $lang === 'fr'
                ? 'Explorez nos entreprises par secteur d\'activité.'
                : 'Explore our businesses by industry sector.'
            }}
        </p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        @foreach($industries as $industry)
        <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $industry->slug]) }}"
            class="group bg-white border border-gray-200 rounded-xl p-4 sm:p-5 hover:border-brand-300 hover:shadow-md transition-all">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center group-hover:bg-brand-100 transition-colors shrink-0">
                    <i data-lucide="{{ $industry->icon ?? 'box' }}" class="w-5 h-5 text-brand-600"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="font-semibold text-gray-900 text-sm line-clamp-1 group-hover:text-brand-600 transition-colors">
                        {{ $lang === 'fr' ? $industry->name_fr : $industry->name_en }}
                    </h2>
                    <p class="text-xs text-gray-400 truncate">{{ $industry->businesses_count }} {{ $lang === 'fr' ? 'entreprises' : 'businesses' }}</p>
                </div>
            </div>
            @if($industry->description_fr)
            <p class="text-xs text-gray-500 line-clamp-2 mb-3">
                {{ $lang === 'fr' ? $industry->description_fr : ($industry->description_en ?? $industry->description_fr) }}
            </p>
            @endif
            <div class="flex items-center text-xs text-brand-600 font-medium gap-1 group-hover:gap-2 transition-all">
                {{ $lang === 'fr' ? 'Voir les entreprises' : 'View businesses' }}
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
