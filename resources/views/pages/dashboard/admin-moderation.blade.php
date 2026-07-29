@extends('layouts.admin')

@php
$pageTitle = $lang === 'fr' ? 'Modération' : 'Moderation';
$reasonLabels = [
    'spam'          => ['fr' => 'Spam', 'en' => 'Spam'],
    'misleading'    => ['fr' => 'Trompeur', 'en' => 'Misleading'],
    'inappropriate' => ['fr' => 'Inapproprié', 'en' => 'Inappropriate'],
    'duplicate'     => ['fr' => 'Doublon', 'en' => 'Duplicate'],
    'other'         => ['fr' => 'Autre', 'en' => 'Other'],
];
@endphp

@section('content')
<div class="max-w-4xl">

    @include('pages.partials.admin-moderation-tabs', ['isFr' => $lang === 'fr', 'modTab' => 'reports'])

    {{-- Product reports --}}
    <div class="ui-card ui-card--flush">
        @forelse($reports as $report)
        <div class="px-5 py-3.5 border-b border-[#F5F1E8] dark:border-[#262B21] last:border-0">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-[#3A1013] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="flag" class="w-4 h-4 text-red-500 dark:text-[#F0555C]"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($report->product)
                        <a href="{{ route('products.show', $report->product->slug) }}" class="text-sm font-semibold text-[#1B1B18] dark:text-[#F3EFE7] hover:text-[#157A43] dark:hover:text-[#339B56]">
                            {{ $lang === 'fr' ? $report->product->name_fr : ($report->product->name_en ?? $report->product->name_fr) }}
                        </a>
                        @else
                        <span class="text-sm font-semibold text-[#B8B2A4] dark:text-[#868778]">{{ $lang === 'fr' ? 'Produit supprimé' : 'Deleted product' }}</span>
                        @endif
                        <span class="ui-pill ui-pill-warn">
                            {{ $reasonLabels[$report->reason][$lang] ?? $report->reason }}
                        </span>
                    </div>
                    @if($report->details)
                    <p class="text-xs text-[#8A857A] dark:text-[#868778] mt-1">{{ $report->details }}</p>
                    @endif
                    <p class="text-[11px] text-[#B8B2A4] dark:text-[#868778] mt-1">
                        {{ $report->reporter?->name ?? ($lang === 'fr' ? 'Anonyme' : 'Anonymous') }}
                        · {{ $report->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-50 dark:bg-[#0C3D1D] text-green-700 dark:text-[#339B56] text-xs font-semibold hover:bg-green-100 dark:hover:bg-[#0C3D1D] transition-colors">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Résoudre' : 'Resolve' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="dismissed">
                        <button type="submit" class="ui-btn ui-btn-secondary ui-btn-sm">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Rejeter' : 'Dismiss' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        {{-- Reporting is connected end to end: a visitor reports a listing from
             the product page (ProductActionsWebController::report), the row
             lands in product_reports with status "open" and surfaces here. An
             empty queue is the good outcome, but it has to read as "nothing to
             do" rather than "nothing works". --}}
        @include('pages.partials.empty-state', [
            'icon'  => 'shield-check',
            'state' => 'empty',
            'title' => $lang === 'fr' ? 'Aucun signalement en attente' : 'No reports waiting',
            'body'  => $lang === 'fr'
                ? 'Quand un visiteur signale une fiche produit, elle arrive ici pour être traitée ou écartée. Rien n\'attend actuellement de décision.'
                : 'When a visitor reports a product listing, it arrives here to be resolved or dismissed. Nothing is currently waiting on a decision.',
        ])
        @endforelse
    </div>
    @if($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
    @endif

</div>
@endsection
