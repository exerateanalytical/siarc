@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $dirNavActive = 'partners';
    $pName = $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr);
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%s %d', $monthsFr[$d->month], $d->year) : $d->format('M Y'); };
    $dtFull = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y'); };
    $partnerTiles = ['MINCOMMERCE'=>'partner-mincommerce.png','MINAC'=>'partner-minac.png','UNESCO'=>'partner-unesco.png','ITC'=>'partner-itc.png','CEPII Cameroun'=>'partner-cepii.png','OAPI'=>'partner-oapi.png','Banque Africaine de Développement'=>'partner-bad.png','AFD'=>'partner-afd.png','Union Européenne'=>'partner-ue.png'];
    $logoFile = $partnerTiles[$partner->name_fr] ?? null;

    // Partner facts only — the design's project / artisan / event counters and its
    // "Indicateurs clés" tiles had no partner-scoped source in the schema and are gone.
    $durationYears = ($partner->start_date && $partner->end_date)
        ? max(1, (int) round(\Carbon\Carbon::parse($partner->start_date)->floatDiffInYears(\Carbon\Carbon::parse($partner->end_date))))
        : null;
    $sinceYears = $partner->start_date ? (int) \Carbon\Carbon::parse($partner->start_date)->diffInYears(now()) : null;
    $statBand = [
        ['shield-check', $partner->is_active ? ($isFr?'Actif':'Active') : ($isFr?'Inactif':'Inactive'), 'Statut',
            $sinceYears !== null ? ($isFr ? 'Partenariat depuis '.$sinceYears.' an'.($sinceYears > 1 ? 's' : '') : 'Partnership for '.$sinceYears.' year'.($sinceYears > 1 ? 's' : '')) : null],
        ['award', $partner->partnership_type ?: '—', $isFr?'Type de partenariat':'Partnership type', null],
        ['star', $partner->partnership_level ?: '—', $isFr?'Niveau de partenariat':'Partnership level', null],
        ['badge-check', $partner->reliability !== null ? number_format((float) $partner->reliability, 1) : '—', $isFr?'Indice de fiabilité':'Reliability index', 'stars'],
        ['map-pin', $partner->sector_fr ?: '—', $isFr?'Secteur':'Sector', null],
    ];
    $tabs = [[$isFr?'Aperçu':'Overview',true],[$isFr?'À propos':'About',false],[$isFr?'Programmes':'Programmes',false],[$isFr?'Actualités':'News',false],['Contact',false]];
    $infosCles = [
        [$isFr?'Type de partenariat':'Partnership type', $partner->partnership_type ?: '—', 'text'],
        [$isFr?'Niveau de partenariat':'Partnership level', $partner->partnership_level ?: '—', 'level'],
        [$isFr?'Durée du partenariat':'Duration', $durationYears ? ($durationYears.' '.($isFr?'ans':'years')) : '—', 'text'],
        [$isFr?'Date de début':'Start date', $dtFull($partner->start_date), 'text'],
        [$isFr?'Date de fin':'End date', $dtFull($partner->end_date), 'text'],
        [$isFr?'Renouvellement auto.':'Auto-renewal', $partner->auto_renew ? ($isFr?'Oui':'Yes') : ($isFr?'Non':'No'), $partner->auto_renew ? 'yes' : 'text'],
        [$isFr?'Statut légal':'Legal status', $partner->legal_verified ? ($isFr?'Vérifié':'Verified') : ($isFr?'Non vérifié':'Not verified'), $partner->legal_verified ? 'yes' : 'text'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $pName }} — {{ $isFr ? 'Partenaire d\'Artisan Hub 237' : 'Partner of Artisan Hub 237' }}">
    <title>{{ $pName }} — {{ $isFr ? 'Partenaires' : 'Partners' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#164C28', gold:'#C9942E', cream:'#F8F3ED', sand:'#E7E1D4' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#FBF8F2] text-[#1D1B16] antialiased">
@include('pages.partials.directory-header')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-[#0E2C1A] to-[#123D24] overflow-hidden">
    <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="absolute right-0 top-0 h-full w-[280px] object-cover opacity-40" aria-hidden="true">
    <div class="relative max-w-[1240px] mx-auto px-4 sm:px-6 py-8">
        <nav class="flex items-center gap-2 text-[12px] text-[#CFE3D5]"><a href="{{ route('home', ['lang'=>$lang]) }}" class="hover:text-white">{{ $isFr?'Accueil':'Home' }}</a><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><a href="{{ route('partners.index', ['lang'=>$lang]) }}" class="hover:text-white">{{ $isFr?'Partenaires':'Partners' }}</a><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><span class="text-[#E9C25A]">{{ $pName }}</span></nav>
        <div class="mt-5 grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-6 items-center">
            <div class="bg-white rounded-2xl p-5 flex items-center justify-center h-[200px]">
                @if($logoFile)<img src="{{ asset('images/landing/'.$logoFile) }}" alt="{{ $pName }}" class="max-h-[150px] object-contain">
                @else<span class="text-[40px] font-bold text-[#14652F]">{{ mb_strtoupper(mb_substr($pName,0,2)) }}</span>@endif
            </div>
            <div>
                <h1 class="flex flex-wrap items-center gap-3 font-serif text-[30px] sm:text-[38px] font-bold text-[#F3E7C9] leading-tight">{{ $pName }} @if($partner->partnership_type)<span class="inline-flex items-center gap-1.5 bg-[#0A3B22] border border-[#E9C25A]/40 rounded-lg px-3 py-1 text-[11px] font-semibold text-[#E9C25A]"><i data-lucide="award" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Partenaire '.$partner->partnership_type : $partner->partnership_type.' Partner' }}</span>@endif</h1>
                {{-- No generic fallback blurb: the design's default text described MINAC and was shown for every partner --}}
                @if($isFr ? $partner->description_fr : ($partner->description_en ?? $partner->description_fr))
                <p class="mt-3 text-[13.5px] text-[#DCEAE0] leading-relaxed max-w-[560px]">{{ $isFr ? $partner->description_fr : ($partner->description_en ?? $partner->description_fr) }}</p>
                @endif
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2 text-[12.5px] text-[#DCEAE0]">
                    <p class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-[#E9C25A]"></i>{{ $partner->address }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#E9C25A]"></i>{{ $partner->contact_phone }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#E9C25A]"></i>{{ $partner->contact_email }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="globe" class="w-4 h-4 text-[#E9C25A]"></i>{{ $partner->website }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="flag" class="w-4 h-4 text-[#E9C25A]"></i>{{ $partner->country }}</p>
                    <p class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-[#E9C25A]"></i>{{ $isFr?'Membre depuis':'Member since' }} {{ $dt($partner->start_date) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stat band --}}
<div class="max-w-[1240px] mx-auto px-4 sm:px-6 -mt-6 relative z-10">
    <div class="bg-white border border-[#EDE6D6] rounded-2xl shadow-sm grid grid-cols-2 md:grid-cols-5 divide-x divide-[#F1EDE2]">
        @foreach($statBand as [$sIcon, $sVal, $sLabel, $sSub])
        <div class="px-5 py-4 flex items-center gap-3">
            <i data-lucide="{{ $sIcon }}" class="w-6 h-6 {{ in_array($sIcon, ['star','badge-check'], true)?'text-[#C9942E]':'text-[#157A43]' }} shrink-0"></i>
            <div class="min-w-0"><p class="text-[11px] text-[#6F6B60]">{{ $sLabel }}</p><p class="text-[15px] font-bold text-[#1D1B16] leading-tight">{{ $sVal }}</p>
                @if($sSub==='stars')<span class="flex text-[#C9942E]">@for($i=0;$i<5;$i++)<i data-lucide="star" class="w-3 h-3 {{ $i < round((float) $partner->reliability) ? 'fill-current' : 'opacity-30' }}"></i>@endfor</span>
                @elseif($sSub)<p class="text-[10px] text-[#157A43]">{{ $sSub }}</p>@endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center gap-6 border-b border-[#EDE6D6] overflow-x-auto">@foreach($tabs as [$tLabel, $tActive])<span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}">{{ $tLabel }}</span>@endforeach</div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'À propos du partenaire':'About the partner' }}</h2>
            <p class="mt-3 text-[12.5px] text-[#55524A] leading-relaxed">{{ $isFr ? ($partner->description_fr ?? '') : ($partner->description_en ?? $partner->description_fr ?? '') }}</p>
            {{-- The "Objectif du partenariat" paragraph stated the same objective for
                 every partner and has no per-partner column behind it — dropped. --}}
            @if($partner->website)
            <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr?'En savoir plus':'Learn more' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            @endif
        </section>
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'Informations clés':'Key information' }}</h2>
            <dl class="mt-3.5 space-y-2.5 text-[12px]">
                @foreach($infosCles as [$l, $v, $kind])
                <div class="flex items-center justify-between gap-3 border-b border-[#F1EDE2] pb-2"><dt class="text-[#6F6B60]">{{ $l }}</dt>
                    <dd class="text-right">@if($kind==='level')<span class="inline-flex items-center gap-1 font-semibold text-[#C9942E]"><i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>{{ $v }}</span>@elseif($kind==='yes')<span class="inline-flex items-center gap-1 font-semibold text-[#157A43]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $v }}</span>@else<span class="font-semibold text-[#1D1B16]">{{ $v }}</span>@endif</dd>
                </div>
                @endforeach
            </dl>
        </section>
    </div>

    {{-- The design's "Indicateurs clés", "Activités récentes" and "Documents officiels"
         blocks were entirely invented (per-partner counts, a 2025 activity log and four
         PDFs with file sizes) with nothing behind them in the schema — removed. --}}
    {{-- "Programmes & Initiatives conjoints" is gone too: the three programmes were
         the same invented trio for every partner, UNESCO and AFD included, and the
         partners table has no programmes relation to replace them with. --}}

    {{-- CTA --}}
    <section class="mt-6 relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0E3D22] to-[#123D24] px-6 py-6 flex flex-wrap items-center justify-between gap-4">
        <div><h2 class="text-[18px] font-bold text-white">{{ $isFr?'Vous souhaitez devenir partenaire ?':'Want to become a partner?' }}</h2><p class="mt-1 text-[12.5px] text-[#CFE3D5]">{{ $isFr?'Rejoignez notre réseau et contribuons ensemble à la valorisation de l\'artisanat camerounais.':'Join our network and together let\'s promote Cameroonian craftsmanship.' }}</p></div>
        <a href="{{ route('contact', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-[#F5EEDD] text-[#1D1B16] text-[13px] font-semibold px-5 h-[46px] rounded-lg"><i data-lucide="handshake" class="w-4 h-4"></i>{{ $isFr?'Devenir partenaire':'Become a partner' }}</a>
    </section>
</div>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
