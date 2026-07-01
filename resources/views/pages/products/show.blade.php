@extends('layouts.app')

@php
$name = $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr);
$title = $name . ' — SIAC Galerie';
$description = $lang === 'fr' ? $product->description_fr : ($product->description_en ?? $product->description_fr);
$business = $product->business;
$businessName = $lang === 'fr' ? $business->name_fr : ($business->name_en ?? $business->name_fr);
$categoryName = $product->category ? ($lang === 'fr' ? $product->category->name_fr : ($product->category->name_en ?? $product->category->name_fr)) : null;

$priceLabels = [
    'retail'     => $lang === 'fr' ? 'Détail' : 'Retail',
    'wholesale'  => $lang === 'fr' ? 'Gros' : 'Wholesale',
    'negotiable' => $lang === 'fr' ? 'Négociable' : 'Negotiable',
    'contact'    => $lang === 'fr' ? 'Prix sur demande' : 'Price on request',
];
@endphp

@section('content')

<div class="max-w-6xl mx-auto px-4 py-4">
    <!-- Breadcrumb -->
    <div class="text-xs text-gray-500 flex items-center gap-1.5 mb-4 flex-wrap">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-brand-600">{{ $lang === 'fr' ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-brand-600">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" class="hover:text-brand-600 truncate max-w-[140px]">{{ $businessName }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-800 truncate max-w-[160px]">{{ $name }}</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Main column -->
        <div class="flex-1 min-w-0">

            <!-- Gallery -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
                @if($product->images->isNotEmpty())
                <div class="aspect-[4/3] sm:aspect-[16/9] bg-gray-100">
                    <img src="{{ $product->images->first()->url }}" alt="{{ $name }}" class="w-full h-full object-cover">
                </div>
                @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-1 p-1">
                    @foreach($product->images->skip(1)->take(4) as $img)
                    <div class="aspect-square bg-gray-100 rounded overflow-hidden">
                        <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <div class="aspect-[4/3] sm:aspect-[16/9] bg-gray-100 flex items-center justify-center">
                    <i data-lucide="package" class="w-16 h-16 text-gray-300"></i>
                </div>
                @endif
            </div>

            <!-- Title + badges -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        @if($categoryName)
                        <p class="text-xs font-medium text-brand-600 uppercase tracking-wide mb-1">{{ $categoryName }}</p>
                        @endif
                        <h1 class="text-xl font-bold text-gray-900">{{ $name }}</h1>
                    </div>
                    <button data-save-product="{{ $product->id }}" class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                        <i data-lucide="bookmark" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Sauvegarder' : 'Save' }}
                    </button>
                </div>

                <!-- Feature badges -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($product->is_export_ready)
                    <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="globe" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Prêt à l\'export' : 'Export ready' }}
                    </span>
                    @endif
                    @if($product->is_organic)
                    <span class="inline-flex items-center gap-1 text-xs bg-green-50 text-green-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="leaf" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Biologique' : 'Organic' }}
                    </span>
                    @endif
                    @if($product->is_certified)
                    <span class="inline-flex items-center gap-1 text-xs bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="badge-check" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Certifié' : 'Certified' }}
                    </span>
                    @endif
                    @if($product->is_wholesale)
                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="boxes" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Vente en gros' : 'Wholesale' }}
                    </span>
                    @endif
                    @if($product->is_retail)
                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="shopping-bag" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Vente au détail' : 'Retail' }}
                    </span>
                    @endif
                    @if($product->is_custom_order)
                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="settings-2" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Sur commande' : 'Custom order' }}
                    </span>
                    @endif
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium {{ $product->is_available ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                        <i data-lucide="{{ $product->is_available ? 'check-circle-2' : 'x-circle' }}" class="w-3 h-3"></i>
                        {{ $product->is_available ? ($lang === 'fr' ? 'Disponible' : 'Available') : ($lang === 'fr' ? 'Indisponible' : 'Unavailable') }}
                    </span>
                </div>

                @if($description)
                <p class="text-sm text-gray-600 leading-relaxed">{{ $description }}</p>
                @endif
            </div>

            <!-- Quantity & Order info -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                @if($product->quantity_available)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="warehouse" class="w-4 h-4 text-brand-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ number_format($product->quantity_available) }}</p>
                    <p class="text-[11px] text-gray-400">{{ $product->quantity_unit }} {{ $lang === 'fr' ? 'disponibles' : 'available' }}</p>
                </div>
                @endif
                @if($product->moq)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="layers-3" class="w-4 h-4 text-brand-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ number_format($product->moq) }} {{ $product->moq_unit }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Qté. minimum' : 'Min. order (MOQ)' }}</p>
                </div>
                @endif
                @if($product->category)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="tag" class="w-4 h-4 text-brand-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $categoryName }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Catégorie' : 'Category' }}</p>
                </div>
                @endif
                @if($product->originRegion)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ $lang === 'fr' ? $product->originRegion->name_fr : $product->originRegion->name_en }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Origine' : 'Origin' }}</p>
                </div>
                @endif
            </div>

            <!-- Specifications (dynamic attributes) -->
            @if($product->attributes->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Spécifications' : 'Specifications' }}
                </h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    @foreach($product->attributes as $attr)
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 text-sm">
                        <dt class="text-gray-500">{{ $lang === 'fr' ? ($attr->template->name_fr ?? $attr->key_fr) : ($attr->template->name_en ?? $attr->key_en ?? $attr->key_fr) }}</dt>
                        <dd class="font-medium text-gray-900 text-right">
                            {{ $lang === 'fr' ? $attr->value_fr : ($attr->value_en ?? $attr->value_fr) }}
                            @if($attr->unit) <span class="text-gray-400 font-normal">{{ $attr->unit }}</span> @endif
                        </dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif

            <!-- Documents -->
            @if($product->documents->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Documents' : 'Documents' }}
                </h2>
                <div class="space-y-2">
                    @foreach($product->documents as $doc)
                    <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $doc->original_filename ?? ucfirst(str_replace('_', ' ', $doc->document_type)) }}</p>
                            <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</p>
                        </div>
                        <i data-lucide="download" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Videos -->
            @if($product->videos->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="video" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Vidéos' : 'Videos' }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($product->videos as $video)
                    <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden">
                        <iframe src="{{ $video->embed_url }}" class="w-full h-full" frameborder="0" allowfullscreen title="{{ $video->title_fr }}"></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- More from this business -->
            @if($otherProducts->isNotEmpty())
            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="package-search" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Autres produits de ' . $businessName : 'More from ' . $businessName }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($otherProducts as $op)
                    <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $op->slug]) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-brand-300 hover:shadow-sm transition-all">
                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                            @if($op->primaryImage)
                            <img src="{{ $op->primaryImage->url }}" alt="" class="w-full h-full object-cover">
                            @else
                            <i data-lucide="package" class="w-6 h-6 text-gray-300"></i>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-gray-800 p-2 truncate">{{ $lang === 'fr' ? $op->name_fr : ($op->name_en ?? $op->name_fr) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 shrink-0 space-y-5">

            <!-- Price -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                @if($product->price_type !== 'contact' && $product->price_amount)
                <p class="text-xs text-gray-400 mb-1">{{ $priceLabels[$product->price_type] ?? '' }}</p>
                <p class="text-2xl font-bold text-gray-900 mb-1">
                    {{ number_format($product->price_amount, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-500">{{ $product->price_currency }}</span>
                </p>
                @if($product->price_unit)
                <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'par' : 'per' }} {{ $product->price_unit }}</p>
                @endif
                @else
                <div class="flex items-center gap-2 text-brand-700">
                    <i data-lucide="message-circle-question" class="w-5 h-5"></i>
                    <p class="text-sm font-semibold">{{ $priceLabels['contact'] }}</p>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $lang === 'fr' ? 'Contactez le vendeur pour les tarifs et conditions.' : 'Contact the seller for pricing and terms.' }}</p>
                @endif
            </div>

            <!-- Business card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm sticky top-20">
                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" class="flex items-center gap-3 mb-4 group">
                    <div class="w-11 h-11 shrink-0 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden">
                        @if($business->logo_path)
                        <img src="{{ Storage::url($business->logo_path) }}" alt="" class="w-full h-full object-cover">
                        @else
                        <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-5 h-5 text-gray-400"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 truncate">{{ $businessName }}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                            {{ $business->city->name_fr ?? ($lang === 'fr' ? $business->region->name_fr ?? '' : $business->region->name_en ?? '') }}
                        </p>
                    </div>
                </a>

                <h3 class="font-semibold text-gray-900 mb-3 text-sm flex items-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4 text-brand-500"></i>
                    {{ $lang === 'fr' ? 'Contacter le vendeur' : 'Contact the seller' }}
                </h3>

                <div class="space-y-2.5">
                    @if($business->phone)
                    <a href="tel:{{ $business->phone }}" class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-brand-600"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">{{ $business->phone }}</p>
                    </a>
                    @endif
                    @if($business->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $business->whatsapp) }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-2.5 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-green-600"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">WhatsApp</p>
                    </a>
                    @endif
                    @if($business->email)
                    <a href="mailto:{{ $business->email }}?subject={{ urlencode($name) }}" class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-brand-600"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $lang === 'fr' ? 'Envoyer un e-mail' : 'Send email' }}</p>
                    </a>
                    @endif
                </div>

                <button class="w-full mt-3 bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Demander un devis' : 'Request a quote' }}
                </button>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                    <span class="flex items-center gap-1"><i data-lucide="eye" class="w-3 h-3"></i>{{ number_format($product->views_count) }} {{ $lang === 'fr' ? 'vues' : 'views' }}</span>
                    <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i>{{ $product->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </aside>

    </div>
</div>

@endsection
