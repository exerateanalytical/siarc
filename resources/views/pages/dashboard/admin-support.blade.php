@extends('layouts.admin')

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
<div class="max-w-3xl">

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <div class="flex gap-2 mb-4">
        @foreach(['open', 'in_progress', 'resolved', 'closed', 'all'] as $s)
        <a href="{{ route('admin.support', ['status' => $s]) }}" @class([
            'text-xs font-medium px-3 py-1.5 rounded-full border',
            'bg-[#0F4824] text-white border-[#0F4824]' => $status === $s,
            'bg-white text-[#55524A] border-[#EFEBE2] hover:bg-[#F8F4EC]' => $status !== $s,
        ])>{{ $statusLabels[$s] }}</a>
        @endforeach
    </div>

    <div class="ui-card ui-card--flush">
        @forelse($tickets as $ticket)
        <a href="{{ route('admin.support.ticket', ['id' => $ticket->id, 'lang' => $lang]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-[#F5F1E8] last:border-0 hover:bg-[#F8F4EC] transition-colors">
            <div class="w-9 h-9 rounded-lg bg-[#F8F4EC] flex items-center justify-center shrink-0">
                <i data-lucide="life-buoy" class="w-4 h-4 text-[#B8B2A4]"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] truncate">{{ $lang === 'fr' ? $ticket->subject_fr : ($ticket->subject_en ?? $ticket->subject_fr) }}</p>
                <p class="ui-dt">{{ $ticket->user?->name }} — {{ $ticket->created_at->diffForHumans() }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-amber-100 text-amber-700' => in_array($ticket->status, ['open', 'in_progress']),
                'bg-green-100 text-green-700' => $ticket->status === 'resolved',
                'bg-[#F1EDE3] text-[#8A857A]' => $ticket->status === 'closed',
            ])>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</span>
        </a>
        @empty
        <div class="ui-empty">{{ $lang === 'fr' ? 'Aucun ticket.' : 'No tickets.' }}</div>
        @endforelse
    </div>

    @if($tickets->hasPages())
    <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
