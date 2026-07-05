@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$pageTitle = $lang === 'fr' ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr);
$statusLabels = [
    'open' => $lang === 'fr' ? 'Ouvert' : 'Open',
    'in_progress' => $lang === 'fr' ? 'En cours' : 'In progress',
    'resolved' => $lang === 'fr' ? 'Résolu' : 'Resolved',
    'closed' => $lang === 'fr' ? 'Fermé' : 'Closed',
];
$isAdmin = ! empty($siacUser['is_admin']);
@endphp

@section('content')
<div class="max-w-2xl">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ $isAdmin ? route('admin.support') : route('support.index') }}" class="p-2 -ml-2 rounded-lg hover:bg-[#F1EDE4]">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>
        </a>
        <span @class([
            'text-xs font-medium px-2 py-1 rounded-full',
            'bg-amber-100 text-amber-700' => in_array($ticket->status, ['open', 'in_progress']),
            'bg-green-100 text-green-700' => $ticket->status === 'resolved',
            'bg-[#F1EDE4] text-[#8A857A]' => $ticket->status === 'closed',
        ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>

        @if($isAdmin && $ticket->status !== 'closed')
        <form method="POST" action="{{ route('admin.support.close', ['id' => $ticket->id]) }}" class="ml-auto">
            @csrf
            <button type="submit" class="text-xs text-red-600 hover:underline font-medium">{{ $lang === 'fr' ? 'Fermer le ticket' : 'Close ticket' }}</button>
        </form>
        @endif
    </div>

    <div class="bg-white border border-[#EFEBE2] rounded-xl p-4 mb-4 space-y-3 max-h-[55vh] overflow-y-auto">
        @foreach($ticket->replies as $reply)
        <div class="flex {{ $reply->is_staff ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[80%] {{ $reply->is_staff ? 'bg-forest-600 text-white' : 'bg-[#F1EDE4] text-[#262521]' }} rounded-2xl px-3.5 py-2.5">
                <p class="text-[10px] font-semibold mb-0.5 {{ $reply->is_staff ? 'text-white/70' : 'text-[#A8A296]' }}">
                    {{ $reply->is_staff ? ($lang === 'fr' ? 'Support' : 'Support') : $reply->user?->name }}
                </p>
                <p class="text-sm whitespace-pre-line">{{ $lang === 'fr' ? $reply->body_fr : ($reply->body_en ?? $reply->body_fr) }}</p>
                <p class="text-[10px] mt-1 {{ $reply->is_staff ? 'text-white/70' : 'text-[#A8A296]' }}">{{ $reply->created_at->format('d/m H:i') }}</p>
            </div>
        </div>
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
    <form method="POST" action="{{ route('support.reply', ['id' => $ticket->id]) }}" class="flex items-end gap-2">
        @csrf
        <textarea name="body" rows="2" required maxlength="3000"
            placeholder="{{ $lang === 'fr' ? 'Écrire une réponse...' : 'Write a reply...' }}"
            class="flex-1 text-sm border border-[#EFEBE2] rounded-lg px-3 py-2.5 focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 resize-none"></textarea>
        <button type="submit" class="shrink-0 bg-forest-600 hover:bg-forest-700 text-white p-2.5 rounded-lg transition-colors">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
    @error('body')<p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>@enderror
    @else
    <p class="text-center text-sm text-[#A8A296]">{{ $lang === 'fr' ? 'Ce ticket est fermé.' : 'This ticket is closed.' }}</p>
    @endif
</div>
@endsection
