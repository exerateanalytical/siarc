@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Rapports & Statistiques' : 'Reports & Statistics';
$maxGrowth = $registrationsOverTime->max('total') ?: 1;
$monthLabels = [
    '01' => ['fr' => 'Jan', 'en' => 'Jan'], '02' => ['fr' => 'Fév', 'en' => 'Feb'], '03' => ['fr' => 'Mar', 'en' => 'Mar'],
    '04' => ['fr' => 'Avr', 'en' => 'Apr'], '05' => ['fr' => 'Mai', 'en' => 'May'], '06' => ['fr' => 'Juin', 'en' => 'Jun'],
    '07' => ['fr' => 'Juil', 'en' => 'Jul'], '08' => ['fr' => 'Août', 'en' => 'Aug'], '09' => ['fr' => 'Sep', 'en' => 'Sep'],
    '10' => ['fr' => 'Oct', 'en' => 'Oct'], '11' => ['fr' => 'Nov', 'en' => 'Nov'], '12' => ['fr' => 'Déc', 'en' => 'Dec'],
];
$funnelLabels = [
    'submitted'    => $lang === 'fr' ? 'Soumises' : 'Submitted',
    'under_review' => $lang === 'fr' ? 'En cours' : 'Under review',
    'approved'     => $lang === 'fr' ? 'Approuvées' : 'Approved',
    'rejected'     => $lang === 'fr' ? 'Rejetées' : 'Rejected',
];
@endphp

@section('content')
<div class="max-w-5xl mx-auto">

    <!-- Core stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['published'] }}<span class="text-sm text-gray-400">/{{ $stats['businesses'] }}</span></p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Entreprises publiées' : 'Published businesses' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['published_products'] }}<span class="text-sm text-gray-400">/{{ $stats['products'] }}</span></p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Produits publiés' : 'Published products' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['users'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Utilisateurs' : 'Users' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_rating'] ?: '—' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Note moyenne vendeurs' : 'Avg. seller rating' }}</p>
        </div>
    </div>

    <!-- Engagement -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $stats['conversations'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Conversations' : 'Conversations' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $stats['messages'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Messages échangés' : 'Messages exchanged' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $stats['reviews'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Avis vendeurs' : 'Seller reviews' }}</p>
        </div>
    </div>

    <!-- Growth chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">{{ $lang === 'fr' ? 'Nouveaux utilisateurs (6 derniers mois)' : 'New users (last 6 months)' }}</h2>
        <div class="flex items-end gap-3 h-28">
            @forelse($registrationsOverTime as $point)
                @php $m = substr($point->month, 5, 2); @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5">
                    <span class="text-xs font-semibold text-gray-700">{{ $point->total }}</span>
                    <div class="w-full bg-forest-500 rounded-t-md" style="height: {{ max(6, round($point->total / $maxGrowth * 84)) }}px"></div>
                    <span class="text-[10px] text-gray-400">{{ $monthLabels[$m][$lang] ?? $m }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune donnée.' : 'No data.' }}</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Verification funnel -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Entonnoir de vérification' : 'Verification funnel' }}</h2>
            <div class="space-y-2">
                @forelse($funnelLabels as $key => $label)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $label }}</span>
                    <span class="font-semibold text-gray-800">{{ $verificationFunnel[$key] ?? 0 }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top products -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Produits les plus vus' : 'Most viewed products' }}</h2>
            <div class="space-y-2">
                @forelse($topProducts as $product)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 truncate">{{ $product->name_fr }}</span>
                    <span class="font-semibold text-gray-800 shrink-0 ml-2">{{ number_format($product->views_count) }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune donnée.' : 'No data.' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Top régions' : 'Top regions' }}</h2>
            <div class="space-y-2">
                @foreach($topRegions as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $lang === 'fr' ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                    <span class="font-semibold text-gray-800">{{ $row->total }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Top secteurs' : 'Top industries' }}</h2>
            <div class="space-y-2">
                @foreach($topIndustries as $row)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $lang === 'fr' ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                    <span class="font-semibold text-gray-800">{{ $row->total }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
