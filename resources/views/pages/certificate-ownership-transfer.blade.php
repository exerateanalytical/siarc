@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;
    use App\Support\CertificationAuthority;
    use App\Support\ProvenanceRegistry;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $biz     = $product->business;
    $maker   = $biz?->user;
    $name    = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $cover   = $product->images->sortBy('sort_order')->firstWhere('is_cover', true)
               ?? $product->images->sortBy('sort_order')->first();

    $prn = ProvenanceRegistry::prnFor($product);
    $oln = ProvenanceRegistry::olnFor($product);
    $gan = $biz ? ProvenanceRegistry::ganFor($biz) : null;

    $issued   = Carbon::parse($transfer->issued_at);
    $moved    = Carbon::parse($transfer->transferred_at);
    $verifyUrl   = route('product.certificate.verify', ['ref' => $transfer->certificate_no, 'lang' => $lang]);
    $verifyShort = route('product.certificate.verify.short', ['ref' => $transfer->certificate_no]);

    /* ─────────────────────────────────────────────────────────────────────
       The register, read once.

       $chain is the whole title history oldest-first; the two rows this
       particular certificate is *about* are the ones its from/to ids point at.
       Reading them out of the chain rather than re-querying keeps the sheet
       and the sequence diagram describing the same two rows — a transfer
       document whose "previous owner" panel disagreed with its own chain
       would be worse than one that omitted the panel.
       ───────────────────────────────────────────────────────────────────── */
    $byId    = collect($chain)->keyBy('id');
    $fromRow = $transfer->from_ownership_id ? $byId->get($transfer->from_ownership_id) : null;
    $toRow   = $transfer->to_ownership_id   ? $byId->get($transfer->to_ownership_id)   : null;
    $current = collect($chain)->last();

    $entityLabels = [
        'individual' => $isFr ? 'Particulier'  : 'Individual',
        'company'    => $isFr ? 'Société'      : 'Company',
        'gallery'    => $isFr ? 'Galerie'      : 'Gallery',
        'museum'     => $isFr ? 'Musée'        : 'Museum',
        'government' => $isFr ? 'Organisme public' : 'Government body',
        'foundation' => $isFr ? 'Fondation'    : 'Foundation',
    ];
    $entityIcons = [
        'individual' => 'user-round', 'company' => 'building-2', 'gallery' => 'palette',
        'museum' => 'landmark', 'government' => 'building', 'foundation' => 'heart-handshake',
    ];
    /* Wording matters here. "Verified" means the platform saw evidence for
       this party; it is not a statement that the party is solvent, honest or
       entitled to sell. "Declared" is the far more common case and must not be
       dressed up to look like the other one. */
    $levelLabels = [
        'unverified'  => $isFr ? 'Non vérifié'          : 'Unverified',
        'declared'    => $isFr ? 'Déclaré'              : 'Declared',
        'verified'    => $isFr ? 'Identité vérifiée'    : 'Identity verified',
        'institution' => $isFr ? 'Institution vérifiée' : 'Verified institution',
    ];
    $transferTypes = [
        'sale'                => $isFr ? 'Vente'                   : 'Sale',
        'gallery_acquisition' => $isFr ? 'Acquisition par galerie' : 'Gallery acquisition',
        'donation'            => $isFr ? 'Donation'                : 'Donation',
        'inheritance'         => $isFr ? 'Succession'              : 'Inheritance',
        'exhibition_loan'     => $isFr ? 'Prêt pour exposition'    : 'Exhibition loan',
        'repatriation'        => $isFr ? 'Restitution'             : 'Repatriation',
        'exchange'            => $isFr ? 'Échange'                 : 'Exchange',
        'commission'          => $isFr ? 'Commande'                : 'Commission',
    ];
    $conditions = [
        'excellent' => $isFr ? 'Excellent' : 'Excellent',
        'very_good' => $isFr ? 'Très bon'  : 'Very good',
        'good'      => $isFr ? 'Bon'       : 'Good',
        'fair'      => $isFr ? 'Passable'  : 'Fair',
        'restored'  => $isFr ? 'Restauré'  : 'Restored',
    ];
    $statusLabels = [
        'active'     => [$isFr ? 'ACTIF'    : 'ACTIVE',     '#0F7A34', 'check-circle-2'],
        'superseded' => [$isFr ? 'REMPLACÉ' : 'SUPERSEDED', '#A8802A', 'alert-triangle'],
        'revoked'    => [$isFr ? 'RÉVOQUÉ'  : 'REVOKED',    '#B4141B', 'shield-off'],
    ];
    $statusMeta = $statusLabels[$transfer->status] ?? [$isFr ? 'INCONNU' : 'UNKNOWN', '#6B6659', 'help-circle'];

    $money = function ($value, $currency) {
        if (! filled($value) || ! filled($currency)) return null;
        // A space as the thousands separator, in both languages: the reader of
        // this document may well be neither French nor English.
        return number_format((float) $value, in_array($currency, ['XAF', 'XOF'], true) ? 0 : 2, ',', ' ') . ' ' . $currency;
    };

    /* ─────────────────────────── Product identity ───────────────────────
       The artwork lists an "AI Fingerprint ID" and an "Invisible Watermark
       Ref". There is no model and no watermarking pipeline, so the first is
       gone outright; the second is a real nullable column and prints only when
       something was actually stored in it. What sits in the fingerprint's place
       is the perceptual image hash, labelled as what it is: arithmetic over the
       pixels, not a judgement about them. */
    $attrRows = $product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->map(fn ($a) => [
            $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
            trim(($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)) . ' ' . ($a->unit ?? '')),
            false,
        ]);

    $productRows = collect([
        [$isFr ? 'N° de registre (PRN)' : 'Registry no. (PRN)', $prn, true],
        [$isFr ? 'UUID du produit' : 'Product UUID', $product->uuid, true],
        [$isFr ? 'Nom du produit' : 'Product name', $name, false],
        [$isFr ? 'Catégorie' : 'Category', $product->category ? ($isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr)) : null, false],
        [$isFr ? 'Année de création' : 'Year created', $product->created_at?->format('Y'), false],
        [$isFr ? 'Pays / région d\'origine' : 'Country / region of origin',
            collect([$isFr ? 'Cameroun (CM)' : 'Cameroon (CM)', $biz?->region?->name_fr])->filter()->implode(' · '), false],
    ])->concat($attrRows)->concat(collect([
        ['SKU', $product->sku, true],
        [$isFr ? 'Certificat d\'authenticité' : 'Certificate of authenticity', $coa?->certificate_no, true],
        [$isFr ? 'Empreinte de l\'image (pHash)' : 'Image hash (pHash)', $coa?->image_phash ? Str::upper($coa->image_phash) : null, true],
        [$isFr ? 'Réf. de filigrane' : 'Watermark reference', $coa?->watermark_ref, true],
    ]))->filter(fn ($r) => filled($r[1]))->values();

    /* ───────────────────────────── Original creator ─────────────────────
       The artwork prints the artisan's handwritten signature and a fingerprint
       plate. The platform holds neither, and a drawn-in scrawl on a provenance
       document is a forgery with extra steps. The panel keeps its geometry and
       is filled with the maker's permanent registry number instead. */
    $creatorRows = collect([
        [$isFr ? 'Numéro d\'artisan (GAN)' : 'Artisan number (GAN)', $gan, true],
        [$isFr ? 'Nom de l\'artisan' : 'Artisan name', $maker?->name, false],
        [$isFr ? 'Atelier' : 'Workshop', $biz?->name_fr, false],
        [$isFr ? 'Pays' : 'Country', $biz ? ($isFr ? 'Cameroun (CM)' : 'Cameroon (CM)') : null, false],
        [$isFr ? 'Région' : 'Region', $biz?->region?->name_fr, false],
        [$isFr ? 'Profil public' : 'Public profile', $biz?->slug ? Str::after(route('businesses.show', ['slug' => $biz->slug]), '://') : null, false],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $makerVerified = in_array($biz?->verification_tier, ['verified', 'certified'], true);

    $ownerRows = function (?object $row) use ($isFr, $entityLabels, $levelLabels) {
        if (! $row) return collect();
        return collect([
            [$isFr ? 'Réf. propriétaire' : 'Owner reference', $row->owner_ref, true],
            [$isFr ? 'Raison sociale / nom' : 'Legal name', $row->legal_name, false],
            [$isFr ? 'Type d\'entité' : 'Entity type', $entityLabels[$row->entity_type] ?? $row->entity_type, false],
            [$isFr ? 'Pays (ISO 3166-1)' : 'Country (ISO 3166-1)', $row->country_code, true],
            [$isFr ? 'Adresse' : 'Address', $row->address, false],
            [$isFr ? 'Rang dans la chaîne' : 'Position in chain', '#' . $row->sequence, true],
        ])->filter(fn ($r) => filled($r[1]))->values();
    };

    /* ───────────────────────────── Transfer details ─────────────────────
       Every optional reference drops out when the register never held it. A
       label printed against a blank on a document like this reads as "none",
       which is a fact nobody recorded. */
    $transferRows = collect([
        [$isFr ? 'Numéro de transfert' : 'Transfer number', $transfer->certificate_no, true],
        [$isFr ? 'Type de transfert' : 'Transfer type', $transferTypes[$transfer->transfer_type] ?? $transfer->transfer_type, false],
        [$isFr ? 'Date du transfert' : 'Transfer date', $moved->format('Y-m-d'), true],
        [$isFr ? 'Heure du transfert (UTC)' : 'Transfer time (UTC)', $moved->clone()->utc()->format('H:i:s') . 'Z', true],
        [$isFr ? 'Lieu du transfert' : 'Transfer location',
            collect([$transfer->transfer_city, $transfer->transfer_country])->filter()->implode(', '), false],
        [$isFr ? 'Réf. de transaction' : 'Transaction reference', $transfer->transaction_ref, true],
        [$isFr ? 'Numéro de facture' : 'Invoice number', $transfer->invoice_no, true],
        [$isFr ? 'Référence de paiement' : 'Payment reference', $transfer->payment_ref, true],
        [$isFr ? 'Devise (ISO 4217)' : 'Currency (ISO 4217)', $transfer->currency, true],
        // Respecting value_is_private is not a nicety: a declared price on a
        // public URL is a target, and the parties agreed it would not be shown.
        [$isFr ? 'Valeur déclarée' : 'Declared value',
            $transfer->value_is_private ? null : $money($transfer->declared_value, $transfer->currency), false],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $conditionRows = collect([
        [$isFr ? 'État' : 'Condition', $conditions[$transfer->condition] ?? $transfer->condition],
        [$isFr ? 'Notes sur l\'état' : 'Condition notes', $transfer->condition_notes],
        [$isFr ? 'Accessoires inclus' : 'Accessories included', $transfer->accessories],
        [$isFr ? 'Photographies au registre' : 'Photographs on record',
            $product->images->count() ?: null],
    ])->filter(fn ($r) => filled($r[1]))->values();

    /* ─────────────────────────── Authentication checks ──────────────────
       $flags carries only the concerns this platform is authoritative over,
       and a key it does not carry means "cannot be determined" — which must
       never be rendered as a tick. The remaining lines are recomputed here,
       now, rather than read from a column somebody could have set by hand. */
    $sigValid = filled($transfer->ca_signature) && CertificationAuthority::verifyCertificate(
        'otc', $transfer->certificate_no, $transfer->content_hash,
        $issued->toIso8601String(), $transfer->ca_signature
    );
    $hashValid = $transfer->content_hash === ProvenanceRegistry::transferHash($product, [
        'certificate_no'    => $transfer->certificate_no,
        'transfer_type'     => $transfer->transfer_type,
        'transferred_at'    => $transfer->transferred_at,
        'from_ownership_id' => $transfer->from_ownership_id,
        'to_ownership_id'   => $transfer->to_ownership_id,
    ]);

    $flagLabels = [
        'not_reported_stolen'     => $isFr ? 'Aucun signalement de vol au registre' : 'No theft report on this register',
        'not_under_investigation' => $isFr ? 'Aucune enquête signalée au registre' : 'No investigation reported to this register',
        'ownership_undisputed'    => $isFr ? 'Aucune contestation de propriété enregistrée' : 'No ownership dispute recorded',
    ];

    $checks = collect();
    foreach ($flags as $key => $ok) {
        if (isset($flagLabels[$key])) $checks->push([$flagLabels[$key], (bool) $ok]);
    }
    if ($coa) $checks->push([$isFr ? 'Certificat d\'authenticité émis' : 'Certificate of authenticity issued', true]);
    $checks->push([$isFr ? 'Empreinte du contenu concordante' : 'Content hash matches the register', $hashValid]);
    if (filled($transfer->ca_signature)) {
        $checks->push([$isFr ? 'Signature de l\'autorité valide' : 'Certification authority signature valid', $sigValid]);
    }
    $checks->push([$isFr ? 'Certificat actif' : 'Certificate active', $transfer->status === 'active']);

    /* ────────────────────── Export, customs and insurance ───────────────
       Two whole panels that exist only when a shipper or an insurer actually
       filled them in. An empty "Export information" card on a certificate that
       crosses a border invites a customs officer to read blanks as clearances. */
    $exportRows = collect([
        [$isFr ? 'Permis d\'exportation' : 'Export permit no.', $transfer->export_permit_no],
        [$isFr ? 'Référence CITES' : 'CITES reference', $transfer->cites_reference],
        [$isFr ? 'Pays d\'exportation' : 'Country of export', $transfer->country_of_export],
        [$isFr ? 'Pays de destination' : 'Country of destination', $transfer->country_of_destination],
        [$isFr ? 'Référence douanière' : 'Customs reference', $transfer->customs_reference],
        [$isFr ? 'Référence d\'expédition' : 'Shipping reference', $transfer->shipping_reference],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $insuranceRows = collect([
        [$isFr ? 'Assureur' : 'Insurance provider', $transfer->insurer_name],
        [$isFr ? 'Numéro de police' : 'Policy number', $transfer->insurance_policy_no],
        [$isFr ? 'Valeur assurée' : 'Insured value', $money($transfer->insurance_value, $transfer->insurance_currency)],
        [$isFr ? 'Début de couverture' : 'Coverage start', $transfer->coverage_start ? Carbon::parse($transfer->coverage_start)->format('Y-m-d') : null],
        [$isFr ? 'Fin de couverture' : 'Coverage end', $transfer->coverage_end ? Carbon::parse($transfer->coverage_end)->format('Y-m-d') : null],
    ])->filter(fn ($r) => filled($r[1]))->values();

    /* ─────────────────────── Integrity, honestly described ──────────────
       The artwork's "Security features" column advertises a holographic seal,
       an embossed seal, a watermark, ultraviolet ink, microtext, tamper-evident
       foil and an anti-copy pattern. Those are properties of a physical print
       run; this is a web page, and nothing about how it is served could make
       any of them true. Its "encrypted QR" is not encrypted and its blockchain
       does not exist. What replaces them is the list of things that genuinely
       protect this document — every one of which a reader can check without
       taking our word for it. */
    $integrity = collect([
        ['fingerprint', $isFr ? 'Empreinte SHA-256' : 'SHA-256 content hash',
            $isFr ? 'Calculée sur les faits certifiés' : 'Computed over the certified facts'],
        ['pen-tool', $isFr ? 'Signature Ed25519' : 'Ed25519 signature',
            $isFr ? 'Signée par l\'autorité de certification' : 'Signed by the certification authority'],
        ['key-round', $isFr ? 'Clé publique publiée' : 'Published public key',
            '/.well-known/jwks.json'],
        ['link-2', $isFr ? 'Journal chaîné' : 'Hash-linked event log',
            $isFr ? 'Chaque entrée référence l\'empreinte de la précédente' : 'Each entry carries the previous entry\'s hash'],
        ['qr-code', $isFr ? 'Vérification par QR' : 'QR verification',
            $isFr ? 'Renvoie au registre en ligne' : 'Resolves to the online register'],
        ['lock', $isFr ? 'Code de vérification' : 'Verification PIN',
            $isFr ? 'Connu du porteur du certificat' : 'Held by the certificate bearer'],
        ['book-open', $isFr ? 'Registre de propriété' : 'Ownership ledger',
            $isFr ? 'Chaîne continue, sans recouvrement' : 'One unbroken sequence, no overlap'],
        ['globe', $isFr ? 'Identifiants normalisés' : 'Standard identifiers',
            'ISO 8601 · 3166-1 · 4217 · UUID'],
    ]);

    $compliance = [
        $isFr ? 'ISO 8601 — dates et heures' : 'ISO 8601 date and time',
        $isFr ? 'ISO 3166-1 — codes pays' : 'ISO 3166-1 country codes',
        $isFr ? 'ISO 4217 — codes devise' : 'ISO 4217 currency codes',
        $isFr ? 'SHA-256 — empreinte du certificat' : 'SHA-256 certificate hash',
        $isFr ? 'Ed25519 (EdDSA) — signature' : 'Ed25519 (EdDSA) signature',
        $isFr ? 'UUID — identifiant du certificat' : 'UUID certificate identifier',
    ];

    $eventLabels = [
        'issued'      => $isFr ? 'Certificat émis'      : 'Certificate issued',
        'verified'    => $isFr ? 'Certificat vérifié'   : 'Certificate verified',
        'downloaded'  => $isFr ? 'Certificat téléchargé': 'Certificate downloaded',
        'superseded'  => $isFr ? 'Certificat remplacé'  : 'Certificate superseded',
        'revoked'     => $isFr ? 'Certificat révoqué'   : 'Certificate revoked',
        'reissued'    => $isFr ? 'Certificat réémis'    : 'Certificate reissued',
    ];

    $provenanceUrl = Str::after(route('products.show', ['slug' => $product->slug, 'lang' => $lang]), '://');
    $serial = Str::afterLast($transfer->certificate_no, '-');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat de transfert de propriété' : 'Ownership Transfer Certificate' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat de transfert de propriété' : 'Ownership Transfer Certificate' }} — {{ $transfer->certificate_no }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           Drawn at the artwork's own 1024px canvas and scaled as one unit by
           the script at the foot of the page. Every number below is a source
           pixel measured off the PNG (docs/OTC-DESIGN-SPEC.md). A title deed
           that reflowed into a different arrangement on a narrower screen
           would not be the same document, so it never reflows — it only gets
           smaller, and the site-wide mobile type floor is switched off here.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .coa-page {
            width: 1024px;
            transform-origin: top left;
            font-size: 12px; line-height: 1.3; color: #1D1B16;
            background: #0D1411; padding: 9px; border-radius: 18px;
        }
        .coa-page, .coa-page * { font-size: revert-layer; }

        /* Frame: a 22px ornamental gold band between the dark bezel and the
           cream sheet — measured at x 9→31 and 993→1015. */
        /* The ornamental band: interlocking gold rings between two hairlines,
           the artwork's frame reduced to the repeat that actually reads at
           22px. Painted across the whole frame and covered in the middle by
           the opaque sheet, so the four runs meet exactly at the corners. */
        .otc-frame {
            padding: 22px; position: relative; border-radius: 10px;
            background:
                repeating-radial-gradient(circle at 13px 11px, rgba(0,0,0,0) 0 4.6px, #C9942E 4.6px 5.8px, rgba(0,0,0,0) 5.8px 13px),
                repeating-radial-gradient(circle at 0 11px,   rgba(0,0,0,0) 0 4.6px, #B08428 4.6px 5.6px, rgba(0,0,0,0) 5.6px 13px),
                #FBF3E1;
            background-size: 26px 22px, 26px 22px, auto;
            box-shadow: inset 0 0 0 1.2px #A8802A;
        }
        .otc-sheet { width: 961px; position: relative; background: #FDF9EF;
                     box-shadow: 0 0 0 1.2px #C9942E; }

        /* The guilloché-style field the artwork prints the document over. It is
           decoration and is captioned nowhere: a line pattern on a screen
           deters no forger, and saying otherwise would be the same lie as the
           ultraviolet ink. */
        .otc-guilloche { position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:.5; z-index:0; }
        /* Everything except the decoration is lifted above it. The guilloché is
           excluded by name rather than by rule order: both selectors carry the
           same specificity, so without the :not() the later one would drag the
           ornament back into the flow and push the document off the sheet. */
        .otc-sheet > *:not(.otc-guilloche) { position: relative; z-index: 1; }

        /* Cards. Stroke and head band sampled off the PNG. */
        .cc      { background:#FFFDF7; border:1px solid #E3D3B0; border-radius:9px; }
        .cc-head { display:flex; align-items:center; gap:8px; height:27px; padding:0 10px;
                   background:linear-gradient(180deg,#F8F1DF,#F1E7CE); border-bottom:1px solid #E3D3B0;
                   border-radius:8px 8px 0 0; }
        .cc-head .ico { width:17px; height:17px; border-radius:4px; flex:none; color:#FCEFC6;
                        background:linear-gradient(160deg,#C9942E,#966C1C);
                        display:flex; align-items:center; justify-content:center; }
        .cc-head .ico svg { width:11px; height:11px; }
        .cc-head h2 { font-size:11px; font-weight:700; letter-spacing:.055em; text-transform:uppercase; color:#2B2415; }

        /* Label / value rows, ruled the way the artwork rules them: a hairline
           under every row, not a boxed table. */
        .kv     { display:grid; grid-template-columns:var(--kv,120px) 1fr; column-gap:10px;
                  padding:2px 0; border-bottom:1px solid #F1E9D7; align-items:baseline; }
        .kv:last-child { border-bottom:0; }
        .kv dt  { font-size:9px; font-weight:500; color:#57513F; }
        .kv dd  { font-size:9px; font-weight:600; color:#1D1B16; word-break:break-word; }
        .mono   { font-family:ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing:-.2px; }
        .tick   { display:flex; align-items:flex-start; gap:6px; padding:2.5px 0; font-size:9.5px; color:#26221A; }
        .tick svg { width:11px; height:11px; flex:none; margin-top:1px; }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa-fit { overflow:visible; }
            @page { size: A4 portrait; margin: 5mm; }
        }
    </style>
</head>
<body class="bg-[#EFEADF] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

{{-- Ornament sheet for this certificate. Inline SVG rather than raster: the
     document is scaled to whatever the viewport allows, and a bitmap border
     would soften at every size but one. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        {{-- Guilloché rosette: interfering ellipses, the way a banknote lathe
             draws them. Decorative only. --}}
        <g id="otcRosette" fill="none" stroke="#C9942E" stroke-width=".6">
            @for($i = 0; $i < 36; $i++)
            <ellipse cx="0" cy="0" rx="150" ry="52" transform="rotate({{ $i * 10 }})"/>
            @endfor
        </g>
        <linearGradient id="otcGoldRule" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#C9942E" stop-opacity="0"/>
            <stop offset="25%" stop-color="#C9942E"/>
            <stop offset="55%" stop-color="#F0D493"/>
            <stop offset="85%" stop-color="#C9942E"/>
            <stop offset="1" stop-color="#C9942E" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="otcSeal" x1="20%" y1="10%" x2="85%" y2="95%">
            <stop offset="0" stop-color="#F7E1A4"/><stop offset="45%" stop-color="#C9942E"/>
            <stop offset="100%" stop-color="#8A5F14"/>
        </linearGradient>
    </defs>
</svg>

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Transfert de propriété' : 'Ownership transfer' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.14)] rounded-[6px]">
    <article class="coa-page cert-band-host">
    {{-- The family's classification band, over the frame margin. --}}
    @include('pages.partials.certificate-band', ['code' => 'OTC'])
    <div class="otc-frame">
        {{-- The band itself is painted by .otc-frame and simply covered in the
             middle by the opaque sheet, which is what makes it meet cleanly at
             all four corners. Four separately positioned strips never do. --}}
    <div class="otc-sheet">

        {{-- Guilloché field, centred on the sheet like the artwork's watermark. --}}
        <svg class="otc-guilloche" viewBox="0 0 961 1470" preserveAspectRatio="none" aria-hidden="true">
            <g transform="translate(480,300)" opacity=".16"><use href="#otcRosette"/></g>
            <g transform="translate(480,1080) scale(.8)" opacity=".1"><use href="#otcRosette"/></g>
        </svg>

        {{-- ══ Spine ══
             The artwork sets the issuer's name vertically down the binding edge
             at x 31–61 / y 33–430. The classification band now occupies that
             edge and carries vertical type of its own, so the spine is gone
             rather than stacked behind it: two vertical strips a few pixels
             apart is not what the artwork draws, and the band is the one that
             tells the reader something they cannot get elsewhere on the sheet.
             The issuer's name is still set, in full, in the header below. --}}

        {{-- ══ Header ══ --}}
        <div style="padding:10px 109px 0;text-align:center;">
            {{-- The artwork's lockup is 403 × 59; this one carries the emblem at
                 a much taller aspect, so it is set to the height the artwork
                 allots the header rather than to its width. Matching the width
                 instead would have pushed the meta card 70px down the sheet. --}}
            <img src="{{ brand_asset('full') }}" alt="ArtisanHub237"
                 style="height:66px;width:auto;max-width:100%;margin:0 auto;display:block;">
            <p style="margin-top:2px;font-size:10.5px;font-weight:600;letter-spacing:.26em;color:#5C574B;">
                {{ $isFr ? 'RELIER L\'ARTISANAT AUTHENTIQUE' : 'CONNECTING AUTHENTIC CRAFTSMANSHIP' }}
            </p>

            {{-- The artwork sets this line in a condensed serif: 28px cap-height
                 across 684px. Playfair at that cap-height runs wider, so the
                 line is compressed to fit rather than shrunk — shrinking would
                 have cost a third of the cap-height with it. --}}
            <h1 style="margin-top:8px;font-family:'Playfair Display',Georgia,serif;font-size:41px;line-height:1;
                       font-weight:700;color:#181510;letter-spacing:.01em;display:inline-block;
                       transform:scaleX(.9);transform-origin:center;white-space:nowrap;">
                {{ $isFr ? 'CERTIFICAT DE TRANSFERT DE PROPRIÉTÉ' : 'OWNERSHIP TRANSFER CERTIFICATE' }}
            </h1>

            <div style="margin-top:6px;display:flex;align-items:center;justify-content:center;gap:12px;">
                <svg width="210" height="3" aria-hidden="true"><rect width="210" height="3" fill="url(#otcGoldRule)"/></svg>
                <span style="font-family:'Playfair Display',Georgia,serif;font-size:27px;font-weight:600;color:#181510;">(OTC)</span>
                <svg width="210" height="3" aria-hidden="true"><rect width="210" height="3" fill="url(#otcGoldRule)"/></svg>
            </div>

            <p style="margin:8px auto 0;max-width:640px;font-size:10px;font-weight:500;letter-spacing:.09em;
                      line-height:1.6;color:#4A4536;text-transform:uppercase;">
                {{ $isFr
                   ? 'Ce certificat enregistre le transfert de propriété de l\'œuvre identifiée ci-dessous au sein du registre de provenance ArtisanHub237.'
                   : 'This certificate records the transfer of ownership of the work identified below within the ArtisanHub237 provenance register.' }}
            </p>
        </div>

        {{-- The certification authority's own mark, top right. Green and gold:
             the artwork renders it iridescent, which would read as a claim
             about a foil that no screen carries. --}}
        <svg style="position:absolute;right:12px;top:34px;z-index:2;" width="72" height="86" viewBox="0 0 72 86" aria-hidden="true">
            <path d="M36 2 68 12v33c0 21-14 33-32 39C18 78 4 66 4 45V12z" fill="url(#otcSeal)" stroke="#8A5F14" stroke-width="1.6"/>
            <path d="M36 8 62 16v29c0 18-12 28-26 33-14-5-26-15-26-33V16z" fill="#0C3520" opacity=".92"/>
            <image href="{{ brand_asset('mark') }}" x="19" y="22" width="34" height="34" preserveAspectRatio="xMidYMid meet"/>
            <text x="36" y="18" text-anchor="middle" font-family="Poppins,sans-serif" font-size="7" font-weight="700" fill="#2B2415">ARTISANHUB237</text>
            <text x="36" y="68" text-anchor="middle" font-family="Poppins,sans-serif" font-size="7.5" font-weight="700" letter-spacing="1" fill="#E9CE8B">{{ $isFr ? 'CERTIFIÉ' : 'CERTIFIED' }}</text>
        </svg>

        {{-- ══ Meta card: x 140–887, y 282–477 in the artwork ══ --}}
        <div class="cc" style="margin:12px 74px 0 109px;padding:0;">

            {{-- Row 1: the four things a reader looks for first. --}}
            <div style="display:grid;grid-template-columns:1.35fr 1.15fr 1fr .75fr;border-bottom:1px solid #EFE5CE;">
                @foreach([
                    [$isFr ? 'NUMÉRO DE CERTIFICAT' : 'CERTIFICATE NUMBER', $transfer->certificate_no, '#B4141B'],
                    [$isFr ? 'UUID DU CERTIFICAT' : 'CERTIFICATE UUID', $transfer->uuid, null],
                    [$isFr ? 'DATE D\'ÉMISSION (UTC)' : 'ISSUE DATE (UTC)', $issued->clone()->utc()->format('Y-m-d\TH:i:s\Z'), null],
                ] as [$mLabel, $mValue, $mColour])
                <div style="padding:9px 14px;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $mLabel }}</p>
                    <p class="mono" style="margin-top:6px;font-size:11.5px;font-weight:700;line-height:1.35;word-break:break-all;{{ $mColour ? 'color:' . $mColour . ';' : '' }}">{{ $mValue }}</p>
                </div>
                @endforeach
                <div style="padding:11px 14px;text-align:center;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'STATUT' : 'STATUS' }}</p>
                    <p style="margin-top:5px;display:flex;align-items:center;justify-content:center;gap:5px;
                              font-size:16px;font-weight:700;color:{{ $statusMeta[1] }};">
                        <i data-lucide="{{ $statusMeta[2] }}" style="width:16px;height:16px;"></i>{{ $statusMeta[0] }}
                    </p>
                </div>
            </div>

            {{-- Rows 2 and 3: what the document is actually secured by, and the
                 QR that leads to the copy of record. The QR spans both rows —
                 the artwork gives it a panel of its own down the right edge of
                 the card, and confining it to one row is what made the sheet
                 grow past the measured height. --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 174px;grid-template-rows:auto auto;">
                <div style="padding:9px 14px;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'EMPREINTE DU CERTIFICAT (SHA-256)' : 'CERTIFICATE HASH (SHA-256)' }}</p>
                    <p class="mono" style="margin-top:6px;font-size:10px;line-height:1.5;word-break:break-all;">{{ $transfer->content_hash }}</p>
                </div>
                <div style="padding:9px 14px;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'SIGNATURE NUMÉRIQUE (AC)' : 'DIGITAL SIGNATURE (CA)' }}</p>
                    @if($transfer->ca_signature)
                    {{-- The artwork draws a handwritten flourish here. What is
                         printed instead is the signature itself: an Ed25519
                         detached signature anyone can check against the key at
                         /.well-known/jwks.json without asking us anything. --}}
                    <p class="mono" style="margin-top:6px;font-size:9px;line-height:1.5;word-break:break-all;color:#2B2415;">{{ $transfer->ca_signature }}</p>
                    <p style="margin-top:5px;font-size:8.5px;color:#5D5745;">EdDSA · Ed25519 · <span class="mono">kid {{ $transfer->ca_kid }}</span></p>
                    @endif
                    <p style="margin-top:4px;font-size:9px;color:#3F3A2C;">{{ $isFr ? 'Autorité de certification ArtisanHub237' : 'ArtisanHub237 Certification Authority' }}</p>
                </div>
                <div style="padding:9px 14px;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'URL DE VÉRIFICATION' : 'VERIFICATION URL' }}</p>
                    <p class="mono" style="margin-top:6px;font-size:9.5px;line-height:1.55;word-break:break-all;color:#164C28;">{{ $verifyShort }}</p>
                    <p style="margin-top:6px;font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'CODE DE VÉRIFICATION' : 'VERIFICATION PIN' }}</p>
                    <p class="mono" style="margin-top:3px;font-size:12px;font-weight:700;letter-spacing:.14em;">{{ $transfer->verification_pin }}</p>
                </div>
                <div style="grid-column:4;grid-row:1 / span 2;padding:9px 14px;border-left:1px solid #EFE5CE;text-align:center;">
                    <p style="font-size:9.5px;font-weight:500;letter-spacing:.07em;color:#5D5745;">{{ $isFr ? 'CODE QR' : 'QR CODE' }}</p>
                    <div id="otc-qr" style="margin:7px auto 0;width:118px;height:118px;"></div>
                    <p style="margin-top:6px;font-size:9px;font-weight:600;letter-spacing:.06em;color:#5D5745;">{{ $isFr ? 'SCANNER POUR VÉRIFIER' : 'SCAN TO VERIFY' }}</p>
                </div>

            {{-- The ledger number, given the weight the artwork gives it. It is
                 the only identifier on this sheet that outlives the sheet: one
                 product, one OLN, quoted by every transfer certificate ever
                 issued for it, so a museum holding a single certificate can pull
                 the rest of the chain from it. --}}
            <div style="grid-column:1 / span 3;padding:0 14px 13px;">
                <div style="display:inline-flex;align-items:center;gap:12px;padding:8px 22px;border-radius:8px;
                            background:linear-gradient(180deg,#123D24,#06200F);border:1.5px solid #C9942E;">
                    <span style="font-size:12px;font-weight:700;letter-spacing:.06em;color:#E9CE8B;text-transform:uppercase;">
                        {{ $isFr ? 'Numéro de registre de propriété (OLN)' : 'Ownership ledger number (OLN)' }}
                    </span>
                    <span class="mono" style="font-size:15px;font-weight:700;letter-spacing:.06em;color:#FFFDF7;">{{ $oln }}</span>
                </div>
            </div>
        </div>
        </div>

        {{-- ══ Body: left stack of three rows, right column beside it ══
             Left area x 31–762 (731 wide), right column x 771–992 (221). --}}
        <div style="display:grid;grid-template-columns:731px 222px;gap:8px;margin-top:30px;align-items:stretch;">

            <div style="display:flex;flex-direction:column;gap:8px;">

                {{-- Row 1 — product identity 469 · original creator 255 --}}
                <div style="display:grid;grid-template-columns:469px 255px;gap:7px;align-items:stretch;">
                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="box"></i></span>
                            <h2>{{ $isFr ? 'Identité du produit' : 'Product identity' }}</h2></div>
                        <div style="display:flex;gap:10px;padding:9px 10px 10px;">
                            <div style="width:132px;flex:none;">
                                <div style="height:200px;border:1px solid #E7DAC0;border-radius:6px;background:#F7F1E2;
                                            display:flex;align-items:center;justify-content:center;padding:5px;overflow:hidden;">
                                    @if($cover)
                                    <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    @else
                                    <i data-lucide="image-off" style="width:22px;height:22px;color:#A8A296;"></i>
                                    @endif
                                </div>
                                <p style="margin-top:6px;font-size:9px;font-weight:700;letter-spacing:.05em;color:#A8802A;text-transform:uppercase;">
                                    {{ $isFr ? 'Image principale' : 'Primary image' }}
                                </p>
                                <p style="margin-top:2px;font-size:8.5px;line-height:1.4;color:#5D5745;">
                                    {{ $isFr ? 'Déposée par l\'artisan sur ArtisanHub237' : 'Submitted by the artisan on ArtisanHub237' }}
                                </p>
                            </div>
                            <dl style="flex:1;min-width:0;--kv:104px;">
                                @foreach($productRows as [$pk, $pv, $pMono])
                                <div class="kv"><dt>{{ $pk }}</dt><dd class="{{ $pMono ? 'mono' : '' }}">{{ $pv }}</dd></div>
                                @endforeach
                            </dl>
                        </div>
                    </section>

                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="user-round"></i></span>
                            <h2>{{ $isFr ? 'Créateur d\'origine' : 'Original creator' }}</h2></div>
                        <dl style="padding:9px 10px 10px;--kv:96px;">
                            @foreach($creatorRows as [$ck, $cv, $cMono])
                            <div class="kv"><dt>{{ $ck }}</dt><dd class="{{ $cMono ? 'mono' : '' }}">{{ $cv }}</dd></div>
                            @endforeach
                            <div class="kv">
                                <dt>{{ $isFr ? 'Statut de vérification' : 'Verification status' }}</dt>
                                <dd style="display:flex;align-items:center;gap:5px;">
                                    @if($makerVerified)
                                    <i data-lucide="check-circle-2" style="width:11px;height:11px;color:#0F7A34;flex:none;"></i>
                                    {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}
                                    @else
                                    <i data-lucide="circle-dashed" style="width:11px;height:11px;color:#8A857A;flex:none;"></i>
                                    {{ $isFr ? 'Enregistré, non vérifié' : 'Registered, not verified' }}
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        {{-- The artwork closes this panel with the artisan's
                             handwritten signature and an inked fingerprint. The
                             platform holds neither, and drawing them would be a
                             forgery on a provenance document. The permanent
                             registry number above is what stands in their place
                             and is what a researcher can actually follow. --}}
                    </section>
                </div>

                {{-- Row 2 — previous owner 259 · new owner 242 · transfer 213 --}}
                <div style="display:grid;grid-template-columns:259px 242px 213px;gap:8px;align-items:stretch;">
                    @foreach([
                        ['users', $isFr ? 'Propriétaire précédent (cédant)' : 'Previous owner (transferor)', $fromRow,
                            $isFr ? 'Propriétaire depuis' : 'Ownership since'],
                        ['user-check', $isFr ? 'Nouveau propriétaire (cessionnaire)' : 'New owner (transferee)', $toRow,
                            $isFr ? 'Propriétaire depuis' : 'Ownership begins'],
                    ] as [$oIcon, $oTitle, $oRow, $oSince])
                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="{{ $oIcon }}"></i></span><h2>{{ $oTitle }}</h2></div>
                        <dl style="padding:9px 10px 10px;--kv:100px;">
                            @if($oRow)
                                @foreach($ownerRows($oRow) as [$rk, $rv, $rMono])
                                <div class="kv"><dt>{{ $rk }}</dt><dd class="{{ $rMono ? 'mono' : '' }}">{{ $rv }}</dd></div>
                                @endforeach
                                <div class="kv">
                                    <dt>{{ $isFr ? 'Niveau de vérification' : 'Verification level' }}</dt>
                                    <dd style="display:flex;align-items:center;gap:5px;">
                                        <i data-lucide="{{ in_array($oRow->verification_level, ['verified', 'institution'], true) ? 'check-circle-2' : 'circle-dashed' }}"
                                           style="width:11px;height:11px;flex:none;color:{{ in_array($oRow->verification_level, ['verified', 'institution'], true) ? '#0F7A34' : '#8A857A' }};"></i>
                                        {{ $levelLabels[$oRow->verification_level] ?? $oRow->verification_level }}
                                    </dd>
                                </div>
                                @if($oRow->owned_from)
                                <div class="kv"><dt>{{ $oSince }}</dt><dd class="mono">{{ Carbon::parse($oRow->owned_from)->format('Y-m-d') }}</dd></div>
                                @endif
                                @if($oRow->owned_until)
                                <div class="kv"><dt>{{ $isFr ? 'Propriétaire jusqu\'au' : 'Ownership until' }}</dt><dd class="mono">{{ Carbon::parse($oRow->owned_until)->format('Y-m-d') }}</dd></div>
                                @endif
                            @else
                                {{-- A first registration has no transferor. The
                                     panel says so rather than inventing one. --}}
                                <p style="padding:6px 0;font-size:9.5px;color:#6B6659;">
                                    {{ $isFr ? 'Aucun propriétaire antérieur : premier enregistrement au registre.' : 'No prior holder: first entry on the register.' }}
                                </p>
                            @endif
                        </dl>
                    </section>
                    @endforeach

                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="arrow-left-right"></i></span>
                            <h2>{{ $isFr ? 'Détails du transfert' : 'Transfer details' }}</h2></div>
                        <dl style="padding:9px 10px 10px;--kv:92px;">
                            @foreach($transferRows as [$tk, $tv, $tMono])
                            <div class="kv"><dt>{{ $tk }}</dt><dd class="{{ $tMono ? 'mono' : '' }}">{{ $tv }}</dd></div>
                            @endforeach
                            @if($transfer->value_is_private)
                            {{-- Saying that a value exists and is withheld is
                                 more honest than a silent omission: the reader
                                 knows there is a figure and knows why they do
                                 not have it. --}}
                            <div class="kv"><dt>{{ $isFr ? 'Valeur déclarée' : 'Declared value' }}</dt>
                                <dd style="color:#6B6659;font-weight:500;">{{ $isFr ? 'Confidentielle, à la demande des parties' : 'Withheld at the parties\' request' }}</dd></div>
                            @endif
                        </dl>
                    </section>
                </div>

                {{-- Row 3 — chain 366 · condition 169 · checks 182 --}}
                <div style="display:grid;grid-template-columns:366px 169px 182px;gap:7px;align-items:stretch;">

                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="git-commit-horizontal"></i></span>
                            <h2>{{ $isFr ? 'Chaîne de propriété (provenance)' : 'Chain of ownership (provenance)' }}</h2></div>
                        <div style="padding:12px 10px 9px;">
                            {{-- The sequence, drawn as the artwork draws it: one
                                 node per holder, in the order the register holds
                                 them, with the arrow that makes the direction of
                                 title unambiguous. --}}
                            <div style="display:flex;align-items:flex-start;justify-content:center;gap:2px;">
                                @foreach($chain as $ci => $node)
                                @if($ci)
                                <i data-lucide="arrow-right" style="width:14px;height:14px;color:#A8802A;flex:none;margin-top:22px;"></i>
                                @endif
                                <div style="width:{{ max(62, floor(330 / max(count($chain), 1)) - 16) }}px;text-align:center;">
                                    <span style="width:50px;height:50px;margin:0 auto;border-radius:50%;display:flex;
                                                 align-items:center;justify-content:center;
                                                 background:{{ $node->owned_until ? '#F1E7CE' : 'linear-gradient(160deg,#12472A,#062615)' }};
                                                 border:1.5px solid {{ $node->owned_until ? '#D9C49A' : '#C9942E' }};
                                                 color:{{ $node->owned_until ? '#7A6428' : '#E9CE8B' }};">
                                        <i data-lucide="{{ $entityIcons[$node->entity_type] ?? 'user-round' }}" style="width:24px;height:24px;"></i>
                                    </span>
                                    <p style="margin-top:6px;font-size:8.5px;font-weight:700;letter-spacing:.03em;color:#2B2415;text-transform:uppercase;line-height:1.25;">
                                        {{ $node->sequence }}. {{ $node->is_original_creator ? ($isFr ? 'Artisan d\'origine' : 'Original artisan') : ($entityLabels[$node->entity_type] ?? $node->entity_type) }}
                                    </p>
                                    <p style="margin-top:3px;font-size:9px;font-weight:600;color:#1D1B16;line-height:1.3;">{{ $node->legal_name }}</p>
                                    <p style="margin-top:2px;font-size:8.5px;color:#5D5745;line-height:1.4;">
                                        {{ $isFr ? 'Depuis' : 'Since' }} {{ Carbon::parse($node->owned_from)->format('Y-m-d') }}<br>
                                        @if($node->country_code){{ $node->country_code }}@endif
                                        @if(! $node->owned_until)<br><b style="color:#0F7A34;">{{ $isFr ? 'Détenteur actuel' : 'Current holder' }}</b>@endif
                                    </p>
                                </div>
                                @endforeach
                            </div>
                            <div style="margin-top:10px;padding-top:8px;border-top:1px solid #EFE5CE;display:flex;gap:8px;align-items:baseline;">
                                <span style="font-size:8.5px;font-weight:700;letter-spacing:.05em;color:#5D5745;text-transform:uppercase;flex:none;">
                                    {{ $isFr ? 'Historique complet' : 'Full provenance history' }}
                                </span>
                                <span class="mono" style="font-size:8.5px;color:#164C28;word-break:break-all;">{{ $provenanceUrl }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="clipboard-check"></i></span>
                            <h2>{{ $isFr ? 'État au transfert' : 'Condition at transfer' }}</h2></div>
                        <dl style="padding:9px 10px 10px;--kv:74px;">
                            @forelse($conditionRows as [$dk, $dv])
                            <div class="kv"><dt>{{ $dk }}</dt><dd style="color:#186D33;">{{ $dv }}</dd></div>
                            @empty
                            <p style="padding:6px 0;font-size:9.5px;color:#6B6659;">
                                {{ $isFr ? 'Aucun constat d\'état enregistré pour ce transfert.' : 'No condition report was recorded for this transfer.' }}
                            </p>
                            @endforelse
                        </dl>
                    </section>

                    <section class="cc">
                        <div class="cc-head"><span class="ico"><i data-lucide="shield-check"></i></span>
                            <h2>{{ $isFr ? 'Contrôles' : 'Authentication checks' }}</h2></div>
                        <div style="padding:8px 10px 10px;">
                            @foreach($checks as [$label, $ok])
                            <div class="tick">
                                <i data-lucide="{{ $ok ? 'check-circle-2' : 'alert-circle' }}" style="color:{{ $ok ? '#0F7A34' : '#B4141B' }};"></i>
                                <span>{{ $label }}</span>
                            </div>
                            @endforeach
                            {{-- What a clear result means, in the only wording
                                 that is true: nobody told this register
                                 anything. It is a useful negative and not a
                                 clearance, and the two must never be read as
                                 the same statement. --}}
                            <p style="margin-top:7px;padding-top:7px;border-top:1px solid #F1E9D7;font-size:8px;line-height:1.45;color:#6B6659;">
                                {{ $isFr
                                   ? 'Ces contrôles portent sur ce que ce registre contient. Une ligne au vert signifie qu\'aucun signalement n\'a été reçu, non qu\'une enquête a été menée.'
                                   : 'These checks describe what this register holds. A green line means nothing has been reported to it, not that an investigation was carried out.' }}
                            </p>
                        </div>
                    </section>
                </div>
            </div>

            {{-- ══ Right column: integrity · verification result · export ══ --}}
            <div style="display:flex;flex-direction:column;gap:8px;height:100%;">

                <section class="cc">
                    <div class="cc-head"><span class="ico"><i data-lucide="shield"></i></span>
                        <h2>{{ $isFr ? 'Intégrité du certificat' : 'Certificate integrity' }}</h2></div>
                    <div style="padding:4px 10px 8px;">
                        @foreach($integrity as [$sIcon, $sTitle, $sNote])
                        <div style="display:flex;gap:8px;padding:4px 0;{{ $loop->first ? '' : 'border-top:1px solid #F1E9D7;' }}">
                            <span style="width:26px;height:26px;flex:none;border-radius:50%;border:1px solid #DFCBA2;
                                         background:#F8F1DF;color:#8A5F14;display:flex;align-items:center;justify-content:center;">
                                <i data-lucide="{{ $sIcon }}" style="width:14px;height:14px;"></i>
                            </span>
                            <div style="min-width:0;">
                                <p style="font-size:9.5px;font-weight:700;letter-spacing:.02em;color:#2B2415;">{{ $sTitle }}</p>
                                <p style="margin-top:1px;font-size:8.5px;line-height:1.35;color:#5D5745;word-break:break-word;">{{ $sNote }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="cc" style="border-color:#C9942E;flex:1;">
                    <div style="height:22px;display:flex;align-items:center;justify-content:center;border-radius:8px 8px 0 0;
                                background:linear-gradient(180deg,#123D24,#06200F);">
                        <span style="font-size:9.5px;font-weight:700;letter-spacing:.13em;color:#E9CE8B;">
                            {{ $isFr ? 'RÉSULTAT DE VÉRIFICATION' : 'VERIFICATION RESULT' }}
                        </span>
                    </div>
                    <div style="padding:9px 10px 10px;">
                        <p style="display:flex;align-items:center;gap:7px;font-size:15px;font-weight:700;color:{{ $statusMeta[1] }};">
                            <i data-lucide="{{ $statusMeta[2] }}" style="width:18px;height:18px;"></i>
                            {{ $transfer->status === 'active'
                               ? ($isFr ? 'CERTIFICAT VALIDE' : 'VALID CERTIFICATE')
                               : $statusMeta[0] }}
                        </p>
                        <dl style="margin-top:8px;--kv:104px;">
                            <div class="kv"><dt>{{ $isFr ? 'Détenteur actuel' : 'Current holder' }}</dt><dd>{{ $current?->legal_name }}</dd></div>
                            @if($current?->owned_from)
                            <div class="kv"><dt>{{ $isFr ? 'Propriétaire depuis' : 'Ownership since' }}</dt><dd class="mono">{{ Carbon::parse($current->owned_from)->format('Y-m-d') }}</dd></div>
                            @endif
                            <div class="kv"><dt>{{ $isFr ? 'Détenteurs au registre' : 'Holders on record' }}</dt><dd class="mono">{{ count($chain) }}</dd></div>
                            <div class="kv"><dt>{{ $isFr ? 'Empreinte du certificat' : 'Certificate hash' }}</dt>
                                <dd style="color:{{ $hashValid ? '#0F7A34' : '#B4141B' }};">{{ $hashValid ? ($isFr ? 'Concordante' : 'Matches') : ($isFr ? 'Non concordante' : 'Does not match') }}</dd></div>
                            @if(filled($transfer->ca_signature))
                            <div class="kv"><dt>{{ $isFr ? 'Signature de l\'autorité' : 'Authority signature' }}</dt>
                                <dd style="color:{{ $sigValid ? '#0F7A34' : '#B4141B' }};">{{ $sigValid ? ($isFr ? 'Valide' : 'Valid') : ($isFr ? 'Invalide' : 'Invalid') }}</dd></div>
                            @endif
                            @if($transfer->last_verified_at)
                            <div class="kv"><dt>{{ $isFr ? 'Dernière vérification' : 'Last verified' }}</dt><dd class="mono">{{ Carbon::parse($transfer->last_verified_at)->utc()->format('Y-m-d H:i') }} UTC</dd></div>
                            @endif
                            <div class="kv"><dt>{{ $isFr ? 'Vérifications' : 'Verification count' }}</dt><dd class="mono">{{ (int) $transfer->verification_count }}</dd></div>
                        </dl>
                        {{-- The artwork claims this document lives on a
                             blockchain. It does not; there is no chain of blocks
                             anywhere in this platform. There *is* a hash-linked
                             event log where each entry carries its predecessor's
                             digest, which gives the property the artwork was
                             reaching for — an edit cannot be made without
                             breaking every entry after it — so that is what the
                             sentence says. --}}
                        <p style="margin-top:8px;font-size:8px;line-height:1.45;color:#5D5745;text-align:center;">
                            {{ $isFr
                               ? 'Ce certificat est signé par l\'autorité de certification ArtisanHub237 et consigné dans un journal chaîné par empreintes : une entrée modifiée invalide toutes les suivantes. L\'exemplaire de référence est celui du registre en ligne.'
                               : 'This certificate is signed by the ArtisanHub237 certification authority and recorded in a hash-linked event log: altering one entry invalidates every entry after it. The authoritative copy is the one held by the online register.' }}
                        </p>
                    </div>
                </section>

                @if($exportRows->isNotEmpty())
                <section class="cc">
                    <div class="cc-head"><span class="ico"><i data-lucide="ship"></i></span>
                        <h2>{{ $isFr ? 'Informations d\'exportation' : 'Export information' }}</h2></div>
                    <dl style="padding:9px 10px 10px;--kv:100px;">
                        @foreach($exportRows as [$ek, $ev])
                        <div class="kv"><dt>{{ $ek }}</dt><dd class="mono">{{ $ev }}</dd></div>
                        @endforeach
                    </dl>
                    {{-- No "export ready: yes" line. Whether a piece may lawfully
                         leave the country turns on CITES schedules and cultural
                         heritage law that this platform holds no register of,
                         so a green clearance printed here would be invented. --}}
                    <p style="padding:0 10px 9px;font-size:8px;line-height:1.45;color:#6B6659;">
                        {{ $isFr
                           ? 'Références déclarées par les parties. ArtisanHub237 ne délivre aucune autorisation d\'exportation et ne se prononce pas sur la légalité d\'une sortie du territoire.'
                           : 'References as declared by the parties. ArtisanHub237 issues no export clearance and takes no position on whether the work may lawfully leave the country.' }}
                    </p>
                </section>
                @endif
            </div>
        </div>

        {{-- ══ Insurance · audit trail · compliance ══ --}}
        <div style="display:grid;grid-template-columns:{{ $insuranceRows->isNotEmpty() ? '210px 1fr 230px' : '1fr 230px' }};gap:7px;margin-top:8px;align-items:stretch;">

            @if($insuranceRows->isNotEmpty())
            <section class="cc">
                <div class="cc-head"><span class="ico"><i data-lucide="umbrella"></i></span>
                    <h2>{{ $isFr ? 'Assurance' : 'Insurance' }}</h2></div>
                <dl style="padding:9px 10px 10px;--kv:92px;">
                    @foreach($insuranceRows as [$ik, $iv])
                    <div class="kv"><dt>{{ $ik }}</dt><dd>{{ $iv }}</dd></div>
                    @endforeach
                </dl>
            </section>
            @endif

            <section class="cc">
                <div class="cc-head"><span class="ico"><i data-lucide="list-checks"></i></span>
                    <h2>{{ $isFr ? 'Journal du certificat (chaîné)' : 'Audit trail (hash-linked)' }}</h2></div>
                <div style="padding:9px 12px 10px;">
                    {{-- Every line is a stored certificate_events row. The
                         artwork shows five neat lifecycle steps; a register
                         prints the steps that happened, however few. --}}
                    @forelse($trail as $ti => $event)
                    <div style="display:grid;grid-template-columns:16px 1fr 130px 1fr;gap:8px;align-items:baseline;
                                padding:4px 0;{{ $ti ? 'border-top:1px solid #F1E9D7;' : '' }}">
                        <i data-lucide="circle-check-big" style="width:12px;height:12px;color:#0F7A34;"></i>
                        <span style="font-size:9.5px;font-weight:600;color:#2B2415;">{{ $eventLabels[$event->event] ?? $event->event }}</span>
                        <span class="mono" style="font-size:9px;color:#5D5745;">{{ Carbon::parse($event->occurred_at)->utc()->format('Y-m-d H:i:s') }} UTC</span>
                        <span class="mono" style="font-size:8px;color:#8A8264;word-break:break-all;">{{ Str::limit($event->entry_hash, 32, '…') }}</span>
                    </div>
                    @empty
                    <p style="font-size:9.5px;color:#6B6659;">{{ $isFr ? 'Aucun événement consigné.' : 'No events recorded.' }}</p>
                    @endforelse
                    <p style="margin-top:8px;padding-top:7px;border-top:1px solid #F1E9D7;font-size:8px;line-height:1.45;color:#6B6659;">
                        {{ $isFr
                           ? 'La dernière colonne est l\'empreinte de l\'entrée ; chacune inclut celle de la précédente, ce qui rend toute suppression détectable.'
                           : 'The last column is the entry digest; each one includes its predecessor\'s, which is what makes a deletion detectable.' }}
                    </p>
                    {{-- The artwork's "Digital evidence — linked records" panel
                         listed six documents behind View links. None of those
                         records exist to link to, and six dead links on a
                         provenance certificate imply six files a buyer could ask
                         for. The panel is gone and its width given to the log. --}}
                </div>
            </section>

            <section class="cc">
                <div class="cc-head"><span class="ico"><i data-lucide="globe"></i></span>
                    <h2>{{ $isFr ? 'Normes appliquées' : 'Standards applied' }}</h2></div>
                <div style="padding:8px 10px 10px;">
                    @foreach($compliance as $line)
                    <div class="tick"><i data-lucide="check-circle-2" style="color:#0F7A34;"></i><span>{{ $line }}</span></div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ══ Parties and authority ══ --}}
        <div style="display:grid;grid-template-columns:670px 283px;gap:8px;margin-top:8px;align-items:stretch;">

            <section class="cc">
                <div class="cc-head"><span class="ico"><i data-lucide="scroll-text"></i></span>
                    <h2>{{ $isFr ? 'Parties au transfert' : 'Parties to the transfer' }}</h2></div>
                <div style="padding:11px 14px 13px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                    {{-- The artwork gives each party a handwritten signature and
                         a date. The register holds no signatures — it holds who
                         the parties were, the reference each is filed under and
                         when the transfer was entered. That is what is printed,
                         because a rendered scrawl beside a real name is exactly
                         the thing a provenance document exists to prevent. --}}
                    @foreach(array_filter([
                        [$isFr ? 'Propriétaire précédent (cédant)' : 'Previous owner (transferor)', $fromRow?->legal_name, $fromRow?->owner_ref],
                        [$isFr ? 'Nouveau propriétaire (cessionnaire)' : 'New owner (transferee)', $toRow?->legal_name, $toRow?->owner_ref],
                        [$isFr ? 'Artisan (créateur)' : 'Artisan (creator)', $maker?->name ?: $biz?->name_fr, $gan],
                    ], fn ($p) => filled($p[1])) as [$pTitle, $pName, $pRef])
                    <div>
                        <p style="font-size:9px;font-weight:700;letter-spacing:.04em;color:#5D5745;text-transform:uppercase;line-height:1.3;">{{ $pTitle }}</p>
                        <p style="margin-top:7px;font-size:11px;font-weight:600;color:#1D1B16;">{{ $pName }}</p>
                        <p class="mono" style="margin-top:3px;font-size:9px;color:#5D5745;">{{ $pRef }}</p>
                        <div style="margin-top:10px;height:1px;background:#DFCBA2;"></div>
                        <p style="margin-top:5px;font-size:8.5px;color:#6B6659;">
                            {{ $isFr ? 'Inscrit au registre le' : 'Entered on the register' }}
                            <span class="mono">{{ $moved->format('Y-m-d') }}</span>
                        </p>
                    </div>
                    @endforeach
                </div>
            </section>

            <section class="cc" style="text-align:center;padding:12px 14px 13px;">
                <p style="font-family:'Playfair Display',Georgia,serif;font-size:16px;color:#181510;">
                    ArtisanHub237 <span style="color:#A8802A;">{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}</span>
                </p>
                <svg width="180" height="52" viewBox="0 0 180 52" style="margin:8px auto 0;display:block;" aria-hidden="true">
                    <circle cx="26" cy="26" r="24" fill="url(#otcSeal)" stroke="#8A5F14" stroke-width="1.4"/>
                    <circle cx="26" cy="26" r="18" fill="#0C3520"/>
                    <image href="{{ brand_asset('mark') }}" x="14" y="14" width="24" height="24" preserveAspectRatio="xMidYMid meet"/>
                    <rect x="58" y="6" width="118" height="3" fill="url(#otcGoldRule)"/>
                    <rect x="58" y="43" width="118" height="3" fill="url(#otcGoldRule)"/>
                    <text x="117" y="21" text-anchor="middle" font-family="Poppins,sans-serif" font-size="8" font-weight="700" letter-spacing=".8" fill="#3F3A2C">{{ $isFr ? 'PROPRIÉTÉ VÉRIFIÉE' : 'OWNERSHIP VERIFIED' }}</text>
                    <text x="117" y="34" text-anchor="middle" font-family="Poppins,sans-serif" font-size="8" font-weight="700" letter-spacing=".8" fill="#3F3A2C">{{ $isFr ? 'PROVENANCE CONSERVÉE' : 'PROVENANCE PRESERVED' }}</text>
                </svg>
                <p style="margin-top:9px;font-size:8.5px;line-height:1.5;color:#5D5745;text-align:left;">
                    {{ $isFr
                       ? 'ArtisanHub237 est une entreprise privée. Elle n\'est ni un registre public ni une autorité de titre : elle horodate et signe ce que des parties identifiées déclarent, et conserve la trace de chaque modification. Elle n\'est pas partie à la transaction et n\'encaisse aucun paiement.'
                       : 'ArtisanHub237 is a private company. It is neither a public register nor a title authority: it timestamps and signs what identified parties declare, and keeps a trace of every change. It is not a party to the transaction and collects no payments.' }}
                </p>
                <p class="mono" style="margin-top:7px;font-size:8.5px;color:#164C28;">www.artisanhub237.com</p>
            </section>
        </div>

        {{-- ══ Foot: serial · statement · reference ══ --}}
        <div style="display:flex;align-items:center;gap:14px;margin-top:10px;">
            <span class="mono" style="width:96px;flex:none;font-size:13px;font-weight:700;color:#B4141B;letter-spacing:.06em;">{{ $serial }}</span>
            <div style="flex:1;height:24px;border-radius:6px;border:1px solid #C9942E;
                        background:linear-gradient(180deg,#123D24,#06200F);display:flex;align-items:center;justify-content:center;">
                {{-- The artwork's line reads "legally recognized when verified
                     online". A private register cannot confer legal recognition
                     on anything, so the sentence says what is actually true: the
                     copy that counts is the online one. --}}
                <span style="font-size:10.5px;font-weight:700;letter-spacing:.07em;color:#E9CE8B;text-transform:uppercase;">
                    {{ $isFr
                       ? 'Ce certificat est un document numérique — l\'exemplaire de référence est celui vérifié en ligne'
                       : 'This certificate is a digital record — the authoritative copy is the one verified online' }}
                </span>
            </div>
            <span class="mono" style="width:186px;flex:none;text-align:right;font-size:8.5px;color:#3F3A2C;word-break:break-all;">{{ $transfer->certificate_no }}</span>
        </div>

        <p style="margin-top:9px;padding-bottom:10px;text-align:center;font-size:8.5px;font-weight:600;
                  letter-spacing:.08em;color:#8A5F14;text-transform:uppercase;">
            {{ $isFr
               ? 'Toute altération de ce document le rend sans valeur — vérifiez le registre en ligne avant de vous y fier'
               : 'Any alteration voids this document — check the online register before relying on it' }}
        </p>
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
        <a href="{{ $verifyUrl }}" class="ui-btn ui-btn-secondary">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            {{ $isFr ? 'Vérifier ce certificat' : 'Verify this certificate' }}
        </a>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
            {{ $isFr ? 'Retour au produit' : 'Back to the product' }}
        </a>
    </div>
</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    lucide.createIcons();

    (function () {
        var box = document.getElementById('otc-qr');
        if (box && window.QRCode) {
            new QRCode(box, {
                text: @json($verifyShort),
                width: 118, height: 118,
                colorDark: '#12331F', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* The sheet is drawn at the artwork's own 1024px width and scaled to fit,
       so the layout never rearranges — it only gets smaller. The wrapper has to
       be given the scaled height by hand, because a transform does not change
       the space an element reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.coa-page');
        if (!fit || !page) return;

        /* Below this the document is present but unreadable — 9.5px body copy
           lands near 4px on a narrow phone. Rather than reflow a title deed
           into a different arrangement, it is held at a legible scale and the
           frame becomes pannable, which is how anyone reads a certificate on a
           phone anyway. */
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
