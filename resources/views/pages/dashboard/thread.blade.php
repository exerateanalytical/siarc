@extends('layouts.app')

@php
$isBuyer = $conversation->buyer_id === $user->id;
$otherName = $isBuyer
    ? ($lang === 'fr' ? $conversation->business?->name_fr : ($conversation->business?->name_en ?? $conversation->business?->name_fr))
    : $conversation->buyer?->name;
@endphp

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('messages.inbox') }}" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-9 h-9 rounded-full bg-brand-50 flex items-center justify-center shrink-0">
            <i data-lucide="{{ $isBuyer ? 'building-2' : 'user' }}" class="w-4 h-4 text-brand-500"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-bold text-gray-900 truncate">{{ $otherName ?: ($lang === 'fr' ? 'Utilisateur' : 'User') }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $conversation->subject }}</p>
        </div>
        @if($conversation->product)
        <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $conversation->product->slug]) }}" class="ml-auto shrink-0 flex items-center gap-1 px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Voir le produit' : 'View product' }}
        </a>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 space-y-3 max-h-[55vh] overflow-y-auto">
        @forelse($conversation->messages as $message)
            @php $mine = $message->sender_id === $user->id; @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] {{ $mine ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800' }} rounded-2xl px-3.5 py-2.5">
                    <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
                    <p class="text-[10px] mt-1 {{ $mine ? 'text-white/70' : 'text-gray-400' }}">{{ $message->created_at->format('d/m H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-8">{{ $lang === 'fr' ? 'Aucun message.' : 'No messages.' }}</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('messages.reply', ['id' => $conversation->id]) }}" class="flex items-end gap-2">
        @csrf
        <textarea name="body" rows="2" required
            placeholder="{{ $lang === 'fr' ? 'Écrire une réponse...' : 'Write a reply...' }}"
            class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 resize-none"></textarea>
        <button type="submit" class="shrink-0 bg-brand-500 hover:bg-brand-600 text-white p-2.5 rounded-lg transition-colors">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
    @error('body')<p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>@enderror
</div>
@endsection
