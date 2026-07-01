@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Entreprises — ' : 'Businesses — ') . 'SIAC Galerie';
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Page header -->
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 mb-1 flex items-center gap-2">
            <i data-lucide="building-2" class="w-5 h-5 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Annuaire des entreprises' : 'Business Directory' }}
        </h1>
        <p class="text-sm text-gray-500">
            {{ $businesses->total() }} {{ $lang === 'fr' ? 'résultat(s)' : 'result(s)' }}
        </p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Filters sidebar -->
        <aside class="lg:w-56 shrink-0">
            <form method="GET" action="{{ route('businesses.index', ['lang' => $lang]) }}" id="filter-form">
                <input type="hidden" name="lang" value="{{ $lang }}">

                <!-- Search -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        {{ $lang === 'fr' ? 'Recherche' : 'Search' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400"></i>
                        <input name="q" type="search" value="{{ request('q') }}"
                            placeholder="{{ $lang === 'fr' ? 'Nom, produit...' : 'Name, product...' }}"
                            class="w-full pl-8 pr-3 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:border-forest-400">
                    </div>
                </div>

                <!-- Industry filter -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        {{ $lang === 'fr' ? 'Secteur' : 'Sector' }}
                    </label>
                    <div class="space-y-1">
                        <label class="flex items-center gap-2 text-sm cursor-pointer py-1">
                            <input type="radio" name="industry" value="" {{ !request('industry') ? 'checked' : '' }} onchange="this.form.submit()" class="text-forest-500">
                            <span class="{{ !request('industry') ? 'font-medium text-gray-900' : 'text-gray-600' }}">
                                {{ $lang === 'fr' ? 'Tous les secteurs' : 'All sectors' }}
                            </span>
                        </label>
                        @foreach($industries as $ind)
                        <label class="flex items-center gap-2 text-sm cursor-pointer py-1">
                            <input type="radio" name="industry" value="{{ $ind->slug }}" {{ request('industry') === $ind->slug ? 'checked' : '' }} onchange="this.form.submit()" class="text-forest-500">
                            <span class="{{ request('industry') === $ind->slug ? 'font-medium text-gray-900' : 'text-gray-600' }}">
                                {{ $lang === 'fr' ? $ind->name_fr : $ind->name_en }}
                            </span>
                            <span class="text-xs text-gray-400 ml-auto">{{ $ind->businesses_count }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Tier filter -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        {{ $lang === 'fr' ? 'Niveau de vérification' : 'Verification tier' }}
                    </label>
                    <div class="space-y-1">
                        @foreach(['' => ($lang === 'fr' ? 'Tous' : 'All'), 'certified' => ($lang === 'fr' ? 'Certifiés' : 'Certified'), 'verified' => ($lang === 'fr' ? 'Vérifiés' : 'Verified'), 'basic' => 'Basic'] as $val => $label)
                        <label class="flex items-center gap-2 text-sm cursor-pointer py-1">
                            <input type="radio" name="tier" value="{{ $val }}" {{ request('tier', '') === $val ? 'checked' : '' }} onchange="this.form.submit()" class="text-forest-500">
                            <span class="{{ request('tier', '') === $val ? 'font-medium text-gray-900' : 'text-gray-600' }}">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Region filter -->
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">
                        {{ $lang === 'fr' ? 'Région' : 'Region' }}
                    </label>
                    <select name="region" onchange="this.form.submit()"
                        class="w-full text-sm bg-white border border-gray-200 rounded-lg py-2 px-3 focus:outline-none focus:border-forest-400">
                        <option value="">{{ $lang === 'fr' ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $region)
                        <option value="{{ $region->code }}" {{ request('region') === $region->code ? 'selected' : '' }}>
                            {{ $lang === 'fr' ? $region->name_fr : $region->name_en }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['q','industry','tier','region']))
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-xs text-forest-600 hover:text-forest-700 flex items-center gap-1 mt-2">
                    <i data-lucide="x" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Effacer les filtres' : 'Clear filters' }}
                </a>
                @endif
            </form>
        </aside>

        <!-- Results -->
        <div class="flex-1 min-w-0">
            @if($businesses->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
                <p class="font-medium text-gray-600">{{ $lang === 'fr' ? 'Aucune entreprise trouvée' : 'No businesses found' }}</p>
                <p class="text-sm mt-1">{{ $lang === 'fr' ? 'Essayez avec d\'autres filtres.' : 'Try different filters.' }}</p>
            </div>
            @else
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 mb-6">
                @foreach($businesses as $business)
                @include('components.business-card', ['business' => $business, 'lang' => $lang])
                @endforeach
            </div>

            <!-- Pagination -->
            @if($businesses->hasPages())
            <div class="flex items-center justify-center gap-1">
                @if($businesses->onFirstPage())
                <span class="px-3 py-1.5 text-sm text-gray-300 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                @else
                <a href="{{ $businesses->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-forest-600 hover:bg-forest-50 rounded-lg transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
                @endif

                <span class="px-3 py-1.5 text-sm text-gray-500">
                    {{ $businesses->currentPage() }} / {{ $businesses->lastPage() }}
                </span>

                @if($businesses->hasMorePages())
                <a href="{{ $businesses->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-forest-600 hover:bg-forest-50 rounded-lg transition-colors">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
                @else
                <span class="px-3 py-1.5 text-sm text-gray-300 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                @endif
            </div>
            @endif
            @endif
        </div>

    </div>
</div>
@endsection
