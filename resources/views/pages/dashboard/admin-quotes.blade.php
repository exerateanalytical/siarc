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
                            {{-- Rows used to be inert text: four columns naming a buyer and a
                                 business, with nothing to open. A list whose rows go nowhere is
                                 the clearest way to make a working console feel dead.

                                 They now open the two admin records behind the conversation.
                                 Deliberately NOT the message thread: messages.thread admits only
                                 participants (MessagingWebController::thread aborts 403 for
                                 everyone else), and letting staff read private buyer ↔ artisan
                                 correspondence is a policy decision, not a broken-link fix. --}}
                            @forelse($adminConversations as $c)
                            <tr>
                                <td>
                                    <p class="text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $c->subject ?? ($isFr ? 'Conversation' : 'Conversation') }}</p>
                                    <p class="text-[11px] text-[#8A857A] dark:text-[#868778]">#{{ $c->id }}</p>
                                </td>
                                <td>
                                    @if($c->buyer_id && $c->buyer_name)
                                        <a href="{{ route('admin.users.detail', ['id' => $c->buyer_id, 'lang' => $lang]) }}"
                                           class="font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">{{ $c->buyer_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($c->business_id && $c->business_name)
                                        <a href="{{ route('admin.businesses.detail', ['id' => $c->business_id, 'lang' => $lang]) }}"
                                           class="font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">{{ $c->business_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($c->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="p-0">
                                @include('pages.partials.empty-state', [
                                    'icon'  => 'messages-square',
                                    'state' => 'empty',
                                    'title' => $isFr ? 'Aucune conversation' : 'No conversations',
                                    'body'  => $isFr
                                        ? 'Chaque échange ouvert par un acheteur auprès d\'un artisan apparaît ici. Aucun n\'a encore été ouvert.'
                                        : 'Every exchange a buyer opens with an artisan appears here. None has been opened yet.',
                                ])
                            </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
@endsection
