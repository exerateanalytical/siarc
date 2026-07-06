@php
    // ══ Public badge verification — landing page for every printed badge QR ══
    $isFr = ($lang ?? 'fr') === 'fr';
    $theme = [
        'valid'   => ['#157A43', '#EAF6EE', '#CFE8D8', 'check',        $isFr ? 'BADGE VALIDE' : 'VALID BADGE'],
        'blocked' => ['#C0010C', '#FDF0F0', '#F5CFCF', 'ban',          $isFr ? 'BADGE BLOQUÉ' : 'BADGE BLOCKED'],
        'unknown' => ['#C97A16', '#FDF9EA', '#F3E5B2', 'circle-help',  $isFr ? 'BADGE INCONNU' : 'UNKNOWN BADGE'],
    ][$state];
    [$c, $bg, $bd, $icon, $title] = $theme;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Vérification du badge' : 'Badge verification' }} {{ $code }} — SIARC 2026</title>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Poppins',system-ui,sans-serif;background:#EFEDE6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{width:100%;max-width:420px;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 16px 40px -16px rgba(6,43,21,.4)}
        .head{padding:20px;text-align:center;color:#fff;background:linear-gradient(160deg,#0B3A1E 0%,#052A15 100%)}
        .head b{font-size:19px;letter-spacing:-.2px}
        .head i.y{font-style:normal;color:#E6B201;font-weight:700}
        .head p{font-size:9px;font-weight:600;letter-spacing:1.4px;opacity:.75;margin-top:3px}
        .state{margin:20px;border-radius:16px;border:1px solid {{ $bd }};background:{{ $bg }};padding:22px;text-align:center}
        .state .dot{width:60px;height:60px;border-radius:50%;background:{{ $c }};display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
        .state h1{font-size:21px;font-weight:800;color:{{ $c }};letter-spacing:.4px}
        .state p{font-size:12px;color:#3B382F;margin-top:4px}
        dl{padding:0 22px 6px}
        dl div{display:flex;justify-content:space-between;gap:14px;padding:9px 0;border-bottom:1px dashed #EFEDE6;font-size:13px}
        dt{color:#8A857A}dd{font-weight:600;color:#131313;text-align:right}
        .foot{padding:16px 22px 22px;display:flex;gap:10px}
        .foot a{flex:1;text-align:center;font:600 12.5px 'Poppins',sans-serif;padding:11px;border-radius:12px;text-decoration:none}
        .foot .g{background:#157A43;color:#fff}
        .foot .o{border:1px solid #E6E3DB;color:#3B382F;background:#fff}
        .stamp{font-size:10.5px;color:#B0AB9F;text-align:center;padding-bottom:16px}
    </style>
</head>
<body>
    <main class="card">
        <div class="head">
            <b>SIARC <i class="y">2026</i></b>
            <p>{{ $isFr ? 'VÉRIFICATION OFFICIELLE DES BADGES' : 'OFFICIAL BADGE VERIFICATION' }}</p>
        </div>
        <div class="state">
            <span class="dot"><i data-lucide="{{ $icon }}" style="width:30px;height:30px;color:#fff"></i></span>
            <h1>{{ $title }}</h1>
            <p>
                @if($state === 'valid'){{ $isFr ? 'Ce badge est authentique et actif pour le SIARC 2026.' : 'This badge is authentic and active for SIARC 2026.' }}
                @elseif($state === 'blocked'){{ $isFr ? 'Ce badge a été déclaré perdu ou révoqué. Merci de le remettre à un agent.' : 'This badge was reported lost or revoked. Please hand it to a staff member.' }}
                @else{{ $isFr ? "Ce code ne correspond à aucun badge SIARC 2026." : 'This code does not match any SIARC 2026 badge.' }}
                @endif
            </p>
        </div>
        <dl>
            <div><dt>{{ $isFr ? 'Code vérifié' : 'Verified code' }}</dt><dd>{{ $code }}</dd></div>
            @if($holder)
                <div><dt>{{ $isFr ? 'Titulaire' : 'Holder' }}</dt><dd>{{ $holder['name'] }}</dd></div>
                <div><dt>Type</dt><dd>{{ $holder['type'] }}</dd></div>
                <div><dt>Badge</dt><dd>{{ $holder['code'] }}</dd></div>
                @if(in_array($holder['kind'], ['visitor','exhibitor']))
                <div><dt>{{ $isFr ? 'Sur site' : 'On site' }}</dt><dd style="color:{{ $holder['checked_in'] ? '#157A43' : '#8A857A' }}">{{ $holder['checked_in'] ? ($isFr ? 'Oui — enregistré' : 'Yes — checked in') : ($isFr ? 'Pas encore' : 'Not yet') }}</dd></div>
                @endif
                <div><dt>{{ $isFr ? 'Validité' : 'Validity' }}</dt><dd>27/07 – 05/08/2026</dd></div>
            @endif
        </dl>
        <div class="foot">
            <a class="o" href="{{ route('siarc.home', ['lang' => $lang]) }}">{{ $isFr ? 'Site du salon' : 'Salon website' }}</a>
            @if($state === 'valid' && $holder)
            <a class="g" href="{{ route('siarc.badge.print', ['code' => $holder['code'], 'lang' => $lang]) }}">{{ $isFr ? 'Voir le badge' : 'View badge' }}</a>
            @endif
        </div>
        <p class="stamp">{{ $isFr ? 'Vérifié le' : 'Verified on' }} {{ now()->format('d/m/Y H:i') }} · Musée National de Yaoundé</p>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
