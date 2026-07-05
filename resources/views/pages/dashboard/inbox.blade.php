@extends('layouts.dashboard')

@php $pageTitle = $lang === 'fr' ? 'Messagerie' : 'Messages'; @endphp

@section('content')
<div class="max-w-3xl">

    <div class="bg-white rounded-xl border border-[#EFEBE2] overflow-hidden">
        @forelse($conversations as $conv)
            @php
                $isBuyer = $conv->buyer_id === $userId;
                $otherName = $isBuyer
                    ? ($lang === 'fr' ? $conv->business?->name_fr : ($conv->business?->name_en ?? $conv->business?->name_fr))
                    : $conv->buyer?->name;
                $unread = $conv->messages()->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
            @endphp
            <a href="{{ route('messages.thread', ['id' => $conv->id]) }}" class="flex items-center gap-3 px-4 py-3.5 hover:bg-[#FBF9F4] transition-colors border-b border-[#FBF9F4] last:border-0">
                <div class="w-10 h-10 rounded-full bg-forest-50 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $isBuyer ? 'building-2' : 'user' }}" class="w-4 h-4 text-forest-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-[#1B1B18] truncate">{{ $otherName ?: ($lang === 'fr' ? 'Utilisateur' : 'User') }}</p>
                        @if($unread > 0)
                        <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-xs text-[#8A857A] truncate">{{ $conv->subject }}</p>
                    @if($conv->latestMessage)
                    <p class="text-xs text-[#A8A296] truncate mt-0.5">{{ Str::limit($conv->latestMessage->body, 60) }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-[#A8A296]">{{ $conv->last_message_at?->diffForHumans() }}</p>
                    @if($unread > 0)
                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full">{{ $unread }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-14 px-4">
                <i data-lucide="message-circle" class="w-10 h-10 text-[#EFEBE2] mx-auto mb-3"></i>
                <p class="text-sm text-[#A8A296]">{{ $lang === 'fr' ? 'Aucun message pour l\'instant.' : 'No messages yet.' }}</p>
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
    <div class="mt-4">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection
