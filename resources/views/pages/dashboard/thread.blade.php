@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$isBuyer = $conversation->buyer_id === $user->id;
$otherName = $isBuyer
    ? ($lang === 'fr' ? $conversation->business?->name_fr : ($conversation->business?->name_en ?? $conversation->business?->name_fr))
    : $conversation->buyer?->name;
$pageTitle = $lang === 'fr' ? 'Messagerie' : 'Messages';
@endphp

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('messages.inbox') }}" class="p-2 -ml-2 rounded-lg hover:bg-[#F1EDE4] lg:hidden">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>
        </a>
        <div class="w-9 h-9 rounded-full bg-forest-50 flex items-center justify-center shrink-0">
            <i data-lucide="{{ $isBuyer ? 'building-2' : 'user' }}" class="w-4 h-4 text-forest-500"></i>
        </div>
        <div class="min-w-0">
            <p class="text-sm font-bold text-[#1B1B18] truncate">{{ $otherName ?: ($lang === 'fr' ? 'Utilisateur' : 'User') }}</p>
            <p class="text-xs text-[#A8A296] truncate">{{ $conversation->subject }}</p>
        </div>
        @if($conversation->product)
        <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $conversation->product->slug]) }}" class="ml-auto shrink-0 flex items-center gap-1 px-2.5 py-1.5 border border-[#EFEBE2] rounded-lg text-xs text-[#6F6B60] hover:bg-[#FBF9F4]">
            <i data-lucide="package" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Voir le produit' : 'View product' }}
        </a>
        @endif
    </div>

    <div class="bg-white border border-[#EFEBE2] rounded-xl p-4 mb-4 space-y-3 max-h-[55vh] overflow-y-auto">
        @forelse($conversation->messages as $message)
            @php $mine = $message->sender_id === $user->id; @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] {{ $mine ? 'bg-forest-500 text-white' : 'bg-[#F1EDE4] text-[#262521]' }} rounded-2xl px-3.5 py-2.5">
                    <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
                    <p class="text-[10px] mt-1 {{ $mine ? 'text-white/70' : 'text-[#A8A296]' }}">{{ $message->created_at->format('d/m H:i') }}</p>
                </div>
            </div>
        @empty
            <p class="text-sm text-[#A8A296] text-center py-8">{{ $lang === 'fr' ? 'Aucun message.' : 'No messages.' }}</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('messages.reply', ['id' => $conversation->id]) }}" class="flex items-end gap-2">
        @csrf
        <textarea name="body" rows="2" required
            placeholder="{{ $lang === 'fr' ? 'Écrire une réponse...' : 'Write a reply...' }}"
            class="flex-1 text-sm border border-[#EFEBE2] rounded-lg px-3 py-2.5 focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 resize-none"></textarea>
        <button type="submit" class="shrink-0 bg-forest-500 hover:bg-forest-600 text-white p-2.5 rounded-lg transition-colors">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
    @error('body')<p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>@enderror
</div>
@endsection
