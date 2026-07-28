@extends('layouts.dashboard')

@php
$pageTitle = ($lang === 'fr' ? 'Représentant régional' : 'Regional Representative') . (($region ?? null) ? ' — ' . ($lang === 'fr' ? $region->name_fr : $region->name_en) : '');
@endphp

@section('content')
<div class="max-w-5xl">

    @if(! ($region ?? null))
    <div class="bg-amber-50 dark:bg-[#3A2B06] border border-amber-200 dark:border-[#4A3A12] text-amber-700 dark:text-[#EDB33A] text-sm rounded-xl p-5 flex items-start gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
        {{ $lang === 'fr'
            ? 'Aucune région ne vous a été assignée. Contactez un administrateur pour associer votre compte à une région.'
            : 'No region has been assigned to you. Contact an administrator to link your account to a region.' }}
    </div>
    @else

    <div class="bg-indigo-50 dark:bg-[#101C33] border border-indigo-100 dark:border-[#20304F] text-indigo-700 dark:text-[#8FB6F5] text-xs rounded-lg p-3 mb-5 flex items-start gap-2">
        <i data-lucide="eye" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
        {{ $lang === 'fr'
            ? 'Tableau de bord en lecture seule — vous pouvez consulter les données de votre région mais ne pouvez pas les modifier.'
            : 'Read-only dashboard — you can view your region\'s data but cannot modify it.' }}
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4 text-center">
            <p class="text-2xl font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $stats['total'] }}</p>
            <p class="text-xs text-[#8A857A] dark:text-[#868778] mt-1 flex items-center justify-center gap-1"><i data-lucide="building-2" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</p>
        </div>
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4 text-center">
            <p class="text-2xl font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $stats['products'] }}</p>
            <p class="text-xs text-[#8A857A] dark:text-[#868778] mt-1 flex items-center justify-center gap-1"><i data-lucide="package" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Produits' : 'Products' }}</p>
        </div>
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4 text-center">
            <p class="text-2xl font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $stats['certified'] + $stats['verified'] }}</p>
            <p class="text-xs text-[#8A857A] dark:text-[#868778] mt-1 flex items-center justify-center gap-1"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Vérifiées/Certifiées' : 'Verified/Certified' }}</p>
        </div>
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4 text-center">
            <p class="text-2xl font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($stats['views']) }}</p>
            <p class="text-xs text-[#8A857A] dark:text-[#868778] mt-1 flex items-center justify-center gap-1"><i data-lucide="eye" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Vues cumulées' : 'Total views' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        <!-- Status breakdown -->
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4">
            <h2 class="text-sm font-semibold text-[#1B1B18] dark:text-[#F3EFE7] mb-3">{{ $lang === 'fr' ? 'Statut' : 'Status' }}</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-[#8A857A] dark:text-[#868778]">{{ $lang === 'fr' ? 'Publiées' : 'Published' }}</span><span class="font-semibold text-green-600 dark:text-[#339B56]">{{ $stats['published'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#8A857A] dark:text-[#868778]">{{ $lang === 'fr' ? 'Brouillons' : 'Draft' }}</span><span class="font-semibold text-[#8A857A] dark:text-[#868778]">{{ $stats['draft'] }}</span></div>
                <div class="flex justify-between"><span class="text-[#8A857A] dark:text-[#868778]">{{ $lang === 'fr' ? 'Suspendues' : 'Suspended' }}</span><span class="font-semibold text-red-500 dark:text-[#F0555C]">{{ $stats['suspended'] }}</span></div>
            </div>
        </div>

        <!-- By industry -->
        <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] p-4 lg:col-span-2">
            <h2 class="text-sm font-semibold text-[#1B1B18] dark:text-[#F3EFE7] mb-3">{{ $lang === 'fr' ? 'Par secteur' : 'By industry' }}</h2>
            @if($businessesByIndustry->isNotEmpty())
            <div class="space-y-2">
                @foreach($businessesByIndustry as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-[#6F6B60] dark:text-[#868778]">{{ $lang === 'fr' ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 bg-[#F1EDE4] dark:bg-[#1A1E16] rounded-full overflow-hidden">
                            <div class="h-full bg-forest-500 dark:bg-[#2E9250]" style="width: {{ $stats['total'] ? min(100, round($row->total / $stats['total'] * 100)) : 0 }}%"></div>
                        </div>
                        <span class="font-semibold text-[#262521] dark:text-[#F3EFE7] w-6 text-right">{{ $row->total }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-[#A8A296] dark:text-[#868778]">{{ $lang === 'fr' ? 'Aucune donnée.' : 'No data.' }}</p>
            @endif
        </div>
    </div>

    <!-- Business list -->
    <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] overflow-hidden">
        <div class="px-4 py-3 border-b border-[#F1EDE4] dark:border-[#262B21]">
            <h2 class="text-sm font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $lang === 'fr' ? 'Entreprises de la région' : 'Businesses in region' }}</h2>
        </div>
        @forelse($businesses as $business)
        <div class="flex items-center gap-3 px-4 py-3 border-b border-[#FBF9F4] dark:border-[#262B21] last:border-0">
            <div class="w-9 h-9 rounded-lg bg-[#FBF9F4] dark:bg-[#12150F] flex items-center justify-center shrink-0">
                <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-4 h-4 text-[#A8A296] dark:text-[#868778]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $business->name_fr }}</p>
                <p class="text-xs text-[#A8A296] dark:text-[#868778]">{{ $lang === 'fr' ? $business->industry->name_fr : ($business->industry->name_en ?? '') }} · {{ $business->city->name_fr ?? '' }}</p>
            </div>
            <span @class(['px-2 py-0.5 rounded-full text-xs font-medium shrink-0', 'bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#339B56]' => $business->status === 'published', 'bg-[#F1EDE4] dark:bg-[#1A1E16] text-[#8A857A] dark:text-[#868778]' => $business->status !== 'published'])>
                {{ ucfirst($business->status) }}
            </span>
        </div>
        @empty
        <div class="text-center py-10 text-sm text-[#A8A296] dark:text-[#868778]">{{ $lang === 'fr' ? 'Aucune entreprise dans cette région.' : 'No businesses in this region.' }}</div>
        @endforelse
    </div>
    @if($businesses->hasPages())
    <div class="mt-4">{{ $businesses->links() }}</div>
    @endif

    <!-- Events participation -->
    <div class="bg-white dark:bg-[#12150F] rounded-xl border border-[#EFEBE2] dark:border-[#262B21] overflow-hidden mt-4">
        <div class="px-4 py-3 border-b border-[#F1EDE4] dark:border-[#262B21] flex items-center gap-2">
            <i data-lucide="calendar-days" class="w-4 h-4 text-indigo-500 dark:text-[#8FB6F5]"></i>
            <h2 class="text-sm font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $lang === 'fr' ? 'Participation aux événements' : 'Event participation' }}</h2>
        </div>
        @forelse($events as $event)
        <div class="px-4 py-3 border-b border-[#FBF9F4] dark:border-[#262B21] last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-[#101C33] flex items-center justify-center shrink-0">
                    <i data-lucide="calendar" class="w-4 h-4 text-indigo-600 dark:text-[#8FB6F5]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $lang === 'fr' ? $event->name_fr : $event->name_en }}</p>
                    <p class="text-xs text-[#A8A296] dark:text-[#868778]">{{ $event->starts_at?->format('d/m/Y') }}</p>
                </div>
                <span class="text-xs text-[#8A857A] dark:text-[#868778] flex items-center gap-1 shrink-0"><i data-lucide="users" class="w-3.5 h-3.5"></i>{{ $event->attendees_count }}</span>
            </div>
            @if($event->exhibitingBusinesses->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mt-2 ml-12">
                @foreach($event->exhibitingBusinesses as $exhibitor)
                <span class="text-xs bg-[#FBF9F4] dark:bg-[#12150F] text-[#6F6B60] dark:text-[#868778] px-2 py-1 rounded-full">{{ $exhibitor->name_fr }}</span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-8 text-sm text-[#A8A296] dark:text-[#868778]">{{ $lang === 'fr' ? 'Aucune entreprise de la région n\'a participé à un événement.' : 'No businesses from this region have participated in an event.' }}</div>
        @endforelse
    </div>

    @endif
</div>
@endsection
