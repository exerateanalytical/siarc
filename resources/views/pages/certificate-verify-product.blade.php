@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $cert    = $result['certificate'] ?? null;
    $product = $result['product'] ?? null;
    $status  = $result['status'] ?? null;

    /*
     * The platform issues six documents and every one of them prints a
     * verification address. The route in front of this page asks the product
     * register first, which answers for the Certificate of Authenticity; when it
     * has never heard of the reference, the directory is asked, and it answers
     * for the ownership transfer, artisan verification and export registers too.
     *
     * Only the notfound case falls through, so the Certificate of Authenticity
     * path is untouched — it is not verified twice, and its counter is not
     * incremented twice for one visit.
     */
    $doc = null;

    if ($ref !== '' && $status === 'notfound') {
        $doc = \App\Support\CertificateDirectory::resolve($ref, $pin ?: null);

        if ($doc['status'] !== 'notfound') {
            $status = $doc['status'];
            $cert   = $doc['certificate'];
        } else {
            $doc = null;
        }
    }

    // The product register answered, so the document is a Certificate of
    // Authenticity. Named on the page for the same reason the other three are:
    // a reader holding a printed sheet should be able to see that the page is
    // talking about the document in their hand.
    if (! $doc && $cert && $status !== null) {
        $doc = [
            'type'               => 'coa',
            'status'             => $status,
            'certificate'        => $cert,
            'subject'            => $product
                ? ['label' => $product->name_fr, 'url' => null]
                : ['label' => null, 'url' => null],
            'signature'          => $result['signature'] ?? \App\Support\ProductCertificate::signatureState($cert),
            'issued_at'          => $cert->issued_at ?? null,
            'expires_at'         => $cert->expires_at ?? null,
            'verification_count' => isset($cert->verification_count) ? (int) $cert->verification_count : null,
            'document_url'       => $product ? route('product.certificate', ['slug' => $product->slug, 'lang' => $lang]) : null,
        ];
    }

    // pin_mismatch names the type but carries no certificate, so the number can
    // be checked without the contents being handed over.
    if (! $doc && $status === 'pin_mismatch') {
        $docName = $isFr ? "Certificat d'authenticité" : 'Certificate of Authenticity';
    }

    $docName = $docName ?? ($doc ? \App\Support\CertificateDirectory::name($doc['type'], $lang) : null);

    // Each outcome gets its own wording. "Not found" and "the record changed
    // after issue" are different situations, and a buyer standing in a market
    // needs to tell them apart rather than seeing one generic failure.
    $verdicts = [
        'valid' => ['ui-alert-ok', 'shield-check',
            $isFr ? 'Certificat valide' : 'Valid certificate',
            $isFr ? 'Les informations ci-dessous correspondent à l\'enregistrement officiel. Comparez-les avec l\'objet que vous avez sous les yeux.'
                  : 'The details below match the official registration. Compare them with the object in front of you.'],
        'superseded' => ['ui-alert-warn', 'alert-triangle',
            $isFr ? 'Certificat remplacé' : 'Superseded certificate',
            $isFr ? 'La fiche produit a été modifiée depuis l\'émission. Le produit reste enregistré, mais demandez le certificat à jour au vendeur.'
                  : 'The product record was edited after issue. The product is still registered, but ask the seller for the current certificate.'],
        'revoked' => ['ui-alert-danger', 'x-circle',
            $isFr ? 'Certificat révoqué' : 'Revoked certificate',
            $isFr ? 'Ce certificat a été annulé et ne doit pas être accepté comme preuve d\'enregistrement.'
                  : 'This certificate has been cancelled and should not be accepted as proof of registration.'],
        'pin_mismatch' => ['ui-alert-danger', 'key-round',
            $isFr ? 'Code de vérification incorrect' : 'Verification PIN does not match',
            $isFr ? 'Le numéro existe mais le code ne correspond pas. Vérifiez les deux sur le certificat.'
                  : 'The number exists but the PIN does not match it. Check both against the certificate.'],
        // Only the export certificate carries a real expiry window, and a
        // window that has closed is a different thing from a revocation: the
        // record stands, the authorisation to move the piece under it does not.
        'expired' => ['ui-alert-warn', 'clock',
            $isFr ? 'Certificat expiré' : 'Expired certificate',
            $isFr ? 'La période de validité indiquée sur ce certificat est écoulée. L\'enregistrement subsiste, mais le document n\'est plus courant : demandez-en un à jour.'
                  : 'The validity period printed on this certificate has passed. The registration stands, but the document is no longer current — ask for an up-to-date one.'],
        'notfound' => ['ui-alert-danger', 'search-x',
            $isFr ? 'Aucun certificat trouvé' : 'No certificate found',
            $isFr ? 'Aucun certificat ne porte cette référence. Vérifiez la saisie — et méfiez-vous si le vendeur insiste.'
                  : 'No certificate carries this reference. Check what you typed — and be wary if the seller insists.'],
    ];
    $v = $verdicts[$status] ?? null;

    $dfShowHelp = true;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Vérifiez l\'authenticité d\'un certificat de produit Artisan Hub 237.' : 'Verify an Artisan Hub 237 product certificate.' }}">
    <title>{{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }} — Artisan Hub 237</title>

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
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#FEFEFE] dark:bg-[#12150F] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[720px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <h1 class="font-serif text-[26px] sm:text-[32px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-tight">
        {{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }}
    </h1>
    <p class="mt-2.5 text-[13.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed max-w-[520px]">
        {{ $isFr
           ? 'Saisissez le numéro figurant sur le certificat d\'authenticité. Le code de vérification est facultatif, mais le fournir confirme que vous avez le certificat sous les yeux.'
           : 'Enter the number printed on the certificate of authenticity. The PIN is optional, but supplying it confirms you are holding the certificate itself.' }}
    </p>

    <form method="GET" action="{{ route('product.certificate.verify') }}" class="mt-6 ui-card">
        <input type="hidden" name="lang" value="{{ $lang }}">
        <div class="ui-form-grid ui-form-grid--2">
            <div>
                <label class="ui-label" for="ref">{{ $isFr ? 'Numéro de certificat' : 'Certificate number' }}<span class="ui-req">*</span></label>
                <input id="ref" name="ref" type="text" required value="{{ $ref }}"
                       placeholder="AHC-COA-2026-000000123" class="ui-field font-mono">
            </div>
            <div>
                <label class="ui-label" for="pin">{{ $isFr ? 'Code de vérification' : 'Verification PIN' }}</label>
                <input id="pin" name="pin" type="text" value="{{ $pin }}" placeholder="{{ $isFr ? 'facultatif' : 'optional' }}"
                       class="ui-field font-mono tracking-[0.12em]">
            </div>
        </div>
        <button type="submit" class="ui-btn ui-btn-primary mt-4">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            {{ $isFr ? 'Vérifier' : 'Verify' }}
        </button>
    </form>

    @if($v)
    <div class="ui-alert {{ $v[0] }} mt-6">
        <i data-lucide="{{ $v[1] }}" class="w-4 h-4 shrink-0 mt-0.5"></i>
        <span><strong>{{ $v[2] }}</strong><br>{{ $v[3] }}</span>
    </div>
    @endif

    {{-- The document itself: which of the six this reference belongs to, where
         the full sheet is, and whether the authority's signature over it still
         verifies. Every row is omitted when the register holds nothing for it —
         a blank field on a verification page reads as a fact. --}}
    @if($docName)
    <div class="ui-card mt-5">
        <div class="ui-card-head">
            <h2 class="ui-card-title">{{ $isFr ? 'Document' : 'Document' }}</h2>
            @if($doc && $doc['type'] !== 'coa')
            <span class="ui-pill shrink-0 font-mono">{{ strtoupper($doc['type']) }}</span>
            @endif
        </div>

        <p class="text-[15px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $docName }}</p>

        @if($doc)
        <dl class="ui-dl ui-dl--2 mt-3">
            @if($doc['subject']['label'])
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Objet du certificat' : 'What it certifies' }}</dt>
                <dd class="ui-dd">
                    @if($doc['subject']['url'])
                    <a href="{{ $doc['subject']['url'] }}" class="text-[#157A43] dark:text-[#339B56] font-semibold">{{ $doc['subject']['label'] }}</a>
                    @else
                    {{ $doc['subject']['label'] }}
                    @endif
                </dd>
            </div>
            @endif
            @if($doc['issued_at'])
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Émis le' : 'Issued' }}</dt>
                <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($doc['issued_at'])->translatedFormat('d F Y') }}</dd>
            </div>
            @endif
            @if($doc['expires_at'])
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Valable jusqu\'au' : 'Valid until' }}</dt>
                <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($doc['expires_at'])->translatedFormat('d F Y') }}</dd>
            </div>
            @endif
            @if($doc['verification_count'] !== null)
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Vérifications' : 'Times verified' }}</dt>
                <dd class="ui-dd">{{ $doc['verification_count'] }}</dd>
            </div>
            @endif
        </dl>

        @php
            $sig = $doc['signature'] ?? null;
            $sigLabels = [
                'valid'    => [$isFr ? 'Signature Ed25519 vérifiée' : 'Ed25519 signature verifies', 'ui-pill-ok'],
                'invalid'  => [$isFr ? 'Signature Ed25519 non vérifiée' : 'Ed25519 signature does not verify', 'ui-pill-danger'],
                'unsigned' => [$isFr ? 'Aucune signature de l\'autorité' : 'No authority signature', 'ui-pill-warn'],
            ];
            $sigLabel = $sig ? ($sigLabels[$sig['state']] ?? null) : null;
        @endphp

        @if($sigLabel)
        <div class="mt-4 pt-4 border-t border-[#EFEBE2] dark:border-[#262B21]">
            <span class="ui-pill {{ $sigLabel[1] }}">{{ $sigLabel[0] }}</span>
            @if($sig['kid'])
            <p class="mt-2 ui-dt">{{ $isFr ? 'Identifiant de clé' : 'Key id' }}</p>
            <p class="mt-1 font-mono text-[10.5px] text-[#55524A] dark:text-[#B4B5A6] break-all">{{ $sig['kid'] }}</p>
            @endif
            {{-- The point of publishing the key: this check does not depend on
                 us being asked, or on our answer being believed. --}}
            <p class="ui-hint mt-2">
                {{ $isFr
                   ? 'Cette signature est vérifiable sans nous, hors ligne, avec la clé publique publiée sur'
                   : 'This signature is checkable without us, offline, against the public key published at' }}
                <a href="{{ url('/.well-known/jwks.json') }}" class="font-mono text-[#157A43] dark:text-[#339B56]">/.well-known/jwks.json</a>.
            </p>
        </div>
        @endif

        @if($doc['document_url'])
        <a href="{{ $doc['document_url'] }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Voir le document complet' : 'View the full document' }}
        </a>
        @endif
        @endif
    </div>
    @endif

    @if($product && in_array($status, ['valid', 'superseded'], true))
    @php
        $vName  = $isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr);
        $vCover = $product->images->firstWhere('is_cover', true) ?? $product->images->sortBy('sort_order')->first();
        $vBiz   = $product->business;
    @endphp
    <div class="ui-card mt-5">
        <div class="ui-card-head">
            <h2 class="ui-card-title">{{ $isFr ? 'Produit enregistré' : 'Registered product' }}</h2>
            <span class="ui-pill {{ $status === 'valid' ? 'ui-pill-ok' : 'ui-pill-warn' }} shrink-0">
                {{ $status === 'valid' ? ($isFr ? 'Valide' : 'Valid') : ($isFr ? 'Remplacé' : 'Superseded') }}
            </span>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            @if($vCover)
            <img src="{{ asset('storage/' . $vCover->file_path) }}" alt="{{ $vName }}"
                 class="w-full sm:w-[150px] h-[150px] object-cover rounded-xl border border-[#EFEBE2] dark:border-[#262B21] shrink-0">
            @endif
            <div class="min-w-0">
                <p class="text-[16px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $vName }}</p>
                @if($vBiz)
                <p class="mt-1 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">
                    {{ $isFr ? 'par' : 'by' }} <span class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $vBiz->name_fr }}</span>
                    @if(in_array($vBiz->verification_tier, ['verified','certified'], true))
                    <span class="ui-pill ui-pill-ok ml-1.5">{{ $isFr ? 'Vérifié' : 'Verified' }}</span>
                    @endif
                </p>
                @endif
                <dl class="ui-dl ui-dl--2 mt-3">
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Émis le' : 'Issued' }}</dt>
                        <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($cert->issued_at)->translatedFormat('d F Y') }}</dd>
                    </div>
                    @if($vBiz?->region)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Région' : 'Region' }}</dt>
                        <dd class="ui-dd">{{ $vBiz->region->name_fr }}</dd>
                    </div>
                    @endif
                </dl>

                {{-- The certificate itself prints only the leading characters of
                     these values, because it is a poster and a 64-character
                     string across it is unreadable. The full values belong here,
                     on the page whose whole job is checking, so that anyone
                     comparing two copies of a certificate has something
                     complete to compare. --}}
                <details class="mt-4">
                    <summary class="text-[12.5px] font-semibold text-[#157A43] dark:text-[#339B56] cursor-pointer">
                        {{ $isFr ? 'Empreintes complètes' : 'Full fingerprints' }}
                    </summary>
                    <div class="mt-2 space-y-2">
                        <div>
                            <p class="ui-dt">{{ $isFr ? 'Empreinte du contenu (SHA-256)' : 'Content hash (SHA-256)' }}</p>
                            <p class="mt-1 font-mono text-[10.5px] leading-relaxed text-[#55524A] dark:text-[#B4B5A6] break-all">{{ $cert->content_hash }}</p>
                        </div>
                        @if($cert->image_phash)
                        <div>
                            <p class="ui-dt">{{ $isFr ? 'Empreinte de l\'image' : 'Perceptual image hash' }}</p>
                            <p class="mt-1 font-mono text-[10.5px] text-[#55524A] dark:text-[#B4B5A6] break-all">{{ $cert->image_phash }}</p>
                        </div>
                        @endif
                        @if($cert->signature)
                        <div>
                            <p class="ui-dt">{{ $isFr ? 'Signature (HMAC-SHA256)' : 'Signature (HMAC-SHA256)' }}</p>
                            <p class="mt-1 font-mono text-[10.5px] leading-relaxed text-[#55524A] dark:text-[#B4B5A6] break-all">{{ $cert->signature }}</p>
                        </div>
                        @endif
                        <p class="ui-hint">
                            {{ $isFr
                               ? 'Recalculées à chaque vérification. Toute modification de la fiche ou de la photographie change ces valeurs et remplace le certificat.'
                               : 'Recomputed on every verification. Any change to the record or the photograph changes these values and supersedes the certificate.' }}
                        </p>
                    </div>
                </details>

                <a href="{{ route('product.certificate', ['slug' => $product->slug, 'lang' => $lang]) }}"
                   class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
                    {{ $isFr ? 'Voir le certificat complet' : 'View the full certificate' }}
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Said here as well as on the certificate, because this is the page a
         buyer reaches from a QR code with the object in their hand. --}}
    <div class="ui-card mt-5">
        <h2 class="ui-card-title">{{ $isFr ? 'Ce que cette vérification prouve' : 'What this check proves' }}</h2>
        <p class="mt-2 text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? 'Elle confirme qu\'un produit portant ces informations a bien été enregistré sur Artisan Hub 237 par l\'artisan indiqué, à la date affichée.'
               : 'It confirms that a product with these details was registered on Artisan Hub 237 by the artisan shown, on the date displayed.' }}
        </p>
        <p class="mt-3 text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? 'Elle ne prouve pas que l\'objet devant vous est celui qui figure sur les photographies. Comparez vous-même les images, les dimensions et les matériaux avant d\'acheter.'
               : 'It does not prove that the object in front of you is the one in the photographs. Compare the images, dimensions and materials yourself before buying.' }}
        </p>
    </div>
</main>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
