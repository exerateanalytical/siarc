@php
    /*
     * The certificate hub: every document the register holds for one product,
     * and — the part that makes the page worth having — every document it does
     * not hold, with the reason.
     *
     * A buyer is handed one certificate. Nothing on that sheet tells them the
     * family has five other members, and nothing tells them which of the five
     * this piece actually has. Before this page the only way to find out was to
     * guess a URL. So the hub is written as an index of the register rather
     * than as a marketing page: an absent document is given the same amount of
     * room as a present one, because "no export certificate has been requested"
     * is a fact a buyer at a border very much wants before they travel.
     *
     * The route hands us only $lang and $product, so everything else is read
     * here. That is a deliberate trade: the gathering is the page's whole
     * substance, and putting it in the view keeps it beside the markup that
     * shows it, where a reviewer can see that each printed line has a query
     * behind it. The cost is that the view does database work — acceptable for
     * a page that is one product deep and rate-limited at the route.
     */

    $isFr       = $lang === 'fr';
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $business = $product->business;

    /* ── The permanent identifiers ───────────────────────────────────────
       These are what tie the set together: every document below quotes at
       least one of them, and they are the numbers a museum or an insurer
       would use to pull the file. They are assigned on first read, so this
       is also what registers a product that has never been looked at. */
    $prn = \App\Support\ProvenanceRegistry::prnFor($product);
    $oln = \App\Support\ProvenanceRegistry::olnFor($product);
    $gan = $business ? \App\Support\ProvenanceRegistry::ganFor($business) : null;

    /* ── The records behind each document ───────────────────────────────── */
    $coa = \App\Support\ProductCertificate::forProduct($product);

    // Null is the ordinary answer here, not an error: an artisan whose identity
    // nobody has checked has no verification certificate, and the register is
    // right to refuse to issue one. The page has to say so in words.
    $avc = $business ? \App\Support\ArtisanVerification::forBusiness($business) : null;

    $transfers = \Illuminate\Support\Facades\DB::table('ownership_transfers')
        ->where('product_id', $product->id)->orderBy('id')->get()->all();

    $consignments = \Illuminate\Support\Facades\DB::table('export_consignments')
        ->where('product_id', $product->id)->orderBy('id')->get()->all();

    $chain   = \App\Support\ProvenanceRegistry::chain($product);
    $owner   = \App\Support\ProvenanceRegistry::currentOwner($product);
    $summary = \App\Support\ProvenanceDossier::summary($product);
    $legacy  = \App\Support\ProvenanceDossier::legacyIndex($product);
    $flags   = \App\Support\ProductFlags::checks($product);

    /*
     * Signature state is read through signatureState() rather than verify().
     * verify() is the buyer-facing check and it writes: it bumps the
     * verification counter and stamps last_verified_at. A listing page that
     * silently logged a verification every time anyone glanced at it would
     * corrupt the one statistic on the certificate that means something.
     */
    $coaSignature = $coa ? \App\Support\ProductCertificate::signatureState($coa) : null;

    // The locale is set per call rather than taken from the app, because the
    // page's language comes from the query string and the app locale does not
    // follow it — without this an English reader gets "28 juillet 2026".
    $date = function ($value) use ($isFr) {
        return $value
            ? \Illuminate\Support\Carbon::parse($value)->locale($isFr ? 'fr' : 'en')->translatedFormat('j F Y')
            : null;
    };

    /*
     * Only one public verification address exists on this platform today: the
     * product certificate verifier at /verifier. There is no published endpoint
     * for a transfer, an export consignment or an artisan verification, so
     * those rows link their document and say plainly that no verification
     * address is published yet. Pointing them at the product verifier would
     * return "not found" for a perfectly valid document, which is worse than an
     * honest gap.
     */
    $verifyUrl = fn (string $no) => '/verifier?ref=' . $no . ($isFr ? '' : '&lang=en');
    $q         = $isFr ? '' : '?lang=en';

    $rows = [];

    /* ── COA ─────────────────────────────────────────────────────────────
       Issued lazily on first read of a published product, so for anything
       reachable at this URL it exists — unless it has been revoked. */
    $rows[] = [
        'code'   => 'COA',
        'issued' => (bool) $coa && ! $coa->revoked_at,
        'number' => $coa?->certificate_no,
        'date'   => $date($coa?->issued_at),
        'doc'    => $coa ? '/certificat/' . $product->slug . $q : null,
        'verify' => $coa ? $verifyUrl($coa->certificate_no) : null,
        'state'  => $coa && $coa->revoked_at
            ? ($isFr ? 'Révoqué' : 'Revoked')
            : ($coaSignature && $coaSignature['state'] === 'valid'
                ? ($isFr ? 'Signature valide' : 'Signature valid')
                : ($isFr ? 'Émis' : 'Issued')),
        'ok'     => ! ($coa && $coa->revoked_at),
        'reason' => $coa ? null : ($isFr
            ? "Aucun certificat d'authenticité n'a été émis pour cette pièce."
            : 'No certificate of authenticity has been issued for this piece.'),
    ];

    /* ── PRC and PPC ─────────────────────────────────────────────────────
       Neither is a stored row: both are views generated over the register
       entry itself, so they exist for as long as the entry is published and
       carry the entry's own numbers rather than a number of their own. Their
       verification is therefore the underlying entry's — the COA — and that
       is what the link says. */
    $rows[] = [
        'code'   => 'PRC',
        'issued' => true,
        'number' => $prn,
        'date'   => $date($product->registered_at ?? $product->created_at),
        'doc'    => '/certificat-enregistrement/' . $product->slug . $q,
        'verify' => $coa ? $verifyUrl($coa->certificate_no) : null,
        'state'  => $isFr ? 'Généré depuis le registre' : 'Generated from the register',
        'ok'     => true,
        'reason' => null,
        'note'   => $isFr
            ? "Document généré à partir de l'entrée au registre. Il porte le numéro d'enregistrement du produit, pas un numéro propre."
            : 'Generated from the registry entry. It carries the product registration number rather than a number of its own.',
    ];

    $rows[] = [
        'code'   => 'PPC',
        'issued' => true,
        'number' => $oln,
        'date'   => $date($product->registered_at ?? $product->created_at),
        'doc'    => '/certificat-provenance/' . $product->slug . $q,
        'verify' => $coa ? $verifyUrl($coa->certificate_no) : null,
        'state'  => $isFr ? 'Généré depuis le registre' : 'Generated from the register',
        'ok'     => true,
        'reason' => null,
        'note'   => $isFr
            ? "Dossier généré à partir du registre de provenance. Il porte le numéro du grand livre de propriété."
            : 'A dossier generated from the provenance register. It carries the ownership ledger number.',
    ];

    /* ── AVC ─────────────────────────────────────────────────────────────
       About the maker, not the piece — the only row here whose subject is a
       person. Its absence is the commonest absence on the platform and the
       one most easily misread, so the reason names the missing step. */
    $rows[] = [
        'code'   => 'AVC',
        'issued' => (bool) $avc,
        'number' => $avc?->certificate_no,
        'date'   => $date($avc?->issued_at),
        'doc'    => $avc && $business ? '/certificat-artisan/' . $business->slug . $q : null,
        'verify' => $avc ? $verifyUrl($avc->certificate_no) : null,
        'state'  => $avc ? ($isFr ? 'Actif' : 'Active') : null,
        'ok'     => true,
        'reason' => $avc ? null : ($isFr
            ? "L'identité de cet artisan n'a pas encore été vérifiée par la plateforme, donc aucun certificat de vérification n'existe. Ce n'est pas un jugement sur son travail."
            : 'This artisan’s identity has not yet been verified by the platform, so no verification certificate exists. That is not a judgement on the work.'),
        'extra'  => $avc ? ($isFr ? 'Niveau ' : 'Level ') . $avc->level : null,
    ];

    /* ── OTC ─────────────────────────────────────────────────────────────
       One per change of hands. A piece still with its maker has none, and
       that is a complete and unremarkable answer. */
    $latestTransfer = $transfers ? end($transfers) : null;
    $rows[] = [
        'code'   => 'OTC',
        'issued' => (bool) $latestTransfer,
        'number' => $latestTransfer?->certificate_no,
        'date'   => $date($latestTransfer?->issued_at),
        'doc'    => $latestTransfer ? '/certificat-transfert/' . $latestTransfer->certificate_no . $q : null,
        'verify' => $latestTransfer ? $verifyUrl($latestTransfer->certificate_no) : null,
        'state'  => $latestTransfer
            ? ($latestTransfer->status === 'active'
                ? ($isFr ? 'En vigueur' : 'In force')
                : ($isFr ? 'Remplacé' : 'Superseded'))
            : null,
        'ok'     => ! $latestTransfer || $latestTransfer->status === 'active',
        'reason' => $latestTransfer ? null : ($isFr
            ? "Aucun transfert de propriété n'a été enregistré : la pièce est toujours détenue par son créateur."
            : 'No ownership transfer has been recorded: the piece is still held by its maker.'),
        'count'  => count($transfers),
    ];

    /* ── EAC ─────────────────────────────────────────────────────────────
       Opened as a draft with its number already assigned, and signed later.
       The two states are genuinely different to a customs officer, so the row
       distinguishes them instead of calling an open draft "issued". */
    $latestConsignment = $consignments ? end($consignments) : null;
    $rows[] = [
        'code'   => 'EAC',
        'issued' => (bool) ($latestConsignment && $latestConsignment->issued_at),
        'number' => $latestConsignment && $latestConsignment->issued_at ? $latestConsignment->certificate_no : null,
        'date'   => $date($latestConsignment?->issued_at),
        'doc'    => $latestConsignment && $latestConsignment->issued_at
            ? '/certificat-export/' . $latestConsignment->certificate_no . $q : null,
        'verify' => $latestConsignment && $latestConsignment->issued_at
            ? $verifyUrl($latestConsignment->certificate_no) : null,
        'state'  => $latestConsignment && $latestConsignment->issued_at
            ? ($isFr ? 'Émis' : 'Issued') : null,
        'ok'     => true,
        'reason' => $latestConsignment
            ? ($latestConsignment->issued_at ? null : ($isFr
                ? "Une expédition à l'export est ouverte pour cette pièce mais le certificat n'a pas encore été signé."
                : 'An export consignment is open for this piece, but no export certificate has been signed yet.'))
            : ($isFr
                ? "Aucun certificat d'exportation n'a été demandé pour cette pièce."
                : 'No export certificate has been requested for this piece.'),
        'count'  => count($consignments),
    ];

    $types = config('certificate_types');

    // The three concerns the register is authoritative over. A key that is
    // absent means the platform cannot determine the answer, and the loop below
    // never invents a row for one — printing an unticked box would be a claim.
    $flagLabels = [
        'not_reported_stolen'     => ['fr' => 'Non signalée volée', 'en' => 'Not reported stolen'],
        'not_under_investigation' => ['fr' => "Aucune enquête en cours", 'en' => 'No investigation on record'],
        'ownership_undisputed'    => ['fr' => 'Propriété non contestée', 'en' => 'Ownership undisputed'],
    ];

    $summaryLabels = [
        'ownership_changes' => ['fr' => 'Changements de propriétaire', 'en' => 'Ownership changes'],
        'events'            => ['fr' => 'Événements documentés', 'en' => 'Documented events'],
        'evidence'          => ['fr' => 'Pièces justificatives', 'en' => 'Evidence items'],
        'exhibitions'       => ['fr' => 'Expositions', 'en' => 'Exhibitions'],
        'publications'      => ['fr' => 'Publications', 'en' => 'Publications'],
        'countries'         => ['fr' => 'Pays traversés', 'en' => 'Countries recorded'],
    ];

    $productName = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $makerName   = $business ? ($isFr ? $business->name_fr : ($business->name_en ?: $business->name_fr)) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="description" content="{{ $isFr
        ? 'Tous les certificats que le registre détient pour cette pièce, et ceux qu’il ne détient pas.'
        : 'Every certificate the register holds for this piece, and every one it does not.' }}">
    <title>{{ $isFr ? 'Certificats' : 'Certificates' }} — {{ $productName }} — Artisan Hub 237</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { leaf: '#164C28', deepfc: '#02301B', gold: '#E5A82E' },
            fontFamily: { sans: ['Poppins','system-ui','sans-serif'], serif: ['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        /* The classification band is drawn absolutely against its host, so each
           certificate card becomes the host and reserves the band's 40px on its
           left. The width is the partial's to own, not ours. */
        /* !important because the card's own Tailwind padding utility sets
           padding-left too, and Tailwind's sheet is injected after this one —
           without it the band lands on top of the certificate name. The band's
           40px is the partial's to own; the rest is breathing room. */
        .hub-row { position: relative; padding-left: 62px !important; }
        .hub-ident { font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
                     font-size: 12.5px; letter-spacing: .02em; word-break: break-all; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F5F3EE] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[880px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <nav class="flex items-center gap-2 text-[12.5px] mb-5" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="/galerie/produits/{{ $product->slug }}{{ $q }}" class="text-[#6F6B60] hover:text-leaf">{{ $productName }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificats' : 'Certificates' }}</span>
    </nav>

    <header class="mb-7">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Registre' : 'Register' }}</span>
        <h1 class="mt-3 font-serif text-[26px] sm:text-[34px] font-bold text-[#02301B] leading-tight">
            {{ $isFr ? 'Certificats de cette pièce' : 'Certificates for this piece' }}
        </h1>
        <p class="mt-2 text-[14px] text-[#3A3A35]">
            {{ $productName }}@if($makerName) — {{ $makerName }}@endif
        </p>
        <p class="mt-3 text-[13.5px] text-[#3A3A35] leading-relaxed max-w-[640px]">
            {{ $isFr
               ? "Cette page liste les six documents que la plateforme peut émettre pour une pièce et son artisan, avec leur état réel. Un document absent est indiqué comme absent, avec la raison : c'est aussi utile à savoir qu'un document présent."
               : 'This page lists the six documents the platform can issue for a piece and its maker, with their real state. A document that does not exist is shown as absent, with the reason — which is as useful to know as one that does.' }}
        </p>
    </header>

    {{-- ── The permanent identifiers ─────────────────────────────────────
         Shown once and near the top, because every document below quotes one
         of them and a reader who copies the wrong number gets nowhere. --}}
    <section class="ui-card p-5 sm:p-6">
        <h2 class="ui-card-title">{{ $isFr ? 'Identifiants permanents' : 'Permanent identifiers' }}</h2>
        <p class="mt-1 text-[12.5px] text-[#6F6B60]">
            {{ $isFr
               ? "Attribués une fois et jamais réutilisés. Ils relient tous les documents ci-dessous."
               : 'Assigned once and never reused. They are what ties every document below together.' }}
        </p>
        <dl class="mt-4 grid sm:grid-cols-3 gap-4">
            <div>
                <dt class="ui-dt">{{ $isFr ? "Numéro d'enregistrement (PRN)" : 'Registration number (PRN)' }}</dt>
                <dd class="ui-dd hub-ident">{{ $prn }}</dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Grand livre de propriété (OLN)' : 'Ownership ledger (OLN)' }}</dt>
                <dd class="ui-dd hub-ident">{{ $oln }}</dd>
            </div>
            @if($gan)
                <div>
                    <dt class="ui-dt">{{ $isFr ? "Numéro d'artisan (GAN)" : 'Artisan number (GAN)' }}</dt>
                    <dd class="ui-dd hub-ident">{{ $gan }}</dd>
                </div>
            @endif
        </dl>
    </section>

    {{-- ── The six documents ─────────────────────────────────────────────
         Every type gets a card whether or not it exists. The band carries the
         type's colour from config/certificate_types.php, so a reader who has
         seen one of these documents recognises its row here by colour before
         reading the name. --}}
    <h2 class="mt-8 mb-3 font-serif text-[19px] font-bold text-[#02301B]">
        {{ $isFr ? 'Les six documents' : 'The six documents' }}
    </h2>

    <div class="space-y-3">
        @foreach($rows as $row)
            @php $type = $types[$row['code']] ?? null; @endphp
            <section class="ui-card p-5 sm:p-6 hub-row cert-band-host">
                @include('pages.partials.certificate-band', ['code' => $row['code'], 'lang' => $lang])

                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-serif text-[16.5px] font-bold text-[#02301B] leading-tight">
                            {{ $type['name'][$lang] ?? $row['code'] }}
                        </h3>
                        <p class="mt-0.5 text-[11.5px] font-semibold tracking-[.14em] uppercase"
                           style="color: {{ $type['colour'] ?? '#6F6B60' }}">{{ $row['code'] }}</p>
                    </div>

                    @if($row['issued'])
                        <span class="ui-pill {{ $row['ok'] ? 'ui-pill-ok' : 'ui-pill-danger' }}">{{ $row['state'] }}</span>
                    @else
                        {{-- Neutral, not red. An absent document is usually not a
                             fault — most pieces never leave the country. --}}
                        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non émis' : 'Not issued' }}</span>
                    @endif
                </div>

                @if($row['issued'])
                    <dl class="mt-4 grid sm:grid-cols-2 gap-4">
                        @if($row['number'])
                            <div>
                                <dt class="ui-dt">{{ $isFr ? 'Numéro' : 'Number' }}</dt>
                                <dd class="ui-dd hub-ident">{{ $row['number'] }}</dd>
                            </div>
                        @endif
                        @if($row['date'])
                            <div>
                                <dt class="ui-dt">{{ $isFr ? 'Date' : 'Date' }}</dt>
                                <dd class="ui-dd">{{ $row['date'] }}</dd>
                            </div>
                        @endif
                        @if(! empty($row['extra']))
                            <div>
                                <dt class="ui-dt">{{ $isFr ? 'Niveau de vérification' : 'Verification level' }}</dt>
                                <dd class="ui-dd">{{ $row['extra'] }}</dd>
                            </div>
                        @endif
                        @if(! empty($row['count']) && $row['count'] > 1)
                            <div>
                                <dt class="ui-dt">{{ $isFr ? 'Documents de ce type' : 'Documents of this type' }}</dt>
                                <dd class="ui-dd">{{ $row['count'] }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if(! empty($row['note']))
                        <p class="mt-3 text-[12.5px] text-[#6F6B60] leading-relaxed">{{ $row['note'] }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($row['doc'])
                            <a href="{{ $row['doc'] }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                                {{ $isFr ? 'Voir le document' : 'View document' }}
                            </a>
                        @endif
                        @if($row['verify'])
                            <a href="{{ $row['verify'] }}" class="ui-btn ui-btn-ghost ui-btn-sm">
                                {{ $isFr ? 'Vérifier' : 'Verify' }}
                            </a>
                        @else
                            {{-- Said rather than hidden: a reader who cannot find a
                                 verify button should know it is missing from the
                                 platform, not from this page. --}}
                            <span class="text-[12px] text-[#6F6B60] self-center">
                                {{ $isFr
                                   ? "Aucune adresse de vérification publique n'est encore publiée pour ce type de document."
                                   : 'No public verification address is published for this document type yet.' }}
                            </span>
                        @endif
                    </div>
                @else
                    <p class="mt-3 text-[13px] text-[#3A3A35] leading-relaxed">{{ $row['reason'] }}</p>
                @endif
            </section>
        @endforeach
    </div>

    {{-- ── The ownership chain ───────────────────────────────────────────
         Compact: names, dates and standing, in the register's own order. The
         full dossier is a click away on the provenance certificate. --}}
    <section class="ui-card p-5 sm:p-6 mt-8">
        <h2 class="ui-card-title">{{ $isFr ? 'Chaîne de propriété' : 'Ownership chain' }}</h2>
        <p class="mt-1 text-[12.5px] text-[#6F6B60]">
            {{ $isFr ? 'Détenteurs enregistrés' : 'Recorded holders' }}: {{ count($chain) }}
        </p>
        <ol class="mt-4 space-y-3">
            @foreach($chain as $holder)
                <li class="flex items-start gap-3">
                    <span class="ui-pill ui-pill-neutral mt-0.5">{{ $holder->sequence }}</span>
                    <div class="min-w-0">
                        <p class="text-[13.5px] font-semibold text-[#1D1B16]">{{ $holder->legal_name }}</p>
                        <p class="text-[12px] text-[#6F6B60]">
                            @if($holder->is_original_creator){{ $isFr ? 'Créateur' : 'Maker' }} · @endif
                            {{ $date($holder->owned_from) }}
                            @if($holder->owned_until) — {{ $date($holder->owned_until) }}
                            @else — {{ $isFr ? "aujourd'hui" : 'present' }} @endif
                            @if($holder->country_code) · {{ $holder->country_code }} @endif
                        </p>
                        @if($holder->verification_level === 'verified')
                            <span class="ui-pill ui-pill-ok mt-1">{{ $isFr ? 'Identité vérifiée' : 'Identity verified' }}</span>
                        @else
                            {{-- "Declared" is what the register actually holds:
                                 the holder told us who they are and nobody
                                 checked. Printing it as verified would be the
                                 single most damaging lie on this page. --}}
                            <span class="ui-pill ui-pill-neutral mt-1">{{ $isFr ? 'Identité déclarée' : 'Identity declared' }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
        @if($owner)
            <p class="mt-4 text-[12.5px] text-[#6F6B60]">
                {{ $isFr ? 'Détenteur actuel' : 'Current holder' }}: <span class="font-semibold text-[#1D1B16]">{{ $owner->legal_name }}</span>
            </p>
        @endif
    </section>

    {{-- ── Provenance summary ────────────────────────────────────────────
         Counts, each of them a query. A zero here is a real zero. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Résumé de la provenance' : 'Provenance summary' }}</h2>
        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($summaryLabels as $key => $label)
                @if(array_key_exists($key, $summary))
                    <div>
                        <dt class="ui-dt">{{ $label[$lang] }}</dt>
                        <dd class="ui-dd">{{ $summary[$key] }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>

    {{-- ── Legacy Index ──────────────────────────────────────────────────
         Printed exactly as returned, over its own maximum, because the
         denominator moves: a category with nothing to assess drops out
         entirely, and a score shown out of a fixed 100 would misreport it. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Indice de patrimoine' : 'Legacy Index' }}</h2>
        <p class="mt-3 text-[26px] font-bold text-[#02301B]">{{ $legacy['total'] }}/{{ $legacy['max'] }}</p>
        <p class="mt-2 text-[12.5px] text-[#6F6B60] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? "Cet indice mesure la complétude du dossier documentaire, et non la valeur ni la qualité de l'œuvre ni celle de son artisan."
               : 'This index measures how completely the record is documented. It is not a measure of the merit of the work, nor of the artisan.' }}
        </p>
    </section>

    {{-- ── What the register has been told ───────────────────────────────
         Only the keys ProductFlags::checks() returned. A key it omitted is a
         question the platform cannot answer, and it gets neither a tick nor a
         cross — not even a greyed one, because a greyed box still reads as a
         box someone looked at. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Signalements au registre' : 'Reports to the register' }}</h2>
        <p class="mt-1 text-[12.5px] text-[#6F6B60] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? "Ce que personne n'a signalé à ce registre. C'est un négatif utile, pas une enquête : la plateforme n'est pas un enquêteur."
               : 'What nobody has reported to this register. That is a useful negative, not an investigation — the platform is not an investigator.' }}
        </p>
        <ul class="mt-4 space-y-2">
            @foreach($flagLabels as $key => $label)
                @if(array_key_exists($key, $flags))
                    <li class="flex items-center gap-2 text-[13px]">
                        @if($flags[$key])
                            <i data-lucide="check" class="w-4 h-4 text-leaf"></i>
                            <span>{{ $label[$lang] }}</span>
                        @else
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-[#B4231F]"></i>
                            <span>{{ $isFr ? 'Signalement actif' : 'Active report' }} — {{ $label[$lang] }}</span>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </section>

    <p class="mt-6 text-[12px] text-[#6F6B60] leading-relaxed">
        {{ $isFr
           ? "Les signatures numériques de ces documents peuvent être vérifiées hors ligne contre la clé publique de l'autorité de certification."
           : 'The digital signatures on these documents can be checked offline against the certification authority’s published key.' }}
        <a href="/autorite-de-certification{{ $q }}" class="text-leaf underline">{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}</a>
    </p>

</main>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
