@extends('layouts.admin')

@php
    $isFr = ($lang ?? 'fr') === 'fr';

    $p = $adminProduct;
    $pName = $isFr ? ($p->name_fr ?? $p->name_en ?? '—') : ($p->name_en ?? $p->name_fr ?? '—');
    $pDesc = $isFr ? ($p->description_fr ?? $p->description_en ?? null) : ($p->description_en ?? $p->description_fr ?? null);

    // Status → pill
    $pdStatusMeta = [
        'published' => [$isFr ? 'En stock' : 'In stock', 'bg-[#E2F3E8] text-[#157A43]'],
        'draft'     => [$isFr ? 'Brouillon' : 'Draft',   'bg-[#FDF3E0] text-[#C97A16]'],
        'archived'  => [$isFr ? 'Archivé' : 'Archived',  'bg-[#EEECE6] text-[#6F6B60]'],
        'suspended' => [$isFr ? 'Suspendu' : 'Suspended','bg-[#FDE8E8] text-[#DC2626]'],
    ];
    [$stLabel, $stCls] = $pdStatusMeta[$p->status ?? ''] ?? [$p->status ?? '—', 'bg-[#EEECE6] text-[#6F6B60]'];

    // Business tier → pill
    $pdTierMeta = [
        'basic'     => [$isFr ? 'Basique' : 'Basic',       'bg-[#EEECE6] text-[#6F6B60]'],
        'verified'  => [$isFr ? 'Vérifié' : 'Verified',    'bg-[#E2F3E8] text-[#157A43]'],
        'certified' => [$isFr ? 'Certifié' : 'Certified',  'bg-[#E8EFFB] text-[#3565DE]'],
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
            <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Détails du Produit' : 'Product Details' }}</h1>
            <nav class="mt-1 flex items-center gap-1.5 text-[12px] text-[#8A857A]">
                <span>{{ $isFr ? 'Accueil' : 'Home' }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('admin.products', $langQ) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Produits & Services' : 'Products & Services' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-[#3B382F]">{{ $isFr ? 'Détails du produit' : 'Product details' }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.products', $langQ) }}" class="h-[40px] inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 text-[13px] font-semibold text-white transition-colors">
                <i data-lucide="edit" class="w-4 h-4"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
            </a>
            <button type="button" class="h-[40px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-4 text-[13px] font-semibold text-[#3B382F] transition-colors">
                <i data-lucide="copy" class="w-4 h-4"></i>{{ $isFr ? 'Dupliquer' : 'Duplicate' }}
            </button>
            <button type="button" class="h-[40px] inline-flex items-center gap-2 bg-[#FBF6EA] border border-[#EAD9AC] hover:border-[#C9942E] rounded-lg px-4 text-[13px] font-semibold text-[#7A5A12] transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}
            </button>
        </div>
    </div>

    <a href="{{ route('admin.products', $langQ) }}" class="mt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#14652F] hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour aux produits' : 'Back to products' }}
    </a>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">

        {{-- ============ LEFT COLUMN ============ --}}
        <div class="space-y-5">

            {{-- Product hero card --}}
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="grid grid-cols-1 md:grid-cols-[auto_1fr] gap-5">

                    {{-- Gallery --}}
                    <div class="flex gap-3">
                        <div class="flex flex-col gap-2.5 w-[62px] shrink-0">
                            @forelse($thumbs->take(4) as $t)
                            <div class="w-[62px] h-[58px] rounded-lg overflow-hidden bg-[#EDE7DA] border border-gray-200">
                                <img src="{{ asset('storage/' . $t->file_path) }}" alt="{{ $t->caption_fr ?? '' }}" class="{{ $ci }}">
                            </div>
                            @empty
                            @endforelse
                            @if($imgs->count() > 5)
                            <div class="w-[62px] h-[58px] rounded-lg bg-[#1B1B18] text-white flex items-center justify-center text-[13px] font-bold">+{{ $imgs->count() - 5 }}</div>
                            @endif
                        </div>
                        <div class="w-[230px] max-w-full h-[248px] rounded-xl overflow-hidden bg-[#EDE7DA] border border-gray-200 flex items-center justify-center">
                            @if($cover)
                            <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $cover->caption_fr ?? $pName }}" class="{{ $ci }}">
                            @else
                            <i data-lucide="image" class="w-10 h-10 text-[#B9B4A9]"></i>
                            @endif
                        </div>
                    </div>

                    {{-- Product summary --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-[24px] font-bold text-[#1B1B18] leading-tight">{{ $pName }}</h2>
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11.5px] font-semibold {{ $stCls }}">{{ $stLabel }}</span>
                        </div>

                        <div class="mt-2.5 flex items-center gap-3 flex-wrap text-[12.5px]">
                            @if($catName)
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11.5px] font-semibold bg-[#F0EAFB] text-[#7C4FE0]">{{ $catName }}</span>
                            @endif
                            <span class="text-[#8A857A]">{{ $isFr ? 'Réf' : 'Ref' }} : <span class="text-[#3B382F] font-medium">{{ $p->sku ?? '—' }}</span></span>
                        </div>

                        <div class="mt-3 flex items-center gap-4 flex-wrap text-[12.5px] text-[#55524A]">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="store" class="w-4 h-4 text-[#8A857A]"></i>
                                {{ $isFr ? 'Vendu par' : 'Sold by' }}
                                @if($p->business_slug ?? null)
                                <a href="{{ route('businesses.show', array_merge(['slug' => $p->business_slug], $langQ)) }}" class="font-semibold text-[#14652F] hover:underline">{{ $p->business_name ?? '—' }}</a>
                                @else
                                <span class="font-semibold text-[#1B1B18]">{{ $p->business_name ?? '—' }}</span>
                                @endif
                            </span>
                            @if($tierLabel)
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $tierCls }}">{{ $tierLabel }}</span>
                            @endif
                            @if($productCity)
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>{{ $productCity }}</span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-center gap-5 text-[12.5px]">
                            <span class="inline-flex items-center gap-1.5 text-[#3B382F]">
                                <i data-lucide="star" class="w-4 h-4 text-[#E6B201]" fill="#E6B201"></i>
                                <span class="font-semibold">{{ $p->quality_score ? number_format($p->quality_score, 1) : '—' }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-[#55524A]">
                                <i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i>{{ number_format($p->views_count ?? 0) }} {{ $isFr ? 'vues' : 'views' }}
                            </span>
                        </div>

                        <p class="mt-4 text-[22px] font-bold text-[#14652F]">
                            {{ $p->price_amount ? number_format($p->price_amount, 0, ',', ' ') : '—' }} {{ $p->price_currency ?? 'FCFA' }}
                        </p>

                        @if($pDesc)
                        <p class="mt-3 text-[12.5px] leading-relaxed text-[#55524A]">{{ $pDesc }}</p>
                        @endif
                    </div>
                </div>

                {{-- Meta row --}}
                <div class="mt-5 pt-4 border-t border-[#F0F1F0] grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="tag" class="w-4 h-4 text-[#8A857A] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Catégorie' : 'Category' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $catName ?? '—' }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="user" class="w-4 h-4 text-[#8A857A] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Sous-catégorie' : 'Sub-category' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $p->product_type ?? '—' }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Créé le' : 'Created' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $pdDate($p->created_at ?? null) }}</p></div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] mt-0.5"></i>
                        <div><p class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Mise à jour' : 'Updated' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $pdDate($p->updated_at ?? null) }}</p></div>
                    </div>
                </div>
            </section>

            {{-- Tab bar + panels --}}
            <section class="bg-white border border-gray-200 rounded-xl">
                <div class="px-5 border-b border-[#F0F1F0] overflow-x-auto">
                    <div class="flex items-center gap-6 min-w-max">
                        @foreach($pdTabs as $i => $tab)
                        <button type="button" class="relative py-3.5 text-[12.5px] whitespace-nowrap {{ $i === 0 ? 'font-semibold text-[#14652F]' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                            {{ $tab }}
                            @if($i === 0)<span class="absolute left-0 right-0 -bottom-px h-[2.5px] bg-[#14652F] rounded-full"></span>@endif
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Panel: Informations Générales --}}
                <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Informations Générales --}}
                    <div>
                        <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'INFORMATIONS GÉNÉRALES' : 'GENERAL INFORMATION' }}</h3>
                        <dl class="mt-4 space-y-3.5">
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Nom du produit' : 'Product name' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $pName }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $p->sku ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Catégorie' : 'Category' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $catName ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Sous-catégorie' : 'Sub-category' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $p->product_type ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Description courte' : 'Short description' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F]">{{ $pDesc ? \Illuminate\Support\Str::limit($pDesc, 90) : '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Description complète' : 'Full description' }}</dt>
                                <dd class="text-[12px] leading-relaxed text-[#55524A]">{{ $pDesc ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Mots-clés' : 'Keywords' }}</dt>
                                <dd class="flex flex-wrap gap-1.5">
                                    @forelse(($productAttributes ?? collect()) as $attr)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium bg-[#E2F3E8] text-[#157A43]">{{ $attr->value_fr }}{{ $attr->unit ? ' '.$attr->unit : '' }}</span>
                                    @empty
                                    <span class="text-[12.5px] text-[#8A857A]">—</span>
                                    @endforelse
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Informations du Vendeur --}}
                    <div>
                        <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'INFORMATIONS DU VENDEUR' : 'SELLER INFORMATION' }}</h3>
                        <dl class="mt-4 space-y-3.5">
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Nom du vendeur' : 'Seller name' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $p->business_name ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Type de vendeur' : 'Seller type' }}</dt>
                                <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Entreprise' : 'Business' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                                <dd>@if($tierLabel)<span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $tierCls }}">{{ $tierLabel }}</span>@else<span class="text-[12.5px] text-[#8A857A]">—</span>@endif</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">Email</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F]">—</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F]">—</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Boutique' : 'Shop' }}</dt>
                                <dd>
                                    @if($p->business_slug ?? null)
                                    <a href="{{ route('businesses.show', array_merge(['slug' => $p->business_slug], $langQ)) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#14652F] hover:underline">{{ $isFr ? 'Voir la boutique' : 'View shop' }}<i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>
                                    @else
                                    <span class="text-[12.5px] text-[#8A857A]">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Localisation' : 'Location' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F]">{{ $productCity ?? '—' }}</dd>
                            </div>
                            <div class="grid grid-cols-[130px_1fr] gap-3">
                                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Membre depuis' : 'Member since' }}</dt>
                                <dd class="text-[12.5px] font-medium text-[#3B382F]">{{ $pdDate($p->business_since ?? null) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            {{-- Prix & Variantes --}}
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'PRIX & VARIANTES' : 'PRICE & VARIANTS' }}</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px]">
                        <thead>
                            <tr class="text-left border-b border-[#F0F1F0]">
                                <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">#</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Variante' : 'Variant' }}</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Prix (FCFA)' : 'Price (FCFA)' }}</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Prix Promo (FCFA)' : 'Promo (FCFA)' }}</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Stock</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">SKU</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4F5F4]">
                            <tr>
                                <td class="py-3.5 pr-3 text-[12.5px] text-[#3B382F]">1</td>
                                <td class="py-3.5 px-2 text-[12.5px] font-semibold text-[#1B1B18]">{{ $pName }}</td>
                                <td class="py-3.5 px-2 text-[12.5px] text-[#3B382F]">{{ $p->price_amount ? number_format($p->price_amount, 0, ',', ' ') : '—' }}</td>
                                <td class="py-3.5 px-2 text-[12.5px] text-[#8A857A]">—</td>
                                <td class="py-3.5 px-2 text-[12.5px] text-[#3B382F]">{{ $p->quantity_available !== null ? number_format($p->quantity_available) : '—' }}</td>
                                <td class="py-3.5 px-2 text-[12.5px] text-[#3B382F]">{{ $p->sku ?? '—' }}</td>
                                <td class="py-3.5 px-2"><span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}">{{ $stLabel }}</span></td>
                                <td class="py-3.5 pl-2">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="w-8 h-8 rounded-lg border border-[#E5E7E5] flex items-center justify-center text-[#55524A]"><i data-lucide="edit" class="w-4 h-4"></i></span>
                                        <span class="w-8 h-8 rounded-lg border border-[#E5E7E5] flex items-center justify-center text-[#55524A]"><i data-lucide="link" class="w-4 h-4"></i></span>
                                        <span class="w-8 h-8 rounded-lg border border-[#E5E7E5] flex items-center justify-center text-[#55524A]"><i data-lucide="more-vertical" class="w-4 h-4"></i></span>
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
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'STATUT & STOCK' : 'STATUS & STOCK' }}</h3>
                <dl class="mt-4 space-y-3.5 text-[12.5px]">
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">{{ $isFr ? 'Statut du produit' : 'Product status' }}</dt>
                        <dd><span class="inline-flex items-center rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}">{{ $stLabel }}</span></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">{{ $isFr ? 'Stock disponible' : 'Available stock' }}</dt>
                        <dd class="font-semibold text-[#1B1B18]">{{ $p->quantity_available !== null ? number_format($p->quantity_available) . ' ' . ($p->quantity_unit ?? ($isFr ? 'pièces' : 'pcs')) : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">{{ $isFr ? 'Stock minimum' : 'Minimum stock' }}</dt>
                        <dd class="font-semibold text-[#1B1B18]">{{ $p->moq !== null ? number_format($p->moq) . ' ' . ($p->quantity_unit ?? ($isFr ? 'pièces' : 'pcs')) : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">UGS</dt>
                        <dd class="font-semibold text-[#1B1B18]">{{ $p->sku ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">{{ $isFr ? 'Poids' : 'Weight' }}</dt>
                        <dd class="font-semibold text-[#1B1B18]">—</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-[#8A857A]">Dimensions</dt>
                        <dd class="font-semibold text-[#1B1B18]">—</dd>
                    </div>
                </dl>
            </section>

            {{-- Historique des Prix --}}
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'HISTORIQUE DES PRIX (FCFA)' : 'PRICE HISTORY (FCFA)' }}</h3>
                    <span class="inline-flex items-center gap-1 text-[11px] text-[#8A857A] border border-[#E5E7E5] rounded-lg px-2.5 py-1">{{ $isFr ? '6 derniers mois' : 'Last 6 months' }}<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
                </div>
                <div class="mt-4 relative h-[190px] rounded-lg border border-dashed border-[#EAE6DC] bg-[#FBFAF6] flex items-center justify-center">
                    <svg viewBox="0 0 320 170" class="absolute inset-0 w-full h-full opacity-30" preserveAspectRatio="none" aria-hidden="true">
                        @foreach([30, 65, 100, 135] as $gy)
                        <line x1="0" y1="{{ $gy }}" x2="320" y2="{{ $gy }}" stroke="#D9D3C5" stroke-width="1"/>
                        @endforeach
                    </svg>
                    <div class="relative text-center px-6">
                        <i data-lucide="line-chart" class="w-7 h-7 text-[#B9B4A9] mx-auto"></i>
                        <p class="mt-2 text-[12px] text-[#8A857A]">{{ $isFr ? "Aucun historique de prix disponible." : 'No price history available.' }}</p>
                    </div>
                </div>
            </section>

            {{-- Performances du Produit --}}
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'PERFORMANCES DU PRODUIT' : 'PRODUCT PERFORMANCE' }}</h3>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-[#EEF5FF] border border-[#DCE8FB] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] leading-none">{{ number_format($p->views_count ?? 0) }}</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#3565DE]"><i data-lucide="eye" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Vues' : 'Views' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#EDF7F0] border border-[#D6ECDE] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#157A43]"><i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Ventes' : 'Sales' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#FFF7EA] border border-[#F5E6C4] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#C97A16]"><i data-lucide="trending-up" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Taux de conv.' : 'Conv. rate' }}</p>
                    </div>
                    <div class="rounded-xl bg-[#F4F0FC] border border-[#E4DAF7] px-4 py-3.5">
                        <p class="text-[20px] font-bold text-[#1B1B18] leading-none">—</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#7C4FE0]"><i data-lucide="star" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Note moyenne' : 'Avg. rating' }}</p>
                    </div>
                </div>
            </section>

            {{-- Actions Rapides --}}
            <section class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'ACTIONS RAPIDES' : 'QUICK ACTIONS' }}</h3>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @if($p->business_slug ?? null)
                    <a href="{{ route('products.show', array_merge(['slug' => $p->slug ?? $p->id], $langQ)) }}" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="eye" class="w-4 h-4 text-[#14652F]"></i>{{ $isFr ? 'Voir le produit' : 'View product' }}
                    </a>
                    @else
                    <span class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#B9B4A9]">
                        <i data-lucide="eye" class="w-4 h-4"></i>{{ $isFr ? 'Voir le produit' : 'View product' }}
                    </span>
                    @endif
                    <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-[#14652F]"></i>{{ $isFr ? 'Historique des ventes' : 'Sales history' }}
                    </button>
                    <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="image" class="w-4 h-4 text-[#14652F]"></i>{{ $isFr ? 'Gérer les images' : 'Manage images' }}
                    </button>
                    <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="package" class="w-4 h-4 text-[#14652F]"></i>{{ $isFr ? 'Ajuster le stock' : 'Adjust stock' }}
                    </button>
                    <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="copy" class="w-4 h-4 text-[#14652F]"></i>{{ $isFr ? 'Dupliquer' : 'Duplicate' }}
                    </button>
                    <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-[#FDECEC] border border-[#F5CFCF] hover:border-[#DC2626] rounded-lg px-3.5 text-[12.5px] font-semibold text-[#DC2626] transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>{{ $isFr ? 'Supprimer' : 'Delete' }}
                    </button>
                </div>
            </section>
        </aside>
    </div>

    <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>

    <script>lucide.createIcons();</script>
@endsection
