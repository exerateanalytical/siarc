@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i data-lucide="briefcase" class="w-4 h-4 text-amber-600"></i>
                </div>
                <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Mon espace entrepreneur' : 'My Business Space' }}</h1>
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

    @if($business)

    {{-- Business profile card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-4">
        @php
            $coverUrl = ($business->cover_image ?? null) ? asset('storage/' . $business->cover_image) : null;
            $logoUrl  = ($business->logo ?? null) ? asset('storage/' . $business->logo) : null;
        @endphp
        {{-- Cover --}}
        <div class="h-24 sm:h-32 relative" style="{{ $coverUrl ? 'background-image:url('.$coverUrl.');background-size:cover;background-position:center' : 'background: linear-gradient(135deg, #e8880e 0%, #1b4332 100%)' }}">
            <div class="absolute inset-0 bg-black/20"></div>
            @if($business->verification_tier === 'certified')
                <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-1 bg-amber-500 text-white rounded-full text-xs font-bold">
                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Certifiée' : 'Certified' }}
                </span>
            @elseif($business->verification_tier === 'verified')
                <span class="absolute top-3 right-3 inline-flex items-center gap-1 px-2 py-1 bg-green-500 text-white rounded-full text-xs font-bold">
                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Vérifiée' : 'Verified' }}
                </span>
            @endif
        </div>
        <div class="px-4 pb-4">
            <div class="flex items-end gap-3 -mt-7 mb-3">
                <div class="w-14 h-14 rounded-xl border-2 border-white bg-white shadow-sm flex items-center justify-center overflow-hidden shrink-0">
                    @if($logoUrl ?? false)
                        <img src="{{ $logoUrl }}" alt="" class="w-14 h-14 object-cover">
                    @else
                        <i data-lucide="building-2" class="w-6 h-6 text-gray-400"></i>
                    @endif
                </div>
                <div class="pb-1 flex-1 min-w-0">
                    <h2 class="text-base font-bold text-gray-900 truncate">{{ $business->name_fr }}</h2>
                    <p class="text-xs text-gray-500 truncate">{{ $business->tagline_fr ?? ($lang === 'fr' ? 'Aucune description courte.' : 'No tagline set.') }}</p>
                </div>
                <span @class(['px-2 py-1 rounded-full text-xs font-semibold shrink-0', 'bg-green-100 text-green-700' => $business->status === 'published', 'bg-yellow-100 text-yellow-700' => $business->status === 'pending', 'bg-gray-100 text-gray-500' => !in_array($business->status, ['published','pending'])])>
                    {{ ucfirst($business->status) }}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-forest-500 text-white text-sm font-semibold rounded-lg hover:bg-forest-600 transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Voir ma vitrine' : 'View storefront' }}
                </a>
                <a href="{{ route('business.edit') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition-colors">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Modifier' : 'Edit' }}
                </a>
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $productCount }}</p>
            <p class="text-xs text-gray-500 mt-1 flex items-center justify-center gap-1">
                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                {{ $lang === 'fr' ? 'Produits' : 'Products' }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $business->views_count ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1 flex items-center justify-center gap-1">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                {{ $lang === 'fr' ? 'Vues' : 'Views' }}
            </p>
        </div>
        <a href="{{ route('messages.inbox') }}" class="bg-white rounded-xl border border-gray-200 p-4 text-center hover:border-forest-300 transition-colors">
            <p class="text-2xl font-bold text-gray-900">{{ $messageCount }}</p>
            <p class="text-xs text-gray-500 mt-1 flex items-center justify-center gap-1">
                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                {{ $lang === 'fr' ? 'Messages' : 'Messages' }}
            </p>
        </a>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900 capitalize">{{ $business->verification_tier ?? 'basic' }}</p>
            <p class="text-xs text-gray-500 mt-1 flex items-center justify-center gap-1">
                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                {{ $lang === 'fr' ? 'Niveau' : 'Tier' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- My Products --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                    <i data-lucide="package" class="w-4 h-4 text-gray-400"></i>
                    {{ $lang === 'fr' ? 'Mes produits' : 'My Products' }}
                </h2>
                <a href="{{ route('products.web-create') }}" class="text-xs text-forest-500 font-medium">
                    {{ $lang === 'fr' ? '+ Ajouter' : '+ Add' }}
                </a>
            </div>
            @if($products->count())
            <div class="divide-y divide-gray-50">
                @foreach($products as $product)
                <a href="{{ route('products.web-edit', ['slug' => $product->slug]) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
                        <i data-lucide="package" class="w-4 h-4 text-green-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name_fr }}</p>
                        <p class="text-xs text-gray-400">{{ $product->quantity_unit ? 'Unité: ' . $product->quantity_unit : 'Contact pour prix' }}</p>
                    </div>
                    <span @class(['px-2 py-0.5 rounded-full text-xs font-medium shrink-0', 'bg-green-100 text-green-700' => $product->status === 'published', 'bg-gray-100 text-gray-500' => $product->status !== 'published'])>
                        {{ $product->status }}
                    </span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 shrink-0"></i>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 px-4">
                <i data-lucide="package" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                <p class="text-sm text-gray-400 mb-3">{{ $lang === 'fr' ? 'Aucun produit encore.' : 'No products yet.' }}</p>
                <a href="{{ route('products.web-create') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-forest-500 text-white text-sm font-semibold rounded-lg hover:bg-forest-600 transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Ajouter un produit' : 'Add product' }}
                </a>
            </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Actions rapides' : 'Quick Actions' }}</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <a href="{{ route('messages.inbox') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="message-circle" class="w-4 h-4 text-blue-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Mes messages' : 'My messages' }}
                </a>
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Voir ma vitrine' : 'My storefront' }}
                </a>
                <a href="{{ route('business.edit') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="edit-3" class="w-4 h-4 text-amber-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Modifier mon profil' : 'Edit my profile' }}
                </a>
                <a href="{{ route('products.web-create') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-green-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Ajouter un produit' : 'Add product' }}
                </a>
                <a href="{{ route('business.edit') }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="image" class="w-4 h-4 text-purple-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Logo & couverture' : 'Logo & cover' }}
                </a>
                <a href="/api/v1/my/business/verification/apply" target="_blank"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="badge-check" class="w-4 h-4 text-indigo-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Demander vérification' : 'Request verification' }}
                </a>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Explorer la galerie' : 'Browse gallery' }}
                </a>
                <a href="/docs/api" target="_blank"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="code-2" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    API Documentation
                </a>
            </div>
        </div>
    </div>

    @else
    {{-- No business yet --}}
    <div class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-8 sm:p-12 text-center">
        <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i data-lucide="building-2" class="w-7 h-7 text-amber-500"></i>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-2">{{ $lang === 'fr' ? 'Créez votre vitrine' : 'Create your storefront' }}</h2>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
            {{ $lang === 'fr' ? 'Présentez vos produits à des acheteurs du monde entier. Gratuit, rapide, efficace.' : 'Showcase your products to buyers worldwide. Free, fast, effective.' }}
        </p>
        <a href="{{ route('business.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-forest-500 text-white font-semibold rounded-lg hover:bg-forest-600 transition-colors text-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $lang === 'fr' ? 'Créer mon entreprise' : 'Create my business' }}
        </a>
        <p class="text-xs text-gray-400 mt-4">
            <a href="/docs/api" target="_blank" class="text-forest-500 underline">{{ $lang === 'fr' ? 'Voir la documentation API' : 'View API docs' }}</a>
        </p>
    </div>
    @endif
</div>
@endsection
