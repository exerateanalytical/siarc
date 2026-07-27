@extends('layouts.dashboard')

@php
$isFr = $lang === 'fr';
$pageTitle = $rfq->reference;

$statusMeta = [
    'pending'     => [$isFr ? 'En attente de proposition' : 'Awaiting a proposal', 'bg-[#F2F5F2] text-[#55524A]'],
    'quoted'      => [$isFr ? 'Proposition reçue' : 'Proposal received',           'bg-[#E2F3E8] text-[#157A43]'],
    'negotiation' => [$isFr ? 'En négociation' : 'In negotiation',                 'bg-[#FBF1DD] text-[#8A6D1F]'],
    'accepted'    => [$isFr ? 'Acceptée' : 'Accepted',                             'bg-[#E2F3E8] text-[#14532D]'],
    'refused'     => [$isFr ? 'Refusée' : 'Refused',                               'bg-[#FDE8E8] text-[#B42025]'],
    'expired'     => [$isFr ? 'Expirée' : 'Expired',                               'bg-[#F2F5F2] text-[#8A857A]'],
];
$meta = $statusMeta[$rfq->status] ?? $statusMeta['pending'];
@endphp

@section('content')
<div class="max-w-3xl space-y-5">

    @if(session('success'))
        <div class="flex items-start gap-2 bg-[#E2F3E8] border border-[#BFDCC8] rounded-lg px-4 py-3 text-sm text-[#14532D]">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ $isOwner ? route('dashboard.quotes') : route('quotes.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#55524A] hover:text-[#14652F]">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ $isFr ? 'Retour aux demandes' : 'Back to requests' }}
    </a>

    <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11.5px] font-semibold text-[#8A857A]">{{ $rfq->reference }}</p>
                <h1 class="text-[17px] font-bold text-[#1B1B18] mt-0.5">{{ $rfq->title }}</h1>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 {{ $meta[1] }}">{{ $meta[0] }}</span>
        </div>

        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-[12.5px]">
            <div>
                <dt class="text-[#8A857A]">{{ $isOwner ? ($isFr ? 'Acheteur' : 'Buyer') : ($isFr ? 'Artisan / Entreprise' : 'Artisan / Business') }}</dt>
                <dd class="font-semibold text-[#1B1B18] mt-0.5">
                    @if($isOwner)
                        {{ $rfq->buyer?->name ?? '—' }}
                    @elseif($rfq->business)
                        <a href="{{ route('businesses.show', ['slug' => $rfq->business->slug, 'lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $rfq->business->name_fr }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[#8A857A]">{{ $isFr ? 'Envoyée le' : 'Sent on' }}</dt>
                <dd class="font-semibold text-[#1B1B18] mt-0.5">{{ $rfq->created_at?->translatedFormat('d F Y') }}</dd>
            </div>
            @if($rfq->desired_response_date)
            <div>
                <dt class="text-[#8A857A]">{{ $isFr ? 'Réponse souhaitée avant le' : 'Response wanted before' }}</dt>
                <dd class="font-semibold text-[#1B1B18] mt-0.5">{{ $rfq->desired_response_date->translatedFormat('d F Y') }}</dd>
            </div>
            @endif
        </dl>

        <div class="mt-5 pt-4 border-t border-[#F0F1F0]">
            <p class="text-[11.5px] font-bold uppercase tracking-[0.04em] text-[#8A6D1F]">{{ $isFr ? 'Votre besoin' : 'Your requirement' }}</p>
            <p class="mt-2 text-[13px] text-[#3B382F] leading-relaxed whitespace-pre-line">{{ $rfq->description }}</p>
            @if($rfq->message)
            <p class="mt-3 text-[12.5px] text-[#55524A] leading-relaxed whitespace-pre-line border-l-2 border-[#ECECEA] pl-3">{{ $rfq->message }}</p>
            @endif
        </div>
    </div>

    {{-- Proposals --}}
    <div>
        <h2 class="text-sm font-semibold text-[#1B1B18] mb-3">
            {{ $isFr ? 'Propositions' : 'Proposals' }}
            <span class="text-xs text-[#8A857A] font-normal">({{ $rfq->proposals->count() }})</span>
        </h2>

        @if($rfq->proposals->isEmpty())
        <div class="bg-white border border-[#ECECEA] rounded-xl text-center py-10 px-4">
            <i data-lucide="hourglass" class="w-8 h-8 text-[#DCE7DF] mx-auto mb-2"></i>
            <p class="text-sm text-[#8A857A]">
                {{ $isOwner
                   ? ($isFr ? 'Vous n\'avez pas encore répondu à cette demande.' : 'You have not answered this request yet.')
                   : ($isFr ? 'L\'artisan n\'a pas encore envoyé de proposition.' : 'The artisan has not sent a proposal yet.') }}
            </p>
            @if($isOwner)
            <a href="{{ route('quotes.builder', ['rfq' => $rfq->id, 'lang' => $lang]) }}" class="inline-flex items-center gap-1.5 mt-3 text-sm font-semibold text-[#14652F] hover:text-[#14532D]">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                {{ $isFr ? 'Rédiger une proposition' : 'Write a proposal' }}
            </a>
            @endif
        </div>
        @else
        <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
            @foreach($rfq->proposals as $p)
            <a href="{{ route('quotes.detail', ['proposal' => $p->id, 'lang' => $lang]) }}" class="flex items-center gap-3 px-4 py-3.5 border-b border-[#F0F1F0] last:border-0 hover:bg-[#FAFBFA]">
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-[#1B1B18]">{{ $p->reference }} <span class="text-[11.5px] font-normal text-[#8A857A]">v{{ $p->version }}</span></p>
                    <p class="text-[11.5px] text-[#8A857A] mt-0.5">
                        {{ $p->created_at?->translatedFormat('d M Y') }}
                        @if($p->valid_until) · {{ $isFr ? 'valable jusqu\'au' : 'valid until' }} {{ $p->valid_until->translatedFormat('d M Y') }}@endif
                    </p>
                </div>
                <p class="text-[13.5px] font-bold text-[#1B1B18] shrink-0 whitespace-nowrap">{{ number_format((int) $p->total, 0, ',', ' ') }} FCFA</p>
                <i data-lucide="chevron-right" class="w-4 h-4 text-[#B4B0A6] shrink-0"></i>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-[#ECECEA] bg-white text-[13px] font-semibold text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            {{ $isFr ? 'Ouvrir la conversation' : 'Open the conversation' }}
        </a>
        @if($isOwner && $rfq->status !== 'accepted')
        <a href="{{ route('quotes.builder', ['rfq' => $rfq->id, 'lang' => $lang]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#14652F] hover:bg-[#14532D] text-white text-[13px] font-semibold transition-colors">
            <i data-lucide="file-plus" class="w-4 h-4"></i>
            {{ $rfq->proposals->isEmpty() ? ($isFr ? 'Rédiger une proposition' : 'Write a proposal') : ($isFr ? 'Nouvelle version' : 'New version') }}
        </a>
        @endif
    </div>
</div>
@endsection
