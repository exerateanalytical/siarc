@extends('layouts.admin')

@php
$pageTitle = $lang === 'fr' ? 'Journal d\'audit' : 'Audit Log';
$actionIcons = [
    'business.status_changed'    => ['icon' => 'building-2', 'color' => 'blue'],
    'verification.approved'      => ['icon' => 'badge-check', 'color' => 'green'],
    'verification.rejected'      => ['icon' => 'shield-x', 'color' => 'red'],
    'user.status_changed'        => ['icon' => 'user', 'color' => 'cyan'],
    'user.role_changed'          => ['icon' => 'key-round', 'color' => 'purple'],
    'certification.verified'     => ['icon' => 'check-circle-2', 'color' => 'green'],
    'certification.rejected'     => ['icon' => 'x-circle', 'color' => 'red'],
];
$actionLabels = [
    'business.status_changed' => $lang === 'fr' ? 'Statut entreprise modifié' : 'Business status changed',
    'verification.approved'   => $lang === 'fr' ? 'Vérification approuvée' : 'Verification approved',
    'verification.rejected'   => $lang === 'fr' ? 'Vérification rejetée' : 'Verification rejected',
    'user.status_changed'     => $lang === 'fr' ? 'Statut utilisateur modifié' : 'User status changed',
    'user.role_changed'       => $lang === 'fr' ? 'Rôle utilisateur modifié' : 'User role changed',
    'certification.verified'  => $lang === 'fr' ? 'Certification vérifiée' : 'Certification verified',
    'certification.rejected'  => $lang === 'fr' ? 'Certification rejetée' : 'Certification rejected',
];
@endphp

@section('content')
<div class="max-w-3xl">

    <form method="GET" class="flex gap-2 mb-4">
        <input name="action" value="{{ request('action') }}" placeholder="{{ $lang === 'fr' ? 'Filtrer par action (ex: verification)...' : 'Filter by action (e.g. verification)...' }}" class="ui-field flex-1">
        <button type="submit" class="ui-btn ui-btn-primary">{{ $lang === 'fr' ? 'Filtrer' : 'Filter' }}</button>
    </form>

    <div class="ui-card ui-card--flush">
        @forelse($logs as $log)
        @php $meta = $actionIcons[$log->action] ?? ['icon' => 'activity', 'color' => 'gray']; @endphp
        <div class="flex items-start gap-3 px-4 py-3.5 border-b border-[#F5F1E8] last:border-0">
            <div class="w-8 h-8 rounded-lg bg-{{ $meta['color'] }}-50 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 text-{{ $meta['color'] }}-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18]">{{ $actionLabels[$log->action] ?? $log->action }}</p>
                <p class="ui-dt">
                    {{ $log->user?->name ?? ($lang === 'fr' ? 'Système' : 'System') }}
                    @if($log->new_values)
                        — {{ collect($log->new_values)->map(fn($v, $k) => "$k: $v")->implode(', ') }}
                    @endif
                </p>
            </div>
            <span class="text-xs text-[#B8B2A4] shrink-0">{{ $log->created_at->diffForHumans() }}</span>
        </div>
        @empty
        <div class="ui-empty">{{ $lang === 'fr' ? 'Aucune activité enregistrée.' : 'No activity recorded.' }}</div>
        @endforelse
    </div>

    @if($logs->hasPages())
    <div class="mt-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
