@php
    use App\Support\CertificateRevocation;

    $isFr = $lang === 'fr';
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $q = trim((string) request()->query('q', ''));

    // Everything the page renders comes from publicList(), which returns four
    // fields and drops the rest. The private columns are not withheld by this
    // template — they never arrive here.
    $entries = CertificateRevocation::publicList($q === '' ? [] : ['q' => $q]);
    $total   = count($entries);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr
        ? 'Liste publique des certificats Artisan Hub 237 retirés, avec leur motif et leur date.'
        : 'Public list of withdrawn Artisan Hub 237 certificates, with the reason and date.' }}">
    <title>{{ $isFr ? 'Certificats révoqués' : 'Revoked certificates' }} — Artisan Hub 237</title>

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
    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#F5F3EE] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[880px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <nav class="flex items-center gap-2 text-[12.5px] mb-5" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificats révoqués' : 'Revoked certificates' }}</span>
    </nav>

    <header class="mb-7">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Registre public' : 'Public register' }}</span>
        <h1 class="mt-3 font-serif text-[26px] sm:text-[34px] font-bold text-[#02301B] dark:text-[#339B56] leading-tight">
            {{ $isFr ? 'Certificats révoqués' : 'Revoked certificates' }}
        </h1>
        <p class="mt-3 text-[13.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed max-w-[640px]">
            {{ $isFr
               ? "Un certificat révoqué est un certificat retiré : il ne doit plus être considéré comme valable, quelle que soit l'apparence du document imprimé. Cette liste est publique afin que toute personne détenant un numéro puisse le vérifier elle-même."
               : 'A revoked certificate has been withdrawn: it should no longer be relied on, however convincing the printed document looks. This list is public so that anyone holding a number can check it themselves.' }}
        </p>
    </header>

    {{-- ── Search by the number on the sheet in the reader's hand ── --}}
    <section class="ui-card p-5 sm:p-6">
        <h2 class="ui-card-title">{{ $isFr ? 'Rechercher un numéro de certificat' : 'Search a certificate number' }}</h2>

        <form method="GET" action="{{ route('revocation.list') }}" class="mt-3">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <label class="ui-label" for="q">{{ $isFr ? 'Numéro de certificat' : 'Certificate number' }}</label>
            <div class="flex flex-col sm:flex-row gap-2 mt-1">
                <input id="q" name="q" type="text" class="ui-field sm:flex-1" maxlength="64"
                       value="{{ $q }}"
                       placeholder="AH237-AVC-CM-2026-0000000000"
                       autocomplete="off" spellcheck="false">
                <button type="submit" class="ui-btn ui-btn-primary">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    {{ $isFr ? 'Vérifier' : 'Check' }}
                </button>
            </div>
            <p class="ui-hint mt-2">
                {{ $isFr
                   ? "Le numéro figure en haut de chaque certificat. L'absence d'un numéro dans cette liste signifie qu'il n'a pas été révoqué — elle ne prouve pas à elle seule que le document est authentique."
                   : 'The number is printed at the top of every certificate. A number missing from this list has not been revoked — that alone does not prove the document is genuine.' }}
            </p>
        </form>
    </section>

    {{-- ── The list ── --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">
            {{ $isFr ? 'Certificats retirés' : 'Withdrawn certificates' }}
            @if($total > 0)
                <span class="text-[#8A857A] dark:text-[#868778] font-normal">({{ $total }})</span>
            @endif
        </h2>

        @if($total === 0)
            {{-- An empty register is good news, and should read as such rather
                 than as a page that failed to load. --}}
            <div class="ui-alert ui-alert-ok mt-3">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>
                    @if($q !== '')
                        {{ $isFr
                           ? "Ce numéro ne figure pas parmi les certificats révoqués."
                           : 'This number is not among the revoked certificates.' }}
                    @else
                        {{ $isFr
                           ? "Aucun certificat n'a été révoqué à ce jour."
                           : 'No certificate has been revoked to date.' }}
                    @endif
                </span>
            </div>
        @else
            <div class="ui-table-wrap mt-3">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ $isFr ? 'Numéro' : 'Number' }}</th>
                            <th>{{ $isFr ? 'Document' : 'Document' }}</th>
                            <th>{{ $isFr ? 'Motif' : 'Reason' }}</th>
                            <th>{{ $isFr ? 'Date de révocation' : 'Revoked on' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                        <tr>
                            <td class="font-mono text-[11.5px] break-all">{{ $entry->certificate_no }}</td>
                            <td>{{ CertificateRevocation::typeLabel($entry->certificate_type, $lang) }}</td>
                            <td>{{ CertificateRevocation::reasonLabel($entry->reason, $lang) }}</td>
                            <td class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($entry->revoked_at)->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="ui-hint mt-3">
                {{ $isFr
                   ? "Cette liste indique le numéro, le type de document, le motif et la date. Elle ne publie ni les détails du dossier, ni l'agent qui a prononcé la révocation, ni les données personnelles du titulaire."
                   : 'This list gives the number, the document type, the reason and the date. It publishes neither the case detail, nor the officer who ordered the revocation, nor the holder’s personal data.' }}
            </p>
        @endif
    </section>

    {{-- ── What it means, and what to do ── --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Que signifie une révocation ?' : 'What a revocation means' }}</h2>
        <p class="mt-2 text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? "Une révocation annule le certificat à compter de la date indiquée. Le document imprimé reste ce qu'il est — du papier — mais l'enregistrement auquel il renvoie ne le confirme plus. Les motifs vont de l'erreur administrative, qui n'implique aucune faute, à la fraude ou à la falsification, qui sont graves."
               : 'A revocation cancels the certificate from the date shown. The printed document remains what it is — paper — but the record it points at no longer stands behind it. Reasons range from an administrative error, which implies no wrongdoing at all, to fraud or forgery, which are serious.' }}
        </p>
        <p class="mt-3 text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? "Si le numéro que vous détenez figure ci-dessus : ne vous fondez pas sur ce certificat pour un achat, une assurance, une expédition ou une expertise, et contactez la partie qui vous l'a remis. Si vous pensez que la révocation est une erreur, écrivez-nous — une révocation prononcée à tort est réversible, et la réversion est elle aussi consignée."
               : 'If the number you hold appears above: do not rely on that certificate for a purchase, an insurance claim, a shipment or an appraisal, and go back to whoever gave it to you. If you believe the revocation is a mistake, write to us — a revocation entered in error can be reversed, and the reversal is recorded too.' }}
        </p>
        <div class="flex flex-wrap gap-2 mt-4">
            <a href="{{ route('certificate.verify', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                {{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }}
            </a>
            <a href="{{ route('ca.page', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                {{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}
            </a>
        </div>
    </section>

</main>

@include('pages.partials.directory-footer')

<script>lucide.createIcons();</script>
</body>
</html>
