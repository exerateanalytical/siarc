@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$pageTitle = $lang === 'fr' ? 'Support' : 'Support';
$statusLabels = [
    'open' => $lang === 'fr' ? 'Ouvert' : 'Open',
    'in_progress' => $lang === 'fr' ? 'En cours' : 'In progress',
    'resolved' => $lang === 'fr' ? 'Résolu' : 'Resolved',
    'closed' => $lang === 'fr' ? 'Fermé' : 'Closed',
];
@endphp

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
    </div>
    @endif

    <div class="ui-card ui-card--flush mb-6">
        @forelse($tickets as $ticket)
        <a href="{{ route('support.show', ['id' => $ticket->id]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-[#FBF9F4] dark:border-[#262B21] last:border-0 hover:bg-[#FBF9F4] dark:hover:bg-[#242A1E] transition-colors">
            <div class="w-9 h-9 rounded-lg bg-[#FBF9F4] dark:bg-[#12150F] flex items-center justify-center shrink-0">
                <i data-lucide="life-buoy" class="w-4 h-4 text-[#A8A296] dark:text-[#868778]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $lang === 'fr' ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr) }}</p>
                <p class="text-xs text-[#A8A296] dark:text-[#868778]">{{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <span @class([
                'ui-pill shrink-0',
                'ui-pill-warn'    => in_array($ticket->status, ['open', 'in_progress']),
                'ui-pill-ok'      => $ticket->status === 'resolved',
                'ui-pill-neutral' => $ticket->status === 'closed',
            ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        </a>
        @empty
        <div class="ui-empty">{{ $lang === 'fr' ? 'Aucun ticket pour l\'instant.' : 'No tickets yet.' }}</div>
        @endforelse
    </div>

    <div class="ui-card">
        <h2 class="ui-card-title mb-4">{{ $lang === 'fr' ? 'Nouveau ticket' : 'New ticket' }}</h2>
        <form method="POST" action="{{ route('support.store') }}" class="space-y-3">
            @csrf
            @if($categories->isNotEmpty())
            <select name="category_id" class="ui-field ui-select">
                <option value="">{{ $lang === 'fr' ? 'Catégorie (optionnel)' : 'Category (optional)' }}</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $lang === 'fr' ? $cat->name_fr : ($cat->name_en ?? $cat->name_fr) }}</option>
                @endforeach
            </select>
            @endif
            <input name="subject" required maxlength="255" placeholder="{{ $lang === 'fr' ? 'Sujet' : 'Subject' }}" class="ui-field">
            <textarea name="body" required rows="4" maxlength="3000" placeholder="{{ $lang === 'fr' ? 'Décrivez votre problème...' : 'Describe your issue...' }}" class="ui-field ui-textarea"></textarea>
            <button type="submit" class="ui-btn ui-btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Envoyer' : 'Submit' }}
            </button>
        </form>
    </div>
</div>
@endsection
