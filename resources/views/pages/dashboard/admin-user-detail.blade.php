@extends('layouts.admin')

@php $pageTitle = $user->name; @endphp

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.users') }}" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-forest-50 flex items-center justify-center text-forest-700 font-bold">
                {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
            </div>
            <div>
                <p class="text-base font-semibold text-gray-900">{{ $user->name }}</p>
                <p class="text-sm text-gray-400">{{ $user->email }}</p>
            </div>
            <span @class(['ml-auto text-xs font-medium px-2 py-1 rounded-full', 'bg-green-100 text-green-700' => $user->status === 'active', 'bg-red-100 text-red-700' => $user->status === 'suspended'])>
                {{ ucfirst($user->status) }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-3 text-sm border-t border-gray-100 pt-4">
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Rôle' : 'Role' }}</p><p class="font-medium text-gray-800">{{ $user->roles->pluck('name')->implode(', ') ?: 'buyer' }}</p></div>
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Région assignée' : 'Assigned region' }}</p><p class="font-medium text-gray-800">{{ $user->assignedRegion?->name_fr ?? '—' }}</p></div>
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Membre depuis' : 'Member since' }}</p><p class="font-medium text-gray-800">{{ $user->created_at->format('d/m/Y') }}</p></div>
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Dernière connexion' : 'Last login' }}</p><p class="font-medium text-gray-800">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</p></div>
        </div>
    </div>

    @if($user->business)
    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex items-center gap-3">
        <i data-lucide="building-2" class="w-5 h-5 text-blue-500 shrink-0"></i>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">{{ $user->business->name_fr }}</p>
            <p class="text-xs text-gray-400">{{ ucfirst($user->business->status) }}</p>
        </div>
        <a href="{{ route('admin.businesses.detail', ['id' => $user->business->id]) }}" class="text-xs text-forest-600 hover:underline">{{ $lang === 'fr' ? 'Voir' : 'View' }}</a>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $conversationCount }}</p>
            <p class="text-xs text-gray-500">{{ $lang === 'fr' ? 'Conversations' : 'Conversations' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $reviewCount }}</p>
            <p class="text-xs text-gray-500">{{ $lang === 'fr' ? 'Avis laissés' : 'Reviews left' }}</p>
        </div>
    </div>

    @if($auditAsActor->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Actions effectuées' : 'Actions taken' }}</h2></div>
        @foreach($auditAsActor as $entry)
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
            <span class="text-sm text-gray-700 flex-1">{{ $entry->action }}</span>
            <span class="text-xs text-gray-400">{{ $entry->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
