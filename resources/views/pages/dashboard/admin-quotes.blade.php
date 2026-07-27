@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'quotes';
    $pageTitle = $isFr ? 'Commandes, Devis & Propositions' : 'Orders, Quotes & Proposals';
    $pageSubtitle = $isFr ? 'Toutes les conversations acheteur ↔ artisan de la plateforme (demandes de devis, négociations et commandes).' : 'All the buyer ↔ artisan conversations on the platform (quote requests, negotiations and orders).';
@endphp

@section('content')
            <section class="ui-card ui-card--flush mt-4">
                <div class="ui-table-wrap">
                    <table class="ui-table min-w-[760px]">
                        <thead>
                            <tr>
                                <th>{{ $isFr ? 'Sujet' : 'Subject' }}</th>
                                <th>{{ $isFr ? 'Acheteur' : 'Buyer' }}</th>
                                <th>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</th>
                                <th>{{ $isFr ? 'Dernière activité' : 'Last activity' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminConversations as $c)
                            <tr>
                                <td>
                                    <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $c->subject ?? ($isFr ? 'Conversation' : 'Conversation') }}</p>
                                    <p class="text-[11px] text-[#8A857A]">#{{ $c->id }}</p>
                                </td>
                                <td>{{ $c->buyer_name ?? '—' }}</td>
                                <td>{{ $c->business_name ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($c->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="ui-empty">{{ $isFr ? 'Aucune conversation pour le moment.' : 'No conversations yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection
