@extends('layouts.admin')

@php
    $isFr = ($lang ?? 'fr') === 'fr';

    $p = $adminProduct;
    $pName = $isFr ? ($p->name_fr ?? $p->name_en ?? '—') : ($p->name_en ?? $p->name_fr ?? '—');
    $pDesc = $isFr ? ($p->description_fr ?? $p->description_en ?? null) : ($p->description_en ?? $p->description_fr ?? null);

    // Status → pill
    $pdStatusMeta = [
        'published' => [$isFr ? 'En stock' : 'In stock', 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        'draft'     => [$isFr ? 'Brouillon' : 'Draft',   'bg-[#FDF3E0] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'],
        'archived'  => [$isFr ? 'Archivé' : 'Archived',  'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
        'suspended' => [$isFr ? 'Suspendu' : 'Suspended','bg-[#FDE8E8] dark:bg-[#3A1013] text-[#DC2626] dark:text-[#F0555C]'],
    ];
    [$stLabel, $stCls] = $pdStatusMeta[$p->status ?? ''] ?? [$p->status ?? '—', 'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'];

    // Business tier → pill
    $pdTierMeta = [
        'basic'     => [$isFr ? 'Basique' : 'Basic',       'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
        'verified'  => [$isFr ? 'Vérifié' : 'Verified',    'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        'certified' => [$isFr ? 'Certifié' : 'Certified',  'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5]'],
    ];
    [$tierLabel, $tierCls] = $pdTierMeta[$p->business_tier ?? ''] ?? [null, 'bg-[#E2F3E8] text-[#157A43]'];

    // French dates
    $pdMonthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $pdDate = function ($value) use ($isFr, $pdMonthsFr) {
        if (! $value) return '—';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%d %s %d', $d->day, $pdMonthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    $ci = 'w-full h-full object-cover';

    // Cover + thumbnails
    $imgs = $productImages ?? collect();
    $cover = $imgs->firstWhere('is_cover', true) ?? $imgs->first();
    $thumbs = $imgs->filter(fn ($x) => $x !== $cover)->values();

    // Category chip
    $catName = $productCategory->name_fr ?? null;

    $langQ = ['lang' => $lang];

    // Tabs
    $pdTabs = [
        ($isFr ? 'Informations Générales' : 'General Information'),
        ($isFr ? 'Images & Médias' : 'Images & Media') . ' (' . $imgs->count() . ')',
        ($isFr ? 'Variantes' : 'Variants'),
        ($isFr ? 'Prix & Stock' : 'Price & Stock'),
        ($isFr ? 'Expédition' : 'Shipping'),
        ($isFr ? 'SEO & Visibilité' : 'SEO & Visibility'),
        ($isFr ? 'Historique' : 'History'),
    ];
@endphp

@section('content')

    {{-- Top action row (top bar owned by layout → rendered here) --}}
    <div class="pt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Détails du Produit' : 'Product Details' }}</h1>
            <nav class="mt-1 flex items-center gap-1.5 text-[12px] text-[#8A857A] dark:text-[#868778]">
                <span>{{ $isFr ? 'Accueil' : 'Home' }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('admin.products', $langQ) }}" class="hover:text-[#14652F] dark:hover:text-[#339B56]">{{ $isFr ? 'Produits & Services' : 'Products & Services' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Détails du produit' : 'Product details' }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.products', $langQ) }}" class="ui-btn ui-btn-primary">
                <i data-lucide="edit" class="w-4 h-4"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
            </a>
            <button type="button" class="ui-btn ui-btn-secondary">
                <i data-lucide="copy" class="w-4 h-4"></i>{{ $isFr ? 'Dupliquer' : 'Duplicate' }}
            </button>
            <button type="button" class="ui-btn ui-btn-secondary">
                <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}
            </button>
        </div>
    </div>

    <a href="{{ route('admin.products', $langQ) }}" class="mt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour aux produits' : 'Back to products' }}
    </a>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">

        {{-- ============ LEFT COLUMN ============ --}}
        <div class="space-y-5">

            {{-- Product hero card --}}
            <section class="ui-card">
                <div class="grid grid-cols-1 md:grid-cols-[auto_1fr] gap-5">

                    {{-- Gallery --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col gap-2.5 w-[62px] shrink-0">
                            @forelse($thumbs->take(4) as $t)
                            <div class="w-[62px] h-[58px] rounded-lg overflow-hidden bg-[#EDE7DA] dark:bg-[#1A1E16] border border-[#EFEBE2] dark:border-[#262B21]">
                                <img src="{{ asset('storage/' . $t->file_path) }}" alt="{{ $t->caption_fr ?? '' }}" class="{{ $ci }}">
                            </div>
                            @empty
                            @endforelse
                            @if($imgs->count() > 5)
                            <div class="w-[62px] h-[58px] rounded-lg bg-[#1B1B18] text-white flex items-center justify-center text-[13px] font-bold">+{{ $imgs->count() - 5 }}</div>
                            @endif
                        </div>
                        <div class="w-[230px] max-w-full h-[248px] rounded-xl overflow-hidden bg-[#EDE7DA] dark:bg-[#1A1E16] border border-[#EFEBE2] dark:border-[#262B21] flex items-center justify-center">
                            @if($cover)
                            <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $cover->caption_fr ?? $pName }}" class="{{ $ci }}">
                            @else
                            <i data-lucide="image" class="w-10 h-10 text-[#B9B4A9] dark:text-[#868778]"></i>
                            @endif
                        </div>
                    </div>

                    {{-- Product summary --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-[24px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight">{{ $pName }}</h2>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11.5px] font-semibold {{ $stCls }}">{{ $stLabel }}</span>
                        </div>

                        <div class="mt-2.5 flex items-center gap-3 flex-wrap text-[12.5px]">
                            @if($catName)
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11.5px] font-semibold bg-[#F0EAFB] dark:bg-[#1E1733] text-[#7C4FE0] dark:text-[#BCA4F0]">{{ $catName }}</span>
                            @endif
                            <span class="text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Réf' : 'Ref' }} : <span class="text-[#3B382F] dark:text-[#B4B5A6] font-medium">{{ $p->sku ?? '—' }}</span></span>
                        </div>

                        <div class="mt-3 flex items-center gap-4 flex-wrap text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="store" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>
                                {{ $isFr ? 'Vendu par' : 'Sold by' }}
                                @if($p->business_slug ?? null)
                                <a href="{{ route('businesses.show', array_merge(['slug' => $p->business_slug], $langQ)) }}" class="font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">{{ $p->business_name ?? '—' }}</a>
                                @else
                                <span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->business_name ?? '—' }}</span>
                                @endif
                            </span>
                            @if($tierLabel)
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $tierCls }}">{{ $tierLabel }}</span>
                            @endif
                            @if($productCity)
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>{{ $productCity }}</span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center gap-5 text-[12.5px]">
                            <span class="inline-flex items-center gap-1.5 text-[#3B382F] dark:text-[#B4B5A6]">
                                <i data-lucide="star" class="w-4 h-4 text-[#E6B201] dark:text-[#EDB33A]" fill="#E6B201"></i>
                                <span class="font-semibold">{{ $p->quality_score ? number_format($p->quality_score, 1) : '—' }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-[#55524A] dark:text-[#B4B5A6]">
                                <i data-lucide="eye" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>{{ number_format($p->views_count ?? 0) }} {{ $isFr ? 'vues' : 'views' }}
                            </span>
                        </div>

                        <p class="mt-4 text-[22px] font-bold text-[#14652F] dark:text-[#339B56]">
                            {{ $p->price_amount ? number_format($p->price_amount, 0, ',', ' ') : '—' }} {{ $p->price_currency ?? 'FCFA' }}
                        </p>

                        @if($pDesc)
                        <p class="mt-3 text-[12.5px] leading-relaxed text-[#55524A] dark:text-[#B4B5A6]">{{ $pDesc }}</p>
                        @endif
                    </div>
                </div>

                {{-- Meta row --}}
                <div class="mt-5 pt-4 border-t border-[#EFEBE2] dark:border-[#262B21] grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="tag" class="w-4 h-4 text-[#8A857A] dark:text-[#868778] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Catégorie' : 'Category' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $catName ?? '—' }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="user" class="w-4 h-4 text-[#8A857A] dark:text-[#868778] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Sous-catégorie' : 'Sub-category' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->product_type ?? '—' }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] dark:text-[#868778] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Créé le' : 'Created' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $pdDate($p->created_at ?? null) }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] dark:text-[#868778] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Mise à jour' : 'Updated' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $pdDate($p->updated_at ?? null) }}</p></div>
                    </div>
                </div>
            </section>

            {{-- Tab bar + panels --}}
            <section class="ui-card">
                <div class="px-5 border-b border-[#EFEBE2] dark:border-[#262B21] overflow-x-auto">
                    <div class="flex items-center gap-6 min-w-max">
                        @foreach($pdTabs as $i => $tab)
                        <button type="button" class="relative py-3.5 text-[12.5px] whitespace-nowrap {{ $i === 0 ? 'font-semibold text-[#14652F] dark:text-[#339B56] ' : 'text-[#8A857A] dark:text-[#868778] hover:text-[#3B382F] dark:hover:text-[#B4B5A6] ' }}">
                            {{ $tab }}
                            @if($i === 0)<span class="absolute left-0 right-0 -bottom-px h-[2.5px] bg-[#14652F] dark:bg-[#2E9250] rounded-full"></span>@endif
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Panel: Informations Générales --}}
                <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Informations Générales --}}
                    <div>
                        <h3 class="ui-card-title">{{ $isFr ? 'INFORMATIONS GÉNÉRALES' : 'GENERAL INFORMATION' }}</h3>
                        <dl class="mt-4 space-y-3.5">
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Nom du produit' : 'Product name' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $pName }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->sku ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Catégorie' : 'Category' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $catName ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Sous-catégorie' : 'Sub-category' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->product_type ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Description courte' : 'Short description' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $pDesc ? \Illuminate\Support\Str::limit($pDesc, 90) : '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Description complète' : 'Full description' }}</dt>
                                <dd class="text-[12px] leading-relaxed text-[#55524A] dark:text-[#B4B5A6]">{{ $pDesc ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Mots-clés' : 'Keywords' }}</dt>
                                <dd class="flex flex-wrap gap-1.5">
                                    @forelse(($productAttributes ?? collect()) as $attr)
                                    <span class="ui-pill ui-pill-ok">{{ $attr->value_fr }}{{ $attr->unit ? ' '.$attr->unit : '' }}</span>
                                    @empty
                                    <span class="text-[12.5px] text-[#8A857A] dark:text-[#868778]">—</span>
                                    @endforelse
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Informations du Vendeur --}}
                    <div>
                        <h3 class="ui-card-title">{{ $isFr ? 'INFORMATIONS DU VENDEUR' : 'SELLER INFORMATION' }}</h3>
                        <dl class="mt-4 space-y-3.5">
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Nom du vendeur' : 'Seller name' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->business_name ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Type de vendeur' : 'Seller type' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Entreprise' : 'Business' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                                <dd>@if($tierLabel)<span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $tierCls }}">{{ $tierLabel }}</span>@else<span class="text-[12.5px] text-[#8A857A] dark:text-[#868778]">—</span>@endif</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">Email</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">—</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">—</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Boutique' : 'Shop' }}</dt>
                                <dd>
                                    @if($p->business_slug ?? null)
                                    <a href="{{ route('businesses.show', array_merge(['slug' => $p->business_slug], $langQ)) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Voir la boutique' : 'View shop' }}<i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>
                                    @else
                                    <span class="text-[12.5px] text-[#8A857A] dark:text-[#868778]">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Localisation' : 'Location' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $productCity ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Membre depuis' : 'Member since' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $pdDate($p->business_since ?? null) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            {{-- Prix & Variantes --}}
            <section class="ui-card">
                <h3 class="ui-card-title">{{ $isFr ? 'PRIX & VARIANTES' : 'PRICE & VARIANTS' }}</h3>
                <div class="ui-table-wrap mt-4">
                    <table class="ui-table min-w-[640px]">
                        <thead>
                            <tr class="border-b border-[#EFEBE2] dark:border-[#262B21]">
                                <th>#</th>
                                <th>{{ $isFr ? 'Variante' : 'Variant' }}</th>
                                <th>{{ $isFr ? 'Prix (FCFA)' : 'Price (FCFA)' }}</th>
                                <th>{{ $isFr ? 'Prix Promo (FCFA)' : 'Promo (FCFA)' }}</th>
                                <th>Stock</th>
                                <th>SKU</th>
                                <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $pName }}</td>
                                <td>{{ $p->price_amount ? number_format($p->price_amount, 0, ',', ' ') : '—' }}</td>
                                <td class="text-[#8A857A] dark:text-[#868778]">—</td>
                                <td>{{ $p->quantity_available !== null ? number_format($p->quantity_available) : '—' }}</td>
                                <td>{{ $p->sku ?? '—' }}</td>
                                <td><span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}">{{ $stLabel }}</span></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="w-8 h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]"><i data-lucide="edit" class="w-4 h-4"></i></span>
                                        <span class="w-8 h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]"><i data-lucide="link" class="w-4 h-4"></i></span>
                                        <span class="w-8 h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]"><i data-lucide="more-vertical" class="w-4 h-4"></i></span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        {{-- ============ RIGHT COLUMN ============ --}}
        <aside class="space-y-5">

            {{-- Statut & Stock --}}
            <section class="ui-card">
                <h3 class="ui-card-title">{{ $isFr ? 'STATUT & STOCK' : 'STATUS & STOCK' }}</h3>
                <dl class="mt-4 space-y-3.5 text-[12.5px]">
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Statut du produit' : 'Product status' }}</dt>
                        <dd><span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}">{{ $stLabel }}</span></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Stock disponible' : 'Available stock' }}</dt>
                        <dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->quantity_available !== null ? number_format($p->quantity_available) . ' ' . ($p->quantity_unit ?? ($isFr ? 'pièces' : 'pcs')) : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Stock minimum' : 'Minimum stock' }}</dt>
                        <dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->moq !== null ? number_format($p->moq) . ' ' . ($p->quantity_unit ?? ($isFr ? 'pièces' : 'pcs')) : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">UGS</dt>
                        <dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->sku ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Poids' : 'Weight' }}</dt>
                        <dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">—</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A] dark:text-[#868778]">Dimensions</dt>
                        <dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">—</dd>
                    </div>
                </dl>
            </section>

            {{-- Historique des Prix --}}
            <section class="ui-card">
                <div class="flex items-center justify-between">
                    <h3 class="ui-card-title">{{ $isFr ? 'HISTORIQUE DES PRIX (FCFA)' : 'PRICE HISTORY (FCFA)' }}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] text-[#8A857A] dark:text-[#868778] border border-[#EAE5D8] dark:border-[#262B21] rounded-lg px-2.5 py-1">{{ $isFr ? '6 derniers mois' : 'Last 6 months' }}<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
                </div>
                <div class="mt-4 relative h-[190px] rounded-lg border border-dashed border-[#EAE5D8] dark:border-[#262B21] bg-[#FBFAF6] dark:bg-[#12150F] flex items-center justify-center">
                    <svg viewBox="0 0 320 170" class="absolute inset-0 w-full h-full opacity-30" preserveAspectRatio="none" aria-hidden="true">
                        @foreach([30, 65, 100, 135] as $gy)
                        <line x1="0" y1="{{ $gy }}" x2="320" y2="{{ $gy }}" stroke="#D9D3C5" stroke-width="1"/>
                        @endforeach
                    </svg>
                    <div class="relative text-center px-6">
                        <i data-lucide="line-chart" class="w-7 h-7 text-[#B9B4A9] dark:text-[#868778] mx-auto"></i>
                        <p class="mt-2 text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? "Aucun historique de prix disponible." : 'No price history available.' }}</p>
                    </div>
                </div>
            </section>

            {{-- Performances du Produit --}}
            <section class="ui-card">
                <h3 class="ui-card-title">{{ $isFr ? 'PERFORMANCES DU PRODUIT' : 'PRODUCT PERFORMANCE' }}</h3>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-[#EEF5FF] dark:bg-[#101C33] border border-[#DCE8FB] dark:border-[#20304F] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ number_format($p->views_count ?? 0) }}</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#3565DE] dark:text-[#8FB6F5]"><i data-lucide="eye" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Vues' : 'Views' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#EDF7F0] dark:bg-[#1A1E16] border border-[#D6ECDE] dark:border-[#39402F] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#157A43] dark:text-[#339B56]"><i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Ventes' : 'Sales' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#FFF7EA] dark:bg-[#12150F] border border-[#F5E6C4] dark:border-[#4A3A12] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#C97A16] dark:text-[#EDB33A]"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Taux de conv.' : 'Conv. rate' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#F4F0FC] dark:bg-[#1E1733] border border-[#E4DAF7] dark:border-[#2E2450] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#7C4FE0] dark:text-[#BCA4F0]"><i data-lucide="star" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Note moyenne' : 'Avg. rating' }}</p>
                    </div>
                </div>
            </section>

            {{-- Actions Rapides --}}
            <section class="ui-card">
                <h3 class="ui-card-title">{{ $isFr ? 'ACTIONS RAPIDES' : 'QUICK ACTIONS' }}</h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @if($p->business_slug ?? null)
                    <a href="{{ route('products.show', array_merge(['slug' => $p->slug ?? $p->id], $langQ)) }}" class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="eye" class="w-4 h-4 text-[#14652F] dark:text-[#339B56]"></i>{{ $isFr ? 'Voir le produit' : 'View product' }}
                    </a>
                    @else
                    <span class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="eye" class="w-4 h-4"></i>{{ $isFr ? 'Voir le produit' : 'View product' }}
                    </span>
                    @endif
                    <button type="button" class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-[#14652F] dark:text-[#339B56]"></i>{{ $isFr ? 'Historique des ventes' : 'Sales history' }}
                    </button>
                    <button type="button" class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="image" class="w-4 h-4 text-[#14652F] dark:text-[#339B56]"></i>{{ $isFr ? 'Gérer les images' : 'Manage images' }}
                    </button>
                    <button type="button" class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="package" class="w-4 h-4 text-[#14652F] dark:text-[#339B56]"></i>{{ $isFr ? 'Ajuster le stock' : 'Adjust stock' }}
                    </button>
                    <button type="button" class="ui-btn ui-btn-secondary ui-btn-lg">
                        <i data-lucide="copy" class="w-4 h-4 text-[#14652F] dark:text-[#339B56]"></i>{{ $isFr ? 'Dupliquer' : 'Duplicate' }}
                    </button>
                    <button type="button" class="ui-btn ui-btn-danger ui-btn-lg">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>{{ $isFr ? 'Supprimer' : 'Delete' }}
                    </button>
                </div>
            </section>
        </aside>
    </div>

    <p class="mt-6 text-center text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>

    <script>lucide.createIcons();</script>
@endsection
