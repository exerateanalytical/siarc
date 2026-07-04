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

    $statBand = [
        ['users', '8', $isFr?'Projets Collaboratifs':'Collaborative Projects', '+2 '.($isFr?'ce mois':'this month')],
        ['heart-handshake', '245', $isFr?'Artisans Soutenus':'Artisans Supported', '+18 '.($isFr?'ce mois':'this month')],
        ['calendar-days', '12', $isFr?'Événements Réalisés':'Events Held', '+1 '.($isFr?'ce mois':'this month')],
        ['star', $isFr?'Excellent':'Excellent', $isFr?'Niveau d\'Engagement':'Engagement Level', 'stars'],
        ['shield-check', $isFr?'Actif':'Active', 'Statut', $isFr?'Partenariat depuis 1 an':'Partnership for 1 year'],
    ];
    $tabs = [[$isFr?'Aperçu':'Overview',true],[$isFr?'À propos':'About',false],[$isFr?'Activités & Projets':'Activities & Projects',false],[$isFr?'Actualités':'News',false],['Documents',false],['Contact',false]];
    $infosCles = [
        [$isFr?'Type de partenariat':'Partnership type', $partner->partnership_type ?? 'Institutionnel', 'text'],
        [$isFr?'Niveau de partenariat':'Partnership level', $partner->partnership_level ?? 'Standard', 'level'],
        [$isFr?'Durée du partenariat':'Duration', '3 ans', 'text'],
        [$isFr?'Date de début':'Start date', $dtFull($partner->start_date), 'text'],
        [$isFr?'Date de fin':'End date', $dtFull($partner->end_date), 'text'],
        [$isFr?'Renouvellement auto.':'Auto-renewal', $isFr?'Oui':'Yes', 'yes'],
        [$isFr?'Statut légal':'Legal status', $isFr?'Vérifié':'Verified', 'yes'],
    ];
    $indicateurs = [
        ['heart-handshake', '#157A43', '8', $isFr?'Projets Collaboratifs':'Collaborative Projects', '+2 '.($isFr?'ce mois':'this month')],
        ['users', '#7C4FE0', '245', $isFr?'Artisans Soutenus':'Artisans Supported', '+18 '.($isFr?'ce mois':'this month')],
        ['calendar-days', '#3565DE', '12', $isFr?'Événements Réalisés':'Events Held', '+1 '.($isFr?'ce mois':'this month')],
        ['eye', '#C97A16', '1.2M', $isFr?'Visibilité Générée':'Visibility Generated', '+15.5% '.($isFr?'ce mois':'this month')],
    ];
    $activites = [
        [$isFr?'Participation au SIARC 2024':'SIARC 2024 participation', '08 '.($isFr?'Mai':'May').' 2025'],
        [$isFr?'Atelier de formation des artisans':'Artisan training workshop', '22 '.($isFr?'Avr.':'Apr.').' 2025'],
        [$isFr?'Campagne de valorisation':'Promotion campaign', '15 '.($isFr?'Avr.':'Apr.').' 2025'],
        [$isFr?'Visite officielle au MINAC':'Official visit to MINAC', '03 '.($isFr?'Mar.':'Mar.').' 2025'],
        [$isFr?'Réunion stratégique':'Strategic meeting', '12 '.($isFr?'Fév.':'Feb.').' 2025'],
    ];
    $programmes = [
        ['event-1.png', $isFr?'Soutien aux Artisans':'Artisan Support', $isFr?'Appui technique et financier aux artisans locaux.':'Technical and financial support to local artisans.'],
        ['event-2.png', $isFr?'Valorisation Culturelle':'Cultural Promotion', $isFr?'Promotion des arts et du patrimoine camerounais.':'Promotion of Cameroonian arts and heritage.'],
        ['event-3.png', $isFr?'Foires & Expositions':'Fairs & Exhibitions', $isFr?'Organisation et participation aux foires artisanales.':'Organising and joining craft fairs.'],
    ];
    $documents = [
        [$isFr?'Convention de partenariat':'Partnership agreement', 'PDF · 1.2 MB'],
        [$isFr?'Accord de collaboration':'Collaboration agreement', 'PDF · 980 KB'],
        [$isFr?'Plan d\'action 2024-2027':'Action plan 2024-2027', 'PDF · 1.8 MB'],
        [$isFr?'Charte de partenariat':'Partnership charter', 'PDF · 1.1 MB'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $pName }} — {{ $isFr ? 'Partenaire de la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Partner of the National Virtual Gallery of Cameroonian Crafts' }}">
    <title>{{ $pName }} — {{ $isFr ? 'Partenaires' : 'Partners' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#164C28', gold:'#C9942E', cream:'#F8F3ED', sand:'#E7E1D4' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
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
                <h1 class="flex flex-wrap items-center gap-3 font-serif text-[30px] sm:text-[38px] font-bold text-[#F3E7C9] leading-tight">{{ $pName }} <span class="inline-flex items-center gap-1.5 bg-[#0A3B22] border border-[#E9C25A]/40 rounded-lg px-3 py-1 text-[11px] font-semibold text-[#E9C25A]"><i data-lucide="award" class="w-3.5 h-3.5"></i>{{ $isFr?'Partenaire Institutionnel':'Institutional Partner' }}</span></h1>
                <p class="mt-3 text-[13.5px] text-[#DCEAE0] leading-relaxed max-w-[560px]">{{ $isFr ? ($partner->description_fr ?? 'Institution gouvernementale chargée de la promotion et de la préservation des arts et du patrimoine national du Cameroun.') : ($partner->description_en ?? $partner->description_fr ?? 'Government institution for the promotion and preservation of Cameroon\'s arts and national heritage.') }}</p>
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
            <i data-lucide="{{ $sIcon }}" class="w-6 h-6 {{ $sIcon==='star'?'text-[#C9942E]':'text-[#157A43]' }} shrink-0"></i>
            <div><p class="text-[11px] text-[#6F6B60]">{{ $sLabel }}</p><p class="text-[18px] font-bold text-[#1D1B16] leading-none">{{ $sVal }}</p>
                @if($sSub==='stars')<span class="flex text-[#C9942E]">@for($i=0;$i<5;$i++)<i data-lucide="star" class="w-3 h-3 fill-current"></i>@endfor</span>@else<p class="text-[10px] text-[#157A43]">{{ $sSub }}</p>@endif
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
            <div class="mt-4 bg-[#F7F9F7] border border-[#EAF0EB] rounded-xl px-4 py-3.5">
                <p class="flex items-center gap-2 text-[12px] font-bold text-[#157A43]"><i data-lucide="target" class="w-4 h-4"></i>{{ $isFr?'Objectif du partenariat':'Partnership objective' }}</p>
                <p class="mt-1.5 text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Promouvoir l\'artisanat camerounais, soutenir les artisans locaux et préserver le patrimoine culturel à travers des initiatives conjointes et des programmes de développement.' : 'Promote Cameroonian craftsmanship, support local artisans and preserve cultural heritage through joint initiatives and development programmes.' }}</p>
                <a href="{{ $partner->website }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr?'En savoir plus':'Learn more' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            </div>
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

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'Indicateurs clés':'Key indicators' }}</h2>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                @foreach($indicateurs as [$iIcon, $iColor, $iVal, $iLabel, $iSub])
                <div><span class="w-12 h-12 mx-auto rounded-full flex items-center justify-center" style="background-color: {{ $iColor }}"><i data-lucide="{{ $iIcon }}" class="w-5 h-5 text-white"></i></span><p class="mt-2 text-[11px] text-[#6F6B60]">{{ $iLabel }}</p><p class="text-[18px] font-bold text-[#1D1B16]">{{ $iVal }}</p><p class="text-[10px] text-[#157A43]">{{ $iSub }}</p></div>
                @endforeach
            </div>
            <div class="mt-4"><div class="flex items-center justify-between text-[11.5px]"><span class="text-[#6F6B60]">{{ $isFr?'Niveau d\'engagement':'Engagement level' }}</span><span class="font-semibold">85%</span></div><div class="mt-1.5 h-2 rounded-full bg-[#F0EFEA] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:85%"></span></div><p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]"><i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>{{ $isFr?'Excellent partenariat':'Excellent partnership' }}</p></div>
        </section>
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'Activités récentes':'Recent activities' }}</h2>
            <div class="mt-4 space-y-3">@foreach($activites as [$aTitle, $aDate])<div class="flex items-center justify-between gap-3 border-b border-[#F1EDE2] pb-2.5"><span class="flex items-center gap-2.5 text-[12px] text-[#3B382F]"><i data-lucide="file-text" class="w-4 h-4 text-[#C9942E]"></i>{{ $aTitle }}</span><span class="text-[11px] text-[#8A857A] whitespace-nowrap">{{ $aDate }}</span></div>@endforeach</div>
            <a href="{{ route('events.index', ['lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43]">{{ $isFr?'Voir toutes les activités':'View all activities' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'Programmes & Initiatives conjoints':'Joint programmes & initiatives' }}</h2>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($programmes as [$pImg, $pTitle, $pDesc])
                <div class="border border-[#EDE6D6] rounded-xl overflow-hidden"><img src="{{ asset('images/landing/'.$pImg) }}" alt="" class="w-full h-[80px] object-cover"><div class="p-3"><p class="text-[12px] font-bold text-[#1D1B16]">{{ $pTitle }}</p><p class="mt-1 text-[10.5px] text-[#6F6B60] leading-snug">{{ $pDesc }}</p><a href="{{ route('events.index', ['lang'=>$lang]) }}" class="mt-1.5 inline-block text-[10.5px] font-semibold text-[#157A43]">{{ $isFr?'En savoir plus':'Learn more' }} →</a></div></div>
                @endforeach
            </div>
        </section>
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1D1B16] uppercase">{{ $isFr?'Documents officiels':'Official documents' }}</h2>
            <div class="mt-4 space-y-2.5">
                @foreach($documents as [$dName, $dMeta])
                <a href="{{ route('contact', ['lang'=>$lang]) }}" class="flex items-center gap-3 border border-[#F1EDE2] rounded-xl px-3.5 py-2.5 hover:border-[#14652F] group"><span class="w-7 h-8 rounded bg-[#FDE8E8] text-[#DC2626] flex items-center justify-center"><i data-lucide="file-text" class="w-4 h-4"></i></span><span class="min-w-0 flex-1"><span class="block text-[12px] font-medium text-[#1D1B16] truncate group-hover:text-[#157A43]">{{ $dName }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $dMeta }}</span></span><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i></a>
                @endforeach
            </div>
        </section>
    </div>

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
