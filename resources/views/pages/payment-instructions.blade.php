@php
    use App\Support\ManualPayment;

    /*
     * How to pay the platform's own fee.
     *
     * Two things on this page can do real harm, and everything below is arranged
     * around them.
     *
     * The first is the account number. If nobody has filled it in and the page
     * renders the field anyway — blank, or with something that looks like a
     * number — a member will read it off the screen and send real money to a
     * stranger or into nowhere. There is no safe placeholder value. So the
     * unconfigured case is not an empty box, it is the absence of the box, plus
     * a plain sentence saying we are not able to take payment yet and where to
     * ask. ManualPayment::methods() has already dropped anything unconfigured;
     * this template simply never invents a fallback for what it hands back.
     *
     * The second is the wording around reporting. Telling us you have paid is a
     * claim. Nothing is granted by it. A page that says "payment received" the
     * moment the form is submitted has taught the member something false about
     * where they stand, and they will find out at the worst moment. The notice
     * is therefore next to the button, not in a footnote.
     *
     * Deliberately public and unauthenticated: a registration fee is frequently
     * owed by somebody who has no account yet, and a payer often forwards this
     * page to whoever actually holds the mobile-money account. It is written to
     * be safe in a stranger's hands — it names one payment, grants nothing, and
     * reveals nothing about any other.
     */

    $isFr = $lang === 'fr';

    // The shared directory header reads both of these off the page rather than
    // taking them as parameters, so they have to exist before it is included.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    // Configured AND active. An unconfigured method is not represented here at
    // all, which is what makes the "no number can leak" property structural
    // rather than a matter of remembering to write @if around every echo.
    $methods = ManualPayment::methods($lang);
    $windowDays = (int) config('payments.confirmation_window_days');

    $purposeLabels = [
        'registration'        => $isFr ? "Frais d'inscription"          : 'Registration fee',
        'membership'          => $isFr ? 'Adhésion'                      : 'Membership',
        'renewal'             => $isFr ? 'Renouvellement'                : 'Renewal',
        'verification'        => $isFr ? 'Vérification'                  : 'Verification',
        'workshop_inspection' => $isFr ? "Inspection d'atelier"          : 'Workshop inspection',
        'other'               => $isFr ? 'Frais de plateforme'           : 'Platform fee',
    ];

    /* Status in words. Every one of these is written from the platform's side of
       the ignorance: "reported" is what somebody told us, not what happened. */
    $statusLabels = [
        'awaiting_payment' => $isFr ? 'En attente de votre paiement'      : 'Awaiting your payment',
        'reported'         => $isFr ? 'Signalé, en attente de contrôle'   : 'Reported, awaiting checking',
        'under_review'     => $isFr ? 'Contrôle en cours'                 : 'Being checked',
        'confirmed'        => $isFr ? 'Confirmé par un examinateur'       : 'Confirmed by a reviewer',
        'rejected'         => $isFr ? 'Rejeté'                            : 'Rejected',
        'cancelled'        => $isFr ? 'Annulé'                            : 'Cancelled',
        'expired'          => $isFr ? 'Expiré'                            : 'Expired',
    ];

    // report() accepts only these two states, so the form appears for exactly
    // those and no others. Showing a form the engine will refuse would waste a
    // payer's time and produce an error page after they had already sent money.
    $canReport = $payment && in_array($payment->status, ['awaiting_payment', 'rejected'], true);

    $pageTitle = $isFr ? 'Régler des frais de plateforme' : 'Pay a platform fee';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- noindex: a reference in a search result is a reference in a stranger's hands. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }} — Artisan Hub 237</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 229 168 46;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>
    @include('pages.partials.icons')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        /* The reference is the only link between an MTN transaction record and
           a row in our database, and it gets typed on a keypad by somebody
           standing in a queue. Big, spaced, and monospaced so 0 and O cannot be
           confused, which is a transcription error that loses somebody's money. */
        .pay-ref {
            font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #02301B;
            word-break: break-all;
        }
        .pay-number {
            font-family: ui-monospace, "SFMono-Regular", Menlo, Consolas, monospace;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #02301B;
        }
        @media (min-width: 640px) { .pay-ref { font-size: 28px; } .pay-number { font-size: 22px; } }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F5F3EE] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[760px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <header class="mb-7">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Frais de plateforme' : 'Platform fee' }}</span>
        <h1 class="mt-3 font-serif text-[26px] sm:text-[34px] font-bold text-[#02301B] dark:text-[#339B56] leading-tight">
            {{ $pageTitle }}
        </h1>
        <p class="mt-3 text-[15px] md:text-[13.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? "Cette page concerne uniquement les frais que la plateforme facture pour ses propres services : inscription, adhésion, renouvellement, vérification. Elle ne concerne jamais l'achat d'une pièce à un artisan : la plateforme n'est pas partie à ces ventes et n'encaisse aucun prix de produit."
               : 'This page is only about fees the platform charges for its own services: registration, membership, renewal, verification. It is never about buying a piece from an artisan: the platform is not a party to those sales and receives no product price.' }}
        </p>
    </header>

    @if(session('payment_reported'))
        {{-- Acknowledges receipt of the CLAIM. It must not read as a receipt for
             the money, which is why it says what happens next rather than "thank
             you for your payment". --}}
        <div class="ui-alert ui-alert-ok mb-5" role="status">
            <i data-lucide="check" class="w-4 h-4"></i>
            <span>
                {{ $isFr
                   ? "Votre déclaration est enregistrée. Elle sera vérifiée par une personne de l'équipe ; rien n'est encore accordé."
                   : 'Your report is recorded. Somebody on the team will check it; nothing is granted yet.' }}
            </span>
        </div>
    @endif

    {{-- ───────────── 1. The payment itself ───────────── --}}
    @if($payment)
    <section class="ui-card p-5 sm:p-6 mb-5" data-payment-reference="{{ $payment->reference }}">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="ui-card-title">{{ $purposeLabels[$payment->purpose] ?? $purposeLabels['other'] }}</h2>
                <p class="ui-hint">{{ $isFr ? 'Ouvert le' : 'Opened on' }}
                   {{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d/m/Y') }}</p>
            </div>
            <span class="ui-pill {{ $payment->status === 'confirmed' ? 'ui-pill-ok' : ($payment->status === 'rejected' ? 'ui-pill-danger' : 'ui-pill-neutral') }}"
                  data-payment-status="{{ $payment->status }}">
                {{ $statusLabels[$payment->status] ?? $payment->status }}
            </span>
        </div>

        <dl class="ui-dl mt-4">
            <dt class="ui-dt">{{ $isFr ? 'Montant à envoyer' : 'Amount to send' }}</dt>
            {{-- Printed from the record. Never recomputed here: a view that
                 recalculated a fee could disagree with what the reviewer sees. --}}
            <dd class="ui-dd">
                <strong>{{ number_format((float) $payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</strong>
            </dd>
            @if($payment->expires_at)
            <dt class="ui-dt">{{ $isFr ? 'À régler avant le' : 'To be settled before' }}</dt>
            <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($payment->expires_at)->format('d/m/Y') }}</dd>
            @endif
        </dl>

        {{-- The reference. The single most important string on the page. --}}
        <div class="mt-5 rounded-[12px] border border-[#EFEBE2] dark:border-[#262B21] bg-[#FBF9F4] dark:bg-[#0A0C09] p-4">
            <span class="ui-eyebrow">{{ $isFr ? 'Référence à saisir dans le motif' : 'Reference to type in the reason field' }}</span>
            <p class="pay-ref mt-2" id="payref">{{ $payment->reference }}</p>
            <p class="ui-hint">
                {{ $isFr
                   ? "Sans cette référence dans le motif du transfert, nous ne pouvons pas rattacher votre versement à votre dossier."
                   : 'Without this reference in the transfer reason, we cannot match your payment to your file.' }}
            </p>
            <button type="button" class="ui-btn ui-btn-secondary ui-btn-sm mt-3"
                    data-copy="{{ $payment->reference }}">
                <i data-lucide="copy" class="w-4 h-4"></i>
                {{ $isFr ? 'Copier la référence' : 'Copy the reference' }}
            </button>
        </div>

        @if($payment->status === 'rejected' && $payment->rejection_reason)
        <div class="ui-alert ui-alert-danger mt-4">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <span>
                <strong>{{ $isFr ? 'Motif du rejet :' : 'Reason for the rejection:' }}</strong>
                {{ $payment->rejection_reason }}
            </span>
        </div>
        @endif
    </section>
    @endif

    {{-- ───────────── 2. Where to send it ───────────── --}}
    <section class="ui-card p-5 sm:p-6 mb-5"
             data-payment-methods="{{ $methods === [] ? 'none' : count($methods) }}">
        <h2 class="ui-card-title">{{ $isFr ? 'Où envoyer l\'argent' : 'Where to send the money' }}</h2>

        @if($methods === [])
            {{-- NOTHING resembling an account appears in this branch. Not a blank
                 field, not an example, not a greyed-out number. A member reading
                 a payment page will fill a gap with a guess, and a guessed
                 mobile-money number belongs to a real stranger. --}}
            <div class="ui-alert ui-alert-warn mt-3">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>
                    {{ $isFr
                       ? "Aucun moyen de paiement n'est configuré pour le moment. Nous ne pouvons donc pas encore recevoir de règlement, et aucun numéro ne peut être affiché ici. N'envoyez d'argent à aucun numéro que vous auriez vu ailleurs : contactez-nous et nous vous indiquerons le compte officiel."
                       : 'No payment method is configured at the moment. We therefore cannot yet receive a settlement, and no number can be shown here. Do not send money to any number you have seen elsewhere: contact us and we will give you the official account.' }}
                </span>
            </div>
            <a href="{{ route('support.index', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary ui-btn-sm mt-4">
                <i data-lucide="life-buoy" class="w-4 h-4"></i>
                {{ $isFr ? 'Nous contacter' : 'Contact us' }}
            </a>
        @else
            <p class="ui-hint">
                {{ $isFr
                   ? "Envoyez le montant exact à l'un des comptes ci-dessous depuis votre propre téléphone, en indiquant la référence dans le motif."
                   : 'Send the exact amount to one of the accounts below from your own phone, putting the reference in the reason field.' }}
            </p>

            <div class="mt-4 space-y-3">
                @foreach($methods as $method)
                <div class="rounded-[12px] border border-[#EFEBE2] dark:border-[#262B21] p-4" data-method="{{ $method['code'] }}">
                    <p class="text-[13px] font-semibold text-[#02301B] dark:text-[#339B56]">{{ $method['label'] }}</p>

                    @if($method['kind'] !== 'cash' && filled($method['number']))
                        {{-- data-account-number is asserted absent by the test for
                             the unconfigured case; it exists only where a real
                             configured value is being printed. --}}
                        <p class="pay-number mt-2" data-account-number>{{ $method['number'] }}</p>
                    @endif

                    @if(filled($method['holder']))
                    <p class="ui-hint">
                        {{ $isFr ? 'Au nom de' : 'In the name of' }} : <strong>{{ $method['holder'] }}</strong>
                    </p>
                    @endif

                    @if(filled($method['instructions']))
                    <p class="ui-hint">{{ $method['instructions'] }}</p>
                    @endif

                    @if($method['kind'] !== 'cash' && filled($method['number']))
                    <button type="button" class="ui-btn ui-btn-secondary ui-btn-sm mt-3"
                            data-copy="{{ $method['number'] }}">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        {{ $isFr ? 'Copier le numéro' : 'Copy the number' }}
                    </button>
                    @endif
                </div>
                @endforeach
            </div>

            <p class="ui-hint mt-4">
                {{ $isFr
                   ? "Vérifiez le nom du titulaire affiché par votre opérateur avant de valider. S'il ne correspond pas à celui indiqué ci-dessus, arrêtez-vous et écrivez-nous."
                   : 'Check the holder name your operator shows you before you confirm. If it does not match the one above, stop and write to us.' }}
            </p>
        @endif
    </section>

    {{-- ───────────── 3. Report the payment ───────────── --}}
    @if($payment && $canReport && $methods !== [])
    <section class="ui-card p-5 sm:p-6 mb-5">
        <h2 class="ui-card-title">{{ $isFr ? "J'ai envoyé l'argent" : 'I have sent the money' }}</h2>

        {{-- The honesty notice, placed above the fields rather than below the
             button, because it changes what the person is about to do. --}}
        <div class="ui-alert ui-alert-warn mt-3" data-claim-notice>
            <i data-lucide="info" class="w-4 h-4"></i>
            <span>
                {{ $isFr
                   ? "Ce formulaire enregistre une déclaration, pas une confirmation. Rien ne vous est accordé tant qu'une personne de l'équipe n'a pas vérifié le versement auprès de l'opérateur. Ce contrôle prend habituellement jusqu'à {$windowDays} jours."
                   : "This form records a claim, not a confirmation. Nothing is granted to you until somebody on the team has checked the transfer against the operator's records. That check usually takes up to {$windowDays} days." }}
            </span>
        </div>

        <form method="POST" action="{{ route('payment.report', ['reference' => $payment->reference]) }}"
              enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">

            <div>
                <label class="ui-label" for="payer_name">
                    {{ $isFr ? 'Nom de la personne qui a payé' : 'Name of the person who paid' }}<span class="ui-req">*</span>
                </label>
                <input id="payer_name" name="payer_name" type="text" maxlength="190" required
                       class="ui-field @error('payer_name') ui-field--invalid @enderror"
                       value="{{ old('payer_name') }}" autocomplete="name">
                <p class="ui-hint">
                    {{ $isFr
                       ? "Tel qu'il apparaît chez l'opérateur. Ce n'est pas forcément vous : beaucoup de versements sont faits depuis le compte d'un proche."
                       : "As it appears with the operator. It need not be you: many transfers are made from a relative's account." }}
                </p>
                @error('payer_name')<p class="ui-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-label" for="payer_number">{{ $isFr ? 'Numéro utilisé pour payer' : 'Number used to pay' }}</label>
                <input id="payer_number" name="payer_number" type="tel" maxlength="40"
                       class="ui-field @error('payer_number') ui-field--invalid @enderror"
                       value="{{ old('payer_number') }}" autocomplete="tel">
                @error('payer_number')<p class="ui-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-label" for="payer_reference">
                    {{ $isFr ? "Référence de transaction de l'opérateur" : "Operator's transaction reference" }}
                </label>
                <input id="payer_reference" name="payer_reference" type="text" maxlength="80"
                       class="ui-field @error('payer_reference') ui-field--invalid @enderror"
                       value="{{ old('payer_reference') }}" autocomplete="off" spellcheck="false">
                <p class="ui-hint">
                    {{ $isFr
                       ? "Le code figurant dans le SMS de confirmation de MTN ou d'Orange. C'est ce qui permet de retrouver votre versement le plus vite."
                       : 'The code in the MTN or Orange confirmation SMS. It is what finds your transfer fastest.' }}
                </p>
                @error('payer_reference')<p class="ui-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ui-label" for="proof">{{ $isFr ? 'Justificatif (facultatif)' : 'Proof (optional)' }}</label>
                <input id="proof" name="proof" type="file" accept="image/*,application/pdf"
                       class="ui-field @error('proof') ui-field--invalid @enderror">
                <p class="ui-hint">
                    {{ $isFr
                       ? "Une photo du SMS de confirmation suffit. Elle n'est vue que par la personne qui contrôle votre dossier."
                       : 'A photo of the confirmation SMS is enough. It is seen only by the person checking your file.' }}
                </p>
                @error('proof')<p class="ui-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="ui-btn ui-btn-primary">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ $isFr ? "Déclarer ce paiement" : 'Report this payment' }}
            </button>
        </form>
    </section>
    @elseif($payment && ! $canReport)
    <section class="ui-card p-5 sm:p-6 mb-5">
        <h2 class="ui-card-title">{{ $isFr ? 'Où en est ce paiement' : 'Where this payment stands' }}</h2>
        <p class="ui-body mt-2">
            @switch($payment->status)
                @case('reported')
                @case('under_review')
                    {{ $isFr
                       ? "Vous avez déjà déclaré ce versement. Une personne de l'équipe doit maintenant le retrouver dans les relevés de l'opérateur. Il n'y a rien d'autre à faire de votre côté."
                       : 'You have already reported this transfer. Somebody on the team now has to find it in the operator statements. There is nothing else for you to do.' }}
                    @break
                @case('confirmed')
                    {{ $isFr
                       ? "Un examinateur a confirmé avoir retrouvé ce versement. C'est cette confirmation humaine, et elle seule, qui règle ces frais."
                       : 'A reviewer confirmed finding this transfer. That human confirmation, and only it, settles this fee.' }}
                    @break
                @case('expired')
                    {{ $isFr
                       ? "Ce règlement a expiré sans être confirmé. Rien ne vous a été retiré et rien ne vous a été accordé ; écrivez-nous pour en rouvrir un."
                       : 'This settlement expired without being confirmed. Nothing was taken from you and nothing was granted; write to us to open a new one.' }}
                    @break
                @default
                    {{ $isFr
                       ? "Ce règlement est clos. Écrivez-nous si vous pensez que c'est une erreur."
                       : 'This settlement is closed. Write to us if you think that is a mistake.' }}
            @endswitch
        </p>
        <a href="{{ route('support.index', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
            <i data-lucide="life-buoy" class="w-4 h-4"></i>
            {{ $isFr ? 'Nous écrire' : 'Write to us' }}
        </a>
    </section>
    @endif

    {{-- ───────────── 4. How this works, stated once ───────────── --}}
    <section class="ui-card p-5 sm:p-6">
        <h2 class="ui-card-title">{{ $isFr ? 'Comment fonctionne le règlement' : 'How settlement works' }}</h2>
        <ol class="mt-3 space-y-2 text-[15px] md:text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed list-decimal pl-5">
            <li>{{ $isFr ? "Vous envoyez le montant exact au compte indiqué, en mettant la référence dans le motif."
                          : 'You send the exact amount to the account shown, putting the reference in the reason field.' }}</li>
            <li>{{ $isFr ? "Vous nous le déclarez depuis cette page. C'est une déclaration : elle n'accorde rien."
                          : 'You report it to us from this page. That is a claim: it grants nothing.' }}</li>
            <li>{{ $isFr ? "Une personne de l'équipe cherche votre versement dans les relevés de l'opérateur."
                          : 'Somebody on the team looks for your transfer in the operator statements.' }}</li>
            <li>{{ $isFr ? "Si elle le trouve, elle confirme, et le règlement prend effet. Sinon, elle rejette en vous disant pourquoi."
                          : 'If they find it, they confirm and the settlement takes effect. If not, they reject it and tell you why.' }}</li>
        </ol>
        <p class="ui-hint mt-4">
            {{ $isFr
               ? "Il n'y a aucune passerelle de paiement automatique : l'argent circule par mobile money, en dehors de nos systèmes. Nous ne pouvons donc jamais prétendre savoir qu'un versement est arrivé avant qu'une personne ne l'ait constaté."
               : 'There is no automatic payment gateway: the money moves by mobile money, entirely outside our systems. So we can never claim to know a transfer arrived before a person has seen it.' }}
        </p>
    </section>

</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();

    /* Copy-to-clipboard for the reference and the account numbers. Mistyping
       either is how somebody's money gets lost, and this is a phone-first
       audience. Falls back silently where the clipboard API is unavailable —
       the value is on screen and selectable either way. */
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var value = btn.getAttribute('data-copy');
            if (!navigator.clipboard) return;
            navigator.clipboard.writeText(value).then(function () {
                var original = btn.innerHTML;
                btn.textContent = @json($isFr ? 'Copié' : 'Copied');
                setTimeout(function () { btn.innerHTML = original; lucide.createIcons(); }, 1500);
            });
        });
    });
</script>
</body>
</html>
