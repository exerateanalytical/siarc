@php
    $isFr = $lang === 'fr';
    $adminActive = 'products';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Produits & Services — Administration' : 'Products & Services — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Produits & Services' : 'Products & Services' }}</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Tous les produits référencés sur la plateforme.' : 'All the products listed on the platform.' }}</p>
                </div>
                <a href="{{ route('products.web-create') }}" class="shrink-0 inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
                </a>
            </div>

            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Entreprise' : 'Business' }}</th>
                                <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Vues' : 'Views' }}</th>
                                <th class="px-2 pr-5 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @forelse($adminProducts as $p)
                            <tr>
                                <td class="pl-5 pr-2 py-3">
                                    <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr) }}</p>
                                    <p class="text-[11px] text-[#8A857A]">{{ $p->slug }}</p>
                                </td>
                                <td class="px-2 py-3 text-[12px] text-[#3B382F]">{{ $p->business_name ?? '—' }}</td>
                                <td class="px-2 py-3 text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold {{ $p->status === 'published' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDF0DC] text-[#C97A16]' }}">{{ $p->status }}</span>
                                </td>
                                <td class="px-2 py-3 text-right text-[12px] text-[#3B382F]">{{ number_format($p->views_count) }}</td>
                                <td class="px-2 pr-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('products.show', ['slug' => $p->slug, 'lang' => $lang]) }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir' : 'View' }}</a>
                                    <span class="text-[#D9D4C8] mx-1.5">|</span>
                                    <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#C97A16] hover:text-[#8A6D1F]">{{ $isFr ? 'Modifier' : 'Edit' }}</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun produit.' : 'No products.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
