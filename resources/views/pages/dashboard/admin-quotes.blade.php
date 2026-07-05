@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'quotes';
    $pageTitle = $isFr ? 'Commandes, Devis & Propositions' : 'Orders, Quotes & Proposals';
    $pageSubtitle = $isFr ? 'Toutes les conversations acheteur ↔ artisan de la plateforme (demandes de devis, négociations et commandes).' : 'All the buyer ↔ artisan conversations on the platform (quote requests, negotiations and orders).';
@endphp

@section('content')
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Sujet' : 'Subject' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Acheteur' : 'Buyer' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Dernière activité' : 'Last activity' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @forelse($adminConversations as $c)
                            <tr>
                                <td class="pl-5 pr-2 py-3">
                                    <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $c->subject ?? ($isFr ? 'Conversation' : 'Conversation') }}</p>
                                    <p class="text-[11px] text-[#8A857A]">#{{ $c->id }}</p>
                                </td>
                                <td class="px-2 py-3 text-[12px] text-[#3B382F]">{{ $c->buyer_name ?? '—' }}</td>
                                <td class="px-2 py-3 text-[12px] text-[#3B382F]">{{ $c->business_name ?? '—' }}</td>
                                <td class="px-2 py-3 text-[12px] text-[#6F6B60]">{{ \Illuminate\Support\Carbon::parse($c->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune conversation pour le moment.' : 'No conversations yet.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection
