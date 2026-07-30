@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $isFr = $lang === 'fr';

    // Consumed by the shared directory header and footer partials.
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $biz     = $product->business;
    $owner   = $biz?->user;
    $name    = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $desc    = $isFr ? $product->description_fr : ($product->description_en ?: $product->description_fr);
    $bizName = $biz?->name_fr;

    $images = $product->images->sortBy('sort_order')->values();
    $cover  = $images->firstWhere('is_cover', true) ?? $images->first();
    $thumbs = $images->reject(fn ($i) => $cover && $i->id === $cover->id)->take(4);

    $issued    = Carbon::parse($certificate->issued_at);
    $verifyUrl = route('product.certificate.verify', ['ref' => $certificate->certificate_no, 'lang' => $lang]);
    // Short enough to read off a printed sheet and type by hand.
    $verifyShort = route('product.certificate.verify.short', ['ref' => $certificate->certificate_no]);

    /* Every row below comes from a stored value. A field the artisan never
       filled in is dropped rather than printed empty: a blank "Dimensions" line
       on a document like this reads as a measured fact, not a missing one.
       That is why the artwork's "Collection" and "Edition" rows are absent —
       the platform stores neither. */
    $attrRows = $product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->map(fn ($a) => [
            $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
            trim(($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)) . ' ' . ($a->unit ?? '')),
        ]);

    $productRows = collect([
        [$isFr ? 'Nom du produit' : 'Product name', $name],
        [$isFr ? 'Catégorie' : 'Category', $product->category ? ($isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr)) : null],
        [$isFr ? 'Description' : 'Description', $desc ? Str::limit($desc, 190) : null],
        [$isFr ? 'Pays d\'origine' : 'Country of origin', $biz ? ($isFr ? 'Cameroun' : 'Cameroon') : null],
        [$isFr ? 'Région' : 'Region', $biz?->region?->name_fr],
        [$isFr ? 'Année de création' : 'Year created', $product->created_at?->format('Y')],
    ])->concat($attrRows)->filter(fn ($r) => filled($r[1]))->values();

    $artisanRef = $biz ? ($biz->certificate_no ?: certNumberFor($biz->id, $biz->created_at)) : null;
    $artisanId  = $biz ? 'AHC-ART-' . str_pad((string) $biz->id, 6, '0', STR_PAD_LEFT) : null;
    $productRef = 'AHC-PRD-' . $issued->format('Y') . '-' . str_pad((string) $product->id, 8, '0', STR_PAD_LEFT);
    $verified   = in_array($biz?->verification_tier, ['verified', 'certified'], true);
    $location   = collect([$biz?->city?->name_fr, $biz?->region?->name_fr, $isFr ? 'Cameroun' : 'Cameroon'])->filter()->unique()->implode(', ');
    $profileUrl = $biz?->slug ? Str::after(route('businesses.show', ['slug' => $biz->slug]), '://') : null;

    $creatorRows = collect([
        [$isFr ? 'ID artisan' : 'Artisan ID', $artisanId, true],
        [$isFr ? 'Nom de l\'artisan' : 'Artisan name', $owner?->name, false],
        [$isFr ? 'Atelier' : 'Workshop', $bizName, false],
        [$isFr ? 'Référence' : 'Reference', $artisanRef, true],
        [$isFr ? 'Profil' : 'Profile ID', $profileUrl, false],
        [$isFr ? 'Localisation' : 'Location', $location, false],
    ])->filter(fn ($r) => filled($r[1]))->values();

    /* The artwork's Digital Identity block lists an AI visual fingerprint and a
       watermark reference. Neither is implemented — no model, no watermarking —
       and a labelled row on a certificate is read as a measure that was taken.
       They are omitted; what remains is computed for real at issue and
       recomputed on every verification. */
    $identityRows = collect([
        [$isFr ? 'Empreinte du contenu' : 'Content fingerprint', Str::upper(substr($certificate->content_hash, 0, 20))],
        [$isFr ? 'Empreinte de l\'image' : 'Perceptual image hash', $certificate->image_phash ? Str::upper($certificate->image_phash) : null],
        [$isFr ? 'Signature' : 'Digital signature', $certificate->signature ? Str::upper(substr($certificate->signature, 0, 20)) : null],
        [$isFr ? 'Code de vérification' : 'Verification PIN', $certificate->verification_pin],
    ])->filter(fn ($r) => filled($r[1]))->values();

    $statusMeta = [
        'valid'      => [$isFr ? 'VALIDE' : 'VALID', 'shield-check'],
        'superseded' => [$isFr ? 'REMPLACÉ' : 'SUPERSEDED', 'alert-triangle'],
        'revoked'    => [$isFr ? 'RÉVOQUÉ' : 'REVOKED', 'shield-off'],
    ][$status] ?? [$isFr ? 'INCONNU' : 'UNKNOWN', 'help-circle'];

    $onSale = $product->status === 'published' && $product->is_available;

    /* The artwork lists nine features. Eight are things the platform does; the
       ninth, "AI Image Fingerprint", is not, and its slot is taken by the
       perceptual hash — the real arithmetic that sits in its place. */
    $features = [
        ['badge-check', $isFr ? ['ARTISAN', 'VÉRIFIÉ'] : ['VERIFIED', 'ARTISAN']],
        ['package',     $isFr ? ['PRODUIT', 'ENREGISTRÉ'] : ['REGISTERED', 'PRODUCT']],
        ['barcode',     $isFr ? ['NUMÉRO DE SÉRIE', 'UNIQUE'] : ['UNIQUE', 'SERIAL NUMBER']],
        ['book-open',   $isFr ? ['PASSEPORT', 'NUMÉRIQUE'] : ['DIGITAL', 'PASSPORT']],
        ['fingerprint', $isFr ? ['EMPREINTE', 'D\'IMAGE'] : ['IMAGE', 'FINGERPRINT']],
        ['qr-code',     $isFr ? ['VÉRIFICATION', 'QR'] : ['QR', 'VERIFICATION']],
        ['users',       $isFr ? ['HISTORIQUE', 'DE PROPRIÉTÉ'] : ['OWNERSHIP', 'HISTORY']],
        ['pen-tool',    $isFr ? ['SIGNATURE', 'SÉCURISÉE'] : ['SECURE DIGITAL', 'SIGNATURE']],
        ['lock',        $isFr ? ['ENREGISTREMENT', 'PERMANENT'] : ['PERMANENT', 'REGISTRATION']],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat d\'authenticité' : 'Certificate of Authenticity' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat d\'authenticité' : 'Certificate of Authenticity' }} — {{ $name }}</title>

    @include('pages.partials.icons')
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* ────────────────────────────────────────────────────────────────
           The certificate is drawn at exactly the artwork's 1024×1536 canvas
           and then scaled as a single unit. Every measurement below is in
           source pixels taken off the PNG (docs/COA-DESIGN-SPEC.md), so what
           the viewer sees at any width is the same document, smaller — not a
           different one reflowed. That is the only way the proportions hold.
           The site-wide mobile type floor is therefore switched off here.
           ──────────────────────────────────────────────────────────────── */
        .coa-fit { overflow: hidden; }
        .coa-fit.coa-pannable { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .coa-hint { display: none; }
        .coa-fit.coa-pannable + .coa-hint { display: flex; }
        .coa-page {
            width: 1024px;
            transform-origin: top left;
            font-size: 13px;
            line-height: 1.35;
            color: #1D1B16;
            background: #023415;
            padding: 16px;
        }
        .coa-page, .coa-page * { font-size: revert-layer; }

        .coa-gold  { background: linear-gradient(135deg,#C9942E,#F4D98C 28%,#C9942E 55%,#E8C065 78%,#A6761F); padding: 5px; }
        .coa-cream { background: #FEFDF7; position: relative; padding: 0 18px; overflow: hidden; }

        /* The four corner motifs. One symbol, mirrored into place — the artwork
           leaves the long edges plain, so there is no repeating strip. */
        .coa-corner { position:absolute; width:200px; height:116px; pointer-events:none; z-index:0; }
        /* Everything else sits above the corner motifs. */
        .coa-cream > *:not(.coa-corner) { position:relative; z-index:1; }
        .coa-corner.tl { top:0; left:0; }
        .coa-corner.tr { top:0; right:0; transform:scaleX(-1); }
        .coa-corner.bl { bottom:0; left:0; transform:scaleY(-1); }
        .coa-corner.br { bottom:0; right:0; transform:scale(-1,-1); }

        /* Cards */
        .cc      { background:#fff; border:1px solid #E8E0CB; border-radius:10px; }
        .cc-head { display:flex; align-items:center; gap:8px; height:33px; padding:0 12px;
                   background:linear-gradient(180deg,#F5F1E5,#EDE7D6); border-bottom:1px solid #E2D9BF;
                   border-radius:9px 9px 0 0; }
        .cc-head .ico { width:19px; height:19px; border-radius:5px; background:#0A3A22;
                        display:flex; align-items:center; justify-content:center; color:#F4D98C; flex:none; }
        .cc-head .ico svg { width:12px; height:12px; }
        .cc-head h2 { font-size:12px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:#0A3A22; }

        /* Label / value rows */
        .kv       { display:grid; grid-template-columns:var(--kv,120px) 1fr; column-gap:8px; padding:2.5px 0; align-items:baseline; }
        .kv dt    { font-size:9px; font-weight:700; letter-spacing:.045em; text-transform:uppercase; color:#6B6659; }
        .kv dd    { font-size:11px; font-weight:500; color:#1D1B16; }
        .mono     { font-family:ui-monospace,'SFMono-Regular',Consolas,monospace; letter-spacing:-.2px; }

        .coa-ribbon { position:relative; display:inline-block; height:35px; line-height:35px; padding:0 30px;
                      background:linear-gradient(180deg,#F0CD70,#DDA93C); color:#33240A;
                      font-size:14.5px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        .coa-ribbon::before, .coa-ribbon::after { content:''; position:absolute; top:0; width:14px; height:35px; background:inherit; }
        .coa-ribbon::before { left:-13px;  clip-path:polygon(100% 0,100% 100%,0 50%); }
        .coa-ribbon::after  { right:-13px; clip-path:polygon(0 0,0 100%,100% 50%); }

        .coa-foot { background:linear-gradient(180deg,#0E4A2B,#062D19); height:60px; margin:0 -18px;
                    border-top:3px solid #C9942E; border-bottom:3px solid #C9942E;
                    display:flex; flex-direction:column; align-items:center; justify-content:center;
                    gap:5px; position:relative; overflow:hidden; }
        .coa-soc  { width:15px; height:15px; border-radius:50%; border:1.2px solid #EBD9A6;
                    display:flex; align-items:center; justify-content:center; color:#EBD9A6; }
        .coa-soc svg { width:8px; height:8px; }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa-fit { overflow:visible; }
            @page { size: A4 portrait; margin: 6mm; }
        }
    </style>
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#EFEADF] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>
@include('pages.partials.coa-ornaments')

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28]">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-[#164C28] truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat' : 'Certificate' }}</span>
    </nav>

    <div class="coa-fit shadow-[0_4px_28px_rgba(0,0,0,0.12)] rounded-[4px]">
    <article class="coa-page cert-band-host">
    {{-- The family's classification band, over the frame margin. It is placed
         on the page rather than inside the cream sheet so it can sit across the
         bezel and the gutter without touching the measured column at x 45. --}}
    @include('pages.partials.certificate-band', ['code' => 'COA'])
    <div class="coa-gold">
    <div class="coa-cream">

        {{-- ══ Corner ornament ══ --}}
        @foreach(['tl', 'tr', 'bl', 'br'] as $corner)
        <svg class="coa-corner {{ $corner }}" viewBox="0 0 200 116" aria-hidden="true"><use href="#coaCorner"/></svg>
        @endforeach

        {{-- ══ Header: lockup + title + ribbon, QR + status alongside ══ --}}
        <div class="flex gap-3" style="padding-top:15px;position:relative;">
            <div class="flex-1 min-w-0 text-center">
                <img src="{{ brand_asset('full') }}" alt="Artisan Hub 237"
                     style="width:492px;max-width:100%;height:auto;margin:0 auto;display:block;">
                <p style="margin-top:-1px;font-size:11px;font-weight:600;letter-spacing:.215em;color:#5C574B;">
                    {{ $isFr ? 'AUTHENTIQUE. CERTIFIÉ. CAMEROUNAIS.' : 'AUTHENTIC. CERTIFIED. CAMEROONIAN.' }}
                </p>

                {{-- The artwork sets this line in a condensed serif: cap-height
                     32px across 606px. Playfair at the same cap-height runs 832px
                     wide, so it is compressed to fit rather than shrunk, which
                     would have lost a third of the cap-height with it. --}}
                <h1 style="margin-top:10px;font-family:'Playfair Display',Georgia,serif;font-size:46px;line-height:1;
                           font-weight:800;color:#0A3A22;letter-spacing:.005em;
                           display:inline-block;transform:scaleX(.728);transform-origin:center;">
                    {{ $isFr ? "CERTIFICAT D'AUTHENTICITÉ" : 'CERTIFICATE OF AUTHENTICITY' }}
                </h1>

                <p style="margin-top:6px;">
                    <span class="coa-ribbon">{{ $isFr ? 'ÉMIS AUTOMATIQUEMENT PAR ARTISANHUB237' : 'ISSUED AUTOMATICALLY BY ARTISANHUB237' }}</span>
                </p>

                <p style="margin:4px auto 0;max-width:672px;font-size:14px;line-height:1.5;color:#3F3C34;">
                    {{ $isFr
                       ? 'Ce certificat confirme que le produit décrit ci-dessous a été enregistré et vérifié par la plateforme ArtisanHub237. Il en constitue un enregistrement numérique permanent : origine et créateur.'
                       : 'This certificate confirms that the product described below has been registered and verified by the ArtisanHub237 platform. It provides a permanent digital record of the product\'s origin and creator.' }}
                </p>
            </div>

            <div style="width:139px;flex:none;padding-top:34px;">
                <div class="cc" style="padding:8px 6px;text-align:center;">
                    <p style="font-size:9.5px;font-weight:800;letter-spacing:.055em;color:#0A3A22;">
                        {{ $isFr ? 'VÉRIFIER LE CERTIFICAT' : 'VERIFY CERTIFICATE' }}
                    </p>
                    <div id="coa-qr" style="margin:7px auto 0;width:110px;height:110px;"></div>
                    <p style="margin-top:7px;font-size:9px;font-weight:600;letter-spacing:.05em;color:#6B6659;">
                        {{ $isFr ? 'SCANNER LE CODE QR' : 'SCAN QR CODE' }}
                    </p>
                </div>
                <div style="margin-top:4px;height:78px;border-radius:10px;color:#fff;text-align:center;
                            background:linear-gradient(180deg,#0F5130,#04220F);display:flex;flex-direction:column;
                            align-items:center;justify-content:center;gap:3px;">
                    <p style="font-size:8.5px;font-weight:600;letter-spacing:.07em;opacity:.82;">
                        {{ $isFr ? 'STATUT DU CERTIFICAT' : 'CERTIFICATE STATUS' }}
                    </p>
                    <p style="display:flex;align-items:center;gap:6px;font-size:21px;font-weight:800;letter-spacing:.02em;">
                        <i data-lucide="{{ $statusMeta[1] }}" style="width:19px;height:19px;"></i>{{ $statusMeta[0] }}
                    </p>
                </div>
            </div>
        </div>

        @if($status === 'superseded')
        <div class="ui-alert ui-alert-warn" style="margin-top:12px;font-size:11px;">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <span>{{ $isFr
                ? 'La fiche produit a été modifiée depuis l\'émission de ce certificat. Les informations ci-dessous restent celles enregistrées à la date d\'émission.'
                : 'The product record has been edited since this certificate was issued. The details below remain those recorded on the issue date.' }}</span>
        </div>
        @endif

        {{-- ══ Meta strip ══ --}}
        <div class="cc" style="margin-top:2px;padding:11px 22px;">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);column-gap:26px;row-gap:12px;">
                @foreach([
                    ['file-badge', $isFr ? 'NUMÉRO DE CERTIFICAT' : 'CERTIFICATE NUMBER', $certificate->certificate_no, true],
                    ['calendar',   $isFr ? 'DATE D\'ÉMISSION' : 'ISSUE DATE', $issued->format('d / m / Y'), false],
                    ['link',       $isFr ? 'URL DE VÉRIFICATION' : 'VERIFICATION URL', $verifyShort, true],
                    ['box',        $isFr ? 'IDENTIFIANT PRODUIT' : 'PRODUCT ID', $productRef, true],
                    ['clock',      $isFr ? 'HEURE D\'ÉMISSION (UTC)' : 'ISSUE TIME (UTC)', $issued->clone()->utc()->format('H:i:s'), false],
                    ['history',    $isFr ? 'VERSION DU CERTIFICAT' : 'CERTIFICATE VERSION', number_format((float) $certificate->version, 1), false],
                ] as [$mIcon, $mLabel, $mValue, $mMono])
                <div style="display:flex;gap:9px;min-width:0;">
                    <span style="width:20px;height:20px;flex:none;border-radius:5px;background:#0A3A22;color:#F4D98C;
                                 display:flex;align-items:center;justify-content:center;margin-top:1px;">
                        <i data-lucide="{{ $mIcon }}" style="width:12px;height:12px;"></i>
                    </span>
                    <div style="min-width:0;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.055em;color:#6B6659;">{{ $mLabel }}</p>
                        <p class="{{ $mMono ? 'mono' : '' }}" style="margin-top:2px;font-size:13px;font-weight:500;word-break:break-word;line-height:1.25;">{{ $mValue }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ Body: product · photograph · creator + identity ══ --}}
        <div style="display:grid;grid-template-columns:333px 262px 321px;gap:9px;margin-top:15px;align-items:start;">

            <section class="cc" style="min-height:399px;">
                <div class="cc-head">
                    <span class="ico"><i data-lucide="clipboard-list"></i></span>
                    <h2>{{ $isFr ? 'Informations produit' : 'Product information' }}</h2>
                </div>
                <dl style="padding:10px 12px 12px;--kv:112px;">
                    @foreach($productRows as [$pk, $pv])
                    <div class="kv"><dt>{{ $pk }}</dt><dd>{{ $pv }}</dd></div>
                    @endforeach
                </dl>
            </section>

            <section style="display:flex;flex-direction:column;gap:8px;">
                <div class="cc" style="height:305px;display:flex;align-items:center;justify-content:center;padding:10px;">
                    @if($cover)
                    <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}"
                         style="max-width:100%;max-height:100%;object-fit:contain;">
                    @else
                    <div style="text-align:center;color:#A8A296;">
                        <i data-lucide="image-off" style="width:26px;height:26px;"></i>
                        <p style="margin-top:6px;font-size:10px;">{{ $isFr ? 'Aucune photographie enregistrée' : 'No photograph on record' }}</p>
                    </div>
                    @endif
                </div>
                @if($thumbs->isNotEmpty())
                <div style="display:grid;grid-template-columns:repeat({{ $thumbs->count() }},1fr);gap:7px;">
                    @foreach($thumbs as $t)
                    <div class="cc" style="height:84px;display:flex;align-items:center;justify-content:center;padding:5px;">
                        <img src="{{ asset('storage/' . $t->file_path) }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                    </div>
                    @endforeach
                </div>
                @endif
            </section>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <section class="cc">
                    <div class="cc-head">
                        <span class="ico"><i data-lucide="user-round"></i></span>
                        <h2>{{ $isFr ? 'Créateur (artisan d\'origine)' : 'Creator (original artisan)' }}</h2>
                    </div>
                    <dl style="padding:10px 12px 11px;--kv:118px;">
                        @foreach($creatorRows as [$ck, $cv, $cMono])
                        <div class="kv"><dt>{{ $ck }}</dt><dd class="{{ $cMono ? 'mono' : '' }}" style="word-break:break-word;">{{ $cv }}</dd></div>
                        @endforeach
                        <div class="kv">
                            <dt>{{ $isFr ? 'Vérification' : 'Verification status' }}</dt>
                            <dd style="display:flex;align-items:center;gap:5px;">
                                @if($verified)
                                    <i data-lucide="check-circle-2" style="width:13px;height:13px;color:#0C7A3E;flex:none;"></i>
                                    <b>{{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</b>
                                @else
                                    <i data-lucide="circle-dashed" style="width:13px;height:13px;color:#8A857A;flex:none;"></i>
                                    {{ $isFr ? 'Enregistré, non vérifié' : 'Registered, not verified' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="cc">
                    <div class="cc-head">
                        <span class="ico"><i data-lucide="fingerprint"></i></span>
                        <h2>{{ $isFr ? 'Identité numérique' : 'Digital identity' }}</h2>
                    </div>
                    <dl style="padding:10px 12px 11px;--kv:118px;">
                        @foreach($identityRows as [$ik, $iv])
                        <div class="kv"><dt>{{ $ik }}</dt><dd class="mono" style="word-break:break-all;">{{ $iv }}</dd></div>
                        @endforeach
                    </dl>
                </section>
            </div>
        </div>

        {{-- ══ Ownership ══ --}}
        <div style="display:grid;grid-template-columns:298px 398px 220px;gap:9px;margin-top:12px;align-items:start;">

            <section class="cc" style="min-height:128px;">
                <div class="cc-head">
                    <span class="ico"><i data-lucide="users"></i></span>
                    <h2>{{ $isFr ? 'Propriété' : 'Ownership information' }}</h2>
                </div>
                <dl style="padding:10px 12px;--kv:118px;">
                    <div class="kv"><dt>{{ $isFr ? 'Détenteur actuel' : 'Current owner' }}</dt><dd>{{ $owner?->name ?: $bizName }}</dd></div>
                    <div class="kv"><dt>{{ $isFr ? 'Statut' : 'Ownership status' }}</dt><dd>{{ $isFr ? 'Artisan d\'origine' : 'Original artisan' }}</dd></div>
                    <div class="kv"><dt>{{ $isFr ? 'Propriétaire depuis' : 'Ownership since' }}</dt><dd>{{ $product->created_at?->format('d / m / Y') }}</dd></div>
                </dl>
            </section>

            <section class="cc" style="min-height:128px;">
                <div class="cc-head">
                    <span class="ico"><i data-lucide="history"></i></span>
                    <h2>{{ $isFr ? 'Historique de propriété' : 'Ownership history' }}</h2>
                </div>
                <div style="padding:9px 12px 10px;">
                    <table style="width:100%;border-collapse:collapse;font-size:10.5px;">
                        <thead>
                            <tr style="font-size:8.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6B6659;text-align:left;">
                                <th style="padding-bottom:5px;">{{ $isFr ? 'Date' : 'Date' }}</th>
                                <th style="padding-bottom:5px;">{{ $isFr ? 'De' : 'From' }}</th>
                                <th style="padding-bottom:5px;">{{ $isFr ? 'À' : 'To' }}</th>
                                <th style="padding-bottom:5px;">{{ $isFr ? 'Événement' : 'Event' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-top:1px solid #EFE9DA;">
                                <td style="padding:6px 6px 6px 0;white-space:nowrap;">{{ $product->created_at?->format('d / m / Y') }}</td>
                                <td style="padding:6px 6px 6px 0;color:#8A857A;">—</td>
                                <td style="padding:6px 6px 6px 0;">{{ $owner?->name ?: $bizName }}</td>
                                <td style="padding:6px 0;">{{ $isFr ? 'Premier enregistrement' : 'First registration' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- The platform records no transfers because it has no transfer
                         feature. Saying so is the honest form of the artwork's
                         "No further transfers recorded". --}}
                    <p style="margin-top:7px;text-align:center;font-size:10px;color:#8A857A;">
                        {{ $isFr ? 'Aucun transfert de propriété enregistré.' : 'No ownership transfers recorded.' }}
                    </p>
                </div>
            </section>

            <section class="cc" style="min-height:128px;">
                <div class="cc-head">
                    <span class="ico"><i data-lucide="check-circle-2"></i></span>
                    <h2>{{ $isFr ? 'Statut' : 'Product status' }}</h2>
                </div>
                <div style="padding:16px 12px;text-align:center;">
                    <span style="display:inline-block;padding:7px 22px;border-radius:7px;color:#fff;font-size:14px;
                                 font-weight:800;letter-spacing:.06em;
                                 background:{{ $onSale ? 'linear-gradient(180deg,#12833F,#0A5C2E)' : 'linear-gradient(180deg,#8A857A,#6B6659)' }};">
                        {{ $onSale ? ($isFr ? 'ACTIF' : 'ACTIVE') : ($isFr ? 'INACTIF' : 'INACTIVE') }}
                    </span>
                    <p style="margin-top:9px;font-size:10.5px;color:#6B6659;">
                        {{ $onSale ? ($isFr ? 'Disponible à la vente' : 'Available for sale')
                                   : ($isFr ? 'Non disponible actuellement' : 'Not currently available') }}
                    </p>
                </div>
            </section>
        </div>

        {{-- ══ Authenticity features ══ --}}
        <section class="cc" style="margin-top:16px;padding:13px 20px 15px;">
            <div style="display:flex;align-items:center;gap:14px;justify-content:center;">
                <svg width="180" height="3" aria-hidden="true"><rect width="180" height="3" fill="url(#coaGoldRule)"/></svg>
                <h2 style="font-size:14px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#0A3A22;white-space:nowrap;">
                    {{ $isFr ? 'Éléments d\'authenticité' : 'Authenticity features' }}
                </h2>
                <svg width="180" height="3" aria-hidden="true"><rect width="180" height="3" fill="url(#coaGoldRule)"/></svg>
            </div>
            <div style="display:grid;grid-template-columns:repeat(9,1fr);gap:6px;margin-top:14px;">
                @foreach($features as $fi => [$fIcon, $fLines])
                <div style="text-align:center;{{ $fi ? 'border-left:1px solid #F0EAD9;' : '' }}">
                    <i data-lucide="{{ $fIcon }}" style="width:28px;height:28px;color:#0A3A22;stroke-width:1.6;"></i>
                    <p style="margin-top:7px;font-size:8.5px;font-weight:800;letter-spacing:.02em;color:#3F3C34;line-height:1.28;">
                        {{ $fLines[0] }}<br>{{ $fLines[1] }}
                    </p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ══ Seal · statement · disclaimer ══ --}}
        <div style="display:grid;grid-template-columns:223px 397px 297px;gap:16px;margin-top:16px;align-items:stretch;">

            <div style="display:flex;align-items:center;justify-content:center;">
                <svg width="182" height="208" viewBox="0 0 194 220" aria-hidden="true">
                    {{-- Tricolour ribbons behind the seal --}}
                    <path d="M62 150l-14 62 22-13 16 11 8-56z" fill="#0F7A34"/>
                    <path d="M132 150l14 62-22-13-16 11-8-56z" fill="#B4141B"/>
                    <path d="M84 158h26l6 58-19-12-19 12z" fill="#E5A82E"/>
                    <use href="#coaSealTeeth"/>
                    <circle cx="97" cy="97" r="90" fill="url(#coaSealFace)"/>
                    <circle cx="97" cy="97" r="82" fill="none" stroke="#FBEEC6" stroke-width="2" opacity=".7"/>
                    <circle cx="97" cy="97" r="57" fill="#FDFBF3"/>
                    <circle cx="97" cy="97" r="57" fill="none" stroke="#9C6E1B" stroke-width="2.5" opacity=".6"/>
                    {{-- Rim lettering rides the arcs defined in the ornament sheet:
                         the name over the top, the origin under the bottom. --}}
                    <text font-family="Poppins,sans-serif" font-size="14" font-weight="800" fill="#4A360B" letter-spacing="3.4">
                        <textPath href="#coaSealTop" startOffset="50%" text-anchor="middle">ARTISANHUB237</textPath>
                    </text>
                    <text font-family="Poppins,sans-serif" font-size="10" font-weight="700" fill="#4A360B" letter-spacing="2.4">
                        <textPath href="#coaSealBot" startOffset="50%" text-anchor="middle">{{ $isFr ? 'CERTIFIÉ · CAMEROUN' : 'CERTIFIED · CAMEROON' }}</textPath>
                    </text>
                    <image href="{{ brand_asset('mark') }}" x="55" y="55" width="84" height="84" preserveAspectRatio="xMidYMid meet"/>
                </svg>
            </div>

            <section class="cc" style="padding:12px 18px 13px;">
                <h2 style="text-align:center;font-size:11.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#0A3A22;">
                    {{ $isFr ? 'Déclaration d\'authenticité' : 'Authenticity statement' }}
                </h2>
                <p style="margin-top:9px;font-size:10px;line-height:1.5;color:#3F3C34;">
                    {{ $isFr
                       ? 'ArtisanHub237 atteste que ce produit a été enregistré sur la plateforme par son créateur d\'origine ou par un représentant autorisé. À la date d\'émission, les informations contenues dans ce certificat correspondaient à l\'enregistrement officiel conservé par la plateforme.'
                       : 'ArtisanHub237 certifies that this product has been registered on the platform by its original creator or an authorized representative. At the time of issuance, the information contained in this certificate matched the official registration record maintained by the platform.' }}
                </p>
                <p style="margin-top:7px;font-size:10px;line-height:1.5;color:#3F3C34;">
                    {{ $isFr
                       ? 'Ce certificat établit un enregistrement numérique vérifiable de l\'identité et de la provenance déclarée du produit. Il ne prouve pas qu\'un objet physique donné est celui figurant sur les photographies : les acheteurs doivent toujours confirmer son statut actuel à l\'aide du code QR ou de la page de vérification.'
                       : 'This certificate establishes a verifiable digital record of the product\'s declared identity and provenance. It does not prove that a given physical object is the one shown in the photographs: buyers should always confirm its current status using the QR code or verification page.' }}
                </p>
                <p style="margin-top:11px;text-align:center;font-family:'Playfair Display',Georgia,serif;font-style:italic;font-size:19px;color:#0A3A22;">
                    ArtisanHub237 Authority
                </p>
                <div style="margin:4px auto 0;width:186px;height:1px;background:#C4BCA6;"></div>
                <p style="margin-top:6px;text-align:center;font-size:9.5px;color:#6B6659;">
                    {{ $isFr ? 'Signé numériquement par l\'autorité de certification ArtisanHub237' : 'Digitally Signed by ArtisanHub237 Certification Authority' }}
                </p>
            </section>

            <section class="cc" style="padding:12px 15px 13px;">
                <h2 style="text-align:center;font-size:11.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#0A3A22;">
                    {{ $isFr ? 'Avertissement' : 'Disclaimer' }}
                </h2>
                <div style="margin-top:8px;font-size:9px;line-height:1.48;color:#5A554A;display:flex;flex-direction:column;gap:6px;">
                    <p>{{ $isFr
                        ? 'Ce certificat est généré automatiquement par la plateforme ArtisanHub237 après enregistrement et vérification du produit.'
                        : 'This certificate is generated automatically by the ArtisanHub237 platform upon successful product registration and verification.' }}</p>
                    <p>{{ $isFr
                        ? 'Il enregistre l\'identité du produit, son authenticité et sa provenance au sein de l\'écosystème ArtisanHub237.'
                        : 'It records product identity, authenticity, and provenance within the ArtisanHub237 ecosystem.' }}</p>
                    <p>{{ $isFr
                        ? 'L\'enregistrement sur ArtisanHub237 ne remplace pas le droit d\'auteur, une marque, un dessin ou modèle industriel ni aucun autre droit de propriété intellectuelle applicable. Les utilisateurs restent responsables de l\'obtention de toute protection légale supplémentaire prévue par la loi applicable.'
                        : 'Registration on ArtisanHub237 does not replace applicable copyright, trademark, industrial design, or other intellectual property rights. Users remain responsible for obtaining any additional legal protections available under applicable law.' }}</p>
                    <p>{{ $isFr
                        ? 'ArtisanHub237 est une entreprise privée. La plateforme n\'est pas partie aux transactions entre acheteur et artisan et n\'en reçoit pas le prix ; seuls ses propres frais de service lui sont réglés.'
                        : 'ArtisanHub237 is a private company. The platform is not a party to transactions between buyer and artisan and does not receive the price; only its own service fees are paid to it.' }}</p>
                </div>
            </section>
        </div>

        {{-- ══ Footer band ══ --}}
        <div style="height:9px;"></div>
        <footer class="coa-foot">
            {{-- Kente lattice worked in gold at both ends of the band, and the
                 Cameroon silhouette at the right, as in the artwork. --}}
            <svg style="position:absolute;left:0;top:0;" width="186" height="66" aria-hidden="true">
                <rect width="186" height="66" fill="url(#coaKenteDark)"/>
            </svg>
            <svg style="position:absolute;right:186px;top:0;transform:scaleX(-1);" width="150" height="66" aria-hidden="true">
                <rect width="150" height="66" fill="url(#coaKenteDark)"/>
            </svg>
            <svg style="position:absolute;right:26px;top:1px;" width="56" height="58" viewBox="0 0 60 68" aria-hidden="true">
                <use href="#coaCameroon" fill="#0F7A34"/>
                <path d="M0 0h20v68H0z" fill="#0F7A34" clip-path="url(#coaCamClip)"/>
                <clipPath id="coaCamClip"><use href="#coaCameroon"/></clipPath>
                <g clip-path="url(#coaCamClip)">
                    <rect x="0" y="0" width="20" height="68" fill="#0F7A34"/>
                    <rect x="20" y="0" width="20" height="68" fill="#C8102E"/>
                    <rect x="40" y="0" width="20" height="68" fill="#E5A82E"/>
                </g>
                <path d="M30 28l2.6 7.6h8l-6.5 4.8 2.5 7.7-6.6-4.8-6.6 4.8 2.5-7.7-6.5-4.8h8z" fill="#F7DC7A"/>
            </svg>

            <p style="font-family:'Playfair Display',Georgia,serif;font-style:italic;font-size:23px;
                      color:#F0D79A;line-height:1.1;position:relative;">
                {{ $isFr ? 'Chaque produit a une histoire. Chaque histoire a une preuve.' : 'Every Product Has a Story. Every Story Has Proof.' }}
            </p>
            <p style="display:flex;align-items:center;gap:11px;font-size:11px;letter-spacing:.045em;color:#E7EFE8;position:relative;">
                {{-- Drawn inline: this build of lucide carries no brand marks,
                     and empty circles in the footer looked like a failed load. --}}
                <span style="display:flex;gap:5px;">
                    <span class="coa-soc"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5h1.65V3.6c-.3 0-1.3-.1-2.45-.1-2.4 0-4.05 1.5-4.05 4.2v2.2H7.5V13h2.7v8z"/></svg></span>
                    <span class="coa-soc"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 5.9c-.7.3-1.4.5-2.2.6.8-.5 1.4-1.2 1.7-2.1-.75.45-1.55.75-2.4.9A3.75 3.75 0 0 0 11.7 8.6c-3.1-.15-5.85-1.65-7.7-3.9-.32.55-.5 1.2-.5 1.9 0 1.3.66 2.45 1.67 3.12-.62-.02-1.2-.19-1.7-.47v.05c0 1.82 1.29 3.33 3 3.68-.31.08-.64.13-.98.13-.24 0-.47-.02-.7-.07.48 1.49 1.86 2.57 3.5 2.6A7.53 7.53 0 0 1 3 17.2 10.6 10.6 0 0 0 8.75 18.9c6.9 0 10.68-5.72 10.68-10.68v-.49c.73-.53 1.37-1.19 1.87-1.94z"/></svg></span>
                    <span class="coa-soc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><circle cx="17.2" cy="6.9" r="1.15" fill="currentColor" stroke="none"/></svg></span>
                </span>
                <span style="font-weight:600;">WWW.ARTISANHUB237.COM</span>
                <span style="opacity:.88;">&#64;artisanhub237</span>
            </p>
        </footer>
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
        var box = document.getElementById('coa-qr');
        if (box && window.QRCode) {
            new QRCode(box, {
                text: @json($verifyShort),
                width: 110, height: 110,
                colorDark: '#0A3A22', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();

    /* The sheet is drawn at the artwork's own 1024px width and scaled to fit,
       so the layout never rearranges — it only gets smaller. The wrapper is
       given the scaled height by hand, because a transform does not affect the
       space an element reserves. */
    (function () {
        var fit  = document.querySelector('.coa-fit');
        var page = document.querySelector('.coa-page');
        if (!fit || !page) return;

        /* Below this scale the document is present but unreadable — 11px body
           copy lands near 4px on a narrow phone. Rather than reflow the sheet
           into a different document, it is held at a legible scale and the
           frame becomes pannable, which is how people read a certificate on a
           phone anyway. */
        var MIN_SCALE = 0.62;

        function apply() {
            var w = fit.getBoundingClientRect().width;
            var k = Math.max(MIN_SCALE, Math.min(1, w / 1024));

            page.style.transform = k < 1 ? 'scale(' + k + ')' : '';
            fit.style.height = (page.offsetHeight * k) + 'px';

            // Only pan when the sheet genuinely cannot fit.
            var scaled = 1024 * k;
            fit.style.overflowX = scaled > w + 1 ? 'auto' : 'hidden';
            fit.classList.toggle('coa-pannable', scaled > w + 1);
        }

        apply();
        new ResizeObserver(apply).observe(fit);
        window.addEventListener('load', apply);
        // Images and the QR canvas land after first paint and change the height.
        document.querySelectorAll('.coa-page img').forEach(function (img) {
            if (!img.complete) img.addEventListener('load', apply);
        });
        window.addEventListener('beforeprint', apply);
    })();
</script>
</body>
</html>
