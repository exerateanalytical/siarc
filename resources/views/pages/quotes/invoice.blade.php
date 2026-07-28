@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Navigation comes from the canonical dashboard sidebar (role-scoped, live
    // counts). The design's standalone nav list with fixed badge numbers is gone.

    // Real invoice threading (?invoice=ID, authorized in the route)
    $rin   = $realInvoice;
    $rpp   = $rin->purchaseOrder->proposal;
    $rq    = $rpp->request;
    $biz   = $rq?->business;
    $buyer = $rq?->buyer;

    // [thumb, name, desc, qty, unit, total]
    $invRows = $rpp->items->map(fn ($it, $i) => [
        'qv-prod-' . (($i % 4) + 1) . '.png',
        $it->name,
        $it->description ?? '',
        (string) $it->quantity,
        number_format($it->unit_price),
        number_format($it->total),
    ])->all();

    $realInvRef    = $rin->reference;
    $realQuoRef    = $rpp->reference;
    $realPoRef     = $rin->purchaseOrder->reference;
    $realBizName   = $biz->name_fr ?? '—';
    $realBizLogo   = $biz?->logo ? asset('storage/' . $biz->logo) : brand_asset('mark');
    $realBuyerName = $buyer->name ?? '—';
    $realInvDate   = $rin->created_at->format('d/m/Y');
    $realDueDate   = $rin->due_date?->format('d/m/Y');
    $realDueIn     = $rin->due_date ? max((int) now()->startOfDay()->diffInDays($rin->due_date, false), 0) : null;
    $realPaid      = $rin->status === 'paid';
    $realPaidAt    = $rin->paid_at?->format('d/m/Y H:i');
    $realMethod    = $rin->payment_method;
    $realTotal     = number_format($rin->total) . ' FCFA';

    // [label, value, color]
    $invTotals = [
        [$isFr ? 'Sous-total' : 'Subtotal', number_format($rpp->subtotal) . ' FCFA', '#1B1B18'],
        [($isFr ? 'Remise globale' : 'Global discount') . ' (' . rtrim(rtrim(number_format($rpp->global_discount_pct, 2), '0'), '.') . '%)', '-' . number_format($rpp->discount_amount) . ' FCFA', '#E5484D'],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)', number_format($rpp->tax_amount) . ' FCFA', '#1B1B18'],
        [$isFr ? 'Frais de livraison' : 'Delivery costs', number_format($rpp->delivery_fee) . ' FCFA', '#1B1B18'],
        [$isFr ? 'Assurance' : 'Insurance', number_format($rpp->insurance_fee) . ' FCFA', '#1B1B18'],
    ];

    // Only the account holder is known — no banking details are stored on the record.
    $bankRows = [
        [$isFr ? 'Titulaire' : 'Account holder', $realBizName],
    ];

    // [title, timestamp]
    $invHistory = [
        [$isFr ? 'Facture créée' : 'Invoice created', $rin->created_at->format('d/m/Y H:i')],
        [$isFr ? 'Envoyée au client' : 'Sent to the client', $rin->created_at->format('d/m/Y H:i')],
    ];
    if ($realPaid) {
        $invHistory[] = [$isFr ? 'Paiement reçu' : 'Payment received', $realPaidAt];
        $invHistory[] = [$isFr ? 'Facture payée' : 'Invoice paid', $realPaidAt];
    } else {
        $invHistory[] = [$isFr ? 'En attente de paiement' : 'Awaiting payment', $isFr ? 'En cours' : 'Pending'];
    }

    // [icon, label, print]
    $invActions = [
        ['send',       $isFr ? 'Envoyer par email' : 'Send by email',        route('messages.inbox', ['lang' => $lang])],
        ['rotate-ccw', $isFr ? 'Créer un avoir' : 'Create a credit note',    route('messages.inbox', ['lang' => $lang])],
        ['printer',    $isFr ? 'Imprimer la facture' : 'Print the invoice',  null],
        ['download',   $isFr ? 'Télécharger PDF' : 'Download PDF',           null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Détails de la facture — Artisan Hub 237' : 'Invoice details — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        @if(session('success'))
        <div class="mb-4 ui-alert ui-alert-ok">
            <i data-lucide="circle-check" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Breadcrumb + title -->
        <nav class="flex items-center gap-2 text-[12.5px]">
            <a href="{{ route('orders.index', ['lang' => $lang]) }}" class="font-semibold text-[#3565DE] hover:text-[#14652F]">Factures</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Détails de la facture' : 'Invoice details' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[24px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Facture' : 'Invoice' }}
                    <span id="inv-pill" class="rounded-md px-3 py-1 text-[11.5px] font-bold tracking-[0.03em] uppercase" style="background:{{ $realPaid ? '#E2F3E8' : '#FDE8E8' }};color:{{ $realPaid ? '#157A43' : '#E5484D' }}">{{ $realPaid ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Facture' : 'Invoice' }} #: <span class="font-semibold text-[#1B1B18]">{{ $realInvRef }}</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Basée sur' : 'Based on' }}: <span class="font-semibold text-[#1B1B18]">{{ $realQuoRef }}</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Artisan' : 'Artisan' }}: <span class="font-semibold text-[#1B1B18]">{{ $realBizName }}</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </button>
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="printer" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Imprimer' : 'Print' }}
                </button>
                <a href="#reglement" class="ui-btn ui-btn-primary">
                    <i data-lucide="banknote" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Règlement' : 'Settlement' }}
                </a>
            </div>
        </div>

        {{-- ── Offline settlement ──────────────────────────────────────────
             The platform never receives the price of a sale, so it cannot know an invoice
             is paid. The artisan records what they received; the buyer confirms
             or disputes it. Every entry is attributed, so "paid" is never one
             party's unaccountable word. --}}
        @php
            $vUser     = session('siac_user') ?? [];
            $vIsSeller = ($biz->user_id ?? null) === ($vUser['id'] ?? null);
            $vIsBuyer  = ($rq->buyer_id ?? null) === ($vUser['id'] ?? null);
            $vMethods  = [
                'mobile_money'  => $isFr ? 'Mobile Money (MTN / Orange)' : 'Mobile Money (MTN / Orange)',
                'bank_transfer' => $isFr ? 'Virement bancaire' : 'Bank transfer',
                'cash'          => $isFr ? 'Espèces' : 'Cash',
                'cheque'        => $isFr ? 'Chèque' : 'Cheque',
                'other'         => $isFr ? 'Autre' : 'Other',
            ];
            $vRecorder = $rin->recorded_by ? \App\Modules\Auth\Models\User::find($rin->recorded_by) : null;
        @endphp
        <section id="reglement" class="ui-card mt-5">
            <div class="ui-card-head">
                <div>
                    <h2 class="ui-card-title">{{ $isFr ? 'Règlement' : 'Settlement' }}</h2>
                    <p class="ui-card-sub">{{ $isFr
                        ? 'Le paiement se fait directement entre vous — la plateforme ne reçoit pas le prix de la vente.'
                        : 'Payment happens directly between you — the platform does not receive the price of the sale.' }}</p>
                </div>
                @if($rin->disputed_at)
                    <span class="ui-pill ui-pill-danger">{{ $isFr ? 'Contesté' : 'Disputed' }}</span>
                @elseif($rin->confirmed_at)
                    <span class="ui-pill ui-pill-ok">{{ $isFr ? 'Confirmé par l\'acheteur' : 'Confirmed by buyer' }}</span>
                @elseif($realPaid)
                    <span class="ui-pill ui-pill-warn">{{ $isFr ? 'En attente de confirmation' : 'Awaiting confirmation' }}</span>
                @else
                    <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non réglée' : 'Unsettled' }}</span>
                @endif
            </div>

            @if($errors->has('payment'))
            <div class="ui-alert ui-alert-danger mb-4"><i data-lucide="alert-circle" class="w-4 h-4"></i>{{ $errors->first('payment') }}</div>
            @endif

            {{-- What has been recorded so far --}}
            @if($realPaid || $rin->disputed_at)
            <dl class="ui-dl ui-dl--2">
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Moyen' : 'Method' }}</dt>
                    <dd class="ui-dd">{{ $vMethods[$rin->payment_method] ?? ($rin->payment_method ?: '—') }}</dd>
                </div>
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Reçu le' : 'Received on' }}</dt>
                    <dd class="ui-dd">{{ $rin->paid_at ? \Illuminate\Support\Carbon::parse($rin->paid_at)->translatedFormat('d F Y') : '—' }}</dd>
                </div>
                @if($rin->payment_reference)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                    <dd class="ui-dd">{{ $rin->payment_reference }}</dd>
                </div>
                @endif
                @if($vRecorder)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Enregistré par' : 'Recorded by' }}</dt>
                    <dd class="ui-dd">{{ $vRecorder->name }}</dd>
                </div>
                @endif
            </dl>
            @if($rin->payment_note)
            <p class="ui-hint mt-3">{{ $rin->payment_note }}</p>
            @endif
            @if($rin->disputed_at)
            <div class="ui-alert ui-alert-danger mt-4">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span><strong>{{ $isFr ? 'Contesté par l\'acheteur' : 'Disputed by the buyer' }}</strong> — {{ $rin->dispute_reason }}</span>
            </div>
            @endif
            @endif

            {{-- Seller records what they received --}}
            @if($vIsSeller && ! $rin->confirmed_at)
            <form method="POST" action="{{ route('quotes.record-payment', ['invoice' => $rin->id, 'lang' => $lang]) }}" class="mt-5">
                @csrf
                @if($realPaid)<hr class="ui-divider">@endif
                <p class="ui-eyebrow mb-3">{{ $realPaid ? ($isFr ? 'Corriger l\'enregistrement' : 'Correct the record') : ($isFr ? 'Enregistrer le paiement reçu' : 'Record the payment you received') }}</p>
                <div class="ui-form-grid ui-form-grid--2">
                    <div>
                        <label class="ui-label" for="pay-method">{{ $isFr ? 'Moyen de paiement' : 'Payment method' }}<span class="ui-req">*</span></label>
                        <select id="pay-method" name="payment_method" required class="ui-field ui-select">
                            @foreach($vMethods as $mVal => $mLabel)
                            <option value="{{ $mVal }}" @selected($rin->payment_method === $mVal)>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="ui-label" for="pay-date">{{ $isFr ? 'Date de réception' : 'Date received' }}</label>
                        <input id="pay-date" type="date" name="paid_at" max="{{ now()->toDateString() }}"
                               value="{{ $rin->paid_at ? \Illuminate\Support\Carbon::parse($rin->paid_at)->toDateString() : now()->toDateString() }}" class="ui-field">
                    </div>
                    <div>
                        <label class="ui-label" for="pay-ref">{{ $isFr ? 'Référence de la transaction' : 'Transaction reference' }}</label>
                        <input id="pay-ref" type="text" name="payment_reference" value="{{ $rin->payment_reference }}" class="ui-field">
                        <p class="ui-hint">{{ $isFr ? 'N° Mobile Money, référence de virement…' : 'Mobile Money ID, transfer reference…' }}</p>
                    </div>
                    <div>
                        <label class="ui-label" for="pay-note">{{ $isFr ? 'Note' : 'Note' }}</label>
                        <input id="pay-note" type="text" name="payment_note" value="{{ $rin->payment_note }}" class="ui-field">
                    </div>
                </div>
                <button type="submit" class="ui-btn ui-btn-primary mt-4">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $isFr ? 'Enregistrer le paiement' : 'Record the payment' }}
                </button>
            </form>
            @endif

            {{-- Buyer confirms or disputes --}}
            @if($vIsBuyer && $realPaid && ! $rin->confirmed_at)
            <hr class="ui-divider">
            <p class="ui-eyebrow mb-3">{{ $isFr ? 'Ce paiement est-il exact ?' : 'Is this record correct?' }}</p>
            <div class="flex flex-wrap items-start gap-3">
                <form method="POST" action="{{ route('quotes.respond-payment', ['invoice' => $rin->id, 'lang' => $lang]) }}">
                    @csrf
                    <input type="hidden" name="response" value="confirm">
                    <button type="submit" class="ui-btn ui-btn-primary">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        {{ $isFr ? 'Je confirme le paiement' : 'I confirm the payment' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('quotes.respond-payment', ['invoice' => $rin->id, 'lang' => $lang]) }}" class="flex flex-wrap items-start gap-2">
                    @csrf
                    <input type="hidden" name="response" value="dispute">
                    <input type="text" name="dispute_reason" required maxlength="500"
                           placeholder="{{ $isFr ? 'Motif de la contestation' : 'Reason for dispute' }}" class="ui-field w-[240px] max-w-full">
                    <button type="submit" class="ui-btn ui-btn-danger">{{ $isFr ? 'Contester' : 'Dispute' }}</button>
                </form>
            </div>
            @endif

            @if($rin->confirmed_at)
            <div class="ui-alert ui-alert-ok mt-4">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ $isFr ? 'Paiement confirmé par l\'acheteur le' : 'Payment confirmed by the buyer on' }}
                {{ \Illuminate\Support\Carbon::parse($rin->confirmed_at)->translatedFormat('d F Y') }}.
            </div>
            @endif
        </section>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Parties / dates card -->
                <section class="ui-card grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ $realBizLogo }}" alt="{{ $realBizName }}" class="w-[86px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="text-[14px] font-bold text-[#1B1B18]">{{ $realBizName }}</span>
                                @if(in_array($biz?->verification_tier, ['verified', 'certified'], true))
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                                @endif
                            </p>
                            @if($biz?->address_fr)
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? $biz->address_fr : ($biz->address_en ?: $biz->address_fr) }}</p>
                            @endif
                            @if($biz?->phone)
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $biz->phone }}</p>
                            @endif
                            @if($biz?->email)
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $biz->email }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="ui-dt">{{ $isFr ? 'Facturé à' : 'Billed to' }}</p>
                        <p class="ui-dd text-[14px]">{{ $realBuyerName }}</p>
                        @if($buyer?->email)
                        <p class="mt-2 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $buyer->email }}</p>
                        @endif
                        @if($buyer?->phone)
                        <p class="mt-2 text-[12.5px] text-[#3B382F]">{{ $buyer->phone }}</p>
                        @endif
                    </div>
                    <div class="flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="ui-dt">{{ $isFr ? 'Date de facture' : 'Invoice date' }}</p>
                            <p class="ui-dd">{{ $realInvDate }}</p>
                            <p class="mt-2.5 ui-dt">{{ $isFr ? 'Date d\'échéance' : 'Due date' }}</p>
                            <p class="ui-dd text-[#E5484D]">{{ $realDueDate ?? '—' }} @if($realDueIn !== null)<span class="font-semibold">({{ $realDueIn }} {{ $isFr ? 'jours' : 'days' }})</span>@endif</p>
                            <p class="mt-2.5 ui-dt">{{ $isFr ? 'Statut' : 'Status' }}</p>
                            <p id="inv-statut" class="text-[12.5px] font-bold tracking-[0.03em] uppercase" style="color:{{ $realPaid ? '#157A43' : '#E5484D' }}">{{ $realPaid ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</p>
                        </div>
                    </div>
                </section>

                <!-- Invoice details -->
                <section class="mt-4 ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Détails de la facture' : 'Invoice details' }}</h2>
                    <div class="mt-3 ui-table-wrap">
                        <table class="ui-table min-w-[820px]">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $isFr ? 'Produit' : 'Product' }}</th>
                                    <th>Description</th>
                                    <th class="text-center">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                    <th class="text-center">{{ $isFr ? 'Unité' : 'Unit' }}</th>
                                    <th class="text-right">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                    <th class="text-right">Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invRows as $ri => [$vImg, $vName, $vDesc, $vQty, $vUnit, $vTotal])
                                <tr>
                                    <td class="align-top text-[#1B1B18]">{{ $ri + 1 }}</td>
                                    <td class="align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $vImg) }}" alt="" class="w-[40px] h-[40px] shrink-0 rounded-lg object-cover">
                                            <p class="text-[13px] font-bold text-[#1B1B18] max-w-[170px]">{{ $vName }}</p>
                                        </div>
                                    </td>
                                    <td class="align-top text-[#3B382F] leading-relaxed max-w-[230px]">{{ $vDesc }}</td>
                                    <td class="align-top text-center font-semibold text-[#1B1B18]">{{ $vQty }}</td>
                                    <td class="align-top text-center text-[#3B382F]">{{ $isFr ? 'Pièces' : 'Pieces' }}</td>
                                    <td class="align-top text-right font-semibold text-[#1B1B18] whitespace-nowrap">{{ $vUnit }}</td>
                                    <td class="align-top text-right font-bold text-[#1B1B18] whitespace-nowrap">{{ $vTotal }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="ui-card-title">Notes</h3>
                            <p class="mt-2.5 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Merci pour votre confiance.' : 'Thank you for your trust.' }}<br>{{ $isFr ? 'Paiement par virement bancaire ou mobile money.' : 'Payment by bank transfer or mobile money.' }}</p>
                        </div>
                        <div>
                            <h3 class="ui-card-title">{{ $isFr ? 'Informations bancaires' : 'Bank information' }}</h3>
                            <dl class="mt-2.5 space-y-1.5">
                                @foreach($bankRows as [$bkLabel, $bkValue])
                                <div class="text-[12px] text-[#3B382F]"><span class="font-semibold">{{ $bkLabel }}:</span> {{ $bkValue }}</div>
                                @endforeach
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-2.5">
                                @foreach($invTotals as [$itLabel, $itValue, $itColor])
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[12.5px] text-[#3B382F]">{{ $itLabel }}</dt>
                                    <dd class="text-[12.5px] font-semibold" style="color:{{ $itColor }}">{{ $itValue }}</dd>
                                </div>
                                @endforeach
                            </dl>
                            <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                                <span class="text-[14.5px] font-bold text-[#157A43]">{{ $isFr ? 'TOTAL À PAYER' : 'TOTAL TO PAY' }}</span>
                                <span class="text-[15.5px] font-bold text-[#157A43]">{{ $realTotal }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Invoice history -->
                <section class="mt-4 ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Historique de la facture' : 'Invoice history' }}</h2>
                    <div class="mt-6 grid" style="grid-template-columns: repeat({{ count($invHistory) }}, minmax(0, 1fr));">
                        @foreach($invHistory as $ihIdx => [$ihTitle, $ihWhen])
                        <div class="text-center">
                            <div class="flex items-center">
                                <span class="flex-1 h-px {{ $ihIdx === 0 ? 'bg-transparent' : 'bg-[#CBE3D2]' }}"></span>
                                <span class="w-[34px] h-[34px] shrink-0 rounded-full flex items-center justify-center {{ $ihIdx === count($invHistory) - 1 ? 'bg-[#1F8A4C]' : 'bg-white border-2 border-[#1F8A4C]' }}">
                                    <i data-lucide="check" class="w-4 h-4 {{ $ihIdx === count($invHistory) - 1 ? 'text-white' : 'text-[#1F8A4C]' }}" style="stroke-width:2.8"></i>
                                </span>
                                <span class="flex-1 h-px {{ $ihIdx === count($invHistory) - 1 ? 'bg-transparent' : 'bg-[#CBE3D2]' }}"></span>
                            </div>
                            <p class="mt-3 text-[12px] font-bold text-[#1B1B18] leading-snug px-1">{{ $ihTitle }}</p>
                            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $ihWhen }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Résumé du paiement' : 'Payment summary' }}</h2>
                    <p class="mt-3.5 ui-dt">{{ $isFr ? 'Montant total' : 'Total amount' }}</p>
                    <p class="mt-0.5 text-[22px] font-bold text-[#157A43]">{{ $realTotal }}</p>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Montant payé' : 'Amount paid' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">{{ $realPaid ? $realTotal : '0 FCFA' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Mode de paiement' : 'Payment method' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $realMethod ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Date de paiement' : 'Payment date' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $realPaidAt ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if($realPaid)
                    <div class="mt-4 bg-[#EFF6F1] rounded-xl px-4 py-3.5 flex items-start gap-3">
                        <i data-lucide="circle-check" class="w-[21px] h-[21px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.8"></i>
                        <p class="text-[12px] leading-relaxed">
                            <span class="font-bold text-[#157A43]">{{ $isFr ? 'Paiement confirmé' : 'Payment confirmed' }}</span><br>
                            <span class="text-[#3B382F]">{{ $isFr ? 'Cette facture a été entièrement payée.' : 'This invoice has been fully paid.' }}</span>
                        </p>
                    </div>
                    @else
                    <div class="mt-4 bg-[#FDF3E0] rounded-xl px-4 py-3.5 flex items-start gap-3">
                        <i data-lucide="clock" class="w-[21px] h-[21px] shrink-0 text-[#C97A16]" style="stroke-width:1.8"></i>
                        <p class="text-[12px] leading-relaxed">
                            <span class="font-bold text-[#C97A16]">{{ $isFr ? 'En attente de paiement' : 'Awaiting payment' }}</span><br>
                            <span class="text-[#3B382F]">{{ $isFr ? 'Cette facture n\'a pas encore été réglée.' : 'This invoice has not been settled yet.' }}</span>
                        </p>
                    </div>
                    @endif
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Documents associés' : 'Associated documents' }}</h2>
                    <div class="mt-3.5 space-y-3.5">
                        @foreach([[$isFr ? 'Devis' : 'Quote', $realQuoRef . '.pdf'], [$isFr ? 'Bon de commande' : 'Purchase order', $realPoRef . '.pdf']] as [$adTitle, $adFile])
                        <div class="flex items-center gap-3.5">
                            <img src="{{ asset('images/landing/qv-pdf-green.png') }}" alt="" class="w-[28px] h-[32px] shrink-0" aria-hidden="true">
                            <span class="min-w-0">
                                <span class="block text-[12.5px] font-bold text-[#1B1B18]">{{ $adTitle }}</span>
                                <span class="block text-[11.5px] text-[#3565DE]">{{ $adFile }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3.5 text-right"><a href="{{ route('orders.index', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#3565DE]">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }}</a></p>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($invActions as [$iaIcon, $iaLabel, $iaUrl])
                        @if($iaUrl)
                        <a href="{{ $iaUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $iaIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $iaLabel }}</span>
                        </a>
                        @else
                        <button type="button" onclick="window.print()" class="w-full flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $iaIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-left text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $iaLabel }}</span>
                        </button>
                        @endif
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
