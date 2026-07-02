@extends('layouts.dashboard')

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
<div class="max-w-4xl mx-auto">

    @if(session('success'))
        <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tab pills --}}
    <div class="flex items-center gap-2 mb-5">
        <a href="{{ route('admin.moderation') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab === 'reports' ? 'bg-forest-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="flag" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Signalements' : 'Reports' }}
            <span class="{{ $tab === 'reports' ? 'bg-white/20' : 'bg-gray-100' }} px-1.5 rounded-full text-[10px]">{{ $reports->total() }}</span>
        </a>
        <a href="{{ route('admin.moderation', ['tab' => 'reviews']) }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $tab === 'reviews' ? 'bg-forest-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="star" class="w-3.5 h-3.5"></i>
            {{ $lang === 'fr' ? 'Avis' : 'Reviews' }}
            <span class="{{ $tab === 'reviews' ? 'bg-white/20' : 'bg-gray-100' }} px-1.5 rounded-full text-[10px]">{{ $reviews->total() }}</span>
        </a>
    </div>

    @if($tab === 'reports')
    {{-- Product reports --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($reports as $report)
        <div class="px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="flag" class="w-4 h-4 text-red-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($report->product)
                        <a href="{{ route('products.show', $report->product->slug) }}" class="text-sm font-semibold text-gray-900 hover:text-forest-600">
                            {{ $lang === 'fr' ? $report->product->name_fr : ($report->product->name_en ?? $report->product->name_fr) }}
                        </a>
                        @else
                        <span class="text-sm font-semibold text-gray-400">{{ $lang === 'fr' ? 'Produit supprimé' : 'Deleted product' }}</span>
                        @endif
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-semibold">
                            {{ $reasonLabels[$report->reason][$lang] ?? $report->reason }}
                        </span>
                    </div>
                    @if($report->details)
                    <p class="text-xs text-gray-500 mt-1">{{ $report->details }}</p>
                    @endif
                    <p class="text-[11px] text-gray-400 mt-1">
                        {{ $report->reporter?->name ?? ($lang === 'fr' ? 'Anonyme' : 'Anonymous') }}
                        · {{ $report->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 transition-colors">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Résoudre' : 'Resolve' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="dismissed">
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-semibold hover:bg-gray-100 transition-colors">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Rejeter' : 'Dismiss' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-14 px-4">
            <i data-lucide="shield-check" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun signalement en attente.' : 'No open reports.' }}</p>
        </div>
        @endforelse
    </div>
    @if($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
    @endif

    @else
    {{-- Business reviews --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($reviews as $review)
        <div class="px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="star" class="w-4 h-4 text-amber-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($review->business)
                        <a href="{{ route('businesses.show', $review->business->slug) }}" class="text-sm font-semibold text-gray-900 hover:text-forest-600">
                            {{ $lang === 'fr' ? $review->business->name_fr : ($review->business->name_en ?? $review->business->name_fr) }}
                        </a>
                        @endif
                        <span class="inline-flex items-center gap-0.5 text-[11px] font-bold text-amber-600">
                            {{ $review->rating }}/5
                            <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i>
                        </span>
                    </div>
                    @if($review->title)<p class="text-xs font-medium text-gray-700 mt-1">{{ $review->title }}</p>@endif
                    @if($review->body)<p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($review->body, 200) }}</p>@endif
                    <p class="text-[11px] text-gray-400 mt-1">
                        {{ $review->reviewer?->name ?? '—' }} · {{ $review->created_at->diffForHumans() }}
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" class="shrink-0"
                    onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer cet avis ?' : 'Delete this review?' }}')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        {{ $lang === 'fr' ? 'Supprimer' : 'Delete' }}
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-14 px-4">
            <i data-lucide="star-off" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun avis pour l\'instant.' : 'No reviews yet.' }}</p>
        </div>
        @endforelse
    </div>
    @if($reviews->hasPages())
    <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
    @endif
</div>
@endsection
