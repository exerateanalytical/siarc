@extends('layouts.dashboard')

@php
$isFr = $lang === 'fr';
$pageTitle = $rfq->reference;

$statusMeta = [
    'pending'     => [$isFr ? 'En attente de proposition' : 'Awaiting a proposal', 'ui-pill-neutral'],
    'quoted'      => [$isFr ? 'Proposition reçue' : 'Proposal received',           'ui-pill-ok'],
    'negotiation' => [$isFr ? 'En négociation' : 'In negotiation',                 'ui-pill-warn'],
    'accepted'    => [$isFr ? 'Acceptée' : 'Accepted',                             'ui-pill-ok'],
    'refused'     => [$isFr ? 'Refusée' : 'Refused',                               'ui-pill-danger'],
    'expired'     => [$isFr ? 'Expirée' : 'Expired',                               'ui-pill-neutral'],
];
$meta = $statusMeta[$rfq->status] ?? $statusMeta['pending'];
@endphp

@section('content')
<div class="max-w-3xl space-y-5">

    @if(session('success'))
        <div class="ui-alert ui-alert-ok">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ $isOwner ? route('dashboard.quotes') : route('quotes.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#55524A] dark:text-[#B4B5A6] hover:text-[#14652F] dark:hover:text-[#339B56]">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ $isFr ? 'Retour aux demandes' : 'Back to requests' }}
    </a>

    <div class="ui-card">
        <div class="ui-card-head">
            <div class="min-w-0">
                <p class="ui-eyebrow">{{ $rfq->reference }}</p>
                <h1 class="text-[17px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] mt-0.5">{{ $rfq->title }}</h1>
            </div>
            <span class="ui-pill {{ $meta[1] }} shrink-0">{{ $meta[0] }}</span>
        </div>

        <dl class="ui-dl ui-dl--2">
            <div>
                <dt class="ui-dt">{{ $isOwner ? ($isFr ? 'Acheteur' : 'Buyer') : ($isFr ? 'Artisan / Entreprise' : 'Artisan / Business') }}</dt>
                <dd class="ui-dd">
                    @if($isOwner)
                        {{ $rfq->buyer?->name ?? '—' }}
                    @elseif($rfq->business)
                        <a href="{{ route('businesses.show', ['slug' => $rfq->business->slug, 'lang' => $lang]) }}" class="hover:text-[#14652F] dark:hover:text-[#339B56]">{{ $rfq->business->name_fr }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Envoyée le' : 'Sent on' }}</dt>
                <dd class="ui-dd">{{ $rfq->created_at?->translatedFormat('d F Y') }}</dd>
            </div>
            @if($rfq->desired_response_date)
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Réponse souhaitée avant le' : 'Response wanted before' }}</dt>
                <dd class="ui-dd">{{ $rfq->desired_response_date->translatedFormat('d F Y') }}</dd>
            </div>
            @endif
        </dl>

        <hr class="ui-divider">
        <div>
            <p class="ui-eyebrow">{{ $isFr ? 'Votre besoin' : 'Your requirement' }}</p>
            <p class="mt-2 text-[13px] text-[#3B382F] dark:text-[#B4B5A6] leading-relaxed whitespace-pre-line">{{ $rfq->description }}</p>
            @if($rfq->message)
            <p class="mt-3 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed whitespace-pre-line border-l-2 border-[#ECECEA] dark:border-[#262B21] pl-3">{{ $rfq->message }}</p>
            @endif
        </div>
    </div>

    {{-- Proposals --}}
    <div>
        <h2 class="ui-card-title mb-3">
            {{ $isFr ? 'Propositions' : 'Proposals' }}
            <span class="text-xs text-[#8A857A] dark:text-[#868778] font-normal">({{ $rfq->proposals->count() }})</span>
        </h2>

        @if($rfq->proposals->isEmpty())
        <div class="ui-card ui-card--flush">
            <div class="ui-empty">
                <i data-lucide="hourglass" class="w-8 h-8 text-[#DCE7DF] mx-auto mb-2"></i>
                {{ $isOwner
                   ? ($isFr ? 'Vous n\'avez pas encore répondu à cette demande.' : 'You have not answered this request yet.')
                   : ($isFr ? 'L\'artisan n\'a pas encore envoyé de proposition.' : 'The artisan has not sent a proposal yet.') }}
                @if($isOwner)
                <a href="{{ route('quotes.builder', ['rfq' => $rfq->id, 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-3">
                    <i data-lucide="file-plus" class="w-4 h-4"></i>
                    {{ $isFr ? 'Rédiger une proposition' : 'Write a proposal' }}
                </a>
                @endif
            </div>
        </div>
        @else
        <div class="ui-card ui-card--flush">
            @foreach($rfq->proposals as $p)
            <a href="{{ route('quotes.detail', ['proposal' => $p->id, 'lang' => $lang]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-[#F5F1E8] dark:border-[#262B21] last:border-0 hover:bg-[#FAFBFA] dark:hover:bg-[#242A1E]">
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->reference }} <span class="text-[11.5px] font-normal text-[#8A857A] dark:text-[#868778]">v{{ $p->version }}</span></p>
                    <p class="text-[11.5px] text-[#8A857A] dark:text-[#868778] mt-0.5">
                        {{ $p->created_at?->translatedFormat('d M Y') }}
                        @if($p->valid_until) · {{ $isFr ? 'valable jusqu\'au' : 'valid until' }} {{ $p->valid_until->translatedFormat('d M Y') }}@endif
                    </p>
                </div>
                <p class="text-[13.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] shrink-0 whitespace-nowrap">{{ number_format((int) $p->total, 0, ',', ' ') }} FCFA</p>
                <i data-lucide="chevron-right" class="w-4 h-4 text-[#B4B0A6] dark:text-[#868778] shrink-0"></i>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            {{ $isFr ? 'Ouvrir la conversation' : 'Open the conversation' }}
        </a>
        @if($isOwner && $rfq->status !== 'accepted')
        <a href="{{ route('quotes.builder', ['rfq' => $rfq->id, 'lang' => $lang]) }}" class="ui-btn ui-btn-primary">
            <i data-lucide="file-plus" class="w-4 h-4"></i>
            {{ $rfq->proposals->isEmpty() ? ($isFr ? 'Rédiger une proposition' : 'Write a proposal') : ($isFr ? 'Nouvelle version' : 'New version') }}
        </a>
        @endif
    </div>
</div>
@endsection
