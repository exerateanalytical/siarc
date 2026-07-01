@extends('layouts.dashboard')

@php $pageTitle = $lang === 'fr' ? 'Administration' : 'Admin Dashboard'; @endphp

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="building-2" class="w-4 h-4 text-blue-500 shrink-0"></i>
                <span class="text-xs text-gray-500 truncate">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['businesses']['total'] }}</p>
            <p class="text-xs text-green-600 mt-0.5">{{ $stats['businesses']['published'] }} {{ $lang === 'fr' ? 'publiées' : 'live' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="package" class="w-4 h-4 text-purple-500 shrink-0"></i>
                <span class="text-xs text-gray-500 truncate">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['products']['total'] }}</p>
            <p class="text-xs text-green-600 mt-0.5">{{ $stats['products']['published'] }} {{ $lang === 'fr' ? 'publiés' : 'live' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="users" class="w-4 h-4 text-cyan-500 shrink-0"></i>
                <span class="text-xs text-gray-500 truncate">{{ $lang === 'fr' ? 'Utilisateurs' : 'Users' }}</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['users']['total'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stats['users']['business_owners'] }} entrepreneurs</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="badge-check" class="w-4 h-4 text-pink-500 shrink-0"></i>
                <span class="text-xs text-gray-500 truncate">{{ $lang === 'fr' ? 'Vérifiées' : 'Verified' }}</span>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['businesses']['verified'] }}</p>
            @if($pendingVerifications > 0)
                <p class="text-xs text-amber-600 mt-0.5 font-medium">{{ $pendingVerifications }} {{ $lang === 'fr' ? 'en attente' : 'pending' }}</p>
            @else
                <p class="text-xs text-gray-400 mt-0.5">{{ $lang === 'fr' ? 'certifiées / vérifiées' : 'certified / verified' }}</p>
            @endif
        </div>
    </div>

    @if($pendingVerifications > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3 mb-6">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0 mt-0.5"></i>
        <div>
            <p class="text-sm font-semibold text-amber-900">{{ $pendingVerifications }} {{ $lang === 'fr' ? 'vérification(s) en attente' : 'pending verification(s)' }}</p>
            <a href="{{ route('admin.verifications') }}" class="text-xs text-amber-700 underline mt-1 inline-block">{{ $lang === 'fr' ? 'Traiter' : 'Review' }}</a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Recent Businesses --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i>
                    {{ $lang === 'fr' ? 'Dernières entreprises' : 'Recent Businesses' }}
                </h2>
                <a href="{{ route('admin.businesses') }}" class="text-xs text-forest-600 font-medium">{{ $lang === 'fr' ? 'Voir tout' : 'View all' }}</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentBusinesses as $biz)
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-gray-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $biz->name_fr }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($biz->created_at)->diffForHumans() }}</p>
                    </div>
                    <span @class(['px-2 py-0.5 rounded-full text-xs font-medium shrink-0', 'bg-green-100 text-green-700' => $biz->status === 'published', 'bg-amber-100 text-amber-700' => $biz->status === 'pending', 'bg-gray-100 text-gray-500' => !in_array($biz->status, ['published','pending'])])>{{ $biz->status }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">{{ $lang === 'fr' ? 'Aucune entreprise.' : 'No businesses.' }}</p>
                @endforelse
            </div>
        </div>

        {{-- Shortcuts not already covered by the sidebar --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Raccourcis' : 'Shortcuts' }}</h2>
            </div>
            <div class="divide-y divide-gray-50">
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="globe" class="w-4 h-4 text-blue-500 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Galerie publique' : 'Public gallery' }}
                </a>
                <a href="/docs/api" target="_blank" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                    <i data-lucide="code-2" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Documentation API' : 'API Documentation' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
