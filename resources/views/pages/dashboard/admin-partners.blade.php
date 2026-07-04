@php
    $isFr = $lang === 'fr';
    $adminActive = 'partners';

    $tierLabels = [
        'institutional' => $isFr ? 'Institutionnel' : 'Institutional',
        'platinum'      => 'Platinum', 'gold' => 'Gold', 'silver' => 'Silver',
        'partner'       => $isFr ? 'Partenaire' : 'Partner',
    ];
    $typeCls = [
        'Institutionnel' => 'bg-[#E2F3E8] text-[#157A43]',
        'International'  => 'bg-[#E8EFFB] text-[#3565DE]',
        'Finance'        => 'bg-[#FDF3E0] text-[#C97A16]',
        'Privé'          => 'bg-[#F0EAFB] text-[#7C4FE0]',
    ];
    $statusCls = [
        'active'  => ['bg-[#E2F3E8] text-[#157A43]', $isFr ? 'Actif' : 'Active'],
        'pending' => ['bg-[#FDF3E0] text-[#C97A16]', $isFr ? 'En attente' : 'Pending'],
    ];
    // Windows/Chromium render flag emoji as raw two-letter codes rather than
    // flag glyphs, and the project has no flag-image asset — use a neutral
    // map-pin icon instead of a broken/misleading flag.

    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $fmtDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '—';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    // 5 KPI cards [icon, iconColor, tile, value, label, sub, spark]
    $partnerCards = [
        ['handshake',   '#157A43', '#E2F3E8', $partnerKpis['active'],      $isFr ? 'Partenaires Actifs' : 'Active Partners', $isFr ? 'sur ' . $partners->total() . ' au total' : 'of ' . $partners->total() . ' total', '#3FA96A'],
        ['hourglass',   '#C97A16', '#FDF3E0', $partnerKpis['pending'],     $isFr ? 'En Attente' : 'Pending',                  $isFr ? 'demandes en cours' : 'requests in progress', '#E9A83A'],
        ['globe',       '#3565DE', '#E8EFFB', $partnerKpis['international'], $isFr ? 'Internationaux' : 'International',    $partnerKpis['international_pct'] . ($isFr ? '% du total' : '% of total'), '#3565DE'],
        ['building-2',  '#7C4FE0', '#F0EAFB', $partnerKpis['national'],    $isFr ? 'Nationaux' : 'National',                  $partnerKpis['national_pct'] . ($isFr ? '% du total' : '% of total'), '#7C4FE0'],
        ['star',        '#8A5A1F', '#F5EEDD', $partnerKpis['premium'],     $isFr ? 'Partenariats Premium' : 'Premium Partnerships', $isFr ? 'niveau premium' : 'premium level', '#B98A3D'],
    ];

    // Donut — répartition par type (real, from $byType/$byTypePct)
    $donutColors = ['Institutionnel' => '#157A43', 'Privé' => '#7C4FE0', 'International' => '#3565DE', 'Finance' => '#C97A16'];
    $deg = 0; $conicStops = [];
    foreach ($byTypePct as $tName => $pct) {
        $start = $deg; $deg += $pct / 100 * 360;
        $conicStops[] = ($donutColors[$tName] ?? '#8A857A') . " {$start}deg {$deg}deg";
    }
    $typeConic = 'conic-gradient(' . implode(', ', $conicStops) . ')';

    $logoFor = function ($p) {
        $known = [
            'MINAC' => 'partner-minac.png', 'MINCOMMERCE' => 'partner-mincommerce.png', 'UNESCO' => 'partner-unesco.png',
            'ITC' => 'partner-itc.png', 'CEPII Cameroun' => 'partner-cepii.png', 'OAPI' => 'partner-oapi.png',
            'Banque Africaine de Développement' => 'partner-bad.png', 'AFD' => 'partner-afd.png', 'Union Européenne' => 'partner-ue.png',
        ];
        return $known[$p->name_fr] ?? null;
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Partenaires — Administration' : 'Partners — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', [
            'pageTitle' => 'PARTNERS',
            'pageSubtitle' => $isFr ? 'Ensemble, valorisons l\'artisanat et notre patrimoine.' : 'Together, let\'s promote craftsmanship and our heritage.',
            'pageSearchPlaceholder' => $isFr ? 'Rechercher un partenaire, secteur, pays...' : 'Search a partner, sector, country...',
        ])

        <main class="px-5 lg:px-7 pt-5 pb-8">

            @if(session('success'))
            <div class="bg-[#E2F3E8] border border-[#CFE0D4] text-[#157A43] text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
            </div>
            @endif

            {{-- 5 KPI stat cards --}}
            <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($partnerCards as [$pcIcon, $pcColor, $pcTile, $pcValue, $pcLabel, $pcSub, $pcSpark])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4 flex flex-col">
                    <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center" style="background-color: {{ $pcTile }}">
                        <i data-lucide="{{ $pcIcon }}" class="w-[22px] h-[22px]" style="color: {{ $pcColor }};stroke-width:1.8"></i>
                    </span>
                    <p class="mt-3 text-[24px] font-extrabold text-[#1B1B18] leading-none">{{ $pcValue }}</p>
                    <p class="mt-1 text-[12.5px] font-semibold text-[#3B382F]">{{ $pcLabel }}</p>
                    <p class="mt-0.5 text-[11px] text-[#8A857A]">{{ $pcSub }}</p>
                    <svg viewBox="0 0 120 28" class="mt-2 w-full h-[26px]" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="0,20 15,16 30,18 45,10 60,14 75,7 90,11 105,5 120,8" fill="none" stroke="{{ $pcSpark }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
                    </svg>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5 items-start">
                {{-- Main: list + filters + table --}}
                <div class="bg-white border border-[#EFF0EF] rounded-2xl overflow-hidden">
                    <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-[#F1F1EF]">
                        <h2 class="text-[15px] font-bold text-[#1B1B18] tracking-wide">{{ $isFr ? 'LISTE DES PARTENAIRES' : 'PARTNERS LIST' }}</h2>
                    </div>

                    <form method="GET" class="px-5 py-3.5 flex flex-wrap items-center gap-2.5 border-b border-[#F1F1EF]">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex items-center gap-2 bg-[#F8F8F6] border border-[#E7E7E5] rounded-lg px-3 h-[38px] flex-1 min-w-[180px]">
                            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher un partenaire...' : 'Search a partner...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none">
                            <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
                        </div>
                        <select name="status" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                            <option value="">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                            <option value="active" @selected($status==='active')>{{ $isFr ? 'Actif' : 'Active' }}</option>
                            <option value="pending" @selected($status==='pending')>{{ $isFr ? 'En attente' : 'Pending' }}</option>
                        </select>
                        <select name="type" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                            <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                            @foreach($partnerTypes as $pt)
                            <option value="{{ $pt }}" @selected($type===$pt)>{{ $pt }}</option>
                            @endforeach
                        </select>
                        <select name="country" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                            <option value="">{{ $isFr ? 'Tous les pays' : 'All countries' }}</option>
                            @foreach($partnerCountries as $pc)
                            <option value="{{ $pc }}" @selected($country===$pc)>{{ $pc }}</option>
                            @endforeach
                        </select>
                        <a href="#add-partner" class="h-[38px] inline-flex items-center gap-1.5 bg-[#0F4824] hover:bg-[#14652F] text-white text-[12.5px] font-semibold px-4 rounded-lg">
                            <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un partenaire' : 'Add a partner' }}
                        </a>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10.5px] font-bold text-[#8A857A] tracking-wide">
                                    <th class="px-5 py-2.5">{{ $isFr ? 'PARTENAIRE' : 'PARTNER' }}</th>
                                    <th class="px-3 py-2.5">{{ $isFr ? 'TYPE' : 'TYPE' }}</th>
                                    <th class="px-3 py-2.5">{{ $isFr ? 'PAYS' : 'COUNTRY' }}</th>
                                    <th class="px-3 py-2.5">{{ $isFr ? 'SECTEUR' : 'SECTOR' }}</th>
                                    <th class="px-3 py-2.5">{{ $isFr ? 'STATUT' : 'STATUS' }}</th>
                                    <th class="px-3 py-2.5">{{ $isFr ? 'DATE DE PARTENARIAT' : 'PARTNERSHIP DATE' }}</th>
                                    <th class="px-5 py-2.5 text-right">{{ $isFr ? 'ACTIONS' : 'ACTIONS' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partners as $partner)
                                <tr class="border-t border-[#F1F1EF] hover:bg-[#FAFAF8]">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-lg bg-[#F8F8F6] border border-[#EFEFED] flex items-center justify-center shrink-0 overflow-hidden">
                                                @if($logoFor($partner))
                                                <img src="{{ asset('images/landing/' . $logoFor($partner)) }}" alt="" class="w-full h-full object-contain">
                                                @elseif($partner->logo)
                                                <img src="{{ asset('storage/' . $partner->logo) }}" alt="" class="w-full h-full object-contain">
                                                @else
                                                <i data-lucide="building-2" class="w-4 h-4 text-[#B9B4A9]"></i>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}</p>
                                                <p class="text-[11px] text-[#8A857A] truncate">{{ $partner->contact_email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $typeCls[$partner->partner_type] ?? 'bg-[#EEECE6] text-[#6F6B60]' }}">{{ $partner->partner_type }}</span></td>
                                    <td class="px-3 py-3 text-[12.5px] text-[#3B382F]"><span class="inline-flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#8A857A]"></i>{{ $partner->country }}</span></td>
                                    <td class="px-3 py-3 text-[12.5px] text-[#3B382F]">{{ $partner->sector_fr }}</td>
                                    <td class="px-3 py-3">
                                        @php [$sCls, $sLabel] = $statusCls[$partner->status] ?? ['bg-[#EEECE6] text-[#6F6B60]', $partner->status]; @endphp
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $sCls }}">{{ $sLabel }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-[12px] text-[#6F6B60]">{{ $fmtDate($partner->start_date) }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.partners.detail', ['id' => $partner->id, 'lang' => $lang]) }}" class="p-1.5 rounded-lg hover:bg-[#E2F3E8] text-[#157A43]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.partners.detail', ['id' => $partner->id, 'lang' => $lang]) }}#edit" class="p-1.5 rounded-lg hover:bg-[#E8EFFB] text-[#3565DE]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                            <form method="POST" action="{{ route('admin.partners.destroy', ['id' => $partner->id]) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer ce partenaire ?' : 'Remove this partner?' }}')">
                                                @csrf
                                                <button type="submit" class="p-1.5 rounded-lg hover:bg-[#FDE8E8] text-[#DC2626]" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"><i data-lucide="more-vertical" class="w-4 h-4"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center py-10 text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucun partenaire trouvé.' : 'No partners found.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($partners->total() > 0)
                    <div class="px-5 py-3.5 border-t border-[#F1F1EF] flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $partners->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $partners->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $partners->total() }} {{ $isFr ? 'partenaires' : 'partners' }}</p>
                        <div class="flex items-center gap-1">
                            {{ $partners->onEachSide(1)->links() }}
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Right rail: analytics --}}
                <div class="space-y-5">
                    <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                        <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'RÉPARTITION PAR TYPE' : 'BREAKDOWN BY TYPE' }}</h3>
                        <div class="flex items-center gap-4">
                            <span class="w-[96px] h-[96px] rounded-full shrink-0" style="background: {{ $typeConic }}"><span class="block w-[52px] h-[52px] rounded-full bg-[#FEFAF4] m-[22px]"></span></span>
                            <ul class="space-y-1.5 min-w-0">
                                @foreach($byTypePct as $tName => $pct)
                                <li class="flex items-center gap-2 text-[12px] text-[#3B382F]">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $donutColors[$tName] ?? '#8A857A' }}"></span>
                                    <span class="flex-1 truncate">{{ $tName }}</span>
                                    <span class="font-semibold shrink-0">{{ $pct }}% ({{ $byType[$tName] }})</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5">
                        <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'RÉPARTITION PAR SECTEUR' : 'BREAKDOWN BY SECTOR' }}</h3>
                        <div class="space-y-3">
                            @foreach($bySectorPct as $secName => $pct)
                            <div>
                                <div class="flex items-center justify-between text-[12px] mb-1">
                                    <span class="text-[#3B382F] truncate">{{ $secName }}</span>
                                    <span class="font-semibold text-[#1B1B18] shrink-0">{{ $pct }}% ({{ $bySector[$secName] }})</span>
                                </div>
                                <div class="h-[6px] bg-[#F1F1EF] rounded-full overflow-hidden">
                                    <div class="h-full bg-[#157A43] rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-2xl p-5 text-white" style="background: linear-gradient(135deg, #0F4824, #14652F)">
                        <div class="flex items-center gap-2 mb-2"><i data-lucide="crown" class="w-4 h-4 text-[#E9C25A]"></i><h3 class="text-[13px] font-bold tracking-wide">{{ $isFr ? 'PARTENARIATS PREMIUM' : 'PREMIUM PARTNERSHIPS' }}</h3></div>
                        <p class="text-[12px] text-[#E8F2EC] leading-relaxed mb-3">{{ $isFr ? 'Mettez en avant vos partenaires stratégiques avec le statut Premium et plus d\'avantages.' : 'Highlight your strategic partners with Premium status and more benefits.' }}</p>
                        <a href="{{ route('admin.subscriptions', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-[12px] font-semibold px-3.5 py-2 rounded-lg">{{ $isFr ? 'Découvrir les avantages' : 'Discover the benefits' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                    </div>

                    <div class="relative rounded-2xl p-5 text-white overflow-hidden" style="background: linear-gradient(135deg, #0A2E18, #0F4824)">
                        <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="absolute right-2 bottom-0 h-[90%] w-auto opacity-90" aria-hidden="true">
                        <p class="relative text-[12.5px] italic leading-relaxed max-w-[75%]">{{ $isFr ? 'Nos partenaires sont les piliers de notre mission. Ensemble, construisons un avenir durable.' : 'Our partners are the pillars of our mission. Together, let\'s build a sustainable future.' }}</p>
                        <p class="relative mt-2 text-[10px] tracking-[0.2em] text-[#E9C25A]">••《•》••</p>
                    </div>
                </div>
            </div>

            {{-- Bottom CTA band --}}
            <div class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl p-6 flex flex-wrap items-center gap-6 justify-between">
                <div class="max-w-[520px]">
                    <h3 class="text-[15px] font-bold text-[#1B1B18] mb-1.5">{{ $isFr ? 'DEVENIR PARTENAIRE' : 'BECOME A PARTNER' }}</h3>
                    <p class="text-[12.5px] text-[#6F6B60] leading-relaxed">{{ $isFr ? 'Rejoignez notre réseau de partenaires et contribuez à la valorisation de l\'artisanat camerounais et africain.' : 'Join our partner network and contribute to promoting Cameroonian and African craftsmanship.' }}</p>
                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2">
                        @foreach(($isFr ? ['handshake'=>'Visibilité accrue','heart'=>'Impact durable','users'=>'Réseau influent','layers'=>'Projets collaboratifs'] : ['handshake'=>'Increased visibility','heart'=>'Lasting impact','users'=>'Influential network','layers'=>'Collaborative projects']) as $icon => $label)
                        <span class="flex items-center gap-1.5 text-[12px] text-[#3B382F]"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#157A43]"></i>{{ $label }}</span>
                        @endforeach
                    </div>
                </div>
                <a href="#add-partner" class="shrink-0 bg-[#0F4824] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 py-3 rounded-lg">{{ $isFr ? 'Faire une demande de partenariat' : 'Request a partnership' }}</a>
            </div>

            {{-- Add-partner form (project adaptation: PNG's CTA links here since the design has no separate creation screen) --}}
            <div id="add-partner" class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl p-5">
                <h2 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-4">{{ $isFr ? 'AJOUTER UN PARTENAIRE' : 'ADD A PARTNER' }}</h2>
                <form method="POST" action="{{ route('admin.partners.store') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input name="name_fr" required placeholder="{{ $isFr ? 'Nom (français)' : 'Name (French)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
                        <input name="name_en" placeholder="{{ $isFr ? 'Nom (anglais)' : 'Name (English)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <select name="tier" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
                            @foreach($tierLabels as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="url" name="website" placeholder="https://" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F]">
                    </div>
                    <textarea name="description_fr" rows="2" placeholder="{{ $isFr ? 'Description courte (français)' : 'Short description (French)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-[#14652F] resize-none"></textarea>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $isFr ? 'Logo' : 'Logo' }}</label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#E2F3E8] file:text-[#157A43] file:text-xs">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-[#14652F]">
                        {{ $isFr ? 'Actif (visible publiquement)' : 'Active (publicly visible)' }}
                    </label>
                    <button type="submit" class="bg-[#0F4824] hover:bg-[#14652F] text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter' : 'Add' }}
                    </button>
                </form>
            </div>
        </main>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
