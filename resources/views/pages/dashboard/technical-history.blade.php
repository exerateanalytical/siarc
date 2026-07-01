@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Historique des décisions' : 'Decision History';
$actionMeta = [
    'verification.approved'  => ['icon' => 'badge-check', 'color' => 'green', 'label' => ['fr' => 'Vérification approuvée', 'en' => 'Verification approved']],
    'verification.rejected'  => ['icon' => 'shield-x', 'color' => 'red', 'label' => ['fr' => 'Vérification rejetée', 'en' => 'Verification rejected']],
    'certification.verified' => ['icon' => 'check-circle-2', 'color' => 'green', 'label' => ['fr' => 'Certification vérifiée', 'en' => 'Certification verified']],
    'certification.rejected' => ['icon' => 'x-circle', 'color' => 'red', 'label' => ['fr' => 'Certification rejetée', 'en' => 'Certification rejected']],
];
@endphp

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($decisions as $log)
        @php $meta = $actionMeta[$log->action] ?? ['icon' => 'activity', 'color' => 'gray', 'label' => ['fr' => $log->action, 'en' => $log->action]]; @endphp
        <div class="flex items-start gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-8 h-8 rounded-lg bg-{{ $meta['color'] }}-50 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 text-{{ $meta['color'] }}-600"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $meta['label'][$lang] }}</p>
                @if($log->new_values)
                <p class="text-xs text-gray-400">{{ collect($log->new_values)->map(fn($v, $k) => "$k: $v")->implode(' — ') }}</p>
                @endif
            </div>
            <span class="text-xs text-gray-400 shrink-0">{{ $log->created_at->diffForHumans() }}</span>
        </div>
        @empty
        <div class="text-center py-12 text-sm text-gray-400">
            <i data-lucide="history" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
            {{ $lang === 'fr' ? 'Aucune décision prise pour l\'instant.' : 'No decisions made yet.' }}
        </div>
        @endforelse
    </div>
    @if($decisions->hasPages())
    <div class="mt-4">{{ $decisions->links() }}</div>
    @endif
</div>
@endsection
