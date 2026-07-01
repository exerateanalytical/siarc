@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Support' : 'Support';
$statusLabels = [
    'open' => $lang === 'fr' ? 'Ouvert' : 'Open',
    'in_progress' => $lang === 'fr' ? 'En cours' : 'In progress',
    'resolved' => $lang === 'fr' ? 'Résolu' : 'Resolved',
    'closed' => $lang === 'fr' ? 'Fermé' : 'Closed',
    'all' => $lang === 'fr' ? 'Tous' : 'All',
];
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <div class="flex gap-2 mb-4">
        @foreach(['open', 'in_progress', 'resolved', 'closed', 'all'] as $s)
        <a href="{{ route('admin.support', ['status' => $s]) }}" @class([
            'text-xs font-medium px-3 py-1.5 rounded-full border',
            'bg-forest-600 text-white border-forest-600' => $status === $s,
            'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' => $status !== $s,
        ])>{{ $statusLabels[$s] }}</a>
        @endforeach
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($tickets as $ticket)
        <a href="{{ route('support.show', ['id' => $ticket->id]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                <i data-lucide="life-buoy" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $lang === 'fr' ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr) }}</p>
                <p class="text-xs text-gray-400">{{ $ticket->user?->name }} — {{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-amber-100 text-amber-700' => in_array($ticket->status, ['open', 'in_progress']),
                'bg-green-100 text-green-700' => $ticket->status === 'resolved',
                'bg-gray-100 text-gray-500' => $ticket->status === 'closed',
            ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        </a>
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun ticket.' : 'No tickets.' }}</div>
        @endforelse
    </div>

    @if($tickets->hasPages())
    <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
