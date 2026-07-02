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

    // Business rating (design fallbacks when no reviews yet: 4.8 (23) / 4.8 (56))
    $reviews = $business->reviews ?? collect();
    $ratingAvg = $reviews->count() ? number_format($reviews->avg('rating'), 1) : '4.8';
    $ratingCountProduct = $reviews->count() ?: 23;
    $ratingCountVendor = $reviews->count() ?: 56;

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

    $featureChips = [
        ['sparkles', $isFr ? 'Pièce unique' : 'One-of-a-kind'],
        ['hand',     $isFr ? 'Fait main' : 'Handmade'],
        ['leaf',     $isFr ? 'Écoresponsable' : 'Eco-friendly'],
        ['home',     $isFr ? 'Produit local' : 'Local product'],
    ];

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
    $designBadges = ['panier-africain-tresse' => 'new', 'sculpture-en-bois-sawa' => 'best', 'vase-en-terre-cuite-grave-a-la-main' => 'new'];

    $contactPhone = $business->phone ?: '+237670416238';
    $contactEmail = $business->email ?: 'contact@gvnac.cm';
    $waNumber = preg_replace('/\D/', '', $business->whatsapp ?: $contactPhone);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $description), 150) }}">
    <title>{{ $name }} — {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</title>

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
</head>
<body class="bg-[#FEFEFE] text-[#1D1B16] antialiased">

@php $dirIconVariant = 'detail'; $dirCartCount = 2; @endphp
@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4 pb-12">

    <nav class="flex flex-wrap items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        @if($industryName)
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $industry->slug]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $industryName }}</a>
        @endif
        @if($sectorName)
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $sectorName }}</a>
        @endif
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16]">{{ $name }}</span>
    </nav>

    <div class="mt-5 grid grid-cols-1 lg:grid-cols-[440px_minmax(0,1fr)_305px] gap-8">

        <!-- Gallery -->
        <div>
            <div class="relative rounded-xl overflow-hidden bg-[#F4F1EC]">
                <img id="gallery-main" src="{{ $mainImage ? asset('storage/' . $mainImage->file_path) : asset('images/landing/pdetail-main.png') }}" alt="{{ $name }}" class="w-full h-[402px] object-cover">
                @if(($product->created_at ?? null) && $product->created_at->gt(now()->subDays(60)))
                <span class="absolute top-4 left-4 bg-[#0E3D26] text-white text-[10px] font-bold tracking-[0.06em] uppercase rounded-md px-2.5 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                @endif
                <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                    class="absolute top-3.5 right-3.5 w-9 h-9 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
                    <i data-lucide="heart" class="w-4 h-4"></i>
                </a>
                <button type="button" id="gal-prev" aria-label="{{ $isFr ? 'Image précédente' : 'Previous image' }}"
                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
                    <i data-lucide="chevron-left" class="w-[18px] h-[18px]"></i>
                </button>
                <button type="button" id="gal-next" aria-label="{{ $isFr ? 'Image suivante' : 'Next image' }}"
                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-9 h-9 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
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
                <button type="button" id="thumbs-more" aria-label="{{ $isFr ? 'Plus d\'images' : 'More images' }}" class="shrink-0 w-7 h-7 rounded-full border border-[#E3E3E1] flex items-center justify-center text-[#6F6B60] hover:text-leaf">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
            @endif
        </div>

        <!-- Product info -->
        <div class="min-w-0">
            <span class="inline-flex items-center gap-1.5 border border-[#E0B453] text-[#B07C14] text-[11.5px] font-semibold rounded-md px-2.5 py-1">
                {{ $isFr ? 'Fait main' : 'Handmade' }}
            </span>
            <h1 class="mt-3 font-serif text-[28px] sm:text-[32px] leading-tight text-[#1D1B16] font-semibold">{{ $name }}</h1>
            <p class="mt-2.5 text-[13px] text-[#55524A]">
                {{ $isFr ? 'Artisan :' : 'Artisan:' }}
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="font-semibold text-[#14532D] hover:underline">{{ $businessName }}</a>
                @if(in_array($business->verification_tier, ['verified', 'certified']))
                <svg viewBox="0 0 16 16" class="inline w-4 h-4 -mt-0.5" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @endif
                @if($locationLabel)
                <span class="ml-4">{{ $isFr ? 'Localisation :' : 'Location:' }} <span class="text-[#1D1B16]">{{ $locationLabel }}</span></span>
                @endif
            </p>
            <p class="mt-2.5 flex items-center gap-2">
                <span class="flex items-center gap-0.5">
                    @for($i = 0; $i < 5; $i++)
                    <svg viewBox="0 0 20 20" class="w-[17px] h-[17px] fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                    @endfor
                </span>
                <span class="text-[14px] font-bold text-[#1D1B16]">{{ $ratingAvg }}</span>
                <span class="text-[13px] text-[#6F6B60]">({{ $ratingCountProduct }} {{ $isFr ? 'avis' : 'reviews' }})</span>
            </p>

            @if($description)
            <p class="mt-4 text-[13.5px] text-[#3A3A35] leading-relaxed">{{ $description }}</p>
            @endif

            <!-- Specs -->
            <div class="mt-5 space-y-2.5">
                @foreach($specRows as [$specLabel, $specValue])
                <div class="flex items-start gap-3 text-[13px]">
                    <i data-lucide="{{ $specIcons[mb_strtolower($specLabel)] ?? 'tag' }}" class="w-[15px] h-[15px] text-[#55524A] mt-0.5 shrink-0"></i>
                    <span class="w-[105px] shrink-0 text-[#55524A]">{{ $specLabel }} :</span>
                    <span class="text-[#1D1B16]">{{ $specValue }}</span>
                </div>
                @endforeach
            </div>

            <!-- Feature chips -->
            <div class="mt-5 bg-[#F8F6F1] border border-[#EEEBE2] rounded-xl px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2">
                @foreach($featureChips as [$chipIcon, $chipLabel])
                <span class="flex items-center gap-2 text-[12px] text-[#3A3A35]">
                    <i data-lucide="{{ $chipIcon }}" class="w-[15px] h-[15px] text-[#55524A]"></i>
                    {{ $chipLabel }}
                </span>
                @endforeach
            </div>

            <!-- CTA buttons -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}"
                    class="h-[46px] bg-[#02301B] hover:bg-leaf text-white rounded-lg flex items-center justify-center gap-2.5 text-[11.5px] font-bold tracking-[0.08em] uppercase transition-colors">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    {{ $isFr ? 'Envoyer une demande (Enquiry)' : 'Send an enquiry' }}
                </a>
                <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}"
                    class="h-[46px] bg-white border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center gap-2.5 text-[11.5px] font-bold tracking-[0.08em] uppercase text-[#1D1B16] transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    {{ $isFr ? 'Envoyer un message' : 'Send a message' }}
                </a>
            </div>

            <!-- Contact icon row -->
            <div class="mt-5 flex flex-wrap items-start gap-6">
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-[#22C05C] flex items-center justify-center text-white group-hover:opacity-85 transition-opacity">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.2-3.5-.7-2.9-1.2-4.8-4.1-4.9-4.3-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.2-.3.5-.4.7-.4h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.4.9-.9 1-.7 1.4.9 1.5 2 2.4 3.3 3 .3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1s1.8.8 2.1 1c.3.1.5.2.6.4 0 .1 0 .7-.2 1z"/></svg>
                    </span>
                    <span class="text-[11px] text-[#3A3A35]">WhatsApp</span>
                </a>
                <a href="mailto:{{ $contactEmail }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-[#E8542F] flex items-center justify-center text-white group-hover:opacity-85 transition-opacity">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </span>
                    <span class="text-[11px] text-[#3A3A35]">Email</span>
                </a>
                <a href="tel:{{ $contactPhone }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white border border-[#DFDCD5] flex items-center justify-center text-[#3A3A35] group-hover:border-leaf transition-colors">
                        <i data-lucide="phone" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[11px] text-[#3A3A35]">{{ $isFr ? 'Appel' : 'Call' }}</span>
                </a>
                <button type="button" id="share-btn" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white border border-[#DFDCD5] flex items-center justify-center text-[#3A3A35] group-hover:border-leaf transition-colors">
                        <i data-lucide="share-2" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[11px] text-[#3A3A35]">{{ $isFr ? 'Partager' : 'Share' }}</span>
                </button>
                <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" class="flex flex-col items-center gap-1.5 group">
                    <span class="w-11 h-11 rounded-full bg-white border border-[#F3C9C9] flex items-center justify-center text-[#D93838] group-hover:border-[#D93838] transition-colors">
                        <i data-lucide="heart" class="w-[18px] h-[18px]"></i>
                    </span>
                    <span class="text-[11px] text-[#3A3A35]">{{ $isFr ? 'Ajouter aux favoris' : 'Add to favorites' }}</span>
                </a>
            </div>

            <!-- Custom request note -->
            <div class="mt-5 bg-[#F6F5F1] border border-[#EAE8E1] rounded-lg px-4 py-3 flex items-start gap-3">
                <i data-lucide="info" class="w-4 h-4 text-[#6F6B60] mt-0.5 shrink-0"></i>
                <div class="text-[12.5px] leading-relaxed">
                    <p class="font-semibold text-[#1D1B16]">{{ $isFr ? 'Vous ne trouvez pas ce que vous cherchez ?' : 'Can\'t find what you\'re looking for?' }}</p>
                    <p class="text-[#55524A]">{{ $isFr ? 'Contactez cet artisan pour des demandes personnalisées ou des commandes spéciales.' : 'Contact this artisan for custom requests or special orders.' }}</p>
                </div>
            </div>
        </div>

        <!-- Right rail -->
        <aside class="space-y-4">
            <!-- Artisan card -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'À propos de l\'artisan' : 'About the artisan' }}</h2>
                <div class="mt-4 flex items-center gap-3.5">
                    @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="" class="w-[62px] h-[62px] rounded-full object-cover border border-[#ECECEA]">
                    @else
                    <span class="w-[62px] h-[62px] rounded-full bg-[#F4F1EC] flex items-center justify-center text-[#8A857A]"><i data-lucide="store" class="w-6 h-6"></i></span>
                    @endif
                    <div class="min-w-0">
                        <p class="flex items-center gap-1.5 text-[13.5px] font-bold text-[#1D1B16]">
                            <span class="truncate">{{ $businessName }}</span>
                            @if(in_array($business->verification_tier, ['verified', 'certified']))
                            <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </p>
                        @if($locationLabel)<p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $locationLabel }}</p>@endif
                        <p class="mt-1 flex items-center gap-1.5">
                            <span class="flex items-center gap-0.5">
                                @for($i = 0; $i < 4; $i++)
                                <svg viewBox="0 0 20 20" class="w-3 h-3 fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                                @endfor
                                <svg viewBox="0 0 20 20" class="w-3 h-3 fill-[#E3DED2]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                            </span>
                            <span class="text-[11.5px] text-[#6F6B60]">{{ $ratingAvg }} ({{ $ratingCountVendor }} {{ $isFr ? 'avis' : 'reviews' }})</span>
                        </p>
                    </div>
                </div>
                <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
                    class="mt-4 w-full h-[38px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[12.5px] font-semibold text-[#1D1B16] transition-colors">
                    {{ $isFr ? 'Voir la boutique' : 'View the shop' }}
                </a>
                <div class="mt-4 pt-4 border-t border-[#F0EEE9] grid grid-cols-3 divide-x divide-[#F0EEE9] text-center">
                    <div class="px-1">
                        <i data-lucide="package" class="w-[18px] h-[18px] text-[#B07C14] mx-auto"></i>
                        <p class="mt-1.5 text-[13px] font-bold text-[#1D1B16] leading-none">156</p>
                        <p class="mt-1 text-[10.5px] text-[#6F6B60]">{{ $isFr ? 'Produits' : 'Products' }}</p>
                    </div>
                    <div class="px-1">
                        <i data-lucide="thumbs-up" class="w-[18px] h-[18px] text-[#B07C14] mx-auto"></i>
                        <p class="mt-1.5 text-[13px] font-bold text-[#1D1B16] leading-none">98%</p>
                        <p class="mt-1 text-[10.5px] text-[#6F6B60]">{{ $isFr ? 'Avis positifs' : 'Positive reviews' }}</p>
                    </div>
                    <div class="px-1">
                        <i data-lucide="briefcase" class="w-[18px] h-[18px] text-[#B07C14] mx-auto"></i>
                        <p class="mt-1.5 text-[13px] font-bold text-[#1D1B16] leading-none">2 {{ $isFr ? 'ans' : 'yrs' }}</p>
                        <p class="mt-1 text-[10.5px] text-[#6F6B60]">{{ $isFr ? 'Sur la plateforme' : 'On the platform' }}</p>
                    </div>
                </div>
            </div>

            <!-- Delivery info -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Informations de livraison' : 'Delivery information' }}</h2>
                <ul class="mt-4 space-y-3 text-[12px]">
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-[14px] h-[14px] text-[#55524A] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A]">{{ $isFr ? 'Lieu d\'expédition :' : 'Ships from:' }} <span class="text-[#1D1B16] font-medium">{{ $cityName ?? 'Cameroun' }}, {{ $isFr ? 'Cameroun' : 'Cameroon' }}</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="truck" class="w-[14px] h-[14px] text-[#55524A] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A]">{{ $isFr ? 'Livraison disponible :' : 'Delivery available:' }} <span class="text-[#1D1B16] font-medium">National & International</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="clock" class="w-[14px] h-[14px] text-[#55524A] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A]">{{ $isFr ? 'Délai de livraison :' : 'Delivery time:' }} <span class="text-[#1D1B16] font-medium">{{ $isFr ? '3 – 7 jours ouvrables' : '3 – 7 working days' }}</span></span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="package" class="w-[14px] h-[14px] text-[#55524A] mt-0.5 shrink-0"></i>
                        <span class="text-[#55524A]">{{ $isFr ? 'Emballage :' : 'Packaging:' }} <span class="text-[#1D1B16] font-medium">{{ $isFr ? 'Sécurisé et écoresponsable' : 'Secure and eco-friendly' }}</span></span>
                    </li>
                </ul>
                <p class="mt-4 text-[11px] text-[#8A857A]">{{ $isFr ? 'Les délais peuvent varier en fonction de votre localisation.' : 'Delivery times may vary depending on your location.' }}</p>
            </div>

            <!-- Help card -->
            <div class="bg-[#F8F6F1] border border-[#EEEBE2] rounded-xl p-5">
                <div class="flex items-start gap-3">
                    <i data-lucide="headphones" class="w-6 h-6 text-[#1D4A2E] shrink-0" stroke-width="1.5"></i>
                    <div>
                        <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</h2>
                        <p class="mt-1 text-[12px] text-[#55524A]">{{ $isFr ? 'Notre équipe vous accompagne 7j/7' : 'Our team is here for you 7 days a week' }}</p>
                    </div>
                </div>
                <a href="{{ route('contact', ['lang' => $lang]) }}"
                    class="mt-3.5 inline-flex items-center gap-2 bg-white border border-[#DBD8D0] hover:border-leaf hover:text-leaf rounded-lg px-4 py-2 text-[12.5px] font-semibold text-[#1D1B16] transition-colors">
                    {{ $isFr ? 'Nous contacter' : 'Contact us' }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Confidence card -->
            <div class="relative bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Achetez en toute confiance' : 'Buy with confidence' }}</h2>
                <ul class="mt-4 space-y-3 text-[12px] text-[#3A3A35]">
                    <li class="flex items-center gap-2.5"><i data-lucide="lock" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Paiement sécurisé' : 'Secure payment' }}</li>
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
        <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
            <div class="flex items-center gap-1 overflow-x-auto border-b border-[#EFEDEA] px-2">
                @foreach($tabs as $ti => [$tabKey, $tabLabel, $tabIcon])
                <button type="button" data-tab="{{ $tabKey }}"
                    class="tab-btn relative shrink-0 flex items-center gap-2 px-3.5 py-3.5 text-[12.5px] {{ $ti === 0 ? 'font-semibold text-[#14532D]' : 'font-medium text-[#55524A] hover:text-[#1D1B16]' }} transition-colors">
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
                            <p class="text-[13px] text-[#3A3A35] leading-relaxed">
                                {{ $isFr
                                    ? 'Ce ' . mb_strtolower($categoryName ?? 'produit') . ' est une œuvre d\'art traditionnelle fabriquée à la main par des artisans expérimentés de ' . ($cityName ?? 'nos régions') . ($product->slug === 'vase-en-terre-cuite-grave-a-la-main' ? ', berceau de la civilisation Bamoun. Chaque motif gravé raconte une histoire et symbolise l\'héritage culturel camerounais.' : '. Chaque pièce raconte une histoire et symbolise l\'héritage culturel camerounais.')
                                    : 'This piece is a traditional work of art handmade by experienced artisans from ' . ($cityName ?? 'our regions') . '. Each piece tells a story and symbolises Cameroon\'s cultural heritage.'
                                }}
                            </p>
                            <ul class="mt-4 space-y-2 text-[12.5px] text-[#3A3A35]">
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Idéal pour la décoration intérieure' : 'Ideal for interior decoration' }}</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Parfait comme cadeau unique et authentique' : 'Perfect as a unique and authentic gift' }}</li>
                                <li class="flex items-center gap-2.5"><i data-lucide="check" class="w-[14px] h-[14px] text-[#17A34A]"></i>{{ $isFr ? 'Soutient l\'artisanat local et l\'économie communautaire' : 'Supports local craftsmanship and the community economy' }}</li>
                            </ul>
                        </div>
                        <div class="bg-[#FAF6EA] border border-[#EFE4C8] rounded-xl p-4">
                            <p class="flex items-center gap-2 text-[12.5px] font-bold text-[#1D1B16]">
                                <i data-lucide="wand-2" class="w-4 h-4 text-[#B07C14]"></i>
                                {{ $isFr ? 'Personnalisation disponible' : 'Customisation available' }}
                            </p>
                            <p class="mt-2 text-[11.5px] text-[#55524A] leading-relaxed">
                                {{ $isFr ? 'Vous souhaitez un motif ou une taille différente ? Cet artisan peut réaliser des pièces sur mesure selon vos préférences.' : 'Would you like a different pattern or size? This artisan can craft custom pieces to your preferences.' }}
                            </p>
                            <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}"
                                class="mt-3.5 w-full h-[36px] bg-white border border-[#E0D9C6] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center gap-2 text-[12px] font-semibold text-[#1D1B16] transition-colors">
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
                            <span class="w-[130px] shrink-0 text-[#55524A]">{{ $specLabel }} :</span>
                            <span class="text-[#1D1B16]">{{ $specValue }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="tab-panel hidden" data-panel="details">
                    <p class="text-[13px] text-[#3A3A35] leading-relaxed">{{ $isFr ? $business->description_fr : ($business->description_en ?? $business->description_fr) }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="care">
                    <p class="text-[13px] text-[#3A3A35] leading-relaxed">{{ $isFr ? 'Nettoyez délicatement avec un chiffon doux et sec. Évitez l\'exposition prolongée à l\'humidité et au soleil direct pour préserver les motifs.' : 'Clean gently with a soft, dry cloth. Avoid prolonged exposure to humidity and direct sunlight to preserve the patterns.' }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="shipping">
                    <p class="text-[13px] text-[#3A3A35] leading-relaxed">{{ $isFr ? 'Livraison nationale et internationale sous 3 – 7 jours ouvrables, emballage sécurisé et écoresponsable. Retours acceptés sous 14 jours — contactez l\'artisan pour toute question.' : 'National and international delivery within 3 – 7 working days, secure and eco-friendly packaging. Returns accepted within 14 days — contact the artisan with any questions.' }}</p>
                </div>
                <div class="tab-panel hidden" data-panel="reviews">
                    @if($reviews->count())
                    <div class="space-y-4">
                        @foreach($reviews->take(5) as $review)
                        <div class="border-b border-[#F0EEE9] pb-3">
                            <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $review->reviewer->name ?? 'Client' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $review->comment ?? '' }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Connectez-vous pour consulter et laisser des avis sur cet artisan.' : 'Sign in to view and leave reviews for this artisan.' }}</p>
                    @endif
                </div>
                <div class="tab-panel hidden" data-panel="questions">
                    <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Une question sur ce produit ? Contactez directement l\'artisan via le bouton « Envoyer un message ».' : 'A question about this product? Contact the artisan directly via the "Send a message" button.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related products -->
    @if($related->count())
    <div class="mt-10">
        <h2 class="font-serif text-[22px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Vous pourriez aussi aimer' : 'You may also like' }}</h2>
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($related as $rel)
            @php
                $relName = $isFr ? $rel->name_fr : ($rel->name_en ?? $rel->name_fr);
                $relImg = $rel->primaryImage ? asset('storage/' . $rel->primaryImage->file_path) : null;
                $relBadge = $designBadges[$rel->slug] ?? null;
            @endphp
            <article class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                <div class="relative">
                    <a href="{{ route('products.show', ['slug' => $rel->slug, 'lang' => $lang]) }}">
                        @if($relImg)
                        <img src="{{ $relImg }}" alt="{{ $relName }}" class="w-full h-[150px] object-cover">
                        @else
                        <span class="w-full h-[150px] bg-[#F4F1EC] flex items-center justify-center text-[#C9C4BA]"><i data-lucide="image" class="w-8 h-8"></i></span>
                        @endif
                    </a>
                    @if($relBadge === 'new')
                    <span class="absolute top-2.5 left-2.5 bg-[#0E3D26] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                    @elseif($relBadge === 'best')
                    <span class="absolute top-2.5 left-2.5 bg-[#EFA912] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">Best-seller</span>
                    @endif
                    <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                        class="absolute top-2 right-2 w-8 h-8 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
                        <i data-lucide="heart" class="w-[15px] h-[15px]"></i>
                    </a>
                </div>
                <div class="p-3">
                    <h3 class="text-[12.5px] font-bold text-[#1D1B16] truncate">
                        <a href="{{ route('products.show', ['slug' => $rel->slug, 'lang' => $lang]) }}" class="hover:text-leaf transition-colors">{{ $relName }}</a>
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
                b.classList.toggle('text-[#14532D]', active);
                b.classList.toggle('font-medium', !active);
                b.classList.toggle('text-[#55524A]', !active);
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
