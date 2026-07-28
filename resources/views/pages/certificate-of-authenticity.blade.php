@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $biz     = $product->business;
    $name    = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
    $desc    = $isFr ? $product->description_fr : ($product->description_en ?: $product->description_fr);
    $bizName = $biz?->name_fr;
    $cover   = $product->images->firstWhere('is_cover', true) ?? $product->images->sortBy('sort_order')->first();

    $verifyUrl = route('product.certificate.verify', ['ref' => $certificate->certificate_no, 'lang' => $lang]);

    // Specifications come from the artisan's own attribute values. Nothing is
    // invented: a dimension the artisan never entered simply does not appear,
    // rather than printing an empty "Height" row on a certificate.
    $specs = $product->attributes
        ->filter(fn ($a) => $a->template && filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->map(fn ($a) => [
            $isFr ? $a->template->name_fr : ($a->template->name_en ?: $a->template->name_fr),
            $isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr),
        ])->values();

    $origin = collect([
        $biz?->city?->name_fr,
        $biz?->region?->name_fr,
        'Cameroun',
    ])->filter()->unique()->implode(', ');

    $statusMeta = [
        'valid'      => [$isFr ? 'Valide' : 'Valid', 'ui-pill-ok'],
        'superseded' => [$isFr ? 'Remplacé' : 'Superseded', 'ui-pill-warn'],
        'revoked'    => [$isFr ? 'Révoqué' : 'Revoked', 'ui-pill-danger'],
    ][$status] ?? [$isFr ? 'Inconnu' : 'Unknown', 'ui-pill-neutral'];

    $dfShowHelp = true;
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
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        /* The certificate is meant to be printed and kept. Drop the site chrome
           and let it fall onto one page. */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .coa { border: 1px solid #CBBE93 !important; box-shadow: none !important; }
        }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F5F3EE] text-[#1D1B16] antialiased">

<div class="no-print">@include('pages.partials.directory-header')</div>

<main class="max-w-[880px] mx-auto px-4 sm:px-6 py-6 sm:py-10">

    <nav class="no-print flex items-center gap-2 text-[12.5px] mb-4" aria-label="Breadcrumb">
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf">{{ $isFr ? 'Produits' : 'Products' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf truncate max-w-[160px]">{{ $name }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Certificat' : 'Certificate' }}</span>
    </nav>

    <article class="coa bg-white border border-[#E4DEC9] rounded-2xl overflow-hidden shadow-[0_2px_18px_rgba(0,0,0,0.05)]">

        {{-- Tricolour band, matching the platform's other certificates --}}
        <div class="flex h-[6px]" aria-hidden="true">
            <div class="w-[33%] bg-[#014D25]"></div>
            <div class="w-[34%] bg-[#CA0107]"></div>
            <div class="flex-1 bg-[#E5A82E]"></div>
        </div>

        <header class="px-5 sm:px-9 pt-7 pb-6 text-center border-b border-[#F0EAD8]">
            <img src="{{ brand_asset('mark') }}" alt="" class="w-[58px] h-[58px] mx-auto object-contain">
            <h1 class="mt-4 font-serif text-[22px] sm:text-[30px] font-bold tracking-[0.02em] text-[#02301B] leading-tight">
                {{ $isFr ? "CERTIFICAT D'AUTHENTICITÉ" : 'CERTIFICATE OF AUTHENTICITY' }}
            </h1>
            <p class="mt-2 text-[12.5px] text-[#55524A] max-w-[440px] mx-auto leading-relaxed">
                {{ $isFr
                   ? 'Enregistrement officiel du produit et de son créateur sur Artisan Hub 237.'
                   : 'Official record of the product and its maker on Artisan Hub 237.' }}
            </p>
            <p class="mt-3 text-[11.5px] font-semibold tracking-[0.14em] text-[#157A43] uppercase">
                {{ $isFr ? 'Authentique • Certifié • Traçable' : 'Authentic • Certified • Traceable' }}
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <span class="ui-pill {{ $statusMeta[1] }}">{{ $statusMeta[0] }}</span>
                <span class="ui-pill ui-pill-neutral">v{{ $certificate->version }}</span>
            </div>
        </header>

        @if($status === 'superseded')
        <div class="px-5 sm:px-9 pt-5">
            <div class="ui-alert ui-alert-warn">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>{{ $isFr
                    ? 'La fiche produit a été modifiée depuis l\'émission de ce certificat. Les informations ci-dessous restent celles enregistrées à la date d\'émission.'
                    : 'The product record has been edited since this certificate was issued. The details below remain those recorded on the issue date.' }}</span>
            </div>
        </div>
        @endif

        {{-- ── Certificate identity ── --}}
        <section class="px-5 sm:px-9 py-6 border-b border-[#F0EAD8]">
            <h2 class="ui-card-title">{{ $isFr ? 'Identification du certificat' : 'Certificate identity' }}</h2>
            <dl class="ui-dl ui-dl--2 mt-3">
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Numéro de certificat' : 'Certificate number' }}</dt>
                    <dd class="ui-dd font-mono text-[12.5px] break-all">{{ $certificate->certificate_no }}</dd>
                </div>
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Code de vérification' : 'Verification PIN' }}</dt>
                    <dd class="ui-dd font-mono tracking-[0.14em]">{{ $certificate->verification_pin }}</dd>
                </div>
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Date d\'émission' : 'Issue date' }}</dt>
                    <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($certificate->issued_at)->translatedFormat('d F Y') }}
                        <span class="text-[#8A857A]">· {{ \Illuminate\Support\Carbon::parse($certificate->issued_at)->format('H:i') }} UTC</span></dd>
                </div>
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Vérifications' : 'Verifications' }}</dt>
                    <dd class="ui-dd">{{ $certificate->verification_count }}</dd>
                </div>
            </dl>

            <div class="mt-4">
                <p class="ui-dt">{{ $isFr ? 'Empreinte du contenu (SHA-256)' : 'Content hash (SHA-256)' }}</p>
                <p class="mt-1 font-mono text-[10.5px] leading-relaxed text-[#55524A] break-all">{{ $certificate->content_hash }}</p>
                <p class="ui-hint mt-1">
                    {{ $isFr
                       ? 'Calculée sur les informations certifiées. Toute modification ultérieure de la fiche produit change cette empreinte et remplace le certificat.'
                       : 'Computed over the certified details. Any later edit to the product record changes this hash and supersedes the certificate.' }}
                </p>
            </div>
        </section>

        {{-- ── Maker ── --}}
        <section class="px-5 sm:px-9 py-6 border-b border-[#F0EAD8]">
            <h2 class="ui-card-title">{{ $isFr ? 'Créateur' : 'Maker' }}</h2>
            <div class="mt-3 flex items-start gap-4">
                @if($biz?->logo)
                <img src="{{ asset('storage/' . $biz->logo) }}" alt="" class="w-[54px] h-[54px] rounded-lg object-cover shrink-0 border border-[#EFEBE2]">
                @endif
                <div class="min-w-0">
                    <p class="text-[15px] font-bold text-[#1D1B16]">{{ $bizName }}</p>
                    @if(in_array($biz?->verification_tier, ['verified','certified'], true))
                    <span class="ui-pill ui-pill-ok mt-1.5">{{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                    @endif
                    <a href="{{ route('businesses.show', ['slug' => $biz->slug, 'lang' => $lang]) }}"
                       class="no-print block mt-2 text-[12.5px] font-semibold text-[#157A43] hover:underline">
                        {{ $isFr ? 'Voir le profil de l\'artisan' : 'View the artisan profile' }}
                    </a>
                </div>
            </div>
            <dl class="ui-dl ui-dl--2 mt-4">
                {{-- The artisan's reference on THIS platform. An earlier version
                     printed the SIARC 2026 competition code here; a certificate
                     issued by Artisan Hub 237 should carry Artisan Hub 237's own
                     identifiers, not another organisation's. --}}
                @if($biz)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Référence artisan' : 'Artisan reference' }}</dt>
                    <dd class="ui-dd font-mono">{{ $biz->certificate_no ?: certNumberFor($biz->id, $biz->created_at) }}</dd>
                </div>
                @endif
                @if($origin)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Origine' : 'Origin' }}</dt>
                    <dd class="ui-dd">{{ $origin }}</dd>
                </div>
                @endif
                @if($biz?->created_at)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Inscrit depuis' : 'Registered since' }}</dt>
                    <dd class="ui-dd">{{ $biz->created_at->translatedFormat('F Y') }}</dd>
                </div>
                @endif
            </dl>
        </section>

        {{-- ── Product ── --}}
        <section class="px-5 sm:px-9 py-6 border-b border-[#F0EAD8]">
            <h2 class="ui-card-title">{{ $isFr ? 'Produit' : 'Product' }}</h2>

            <div class="mt-3 flex flex-col sm:flex-row gap-5">
                @if($cover)
                <img src="{{ asset('storage/' . $cover->file_path) }}" alt="{{ $name }}"
                     class="w-full sm:w-[190px] h-[190px] object-cover rounded-xl border border-[#EFEBE2] shrink-0">
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[17px] font-bold text-[#1D1B16] leading-snug">{{ $name }}</p>
                    @if($product->category)
                    <p class="mt-1 text-[12.5px] text-[#6F6B60]">{{ $isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr) }}</p>
                    @endif
                    @if($desc)
                    <p class="mt-3 text-[12.5px] text-[#3A3A35] leading-relaxed whitespace-pre-line">{{ \Illuminate\Support\Str::limit($desc, 420) }}</p>
                    @endif
                </div>
            </div>

            <dl class="ui-dl ui-dl--2 mt-5">
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Identifiant produit' : 'Product ID' }}</dt>
                    <dd class="ui-dd font-mono text-[11.5px] break-all">{{ $product->uuid }}</dd>
                </div>
                @if($product->sku)
                <div>
                    <dt class="ui-dt">SKU</dt>
                    <dd class="ui-dd font-mono">{{ $product->sku }}</dd>
                </div>
                @endif
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Enregistré le' : 'Registered on' }}</dt>
                    <dd class="ui-dd">{{ $product->created_at?->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Photographies archivées' : 'Photographs archived' }}</dt>
                    <dd class="ui-dd">{{ $product->images->count() }}</dd>
                </div>
            </dl>

            @if($specs->isNotEmpty())
            <hr class="ui-divider">
            <p class="ui-eyebrow">{{ $isFr ? 'Caractéristiques' : 'Specifications' }}</p>
            <dl class="ui-dl ui-dl--2 mt-2">
                @foreach($specs as [$specLabel, $specValue])
                <div>
                    <dt class="ui-dt">{{ $specLabel }}</dt>
                    <dd class="ui-dd">{{ $specValue }}</dd>
                </div>
                @endforeach
            </dl>
            @endif
        </section>

        {{-- ── Archived photographs ── --}}
        @if($product->images->count() > 1)
        <section class="px-5 sm:px-9 py-6 border-b border-[#F0EAD8]">
            <h2 class="ui-card-title">{{ $isFr ? 'Photographies enregistrées' : 'Registered photographs' }}</h2>
            <div class="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                @foreach($product->images->sortBy('sort_order') as $img)
                <img src="{{ asset('storage/' . $img->file_path) }}" alt=""
                     class="w-full aspect-square object-cover rounded-lg border border-[#EFEBE2]">
                @endforeach
            </div>
        </section>
        @endif

        {{-- ── Verification ── --}}
        <section class="px-5 sm:px-9 py-6 border-b border-[#F0EAD8]">
            <h2 class="ui-card-title">{{ $isFr ? 'Vérifier ce certificat' : 'Verify this certificate' }}</h2>
            <p class="mt-2 text-[12.5px] text-[#3A3A35] leading-relaxed">
                {{ $isFr
                   ? 'Rendez-vous à l\'adresse ci-dessous et saisissez le numéro de certificat, puis comparez les informations affichées avec l\'objet que vous avez sous les yeux.'
                   : 'Go to the address below, enter the certificate number, then compare what is shown with the object in front of you.' }}
            </p>
            <p class="mt-3 font-mono text-[12px] text-[#157A43] break-all">{{ $verifyUrl }}</p>
            <a href="{{ $verifyUrl }}" class="no-print ui-btn ui-btn-secondary ui-btn-sm mt-3">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                {{ $isFr ? 'Ouvrir la page de vérification' : 'Open the verification page' }}
            </a>
        </section>

        {{-- ── What this certificate does and does not say ── --}}
        <section class="px-5 sm:px-9 py-6 bg-[#FAF8F2]">
            <h2 class="ui-card-title">{{ $isFr ? 'Portée de ce certificat' : 'What this certificate covers' }}</h2>

            <p class="mt-3 text-[12.5px] text-[#3A3A35] leading-relaxed">
                {{ $isFr
                   ? 'Artisan Hub 237 atteste qu\'à la date d\'émission, ce produit était enregistré sur la plateforme par l\'artisan nommé ci-dessus, avec les informations et les photographies reproduites ici. Ce certificat constitue un enregistrement horodaté de cette déclaration.'
                   : 'Artisan Hub 237 attests that on the issue date this product was registered on the platform by the artisan named above, with the details and photographs reproduced here. This certificate is a time-stamped record of that declaration.' }}
            </p>

            {{-- Saying plainly what it cannot do is what makes the rest credible.
                 The platform has no physical link between an object and its
                 record — no fingerprint, no watermark, no tag — so it must not
                 imply one. --}}
            <div class="ui-alert ui-alert-warn mt-4">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>
                    {{ $isFr
                       ? 'Ce certificat ne prouve pas qu\'un objet physique donné est celui figurant sur les photographies. Il atteste de l\'enregistrement, non de l\'identité matérielle de l\'objet. Vérifiez toujours le statut du certificat avant un achat.'
                       : 'This certificate does not prove that a given physical object is the one in the photographs. It attests to the registration, not to the material identity of the object. Always check the certificate status before buying.' }}
                </span>
            </div>

            <p class="mt-4 text-[11.5px] text-[#6F6B60] leading-relaxed">
                {{ $isFr
                   ? 'L\'enregistrement sur Artisan Hub 237 ne remplace pas le droit d\'auteur, une marque, un dessin ou modèle industriel, une indication géographique ni aucun autre droit de propriété intellectuelle prévu par la loi applicable. Il constitue un élément de preuve indépendant et horodaté de la paternité déclarée et de la date d\'enregistrement.'
                   : 'Registration on Artisan Hub 237 does not replace copyright, trademark, industrial design, geographical indication or any other intellectual property right available under applicable law. It provides an independent, time-stamped record of declared authorship and registration date.' }}
            </p>
            <p class="mt-3 text-[11.5px] text-[#6F6B60] leading-relaxed">
                {{ $isFr
                   ? 'Artisan Hub 237 est une entreprise privée. La plateforme n\'est pas partie aux transactions et n\'encaisse aucun paiement.'
                   : 'Artisan Hub 237 is a private company. The platform is not a party to transactions and collects no payments.' }}
            </p>
        </section>

        <footer class="px-5 sm:px-9 py-6 border-t border-[#F0EAD8] text-center">
            <p class="font-serif text-[15px] font-bold text-[#02301B]">Artisan Hub 237</p>
            <p class="mt-1 text-[11.5px] text-[#6F6B60]">
                {{ $isFr ? 'Chaque produit a une histoire. Chaque histoire a une preuve.' : 'Every product has a story. Every story has proof.' }}
            </p>
            <p class="mt-2 font-mono text-[11px] text-[#8A857A]">{{ $certificate->certificate_no }}</p>
        </footer>
    </article>

    <div class="no-print mt-5 flex flex-wrap gap-2.5 justify-center">
        <button type="button" onclick="window.print()" class="ui-btn ui-btn-primary">
            <i data-lucide="printer" class="w-4 h-4"></i>
            {{ $isFr ? 'Imprimer / Enregistrer en PDF' : 'Print / Save as PDF' }}
        </button>
        <a href="{{ route('products.show', ['slug' => $product->slug, 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
            {{ $isFr ? 'Retour au produit' : 'Back to the product' }}
        </a>
    </div>
</main>

<div class="no-print">@include('pages.partials.directory-footer')</div>
<script>lucide.createIcons();</script>
</body>
</html>
