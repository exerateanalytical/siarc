@php
    // ══ SIARC 2026 printable badge — visitor / exhibitor / VIP / speaker ══
    // Card prints at the exact physical badge size (54 × 85.6 mm, vertical).
    $isFr = ($lang ?? 'fr') === 'fr';
    $ev = function_exists('siarcEvent') ? siarcEvent() : null;
    $validity = '27 JUILLET - 05 AOÛT 2026';
    $venue = 'MUSÉE NATIONAL DE YAOUNDÉ';

    $themes = [
        // header gradient, accent (role color), footer gradient — per badge family
        'visitor'   => ['grad' => 'linear-gradient(160deg,#0B3A1E 0%,#07351C 60%,#052A15 100%)', 'accent' => '#E6B201', 'role' => $isFr ? 'VISITEUR' : 'VISITOR'],
        'exhibitor' => ['grad' => 'linear-gradient(160deg,#7A4A0E 0%,#9A5F12 55%,#6B400A 100%)', 'accent' => '#FFD98A', 'role' => $isFr ? 'EXPOSANT' : 'EXHIBITOR'],
        'vip'       => ['grad' => 'linear-gradient(160deg,#8A6A00 0%,#B08900 55%,#7A5E00 100%)', 'accent' => '#FFF2C2', 'role' => 'VIP'],
        'speaker'   => ['grad' => 'linear-gradient(160deg,#3B2470 0%,#4C2F8F 55%,#2F1C5C 100%)', 'accent' => '#D9C9FF', 'role' => $isFr ? 'INTERVENANT' : 'SPEAKER'],
    ];
    $t = $themes[$type] ?? $themes['visitor'];

    // ── Field mapping per holder ─────────────────────────────────────────────
    if ($type === 'exhibitor') {
        $name = $x->company ?? 'Exposant';
        $code = $x->badge_code ?? ('EXP-'.str_pad((string) $x->id, 5, '0', STR_PAD_LEFT));
        $qrData = $x->qr_token ?? $code;
        $lines = array_filter([
            [$isFr ? 'Stand' : 'Booth', $x->booth_number ?? '—'],
            [$isFr ? 'Pavillon' : 'Pavilion', $x->pavilion ?? '—'],
            [$isFr ? 'Catégorie' : 'Category', $x->category ?? ($isFr ? 'Artisanat' : 'Crafts')],
            [$isFr ? 'Accès' : 'Access', $isFr ? 'Pavillons + Stands' : 'Pavilions + Booths'],
        ]);
        $country = 'CAMEROUN';
        $org = null;
    } elseif ($type === 'speaker') {
        $name = trim(($s->first_name ?? '').' '.($s->last_name ?? '')) ?: ($s->name ?? 'Intervenant');
        $code = 'SPK-'.str_pad((string) $s->id, 5, '0', STR_PAD_LEFT);
        $qrData = $code;
        $lines = array_filter([
            ['Session', Str::limit($session->title_fr ?? '—', 34)],
            [$isFr ? 'Scène' : 'Stage', $session->venue_fr ?? '—'],
            [$isFr ? 'Horaire' : 'Time', $session && $session->starts_at ? \Illuminate\Support\Carbon::parse($session->starts_at)->format('d/m · H\hi') : '—'],
            [$isFr ? 'Accès' : 'Access', $isFr ? 'Zones de conférences + VIP' : 'Conference zones + VIP'],
        ]);
        $country = strtoupper($s->country ?? 'CAMEROUN');
        $org = $s->organization ?? null;
    } else { // visitor & vip share the visitors table
        $name = trim(($v->first_name ?? '').' '.($v->last_name ?? ''));
        $code = $v->badge_code;
        $qrData = $v->qr_token ?? $code;
        $org = $v->organization ?? null;
        $country = strtoupper($v->country ?? 'CAMEROUN');
        $lines = $type === 'vip'
            ? array_filter([
                [$isFr ? 'Niveau VIP' : 'VIP level', $isFr ? 'Officiel' : 'Official'],
                [$isFr ? 'Escorte' : 'Escort', $isFr ? 'Autorisée (2 pers.)' : 'Allowed (2 pers.)'],
                ['Salon VIP', $isFr ? 'Accès lounge + parking' : 'Lounge + parking access'],
                [$isFr ? 'Placement' : 'Seating', $isFr ? 'Sièges réservés' : 'Reserved seating'],
              ])
            : array_filter([
                ['Type', ucfirst($v->type ?? 'visitor')],
                [$isFr ? "Niveau d'accès" : 'Access level', 'Standard'],
                [$isFr ? 'Halls' : 'Hall access', $isFr ? 'Pavillons & Expositions' : 'Pavilions & Exhibitions'],
                [$isFr ? 'Urgence' : 'Emergency', '+237 6 78 90 12 34'],
              ]);
    }
    $verify = 'https://siarc2026.cm/verify/'.$code;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Badge' : 'Badge' }} {{ $code }} — SIARC 2026</title>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Poppins',system-ui,sans-serif;background:#EFEDE6;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;padding:24px}
        .badge{width:54mm;height:85.6mm;border-radius:3mm;overflow:hidden;background:#fff;box-shadow:0 10px 28px -10px rgba(6,43,21,.45);display:flex;flex-direction:column}
        .head{position:relative;padding:3mm 2mm 6.5mm;text-align:center;color:#fff;background:{{ $t['grad'] }}}
        .head .brand{display:flex;align-items:center;justify-content:center;gap:1.4mm}
        .head .brand b{font-size:5mm;letter-spacing:-.2mm;line-height:1}
        .head .brand i{font-style:normal;font-weight:700;font-size:3.2mm;color:{{ $t['accent'] }}}
        .head p{font-size:1.55mm;font-weight:600;letter-spacing:.35mm;opacity:.78;margin-top:.6mm}
        .curve{position:absolute;bottom:-0.2mm;left:0;width:100%;height:5mm}
        .photo{margin:-7mm auto 0;position:relative;z-index:2;width:15.5mm;height:15.5mm;border-radius:50%;border:1mm solid #fff;object-fit:cover;display:block;background:#EFEDE6}
        .logo-tile{margin:-7mm auto 0;position:relative;z-index:2;width:15.5mm;height:15.5mm;border-radius:2.4mm;border:1mm solid #fff;background:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:6mm;color:#7A4A0E}
        .mid{flex:1;text-align:center;padding:1mm 3mm 0}
        .name{font-size:3.4mm;font-weight:800;color:#131313;line-height:1.12}
        .role{font-size:2.4mm;font-weight:700;letter-spacing:.25mm;color:{{ ['visitor'=>'#157A43','exhibitor'=>'#9A5F12','vip'=>'#8A6A00','speaker'=>'#4C2F8F'][$type] }};margin-top:.5mm}
        .org{font-size:1.8mm;font-weight:600;color:#4A473F;margin-top:.4mm}
        .country{display:flex;align-items:center;justify-content:center;gap:1.2mm;border-top:.2mm solid #EDEBE4;margin-top:1.2mm;padding-top:1.2mm;font-size:2mm;font-weight:700;color:#3B382F}
        .flag{width:3.4mm;height:2.2mm;border-radius:.3mm;background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)}
        .fields{margin-top:1.2mm;text-align:left;padding:0 1mm}
        .fields div{display:flex;justify-content:space-between;gap:2mm;font-size:1.85mm;padding:.45mm 0;border-bottom:.15mm dashed #EFEDE6}
        .fields dt{color:#8A857A;font-weight:600;white-space:nowrap}
        .fields dd{color:#131313;font-weight:700;text-align:right}
        .qr{display:flex;flex-direction:column;align-items:center;margin-top:1.2mm}
        .qr #qrbox{width:14mm;height:14mm}
        .qr #qrbox img,.qr #qrbox canvas{width:14mm !important;height:14mm !important}
        .qr small{font-size:1.9mm;font-weight:700;color:#3B382F;margin-top:.7mm;letter-spacing:.2mm}
        .foot{text-align:center;color:#fff;padding:1.4mm 1mm;background:{{ $t['grad'] }}}
        .foot b{display:block;font-size:1.9mm;letter-spacing:.25mm}
        .foot span{display:block;font-size:1.6mm;font-weight:600;opacity:.82}
        .toolbar{display:flex;gap:10px}
        .toolbar button,.toolbar a{font:600 13px 'Poppins',sans-serif;padding:10px 18px;border-radius:10px;border:1px solid #D8E5DC;background:#157A43;color:#fff;cursor:pointer;text-decoration:none}
        .toolbar .ghost{background:#fff;color:#157A43}
        @media print{
            body{background:#fff;padding:0;display:block}
            .toolbar{display:none}
            .badge{box-shadow:none;margin:0}
            @page{size:54mm 85.6mm;margin:0}
        }
    </style>
</head>
<body>
    <div class="badge">
        <div class="head">
            <div class="brand">
                <svg width="14" height="15" viewBox="0 0 40 44" fill="none">
                    <circle cx="20" cy="7.5" r="5" fill="#E6B201"/>
                    <path d="M20 14 L6 6" stroke="#C0010C" stroke-width="4.4" stroke-linecap="round"/>
                    <path d="M20 14 L34 9" stroke="#157A43" stroke-width="4.4" stroke-linecap="round"/>
                    <path d="M20 13 C25 20 25 28 20 30 C15 28 15 20 20 13 Z" fill="#fff"/>
                    <path d="M20 29 L11 41" stroke="#C97A16" stroke-width="4.4" stroke-linecap="round"/>
                    <path d="M20 29 L29 41" stroke="#14652F" stroke-width="4.4" stroke-linecap="round"/>
                </svg>
                <span><b>SIARC</b> <i>2026</i></span>
            </div>
            <p>SALON INTERNATIONAL DE L'ARTISANAT DU CAMEROUN</p>
            <svg class="curve" viewBox="0 0 100 12" preserveAspectRatio="none"><path d="M0 12 L0 6 Q50 -6 100 6 L100 12 Z" fill="#fff"/></svg>
        </div>

        @if($type === 'exhibitor')
            <span class="logo-tile">{{ strtoupper(mb_substr($name, 0, 1)) }}</span>
        @else
            <img class="photo" src="{{ asset('images/siarc/accred-portrait.png') }}" alt="">
        @endif

        <div class="mid">
            <p class="name">{{ strtoupper($name) }}</p>
            <p class="role">{{ $t['role'] }}</p>
            @if(!empty($org))<p class="org">{{ Str::limit($org, 40) }}</p>@endif
            <div class="country"><span class="flag"></span>{{ $country }}</div>
            <dl class="fields">
                @foreach($lines as [$k,$vv])
                <div><dt>{{ $k }}</dt><dd>{{ $vv }}</dd></div>
                @endforeach
            </dl>
            <div class="qr">
                <span id="qrbox"></span>
                <small>{{ $code }}</small>
            </div>
        </div>

        <div class="foot">
            <b>{{ $validity }}</b>
            <span>{{ strtoupper($venue) }}</span>
        </div>
    </div>

    <div class="toolbar">
        <button onclick="window.print()">{{ $isFr ? 'Imprimer le badge' : 'Print badge' }}</button>
        <a class="ghost" href="{{ route('siarc.home', ['lang' => $lang]) }}">{{ $isFr ? 'Retour au salon' : 'Back to the salon' }}</a>
    </div>

    <script>
        new QRCode(document.getElementById('qrbox'), {
            text: @json($verify),
            width: 160, height: 160,
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>
