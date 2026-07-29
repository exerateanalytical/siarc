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
        'salons' => 'bg-[#F0EAFB] dark:bg-[#1E1733] text-[#7C4FE0] dark:text-[#BCA4F0]', 'festivals' => 'bg-[#FDF3E0] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]',
        'ateliers' => 'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5]', 'marches' => 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]',
        'conferences' => 'bg-[#E3F4F6] dark:bg-[#1A1E16] text-[#0E7C86] dark:text-[#868778]', 'concours' => 'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#DC2626] dark:text-[#F0555C]',
    ];
    $typeColors = ['salons' => '#7C4FE0', 'festivals' => '#C97A16', 'ateliers' => '#3565DE', 'marches' => '#157A43', 'conferences' => '#0E7C86', 'concours' => '#DC2626'];
    $now = now();
    $statusFor = function ($e) use ($now, $isFr) {
        if ($e->starts_at > $now) return [$isFr ? 'À venir' : 'Upcoming', 'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5]'];
        if ($e->ends_at < $now) return [$isFr ? 'Terminé' : 'Completed', 'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'];
        return [$isFr ? 'En cours' : 'Ongoing', 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'];
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
            <p class="flex items-center gap-1.5 text-[11.5px] text-[#8A857A] dark:text-[#868778]">
                <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F] dark:hover:text-[#339B56]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Événements & Festivals' : 'Events & Festivals' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="#create-event" class="ui-btn ui-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer un événement' : 'Create an event' }}</a>
            <a href="{{ route('admin.exports') }}" class="ui-btn ui-btn-secondary"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
        </div>
    </div>

    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
        @foreach($evCards as [$ecIcon, $ecColor, $ecTile, $ecValue, $ecLabel])
        <div class="ui-card">
            <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $ecTile }}"><i data-lucide="{{ $ecIcon }}" class="w-[19px] h-[19px]" style="color: {{ $ecColor }};stroke-width:1.8"></i></span>
            <p class="mt-3 text-[20px] font-extrabold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ $ecValue }}</p>
            <p class="mt-1 text-[11.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $ecLabel }}</p>
        </div>
        @endforeach
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">
        {{-- Main: filters + table --}}
        <div class="ui-card ui-card--flush">
            <form method="GET" class="px-5 py-3.5 flex flex-wrap items-center gap-2.5 border-b border-[#EFEBE2] dark:border-[#262B21]">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="ui-field-group flex-1 min-w-[160px]">
                    <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher un événement...' : 'Search an event...' }}" class="ui-field-bare flex-1 min-w-0">
                    <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i></button>
                </div>
                <select name="status" onchange="this.form.submit()" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                    <option value="upcoming" @selected($status==='upcoming')>{{ $isFr ? 'À venir' : 'Upcoming' }}</option>
                    <option value="ongoing" @selected($status==='ongoing')>{{ $isFr ? 'En cours' : 'Ongoing' }}</option>
                    <option value="completed" @selected($status==='completed')>{{ $isFr ? 'Terminé' : 'Completed' }}</option>
                </select>
                <select name="type" onchange="this.form.submit()" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                    @foreach($typeLabels as $val => $label)<option value="{{ $val }}" @selected($type===$val)>{{ $label }}</option>@endforeach
                </select>
                <select name="region" onchange="this.form.submit()" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                    @foreach($evRegionLabels as $key => $label)<option value="{{ $key }}" @selected($region===$key)>{{ $label }}</option>@endforeach
                </select>
            </form>

            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead><tr>
                        <th>{{ $isFr ? 'ÉVÉNEMENT' : 'EVENT' }}</th>
                        <th>{{ $isFr ? 'TYPE' : 'TYPE' }}</th>
                        <th>{{ $isFr ? 'RÉGION' : 'REGION' }}</th>
                        <th>{{ $isFr ? 'DATES' : 'DATES' }}</th>
                        <th>{{ $isFr ? 'STATUT' : 'STATUS' }}</th>
                        <th>{{ $isFr ? 'PARTICIPANTS' : 'PARTICIPANTS' }}</th>
                        <th class="text-right">{{ $isFr ? 'ACTIONS' : 'ACTIONS' }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse($events as $event)
                            @php [$sLabel, $sCls] = $statusFor($event); @endphp
                        <tr class="border-t border-[#EFEBE2] dark:border-[#262B21] hover:bg-[#FAFAF8] dark:hover:bg-[#242A1E]">
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($event->cover_image)<img src="{{ asset('storage/' . $event->cover_image) }}" alt="" class="w-full h-full object-cover">@else<i data-lucide="calendar" class="w-4 h-4 text-[#B9B4A9] dark:text-[#868778]"></i>@endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] truncate max-w-[200px]">{{ $isFr ? $event->name_fr : ($event->name_en ?? $event->name_fr) }}</p>
                                        <p class="text-[11px] text-[#8A857A] dark:text-[#868778] truncate">{{ $event->location_fr ?? $event->city_fr ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $typeCls[$event->event_type] ?? 'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778] ' }}">{{ $typeLabels[$event->event_type] ?? $event->event_type }}</span></td>
                            <td>{{ $evRegionLabels[$event->region_key] ?? ($event->city_fr ?? '—') }}</td>
                            <td>{{ $fmtDate($event->starts_at) }}</td>
                            <td><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $sCls }}">{{ $sLabel }}</span></td>
                            <td>{{ number_format($event->exhibitors_count + $event->attendees_count) }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('events.show', ['slug' => $event->slug]) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-[#E2F3E8] dark:hover:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <span class="p-1.5 rounded-lg text-[#B9B4A9] dark:text-[#868778]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></span>
                                    <form method="POST" action="{{ route('admin.events.destroy', ['id' => $event->id]) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer cet événement ?' : 'Delete this event?' }}')">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-[#FDE8E8] dark:hover:bg-[#3A1013] text-[#DC2626] dark:text-[#F0555C]" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- platform_events is empty; the create form on this screen
                             (AdminWebController::storeEvent) is what fills it. --}}
                        <tr><td colspan="7" class="p-0">
                            @include('pages.partials.empty-state', [
                                'icon'  => 'calendar',
                                'state' => 'empty',
                                'title' => $isFr ? 'Aucun événement enregistré' : 'No events recorded',
                                'body'  => $isFr
                                    ? 'Les événements créés ici (salons, ateliers, expositions) sont annoncés sur les pages publiques. Aucun n\'a encore été créé.'
                                    : 'Events created here (fairs, workshops, exhibitions) are announced on the public pages. None has been created yet.',
                            ])
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->total() > 0)
            <div class="px-5 py-3.5 border-t border-[#EFEBE2] dark:border-[#262B21] flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $events->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $events->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $events->total() }} {{ $isFr ? 'événements' : 'events' }}</p>
                <div class="flex items-center gap-1">{{ $events->onEachSide(1)->links() }}</div>
            </div>
            @endif
        </div>

        {{-- Right rail: calendar + upcoming --}}
        <div class="space-y-5">
            <div class="ui-card">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="ui-card-title">{{ $isFr ? 'CALENDRIER DES ÉVÉNEMENTS' : 'EVENTS CALENDAR' }}</h3>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calMonth->copy()->subMonth()->format('Y-m')]) }}" class="p-1 rounded hover:bg-[#F1F1EF] dark:hover:bg-[#242A1E]"><i data-lucide="chevron-left" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i></a>
                    <span class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $calLabel }}</span>
                    <a href="{{ request()->fullUrlWithQuery(['month' => $calMonth->copy()->addMonth()->format('Y-m')]) }}" class="p-1 rounded hover:bg-[#F1F1EF] dark:hover:bg-[#242A1E]"><i data-lucide="chevron-right" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i></a>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-semibold text-[#8A857A] dark:text-[#868778] mb-1">
                    @foreach(($isFr ? ['L','M','M','J','V','S','D'] : ['M','T','W','T','F','S','S']) as $d)<span>{{ $d }}</span>@endforeach
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[11px]">
                    @for($i = 0; $i < $calLeadEmpty; $i++)<span></span>@endfor
                    @for($d = 1; $d <= $calEnd->day; $d++)
                        @php $hasEvent = $calDays->has((string) $d); $isToday = $now->isSameDay($calMonth->copy()->day($d)); @endphp
                        <span class="h-6 w-6 mx-auto flex items-center justify-center rounded-full {{ $isToday ? 'bg-[#0F4824] dark:bg-[#2E9250] text-white dark:text-[#04150A] font-bold' : ($hasEvent ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] font-semibold' : 'text-[#3B382F] dark:text-[#B4B5A6] ') }}">{{ $d }}</span>
                    @endfor
                </div>
            </div>

            <div class="ui-card">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="ui-card-title">{{ $isFr ? 'PROCHAINS ÉVÉNEMENTS' : 'UPCOMING EVENTS' }}</h3>
                </div>
                <div class="space-y-3">
                    @forelse($evUpcoming as $ue)
                    <a href="{{ route('events.show', ['slug' => $ue->slug]) }}" target="_blank" class="flex items-start gap-2.5 group">
                        <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] flex items-center justify-center shrink-0"><i data-lucide="calendar" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i></div>
                        <div class="min-w-0">
                            <p class="text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] group-hover:text-[#157A43] dark:group-hover:text-[#339B56] truncate">{{ $isFr ? $ue->name_fr : ($ue->name_en ?? $ue->name_fr) }}</p>
                            <p class="text-[11px] text-[#8A857A] dark:text-[#868778] truncate">{{ $fmtDate($ue->starts_at) }} · {{ $evRegionLabels[$ue->region_key] ?? '—' }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-[12px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucun événement à venir.' : 'No upcoming events.' }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="ui-card">
            <h3 class="ui-card-title mb-4">{{ $isFr ? 'RÉPARTITION DES ÉVÉNEMENTS PAR TYPE' : 'EVENTS BY TYPE' }}</h3>
            @if($evByType->isNotEmpty())
            <div class="flex items-center gap-4">
                <span class="w-[84px] h-[84px] rounded-full shrink-0" style="background: {{ $typeConic }}"><span class="block w-[46px] h-[46px] rounded-full bg-white dark:bg-[#12150F] m-[19px]"></span></span>
                <ul class="space-y-1.5 min-w-0 text-[11.5px]">
                    @foreach($evByType as $t => $c)
                    <li class="flex items-center gap-2 text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $typeColors[$t] ?? '#8A857A' }}"></span><span class="flex-1 truncate">{{ $typeLabels[$t] ?? $t }}</span><span class="font-semibold shrink-0">{{ round($c / $typeTotal * 100, 1) }}% ({{ $c }})</span></li>
                    @endforeach
                </ul>
            </div>
            @else<p class="text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endif
        </div>

        <div class="ui-card">
            <h3 class="ui-card-title mb-4">{{ $isFr ? 'TOP RÉGIONS PAR NOMBRE D\'ÉVÉNEMENTS' : 'TOP REGIONS BY EVENT COUNT' }}</h3>
            <div class="space-y-2.5">
                @forelse($evByRegion as $row)
                <div>
                    <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-[#3B382F] dark:text-[#B4B5A6]">{{ $evRegionLabels[$row->region_key] ?? $row->region_key }}</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $row->c }}</span></div>
                    <div class="h-[6px] bg-[#F1F1EF] dark:bg-[#1A1E16] rounded-full overflow-hidden"><div class="h-full bg-[#157A43] dark:bg-[#2E9250] rounded-full" style="width: {{ round($row->c / $regionMax * 100, 1) }}%"></div></div>
                </div>
                @empty<p class="text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endforelse
            </div>
        </div>

        <div class="ui-card">
            <h3 class="ui-card-title mb-4">{{ $isFr ? 'ÉVOLUTION DES PARTICIPANTS' : 'PARTICIPANTS EVOLUTION' }}</h3>
            @if($evSeriesRaw->isNotEmpty())
            <div class="flex items-end gap-2 h-[90px]">
                @foreach($evSeriesRaw->sortKeys() as $month => $val)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <span class="text-[10px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ number_format($val) }}</span>
                    <div class="w-full bg-[#157A43] dark:bg-[#2E9250] rounded-t-md" style="height: {{ max(6, round($val / $maxSeries * 70)) }}px"></div>
                    <span class="text-[9.5px] text-[#8A857A] dark:text-[#868778]">{{ \Illuminate\Support\Carbon::parse($month . '-01')->translatedFormat('M Y') }}</span>
                </div>
                @endforeach
            </div>
            @else<p class="text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>@endif
        </div>
    </div>

    {{-- Quick stats — honest: only real trackable metrics; budget/partners/satisfaction/media coverage aren't tracked yet --}}
    <div class="ui-card mt-5">
        <h3 class="ui-card-title mb-4">{{ $isFr ? 'STATISTIQUES RAPIDES' : 'QUICK STATS' }}</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778] uppercase tracking-wide mb-1">{{ $isFr ? 'Exposants totaux' : 'Total exhibitors' }}</p><p class="text-[18px] font-extrabold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($evKpis['exhibitors']) }}</p></div>
            <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778] uppercase tracking-wide mb-1">{{ $isFr ? 'Inscrits totaux' : 'Total attendees' }}</p><p class="text-[18px] font-extrabold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($evKpis['attendees']) }}</p></div>
            <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778] uppercase tracking-wide mb-1">{{ $isFr ? 'Budget total' : 'Total budget' }}</p><p class="text-[18px] font-extrabold text-[#B9B4A9] dark:text-[#868778]">—</p></div>
            <div><p class="text-[11px] text-[#8A857A] dark:text-[#868778] uppercase tracking-wide mb-1">{{ $isFr ? 'Satisfaction' : 'Satisfaction' }}</p><p class="text-[18px] font-extrabold text-[#B9B4A9] dark:text-[#868778]">—</p></div>
        </div>
    </div>

    {{-- Create-event form (project adaptation: PNG's "Créer un événement" opens a form; kept as an anchored section) --}}
    <div id="create-event" class="ui-card mt-5">
        <h2 class="ui-card-title mb-4">{{ $isFr ? 'CRÉER UN ÉVÉNEMENT' : 'CREATE AN EVENT' }}</h2>
        <form method="POST" action="{{ route('admin.events.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input name="name_fr" required placeholder="{{ $isFr ? 'Nom (français)' : 'Name (French)' }}" class="ui-field">
                <input name="name_en" placeholder="{{ $isFr ? 'Nom (anglais)' : 'Name (English)' }}" class="ui-field">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="ui-label">{{ $isFr ? 'Début' : 'Start' }} *</label><input type="datetime-local" name="starts_at" required class="ui-field w-full"></div>
                <div><label class="ui-label">{{ $isFr ? 'Fin' : 'End' }}</label><input type="datetime-local" name="ends_at" class="ui-field w-full"></div>
            </div>
            <input name="location_fr" placeholder="{{ $isFr ? 'Lieu' : 'Location' }}" class="ui-field w-full">
            <textarea name="description_fr" rows="3" placeholder="{{ $isFr ? 'Description (français)' : 'Description (French)' }}" class="ui-field ui-textarea w-full"></textarea>
            <label class="ui-check-row items-center"><input type="checkbox" name="is_published" value="1" checked class="ui-check">{{ $isFr ? 'Publié (visible publiquement)' : 'Published (publicly visible)' }}</label>
            <button type="submit" class="bg-[#0F4824] dark:bg-[#2E9250] hover:bg-[#14652F] dark:hover:bg-[#2E9250] text-white dark:text-[#04150A] text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer' : 'Create' }}</button>
        </form>
    </div>
</div>
@endsection
