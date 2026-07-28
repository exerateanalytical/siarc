@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials; without them
    // those partials fatal, so they are set before anything else happens.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $w    = $workshop;
    $c    = $certificate;
    $insp = $inspection;

    $issued  = $c->issued_at  ? Carbon::parse($c->issued_at)  : null;
    $expires = $c->expires_at ? Carbon::parse($c->expires_at) : null;

    /* The sheet's own address is the verification address. /verifier answers for
       product certificates only, so printing it here would hand a reader a form
       that reports "not found" for a certificate that is perfectly valid — worse
       than printing no address at all. This URL is the register lookup, and it
       is what the QR carries. */
    $verifyUrl   = route('workshop.certificate', ['gwn' => $gwn, 'lang' => $lang]);
    $verifyShort = Str::after(route('workshop.certificate', ['gwn' => $gwn]), '://');

    /* Status, with the expiry given precedence over the column exactly as the
       register gives it: nothing sweeps this table, so a certificate whose
       inspection lapsed this morning still reads `active` in storage, and that
       is the one state somebody would present at a door. */
    $expired    = $expires && $expires->isPast();
    $statusMeta = [
        'active'    => [$isFr ? 'EN VIGUEUR' : 'IN FORCE',   '#0F7A34', 'check-circle-2'],
        'expired'   => [$isFr ? 'EXPIRÉ'     : 'EXPIRED',    '#B4141B', 'clock-alert'],
        'suspended' => [$isFr ? 'SUSPENDU'   : 'SUSPENDED',  '#A8802A', 'pause-circle'],
        'revoked'   => [$isFr ? 'RÉVOQUÉ'    : 'REVOKED',    '#B4141B', 'shield-off'],
    ][$c->status] ?? [Str::upper((string) $c->status), '#6B6659', 'help-circle'];

    if ($expired) {
        $statusMeta = [$isFr ? 'EXPIRÉ' : 'EXPIRED', '#B4141B', 'clock-alert'];
    }

    $unassessedWord = $isFr ? 'Non évalué' : 'Not assessed';
    $neverAssessed  = $isFr ? 'Jamais évalué' : 'Never assessed';
    $dash           = '—';

    /* Rows are filtered on filled() everywhere. A label printed against a blank
       reads as "none", and on this document "Fire safety equipment: —" and
       "Fire safety equipment: none fitted" are opposite findings. */
    $rows = fn (array $set) => collect($set)->filter(fn ($r) => filled($r[1]))->values();

    $ratingLabels = [
        'excellent'  => $isFr ? 'EXCELLENT'  : 'EXCELLENT',
        'very good'  => $isFr ? 'TRÈS BON'   : 'VERY GOOD',
        'good'       => $isFr ? 'BON'        : 'GOOD',
        'fair'       => $isFr ? 'PASSABLE'   : 'FAIR',
        'poor'       => $isFr ? 'FAIBLE'     : 'POOR',
        'unassessed' => $isFr ? 'NON ÉVALUÉ' : 'NOT ASSESSED',
    ];
    $ratingColour = fn (string $r) => match ($r) {
        'excellent', 'very good' => '#0F7A34',
        'good'                   => '#4E7A15',
        'fair'                   => '#A8802A',
        'poor'                   => '#B4141B',
        default                  => '#5A6B7E',
    };

    $assessLabels = [
        'infrastructure' => $isFr ? 'Infrastructure'   : 'Infrastructure',
        'equipment'      => $isFr ? 'Équipement'       : 'Equipment',
        'workforce'      => $isFr ? 'Main-d\'œuvre'    : 'Workforce',
        'safety'         => $isFr ? 'Sécurité'         : 'Safety',
        'compliance'     => $isFr ? 'Conformité'       : 'Compliance',
        'sustainability' => $isFr ? 'Durabilité'       : 'Sustainability',
        'documentation'  => $isFr ? 'Documentation'    : 'Documentation',
    ];
    $exportLabels = [
        'packaging'           => $isFr ? 'Emballage à l\'export'   : 'Export packaging',
        'shipping_experience' => $isFr ? 'Expérience d\'expédition' : 'Shipping experience',
        'customs'             => $isFr ? 'Formalités douanières'    : 'Customs paperwork',
        'traceability'        => $isFr ? 'Traçabilité'              : 'Traceability',
    ];

    $equipmentLabels = [
        'major_machine' => $isFr ? 'Machine principale' : 'Major machine',
        'hand_tool'     => $isFr ? 'Outil à main'       : 'Hand tool',
        'carving_tool'  => $isFr ? 'Outil de sculpture' : 'Carving tool',
        'power_tool'    => $isFr ? 'Outil électrique'   : 'Power tool',
        'kiln_or_oven'  => $isFr ? 'Four / séchoir'     : 'Kiln or oven',
        'safety'        => $isFr ? 'Sécurité'           : 'Safety',
        'extraction'    => $isFr ? 'Extraction'         : 'Extraction',
        'drying'        => $isFr ? 'Séchage'            : 'Drying',
        'measuring'     => $isFr ? 'Mesure'             : 'Measuring',
        'finishing'     => $isFr ? 'Finition'           : 'Finishing',
        'other'         => $isFr ? 'Autre'              : 'Other',
    ];

    $complianceLabels = [
        'business_licence'  => $isFr ? 'Licence d\'exploitation'      : 'Business licence',
        'tax_registration'  => $isFr ? 'Immatriculation fiscale'      : 'Tax registration',
        'environmental'     => $isFr ? 'Conformité environnementale'  : 'Environmental compliance',
        'fire_safety'       => $isFr ? 'Sécurité incendie'            : 'Fire safety',
        'health_safety'     => $isFr ? 'Hygiène et sécurité'          : 'Health and safety',
        'labour'            => $isFr ? 'Droit du travail'             : 'Labour law',
        'insurance'         => $isFr ? 'Assurance'                    : 'Insurance coverage',
        'export_packaging'  => $isFr ? 'Emballage à l\'exportation'   : 'Export packaging',
    ];

    /* Four states, four appearances, and the whole point of the table is that
       `unassessed` cannot be mistaken for `valid`. The register defaults every
       licence row to unassessed precisely because recording that an obligation
       exists is not evidence about it, so this is where that care is either
       kept or thrown away. */
    $complianceMeta = [
        'valid'          => [$isFr ? 'Valide'         : 'Valid',          '#0F7A34', 'check-circle-2'],
        'expired'        => [$isFr ? 'Expiré'         : 'Expired',        '#B4141B', 'clock-alert'],
        'invalid'        => [$isFr ? 'Non conforme'   : 'Not compliant',  '#B4141B', 'x-circle'],
        'pending'        => [$isFr ? 'En cours'       : 'Pending',        '#A8802A', 'loader'],
        'not_applicable' => [$isFr ? 'Sans objet'     : 'Does not apply', '#5A6B7E', 'minus-circle'],
        'unassessed'     => [$neverAssessed,                              '#5A6B7E', 'circle-dashed'],
    ];

    $methodLabels = [
        'on_site'         => $isFr ? 'Visite sur site'      : 'On-site visit',
        'photographic'    => $isFr ? 'Dossier photographique' : 'Photographic record',
        'remote'          => $isFr ? 'À distance'           : 'Remote',
        'document_review' => $isFr ? 'Examen documentaire'  : 'Document review',
    ];
    $outcomeMeta = [
        'passed'                 => [$isFr ? 'Satisfaisante' : 'Passed', '#0F7A34'],
        'passed_with_conditions' => [$isFr ? 'Satisfaisante sous conditions' : 'Passed with conditions', '#A8802A'],
        'failed'                 => [$isFr ? 'Non satisfaisante' : 'Failed', '#B4141B'],
        'inconclusive'           => [$isFr ? 'Non concluante' : 'Inconclusive', '#5A6B7E'],
    ];

    /* The tick map. Only the keys the register actually returns are looked up
       here; a key it does not carry is a check this platform does not perform,
       and the sheet says so in the caption rather than drawing a cross. */
    $checkLabels = [
        'workshop_registered'   => $isFr ? 'Atelier inscrit au registre'          : 'Workshop on the register',
        'owner_account_linked'  => $isFr ? 'Compte propriétaire rattaché'         : 'Owner account linked',
        'address_recorded'      => $isFr ? 'Adresse enregistrée'                  : 'Address recorded',
        'floor_area_measured'   => $isFr ? 'Surface au sol mesurée'               : 'Floor area measured',
        'equipment_inventoried' => $isFr ? 'Équipement inventorié'                : 'Equipment inventoried',
        'inspection_on_file'    => $isFr ? 'Rapport d\'inspection au dossier'     : 'Inspection report on file',
        'inspected_on_site'     => $isFr ? 'Inspection menée sur place'           : 'Inspected on site',
        'inspection_passed'     => $isFr ? 'Inspection satisfaisante'             : 'Inspection passed',
        'licence_checked'       => $isFr ? 'Au moins une licence vérifiée'        : 'At least one licence checked',
        'currently_verified'    => $isFr ? 'Atelier actuellement vérifié'         : 'Workshop currently verified',
    ];

    $eventLabels = [
        'opened'           => $isFr ? 'Atelier ouvert au registre' : 'Workshop opened on the register',
        'submitted'        => $isFr ? 'Dossier déposé'             : 'Submitted for inspection',
        'inspection_begun' => $isFr ? 'Inspection engagée'         : 'Inspection begun',
        'verified'         => $isFr ? 'Atelier vérifié'            : 'Workshop verified',
        'issued'           => $isFr ? 'Certificat émis'            : 'Certificate issued',
        'suspended'        => $isFr ? 'Atelier suspendu'           : 'Workshop suspended',
        'revoked'          => $isFr ? 'Atelier révoqué'            : 'Workshop revoked',
    ];

    $securityLegend = collect([
        ['pen-tool',     $isFr ? 'Signature Ed25519 (EdDSA)' : 'Ed25519 (EdDSA) signature',
            $isFr ? 'Vérifiable avec la clé publiée' : 'Checkable against the published key'],
        ['fingerprint',  $isFr ? 'Empreinte SHA-256' : 'SHA-256 content hash',
            $isFr ? 'Calculée sur les faits certifiés' : 'Computed over the certified facts'],
        ['key-round',    $isFr ? 'Clé publique publiée' : 'Published public key', '/.well-known/jwks.json'],
        ['hash',         $isFr ? 'UUID du certificat' : 'Certificate UUID',
            $isFr ? 'Identifiant unique, RFC 4122' : 'Unique identifier, RFC 4122'],
        ['list-ordered', $isFr ? 'Numérotation de série' : 'Serial numbering',
            $isFr ? 'Séquence continue au registre' : 'One unbroken sequence on the register'],
        ['qr-code',      $isFr ? 'Vérification par QR' : 'QR verification',
            $isFr ? 'Renvoie au registre en ligne' : 'Resolves to the online register'],
        ['link-2',       $isFr ? 'Journal chaîné par empreintes' : 'Hash-chained event log',
            $isFr ? 'Chaque entrée référence la précédente' : 'Each entry carries its predecessor\'s digest'],
        ['spline',       $isFr ? 'Guilloché' : 'Guilloché',
            $isFr ? 'Tracé hypotrochoïde, vectoriel' : 'Hypotrochoid line work, vector'],
        ['type',         $isFr ? 'Microtexte' : 'Microtext',
            $isFr ? 'Texte réel de 1,6 px, lisible au zoom' : 'Real 1.6px type, resolves on zoom'],
        ['grid-2x2',     $isFr ? 'Trame anti-copie' : 'Anti-copy screen',
            $isFr ? 'Se dégrade à la reproduction' : 'Degrades under reproduction'],
    ]);

    $title = $isFr ? 'CERTIFICAT DE VÉRIFICATION D\'ATELIER' : 'WORKSHOP VERIFICATION CERTIFICATE';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $title }} — {{ $w->name }}">
    <title>{{ $title }} — {{ $c->certificate_no }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* One sheet drawn at the artwork's own 1024px width and scaled as a
           single unit by the script at the foot of the page, exactly as its
           siblings are. Nothing here reflows: on a narrow screen the whole
           sheet gets smaller, so a printed copy and a phone show the same
           document in the same arrangement. */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .coa-page { width: 1024px; transform-origin: top left; font-size: 12px; line-height: 1.3; color: #14251A; }
        .coa-page, .coa-page * { font-size: revert-layer; }

        .wvc-sheet { background:#05301C; padding:9px; border-radius:18px; position:relative; }
        .wvc-frame {
            padding:22px; position:relative; border-radius:10px;
            background:
                repeating-radial-gradient(circle at 13px 11px, rgba(0,0,0,0) 0 4.6px, #C9942E 4.6px 5.8px, rgba(0,0,0,0) 5.8px 13px),
                repeating-radial-gradient(circle at 0 11px,   rgba(0,0,0,0) 0 4.6px, #A87C22 4.6px 5.6px, rgba(0,0,0,0) 5.6px 13px),
                #0A4127;
            background-size:26px 22px, 26px 22px, auto;
            box-shadow: inset 0 0 0 1.2px #C9942E;
        }
        .wvc-paper { width:961px; position:relative; background:#FCF6EC; box-shadow:0 0 0 1.2px #C9942E; }
        .wvc-guilloche { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:.34; z-index:0; }
        .wvc-paper > *:not(.wvc-guilloche) { position:relative; z-index:1; }

        /* Section cards, numbered the way the artwork numbers them. The numbers
           are how a reader refers to a panel out loud — "section 8 says the
           insurance was never checked" — so they are rendered, not dropped. */
        .sc      { border:1px solid #C4BFA6; border-radius:7px; background:#FFFDF7; overflow:hidden; }
        .sc-head { display:flex; align-items:center; gap:7px; min-height:22px; padding:3px 9px;
                   background:linear-gradient(180deg,#125C33,#0A4127); }
        .sc-head .ltr { min-width:14px; height:14px; padding:0 3px; border-radius:3px; flex:none; background:#C9942E; color:#0A4127;
                        font-size:9px; font-weight:800; display:flex; align-items:center; justify-content:center; }
        .sc-head h2  { font-size:9.5px; font-weight:700; letter-spacing:.075em; text-transform:uppercase; color:#F4EAD4; }
        .sc-body { padding:8px 10px 9px; }

        .kv     { display:grid; grid-template-columns:var(--kv,140px) 1fr; column-gap:9px;
                  padding:2px 0; border-bottom:1px solid #EFE7D5; align-items:baseline; }
        .kv:last-child { border-bottom:0; }
        .kv dt  { font-size:8.5px; font-weight:500; color:#4E5B50; }
        .kv dd  { font-size:8.5px; font-weight:600; color:#14251A; word-break:break-word; }
        .mono   { font-family:ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing:-.2px; }
        .tick   { display:flex; align-items:flex-start; gap:6px; padding:2.2px 0; font-size:9px; color:#1B2E20; }
        .tick svg { width:11px; height:11px; flex:none; margin-top:1px; }
        .note   { font-size:8px; line-height:1.45; color:#5A6357; }

        /* The unassessed chip. Dashed, slate, and never green: the border is
           the part that carries the meaning at a glance, and a dashed outline
           is the one treatment that survives a black-and-white photocopy. */
        .chip-un { display:inline-flex; align-items:center; gap:4px; padding:0 5px; height:14px;
                   border:1px dashed #8A9490; border-radius:3px; background:#F1F2EE;
                   font-size:8px; font-weight:700; letter-spacing:.03em; color:#4A554E; text-transform:uppercase; }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa-fit { overflow: visible; }
            @page { size: A4 portrait; margin: 5mm; }
        }
    </style>
</head>
<body class="bg-[#E7E9E4] text-[#14251A] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

@include('pages.partials.coa-ornaments')
@include('pages.partials.coa-security')

<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="wvcGoldRule" x1="0" y1="0" x2="1" y2="0">
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
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat de vérification d\'atelier' : 'Workshop verification certificate' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[6px]">
    <article class="coa-page">

    <section class="wvc-sheet cert-band-host">
    @include('pages.partials.certificate-band', ['code' => 'WVC'])
    <div class="wvc-frame">
    <div class="wvc-paper">

        <svg class="wvc-guilloche" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(0,20)" opacity=".5"><use href="#coaGuillocheRosette"/></g>
            <g transform="translate(0,190) scale(1,.9)" opacity=".33"><use href="#coaGuillocheRosette"/></g>
        </svg>

        {{-- ══ Header ══ --}}
        <div style="padding:12px 96px 0;text-align:center;">
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237"
                 style="height:58px;width:auto;max-width:100%;margin:0 auto;display:block;">
            <p style="margin-top:2px;font-size:10px;font-weight:600;letter-spacing:.26em;color:#4E5B50;">
                {{ $isFr ? 'RELIER L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
            </p>
            <h1 style="margin-top:8px;font-family:'Playfair Display',Georgia,serif;font-size:36px;line-height:1;
                       font-weight:700;color:#0A2617;letter-spacing:.01em;display:inline-block;
                       transform:scaleX(.93);transform-origin:center;white-space:nowrap;">
                {{ $title }} (WVC)
            </h1>
            <div style="margin-top:5px;display:flex;align-items:center;justify-content:center;gap:12px;">
                <svg width="250" height="3" aria-hidden="true"><rect width="250" height="3" fill="url(#wvcGoldRule)"/></svg>
                <i data-lucide="hammer" style="width:15px;height:15px;color:#8A6410;"></i>
                <svg width="250" height="3" aria-hidden="true"><rect width="250" height="3" fill="url(#wvcGoldRule)"/></svg>
            </div>
            {{-- The artwork's strap says the workshop "has been verified and meets
                 the standards of the ArtisanHub237 trust infrastructure". There is
                 no published standard to meet, so the line says what was actually
                 done: a named inspector visited, and this is their record. --}}
            <p style="margin:8px auto 0;display:inline-block;padding:5px 18px;border-radius:5px;
                      background:linear-gradient(180deg,#125C33,#0A4127);border:1px solid #C9942E;
                      font-size:9.5px;font-weight:700;letter-spacing:.08em;color:#F0DFB4;text-transform:uppercase;">
                {{ $isFr
                   ? 'Registre de l\'inspection d\'un atelier de production, de ses installations et de ses moyens'
                   : 'A register of one workshop inspection, its facility, its equipment and its people' }}
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin:11px 20px 0;padding:5px 12px;border-radius:5px;
                    background:#0A3D24;border:1px solid #C9942E;">
            <span class="mono" style="font-size:10px;font-weight:700;color:#F4EAD4;letter-spacing:.04em;">{{ $c->certificate_no }}</span>
            <span style="flex:1;height:1px;background:rgba(201,148,46,.5);"></span>
            <span class="mono" style="font-size:10px;font-weight:700;color:#C9942E;letter-spacing:.04em;">{{ $gwn }}</span>
        </div>

        @php
            /* ── Section 1 ── Everything a third party needs to look this
               document up, check its signature and tell it apart from a later
               version of itself. The key id is printed beside the signature
               because the signature is worthless without knowing which key to
               check it against, and that key is published at a URL we do not
               control the reading of. */
            $identityRows = $rows([
                [$isFr ? 'N° de certificat' : 'Certificate number', $c->certificate_no, true],
                [$isFr ? 'UUID du certificat' : 'Certificate UUID', $c->uuid, true],
                [$isFr ? 'N° mondial d\'atelier (GWN)' : 'Global workshop number (GWN)', $gwn, true],
                [$isFr ? 'UUID de l\'atelier' : 'Workshop UUID', $w->uuid, true],
                [$isFr ? 'N° de registre artisan (GAN)' : 'Artisan register no. (GAN)', $business?->gan, true],
                [$isFr ? 'Version du certificat' : 'Certificate version', $c->version, true],
                [$isFr ? 'Empreinte du contenu (SHA-256)' : 'Content hash (SHA-256)', $c->content_hash, true],
                [$isFr ? 'Signature Ed25519' : 'Ed25519 signature', $c->ca_signature, true],
                [$isFr ? 'Identifiant de clé (kid)' : 'Signing key id (kid)', $c->ca_kid, true],
                [$isFr ? 'Émis le (UTC, ISO 8601)' : 'Issued at (UTC, ISO 8601)',
                    $issued?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
                [$isFr ? 'Expire le (UTC, ISO 8601)' : 'Expires at (UTC, ISO 8601)',
                    $expires?->clone()->utc()->format('Y-m-d\TH:i:s\Z'), true],
                [$isFr ? 'Consultations enregistrées' : 'Recorded verifications', $c->verification_count, true],
            ]);

            /* ── Section 2 ── The workshop as the register holds it. Employee
               counts are deliberately absent here and live in section 7, where
               each one can be shown as recorded or not recorded rather than
               summed into a single figure that hides which parts were left
               blank. */
            $legalLabels = [
                'sole_trader'         => $isFr ? 'Entreprise individuelle' : 'Sole trader',
                'registered_business' => $isFr ? 'Entreprise enregistrée'  : 'Registered business',
                'cooperative'         => $isFr ? 'Coopérative'             : 'Cooperative',
                'company'             => $isFr ? 'Société'                 : 'Company',
                'informal'            => $isFr ? 'Activité informelle'     : 'Informal activity',
            ];

            $profileRows = $rows([
                [$isFr ? 'Nom de l\'atelier' : 'Workshop name', $w->name, false],
                [$isFr ? 'Entreprise' : 'Business', $business?->name_fr, false],
                [$isFr ? 'N° d\'enregistrement' : 'Registration number', $w->registration_no, true],
                [$isFr ? 'Type d\'atelier' : 'Workshop type', $w->workshop_type, false],
                [$isFr ? 'Statut juridique' : 'Legal status', $legalLabels[$w->legal_status] ?? $w->legal_status, false],
                [$isFr ? 'Créé le' : 'Established', $w->established_on ? Carbon::parse($w->established_on)->format('Y-m-d') : null, true],
                [$isFr ? 'Propriétaire' : 'Owner', $business?->user?->name, false],
                [$isFr ? 'Responsable d\'atelier' : 'Workshop manager', $w->manager_name, false],
            ]);

            /* ── Section 3 ── Coordinates are printed as what they are. The
               artwork stamps "GPS VERIFIED" across the photograph; geo_verified_at
               is set only when somebody stood at the pin, and where it is null
               the sheet says the coordinates were declared, because a map pin
               dropped by an owner is a claim about a place, not a survey of it. */
            $geoRows = $rows([
                [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)',
                    $w->country ? (($isFr ? 'Cameroun' : 'Cameroon') . ' (' . $w->country . ')') : null, false],
                [$isFr ? 'Région' : 'Region', $business?->region?->name_fr, false],
                [$isFr ? 'Département' : 'Division', $w->division, false],
                [$isFr ? 'Arrondissement' : 'Subdivision', $w->subdivision, false],
                [$isFr ? 'Ville' : 'City', $business?->city?->name_fr, false],
                [$isFr ? 'Village / communauté' : 'Village or community', $w->village ?: $w->community, false],
                [$isFr ? 'Adresse' : 'Address', $w->address, false],
                [$isFr ? 'Coordonnées GPS' : 'GPS coordinates',
                    ($w->gps_lat !== null && $w->gps_lng !== null) ? $w->gps_lat . ', ' . $w->gps_lng : null, true],
                // Never "0 m". An altitude nobody recorded drops the row.
                [$isFr ? 'Altitude' : 'Altitude', $w->altitude_m !== null ? $w->altitude_m . ' m' : null, true],
            ]);

            /* ── Section 4 ── Units on every figure, and null is a row that does
               not exist. "Drying areas: 0" would state that an inspector counted
               the drying rooms and found none; the register does not know that
               unless someone wrote it down. */
            $areaRow = function (string $label, $value, string $unit = '') {
                return [$label, $value === null ? null : (rtrim(rtrim(number_format((float) $value, 2, '.', ' '), '0'), '.') . $unit), true];
            };

            $accessLabels = [
                'poor' => $isFr ? 'Difficile' : 'Poor', 'fair' => $isFr ? 'Passable' : 'Fair',
                'good' => $isFr ? 'Bonne' : 'Good', 'excellent' => $isFr ? 'Excellente' : 'Excellent',
            ];

            $infraRows = $rows([
                $areaRow($isFr ? 'Surface totale' : 'Total floor area', $w->total_area_m2, ' m²'),
                [$isFr ? 'Salles de production' : 'Production rooms', $w->production_rooms, true],
                [$isFr ? 'Espaces de finition' : 'Finishing areas', $w->finishing_areas, true],
                [$isFr ? 'Espaces de stockage' : 'Storage areas', $w->storage_areas, true],
                [$isFr ? 'Espaces de séchage' : 'Drying areas', $w->drying_areas, true],
                [$isFr ? 'Espaces d\'emballage' : 'Packaging areas', $w->packaging_areas, true],
                [$isFr ? 'Espaces d\'exposition' : 'Display areas', $w->display_areas, true],
                [$isFr ? 'Eau' : 'Water supply', $w->water_supply, false],
                [$isFr ? 'Électricité' : 'Electricity', $w->electricity_supply, false],
                [$isFr ? 'Internet' : 'Internet', $w->internet, false],
                [$isFr ? 'Accessibilité' : 'Accessibility', $accessLabels[$w->accessibility] ?? $w->accessibility, false],
            ]);

            /* The two three-valued booleans get their own treatment: present,
               absent, or never looked at, and the third must not print like the
               second. An inspector recording "no emergency exits" is a finding
               against the workshop; nobody having checked is a gap in ours. */
            $safetyFacts = collect([
                [$isFr ? 'Équipement anti-incendie' : 'Fire safety equipment', $w->fire_safety_equipment],
                [$isFr ? 'Issues de secours' : 'Emergency exits', $w->emergency_exits],
            ]);
        @endphp

        {{-- ══ 1 · Certificate identity · verification ══ --}}
        <div style="display:grid;grid-template-columns:598px 315px;gap:8px;margin:9px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">1</span><h2>{{ $isFr ? 'Identité du certificat' : 'Certificate identity' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:214px;">
                        @foreach($identityRows as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}" style="{{ $m ? 'font-size:7.5px;' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                        <div class="kv">
                            <dt>{{ $isFr ? 'Statut du certificat' : 'Certificate status' }}</dt>
                            <dd style="display:flex;align-items:center;gap:5px;color:{{ $statusMeta[1] }};font-weight:700;">
                                <i data-lucide="{{ $statusMeta[2] }}" style="width:11px;height:11px;flex:none;"></i>{{ $statusMeta[0] }}
                            </dd>
                        </div>
                    </dl>
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'La signature ci-dessus se vérifie avec la clé publique publiée à /.well-known/jwks.json, sans passer par nous. Une version ultérieure de ce certificat porte un numéro de version supérieur : c\'est elle qui fait foi.'
                           : 'The signature above can be checked against the public key published at /.well-known/jwks.json, without going through us. A later version of this certificate carries a higher version number, and that is the one that counts.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">QR</span><h2>{{ $isFr ? 'Vérifier ce certificat' : 'Verify this certificate' }}</h2></div>
                <div class="sc-body" style="text-align:center;">
                    <div id="wvc-qr" style="margin:2px auto 0;width:122px;height:122px;"></div>
                    <p style="margin-top:6px;font-size:8.5px;font-weight:700;letter-spacing:.07em;color:#4E5B50;text-transform:uppercase;">
                        {{ $isFr ? 'Scanner pour vérifier' : 'Scan to verify' }}
                    </p>
                    <p class="mono" style="margin-top:4px;font-size:8px;color:#0B4A2A;word-break:break-all;">{{ $verifyShort }}</p>
                    @if($c->verification_pin)
                    <p style="margin-top:6px;font-size:8.5px;font-weight:500;letter-spacing:.07em;color:#4E5B50;">
                        {{ $isFr ? 'CODE DE VÉRIFICATION' : 'VERIFICATION CODE' }}
                    </p>
                    <p class="mono" style="font-size:13px;font-weight:700;letter-spacing:.14em;color:#14251A;">{{ $c->verification_pin }}</p>
                    @endif
                    <p class="note" style="margin-top:6px;text-align:left;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'L\'exemplaire de référence est celui vérifié en ligne à cette adresse. En cas de divergence entre cette impression et le registre, c\'est le registre qui fait foi.'
                           : 'The authoritative copy is the one verified online at this address. Where this printout and the register disagree, the register is what counts.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 2 · 3 · 4 ══ --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">2</span><h2>{{ $isFr ? 'Profil de l\'atelier' : 'Workshop profile' }}</h2></div>
                <div class="sc-body"><dl style="--kv:112px;">
                    @foreach($profileRows as [$k, $v, $m])
                    <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl></div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">3</span><h2>{{ $isFr ? 'Identité géographique' : 'Geographic identity' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:112px;">
                        @foreach($geoRows as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        @if($w->geo_verified_at)
                            {{ $isFr ? 'Coordonnées confirmées sur place le ' : 'Coordinates confirmed on site on ' }}{{ Carbon::parse($w->geo_verified_at)->format('Y-m-d') }}.
                        @else
                            {{ $isFr
                               ? 'Coordonnées déclarées par l\'atelier. Personne ne s\'est tenu à ce point pour les confirmer, et elles ne sont donc pas une localisation vérifiée.'
                               : 'Coordinates as declared by the workshop. Nobody has stood at this point to confirm them, so they are not a verified location.' }}
                        @endif
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">4</span><h2>{{ $isFr ? 'Infrastructure' : 'Infrastructure' }}</h2></div>
                <div class="sc-body">
                    @if($infraRows->isEmpty())
                        <p class="note">{{ $isFr
                            ? 'Aucune mesure d\'infrastructure n\'a été relevée pour cet atelier.'
                            : 'No infrastructure measurement has been recorded for this workshop.' }}</p>
                    @else
                    <dl style="--kv:126px;">
                        @foreach($infraRows as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @endif
                    <div style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        @foreach($safetyFacts as [$label, $value])
                        <div class="tick">
                            @if($value === null)
                                <i data-lucide="circle-dashed" style="color:#8A9490;"></i>
                                <span>{{ $label }} — <span style="color:#4A554E;font-weight:700;">{{ $unassessedWord }}</span></span>
                            @elseif($value)
                                <i data-lucide="check-circle-2" style="color:#0F7A34;"></i>
                                <span>{{ $label }} — {{ $isFr ? 'présent' : 'present' }}</span>
                            @else
                                <i data-lucide="x-circle" style="color:#B4141B;"></i>
                                <span>{{ $label }} — {{ $isFr ? 'aucun constaté' : 'none found' }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        @php
            /* ── Section 6 ── Only the three capability columns the register
               holds. The artwork lists craft categories, main products, primary
               materials and a quality-assurance tick; none of those is a column
               on this table, and inventing "Mahogany, Ebony, Rattan" for a
               facility nobody asked would be the same lie as inventing its floor
               area. */
            $capacityRows = $rows([
                [$isFr ? 'Capacité mensuelle maximale' : 'Maximum monthly capacity',
                    $w->max_monthly_capacity !== null
                        ? $w->max_monthly_capacity . ' ' . ($isFr ? 'pièces / mois' : 'pieces per month')
                        : null, true],
                [$isFr ? 'Normes de production' : 'Production standards', $w->production_standards, false],
                [$isFr ? 'Normes d\'emballage' : 'Packaging standards', $w->packaging_standards, false],
            ]);

            /* ── Section 7 ── Each count separately optional, and a blank is
               printed as a blank. Summing the recorded ones into an "employees"
               figure would let a workshop that declared four masters and left
               everything else empty read as a workforce of four. */
            $workforceRows = collect([
                [$isFr ? 'Maîtres artisans' : 'Master artisans', $w->master_artisans],
                [$isFr ? 'Ouvriers qualifiés' : 'Skilled workers', $w->skilled_workers],
                [$isFr ? 'Apprentis' : 'Apprentices', $w->apprentices],
                [$isFr ? 'Femmes' : 'Female workers', $w->female_workers],
                [$isFr ? 'Jeunes (18–35 ans)' : 'Youth (18–35)', $w->youth_workers],
            ]);
        @endphp

        {{-- ══ 5 · 6 · 7 ══ --}}
        <div style="display:grid;grid-template-columns:340px 300px 273px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">5</span><h2>{{ $isFr ? 'Inventaire de l\'équipement' : 'Equipment inventory' }}</h2></div>
                <div class="sc-body">
                    @if(count($equipment) === 0)
                        <p class="note">{{ $isFr
                            ? 'Aucun équipement n\'a été inventorié pour cet atelier. Cela ne signifie pas que l\'atelier n\'en possède pas : personne n\'a relevé d\'inventaire.'
                            : 'No equipment has been inventoried for this workshop. That does not mean it has none: nobody has taken an inventory.' }}</p>
                    @else
                    <table style="width:100%;border-collapse:collapse;">
                        <thead><tr>
                            <th style="text-align:left;font-size:8px;font-weight:700;color:#4E5B50;padding-bottom:3px;border-bottom:1px solid #D9D0B8;">{{ $isFr ? 'Article' : 'Item' }}</th>
                            <th style="text-align:left;font-size:8px;font-weight:700;color:#4E5B50;padding-bottom:3px;border-bottom:1px solid #D9D0B8;">{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                            <th style="text-align:right;font-size:8px;font-weight:700;color:#4E5B50;padding-bottom:3px;border-bottom:1px solid #D9D0B8;">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                        </tr></thead>
                        <tbody>
                        @foreach($equipment as $item)
                        <tr>
                            <td style="font-size:8.5px;font-weight:600;color:#14251A;padding:2px 0;border-bottom:1px solid #EFE7D5;">
                                {{ $item->label }}
                                @if($item->notes)<span class="note"> · {{ $item->notes }}</span>@endif
                            </td>
                            <td style="font-size:8.5px;color:#4E5B50;padding:2px 0;border-bottom:1px solid #EFE7D5;">{{ $equipmentLabels[$item->category] ?? $item->category }}</td>
                            <td class="mono" style="font-size:8.5px;font-weight:700;text-align:right;padding:2px 0;border-bottom:1px solid #EFE7D5;">{{ $item->quantity }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'Inventaire déclaré par l\'atelier et constaté lors de l\'inspection ci-dessous. Il n\'est pas exhaustif : il ne contient que ce qui a été consigné.'
                           : 'Inventory as declared by the workshop and seen at the inspection below. It is not exhaustive: it holds only what was written down.' }}
                    </p>
                    @endif
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">6</span><h2>{{ $isFr ? 'Moyens de production' : 'Production capabilities' }}</h2></div>
                <div class="sc-body">
                    @if($capacityRows->isEmpty())
                        <p class="note">{{ $isFr
                            ? 'Ni capacité de production, ni norme de production ou d\'emballage n\'a été enregistrée pour cet atelier.'
                            : 'Neither a production capacity nor a production or packaging standard has been recorded for this workshop.' }}</p>
                    @else
                    <dl style="--kv:132px;">
                        @foreach($capacityRows as [$k, $v, $m])
                        <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @endif
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'La capacité indiquée est une déclaration de l\'atelier, horodatée par nous. Ce n\'est pas un engagement de livraison et nous ne la mesurons pas.'
                           : 'The capacity above is the workshop\'s own declaration, timestamped by us. It is not a delivery commitment, and we do not measure it.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">7</span><h2>{{ $isFr ? 'Effectifs' : 'Workforce' }}</h2></div>
                <div class="sc-body">
                    <dl style="--kv:132px;">
                        @foreach($workforceRows as [$k, $v])
                        <div class="kv">
                            <dt>{{ $k }}</dt>
                            @if($v === null)
                            <dd><span class="chip-un">{{ $isFr ? 'Non renseigné' : 'Not recorded' }}</span></dd>
                            @else
                            <dd class="mono">{{ $v }}</dd>
                            @endif
                        </div>
                        @endforeach
                    </dl>
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Un effectif non renseigné n\'est pas un effectif nul. Les chiffres ci-dessus ne sont pas additionnés en un total, car un total masquerait ce qui n\'a jamais été déclaré.'
                           : 'A count that was not recorded is not a count of zero. The figures above are not added into a total, because a total would hide what was never declared.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 8 · Compliance ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">8</span><h2>{{ $isFr ? 'Licences et conformité' : 'Licences and compliance' }}</h2></div>
            <div class="sc-body">
                @if(count($compliance) === 0)
                    <p class="note">{{ $isFr
                        ? 'Aucune licence ni permis n\'est enregistré pour cet atelier.'
                        : 'No licence or permit is recorded for this workshop.' }}</p>
                @else
                <table style="width:100%;border-collapse:collapse;">
                    <thead><tr>
                        @foreach([
                            $isFr ? 'Obligation' : 'Obligation',
                            $isFr ? 'Référence' : 'Reference',
                            $isFr ? 'Délivré le' : 'Issued',
                            $isFr ? 'Expire le' : 'Expires',
                            $isFr ? 'Vérifié par' : 'Checked by',
                            $isFr ? 'État' : 'State',
                        ] as $head)
                        <th style="text-align:left;font-size:8px;font-weight:700;color:#4E5B50;padding-bottom:3px;border-bottom:1px solid #D9D0B8;">{{ $head }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody>
                    @foreach($compliance as $row)
                        @php
                            /* The expiry beats the column, as it does in the
                               register: nothing sweeps this table, so a licence
                               that lapsed last week still says `valid` in
                               storage. An unassessed row is never re-read this
                               way — a date on a document nobody looked at
                               proves nothing either way. */
                            $state = $row->status;
                            if ($state !== 'unassessed' && $row->expires_on && Carbon::parse($row->expires_on)->isPast()) {
                                $state = 'expired';
                            }
                            $meta = $complianceMeta[$state] ?? [Str::upper($state), '#5A6B7E', 'help-circle'];
                            $un   = $state === 'unassessed';
                        @endphp
                        <tr data-compliance="{{ $state }}" style="{{ $un ? 'background:#F6F6F2;' : '' }}">
                            <td style="font-size:8.5px;font-weight:600;color:{{ $un ? '#4A554E' : '#14251A' }};padding:3px 0;border-bottom:1px solid #EFE7D5;">
                                {{ $complianceLabels[$row->kind] ?? str_replace('_', ' ', $row->kind) }}
                            </td>
                            <td class="mono" style="font-size:8px;color:#4E5B50;padding:3px 6px 3px 0;border-bottom:1px solid #EFE7D5;">{{ $row->reference ?: $dash }}</td>
                            <td class="mono" style="font-size:8px;color:#4E5B50;padding:3px 6px 3px 0;border-bottom:1px solid #EFE7D5;">{{ $row->issued_on ? Carbon::parse($row->issued_on)->format('Y-m-d') : $dash }}</td>
                            <td class="mono" style="font-size:8px;color:#4E5B50;padding:3px 6px 3px 0;border-bottom:1px solid #EFE7D5;">{{ $row->expires_on ? Carbon::parse($row->expires_on)->format('Y-m-d') : $dash }}</td>
                            <td style="font-size:8px;color:#4E5B50;padding:3px 6px 3px 0;border-bottom:1px solid #EFE7D5;">
                                {{ $row->verified_at ? (($row->verified_by ?: ($isFr ? 'la plateforme' : 'the platform')) . ' · ' . Carbon::parse($row->verified_at)->format('Y-m-d')) : $dash }}
                            </td>
                            <td style="padding:3px 0;border-bottom:1px solid #EFE7D5;">
                                @if($un)
                                    {{-- Dashed, slate and captioned. This is the row
                                         the whole table exists to keep separate from
                                         the one above it. --}}
                                    <span class="chip-un"><i data-lucide="circle-dashed" style="width:9px;height:9px;"></i>{{ $meta[0] }}</span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:8.5px;font-weight:700;color:{{ $meta[1] }};">
                                        <i data-lucide="{{ $meta[2] }}" style="width:10px;height:10px;flex:none;"></i>{{ $meta[0] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @php
                    $unassessedKinds = collect($compliance)->where('status', 'unassessed')
                        ->map(fn ($r) => $complianceLabels[$r->kind] ?? str_replace('_', ' ', $r->kind))->values();
                @endphp
                @if($unassessedKinds->isNotEmpty())
                <p class="note" style="margin-top:6px;padding:5px 7px;border:1px dashed #8A9490;border-radius:4px;background:#F1F2EE;color:#3F4A43;">
                    <strong>{{ $isFr ? 'À lire avec le tableau' : 'Read this with the table' }} :</strong>
                    {{ $isFr
                       ? 'les obligations suivantes figurent au registre parce qu\'elles s\'appliquent, mais personne n\'a examiné le document correspondant : '
                       : 'the following obligations are on the register because they apply, but nobody has examined the document behind them: ' }}
                    <strong>{{ $unassessedKinds->join($isFr ? ', ' : ', ') }}</strong>.
                    {{ $isFr
                       ? 'Elles ne sont ni conformes ni non conformes ici — elles n\'ont pas été évaluées, et rien sur cette feuille ne doit être lu comme une confirmation à leur sujet.'
                       : 'They are neither compliant nor non-compliant here — they were never assessed, and nothing on this sheet should be read as confirming anything about them.' }}
                </p>
                @endif
                @endif
            </div>
        </section>
        @php
            /* ── Section 9 ── Three-valued again. "No recycling" is a finding;
               "recycling never asked about" is not, and the artwork's column of
               green ticks cannot express the difference. */
            $sustainability = collect([
                [$isFr ? 'Matériaux renouvelables' : 'Renewable materials', $w->renewable_materials],
                [$isFr ? 'Gestion des déchets' : 'Waste management', $w->waste_management],
                [$isFr ? 'Recyclage' : 'Recycling', $w->recycling],
                [$isFr ? 'Économie d\'eau' : 'Water conservation', $w->water_conservation],
            ]);

            $effLabels = [
                'poor' => $isFr ? 'Faible' : 'Poor', 'fair' => $isFr ? 'Passable' : 'Fair',
                'good' => $isFr ? 'Bonne' : 'Good', 'excellent' => $isFr ? 'Excellente' : 'Excellent',
            ];

            /* ── Section 10 ── The inspection that actually happened.
               The artwork's panel here is "AI & Field Inspection Report":
               satellite location verification, GPS accuracy, an image match, a
               duplicate-workshop check and a fraud risk score. There is no
               model, no imagery feed and no fraud system behind this platform,
               and the register has no columns for any of it — deliberately, so
               that nobody can quietly fill them. What replaces the panel is the
               evidence that exists and is answerable: a named person, their
               reference, the date they were there, how they looked, what they
               wrote down, what they scored, and when they are due back. */
            $inspectionRows = $insp ? $rows([
                [$isFr ? 'Inspecteur' : 'Inspector', $insp->inspector_name, false],
                [$isFr ? 'Réf. de l\'inspecteur' : 'Inspector reference', $insp->inspector_ref, true],
                [$isFr ? 'Réf. du rapport' : 'Report reference', $insp->reference, true],
                [$isFr ? 'Méthode' : 'Method', $methodLabels[$insp->method] ?? $insp->method, false],
                [$isFr ? 'Date d\'inspection' : 'Inspection date', $insp->inspected_on ? Carbon::parse($insp->inspected_on)->format('Y-m-d') : null, true],
                [$isFr ? 'Prochaine échéance' : 'Next inspection due', $insp->next_due_on ? Carbon::parse($insp->next_due_on)->format('Y-m-d') : null, true],
                [$isFr ? 'Réf. interne (UUID)' : 'Internal reference (UUID)', $insp->uuid, true],
            ]) : collect();

            $outcomeM = $insp && $insp->outcome
                ? ($outcomeMeta[$insp->outcome] ?? [Str::upper($insp->outcome), '#5A6B7E'])
                : null;

            $dimScores = $insp ? collect(\App\Support\WorkshopRegister::DIMENSIONS)
                ->map(fn ($d) => [$assessLabels[$d] ?? $d, $insp->{$d . '_score'}]) : collect();
            $scoredCount = $dimScores->filter(fn ($d) => $d[1] !== null)->count();
        @endphp

        {{-- ══ 9 · 10 ══ --}}
        <div style="display:grid;grid-template-columns:300px 613px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">9</span><h2>{{ $isFr ? 'Pratiques de durabilité' : 'Sustainability practices' }}</h2></div>
                <div class="sc-body">
                    @foreach($sustainability as [$label, $value])
                    <div class="tick">
                        @if($value === null)
                            <i data-lucide="circle-dashed" style="color:#8A9490;"></i>
                            <span>{{ $label }} — <span style="color:#4A554E;font-weight:700;">{{ $unassessedWord }}</span></span>
                        @elseif($value)
                            <i data-lucide="check-circle-2" style="color:#0F7A34;"></i><span>{{ $label }}</span>
                        @else
                            <i data-lucide="x-circle" style="color:#B4141B;"></i>
                            <span>{{ $label }} — {{ $isFr ? 'non pratiqué' : 'not practised' }}</span>
                        @endif
                    </div>
                    @endforeach
                    <dl style="--kv:132px;margin-top:5px;padding-top:5px;border-top:1px solid #EFE7D5;">
                        <div class="kv">
                            <dt>{{ $isFr ? 'Efficacité énergétique' : 'Energy efficiency' }}</dt>
                            @if($w->energy_efficiency === null)
                            <dd><span class="chip-un">{{ $unassessedWord }}</span></dd>
                            @else
                            <dd>{{ $effLabels[$w->energy_efficiency] ?? $w->energy_efficiency }}</dd>
                            @endif
                        </div>
                        @if($w->carbon_note)
                        <div class="kv"><dt>{{ $isFr ? 'Note carbone' : 'Carbon note' }}</dt><dd>{{ $w->carbon_note }}</dd></div>
                        @endif
                    </dl>
                    <p class="note" style="margin-top:6px;">
                        {{ $isFr
                           ? 'Ces réponses sont déclarées par l\'atelier. Aucune n\'est mesurée, et aucune n\'est une certification environnementale.'
                           : 'These answers are declared by the workshop. None is measured, and none is an environmental certification.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">10</span><h2>{{ $isFr ? 'Rapport d\'inspection' : 'Inspection report' }}</h2></div>
                <div class="sc-body">
                    @if(! $insp)
                        <p class="note">{{ $isFr ? 'Aucun rapport d\'inspection n\'est au dossier.' : 'No inspection report is on file.' }}</p>
                    @else
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <dl style="--kv:118px;">
                            @foreach($inspectionRows as [$k, $v, $m])
                            <div class="kv"><dt>{{ $k }}</dt><dd class="{{ $m ? 'mono' : '' }}" style="{{ $m ? 'font-size:8px;' : '' }}">{{ $v }}</dd></div>
                            @endforeach
                            @if($outcomeM)
                            <div class="kv">
                                <dt>{{ $isFr ? 'Conclusion' : 'Outcome' }}</dt>
                                <dd style="color:{{ $outcomeM[1] }};font-weight:700;">{{ $outcomeM[0] }}</dd>
                            </div>
                            @endif
                        </dl>
                        <div>
                            <p style="font-size:8px;font-weight:700;letter-spacing:.06em;color:#4E5B50;text-transform:uppercase;">
                                {{ $isFr ? 'Notes par dimension' : 'Scores by dimension' }}
                            </p>
                            @foreach($dimScores as [$label, $score])
                            <div style="display:flex;align-items:center;gap:6px;padding:1.5px 0;">
                                <span style="width:96px;flex:none;font-size:8.5px;color:#4E5B50;">{{ $label }}</span>
                                @if($score === null)
                                    <span class="chip-un">{{ $isFr ? 'Non noté' : 'Not scored' }}</span>
                                @else
                                    <span style="flex:1;height:6px;border-radius:3px;background:#E4E9E1;overflow:hidden;">
                                        <span style="display:block;height:100%;width:{{ round($score / 20 * 100) }}%;background:#125C33;"></span>
                                    </span>
                                    <span class="mono" style="width:38px;flex:none;text-align:right;font-size:8.5px;font-weight:700;">{{ $score }}/20</span>
                                @endif
                            </div>
                            @endforeach
                            <p class="note" style="margin-top:4px;">
                                {{ $scoredCount }} {{ $isFr ? 'des sept dimensions ont été notées lors de cette visite ; les autres ne l\'ont pas été et ne comptent pas comme des zéros.' : 'of the seven dimensions were scored at this visit; the rest were not, and do not count as zeros.' }}
                            </p>
                        </div>
                    </div>
                    @if($insp->findings)
                    <div style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        <p style="font-size:8px;font-weight:700;letter-spacing:.06em;color:#4E5B50;text-transform:uppercase;">{{ $isFr ? 'Constatations' : 'Findings' }}</p>
                        <p style="font-size:8.5px;color:#14251A;line-height:1.5;">{{ $insp->findings }}</p>
                    </div>
                    @endif
                    <p class="note" style="margin-top:6px;">
                        {{ $isFr
                           ? 'Cette inspection est une visite humaine consignée par la personne nommée ci-dessus, qui en répond. Elle décrit l\'atelier tel qu\'il était ce jour-là. La plateforme n\'exploite ni imagerie satellite, ni reconnaissance d\'image, ni système de détection : ces contrôles n\'existent pas ici et ne sont donc revendiqués nulle part sur cette feuille.'
                           : 'This inspection is a human visit recorded by the person named above, who answers for it. It describes the workshop as it stood that day. The platform runs no imagery feed, no image recognition and no detection system: those checks do not exist here, and so are claimed nowhere on this sheet.' }}
                    </p>
                    @endif
                </div>
            </section>
        </div>

        @php
            /* ── Sections 11 and 12 ── One renderer for both, because they are
               the same shape and the same rule: print the register's own
               score/max and its basis phrase, and give a category with a maximum
               of zero a dashed chip instead of a fraction. The artwork's
               "108/120 (90%)" is a number a designer typed; the total below is
               whatever assessment() returned, over the dimensions that could
               actually be assessed. */
            $scorePanel = function (array $set, array $labels) use ($isFr, $ratingLabels, $ratingColour, $unassessedWord) {
                $pct = $set['max'] > 0 ? round($set['total'] / $set['max'] * 100) : null;

                return [
                    'pct'        => $pct,
                    'word'       => $ratingLabels[$set['rating']] ?? Str::upper($set['rating']),
                    'colour'     => $ratingColour($set['rating']),
                    'categories' => collect($set['categories'])->map(fn ($cat, $key) => [
                        'label'      => $labels[$key] ?? $key,
                        'score'      => $cat['score'],
                        'max'        => $cat['max'],
                        'basis'      => $cat['basis'],
                        'unassessed' => $cat['max'] === 0,
                    ])->values(),
                    'unassessedCount' => collect($set['categories'])->filter(fn ($cat) => $cat['max'] === 0)->count(),
                ];
            };

            $quality = $scorePanel($assessment, $assessLabels);
            $export  = $scorePanel($exportReady, $exportLabels);
        @endphp

        {{-- ══ 11 · 12 ══ --}}
        <div style="display:grid;grid-template-columns:527px 386px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            @foreach([
                ['11', $isFr ? 'Évaluation de la qualité' : 'Quality assessment', $assessment, $quality],
                ['12', $isFr ? 'Aptitude à l\'exportation' : 'Export readiness', $exportReady, $export],
            ] as [$num, $heading, $set, $panel])
            <section class="sc">
                <div class="sc-head"><span class="ltr">{{ $num }}</span><h2>{{ $heading }}</h2></div>
                <div class="sc-body">
                    @foreach($panel['categories'] as $cat)
                    <div style="padding:2.5px 0;border-bottom:1px solid #EFE7D5;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="width:104px;flex:none;font-size:8.5px;font-weight:600;color:#14251A;">{{ $cat['label'] }}</span>
                            @if($cat['unassessed'])
                                {{-- Never "0/20". A maximum of zero means the
                                     register held nothing to judge, and a zero
                                     score would turn our own missing data into a
                                     finding against this workshop. --}}
                                <span class="chip-un" data-score="unassessed">{{ $unassessedWord }}</span>
                            @else
                                <span style="flex:1;height:7px;border-radius:3px;background:#E4E9E1;overflow:hidden;">
                                    <span style="display:block;height:100%;width:{{ $cat['max'] > 0 ? round($cat['score'] / $cat['max'] * 100) : 0 }}%;background:#125C33;"></span>
                                </span>
                                <span class="mono" style="width:46px;flex:none;text-align:right;font-size:8.5px;font-weight:700;color:#14251A;">{{ $cat['score'] }}/{{ $cat['max'] }}</span>
                            @endif
                        </div>
                        <p class="note" style="margin-top:1px;">{{ $cat['basis'] }}</p>
                    </div>
                    @endforeach

                    <div style="margin-top:7px;display:flex;align-items:center;gap:10px;padding:6px 9px;border-radius:5px;
                                background:linear-gradient(180deg,#125C33,#0A4127);border:1px solid #C9942E;">
                        <span style="font-size:9px;font-weight:700;letter-spacing:.07em;color:#F0DFB4;text-transform:uppercase;">
                            {{ $isFr ? 'Total' : 'Total' }}
                        </span>
                        <span class="mono" style="font-size:15px;font-weight:800;color:#FFFFFF;">{{ $set['total'] }}/{{ $set['max'] }}</span>
                        @if($panel['pct'] !== null)
                        <span class="mono" style="font-size:10px;font-weight:700;color:#E7C878;">({{ $panel['pct'] }}%)</span>
                        @endif
                        <span style="flex:1;"></span>
                        <span style="padding:1px 7px;border-radius:3px;background:{{ $panel['colour'] }};
                                     font-size:9px;font-weight:800;letter-spacing:.06em;color:#FFFFFF;">{{ $panel['word'] }}</span>
                    </div>

                    <p class="note" style="margin-top:5px;">
                        @if($panel['unassessedCount'] > 0)
                            {{ $isFr
                               ? 'Ce total porte uniquement sur les dimensions évaluables : '
                               : 'This total is over the assessable dimensions only: ' }}<strong>{{ $panel['unassessedCount'] }}</strong>
                            {{ $isFr
                               ? 'd\'entre elles n\'ont pas pu être évaluées et sont exclues du dénominateur, ni créditées ni pénalisées. Un pourcentage élevé sur peu de dimensions n\'est pas un bon score : c\'est un score étroit.'
                               : 'of them could not be assessed and are excluded from the denominator, neither credited nor penalised. A high percentage over few dimensions is not a good score; it is a narrow one.' }}
                        @else
                            {{ $isFr
                               ? 'Toutes les dimensions ont pu être évaluées, et le total ci-dessus porte donc sur l\'ensemble d\'entre elles. Chaque ligne indique sur quoi sa note repose.'
                               : 'Every dimension could be assessed, so the total above is over all of them. Each line states what its score rests on.' }}
                        @endif
                    </p>
                </div>
            </section>
            @endforeach
        </div>
        @php
            /* ── Section 13 ── The artwork prints six counters: registered
               products, "authentic products", collections, awards, exhibitions,
               exported products. Four of those are not columns anywhere in this
               platform, and a certificate that announces "Awards received: 4"
               for a workshop that has never been asked about awards is inventing
               a reputation. What is real is the traceability finding the export
               register already computed — how many pieces from this workshop can
               be followed back to it — so that is what the panel prints, in the
               register's own words. */
            $traceBasis = $exportReady['categories']['traceability']['basis'] ?? null;

            /* ── Section 15 ── The ladder. Seven tiers exist because the
               register caps the level at what one inspection can support, and
               printing "LEVEL 4" with nothing beside it tells a reader only that
               three higher numbers exist. The rule is stated instead. */
            $levelRules = [
                $isFr ? 'Examen documentaire ou contrôle à distance' : 'Document review or remote check',
                $isFr ? 'Une visite avec une dimension notée'        : 'A visit with one dimension scored',
                $isFr ? 'Dossier photographique, ou pass sous conditions' : 'Photographic record, or a pass with conditions',
                $isFr ? 'Visite sur site, trois dimensions notées'    : 'On-site visit, three dimensions scored',
                $isFr ? 'Visite sur site, quatre dimensions notées'   : 'On-site visit, four dimensions scored',
                $isFr ? 'Visite sur site, cinq dimensions notées'     : 'On-site visit, five dimensions scored',
                $isFr ? 'Visite sur site, six dimensions ou plus'     : 'On-site visit, six dimensions or more',
            ];
            $level = (int) $c->level;

            $sigState = \App\Support\WorkshopRegister::signatureState($c);
            $sigMeta  = [
                'valid'    => [$isFr ? 'Signature vérifiée' : 'Signature verified', '#0F7A34', 'check-circle-2'],
                'invalid'  => [$isFr ? 'Signature invalide' : 'Signature does not check out', '#B4141B', 'x-circle'],
                'unsigned' => [$isFr ? 'Non signé' : 'Unsigned', '#5A6B7E', 'circle-dashed'],
            ][$sigState['state']] ?? [$sigState['state'], '#5A6B7E', 'help-circle'];
        @endphp

        {{-- ══ 13 · 14 ══ --}}
        <div style="display:grid;grid-template-columns:300px 613px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">13</span><h2>{{ $isFr ? 'Production traçable' : 'Traceable portfolio' }}</h2></div>
                <div class="sc-body">
                    <p style="font-size:8.5px;color:#14251A;line-height:1.5;">{{ $traceBasis ?: ($isFr ? 'Aucune donnée de traçabilité.' : 'No traceability finding.') }}</p>
                    @if($business?->slug)
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr ? 'Fiche publique de l\'atelier : ' : 'The workshop\'s public page: ' }}
                        <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" style="color:#0B4A2A;text-decoration:underline;">{{ $business->name_fr }}</a>
                    </p>
                    @endif
                    <p class="note" style="margin-top:6px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Ce panneau ne compte que les pièces enregistrées au registre. Distinctions, expositions et collections ne sont pas comptabilisées ici : la plateforme ne les recueille pas, et un chiffre inventé serait une réputation inventée.'
                           : 'This panel counts only pieces on the register. Awards, exhibitions and collections are not counted here: the platform does not collect them, and an invented figure would be an invented reputation.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">14</span><h2>{{ $isFr ? 'Éléments vérifiables' : 'Verifiable features' }}</h2></div>
                <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:3px 14px;">
                    @foreach($securityLegend as [$sIcon, $sTitle, $sNote])
                    <div style="display:flex;gap:7px;padding:2px 0;">
                        <span style="width:20px;height:20px;flex:none;border-radius:50%;border:1px solid #C9942E;
                                     background:#F6EEDC;color:#8A6410;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="{{ $sIcon }}" style="width:11px;height:11px;"></i>
                        </span>
                        <div style="min-width:0;">
                            <p style="font-size:9px;font-weight:700;color:#14251A;">{{ $sTitle }}</p>
                            <p class="note" style="word-break:break-word;">{{ $sNote }}</p>
                        </div>
                    </div>
                    @endforeach
                    <p class="note" style="grid-column:1 / -1;margin-top:4px;padding-top:5px;border-top:1px solid #EFE7D5;">
                        {{ $isFr
                           ? 'Cette liste ne contient que ce qu\'un lecteur peut vérifier sur un écran ou une impression ordinaire. Les mesures qui exigent une imprimerie de sécurité — encres, films, gaufrage, papier filigrané — ne figurent pas ici et sont spécifiées séparément.'
                           : 'This list contains only what a reader can check on a screen or an ordinary printout. Measures that need a security printer — inks, films, embossing, watermarked stock — are not claimed here and are specified separately.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 15 · 16 ══ --}}
        <div style="display:grid;grid-template-columns:437px 476px;gap:8px;margin:8px 20px 0;align-items:stretch;">

            <section class="sc">
                <div class="sc-head"><span class="ltr">15</span><h2>{{ $isFr ? 'Résultat de la vérification' : 'Verification result' }}</h2></div>
                <div class="sc-body">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="width:44px;height:44px;flex:none;border-radius:50%;border:2px solid {{ $statusMeta[1] }};
                                     display:flex;align-items:center;justify-content:center;color:{{ $statusMeta[1] }};">
                            <i data-lucide="{{ $statusMeta[2] }}" style="width:24px;height:24px;"></i>
                        </span>
                        <div>
                            <p style="font-size:14px;font-weight:800;letter-spacing:.04em;color:{{ $statusMeta[1] }};">{{ $statusMeta[0] }}</p>
                            <p style="font-size:10px;font-weight:700;color:#14251A;">{{ $isFr ? 'Niveau' : 'Level' }} {{ $level }} {{ $isFr ? 'sur 7' : 'of 7' }}</p>
                        </div>
                    </div>

                    {{-- The ladder, with the granted rung named. --}}
                    <div style="margin-top:7px;">
                        @foreach($levelRules as $i => $rule)
                        @php $tier = $i + 1; @endphp
                        <div style="display:flex;align-items:center;gap:6px;padding:1px 0;">
                            <span style="width:14px;height:14px;flex:none;border-radius:3px;font-size:8px;font-weight:800;
                                         display:flex;align-items:center;justify-content:center;
                                         {{ $tier === $level ? 'background:#125C33;color:#F0DFB4;' : ($tier < $level ? 'background:#D9E4DB;color:#4E5B50;' : 'background:#F1F2EE;color:#9AA39B;') }}">{{ $tier }}</span>
                            <span style="font-size:8px;color:{{ $tier === $level ? '#14251A' : '#6E7A70' }};font-weight:{{ $tier === $level ? '700' : '400' }};">{{ $rule }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div style="margin-top:7px;padding-top:6px;border-top:1px solid #EFE7D5;">
                        @foreach($checks as $key => $ok)
                            @continue(! isset($checkLabels[$key]))
                            <div class="tick" data-check="{{ $key }}">
                                <i data-lucide="{{ $ok ? 'check-circle-2' : 'x-circle' }}" style="color:{{ $ok ? '#0F7A34' : '#B4141B' }};"></i>
                                <span>{{ $checkLabels[$key] }}</span>
                            </div>
                        @endforeach
                        <div class="tick" data-signature="{{ $sigState['state'] }}">
                            <i data-lucide="{{ $sigMeta[2] }}" style="color:{{ $sigMeta[1] }};"></i>
                            <span>{{ $sigMeta[0] }}@if($sigState['kid']) <span class="mono note">· {{ $sigState['kid'] }}</span>@endif</span>
                        </div>
                    </div>

                    <p class="note" style="margin-top:6px;">
                        {{ $isFr
                           ? 'Cette liste est complète : elle contient tous les contrôles que la plateforme effectue. Un contrôle absent de cette liste n\'a pas échoué — il n\'existe pas ici, et aucune coche ni croix ne lui est attribuée.'
                           : 'This list is complete: it holds every check the platform performs. A check that is not listed has not failed — it does not exist here, and is given neither a tick nor a cross.' }}
                    </p>
                </div>
            </section>

            <section class="sc">
                <div class="sc-head"><span class="ltr">16</span><h2>{{ $isFr ? 'Journal du registre' : 'Audit trail' }}</h2></div>
                <div class="sc-body">
                    @if(count($trail) === 0)
                        <p class="note">{{ $isFr ? 'Aucun événement au journal.' : 'No events on the log.' }}</p>
                    @else
                    <table style="width:100%;border-collapse:collapse;">
                        <thead><tr>
                            @foreach(['#', $isFr ? 'Événement' : 'Event', $isFr ? 'Horodatage (UTC, ISO 8601)' : 'Timestamp (UTC, ISO 8601)', $isFr ? 'Par' : 'By'] as $head)
                            <th style="text-align:left;font-size:8px;font-weight:700;color:#4E5B50;padding-bottom:3px;border-bottom:1px solid #D9D0B8;">{{ $head }}</th>
                            @endforeach
                        </tr></thead>
                        <tbody>
                        @foreach($trail as $i => $event)
                        <tr>
                            <td class="mono" style="font-size:8px;color:#8A9490;padding:2px 6px 2px 0;border-bottom:1px solid #EFE7D5;">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                            <td style="font-size:8.5px;font-weight:600;color:#14251A;padding:2px 6px 2px 0;border-bottom:1px solid #EFE7D5;">
                                {{ $eventLabels[$event->event] ?? str_replace('_', ' ', $event->event) }}
                                @if($event->note ?? null)<span class="note"> · {{ $event->note }}</span>@endif
                            </td>
                            <td class="mono" style="font-size:8px;color:#4E5B50;padding:2px 6px 2px 0;border-bottom:1px solid #EFE7D5;">
                                {{ $event->occurred_at ? Carbon::parse($event->occurred_at)->utc()->format('Y-m-d\TH:i:s\Z') : $dash }}
                            </td>
                            <td style="font-size:8px;color:#4E5B50;padding:2px 0;border-bottom:1px solid #EFE7D5;">{{ $event->actor ?? ($isFr ? 'Registre' : 'The register') }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <p class="note" style="margin-top:5px;">
                        {{ $isFr
                           ? 'Chaque entrée porte l\'empreinte de la précédente : une ligne retirée ou modifiée rompt la chaîne et se voit.'
                           : 'Each entry carries the digest of the one before it: a line removed or altered breaks the chain and shows.' }}
                    </p>
                    @endif
                </div>
            </section>
        </div>

        {{-- ══ 17 · Who this document rests on ══
             The artwork closes with four handwritten signatures over four named
             officers: the workshop owner, a field inspector, a regional
             verification officer and an "AHCA Director". Three of those four
             posts do not exist, and a drawn signature is an assertion that a
             particular person put their hand to a particular sheet, which
             nobody did — this document is issued by software. The inspector is
             real, so the inspector is named, with their reference, exactly as
             the transfer certificate names its parties. --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">17</span><h2>{{ $isFr ? 'Sur quoi repose ce document' : 'Who this document rests on' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                <div>
                    <p style="font-size:8px;font-weight:700;letter-spacing:.06em;color:#4E5B50;text-transform:uppercase;">{{ $isFr ? 'Inspecteur au dossier' : 'Inspector of record' }}</p>
                    @if($insp && $insp->inspector_name)
                        <p style="font-size:10px;font-weight:700;color:#14251A;">{{ $insp->inspector_name }}</p>
                        @if($insp->inspector_ref)
                        <p class="mono note">{{ $insp->inspector_ref }}</p>
                        @endif
                        <p class="note" style="margin-top:3px;">
                            {{ $methodLabels[$insp->method] ?? $insp->method }}, {{ Carbon::parse($insp->inspected_on)->format('Y-m-d') }}
                        </p>
                    @else
                        <p class="note">{{ $isFr ? 'Le rapport ne nomme aucun inspecteur.' : 'The report names no inspector.' }}</p>
                    @endif
                </div>
                <div>
                    <p style="font-size:8px;font-weight:700;letter-spacing:.06em;color:#4E5B50;text-transform:uppercase;">{{ $isFr ? 'Déclarant' : 'Declared by' }}</p>
                    <p style="font-size:10px;font-weight:700;color:#14251A;">{{ $business?->user?->name ?: ($w->manager_name ?: ($isFr ? 'L\'atelier' : 'The workshop')) }}</p>
                    <p class="note" style="margin-top:3px;">
                        {{ $isFr
                           ? 'Les mesures, effectifs et pratiques ci-dessus sont déclarés par l\'atelier, sauf là où une vérification est explicitement indiquée.'
                           : 'The measurements, counts and practices above are declared by the workshop, except where a check is explicitly stated.' }}
                    </p>
                </div>
                <div>
                    <p style="font-size:8px;font-weight:700;letter-spacing:.06em;color:#4E5B50;text-transform:uppercase;">{{ $isFr ? 'Émis par' : 'Issued by' }}</p>
                    <p style="font-size:10px;font-weight:700;color:#14251A;">{{ $isFr ? 'ArtisanHub237 — autorité de certification' : 'ArtisanHub237 — certification authority' }}</p>
                    <p class="note" style="margin-top:3px;">
                        {{ $isFr
                           ? 'Émis par programme et signé cryptographiquement. Aucune signature manuscrite ne figure sur ce document : il n\'y en a pas, et une signature dessinée serait une pièce de théâtre. La signature qui compte est celle de la section 1.'
                           : 'Issued programmatically and signed cryptographically. No handwritten signature appears on this document: there is none, and a drawn one would be theatre. The signature that counts is the one in section 1.' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ══ 18 · Scope, limits, foot ══ --}}
        <section class="sc" style="margin:8px 20px 0;">
            <div class="sc-head"><span class="ltr">18</span><h2>{{ $isFr ? 'Portée et limites' : 'Scope and limits' }}</h2></div>
            <div class="sc-body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <p class="note">
                    {{ $isFr
                       ? 'ArtisanHub237 est une entreprise privée. Elle n\'est ni un organisme public d\'inspection, ni une autorité de licence, ni un certificateur au sens de la loi. Elle consigne ce que des personnes identifiées déclarent et ce qu\'un inspecteur nommé a constaté, et l\'horodate. Elle n\'est partie à aucune transaction : elle n\'encaisse aucun paiement et n\'expédie rien.'
                       : 'ArtisanHub237 is a private company. It is not a public inspectorate, a licensing authority or a certifier in law. It records what identified people declare and what a named inspector found, and timestamps it. It is party to no transaction: it collects no payments and ships nothing.' }}
                </p>
                <p class="note">
                    {{ $isFr
                       ? 'Ce certificat décrit un atelier tel qu\'il était à la date de l\'inspection citée en section 10. Il ne garantit ni la qualité d\'une pièce, ni un délai, ni la solvabilité de l\'atelier, et il cesse de faire foi à sa date d\'expiration. Il ne dispense d\'aucune formalité administrative, fiscale ou douanière.'
                       : 'This certificate describes a workshop as it stood on the date of the inspection cited in section 10. It guarantees no piece\'s quality, no delivery time and no workshop\'s solvency, and it stops speaking for the workshop at its expiry date. It replaces no administrative, tax or customs formality.' }}
                </p>
            </div>
        </section>

        <div style="margin:9px 20px 0;">
            <svg width="921" height="6" viewBox="0 0 1000 6" preserveAspectRatio="none" aria-hidden="true">
                <g transform="translate(0,1)"><use href="#coaMicrotextLine"/></g>
            </svg>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin:4px 20px 0;">
            <span class="mono" style="width:110px;flex:none;font-size:12px;font-weight:700;color:#0A4127;letter-spacing:.05em;">{{ Str::afterLast($c->certificate_no, '-') }}</span>
            <div style="flex:1;height:22px;border-radius:5px;border:1px solid #C9942E;
                        background:linear-gradient(180deg,#125C33,#0A4127);display:flex;align-items:center;justify-content:center;">
                <span style="font-size:9.5px;font-weight:700;letter-spacing:.06em;color:#F0DFB4;text-transform:uppercase;">
                    {{ $isFr
                       ? 'Document numérique — l\'exemplaire de référence est celui vérifié en ligne'
                       : 'A digital record — the authoritative copy is the one verified online' }}
                </span>
            </div>
            <span class="mono" style="width:186px;flex:none;text-align:right;font-size:8px;color:#4E5B50;word-break:break-all;">{{ $c->certificate_no }}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin:6px 20px 0;padding-bottom:11px;">
            <span style="font-size:8px;font-weight:600;letter-spacing:.06em;color:#5A6357;text-transform:uppercase;">
                {{ $isFr ? 'ArtisanHub237 · Autorité de certification' : 'ArtisanHub237 · Certification Authority' }}
            </span>
            <span class="mono" style="font-size:8px;color:#5A6357;">{{ $verifyShort }}</span>
        </div>
    </div>
    </div>
    </section>

    </article>
    </div>
    <p class="coa-hint no-print items-center justify-center gap-1.5 mt-2 text-[12px] text-[#6F6B60]">
        <i data-lucide="move-horizontal" class="w-3.5 h-3.5"></i>
        {{ $isFr ? 'Faites glisser pour voir tout le certificat, ou imprimez-le.' : 'Swipe to see the whole certificate, or print it.' }}
    </p>

    <div class="no-print mt-5 flex flex-wrap gap-2.5 justify-center">
        <button type="button" onclick="window.print()" class="ui-btn ui-btn-primary">
            <i data-lucide="printer" class="w-4 h-4"></i>
            {{ $isFr ? 'Imprimer / Enregistrer en PDF' : 'Print / Save as PDF' }}
        </button>
    </div>
</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    lucide.createIcons();

    (function () {
        var box = document.getElementById('wvc-qr');
        if (box && window.QRCode) {
            new QRCode(box, {
                text: @json($verifyUrl),
                width: 122, height: 122,
                colorDark: '#0A3D24', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* The sheet is drawn at 1024px and scaled as one unit, so it never
       rearranges — it only gets smaller. The wrapper has to be given the scaled
       height by hand, because a transform does not change the space an element
       reserves. */
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
