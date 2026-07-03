@php
    $isFr = $lang === 'fr';
    $adminActive = 'quotes';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Devis & Propositions — Administration' : 'Quotes & Proposals — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">
            <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Commandes, Devis & Propositions' : 'Orders, Quotes & Proposals' }}</h1>
            <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Toutes les conversations acheteur ↔ artisan de la plateforme (demandes de devis, négociations et commandes).' : 'All the buyer ↔ artisan conversations on the platform (quote requests, negotiations and orders).' }}</p>

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
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
