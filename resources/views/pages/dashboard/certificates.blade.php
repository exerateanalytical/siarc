@extends('layouts.dashboard')

@php
/*
 * The artisan's own view of the certificate machinery.
 *
 * Everything else built on top of the registers points outward — at a buyer
 * scanning a code, or at an institution auditing a register. This page points
 * inward, at the person the records are about, and that changes what "correct"
 * means. A public certificate that omits a fact is incomplete. This page
 * telling a weaver that a check "failed" when nobody ever ran it is an
 * accusation delivered to the one person who cannot dismiss it.
 *
 * So the whole page is written under one rule: it reports the state of the
 * RECORD, never the quality of the work, and it never renders a verdict the
 * register does not hold. Where something is missing it names the next step.
 *
 * All gathering happens here because the route deliberately passes only $lang;
 * the page is a read of five registers and nothing about it needs a controller.
 */

$isFr      = $lang === 'fr';
$pageTitle = $isFr ? 'Mes certificats' : 'My certificates';

$siacUser = session('siac_user') ?? [];

// Same lookup every other seller page uses. first(), not firstOrFail(): an
// artisan who has not opened a shop yet is a normal state of the world, not a
// 404, and this page is one of the places they are most likely to land first.
$business = \App\Modules\Businesses\Models\Business::with('user')
    ->where('user_id', $siacUser['id'] ?? null)
    ->first();

// Defaults, so the no-shop branch below can be a plain early return in the
// template rather than a nest of isset() guards further down.
$verification = null;
$level        = 0;
$checks       = [];
$workshop     = null;
$assessment   = null;
$rows         = [];
$flagRows     = [];

if ($business) {
    // forBusiness() is a pure read — unlike ProductCertificate::forProduct(),
    // it does not issue on demand. Nothing on this page may mint a document:
    // an artisan looking at their own record must not thereby change it.
    $verification = \App\Support\ArtisanVerification::forBusiness($business);
    $level        = \App\Support\ArtisanVerification::levelFor($business);
    $checks       = \App\Support\ArtisanVerification::checksFor($business);

    $workshop = \Illuminate\Support\Facades\DB::table('workshops')
        ->where('business_id', $business->id)
        ->orderByDesc('id')
        ->first();

    if ($workshop) {
        $assessment = \App\Support\WorkshopRegister::assessment($workshop->id);
    }

    $products = \App\Modules\Products\Models\Product::where('business_id', $business->id)
        ->where('status', 'published')
        ->orderByDesc('id')
        ->get();

    foreach ($products as $product) {
        // Read the certificate row directly rather than through
        // ProductCertificate::forProduct(), which ISSUES one when none exists.
        // Loading a dashboard is not an act of registration, and a page that
        // silently minted documents would fill the number sequence with
        // certificates nobody asked for.
        $certificate = \Illuminate\Support\Facades\DB::table('product_certificates')
            ->where('product_id', $product->id)
            ->whereNull('revoked_at')
            ->orderByDesc('version')
            ->first();

        $rows[] = [
            'product'     => $product,
            'certificate' => $certificate,
            // signatureState(), never verify(): verify() writes
            // verification_count and last_verified_at, so rendering this page
            // would inflate the artisan's own public verification tally every
            // time they checked it. The counter is supposed to mean "a stranger
            // looked this up".
            'signature'   => $certificate ? \App\Support\ProductCertificate::signatureState($certificate) : null,
        ];

        foreach (\App\Support\ProductFlags::active($product) as $flag) {
            $flagRows[] = ['product' => $product, 'flag' => $flag];
        }
    }
}

/* The seven rungs, worded exactly as ArtisanVerification::levelFor() defines
   them. 6 and 7 carry their own explanation because they are not merely
   unearned — the platform holds no register of either and cannot award them,
   and an artisan is entitled to know that rather than assume they fell short. */
$rungs = [
    1 => [$isFr ? 'Identité établie' : 'Identity established',
          $isFr ? 'Un examinateur a confirmé de qui il s\'agit.'
                : 'A reviewer confirmed who this is.'],
    2 => [$isFr ? 'Professionnel' : 'Professional',
          $isFr ? 'Vos documents de métier ont été déposés et acceptés.'
                : 'Your trade paperwork has been filed and accepted.'],
    3 => [$isFr ? 'Atelier enregistré' : 'Workshop on record',
          $isFr ? 'Un lieu de travail figure à votre dossier.'
                : 'A place of work is on your file.'],
    4 => [$isFr ? 'Certifié par la plateforme' : 'Platform certified',
          $isFr ? 'Le niveau le plus élevé que notre propre examen puisse accorder.'
                : 'The highest standing our own review can award.'],
    5 => [$isFr ? 'Maître attesté par un tiers' : 'Third-party attested master',
          $isFr ? 'Une accréditation délivrée par quelqu\'un d\'autre que nous.'
                : 'An accreditation issued by somebody other than us.'],
    6 => [$isFr ? 'Maître du patrimoine' : 'Heritage master',
          $isFr ? 'Conféré par un organisme extérieur. La plateforme ne tient aucun registre de ce titre et ne peut donc pas l\'attribuer.'
                : 'Conferred by an outside body. The platform holds no register of this title and therefore cannot award it.'],
    7 => [$isFr ? 'Reconnaissance nationale' : 'Nationally recognised',
          $isFr ? 'Conféré par un organisme extérieur. La plateforme ne tient aucun registre de ce titre et ne peut donc pas l\'attribuer.'
                : 'Conferred by an outside body. The platform holds no register of this title and therefore cannot award it.'],
];

/* What each rung needs, in the artisan's own terms. This is the part of the
   page that has to work: "you are at level 1" is useless without "here is the
   one thing that moves you to 2, and here is where you do it". */
$rungNext = [
    1 => $isFr ? 'Envoyez une pièce d\'identité depuis la page de vérification. Un examinateur la regarde ; le simple dépôt ne suffit pas.'
               : 'Send an identity document from the verification page. A reviewer looks at it; merely uploading it is not enough.',
    2 => $isFr ? 'Joignez vos documents de métier (registre de commerce, carte professionnelle, attestation de coopérative) à votre demande.'
               : 'Attach your trade documents (business registration, professional card, cooperative attestation) to your application.',
    3 => $isFr ? 'Renseignez l\'adresse de votre atelier dans la fiche de votre entreprise.'
               : 'Fill in your workshop address on your business record.',
    4 => $isFr ? 'Ce niveau est accordé par l\'équipe d\'examen après étude du dossier complet. Il ne se demande pas directement.'
               : 'This level is granted by the review team once the file is complete. It is not requested directly.',
    5 => $isFr ? 'Transmettez une accréditation délivrée par un organisme extérieur ; c\'est ce tiers, et non nous, qui fait ce niveau.'
               : 'Send in an accreditation issued by an outside body; it is that third party, not us, who makes this level.',
];

/* Check labels. Only keys the platform genuinely assesses ever appear — a row
   for "casier judiciaire" with an empty box would imply we ran one. */
$checkLabels = [
    'identity_document_verified' => $isFr ? 'Pièce d\'identité examinée par un vérificateur' : 'Identity document examined by a reviewer',
    'workshop_address_on_record' => $isFr ? 'Adresse d\'atelier au dossier' : 'Workshop address on record',
    'trade_documents_accepted'   => $isFr ? 'Documents de métier acceptés' : 'Trade documents accepted',
    'application_reviewed'       => $isFr ? 'Demande de vérification examinée' : 'Verification application reviewed',
    'portfolio_published'        => $isFr ? 'Au moins une pièce publiée' : 'At least one piece published',
    'third_party_certification'  => $isFr ? 'Accréditation d\'un organisme tiers' : 'Third-party accreditation',
    'email_verified'             => $isFr ? 'Adresse email confirmée' : 'Email address confirmed',
    'phone_verified'             => $isFr ? 'Numéro de téléphone confirmé' : 'Phone number confirmed',
];

$dimensionLabels = [
    'infrastructure' => $isFr ? 'Locaux' : 'Premises',
    'equipment'      => $isFr ? 'Équipement' : 'Equipment',
    'workforce'      => $isFr ? 'Main-d\'œuvre' : 'Workforce',
    'safety'         => $isFr ? 'Sécurité' : 'Safety',
    'compliance'     => $isFr ? 'Conformité' : 'Compliance',
    'sustainability' => $isFr ? 'Durabilité' : 'Sustainability',
    'documentation'  => $isFr ? 'Documentation' : 'Documentation',
];

$workshopStatusLabels = [
    'draft'            => $isFr ? 'Brouillon' : 'Draft',
    'submitted'        => $isFr ? 'Soumis pour inspection' : 'Submitted for inspection',
    'under_inspection' => $isFr ? 'Inspection en cours' : 'Inspection under way',
    'verified'         => $isFr ? 'Vérifié' : 'Verified',
    'suspended'        => $isFr ? 'Suspendu' : 'Suspended',
    'revoked'          => $isFr ? 'Retiré' : 'Withdrawn',
    'archived'         => $isFr ? 'Archivé' : 'Archived',
];

$flagLabels = [
    'reported_stolen'     => $isFr ? 'Signalée comme volée' : 'Reported stolen',
    'under_investigation' => $isFr ? 'Sous enquête' : 'Under investigation',
    'disputed_ownership'  => $isFr ? 'Propriété contestée' : 'Disputed ownership',
    'export_restricted'   => $isFr ? 'Export restreint' : 'Export restricted',
];

$signatureLabels = [
    'valid'    => $isFr ? 'Signature de l\'autorité valide' : 'Authority signature valid',
    'invalid'  => $isFr ? 'Signature de l\'autorité non concordante' : 'Authority signature does not match',
    'unsigned' => $isFr ? 'Non contresigné par l\'autorité' : 'Not counter-signed by the authority',
];
@endphp

@section('content')
<div class="max-w-4xl space-y-5">

    <div>
        <h1 class="ui-h text-[19px]">{{ $pageTitle }}</h1>
        <p class="ui-hint mt-1">
            {{ $isFr
               ? 'Ce que la plateforme a enregistré à votre sujet, et ce qu\'il manque encore. Ces mesures portent sur l\'état du dossier, jamais sur la qualité de votre travail.'
               : 'What the platform has on record about you, and what is still missing. These measures are about the state of the file, never about the quality of your work.' }}
        </p>
    </div>

    @if(! $business)
        {{-- An artisan with no shop yet. Not an error, and not a scolding: the
             registers simply have nothing to attach a certificate to. --}}
        <div class="ui-card text-center py-14 px-4">
            <i data-lucide="store" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
            <p class="ui-body max-w-md mx-auto">
                {{ $isFr
                   ? 'Les certificats se rattachent à un atelier ou une entreprise. Vous n\'en avez pas encore enregistré, il n\'y a donc rien à afficher ici pour l\'instant.'
                   : 'Certificates attach to a workshop or a business. You have not registered one yet, so there is nothing to show here for now.' }}
            </p>
            <a href="{{ route('business.create') }}" class="ui-btn ui-btn-primary mt-4">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $isFr ? 'Enregistrer mon activité' : 'Register my business' }}
            </a>
        </div>
    @else

    {{-- ───────────── 1. Verification standing ───────────── --}}
    <div class="ui-card">
        <div class="ui-card-head">
            <div>
                <p class="ui-card-title">{{ $isFr ? 'Votre vérification d\'artisan' : 'Your artisan verification' }}</p>
                <p class="ui-card-sub">{{ $business->name_fr }}</p>
            </div>
            <span class="ui-pill {{ $level > 0 ? 'ui-pill-ok' : 'ui-pill-neutral' }}">
                {{ $isFr ? 'Niveau' : 'Level' }} {{ $level }}
            </span>
        </div>

        <div class="px-4 py-4 space-y-4">

            @if($verification)
                {{-- A certificate exists: its own facts, nothing inferred. --}}
                <dl class="ui-dl">
                    <dt class="ui-dt">{{ $isFr ? 'Numéro de certificat' : 'Certificate number' }}</dt>
                    <dd class="ui-dd">{{ $verification->certificate_no }}</dd>
                    <dt class="ui-dt">{{ $isFr ? 'Émis le' : 'Issued on' }}</dt>
                    <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($verification->issued_at)->format('d/m/Y') }}</dd>
                    @if($verification->expires_at)
                    <dt class="ui-dt">{{ $isFr ? 'À réattester avant le' : 'Re-attested before' }}</dt>
                    <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($verification->expires_at)->format('d/m/Y') }}</dd>
                    @endif
                    <dt class="ui-dt">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                    <dd class="ui-dd">{{ $verification->status }}</dd>
                </dl>
                <a href="{{ route('artisan.verification.certificate', ['slug' => $business->slug]) }}"
                   class="ui-btn ui-btn-secondary ui-btn-sm">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    {{ $isFr ? 'Voir le certificat' : 'View the certificate' }}
                </a>
            @else
                {{-- No certificate. The wording is load-bearing: the platform has
                     not yet checked, which says something about us and nothing
                     about the artisan. No number is shown, because none exists. --}}
                <div class="ui-alert ui-alert-warn">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>
                        {{ $isFr
                           ? 'La plateforme n\'a pas encore vérifié votre dossier. Aucun certificat d\'artisan n\'a donc été émis à ce jour. Cela ne dit rien de votre travail : cela signifie seulement que nous n\'avons pas encore examiné les pièces.'
                           : 'The platform has not yet checked your file, so no artisan certificate has been issued to date. This says nothing about your work: it only means we have not looked at the documents yet.' }}
                    </span>
                </div>
                <a href="{{ route('verification.show') }}" class="ui-btn ui-btn-primary ui-btn-sm">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    {{ $isFr ? 'Ouvrir ma demande de vérification' : 'Open my verification application' }}
                </a>
            @endif

            {{-- The ladder. Attained rungs are marked; the rest are simply not
                 yet marked, each with the one concrete step that reaches it. --}}
            <div>
                <p class="ui-eyebrow">{{ $isFr ? 'Les sept niveaux' : 'The seven levels' }}</p>
                <ol class="mt-2 divide-y divide-[#F0F1F0]">
                    @foreach($rungs as $n => [$label, $note])
                    @php $attained = $level >= $n; @endphp
                    <li class="flex items-start gap-3 py-2.5"
                        data-rung="{{ $n }}" data-attained="{{ $attained ? 1 : 0 }}">
                        <span class="mt-0.5 shrink-0">
                            @if($attained)
                                <i data-lucide="check-circle" class="w-4 h-4 ui-green"></i>
                            @else
                                <i data-lucide="circle" class="w-4 h-4 text-[#DCE7DF]"></i>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] {{ $attained ? 'font-semibold ui-ink' : 'ui-muted' }}">
                                {{ $n }}. {{ $label }}
                            </p>
                            <p class="ui-hint">{{ $note }}</p>
                            @if(! $attained && $level + 1 === $n && isset($rungNext[$n]))
                                <p class="ui-hint mt-1">
                                    <strong>{{ $isFr ? 'Prochaine étape :' : 'Next step:' }}</strong>
                                    {{ $rungNext[$n] }}
                                </p>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>

            {{-- The checks. Only what we actually perform is listed; anything we
                 do not attempt is absent from the register and absent here, so
                 it can never read as a box we ticked "no". --}}
            <div>
                <p class="ui-eyebrow">{{ $isFr ? 'Ce que nous avons contrôlé' : 'What we checked' }}</p>
                <ul class="mt-2 divide-y divide-[#F0F1F0]">
                    @foreach($checks as $key => $passed)
                    <li class="flex items-start gap-3 py-2" data-check="{{ $key }}" data-outcome="{{ $passed ? 'yes' : 'no' }}">
                        <span class="mt-0.5 shrink-0">
                            @if($passed)
                                <i data-lucide="check" class="w-4 h-4 ui-green"></i>
                            @else
                                <i data-lucide="minus" class="w-4 h-4 text-[#B8B2A6]"></i>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] {{ $passed ? 'ui-ink' : 'ui-muted' }}">
                                {{ $checkLabels[$key] ?? $key }}
                            </p>
                            @unless($passed)
                                <p class="ui-hint">{{ $isFr ? 'Pas encore au dossier.' : 'Not on file yet.' }}</p>
                            @endunless
                        </div>
                    </li>
                    @endforeach
                </ul>
                <p class="ui-hint mt-3">
                    {{ $isFr
                       ? 'Cette liste est complète : la plateforme ne contrôle rien d\'autre. Ce qui n\'y figure pas — casier judiciaire, fiscalité, visite de site, inspection de la qualité des pièces — relève d\'institutions que nous ne sommes pas, et nous n\'émettons aucun avis là-dessus.'
                       : 'This list is complete: the platform checks nothing else. What is not on it — criminal record, tax standing, site visits, inspection of the quality of the pieces — belongs to institutions we are not, and we express no opinion on any of it.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ───────────── 2. Workshop ───────────── --}}
    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Votre atelier' : 'Your workshop' }}</p>
            @if($workshop)
            <span class="ui-pill {{ $workshop->status === 'verified' ? 'ui-pill-ok' : 'ui-pill-neutral' }}">
                {{ $workshopStatusLabels[$workshop->status] ?? $workshop->status }}
            </span>
            @endif
        </div>

        <div class="px-4 py-4 space-y-4">
        @if(! $workshop)
            <p class="ui-body">
                {{ $isFr
                   ? 'Aucun atelier n\'est inscrit au registre pour votre activité. L\'inscription se fait avec notre équipe : elle enregistre les locaux, l\'équipement et l\'effectif, puis une inspection peut être programmée. Écrivez-nous depuis le support pour l\'ouvrir.'
                   : 'No workshop is entered in the register for your business. Registration is done with our team: it records the premises, the equipment and the workforce, after which an inspection can be scheduled. Write to us from support to open one.' }}
            </p>
            <a href="{{ route('support.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="life-buoy" class="w-4 h-4"></i>
                {{ $isFr ? 'Demander l\'inscription de mon atelier' : 'Request my workshop registration' }}
            </a>
        @else
            <dl class="ui-dl">
                <dt class="ui-dt">{{ $isFr ? 'Nom' : 'Name' }}</dt>
                <dd class="ui-dd">{{ $workshop->name }}</dd>
                @if($workshop->gwn)
                <dt class="ui-dt">{{ $isFr ? 'Numéro d\'atelier' : 'Workshop number' }}</dt>
                <dd class="ui-dd">{{ $workshop->gwn }}</dd>
                @endif
                <dt class="ui-dt">{{ $isFr ? 'Niveau de vérification' : 'Verification level' }}</dt>
                <dd class="ui-dd">
                    {{-- Null until an inspection supports a level; printing 0 or 1
                         would be the register awarding a tier it never assessed. --}}
                    {{ $workshop->verification_level !== null
                       ? $workshop->verification_level
                       : ($isFr ? 'Pas encore attribué — aucune inspection ne le soutient à ce jour.'
                                : 'Not yet assigned — no inspection supports one to date.') }}
                </dd>
                @if($workshop->verified_at)
                <dt class="ui-dt">{{ $isFr ? 'Vérifié le' : 'Verified on' }}</dt>
                <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($workshop->verified_at)->format('d/m/Y') }}</dd>
                @endif
            </dl>

            @if($assessment)
            <div>
                <p class="ui-eyebrow">{{ $isFr ? 'Les sept dimensions' : 'The seven dimensions' }}</p>
                <p class="ui-hint mt-1">
                    {{ $isFr
                       ? 'Chaque ligne indique sur quoi elle se fonde. Une dimension non évaluée signifie que personne ne l\'a encore mesurée — pas qu\'elle a été jugée insuffisante.'
                       : 'Each row states what it rests on. An unassessed dimension means nobody has measured it yet — not that it was found wanting.' }}
                </p>
                <ul class="mt-2 divide-y divide-[#F0F1F0]">
                    @foreach($assessment['categories'] as $dim => $cat)
                    <li class="py-2.5" data-dimension="{{ $dim }}" data-max="{{ $cat['max'] }}">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="text-[13px] ui-ink">{{ $dimensionLabels[$dim] ?? $dim }}</p>
                            <p class="text-[12.5px] {{ $cat['max'] > 0 ? 'ui-ink' : 'ui-muted' }} shrink-0">
                                {{ $cat['max'] > 0
                                   ? $cat['score'] . ' / ' . $cat['max']
                                   : ($isFr ? 'Non évalué' : 'Not assessed') }}
                            </p>
                        </div>
                        <p class="ui-hint">{{ $cat['basis'] }}</p>
                    </li>
                    @endforeach
                </ul>
                <p class="ui-hint mt-3">
                    {{ $isFr ? 'Total évaluable :' : 'Assessable total:' }}
                    {{ $assessment['total'] }} / {{ $assessment['max'] }} — {{ $assessment['rating'] }}
                </p>
            </div>
            @endif

            @if($workshop->gwn)
            <a href="{{ route('workshop.certificate', ['gwn' => $workshop->gwn]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                {{ $isFr ? 'Certificat d\'atelier' : 'Workshop certificate' }}
            </a>
            @endif
        @endif
        </div>
    </div>

    {{-- ───────────── 3. Product certificates ───────────── --}}
    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Vos pièces publiées' : 'Your published pieces' }}</p>
        </div>

        @if($rows === [])
        <div class="px-4 py-10 text-center">
            <p class="ui-body">
                {{ $isFr
                   ? 'Un certificat d\'authenticité se rattache à une pièce publiée. Vous n\'en avez aucune publiée pour l\'instant.'
                   : 'A certificate of authenticity attaches to a published piece. You have none published at the moment.' }}
            </p>
            <a href="{{ route('products.web-create') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $isFr ? 'Ajouter une pièce' : 'Add a piece' }}
            </a>
        </div>
        @else
        <div>
            @foreach($rows as $row)
            @php
                $p    = $row['product'];
                $cert = $row['certificate'];
                $sig  = $row['signature'];
            @endphp
            <div class="px-4 py-3.5 border-b border-[#F0F1F0] last:border-0" data-product="{{ $p->slug }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[13px] font-semibold ui-ink min-w-0">{{ $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr) }}</p>
                    <span class="ui-pill {{ $cert ? 'ui-pill-ok' : 'ui-pill-neutral' }}">
                        {{ $cert ? ($isFr ? 'Certificat émis' : 'Certificate issued')
                                 : ($isFr ? 'Pas encore émis' : 'Not issued yet') }}
                    </span>
                </div>

                @if($cert)
                    <dl class="ui-dl mt-2">
                        <dt class="ui-dt">{{ $isFr ? 'Numéro' : 'Number' }}</dt>
                        <dd class="ui-dd">{{ $cert->certificate_no }}</dd>
                        <dt class="ui-dt">{{ $isFr ? 'Émis le' : 'Issued on' }}</dt>
                        <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($cert->issued_at)->format('d/m/Y') }}</dd>
                        <dt class="ui-dt">{{ $isFr ? 'Signature' : 'Signature' }}</dt>
                        <dd class="ui-dd">{{ $signatureLabels[$sig['state']] ?? $sig['state'] }}</dd>
                    </dl>
                @else
                    <p class="ui-hint mt-1">
                        {{ $isFr
                           ? 'Le certificat est établi à la première consultation publique de la fiche. Rien ne vous est demandé.'
                           : 'The certificate is drawn up the first time the public record is opened. Nothing is asked of you.' }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-1.5 mt-2.5">
                    @if($cert)
                    <a href="{{ route('product.certificate', ['slug' => $p->slug]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                        {{ $isFr ? 'Le document' : 'The document' }}
                    </a>
                    @endif
                    <a href="{{ route('certificate.hub', ['slug' => $p->slug]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                        {{ $isFr ? 'Tous ses certificats' : 'All its certificates' }}
                    </a>
                    <a href="{{ route('product.passport', ['slug' => $p->slug]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                        {{ $isFr ? 'Passeport' : 'Passport' }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ───────────── 4. Flags ───────────── --}}
    @if($flagRows !== [])
    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Signalements en cours' : 'Open concerns' }}</p>
        </div>
        <div class="px-4 py-4 space-y-3">
            <p class="ui-hint">
                {{ $isFr
                   ? 'Un signalement est une question ouverte consignée au registre, pas une conclusion. Il est levé dès qu\'il est résolu.'
                   : 'A concern is an open question recorded in the register, not a conclusion. It is lifted as soon as it is resolved.' }}
            </p>
            @foreach($flagRows as $fr)
            @php $f = $fr['flag']; @endphp
            <div class="py-2 border-t border-[#F0F1F0] first:border-0" data-flag="{{ $f->flag }}">
                <p class="text-[13px] ui-ink">
                    {{ $flagLabels[$f->flag] ?? $f->flag }} —
                    {{ $isFr ? $fr['product']->name_fr : ($fr['product']->name_en ?? $fr['product']->name_fr) }}
                </p>
                <p class="ui-hint">
                    {{ $isFr ? 'Consigné le' : 'Recorded on' }}
                    {{ \Illuminate\Support\Carbon::parse($f->raised_at)->format('d/m/Y') }}
                    @if($f->reference) · {{ $isFr ? 'Référence' : 'Reference' }} {{ $f->reference }} @endif
                </p>
                @if($f->notes)
                <p class="ui-hint mt-1">{{ $f->notes }}</p>
                @endif
            </div>
            @endforeach
            <a href="{{ route('support.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="life-buoy" class="w-4 h-4"></i>
                {{ $isFr ? 'Nous répondre à ce sujet' : 'Reply to us about this' }}
            </a>
        </div>
    </div>
    @endif

    @endif

</div>
@endsection
