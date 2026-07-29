@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $isFr = $lang === 'fr';

    /* The route builds these three without knowing the reader's language, so
       they arrive in the platform default. They are rebuilt here in $lang
       because they are the only parts of the dossier written as sentences —
       the timeline's labels, the journey's reasons and every "basis" line
       under the Legacy Index. Nothing else about them changes: the scores,
       the maxima and the band word are identical in both languages, which is
       what lets a French and an English printout of one dossier be compared. */
    $timeline = \App\Support\ProvenanceDossier::timeline($product, $lang);
    $journey  = \App\Support\ProvenanceDossier::journey($product, $lang);
    $legacy   = \App\Support\ProvenanceDossier::legacyIndex($product, $lang);

    // The shared directory header and footer read these two. They are set first
    // because those partials fatal without them, and a fatal here would take
    // down the one document a reader may already be holding a printout of.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $biz   = $product->business;
    $maker = $biz?->user;
    $name  = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $cover = $product->images->sortBy('sort_order')->firstWhere('is_cover', true)
             ?? $product->images->sortBy('sort_order')->first();

    /* ───────────────────────── The dossier's own number ──────────────────
       There is no separate "PPC number" anywhere in the register, and the
       artwork's AH237-PPC-… string would therefore be a number nobody could
       look up. The dossier is compiled from the certificate of authenticity's
       record, so that certificate's number is the reference printed on all
       three sheets — a reader can quote it and get an answer. */
    $certNo   = $coa?->certificate_no;
    $issued   = $coa?->issued_at ? Carbon::parse($coa->issued_at) : null;
    $revoked  = $coa?->revoked_at ? Carbon::parse($coa->revoked_at) : null;

    $verifyShort = $certNo ? route('product.certificate.verify.short', ['ref' => $certNo]) : null;

    /* ───────────────────────────── Vocabularies ─────────────────────────── */
    $entityLabels = [
        'individual' => $isFr ? 'Particulier'      : 'Individual',
        'company'    => $isFr ? 'Société'          : 'Company',
        'gallery'    => $isFr ? 'Galerie'          : 'Gallery',
        'museum'     => $isFr ? 'Musée'            : 'Museum',
        'government' => $isFr ? 'Organisme public' : 'Government body',
        'foundation' => $isFr ? 'Fondation'        : 'Foundation',
        'dealer'     => $isFr ? 'Marchand d\'art'  : 'Art dealer',
        'collector'  => $isFr ? 'Collectionneur'   : 'Collector',
    ];
    $levelLabels = [
        'unverified'  => $isFr ? 'Non vérifié'          : 'Unverified',
        'declared'    => $isFr ? 'Déclaré'              : 'Declared',
        'verified'    => $isFr ? 'Identité vérifiée'    : 'Identity verified',
        'institution' => $isFr ? 'Institution vérifiée' : 'Verified institution',
    ];
    $transferLabels = [
        'sale'                => $isFr ? 'Vente'                 : 'Sale',
        'gift'                => $isFr ? 'Don'                   : 'Gift',
        'donation'            => $isFr ? 'Donation'              : 'Donation',
        'inheritance'         => $isFr ? 'Succession'            : 'Inheritance',
        'museum_acquisition'  => $isFr ? 'Acquisition muséale'   : 'Museum acquisition',
        'gallery_acquisition' => $isFr ? 'Acquisition galerie'   : 'Gallery acquisition',
        'exchange'            => $isFr ? 'Échange'               : 'Exchange',
        'court_order'         => $isFr ? 'Décision de justice'   : 'Court order',
        'other'               => $isFr ? 'Autre'                 : 'Other',
    ];

    /* Event vocabulary. Each type is its own section on sheet 2, and each has
       its own empty sentence: "no exhibition has been recorded" and "no
       appraisal has been recorded" are different findings and a shared "none"
       would blur them. */
    $eventSections = [
        'exhibition'             => [$isFr ? 'Expositions' : 'Exhibitions', 'frame',
            $isFr ? 'Aucune exposition n\'est inscrite au registre.' : 'No exhibition has been recorded on this register.'],
        'museum_accession'       => [$isFr ? 'Entrées en collection muséale' : 'Museum accessions', 'landmark',
            $isFr ? 'Aucune entrée en collection muséale n\'est inscrite.' : 'No museum accession has been recorded.'],
        'gallery_representation' => [$isFr ? 'Représentation en galerie' : 'Gallery representation', 'palette',
            $isFr ? 'Aucune représentation en galerie n\'est inscrite.' : 'No gallery representation has been recorded.'],
        'publication'            => [$isFr ? 'Publications' : 'Publications', 'book-open',
            $isFr ? 'Aucune publication n\'est inscrite.' : 'No publication has been recorded.'],
        'media'                  => [$isFr ? 'Presse et médias' : 'Press and media', 'radio',
            $isFr ? 'Aucune mention presse n\'est inscrite.' : 'No press or media record has been recorded.'],
        'award'                  => [$isFr ? 'Distinctions' : 'Awards', 'award',
            $isFr ? 'Aucune distinction n\'est inscrite.' : 'No award has been recorded.'],
        'restoration'            => [$isFr ? 'Restaurations' : 'Restorations', 'wrench',
            $isFr ? 'Aucune restauration n\'est inscrite.' : 'No restoration has been recorded.'],
        'conservation'           => [$isFr ? 'Conservation' : 'Conservation', 'shield',
            $isFr ? 'Aucune intervention de conservation n\'est inscrite.' : 'No conservation work has been recorded.'],
        'condition_report'       => [$isFr ? 'Constats d\'état' : 'Condition reports', 'clipboard-list',
            $isFr ? 'Aucun constat d\'état n\'est inscrit.' : 'No condition report has been recorded.'],
        'valuation'              => [$isFr ? 'Évaluations' : 'Valuations', 'scale',
            $isFr ? 'Aucune évaluation n\'est inscrite.' : 'No appraisal has been recorded.'],
    ];

    // Grouped once, here, so a type that gains rows later cannot be silently
    // left off the sheet by a forgotten filter in the markup.
    $grouped = [];
    foreach ($eventSections as $type => $meta) {
        $grouped[$type] = array_values(array_filter($events, fn ($e) => $e->type === $type));
    }
    // Anything the register holds that this vocabulary does not name still has
    // to appear: an unlisted event is a fact somebody recorded.
    $otherEvents = array_values(array_filter($events, fn ($e) => ! isset($eventSections[$e->type])));

    /* ─────────────────────────── Timeline iconography ────────────────────
       Icons are per event type and fall back to a neutral dot. A wrong-but-
       pretty icon reads as a claim about what kind of event it was. */
    $typeIcons = [
        'registration' => 'file-text', 'ownership' => 'users',
        'exhibition' => 'frame', 'museum_accession' => 'landmark',
        'gallery_representation' => 'palette', 'publication' => 'book-open',
        'media' => 'radio', 'award' => 'award', 'restoration' => 'wrench',
        'conservation' => 'shield', 'condition_report' => 'clipboard-list',
        'valuation' => 'scale',
    ];
    $typeWords = [
        'registration' => $isFr ? 'Enregistrement' : 'Registration',
        'ownership'    => $isFr ? 'Propriété'      : 'Ownership',
    ] + array_map(fn ($m) => $m[0], $eventSections);

    /* ───────────────────────── Countries, ISO 3166-1 ─────────────────────
       Only codes the register actually stores are printed, and the name is a
       lookup that falls back to the code itself. A code with no name is still
       a fact; a guessed name is not. */
    $countryNames = [
        'CM' => $isFr ? 'Cameroun' : 'Cameroon', 'FR' => 'France',
        'BE' => $isFr ? 'Belgique' : 'Belgium',  'US' => $isFr ? 'États-Unis' : 'United States',
        'JP' => $isFr ? 'Japon' : 'Japan',       'GB' => $isFr ? 'Royaume-Uni' : 'United Kingdom',
        'DE' => $isFr ? 'Allemagne' : 'Germany', 'CA' => 'Canada',
        'NG' => $isFr ? 'Nigéria' : 'Nigeria',   'CH' => $isFr ? 'Suisse' : 'Switzerland',
        'CN' => $isFr ? 'Chine' : 'China',       'ZA' => $isFr ? 'Afrique du Sud' : 'South Africa',
    ];
    $countryName = fn (?string $cc) => $cc ? ($countryNames[strtoupper($cc)] ?? strtoupper($cc)) : null;

    /* ─────────────────────────── Legacy Index labels ─────────────────────
       The register hands back max 0 for a category it could not assess, and
       the single most important thing this view does with the index is refuse
       to flatten that into a fraction. See the card on sheet 3. */
    $catLabels = [
        'registration'    => $isFr ? 'Enregistrement au registre' : 'Registry entry',
        'ownership_chain' => $isFr ? 'Chaîne de propriété'        : 'Ownership chain',
        'evidence'        => $isFr ? 'Pièces justificatives'      : 'Supporting documents',
        'public_record'   => $isFr ? 'Histoire publique'          : 'Public record',
        'conservation'    => $isFr ? 'Conservation'               : 'Conservation',
        'valuation'       => $isFr ? 'Évaluation'                 : 'Valuation',
    ];
    $bandWords = [
        'comprehensive' => $isFr ? 'COMPLET'     : 'COMPREHENSIVE',
        'strong'        => $isFr ? 'SOLIDE'      : 'STRONG',
        'partial'       => $isFr ? 'PARTIEL'     : 'PARTIAL',
        'limited'       => $isFr ? 'LIMITÉ'      : 'LIMITED',
        'minimal'       => $isFr ? 'MINIMAL'     : 'MINIMAL',
        'unassessed'    => $isFr ? 'NON ÉVALUÉ'  : 'NOT ASSESSED',
    ];
    // English "Not assessed" is asserted by the test; the French sheet says the
    // same thing in French, and both are the phrase — never a zero.
    $unassessedWord = $isFr ? 'Non évalué' : 'Not assessed';
    $bandWord   = $bandWords[$legacy['band']] ?? Str::upper($legacy['band']);
    $legacyPct  = $legacy['max'] > 0 ? round($legacy['total'] / $legacy['max'] * 100) : null;
    $bandColour = match ($legacy['band']) {
        'comprehensive', 'strong' => '#0F7A34',
        'partial'                 => '#4E7A15',
        'limited'                 => '#A8802A',
        'minimal'                 => '#B4141B',
        default                   => '#5A6B7E',
    };

    /* ─────────────────────────── Register ticks ──────────────────────────
       $flags carries only the concerns this register is authoritative over.
       A key it does not carry is "cannot be determined" and is never a tick. */
    $flagLabels = [
        'not_reported_stolen'     => $isFr ? 'Aucun signalement de vol au registre' : 'No theft report on this register',
        'not_under_investigation' => $isFr ? 'Aucune enquête signalée au registre'  : 'No investigation reported to this register',
        'ownership_undisputed'    => $isFr ? 'Aucune contestation de propriété'     : 'No ownership dispute recorded',
    ];
    $checks = collect();
    foreach ($flags as $key => $ok) {
        if (isset($flagLabels[$key])) $checks->push([$flagLabels[$key], (bool) $ok]);
    }
    $checks->push([$isFr ? 'Certificat d\'authenticité émis' : 'Certificate of authenticity issued', (bool) $coa]);
    $checks->push([$isFr ? 'Créateur d\'origine au registre' : 'Original creator on the register',
        (bool) collect($chain)->first()?->is_original_creator]);
    $checks->push([$isFr ? 'Détenteur actuel identifié' : 'Current holder identified', (bool) $owner]);
    $checks->push([$isFr ? 'Certificat signé par l\'autorité' : 'Signed by the certification authority', filled($coa?->ca_signature)]);
    $checks->push([$isFr ? 'Certificat en vigueur' : 'Certificate in force', (bool) $coa && ! $revoked]);

    /* Related certificates. Every line is a reference this register actually
       holds; the artwork lists ten documents with "Active" chips whether or not
       anything was issued, which turns an empty drawer into a filing cabinet. */
    $rows = fn (array $set) => collect($set)->filter(fn ($r) => filled($r[1]))->values();

    $related = $rows([
        [$isFr ? 'Certificat d\'authenticité (COA)' : 'Certificate of authenticity (COA)', $certNo, true],
        [$isFr ? 'Registre produit (PRN)' : 'Product registry (PRN)', $prn, true],
        [$isFr ? 'Registre de propriété (OLN)' : 'Ownership ledger (OLN)', $oln, true],
        [$isFr ? 'Numéro global d\'artisan (GAN)' : 'Global artisan number (GAN)', $gan, true],
        [$isFr ? 'UUID du produit' : 'Product UUID', $product->uuid, true],
        [$isFr ? 'UUID du certificat' : 'Certificate UUID', $coa?->uuid, true],
    ]);
    // Transfer certificates are register documents in their own right, so each
    // one is listed by its real number rather than summarised as a count.
    foreach ($transfers as $t) {
        $related->push([$isFr ? 'Certificat de transfert (OTC)' : 'Ownership transfer certificate (OTC)', $t->certificate_no, true]);
    }

    $standards = [
        $isFr ? 'ISO 8601 — dates et heures'        : 'ISO 8601 date and time',
        $isFr ? 'ISO 3166-1 alpha-2 — codes pays'   : 'ISO 3166-1 alpha-2 country codes',
        $isFr ? 'ISO 4217 — codes devise'           : 'ISO 4217 currency codes',
        $isFr ? 'RFC 4122 — identifiants UUID'      : 'RFC 4122 UUID identifiers',
        $isFr ? 'SHA-256 — empreinte du contenu'    : 'SHA-256 content hash',
        $isFr ? 'Ed25519 (EdDSA) — signature'       : 'Ed25519 (EdDSA) signature',
    ];

    /* ─────────────────────── The verifiable-features legend ──────────────
       Only what a reader can check on a screen. The artwork's ten badges
       include a rainbow shield, an embossed seal, a ghost portrait, reactive
       ink and a chip in the paper — every one a property of a print run or a
       piece of hardware that no web page and no home printout carries. Naming
       them would be the most damaging thing on the sheet, because they are
       exactly the features a reader would "check" by looking at a picture. */
    $securityLegend = collect([
        ['pen-tool',     $isFr ? 'Signature Ed25519 (EdDSA)' : 'Ed25519 (EdDSA) signature',
            $isFr ? 'Vérifiable avec la clé publiée' : 'Checkable against the published key'],
        ['fingerprint',  $isFr ? 'Empreinte SHA-256' : 'SHA-256 content hash',
            $isFr ? 'Calculée sur les faits certifiés' : 'Computed over the certified facts'],
        ['key-round',    $isFr ? 'Clé publique publiée' : 'Published public key', '/.well-known/jwks.json'],
        ['hash',         $isFr ? 'UUID du certificat' : 'Certificate UUID',
            $isFr ? 'Identifiant unique, RFC 4122' : 'Unique identifier, RFC 4122'],
        ['qr-code',      $isFr ? 'Vérification par QR' : 'QR verification',
            $isFr ? 'Renvoie au registre en ligne' : 'Resolves to the online register'],
        ['link-2',       $isFr ? 'Journal chaîné par empreintes' : 'Hash-chained event log',
            $isFr ? 'Chaque entrée référence la précédente' : 'Each entry carries its predecessor\'s digest'],
        ['spline',       $isFr ? 'Guilloché' : 'Guilloché',
            $isFr ? 'Tracé hypotrochoïde, vectoriel' : 'Hypotrochoid line work, vector'],
        ['type',         $isFr ? 'Microtexte' : 'Microtext',
            $isFr ? 'Texte réel de 1,6 px, lisible au zoom' : 'Real 1.6px type, resolves on zoom'],
    ]);

    $pageOf   = fn (int $n) => $isFr ? "Page {$n} sur 3" : "Page {$n} of 3";
    $sheetSub = [
        1 => $isFr ? 'Certificat exécutif'      : 'Executive certificate',
        2 => $isFr ? 'Dossier de provenance'    : 'Provenance record',
        3 => $isFr ? 'Vérification et registre' : 'Verification and register',
    ];

    $serial = $certNo ? Str::afterLast($certNo, '-') : '';

    /* The sheet foot, built once and echoed three times. Not for brevity:
       three hand-maintained copies are how page 3 comes to say "Page 2 of 3"
       six months from now, and the one thing a multi-page dossier cannot
       afford is a sheet that misidentifies itself. */
    $sheetFoot = function (int $n) use ($certNo, $serial, $isFr, $pageOf) {
        $no    = e($certNo);
        $ser   = e($serial);
        $page  = e($pageOf($n));
        $strap = e($isFr
            ? 'Dossier de provenance en trois feuillets — l\'exemplaire de référence est celui vérifié en ligne'
            : 'A three-sheet provenance dossier — the authoritative copy is the one verified online');
        $issuer = e($isFr ? 'ArtisanHub237 · Autorité de certification' : 'ArtisanHub237 · Certification Authority');

        return <<<HTML
        <div style="margin:9px 20px 0;">
            <svg width="921" height="6" viewBox="0 0 1000 6" preserveAspectRatio="none" aria-hidden="true">
                <g transform="translate(0,1)"><use href="#coaMicrotextLine"/></g>
            </svg>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin:4px 20px 0;">
            <span class="mono" style="width:104px;flex:none;font-size:12px;font-weight:700;color:#7A1B33;letter-spacing:.05em;">{$ser}</span>
            <div style="flex:1;height:22px;border-radius:5px;border:1px solid #C9942E;
                        background:linear-gradient(180deg,#5C1327,#3E0C1B);display:flex;align-items:center;justify-content:center;">
                <span style="font-size:9px;font-weight:700;letter-spacing:.05em;color:#F0DFB4;text-transform:uppercase;">{$strap}</span>
            </div>
            <span class="mono" style="width:186px;flex:none;text-align:right;font-size:8px;color:#4A3B40;word-break:break-all;">{$no}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 20px 0;padding-bottom:11px;">
            <span style="font-size:8px;font-weight:600;letter-spacing:.06em;color:#6B5A5F;text-transform:uppercase;">{$issuer}</span>
            <span style="font-size:8.5px;font-weight:700;letter-spacing:.09em;color:#8A5F14;text-transform:uppercase;">{$page}</span>
        </div>
        HTML;
    };

    $docTitle = $isFr ? 'CERTIFICAT DE PROVENANCE DU PRODUIT' : 'PRODUCT PROVENANCE CERTIFICATE';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $docTitle }} — {{ $name }}">
    <title>{{ $docTitle }} — {{ $certNo }}</title>

    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           Three sheets, one canvas. Each sheet is drawn at the artwork's own
           1024px width and the whole stack is scaled as a single unit by the
           script at the foot of the page. Scaling each sheet on its own would
           let two pages of the same dossier end up at different sizes on a
           narrow screen, which is the one thing a multi-page document must
           never do — a reader would not know they were looking at one file.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .coa-page { width: 1024px; transform-origin: top left; font-size: 12px; line-height: 1.3; color: #241A1E; }
        .coa-page, .coa-page * { font-size: revert-layer; }

        /* One sheet. Burgundy is the PPC's classification colour on the shared
           band, so the bezel takes it too: the band and the sheet reading as
           two different documents was the alternative. */
        .ppc-sheet { background:#2B0A15; padding:9px; border-radius:18px; position:relative; margin-bottom:26px; }
        .ppc-sheet:last-of-type { margin-bottom:0; }
        .ppc-frame {
            padding:22px; position:relative; border-radius:10px;
            background:
                repeating-radial-gradient(circle at 13px 11px, rgba(0,0,0,0) 0 4.6px, #C9942E 4.6px 5.8px, rgba(0,0,0,0) 5.8px 13px),
                repeating-radial-gradient(circle at 0 11px,   rgba(0,0,0,0) 0 4.6px, #A87C22 4.6px 5.6px, rgba(0,0,0,0) 5.6px 13px),
                #4A0F20;
            background-size:26px 22px, 26px 22px, auto;
            box-shadow: inset 0 0 0 1.2px #C9942E;
        }
        .ppc-paper { width:961px; position:relative; background:#FCF6EC; box-shadow:0 0 0 1.2px #C9942E; }
        .ppc-guilloche { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:.42; z-index:0; }
        .ppc-paper > *:not(.ppc-guilloche) { position:relative; z-index:1; }

        /* Section cards, lettered. In a three-sheet dossier the letters are the
           navigation: "see I on sheet 2" is only usable if the bars carry them,
           so they are rendered rather than dropped for tidiness. */
        .sc      { border:1px solid #C9BFA6; border-radius:7px; background:#FFFDF7; overflow:hidden; }
        .sc-head { display:flex; align-items:center; gap:7px; min-height:22px; padding:3px 9px;
                   background:linear-gradient(180deg,#5C1327,#3E0C1B); }
        .sc-head .ltr { width:14px; height:14px; border-radius:3px; flex:none; background:#C9942E; color:#3E0C1B;
                        font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center; }
        .sc-head h2  { font-size:9.5px; font-weight:700; letter-spacing:.075em; text-transform:uppercase; color:#F4EAD4; }
        .sc-head .hint { margin-left:auto; font-size:7.5px; font-weight:600; letter-spacing:.05em; color:#D8B98A; text-transform:uppercase; }
        .sc-body { padding:8px 10px 9px; }

        .kv     { display:grid; grid-template-columns:var(--kv,140px) 1fr; column-gap:9px;
                  padding:2px 0; border-bottom:1px solid #EFE7D5; align-items:baseline; }
        .kv:last-child { border-bottom:0; }
        .kv dt  { font-size:8.5px; font-weight:500; color:#5B4A50; }
        .kv dd  { font-size:8.5px; font-weight:600; color:#241A1E; word-break:break-word; }
        .mono   { font-family:ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing:-.2px; }
        .tick   { display:flex; align-items:flex-start; gap:6px; padding:2.2px 0; font-size:9px; color:#241A1E; }
        .tick svg { width:11px; height:11px; flex:none; margin-top:1px; }
        .note   { font-size:8px; line-height:1.45; color:#6B5A5F; }

        /* Register tables. Dense on purpose: a provenance reader compares rows,
           and a row that wraps to three lines stops being comparable. */
        table.reg { width:100%; border-collapse:collapse; }
        table.reg th { font-size:7.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
                       color:#5B4A50; text-align:left; padding:2px 5px; border-bottom:1px solid #D9CDB5; }
        table.reg td { font-size:8.5px; color:#241A1E; padding:2.6px 5px; border-bottom:1px solid #EFE7D5; vertical-align:top; }
        table.reg tr:last-child td { border-bottom:0; }
        table.reg td.mono { font-size:8px; }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa-fit { overflow:visible; }
            /* One sheet, one A4 page. Without this the three run together and
               the page numbers printed on them become wrong, which is worse
               than printing no page numbers at all. */
            .ppc-sheet { break-after: page; page-break-after: always; margin-bottom:0; }
            .ppc-sheet:last-of-type { break-after: auto; page-break-after: auto; }
            @page { size: A4 portrait; margin: 5mm; }
        }
    </style>
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#E9E5E6] text-[#241A1E] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

@include('pages.partials.coa-ornaments')
@include('pages.partials.coa-security')

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="ppcGoldRule" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#C9942E" stop-opacity="0"/>
            <stop offset="25%" stop-color="#C9942E"/>
            <stop offset="55%" stop-color="#F0D493"/>
            <stop offset="85%" stop-color="#C9942E"/>
            <stop offset="1" stop-color="#C9942E" stop-opacity="0"/>
        </linearGradient>
    </defs>
</svg>

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat de provenance' : 'Provenance certificate' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[6px]">
    <article class="coa-page">

    {{-- ══════════════════════════ SHEET 1 ══════════════════════════════ --}}
    <section class="ppc-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'PPC'])
    <div class="ppc-frame">
    <div class="ppc-paper">

        <svg class="ppc-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,20)" opacity=".5"><use href="#coaGuillocheRosette"/></g>
            <g transform="translate(0,180) scale(1,.9)" opacity=".35"><use href="#coaGuillocheRosette"/></g>
        </svg>

        {{-- ══ Header ══ --}}
        <div style="padding:12px 96px 0;text-align:center;">
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237"
                 style="height:62px;width:auto;max-width:100%;margin:0 auto;display:block;">
            <p style="margin-top:2px;font-size:10px;font-weight:600;letter-spacing:.26em;color:#5B4A50;">
                {{ $isFr ? 'RELIER L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
            </p>
            <h1 style="margin-top:8px;font-family:'Playfair Display',Georgia,serif;font-size:36px;line-height:1;
                       font-weight:700;color:#3E0C1B;letter-spacing:.01em;display:inline-block;
                       transform:scaleX(.93);transform-origin:center;white-space:nowrap;">
                {{ $docTitle }}
            </h1>
            <div style="margin-top:5px;display:flex;align-items:center;justify-content:center;gap:12px;">
                <svg width="230" height="3" aria-hidden="true"><rect width="230" height="3" fill="url(#ppcGoldRule)"/></svg>
                <span style="font-family:'Playfair Display',Georgia,serif;font-size:25px;font-weight:600;color:#3E0C1B;">(PPC)</span>
                <svg width="230" height="3" aria-hidden="true"><rect width="230" height="3" fill="url(#ppcGoldRule)"/></svg>
            </div>
            {{-- The artwork's strap reads "THIS CERTIFICATE ESTABLISHES THE
                 COMPLETE, VERIFIED HISTORY AND PROVENANCE OF THE PRODUCT". No
                 register can establish that a history is complete — it can only
                 publish what was recorded in it, which is what the line says. --}}
            <p style="margin:8px auto 0;display:inline-block;padding:5px 18px;border-radius:5px;
                      background:linear-gradient(180deg,#5C1327,#3E0C1B);border:1px solid #C9942E;
                      font-size:9.5px;font-weight:700;letter-spacing:.08em;color:#F0DFB4;text-transform:uppercase;">
                {{ $isFr
                   ? 'Ce document publie l\'historique inscrit au registre pour cette pièce'
                   : 'This document publishes the history recorded on the register for this piece' }}
            </p>
        </div>

        {{-- Sheet identity strip, repeated on all three sheets: a page that
             arrives on its own must still say which dossier it belongs to. --}}
        <div style="display:flex;align-items:center;gap:10px;margin:11px 20px 0;padding:5px 12px;border-radius:5px;
                    background:#4A0F20;border:1px solid #C9942E;">
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;letter-spacing:.04em;">{{ $certNo }}</span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span style="font-size:9px;font-weight:700;letter-spacing:.12em;color:#C9942E;text-transform:uppercase;">
                {{ $pageOf(1) }} — {{ $sheetSub[1] }}
            </span>
        </div>

        {{-- ══ A · identity · B · verification · C · the piece ══ --}}
        <div style="display:grid;grid-template-columns:430px 226px 273px;gap:8px;margin:9px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">A</span><h2>{{ $isFr ? 'Identité du dossier' : 'Dossier identity' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:184px;">
                        @foreach($rows([
                            [$isFr ? 'N° de certificat (COA)' : 'Certificate no. (COA)', $certNo, true],
                            [$isFr ? 'UUID du certificat' : 'Certificate UUID', $coa?->uuid, true],
                            [$isFr ? 'Version' : 'Certificate version', $coa?->version, true],
                            [$isFr ? 'N° de registre produit (PRN)' : 'Product registry no. (PRN)', $prn, true],
                            [$isFr ? 'N° de registre de propriété (OLN)' : 'Ownership ledger no. (OLN)', $oln, true],
                            [$isFr ? 'N° global d\'artisan (GAN)' : 'Global artisan no. (GAN)', $gan, true],
                            [$isFr ? 'UUID du produit' : 'Product UUID', $product->uuid, true],
                            [$isFr ? 'Émis le (UTC)' : 'Issued at (UTC)', $issued?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
                            [$isFr ? 'Révoqué le (UTC)' : 'Revoked at (UTC)', $revoked?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
                        ]) as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                        <div class="kv">
                            <dt>{{ $isFr ? 'Statut du certificat' : 'Certificate status' }}</dt>
                            <dd style="display:flex;align-items:center;gap:5px;font-weight:700;color:{{ $revoked ? '#B4141B' : '#0F7A34' }};">
                                <i data-lucide="{{ $revoked ? 'shield-off' : 'check-circle-2' }}" style="width:11px;height:11px;flex:none;"></i>
                                {{ $revoked ? ($isFr ? 'RÉVOQUÉ' : 'REVOKED') : ($isFr ? 'EN VIGUEUR' : 'IN FORCE') }}
                            </dd>
                        </div>
                    </dl>
                    @if($coa?->revoked_reason)
                    <p class="note" style="margin-top:6px;color:#B4141B;">{{ $isFr ? 'Motif de révocation' : 'Reason for revocation' }} : {{ $coa->revoked_reason }}</p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">B</span><h2>{{ $isFr ? 'Vérification' : 'Verification' }}</h2></div>
                <div class="sc-body" style="text-align:center;">
                    <div id="ppc-qr" style="margin:2px auto 0;width:122px;height:122px;"></div>
                    <p style="margin-top:6px;font-size:8.5px;font-weight:700;letter-spacing:.07em;color:#5B4A50;text-transform:uppercase;">
                        {{ $isFr ? 'Scanner pour vérifier' : 'Scan to verify' }}
                    </p>
                    @if($verifyShort)
                    <p class="mono" style="margin-top:4px;font-size:8.5px;color:#0B4A2A;word-break:break-all;">{{ $verifyShort }}</p>
                    @endif
                    @if($coa?->verification_pin)
                    <p style="margin-top:6px;font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#5B4A50;">{{ $isFr ? 'CODE DE VÉRIFICATION' : 'VERIFICATION CODE' }}</p>
                    <p class="mono" style="font-size:13px;font-weight:700;letter-spacing:.14em;color:#241A1E;">{{ $coa->verification_pin }}</p>
                    @endif
                    <p class="note" style="margin-top:6px;text-align:left;">
                        {{ $isFr
                           ? 'La vérification en ligne renvoie l\'état actuel du registre, qui peut avoir changé depuis l\'impression de ce feuillet.'
                           : 'Online verification returns the register\'s current state, which may have changed since this sheet was printed.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">C</span><h2>{{ $isFr ? 'La pièce' : 'The piece' }}</h2></div>
                <div class="sc-body">
                    <div style="height:146px;border:1px solid #DDD2BA;border-radius:5px;background:#F4EEE1;
                                display:flex;align-items:center;justify-content:center;padding:5px;overflow:hidden;">
                        @if($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                        @else
                        <i data-lucide="image-off" style="width:22px;height:22px;color:#A8A296;"></i>
                        @endif
                    </div>
                    <p style="margin-top:6px;font-size:10.5px;font-weight:700;color:#3E0C1B;line-height:1.3;">{{ $name }}</p>
                    <dl style="margin-top:4px;--kv:96px;">
                        @foreach($rows([
                            [$isFr ? 'Catégorie' : 'Category', $isFr ? $product->category?->name_fr : ($product->category?->name_en ?: $product->category?->name_fr), false],
                            [$isFr ? 'Enregistré le' : 'Registered on', $product->registered_at ? Carbon::parse($product->registered_at)->format('Y-m-d') : ($product->created_at?->format('Y-m-d')), true],
                            [$isFr ? 'Empreinte image' : 'Image hash', $coa?->image_phash ? Str::upper($coa->image_phash) : null, true],
                        ]) as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="note" style="margin-top:5px;">
                        {{-- Named for what it is. The artwork calls this an "AI
                             fingerprint" and a "feature vector"; it is a 64-bit
                             perceptual hash of the photograph and nothing more,
                             and calling it intelligence would invite a reader to
                             believe a machine had examined the object. --}}
                        {{ $isFr
                           ? 'L\'empreinte image est un hachage perceptuel 64 bits de la photographie déposée par l\'artisan. Elle détecte un changement d\'image ; elle n\'examine pas l\'objet.'
                           : 'The image hash is a 64-bit perceptual hash of the photograph the artisan filed. It detects a changed image; it does not examine the object.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ D · creator · E · current owner · F · summary ══ --}}
        <div style="display:grid;grid-template-columns:328px 288px 313px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">D</span><h2>{{ $isFr ? 'Créateur d\'origine' : 'Original creator' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:118px;">
                        @forelse($rows([
                            [$isFr ? 'Atelier' : 'Workshop', $biz?->name_fr, false],
                            [$isFr ? 'N° global d\'artisan (GAN)' : 'Global artisan no. (GAN)', $gan, true],
                            [$isFr ? 'Artisan' : 'Artisan', $maker?->name, false],
                            [$isFr ? 'Région' : 'Region', $biz?->region?->name_fr, false],
                            [$isFr ? 'Ville' : 'City', $biz?->city?->name_fr, false],
                            [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $biz ? 'CM' : null, true],
                        ]) as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @empty
                        <p class="note">{{ $isFr ? 'Aucune donnée au registre.' : 'Nothing on the register.' }}</p>
                        @endforelse
                    </dl>
                    {{-- The artwork closes this panel with the artisan's
                         handwritten signature. No specimen signature exists
                         anywhere in this platform, and drawing one under a real
                         person's name is a forgery with extra steps. The party
                         is a text entry with its register reference. --}}
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">E</span><h2>{{ $isFr ? 'Détenteur actuel' : 'Current holder' }}</h2></div>
                <div class="sc-body">
                    @if($owner)
                    <dl style="--kv:118px;">
                        @foreach($rows([
                            [$isFr ? 'Raison sociale / nom' : 'Legal name', $owner->legal_name, false],
                            [$isFr ? 'Réf. propriétaire' : 'Holder reference', $owner->owner_ref, true],
                            [$isFr ? 'Type d\'entité' : 'Entity type', $entityLabels[$owner->entity_type] ?? $owner->entity_type, false],
                            [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $owner->country_code, true],
                            [$isFr ? 'Détenteur depuis' : 'Holder since', $owner->owned_from ? Carbon::parse($owner->owned_from)->format('Y-m-d') : null, true],
                            [$isFr ? 'Niveau d\'identification' : 'Identification level', $levelLabels[$owner->verification_level] ?? $owner->verification_level, false],
                        ]) as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @else
                    <p class="note">{{ $isFr ? 'Aucun détenteur ouvert au registre.' : 'No open holder on the register.' }}</p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">F</span><h2>{{ $isFr ? 'Synthèse de provenance' : 'Provenance summary' }}</h2></div>
                <div class="sc-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 12px;">
                        @foreach([
                            ['years_documented', $isFr ? 'Années documentées' : 'Years documented'],
                            ['ownership_changes', $isFr ? 'Changements de propriété' : 'Ownership changes'],
                            ['exhibitions',  $isFr ? 'Expositions' : 'Exhibitions'],
                            ['museums',      $isFr ? 'Collections muséales' : 'Museum accessions'],
                            ['galleries',    $isFr ? 'Galeries' : 'Galleries'],
                            ['publications', $isFr ? 'Publications' : 'Publications'],
                            ['restorations', $isFr ? 'Restaurations' : 'Restorations'],
                            ['conservations',$isFr ? 'Conservation' : 'Conservation'],
                            ['valuations',   $isFr ? 'Évaluations' : 'Valuations'],
                            ['awards',       $isFr ? 'Distinctions' : 'Awards'],
                            ['countries',    $isFr ? 'Pays traversés' : 'Countries recorded'],
                            ['evidence',     $isFr ? 'Pièces justificatives' : 'Supporting documents'],
                            ['events',       $isFr ? 'Événements inscrits' : 'Events recorded'],
                        ] as [$key, $label])
                        <div class="kv" style="--kv:1fr;grid-template-columns:1fr auto;">
                            <dt>{{ $label }}</dt>
                            <dd class="mono" style="font-weight:700;color:{{ (int) $summary[$key] > 0 ? '#0F7A34' : '#8A8079' }};">{{ (int) $summary[$key] }}</dd>
                        </div>
                        @endforeach
                    </div>
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'Chaque nombre est un décompte de lignes du registre. Un zéro signifie que rien n\'a été inscrit, non que rien n\'a eu lieu.'
                           : 'Every number is a count of register rows. A zero means nothing was recorded, not that nothing happened.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ G · executive timeline ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">G</span><h2>{{ $isFr ? 'Chronologie (synthèse)' : 'Provenance timeline (executive summary)' }}</h2>
                <span class="hint">{{ $isFr ? 'Détail au feuillet 2' : 'Full record on sheet 2' }}</span></div>
            <div class="sc-body">
                @if($timeline)
                <div style="display:flex;align-items:flex-start;gap:6px;overflow:hidden;">
                    @foreach($timeline as $i => $entry)
                    <div style="flex:1;min-width:0;text-align:center;">
                        <span style="width:32px;height:32px;margin:0 auto;border-radius:50%;border:1.4px solid #C9942E;background:#F6EEDC;
                                     color:#8A5F14;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $typeIcons[$entry['type']] ?? 'circle-dot' }}" style="width:15px;height:15px;"></i>
                        </span>
                        <p style="margin-top:4px;font-size:7.5px;font-weight:700;letter-spacing:.04em;color:#3E0C1B;text-transform:uppercase;">
                            {{ $typeWords[$entry['type']] ?? Str::headline($entry['type']) }}
                        </p>
                        <p class="mono" style="margin-top:2px;font-size:7.5px;color:#5B4A50;">{{ $entry['date'] ?? ($isFr ? 'sans date' : 'undated') }}</p>
                        <p style="margin-top:1px;font-size:7.5px;line-height:1.3;color:#241A1E;">{{ Str::limit($entry['label'], 46) }}</p>
                        @if($entry['country'])
                        <p style="font-size:7px;color:#6B5A5F;">{{ strtoupper($entry['country']) }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="note">{{ $isFr ? 'Aucune entrée chronologique au registre.' : 'No timeline entry has been recorded on this register.' }}</p>
                @endif
            </div>
        </section>

        {{-- ══ H · geographic journey · I · register ticks ══ --}}
        <div style="display:grid;grid-template-columns:1fr 340px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">H</span><h2>{{ $isFr ? 'Parcours géographique' : 'Geographic journey' }}</h2></div>
                <div class="sc-body">
                    @if($journey)
                    <table class="reg">
                        <thead><tr>
                            <th style="width:22px;">#</th>
                            <th>{{ $isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)' }}</th>
                            <th style="width:88px;">{{ $isFr ? 'Date (ISO 8601)' : 'Date (ISO 8601)' }}</th>
                            <th>{{ $isFr ? 'Ligne du registre' : 'Register row' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($journey as $i => $leg)
                        <tr>
                            <td class="mono">{{ $i + 1 }}</td>
                            <td>{{ $countryName($leg['country']) }} ({{ $leg['country'] }})</td>
                            <td class="mono">{{ $leg['date'] ?? '—' }}</td>
                            <td>{{ $leg['reason'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    {{-- The artwork draws a world map with five pins on it. When
                         no register row names a country there is no journey, and
                         the honest output is this sentence — a default origin
                         would invent the single fact a provenance reader most
                         wants checked. --}}
                    <p class="note" style="font-size:9px;">
                        {{ $isFr
                           ? 'Aucun pays n\'est inscrit au registre pour cette pièce. Aucun parcours ne peut donc être publié : une origine par défaut serait une invention.'
                           : 'No country has been recorded on the register for this piece, so no journey can be published. A default origin would be an invention.' }}
                    </p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">I</span><h2>{{ $isFr ? 'État au registre' : 'Register status' }}</h2></div>
                <div class="sc-body">
                    @foreach($checks as [$label, $ok])
                    <div class="tick">
                        <i data-lucide="{{ $ok ? 'check-circle-2' : 'circle-dashed' }}" style="color:{{ $ok ? '#0F7A34' : '#8A8079' }};"></i>
                        <span style="color:{{ $ok ? '#241A1E' : '#6B5A5F' }};">{{ $label }}</span>
                    </div>
                    @endforeach
                    <p class="note" style="margin-top:5px;padding-top:5px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Une coche signifie que rien n\'a été signalé à ce registre. Ce n\'est pas une enquête, et l\'absence de signalement n\'est pas une preuve.'
                           : 'A tick means nothing has been reported to this register. It is not an investigation, and the absence of a report is not proof.' }}
                    </p>
                </div>
            </section>
        </div>

        {!! $sheetFoot(1) !!}
    </div>
    </div>
    </section>

    {{-- ══════════════════════════ SHEET 2 ══════════════════════════════ --}}
    <section class="ppc-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'PPC'])
    <div class="ppc-frame">
    <div class="ppc-paper">
        <svg class="ppc-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,60)" opacity=".38"><use href="#coaGuillocheRosette"/></g>
        </svg>

        {{-- ══ Sheet head. Shorter than sheet 1's: the reader has already seen
             the masthead, and the identity strip is what a loose page needs. ══ --}}
        <div style="display:flex;align-items:flex-end;gap:14px;padding:13px 20px 0;">
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237" style="height:38px;width:auto;">
            <div style="flex:1;min-width:0;">
                <p style="font-family:'Playfair Display',Georgia,serif;font-size:19px;font-weight:700;color:#3E0C1B;line-height:1.1;">{{ $docTitle }} (PPC)</p>
                <p style="font-size:9px;font-weight:700;letter-spacing:.1em;color:#8A5F14;text-transform:uppercase;">{{ $pageOf(2) }} — {{ $sheetSub[2] }}</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin:9px 20px 0;padding:5px 12px;border-radius:5px;
                    background:#4A0F20;border:1px solid #C9942E;">
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;letter-spacing:.04em;">{{ $certNo }}</span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span class="mono" style="font-size:8px;color:#E4CDA0;">PRN {{ $prn }}</span>
            <span class="mono" style="font-size:8px;color:#E4CDA0;">OLN {{ $oln }}</span>
        </div>

        {{-- ══ J · the ownership chain, in the register's own order ══ --}}
        <section class="sc" style="margin:9px 20px 0;">
            <div class="sc-head"><span class="ltr">J</span><h2>{{ $isFr ? 'Chaîne de propriété' : 'Ownership chain' }}</h2>
                <span class="hint">{{ count($chain) }} {{ $isFr ? 'détenteurs inscrits' : 'recorded holders' }}</span></div>
            <div class="sc-body">
                @if($chain)
                <table class="reg">
                    <thead><tr>
                        <th style="width:22px;">#</th>
                        <th>{{ $isFr ? 'Détenteur' : 'Holder' }}</th>
                        <th style="width:96px;">{{ $isFr ? 'Type d\'entité' : 'Entity type' }}</th>
                        <th style="width:100px;">{{ $isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)' }}</th>
                        <th style="width:74px;">{{ $isFr ? 'Depuis' : 'From' }}</th>
                        <th style="width:74px;">{{ $isFr ? 'Jusqu\'au' : 'Until' }}</th>
                        <th style="width:126px;">{{ $isFr ? 'Réf. registre' : 'Register ref.' }}</th>
                        <th style="width:104px;">{{ $isFr ? 'Identification' : 'Identification' }}</th>
                    </tr></thead>
                    <tbody>
                    @foreach($chain as $holder)
                    <tr>
                        <td class="mono">{{ $holder->sequence }}</td>
                        <td style="font-weight:600;">
                            {{ $holder->legal_name }}
                            @if($holder->is_original_creator)
                            <span style="margin-left:4px;font-size:7px;font-weight:700;letter-spacing:.05em;color:#8A5F14;text-transform:uppercase;">{{ $isFr ? 'créateur' : 'creator' }}</span>
                            @endif
                        </td>
                        <td>{{ $entityLabels[$holder->entity_type] ?? $holder->entity_type }}</td>
                        <td>{{ $holder->country_code ? $countryName($holder->country_code) . ' (' . strtoupper($holder->country_code) . ')' : ($isFr ? 'Non inscrit' : 'Not recorded') }}</td>
                        <td class="mono">{{ $holder->owned_from ? Carbon::parse($holder->owned_from)->format('Y-m-d') : '—' }}</td>
                        <td class="mono">{{ $holder->owned_until ? Carbon::parse($holder->owned_until)->format('Y-m-d') : ($isFr ? 'en cours' : 'present') }}</td>
                        <td class="mono">{{ $holder->owner_ref ?: '—' }}</td>
                        <td>{{ $levelLabels[$holder->verification_level] ?? $holder->verification_level }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @else
                <p class="note">{{ $isFr ? 'Aucune ligne de propriété au registre.' : 'No ownership row has been recorded on this register.' }}</p>
                @endif
            </div>
        </section>

        {{-- ══ K · transfer certificates · L · the full journey ══ --}}
        <div style="display:grid;grid-template-columns:1fr 372px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">K</span><h2>{{ $isFr ? 'Certificats de transfert' : 'Transfer certificates' }}</h2></div>
                <div class="sc-body">
                    @if($transfers)
                    <table class="reg">
                        <thead><tr>
                            <th style="width:158px;">{{ $isFr ? 'N° de certificat' : 'Certificate no.' }}</th>
                            <th style="width:104px;">{{ $isFr ? 'Nature' : 'Type' }}</th>
                            <th style="width:82px;">{{ $isFr ? 'Date' : 'Date' }}</th>
                            <th>{{ $isFr ? 'Lieu' : 'Place' }}</th>
                            <th style="width:96px;">{{ $isFr ? 'Valeur déclarée' : 'Declared value' }}</th>
                            <th style="width:74px;">{{ $isFr ? 'Statut' : 'Status' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($transfers as $t)
                        <tr>
                            <td class="mono">{{ $t->certificate_no }}</td>
                            <td>{{ $transferLabels[$t->transfer_type] ?? $t->transfer_type }}</td>
                            <td class="mono">{{ $t->transferred_at ? Carbon::parse($t->transferred_at)->format('Y-m-d') : '—' }}</td>
                            <td>{{ trim(($t->transfer_city ?: '') . ($t->transfer_country ? ', ' . strtoupper($t->transfer_country) : ''), ', ') ?: '—' }}</td>
                            {{-- Value prints only when the parties declared one
                                 and did not mark it private. A blank here is a
                                 figure nobody gave us, never a free one. --}}
                            <td class="mono">
                                @if($t->value_is_private)
                                    {{ $isFr ? 'privée' : 'private' }}
                                @elseif($t->declared_value !== null && $t->currency)
                                    {{ $t->currency }} {{ number_format((float) $t->declared_value, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $t->status }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'Les valeurs et références de paiement sont déclarées par les parties. La plateforme n\'est pas partie à la vente et n\'en reçoit pas le prix ; elle ne facture que ses propres frais de service.'
                           : 'Values and payment references are declared by the parties. The platform is not a party to the sale and does not receive the price; it bills only its own service fees.' }}
                    </p>
                    @else
                    <p class="note">{{ $isFr ? 'Aucun certificat de transfert n\'a été émis pour cette pièce.' : 'No transfer certificate has been issued for this piece.' }}</p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">L</span><h2>{{ $isFr ? 'Parcours géographique (détail)' : 'Geographic journey (detail)' }}</h2></div>
                <div class="sc-body">
                    @if($journey)
                    <table class="reg">
                        <thead><tr>
                            <th style="width:22px;">#</th>
                            <th>{{ $isFr ? 'Pays' : 'Country' }}</th>
                            <th style="width:76px;">{{ $isFr ? 'Première date' : 'First dated' }}</th>
                            <th>{{ $isFr ? 'Motif inscrit' : 'Recorded reason' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($journey as $i => $leg)
                        <tr>
                            <td class="mono">{{ $i + 1 }}</td>
                            <td>{{ $countryName($leg['country']) }} ({{ $leg['country'] }})</td>
                            <td class="mono">{{ $leg['date'] ?? '—' }}</td>
                            <td>{{ $leg['reason'] }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="note" style="font-size:9px;">
                        {{ $isFr
                           ? 'Aucun pays n\'est inscrit au registre pour cette pièce.'
                           : 'No country has been recorded on the register for this piece.' }}
                    </p>
                    @endif
                </div>
            </section>
        </div>

        {{-- ══ M… · the documented events, grouped by type ══════════════════
             Each type gets its own block and its own empty sentence, because
             "no exhibition recorded" and "no appraisal recorded" are different
             findings about a file and a shared "none" would blur them. The
             organisation is printed as a plain register entry: an exhibition
             row says where the piece was shown, and no wording here turns that
             into an endorsement by the institution that showed it. --}}
        @php $ltrs = ['M','N','O','P','Q','R','S','T','U','V']; $li = 0; @endphp
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:8px 20px 0;align-items:start;">
            @foreach($eventSections as $type => [$secTitle, $secIcon, $secEmpty])
            <section class="sc">
                <div class="sc-head"><span class="ltr">{{ $ltrs[$li++] ?? '·' }}</span><h2>{{ $secTitle }}</h2>
                    <span class="hint">{{ count($grouped[$type]) }}</span></div>
                <div class="sc-body">
                    @if($grouped[$type])
                    <table class="reg">
                        <thead><tr>
                            <th style="width:18px;">#</th>
                            <th>{{ $isFr ? 'Intitulé inscrit' : 'Recorded title' }}</th>
                            <th style="width:118px;">{{ $isFr ? 'Organisation' : 'Organisation' }}</th>
                            <th style="width:96px;">{{ $isFr ? 'Lieu / pays' : 'Venue / country' }}</th>
                            <th style="width:74px;">{{ $isFr ? 'Date' : 'Date' }}</th>
                            <th style="width:78px;">{{ $isFr ? 'Référence' : 'Reference' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($grouped[$type] as $i => $ev)
                        <tr>
                            <td class="mono">{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $ev->title }}</td>
                            <td>{{ $ev->organisation ?: '—' }}</td>
                            <td>{{ trim(($ev->venue ?: '') . ($ev->country ? ', ' . strtoupper($ev->country) : ''), ', ') ?: '—' }}</td>
                            <td class="mono">
                                {{ $ev->started_on ? Carbon::parse($ev->started_on)->format('Y-m-d') : '—' }}
                                @if($ev->ended_on)<br>{{ Carbon::parse($ev->ended_on)->format('Y-m-d') }}@endif
                            </td>
                            <td class="mono">{{ $ev->reference_no ?: ($ev->certificate_ref ?: '—') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{-- Confirmation is a separate act by a person who checked
                         the institution's own record, so it is stated per row
                         rather than assumed for the block. --}}
                    <p class="note" style="margin-top:4px;">
                        @php $confirmed = count(array_filter($grouped[$type], fn ($e) => (bool) $e->is_verified)); @endphp
                        {{ $isFr
                           ? $confirmed . ' sur ' . count($grouped[$type]) . ' confirmée(s) auprès de l\'institution ; les autres sont inscrites telles que déclarées.'
                           : $confirmed . ' of ' . count($grouped[$type]) . ' confirmed against the institution\'s own record; the rest are recorded as declared.' }}
                    </p>
                    @else
                    <p class="note">{{ $secEmpty }}</p>
                    @endif
                </div>
            </section>
            @endforeach

            @if($otherEvents)
            <section class="sc" style="grid-column:1 / -1;">
                <div class="sc-head"><span class="ltr">·</span><h2>{{ $isFr ? 'Autres entrées du registre' : 'Other register entries' }}</h2></div>
                <div class="sc-body">
                    <table class="reg">
                        <tbody>
                        @foreach($otherEvents as $ev)
                        <tr>
                            <td style="width:120px;">{{ Str::headline($ev->type) }}</td>
                            <td style="font-weight:600;">{{ $ev->title }}</td>
                            <td style="width:140px;">{{ $ev->organisation ?: '—' }}</td>
                            <td class="mono" style="width:86px;">{{ $ev->started_on ? Carbon::parse($ev->started_on)->format('Y-m-d') : '—' }}</td>
                            <td class="mono" style="width:96px;">{{ $ev->reference_no ?: '—' }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif
        </div>

        {!! $sheetFoot(2) !!}
    </div>
    </div>
    </section>

    {{-- ══════════════════════════ SHEET 3 ══════════════════════════════ --}}
    <section class="ppc-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'PPC'])
    <div class="ppc-frame">
    <div class="ppc-paper">
        <svg class="ppc-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,60)" opacity=".38"><use href="#coaGuillocheRosette"/></g>
        </svg>

        <div style="display:flex;align-items:flex-end;gap:14px;padding:13px 20px 0;">
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237" style="height:38px;width:auto;">
            <div style="flex:1;min-width:0;">
                <p style="font-family:'Playfair Display',Georgia,serif;font-size:19px;font-weight:700;color:#3E0C1B;line-height:1.1;">{{ $docTitle }} (PPC)</p>
                <p style="font-size:9px;font-weight:700;letter-spacing:.1em;color:#8A5F14;text-transform:uppercase;">{{ $pageOf(3) }} — {{ $sheetSub[3] }}</p>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin:9px 20px 0;padding:5px 12px;border-radius:5px;
                    background:#4A0F20;border:1px solid #C9942E;">
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;letter-spacing:.04em;">{{ $certNo }}</span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span style="font-size:8.5px;font-weight:700;letter-spacing:.06em;color:#C9942E;text-transform:uppercase;">
                {{ $isFr ? 'Feuillets 1 · 2 · 3 forment un seul dossier' : 'Sheets 1 · 2 · 3 form one dossier' }}
            </span>
        </div>

        {{-- ══ W · related register documents · X · Legacy Index ══ --}}
        <div style="display:grid;grid-template-columns:392px 1fr;gap:8px;margin:9px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">W</span><h2>{{ $isFr ? 'Documents liés au registre' : 'Related register documents' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:196px;">
                        @foreach($related as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="note" style="margin-top:5px;">
                        {{-- The artwork lists ten document types with an
                             "Active" chip apiece. Only references the register
                             actually holds are listed here; a document nobody
                             issued has no number, and a row for it would turn an
                             empty drawer into a filing cabinet. --}}
                        {{ $isFr
                           ? 'Seuls les documents réellement émis figurent ici, avec leur numéro vérifiable au registre.'
                           : 'Only documents actually issued are listed, each by the number the register can be searched on.' }}
                    </p>
                    @if($certNo)
                    <p class="note" style="margin-top:4px;">
                        {{ $isFr ? 'Tous les certificats de cette pièce' : 'Every certificate for this piece' }} :
                        <span class="mono">{{ Str::after(route('certificate.hub', ['slug' => $product->slug, 'lang' => $lang]), '://') }}</span>
                    </p>
                    @endif
                </div>
            </section>

            {{-- ══ The Legacy Index, showing its working ══════════════════════
                 The artwork prints a designed-in 96/100 inside a gold medal.
                 What is printed here is whatever legacyIndex() returned, over
                 whatever denominator the assessable categories added up to —
                 the two differ precisely because categories the register could
                 not assess drop out rather than scoring zero. Each row carries
                 the basis it scored on, so the holder can read the number,
                 disagree with it, and see which missing document would move it.
                 Category fractions are printed without spaces around the slash
                 and unassessed ones print the word instead, so a genuine "0 of
                 20" can never be confused with a category nobody could judge. --}}
            <section class="sc">
                <div class="sc-head"><span class="ltr">X</span><h2>{{ $isFr ? 'Indice de patrimoine' : 'Legacy Index' }}</h2>
                    <span class="hint">{{ $isFr ? 'Exhaustivité du dossier' : 'Completeness of the file' }}</span></div>
                <div class="sc-body" style="display:grid;grid-template-columns:172px 1fr;gap:12px;">
                    <div style="text-align:center;">
                        <div style="width:150px;height:150px;margin:0 auto;border-radius:50%;border:3px solid #C9942E;
                                    background:radial-gradient(circle at 50% 35%,#5C1327,#2B0A15);display:flex;
                                    flex-direction:column;align-items:center;justify-content:center;">
                            <span style="font-size:8px;font-weight:700;letter-spacing:.14em;color:#E4CDA0;text-transform:uppercase;">{{ $isFr ? 'Indice' : 'Legacy index' }}</span>
                            <span class="mono" style="font-size:34px;font-weight:800;line-height:1.05;color:#F4EAD4;">{{ $legacy['total'] }}</span>
                            <span class="mono" style="font-size:13px;font-weight:700;color:#C9942E;">/ {{ $legacy['max'] }}</span>
                            <span style="margin-top:3px;font-size:8px;font-weight:700;letter-spacing:.1em;color:#E4CDA0;">{{ $bandWord }}</span>
                        </div>
                        <p class="mono" style="margin-top:6px;font-size:11px;font-weight:700;color:{{ $bandColour }};">
                            {{ $legacy['total'] }} / {{ $legacy['max'] }}{{ $legacyPct !== null ? ' · ' . $legacyPct . '%' : '' }}
                        </p>
                    </div>
                    <div>
                        <table class="reg">
                            <thead><tr>
                                <th>{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                <th style="width:74px;">{{ $isFr ? 'Score' : 'Score' }}</th>
                                <th>{{ $isFr ? 'Sur quoi le score repose' : 'What the score rests on' }}</th>
                            </tr></thead>
                            <tbody>
                            @foreach($legacy['categories'] as $key => $cat)
                            <tr>
                                <td style="font-weight:600;">{{ $catLabels[$key] ?? Str::headline($key) }}</td>
                                <td class="mono" style="font-weight:700;color:{{ $cat['max'] === 0 ? '#5A6B7E' : '#0F7A34' }};">
                                    @if($cat['max'] === 0)
                                    <span style="display:inline-flex;align-items:center;gap:3px;font-family:Poppins,sans-serif;font-size:8px;
                                                 padding:1px 5px;border-radius:9px;border:1px dashed #9AA6B4;color:#5A6B7E;">
                                        {{ $unassessedWord }}
                                    </span>
                                    @else
                                    {{ $cat['score'] }}/{{ $cat['max'] }}
                                    @endif
                                </td>
                                <td>{{ $cat['basis'] }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <p class="note" style="margin-top:5px;padding-top:5px;border-top:1px solid #EFE7D5;">
                            {{ $isFr
                               ? 'L\'indice mesure l\'exhaustivité du dossier documenté — completeness of the documented record — et non la valeur de l\'œuvre ni le mérite de son auteur.'
                               : 'The index measures the completeness of the documented record, not the merit of the work or the maker.' }}
                        </p>
                        <p class="note">
                            {{ $isFr
                               ? 'Une catégorie que le registre ne peut pas juger sort du total : elle n\'est ni notée zéro ni comptée dans le dénominateur, faute de quoi une pièce serait pénalisée pour n\'avoir jamais eu besoin d\'un restaurateur.'
                               : 'A category the register cannot judge leaves the total: it is neither scored zero nor counted in the denominator, or a piece would be punished for never having needed a restorer.' }}
                        </p>
                    </div>
                </div>
            </section>
        </div>

        {{-- ══ Y · audit trail · Z · verification statement ══ --}}
        <div style="display:grid;grid-template-columns:1fr 320px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">Y</span><h2>{{ $isFr ? 'Journal du dossier' : 'Audit trail' }}</h2>
                    <span class="hint">{{ count($timeline) }} {{ $isFr ? 'entrées' : 'entries' }}</span></div>
                <div class="sc-body">
                    @if($timeline)
                    <table class="reg">
                        <thead><tr>
                            <th style="width:22px;">#</th>
                            <th style="width:78px;">{{ $isFr ? 'Date (ISO 8601)' : 'Date (ISO 8601)' }}</th>
                            <th style="width:96px;">{{ $isFr ? 'Nature' : 'Kind' }}</th>
                            <th>{{ $isFr ? 'Entrée inscrite' : 'Recorded entry' }}</th>
                            <th style="width:132px;">{{ $isFr ? 'Référence' : 'Reference' }}</th>
                            <th style="width:96px;">{{ $isFr ? 'Confirmée' : 'Confirmed' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($timeline as $i => $entry)
                        <tr>
                            <td class="mono">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="mono">{{ $entry['date'] ?? ($isFr ? 'sans date' : 'undated') }}</td>
                            <td>{{ $typeWords[$entry['type']] ?? Str::headline($entry['type']) }}</td>
                            <td>{{ $entry['label'] }}@if($entry['organisation'] && $entry['organisation'] !== $entry['label'])<span style="color:#6B5A5F;"> · {{ $entry['organisation'] }}</span>@endif</td>
                            <td class="mono">{{ $entry['reference'] ?: '—' }}</td>
                            <td style="color:{{ $entry['verified'] ? '#0F7A34' : '#6B5A5F' }};">
                                {{ $entry['verified'] ? ($isFr ? 'Confirmée' : 'Confirmed') : ($isFr ? 'Telle que déclarée' : 'As declared') }}
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="note">{{ $isFr ? 'Aucune entrée au journal.' : 'No entry has been recorded in this log.' }}</p>
                    @endif
                    <p class="note" style="margin-top:5px;padding-top:5px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Le journal des certificats est chaîné par empreintes : chaque entrée porte celle de la précédente, de sorte qu\'une suppression invalide toutes les suivantes.'
                           : 'The certificate log is hash-chained: each entry carries its predecessor\'s digest, so a deletion invalidates every entry after it.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">Z</span><h2>{{ $isFr ? 'Portée du document' : 'What this document says' }}</h2></div>
                <div class="sc-body">
                    {{-- The artwork's statement claims the information was
                         "collected from verified sources" and calls the history
                         complete and verified. This says what is true: the
                         register publishes what was filed with it, marks what a
                         person confirmed, and creates no rights. --}}
                    <p style="font-size:8.5px;line-height:1.5;color:#241A1E;">
                        {{ $isFr
                           ? 'Ce certificat publie les faits inscrits au registre ArtisanHub237 pour cette pièce à la date d\'émission. Chaque ligne renvoie à une entrée du registre ; les lignes marquées « telles que déclarées » ont été fournies par une partie et non contrôlées auprès de l\'institution citée.'
                           : 'This certificate publishes the facts recorded on the ArtisanHub237 register for this piece at the date of issue. Every line points at a register row; lines marked "as declared" were supplied by a party and not checked against the institution named.' }}
                    </p>
                    <p style="margin-top:6px;font-size:8.5px;line-height:1.5;color:#241A1E;">
                        {{ $isFr
                           ? 'ArtisanHub237 est une société privée. Elle n\'est ni une administration ni un organisme public, n\'est pas partie aux ventes et n\'en reçoit pas le prix ; seuls ses propres frais de service lui sont réglés. Ce document ne crée, ne transfère et ne remplace aucun droit de propriété ou de propriété intellectuelle.'
                           : 'ArtisanHub237 is a private company. It is neither a government body nor a public authority, is not a party to any sale and does not receive the price; only its own service fees are paid to it. This document creates, transfers and replaces no ownership or intellectual property right.' }}
                    </p>
                    <p class="note" style="margin-top:6px;">
                        {{ $isFr
                           ? 'L\'exemplaire de référence est celui vérifié en ligne : une impression est une photographie du registre à un instant donné.'
                           : 'The authoritative copy is the one verified online: a printout is a photograph of the register at one moment.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ AA · the signature block ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">AA</span><h2>{{ $isFr ? 'Signature de l\'autorité de certification' : 'Certification authority signature' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div>
                    <p style="font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#5B4A50;">{{ $isFr ? 'EMPREINTE DU CONTENU (SHA-256)' : 'CONTENT HASH (SHA-256)' }}</p>
                    <p class="mono" style="margin-top:4px;font-size:9px;line-height:1.5;word-break:break-all;">{{ $coa?->content_hash ?: '—' }}</p>
                    @if($coa?->signature)
                    <p style="margin-top:7px;font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#5B4A50;">{{ $isFr ? 'SCEAU INTERNE (HMAC)' : 'INTERNAL SEAL (HMAC)' }}</p>
                    <p class="mono" style="margin-top:3px;font-size:8px;line-height:1.5;word-break:break-all;color:#6B5A5F;">{{ $coa->signature }}</p>
                    @endif
                    {{-- The artwork hangs four handwritten signatures here, two
                         of them belonging to named, real people. No specimen
                         signature exists in this platform; drawing one would
                         forge a real person's hand. The parties are printed as
                         text with their register references instead. --}}
                    <dl style="margin-top:7px;--kv:150px;">
                        <div class="kv"><dt>{{ $isFr ? 'Émetteur' : 'Issued by' }}</dt>
                            <dd>{{ $isFr ? 'ArtisanHub237 · Autorité de certification' : 'ArtisanHub237 · Certification Authority' }}</dd></div>
                        @if($gan)
                        <div class="kv"><dt>{{ $isFr ? 'Créateur au registre' : 'Creator on the register' }}</dt><dd class="mono">{{ $gan }}</dd></div>
                        @endif
                        @if($owner?->owner_ref)
                        <div class="kv"><dt>{{ $isFr ? 'Détenteur au registre' : 'Holder on the register' }}</dt><dd class="mono">{{ $owner->owner_ref }}</dd></div>
                        @endif
                    </dl>
                </div>
                <div>
                    @if($coa?->ca_signature)
                    <p style="font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#5B4A50;">{{ $isFr ? 'SIGNATURE NUMÉRIQUE (AC)' : 'DIGITAL SIGNATURE (CA)' }}</p>
                    <p class="mono" style="margin-top:4px;font-size:8.5px;line-height:1.5;word-break:break-all;">{{ $coa->ca_signature }}</p>
                    <p style="margin-top:4px;font-size:8.5px;color:#5B4A50;">
                        EdDSA · Ed25519 · <span class="mono">kid {{ $coa->ca_kid }}</span>
                    </p>
                    <p class="mono" style="margin-top:2px;font-size:8.5px;color:#0B4A2A;">/.well-known/jwks.json</p>
                    @else
                    <p class="note">{{ $isFr ? 'Ce certificat n\'a pas encore été signé par l\'autorité de certification.' : 'This certificate has not yet been signed by the certification authority.' }}</p>
                    @endif
                    <dl style="margin-top:7px;--kv:150px;">
                        @if($coa?->last_verified_at)
                        <div class="kv"><dt>{{ $isFr ? 'Dernière vérification' : 'Last verified' }}</dt>
                            <dd class="mono">{{ Carbon::parse($coa->last_verified_at)->utc()->format('Y-m-d H:i') }} UTC</dd></div>
                        @endif
                        <div class="kv"><dt>{{ $isFr ? 'Nombre de vérifications' : 'Verification count' }}</dt>
                            <dd class="mono">{{ (int) ($coa?->verification_count ?? 0) }}</dd></div>
                        @if($coa?->verification_pin)
                        <div class="kv"><dt>{{ $isFr ? 'Code de vérification' : 'Verification code' }}</dt>
                            <dd class="mono">{{ $coa->verification_pin }}</dd></div>
                        @endif
                    </dl>
                </div>
            </div>
        </section>

        {{-- ══ AB · verifiable features · AC · standards ══ --}}
        <div style="display:grid;grid-template-columns:1fr 320px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">AB</span><h2>{{ $isFr ? 'Éléments vérifiables' : 'Verifiable features' }}</h2></div>
                <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:4px 14px;">
                    @foreach($securityLegend as [$sIcon, $sTitle, $sNote])
                    <div style="display:flex;gap:7px;padding:3px 0;">
                        <span style="width:22px;height:22px;flex:none;border-radius:50%;border:1px solid #C9942E;
                                     background:#F6EEDC;color:#8A5F14;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $sIcon }}" style="width:12px;height:12px;"></i>
                        </span>
                        <div style="min-width:0;">
                            <p style="font-size:9px;font-weight:700;color:#3E0C1B;">{{ $sTitle }}</p>
                            <p class="note" style="word-break:break-word;">{{ $sNote }}</p>
                        </div>
                    </div>
                    @endforeach
                    <p class="note" style="grid-column:1 / -1;margin-top:5px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Cette liste ne contient que ce qu\'un lecteur peut vérifier sur un écran ou une impression ordinaire. Les mesures qui exigent une imprimerie de sécurité — encres, films, gaufrage, papier filigrané — ne sont pas revendiquées ici.'
                           : 'This list contains only what a reader can check on a screen or an ordinary printout. Measures that need a security printer — inks, films, embossing, watermarked stock — are not claimed here.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">AC</span><h2>{{ $isFr ? 'Normes appliquées' : 'Standards used' }}</h2></div>
                <div class="sc-body">
                    @foreach($standards as $std)
                    <div class="tick"><i data-lucide="check" style="color:#0F7A34;"></i><span>{{ $std }}</span></div>
                    @endforeach
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'Les devises ne sont imprimées que lorsqu\'une valeur déclarée existe, avec son code ISO 4217.'
                           : 'Currencies are printed only where a declared value exists, with its ISO 4217 code.' }}
                    </p>
                </div>
            </section>
        </div>

        {!! $sheetFoot(3) !!}
    </div>
    </div>
    </section>

    </article>
    </div>
    <p class="coa-hint no-print items-center gap-1.5 justify-center mt-2 text-[11.5px] text-[#6F6B60]">
        <i data-lucide="move-horizontal" class="w-3.5 h-3.5"></i>
        {{ $isFr ? 'Faites glisser pour voir toute la largeur du document.' : 'Drag sideways to see the full width of the document.' }}
    </p>

</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    lucide.createIcons();

    (function () {
        var box = document.getElementById('ppc-qr');
        if (box && window.QRCode && @json((bool) $verifyShort)) {
            new QRCode(box, {
                text: @json($verifyShort),
                width: 122, height: 122,
                colorDark: '#4A0F20', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* All three sheets are drawn at the artwork's 1024px width and scaled as a
       single unit, so the dossier never rearranges — it only gets smaller, and
       sheet 2 can never end up a different size from sheet 1. The wrapper is
       given the scaled height by hand, because a transform does not change the
       space an element reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.coa-page');
        if (!fit || !page) return;

        var MIN_SCALE = 0.62;

        function apply() {
            var w = fit.getBoundingClientRect().width;
            var k = Math.max(MIN_SCALE, Math.min(1, w / 1024));

            page.style.transform = k < 1 ? 'scale(' + k + ')' : '';
            fit.style.height = (page.offsetHeight * k) + 'px';

            var scaled = 1024 * k;
            fit.style.overflowX = scaled > w + 1 ? 'auto' : 'hidden';
            fit.classList.toggle('coa-pannable', scaled > w + 1);
        }

        apply();
        new ResizeObserver(apply).observe(fit);
        window.addEventListener('load', apply);
        document.querySelectorAll('.coa-page img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', apply);
        });
        window.addEventListener('beforeprint', apply);
    })();
</script>
</body>
</html>
