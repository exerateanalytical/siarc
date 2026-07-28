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
            @php
                // Mirrors Product::primaryImage(): an explicit cover wins, otherwise
                // the first by sort_order — so the badge shows what buyers see.
                $images  = $product->images;
                $coverId = optional($images->firstWhere('is_cover', true) ?? $images->first())->id;
                $lastIdx = $images->count() - 1;
            @endphp
            <p class="ui-card-sub mb-3">{{ $lang === 'fr' ? 'La photo principale est celle affichée dans l\'annuaire, la recherche et les fiches produits.' : 'The main photo is the one shown in the directory, in search and on product cards.' }}</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                @foreach($images as $idx => $img)
                <div class="rounded-lg overflow-hidden border border-[#EFEBE2] dark:border-[#262B21] bg-white dark:bg-[#12150F]">
                    <div class="relative aspect-square">
                        <img src="{{ $img->url }}" alt="" class="w-full h-full object-cover">
                        @if($img->id === $coverId)
                        <span class="ui-pill ui-pill-ok absolute top-1 left-1">
                            <i data-lucide="star" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Principale' : 'Main' }}
                        </span>
                        @else
                        <span class="ui-pill ui-pill-neutral absolute top-1 left-1">{{ $idx + 1 }}</span>
                        @endif
                    </div>

                    {{-- Buttons rather than drag-and-drop: this has to work with a
                         thumb on a mid-range Android browser, where dragging a
                         thumbnail inside a scrolling page is unreliable.

                         The buttons own no <form> of their own — a form nested
                         inside the product form is dropped by the HTML parser,
                         so they point at the forms rendered after </form> via
                         the form= attribute. --}}
                    <div class="flex flex-wrap items-center gap-1 p-1.5 border-t border-[#EFEBE2] dark:border-[#262B21]">
                        <button type="submit" form="pimg-up-{{ $img->id }}" class="ui-btn ui-btn-secondary ui-btn-sm px-2" @disabled($idx === 0)
                                aria-label="{{ $lang === 'fr' ? 'Reculer la photo' : 'Move photo earlier' }}">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        </button>

                        <button type="submit" form="pimg-down-{{ $img->id }}" class="ui-btn ui-btn-secondary ui-btn-sm px-2" @disabled($idx === $lastIdx)
                                aria-label="{{ $lang === 'fr' ? 'Avancer la photo' : 'Move photo later' }}">
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </button>

                        @if($img->id !== $coverId)
                        <button type="submit" form="pimg-cover-{{ $img->id }}" class="ui-btn ui-btn-secondary ui-btn-sm px-2"
                                aria-label="{{ $lang === 'fr' ? 'Définir comme photo principale' : 'Set as main photo' }}">
                            <i data-lucide="star" class="w-3.5 h-3.5"></i>
                        </button>
                        @endif

                        <button type="submit" form="pimg-del-{{ $img->id }}" class="ui-btn ui-btn-danger ui-btn-sm px-2 ml-auto"
                                aria-label="{{ $lang === 'fr' ? 'Supprimer la photo' : 'Delete photo' }}">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
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

    @if($isEdit && $product->images->isNotEmpty())
    {{-- Form owners for the per-photo buttons above; they must live outside the
         product form, which is why they sit here instead of next to the tiles. --}}
    @foreach($product->images as $img)
    <form method="POST" id="pimg-up-{{ $img->id }}" action="{{ route('products.web-move-image', ['slug' => $product->slug, 'imageId' => $img->id]) }}" class="hidden">
        @csrf<input type="hidden" name="direction" value="up">
    </form>
    <form method="POST" id="pimg-down-{{ $img->id }}" action="{{ route('products.web-move-image', ['slug' => $product->slug, 'imageId' => $img->id]) }}" class="hidden">
        @csrf<input type="hidden" name="direction" value="down">
    </form>
    <form method="POST" id="pimg-cover-{{ $img->id }}" action="{{ route('products.web-set-cover-image', ['slug' => $product->slug, 'imageId' => $img->id]) }}" class="hidden">
        @csrf
    </form>
    <form method="POST" id="pimg-del-{{ $img->id }}" action="{{ route('products.web-delete-image', ['slug' => $product->slug, 'imageId' => $img->id]) }}" class="hidden">
        @csrf
    </form>
    @endforeach
    @endif
</div>
@endsection
