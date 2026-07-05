@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'regions';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Régions & Centres' : 'Regions & Centres', null]];
    $fmt = fn ($n) => number_format($n, 0, ',', ' ');
    $cName = $isFr ? $centre->name_fr : ($centre->name_en ?? $centre->name_fr);
    $pageTitle = $cName;
    $regionName = $isFr ? $centre->region_fr : ($centre->region_en ?? $centre->region_fr);

    // Real métier distribution from the region's businesses by industry
    $metierRows = DB::table('businesses as b')->join('industries as i', 'i.id', '=', 'b.industry_id')
        ->whereNull('b.deleted_at')->where('b.region_id', $centre->region_id)
        ->select('i.name_fr', 'i.name_en', DB::raw('count(*) n'))->groupBy('i.name_fr', 'i.name_en')->orderByDesc('n')->get();
    $metierTotal = max(1, $metierRows->sum('n'));
    $palette = ['#157A43', '#C9942E', '#3565DE', '#7C4FE0', '#E07B2A', '#9B1C31', '#0E9F9F', '#C86A4A'];
    $angle = 0; $segs = [];
    foreach ($metierRows as $mi => $m) { $to = $angle + ($m->n / $metierTotal) * 360; $segs[] = sprintf('%s %.2fdeg %.2fdeg', $palette[$mi % 8], $angle, $to); $angle = $to; }
    $metierConic = $segs ? 'conic-gradient(' . implode(', ', $segs) . ')' : '#EEE';

    $tabs = [
        [$isFr ? 'Vue d\'ensemble' : 'Overview', true], ['Artisans', false], [$isFr ? 'Produits & Services' : 'Products', false],
        [$isFr ? 'Statistiques' : 'Statistics', false], ['Infrastructures', false], [$isFr ? 'Événements' : 'Events', false],
        ['Documents', false], [$isFr ? 'Paramètres' : 'Settings', false],
    ];
    $cards = [
        ['users', '#157A43', '#E8F2EC', $centre->artisans_count, 'Artisans'],
        ['package', '#3565DE', '#E8EFFB', DB::table('products as p')->join('businesses as b','b.id','=','p.business_id')->where('b.region_id',$centre->region_id)->where('p.status','published')->count(), $isFr ? 'Produits & Services' : 'Products & Services'],
        ['store', '#C97A16', '#FDF3E0', DB::table('businesses')->whereNull('deleted_at')->where('region_id',$centre->region_id)->count(), $isFr ? 'Ateliers & Boutiques' : 'Workshops & Shops'],
        ['calendar-days', '#7C4FE0', '#F0EAFB', DB::table('events')->count(), $isFr ? 'Événements' : 'Events'],
        ['ruler', '#0E9F9F', '#E4F5F5', $regionName, $isFr ? 'Région' : 'Region'],
    ];
    $infoLeft = [
        [$isFr ? 'Nom du Centre' : 'Centre name', $cName],
        [$isFr ? 'Région' : 'Region', $regionName],
        [$isFr ? 'Ville' : 'City', $centre->city ?? $centre->chef_lieu],
        [$isFr ? 'Adresse' : 'Address', $centre->address ?? ($centre->city . ', ' . $regionName)],
        [$isFr ? 'Téléphone' : 'Phone', $centre->contact_phone],
        [$isFr ? 'Type de Centre' : 'Centre type', $centre->type === 'principal' ? ($isFr ? 'Centre Principal' : 'Main Centre') : ($isFr ? 'Centre Secondaire' : 'Secondary Centre')],
        [$isFr ? 'Spécialités' : 'Specialties', $centre->specialties_fr],
        [$isFr ? 'Statut' : 'Status', $isFr ? 'Actif' : 'Active'],
    ];
@endphp

@section('content')
            {{-- Title + actions --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <span class="rounded-md px-2.5 py-0.5 text-[11.5px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr ? 'Actif' : 'Active' }}</span>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin.regions', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la liste' : 'Back to list' }}</a>
                    <a href="{{ route('admin.regions', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#14652F]"><i data-lucide="pencil" class="w-4 h-4"></i>{{ $isFr ? 'Modifier le centre' : 'Edit centre' }}</a>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 flex items-center gap-6 border-b border-[#EAE7DE] overflow-x-auto">
                @foreach($tabs as [$tLabel, $tActive])
                <span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}">{{ $tLabel }}</span>
                @endforeach
            </div>

            {{-- Stat cards --}}
            <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($cards as [$cIcon, $cColor, $cTile, $cValue, $cLabel])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
                    <span class="w-[44px] h-[44px] rounded-xl flex items-center justify-center" style="background-color: {{ $cTile }}"><i data-lucide="{{ $cIcon }}" class="w-[21px] h-[21px]" style="color: {{ $cColor }};stroke-width:1.8"></i></span>
                    <p class="mt-3 text-[20px] font-bold text-[#1B1B18] leading-none">{{ is_numeric($cValue) ? $fmt($cValue) : $cValue }}</p>
                    <p class="mt-1 text-[12px] font-semibold text-[#3B382F]">{{ $cLabel }}</p>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Informations Générales --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations Générales' : 'General Information' }}</h2>
                        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                            @foreach($infoLeft as [$iLabel, $iVal])
                            <div class="flex items-start justify-between gap-3 border-b border-[#F4F5F4] pb-2.5"><dt class="text-[12px] text-[#6F6B60]">{{ $iLabel }}</dt><dd class="text-[12.5px] font-semibold text-[#1B1B18] text-right">{{ $iVal }}</dd></div>
                            @endforeach
                        </dl>
                        <div class="mt-4 flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-[#F3F0E6] flex items-center justify-center"><i data-lucide="user-cog" class="w-4 h-4 text-[#14652F]"></i></span>
                            <div><p class="text-[11px] text-[#6F6B60]">{{ $isFr ? 'Coordonnateur' : 'Coordinator' }}</p><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $centre->chef_lieu ? 'Jean Mbarga' : '—' }}</p></div>
                        </div>
                    </section>

                    {{-- Métier distribution --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition par Métiers' : 'Distribution by craft' }}</h2>
                        <div class="mt-4 flex items-center gap-6">
                            <span class="w-[120px] h-[120px] rounded-full shrink-0 relative" style="background: {{ $metierConic }}"><span class="absolute inset-[26px] rounded-full bg-white"></span></span>
                            <div class="flex-1 space-y-2">
                                @forelse($metierRows as $mi => $m)
                                <div class="flex items-center justify-between text-[12px]"><span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $palette[$mi % 8] }}"></span>{{ $isFr ? $m->name_fr : ($m->name_en ?? $m->name_fr) }}</span><span class="font-semibold text-[#1B1B18]">{{ round($m->n / $metierTotal * 100, 1) }}% ({{ $m->n }})</span></div>
                                @empty
                                <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Aucun artisan répertorié dans cette région pour le moment.' : 'No artisans listed in this region yet.' }}</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    {{-- Businesses of the region --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Artisans & Boutiques du centre' : 'Artisans & Shops of the centre' }}</h2>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @forelse($businesses as $b)
                            <a href="{{ route('admin.businesses.detail', ['id' => $b->id, 'lang' => $lang]) }}" class="flex items-center gap-3 border border-[#EFF0EF] rounded-xl px-3.5 py-3 hover:border-[#14652F]">
                                @if($b->logo)<img src="{{ asset('storage/' . $b->logo) }}" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                @else<span class="w-10 h-10 rounded-lg bg-[#14652F] text-white text-[14px] font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($b->name_fr,0,1)) }}</span>@endif
                                <span class="min-w-0"><span class="block text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $b->name_fr }}</span><span class="block text-[11px] text-[#8A857A]">{{ $isFr ? 'Voir le profil' : 'View profile' }}</span></span>
                            </a>
                            @empty
                            <p class="text-[12px] text-[#6F6B60] col-span-full">{{ $isFr ? 'Aucune boutique répertoriée dans cette région.' : 'No shops listed in this region.' }}</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations de Contact' : 'Contact Information' }}</h2>
                        <dl class="mt-3.5 space-y-2.5 text-[12px]">
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr ? 'Responsable' : 'Manager' }}</dt><dd class="font-semibold text-[#1B1B18]">Jean Mbarga</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $centre->contact_phone }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">Email</dt><dd class="font-semibold text-[#1B1B18] truncate">{{ $centre->contact_email ?? 'contact@artisanat.cm' }}</dd></div>
                            <div class="flex items-center justify-between"><dt class="text-[#6F6B60]">{{ $isFr ? 'Ville' : 'City' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $centre->city ?? $centre->chef_lieu }}</dd></div>
                        </dl>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions Rapides' : 'Quick Actions' }}</h2>
                        <div class="mt-3 space-y-2">
                            <a href="{{ route('admin.regions', ['lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-[#EFF6F1] hover:bg-[#E2F3E8] rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-[#14652F]"><i data-lucide="pencil" class="w-4 h-4"></i>{{ $isFr ? 'Modifier le Centre' : 'Edit Centre' }}</a>
                            <a href="{{ route('admin.businesses', ['lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-[#F0EAFB] hover:bg-[#E7DCFA] rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-[#7C4FE0]"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un Artisan' : 'Add an Artisan' }}</a>
                            <a href="{{ route('centres.show', ['slug'=>$centre->slug, 'lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-[#E8EFFB] hover:bg-[#DBE8FA] rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-[#3565DE]"><i data-lucide="external-link" class="w-4 h-4"></i>{{ $isFr ? 'Voir la page publique' : 'View public page' }}</a>
                        </div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
@endsection
