@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-green-600"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Mon espace acheteur' : 'My Buyer Space' }}</h1>
            </div>
            <p class="text-sm text-gray-500 mt-0.5 ml-10">{{ $siacUser['name'] ?? '' }}</p>
        </div>
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="flex items-center gap-1.5 px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors w-full sm:w-auto justify-center">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
            </button>
        </form>
    </div>

    {{-- Hero search banner --}}
    <div class="bg-forest-600 rounded-2xl p-5 mb-5 text-white">
        <p class="text-base font-bold mb-1">{{ $lang === 'fr' ? 'Trouvez vos fournisseurs au Cameroun' : 'Find your Cameroonian suppliers' }}</p>
        <p class="text-sm text-white/70 mb-4">{{ $stats['businesses'] }} {{ $lang === 'fr' ? 'entreprises disponibles — contact direct, sans intermédiaire' : 'businesses available — direct contact, no middleman' }}</p>
        <form action="{{ route('businesses.index', ['lang' => $lang]) }}" method="GET">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                <input name="q" type="search"
                    placeholder="{{ $lang === 'fr' ? 'Rechercher tilapia, artisanat, épices...' : 'Search tilapia, crafts, spices...' }}"
                    class="w-full pl-9 pr-24 py-2.5 bg-white text-gray-900 text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-forest-400 placeholder-gray-400">
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-forest-500 text-white text-sm font-semibold rounded-md hover:bg-forest-600 transition-colors">
                    {{ $lang === 'fr' ? 'Chercher' : 'Search' }}
                </button>
            </div>
        </form>
        <div class="flex flex-wrap gap-2 mt-3">
            <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'aquaculture']) }}" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 rounded-full text-xs text-white transition-colors">
                {{ $lang === 'fr' ? 'Aquaculture' : 'Aquaculture' }}
            </a>
            <a href="{{ route('businesses.index', ['lang' => $lang, 'tier' => 'certified']) }}" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 rounded-full text-xs text-white transition-colors">
                {{ $lang === 'fr' ? 'Certifiées' : 'Certified' }}
            </a>
            <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 rounded-full text-xs text-white transition-colors">
                {{ $lang === 'fr' ? 'Artisanat' : 'Crafts' }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left column: saved + conversations --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Saved Businesses --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                        <i data-lucide="heart" class="w-4 h-4 text-red-400"></i>
                        {{ $lang === 'fr' ? 'Entreprises sauvegardées' : 'Saved Businesses' }}
                        <span class="ml-1 px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-xs">{{ $savedBusinesses->count() }}</span>
                    </h2>
                    <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-xs text-forest-500 font-medium">{{ $lang === 'fr' ? 'Explorer' : 'Browse' }}</a>
                </div>

                @if($savedBusinesses->count())
                <div class="divide-y divide-gray-50">
                    @foreach($savedBusinesses as $row)
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 overflow-hidden border border-gray-100">
                            @if($row->logo ?? false)
                                <img src="{{ asset('storage/' . $row->logo) }}" alt="" class="w-10 h-10 object-cover">
                            @else
                                <i data-lucide="building-2" class="w-4 h-4 text-gray-400"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $row->name_fr }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $row->industry_name ?? '' }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($row->verification_tier === 'certified')
                                <span class="hidden sm:inline px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-medium">{{ $lang === 'fr' ? 'Certifiée' : 'Certified' }}</span>
                            @elseif($row->verification_tier === 'verified')
                                <span class="hidden sm:inline px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium">{{ $lang === 'fr' ? 'Vérifiée' : 'Verified' }}</span>
                            @endif
                            <a href="{{ route('businesses.show', ['slug' => $row->slug, 'lang' => $lang]) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-forest-500 text-white text-xs font-semibold rounded-lg hover:bg-forest-600 transition-colors">
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                {{ $lang === 'fr' ? 'Voir' : 'View' }}
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10 px-4">
                    <i data-lucide="heart" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                    <p class="text-sm text-gray-400 mb-3">{{ $lang === 'fr' ? 'Aucune entreprise sauvegardée.' : 'No saved businesses yet.' }}</p>
                    <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-forest-500 text-white text-sm font-semibold rounded-lg hover:bg-forest-600 transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Parcourir les entreprises' : 'Browse businesses' }}
                    </a>
                </div>
                @endif
            </div>

            {{-- Conversations --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                        <i data-lucide="message-circle" class="w-4 h-4 text-blue-400"></i>
                        {{ $lang === 'fr' ? 'Mes conversations' : 'My Conversations' }}
                        <span class="ml-1 px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded text-xs">{{ $conversations->count() }}</span>
                    </h2>
                    <a href="{{ route('messages.inbox') }}" class="text-xs text-forest-500 font-medium">{{ $lang === 'fr' ? 'Tout voir' : 'View all' }}</a>
                </div>
                @if($conversations->count())
                <div class="divide-y divide-gray-50">
                    @foreach($conversations as $conv)
                    <a href="{{ route('messages.thread', ['id' => $conv->id]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-4 h-4 text-blue-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $conv->subject ?? ($lang === 'fr' ? 'Demande de contact' : 'Contact request') }}</p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($conv->updated_at)->diffForHumans() }}</p>
                        </div>
                        <span @class(['w-2 h-2 rounded-full shrink-0', 'bg-blue-500' => $conv->status === 'active', 'bg-gray-300' => $conv->status !== 'active'])></span>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 px-4">
                    <i data-lucide="message-circle" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                    <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune conversation pour l\'instant.' : 'No conversations yet.' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $lang === 'fr' ? 'Visitez une entreprise et cliquez "Contacter".' : 'Visit a business and click "Contact".' }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">
            {{-- Quick Links --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Navigation rapide' : 'Quick Links' }}</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="building-2" class="w-4 h-4 text-amber-500 shrink-0"></i>
                        {{ $lang === 'fr' ? 'Toutes les entreprises' : 'All businesses' }}
                    </a>
                    <a href="{{ route('industries.index', ['lang' => $lang]) }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="layers" class="w-4 h-4 text-blue-500 shrink-0"></i>
                        {{ $lang === 'fr' ? 'Explorer par secteur' : 'Browse by sector' }}
                    </a>
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'aquaculture']) }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="fish" class="w-4 h-4 text-green-500 shrink-0"></i>
                        SIAC Aquaculture
                    </a>
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'tier' => 'certified']) }}"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="badge-check" class="w-4 h-4 text-purple-500 shrink-0"></i>
                        {{ $lang === 'fr' ? 'Entreprises certifiées' : 'Certified businesses' }}
                    </a>
                    <a href="/api/v1/products" target="_blank"
                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i data-lucide="package" class="w-4 h-4 text-gray-400 shrink-0"></i>
                        {{ $lang === 'fr' ? 'Catalogue produits (API)' : 'Product catalog (API)' }}
                    </a>
                </div>
            </div>

            {{-- Platform stats --}}
            <div class="bg-forest-600 text-white rounded-xl p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide mb-3 opacity-70">{{ $lang === 'fr' ? 'La plateforme SIAC' : 'SIAC Platform' }}</h3>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-80 flex items-center gap-1.5"><i data-lucide="building-2" class="w-3.5 h-3.5"></i> {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</span>
                        <span class="text-sm font-bold">{{ $stats['businesses'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-80 flex items-center gap-1.5"><i data-lucide="package" class="w-3.5 h-3.5"></i> {{ $lang === 'fr' ? 'Produits' : 'Products' }}</span>
                        <span class="text-sm font-bold">{{ $stats['products'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-80 flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5"></i> {{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}</span>
                        <span class="text-sm font-bold">{{ $stats['industries'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
