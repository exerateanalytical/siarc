@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $cName = $isFr ? $collection->name_fr : ($collection->name_en ?? $collection->name_fr);
    $cDesc = $isFr ? $collection->description_fr : ($collection->description_en ?? $collection->description_fr);
    $cover = $collection->cover_image ? asset('storage/'.$collection->cover_image) : asset('images/landing/hc-masques.png');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $cDesc ?? ($isFr ? 'Collection héritage — patrimoine artisanal du Cameroun.' : 'Heritage collection — Cameroon\'s craft heritage.') }}">
    <title>{{ $cName }} — {{ $isFr ? 'Collections Héritage' : 'Heritage Collections' }} — Artisan Hub 237</title>
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 201 148 46;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>
    @include('pages.partials.icons')
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#FBF8F2] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">
@include('pages.partials.directory-header')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-[#0E2C1A] to-[#123D24] overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <img src="{{ $cover }}" alt="" class="w-full h-full object-cover">
    </div>
    <div class="relative max-w-[1240px] mx-auto px-4 sm:px-6 py-12 text-center">
        <nav class="text-[11.5px] text-[#CFE3D5] mb-4">
            <a href="{{ route('collections.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Collections Héritage' : 'Heritage Collections' }}</a>
            <span class="mx-1.5">/</span>
            <span class="text-white">{{ $cName }}</span>
        </nav>
        @if($collection->category_fr)
        <span class="inline-block bg-[#0A3B22] border border-[#E9C25A]/40 rounded-md px-3 py-1 text-[10.5px] font-bold tracking-[0.12em] text-[#E9C25A] uppercase">{{ $isFr ? $collection->category_fr : ($collection->category_en ?? $collection->category_fr) }}</span>
        @endif
        <h1 class="mt-4 font-serif text-[30px] sm:text-[40px] font-bold text-[#F3E7C9] leading-tight">{{ $cName }}</h1>
        @if($cDesc)
        <p class="mt-3 text-[13.5px] text-[#DCEAE0] max-w-[600px] mx-auto leading-relaxed">{{ $cDesc }}</p>
        @endif
        <div class="mt-5 flex items-center justify-center gap-6 text-white text-[12px]">
            @if($collection->region_fr)
            <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#E9C25A]"></i>{{ $isFr ? $collection->region_fr : ($collection->region_en ?? $collection->region_fr) }}</span>
            @endif
            <span class="flex items-center gap-1.5"><i data-lucide="package" class="w-3.5 h-3.5 text-[#E9C25A]"></i>{{ $products->total() }} {{ $isFr ? 'objets' : 'items' }}</span>
        </div>
    </div>
</section>

<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-10">
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($products as $product)
        @php
            $pName = $isFr ? $product->name_fr : ($product->name_en ?? $product->name_fr);
            $pImg = $product->primaryImage ? asset('storage/'.$product->primaryImage->file_path) : asset('images/landing/default-product-arts-decoration.png');
            $pBiz = $product->business;
        @endphp
        <article class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}">
                <img src="{{ $pImg }}" alt="{{ $pName }}" class="w-full h-[168px] object-cover">
            </a>
            <div class="p-3">
                <h3 class="text-[13px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] truncate">
                    <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $pName }}</a>
                </h3>
                @if($pBiz)
                <p class="mt-1 text-[11.5px] text-[#6F6B60] dark:text-[#868778] truncate">{{ $isFr ? $pBiz->name_fr : ($pBiz->name_en ?? $pBiz->name_fr) }}</p>
                @endif
            </div>
        </article>
        @empty
        <p class="col-span-full text-center py-14 text-[13px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Aucun produit dans cette collection pour le moment.' : 'No products in this collection yet.' }}</p>
        @endforelse
    </div>

    @if($products->lastPage() > 1)
    <nav class="mt-8 flex items-center justify-center gap-1.5" aria-label="Pagination">
        @if($products->onFirstPage())
        <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
        @else
        <a href="{{ $products->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09] rounded-md" aria-label="{{ $isFr ? 'Page précédente' : 'Previous page' }}"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
        @endif
        @foreach(range(1, $products->lastPage()) as $pageNum)
        <a href="{{ $products->url($pageNum) }}" class="w-8 h-8 flex items-center justify-center rounded-md text-[12.5px] {{ $pageNum === $products->currentPage() ? 'bg-[#0A3020] text-white' : 'text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09]' }}">{{ $pageNum }}</a>
        @endforeach
        @if($products->hasMorePages())
        <a href="{{ $products->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
        @else
        <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
        @endif
    </nav>
    @endif
</div>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
