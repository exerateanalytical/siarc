@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Sidebar categories: real industries + real per-industry product counts.
    // The design's side icons (product-side-N.png) follow the seeded sort order.
    $tileIndustries = $industries->filter(fn ($ind) => $ind->image_icon)->sortBy('sort_order')->values();
    $designSideCats = collect([['', $isFr ? 'Toutes les catégories' : 'All categories', (string) $liveCount, 'product-side-0.png']])
        ->concat($tileIndustries->map(fn ($ind, $idx) => [
            $ind->slug,
            $isFr ? $ind->name_fr : ($ind->name_en ?? $ind->name_fr),
            (string) ($sideCounts[$ind->id] ?? 0),
            'product-side-' . ($idx + 1) . '.png',
        ]))->all();

    // Product cards: the real, filtered, paginated query. The design's product
    // crops stay as artwork for the seeded design products (mapped by slug) and
    // as cycling fallbacks for products without an uploaded photo.
    $designArt = [
        'panier-africain-tresse' => 'product-1.png',  'sculpture-en-bois-sawa' => 'product-2.png',
        'sac-a-main-traditionnel' => 'product-3.png', 'vase-en-terre-cuite' => 'product-4.png',
        'collier-perles-africaines' => 'product-5.png', 'sac-en-cuir-veritable' => 'product-6.png',
        'djembe-traditionnel' => 'product-7.png',     'miel-naturel-du-cameroun' => 'product-8.png',
        'feves-de-cacao-premium' => 'product-9.png',  'savon-naturel-artisanal' => 'product-10.png',
        'lampe-solaire-artisanale' => 'product-11.png', 'beurre-de-karite-pur' => 'product-12.png',
    ];

    $shownProducts = $products->map(function ($prod) use ($isFr, $designArt) {
        static $i = 0;
        $i++;
        $firstImage = $prod->images->first();
        return [
            'slug'  => $prod->slug,
            'img'   => $firstImage
                ? asset('storage/' . $firstImage->file_path)
                : asset('images/landing/' . ($designArt[$prod->slug] ?? 'product-' . (($i - 1) % 12 + 1) . '.png')),
            'badge' => $prod->created_at->gt(now()->subDays(30)) ? 'new' : null,
            'name'  => $isFr ? $prod->name_fr : ($prod->name_en ?? $prod->name_fr),
            'cat'   => $prod->business->industry
                ? ($isFr ? $prod->business->industry->name_fr : ($prod->business->industry->name_en ?? $prod->business->industry->name_fr))
                : '',
        ];
    })->all();

    $activeCat = $categorie ?? '';
    $fmt = fn ($n) => $isFr ? number_format($n, 0, ',', ' ') : number_format($n);

    $trustItems = [
        ['product-trust-1.png', $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity', $isFr ? 'Produits 100% authentiques' : '100% authentic products'],
        ['product-trust-2.png', $isFr ? 'Soutien aux artisans' : 'Support for artisans',     $isFr ? 'Vous contribuez à l\'économie locale' : 'You support the local economy'],
        ['product-trust-3.png', $isFr ? 'Paiement sécurisé' : 'Secure payment',              $isFr ? 'Transactions protégées' : 'Protected transactions'],
        ['product-trust-4.png', $isFr ? 'Livraison fiable' : 'Reliable delivery',            $isFr ? "Partout au Cameroun et\nà l'international" : "Across Cameroon and\nworldwide"],
        ['product-trust-5.png', $isFr ? 'Service client dédié' : 'Dedicated customer service',$isFr ? 'Nous sommes à votre écoute' : 'We are here to listen'],
    ];

@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Annuaire des produits — Découvrez des milliers de créations authentiques fabriquées par nos artisans et entreprises.' : 'Product directory — Discover thousands of authentic creations made by our artisans and businesses.' }}">
    <title>{{ $isFr ? 'Annuaire des produits — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Product directory — National Virtual Gallery of Cameroonian Crafts' }}</title>

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

@include('pages.partials.directory-header', ['dirNavActive' => 'products'])

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-5 pb-10">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar -->
        <aside class="w-full lg:w-[265px] shrink-0">
            <!-- Browse by official sector -->
            <div class="mb-4">@include('pages.partials.sector-browser')</div>
            <!-- Categories -->
            <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[38px]">
                    <i data-lucide="list" class="w-4 h-4 text-white"></i>
                    <span class="text-[12px] font-bold tracking-[0.1em] text-white uppercase">{{ $isFr ? 'Catégories' : 'Categories' }}</span>
                </div>
                <ul class="py-1.5">
                    @foreach($designSideCats as [$scSlug, $scLabel, $scCount, $scIcon])
                    @php $scActive = $scSlug === $activeCat; @endphp
                    <li>
                        <a href="{{ route('products.index', array_filter(['lang' => $lang, 'categorie' => $scSlug])) }}"
                            class="relative flex items-center gap-2.5 pl-4 pr-3 h-[30px] {{ $scActive ? 'bg-[#F9F4EC]' : 'hover:bg-[#FAFAF8]' }}">
                            @if($scActive)<span class="absolute left-0 top-0 bottom-0 w-[4px] bg-[#E7A320]"></span>@endif
                            <img src="{{ asset('images/landing/' . $scIcon) }}" alt="" class="w-[16px] h-[16px] object-contain shrink-0">
                            <span class="flex-1 truncate text-[12.5px] {{ $scActive ? 'font-semibold text-[#1D1B16]' : 'text-[#3A3A35]' }}">{{ $scLabel }}</span>
                            <span class="shrink-0 text-[10.5px] {{ $scActive ? 'bg-[#F8F1DE] border border-[#EAD9AC] text-[#7A5A12] font-semibold' : 'bg-white border border-[#E7E7E5] text-[#6F6B60]' }} rounded-full px-2 py-[1px]">{{ $scCount }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Filters -->
            <div class="mt-4 bg-white border border-[#ECECEA] rounded-xl p-4">
                <button type="button" class="w-full flex items-center justify-between" onclick="document.getElementById('filters-body').classList.toggle('hidden')">
                    <span class="flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4 text-[#1D1B16]"></i>
                        <span class="text-[12px] font-bold tracking-[0.1em] text-[#1D1B16] uppercase">{{ $isFr ? 'Filtres' : 'Filters' }}</span>
                    </span>
                    <i data-lucide="chevron-up" class="w-4 h-4 text-[#6F6B60]"></i>
                </button>
                <form id="filters-body" method="GET" action="{{ route('products.index') }}" class="mt-4">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @if($activeCat !== '')<input type="hidden" name="categorie" value="{{ $activeCat }}">@endif

                    <p class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Région' : 'Region' }}</p>
                    <select name="region" class="mt-2 w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12.5px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $regionRow)
                        <option value="{{ $regionRow->code }}" {{ ($region ?? '') === $regionRow->code ? 'selected' : '' }}>{{ $regionRow->name_fr }}</option>
                        @endforeach
                    </select>

                    <p class="mt-5 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Type de vendeur' : 'Vendor type' }}</p>
                    <div class="mt-2.5 space-y-2.5">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="artisan" {{ in_array('artisan', $vendorTypes ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">Artisan&nbsp;&nbsp;({{ $vendorTypeCounts['artisan'] ?? 0 }})</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="entreprise" {{ in_array('entreprise', $vendorTypes ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Entreprise' : 'Business' }}&nbsp;&nbsp;({{ $vendorTypeCounts['entreprise'] ?? 0 }})</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="cooperative" {{ in_array('cooperative', $vendorTypes ?? []) ? 'checked' : '' }} class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Coopérative' : 'Cooperative' }}&nbsp;&nbsp;({{ $vendorTypeCounts['cooperative'] ?? 0 }})</span>
                        </label>
                    </div>

                    <p class="mt-5 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Disponibilité' : 'Availability' }}</p>
                    <label class="mt-2.5 flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="dispo" value="1" {{ request()->boolean('dispo') ? 'checked' : '' }} class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                        <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Disponible maintenant' : 'Available now' }}</span>
                    </label>

                    <button type="submit" class="mt-6 w-full h-[40px] bg-[#02331C] hover:bg-leaf text-white text-[12.5px] font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="filter" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Appliquer les filtres' : 'Apply filters' }}
                    </button>
                    <a href="{{ route('products.index', ['lang' => $lang]) }}" class="mt-2.5 w-full h-[36px] border border-[#D7D7D7] hover:bg-[#FAFAF8] text-[#3A3A35] text-[12.5px] font-medium rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Réinitialiser' : 'Reset' }}
                    </a>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <section class="flex-1 min-w-0 relative">
            <img src="{{ asset('images/landing/product-stamp.png') }}" alt="{{ $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity' }}"
                class="hidden xl:block absolute right-0 top-2 w-[84px] select-none pointer-events-none">

            <nav class="flex items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
                <span class="text-[#1D1B16]">{{ $isFr ? 'Annuaire des produits' : 'Product directory' }}</span>
            </nav>

            <div class="mt-3 flex flex-wrap items-end justify-between gap-4 xl:pr-[110px]">
                <div>
                    <h1 class="font-serif text-[30px] sm:text-[34px] leading-tight text-[#1D1B16] font-semibold">{{ $isFr ? 'Annuaire des produits' : 'Product directory' }}</h1>
                    <p class="mt-1.5 text-[13px] text-[#55524A]">{{ $isFr ? 'Découvrez des milliers de créations authentiques fabriquées par nos artisans et entreprises.' : 'Discover thousands of authentic creations made by our artisans and businesses.' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2 h-[40px] bg-white border border-[#E3E3E1] rounded-lg px-3.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        @if($activeCat !== '')<input type="hidden" name="categorie" value="{{ $activeCat }}">@endif
                        <label for="sort-select" class="text-[12.5px] text-[#55524A] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort-select" name="sort" onchange="this.form.submit()"
                            class="text-[12.5px] font-medium text-[#1D1B16] bg-transparent focus:outline-none cursor-pointer">
                            <option value="recents" {{ ($sort ?? 'recents') === 'recents' ? 'selected' : '' }}>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                            <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                        </select>
                    </form>
                    <div class="flex items-center h-[40px] bg-white border border-[#E3E3E1] rounded-lg overflow-hidden">
                        <button type="button" id="view-grid" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}" class="w-[40px] h-full flex items-center justify-center bg-[#F2F5F2] text-[#0B3D28]">
                            <i data-lucide="layout-grid" class="w-[17px] h-[17px]"></i>
                        </button>
                        <span class="h-[22px] w-px bg-[#E3E3E1]"></span>
                        <button type="button" id="view-list" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}" class="w-[40px] h-full flex items-center justify-center text-[#8A857A]">
                            <i data-lucide="list" class="w-[17px] h-[17px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <p class="mt-5 flex items-center gap-2 text-[12.5px] font-semibold text-[#1D1B16]">
                <i data-lucide="package" class="w-[15px] h-[15px] text-[#0B3D28]"></i>
                {{ $fmt($liveCount) }} {{ $isFr ? 'produits disponibles' : 'products available' }}
            </p>

            <!-- Product grid -->
            <div id="product-grid" class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
                @forelse($shownProducts as $product)
                <article class="prod-card bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div class="prod-media relative">
                        <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}">
                            <img src="{{ $product['img'] }}" alt="{{ $product['name'] }}" class="prod-img w-full h-[168px] object-cover">
                        </a>
                        @if($product['badge'] === 'new')
                        <span class="absolute top-2.5 left-2.5 bg-[#0E3D26] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                        @elseif($product['badge'] === 'best')
                        <span class="absolute top-2.5 left-2.5 bg-[#EFA912] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">Best-seller</span>
                        @endif
                        <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                            class="absolute top-2 right-2 w-8 h-8 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
                            <i data-lucide="heart" class="w-[15px] h-[15px]"></i>
                        </a>
                    </div>
                    <div class="p-3">
                        <h3 class="text-[13px] font-bold text-[#1D1B16] truncate">
                            <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}" class="hover:text-leaf transition-colors">{{ $product['name'] }}</a>
                        </h3>
                        <p class="mt-1 text-[11.5px] text-[#6F6B60] truncate">{{ $product['cat'] }}</p>
                        <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}"
                            class="mt-3 w-full h-[34px] border border-[#E2E6E3] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center gap-2 text-[10.5px] font-bold tracking-[0.08em] text-[#26312B] uppercase transition-colors">
                            <i data-lucide="message-square" class="w-[13px] h-[13px]"></i>
                            {{ $isFr ? 'Enquérir' : 'Inquire' }}
                        </a>
                    </div>
                </article>
                @empty
                <p class="col-span-full py-10 text-center text-[13px] text-[#6F6B60]">
                    {{ $isFr ? 'Aucun produit ne correspond à ces critères.' : 'No product matches these criteria.' }}
                </p>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($products->lastPage() > 1)
            <nav class="mt-8 flex items-center justify-center gap-1.5" aria-label="Pagination">
                @if($products->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                @else
                <a href="{{ $products->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md" aria-label="{{ $isFr ? 'Page précédente' : 'Previous page' }}"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                @endif

                @foreach(range(1, $products->lastPage()) as $pageNum)
                @if($pageNum === $products->currentPage())
                <span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md" aria-current="page">{{ $pageNum }}</span>
                @else
                <a href="{{ $products->url($pageNum) }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pageNum }}</a>
                @endif
                @endforeach

                @if($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                @else
                <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                @endif
            </nav>
            @endif
        </section>
    </div>
</div>

<!-- Trust strip -->
<div class="bg-[#F8F6F2] border-t border-[#EEEBE4]">
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6 py-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @foreach($trustItems as [$trustIcon, $trustTitle, $trustSub])
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/landing/' . $trustIcon) }}" alt="" class="w-[42px] h-[42px] object-contain shrink-0">
                <div class="leading-tight">
                    <p class="text-[12.5px] font-bold text-[#1D1B16]">{{ $trustTitle }}</p>
                    <p class="mt-0.5 text-[11.5px] text-[#6F6B60] whitespace-pre-line">{{ $trustSub }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Grid / list toggle (persisted like the categories page's catView)
    const grid = document.getElementById('product-grid');
    const btnGrid = document.getElementById('view-grid');
    const btnList = document.getElementById('view-list');
    function setView(view) {
        const isList = view === 'list';
        grid.classList.toggle('grid-cols-2', !isList);
        grid.classList.toggle('sm:grid-cols-3', !isList);
        grid.classList.toggle('xl:grid-cols-6', !isList);
        grid.classList.toggle('grid-cols-1', isList);
        grid.querySelectorAll('.prod-card').forEach(c => {
            c.classList.toggle('flex', isList);
            c.classList.toggle('items-stretch', isList);
        });
        grid.querySelectorAll('.prod-media').forEach(m => {
            m.classList.toggle('w-[220px]', isList);
            m.classList.toggle('shrink-0', isList);
        });
        grid.querySelectorAll('.prod-img').forEach(i => {
            i.classList.toggle('h-full', isList);
            i.classList.toggle('h-[168px]', !isList);
        });
        btnGrid.classList.toggle('bg-[#F2F5F2]', !isList);
        btnGrid.classList.toggle('text-[#0B3D28]', !isList);
        btnGrid.classList.toggle('text-[#8A857A]', isList);
        btnList.classList.toggle('bg-[#F2F5F2]', isList);
        btnList.classList.toggle('text-[#0B3D28]', isList);
        btnList.classList.toggle('text-[#8A857A]', !isList);
        localStorage.setItem('prodView', view);
    }
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
    if (localStorage.getItem('prodView') === 'list') setView('list');
</script>
</body>
</html>
