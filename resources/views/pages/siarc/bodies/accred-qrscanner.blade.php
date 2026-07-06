@php
    // ══ QR Scanner — verbatim from the approved designs. The tab bar and the
    //    ?etat= state variants reproduce the design set: camera (dark scan view),
    //    granted (Accès accordé), refused (Accès refusé), manual (Saisie
    //    manuelle), validation (Validation réussie), offline (Mode hors ligne). ══
    $state = in_array(($qrState ?? 'camera'), ['camera','granted','refused','manual','validation','offline']) ? ($qrState ?? 'camera') : 'camera';
    $self = fn($etat) => route('siarc.admin.accred.qrscanner', ['lang' => $lang, 'etat' => $etat]);
    $visitor = [
        ['user-round','Nom complet','Jean Paul Essomba'], ['id-card','Type de badge','Visiteur'],
        ['copy','Badge ID','VIS-000356'], ['qr-code','QR / RFID','04 A3 B2 7F 91 6E 80'],
        ['landmark','Pavillon / Zone','Pavillon Officiel'], ['key-round',"Niveau d'accès",'Standard'],
        ['calendar','Validité','27/07/2026 - 05/08/2026'], ['globe','Pays','Cameroun'],
    ];
    $offline = $state === 'offline';

    // Real scan hand-off from the route (?code=...): overrides the design's demo visitor.
    $scan = $qrScan ?? null;
    $live = $scan && ($scan['holder'] ?? null);
    if ($live) {
        $hd = $scan['holder'];
        $visitor = [
            ['user-round','Nom complet',$hd['name']], ['id-card','Type de badge',$hd['type']],
            ['copy','Badge ID',$hd['code']], ['qr-code','QR / RFID',$hd['row']->qr_token ?? '—'],
            ['landmark','Pavillon / Zone','Pavillon Officiel'], ['key-round',"Niveau d'accès",'Standard'],
            ['calendar','Validité','27/07/2026 - 05/08/2026'], ['globe','Pays', ucfirst($hd['row']->country ?? 'Cameroun')],
        ];
    }
    $scanName = $live ? $hd['name'] : 'Jean Paul Essomba';
    $scanCode = $live ? $hd['code'] : 'VIS-000356';
    $scanRole = $live ? strtoupper($hd['type']) : 'VISITEUR';
    $checkinDone = ($qrCheckin ?? false) && $live;
@endphp

{{-- ══ HEADER ROW (title + mode pill live in the body per the design) ══ --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4 -mt-1">
    <div>
        <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">QR SCANNER</h1>
        <p class="text-[12px] text-[#8A857A]">{{ $state === 'validation' ? "Vérification de l'accès" : "Scannez le QR code d'un badge pour vérifier l'accès" }}</p>
    </div>
    @if($offline)
    <span class="flex items-center gap-2 rounded-xl bg-[#FDF6DC] border border-[#F3E5B2] px-4 py-2">
        <i data-lucide="wifi-off" class="w-4 h-4 text-[#C97A16]"></i>
        <span class="leading-tight"><span class="block text-[12.5px] font-bold text-[#C97A16]">MODE HORS LIGNE</span><span class="block text-[10.5px] text-[#7A5E00]">Les données seront synchronisées plus tard</span></span>
    </span>
    @endif
    <a href="{{ $self($offline ? 'camera' : 'offline') }}" class="flex items-center gap-2 rounded-full border border-[#E9E6DE] bg-white px-4 py-2 text-[12.5px] font-semibold text-[#3B382F]">
        <span class="w-2 h-2 rounded-full {{ $offline ? 'bg-[#C97A16]' : 'bg-[#157A43]' }}"></span>{{ $offline ? 'Mode hors ligne' : 'Mode en ligne' }}<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
    </a>
</div>

{{-- ══ TABS ══ --}}
<div class="flex items-center gap-6 border-b border-[#E6E3DB] mb-5 overflow-x-auto">
    @foreach([['qr-code','Scanner (Caméra)','camera',in_array($state,['camera','granted','refused','offline'])],['keyboard','Saisie manuelle','manual',$state==='manual'],['badge-check','Validation','validation',$state==='validation'],['history','Historique',null,false],['settings','Paramètres',null,false]] as [$ic,$lbl,$etat,$on])
        @if($etat)
        <a href="{{ $self($etat) }}" class="si-tab {{ $on ? 'is-active' : '' }} inline-flex items-center gap-2 whitespace-nowrap"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $lbl }}</a>
        @else
        <button data-toast="{{ $lbl }} à venir…" class="si-tab inline-flex items-center gap-2 whitespace-nowrap"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $lbl }}</button>
        @endif
    @endforeach
</div>

@if($state === 'camera')
{{-- ═══════════ CAMERA SCAN VIEW (dark) ═══════════ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start">
    <div>
        <div class="relative rounded-2xl overflow-hidden bg-[#0B0B0A]">
            <img src="{{ asset('images/siarc/qrscan-camera.png') }}" alt="" class="w-full h-auto block opacity-95">
            <span class="absolute top-4 left-4 flex items-center gap-2 rounded-full bg-black/60 text-white text-[11.5px] font-semibold px-3 py-1.5"><span class="w-2 h-2 rounded-full bg-[#35C46A]"></span>Caméra active</span>
            <button data-toast="Lampe activée (démo)" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-black/60 text-white flex items-center justify-center"><i data-lucide="flashlight" class="w-5 h-5"></i></button>
            {{-- scan frame corners --}}
            @foreach([['top-[14%] left-[22%]','border-t-4 border-l-4 rounded-tl-xl'],['top-[14%] right-[22%]','border-t-4 border-r-4 rounded-tr-xl'],['bottom-[16%] left-[22%]','border-b-4 border-l-4 rounded-bl-xl'],['bottom-[16%] right-[22%]','border-b-4 border-r-4 rounded-br-xl']] as [$pos,$bd])
            <span class="absolute {{ $pos }} w-12 h-12 {{ $bd }} border-white/90"></span>
            @endforeach
            <span class="absolute left-[24%] right-[24%] top-1/2 h-[3px] bg-[#35C46A] shadow-[0_0_14px_#35C46A]"></span>
            <span class="absolute bottom-[7%] left-1/2 -translate-x-1/2 rounded-full bg-black/70 text-white text-[12px] font-medium px-4 py-2">Placez le QR code dans le cadre pour scanner</span>
            <span class="absolute right-4 top-1/2 -translate-y-1/2 flex flex-col items-center gap-1 rounded-2xl bg-black/60 text-white px-2 py-2">
                <button data-toast="Zoom +" class="w-8 h-8 flex items-center justify-center"><i data-lucide="plus" class="w-4 h-4"></i></button>
                <span class="text-[11px] font-bold">1x</span>
                <button data-toast="Zoom −" class="w-8 h-8 flex items-center justify-center"><i data-lucide="minus" class="w-4 h-4"></i></button>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 mt-4 items-stretch">
            <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Informations scanner</p>
                <dl class="space-y-1.5 text-[12px]">
                    @foreach([['Lecteur','Caméra arrière (HD)'],['Résolution','1280 x 720'],['Statut','Actif'],['Batterie','78%'],['Mode','Automatique']] as [$k,$v])
                    <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313] {{ $v === 'Actif' ? 'text-[#157A43]' : '' }}">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
            <div class="flex flex-col items-center justify-center gap-2 px-3">
                <span class="flex items-center gap-3">
                    <button data-toast="Lampe activée (démo)" class="flex flex-col items-center gap-1 text-[10.5px] text-[#3B382F]"><span class="w-11 h-11 rounded-xl bg-white border border-[#EFEDE6] siarc-shadow flex items-center justify-center"><i data-lucide="flashlight" class="w-5 h-5 text-[#3B382F]"></i></span>Activer lampe</button>
                    <a href="{{ $self('granted') }}" class="flex flex-col items-center gap-1 text-[10.5px] font-bold text-[#157A43]"><span class="w-[74px] h-[74px] rounded-full border-4 border-[#157A43] bg-white flex items-center justify-center siarc-shadow"><span class="w-[54px] h-[54px] rounded-full bg-white border border-[#EFEDE6]"></span></span>SCAN EN COURS</a>
                    <button data-toast="Caméra basculée (démo)" class="flex flex-col items-center gap-1 text-[10.5px] text-[#3B382F]"><span class="w-11 h-11 rounded-xl bg-white border border-[#EFEDE6] siarc-shadow flex items-center justify-center"><i data-lucide="camera" class="w-5 h-5 text-[#3B382F]"></i></span>Basculer caméra</button>
                </span>
            </div>
            <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Statistiques de session</p>
                <dl class="space-y-1.5 text-[12px]">
                    @foreach([["Scans aujourd'hui",'128','#131313'],['Accès autorisés','116','#157A43'],['Accès refusés','12','#C0010C'],['Dernier scan','10:24:35','#131313']] as [$k,$v,$c])
                    <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-bold" style="color:{{ $c }}">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>

    {{-- right rail: last scan --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Dernier scan</p>
                <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">Accès autorisé</span>
            </div>
            <div class="rounded-xl bg-[#EAF6EE] border border-[#CFE8D8] p-4 text-center">
                <span class="mx-auto w-12 h-12 rounded-full bg-[#157A43] flex items-center justify-center"><i data-lucide="check" class="w-6 h-6 text-white"></i></span>
                <p class="text-[15px] font-extrabold text-[#157A43] mt-2 tracking-wide">ACCÈS ACCORDÉ</p>
                <p class="text-[12px] font-semibold text-[#131313]">Porte A - Entrée Principale</p>
                <p class="text-[11px] text-[#8A857A] mt-1">10:24:35 · 27/07/2026</p>
            </div>
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-4 mb-2.5">Détails du visiteur</p>
            <dl class="space-y-2 text-[12px]">
                @foreach($visitor as [$ic,$k,$v])
                <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-1.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313] text-right">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <a href="{{ $self('validation') }}" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="user-round" class="w-4 h-4"></i>Voir le profil complet</a>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Raccourcis</p>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="{{ $self('granted') }}" class="siarc-btn justify-center text-[12px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-2 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Accès autorisé</a>
                <a href="{{ $self('refused') }}" class="siarc-btn justify-center text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-2 py-2.5 hover:bg-[#FDF3F3]"><i data-lucide="ban" class="w-4 h-4"></i>Accès refusé</a>
            </div>
            <button data-toast="Signalement envoyé au support (démo)" class="mt-2.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#C97A16] border border-[#F3DFC2] rounded-lg px-3 py-2.5 hover:bg-[#FDF6EC]"><i data-lucide="alert-triangle" class="w-4 h-4"></i>Signaler un problème</button>
        </div>
    </div>
</div>

@elseif($state === 'granted' || $state === 'refused')
{{-- ═══════════ SCAN RESULT (Accès accordé / refusé) ═══════════ --}}
@php $ok = $state === 'granted'; @endphp
<div class="rounded-2xl {{ $ok ? 'bg-[#EAF6EE] border border-[#CFE8D8]' : 'bg-[#FDF0F0] border border-[#F5CFCF]' }} px-6 py-6 mb-5">
    <div class="flex flex-col sm:flex-row items-center gap-5">
        <span class="w-20 h-20 rounded-full {{ $ok ? 'bg-[#157A43]' : 'bg-[#C0010C]' }} flex items-center justify-center shrink-0 shadow-lg"><i data-lucide="{{ $ok ? 'check' : 'x' }}" class="w-10 h-10 text-white"></i></span>
        <span class="text-center sm:text-left">
            <span class="block text-[30px] font-extrabold tracking-wide {{ $ok ? 'text-[#157A43]' : 'text-[#C0010C]' }}">{{ $ok ? 'ACCÈS ACCORDÉ' : 'ACCÈS REFUSÉ' }}</span>
            <span class="block text-[15px] font-semibold text-[#131313]">{{ $ok ? 'Bienvenue et bon salon !' : 'Accès non autorisé' }}</span>
            @unless($ok)<span class="block text-[12px] text-[#3B382F] mt-1">Ce badge n'est pas autorisé à accéder à cette zone ou n'est plus valide.</span>@endunless
        </span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5 pt-4 border-t {{ $ok ? 'border-[#CFE8D8]' : 'border-[#F5CFCF]' }}">
        @foreach([['smartphone','Porte / Zone','Porte A','Entrée Principale'],['clock',"Heure d'accès",'10:24:35','27/07/2026'],['shield-check',"Niveau d'accès requis",'Standard','Visiteur'],['user-check','Statut',$ok ? 'Autorisé' : 'Refusé','']] as [$ic,$k,$v,$s])
        <span class="flex items-start gap-2.5">
            <i data-lucide="{{ $ic }}" class="w-[18px] h-[18px] {{ $ok ? 'text-[#157A43]' : 'text-[#C0010C]' }} shrink-0 mt-0.5"></i>
            <span><span class="block text-[11px] text-[#8A857A]">{{ $k }}</span><span class="block text-[13.5px] font-bold text-[#131313]">{{ $v }}</span>@if($s)<span class="block text-[11px] text-[#8A857A]">{{ $s }}</span>@endif</span>
        </span>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-5 items-start mb-5">
    <div class="relative">
        @include('pages.siarc.partials.accred-badge', ['bScale' => '280px'])
        @unless($ok)
        <span class="absolute -bottom-1 left-0 right-0 rounded-b-[14px] bg-[#C0010C] text-white text-[11px] font-bold text-center py-2 flex items-center justify-center gap-1.5"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>BADGE INVALIDE POUR CETTE ZONE</span>
        @endunless
    </div>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        @if($ok)
            <p class="text-[12px] font-bold tracking-[0.08em] text-[#157A43] uppercase mb-3">Informations du visiteur</p>
            <dl class="space-y-2.5 text-[12.5px]">
                @foreach(array_merge([['mail','Email','jean.essomba@minart.gov.cm'],['phone','Téléphone','+237 677 12 34 56']], $visitor) as [$ic,$k,$v])
                <div class="flex items-center justify-between gap-3 border-b border-[#F5F3EE] pb-2"><dt class="flex items-center gap-2 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313] text-right">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <p class="text-[12px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mt-4 mb-2">Journal d'accès</p>
            <div class="rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-3.5 py-2.5 flex items-center gap-3">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-[#157A43]"></i>
                <span class="text-[12px]"><b>10:24:35</b> <span class="text-[#8A857A]">27/07/2026</span> — Porte A - Entrée Principale <span class="text-[#8A857A]">· Scanner Mobile #A01</span></span>
                <span class="ml-auto text-[10px] font-bold px-2 py-0.5 rounded bg-[#157A43] text-white">ACCÈS ACCORDÉ</span>
            </div>
        @else
            <p class="text-[12px] font-bold tracking-[0.08em] text-[#C0010C] uppercase mb-3">Raison du refus</p>
            <div class="rounded-lg bg-[#FDF0F0] border border-[#F5CFCF] px-3.5 py-2.5 flex items-center gap-2.5 mb-3">
                <i data-lucide="x-circle" class="w-4 h-4 text-[#C0010C] shrink-0"></i>
                <p class="text-[12px] text-[#3B382F]">Ce badge n'a pas les permissions requises pour accéder à cette zone.</p>
            </div>
            <dl class="space-y-2.5 text-[12.5px]">
                @foreach([['alert-circle','Raison du refus','Accès non autorisé pour cette zone','#C0010C'],['scan-line','Zone scannée','Porte A - Entrée Principale','#C0010C'],['landmark','Zone autorisée','Pavillon Artisanat Local uniquement',null],['key-round','Niveau requis','Standard',null],['key-round','Niveau du badge','Basique',null],['power','Statut du badge','Inactif / Expiré',null],['calendar',"Date d'expiration",'26/07/2026 (Expiré)','#C0010C'],['copy','Badge ID','VIS-000356',null],['user-round','Visiteur','Jean Paul Essomba',null]] as [$ic,$k,$v,$c])
                <div class="flex items-center justify-between gap-3 border-b border-[#F5F3EE] pb-2"><dt class="flex items-center gap-2 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $k }}</dt><dd class="font-semibold text-right" style="color:{{ $c ?? '#131313' }}">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <p class="text-[12px] font-bold tracking-[0.08em] text-[#C97A16] uppercase mt-4 mb-2">Recommandations</p>
            <div class="rounded-lg bg-[#FDF9EA] border border-[#F3E5B2] px-3.5 py-3 text-[11.5px] text-[#7A5E00] leading-relaxed">
                <p>• Veuillez vérifier les permissions de ce badge.</p>
                <p>• Contactez l'administrateur ou le point d'accréditation.</p>
                <p>• Si vous pensez qu'il s'agit d'une erreur, merci de réessayer.</p>
            </div>
        @endif
    </div>
</div>

@unless($ok)
<div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-5 py-3 mb-4 flex items-center gap-3">
    <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase shrink-0">Journal d'accès</p>
    <i data-lucide="x-circle" class="w-4 h-4 text-[#C0010C]"></i>
    <span class="text-[12px]"><b>10:24:35</b> <span class="text-[#8A857A]">27/07/2026</span> — Porte A - Entrée Principale <span class="text-[#8A857A]">· Scanner Mobile #A01</span></span>
    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#FDE8E8] text-[#C0010C]">ACCÈS REFUSÉ</span>
    <button data-toast="Historique complet à venir…" class="ml-auto siarc-btn text-[11.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-1.5 hover:bg-[#FBFAF6]"><i data-lucide="history" class="w-3.5 h-3.5 text-[#8A857A]"></i>Voir l'historique complet</button>
</div>
@endunless

<div class="rounded-lg bg-[#EFF4FD] border border-[#CBDCF8] px-4 py-2.5 mb-4 flex items-center gap-2.5">
    <i data-lucide="{{ $ok ? 'info' : 'cloud' }}" class="w-4 h-4 text-[#3565DE] shrink-0"></i>
    <p class="text-[12px] text-[#1D3E7E]">{{ $ok ? 'Merci de conserver votre badge visible en tout temps dans l\'enceinte du salon.' : 'Vous êtes actuellement en mode en ligne. Les données sont synchronisées en temps réel.' }}</p>
</div>

<div class="flex flex-wrap items-center gap-3">
    @if($ok)
        @if($checkinDone)
        <span class="inline-flex items-center gap-2 rounded-lg bg-[#E8F5EC] border border-[#CFE8D8] px-4 py-2.5 text-[13px] font-bold text-[#157A43]"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Check-in enregistré pour {{ $scanName }}</span>
        @elseif($live)
        <form method="POST" action="{{ route('siarc.admin.accred.qrscanner.checkin') }}">
            @csrf
            <input type="hidden" name="code" value="{{ $scanCode }}">
            <button type="submit" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="log-in" class="w-4 h-4"></i>Confirmer le check-in</button>
        </form>
        @endif
        <button data-toast="Reçu envoyé à l'impression (démo)" class="siarc-btn text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Imprimer reçu</button>
        <a href="{{ $self('validation') }}" class="siarc-btn text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="history" class="w-4 h-4 text-[#8A857A]"></i>Voir l'historique</a>
    @else
        <a href="{{ $self('camera') }}" class="siarc-btn text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="refresh-cw" class="w-4 h-4 text-[#8A857A]"></i>Réessayer</a>
        <a href="{{ $self('manual') }}" class="siarc-btn text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="keyboard" class="w-4 h-4 text-[#8A857A]"></i>Saisie manuelle</a>
    @endif
    <a href="{{ $self('camera') }}" class="siarc-btn siarc-btn-green text-[13px] px-5 py-2.5 rounded-lg ml-auto"><i data-lucide="scan-line" class="w-4 h-4"></i>Scanner suivant</a>
</div>

@elseif($state === 'manual')
{{-- ═══════════ MANUAL ENTRY ═══════════ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-5 items-start">
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[13px] font-bold tracking-[0.06em] text-[#131313] uppercase">Saisie manuelle du code</p>
            <p class="text-[12px] text-[#8A857A] mt-0.5 mb-4">Entrez manuellement un code QR, UID ou numéro de badge pour vérifier l'accès.</p>
            <div data-tabs="manualtype" class="grid grid-cols-3 gap-2 mb-4">
                <style>[data-tabs="manualtype"] .si-tab>span{border-color:#EFEDE6;color:#3B382F}[data-tabs="manualtype"] .si-tab.is-active>span{border-color:#157A43;color:#157A43;font-weight:700;background:#F4FAF6}[data-tabs="manualtype"] .si-tab.is-active::after{display:none}</style>
                @foreach([['qr-code','Code QR / Code-barres'],['radio','UID RFID'],['id-card','Numéro de badge']] as [$ic,$lbl])
                <button data-tab="{{ $lbl }}" class="si-tab {{ $loop->first ? 'is-active' : '' }} !p-0"><span class="w-full flex items-center justify-center gap-2 rounded-lg border text-[12px] py-2.5"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $lbl }}</span></button>
                @endforeach
            </div>
            <label class="block text-[11px] font-bold tracking-wide text-[#3B382F] uppercase mb-1.5">Entrez le code</label>
            <form method="GET" action="{{ route('siarc.admin.accred.qrscanner') }}" id="qr-manual-form">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <input type="hidden" name="etat" value="validation">
            <div class="relative">
                <input id="qr-manual-code" name="code" value="{{ $scan['input'] ?? '' }}" type="text" maxlength="100" placeholder="Ex: VIS-000356 ou 04A3B2......" class="w-full text-[13px] rounded-lg border-2 border-[#157A43] pl-4 pr-10 py-3 focus:outline-none bg-white text-[#3B382F]">
                <button type="button" onclick="document.getElementById('qr-manual-code').value=''" class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center hover:bg-[#F1F1EF]"><i data-lucide="x-circle" class="w-4 h-4 text-[#8A857A]"></i></button>
            </div>
            <p class="flex items-center justify-between text-[11px] text-[#8A857A] mt-1.5"><span>Formats acceptés : Code QR, Code-barres, UID RFID, Numéro de badge</span><span>0 / 100 caractères</span></p>
            <div class="rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-3.5 py-2.5 mt-3 flex items-center gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#157A43] shrink-0"></i>
                <p class="text-[12px] text-[#155B33]">Vous pouvez entrer manuellement le code visible sur le badge ou le code reçu par email.</p>
            </div>
            <button type="submit" form="qr-manual-form" class="mt-4 w-full siarc-btn justify-center siarc-btn-green text-[14px] px-4 py-3.5 rounded-lg"><i data-lucide="search" class="w-4 h-4"></i>Vérifier l'accès</button>
            </form>
            <p class="text-center text-[12px] text-[#8A857A] my-3">ou</p>
            <button data-toast="Clavier virtuel à venir…" class="w-full max-w-[300px] mx-auto siarc-btn justify-center text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="keyboard" class="w-4 h-4 text-[#8A857A]"></i>Activer le clavier virtuel</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Statistiques aujourd'hui</p>
                <div class="grid grid-cols-4 gap-2 text-center">
                    @foreach([['Scans totaux','128','#131313'],['Accès accordés','116','#157A43'],['Accès refusés','12','#C0010C'],['Dernier scan','10:24:35','#131313']] as [$k,$v,$c])
                    <span><span class="block text-[10px] text-[#8A857A] leading-tight">{{ $k }}</span><span class="block text-[15px] font-bold mt-0.5" style="color:{{ $c }}">{{ $v }}</span></span>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Outils rapides</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach([['flashlight','Activer lampe'],['camera','Basculement caméra'],['cloud-off','Mode hors ligne']] as [$ic,$lbl])
                    <button data-toast="{{ $lbl }} (démo)" class="rounded-lg border border-[#EFEDE6] px-2 py-2.5 text-center hover:bg-[#FBFAF6]"><i data-lucide="{{ $ic }}" class="w-4 h-4 mx-auto mb-1 text-[#3B382F]"></i><span class="block text-[10px] font-medium text-[#3B382F] leading-tight">{{ $lbl }}</span></button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Résultat de la vérification</p>
            <div class="rounded-xl border border-[#EFEDE6] bg-[#FBFAF6] px-4 py-8 text-center">
                <i data-lucide="smartphone" class="w-10 h-10 text-[#C9C5BA] mx-auto"></i>
                <p class="text-[13.5px] font-bold text-[#3B382F] mt-2">Aucun code saisi</p>
                <p class="text-[11.5px] text-[#8A857A] mt-1">Entrez un code manuellement<br>pour vérifier l'accès</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Informations visiteur</p>
            <dl class="space-y-2 text-[12px]">
                @foreach($visitor as [$ic,$k,$v])
                <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-1.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5"></i>{{ $k }}</dt><dd class="text-[#B0AB9F]">--</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Accès rapide</p>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="{{ $self('validation') }}" class="siarc-btn justify-center text-[12px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-2 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="user-round" class="w-4 h-4"></i>Voir le profil complet</a>
                <button data-toast="Historique d'accès à venir…" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-2 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="history" class="w-4 h-4 text-[#8A857A]"></i>Voir l'historique d'accès</button>
            </div>
        </div>
    </div>
</div>

@elseif($state === 'validation')
{{-- ═══════════ VALIDATION SUCCESS ═══════════ --}}
<div class="rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-4 py-3 mb-5 flex items-center gap-3">
    <span class="w-8 h-8 rounded-full bg-[#157A43] flex items-center justify-center shrink-0"><i data-lucide="check" class="w-4 h-4 text-white"></i></span>
    <span><span class="block text-[13.5px] font-bold text-[#157A43]">Code valide</span><span class="block text-[11.5px] text-[#3B382F]">Le badge a été vérifié avec succès.</span></span>
    <a href="{{ $self('camera') }}" class="ml-auto siarc-btn text-[12.5px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3.5 py-2 bg-white hover:bg-[#F4FAF6]"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Nouvelle vérification</a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-6 items-start">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '272px'])
            <div class="text-center md:pt-3">
                <span class="mx-auto w-16 h-16 rounded-full bg-[#157A43] flex items-center justify-center shadow-lg"><i data-lucide="check" class="w-8 h-8 text-white"></i></span>
                <p class="text-[21px] font-extrabold text-[#157A43] mt-3 leading-tight">VALIDATION RÉUSSIE<br>ACCÈS AUTORISÉ</p>
                <span class="inline-block mt-2 text-[12px] font-bold text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-full px-4 py-1.5">Accès accordé</span>
                <dl class="mt-5 space-y-2.5 text-[12.5px] text-left max-w-[340px] mx-auto">
                    @foreach([['qr-code','Méthode','QR Code'],['map-pin','Lieu de scan','Porte A - Entrée Principale'],['smartphone','Lecteur','Scanner Mobile #A01'],['calendar','Date & Heure','27/07/2026 10:24:35'],['user-round','Opérateur','Jude Nshome (Admin)']] as [$ic,$k,$v])
                    <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-2 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                    @endforeach
                    <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-2 text-[#8A857A]"><i data-lucide="badge-check" class="w-4 h-4"></i>Statut</dt><dd><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">Autorisé</span></dd></div>
                </dl>
            </div>
        </div>
        <div class="rounded-lg bg-[#EFF4FD] border border-[#CBDCF8] px-4 py-2.5 mt-5 flex items-center gap-2.5">
            <i data-lucide="info" class="w-4 h-4 text-[#3565DE] shrink-0"></i>
            <p class="text-[12px] text-[#1D3E7E]">Ce badge est valide et l'accès est autorisé pour cette zone.</p>
        </div>
        <div class="grid grid-cols-[auto_1fr] gap-3 mt-4">
            <button data-toast="Reçu envoyé à l'impression (démo)" class="siarc-btn text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-3 bg-white hover:bg-[#FBFAF6]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Imprimer reçu</button>
            <a href="{{ $self('camera') }}" class="siarc-btn justify-center siarc-btn-green text-[13.5px] px-4 py-3 rounded-lg"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Nouvelle vérification</a>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Détails du visiteur</p>
                <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">Visiteur</span>
            </div>
            <dl class="space-y-2 text-[12px]">
                @foreach(array_merge([['mail','Email','jean.essomba@minart.gov.cm'],['phone','Téléphone','+237 677 12 34 56']], $visitor) as [$ic,$k,$v])
                <div class="flex items-center justify-between gap-3"><dt class="flex items-center gap-1.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313] text-right text-[11.5px]">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Règles d'accès</p>
            <div class="space-y-2">
                @foreach([['Pavillon Officiel',true],["Zones d'exposition",true],['Conférences',true],['Backstage',false]] as [$z,$okz])
                <p class="flex items-center justify-between text-[12px]">
                    <span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="{{ $okz ? 'check-circle-2' : 'x' }}" class="w-4 h-4 {{ $okz ? 'text-[#157A43]' : 'text-[#C0010C]' }}"></i>{{ $z }}</span>
                    <span class="font-semibold {{ $okz ? 'text-[#157A43]' : 'text-[#C0010C]' }}">{{ $okz ? 'Autorisé' : 'Non autorisé' }}</span>
                </p>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Historique d'accès (aujourd'hui)</p>
                <button data-toast="Historique complet à venir…" class="text-[11px] font-semibold text-[#157A43] underline underline-offset-2">Voir tout</button>
            </div>
            <p class="flex items-center justify-between text-[12px]">
                <span class="text-[#3B382F]"><b>10:24:35</b> — Porte A - Entrée Principale</span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-[#E8F5EC] text-[#157A43]">Autorisé</span>
            </p>
        </div>
    </div>
</div>

@else
{{-- ═══════════ OFFLINE MODE ═══════════ --}}
<div class="rounded-lg bg-[#EFF4FD] border border-[#CBDCF8] px-4 py-3 mb-5 flex items-center gap-3">
    <span class="w-9 h-9 rounded-full bg-[#DCE7FB] flex items-center justify-center shrink-0"><i data-lucide="wifi-off" class="w-4.5 h-4.5 text-[#3565DE]"></i></span>
    <span>
        <span class="block text-[13.5px] font-bold text-[#3565DE]">MODE HORS LIGNE ACTIF</span>
        <span class="block text-[11.5px] text-[#3B382F]">Vous êtes actuellement en mode hors ligne. Les vérifications d'accès sont enregistrées localement et seront synchronisées dès que la connexion sera rétablie.</span>
    </span>
    <button data-toast="Documentation du mode hors ligne à venir…" class="ml-auto shrink-0 siarc-btn text-[12.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3.5 py-2 bg-white hover:bg-[#FBFAF6]">En savoir plus</button>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] gap-6 items-start">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '272px'])
            <div class="text-center pt-2">
                <span class="mx-auto w-16 h-16 rounded-full bg-[#DCE7FB] flex items-center justify-center"><i data-lucide="wifi-off" class="w-8 h-8 text-[#3565DE]"></i></span>
                <p class="text-[22px] font-extrabold text-[#3565DE] mt-3">MODE HORS LIGNE</p>
                <p class="text-[12px] text-[#3B382F] mt-1">Ce scanner fonctionne actuellement en mode hors ligne.</p>
                <div class="grid grid-cols-3 gap-3 mt-5 rounded-xl border border-[#EFEDE6] bg-[#FBFAF6] p-4">
                    @foreach([['database','928','Vérifications en attente de synchronisation','#157A43'],['clock','15:24','Dernière synchronisation (27/07/2026 08:32)','#131313'],['battery-charging','82%','Batterie du dispositif','#157A43']] as [$ic,$v,$k,$c])
                    <span class="text-center"><i data-lucide="{{ $ic }}" class="w-4 h-4 mx-auto mb-1" style="color:{{ $c }}"></i><span class="block text-[17px] font-bold" style="color:{{ $c }}">{{ $v }}</span><span class="block text-[10px] text-[#8A857A] leading-tight mt-0.5">{{ $k }}</span></span>
                    @endforeach
                </div>
                <div class="rounded-lg bg-[#EFF4FD] border border-[#CBDCF8] px-3.5 py-2.5 mt-4 flex items-center gap-2.5 text-left">
                    <i data-lucide="info" class="w-4 h-4 text-[#3565DE] shrink-0"></i>
                    <p class="text-[11.5px] text-[#1D3E7E]"><b>Conseil :</b> Assurez-vous de synchroniser régulièrement vos données pour éviter toute perte d'informations.</p>
                </div>
                <button data-toast="Synchronisation impossible : aucune connexion (démo)" class="mt-4 w-full max-w-[340px] mx-auto siarc-btn justify-center text-[13.5px] font-bold text-white rounded-lg px-4 py-3" style="background:#3565DE"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Synchroniser maintenant</button>
                <button data-toast="File d'attente : 928 vérifications (démo)" class="mt-2 mx-auto flex items-center gap-1.5 text-[12px] font-semibold text-[#3565DE]"><i data-lucide="list" class="w-4 h-4"></i>Voir la file d'attente</button>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Statut de connexion</p>
                <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md bg-[#FDF6DC] text-[#C97A16]">Hors ligne</span>
            </div>
            <div class="rounded-xl border border-[#F5CFCF] bg-[#FDF0F0] px-4 py-5 text-center mb-3">
                <i data-lucide="cloud-off" class="w-8 h-8 text-[#C0010C] mx-auto"></i>
                <p class="text-[13px] font-bold text-[#C0010C] mt-1.5">Aucune connexion Internet</p>
                <p class="text-[11px] text-[#3B382F]">Le scanner fonctionne hors ligne.</p>
            </div>
            <dl class="space-y-2 text-[12px]">
                @foreach([['Mode actuel','Hors ligne'],['Connexion','Non disponible'],['Synchronisation auto','Désactivée'],['Stockage local','Actif'],["File d'attente",'928 en attente']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Informations du dispositif</p>
            <dl class="space-y-2 text-[12px]">
                @foreach([['Lecteur','Scanner Mobile #A01'],['Version','2.3.1'],['Stockage utilisé','1.2 GB / 8 GB'],['Système','Android 13'],['Dernier redémarrage','27/07/2026 07:15']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Paramètres hors ligne</p>
            @foreach([['Synchronisation auto','Désactivée',false],['Synchroniser via Wi-Fi uniquement','',true]] as [$k,$v,$on])
            <p class="flex items-center justify-between text-[12px] py-1.5">
                <span class="text-[#3B382F]">{{ $k }} @if($v)<span class="text-[#8A857A]">— {{ $v }}</span>@endif</span>
                <button data-toast="{{ $k }} — {{ $on ? 'désactivé' : 'activé' }} (démo)"><span class="w-[34px] h-[19px] rounded-full relative block {{ $on ? 'bg-[#157A43]' : 'bg-[#D5D2CA]' }}"><span class="absolute top-[2px] {{ $on ? 'right-[2px]' : 'left-[2px]' }} w-[15px] h-[15px] rounded-full bg-white shadow"></span></span></button>
            </p>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 max-w-[calc(100%-360px)]">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
        <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Statistiques hors ligne</p>
        <div class="grid grid-cols-4 gap-2 text-center">
            @foreach([["Scans aujourd'hui",'128','#157A43'],['Accès autorisés','116','#157A43'],['Accès refusés','12','#C0010C'],['En attente sync.','928','#3565DE']] as [$k,$v,$c])
            <span><span class="block text-[10px] text-[#8A857A] leading-tight">{{ $k }}</span><span class="block text-[16px] font-bold mt-0.5" style="color:{{ $c }}">{{ $v }}</span></span>
            @endforeach
        </div>
    </div>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
        <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Actions rapides</p>
        <div class="flex items-center gap-2.5">
            <a href="{{ $self('camera') }}" class="siarc-btn text-[12px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="wifi" class="w-4 h-4"></i>Passer en mode en ligne</a>
            <button data-toast="Effacement réservé à la production" class="siarc-btn text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2.5 hover:bg-[#FDF3F3]"><i data-lucide="trash-2" class="w-4 h-4"></i>Effacer la file d'attente</button>
        </div>
    </div>
</div>
@endif
