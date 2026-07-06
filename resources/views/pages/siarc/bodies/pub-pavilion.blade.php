@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    // ══ Public pavilion profile — same design language as the Pavilion Explorer ══
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;
    $p = $pavPublic; // provided by the route (design pavilion, possibly backed by a DB row)
    $exUrl = $h('siarc.exhibitors');
    $eid = siarcEvent()?->id ?? 0;

    // Real exhibitors for this pavilion when a DB row backs it; design fallbacks otherwise.
    $realExhibitors = $p['dbId']
        ? DB::table('event_exhibitors as ee')->join('businesses as b', 'b.id', '=', 'ee.business_id')
            ->where('ee.event_id', $eid)->where('ee.pavilion_id', $p['dbId'])
            ->limit(6)->get(['b.name_fr as name', 'b.slug', 'ee.booth_number'])
        : collect();
    $badgeTones = ['Pays' => ['#157A43', '#E8F5EC'], 'Thématique' => ['#7C4FE0', '#F0EAFB'], 'Régional' => ['#C97A16', '#FDF3E0']];
    [$bc, $bbg] = $badgeTones[$p['badge']] ?? ['#157A43', '#E8F5EC'];
@endphp

{{-- ══ HERO ══ --}}
<section class="relative">
    <img src="{{ asset('images/siarc/'.$p['img']) }}" alt="{{ $p['name'] }}" class="w-full h-[300px] md:h-[380px] object-cover">
    <div class="absolute inset-0" style="background:linear-gradient(180deg,rgba(4,26,14,.15) 0%,rgba(4,26,14,.82) 100%)"></div>
    <div class="absolute inset-x-0 bottom-0 max-w-6xl mx-auto px-4 sm:px-6 pb-7">
        <p class="text-[12px] text-white/80 flex items-center gap-1.5 mb-2">
            <a href="{{ $h('siarc.home') }}" class="hover:text-white">SIARC 2026</a> <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ $h('siarc.pavilions') }}" class="hover:text-white">{{ $isFr ? 'Pavillons' : 'Pavilions' }}</a> <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-white">{{ $p['name'] }}</span>
        </p>
        <span class="inline-block text-[11px] font-bold px-2.5 py-1 rounded-md mb-2" style="color:{{ $bc }};background:{{ $bbg }}">{{ $p['badge'] }}</span>
        <h1 class="text-[30px] md:text-[38px] font-display font-extrabold text-white leading-tight">@if(!empty($p['flag'])){{ $p['flag'] }} @endif{{ $p['name'] }}</h1>
        <p class="text-[13.5px] text-white/85 max-w-2xl mt-1.5 leading-relaxed">{{ $p['desc'] }}</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 py-8 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
    <div class="space-y-6">
        {{-- stats band --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach([['grid-3x3', $p['stands'], $isFr ? 'Stands' : 'Booths'],['store', $p['exhib'], $isFr ? 'Exposants' : 'Exhibitors'],['calendar-days', '10', $isFr ? 'Jours d\'exposition' : 'Exhibition days']] as [$ic,$v,$k])
            <div class="siarc-card siarc-shadow px-4 py-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-lg bg-[#E8F5EC] flex items-center justify-center shrink-0"><i data-lucide="{{ $ic }}" class="w-5 h-5 text-siarc-green"></i></span>
                <span><b class="block text-[20px] text-[#131313] leading-tight">{{ $v }}</b><span class="text-[11.5px] text-[#8A857A]">{{ $k }}</span></span>
            </div>
            @endforeach
        </div>

        {{-- about --}}
        <div class="siarc-card siarc-shadow p-6">
            <h2 class="text-[17px] font-bold text-[#131313] mb-2.5">{{ $isFr ? 'À propos du pavillon' : 'About the pavilion' }}</h2>
            <p class="text-[13px] text-[#3B382F] leading-relaxed">{{ $p['desc'] }}</p>
            <p class="text-[13px] text-[#3B382F] leading-relaxed mt-2.5">{{ $isFr
                ? "Situé au cœur du Musée National de Yaoundé, ce pavillon accueille les visiteurs du 27 juillet au 05 août 2026, de 09h00 à 18h00. Démonstrations d'artisans, ventes directes et rencontres B2B y sont organisées chaque jour."
                : 'Located at the heart of the Musée National de Yaoundé, this pavilion welcomes visitors from 27 July to 05 August 2026, 9:00 AM to 6:00 PM, with daily artisan demonstrations, direct sales and B2B meetings.' }}</p>
            <div class="flex flex-wrap gap-1.5 mt-4">
                @foreach($isFr ? ['Artisanat','Textiles','Céramique','Sculpture','Bijoux','Design'] : ['Crafts','Textiles','Ceramics','Sculpture','Jewellery','Design'] as $chip)
                <span class="text-[11px] font-medium text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-md px-2.5 py-1">{{ $chip }}</span>
                @endforeach
            </div>
        </div>

        {{-- exhibitors --}}
        <div class="siarc-card siarc-shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-[17px] font-bold text-[#131313]">{{ $isFr ? 'Exposants du pavillon' : 'Pavilion exhibitors' }}</h2>
                <a href="{{ $exUrl }}" class="text-[12.5px] font-semibold text-siarc-green inline-flex items-center gap-1">{{ $isFr ? 'Voir tous les exposants' : 'View all exhibitors' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            </div>
            @if($realExhibitors->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($realExhibitors as $ex)
                    <a href="{{ $h('siarc.exhibitor', ['slug' => $ex->slug]) ?? $exUrl }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-3.5 py-3 hover:bg-[#FBFAF6] transition-colors">
                        <span class="w-10 h-10 rounded-lg bg-[#E8F5EC] flex items-center justify-center shrink-0 text-[15px] font-bold text-siarc-green">{{ strtoupper(mb_substr($ex->name, 0, 1)) }}</span>
                        <span class="min-w-0"><b class="block text-[13px] text-[#131313] truncate">{{ $ex->name }}</b><span class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Stand' : 'Booth' }} {{ $ex->booth_number ?? '—' }}</span></span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] ml-auto shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach(($isFr ? [['Coopérative des Tisserands','A-12'],['Atelier Terre & Feu','A-15'],['Maison du Cuir','B-04'],['Bijoux d\'Afrique','B-09'],['Sculptures Royales','C-02'],['Design Contemporain','C-11']] : [['Weavers Cooperative','A-12'],['Earth & Fire Studio','A-15'],['Leather House','B-04'],['Jewels of Africa','B-09'],['Royal Sculptures','C-02'],['Contemporary Design','C-11']]) as [$n,$booth])
                    <a href="{{ $exUrl }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-3.5 py-3 hover:bg-[#FBFAF6] transition-colors">
                        <span class="w-10 h-10 rounded-lg bg-[#E8F5EC] flex items-center justify-center shrink-0 text-[15px] font-bold text-siarc-green">{{ strtoupper(mb_substr($n, 0, 1)) }}</span>
                        <span class="min-w-0"><b class="block text-[13px] text-[#131313] truncate">{{ $n }}</b><span class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Stand' : 'Booth' }} {{ $booth }}</span></span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] ml-auto shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══ RIGHT RAIL ══ --}}
    <div class="space-y-4">
        <div class="siarc-card siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</p>
            <dl class="space-y-2.5 text-[12.5px]">
                @foreach([['map-pin', $isFr ? 'Lieu' : 'Venue', 'Musée National de Yaoundé'],['calendar', 'Dates', '27 juillet – 05 août 2026'],['clock', $isFr ? 'Horaires' : 'Hours', '09h00 – 18h00'],['ticket', $isFr ? 'Accès' : 'Access', $isFr ? 'Badge visiteur requis' : 'Visitor badge required']] as [$ic,$k,$v])
                <div class="flex items-start gap-2.5"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-siarc-green shrink-0 mt-0.5"></i><span><span class="block text-[11px] text-[#8A857A]">{{ $k }}</span><span class="block font-medium text-[#131313]">{{ $v }}</span></span></div>
                @endforeach
            </dl>
        </div>
        <div class="siarc-card siarc-shadow p-5 space-y-2.5">
            <a href="{{ $h('siarc.register') }}" class="w-full siarc-btn siarc-btn-green justify-center text-[13px] px-4 py-3 rounded-xl"><i data-lucide="ticket" class="w-4 h-4"></i>{{ $isFr ? "S'inscrire en tant que visiteur" : 'Register as a visitor' }}</a>
            <a href="{{ $exUrl }}" class="w-full siarc-btn justify-center text-[13px] font-semibold text-siarc-green border border-[#CFE8D8] rounded-xl px-4 py-3 hover:bg-[#F4FAF6]"><i data-lucide="store" class="w-4 h-4"></i>{{ $isFr ? 'Explorer les exposants' : 'Explore exhibitors' }}</a>
            <a href="{{ $h('siarc.programme') }}" class="w-full siarc-btn justify-center text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-xl px-4 py-3 hover:bg-[#FBFAF6]"><i data-lucide="calendar-days" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr ? 'Voir le programme' : 'View programme' }}</a>
        </div>
        <div class="siarc-card siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">{{ $isFr ? 'Autres pavillons' : 'Other pavilions' }}</p>
            <div class="space-y-2">
                @foreach($p['others'] as [$slug,$name,$img])
                <a href="{{ route('siarc.pavilion', ['slug' => $slug, 'lang' => $lang]) }}" class="flex items-center gap-3 rounded-xl hover:bg-[#FBFAF6] p-1.5 transition-colors">
                    <img src="{{ asset('images/siarc/'.$img) }}" alt="" class="w-12 h-9 rounded-lg object-cover shrink-0">
                    <span class="text-[12.5px] font-semibold text-[#131313] min-w-0 truncate">{{ $name }}</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] ml-auto shrink-0"></i>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
