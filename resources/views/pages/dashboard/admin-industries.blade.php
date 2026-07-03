@php
    $isFr = $lang === 'fr';
    $adminActive = 'industries';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Catégories & Régions — Administration' : 'Categories & Regions — Administration' }}</title>
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
            <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Catégories de Métiers & Régions' : 'Craft Categories & Regions' }}</h1>
            <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Taxonomie de la plateforme : secteurs d\'activité et régions, avec le nombre d\'entreprises rattachées.' : 'Platform taxonomy: industries and regions, with the number of attached businesses.' }}</p>

            <div class="mt-4 grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">
                <section class="bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                    <h2 class="px-5 pt-4 pb-2 text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Catégories de métiers' : 'Craft categories' }}</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-2.5 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                <th class="px-2 py-2.5 text-center text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Active' : 'Active' }}</th>
                                <th class="px-2 pr-5 py-2.5 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Entreprises' : 'Businesses' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @foreach($adminIndustries as $ind)
                            <tr>
                                <td class="pl-5 pr-2 py-2.5">
                                    <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? $ind->name_fr : ($ind->name_en ?? $ind->name_fr) }}</p>
                                    <p class="text-[11px] text-[#8A857A]">{{ $ind->slug }}</p>
                                </td>
                                <td class="px-2 py-2.5 text-center">
                                    <span class="inline-block rounded-md px-2.5 py-0.5 text-[10.5px] font-semibold {{ $ind->is_active ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDE8E8] text-[#B42025]' }}">{{ $ind->is_active ? 'Oui' : 'Non' }}</span>
                                </td>
                                <td class="px-2 pr-5 py-2.5 text-right text-[12px] font-semibold text-[#3B382F]">{{ $ind->business_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>

                <section id="regions" class="bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                    <h2 class="px-5 pt-4 pb-2 text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Régions & centres d\'artisanat' : 'Regions & craft centres' }}</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-2.5 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Région' : 'Region' }}</th>
                                <th class="px-2 pr-5 py-2.5 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Entreprises' : 'Businesses' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @foreach($adminRegions as $reg)
                            <tr>
                                <td class="pl-5 pr-2 py-2.5 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? $reg->name_fr : ($reg->name_en ?? $reg->name_fr) }}</td>
                                <td class="px-2 pr-5 py-2.5 text-right text-[12px] font-semibold text-[#3B382F]">{{ $reg->business_count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
