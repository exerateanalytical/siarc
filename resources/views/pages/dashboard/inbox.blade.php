@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-6">
        <a href="/tableau-de-bord" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
            <i data-lucide="message-circle" class="w-4 h-4 text-blue-600"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Messagerie' : 'Messages' }}</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @forelse($conversations as $conv)
            @php
                $isBuyer = $conv->buyer_id === $userId;
                $otherName = $isBuyer
                    ? ($lang === 'fr' ? $conv->business?->name_fr : ($conv->business?->name_en ?? $conv->business?->name_fr))
                    : $conv->buyer?->name;
                $unread = $conv->messages()->where('sender_id', '!=', $userId)->whereNull('read_at')->count();
            @endphp
            <a href="{{ route('messages.thread', ['id' => $conv->id]) }}" class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                <div class="w-10 h-10 rounded-full bg-brand-50 flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $isBuyer ? 'building-2' : 'user' }}" class="w-4 h-4 text-brand-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $otherName ?: ($lang === 'fr' ? 'Utilisateur' : 'User') }}</p>
                        @if($unread > 0)
                        <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 truncate">{{ $conv->subject }}</p>
                    @if($conv->latestMessage)
                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($conv->latestMessage->body, 60) }}</p>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400">{{ $conv->last_message_at?->diffForHumans() }}</p>
                    @if($unread > 0)
                    <span class="inline-block mt-1 px-1.5 py-0.5 bg-blue-500 text-white text-[10px] font-bold rounded-full">{{ $unread }}</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-14 px-4">
                <i data-lucide="message-circle" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
                <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun message pour l\'instant.' : 'No messages yet.' }}</p>
            </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
    <div class="mt-4">{{ $conversations->links() }}</div>
    @endif
</div>
@endsection
