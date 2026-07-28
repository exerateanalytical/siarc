@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $cert    = $result['certificate'] ?? null;
    $product = $result['product'] ?? null;
    $status  = $result['status'] ?? null;

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
<body class="bg-[#FEFEFE] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[720px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <h1 class="font-serif text-[26px] sm:text-[32px] font-bold text-[#1D1B16] leading-tight">
        {{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }}
    </h1>
    <p class="mt-2.5 text-[13.5px] text-[#55524A] leading-relaxed max-w-[520px]">
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
                 class="w-full sm:w-[150px] h-[150px] object-cover rounded-xl border border-[#EFEBE2] shrink-0">
            @endif
            <div class="min-w-0">
                <p class="text-[16px] font-bold text-[#1D1B16]">{{ $vName }}</p>
                @if($vBiz)
                <p class="mt-1 text-[12.5px] text-[#55524A]">
                    {{ $isFr ? 'par' : 'by' }} <span class="font-semibold text-[#1D1B16]">{{ $vBiz->name_fr }}</span>
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
        <p class="mt-2 text-[12.5px] text-[#3A3A35] leading-relaxed">
            {{ $isFr
               ? 'Elle confirme qu\'un produit portant ces informations a bien été enregistré sur Artisan Hub 237 par l\'artisan indiqué, à la date affichée.'
               : 'It confirms that a product with these details was registered on Artisan Hub 237 by the artisan shown, on the date displayed.' }}
        </p>
        <p class="mt-3 text-[12.5px] text-[#3A3A35] leading-relaxed">
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
