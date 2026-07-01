@extends('layouts.app')

@php
$name = $lang === 'fr' ? $business->name_fr : ($business->name_en ?? $business->name_fr);
$title = $name . ' — SIAC Galerie';
$description = $lang === 'fr' ? $business->description_fr : ($business->description_en ?? $business->description_fr);
@endphp

@section('content')

<!-- Cover -->
<div class="relative bg-gradient-to-br from-gray-800 to-gray-900 h-40 sm:h-56 overflow-hidden">
    @if($business->cover_path)
    <img src="{{ Storage::url($business->cover_path) }}" alt="" class="w-full h-full object-cover opacity-80">
    @else
    <div class="w-full h-full flex items-center justify-center">
        <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-20 h-20 text-white/20"></i>
    </div>
    @endif
    <!-- Breadcrumb overlay -->
    <div class="absolute top-3 left-4 text-xs text-white/60 flex items-center gap-1.5">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-white">{{ $lang === 'fr' ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-white truncate max-w-32">{{ $name }}</span>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 -mt-8 relative z-10 pb-12">
    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Main column -->
        <div class="flex-1 min-w-0">

            <!-- Business header card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <!-- Logo -->
                    <div class="w-16 h-16 shrink-0 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden">
                        @if($business->logo_path)
                        <img src="{{ Storage::url($business->logo_path) }}" alt="{{ $name }}" class="w-full h-full object-cover">
                        @else
                        <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-8 h-8 text-gray-400"></i>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="text-lg font-bold text-gray-900">{{ $name }}</h1>
                            @if($business->verification_tier === 'certified')
                            <span class="inline-flex items-center gap-1 bg-brand-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                                <i data-lucide="shield-check" class="w-2.5 h-2.5"></i>
                                {{ $lang === 'fr' ? 'Certifié' : 'Certified' }}
                            </span>
                            @elseif($business->verification_tier === 'verified')
                            <span class="inline-flex items-center gap-1 bg-forest-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                                <i data-lucide="shield" class="w-2.5 h-2.5"></i>
                                {{ $lang === 'fr' ? 'Vérifié' : 'Verified' }}
                            </span>
                            @endif
                        </div>
                        @if($business->tagline_fr)
                        <p class="text-sm text-gray-500 mb-2">
                            {{ $lang === 'fr' ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr) }}
                        </p>
                        @endif
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                            @if($business->city || $business->region)
                            <span class="flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                {{ $business->city->name_fr ?? '' }}{{ $business->city && $business->region ? ', ' : '' }}{{ $lang === 'fr' ? $business->region->name_fr : $business->region->name_en }}
                            </span>
                            @endif
                            @if($business->year_established)
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                {{ $lang === 'fr' ? 'Fondé en' : 'Founded' }} {{ $business->year_established }}
                            </span>
                            @endif
                            @if($business->employee_count)
                            <span class="flex items-center gap-1">
                                <i data-lucide="users" class="w-3 h-3"></i>
                                {{ $business->employee_count }} {{ $lang === 'fr' ? 'employés' : 'employees' }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <i data-lucide="layers" class="w-3 h-3"></i>
                                {{ $lang === 'fr' ? $business->industry->name_fr : $business->industry->name_en }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($description)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $description }}</p>
                </div>
                @endif
            </div>

            <!-- Products -->
            @if($business->products->isNotEmpty())
            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="package" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Produits & Services' : 'Products & Services' }}
                    <span class="text-sm font-normal text-gray-400">({{ $business->products->count() }})</span>
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($business->products as $product)
                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 hover:shadow-sm transition-all">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 shrink-0 bg-brand-50 rounded-lg flex items-center justify-center">
                                <i data-lucide="package" class="w-5 h-5 text-brand-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 mb-1">
                                    {{ $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr) }}
                                </h3>
                                @if($product->description_fr)
                                <p class="text-xs text-gray-500 line-clamp-2">
                                    {{ $lang === 'fr' ? $product->description_fr : ($product->description_en ?? $product->description_fr) }}
                                </p>
                                @endif
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @if($product->moq)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                        MOQ: {{ $product->moq }} {{ $product->quantity_unit ?? '' }}
                                    </span>
                                    @endif
                                    @if($product->is_export_ready)
                                    <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <i data-lucide="globe" class="w-2.5 h-2.5"></i>
                                        {{ $lang === 'fr' ? 'Export prêt' : 'Export ready' }}
                                    </span>
                                    @endif
                                    <span class="text-xs font-medium text-brand-600">
                                        {{ $lang === 'fr' ? 'Prix sur demande' : 'Price on request' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar: Contact -->
        <aside class="lg:w-72 shrink-0">
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm sticky top-20">
                <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Contacter cette entreprise' : 'Contact this business' }}
                </h3>

                <div class="space-y-3 mb-4">
                    @if($business->phone)
                    <a href="tel:{{ $business->phone }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-4 h-4 text-brand-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Téléphone' : 'Phone' }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $business->phone }}</p>
                        </div>
                    </a>
                    @endif

                    @if($business->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $business->whatsapp) }}" target="_blank" rel="noopener"
                        class="flex items-center gap-3 p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-green-600">WhatsApp</p>
                            <p class="text-sm font-medium text-gray-900">{{ $business->whatsapp }}</p>
                        </div>
                    </a>
                    @endif

                    @if($business->email)
                    <a href="mailto:{{ $business->email }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-brand-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-4 h-4 text-brand-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Email' : 'Email' }}</p>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $business->email }}</p>
                        </div>
                    </a>
                    @endif
                </div>

                <!-- "Contact for price" notice -->
                <div class="bg-brand-50 border border-brand-100 rounded-lg p-3 text-xs text-brand-700 flex items-start gap-2">
                    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
                    {{ $lang === 'fr'
                        ? 'Les prix ne sont pas affichés — contactez l\'entreprise directement pour connaître les tarifs et conditions.'
                        : 'Prices are not displayed — contact the business directly for pricing and conditions.'
                    }}
                </div>

                @if($business->address_fr)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1 flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        {{ $lang === 'fr' ? 'Adresse' : 'Address' }}
                    </p>
                    <p class="text-xs text-gray-600">{{ $business->address_fr }}</p>
                </div>
                @endif
            </div>
        </aside>

    </div>
</div>

@endsection
