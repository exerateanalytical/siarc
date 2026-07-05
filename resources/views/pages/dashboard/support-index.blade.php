@extends('layouts.dashboard')

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
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden mb-6">
        @forelse($tickets as $ticket)
        <a href="{{ route('support.show', ['id' => $ticket->id]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-[#FBF9F4] last:border-0 hover:bg-[#FBF9F4] transition-colors">
            <div class="w-9 h-9 rounded-lg bg-[#FBF9F4] flex items-center justify-center shrink-0">
                <i data-lucide="life-buoy" class="w-4 h-4 text-[#A8A296]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] truncate">{{ $lang === 'fr' ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr) }}</p>
                <p class="text-xs text-[#A8A296]">{{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-amber-100 text-amber-700' => in_array($ticket->status, ['open', 'in_progress']),
                'bg-green-100 text-green-700' => $ticket->status === 'resolved',
                'bg-[#F1EDE4] text-[#8A857A]' => $ticket->status === 'closed',
            ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        </a>
        @empty
        <div class="text-center py-10 text-sm text-[#A8A296]">{{ $lang === 'fr' ? 'Aucun ticket pour l\'instant.' : 'No tickets yet.' }}</div>
        @endforelse
    </div>

    <div class="bg-white border border-[#EFEBE2] rounded-xl p-5">
        <h2 class="text-sm font-semibold text-[#1B1B18] mb-4">{{ $lang === 'fr' ? 'Nouveau ticket' : 'New ticket' }}</h2>
        <form method="POST" action="{{ route('support.store') }}" class="space-y-3">
            @csrf
            @if($categories->isNotEmpty())
            <select name="category_id" class="w-full text-sm border border-[#EFEBE2] rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                <option value="">{{ $lang === 'fr' ? 'Catégorie (optionnel)' : 'Category (optional)' }}</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $lang === 'fr' ? $cat->name_fr : ($cat->name_en ?? $cat->name_fr) }}</option>
                @endforeach
            </select>
            @endif
            <input name="subject" required maxlength="255" placeholder="{{ $lang === 'fr' ? 'Sujet' : 'Subject' }}" class="w-full text-sm border border-[#EFEBE2] rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
            <textarea name="body" required rows="4" maxlength="3000" placeholder="{{ $lang === 'fr' ? 'Décrivez votre problème...' : 'Describe your issue...' }}" class="w-full text-sm border border-[#EFEBE2] rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400 resize-none"></textarea>
            <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Envoyer' : 'Submit' }}
            </button>
        </form>
    </div>
</div>
@endsection
