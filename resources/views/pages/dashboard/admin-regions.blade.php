@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'regions';
    $pageTitle = $isFr ? 'Régions & Centres d\'Artisanat' : 'Regions & Craft Centres';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Gestion du contenu' : 'Content management', null]];
    $fmt = fn ($n) => number_format($n, 0, ',', ' ');

    $cards = [
        ['map',          '#157A43', '#E8F2EC', $stats['regions'],  $isFr ? 'Régions' : 'Regions',                     $isFr ? 'Toutes les régions du Cameroun' : 'All regions of Cameroon'],
        ['building-2',   '#3565DE', '#E8EFFB', $stats['centres'],  $isFr ? 'Centres d\'Artisanat' : 'Craft Centres',  $isFr ? 'Centres enregistrés' : 'Registered centres'],
        ['users',        '#C97A16', '#FDF3E0', $stats['artisans'], 'Artisans',                                         $isFr ? 'Répartis dans tout le pays' : 'Across the country'],
        ['shopping-basket','#7C4FE0','#F0EAFB', $stats['products'], $isFr ? 'Produits & Services' : 'Products & Services', $isFr ? 'Répertoriés' : 'Listed'],
    ];
    $dotColors = ['#DC7A18', '#E9A83A', '#3565DE', '#0E9F9F', '#E45C9A', '#3FA96A', '#C9942E', '#E07B2A', '#7C4FE0', '#9B1C31'];
@endphp

@section('content')
            {{-- Title-row actions --}}
            <div class="flex flex-wrap items-start justify-end gap-3">
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin.industries', ['lang' => $lang]) }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-white transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter une Région/Centre' : 'Add a Region/Centre' }}</a>
                    <a href="{{ route('admin.exports', ['lang' => $lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#C9942E] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-[#3B382F]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
                </div>
            </div>

            {{-- Stat cards --}}
            <section class="mt-5 grid grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($cards as [$cIcon, $cColor, $cTile, $cValue, $cLabel, $cSub])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4 flex items-center gap-4">
                    <span class="w-[52px] h-[52px] rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $cTile }}"><i data-lucide="{{ $cIcon }}" class="w-[24px] h-[24px]" style="color: {{ $cColor }};stroke-width:1.7"></i></span>
                    <div class="min-w-0">
                        <p class="text-[22px] font-bold text-[#1B1B18] leading-none">{{ $fmt($cValue) }}</p>
                        <p class="mt-1 text-[12.5px] font-semibold text-[#3B382F]">{{ $cLabel }}</p>
                        <p class="text-[11px] text-[#6F6B60]">{{ $cSub }}</p>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1fr)_300px] gap-5 items-start">
                {{-- Map --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Carte des Régions & Centres' : 'Map of Regions & Centres' }}</h2>
                    <div class="relative mt-3 rounded-xl bg-[#F7F8F7] overflow-hidden">
                        <img src="{{ asset('images/landing/rg-map.png') }}" alt="{{ $isFr ? 'Carte du Cameroun' : 'Map of Cameroon' }}" class="w-full h-auto object-contain">
                        <div class="absolute top-3 left-3 flex flex-col gap-1">
                            <span class="w-8 h-8 rounded-lg bg-white border border-[#E5E7E5] flex items-center justify-center text-[#3B382F]"><i data-lucide="plus" class="w-4 h-4"></i></span>
                            <span class="w-8 h-8 rounded-lg bg-white border border-[#E5E7E5] flex items-center justify-center text-[#3B382F]"><i data-lucide="minus" class="w-4 h-4"></i></span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] text-[#55524A]">
                        <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#157A43]"></i>{{ $isFr ? 'Centre Principal' : 'Main centre' }}</span>
                        <span class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#C9942E]"></i>{{ $isFr ? 'Centre Secondaire' : 'Secondary centre' }}</span>
                    </div>
                </section>

                {{-- Regions list --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Liste des Régions' : 'Regions List' }}</h2>
                        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir toutes les régions' : 'View all regions' }}</a>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[520px]">
                            <thead><tr class="text-left border-b border-[#F0F1F0]">
                                <th class="pb-2.5 pr-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Région' : 'Region' }}</th>
                                <th class="pb-2.5 px-2 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Centres</th>
                                <th class="pb-2.5 px-2 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Artisans</th>
                                <th class="pb-2.5 px-2 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Produits' : 'Products' }}</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Statut</th>
                                <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-[#F4F5F4]">
                                @foreach($regions as $i => $r)
                                <tr>
                                    <td class="py-3 pr-2">
                                        <a href="{{ route('admin.regions', ['lang' => $lang, 'region' => $r->code]) }}" class="flex items-center gap-2.5 group">
                                            <span class="w-6 h-6 rounded-full text-white text-[11px] font-bold flex items-center justify-center shrink-0" style="background-color: {{ $dotColors[$i % 10] }}">{{ $i + 1 }}</span>
                                            <span class="text-[12.5px] font-semibold text-[#1B1B18] group-hover:text-[#157A43]">{{ $isFr ? $r->name_fr : ($r->name_en ?? $r->name_fr) }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-2 text-center text-[12.5px] font-semibold text-[#1B1B18]">{{ $r->centres }}</td>
                                    <td class="py-3 px-2 text-center text-[12.5px] text-[#3B382F]">{{ $fmt($r->artisans) }}</td>
                                    <td class="py-3 px-2 text-center text-[12.5px] text-[#3B382F]">{{ $fmt($r->products) }}</td>
                                    <td class="py-3 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr ? 'Actif' : 'Active' }}</span></td>
                                    <td class="py-3 pl-2">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.regions', ['lang' => $lang, 'region' => $r->code]) }}" class="w-7 h-7 rounded-md border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                            <a href="{{ route('admin.regions', ['lang' => $lang, 'region' => $r->code]) }}" class="w-7 h-7 rounded-md border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                {{-- Region detail rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Détails de la Région' : 'Region details' }}</h2>
                            <form method="GET"><input type="hidden" name="lang" value="{{ $lang }}">
                                <select name="region" onchange="this.form.submit()" class="text-[11.5px] border border-[#E5E7E5] rounded-md px-2 py-1 cursor-pointer">
                                    @foreach($regions as $r)<option value="{{ $r->code }}" @selected($selected && $r->code === $selected->code)>{{ $isFr ? $r->name_fr : ($r->name_en ?? $r->name_fr) }}</option>@endforeach
                                </select>
                            </form>
                        </div>
                        <img src="{{ asset('images/landing/sd-region-map.png') }}" alt="" class="mt-3 w-full h-[120px] object-cover rounded-xl" aria-hidden="true">
                        <div class="mt-3 flex items-center justify-between">
                            <h3 class="text-[16px] font-bold text-[#1B1B18]">{{ $isFr ? $selected->name_fr : ($selected->name_en ?? $selected->name_fr) }}</h3>
                            <span class="rounded-md px-2 py-0.5 text-[11px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr ? 'Actif' : 'Active' }}</span>
                        </div>
                        <p class="mt-1.5 text-[12px] text-[#6F6B60] leading-relaxed">{{ $isFr ? $selected->description_fr : ($selected->description_en ?? $selected->description_fr) }}</p>
                        <dl class="mt-3.5 space-y-2.5 text-[12px]">
                            @foreach([
                                ['map-pin', $isFr ? 'Chef-lieu' : 'Capital', $selected->chef_lieu],
                                ['building-2', $isFr ? 'Centres d\'Artisanat' : 'Craft Centres', $selected->centres],
                                ['users', 'Artisans', $fmt($selected->artisans)],
                                ['shopping-basket', $isFr ? 'Produits & Services' : 'Products & Services', $fmt($selected->products)],
                                ['user-round', 'Population', $fmt($selected->population)],
                                ['ruler', $isFr ? 'Superficie' : 'Area', $fmt($selected->area_km2) . ' km²'],
                                ['calendar', $isFr ? 'Date de création' : 'Created', $selected->created_year],
                                ['user-cog', $isFr ? 'Coordonnateur Régional' : 'Regional Coordinator', $selected->coordinator],
                            ] as [$dIcon, $dLabel, $dVal])
                            <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-2 text-[#6F6B60]"><i data-lucide="{{ $dIcon }}" class="w-3.5 h-3.5"></i>{{ $dLabel }}</dt><dd class="font-semibold text-[#1B1B18] text-right">{{ $dVal }}</dd></div>
                            @endforeach
                        </dl>
                        <a href="{{ route('businesses.index', ['lang' => $lang, 'region' => $selected->code]) }}" class="mt-4 block text-center bg-[#0F4824] hover:bg-[#14652F] rounded-lg py-2.5 text-[12.5px] font-semibold text-white transition-colors">{{ $isFr ? 'Voir le profil complet' : 'View full profile' }}</a>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Statistiques Nationales' : 'National statistics' }}</h2>
                            <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-[#EFF6F1] py-2.5"><p class="text-[16px] font-bold text-[#157A43]">{{ $fmt($stats['centres']) }}</p><p class="text-[10px] text-[#6F6B60]">Centres</p></div>
                            <div class="rounded-lg bg-[#FDF3E0] py-2.5"><p class="text-[16px] font-bold text-[#C97A16]">{{ $fmt($stats['artisans']) }}</p><p class="text-[10px] text-[#6F6B60]">Artisans</p></div>
                            <div class="rounded-lg bg-[#F0EAFB] py-2.5"><p class="text-[16px] font-bold text-[#7C4FE0]">{{ $fmt($stats['products']) }}</p><p class="text-[10px] text-[#6F6B60]">{{ $isFr ? 'Produits' : 'Products' }}</p></div>
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions Rapides' : 'Quick Actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            @foreach([
                                ['plus', $isFr ? 'Ajouter une Région' : 'Add a Region', route('admin.industries', ['lang'=>$lang])],
                                ['building-2', $isFr ? 'Ajouter un Centre d\'Artisanat' : 'Add a Craft Centre', route('admin.industries', ['lang'=>$lang])],
                                ['file-text', $isFr ? 'Rapport des Régions' : 'Regions Report', route('admin.reports', ['lang'=>$lang])],
                                ['map', $isFr ? 'Carte Interactive' : 'Interactive Map', route('businesses.index', ['lang'=>$lang])],
                                ['download', $isFr ? 'Exporter les Données' : 'Export Data', route('admin.exports', ['lang'=>$lang])],
                            ] as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="{{ $qIcon }}" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qLabel }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></a>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>

            {{-- Recent centres --}}
            <section class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Centres d\'Artisanat Récents' : 'Recent Craft Centres' }}</h2>
                    <a href="{{ route('admin.regions', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir tous les centres' : 'View all centres' }}</a>
                </div>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[820px]">
                        <thead><tr class="text-left border-b border-[#F0F1F0]">
                            <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Centre</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Région' : 'Region' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Type</th>
                            <th class="pb-2.5 px-2 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Artisans</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Spécialités' : 'Specialties' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Statut</th>
                            <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-[#F4F5F4]">
                            @foreach($centres as $c)
                            <tr>
                                <td class="py-3 pr-3">
                                    <a href="{{ route('admin.centres.detail', ['id' => $c->id, 'lang' => $lang]) }}" class="flex items-center gap-2.5 group">
                                        <span class="w-8 h-8 rounded-full bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="{{ $c->type === 'principal' ? 'landmark' : 'home' }}" class="w-4 h-4 text-[#14652F]"></i></span>
                                        <span class="text-[12.5px] font-semibold text-[#1B1B18] group-hover:text-[#157A43]">{{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</span>
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-[12px] text-[#157A43] font-medium">{{ $isFr ? $c->region_fr : ($c->region_en ?? $c->region_fr) }}</td>
                                <td class="py-3 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $c->type === 'principal' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDF3E0] text-[#C97A16]' }}">{{ $c->type === 'principal' ? ($isFr ? 'Principal' : 'Main') : ($isFr ? 'Secondaire' : 'Secondary') }}</span></td>
                                <td class="py-3 px-2 text-center text-[12.5px] font-semibold text-[#1B1B18]">{{ $fmt($c->artisans_count) }}</td>
                                <td class="py-3 px-2 text-[12px] text-[#3B382F]">{{ $c->specialties_fr }}</td>
                                <td class="py-3 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr ? 'Actif' : 'Active' }}</span></td>
                                <td class="py-3 pl-2">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.centres.detail', ['id' => $c->id, 'lang' => $lang]) }}" class="w-7 h-7 rounded-md border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                        <a href="{{ route('admin.centres.detail', ['id' => $c->id, 'lang' => $lang]) }}" class="w-7 h-7 rounded-md border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($centres->hasPages())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $centres->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $centres->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $centres->total() }} centres</p>
                    <div class="flex items-center gap-1.5">
                        @if($centres->onFirstPage())<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                        @else<a href="{{ $centres->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>@endif
                        @foreach($centres->getUrlRange(1, $centres->lastPage()) as $pn => $url)
                        @if($pn === $centres->currentPage())<span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>
                        @else<a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pn }}</a>@endif
                        @endforeach
                        @if($centres->hasMorePages())<a href="{{ $centres->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                        @else<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>@endif
                    </div>
                </div>
                @endif
            </section>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
@endsection
