@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $name = $isFr ? $product->name_fr : ($product->name_en ?? $product->name_fr);
    $description = $isFr ? $product->description_fr : ($product->description_en ?? $product->description_fr);
    $business = $product->business;
    $businessName = $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr);
    $cityName = $business->city ? $business->city->name_fr : null;
    $regionName = $business->region ? ($isFr ? $business->region->name_fr : ($business->region->name_en ?? $business->region->name_fr)) : null;
    $locationLabel = trim(($cityName ? $cityName . ', ' : '') . ($regionName ?? ''), ', ');

    $category = $product->category;
    $categoryName = $category ? ($isFr ? $category->name_fr : ($category->name_en ?? $category->name_fr)) : null;
    $sector = $category?->sector;
    $sectorName = $sector ? ($isFr ? $sector->name_fr : ($sector->name_en ?? $sector->name_fr)) : null;
    $industry = $sector?->industry;
    $industryName = $industry ? ($isFr ? $industry->name_fr : ($industry->name_en ?? $industry->name_fr)) : null;

    // Business rating (shared across the product and vendor rating sections)
    $reviews = $business->reviews ?? collect();
    $hasReviews = $reviews->count() > 0;
    $ratingAvg = $hasReviews ? number_format($reviews->avg('rating'), 1) : null;
    $ratingCountProduct = $reviews->count();
    $ratingCountVendor = $reviews->count();

    // Gallery
    $gallery = $product->images->sortBy('sort_order')->values();
    $mainImage = $gallery->firstWhere('is_cover', true) ?? $gallery->first();

    // Spec rows: Catégorie + the product's attributes in template order
    $specRows = [];
    if ($categoryName) $specRows[] = [$isFr ? 'Catégorie' : 'Category', $categoryName];
    foreach ($product->attributes->sortBy(fn ($a) => $a->template->sort_order ?? 99) as $attr) {
        $attrLabel = $isFr ? ($attr->template->name_fr ?? '') : ($attr->template->name_en ?? $attr->template->name_fr ?? '');
        $attrValue = $isFr ? $attr->value_fr : ($attr->value_en ?? $attr->value_fr);
        if ($attrLabel && $attrValue) $specRows[] = [$attrLabel, $attrValue];
    }
    $specIcons = [
        'catégorie' => 'shopping-bag', 'category' => 'shopping-bag',
        'matière' => 'layers', 'material' => 'layers',
        'technique' => 'settings-2',
        'origine' => 'map-pin', 'origin' => 'map-pin',
        'couleur' => 'palette', 'colour' => 'palette', 'color' => 'palette',
        'dimensions' => 'move-diagonal',
        'poids' => 'weight', 'weight' => 'weight',
    ];

    // Only what this product's own record supports. The design asserted
    // "Pièce unique", "Fait main", "Écoresponsable" and "Produit local" on
    // every listing — "Écoresponsable" in particular is an environmental claim
    // the platform is in no position to make on an artisan's behalf.
    $featureChips = collect([
        $product->is_custom_order ? ['sparkles', $isFr ? 'Sur commande' : 'Made to order'] : null,
        $locationLabel !== '' ? ['map-pin', $locationLabel] : null,
        $product->lead_time_days
            ? ['clock', ($isFr ? 'Délai ' : 'Lead time ') . $product->lead_time_days . ($isFr ? ' jours' : ' days')]
            : null,
        $product->moq && $product->moq > 1
            ? ['package', ($isFr ? 'Min. ' : 'Min. ') . $product->moq . ' ' . ($product->moq_unit ?? '')]
            : null,
    ])->filter()->values();

    $tabs = [
        ['description',    $isFr ? 'Description' : 'Description',                'align-left'],
        ['specifications', $isFr ? 'Spécifications' : 'Specifications',          'layout-grid'],
        ['details',        $isFr ? 'Détails artisanaux' : 'Artisanal details',   'hand'],
        ['care',           $isFr ? 'Entretien' : 'Care',                         'heart'],
        ['shipping',       $isFr ? 'Livraison & Retours' : 'Shipping & Returns', 'package'],
        ['reviews',        ($isFr ? 'Avis' : 'Reviews') . ' (' . $ratingCountProduct . ')', 'star'],
        ['questions',      'Questions',                                          'message-square'],
    ];

    // "Vous pourriez aussi aimer" — real related products (similar + same artisan), max 6
    $related = collect($similarProducts ?? [])->concat($otherProducts ?? [])
        ->unique('id')->take(6)->values();
    // Earned from each product's own record rather than pinned to the three
    // slugs the design happened to feature. "Best" needs real traffic behind it,
    // so it only appears once the leader has meaningful views.
    $relTopViews = (int) ($related->max('views_count') ?? 0);
    $relBadgeFor = function ($p) use ($relTopViews) {
        if ($relTopViews >= 20 && (int) $p->views_count === $relTopViews) {
            return 'best';
        }
        return $p->created_at && $p->created_at->gt(now()->subDays(30)) ? 'new' : null;
    };

    $contactPhone = $business->phone ?: null;
    $contactEmail = $business->email ?: null;
    $waNumber = preg_replace('/\D/', '', $business->whatsapp ?: $contactPhone);

    // Default product artwork per category (design: "default product images by category")
    $defaultBySlug = fn (?string $slug) => asset('images/landing/default-product-' . (in_array($slug, [
        'arts-decoration', 'textile-mode', 'bois-sculpture', 'poterie-ceramique', 'bijouterie-accessoires',
        'cuir-maroquinerie', 'musique-instruments', 'produits-naturels', 'agroalimentaire', 'technologies-innovation',
    ]) ? $slug : (['artisanat' => 'arts-decoration', 'aquaculture' => 'produits-naturels', 'agriculture' => 'produits-naturels'][$slug] ?? 'arts-decoration')) . '.png');
    $productDefaultImg = $defaultBySlug($industry->slug ?? $business->industry?->slug);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 150) }}">
    <title>{{ $name }} — {{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        leaf:   '#164C28',
                        deepfc: '#02301B',
                        gold:   '#E5A82E',
                        goldbt: '#F0B93E',
                    },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>

    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#FEFEFE] dark:bg-[#12150F] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@php $dirIconVariant = 'detail'; $dirCartCount = 2; @endphp
@include('pages.partials.directory-header')

{{-- Owner/admin preview banner. Reachable only when the product is published
     but its business is not (FrontendController::productShow) — the same
     pattern as businesses/show's preview banner. Without it, a vendor who
     clicks their own "View public page" link right after publishing a
     product, before publishing their business, would see their product page
     with no indication that the public cannot actually reach it yet. --}}
@if(! empty($isPreview))
    @php
        $previewStatusLabels = [
            'draft'     => ['fr' => 'brouillon',   'en' => 'draft'],
            'pending'   => ['fr' => 'en attente',  'en' => 'pending review'],
            'suspended' => ['fr' => 'suspendu',    'en' => 'suspended'],
            'archived'  => ['fr' => 'archivé',     'en' => 'archived'],
        ];
        $previewBizStatus = $previewStatusLabels[$product->business->status][$lang] ?? $product->business->status;
    @endphp
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4">
        <div class="ui-alert ui-alert-warn flex items-start gap-2.5">
            <i data-lucide="eye-off" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <span>
                <strong>{{ $isFr ? 'Aperçu.' : 'Preview.' }}</strong>
                {{ $isFr
                    ? 'Ce produit est publié, mais votre profil artisan est ' . $previewBizStatus . ' : cette page n\'est pas encore visible du public. Publiez votre profil pour la rendre accessible.'
                    : 'This product is published, but your artisan profile is ' . $previewBizStatus . ': this page is not yet visible to the public. Publish your profile to make it accessible.' }}
            </span>
        </div>
    </div>
@endif

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4 pb-12">

    <nav class="flex flex-wrap items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        @if($industryName)
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $industry->slug]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $industryName }}</a>
        @endif
        @if($sectorName)
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $sectorName }}</a>
        @endif
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $name }}</span>
    </nav>

    <div class="mt-5 grid grid-cols-1 lg:grid-cols-[440px_minmax(0,1fr)_305px] gap-8">

        <!-- Gallery -->
        <div>
            <div class="relative rounded-xl overflow-hidden bg-[#F4F1EC] dark:bg-[#0A0C09]">
                <img id="gallery-main" src="{{ $mainImage ? asset('storage/' . $mainImage->file_path) : $productDefaultImg }}" alt="{{ $name }}" class="w-full h-[402px] {{ $mainImage ? 'object-cover' : 'object-contain p-8' }}">
                @if(($product->created_at ?? null) && $product->created_at->gt(now()->subDays(60)))
                <span class="absolute top-4 left-4 bg-[#0E3D26] text-white text-[12px] md:text-[10px] font-bold tracking-[0.06em] uppercase rounded-md px-2.5 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                @endif
                @if($siacUser)
                <form method="POST" action="{{ route('products.toggle-save', $product->slug) }}" class="absolute top-3.5 right-3.5">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                    <button type="submit" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                        class="w-11 h-11 md:w-9 md:h-9 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                        <i data-lucide="heart" class="w-4 h-4"></i>
                    </button>
                </form>
                @else
                <a href="{{ route('login', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                    class="absolute top-3.5 right-3.5 w-11 h-11 md:w-9 md:h-9 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="heart" class="w-4 h-4"></i>
                </a>
                @endif
                <button type="button" id="gal-prev" aria-label="{{ $isFr ? 'Image précédente' : 'Previous image' }}"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-11 h-11 md:w-9 md:h-9 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="chevron-left" class="w-[18px] h-[18px]"></i>
                </button>
                <button type="button" id="gal-next" aria-label="{{ $isFr ? 'Image suivante' : 'Next image' }}"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-11 h-11 md:w-9 md:h-9 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="chevron-right" class="w-[18px] h-[18px]"></i>
                </button>
            </div>
            @if($gallery->count() > 1)
            <div class="mt-2.5 flex items-center gap-2">
                <div id="gallery-thumbs" class="flex items-center gap-2 overflow-x-auto">
                    @foreach($gallery as $gi => $gImage)
                    <button type="button" data-src="{{ asset('storage/' . $gImage->file_path) }}" data-idx="{{ $gi }}"
                        class="gal-thumb shrink-0 w-[79px] h-[76px] rounded-lg overflow-hidden border-2 {{ $gi === 0 ? 'border-[#0E3D26]' : 'border-transparent hover:border-[#C9C4BA]' }}">
                        <img src="{{ asset('storage/' . $gImage->file_path) }}" alt="" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                <button type="button" id="thumbs-more" aria-label="{{ $isFr ? 'Plus d\'images' : 'More images' }}" class="shrink-0 w-7 h-7 rounded-full border border-[#E3E3E1] dark:border-[#262B21] flex items-center justify-center text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56]">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Product info -->
        <div class="min-w-0">
            <span class="inline-flex items-center gap-1.5 border border-[#E0B453] text-[#B07C14] dark:text-[#EDB33A] text-[13px] md:text-[11.5px] font-semibold rounded-md px-2.5 py-1">
                {{ $isFr ? 'Fait main' : 'Handmade' }}
            </span>
            <h1 class="mt-3 font-serif text-[28px] sm:text-[32px] leading-tight text-[#1D1B16] dark:text-[#F3EFE7] font-semibold">{{ $name }}</h1>
            <p class="mt-2.5 text-[13px] text-[#55524A] dark:text-[#B4B5A6]">
                {{ $isFr ? 'Artisan :' : 'Artisan:' }}
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">{{ $businessName }}</a>
                @if(in_array($business->verification_tier, ['verified', 'certified']))
                <svg viewBox="0 0 16 16" class="inline w-4 h-4 -mt-0.5" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @endif
                @if($locationLabel)
                <span class="ml-4">{{ $isFr ? 'Localisation :' : 'Location:' }} <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $locationLabel }}</span></span>
                @endif
            </p>
            <p class="mt-2.5 flex items-center gap-2">
                @if($hasReviews)
                <span class="flex items-center gap-0.5">
                    @for($i = 0; $i < 5; $i++)
                    <svg viewBox="0 0 20 20" class="w-[17px] h-[17px] fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                    @endfor
                </span>
                <span class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $ratingAvg }}</span>
                <span class="text-[13px] text-[#6F6B60] dark:text-[#868778]">({{ $ratingCountProduct }} {{ $isFr ? 'avis' : 'reviews' }})</span>
                @else
                <span class="text-[13px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Pas encore d\'avis' : 'No reviews yet' }}</span>
                @endif
            </p>

            @if($description)
            <p class="mt-4 text-[15px] md:text-[13.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">{{ $description }}</p>
            @endif

            <!-- Specs -->
            <div class="mt-5 space-y-2.5">
                @foreach($specRows as [$specLabel, $specValue])
                <div class="flex items-start gap-3 text-[13px]">
                    <i data-lucide="{{ $specIcons[mb_strtolower($specLabel)] ?? 'tag' }}" class="w-[15px] h-[15px] text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                    <span class="w-[105px] shrink-0 text-[#55524A] dark:text-[#B4B5A6]">{{ $specLabel }} :</span>
                    <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $specValue }}</span>
                </div>
                @endforeach
            </div>

            <!-- Feature chips -->
            @if($featureChips->isNotEmpty())
            <div class="mt-5 bg-[#F8F6F1] dark:bg-[#0A0C09] border border-[#EEEBE2] dark:border-[#262B21] rounded-xl px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2">
                @foreach($featureChips as [$chipIcon, $chipLabel])
                <span class="flex items-center gap-2 text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">
                    <i data-lucide="{{ $chipIcon }}" class="w-[15px] h-[15px] text-[#55524A] dark:text-[#B4B5A6]"></i>
                    {{ $chipLabel }}
                </span>
                @endforeach
            </div>
            @endif

            <!-- CTA buttons -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ $siacUser ? route('quotes.create', ['business' => $business->slug, 'lang' => $lang]) : route('login', ['lang' => $lang, 'next' => route('quotes.create', ['business' => $business->slug]) ]) }}"
                    class="h-[46px] bg-[#02301B] hover:bg-leaf text-white rounded-lg flex items-center justify-center gap-2.5 text-[13px] md:text-[11.5px] font-bold tracking-[0.08em] uppercase transition-colors">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    {{ $isFr ? 'Demander un devis' : 'Request a quote' }}
                </a>
                <a href="{{ $siacUser ? route('messages.compose', ['business' => $business->slug, 'product' => $product->slug, 'lang' => $lang]) : route('login', ['lang' => $lang]) }}"
                    class="h-[46px] bg-white dark:bg-[#12150F] border border-[#DBDFDC] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center gap-2.5 text-[13px] md:text-[11.5px] font-bold tracking-[0.08em] uppercase text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    {{ $isFr ? 'Envoyer un message' : 'Send a message' }}
                </a>
            </div>

            <!-- Contact icon row -->
            <div class="mt-5 flex flex-wrap items-start gap-6">
                @if($waNumber)
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-[#22C05C] flex items-center justify-center text-white group-hover:opacity-85 transition-opacity">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.2-3.5-.7-2.9-1.2-4.8-4.1-4.9-4.3-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.2-.3.5-.4.7-.4h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.4.9-.9 1-.7 1.4.9 1.5 2 2.4 3.3 3 .3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1s1.8.8 2.1 1c.3.1.5.2.6.4 0 .1 0 .7-.2 1z"/></svg>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">WhatsApp</span>
                </a>
                @endif
                @if($contactEmail)
                <a href="mailto:{{ $contactEmail }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-[#E8542F] flex items-center justify-center text-white group-hover:opacity-85 transition-opacity">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">Email</span>
                </a>
                @endif
                @if($contactPhone)
                <a href="tel:{{ $contactPhone }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white dark:bg-[#12150F] border border-[#DFDCD5] dark:border-[#262B21] flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] group-hover:border-leaf transition-colors">
                        <i data-lucide="phone" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Appel' : 'Call' }}</span>
                </a>
                @endif
                {{-- The certificate is the reason a buyer can trust the listing,
                     so it belongs beside the contact actions rather than buried
                     in a tab. Only published products have one. --}}
                @if($product->status === 'published')
                <a href="{{ route('product.certificate', ['slug' => $product->slug, 'lang' => $lang]) }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white dark:bg-[#12150F] border border-[#DFDCD5] dark:border-[#262B21] flex items-center justify-center text-[#157A43] dark:text-[#339B56] group-hover:border-leaf transition-colors">
                        <i data-lucide="badge-check" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Certificat' : 'Certificate' }}</span>
                </a>
                @endif
                <button type="button" id="share-btn" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white dark:bg-[#12150F] border border-[#DFDCD5] dark:border-[#262B21] flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] group-hover:border-leaf transition-colors">
                        <i data-lucide="share-2" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Partager' : 'Share' }}</span>
                </button>
                @if($siacUser)
                <form method="POST" action="{{ route('products.toggle-save', $product->slug) }}" class="flex flex-col items-center gap-1.5 group">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                    <button type="submit" class="flex flex-col items-center gap-1.5">
                        <span class="w-11 h-11 rounded-full bg-white dark:bg-[#12150F] border border-[#F3C9C9] dark:border-[#7A2A2E] flex items-center justify-center text-[#D93838] dark:text-[#F0555C] group-hover:border-[#D93838] transition-colors">
                            <i data-lucide="heart" class="w-[18px] h-[18px]"></i>
                        </span>
                        <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Ajouter aux favoris' : 'Add to favorites' }}</span>
                    </button>
                </form>
                @else
                <a href="{{ route('login', ['lang' => $lang]) }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white dark:bg-[#12150F] border border-[#F3C9C9] dark:border-[#7A2A2E] flex items-center justify-center text-[#D93838] dark:text-[#F0555C] group-hover:border-[#D93838] transition-colors">
                        <i data-lucide="heart" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[13px] md:text-[11px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Ajouter aux favoris' : 'Add to favorites' }}</span>
                </a>
                @endif
            </div>

            <!-- Custom request note -->
            <div class="mt-5 bg-[#F6F5F1] dark:bg-[#0A0C09] border border-[#EAE8E1] dark:border-[#262B21] rounded-lg px-4 py-3 flex items-start gap-3">
                <i data-lucide="info" class="w-4 h-4 text-[#6F6B60] dark:text-[#868778] mt-0.5 shrink-0"></i>
                <div class="text-[12.5px] leading-relaxed">
                    <p class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Vous ne trouvez pas ce que vous cherchez ?' : 'Can\'t find what you\'re looking for?' }}</p>
                    <p class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Contactez cet artisan pour des demandes personnalisées ou des commandes spéciales.' : 'Contact this artisan for custom requests or special orders.' }}</p>
                </div>
            </div>
        </div>

        <!-- Right rail -->
        <aside class="space-y-4">
            <!-- Artisan card -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'À propos de l\'artisan' : 'About the artisan' }}</h2>
                <div class="mt-4 flex items-center gap-3.5">
                    @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="" class="w-[62px] h-[62px] rounded-full object-cover border border-[#ECECEA] dark:border-[#262B21]">
                    @else
                    <span class="w-[62px] h-[62px] rounded-full bg-[#F4F1EC] dark:bg-[#0A0C09] flex items-center justify-center text-[#8A857A] dark:text-[#868778]"><i data-lucide="store" class="w-6 h-6"></i></span>
                    @endif
                    <div class="min-w-0">
                        <p class="flex items-center gap-1.5 text-[13.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">
                            <span class="truncate">{{ $businessName }}</span>
                            @if(in_array($business->verification_tier, ['verified', 'certified']))
                            <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </p>
                        @if($locationLabel)<p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $locationLabel }}</p>@endif
                        <p class="mt-1 flex items-center gap-1.5">
                            @if($hasReviews)
                            <span class="flex items-center gap-0.5">
                                @for($i = 0; $i < 4; $i++)
                                <svg viewBox="0 0 20 20" class="w-3 h-3 fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                                @endfor
                                <svg viewBox="0 0 20 20" class="w-3 h-3 fill-[#E3DED2]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                            </span>
                            <span class="text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">{{ $ratingAvg }} ({{ $ratingCountVendor }} {{ $isFr ? 'avis' : 'reviews' }})</span>
                            @else
                            <span class="text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Pas encore d\'avis' : 'No reviews yet' }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
                    class="mt-4 w-full h-[44px] md:h-[38px] border border-[#DBDFDC] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center text-[12.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    {{ $isFr ? 'Voir la boutique' : 'View the shop' }}
                </a>
                @php
                    // Only stats that exist for this artisan — no invented figures.
                    $vendorStats = array_values(array_filter([
                        ['package', number_format((int) ($sellerStats['products_count'] ?? 0)), $isFr ? 'Produits' : 'Products'],
                        $sellerStats['satisfied_pct'] !== null
                            ? ['thumbs-up', $sellerStats['satisfied_pct'] . '%', $isFr ? 'Avis positifs' : 'Positive reviews'] : null,
                        $sellerStats['tenure_years'] !== null
                            ? ['briefcase', $sellerStats['tenure_years'] . ' ' . ($isFr ? 'ans' : 'yrs'), $isFr ? 'Sur la plateforme' : 'On the platform'] : null,
                    ]));
                @endphp
                <div class="mt-4 pt-4 border-t border-[#F0EEE9] dark:border-[#262B21] grid divide-x divide-[#F0EEE9] dark:divide-[#262B21] text-center" style="grid-template-columns: repeat({{ count($vendorStats) }}, minmax(0, 1fr))">
                    @foreach($vendorStats as [$vsIcon, $vsValue, $vsLabel])
                    <div class="px-1">
                        <i data-lucide="{{ $vsIcon }}" class="w-[18px] h-[18px] text-[#B07C14] dark:text-[#EDB33A] mx-auto"></i>
                        <p class="mt-1.5 text-[13px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-none">{{ $vsValue }}</p>
                        <p class="mt-1 text-[12px] md:text-[10.5px] text-[#6F6B60] dark:text-[#868778]">{{ $vsLabel }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Delivery info -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Informations de livraison' : 'Delivery information' }}</h2>
                <ul class="mt-4 space-y-3 text-[12px]">
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Lieu d\'expédition :' : 'Ships from:' }} <span class="text-[#1D1B16] dark:text-[#F3EFE7] font-medium">{{ $cityName ?? 'Cameroun' }}, {{ $isFr ? 'Cameroun' : 'Cameroon' }}</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="truck" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Livraison disponible :' : 'Delivery available:' }} <span class="text-[#1D1B16] dark:text-[#F3EFE7] font-medium">National & International</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="clock" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        {{-- No delivery window exists to quote: the platform ships nothing
                             and the artisan sets their own terms. --}}
                        <span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Livraison :' : 'Delivery:' }} <span class="text-[#1D1B16] dark:text-[#F3EFE7] font-medium">{{ $isFr ? "convenue avec l'artisan" : 'agreed with the artisan' }}</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="package" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Emballage :' : 'Packaging:' }} <span class="text-[#1D1B16] dark:text-[#F3EFE7] font-medium">{{ $isFr ? 'Sécurisé et écoresponsable' : 'Secure and eco-friendly' }}</span></span>
                    </li>
                </ul>
                <p class="mt-4 text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Les délais peuvent varier en fonction de votre localisation.' : 'Delivery times may vary depending on your location.' }}</p>
            </div>

            <!-- Help card -->
            <div class="bg-[#F8F6F1] dark:bg-[#0A0C09] border border-[#EEEBE2] dark:border-[#262B21] rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="headphones" class="w-6 h-6 text-[#1D4A2E] dark:text-[#339B56] shrink-0" stroke-width="1.5"></i>
                    <div>
                        <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</h2>
                        <p class="mt-1 text-[12px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Notre équipe vous accompagne 7j/7' : 'Our team is here for you 7 days a week' }}</p>
                    </div>
                </div>
                <a href="{{ route('contact', ['lang' => $lang]) }}"
                    class="ui-tap mt-3.5 inline-flex items-center gap-2 bg-white dark:bg-[#12150F] border border-[#DBD8D0] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg px-4 py-2 text-[12.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    {{ $isFr ? 'Nous contacter' : 'Contact us' }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Confidence card -->
            <div class="relative bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Achetez en toute confiance' : 'Buy with confidence' }}</h2>
                <ul class="mt-4 space-y-3 text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">
                    <li class="flex items-center gap-2.5"><i data-lucide="hand-coins" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? "Vous réglez l'artisan directement" : 'You pay the artisan directly' }}</li>
                    <li class="flex items-center gap-2.5"><i data-lucide="shield-check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Protection des acheteurs' : 'Buyer protection' }}</li>
                    <li class="flex items-center gap-2.5"><i data-lucide="badge-check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Remboursement garanti' : 'Guaranteed refund' }}</li>
                </ul>
                <img src="{{ asset('images/landing/pdetail-stamp.png') }}" alt="{{ $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity' }}"
                    class="absolute right-4 bottom-4 w-[68px] select-none pointer-events-none">
            </div>
        </aside>
    </div>

    <!-- Tabs -->
    <div class="mt-8 max-w-[850px]">
        <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden">
            <div class="flex items-center gap-1 overflow-x-auto border-b border-[#EFEDEA] dark:border-[#262B21] px-2">
                @foreach($tabs as $ti => [$tabKey, $tabLabel, $tabIcon])
                <button type="button" data-tab="{{ $tabKey }}"
                    class="tab-btn relative shrink-0 flex items-center gap-2 px-3.5 py-3.5 text-[12.5px] {{ $ti === 0 ? 'font-semibold text-[#14532D] dark:text-[#339B56]' : 'font-medium text-[#55524A] dark:text-[#B4B5A6] hover:text-[#1D1B16] hover:dark:text-[#F3EFE7]' }} transition-colors">
                    <i data-lucide="{{ $tabIcon }}" class="w-[14px] h-[14px]"></i>
                    {{ $tabLabel }}
                    <span class="tab-bar absolute left-2 right-2 bottom-0 h-[3px] bg-[#14532D] {{ $ti === 0 ? '' : 'hidden' }}"></span>
                </button>
                @endforeach
            </div>
            <div class="p-6">
                <div class="tab-panel" data-panel="description">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_300px] gap-6">
                        <div>
                            {{-- The artisan's own words. This tab used to synthesise
                                 provenance prose from the category and city — and for
                                 one slug asserted the piece came from the "berceau de
                                 la civilisation Bamoun" — while $description sat
                                 unused a few lines above. --}}
                            @if($description)
                            <p class="text-[15px] md:text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed whitespace-pre-line">{{ $description }}</p>
                            @else
                            <p class="text-[13px] text-[#6F6B60] dark:text-[#868778]">
                                {{ $isFr
                                   ? 'L\'artisan n\'a pas encore ajouté de description pour ce produit.'
                                   : 'The artisan has not added a description for this product yet.' }}
                            </p>
                            @endif
                            <ul class="mt-4 space-y-2 text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7]">
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Idéal pour la décoration intérieure' : 'Ideal for interior decoration' }}</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Parfait comme cadeau unique et authentique' : 'Perfect as a unique and authentic gift' }}</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Soutient l\'artisanat local et l\'économie communautaire' : 'Supports local craftsmanship and the community economy' }}</li>
                            </ul>
                        </div>
                        <div class="bg-[#FAF6EA] dark:bg-[#0A0C09] border border-[#EFE4C8] dark:border-[#6A5210] rounded-xl p-4">
                            <p class="flex items-center gap-2 text-[12.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">
                                <i data-lucide="wand-2" class="w-4 h-4 text-[#B07C14] dark:text-[#EDB33A]"></i>
                                {{ $isFr ? 'Personnalisation disponible' : 'Customisation available' }}
                            </p>
                            <p class="mt-2 text-[13px] md:text-[11.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
                                {{ $isFr ? 'Vous souhaitez un motif ou une taille différente ? Cet artisan peut réaliser des pièces sur mesure selon vos préférences.' : 'Would you like a different pattern or size? This artisan can craft custom pieces to your preferences.' }}
                            </p>
                            <a href="{{ $siacUser ? route('messages.compose', ['business' => $business->slug, 'product' => $product->slug, 'lang' => $lang]) : route('login', ['lang' => $lang]) }}"
                                class="mt-3.5 w-full h-[44px] md:h-[36px] bg-white dark:bg-[#12150F] border border-[#E0D9C6] dark:border-[#6A5210] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center gap-2 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                                <i data-lucide="message-circle" class="w-[14px] h-[14px]"></i>
                                {{ $isFr ? 'Demander une personnalisation' : 'Request a customisation' }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="tab-panel hidden" data-panel="specifications">
                    <div class="space-y-2.5">
                        @foreach($specRows as [$specLabel, $specValue])
                        <div class="flex items-start gap-3 text-[13px]">
                            <span class="w-[130px] shrink-0 text-[#55524A] dark:text-[#B4B5A6]">{{ $specLabel }} :</span>
                            <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $specValue }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="tab-panel hidden" data-panel="details">
                    <p class="text-[15px] md:text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">{{ $isFr ? $business->description_fr : ($business->description_en ?? $business->description_fr) }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="care">
                    <p class="text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">{{ $isFr ? 'Nettoyez délicatement avec un chiffon doux et sec. Évitez l\'exposition prolongée à l\'humidité et au soleil direct pour préserver les motifs.' : 'Clean gently with a soft, dry cloth. Avoid prolonged exposure to humidity and direct sunlight to preserve the patterns.' }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="shipping">
                    {{-- These were invented terms. The platform ships nothing, sets no
                         delivery window and operates no returns policy; a buyer relying on
                         "3-7 jours" or "retours sous 14 jours" would be relying on a promise
                         nobody ever made. The artisan sets their own terms and is the only
                         party who can state them. --}}
                    <p class="text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">{{ $isFr ? "La livraison et les retours sont convenus directement avec l'artisan, qui fixe ses propres conditions et délais. La plateforme n'expédie rien et n'est pas partie à la vente — demandez-lui ses conditions avant de commander." : 'Delivery and returns are agreed directly with the artisan, who sets their own terms and timescales. The platform ships nothing and is not a party to the sale — ask them for their terms before ordering.' }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="reviews">
                    @if($reviews->count())
                    <div class="space-y-4">
                        @foreach($reviews->take(5) as $review)
                        <div class="border-b border-[#F0EEE9] dark:border-[#262B21] pb-3">
                            <p class="text-[12.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $review->reviewer->name ?? 'Client' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">{{ $review->comment ?? '' }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Connectez-vous pour consulter et laisser des avis sur cet artisan.' : 'Sign in to view and leave reviews for this artisan.' }}</p>
                    @endif
                </div>
                <div class="tab-panel hidden" data-panel="questions">
                    <p class="text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Une question sur ce produit ? Contactez directement l\'artisan via le bouton « Envoyer un message ».' : 'A question about this product? Contact the artisan directly via the "Send a message" button.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related products -->
    @if($related->count())
    <div class="mt-10">
        <h2 class="font-serif text-[22px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Vous pourriez aussi aimer' : 'You may also like' }}</h2>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($related as $rel)
            @php
                $relName = $isFr ? $rel->name_fr : ($rel->name_en ?? $rel->name_fr);
                $relImg = $rel->primaryImage ? asset('storage/' . $rel->primaryImage->file_path) : null;
                $relDefault = $defaultBySlug($rel->category?->sector?->industry?->slug ?? $rel->business?->industry?->slug);
                $relBadge = $relBadgeFor($rel);
            @endphp
            <article class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="relative">
                    <a href="{{ route('products.show', ['slug' => $rel->slug, 'lang' => $lang]) }}">
                        @if($relImg)
                        <img src="{{ $relImg }}" alt="{{ $relName }}" class="w-full h-[150px] object-cover">
                        @else
                        <img src="{{ $relDefault }}" alt="{{ $relName }}" class="w-full h-[150px] object-contain p-3 bg-[#F9F5EE] dark:bg-[#0A0C09]">
                        @endif
                    </a>
                    @if($relBadge === 'new')
                    <span class="absolute top-2.5 left-2.5 bg-[#0E3D26] text-white text-[12px] md:text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                    @elseif($relBadge === 'best')
                    <span class="absolute top-2.5 left-2.5 bg-[#EFA912] text-white text-[12px] md:text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">Best-seller</span>
                    @endif
                    @if($siacUser)
                    <form method="POST" action="{{ route('products.toggle-save', $rel->slug) }}" class="absolute top-2 right-2">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ url()->full() }}">
                        <button type="submit" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                            class="w-8 h-8 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                            <i data-lucide="heart" class="w-[15px] h-[15px]"></i>
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                        class="absolute top-2 right-2 w-11 h-11 md:w-8 md:h-8 bg-white/95 dark:bg-[#12150F]/95 hover:bg-white hover:dark:bg-[#12150F] rounded-full flex items-center justify-center text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                        <i data-lucide="heart" class="w-[15px] h-[15px]"></i>
                    </a>
                    @endif
                </div>
                <div class="p-3">
                    <h3 class="text-[12.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] truncate">
                        <a href="{{ route('products.show', ['slug' => $rel->slug, 'lang' => $lang]) }}" class="ui-tap-inset hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $relName }}</a>
                    </h3>
                </div>
            </article>
            @endforeach
        </div>
    </div>
    @endif
</div>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Gallery
    const mainImg = document.getElementById('gallery-main');
    const thumbs = [...document.querySelectorAll('.gal-thumb')];
    let galIdx = 0;
    function setGal(i) {
        if (!thumbs.length) return;
        galIdx = (i + thumbs.length) % thumbs.length;
        mainImg.src = thumbs[galIdx].dataset.src;
        thumbs.forEach((t, j) => {
            t.classList.toggle('border-[#0E3D26]', j === galIdx);
            t.classList.toggle('border-transparent', j !== galIdx);
        });
    }
    thumbs.forEach((t, i) => t.addEventListener('click', () => setGal(i)));
    document.getElementById('gal-prev')?.addEventListener('click', () => setGal(galIdx - 1));
    document.getElementById('gal-next')?.addEventListener('click', () => setGal(galIdx + 1));
    document.getElementById('thumbs-more')?.addEventListener('click', () => setGal(galIdx + 1));

    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                const active = b === btn;
                b.classList.toggle('font-semibold', active);
                b.classList.toggle('text-[#14532D] dark:text-[#339B56]', active);
                b.classList.toggle('font-medium', !active);
                b.classList.toggle('text-[#55524A] dark:text-[#B4B5A6]', !active);
                b.querySelector('.tab-bar').classList.toggle('hidden', !active);
            });
            document.querySelectorAll('.tab-panel').forEach(p => {
                p.classList.toggle('hidden', p.dataset.panel !== btn.dataset.tab);
            });
        });
    });

    // Share
    document.getElementById('share-btn')?.addEventListener('click', async () => {
        try {
            if (navigator.share) await navigator.share({title: document.title, url: location.href});
            else { await navigator.clipboard.writeText(location.href); alert('{{ $isFr ? 'Lien copié !' : 'Link copied!' }}'); }
        } catch (e) { /* cancelled */ }
    });
</script>
</body>
</html>
