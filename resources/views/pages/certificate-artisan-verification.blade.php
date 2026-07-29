@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;
    use App\Support\ArtisanVerification;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials; without them
    // the partials fault on an undefined variable.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $name    = $isFr ? $business->name_fr : ($business->name_en ?: $business->name_fr);
    $owner   = $business->user;
    $issued  = Carbon::parse($certificate->issued_at);
    $expires = $certificate->expires_at ? Carbon::parse($certificate->expires_at) : null;
    $level   = (int) $certificate->level;

    $checks  = json_decode($certificate->checks ?: '{}', true) ?: [];
    $metrics = json_decode($certificate->metrics ?: '{}', true) ?: [];

    /* The document's own address. It is what the QR carries, because the only
       honest thing to point a scanner at is the record itself — not a marketing
       page, and not a verifier that would have to be built to make the claim
       true. */
    $certUrl  = route('artisan.verification.certificate', ['slug' => $business->slug, 'lang' => $lang]);
    $certHost = Str::after($certUrl, '://');

    $signature = ArtisanVerification::signatureState($certificate);

    /* ────────────────────────── What is omitted ──────────────────────────
       The artwork carries nineteen blocks. The ones that do not appear below
       are absent because the platform holds nothing that could fill them, and
       an empty labelled row on a certificate about a named human being reads
       as a checked fact rather than a missing one:

         · Awards & recognitions, training & development, guild membership,
           professional association, apprentices trained — conferred or held by
           bodies outside this platform. No table records any of them, and
           printing a national or international honour we cannot evidence would
           be forging a credential in someone's name.
         · Date of birth, gender, nationality, identity/passport number, GPS
           coordinates — personal data with no business on a public document.
           Only the masked last four of a filed identity document appears, and
           only when one has actually been filed.
         · Artisan photograph and digital portrait — rendered only from a logo
           or cover image the shop itself uploaded. There is no stock face.
         · AI verification: portrait match, duplicate account check, fraud risk,
           identity consistency, AI fingerprint, biometric reference. No model
           runs against these records. The whole block is gone.
         · NFC, wallet integration, holographic shield, UV ink, embossed seal,
           tamper-evident foil — physical or hardware properties that cannot
           exist in a browser. The decorative treatment below stays; the
           captions asserting them do not (docs/PRINT-SECURITY-SPEC.md).
         · Trust score, follower count, sales volume, export readiness,
           sustainability practices — no column on this platform holds them.
         · Named reviewer signatures. The audit trail says what happened and
           when; inventing "Verified by Marine T. Ekani" attributes an act to a
           person who does not exist.
       ─────────────────────────────────────────────────────────────────────── */

    $identityRows = collect([
        [$isFr ? 'Numéro de certificat' : 'Certificate number', $certificate->certificate_no, true],
        [$isFr ? 'Numéro d\'artisan (GAN)' : 'Global Artisan Number (GAN)', $gan, true],
        [$isFr ? 'UUID du certificat' : 'Certificate UUID', $certificate->uuid, true],
        [$isFr ? 'Date d\'émission (UTC)' : 'Issue date (UTC)', $issued->clone()->utc()->toIso8601String(), true],
        [$isFr ? 'Valable jusqu\'au (UTC)' : 'Valid until (UTC)', $expires?->clone()->utc()->toIso8601String(), true],
        [$isFr ? 'Version' : 'Version', number_format((float) $certificate->version, 1), false],
        [$isFr ? 'Vérifications effectuées' : 'Verification lookups', (string) (int) $certificate->verification_count, false],
        [$isFr ? 'Dernière vérification' : 'Last verified',
            $certificate->last_verified_at ? Carbon::parse($certificate->last_verified_at)->utc()->toIso8601String() : null, true],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $maskedId = ArtisanVerification::maskedIdentityDocument($business);

    $idTypeLabels = [
        'national_id'    => $isFr ? 'Carte nationale d\'identité' : 'National identity card',
        'passport'       => $isFr ? 'Passeport' : 'Passport',
        'residence_card' => $isFr ? 'Carte de séjour' : 'Residence permit',
    ];

    $artisanRows = collect([
        [$isFr ? 'Atelier / enseigne' : 'Workshop / trading name', $name, false],
        [$isFr ? 'Responsable' : 'Account holder', $owner?->name, false],
        [$isFr ? 'Profil public' : 'Public profile',
            $business->slug ? Str::after(route('businesses.show', ['slug' => $business->slug]), '://') : null, true],
        [$isFr ? 'Type de pièce d\'identité' : 'Identity document type',
            $business->id_document_type ? ($idTypeLabels[$business->id_document_type] ?? $business->id_document_type) : null, false],
        // The masked form is the only form. See ArtisanVerification::maskedIdentityDocument().
        [$isFr ? 'Pièce d\'identité (masquée)' : 'Identity document (masked)', $maskedId, true],
        [$isFr ? 'Pièce vérifiée le' : 'Document verified on',
            $business->id_verified_at ? Carbon::parse($business->id_verified_at)->utc()->toDateString() : null, true],
        [$isFr ? 'Téléphone' : 'Phone', $business->phone, false],
        [$isFr ? 'Courriel' : 'Email', $business->email, false],
        [$isFr ? 'Site web' : 'Website', $business->website, false],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $workshopRows = collect([
        [$isFr ? 'Adresse' : 'Address', $isFr ? $business->address_fr : ($business->address_en ?: $business->address_fr)],
        [$isFr ? 'Ville' : 'City', $business->city?->name_fr],
        [$isFr ? 'Région' : 'Region', $business->region?->name_fr],
        [$isFr ? 'Pays' : 'Country', $isFr ? 'Cameroun' : 'Cameroon'],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $tierLabels = [
        'certified' => $isFr ? 'Certifié par la plateforme' : 'Platform certified',
        'verified'  => $isFr ? 'Vérifié par la plateforme' : 'Platform verified',
    ];

    $tradeRows = collect([
        [$isFr ? 'Filière' : 'Industry',
            $business->industry ? ($isFr ? $business->industry->name_fr : ($business->industry->name_en ?: $business->industry->name_fr)) : null],
        [$isFr ? 'Activité déclarée' : 'Declared activity', $business->tagline],
        [$isFr ? 'Année de création' : 'Year established', $business->year_established],
        [$isFr ? 'Effectif déclaré' : 'Declared headcount', $business->employee_count],
        [$isFr ? 'Statut de vérification' : 'Verification standing', $tierLabels[$business->verification_tier] ?? null],
    ])->filter(fn ($r) => filled($r[1]))->values();

    /* Only keys present in the stored map are drawn. An absent key means the
       platform never attempted that check, and a cross would read as "we looked
       and they failed" — a statement about a person we have no evidence for. */
    $checkLabels = [
        'identity_document_verified' => $isFr ? 'Pièce d\'identité vérifiée par un examinateur' : 'Identity document verified by a reviewer',
        'workshop_address_on_record' => $isFr ? 'Lieu de travail enregistré' : 'Place of work on record',
        'trade_documents_accepted'   => $isFr ? 'Documents professionnels acceptés' : 'Trade documents accepted',
        'application_reviewed'       => $isFr ? 'Dossier de vérification examiné' : 'Verification application reviewed',
        'portfolio_published'        => $isFr ? 'Portfolio publié' : 'Portfolio published',
        'third_party_certification'  => $isFr ? 'Certification par un tiers' : 'Third-party certification',
        'email_verified'             => $isFr ? 'Courriel confirmé' : 'Email confirmed',
        'phone_verified'             => $isFr ? 'Téléphone confirmé' : 'Phone confirmed',
    ];

    $metricLabels = [
        'products_registered' => $isFr ? 'Produits enregistrés' : 'Products registered',
        'published_products'  => $isFr ? 'Produits publiés' : 'Published products',
        'years_active'        => $isFr ? 'Années d\'activité déclarées' : 'Declared years active',
        'member_since'        => $isFr ? 'Membre depuis' : 'Member since',
        'response_time_hours' => $isFr ? 'Délai de réponse déclaré (h)' : 'Self-reported response time (h)',
        'reviews_published'   => $isFr ? 'Avis publiés' : 'Published reviews',
        'average_rating'      => $isFr ? 'Note moyenne (sur 5)' : 'Average rating (out of 5)',
    ];

    /* The seven rungs, exactly as ArtisanVerification::levelFor() defines them.
       Rungs 6 and 7 are shown greyed with their conferring body named, because
       the pathway is real information — but the platform cannot award them, and
       says so rather than leaving a reader to assume they are merely unearned. */
    $rungs = [
        1 => [$isFr ? 'Identité établie' : 'Identity established',
              $isFr ? 'Un examinateur a confirmé de qui il s\'agit.' : 'A reviewer confirmed who this is.'],
        2 => [$isFr ? 'Professionnel' : 'Professional',
              $isFr ? 'Documents de métier déposés et acceptés.' : 'Trade paperwork filed and accepted.'],
        3 => [$isFr ? 'Atelier enregistré' : 'Workshop on record',
              $isFr ? 'Un lieu de travail figure au dossier.' : 'A place of work is on file.'],
        4 => [$isFr ? 'Certifié par la plateforme' : 'Platform certified',
              $isFr ? 'Le niveau le plus élevé décerné par notre propre examen.' : 'The highest standing our own review awards.'],
        5 => [$isFr ? 'Maître attesté par un tiers' : 'Third-party attested master',
              $isFr ? 'Une accréditation délivrée par quelqu\'un d\'autre que nous.' : 'An accreditation issued by somebody other than us.'],
        6 => [$isFr ? 'Maître du patrimoine' : 'Heritage master',
              $isFr ? 'Conféré par un organisme extérieur. La plateforme ne tient aucun registre de ce titre et ne peut pas l\'attribuer.'
                    : 'Conferred by an outside body. The platform holds no register of this title and cannot award it.'],
        7 => [$isFr ? 'Reconnaissance nationale' : 'Nationally recognised',
              $isFr ? 'Conféré par un organisme extérieur. La plateforme ne tient aucun registre de ce titre et ne peut pas l\'attribuer.'
                    : 'Conferred by an outside body. The platform holds no register of this title and cannot award it.'],
    ];

    // The headline standing: the rung actually held, never a higher one.
    $standing = [
        1 => $isFr ? 'IDENTITÉ VÉRIFIÉE' : 'VERIFIED IDENTITY',
        2 => $isFr ? 'ARTISAN PROFESSIONNEL' : 'PROFESSIONAL ARTISAN',
        3 => $isFr ? 'ATELIER ENREGISTRÉ' : 'REGISTERED WORKSHOP',
        4 => $isFr ? 'ARTISAN CERTIFIÉ PLATEFORME' : 'PLATFORM-CERTIFIED ARTISAN',
        5 => $isFr ? 'MAÎTRE ATTESTÉ PAR UN TIERS' : 'THIRD-PARTY ATTESTED MASTER',
    ][$level] ?? ($isFr ? 'NIVEAU ' . $level : 'LEVEL ' . $level);

    $eventLabels = [
        'issued'    => $isFr ? 'Certificat émis' : 'Certificate issued',
        'verified'  => $isFr ? 'Vérifié' : 'Verified',
        'approved'  => $isFr ? 'Approuvé' : 'Approved',
        'revoked'   => $isFr ? 'Révoqué' : 'Revoked',
        'reissued'  => $isFr ? 'Réémis' : 'Reissued',
    ];

    $statusMeta = [
        'active'  => [$isFr ? 'ACTIF' : 'ACTIVE', 'shield-check', '#0C7A3E'],
        'revoked' => [$isFr ? 'RÉVOQUÉ' : 'REVOKED', 'shield-off', '#A0231B'],
        'expired' => [$isFr ? 'EXPIRÉ' : 'EXPIRED', 'alert-triangle', '#8A6512'],
    ][$certificate->status] ?? [Str::upper($certificate->status), 'help-circle', '#6B6659'];

    // Rendered only if the shop actually uploaded one. Never a stock portrait.
    $portrait = $business->logo ?: $business->cover_image;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat de vérification d\'artisan' : 'Artisan Verification Certificate' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat de vérification d\'artisan' : 'Artisan Verification Certificate' }} — {{ $name }}</title>

    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           Drawn at the artwork's own 1024px canvas and then scaled as one
           unit, exactly as the sister certificates are. Every number below is
           a source pixel measured off
           `certificates/Artisan Verification Certificate (AVC).png` and
           written down in docs/AVC-DESIGN-SPEC.md. Scaling rather than
           reflowing is what keeps the proportions: a reader on a phone gets
           the same document, smaller, not a different one. The site-wide
           mobile type floor is therefore switched off inside the sheet.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .av-page {
            width: 1024px;
            transform-origin: top left;
            font-size: 12px;
            line-height: 1.32;
            color: #1D1B16;
            background: #04120A;
            padding: 7px;
        }
        .av-page, .av-page * { font-size: revert-layer; }

        /* The frame. The artwork's left band is wide (58px) and carries the
           authority wordmark vertically; the right band is a thin gold rule.
           That asymmetry is in the original and is kept. */
        .av-frame {
            position: relative;
            padding: 14px 12px 14px 58px;
            background: radial-gradient(120% 55% at 50% 0%, #0D4325, #04180D 74%) #062816;
            box-shadow: inset 0 0 0 2px #C9A24B;
        }
        .av-band { position: absolute; top: 0; bottom: 0; width: 58px; left: 0; overflow: hidden; }
        .av-vtext {
            position: absolute; left: 50%; top: 50%;
            transform: translate(-50%, -50%) rotate(180deg);
            writing-mode: vertical-rl; white-space: nowrap;
            font-size: 14px; font-weight: 700; letter-spacing: .3em; color: #E8C878;
        }

        .av-cream { background: #FAF5E9; position: relative; padding: 0 13px 0; box-shadow: inset 0 0 0 1px #C9A24B; }

        /* Cards: cream on cream, thin gold rule, numbered green tab overhanging
           the top-left corner — the artwork's own device. */
        .ac      { position: relative; border: 1px solid #C9A24B; border-radius: 8px; background: #FDFAF1; }
        .ac-head { position: absolute; top: -12px; left: 10px; height: 24px; padding: 0 12px;
                   display: flex; align-items: center; gap: 7px;
                   background: linear-gradient(180deg,#10502C,#06301A);
                   border: 1px solid #C9A24B; border-radius: 7px; color: #F6E4B0;
                   font-size: 11.5px; font-weight: 700; letter-spacing: .045em; text-transform: uppercase;
                   white-space: nowrap; }
        .ac-head .n   { color: #E8C878; font-weight: 800; }
        .ac-head .sub { font-size: 8px; font-weight: 500; letter-spacing: .04em; opacity: .8; text-transform: none; }
        .ac-body { padding: 19px 12px 11px; }

        /* Label · colon · value, the artwork's three-column row. */
        .ar    { display: grid; grid-template-columns: var(--ak,140px) 7px 1fr; column-gap: 4px; padding: 1.2px 0; align-items: baseline; }
        .ar dt { font-size: 9.5px; font-weight: 500; color: #3A362D; }
        .ar .cl{ font-size: 9.5px; color: #8A8373; }
        .ar dd { font-size: 9.5px; font-weight: 500; color: #1D1B16; word-break: break-word; }
        .mono  { font-family: ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing: -.25px; }
        .accent{ color: #8A1F14; font-weight: 600; }

        .tick     { display: flex; align-items: flex-start; gap: 6px; font-size: 10px; padding: 2px 0; }
        .tick i   { width: 12px; height: 12px; flex: none; margin-top: 1px; }
        .tick.yes i { color: #0C7A3E; }
        .tick.no  i { color: #A0231B; }
        .tick.no  span { color: #6B6659; }

        /* A rung of the ladder. Attained rungs are filled; the rest are drawn
           as an outline — visibly a step ahead, never a badge held. */
        .rung      { display: grid; grid-template-columns: 26px 1fr; gap: 8px; padding: 3.5px 0; align-items: start; }
        .rung + .rung { border-top: 1px solid #F0E7CE; }
        .rung .pip { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
                     font-size: 10px; font-weight: 800; border: 1.4px solid #CFC5A8; color: #A79E86; background: #FFFDF6; }
        .rung[data-attained="1"] .pip { background: linear-gradient(180deg,#12833F,#0A5C2E); border-color: #0A5C2E; color: #FFF; }
        .rung .t   { font-size: 10px; font-weight: 700; color: #A79E86; }
        .rung[data-attained="1"] .t { color: #0A3A22; }
        .rung .d   { font-size: 8.5px; color: #8A8373; line-height: 1.35; }
        .rung .tag { font-size: 7.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
                     color: #8A6512; background: #FBF1D8; border: 1px solid #E7D6A6; border-radius: 4px; padding: 0 4px; }

        .av-foot  { margin: 0 -13px; height: 86px; position: relative; overflow: hidden;
                    background: linear-gradient(180deg,#0B3E23,#04180D);
                    border-top: 2px solid #C9A24B;
                    display: grid; grid-template-columns: 250px 1fr 250px; align-items: center; }
        .av-strip { margin: 0 -13px; background: #030F08; color: #D8C89A; padding: 6px 0 7px; text-align: center;
                    font-size: 8.5px; letter-spacing: .06em; line-height: 1.5; text-transform: uppercase; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .coa-fit { overflow: visible; }
            @page { size: A4 portrait; margin: 6mm; }
        }
    </style>
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#EFEADF] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>
@include('pages.partials.coa-ornaments')
@include('pages.partials.coa-security')

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Artisans' : 'Artisans' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[200px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Vérification' : 'Verification' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[4px]">
    <article class="av-page cert-band-host">
    {{-- The family's classification band. The artwork's own left band — kente
         lattice under the authority wordmark, set vertically — filled this same
         gutter, and the two cannot both be here: the reader would get two
         columns of vertical type in 58px. The kente stays as the ground the
         band sits on, the wordmark goes, and the authority is named in full in
         the body of the sheet where it can actually be read. --}}
    @include('pages.partials.certificate-band', ['code' => 'AVC'])
    <div class="av-frame">

        <div class="av-band">
            <svg width="58" height="3000" aria-hidden="true"><rect width="58" height="3000" fill="url(#coaKenteDark)" opacity=".5"/></svg>
        </div>

    <div class="av-cream">

        {{-- A real guilloché behind the sheet: a hairline lathe pattern that
             survives the vector original and degrades in a copy. It carries no
             claim, so it needs no caption. --}}
        <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.5;pointer-events:none;z-index:0;"
             viewBox="0 0 200 200" preserveAspectRatio="none" aria-hidden="true">
            <rect width="200" height="200" fill="url(#coaAntiCopy)"/>
        </svg>
        <svg style="position:absolute;left:50%;top:300px;transform:translateX(-50%);opacity:.16;pointer-events:none;z-index:0;"
             width="330" height="330" viewBox="0 0 200 200" aria-hidden="true">
            <use href="#coaGuillocheRosette"/>
        </svg>

        <div style="position:relative;z-index:1;">

        {{-- ══ Header ══ --}}
        <div style="display:grid;grid-template-columns:150px minmax(0,1fr) 168px;gap:10px;padding-top:16px;align-items:start;">

            {{-- Wax seal, artwork x 78–208 --}}
            <svg width="130" height="130" viewBox="0 0 194 194" aria-hidden="true" style="margin-top:6px;">
                <use href="#coaSealTeeth"/>
                <circle cx="97" cy="97" r="90" fill="url(#coaSealFace)"/>
                <circle cx="97" cy="97" r="72" fill="none" stroke="#FBEEC6" stroke-width="2.5" opacity=".7"/>
                <text font-family="Poppins,sans-serif" font-size="15" font-weight="800" fill="#4A360B" letter-spacing="3">
                    <textPath href="#coaSealTop" startOffset="50%" text-anchor="middle">ARTISANHUB237</textPath>
                </text>
                <text font-family="Poppins,sans-serif" font-size="11" font-weight="700" fill="#4A360B" letter-spacing="2.2">
                    <textPath href="#coaSealBot" startOffset="50%" text-anchor="middle">{{ $isFr ? 'VÉRIFIÉ · CAMEROUN' : 'VERIFIED · CAMEROON' }}</textPath>
                </text>
                <image href="{{ brand_asset('mark') }}" x="55" y="55" width="84" height="84" preserveAspectRatio="xMidYMid meet"/>
            </svg>

            <div style="text-align:center;">
                <img src="{{ brand_asset('full') }}" alt="Artisan Hub 237"
                     style="width:392px;max-width:100%;height:auto;margin:0 auto;display:block;">
                <p style="margin-top:1px;font-size:10.5px;font-weight:600;letter-spacing:.21em;color:#5C574B;">
                    {{ $isFr ? 'CONNECTER L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
                </p>

                <div style="display:flex;align-items:center;gap:10px;justify-content:center;margin-top:9px;">
                    <svg width="200" height="3" aria-hidden="true"><rect width="200" height="3" fill="url(#coaGoldRule)"/></svg>
                    <svg width="9" height="9" viewBox="0 0 10 10" aria-hidden="true"><path d="M5 0l5 5-5 5-5-5z" fill="#C9942E"/></svg>
                    <svg width="200" height="3" aria-hidden="true"><rect width="200" height="3" fill="url(#coaGoldRule)"/></svg>
                </div>

                <h1 style="margin-top:8px;font-family:'Playfair Display',Georgia,serif;font-size:{{ $isFr ? 29 : 33 }}px;line-height:1.02;
                           font-weight:800;color:#12140F;letter-spacing:.004em;white-space:nowrap;
                           display:inline-block;transform:scaleX(.86);transform-origin:center;">
                    {{ $isFr ? "CERTIFICAT DE VÉRIFICATION D'ARTISAN" : 'ARTISAN VERIFICATION CERTIFICATE' }}
                </h1>

                <p style="margin-top:9px;">
                    <span style="display:inline-block;height:27px;line-height:27px;padding:0 22px;border-radius:3px;
                                 background:linear-gradient(180deg,#10502C,#06301A);border:1px solid #C9A24B;
                                 color:#F6E4B0;font-size:11px;font-weight:700;letter-spacing:.055em;">
                        {{ $isFr ? 'AVC — VÉRIFICATION OFFICIELLE DANS LE REGISTRE ARTISANHUB237'
                                 : 'AVC — OFFICIAL VERIFICATION IN THE ARTISANHUB237 REGISTRY' }}
                    </span>
                </p>

                <p style="margin:8px auto 0;max-width:600px;font-size:10px;line-height:1.5;color:#3F3C34;">
                    {{ $isFr
                       ? 'Ce certificat indique ce que la plateforme ArtisanHub237 avait au dossier concernant l\'artisan désigné ci-dessous à la date d\'émission, point par point. Il n\'atteste ni de la qualité du travail ni d\'aucune distinction décernée par un organisme extérieur.'
                       : 'This certificate states, item by item, what the ArtisanHub237 platform held on file about the artisan named below on the date of issue. It attests neither to the quality of the work nor to any distinction conferred by an outside body.' }}
                </p>
            </div>

            {{-- The verification panel. The QR carries this document's own
                 address, because that is the one destination that genuinely
                 resolves to the record being certified. --}}
            <div>
                <div class="ac" style="padding:9px 8px;text-align:center;">
                    <p style="font-size:9.5px;font-weight:800;letter-spacing:.05em;color:#0A3A22;">
                        {{ $isFr ? 'CODE QR — VÉRIFIER' : 'QR CODE — VERIFY' }}
                    </p>
                    <div id="av-qr" style="margin:7px auto 0;width:128px;height:128px;"></div>
                    <p style="margin-top:7px;font-size:8.5px;font-weight:600;letter-spacing:.045em;color:#6B6659;">
                        {{ $isFr ? 'SCANNER POUR VÉRIFIER' : 'SCAN TO VERIFY' }}
                    </p>
                    <p class="mono" style="margin-top:2px;font-size:7.5px;color:#8A8373;word-break:break-all;">{{ $certHost }}</p>
                </div>

                <div style="margin-top:6px;border-radius:8px;color:#fff;text-align:center;padding:9px 6px;
                            background:linear-gradient(180deg,#0F5130,#04220F);border:1px solid #C9A24B;">
                    <p style="font-size:8px;font-weight:600;letter-spacing:.07em;opacity:.85;">
                        {{ $isFr ? 'STATUT DU CERTIFICAT' : 'CERTIFICATE STATUS' }}
                    </p>
                    <p style="margin-top:3px;display:flex;align-items:center;justify-content:center;gap:6px;font-size:17px;font-weight:800;">
                        <i data-lucide="{{ $statusMeta[1] }}" style="width:16px;height:16px;"></i>{{ $statusMeta[0] }}
                    </p>
                    <p data-standing="{{ $level }}" style="margin-top:4px;font-size:9px;font-weight:700;letter-spacing:.05em;color:#F6E4B0;">
                        {{ $standing }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ══ 1 · Certificate identity ══ --}}
        <section class="ac" style="margin-top:26px;">
            <div class="ac-head"><span class="n">1.</span> {{ $isFr ? 'Identité du certificat' : 'Certificate identity' }}</div>
            <div class="ac-body">
                <dl style="--ak:186px;">
                    @foreach($identityRows as [$k, $v, $m])
                    <div class="ar"><dt>{{ $k }}</dt><span class="cl">:</span><dd class="{{ $m ? 'mono' : '' }} {{ $loop->index < 3 ? 'accent' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- ══ 2 · Artisan · 3 · Workshop ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:24px;align-items:stretch;">

            <section class="ac">
                <div class="ac-head"><span class="n">2.</span> {{ $isFr ? 'Artisan' : 'Artisan' }}</div>
                <div class="ac-body" style="display:grid;grid-template-columns:{{ $portrait ? '96px 1fr' : '1fr' }};gap:11px;align-items:start;">
                    @if($portrait)
                    {{-- Shown only because the shop uploaded it. There is no
                         stock face and no generated portrait on this document. --}}
                    <img src="{{ asset('storage/' . $portrait) }}" alt=""
                         style="width:96px;height:112px;object-fit:cover;border:1px solid #C9A24B;border-radius:5px;">
                    @endif
                    <dl style="--ak:126px;">
                        @foreach($artisanRows as [$k, $v, $m])
                        <div class="ar"><dt>{{ $k }}</dt><span class="cl">:</span><dd class="{{ $m ? 'mono' : '' }}">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            <section class="ac">
                <div class="ac-head"><span class="n">3.</span> {{ $isFr ? 'Atelier' : 'Workshop' }}</div>
                <div class="ac-body">
                    @if($workshopRows->isNotEmpty())
                    <dl style="--ak:96px;">
                        @foreach($workshopRows as [$k, $v])
                        <div class="ar"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @else
                    <p style="font-size:9.5px;color:#8A8373;">
                        {{ $isFr ? 'Aucun lieu de travail n\'a été enregistré.' : 'No place of work has been recorded.' }}
                    </p>
                    @endif
                    {{-- The artwork prints GPS coordinates to five decimals. A
                         precise fix on where a named person works is not
                         something a public document should carry, so the region
                         is as close as this gets. --}}
                    <p style="margin-top:8px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'Les coordonnées précises de l\'atelier ne figurent pas sur ce document.'
                                 : 'Precise workshop coordinates are deliberately not printed on this document.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 4 · Trade · 5 · Checks ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:24px;align-items:stretch;">

            <section class="ac">
                <div class="ac-head"><span class="n">4.</span> {{ $isFr ? 'Métier déclaré' : 'Declared trade' }}</div>
                <div class="ac-body">
                    @if($tradeRows->isNotEmpty())
                    <dl style="--ak:132px;">
                        @foreach($tradeRows as [$k, $v])
                        <div class="ar"><dt>{{ $k }}</dt><span class="cl">:</span><dd>{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                    @else
                    <p style="font-size:9.5px;color:#8A8373;">
                        {{ $isFr ? 'Aucune information de métier n\'a été déclarée.' : 'No trade information has been declared.' }}
                    </p>
                    @endif
                    <p style="margin-top:8px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'Ces éléments sont déclarés par l\'artisan. La plateforme n\'inspecte pas le travail.'
                                 : 'These entries are declared by the artisan. The platform does not inspect the work.' }}
                    </p>
                </div>
            </section>

            <section class="ac">
                <div class="ac-head">
                    <span class="n">5.</span> {{ $isFr ? 'Contrôles effectués' : 'Checks performed' }}
                    <span class="sub">{{ $isFr ? 'à la date d\'émission' : 'at time of issuance' }}</span>
                </div>
                <div class="ac-body">
                    @foreach($checkLabels as $key => $label)
                        @if(array_key_exists($key, $checks))
                        <div class="tick {{ $checks[$key] ? 'yes' : 'no' }}">
                            <i data-lucide="{{ $checks[$key] ? 'check-circle-2' : 'x-circle' }}"></i>
                            <span>{{ $label }}</span>
                        </div>
                        @endif
                    @endforeach
                    {{-- Anything the platform never attempts is simply not on
                         this list: no tax check, no criminal-record check, no
                         site visit, no inspection of the craft itself. An
                         unticked box for a check we never ran would read as a
                         failure we have no evidence of. --}}
                    <p style="margin-top:9px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'Seuls les contrôles que la plateforme effectue réellement figurent ici. Les points non listés n\'ont pas été évalués.'
                                 : 'Only checks the platform actually performs are listed. Points not listed were not assessed.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 6 · The ladder ══ --}}
        <section class="ac" style="margin-top:24px;">
            <div class="ac-head">
                <span class="n">6.</span> {{ $isFr ? 'Niveau de vérification' : 'Verification level' }}
                <span class="sub">{{ $isFr ? 'niveau ' . $level . ' sur 7' : 'level ' . $level . ' of 7' }}</span>
            </div>
            <div class="ac-body" style="display:grid;grid-template-columns:200px 1fr;gap:16px;align-items:start;">

                <div style="text-align:center;padding-top:4px;">
                    <div style="display:inline-block;padding:9px 26px;border-radius:9px;color:#fff;
                                background:linear-gradient(180deg,#12833F,#0A5C2E);border:1px solid #0A5C2E;">
                        <p style="font-size:8.5px;font-weight:600;letter-spacing:.09em;opacity:.85;">{{ $isFr ? 'NIVEAU' : 'LEVEL' }}</p>
                        <p style="font-size:30px;font-weight:800;line-height:1.05;">{{ $level }}</p>
                    </div>
                    <p style="margin-top:8px;font-size:11px;font-weight:800;letter-spacing:.04em;color:#0A3A22;">{{ $standing }}</p>
                    <p style="margin-top:6px;font-size:8.5px;color:#8A8373;line-height:1.45;">
                        {{ $isFr ? 'Le niveau est calculé à partir des seules pièces figurant au dossier. Il n\'est jamais attribué à la main.'
                                 : 'The level is derived from the records on file alone. It is never granted by hand.' }}
                    </p>
                </div>

                <div>
                    @foreach($rungs as $n => [$rTitle, $rDesc])
                    <div class="rung" data-rung="{{ $n }}" data-attained="{{ $level >= $n ? 1 : 0 }}">
                        <span class="pip">{{ $level >= $n ? '✓' : $n }}</span>
                        <div>
                            <p class="t">
                                {{ $n }}. {{ $rTitle }}
                                @if($n >= 6)
                                <span class="tag">{{ $isFr ? 'hors plateforme' : 'outside the platform' }}</span>
                                @elseif($level >= $n)
                                <span class="tag" style="color:#0A5C2E;background:#E7F4EB;border-color:#B6DCC2;">{{ $isFr ? 'atteint' : 'attained' }}</span>
                                @endif
                            </p>
                            <p class="d">{{ $rDesc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ══ 7 · Metrics · 8 · Integrity ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:24px;align-items:stretch;">

            <section class="ac">
                <div class="ac-head">
                    <span class="n">7.</span> {{ $isFr ? 'Chiffres du dossier' : 'Record metrics' }}
                    <span class="sub">{{ $isFr ? 'à la date d\'émission' : 'at time of issuance' }}</span>
                </div>
                <div class="ac-body">
                    @php $shown = collect($metricLabels)->filter(fn ($l, $k) => array_key_exists($k, $metrics)); @endphp
                    @if($shown->isNotEmpty())
                    <dl style="--ak:186px;">
                        @foreach($shown as $key => $label)
                        <div class="ar"><dt>{{ $label }}</dt><span class="cl">:</span><dd>{{ $metrics[$key] }}</dd></div>
                        @endforeach
                    </dl>
                    @else
                    <p style="font-size:9.5px;color:#8A8373;">{{ $isFr ? 'Aucun chiffre au dossier.' : 'No figures on record.' }}</p>
                    @endif
                    {{-- The artwork lists a trust score, a follower count and a
                         customer rating in stars. Nothing on this platform
                         stores the first two, and a rating only appears above
                         when reviews actually exist. --}}
                    <p style="margin-top:9px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'Ces chiffres décrivent l\'activité sur la plateforme. Ils ne sont pas un indice de confiance et ne sont pas couverts par la signature ci-contre.'
                                 : 'These figures describe activity on the platform. They are not a trust score, and they are not covered by the signature opposite.' }}
                    </p>
                </div>
            </section>

            <section class="ac">
                <div class="ac-head"><span class="n">8.</span> {{ $isFr ? 'Intégrité et signature' : 'Integrity and signature' }}</div>
                <div class="ac-body">
                    <dl style="--ak:150px;">
                        <div class="ar"><dt>{{ $isFr ? 'Empreinte (SHA-256)' : 'Content hash (SHA-256)' }}</dt><span class="cl">:</span>
                            <dd class="mono" style="word-break:break-all;">{{ Str::upper($certificate->content_hash) }}</dd></div>
                        @if($certificate->ca_signature)
                        <div class="ar"><dt>{{ $isFr ? 'Signature Ed25519' : 'Ed25519 signature' }}</dt><span class="cl">:</span>
                            <dd class="mono" style="word-break:break-all;">{{ Str::limit($certificate->ca_signature, 88, '…') }}</dd></div>
                        <div class="ar"><dt>{{ $isFr ? 'Identifiant de clé' : 'Signing key id' }}</dt><span class="cl">:</span>
                            <dd class="mono">{{ $certificate->ca_kid }}</dd></div>
                        <div class="ar"><dt>{{ $isFr ? 'État de la signature' : 'Signature state' }}</dt><span class="cl">:</span>
                            <dd>{{ [
                                'valid'    => $isFr ? 'Vérifiée' : 'Verifies',
                                'invalid'  => $isFr ? 'Ne vérifie pas' : 'Does not verify',
                                'unsigned' => $isFr ? 'Non signée' : 'Unsigned',
                            ][$signature['state']] ?? $signature['state'] }}</dd></div>
                        @endif
                        <div class="ar"><dt>{{ $isFr ? 'Code de vérification' : 'Verification PIN' }}</dt><span class="cl">:</span>
                            <dd class="mono accent">{{ $certificate->verification_pin }}</dd></div>
                        <div class="ar"><dt>{{ $isFr ? 'Clé publique' : 'Public key' }}</dt><span class="cl">:</span>
                            <dd class="mono">{{ Str::after(route('ca.jwks'), '://') }}</dd></div>
                    </dl>
                    {{-- This is the one security claim on the sheet that a
                         stranger can test without trusting us: fetch the key,
                         verify the signature offline. Everything else here is
                         decoration and is captioned as nothing more. --}}
                    <p style="margin-top:9px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'La signature peut être vérifiée hors ligne avec la clé publique ci-dessus. Les chiffres du dossier ne sont pas couverts par l\'empreinte, car ils évoluent.'
                                 : 'The signature can be verified offline against the public key above. The record metrics are outside the hash, because they change.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ 9 · Statement · 10 · Audit trail ══ --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:24px;align-items:stretch;">

            <section class="ac">
                <div class="ac-head"><span class="n">9.</span> {{ $isFr ? 'Déclaration' : 'Statement' }}</div>
                <div class="ac-body" style="font-size:9px;line-height:1.55;color:#3F3C34;display:flex;flex-direction:column;gap:7px;">
                    <p>{{ $isFr
                        ? 'ArtisanHub237 atteste que, à la date d\'émission, les éléments énumérés ci-dessus figuraient au dossier de l\'artisan désigné et avaient été examinés dans les termes décrits.'
                        : 'ArtisanHub237 certifies that, on the date of issue, the items listed above were on file for the artisan named and had been reviewed in the terms described.' }}</p>
                    <p>{{ $isFr
                        ? 'Ce document n\'évalue pas la qualité du travail, ne confère aucun titre professionnel et ne remplace aucune autorisation, licence ou inscription exigée par la loi.'
                        : 'This document does not assess the quality of the work, confers no professional title, and replaces no permit, licence or registration required by law.' }}</p>
                    <p>{{ $isFr
                        ? 'ArtisanHub237 est une entreprise privée, sans affiliation gouvernementale. La plateforme n\'est partie à aucune transaction et n\'en reçoit pas le prix ; elle ne perçoit que ses propres frais de service.'
                        : 'ArtisanHub237 is a private company with no governmental affiliation. The platform is party to no transaction and does not receive the price; it collects only its own service fees.' }}</p>
                    <p style="margin-top:2px;text-align:center;font-family:'Playfair Display',Georgia,serif;font-style:italic;font-size:16px;color:#0A3A22;">
                        ArtisanHub237 Certification Authority
                    </p>
                    {{-- The artwork sets three handwritten signatures under
                         three invented names. The signing act here is the
                         Ed25519 signature in section 8, and attributing it to a
                         person who does not exist would be the forgery the rest
                         of this document exists to prevent. --}}
                    <p style="text-align:center;font-size:8px;color:#9A9384;">
                        {{ $isFr ? 'Signé par clé, non à la main. Voir la section 8.' : 'Signed by key, not by hand. See section 8.' }}
                    </p>
                </div>
            </section>

            <section class="ac">
                <div class="ac-head"><span class="n">10.</span> {{ $isFr ? 'Journal d\'audit' : 'Audit trail' }}</div>
                <div class="ac-body">
                    @if(count($trail))
                    <table style="width:100%;border-collapse:collapse;font-size:9px;">
                        <thead>
                            <tr style="font-size:8px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6B6659;text-align:left;">
                                <th style="padding-bottom:4px;">{{ $isFr ? 'Horodatage (UTC)' : 'Timestamp (UTC)' }}</th>
                                <th style="padding-bottom:4px;">{{ $isFr ? 'Événement' : 'Event' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trail as $event)
                            <tr style="border-top:1px solid #F0E7CE;">
                                <td class="mono" style="padding:4px 8px 4px 0;white-space:nowrap;">{{ Carbon::parse($event->occurred_at)->utc()->toIso8601String() }}</td>
                                <td style="padding:4px 0;">
                                    {{ $eventLabels[$event->event] ?? $event->event }}@if($event->note) — {{ $event->note }}@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="font-size:9.5px;color:#8A8373;">{{ $isFr ? 'Aucun événement enregistré.' : 'No events recorded.' }}</p>
                    @endif
                    <p style="margin-top:9px;font-size:8px;color:#9A9384;line-height:1.4;">
                        {{ $isFr ? 'Le journal est chaîné par empreinte : une entrée supprimée rompt la chaîne.'
                                 : 'The trail is hash-chained: a removed entry breaks the chain.' }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ Footer band ══ --}}
        <div style="height:22px;"></div>
        <footer class="av-foot">
            <svg style="position:absolute;left:0;top:0;" width="150" height="86" aria-hidden="true">
                <rect width="150" height="86" fill="url(#coaKenteDark)" opacity=".7"/>
            </svg>
            <svg style="position:absolute;right:0;top:0;transform:scaleX(-1);" width="150" height="86" aria-hidden="true">
                <rect width="150" height="86" fill="url(#coaKenteDark)" opacity=".7"/>
            </svg>

            <div style="position:relative;padding-left:22px;display:flex;align-items:center;gap:11px;">
                <svg width="34" height="24" viewBox="0 0 60 40" aria-hidden="true">
                    <rect x="0" y="0" width="20" height="40" fill="#0F7A34"/>
                    <rect x="20" y="0" width="20" height="40" fill="#C8102E"/>
                    <rect x="40" y="0" width="20" height="40" fill="#E5A82E"/>
                    <path d="M30 13l2.2 6.4h6.7l-5.4 4 2 6.4-5.5-4-5.5 4 2-6.4-5.4-4h6.7z" fill="#F7DC7A"/>
                </svg>
                <p style="font-size:8.5px;font-weight:700;letter-spacing:.07em;color:#E7EFE8;line-height:1.6;">
                    {{ $isFr ? 'ENRACINÉ DANS LE PATRIMOINE.' : 'ROOTED IN HERITAGE.' }}<br>
                    {{ $isFr ? 'PROPULSÉ PAR LA TECHNOLOGIE.' : 'POWERED BY TECHNOLOGY.' }}
                </p>
            </div>

            <div style="position:relative;text-align:center;">
                <p style="font-size:12px;font-weight:700;letter-spacing:.06em;color:#E8C878;white-space:nowrap;">
                    {{ $isFr ? 'IDENTITÉ VÉRIFIÉE · MÉTIER ENREGISTRÉ · DOSSIER OUVERT'
                             : 'IDENTITY VERIFIED · TRADE ON RECORD · FILE OPEN TO INSPECTION' }}
                </p>
                <p style="margin-top:4px;font-size:12px;color:#F3EFE2;">www.artisanhub237.com</p>
            </div>

            <div style="position:relative;text-align:right;padding-right:22px;">
                <p style="font-size:9.5px;font-weight:700;color:#E7EFE8;line-height:1.5;">
                    ArtisanHub237<br>
                    <span style="font-weight:500;opacity:.85;">{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}</span>
                </p>
            </div>
        </footer>

        {{-- Real microtext: 1.6px vector type that reads as a grey rule at
             100% and resolves under a loupe. It states who issued the sheet,
             which is the only thing microtext is good for. --}}
        <div class="av-strip">
            <svg width="998" height="12" viewBox="0 0 200 2.4" preserveAspectRatio="none" aria-hidden="true" style="display:block;margin:0 auto;">
                <use href="#coaMicrotextLine"/>
            </svg>
        </div>

        </div>{{-- /z-index wrapper --}}
    </div>
    </div>
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
        <a href="{{ route('ca.jwks') }}" class="ui-btn ui-btn-secondary">
            <i data-lucide="key-round" class="w-4 h-4"></i>
            {{ $isFr ? 'Clé publique de signature' : 'Signing public key' }}
        </a>
        <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
            {{ $isFr ? 'Retour au profil' : 'Back to the profile' }}
        </a>
    </div>
</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    lucide.createIcons();

    (function () {
        var box = document.getElementById('av-qr');
        if (box && window.QRCode) {
            new QRCode(box, {
                text: @json($certUrl),
                width: 128, height: 128,
                colorDark: '#0A3A22', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* Drawn at the artwork's own 1024px width and scaled to fit, so the layout
       never rearranges — it only gets smaller. The wrapper is given the scaled
       height by hand, because a transform does not affect the space an element
       reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.av-page');
        if (!fit || !page) return;

        /* Below this scale the sheet is present but unreadable — 9px rows land
           near 4px on a narrow phone. Rather than reflow into a different
           document, it is held at a legible scale and the frame becomes
           pannable, which is how anyone reads a certificate on a phone. */
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
        document.querySelectorAll('.av-page img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', apply);
        });
        window.addEventListener('beforeprint', apply);
    })();
</script>
</body>
</html>
