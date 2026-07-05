@php
    $isFr = $lang === 'fr';

    // Personalisation: real business data patches the design's demo values;
    // without a business the certificate renders exactly as designed.
    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $fmtDate = function ($date) use ($frMonths) {
        $d = \Illuminate\Support\Carbon::parse($date);
        return $d->day . ' ' . $frMonths[$d->month] . ' ' . $d->year;
    };

    $personalized = (bool) $business;
    if ($personalized) {
        $certName    = mb_strtoupper($business->name_fr);
        $seed        = md5('gvn-cert-' . $business->id);
        $certNumber  = $business->certificate_no ?? ('GVN-' . \Illuminate\Support\Carbon::parse($business->created_at)->year . '-' . str_pad((string) (hexdec(substr($seed, 0, 6)) % 10000000), 7, '0', STR_PAD_LEFT));
        $certCode    = strtoupper(substr($seed, 6, 4) . '-' . substr($seed, 10, 4) . '-' . substr($seed, 14, 4));
        $certStart   = $fmtDate($business->certificate_issued_at ?? $business->created_at);
        $certEnd     = $fmtDate($business->certificate_expires_at ?? \Illuminate\Support\Carbon::parse($business->created_at)->addYear()->subDay());
    } else {
        $certNumber  = 'GVN-2025-0002587';
    }

    $verifyUrl = route('certificate.verify', ['numero' => $certNumber]);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Certificat d\'adhésion — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Membership certificate — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; background: #E9E7E1; }
        .cert-canvas { position: relative; width: min(1536px, 96vw); aspect-ratio: 1536 / 1024; margin: 0 auto; }
        .cert-canvas img.cert-bg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .ov { position: absolute; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; }
            .cert-canvas { width: 100%; }
            @page { size: landscape; margin: 0; }
        }
    </style>
</head>
<body class="antialiased">

<div class="no-print max-w-[1536px] mx-auto flex items-center justify-between gap-3 px-4 pt-5 pb-4">
    <a href="/tableau-de-bord" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#1B4332] hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ $isFr ? 'Retour au tableau de bord' : 'Back to dashboard' }}
    </a>
    <div class="flex items-center gap-3">
        <a href="{{ $verifyUrl }}" class="inline-flex items-center gap-2 border border-[#1B4332] text-[#1B4332] text-[13px] font-semibold px-4 py-2.5 rounded-lg hover:bg-[#1B4332]/5 transition-colors">
            <i data-lucide="shield-check" class="w-4 h-4"></i>
            {{ $isFr ? 'Vérifier en ligne' : 'Verify online' }}
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 border border-[#1B4332] text-[#1B4332] text-[13px] font-semibold px-4 py-2.5 rounded-lg hover:bg-[#1B4332]/5 transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i>
            {{ $isFr ? 'Télécharger (PDF)' : 'Download (PDF)' }}
        </button>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#0A331C] hover:bg-[#164C28] text-white text-[13px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
            <i data-lucide="printer" class="w-4 h-4"></i>
            {{ $isFr ? 'Imprimer' : 'Print' }}
        </button>
    </div>
</div>

<div class="cert-canvas mb-8 shadow-xl rounded-[14px] overflow-hidden">
    <img src="{{ asset('images/landing/cert-full.png') }}" alt="{{ $isFr ? 'Certificat d\'adhésion' : 'Membership certificate' }}" class="cert-bg">

    <!-- Live QR over the printed one (encodes the real verification URL) -->
    <div class="ov flex items-center justify-center bg-white rounded-[4px]" style="left:84.55%; top:27.15%; width:9.9%; height:14.95%">
        <div id="cert-qr"></div>
    </div>

    @if($personalized)
    <!-- Real business data patched over the design's demo values -->
    <div class="ov flex items-end justify-center" style="left:24%; top:33.9%; width:34%; height:5.4%; background:#FCFBF6">
        <span class="font-bold text-[#1B1B18] leading-none whitespace-nowrap" style="font-size:min(2.55vw,39px)">{{ $certName }}</span>
    </div>
    <div class="ov flex items-center" style="left:13.7%; top:56.6%; width:13.5%; height:2.6%; background:#FDFCF7">
        <span class="font-semibold text-[#1B1B18] whitespace-nowrap" style="font-size:min(1.05vw,16px)">{{ $certNumber }}</span>
    </div>
    <div class="ov flex items-center" style="left:12.9%; top:65.2%; width:12%; height:2.2%; background:#FCFBF6">
        <span class="font-medium text-[#3B382F] whitespace-nowrap" style="font-size:min(0.9vw,14px)">{{ $certCode }}</span>
    </div>
    <div class="ov flex items-center" style="left:14%; top:73.9%; width:9%; height:2.1%; background:#FCFBF6">
        <span class="font-semibold text-[#1B1B18] whitespace-nowrap" style="font-size:min(0.8vw,12.5px)">{{ $certStart }}</span>
    </div>
    <div class="ov flex items-center" style="left:24.3%; top:73.9%; width:9%; height:2.1%; background:#FCFBF6">
        <span class="font-semibold text-[#1B1B18] whitespace-nowrap" style="font-size:min(0.8vw,12.5px)">{{ $certEnd }}</span>
    </div>
    @endif
</div>

<script>
    lucide.createIcons();
    new QRCode(document.getElementById('cert-qr'), {
        text: @json($verifyUrl),
        width: Math.round(Math.min(1536, window.innerWidth * 0.96) * 0.088),
        height: Math.round(Math.min(1536, window.innerWidth * 0.96) * 0.088),
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
</body>
</html>
