@extends('layouts.dashboard')

@php
$isEdit = (bool) $product;
$action = $isEdit ? route('products.web-update', ['slug' => $product->slug]) : route('products.web-store');
$v = fn ($field, $default = '') => old($field, $isEdit ? ($product->{$field} ?? $default) : $default);
$vb = fn ($field, $default = false) => old($field, $isEdit ? (bool) ($product->{$field} ?? $default) : $default);
$pageTitle = $isEdit ? ($lang === 'fr' ? 'Modifier le produit' : 'Edit product') : ($lang === 'fr' ? 'Ajouter un produit' : 'Add a product');

$fileCls = 'ui-file';
@endphp

@section('content')
<div class="max-w-2xl">

    @if($isEdit)
    <div class="flex justify-end mb-3">
        <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $product->slug]) }}" target="_blank" class="ui-btn ui-btn-ghost ui-btn-sm">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Voir la fiche' : 'View listing' }}
        </a>
    </div>
    @endif

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <div>
            <p class="font-semibold mb-1">{{ $lang === 'fr' ? 'Merci de corriger les erreurs suivantes :' : 'Please fix the following errors:' }}</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Informations générales' : 'General information' }}</h2>

            <div class="mb-4">
                <label class="ui-label">{{ $lang === 'fr' ? 'Catégorie' : 'Category' }} <span class="ui-req">*</span></label>
                <select name="category_id" required class="ui-field ui-select">
                    <option value="">{{ $lang === 'fr' ? 'Choisir...' : 'Choose...' }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $v('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->sector ? ($lang === 'fr' ? $cat->sector->name_fr . ' — ' . $cat->name_fr : $cat->sector->name_en . ' — ' . $cat->name_en) : ($lang === 'fr' ? $cat->name_fr : $cat->name_en) }}</option>
                    @endforeach
                </select>
                @if($categories->isEmpty())
                <p class="ui-hint">{{ $lang === 'fr' ? 'Aucune catégorie disponible pour le moment.' : 'No categories available yet.' }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Nom (français)' : 'Name (French)' }} <span class="ui-req">*</span></label>
                    <input name="name_fr" required value="{{ $v('name_fr') }}" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Nom (anglais)' : 'Name (English)' }}</label>
                    <input name="name_en" value="{{ $v('name_en') }}" class="ui-field">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Description (français)' : 'Description (French)' }}</label>
                    <textarea name="description_fr" rows="4" class="ui-field ui-textarea">{{ $v('description_fr') }}</textarea>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Description (anglais)' : 'Description (English)' }}</label>
                    <textarea name="description_en" rows="4" class="ui-field ui-textarea">{{ $v('description_en') }}</textarea>
                </div>
            </div>
        </div>

        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Quantité & Commande' : 'Quantity & Order' }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Qté disponible' : 'Available qty' }}</label>
                    <input type="number" name="quantity_available" value="{{ $v('quantity_available') }}" min="0" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Unité' : 'Unit' }}</label>
                    <input name="quantity_unit" value="{{ $v('quantity_unit') }}" placeholder="kg, pièce..." class="ui-field">
                </div>
                <div>
                    <label class="ui-label">MOQ</label>
                    <input type="number" name="moq" value="{{ $v('moq') }}" min="0" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Unité MOQ' : 'MOQ unit' }}</label>
                    <input name="moq_unit" value="{{ $v('moq_unit') }}" class="ui-field">
                </div>
            </div>
        </div>

        <div class="ui-card">
            <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Type de prix' : 'Price type' }}</h2>
            <p class="ui-card-sub mb-4">{{ $lang === 'fr' ? 'Le prix exact n\'est jamais affiché publiquement — seul le type est visible, les acheteurs doivent vous contacter.' : 'The exact price is never shown publicly — only the type is visible, buyers must contact you.' }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Type' : 'Type' }}</label>
                    <select name="price_type" class="ui-field ui-select">
                        @foreach(['contact' => ($lang === 'fr' ? 'Sur demande' : 'On request'), 'retail' => ($lang === 'fr' ? 'Détail' : 'Retail'), 'wholesale' => ($lang === 'fr' ? 'Gros' : 'Wholesale'), 'negotiable' => ($lang === 'fr' ? 'Négociable' : 'Negotiable')] as $val => $label)
                        <option value="{{ $val }}" {{ $v('price_type', 'contact') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Montant (interne)' : 'Amount (internal)' }}</label>
                    <input type="number" step="0.01" name="price_amount" value="{{ $v('price_amount') }}" min="0" class="ui-field">
                </div>
                <div>
                    <label class="ui-label">{{ $lang === 'fr' ? 'Par' : 'Per' }}</label>
                    <input name="price_unit" value="{{ $v('price_unit') }}" placeholder="kg, unité..." class="ui-field">
                </div>
            </div>
        </div>

        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Caractéristiques' : 'Characteristics' }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach([
                    'is_available' => $lang === 'fr' ? 'Disponible' : 'Available',
                    'is_export_ready' => $lang === 'fr' ? 'Prêt à l\'export' : 'Export ready',
                    'is_organic' => $lang === 'fr' ? 'Biologique' : 'Organic',
                    'is_certified' => $lang === 'fr' ? 'Certifié' : 'Certified',
                    'is_wholesale' => $lang === 'fr' ? 'Vente en gros' : 'Wholesale',
                    'is_retail' => $lang === 'fr' ? 'Vente au détail' : 'Retail',
                    'is_custom_order' => $lang === 'fr' ? 'Sur commande' : 'Custom order',
                ] as $field => $label)
                <label class="ui-check-row items-center cursor-pointer">
                    <input type="checkbox" name="{{ $field }}" value="1" {{ $vb($field, in_array($field, ['is_available','is_retail'])) ? 'checked' : '' }} class="ui-check">
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="ui-card">
            <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Photos' : 'Photos' }}</h2>

            @if($isEdit && $product->images->isNotEmpty())
            <div class="grid grid-cols-4 gap-2 mb-4">
                @foreach($product->images as $img)
                <div class="relative aspect-square rounded-lg overflow-hidden border border-[#EFEBE2] group">
                    <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                    <form method="POST" action="{{ route('products.web-delete-image', ['slug' => $product->slug, 'imageId' => $img->id]) }}" class="absolute top-1 right-1">
                        @csrf
                        <button type="submit" class="w-6 h-6 bg-[#B42025] text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif

            <label class="ui-label">{{ $lang === 'fr' ? 'Ajouter des photos (jusqu\'à 8)' : 'Add photos (up to 8)' }}</label>
            <input type="file" name="images[]" accept="image/*" multiple class="{{ $fileCls }}">
        </div>

        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
            <i data-lucide="{{ $isEdit ? 'save' : 'rocket' }}" class="w-4 h-4"></i>
            {{ $isEdit ? ($lang === 'fr' ? 'Enregistrer les modifications' : 'Save changes') : ($lang === 'fr' ? 'Créer et publier le produit' : 'Create and publish product') }}
        </button>
    </form>
</div>
@endsection
