@extends('layouts.app')

@php
$title = $lang === 'fr'
    ? 'Galerie Virtuelle Nationale de l\'Artisanat — SIAC Cameroun'
    : 'National Virtual Gallery of Crafts — SIAC Cameroon';
@endphp

@section('content')

<!-- Hero -->
<section class="bg-gradient-to-br from-forest-600 to-forest-700 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-64 h-64 rounded-full bg-brand-400 blur-3xl"></div>
        <div class="absolute bottom-0 right-10 w-80 h-80 rounded-full bg-brand-600 blur-3xl"></div>
    </div>
    <div class="relative max-w-6xl mx-auto px-4 py-12 sm:py-20">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-3 py-1 text-xs font-medium mb-4">
                <i data-lucide="calendar" class="w-3 h-3"></i>
                SIAC 2026 — {{ $lang === 'fr' ? '15 Novembre, Yaoundé' : 'November 15, Yaoundé' }}
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight mb-4">
                {{ $lang === 'fr'
                    ? 'Galerie Virtuelle Nationale de l\'Artisanat'
                    : 'National Virtual Gallery of Crafts'
                }}
            </h1>
            <p class="text-white/80 text-base sm:text-lg mb-8 leading-relaxed">
                {{ $lang === 'fr'
                    ? 'Découvrez les artisans, aquaculteurs et producteurs camerounais. Contactez directement les entreprises pour vos achats et partenariats.'
                    : 'Discover Cameroonian artisans, aquaculture farmers and producers. Contact businesses directly for purchases and partnerships.'
                }}
            </p>

            <!-- Search bar -->
            <form action="{{ route('businesses.index', ['lang' => $lang]) }}" method="GET" class="flex gap-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                    <input name="q" type="search"
                        placeholder="{{ $lang === 'fr' ? 'Rechercher une entreprise, un produit...' : 'Search a business, product...' }}"
                        class="w-full pl-10 pr-4 py-3 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-3 rounded-xl text-sm font-medium transition-colors flex items-center gap-2 shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">{{ $lang === 'fr' ? 'Rechercher' : 'Search' }}</span>
                </button>
            </form>

            <!-- Quick stats -->
            <div class="flex flex-wrap gap-4 mt-8 text-sm">
                <div class="flex items-center gap-1.5 text-white/70">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-300"></i>
                    <strong class="text-white">{{ $stats['businesses'] }}</strong> {{ $lang === 'fr' ? 'entreprises' : 'businesses' }}
                </div>
                <div class="flex items-center gap-1.5 text-white/70">
                    <i data-lucide="package" class="w-4 h-4 text-brand-300"></i>
                    <strong class="text-white">{{ $stats['products'] }}</strong> {{ $lang === 'fr' ? 'produits' : 'products' }}
                </div>
                <div class="flex items-center gap-1.5 text-white/70">
                    <i data-lucide="layers" class="w-4 h-4 text-brand-300"></i>
                    <strong class="text-white">{{ $stats['industries'] }}</strong> {{ $lang === 'fr' ? 'secteurs' : 'sectors' }}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Industries grid -->
<section class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <i data-lucide="layers" class="w-5 h-5 text-brand-500"></i>
            {{ $lang === 'fr' ? 'Secteurs d\'activité' : 'Industry Sectors' }}
        </h2>
        <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
            {{ $lang === 'fr' ? 'Voir tout' : 'See all' }}
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($industries as $industry)
        <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $industry->slug]) }}"
            class="group bg-white border border-gray-200 rounded-xl p-4 hover:border-brand-300 hover:shadow-sm transition-all text-center">
            <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-brand-100 transition-colors">
                <i data-lucide="{{ $industry->icon ?? 'box' }}" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm font-medium text-gray-800 leading-tight mb-1">
                {{ $lang === 'fr' ? $industry->name_fr : $industry->name_en }}
            </p>
            <p class="text-xs text-gray-400">{{ $industry->businesses_count }} {{ $lang === 'fr' ? 'entreprises' : 'businesses' }}</p>
        </a>
        @endforeach
    </div>
</section>

<!-- Featured businesses -->
<section class="bg-white border-y border-gray-100 py-10">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i data-lucide="star" class="w-5 h-5 text-brand-500"></i>
                {{ $lang === 'fr' ? 'Entreprises en vedette' : 'Featured Businesses' }}
            </h2>
            <a href="{{ route('businesses.index', ['lang' => $lang, 'featured' => 1]) }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                {{ $lang === 'fr' ? 'Voir tout' : 'See all' }}
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featured as $business)
            @include('components.business-card', ['business' => $business, 'lang' => $lang])
            @endforeach
        </div>
    </div>
</section>

<!-- SIAC spotlight -->
<section class="bg-brand-50 border-y border-brand-100 py-10">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="fish" class="w-5 h-5 text-forest-600"></i>
            <span class="text-xs font-semibold uppercase tracking-wider text-forest-600">
                {{ $lang === 'fr' ? 'Focus SIAC — Aquaculture' : 'SIAC Focus — Aquaculture' }}
            </span>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-6">
            {{ $lang === 'fr' ? 'Producteurs aquacoles du Cameroun' : 'Cameroonian Aquaculture Producers' }}
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($aquaculture as $business)
            @include('components.business-card', ['business' => $business, 'lang' => $lang])
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'aquaculture']) }}"
                class="inline-flex items-center gap-2 bg-forest-600 hover:bg-forest-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="fish" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Voir tous les producteurs aquacoles' : 'See all aquaculture producers' }}
            </a>
        </div>
    </div>
</section>

<!-- API CTA -->
<section class="max-w-6xl mx-auto px-4 py-10">
    <div class="bg-gray-900 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="code-2" class="w-5 h-5 text-brand-400"></i>
                <span class="text-brand-400 text-xs font-semibold uppercase tracking-wider">API {{ $lang === 'fr' ? 'Publique' : 'Public' }}</span>
            </div>
            <h3 class="text-white text-lg font-semibold mb-1">
                {{ $lang === 'fr' ? 'Intégrez nos données dans votre app' : 'Integrate our data into your app' }}
            </h3>
            <p class="text-gray-400 text-sm">
                {{ $lang === 'fr'
                    ? 'API REST OpenAPI 3.1 — accès libre à 1 000 req/j. Données entreprises, produits, taxonomie.'
                    : 'REST OpenAPI 3.1 API — free access at 1,000 req/day. Business, product, taxonomy data.'
                }}
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 shrink-0">
            <a href="{{ url('/docs/api') }}" target="_blank"
                class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Documentation' : 'Documentation' }}
            </a>
            <a href="{{ url('/api/v1/businesses') }}" target="_blank"
                class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="zap" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Tester l\'API' : 'Try the API' }}
            </a>
        </div>
    </div>
</section>

@endsection
