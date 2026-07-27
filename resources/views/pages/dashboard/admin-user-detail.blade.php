@extends('layouts.admin')

@php $pageTitle = $user->name; @endphp

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.users') }}" class="ui-btn ui-btn-ghost ui-btn-sm -ml-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="ui-card mb-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-[#E2F3E8] flex items-center justify-center text-[#0F4824] font-bold">
                {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
            </div>
            <div>
                <p class="ui-card-title">{{ $user->name }}</p>
                <p class="ui-card-sub">{{ $user->email }}</p>
            </div>
            <span @class(['ui-pill ml-auto', 'ui-pill-ok' => $user->status === 'active', 'ui-pill-danger' => $user->status === 'suspended', 'ui-pill-neutral' => ! in_array($user->status, ['active', 'suspended'])])>
                {{ ucfirst($user->status) }}
            </span>
        </div>
        <hr class="ui-divider">
        <div class="ui-dl ui-dl--2">
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Rôle' : 'Role' }}</p><p class="ui-dd">{{ $user->roles->pluck('name')->implode(', ') ?: 'buyer' }}</p></div>
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Région assignée' : 'Assigned region' }}</p><p class="ui-dd">{{ $user->assignedRegion?->name_fr ?? '—' }}</p></div>
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Membre depuis' : 'Member since' }}</p><p class="ui-dd">{{ $user->created_at->format('d/m/Y') }}</p></div>
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Dernière connexion' : 'Last login' }}</p><p class="ui-dd">{{ $user->last_login_at?->diffForHumans() ?? '—' }}</p></div>
        </div>
    </div>

    @if($user->business)
    <div class="ui-card mb-4 flex items-center gap-3">
        <i data-lucide="building-2" class="w-5 h-5 text-blue-500 shrink-0"></i>
        <div class="flex-1 min-w-0">
            <p class="ui-dd">{{ $user->business->name_fr }}</p>
            <p class="ui-dt">{{ ucfirst($user->business->status) }}</p>
        </div>
        <a href="{{ route('admin.businesses.detail', ['id' => $user->business->id]) }}" class="text-xs text-[#157A43] hover:underline">{{ $lang === 'fr' ? 'Voir' : 'View' }}</a>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="ui-card text-center">
            <p class="text-[21px] font-bold text-[#1B1B18] leading-tight">{{ $conversationCount }}</p>
            <p class="ui-dt">{{ $lang === 'fr' ? 'Conversations' : 'Conversations' }}</p>
        </div>
        <div class="ui-card text-center">
            <p class="text-[21px] font-bold text-[#1B1B18] leading-tight">{{ $reviewCount }}</p>
            <p class="ui-dt">{{ $lang === 'fr' ? 'Avis laissés' : 'Reviews left' }}</p>
        </div>
    </div>

    @if($auditAsActor->isNotEmpty())
    <div class="ui-card ui-card--flush">
        <div class="px-5 py-3.5 border-b border-[#F5F1E8]"><h2 class="ui-card-title">{{ $lang === 'fr' ? 'Actions effectuées' : 'Actions taken' }}</h2></div>
        @foreach($auditAsActor as $entry)
        <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[#F5F1E8] last:border-0">
            <span class="flex-1 text-[12.5px] text-[#3B382F]">{{ $entry->action }}</span>
            <span class="ui-dt">{{ $entry->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
