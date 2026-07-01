@extends('layouts.app')

@php
$typeIcons = [
    'new_message' => 'message-circle',
    'verification_approved' => 'badge-check',
    'verification_rejected' => 'shield-x',
    'business_suspended' => 'ban',
    'business_rejected' => 'x-circle',
];
@endphp

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-6">
        <a href="/tableau-de-bord" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-8 h-8 bg-forest-100 rounded-lg flex items-center justify-center">
            <i data-lucide="bell" class="w-4 h-4 text-forest-600"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Notifications' : 'Notifications' }}</h1>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($notifications as $n)
        <a href="{{ $n->link ?? '#' }}" class="flex items-start gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="{{ $typeIcons[$n->type] ?? 'bell' }}" class="w-4 h-4 text-forest-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $n->title }}</p>
                @if($n->body)<p class="text-xs text-gray-500 mt-0.5">{{ $n->body }}</p>@endif
                <p class="text-[10px] text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
            </div>
            @if(! $n->read_at)<span class="w-2 h-2 rounded-full bg-forest-500 shrink-0 mt-1.5"></span>@endif
        </a>
        @empty
        <div class="text-center py-14 px-4">
            <i data-lucide="bell-off" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune notification pour l\'instant.' : 'No notifications yet.' }}</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
