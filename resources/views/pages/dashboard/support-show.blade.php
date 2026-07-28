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
        <a href="{{ $isAdmin ? route('admin.support') : route('support.index') }}" class="p-2 -ml-2 rounded-lg hover:bg-[#F1EDE4] dark:hover:bg-[#242A1E]">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>
        </a>
        <span @class([
            'ui-pill',
            'ui-pill-warn'    => in_array($ticket->status, ['open', 'in_progress']),
            'ui-pill-ok'      => $ticket->status === 'resolved',
            'ui-pill-neutral' => $ticket->status === 'closed',
        ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>

        @if($isAdmin && $ticket->status !== 'closed')
        <form method="POST" action="{{ route('admin.support.close', ['id' => $ticket->id]) }}" class="ml-auto">
            @csrf
            <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">{{ $lang === 'fr' ? 'Fermer le ticket' : 'Close ticket' }}</button>
        </form>
        @endif
    </div>

    <div class="ui-card p-4 mb-4 space-y-3 max-h-[55vh] overflow-y-auto">
        @foreach($ticket->replies as $reply)
        <div class="flex {{ $reply->is_staff ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[80%] {{ $reply->is_staff ? 'bg-forest-600 dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'bg-[#F1EDE4] dark:bg-[#1A1E16] text-[#262521] dark:text-[#F3EFE7] ' }} rounded-2xl px-3.5 py-2.5">
                <p class="text-[10px] font-semibold mb-0.5 {{ $reply->is_staff ? 'text-white/70' : 'text-[#A8A296] dark:text-[#868778] ' }}">
                    {{ $reply->is_staff ? ($lang === 'fr' ? 'Support' : 'Support') : $reply->user?->name }}
                </p>
                <p class="text-sm whitespace-pre-line">{{ $lang === 'fr' ? $reply->body_fr : ($reply->body_en ?? $reply->body_fr) }}</p>
                <p class="text-[10px] mt-1 {{ $reply->is_staff ? 'text-white/70' : 'text-[#A8A296] dark:text-[#868778] ' }}">{{ $reply->created_at->format('d/m H:i') }}</p>
            </div>
        </div>
        @endforeach
    </div>

    @if($ticket->status !== 'closed')
    <form method="POST" action="{{ route('support.reply', ['id' => $ticket->id]) }}" class="flex items-end gap-2">
        @csrf
        <textarea name="body" rows="2" required maxlength="3000"
            placeholder="{{ $lang === 'fr' ? 'Écrire une réponse...' : 'Write a reply...' }}"
            class="ui-field ui-textarea flex-1 min-h-[64px] resize-none"></textarea>
        <button type="submit" class="ui-btn ui-btn-primary shrink-0 px-3">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
    @error('body')<p class="ui-error">{{ $message }}</p>@enderror
    @else
    <p class="ui-empty">{{ $lang === 'fr' ? 'Ce ticket est fermé.' : 'This ticket is closed.' }}</p>
    @endif
</div>
@endsection
