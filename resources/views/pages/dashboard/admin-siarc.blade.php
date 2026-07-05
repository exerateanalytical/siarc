@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'siarc';
    $pageTitle = $isFr ? 'Gestion SIARC 2026' : 'SIARC 2026 management';
    $pageSubtitle = $isFr ? 'Salon International de l\'Artisanat du Cameroun — exposants, programme, visiteurs et rendez-vous B2B.' : 'International Craft Fair of Cameroon — exhibitors, programme, visitors and B2B meetings.';
@endphp

@section('content')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.events') }}" class="shrink-0 inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    {{ $isFr ? 'Gérer les événements' : 'Manage events' }}
                </a>
            </div>

            @if($siarcEvent)
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl p-5 flex flex-wrap items-center gap-5">
                <img src="{{ asset('images/landing/ad-siarc.png') }}" alt="" class="w-[86px] h-[110px] rounded-xl object-cover shrink-0" aria-hidden="true">
                <div class="flex-1 min-w-[240px]">
                    <h2 class="text-[16px] font-bold text-[#8A6D1F]">{{ $isFr ? $siarcEvent->name_fr : ($siarcEvent->name_en ?? $siarcEvent->name_fr) }}</h2>
                    <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="calendar-days" class="w-3.5 h-3.5 text-[#C97A16]"></i> {{ \Illuminate\Support\Carbon::parse($siarcEvent->starts_at)->format('d/m/Y') }} — {{ \Illuminate\Support\Carbon::parse($siarcEvent->ends_at)->format('d/m/Y') }}</p>
                    @if($siarcEvent->location_fr)
                    <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#C97A16]"></i> {{ $siarcEvent->location_fr }}</p>
                    @endif
                </div>
                <a href="{{ route('events.show', ['slug' => $siarcEvent->slug, 'lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2 border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 py-2.5 text-[12.5px] font-semibold text-[#14652F] transition-colors">
                    {{ $isFr ? 'Voir la page publique' : 'View the public page' }}
                </a>
            </section>
            @else
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl px-5 py-8 text-center">
                <i data-lucide="calendar-x" class="w-8 h-8 text-[#D9D4C8] mx-auto mb-2"></i>
                <p class="text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucun événement SIARC trouvé. Créez-le depuis la gestion des événements.' : 'No SIARC event found. Create it from event management.' }}</p>
            </section>
            @endif

            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <h2 class="px-5 pt-4 pb-2 text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Exposants & Pavillons' : 'Exhibitors & Pavilions' }}</h2>
                <table class="w-full min-w-[560px]">
                    <thead>
                        <tr class="bg-[#F8F4EC] text-left">
                            <th class="pl-5 pr-2 py-2.5 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Exposant' : 'Exhibitor' }}</th>
                            <th class="px-2 py-2.5 text-center text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                            <th class="px-2 pr-5 py-2.5 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F5F1E8]">
                        @forelse($siarcExhibitors as $ex)
                        <tr>
                            <td class="pl-5 pr-2 py-2.5 text-[12.5px] font-bold text-[#1B1B18]">{{ $ex->business_name ?? '—' }}</td>
                            <td class="px-2 py-2.5 text-center">
                                <span class="inline-block rounded-md px-2.5 py-0.5 text-[10.5px] font-semibold {{ ($ex->status ?? '') === 'approved' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDF0DC] text-[#C97A16]' }}">{{ $ex->status ?? '—' }}</span>
                            </td>
                            <td class="px-2 pr-5 py-2.5 text-right">
                                @if($ex->business_slug)
                                <a href="{{ route('businesses.show', ['slug' => $ex->business_slug, 'lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir' : 'View' }}</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun exposant inscrit pour le moment.' : 'No exhibitors registered yet.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach([
                    ['programme',    'calendar-days', $isFr ? 'Programme & Activités' : 'Programme & Activities', $isFr ? 'Le programme détaillé se gère sur la fiche de l\'événement.' : 'The detailed programme is managed on the event page.'],
                    ['visiteurs',    'ticket',        $isFr ? 'Inscriptions Visiteurs' : 'Visitor Registrations', $isFr ? 'Les billets visiteurs sont émis depuis la page publique de l\'événement.' : 'Visitor tickets are issued from the public event page.'],
                    ['b2b',          'handshake',     $isFr ? 'Rendez-vous B2B' : 'B2B Meetings',                 $isFr ? 'Les mises en relation passent par la messagerie de la plateforme.' : 'Matchmaking runs through the platform messaging.'],
                    ['intervenants', 'mic',           $isFr ? 'Intervenants & Conférenciers' : 'Speakers & Lecturers', $isFr ? 'Les intervenants sont annoncés sur la fiche de l\'événement.' : 'Speakers are announced on the event page.'],
                ] as [$scId, $scIcon, $scTitle, $scDesc])
                <section id="{{ $scId }}" class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                    <span class="w-[38px] h-[38px] rounded-lg bg-[#F1EADB] flex items-center justify-center">
                        <i data-lucide="{{ $scIcon }}" class="w-[18px] h-[18px] text-[#8A6D1F]" style="stroke-width:1.7"></i>
                    </span>
                    <h3 class="mt-3 text-[13px] font-bold text-[#1B1B18]">{{ $scTitle }}</h3>
                    <p class="mt-1.5 text-[11.5px] text-[#6F6B60] leading-relaxed">{{ $scDesc }}</p>
                    <a href="{{ $siarcEvent ? route('events.show', ['slug' => $siarcEvent->slug, 'lang' => $lang]) : route('admin.events') }}" class="mt-2.5 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43] hover:text-[#14532D]">
                        {{ $isFr ? 'Ouvrir' : 'Open' }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </section>
                @endforeach
            </div>
@endsection
