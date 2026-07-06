@php
    use Illuminate\Support\Facades\Route as R;
    $lang = request()->query('lang', request()->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr','en']) ? $lang : 'fr';
    $isFr = $lang === 'fr';
    $u = session('siac_user') ?? [];
    $uName = 'Jude Nshome'; // design-verbatim operator identity for the accreditation console
    $uRole = 'Administrateur';
    $navHref = fn($route) => R::has($route) ? route($route, ['lang'=>$lang]) : route('siarc.admin.accred.templates', ['lang'=>$lang]);

    // NAVIGATION section (verbatim from the accreditation designs)
    $accNavTop = [
        ['Tableau de bord', 'home',           'siarc.admin.dashboard',   false],
        ['Exposants',       'store',          'siarc.admin.exhibitors',  false],
        ['Pavillons',       'landmark',       'siarc.admin.pavilions',   false],
        ['Stands',          'grid-3x3',       'siarc.admin.stands',      false],
        ['Programme',       'calendar-days',  'siarc.admin.programme',   false],
        ['Intervenants',    'users',          'siarc.admin.speakers',    false],
        ['Visiteurs',       'users-round',    'siarc.admin.visitors',    false],
    ];
    // ACCREDITATION sub-items — $accActive names the highlighted entry
    $accSub = [
        ['Demandes',           'siarc.admin.checkin'],
        ['Badges',             'siarc.admin.badges'],
        ['Badge Templates',    'siarc.admin.accred.templates'],
        ['Types de Badges',    'siarc.admin.accred.types'],
        ['Print Queue',        'siarc.admin.accred.queue'],
        ['Bulk Printing',      'siarc.admin.accred.bulk'],
        ['Réimpressions',      'siarc.admin.accred.reprints'],
        ['Historique',         'siarc.admin.accred.reprints'],
        ['QR Code Generation', 'siarc.admin.accred.qr'],
        ['RFID Support',       'siarc.admin.accred.rfid'],
        ['QR Scanner',         'siarc.admin.accred.qrscanner'],
    ];
    $accNavBottom = [
        ['Lecteurs & Accès',    'scan-line',      'siarc.admin.entry'],
        ['B2B Matchmaking',     'handshake',      'siarc.admin.b2b'],
        ['Opérations',          'settings-2',     'siarc.admin.live'],
        ['Rapports & Analyses', 'bar-chart-3',    'siarc.admin.analytics'],
        ['Paramètres',          'settings',       'siarc.admin.mode'],
    ];
    // Per-page chrome, keyed by route name (View::share from bodies reaches the
    // layout one render too late, so the layout owns this map).
    $chrome = [
        'siarc.admin.accred.templates' => ['active'=>'Badge Templates',    'art'=>'accred-art-templates.png',  'top'=>['fr'=>true,'logoRight'=>true,'avatar'=>false]],
        'siarc.admin.accred.types'     => ['active'=>'Types de Badges',    'art'=>'accred-art-types.png',      'top'=>['search'=>true,'scope'=>'#btScope','searchPh'=>'Rechercher (nom, badge, ID, type....)','help'=>true]],
        'siarc.admin.accred.preview'   => ['active'=>'Badge Templates',    'art'=>'accred-art-preview.png',    'top'=>['search'=>true,'searchPh'=>'Rechercher (nom, badge, job ID...)','help'=>true,'fr'=>true]],
        'siarc.admin.accred.queue'     => ['active'=>'Print Queue',        'art'=>'accred-art-printqueue.png', 'top'=>['search'=>true,'scope'=>'#pqScope','searchPh'=>'Rechercher (nom, badge, job ID)...']],
        'siarc.admin.accred.bulk'      => ['active'=>'Bulk Printing',      'art'=>'accred-art-bulk.png',       'top'=>['search'=>true,'scope'=>'#bpScope','searchPh'=>'Rechercher un visiteur, exposant...','help'=>true]],
        'siarc.admin.accred.qr'        => ['active'=>'QR Code Generation', 'art'=>'accred-art-qr.png',         'top'=>['fr'=>true]],
        'siarc.admin.accred.rfid'      => ['active'=>'RFID Support',       'art'=>'accred-art-rfid.png',       'top'=>['search'=>true,'scope'=>'#rfScope','searchPh'=>'Rechercher (nom, badge, ID, type...)','help'=>true]],
        'siarc.admin.accred.rfid.card' => ['active'=>'RFID Support',       'art'=>'accred-art-rfiddetail.png', 'top'=>['search'=>true,'searchPh'=>'Rechercher (nom, badge ID, RFID, email...)','help'=>true]],
        'siarc.admin.accred.rfid.write'=> ['active'=>'RFID Support',       'art'=>'accred-art-types.png',      'top'=>['search'=>true,'searchPh'=>'Rechercher (nom, badge, RFID, ID...)','help'=>true,'fr'=>true]],
        'siarc.admin.accred.reprints'  => ['active'=>'Réimpressions',      'art'=>'accred-art-types.png',      'top'=>['search'=>true,'scope'=>'#rpScope','searchPh'=>'Rechercher (nom, badge ID, RFID, email...)','help'=>true]],
        'siarc.admin.accred.qrscanner' => ['active'=>'QR Scanner',         'art'=>'accred-art-types.png',      'top'=>['help'=>true]],
    ];
    // Spec-driven operations pages share one chrome variant.
    foreach (array_keys(config('siarc_accred_ops', [])) as $opsRoute) {
        $chrome[$opsRoute] = $chrome[$opsRoute] ?? ['active'=>'', 'art'=>'accred-art-rfid.png', 'top'=>['search'=>true,'scope'=>'#opsScope','searchPh'=>'Rechercher...','help'=>true]];
    }
    $pc = $chrome[request()->route()?->getName() ?? ''] ?? ['active'=>'', 'art'=>'accred-art-templates.png', 'top'=>[]];
    $accActive  = $pc['active'];
    $accArt     = $pc['art'];
    $accTop     = array_merge(['search'=>false,'searchPh'=>'Rechercher (nom, badge, job ID)...','scope'=>null,'help'=>false,'fr'=>false,'logoRight'=>false,'avatar'=>true], $pc['top']);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($sTitle ?? 'Accréditation') }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { siarc:{green:'#157A43',dark:'#0B3A1E',darker:'#042B15',gold:'#E6B201',ochre:'#C97A16',red:'#C0010C'} },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    @stack('head')
    <style>
        body{font-family:'Poppins',system-ui,sans-serif}
        html,body{overflow-x:clip}
        #si-side{display:none}
        #si-side.open{display:flex;position:fixed;inset:0 auto 0 0;width:223px;z-index:60}
        @media (min-width:1024px){#si-side,#si-side.open{display:flex;position:sticky;top:0;height:100vh;width:223px}}
        .acc-item{display:flex;align-items:center;gap:11px;padding:9px 14px;font-size:13px;font-weight:500;color:#C9D6CD;border-radius:0;transition:color .15s,background .15s}
        .acc-item:hover{color:#fff;background:rgba(255,255,255,.06)}
        .acc-item.on{color:#E6B201;background:rgba(230,178,1,.10);border-left:3px solid #E6B201;padding-left:11px;font-weight:600}
        .acc-sub{display:block;padding:6.5px 14px 6.5px 0;font-size:12.5px;color:#B7C6BC;transition:color .15s}
        .acc-sub:hover{color:#fff}
        .acc-sub.on{color:#E6B201;font-weight:600;background:rgba(230,178,1,.10);border-radius:6px;padding-left:10px;margin-left:-10px}
    </style>
</head>
<body class="bg-[#F6F4EF] text-[#1D1B16] antialiased">
{{-- kente strip across the very top --}}
<div class="h-[14px] w-full" style="background:url('{{ asset('images/siarc/accred-kente.png') }}') repeat-x;background-size:auto 100%"></div>
<div class="flex min-h-screen">

    {{-- ══ SIDEBAR (223px, dark green, NAVIGATION + ACCREDITATION) ══ --}}
    <aside id="si-side" class="siarc-scroll flex-col shrink-0 text-white overflow-y-auto" style="background:linear-gradient(180deg,#07351C 0%,#052A15 100%)">
        <div class="px-4 pt-4 pb-3">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
        </div>
        <div class="mx-4 mb-3 flex items-center gap-2 border-y border-white/10 py-2.5">
            <span class="inline-block w-[22px] h-[15px] rounded-[2px] overflow-hidden shrink-0" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>
            <span class="text-[11px] font-semibold tracking-wide text-white/90">YAOUNDÉ, CAMEROUN</span>
        </div>

        <p class="px-4 mb-1.5 text-[10px] font-bold tracking-[0.16em] text-white/35">NAVIGATION</p>
        <nav>
            @foreach($accNavTop as [$lbl,$icon,$route])
                <a href="{{ $navHref($route) }}" class="acc-item">
                    <i data-lucide="{{ $icon }}" class="w-[16px] h-[16px] shrink-0" style="stroke-width:1.8"></i>{{ $lbl }}
                </a>
            @endforeach
        </nav>

        <p class="px-4 mt-4 mb-1.5 text-[10px] font-bold tracking-[0.16em] text-white/35">ACCREDITATION</p>
        <a href="{{ $navHref('siarc.admin.accred.templates') }}" class="acc-item {{ $accActive !== '' ? 'on' : '' }}">
            <i data-lucide="id-card" class="w-[16px] h-[16px] shrink-0" style="stroke-width:1.8"></i>Accréditation
            <i data-lucide="{{ $accActive !== '' ? 'chevron-up' : 'chevron-down' }}" class="w-3.5 h-3.5 ml-auto"></i>
        </a>
        <div class="pl-[38px] pr-3 pt-1 pb-1 border-l border-white/10 ml-[21px] space-y-0">
            @foreach($accSub as [$lbl,$route])
                <a href="{{ $navHref($route) }}" class="acc-sub {{ $accActive === $lbl ? 'on' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>

        <nav class="mt-2">
            @foreach($accNavBottom as [$lbl,$icon,$route])
                <a href="{{ $navHref($route) }}" class="acc-item">
                    <i data-lucide="{{ $icon }}" class="w-[16px] h-[16px] shrink-0" style="stroke-width:1.8"></i>{{ $lbl }}
                </a>
            @endforeach
        </nav>

        {{-- per-page sidebar footer artwork (cropped from the design) --}}
        <div class="mt-auto">
            @if($accArt === 'accred-art-templates.png')
                <img src="{{ asset('images/siarc/'.$accArt) }}" alt="" class="w-full block">
                <div class="flex items-center gap-2.5 px-4 py-3 bg-[#04240F]">
                    <img src="{{ asset('images/siarc/accred-jude-2.png') }}" alt="" class="w-9 h-9 rounded-full object-cover">
                    <div class="leading-tight">
                        <p class="text-[12.5px] font-semibold text-white">{{ $uName }}</p>
                        <p class="text-[10.5px] text-white/60">{{ $uRole }}</p>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-white/60 ml-auto"></i>
                </div>
            @else
                <img src="{{ asset('images/siarc/'.$accArt) }}" alt="" class="w-full block">
            @endif
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-40 bg-white border-b border-[#ECEAE3]">
            <div class="h-[62px] px-5 sm:px-7 flex items-center gap-4">
                <button id="si-burger" data-toast="Menu" class="w-9 h-9 -ml-1 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center shrink-0">
                    <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
                </button>
                @if($accTop['search'])
                <div class="hidden md:flex items-center gap-2.5 flex-1 max-w-[420px] ml-auto lg:ml-[38%] rounded-full border border-[#E9E6DE] bg-[#FBFAF7] px-4 h-[40px]">
                    <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] shrink-0"></i>
                    <input type="text" @if($accTop['scope']) data-filter="{{ $accTop['scope'] }}" @endif placeholder="{{ $accTop['searchPh'] }}" class="w-full bg-transparent text-[12.5px] text-[#3B382F] placeholder-[#B0AB9F] outline-none">
                </div>
                @endif
                <div class="flex items-center gap-3 ml-auto shrink-0">
                    @if($accTop['fr'])
                    <button data-toast="Français / English" class="flex items-center gap-1 text-[13px] font-medium text-[#3B382F]">FR <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
                    @endif
                    <button data-toast="12 notifications non lues — centre de notifications à venir" class="relative w-9 h-9 rounded-full hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="bell" class="w-[18px] h-[18px] text-[#3B382F]"></i>
                        <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-[16px] px-1 rounded-full bg-siarc-red text-white text-[9.5px] font-bold flex items-center justify-center">12</span>
                    </button>
                    @if($accTop['help'])
                    <button data-toast="Centre d'aide à venir…" class="w-9 h-9 rounded-full border border-[#E9E6DE] hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="circle-help" class="w-[18px] h-[18px] text-[#3B382F]"></i>
                    </button>
                    @endif
                    @if($accTop['logoRight'])
                    <span class="hidden sm:flex items-center gap-2.5 pl-2">
                        <svg width="30" height="33" viewBox="0 0 40 44" fill="none">
                            <circle cx="20" cy="7.5" r="5" fill="#E6B201"/>
                            <path d="M20 14 L6 6" stroke="#C0010C" stroke-width="4.4" stroke-linecap="round"/>
                            <path d="M20 14 L34 9" stroke="#157A43" stroke-width="4.4" stroke-linecap="round"/>
                            <path d="M20 13 C25 20 25 28 20 30 C15 28 15 20 20 13 Z" fill="#0F4824"/>
                            <path d="M20 29 L11 41" stroke="#C97A16" stroke-width="4.4" stroke-linecap="round"/>
                            <path d="M20 29 L29 41" stroke="#14652F" stroke-width="4.4" stroke-linecap="round"/>
                        </svg>
                        <span class="leading-tight text-left">
                            <span class="block text-[15px] font-extrabold tracking-tight"><span class="text-[#0F4824]">SIARC</span> <span class="text-[#C97A16]">2026</span></span>
                            <span class="block text-[8.5px] font-semibold tracking-[0.08em] text-[#8A857A]">YAOUNDÉ, CAMEROUN</span>
                        </span>
                        <span class="inline-block w-[20px] h-[13px] rounded-[2px]" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>
                    </span>
                    @elseif($accTop['avatar'])
                    <span class="flex items-center gap-2.5 pl-1">
                        <img src="{{ asset('images/siarc/accred-jude.png') }}" alt="" class="w-9 h-9 rounded-full object-cover">
                        <span class="hidden sm:block leading-tight">
                            <span class="block text-[13px] font-semibold text-[#1D1B16]">{{ $uName }}</span>
                            <span class="block text-[11px] text-[#8A857A]">{{ $uRole }}</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#B4B0A6] hidden sm:block"></i>
                    </span>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1 px-5 sm:px-7 py-6">
            @yield('content')
        </main>

        <footer class="px-5 sm:px-7 py-4 flex items-center justify-between text-[11.5px] text-[#8A857A]">
            <span>© 2026 SIARC - Salon International de l'Artisanat du Cameroun. Tous droits réservés.</span>
            <span class="flex items-center gap-2.5">Propulsé par Cameroun Digital
                <i data-lucide="component" class="w-6 h-6 text-[#3B382F]"></i>
            </span>
        </footer>
    </div>
</div>
<div id="si-overlay" class="hidden fixed inset-0 bg-black/45 z-50 lg:hidden"></div>
<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-burger'),s=document.getElementById('si-side'),o=document.getElementById('si-overlay');
        function toggle(){s.classList.toggle('open');o.classList.toggle('hidden');}
        if(b)b.addEventListener('click',function(e){ if(window.innerWidth<1024){e.stopImmediatePropagation();toggle();} });
        if(o)o.addEventListener('click',toggle);
    })();
</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
@stack('scripts')
</body>
</html>
