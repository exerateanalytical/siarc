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

    $images = $product->images->sortByDesc('is_cover')->sortBy('sort_order', SORT_REGULAR)->values();
    $cover  = $product->images->firstWhere('is_cover', true) ?? $product->images->sortBy('sort_order')->first();
    $thumbs = $images->reject(fn ($i) => $cover && $i->id === $cover->id)->take(4);

    $issued    = Carbon::parse($certificate->issued_at);
    $verifyUrl = route('product.certificate.verify', ['ref' => $certificate->certificate_no, 'lang' => $lang]);

    /* ── Product information ──────────────────────────────────────────────
       Every row below comes from a stored value. A field the artisan never
       filled in is dropped from the certificate rather than printed empty:
       a blank "Dimensions" line on a document like this reads as a measured
       fact, not a missing one. That is why the design's "Collection" and
       "Edition" rows are absent — the platform stores neither.             */
    $attr = $product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->mapWithKeys(fn ($a) => [
            Str::lower($a->template->field_key ?: $a->template->name_en ?: $a->template->name_fr) => [
                $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
                trim(($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)) . ' ' . ($a->unit ?? '')),
            ],
        ]);

    $productRows = collect([
        [$isFr ? 'Nom du produit' : 'Product name', $name],
        [$isFr ? 'Catégorie' : 'Category', $product->category ? ($isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr)) : null],
        [$isFr ? 'Description' : 'Description', $desc ? Str::limit($desc, 230) : null],
        [$isFr ? 'Pays d\'origine' : 'Country of origin', $biz ? ($isFr ? 'Cameroun' : 'Cameroon') : null],
        [$isFr ? 'Région' : 'Region', $biz?->region?->name_fr],
        [$isFr ? 'Année de création' : 'Year created', $product->created_at?->format('Y')],
    ])->concat($attr->values())
      ->filter(fn ($r) => filled($r[1]))
      ->values();

    $artisanRef  = $biz ? ($biz->certificate_no ?: certNumberFor($biz->id, $biz->created_at)) : null;
    $artisanId   = $biz ? 'AHC-ART-' . str_pad((string) $biz->id, 6, '0', STR_PAD_LEFT) : null;
    $productRef  = 'AHC-PRD-' . $issued->format('Y') . '-' . str_pad((string) $product->id, 8, '0', STR_PAD_LEFT);
    $verified    = in_array($biz?->verification_tier, ['verified', 'certified'], true);
    $location    = collect([$biz?->city?->name_fr, $biz?->region?->name_fr, $isFr ? 'Cameroun' : 'Cameroon'])->filter()->unique()->implode(', ');

    $statusMeta = [
        'valid'      => [$isFr ? 'VALIDE' : 'VALID', '#0C7A3E', 'shield-check'],
        'superseded' => [$isFr ? 'REMPLACÉ' : 'SUPERSEDED', '#B8791A', 'alert-triangle'],
        'revoked'    => [$isFr ? 'RÉVOQUÉ' : 'REVOKED', '#B02020', 'shield-off'],
    ][$status] ?? [$isFr ? 'INCONNU' : 'UNKNOWN', '#6F6B60', 'help-circle'];

    $onSale = $product->status === 'published' && $product->is_available;

    /* ── Authenticity features ────────────────────────────────────────────
       The design lists nine. Eight are things the platform does. The ninth,
       "AI Image Fingerprint", is not: there is no model and no comparison
       step, and an icon captioned that way on a certificate is a guarantee
       nobody made. The perceptual image hash below is the real, arithmetic
       thing that sits in its place.                                        */
    $features = [
        ['badge-check',   $isFr ? 'ARTISAN VÉRIFIÉ' : 'VERIFIED ARTISAN'],
        ['package',       $isFr ? 'PRODUIT ENREGISTRÉ' : 'REGISTERED PRODUCT'],
        ['barcode',       $isFr ? 'NUMÉRO DE SÉRIE UNIQUE' : 'UNIQUE SERIAL NUMBER'],
        ['book-open',     $isFr ? 'PASSEPORT NUMÉRIQUE' : 'DIGITAL PASSPORT'],
        ['fingerprint',   $isFr ? 'EMPREINTE D\'IMAGE' : 'IMAGE FINGERPRINT'],
        ['qr-code',       $isFr ? 'VÉRIFICATION QR' : 'QR VERIFICATION'],
        ['users',         $isFr ? 'HISTORIQUE DE PROPRIÉTÉ' : 'OWNERSHIP HISTORY'],
        ['pen-tool',      $isFr ? 'SIGNATURE SÉCURISÉE' : 'SECURE DIGITAL SIGNATURE'],
        ['lock',          $isFr ? 'ENREGISTREMENT PERMANENT' : 'PERMANENT REGISTRATION'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Certificat d\'authenticité' : 'Certificate of Authenticity' }} — {{ $name }}">
    <title>{{ $isFr ? 'Certificat d\'authenticité' : 'Certificate of Authenticity' }} — {{ $name }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { leaf: '#164C28', deepfc: '#02301B', gold: '#E5A82E' },
            fontFamily: { sans: ['Poppins','system-ui','sans-serif'], serif: ['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }

        /* The certificate is a document, so its type scale is its own and must
           not be lifted by the site-wide mobile floor — a poster that reflows
           to 16px body copy stops being a poster. It stays legible because it
           scales as a whole (see --coa-s below) rather than per-element. */
        .coa, .coa * { font-size: inherit; }

        .coa-sheet {
            --coa-green: #0A3A22;
            --coa-green-d: #05281683;
            --coa-gold: #C9942E;
            --coa-gold-l: #E0B04A;
            --coa-cream: #FBF8F0;
            --coa-ink: #1D1B16;
            background: var(--coa-cream);
        }

        /* Kente-inspired border art. Built from gradients rather than an image
           so it prints crisply at any size and adds no request. */
        .coa-kente {
            height: 26px;
            background:
                repeating-linear-gradient(90deg,
                    var(--coa-gold) 0 7px, transparent 7px 14px,
                    #1E7A3F 14px 21px, transparent 21px 28px,
                    #B4141B 28px 35px, transparent 35px 42px),
                repeating-linear-gradient(45deg,
                    rgba(201,148,46,0.55) 0 4px, transparent 4px 12px);
            background-color: var(--coa-cream);
            opacity: 0.9;
        }
        .coa-rule {
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--coa-gold) 15%, var(--coa-gold-l) 50%, var(--coa-gold) 85%, transparent);
        }

        .coa-card { background:#fff; border:1px solid #E8E0CB; border-radius:10px; }
        .coa-head {
            background: linear-gradient(180deg,#F3EFE2,#EDE7D6);
            border-bottom:1px solid #E2D9BF;
            border-radius:9px 9px 0 0;
            padding:8px 12px;
            display:flex; align-items:center; gap:8px;
            font-weight:700; letter-spacing:.06em; text-transform:uppercase;
            color: var(--coa-green);
        }
        .coa-row { display:grid; grid-template-columns: 108px 1fr; gap:10px; padding:3px 0; align-items:baseline; }
        .coa-k { color:#6B6659; font-weight:600; letter-spacing:.045em; text-transform:uppercase; }
        .coa-v { color: var(--coa-ink); font-weight:500; }

        .coa-ribbon {
            display:inline-block; position:relative;
            background: linear-gradient(180deg, var(--coa-gold-l), var(--coa-gold));
            color:#2A1E05; font-weight:800; letter-spacing:.1em; text-transform:uppercase;
            padding:5px 26px;
        }
        .coa-ribbon::before, .coa-ribbon::after {
            content:''; position:absolute; top:0; bottom:0; width:12px;
            background: inherit;
        }
        .coa-ribbon::before { left:-11px; clip-path: polygon(100% 0,100% 100%,0 50%); }
        .coa-ribbon::after  { right:-11px; clip-path: polygon(0 0,0 100%,100% 50%); }

        .coa-seal {
            background: radial-gradient(circle at 34% 30%, #F2D48A, var(--coa-gold-l) 42%, var(--coa-gold) 68%, #A6761F);
            border-radius:50%;
            box-shadow: 0 3px 10px rgba(0,0,0,.18), inset 0 0 0 3px rgba(255,255,255,.35), inset 0 0 0 8px rgba(166,118,31,.45);
        }

        .coa-sig { font-family:'Playfair Display', Georgia, serif; font-style:italic; }

        /* One knob scales the whole document, so the layout never rearranges
           between a phone and a printed page — it only gets smaller. */
        .coa { --coa-s: 1; font-size: calc(13px * var(--coa-s)); }
        @media (max-width: 1080px) { .coa { --coa-s: .92; } }
        @media (max-width: 900px)  { .coa { --coa-s: .84; } }
        /* On a phone the columns stack, so the document is no longer competing
           for width and can go back to full size. The site-wide 12px floor is
           switched off for this page (above), so the smallest captions get
           their own floor here rather than shrinking to ~8px. */
        @media (max-width: 767px) {
            .coa { --coa-s: 1.06; }
            .coa .coa-k { font-size: 10px !important; }
            .coa [style*="font-size:.66em"],
            .coa [style*="font-size:.62em"],
            .coa [style*="font-size:.6em"] { font-size: 10px !important; }
            .coa [style*="font-size:.72em"],
            .coa [style*="font-size:.74em"],
            .coa [style*="font-size:.76em"] { font-size: 11px !important; }
        }

        @media print {
            .no-print { display:none !important; }
            body { background:#fff; }
            .coa { --coa-s: .78; }
            .coa-sheet { box-shadow:none !important; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body class="bg-[#EFEADF] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

<main class="max-w-[1064px] mx-auto px-3 sm:px-5 py-5 sm:py-8">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf truncate max-w-[180px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat' : 'Certificate' }}</span>
    </nav>

    <article class="coa">
    <div class="coa-sheet p-[10px] sm:p-[14px] shadow-[0_4px_28px_rgba(0,0,0,0.10)]"
         style="border:10px solid var(--coa-green); border-radius:4px;">
    <div style="border:2px solid var(--coa-gold);">
        <div class="coa-kente"></div>

        <div class="px-[14px] sm:px-[26px] pb-[14px]">

            {{-- ───────────────── Header: lockup + QR + status ───────────────── --}}
            <div class="flex flex-col md:flex-row md:items-start gap-4 pt-4">
                <div class="flex-1 min-w-0 text-center md:text-left">
                    <img src="{{ brand_asset('full') }}" alt="Artisan Hub 237"
                         class="h-[62px] sm:h-[76px] w-auto object-contain mx-auto md:mx-0">
                    <p class="mt-1.5 tracking-[0.22em] font-semibold text-[#6B6659]" style="font-size:.72em">
                        {{ $isFr ? 'AUTHENTIQUE. CERTIFIÉ. CAMEROUNAIS.' : 'AUTHENTIC. CERTIFIED. CAMEROONIAN.' }}
                    </p>
                </div>

                <div class="shrink-0 mx-auto md:mx-0 w-[150px]">
                    <div class="coa-card px-2 py-2 text-center">
                        <p class="font-bold tracking-[0.09em] text-[#0A3A22]" style="font-size:.68em">
                            {{ $isFr ? 'VÉRIFIER LE CERTIFICAT' : 'VERIFY CERTIFICATE' }}
                        </p>
                        <div id="coa-qr" class="mt-1.5 flex justify-center [&>img]:block [&>canvas]:block"></div>
                        <p class="mt-1.5 tracking-[0.07em] text-[#6B6659]" style="font-size:.62em">
                            {{ $isFr ? 'SCANNER LE CODE QR' : 'SCAN QR CODE' }}
                        </p>
                    </div>
                    <div class="mt-1.5 rounded-[10px] px-2 py-2 text-center text-white"
                         style="background: linear-gradient(180deg,#0E4A2B,#062E1A);">
                        <p class="tracking-[0.09em] opacity-80" style="font-size:.6em">
                            {{ $isFr ? 'STATUT DU CERTIFICAT' : 'CERTIFICATE STATUS' }}
                        </p>
                        <p class="mt-1 flex items-center justify-center gap-1.5 font-extrabold tracking-[0.06em]" style="font-size:1.15em">
                            <i data-lucide="{{ $statusMeta[2] }}" class="w-[1.1em] h-[1.1em]"></i>{{ $statusMeta[0] }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- ───────────────── Title ───────────────── --}}
            <div class="text-center mt-4">
                <h1 class="font-serif font-extrabold tracking-[0.01em] text-[#0A3A22] leading-none"
                    style="font-size:2.55em">
                    {{ $isFr ? "CERTIFICAT D'AUTHENTICITÉ" : 'CERTIFICATE OF AUTHENTICITY' }}
                </h1>
                <p class="mt-3">
                    <span class="coa-ribbon" style="font-size:.78em">
                        {{ $isFr ? 'ÉMIS AUTOMATIQUEMENT PAR ARTISANHUB237' : 'ISSUED AUTOMATICALLY BY ARTISANHUB237' }}
                    </span>
                </p>
                <p class="mt-3 max-w-[720px] mx-auto text-[#3F3C34] leading-relaxed" style="font-size:.86em">
                    {{ $isFr
                       ? 'Ce certificat confirme que le produit décrit ci-dessous a été enregistré et vérifié sur la plateforme ArtisanHub237. Il constitue un enregistrement numérique permanent de l\'origine du produit, de son créateur et de sa date d\'enregistrement.'
                       : 'This certificate confirms that the product described below has been registered and verified on the ArtisanHub237 platform. It provides a permanent digital record of the product\'s origin, its creator and its registration date.' }}
                </p>
            </div>

            @if($status === 'superseded')
            <div class="ui-alert ui-alert-warn mt-4" style="font-size:.85em">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>{{ $isFr
                    ? 'La fiche produit a été modifiée depuis l\'émission de ce certificat. Les informations ci-dessous restent celles enregistrées à la date d\'émission.'
                    : 'The product record has been edited since this certificate was issued. The details below remain those recorded on the issue date.' }}</span>
            </div>
            @endif

            {{-- ───────────────── Meta strip ───────────────── --}}
            <div class="coa-card mt-4 px-3 sm:px-5 py-3.5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3">
                    @foreach([
                        ['file-badge', $isFr ? 'NUMÉRO DE CERTIFICAT' : 'CERTIFICATE NUMBER', $certificate->certificate_no, true],
                        ['calendar',   $isFr ? 'DATE D\'ÉMISSION' : 'ISSUE DATE', $issued->format('d / m / Y'), false],
                        ['link',       $isFr ? 'URL DE VÉRIFICATION' : 'VERIFICATION URL', $verifyUrl, true],
                        ['box',        $isFr ? 'IDENTIFIANT PRODUIT' : 'PRODUCT ID', $productRef, true],
                        ['clock',      $isFr ? 'HEURE D\'ÉMISSION (UTC)' : 'ISSUE TIME (UTC)', $issued->clone()->utc()->format('H:i:s'), false],
                        ['history',    $isFr ? 'VERSION DU CERTIFICAT' : 'CERTIFICATE VERSION', number_format((float) $certificate->version, 1), false],
                    ] as [$mIcon, $mLabel, $mValue, $mMono])
                    <div class="flex items-start gap-2.5 min-w-0">
                        <i data-lucide="{{ $mIcon }}" class="w-[1.25em] h-[1.25em] text-[#0A3A22] mt-[2px] shrink-0"></i>
                        <div class="min-w-0">
                            <p class="coa-k" style="font-size:.72em">{{ $mLabel }}</p>
                            <p class="coa-v {{ $mMono ? 'font-mono' : '' }} break-words" style="font-size:.88em">{{ $mValue }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ───────────────── Body: product · photo · creator ───────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-[1.05fr_.95fr_1.05fr] gap-3 mt-3">

                {{-- Product information --}}
                <section class="coa-card">
                    <h2 class="coa-head" style="font-size:.8em">
                        <i data-lucide="clipboard-list" class="w-[1.3em] h-[1.3em]"></i>
                        {{ $isFr ? 'Informations produit' : 'Product information' }}
                    </h2>
                    <div class="px-3 py-2.5" style="font-size:.82em">
                        @foreach($productRows as [$pk, $pv])
                        <div class="coa-row">
                            <span class="coa-k">{{ Str::upper($pk) }}</span>
                            <span class="coa-v">{{ $pv }}</span>
                        </div>
                        @endforeach
                    </div>
                </section>

                {{-- Photographs --}}
                <section class="flex flex-col gap-2.5">
                    <div class="coa-card p-2.5 flex items-center justify-center flex-1">
                        @if($cover)
                        <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}"
                             class="w-full h-[240px] sm:h-[290px] object-contain">
                        @else
                        <div class="w-full h-[240px] flex flex-col items-center justify-center text-[#A8A296] gap-2">
                            <i data-lucide="image-off" class="w-7 h-7"></i>
                            <span style="font-size:.8em">{{ $isFr ? 'Aucune photographie enregistrée' : 'No photograph on record' }}</span>
                        </div>
                        @endif
                    </div>
                    @if($thumbs->isNotEmpty())
                    <div class="grid gap-2" style="grid-template-columns: repeat({{ $thumbs->count() }}, minmax(0,1fr));">
                        @foreach($thumbs as $t)
                        <div class="coa-card p-1 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $t->file_path) }}" alt=""
                                 class="w-full h-[62px] object-contain">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </section>

                {{-- Creator + digital identity --}}
                <div class="flex flex-col gap-3">
                    <section class="coa-card">
                        <h2 class="coa-head" style="font-size:.8em">
                            <i data-lucide="user-round" class="w-[1.3em] h-[1.3em]"></i>
                            {{ $isFr ? 'Créateur (artisan d\'origine)' : 'Creator (original artisan)' }}
                        </h2>
                        <div class="px-3 py-2.5" style="font-size:.82em">
                            @if($artisanId)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'ID artisan' : 'Artisan ID' }}</span><span class="coa-v font-mono">{{ $artisanId }}</span></div>
                            @endif
                            @if($owner?->name)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Nom' : 'Artisan name' }}</span><span class="coa-v">{{ $owner->name }}</span></div>
                            @endif
                            @if($bizName)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Atelier' : 'Workshop' }}</span><span class="coa-v">{{ $bizName }}</span></div>
                            @endif
                            <div class="coa-row">
                                <span class="coa-k">{{ $isFr ? 'Vérification' : 'Verification' }}</span>
                                <span class="coa-v flex items-center gap-1.5">
                                    @if($verified)
                                        <i data-lucide="check-circle-2" class="w-[1.15em] h-[1.15em] text-[#0C7A3E]"></i>
                                        <b>{{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</b>
                                    @else
                                        <i data-lucide="circle-dashed" class="w-[1.15em] h-[1.15em] text-[#8A857A]"></i>
                                        {{ $isFr ? 'Enregistré, non encore vérifié' : 'Registered, not yet verified' }}
                                    @endif
                                </span>
                            </div>
                            @if($artisanRef)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Référence' : 'Reference' }}</span><span class="coa-v font-mono">{{ $artisanRef }}</span></div>
                            @endif
                            @if($biz?->slug)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Profil' : 'Profile' }}</span><span class="coa-v break-all">{{ Str::after(route('businesses.show', ['slug' => $biz->slug]), '://') }}</span></div>
                            @endif
                            @if($location)
                            <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Localisation' : 'Location' }}</span><span class="coa-v">{{ $location }}</span></div>
                            @endif
                        </div>
                    </section>

                    {{-- Digital identity.
                         The design also lists an AI visual fingerprint and a
                         watermark reference. Neither is implemented, so neither
                         is printed — a labelled row on a certificate is read as
                         a measure that was taken. What remains is computed for
                         real and re-checked on every verification. --}}
                    <section class="coa-card">
                        <h2 class="coa-head" style="font-size:.8em">
                            <i data-lucide="fingerprint" class="w-[1.3em] h-[1.3em]"></i>
                            {{ $isFr ? 'Identité numérique' : 'Digital identity' }}
                        </h2>
                        <div class="px-3 py-2.5" style="font-size:.82em">
                            <div class="coa-row">
                                <span class="coa-k">{{ $isFr ? 'Empreinte du contenu' : 'Content fingerprint' }}</span>
                                <span class="coa-v font-mono break-all">{{ Str::upper(substr($certificate->content_hash, 0, 24)) }}</span>
                            </div>
                            @if($certificate->image_phash)
                            <div class="coa-row">
                                <span class="coa-k">{{ $isFr ? 'Empreinte de l\'image' : 'Perceptual image hash' }}</span>
                                <span class="coa-v font-mono break-all">{{ Str::upper($certificate->image_phash) }}</span>
                            </div>
                            @endif
                            @if($certificate->signature)
                            <div class="coa-row">
                                <span class="coa-k">{{ $isFr ? 'Signature' : 'Signature' }}</span>
                                <span class="coa-v font-mono break-all">{{ Str::upper(substr($certificate->signature, 0, 24)) }}</span>
                            </div>
                            @endif
                            <div class="coa-row">
                                <span class="coa-k">{{ $isFr ? 'Code de vérification' : 'Verification PIN' }}</span>
                                <span class="coa-v font-mono tracking-[0.14em]">{{ $certificate->verification_pin }}</span>
                            </div>
                            <p class="mt-2 text-[#6B6659] leading-snug" style="font-size:.88em">
                                {{ $isFr
                                   ? 'Valeurs calculées sur les informations et la photographie enregistrées, et recalculées à chaque vérification.'
                                   : 'Computed over the recorded details and photograph, and recomputed on every verification.' }}
                            </p>
                        </div>
                    </section>
                </div>
            </div>

            {{-- ───────────────── Ownership ───────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.3fr_.8fr] gap-3 mt-3">

                <section class="coa-card">
                    <h2 class="coa-head" style="font-size:.8em">
                        <i data-lucide="users" class="w-[1.3em] h-[1.3em]"></i>
                        {{ $isFr ? 'Propriété' : 'Ownership information' }}
                    </h2>
                    <div class="px-3 py-2.5" style="font-size:.82em">
                        <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Détenteur actuel' : 'Current owner' }}</span><span class="coa-v">{{ $owner?->name ?: $bizName }}</span></div>
                        <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Statut' : 'Ownership status' }}</span><span class="coa-v">{{ $isFr ? 'Artisan d\'origine' : 'Original artisan' }}</span></div>
                        <div class="coa-row"><span class="coa-k">{{ $isFr ? 'Depuis le' : 'Owner since' }}</span><span class="coa-v">{{ $product->created_at?->format('d / m / Y') }}</span></div>
                    </div>
                </section>

                <section class="coa-card">
                    <h2 class="coa-head" style="font-size:.8em">
                        <i data-lucide="history" class="w-[1.3em] h-[1.3em]"></i>
                        {{ $isFr ? 'Historique de propriété' : 'Ownership history' }}
                    </h2>
                    <div class="px-3 py-2.5 overflow-x-auto" style="font-size:.8em">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="coa-k" style="font-size:.92em">
                                    <th class="pb-1.5 pr-3 font-semibold">{{ $isFr ? 'Date' : 'Date' }}</th>
                                    <th class="pb-1.5 pr-3 font-semibold">{{ $isFr ? 'De' : 'From' }}</th>
                                    <th class="pb-1.5 pr-3 font-semibold">{{ $isFr ? 'À' : 'To' }}</th>
                                    <th class="pb-1.5 font-semibold">{{ $isFr ? 'Événement' : 'Event' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-t border-[#EFE9DA]">
                                    <td class="py-1.5 pr-3 whitespace-nowrap">{{ $product->created_at?->format('d / m / Y') }}</td>
                                    <td class="py-1.5 pr-3 text-[#8A857A]">—</td>
                                    <td class="py-1.5 pr-3">{{ $owner?->name ?: $bizName }}</td>
                                    <td class="py-1.5">{{ $isFr ? 'Premier enregistrement' : 'First registration' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        {{-- The platform records no transfers, because it has no
                             transfer feature. Saying so is the honest form of
                             the design's "no further transfers recorded". --}}
                        <p class="mt-2 text-center text-[#8A857A]" style="font-size:.92em">
                            {{ $isFr ? 'Aucun transfert de propriété enregistré.' : 'No ownership transfers recorded.' }}
                        </p>
                    </div>
                </section>

                <section class="coa-card">
                    <h2 class="coa-head" style="font-size:.8em">
                        <i data-lucide="check-circle-2" class="w-[1.3em] h-[1.3em]"></i>
                        {{ $isFr ? 'Statut du produit' : 'Product status' }}
                    </h2>
                    <div class="px-3 py-3.5 text-center" style="font-size:.82em">
                        <span class="inline-block rounded-md px-5 py-1.5 font-extrabold tracking-[0.08em] text-white"
                              style="font-size:1.05em; background: {{ $onSale ? 'linear-gradient(180deg,#0E7A3E,#0A5C2E)' : 'linear-gradient(180deg,#8A857A,#6B6659)' }};">
                            {{ $onSale ? ($isFr ? 'ACTIF' : 'ACTIVE') : ($isFr ? 'INACTIF' : 'INACTIVE') }}
                        </span>
                        <p class="mt-2 text-[#6B6659]">
                            {{ $onSale
                               ? ($isFr ? 'Disponible à la vente' : 'Available for sale')
                               : ($isFr ? 'Non disponible actuellement' : 'Not currently available') }}
                        </p>
                    </div>
                </section>
            </div>

            {{-- ───────────────── Authenticity features ───────────────── --}}
            <section class="coa-card mt-3 px-3 sm:px-5 py-4">
                <div class="flex items-center gap-3 justify-center">
                    <span class="h-px flex-1 max-w-[130px]" style="background:linear-gradient(90deg,transparent,var(--coa-gold));"></span>
                    <h2 class="font-extrabold tracking-[0.14em] text-[#0A3A22] uppercase" style="font-size:.88em">
                        {{ $isFr ? 'Éléments d\'authenticité' : 'Authenticity features' }}
                    </h2>
                    <span class="h-px flex-1 max-w-[130px]" style="background:linear-gradient(270deg,transparent,var(--coa-gold));"></span>
                </div>
                <div class="mt-3.5 grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-x-2 gap-y-4">
                    @foreach($features as [$fIcon, $fLabel])
                    <div class="text-center">
                        <i data-lucide="{{ $fIcon }}" class="w-[1.9em] h-[1.9em] mx-auto text-[#0A3A22]" stroke-width="1.6"></i>
                        <p class="mt-1.5 font-bold tracking-[0.03em] text-[#3F3C34] leading-tight" style="font-size:.66em">{{ $fLabel }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- ───────────────── Seal · statement · disclaimer ───────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-[.62fr_1.55fr_1.05fr] gap-3 mt-3 items-stretch">

                <div class="flex items-center justify-center py-2">
                    <div class="coa-seal w-[132px] h-[132px] flex flex-col items-center justify-center text-center px-3">
                        <img src="{{ brand_asset('mark') }}" alt="" class="w-[46px] h-[46px] object-contain drop-shadow">
                        <p class="mt-1 font-extrabold tracking-[0.06em] text-[#3A2A06] leading-none" style="font-size:.62em">ARTISANHUB237</p>
                        <p class="mt-[3px] font-semibold tracking-[0.05em] text-[#4A360B] leading-none" style="font-size:.5em">
                            {{ $isFr ? 'CERTIFIÉ' : 'CERTIFIED' }} · CAMEROON
                        </p>
                    </div>
                </div>

                <section class="coa-card px-4 py-3.5">
                    <h2 class="text-center font-extrabold tracking-[0.1em] text-[#0A3A22] uppercase" style="font-size:.84em">
                        {{ $isFr ? 'Déclaration d\'authenticité' : 'Authenticity statement' }}
                    </h2>
                    <div class="mt-2.5 text-[#3F3C34] leading-relaxed space-y-2" style="font-size:.8em">
                        <p>
                            {{ $isFr
                               ? 'ArtisanHub237 atteste qu\'à la date d\'émission, ce produit était enregistré sur la plateforme par le créateur nommé ci-dessus, avec les informations et les photographies reproduites ici.'
                               : 'ArtisanHub237 attests that on the issue date this product was registered on the platform by the creator named above, with the details and photographs reproduced here.' }}
                        </p>
                        <p>
                            {{ $isFr
                               ? 'Ce certificat établit un enregistrement horodaté et vérifiable de la paternité déclarée. Il ne prouve pas qu\'un objet physique donné est celui figurant sur les photographies : vérifiez toujours son statut à l\'aide du code QR avant un achat.'
                               : 'This certificate establishes a time-stamped, verifiable record of declared authorship. It does not prove that a given physical object is the one shown in the photographs: always check its status with the QR code before buying.' }}
                        </p>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="coa-sig text-[#0A3A22]" style="font-size:1.35em">ArtisanHub237 Authority</p>
                        <span class="block mx-auto mt-1 h-px w-[190px]" style="background:#C4BCA6;"></span>
                        <p class="mt-1.5 text-[#6B6659]" style="font-size:.74em">
                            {{ $isFr ? 'Signé numériquement par l\'autorité de certification ArtisanHub237' : 'Digitally signed by the ArtisanHub237 Certification Authority' }}
                        </p>
                    </div>
                </section>

                <section class="coa-card px-4 py-3.5">
                    <h2 class="text-center font-extrabold tracking-[0.1em] text-[#0A3A22] uppercase" style="font-size:.84em">
                        {{ $isFr ? 'Avertissement' : 'Disclaimer' }}
                    </h2>
                    <div class="mt-2.5 text-[#5A554A] leading-relaxed space-y-2" style="font-size:.76em">
                        <p>
                            {{ $isFr
                               ? 'Ce certificat est généré automatiquement par la plateforme ArtisanHub237 lors de l\'enregistrement et de la publication du produit.'
                               : 'This certificate is generated automatically by the ArtisanHub237 platform upon product registration and publication.' }}
                        </p>
                        <p>
                            {{ $isFr
                               ? 'Il enregistre l\'identité du produit et sa provenance déclarée au sein de l\'écosystème ArtisanHub237.'
                               : 'It records product identity and declared provenance within the ArtisanHub237 ecosystem.' }}
                        </p>
                        <p>
                            {{ $isFr
                               ? 'L\'enregistrement sur ArtisanHub237 ne remplace pas le droit d\'auteur, une marque, un dessin ou modèle industriel ni aucun autre droit de propriété intellectuelle. Les utilisateurs restent responsables de l\'obtention de toute protection légale supplémentaire.'
                               : 'Registration on ArtisanHub237 does not replace applicable copyright, trademark, industrial design or other intellectual property rights. Users remain responsible for obtaining any additional legal protections available under applicable law.' }}
                        </p>
                        <p>
                            {{ $isFr
                               ? 'ArtisanHub237 est une entreprise privée. La plateforme n\'est pas partie aux transactions et n\'encaisse aucun paiement.'
                               : 'ArtisanHub237 is a private company. The platform is not a party to transactions and collects no payments.' }}
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <div class="coa-rule"></div>

        {{-- ───────────────── Footer band ───────────────── --}}
        <footer class="px-5 py-3.5 text-center text-white" style="background:linear-gradient(180deg,#0E4A2B,#052616);">
            <p class="font-serif italic" style="font-size:1.45em; color:#F0D79A;">
                {{ $isFr ? 'Chaque produit a une histoire. Chaque histoire a une preuve.' : 'Every Product Has a Story. Every Story Has Proof.' }}
            </p>
            <p class="mt-1.5 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 tracking-[0.05em]" style="font-size:.76em">
                <span class="font-semibold">WWW.ARTISANHUB237.COM</span>
                <span class="opacity-50">·</span>
                <span class="opacity-90">@artisanhub237</span>
            </p>
            <p class="mt-1 font-mono opacity-70" style="font-size:.72em">{{ $certificate->certificate_no }}</p>
        </footer>

        <div class="coa-kente"></div>
    </div>
    </div>
    </article>

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
                text: @json($verifyUrl),
                width: 116, height: 116,
                colorDark: '#0A3A22', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();
</script>
</body>
</html>
