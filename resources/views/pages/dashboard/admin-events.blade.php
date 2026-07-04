@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'events';
    $pageTitle = $isFr ? 'Gestion des Événements & Festivals' : 'Events & Festivals Management';

    $typeLabels = [
        'salons' => $isFr ? 'Salon' : 'Fair', 'festivals' => $isFr ? 'Festival' : 'Festival',
        'ateliers' => $isFr ? 'Atelier' : 'Workshop', 'marches' => $isFr ? 'Marché' : 'Market',
        'conferences' => $isFr ? 'Conférence' : 'Conference', 'concours' => $isFr ? 'Concours' : 'Contest',
    ];
    $typeCls = [
        'salons' => 'bg-[#F0EAFB] text-[#7C4FE0]', 'festivals' => 'bg-[#FDF3E0] text-[#C97A16]',
        'ateliers' => 'bg-[#E8EFFB] text-[#3565DE]', 'marches' => 'bg-[#E2F3E8] text-[#157A43]',
        'conferences' => 'bg-[#E3F4F6] text-[#0E7C86]', 'concours' => 'bg-[#FDE8E8] text-[#DC2626]',
    ];
    $typeColors = ['salons' => '#7C4FE0', 'festivals' => '#C97A16', 'ateliers' => '#3565DE', 'marches' => '#157A43', 'conferences' => '#0E7C86', 'concours' => '#DC2626'];
    $now = now();
    $statusFor = function ($e) use ($now, $isFr) {
        if ($e->starts_at > $now) return [$isFr ? 'À venir' : 'Upcoming', 'bg-[#E8EFFB] text-[#3565DE]'];
        if ($e->ends_at < $now) return [$isFr ? 'Terminé' : 'Completed', 'bg-[#EEECE6] text-[#6F6B60]'];
        return [$isFr ? 'En cours' : 'Ongoing', 'bg-[#E2F3E8] text-[#157A43]'];
    };
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $fmtDate = fn ($d) => $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');

    // 6 KPI cards [icon, color, tile, value, label]
    $evCards = [
        ['calendar-days', '#157A43', '#E2F3E8', $evKpis['total'], $isFr ? 'Événements Totaux' : 'Total Events'],
        ['calendar-clock', '#3565DE', '#E8EFFB', $evKpis['upcoming'], $isFr ? 'À Venir' : 'Upcoming'],
        ['calendar-check', '#C97A16', '#FDF3E0', $evKpis['ongoing'], $isFr ? 'En Cours' : 'Ongoing'],
        ['check-circle-2', '#7C4FE0', '#F0EAFB', $evKpis['completed'], $isFr ? 'Terminés' : 'Completed'],
        ['users-round', '#0E7C86', '#E3F4F6', number_format($evKpis['participants']), $isFr ? 'Participants Totaux' : 'Total Participants'],
        ['globe', '#8A5A1F', '#F5EEDD', '—', $isFr ? 'Visites en Ligne' : 'Online Visits'],
    ];

    $typeTotal = max(1, $evByType->sum());
    $deg = 0; $typeStops = [];
    foreach ($evByType as $t => $c) { $pct = $c / $typeTotal * 100; $start = $deg; $deg += $pct / 100 * 360; $typeStops[] = ($typeColors[$t] ?? '#8A857A') . " {$start}deg {$deg}deg"; }
    $typeConic = 'conic-gradient(' . implode(', ', $typeStops) . ')';
    $regionMax = max(1, (int) $evByRegion->max('c'));

    // Calendar grid
    $calStart = $calMonth->copy()->startOfMonth();
    $calEnd = $calMonth->copy()->endOfMonth();
    $calLeadEmpty = ($calStart->dayOfWeekIso - 1); // Mon=1..Sun=7
    $calMonthLabelFr = ['January'=>'Janvier','February'=>'Février','March'=>'Mars','April'=>'Avril','May'=>'Mai','June'=>'Juin','July'=>'Juillet','August'=>'Août','September'=>'Septembre','October'=>'Octobre','November'=>'Novembre','December'=>'Décembre'];
    $calLabel = $isFr ? ($calMonthLabelFr[$calMonth->format('F')] ?? $calMonth->format('F')) . ' ' . $calMonth->year : $calMonth->format('F Y');

    $maxSeries = max(1, ...($evSeriesRaw->isEmpty() ? [1] : $evSeriesRaw->values()->all()));
@endphp

@section('content')
<div class="max-w-[1400px]">

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <p class="flex items-center gap-1.5 text-[11.5px] text-[#8A857A]">
                <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-[#55524A]">{{ $isFr ? 'Événements & Festivals' : 'Events & Festivals' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="#create-event" class="inline-flex items-center gap-1.5 bg-[#0F4824] hover:bg-[#14652F] text-white text-[12.5px] font-semibold px-3.5 py-2 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer un événement' : 'Create an event' }}</a>
            <a href="{{ route('admin.exports') }}" class="inline-flex items-center gap-1.5 bg-white border border-[#E7E7E5] hover:border-[#14652F] text-[#3B382F] text-[12.5px] font-semibold px-3.5 py-2 rounded-lg"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-[#E2F3E8] border border-[#CFE0D4] text-[#157A43] text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2"><i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}</div>
    @endif

    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
        @foreach($evCards as [$ecIcon, $ecColor, $ecTile, $ecValue, $ecLabel])
        <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
            <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $ecTile }}"><i data-lucide="{{ $ecIcon }}" class="w-[19px] h-[19px]" style="color: {{ $ecColor }};stroke-width:1.8"></i></span>
            <p class="mt-3 text-[20px] font-extrabold text-[#1B1B18] leading-none">{{ $ecValue }}</p>
            <p class="mt-1 text-[11.5px] font-semibold text-[#3B382F]">{{ $ecLabel }}</p>
        </div>
        @endforeach
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">
        {{-- Main: filters + table --}}
        <div class="bg-white border border-[#EFF0EF] rounded-2xl overflow-hidden">
            <form method="GET" class="px-5 py-3.5 flex flex-wrap items-center gap-2.5 border-b border-[#F1F1EF]">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2 bg-[#F8F8F6] border border-[#E7E7E5] rounded-lg px-3 h-[38px] flex-1 min-w-[160px]">
                    <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher un événement...' : 'Search an event...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none">
                    <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
                </div>
                <select name="status" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                    <option value="">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                    <option value="upcoming" @selected($status==='upcoming')>{{ $isFr ? 'À venir' : 'Upcoming' }}</option>
                    <option value="ongoing" @selected($status==='ongoing')>{{ $isFr ? 'En cours' : 'Ongoing' }}</option>
                    <option value="completed" @selected($status==='completed')>{{ $isFr ? 'Terminé' : 'Completed' }}</option>
                </select>
                <select name="type" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                    <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                    @foreach($typeLabels as $val => $label)<option value="{{ $val }}" @selected($type===$val)>{{ $label }}</option>@endforeach
                </select>
                <select name="region" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                    <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                    @foreach($evRegionLabels as $key => $label)<option value="{{ $key }}" @selected($region===$key)>{{ $label }}</option>@endforeach
                </select>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead><tr class="text-[10.5px] font-bold text-[#8A857A]">
                        <th class="px-5 py-2.5">{{ $isFr ? 'ÉVÉNEMENT' : 'EVENT' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'TYPE' : 'TYPE' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'RÉGION' : 'REGION' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'DATES' : 'DATES' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'STATUT' : 'STATUS' }}</th>
                        <th class="px-3 py-2.5">{{ $isFr ? 'PARTICIPANTS' : 'PARTICIPANTS' }}</th>
                        <th class="px-5 py-2.5 text-right">{{ $isFr ? 'ACTIONS' : 'ACTIONS' }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($events as $event)
                            @php [$sLabel, $sCls] = $statusFor($event); @endphp
                        <tr class="border-t border-[#F1F1EF] hover:bg-[#FAFAF8]">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] border border-[#EFEFED] flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($event->cover_image)<img src="{{ asset('storage/' . $event->cover_image) }}" alt="" class="w-full h-full object-cover">@else<i data-lucide="calendar" class="w-4 h-4 text-[#B9B4A9]"></i>@endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-[#1B1B18] truncate max-w-[200px]">{{ $isFr ? $event->name_fr : ($event->name_en ?? $event->name_fr) }}</p>
                                        <p class="text-[11px] text-[#8A857A] truncate">{{ $event->location_fr ?? $event->city_fr ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $typeCls[$event->event_type] ?? 'bg-[#EEECE6] text-[#6F6B60]' }}">{{ $typeLabels[$event->event_type] ?? $event->event_type }}</span></td>
                            <td class="px-3 py-3 text-[12px] text-[#3B382F]">{{ $evRegionLabels[$event->region_key] ?? ($event->city_fr ?? '—') }}</td>
                            <td class="px-3 py-3 text-[12px] text-[#6F6B60]">{{ $fmtDate($event->starts_at) }}</td>
                            <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $sCls }}">{{ $sLabel }}</span></td>
                            <td class="px-3 py-3 text-[12px] text-[#3B382F]">{{ number_format($event->exhibitors_count + $event->attendees_count) }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('events.show', ['slug' => $event->slug]) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-[#E2F3E8] text-[#157A43]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <span class="p-1.5 rounded-lg text-[#B9B4A9]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></span>
                                    <form method="POST" action="{{ route('admin.events.destroy', ['id' => $event->id]) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer cet événement ?' : 'Delete this event?' }}')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-[#FDE8E8] text-[#DC2626]" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-10 text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucun événement trouvé.' : 'No events found.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->total() > 0)
            <div class="px-5 py-3.5 border-t border-[#F1F1EF] flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $events->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $events->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $events->total() }} {{ $isFr ? 'événements' : 'events' }}</p>
                <div class="flex items-center gap-1">{{ $events->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </div>

        {{-- Right rail: calendar + upcoming --}}
        <div class="space-y-5">
            <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'CALENDRIER DES ÉVÉNEMENTS' : 'EVENTS CALENDAR' }}</h3>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calMonth->copy()->subMonth()->format('Y-m')]) }}" class="p-1 rounded hover:bg-[#F1F1EF]"><i data-lucide="chevron-left" class="w-4 h-4 text-[#8A857A]"></i></a>
                    <span class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $calLabel }}</span>
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calMonth->copy()->addMonth()->format('Y-m')]) }}" class="p-1 rounded hover:bg-[#F1F1EF]"><i data-lucide="chevron-right" class="w-4 h-4 text-[#8A857A]"></i></a>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-semibold text-[#8A857A] mb-1">
                    @foreach(($isFr ? ['L','M','M','J','V','S','D'] : ['M','T','W','T','F','S','S']) as $d)<span>{{ $d }}</span>@endforeach
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[11px]">
                    @for($i = 0; $i < $calLeadEmpty; $i++)<span></span>@endfor
                    @for($d = 1; $d <= $calEnd->day; $d++)
                        @php $hasEvent = $calDays->has((string) $d); $isToday = $now->isSameDay($calMonth->copy()->day($d)); @endphp
                        <span class="h-6 w-6 mx-auto flex items-center justify-center rounded-full {{ $isToday ? 'bg-[#0F4824] text-white font-bold' : ($hasEvent ? 'bg-[#E2F3E8] text-[#157A43] font-semibold' : 'text-[#3B382F]') }}">{{ $d }}</span>
                    @endfor
                </div>
            </div>

            <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'PROCHAINS ÉVÉNEMENTS' : 'UPCOMING EVENTS' }}</h3>
                </div>
                <div class="space-y-3">
                    @forelse($evUpcoming as $ue)
                    <a href="{{ route('events.show', ['slug' => $ue->slug]) }}" target="_blank" class="flex items-start gap-2.5 group">
                        <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] border border-[#EFEFED] flex items-center justify-center shrink-0"><i data-lucide="calendar" class="w-4 h-4 text-[#8A857A]"></i></div>
                        <div class="min-w-0">
                            <p class="text-[12.5px] font-semibold text-[#1B1B18] group-hover:text-[#157A43] truncate">{{ $isFr ? $ue->name_fr : ($ue->name_en ?? $ue->name_fr) }}</p>
                            <p class="text-[11px] text-[#8A857A] truncate">{{ $fmtDate($ue->starts_at) }} · {{ $evRegionLabels[$ue->region_key] ?? '—' }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-[12px] text-[#B9B4A9]">{{ $isFr ? 'Aucun événement à venir.' : 'No upcoming events.' }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
            <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'RÉPARTITION DES ÉVÉNEMENTS PAR TYPE' : 'EVENTS BY TYPE' }}</h3>
            @if($evByType->isNotEmpty())
            <div class="flex items-center gap-4">
                <span class="w-[84px] h-[84px] rounded-full shrink-0" style="background: {{ $typeConic }}"><span class="block w-[46px] h-[46px] rounded-full bg-white m-[19px]"></span></span>
                <ul class="space-y-1.5 min-w-0 text-[11.5px]">
                    @foreach($evByType as $t => $c)
                    <li class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $typeColors[$t] ?? '#8A857A' }}"></span><span class="flex-1 truncate">{{ $typeLabels[$t] ?? $t }}</span><span class="font-semibold shrink-0">{{ round($c / $typeTotal * 100, 1) }}% ({{ $c }})</span></li>
                    @endforeach
                </ul>
            </div>
            @else<p class="text-[12.5px] text-[#B9B4A9]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endif
        </div>

        <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
            <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'TOP RÉGIONS PAR NOMBRE D\'ÉVÉNEMENTS' : 'TOP REGIONS BY EVENT COUNT' }}</h3>
            <div class="space-y-2.5">
                @forelse($evByRegion as $row)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-[#3B382F]">{{ $evRegionLabels[$row->region_key] ?? $row->region_key }}</span><span class="font-semibold text-[#1B1B18]">{{ $row->c }}</span></div>
                    <div class="h-[6px] bg-[#F1F1EF] rounded-full overflow-hidden"><div class="h-full bg-[#157A43] rounded-full" style="width: {{ round($row->c / $regionMax * 100, 1) }}%"></div></div>
                </div>
                @empty<p class="text-[12.5px] text-[#B9B4A9]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endforelse
            </div>
        </div>

        <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
            <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'ÉVOLUTION DES PARTICIPANTS' : 'PARTICIPANTS EVOLUTION' }}</h3>
            @if($evSeriesRaw->isNotEmpty())
            <div class="flex items-end gap-2 h-[90px]">
                @foreach($evSeriesRaw->sortKeys() as $month => $val)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-[10px] font-semibold text-[#3B382F]">{{ number_format($val) }}</span>
                    <div class="w-full bg-[#157A43] rounded-t-md" style="height: {{ max(6, round($val / $maxSeries * 70)) }}px"></div>
                    <span class="text-[9.5px] text-[#8A857A]">{{ \Illuminate\Support\Carbon::parse($month . '-01')->translatedFormat('M Y') }}</span>
                </div>
                @endforeach
            </div>
            @else<p class="text-[12.5px] text-[#B9B4A9]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endif
        </div>
    </div>

    {{-- Quick stats — honest: only real trackable metrics; budget/partners/satisfaction/media coverage aren't tracked yet --}}
    <div class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl p-5">
        <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'STATISTIQUES RAPIDES' : 'QUICK STATS' }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-[11px] text-[#8A857A] uppercase tracking-wide mb-1">{{ $isFr ? 'Exposants totaux' : 'Total exhibitors' }}</p><p class="text-[18px] font-extrabold text-[#1B1B18]">{{ number_format($evKpis['exhibitors']) }}</p></div>
            <div><p class="text-[11px] text-[#8A857A] uppercase tracking-wide mb-1">{{ $isFr ? 'Inscrits totaux' : 'Total attendees' }}</p><p class="text-[18px] font-extrabold text-[#1B1B18]">{{ number_format($evKpis['attendees']) }}</p></div>
            <div><p class="text-[11px] text-[#8A857A] uppercase tracking-wide mb-1">{{ $isFr ? 'Budget total' : 'Total budget' }}</p><p class="text-[18px] font-extrabold text-[#B9B4A9]">—</p></div>
            <div><p class="text-[11px] text-[#8A857A] uppercase tracking-wide mb-1">{{ $isFr ? 'Satisfaction' : 'Satisfaction' }}</p><p class="text-[18px] font-extrabold text-[#B9B4A9]">—</p></div>
        </div>
    </div>

    {{-- Create-event form (project adaptation: PNG's "Créer un événement" opens a form; kept as an anchored section) --}}
    <div id="create-event" class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl p-5">
        <h2 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'CRÉER UN ÉVÉNEMENT' : 'CREATE AN EVENT' }}</h2>
        <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input name="name_fr" required placeholder="{{ $isFr ? 'Nom (français)' : 'Name (French)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
                <input name="name_en" placeholder="{{ $isFr ? 'Nom (anglais)' : 'Name (English)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">{{ $isFr ? 'Début' : 'Start' }} *</label><input type="datetime-local" name="starts_at" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">{{ $isFr ? 'Fin' : 'End' }}</label><input type="datetime-local" name="ends_at" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]"></div>
            </div>
            <input name="location_fr" placeholder="{{ $isFr ? 'Lieu' : 'Location' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
            <textarea name="description_fr" rows="3" placeholder="{{ $isFr ? 'Description (français)' : 'Description (French)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F] resize-none"></textarea>
            <label class="flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="is_published" value="1" checked class="rounded border-gray-300 text-[#14652F]">{{ $isFr ? 'Publié (visible publiquement)' : 'Published (publicly visible)' }}</label>
            <button type="submit" class="bg-[#0F4824] hover:bg-[#14652F] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer' : 'Create' }}</button>
        </form>
    </div>
</div>
@endsection
