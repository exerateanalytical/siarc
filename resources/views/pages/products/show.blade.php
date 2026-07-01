@extends('layouts.app')

@php
$name = $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr);
$title = $name . ' — SIAC Galerie';
$description = $lang === 'fr' ? $product->description_fr : ($product->description_en ?? $product->description_fr);
$business = $product->business;
$businessName = $lang === 'fr' ? $business->name_fr : ($business->name_en ?? $business->name_fr);
$categoryName = $product->category ? ($lang === 'fr' ? $product->category->name_fr : ($product->category->name_en ?? $product->category->name_fr)) : null;
$industryName = $product->category?->sector?->industry ? ($lang === 'fr' ? $product->category->sector->industry->name_fr : ($product->category->sector->industry->name_en ?? $product->category->sector->industry->name_fr)) : null;

$priceLabels = [
    'retail'     => $lang === 'fr' ? 'Détail' : 'Retail',
    'wholesale'  => $lang === 'fr' ? 'Gros' : 'Wholesale',
    'negotiable' => $lang === 'fr' ? 'Négociable' : 'Negotiable',
    'contact'    => $lang === 'fr' ? 'Sur demande' : 'On request',
];

$gradeLabels = [
    'premium' => 'Premium',
    'a'       => 'Grade A',
    'b'       => 'Grade B',
    'c'       => 'Grade C',
];

$inspectionLabels = [
    'not_inspected' => $lang === 'fr' ? 'Non inspecté' : 'Not inspected',
    'pending'       => $lang === 'fr' ? 'Inspection en cours' : 'Inspection pending',
    'passed'        => $lang === 'fr' ? 'Inspection réussie' : 'Inspection passed',
    'failed'        => $lang === 'fr' ? 'Inspection échouée' : 'Inspection failed',
];

$imageCategoryLabels = [
    'main' => $lang === 'fr' ? 'Principal' : 'Main', 'farm' => $lang === 'fr' ? 'Ferme' : 'Farm',
    'production' => $lang === 'fr' ? 'Production' : 'Production', 'processing' => $lang === 'fr' ? 'Transformation' : 'Processing',
    'packaging' => $lang === 'fr' ? 'Emballage' : 'Packaging', 'warehouse' => $lang === 'fr' ? 'Entrepôt' : 'Warehouse',
    'workers' => $lang === 'fr' ? 'Équipe' : 'Workers', 'transport' => $lang === 'fr' ? 'Transport' : 'Transport',
    'closeup' => $lang === 'fr' ? 'Gros plan' : 'Close-up', 'harvest' => $lang === 'fr' ? 'Récolte' : 'Harvest', 'other' => $lang === 'fr' ? 'Autre' : 'Other',
];
$videoCategoryLabels = [
    'overview' => $lang === 'fr' ? 'Présentation' : 'Overview', 'production' => $lang === 'fr' ? 'Production' : 'Production',
    'harvest' => $lang === 'fr' ? 'Récolte' : 'Harvest', 'packaging' => $lang === 'fr' ? 'Emballage' : 'Packaging',
    'inspection' => $lang === 'fr' ? 'Inspection qualité' : 'Quality inspection', 'tour' => $lang === 'fr' ? 'Visite' : 'Tour', 'other' => $lang === 'fr' ? 'Autre' : 'Other',
];

$formatAttrValue = function ($attr) use ($lang) {
    $raw = $lang === 'fr' ? $attr->value_fr : ($attr->value_en ?? $attr->value_fr);
    if ($attr->template?->field_type === 'boolean') {
        return $raw == '1' ? ($lang === 'fr' ? 'Oui' : 'Yes') : ($lang === 'fr' ? 'Non' : 'No');
    }
    return $raw;
};

// Split dynamic attributes: farming/production-related field keys go to the Production tab, everything else to Specifications.
$productionFieldKeys = ['methode_elevage', 'type_alimentation', 'marque_aliment', 'taux_conversion', 'temperature_eau', 'ph_eau', 'oxygene_dissous', 'sans_maladie', 'vaccination', 'taux_survie', 'type_transformation'];
$specAttributes = $product->attributes->filter(fn ($a) => ! in_array($a->template?->field_key, $productionFieldKeys));
$productionAttributes = $product->attributes->filter(fn ($a) => in_array($a->template?->field_key, $productionFieldKeys));

$hasTraceability = $product->pond_number || $product->stocking_date || $product->feed_history || $product->treatments_administered || $product->packaging_date || $product->delivery_route;
$hasHealth = $product->veterinary_inspection_at || $product->mortality_rate;
$hasProductionTab = $productionAttributes->isNotEmpty() || $product->harvest_method || $product->next_harvest_at || $hasHealth;
$hasQualityTab = $product->grade || $product->quality_notes || $product->is_certified || $product->inspection_status !== 'not_inspected' || $business->certifications->isNotEmpty() || $product->documents->whereIn('type', ['certificate', 'lab_report', 'health_certificate'])->isNotEmpty();
$hasDocumentsTab = $product->documents->isNotEmpty();
$hasSustainabilityTab = $product->water_usage || $product->energy_source || $product->carbon_footprint || $product->waste_management || $product->environmental_certifications;
$hasPackagingDetails = $product->package_sizes || $product->is_custom_packaging || $product->is_ice_packed || $product->is_vacuum_packed || $product->is_live_transport || $product->is_bulk_packaging;

$avgRating = $sellerStats['avg_rating'];
$imagesByCategory = $product->images->groupBy('category');
$videosByCategory = $product->videos->groupBy('category');
@endphp

@section('content')

<div class="max-w-6xl mx-auto px-4 py-4">
    <!-- Breadcrumb -->
    <div class="text-xs text-gray-500 flex items-center gap-1.5 mb-4 flex-wrap">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-forest-600">{{ $lang === 'fr' ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-forest-600">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" class="hover:text-forest-600 truncate max-w-[140px]">{{ $businessName }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-800 truncate max-w-[160px]">{{ $name }}</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Main column -->
        <div class="flex-1 min-w-0">

            <!-- Gallery -->
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-5">
                @if($product->images->isNotEmpty())
                <div class="aspect-[4/3] sm:aspect-[16/9] bg-gray-100 relative">
                    <img src="{{ $product->images->first()->url }}" alt="{{ $name }}" class="w-full h-full object-cover">
                    @if($product->images->first()->category !== 'main')
                    <span class="absolute top-2 left-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded-full">{{ $imageCategoryLabels[$product->images->first()->category] ?? '' }}</span>
                    @endif
                </div>
                @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-1 p-1">
                    @foreach($product->images->skip(1)->take(8) as $img)
                    <div class="aspect-square bg-gray-100 rounded overflow-hidden relative group">
                        <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                        <span class="absolute bottom-0.5 left-0.5 right-0.5 bg-black/60 text-white text-[9px] px-1 py-0.5 rounded text-center truncate opacity-0 group-hover:opacity-100 transition-opacity">{{ $imageCategoryLabels[$img->category] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                @else
                <div class="aspect-[4/3] sm:aspect-[16/9] bg-gray-100 flex items-center justify-center">
                    <i data-lucide="package" class="w-16 h-16 text-gray-300"></i>
                </div>
                @endif
                @if($product->videos->isNotEmpty())
                <div class="grid grid-cols-2 gap-1 p-1">
                    @foreach($product->videos as $video)
                    <div class="aspect-video bg-gray-900 rounded overflow-hidden relative">
                        @if($video->type === 'upload')
                        <video src="{{ $video->url }}" controls class="w-full h-full" title="{{ $lang === 'fr' ? $video->caption_fr : ($video->caption_en ?? $video->caption_fr) }}"></video>
                        @else
                        <iframe src="{{ $video->embed_url }}" class="w-full h-full" frameborder="0" allowfullscreen title="{{ $lang === 'fr' ? $video->caption_fr : ($video->caption_en ?? $video->caption_fr) }}"></iframe>
                        @endif
                        <span class="absolute top-1.5 left-1.5 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded-full pointer-events-none">{{ $videoCategoryLabels[$video->category] ?? '' }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Title + badges -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        @if($categoryName)
                        <p class="text-xs font-medium text-forest-600 uppercase tracking-wide mb-1">{{ $categoryName }}{{ $product->product_type ? ' · ' . $product->product_type : '' }}</p>
                        @endif
                        <h1 class="text-xl font-bold text-gray-900">{{ $name }}</h1>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <form method="POST" action="{{ route('products.toggle-save', ['slug' => $product->slug]) }}">
                            @csrf
                            <input type="hidden" name="return_to" value="{{ url()->current() }}">
                            <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border {{ $isSaved ? 'border-forest-300 bg-forest-50 text-forest-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }} text-sm">
                                <i data-lucide="bookmark" class="w-4 h-4 {{ $isSaved ? 'fill-forest-600' : '' }}"></i>
                                {{ $isSaved ? ($lang === 'fr' ? 'Sauvegardé' : 'Saved') : ($lang === 'fr' ? 'Sauvegarder' : 'Save') }}
                            </button>
                        </form>
                        <button type="button" id="share-btn" data-url="{{ url()->current() }}" data-title="{{ $name }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                            <i data-lucide="share-2" class="w-4 h-4"></i>
                            {{ $lang === 'fr' ? 'Partager' : 'Share' }}
                        </button>
                        <button type="button" id="report-toggle" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-500 hover:bg-gray-50">
                            <i data-lucide="flag" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Report form (hidden by default) -->
                <div id="report-form-wrap" class="hidden mb-3 p-3 bg-red-50 border border-red-100 rounded-lg">
                    @if(session('siac_user'))
                    <form method="POST" action="{{ route('products.report', ['slug' => $product->slug]) }}">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ url()->current() }}">
                        <p class="text-xs font-medium text-red-700 mb-2">{{ $lang === 'fr' ? 'Signaler cette annonce' : 'Report this listing' }}</p>
                        <select name="reason" required class="w-full text-sm border border-red-200 rounded-lg px-2 py-1.5 mb-2">
                            <option value="">{{ $lang === 'fr' ? 'Choisir une raison...' : 'Choose a reason...' }}</option>
                            <option value="spam">{{ $lang === 'fr' ? 'Spam' : 'Spam' }}</option>
                            <option value="misleading">{{ $lang === 'fr' ? 'Information trompeuse' : 'Misleading information' }}</option>
                            <option value="inappropriate">{{ $lang === 'fr' ? 'Contenu inapproprié' : 'Inappropriate content' }}</option>
                            <option value="duplicate">{{ $lang === 'fr' ? 'Doublon' : 'Duplicate' }}</option>
                            <option value="other">{{ $lang === 'fr' ? 'Autre' : 'Other' }}</option>
                        </select>
                        <textarea name="details" rows="2" maxlength="1000" placeholder="{{ $lang === 'fr' ? 'Détails (optionnel)' : 'Details (optional)' }}" class="w-full text-sm border border-red-200 rounded-lg px-2 py-1.5 mb-2 resize-none"></textarea>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg">{{ $lang === 'fr' ? 'Envoyer le signalement' : 'Submit report' }}</button>
                    </form>
                    @else
                    <p class="text-xs text-red-700">{{ $lang === 'fr' ? 'Connectez-vous pour signaler cette annonce.' : 'Log in to report this listing.' }}</p>
                    @endif
                </div>

                <!-- Feature badges -->
                <div class="flex flex-wrap gap-2 mb-2">
                    @if($product->grade)
                    <span class="inline-flex items-center gap-1 text-xs bg-brand-50 text-brand-700 px-2.5 py-1 rounded-full font-medium">
                        <i data-lucide="award" class="w-3 h-3"></i>{{ $gradeLabels[$product->grade] ?? $product->grade }}
                    </span>
                    @endif
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

                @if($avgRating > 0)
                <div class="flex items-center gap-1.5 text-sm">
                    <div class="flex items-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <i data-lucide="star" class="w-3.5 h-3.5 {{ $i <= round($avgRating) ? 'text-brand-500 fill-brand-500' : 'text-gray-200 fill-gray-200' }}"></i>
                        @endfor
                    </div>
                    <span class="font-medium text-gray-700">{{ $avgRating }}</span>
                    <span class="text-gray-400">({{ $sellerStats['reviews_count'] }} {{ $lang === 'fr' ? 'avis vendeur' : 'seller reviews' }})</span>
                </div>
                @endif
            </div>

            <!-- Quick stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                @if($product->quantity_available !== null)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="warehouse" class="w-4 h-4 text-forest-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ number_format($product->quantity_available) }}</p>
                    <p class="text-[11px] text-gray-400">{{ $product->quantity_unit }} {{ $lang === 'fr' ? 'disponibles' : 'available' }}</p>
                    @if($product->quantity_updated_at)
                    <p class="text-[10px] text-gray-300 mt-0.5">{{ $lang === 'fr' ? 'MAJ' : 'Updated' }} {{ $product->quantity_updated_at->diffForHumans() }}</p>
                    @endif
                </div>
                @endif
                @if($product->moq)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="layers-3" class="w-4 h-4 text-forest-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ number_format($product->moq) }} {{ $product->moq_unit }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Qté. minimum' : 'Min. order (MOQ)' }}</p>
                </div>
                @endif
                @if($industryName)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="layers" class="w-4 h-4 text-forest-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900 truncate">{{ $industryName }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Secteur' : 'Industry' }}</p>
                </div>
                @endif
                @if($product->originRegion)
                <div class="bg-white border border-gray-200 rounded-xl p-3.5 text-center">
                    <i data-lucide="map-pin" class="w-4 h-4 text-forest-500 mx-auto mb-1.5"></i>
                    <p class="text-sm font-bold text-gray-900">{{ $lang === 'fr' ? $product->originRegion->name_fr : $product->originRegion->name_en }}</p>
                    <p class="text-[11px] text-gray-400">{{ $lang === 'fr' ? 'Origine' : 'Origin' }}</p>
                </div>
                @endif
            </div>

            <!-- Tabs -->
            @php
                $tabs = [];
                $tabs['overview'] = $lang === 'fr' ? 'Aperçu' : 'Overview';
                $tabs['specifications'] = $lang === 'fr' ? 'Spécifications' : 'Specifications';
                if ($hasProductionTab) $tabs['production'] = $lang === 'fr' ? 'Production' : 'Production';
                if ($hasQualityTab) $tabs['quality'] = $lang === 'fr' ? 'Qualité & Certifications' : 'Quality & Certifications';
                $tabs['availability'] = $lang === 'fr' ? 'Disponibilité' : 'Availability';
                if ($hasSustainabilityTab) $tabs['sustainability'] = $lang === 'fr' ? 'Durabilité' : 'Sustainability';
                if ($hasDocumentsTab) $tabs['documents'] = $lang === 'fr' ? 'Documents' : 'Documents';
            @endphp

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-5 overflow-hidden">
                <div class="flex overflow-x-auto border-b border-gray-100 no-scrollbar">
                    @foreach($tabs as $key => $label)
                    <button type="button" data-tab-btn="{{ $key }}"
                        class="tab-btn shrink-0 px-4 py-3 text-xs font-bold border-b-2 transition-colors {{ $loop->first ? 'text-forest-600 border-forest-600' : 'text-gray-400 border-transparent hover:text-gray-600' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

                <div class="p-5">

                    <!-- Overview -->
                    <div data-tab-panel="overview" class="tab-panel">
                        @if($description)
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $description }}</p>
                        @endif
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @if($product->sku)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">SKU</dt><dd class="font-medium text-gray-900">{{ $product->sku }}</dd></div>
                            @endif
                            @if($product->product_type)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Type de produit' : 'Product type' }}</dt><dd class="font-medium text-gray-900">{{ $product->product_type }}</dd></div>
                            @endif
                            @if($product->brand)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Marque' : 'Brand' }}</dt><dd class="font-medium text-gray-900">{{ $product->brand }}</dd></div>
                            @endif
                            @if($product->species)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Espèce' : 'Species' }}</dt><dd class="font-medium text-gray-900">{{ $product->species }}</dd></div>
                            @endif
                            @if($product->scientific_name)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Nom scientifique' : 'Scientific name' }}</dt><dd class="font-medium italic text-gray-900">{{ $product->scientific_name }}</dd></div>
                            @endif
                            @if($product->local_names)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Noms locaux' : 'Local names' }}</dt><dd class="font-medium text-gray-900">{{ $product->local_names }}</dd></div>
                            @endif
                            @if($industryName)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Secteur' : 'Industry' }}</dt><dd class="font-medium text-gray-900">{{ $industryName }}</dd></div>
                            @endif
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Pays d\'origine' : 'Country of origin' }}</dt><dd class="font-medium text-gray-900">{{ $lang === 'fr' ? 'Cameroun' : 'Cameroon' }}</dd></div>
                            @if($product->originRegion)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Région' : 'Region' }}</dt><dd class="font-medium text-gray-900">{{ $lang === 'fr' ? $product->originRegion->name_fr : $product->originRegion->name_en }}</dd></div>
                            @endif
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Producteur' : 'Farm / Manufacturer' }}</dt><dd class="font-medium text-gray-900">{{ $businessName }}</dd></div>
                            @if($product->gps_lat && $product->gps_lng)
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">GPS</dt>
                                <dd><a href="https://maps.google.com/?q={{ $product->gps_lat }},{{ $product->gps_lng }}" target="_blank" rel="noopener" class="font-medium text-forest-600 hover:underline flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>{{ $product->gps_lat }}, {{ $product->gps_lng }}</a></dd>
                            </div>
                            @endif
                            @if($product->batch_number)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Lot de traçabilité' : 'Traceability batch' }}</dt><dd class="font-medium text-gray-900">{{ $product->batch_number }}</dd></div>
                            @endif
                        </dl>

                        @if($hasTraceability)
                        <div class="mt-5 pt-4 border-t border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                                <i data-lucide="route" class="w-3.5 h-3.5"></i>
                                {{ $lang === 'fr' ? 'Traçabilité' : 'Traceability' }}
                            </h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                @if($product->pond_number)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'N° de bassin' : 'Pond number' }}</dt><dd class="font-medium text-gray-900">{{ $product->pond_number }}</dd></div>
                                @endif
                                @if($product->stocking_date)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Date d\'empoissonnement' : 'Stocking date' }}</dt><dd class="font-medium text-gray-900">{{ $product->stocking_date->format('d/m/Y') }}</dd></div>
                                @endif
                                @if($product->packaging_date)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Date d\'emballage' : 'Packaging date' }}</dt><dd class="font-medium text-gray-900">{{ $product->packaging_date->format('d/m/Y') }}</dd></div>
                                @endif
                                @if($product->delivery_route)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Itinéraire de livraison' : 'Delivery route' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->delivery_route }}</dd></div>
                                @endif
                                @if($product->feed_history)
                                <div class="sm:col-span-2 border-b border-gray-100 pb-2"><dt class="text-gray-500 mb-1">{{ $lang === 'fr' ? 'Historique d\'alimentation' : 'Feed history' }}</dt><dd class="font-medium text-gray-900">{{ $product->feed_history }}</dd></div>
                                @endif
                                @if($product->treatments_administered)
                                <div class="sm:col-span-2 pb-2"><dt class="text-gray-500 mb-1">{{ $lang === 'fr' ? 'Traitements administrés' : 'Treatments administered' }}</dt><dd class="font-medium text-gray-900">{{ $product->treatments_administered }}</dd></div>
                                @endif
                            </dl>
                        </div>
                        @endif
                    </div>

                    <!-- Specifications -->
                    <div data-tab-panel="specifications" class="tab-panel hidden">
                        @if($specAttributes->isNotEmpty())
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @foreach($specAttributes as $attr)
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">{{ $lang === 'fr' ? ($attr->template->name_fr ?? $attr->key_fr) : ($attr->template->name_en ?? $attr->key_en ?? $attr->key_fr) }}</dt>
                                <dd class="font-medium text-gray-900 text-right">
                                    {{ $formatAttrValue($attr) }}
                                    @if($attr->unit) <span class="text-gray-400 font-normal">{{ $attr->unit }}</span> @endif
                                </dd>
                            </div>
                            @endforeach
                        </dl>
                        @else
                        <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune spécification renseignée.' : 'No specifications provided.' }}</p>
                        @endif
                    </div>

                    <!-- Production & Farming -->
                    @if($hasProductionTab)
                    <div data-tab-panel="production" class="tab-panel hidden">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-4">
                            @if($product->harvest_method)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Méthode de récolte' : 'Harvest method' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->harvest_method }}</dd></div>
                            @endif
                            @if($product->next_harvest_at)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Prochaine récolte' : 'Next harvest' }}</dt><dd class="font-medium text-gray-900">{{ $product->next_harvest_at->format('d/m/Y') }}</dd></div>
                            @endif
                            @foreach($productionAttributes as $attr)
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">{{ $lang === 'fr' ? ($attr->template->name_fr ?? '') : ($attr->template->name_en ?? '') }}</dt>
                                <dd class="font-medium text-gray-900 text-right">
                                    {{ $formatAttrValue($attr) }}
                                    @if($attr->unit) <span class="text-gray-400 font-normal">{{ $attr->unit }}</span> @endif
                                </dd>
                            </div>
                            @endforeach
                        </dl>

                        @if($hasHealth)
                        <div class="pt-3 border-t border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                                <i data-lucide="heart-pulse" class="w-3.5 h-3.5"></i>
                                {{ $lang === 'fr' ? 'État sanitaire' : 'Health status' }}
                            </h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                @if($product->veterinary_inspection_at)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Inspection vétérinaire' : 'Veterinary inspection' }}</dt><dd class="font-medium text-gray-900">{{ $product->veterinary_inspection_at->format('d/m/Y') }}</dd></div>
                                @endif
                                @if($product->mortality_rate !== null)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Taux de mortalité' : 'Mortality rate' }}</dt><dd class="font-medium text-gray-900">{{ $product->mortality_rate }}%</dd></div>
                                @endif
                            </dl>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Quality & Certifications -->
                    @if($hasQualityTab)
                    <div data-tab-panel="quality" class="tab-panel hidden">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-lg font-bold text-forest-600">{{ $qualityScore }}<span class="text-xs text-gray-400 font-normal">/100</span></p>
                                <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Score qualité' : 'Quality score' }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-lg font-bold text-gray-800">{{ $inspectionLabels[$product->inspection_status] }}</p>
                                <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Statut d\'inspection' : 'Inspection status' }}</p>
                            </div>
                        </div>

                        @if($product->grade || $product->quality_notes)
                        <div class="mb-4">
                            @if($product->grade)
                            <span class="inline-flex items-center gap-1 text-xs bg-brand-50 text-brand-700 px-2.5 py-1 rounded-full font-medium mb-2">
                                <i data-lucide="award" class="w-3 h-3"></i>{{ $gradeLabels[$product->grade] ?? $product->grade }}
                            </span>
                            @endif
                            @if($product->quality_notes)
                            <p class="text-sm text-gray-600 leading-relaxed">{{ $product->quality_notes }}</p>
                            @endif
                        </div>
                        @endif

                        @if($business->certifications->isNotEmpty())
                        <div class="mb-4">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $lang === 'fr' ? 'Certifications de l\'entreprise' : 'Business certifications' }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($business->certifications as $cert)
                                <span class="inline-flex items-center gap-1.5 text-xs bg-forest-50 text-forest-700 px-2.5 py-1.5 rounded-lg font-medium">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                    {{ $lang === 'fr' ? ($cert->certification->name_fr ?? '') : ($cert->certification->name_en ?? '') }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($product->environmental_certifications)
                        <div class="mb-4">
                            <span class="inline-flex items-center gap-1.5 text-xs bg-forest-50 text-forest-700 px-2.5 py-1.5 rounded-lg font-medium">
                                <i data-lucide="sprout" class="w-3.5 h-3.5"></i>{{ $product->environmental_certifications }}
                            </span>
                        </div>
                        @endif

                        @php $labDocs = $product->documents->whereIn('type', ['certificate', 'lab_report', 'health_certificate', 'phytosanitary_certificate']); @endphp
                        @if($labDocs->isNotEmpty())
                        <div class="mb-5">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ $lang === 'fr' ? 'Rapports & certificats' : 'Reports & certificates' }}</h3>
                            <div class="space-y-2">
                                @foreach($labDocs as $doc)
                                <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <i data-lucide="file-check-2" class="w-4 h-4 text-forest-500 shrink-0"></i>
                                    <span class="text-sm text-gray-700 truncate flex-1">{{ ($lang === 'fr' ? $doc->name_fr : ($doc->name_en ?? $doc->name_fr)) ?? ucfirst(str_replace('_', ' ', $doc->type)) }}</span>
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-gray-400 shrink-0"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Buyer confidence / reviews -->
                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $lang === 'fr' ? 'Confiance acheteurs' : 'Buyer confidence' }}</h3>
                                @if($avgRating > 0)
                                <div class="flex items-center gap-1 text-sm">
                                    <i data-lucide="star" class="w-3.5 h-3.5 text-brand-500 fill-brand-500"></i>
                                    <span class="font-semibold text-gray-800">{{ $avgRating }}</span>
                                    <span class="text-gray-400">({{ $sellerStats['reviews_count'] }})</span>
                                </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                                <div class="bg-gray-50 rounded-lg p-2.5">
                                    <p class="text-sm font-bold text-gray-900">{{ $sellerStats['reviews_count'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Avis' : 'Reviews' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2.5">
                                    <p class="text-sm font-bold text-gray-900">{{ $sellerStats['repeat_customers'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Clients fidèles' : 'Repeat customers' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2.5">
                                    <p class="text-sm font-bold text-gray-900">{{ $sellerStats['deals_reported'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Affaires déclarées*' : 'Deals reported*' }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2.5">
                                    <p class="text-sm font-bold text-gray-900">{{ $complaintRate }}%</p>
                                    <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Taux de plainte' : 'Complaint rate' }}</p>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-400 mb-4">* {{ $lang === 'fr' ? 'Déclaré par le vendeur, non vérifié par la plateforme (aucune transaction n\'a lieu sur SIAC).' : 'Self-reported by the seller, not platform-verified (no transactions take place on SIAC).' }}</p>

                            @if($business->reviews->isNotEmpty())
                            <div class="space-y-3 mb-4">
                                @foreach($business->reviews->take(5) as $review)
                                <div class="border border-gray-100 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                            <i data-lucide="star" class="w-3 h-3 {{ $i <= $review->rating ? 'text-brand-500 fill-brand-500' : 'text-gray-200 fill-gray-200' }}"></i>
                                            @endfor
                                        </div>
                                        @if($review->is_verified_contact)
                                        <span class="text-[10px] text-forest-600 flex items-center gap-0.5"><i data-lucide="badge-check" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Contact vérifié' : 'Verified contact' }}</span>
                                        @endif
                                    </div>
                                    @if($review->title)<p class="text-sm font-medium text-gray-900">{{ $review->title }}</p>@endif
                                    @if($review->body)<p class="text-xs text-gray-500 mt-0.5">{{ $review->body }}</p>@endif
                                    <p class="text-[10px] text-gray-400 mt-1">{{ $review->reviewer?->name }} — {{ $review->created_at->diffForHumans() }}</p>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if(session('siac_user'))
                            <form method="POST" action="{{ route('reviews.store') }}" class="border-t border-gray-100 pt-3">
                                @csrf
                                <input type="hidden" name="business_slug" value="{{ $business->slug }}">
                                <input type="hidden" name="return_to" value="{{ url()->current() }}">
                                <p class="text-xs font-medium text-gray-700 mb-1.5">{{ $myReview ? ($lang === 'fr' ? 'Modifier votre avis' : 'Edit your review') : ($lang === 'fr' ? 'Laisser un avis sur ce vendeur' : 'Leave a review for this seller') }}</p>
                                <div class="flex items-center gap-1 mb-2" id="rating-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden" {{ old('rating', $myReview->rating ?? 0) == $i ? 'checked' : '' }}>
                                        <i data-lucide="star" class="w-5 h-5 text-gray-200 fill-gray-200 star-icon"></i>
                                    </label>
                                    @endfor
                                </div>
                                <input type="text" name="title" value="{{ old('title', $myReview->title ?? '') }}" maxlength="150" placeholder="{{ $lang === 'fr' ? 'Titre (optionnel)' : 'Title (optional)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:border-forest-400">
                                <textarea name="body" rows="2" maxlength="2000" placeholder="{{ $lang === 'fr' ? 'Votre expérience avec ce vendeur...' : 'Your experience with this seller...' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-2 focus:outline-none focus:border-forest-400 resize-none">{{ old('body', $myReview->body ?? '') }}</textarea>
                                @error('rating')<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
                                <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                    {{ $lang === 'fr' ? 'Publier l\'avis' : 'Submit review' }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Availability & Logistics -->
                    <div data-tab-panel="availability" class="tab-panel hidden">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-5">
                            @if($product->max_order)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Qté. maximum' : 'Max order' }}</dt><dd class="font-medium text-gray-900">{{ number_format($product->max_order) }} {{ $product->moq_unit }}</dd></div>
                            @endif
                            @if($product->daily_production)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Production journalière' : 'Daily production' }}</dt><dd class="font-medium text-gray-900">{{ number_format($product->daily_production) }} {{ $product->production_unit }}</dd></div>
                            @endif
                            @if($product->monthly_production)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Production mensuelle' : 'Monthly production' }}</dt><dd class="font-medium text-gray-900">{{ number_format($product->monthly_production) }} {{ $product->production_unit }}</dd></div>
                            @endif
                            @if($product->annual_production)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Production annuelle' : 'Annual production' }}</dt><dd class="font-medium text-gray-900">{{ number_format($product->annual_production) }} {{ $product->production_unit }}</dd></div>
                            @endif
                            <div class="flex justify-between border-b border-gray-100 pb-2">
                                <dt class="text-gray-500">{{ $lang === 'fr' ? 'Type de prix' : 'Price type' }}</dt>
                                <dd class="font-medium text-gray-900">{{ $priceLabels[$product->price_type] ?? $priceLabels['contact'] }}</dd>
                            </div>
                        </dl>

                        <!-- Packaging -->
                        <div class="mb-5">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <i data-lucide="package-2" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Emballage & Stockage' : 'Packaging & Storage' }}
                            </h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-2">
                                @if($product->packaging_type)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Type d\'emballage' : 'Packaging type' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->packaging_type }}</dd></div>
                                @endif
                                @if($product->package_sizes)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Tailles disponibles' : 'Package sizes' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->package_sizes }}</dd></div>
                                @endif
                                @if($product->shelf_life_days)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Durée de conservation' : 'Shelf life' }}</dt><dd class="font-medium text-gray-900">{{ $product->shelf_life_days }} {{ $lang === 'fr' ? 'jours' : 'days' }}</dd></div>
                                @endif
                                @if($product->storage_conditions)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Conditions de stockage' : 'Storage conditions' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->storage_conditions }}</dd></div>
                                @endif
                            </dl>
                            @if($hasPackagingDetails)
                            <div class="flex flex-wrap gap-2">
                                @if($product->is_custom_packaging)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Emballage personnalisé' : 'Custom packaging' }}</span>@endif
                                @if($product->is_ice_packed)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Emballé sous glace' : 'Ice packed' }}</span>@endif
                                @if($product->is_vacuum_packed)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Sous vide' : 'Vacuum packed' }}</span>@endif
                                @if($product->is_live_transport)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Transport vivant' : 'Live transport' }}</span>@endif
                                @if($product->is_bulk_packaging)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Vrac' : 'Bulk' }}</span>@endif
                            </div>
                            @endif
                        </div>

                        <!-- Logistics -->
                        <div class="mb-5">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Logistique' : 'Logistics' }}
                            </h3>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <span class="text-xs px-2 py-1 rounded-full flex items-center gap-1 {{ $product->pickup_available ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}"><i data-lucide="{{ $product->pickup_available ? 'check' : 'x' }}" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Retrait sur place' : 'Pickup' }}</span>
                                <span class="text-xs px-2 py-1 rounded-full flex items-center gap-1 {{ $product->delivery_available ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}"><i data-lucide="{{ $product->delivery_available ? 'check' : 'x' }}" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Livraison' : 'Delivery' }}</span>
                                @if($product->is_cold_chain)<span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 flex items-center gap-1"><i data-lucide="snowflake" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Chaîne du froid' : 'Cold chain' }}</span>@endif
                                <span class="text-xs px-2 py-1 rounded-full flex items-center gap-1 {{ $product->ready_for_shipment ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}"><i data-lucide="{{ $product->ready_for_shipment ? 'check' : 'x' }}" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Prêt à expédier' : 'Ready for shipment' }}</span>
                                @if($product->container_loading)<span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 flex items-center gap-1"><i data-lucide="container" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Chargement conteneur' : 'Container loading' }}</span>@endif
                            </div>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                @if($product->delivery_radius_km)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Rayon de livraison' : 'Delivery radius' }}</dt><dd class="font-medium text-gray-900">{{ $product->delivery_radius_km }} km</dd></div>
                                @endif
                                @if($product->lead_time_days)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Délai de préparation' : 'Lead time' }}</dt><dd class="font-medium text-gray-900">{{ $product->lead_time_days }} {{ $lang === 'fr' ? 'jours' : 'days' }}</dd></div>
                                @endif
                                @if($product->shipping_company)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Transporteur' : 'Shipping company' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->shipping_company }}</dd></div>
                                @endif
                                @if($product->warehouse_location)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Localisation entrepôt' : 'Warehouse location' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->warehouse_location }}</dd></div>
                                @endif
                                @if($product->shipping_methods)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Modes d\'expédition' : 'Shipping methods' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->shipping_methods }}</dd></div>
                                @endif
                            </dl>
                        </div>

                        <!-- Payment -->
                        <div class="mb-5">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Paiement' : 'Payment' }}
                            </h3>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm mb-2">
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Devises acceptées' : 'Accepted currencies' }}</dt><dd class="font-medium text-gray-900">{{ $product->accepted_currencies }}</dd></div>
                                @if($product->payment_methods)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Moyens de paiement' : 'Payment methods' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->payment_methods }}</dd></div>
                                @endif
                                @if($product->payment_terms)
                                <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500">{{ $lang === 'fr' ? 'Conditions de paiement' : 'Payment terms' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->payment_terms }}</dd></div>
                                @endif
                            </dl>
                            <div class="flex flex-wrap gap-2">
                                @if($product->deposit_required)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Acompte requis' : 'Deposit required' }}</span>@endif
                                @if($product->trade_finance_support)<span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">{{ $lang === 'fr' ? 'Financement commercial' : 'Trade finance support' }}</span>@endif
                            </div>
                        </div>

                        @if($product->harvestDates->isNotEmpty())
                        <div>
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                                <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                                {{ $lang === 'fr' ? 'Calendrier de récolte' : 'Harvest calendar' }}
                            </h3>
                            <div class="space-y-2">
                                @foreach($product->harvestDates as $hd)
                                <div class="flex items-center justify-between p-2.5 rounded-lg {{ $hd->status === 'upcoming' ? 'bg-forest-50' : 'bg-gray-50' }}">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ $hd->status === 'upcoming' ? 'clock' : 'check-circle-2' }}" class="w-4 h-4 {{ $hd->status === 'upcoming' ? 'text-forest-600' : 'text-gray-400' }}"></i>
                                        <span class="text-sm font-medium text-gray-800">{{ $hd->harvest_date->format('d/m/Y') }}</span>
                                        @if($hd->notes)<span class="text-xs text-gray-400">— {{ $hd->notes }}</span>@endif
                                    </div>
                                    @if($hd->expected_quantity)
                                    <span class="text-xs text-gray-500">{{ number_format($hd->expected_quantity) }} {{ $hd->unit }}</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sustainability -->
                    @if($hasSustainabilityTab)
                    <div data-tab-panel="sustainability" class="tab-panel hidden">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            @if($product->water_usage)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500 flex items-center gap-1.5"><i data-lucide="droplets" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Consommation d\'eau' : 'Water usage' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->water_usage }}</dd></div>
                            @endif
                            @if($product->energy_source)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500 flex items-center gap-1.5"><i data-lucide="zap" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Source d\'énergie' : 'Energy source' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->energy_source }}</dd></div>
                            @endif
                            @if($product->carbon_footprint)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500 flex items-center gap-1.5"><i data-lucide="cloud" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Empreinte carbone' : 'Carbon footprint' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->carbon_footprint }}</dd></div>
                            @endif
                            @if($product->waste_management)
                            <div class="flex justify-between border-b border-gray-100 pb-2"><dt class="text-gray-500 flex items-center gap-1.5"><i data-lucide="recycle" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Gestion des déchets' : 'Waste management' }}</dt><dd class="font-medium text-gray-900 text-right">{{ $product->waste_management }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    @endif

                    <!-- Documents -->
                    @if($hasDocumentsTab)
                    <div data-tab-panel="documents" class="tab-panel hidden">
                        <div class="space-y-2">
                            @foreach($product->documents as $doc)
                            <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-white border border-gray-200 rounded-lg flex items-center justify-center shrink-0">
                                    <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ ($lang === 'fr' ? $doc->name_fr : ($doc->name_en ?? $doc->name_fr)) ?? ucfirst(str_replace('_', ' ', $doc->type)) }}</p>
                                    <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $doc->type)) }}</p>
                                </div>
                                <i data-lucide="download" class="w-4 h-4 text-gray-400 shrink-0"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- More from this business -->
            @if($otherProducts->isNotEmpty())
            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="package-search" class="w-4 h-4 text-forest-500"></i>
                    {{ $lang === 'fr' ? 'Autres produits de ' . $businessName : 'More from ' . $businessName }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($otherProducts as $op)
                    <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $op->slug]) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-forest-300 hover:shadow-sm transition-all">
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

            <!-- Similar products from other businesses -->
            @if($similarProducts->isNotEmpty())
            <div class="mb-5">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-forest-500"></i>
                    {{ $lang === 'fr' ? 'Produits similaires' : 'Similar products' }}
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($similarProducts as $sp)
                    <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $sp->slug]) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-forest-300 hover:shadow-sm transition-all">
                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                            @if($sp->primaryImage)
                            <img src="{{ $sp->primaryImage->url }}" alt="" class="w-full h-full object-cover">
                            @else
                            <i data-lucide="package" class="w-6 h-6 text-gray-300"></i>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-gray-800 p-2 truncate">{{ $lang === 'fr' ? $sp->name_fr : ($sp->name_en ?? $sp->name_fr) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Sidebar -->
        <aside class="lg:w-72 shrink-0 space-y-5">

            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>
                {{ session('success') }}
            </div>
            @endif

            <!-- No price shown: request price / message only -->
            <div class="bg-forest-50 border border-forest-100 rounded-xl p-4 flex items-start gap-2.5">
                <i data-lucide="message-circle-question" class="w-4 h-4 shrink-0 mt-0.5 text-forest-700"></i>
                <p class="text-xs text-forest-700 leading-relaxed">
                    {{ $lang === 'fr'
                        ? 'Les prix ne sont pas affichés. Demandez le prix ou envoyez un message directement au vendeur.'
                        : 'Prices are not displayed. Request the price or message the seller directly.' }}
                </p>
            </div>

            <!-- Business card -->
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm sticky top-20">
                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" class="flex items-center gap-3 mb-2 group">
                    <div class="w-11 h-11 shrink-0 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden">
                        @if($business->logo)
                        <img src="{{ $business->logo_url }}" alt="" class="w-full h-full object-cover">
                        @else
                        <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-5 h-5 text-gray-400"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-forest-600 truncate">{{ $businessName }}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                            {{ $business->city->name_fr ?? ($lang === 'fr' ? $business->region->name_fr ?? '' : $business->region->name_en ?? '') }}
                        </p>
                    </div>
                </a>
                @if($avgRating > 0)
                <div class="flex items-center gap-1 mb-3 text-xs">
                    <i data-lucide="star" class="w-3.5 h-3.5 text-brand-500 fill-brand-500"></i>
                    <span class="font-semibold text-gray-800">{{ $avgRating }}</span>
                    <span class="text-gray-400">({{ $sellerStats['reviews_count'] }} {{ $lang === 'fr' ? 'avis' : 'reviews' }})</span>
                </div>
                @endif

                <!-- Seller quick facts -->
                <div class="grid grid-cols-2 gap-2 mb-4 text-center">
                    @if($business->year_established)
                    <div class="bg-gray-50 rounded-lg py-2">
                        <p class="text-sm font-bold text-gray-900">{{ $business->year_established }}</p>
                        <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Depuis' : 'Since' }}</p>
                    </div>
                    @endif
                    @if($business->employee_count)
                    <div class="bg-gray-50 rounded-lg py-2">
                        <p class="text-sm font-bold text-gray-900">{{ $business->employee_count }}</p>
                        <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Employés' : 'Employees' }}</p>
                    </div>
                    @endif
                    @if($business->response_time_hours)
                    <div class="bg-gray-50 rounded-lg py-2">
                        <p class="text-sm font-bold text-gray-900">{{ $business->response_time_hours }}h</p>
                        <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Temps de réponse' : 'Response time' }}</p>
                    </div>
                    @endif
                    @if($business->verification_tier)
                    <div class="bg-gray-50 rounded-lg py-2">
                        <p class="text-sm font-bold text-gray-900 capitalize">{{ $business->verification_tier }}</p>
                        <p class="text-[10px] text-gray-400">{{ $lang === 'fr' ? 'Niveau' : 'Tier' }}</p>
                    </div>
                    @endif
                </div>

                <h3 class="font-semibold text-gray-900 mb-3 text-sm flex items-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4 text-forest-500"></i>
                    {{ $lang === 'fr' ? 'Contacter le vendeur' : 'Contact the seller' }}
                </h3>

                <div class="space-y-2.5">
                    @if($business->phone)
                    <a href="tel:{{ $business->phone }}" class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-7 h-7 bg-forest-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-forest-600"></i>
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
                        <div class="w-7 h-7 bg-forest-100 rounded-lg flex items-center justify-center shrink-0">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-forest-600"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $lang === 'fr' ? 'Envoyer un e-mail' : 'Send email' }}</p>
                    </a>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100">
                    @if(session('siac_user'))
                    <div class="flex gap-2 mb-2">
                        <button type="button" onclick="document.getElementById('msg-body').value = {{ Js::from($lang === 'fr' ? 'Bonjour, pourriez-vous me communiquer le prix et les conditions pour ce produit ?' : 'Hello, could you share the price and terms for this product?') }}"
                            class="flex-1 flex items-center justify-center gap-1.5 px-2 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="message-circle-question" class="w-3.5 h-3.5 text-forest-600"></i>
                            {{ $lang === 'fr' ? 'Demander le prix' : 'Request price' }}
                        </button>
                        <button type="button" onclick="document.getElementById('msg-body').value=''; document.getElementById('msg-body').focus()"
                            class="flex-1 flex items-center justify-center gap-1.5 px-2 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-forest-600"></i>
                            {{ $lang === 'fr' ? 'Message' : 'Message' }}
                        </button>
                    </div>
                    <button type="button" id="book-meeting-btn"
                        onclick="document.getElementById('msg-body').value = {{ Js::from($lang === 'fr' ? 'Bonjour, je souhaiterais organiser un rendez-vous (visite, appel ou rencontre au salon) pour discuter de ce produit. Quelles sont vos disponibilités ?' : 'Hello, I would like to arrange a meeting (visit, call, or meet at the fair) to discuss this product. What are your availabilities?') }}"
                        class="w-full flex items-center justify-center gap-1.5 px-2 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors mb-2">
                        <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-forest-600"></i>
                        {{ $lang === 'fr' ? 'Réserver un rendez-vous' : 'Book a meeting' }}
                    </button>
                    <form method="POST" action="{{ route('messages.send') }}">
                        @csrf
                        <input type="hidden" name="business_slug" value="{{ $business->slug }}">
                        <input type="hidden" name="product_slug" value="{{ $product->slug }}">
                        <input type="hidden" name="return_to" value="{{ url()->current() }}">
                        <textarea id="msg-body" name="body" rows="3" required maxlength="2000"
                            placeholder="{{ $lang === 'fr' ? 'Écrivez votre message au vendeur...' : 'Write your message to the seller...' }}"
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 mb-2 focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 resize-none">{{ old('body') }}</textarea>
                        @error('body')<p class="text-xs text-red-600 mb-2">{{ $message }}</p>@enderror
                        <button type="submit" class="w-full bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            {{ $lang === 'fr' ? 'Envoyer au vendeur' : 'Send to seller' }}
                        </button>
                    </form>
                    @else
                    <a href="/login?next={{ urlencode(url()->current()) }}" class="w-full bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Se connecter pour contacter' : 'Log in to contact seller' }}
                    </a>
                    @endif
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                    <span class="flex items-center gap-1"><i data-lucide="eye" class="w-3 h-3"></i>{{ number_format($product->views_count) }} {{ $lang === 'fr' ? 'vues' : 'views' }}</span>
                    <span class="flex items-center gap-1"><i data-lucide="calendar" class="w-3 h-3"></i>{{ $product->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </aside>

    </div>
</div>

<style>.no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}</style>
<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var key = btn.getAttribute('data-tab-btn');
        document.querySelectorAll('.tab-btn').forEach(function (b) {
            b.classList.remove('text-forest-600', 'border-forest-600');
            b.classList.add('text-gray-400', 'border-transparent');
        });
        btn.classList.remove('text-gray-400', 'border-transparent');
        btn.classList.add('text-forest-600', 'border-forest-600');
        document.querySelectorAll('.tab-panel').forEach(function (p) {
            p.classList.toggle('hidden', p.getAttribute('data-tab-panel') !== key);
        });
    });
});

// Star rating widget: fill all stars up to and including the selected one
var ratingGroup = document.getElementById('rating-stars');
if (ratingGroup) {
    function paintStars() {
        // Re-query on every call: Lucide swaps <i> for <svg> after this script first runs,
        // so cached node references would point at detached elements.
        var selected = 0;
        ratingGroup.querySelectorAll('input[type=radio]').forEach(function (input) {
            if (input.checked) selected = parseInt(input.value, 10);
        });
        ratingGroup.querySelectorAll('.star-icon').forEach(function (star, i) {
            star.classList.toggle('text-brand-500', i < selected);
            star.classList.toggle('fill-brand-500', i < selected);
            star.classList.toggle('text-gray-200', i >= selected);
            star.classList.toggle('fill-gray-200', i >= selected);
        });
    }
    ratingGroup.addEventListener('change', function (e) {
        if (e.target.matches('input[type=radio]')) paintStars();
    });
    setTimeout(paintStars, 0);
}

// Report toggle
var reportToggle = document.getElementById('report-toggle');
var reportWrap = document.getElementById('report-form-wrap');
if (reportToggle && reportWrap) {
    reportToggle.addEventListener('click', function () {
        reportWrap.classList.toggle('hidden');
    });
}

// Share button
var shareBtn = document.getElementById('share-btn');
if (shareBtn) {
    shareBtn.addEventListener('click', function () {
        var url = shareBtn.getAttribute('data-url');
        var title = shareBtn.getAttribute('data-title');
        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(function () {});
        } else if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                var original = shareBtn.innerHTML;
                shareBtn.innerHTML = '<span class="text-forest-600">{{ $lang === "fr" ? "Lien copié !" : "Link copied!" }}</span>';
                setTimeout(function () { shareBtn.innerHTML = original; if (window.lucide) lucide.createIcons(); }, 2000);
            });
        }
    });
}
</script>

@endsection
