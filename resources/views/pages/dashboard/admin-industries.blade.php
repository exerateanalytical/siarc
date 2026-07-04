@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'industries';
    $pageTitle = $isFr ? 'Catégories de Métiers' : 'Craft Categories';

    $catCards = [
        ['grid-3x3', '#157A43', '#E2F3E8', $catKpis['total'], $isFr ? 'Total Catégories' : 'Total Categories', $isFr ? 'Toutes catégories' : 'All categories'],
        ['folder', '#3565DE', '#E8EFFB', $catKpis['principales'], $isFr ? 'Catégories Principales' : 'Main Categories', $isFr ? 'Niveau 1' : 'Level 1'],
        ['layers', '#7C4FE0', '#F0EAFB', $catKpis['sous'], $isFr ? 'Sous-Catégories' : 'Sub-categories', $isFr ? 'Niveau 2+' : 'Level 2+'],
        ['check-circle-2', '#C97A16', '#FDF3E0', $catKpis['active'], $isFr ? 'Actives' : 'Active', $catKpis['total'] > 0 ? round($catKpis['active'] / $catKpis['total'] * 100, 1) . '% du total' : '—'],
        ['eye-off', '#DC2626', '#FDE8E8', $catKpis['inactive'], $isFr ? 'Inactives' : 'Inactive', $catKpis['total'] > 0 ? round($catKpis['inactive'] / $catKpis['total'] * 100, 1) . '% du total' : '—'],
    ];

    $levelTotal = max(1, array_sum($catLevelDist));
    $levelColors = [1 => '#157A43', 2 => '#3565DE'];
    $deg = 0; $levelStops = [];
    foreach ($catLevelDist as $lvl => $c) { $pct = $c / $levelTotal * 100; $start = $deg; $deg += $pct / 100 * 360; $levelStops[] = $levelColors[$lvl] . " {$start}deg {$deg}deg"; }
    $levelConic = 'conic-gradient(' . implode(', ', $levelStops) . ')';

    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $fmtDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '—';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d %02d:%02d', $d->day, $monthsFr[$d->month], $d->year, $d->hour, $d->minute) : $d->format('d M Y H:i');
    };
@endphp

@section('content')
<div class="max-w-[1400px]">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="flex items-center gap-1.5 text-[11.5px] text-[#8A857A]">
            <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i><span>{{ $isFr ? 'Gestion du contenu' : 'Content management' }}</span>
            <i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-[#55524A]">{{ $pageTitle }}</span>
        </p>
        <div class="flex items-center gap-2">
            <a href="#add-category" class="inline-flex items-center gap-1.5 bg-[#0F4824] hover:bg-[#14652F] text-white text-[12.5px] font-semibold px-3.5 py-2 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter une Catégorie' : 'Add a Category' }}</a>
            <a href="{{ route('admin.exports') }}" class="inline-flex items-center gap-1.5 bg-white border border-[#E7E7E5] hover:border-[#14652F] text-[#3B382F] text-[12.5px] font-semibold px-3.5 py-2 rounded-lg"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
        </div>
    </div>

    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
        @foreach($catCards as [$ccIcon, $ccColor, $ccTile, $ccValue, $ccLabel, $ccSub])
        <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
            <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $ccTile }}"><i data-lucide="{{ $ccIcon }}" class="w-[19px] h-[19px]" style="color: {{ $ccColor }};stroke-width:1.8"></i></span>
            <p class="mt-3 text-[22px] font-extrabold text-[#1B1B18] leading-none">{{ $ccValue }}</p>
            <p class="mt-1 text-[11.5px] font-semibold text-[#3B382F]">{{ $ccLabel }}</p>
            <p class="mt-0.5 text-[10.5px] text-[#8A857A]">{{ $ccSub }}</p>
        </div>
        @endforeach
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5 items-start">
        {{-- Main: filters + hierarchical table --}}
        <div class="bg-white border border-[#EFF0EF] rounded-2xl overflow-hidden">
            <form method="GET" class="px-5 py-3.5 flex flex-wrap items-center gap-2.5 border-b border-[#F1F1EF]">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2 bg-[#F8F8F6] border border-[#E7E7E5] rounded-lg px-3 h-[38px] flex-1 min-w-[160px]">
                    <input type="text" name="q" value="{{ $catQ }}" placeholder="{{ $isFr ? 'Rechercher une catégorie...' : 'Search a category...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none">
                    <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
                </div>
                <select name="status" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                    <option value="">{{ $isFr ? 'Tous' : 'All' }}</option>
                    <option value="active" @selected($catStatus==='active')>{{ $isFr ? 'Actif' : 'Active' }}</option>
                    <option value="inactive" @selected($catStatus==='inactive')>{{ $isFr ? 'Inactif' : 'Inactive' }}</option>
                </select>
                <select name="parent" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white max-w-[180px]">
                    <option value="">{{ $isFr ? 'Toutes catégories' : 'All categories' }}</option>
                    @foreach($catTop as $t)<option value="{{ $t->id }}" @selected($catParent==(string)$t->id)>{{ $t->name_fr }}</option>@endforeach
                </select>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead><tr class="text-[10.5px] font-bold text-[#8A857A]">
                        <th class="px-5 py-2.5">{{ $isFr ? 'CATÉGORIE' : 'CATEGORY' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'NIVEAU' : 'LEVEL' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'SOUS-CATÉGORIES' : 'SUB-CATEGORIES' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'ARTISANS' : 'ARTISANS' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'PRODUITS' : 'PRODUCTS' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'STATUT' : 'STATUS' }}</th>
                        <th class="px-5 py-2.5 text-right">{{ $isFr ? 'ACTIONS' : 'ACTIONS' }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($catPagedRows as $row)
                            @php $isSub = (bool) $row->parent_id; $subCount = $catByParent->get($row->id, collect())->count(); @endphp
                        <tr class="border-t border-[#F1F1EF] hover:bg-[#FAFAF8] {{ $isSub ? 'bg-[#FDFDFC]' : '' }}">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5 {{ $isSub ? 'pl-5' : '' }}">
                                    @if($isSub)<span class="w-1.5 h-1.5 rounded-full bg-[#3565DE] shrink-0"></span>@endif
                                    <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] border border-[#EFEFED] flex items-center justify-center shrink-0"><i data-lucide="{{ $row->icon ?? 'shapes' }}" class="w-4 h-4 text-[#8A857A]"></i></div>
                                    <div class="min-w-0">
                                        <a href="{{ request()->fullUrlWithQuery(['selected' => $row->id]) }}" class="text-[12.5px] font-semibold text-[#1B1B18] hover:text-[#157A43] truncate block">{{ $isFr ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</a>
                                        <p class="text-[11px] text-[#8A857A] truncate max-w-[220px]">{{ $row->description_fr }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isSub ? 'bg-[#E8EFFB] text-[#3565DE]' : 'bg-[#E2F3E8] text-[#157A43]' }}">{{ $isSub ? ($isFr ? 'Niveau 2' : 'Level 2') : ($isFr ? 'Niveau 1' : 'Level 1') }}</span></td>
                            <td class="px-3 py-3 text-[12.5px] text-[#3B382F]">{{ $isSub ? '—' : $subCount }}</td>
                            <td class="px-3 py-3 text-[12.5px] text-[#3B382F]">{{ number_format($row->business_count) }}</td>
                            <td class="px-3 py-3 text-[12.5px] text-[#3B382F]">{{ number_format($row->product_count) }}</td>
                            <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $row->is_active ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDE8E8] text-[#DC2626]' }}">{{ $row->is_active ? ($isFr ? 'Active' : 'Active') : ($isFr ? 'Inactive' : 'Inactive') }}</span></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ request()->fullUrlWithQuery(['selected' => $row->id]) }}" class="p-1.5 rounded-lg hover:bg-[#E2F3E8] text-[#157A43]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <span class="p-1.5 rounded-lg text-[#B9B4A9]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></span>
                                    <span class="p-1.5 rounded-lg text-[#B9B4A9]" title="{{ $isFr ? 'Plus' : 'More' }}"><i data-lucide="more-vertical" class="w-4 h-4"></i></span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-10 text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucune catégorie trouvée.' : 'No categories found.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($catPagedRows->total() > 0)
            <div class="px-5 py-3.5 border-t border-[#F1F1EF] flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $catPagedRows->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $catPagedRows->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $catPagedRows->total() }} {{ $isFr ? 'catégories' : 'categories' }}</p>
                <div class="flex items-center gap-1">{{ $catPagedRows->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </div>

        {{-- Right rail: selected category detail --}}
        <div class="space-y-5">
            @if($catSelected)
            <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <div class="flex items-center gap-2 text-[12.5px] font-semibold mb-4">
                    <span class="text-[#157A43] border-b-2 border-[#157A43] pb-1">{{ $isFr ? 'Détails' : 'Details' }}</span>
                    <a href="{{ request()->fullUrlWithQuery(['selected' => $catTop->first()->id ?? '']) }}" class="text-[#8A857A] pb-1">{{ $isFr ? 'Arborescence' : 'Tree' }}</a>
                </div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-[52px] h-[52px] rounded-xl bg-[#F5EEDD] flex items-center justify-center shrink-0"><i data-lucide="{{ $catSelected->icon ?? 'shapes' }}" class="w-6 h-6 text-[#8A5A1F]"></i></div>
                    <div class="min-w-0">
                        <p class="text-[15px] font-bold text-[#1B1B18] truncate">{{ $isFr ? $catSelected->name_fr : ($catSelected->name_en ?? $catSelected->name_fr) }}</p>
                        <span class="text-[10.5px] font-semibold px-1.5 py-0.5 rounded-full {{ $catSelected->is_active ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDE8E8] text-[#DC2626]' }}">{{ $catSelected->is_active ? ($isFr ? 'Active' : 'Active') : ($isFr ? 'Inactive' : 'Inactive') }}</span>
                    </div>
                </div>
                <p class="text-[12px] text-[#6F6B60] leading-relaxed mb-4">{{ $catSelected->description_fr }}</p>

                <dl class="space-y-2 text-[12.5px] border-t border-[#F1F1EF] pt-3">
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Catégorie principale' : 'Main category' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $catSelectedParent->name_fr ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Niveau' : 'Level' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $catSelected->parent_id ? ($isFr ? 'Niveau 2' : 'Level 2') : ($isFr ? 'Niveau 1' : 'Level 1') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Sous-catégories' : 'Sub-categories' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $catSelectedSubs->count() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Artisans' : 'Artisans' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ number_format($catSelected->business_count) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Produits' : 'Products' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ number_format($catSelected->product_count) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Date de création' : 'Created' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $fmtDate($catSelected->created_at) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-[#8A857A]">{{ $isFr ? 'Dernière mise à jour' : 'Last updated' }}</dt><dd class="font-semibold text-[#1B1B18]">{{ $fmtDate($catSelected->updated_at) }}</dd></div>
                </dl>

                <span class="mt-4 block w-full text-center border border-[#CFE0D4] text-[#157A43] text-[12.5px] font-semibold py-2 rounded-lg">{{ $isFr ? 'Modifier la catégorie' : 'Edit category' }}</span>
            </div>
            @endif

            <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'RÉPARTITION PAR NIVEAU' : 'BREAKDOWN BY LEVEL' }}</h3>
                <div class="flex items-center gap-4">
                    <span class="w-[80px] h-[80px] rounded-full shrink-0" style="background: {{ $levelConic }}"><span class="block w-[44px] h-[44px] rounded-full bg-white m-[18px]"></span></span>
                    <ul class="space-y-1.5 text-[11.5px]">
                        @foreach($catLevelDist as $lvl => $c)
                        <li class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $levelColors[$lvl] }}"></span>{{ $lvl == 1 ? ($isFr ? 'Niveau 1 (Principales)' : 'Level 1 (Main)') : ($isFr ? 'Niveau 2' : 'Level 2') }}<span class="font-semibold ml-auto">{{ round($c / $levelTotal * 100, 1) }}% ({{ $c }})</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-3">{{ $isFr ? 'ACTIONS RAPIDES' : 'QUICK ACTIONS' }}</h3>
                <div class="space-y-1.5">
                    <a href="#add-category" class="flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-[#F6F7F6] text-[12.5px] text-[#3B382F]"><i data-lucide="plus" class="w-4 h-4 text-[#157A43]"></i>{{ $isFr ? 'Ajouter une catégorie' : 'Add a category' }}</a>
                    <a href="{{ route('admin.exports') }}" class="flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-[#F6F7F6] text-[12.5px] text-[#3B382F]"><i data-lucide="download" class="w-4 h-4 text-[#3565DE]"></i>{{ $isFr ? 'Exporter la structure' : 'Export the structure' }}</a>
                    <a href="{{ route('industries.index', ['lang' => $lang]) }}" target="_blank" class="flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-[#F6F7F6] text-[12.5px] text-[#3B382F]"><i data-lucide="external-link" class="w-4 h-4 text-[#7C4FE0]"></i>{{ $isFr ? 'Voir la page publique' : 'View public page' }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Add-category form (project adaptation: anchored section, PNG's toolbar button links here) --}}
    <div id="add-category" class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl p-5">
        <h2 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-1">{{ $isFr ? 'AJOUTER UNE CATÉGORIE' : 'ADD A CATEGORY' }}</h2>
        <p class="text-[12px] text-[#8A857A] mb-4">{{ $isFr ? 'La création de catégories se gère actuellement via les migrations de contenu — contactez un administrateur système pour ajouter une nouvelle catégorie ou sous-catégorie.' : 'Category creation is currently managed via content migrations — contact a system administrator to add a new category or sub-category.' }}</p>
    </div>
</div>
@endsection
