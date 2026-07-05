@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // Real IDs so detail links never 404
    $eid = siarcEvent()?->id ?? 0;
    $pavilionId = DB::table('pavilions')->where('event_id',$eid)->value('id');

    // Per-KPI destination (admin list routes)
    $kpiRoute = fn($name) => R::has($name) ? route($name, ['lang'=>$lang]) : route('siarc.admin.dashboard', ['lang'=>$lang]);

    // KPI cards — approved design figures (verbatim, comma thousands separators)
    // 7th element = admin list route target for "Voir détails"
    $kpis = [
        ['users-round','#157A43','#E2F3E8','Exposants','842','+18%','siarc.admin.exhibitors'],
        ['users','#C97A16','#FDF3E0','Visiteurs inscrits','20,458','+24%','siarc.admin.visitors'],
        ['presentation','#3565DE','#E8EFFB','Réunions B2B planifiées','1,248','+31%','siarc.admin.b2b'],
        ['users-round','#7C4FE0','#F0EAFB','Ateliers & Conférences','48','+14%','siarc.admin.programme'],
        ['store','#C97A16','#FDF3E0','Stands occupés','78%','+12%','siarc.admin.stands'],
        ['banknote','#157A43','#E2F3E8','Revenus générés','128,450,000','+22%','siarc.admin.reports'],
    ];
    $activities = [
        ['user-plus','#157A43','#E2F3E8','Nouvel exposant inscrit','Art Bois Précieux (Pavillon Centre)','Il y a 15 min'],
        ['handshake','#3565DE','#E8EFFB','Réunion B2B planifiée','MEKA International ↔ Art Cam','Il y a 32 min'],
        ['banknote','#C97A16','#FDF3E0','Paiement reçu','Stand C-24 – 450 000 FCFA','Il y a 1 h'],
        ['presentation','#7C4FE0','#F0EAFB','Atelier publié','Sculpture sur bois – 30 Juillet','Il y a 2 h'],
        ['user-plus','#157A43','#E2F3E8','Nouveau visiteur inscrit','Marie Claire ABESSO','Il y a 3 h'],
    ];
    $countries = [['Cameroun',45,'#157A43'],['Nigeria',18,'#C97A16'],['Côte d\'Ivoire',12,'#E6B201'],['France',8,'#7C4FE0'],['États-Unis',5,'#3565DE']];
    $categories = [['Bois & Sculpture',28,'#157A43'],['Textiles & Tissus',22,'#C97A16'],['Bijouterie & Métal',18,'#E6B201'],['Poterie & Céramique',15,'#7C4FE0'],['Cuir & Peaux',10,'#3565DE']];
    $shortcuts = [
        ['user-plus','Ajouter un exposant','#E2F3E8','#157A43','siarc.admin.exhibitors'],
        ['handshake','Créer une réunion B2B','#FDE8E8','#C0010C','siarc.admin.b2b'],
        ['user-plus','Ajouter un atelier','#F0EAFB','#7C4FE0','siarc.admin.programme'],
        ['map','Plan du salon','#E8EFFB','#3565DE','siarc.admin.floorplan'],
        ['id-card','Badge visiteur','#FDF3E0','#C97A16','siarc.admin.badges'],
        ['bar-chart-3','Rapports & Analytics','#E2F3E8','#157A43','siarc.admin.analytics'],
    ];
    $stands = [['Occupés','975 (78%)','#157A43'],['Réservés','150 (12%)','#C97A16'],['Disponibles','100 (8%)','#7C4FE0'],['En maintenance','25 (2%)','#E6B201']];
@endphp

{{-- ══ HERO BANNER + COUNTDOWN ══ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
    <div class="lg:col-span-2 rounded-2xl overflow-hidden siarc-shadow">
        <img src="{{ asset('images/siarc/dash-hero.png') }}" alt="SIARC 2026 – Salon International de l'Artisanat du Cameroun" class="w-full h-full object-cover">
    </div>
    <div class="siarc-card siarc-shadow p-6">
        <p class="text-[13px] font-bold text-[#1A1712] mb-4">Compte à rebours</p>
        <div id="si-countdown" class="grid grid-cols-4 gap-2 text-center" data-target="2026-07-27T09:00:00">
            @foreach(['jours'=>['15','JOURS'],'heures'=>['08','HEURES'],'minutes'=>['42','MINUTES'],'secondes'=>['36','SECONDES']] as $k=>$cell)
            <div class="relative">
                <p class="font-display text-[30px] font-extrabold text-siarc-green leading-none" data-cd="{{ $k }}">{{ $cell[0] }}</p>
                <p class="text-[9px] font-semibold tracking-wide text-[#A8A498] mt-1.5">{{ $cell[1] }}</p>
                @if(!$loop->last)<span class="absolute -right-1 top-[2px] font-display text-[24px] font-extrabold text-[#CFCBBF]">:</span>@endif
            </div>
            @endforeach
        </div>
        <p class="text-center text-[12.5px] text-[#55524A] mt-4 mb-4">Avant l'ouverture de SIARC 2026</p>
        <div class="h-2 rounded-full bg-[#EFEDE6] overflow-hidden mb-2.5">
            <div class="h-full rounded-full bg-gradient-to-r from-siarc-green to-siarc-ochre" style="width:73%"></div>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-[12px]"><span class="font-bold text-[#1A1712]">73%</span> <span class="text-[#8A857A]">de préparation globale</span></span>
            <a href="{{ $h('siarc.admin.reports') }}" class="text-[11.5px] font-semibold text-siarc-green border border-[#D8E5DC] rounded-lg px-3 py-1.5 hover:bg-[#E2F3E8]">Voir la checklist</a>
        </div>
    </div>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$trend,$to])
    <div class="siarc-card siarc-shadow p-4">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
        <p class="text-[22px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}@if($label==='Revenus générés')<span class="text-[11px] font-semibold text-[#8A857A]"> FCFA</span>@endif</p>
        <div class="flex items-center justify-between mt-2">
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $trend }}<span class="text-[#B0AB9F] font-normal">vs dernier mois</span></span>
        </div>
        <a href="{{ $kpiRoute($to) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-ochre mt-2 hover:gap-1.5 transition-all">Voir détails <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
    </div>
    @endforeach
</div>

{{-- ══ CHARTS ROW ══ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
    {{-- line chart --}}
    <div class="lg:col-span-1 xl:col-span-1 siarc-card siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[13.5px] font-bold text-[#1A1712]">Aperçu des inscriptions</h3>
            <button type="button" data-toast="Filtre de période bientôt disponible" class="inline-flex items-center gap-1 text-[11px] font-medium text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1">10 derniers jours <i data-lucide="chevron-down" class="w-3 h-3"></i></button>
        </div>
        <div class="flex items-center gap-4 mb-2 text-[11px]">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-siarc-green"></span>Visiteurs</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-siarc-ochre"></span>Exposants</span>
        </div>
        <svg viewBox="0 0 320 185" class="w-full">
            @foreach(['25K'=>20,'20K'=>44,'15K'=>68,'10K'=>92,'5K'=>116,'0'=>140] as $t=>$yy)
                <line x1="34" y1="{{ $yy }}" x2="315" y2="{{ $yy }}" stroke="#F1F1EF"/>
                <text x="28" y="{{ $yy+3 }}" font-size="8" fill="#B0AB9F" text-anchor="end">{{ $t }}</text>
            @endforeach
            {{-- Visiteurs area + line (rises 3K→19K) --}}
            <polygon points="34,140 34,124 65,120 96,108 127,98 158,90 189,82 220,77 251,72 282,66 315,60 315,140" fill="#157A43" opacity="0.08"/>
            <polyline points="34,124 65,120 96,108 127,98 158,90 189,82 220,77 251,72 282,66 315,60" fill="none" stroke="#157A43" stroke-width="2.2"/>
            @foreach([[34,124],[65,120],[96,108],[127,98],[158,90],[189,82],[220,77],[251,72],[282,66],[315,60]] as [$cx,$cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="2.4" fill="#157A43"/>@endforeach
            {{-- Exposants line (low, rises 0.5K→5K) --}}
            <polyline points="34,138 65,137 96,136 127,134 158,132 189,130 220,128 251,126 282,124 315,121" fill="none" stroke="#C97A16" stroke-width="2.2"/>
            @foreach([[34,138],[65,137],[96,136],[127,134],[158,132],[189,130],[220,128],[251,126],[282,124],[315,121]] as [$cx,$cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="2.4" fill="#C97A16"/>@endforeach
            {{-- x axis labels --}}
            @foreach(['27 Juil','28 Juil','29 Juil','30 Juil','31 Juil','01 Août','02 Août','03 Août','04 Août','05 Août'] as $i=>$d)<text x="{{ 34+$i*31.2 }}" y="153" font-size="6.5" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>@endforeach
        </svg>
    </div>

    {{-- donut --}}
    <div class="siarc-card siarc-shadow p-5">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">Répartition des stands</h3>
        <div class="flex items-center gap-5">
            <div class="relative shrink-0 w-[130px] h-[130px]">
                <svg viewBox="0 0 120 120" class="w-[130px] h-[130px] -rotate-90">
                    @php $circ=326.7; $off=0; @endphp
                    @foreach([[78,'#157A43'],[12,'#C97A16'],[8,'#7C4FE0'],[2,'#E6B201']] as [$pct,$col])
                        <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                            stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                            stroke-dashoffset="{{ -round($off,1) }}"/>
                        @php $off += $circ*$pct/100; @endphp
                    @endforeach
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center leading-none">
                    <span class="text-[10px] text-[#8A857A]">Total</span>
                    <span class="font-display text-[22px] font-extrabold text-[#1A1712] my-0.5">1,250</span>
                    <span class="text-[10px] text-[#8A857A]">Stands</span>
                </div>
            </div>
            <ul class="flex-1 space-y-2.5">
                @foreach($stands as [$lbl,$v,$col])
                <li class="flex items-start gap-2 text-[11.5px]">
                    <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background:{{ $col }}"></span>
                    <span class="flex-1 leading-tight"><span class="block text-[#3B382F] font-semibold">{{ $lbl }}</span><span class="text-[#8A857A]">{{ $v }}</span></span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- activities --}}
    <div class="siarc-card siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[13.5px] font-bold text-[#1A1712]">Activités récentes</h3>
            <a href="{{ $h('siarc.admin.reports') }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green hover:underline">Voir toutes <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
        </div>
        <ul class="space-y-3.5">
            @foreach($activities as [$icon,$color,$tile,$title,$sub,$time])
            <li class="flex gap-3">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $color }}"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">{{ $title }}</p>
                    <p class="text-[11px] text-[#8A857A] truncate">{{ $sub }}</p>
                </div>
                <span class="text-[10px] text-[#B0AB9F] whitespace-nowrap">{{ $time }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- ══ BOTTOM ROW ══ --}}
<div class="grid lg:grid-cols-3 gap-5">
    {{-- countries + world map --}}
    <div class="siarc-card siarc-shadow p-5 relative overflow-hidden">
        <img src="{{ asset('images/siarc/dash-worldmap.png') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute right-3 bottom-4 w-[46%] max-w-[210px] opacity-90">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4 relative">Top 5 pays des visiteurs</h3>
        <ul class="space-y-3 relative max-w-[52%]">
            @foreach($countries as [$name,$pct,$col])
            <li>
                <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-[#3B382F] font-medium">{{ $name }}</span><span class="text-[#8A857A]">{{ $pct }}%</span></div>
                <div class="h-2 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full" style="width:{{ $pct*2 }}%;background:{{ $col }}"></div></div>
            </li>
            @endforeach
        </ul>
    </div>
    {{-- categories + pottery --}}
    <div class="siarc-card siarc-shadow p-5 relative overflow-hidden">
        <img src="{{ asset('images/siarc/dash-pottery.png') }}" alt="" aria-hidden="true" class="pointer-events-none select-none absolute right-4 bottom-4 w-[30%] max-w-[130px]">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4 relative">Catégories d'exposition populaires</h3>
        <ul class="space-y-3 relative max-w-[66%]">
            @foreach($categories as [$name,$pct,$col])
            <li>
                <div class="flex items-center justify-between text-[12px] mb-1"><span class="text-[#3B382F] font-medium">{{ $name }}</span><span class="text-[#8A857A]">{{ $pct }}%</span></div>
                <div class="h-2 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full" style="width:{{ $pct*3 }}%;background:{{ $col }}"></div></div>
            </li>
            @endforeach
        </ul>
    </div>
    {{-- shortcuts --}}
    <div class="siarc-card siarc-shadow p-5">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">Raccourcis rapides</h3>
        <div class="grid grid-cols-3 gap-3">
            @foreach($shortcuts as [$icon,$label,$tile,$color,$route])
            <a href="{{ $h($route) }}" class="rounded-xl border border-[#EFEDE6] p-3 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors text-center">
                <span class="w-9 h-9 mx-auto rounded-lg flex items-center justify-center mb-2" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]" style="color:{{ $color }}"></i></span>
                <p class="text-[11px] font-semibold text-[#3B382F] leading-tight">{{ $label }}</p>
            </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    var el=document.getElementById('si-countdown'); if(!el)return;
    var target=new Date(el.dataset.target).getTime();
    function pad(n){return String(n).padStart(2,'0');}
    function tick(){
        var d=Math.max(0,target-Date.now()),s=Math.floor(d/1000);
        var days=Math.floor(s/86400),h=Math.floor(s%86400/3600),m=Math.floor(s%3600/60),sec=s%60;
        var set=function(k,v){var n=el.querySelector('[data-cd="'+k+'"]');if(n)n.textContent=v;};
        set('jours',pad(days));set('heures',pad(h));set('minutes',pad(m));set('secondes',pad(sec));
    }
    tick();setInterval(tick,1000);
})();
</script>
@endpush
