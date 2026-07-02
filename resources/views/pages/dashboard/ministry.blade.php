@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Tableau de bord ministériel' : 'Ministry Dashboard';
$maxGrowth = $growth->max('total') ?: 1;
$monthLabels = [
    '01' => ['fr' => 'Jan', 'en' => 'Jan'], '02' => ['fr' => 'Fév', 'en' => 'Feb'], '03' => ['fr' => 'Mar', 'en' => 'Mar'],
    '04' => ['fr' => 'Avr', 'en' => 'Apr'], '05' => ['fr' => 'Mai', 'en' => 'May'], '06' => ['fr' => 'Juin', 'en' => 'Jun'],
    '07' => ['fr' => 'Juil', 'en' => 'Jul'], '08' => ['fr' => 'Août', 'en' => 'Aug'], '09' => ['fr' => 'Sep', 'en' => 'Sep'],
    '10' => ['fr' => 'Oct', 'en' => 'Oct'], '11' => ['fr' => 'Nov', 'en' => 'Nov'], '12' => ['fr' => 'Déc', 'en' => 'Dec'],
];
@endphp

@section('content')
<div class="max-w-5xl">

    <div class="bg-forest-50 border border-forest-100 text-forest-700 text-xs rounded-lg p-3 mb-5 flex items-start gap-2">
        <i data-lucide="eye" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
        {{ $lang === 'fr'
            ? 'Vue nationale en lecture seule — agrégation de toutes les régions et secteurs à des fins de politique publique.'
            : 'Read-only national view — aggregated across all regions and sectors for policy purposes.' }}
    </div>

    <!-- National stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['businesses'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['products'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['regions'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Régions' : 'Regions' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['industries'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Secteurs' : 'Industries' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['certified'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Certifiées' : 'Certified' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['verified'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Vérifiées' : 'Verified' }}</p>
        </div>
    </div>

    <!-- Growth chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">{{ $lang === 'fr' ? 'Croissance — nouvelles entreprises (6 derniers mois)' : 'Growth — new businesses (last 6 months)' }}</h2>
        <div class="flex items-end gap-3 h-32">
            @forelse($growth as $point)
                @php $m = substr($point->month, 5, 2); @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5">
                    <span class="text-xs font-semibold text-gray-700">{{ $point->total }}</span>
                    <div class="w-full bg-forest-500 rounded-t-md" style="height: {{ max(6, round($point->total / $maxGrowth * 96)) }}px"></div>
                    <span class="text-[10px] text-gray-400">{{ $monthLabels[$m][$lang] ?? $m }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune donnée.' : 'No data.' }}</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- By region -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Par région' : 'By region' }}</h2>
            <div class="space-y-2">
                @foreach($byRegion as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $lang === 'fr' ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-forest-500" style="width: {{ $stats['businesses'] ? min(100, round($row->total / $stats['businesses'] * 100)) : 0 }}%"></div>
                        </div>
                        <span class="font-semibold text-gray-800 w-6 text-right">{{ $row->total }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- By industry -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Par secteur' : 'By industry' }}</h2>
            <div class="space-y-2">
                @foreach($byIndustry as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $lang === 'fr' ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-500" style="width: {{ $stats['businesses'] ? min(100, round($row->total / $stats['businesses'] * 100)) : 0 }}%"></div>
                        </div>
                        <span class="font-semibold text-gray-800 w-6 text-right">{{ $row->total }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Events / SIAC participation -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <i data-lucide="calendar-days" class="w-4 h-4 text-violet-500"></i>
            <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Événements & participation SIAC' : 'Events & SIAC participation' }}</h2>
        </div>
        @forelse($events as $event)
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-4 h-4 text-violet-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $lang === 'fr' ? $event->name_fr : $event->name_en }}</p>
                <p class="text-xs text-gray-400">{{ $event->starts_at?->format('d/m/Y') }} — {{ $lang === 'fr' ? $event->location_fr : $event->location_en }}</p>
            </div>
            <div class="flex items-center gap-4 shrink-0 text-xs text-gray-500">
                <span class="flex items-center gap-1"><i data-lucide="store" class="w-3.5 h-3.5"></i>{{ $event->exhibitors_count }}</span>
                <span class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5"></i>{{ $event->attendees_count }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun événement enregistré.' : 'No events recorded.' }}</div>
        @endforelse
    </div>
</div>
@endsection
