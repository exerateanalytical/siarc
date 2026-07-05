@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'partners';
    $pName = $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr);
    $pageTitle = $isFr ? 'DÉTAIL DU PARTENAIRE' : 'PARTNER DETAIL';
    $pageBreadcrumb = [['Dashboard', route('dashboard.admin')], ['Partners', route('admin.partners')], [$pName, null]];
    $pageSearchPlaceholder = $isFr ? 'Rechercher un partenaire, email, pays...' : 'Search a partner, email, country...';
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y'); };

    $partnerTiles = ['MINCOMMERCE'=>'partner-mincommerce.png','MINAC'=>'partner-minac.png','UNESCO'=>'partner-unesco.png','ITC'=>'partner-itc.png','CEPII Cameroun'=>'partner-cepii.png','OAPI'=>'partner-oapi.png','Banque Africaine de Développement'=>'partner-bad.png','AFD'=>'partner-afd.png','Union Européenne'=>'partner-ue.png'];
    $logoFile = $partnerTiles[$partner->name_fr] ?? null;

    $tabs = [[$isFr?'Aperçu':'Overview',true],['Informations',false],['Contacts',false],['Accords',false],[$isFr?'Activités & Projets':'Activities & Projects',false],[$isFr?'Statistiques':'Statistics',false],['Documents',false],['Historique',false]];
    $infosCles = [
        [$isFr?'Type de partenariat':'Partnership type', $partner->partnership_type ?? 'Institutionnel', 'pill'],
        [$isFr?'Niveau de partenariat':'Partnership level', $partner->partnership_level ?? 'Standard', 'level'],
        [$isFr?'Durée du partenariat':'Duration', '3 ans', 'text'],
        [$isFr?'Date de début':'Start date', $dt($partner->start_date), 'text'],
        [$isFr?'Date de fin':'End date', $dt($partner->end_date), 'text'],
        [$isFr?'Renouvellement auto.':'Auto-renewal', $partner->auto_renew ? ($isFr?'Oui':'Yes') : ($isFr?'Non':'No'), 'toggle'],
        [$isFr?'Statut légal':'Legal status', $partner->legal_verified ? ($isFr?'Vérifié':'Verified') : '—', 'verified'],
    ];
    $indicateurs = [
        ['users', '#157A43', '#E8F2EC', '8', $isFr?'Projets collaboratifs':'Collaborative projects', '+2 '.($isFr?'ce mois':'this month')],
        ['heart-handshake', '#7C4FE0', '#F0EAFB', '245', $isFr?'Artisans soutenus':'Artisans supported', '+18 '.($isFr?'ce mois':'this month')],
        ['calendar-days', '#3565DE', '#E8EFFB', '12', $isFr?'Événements réalisés':'Events held', '+1 '.($isFr?'ce mois':'this month')],
        ['trending-up', '#C97A16', '#FDF3E0', '1.2M', $isFr?'Visibilité générée':'Visibility generated', '+15.5% '.($isFr?'ce mois':'this month')],
    ];
    $activites = [
        [$isFr?'Participation au SIARC 2024':'SIARC 2024 participation', '08 '.($isFr?'Mai':'May').' 2025'],
        [$isFr?'Atelier de formation des artisans':'Artisan training workshop', '22 '.($isFr?'Avr.':'Apr.').' 2025'],
        [$isFr?'Campagne de valorisation':'Promotion campaign', '15 '.($isFr?'Avr.':'Apr.').' 2025'],
        [$isFr?'Visite officielle au MINAC':'Official visit to MINAC', '03 '.($isFr?'Mar.':'Mar.').' 2025'],
        [$isFr?'Réunion stratégique':'Strategic meeting', '12 '.($isFr?'Fév.':'Feb.').' 2025'],
    ];
    $documents = [
        ['pdf', $isFr?'Convention de partenariat':'Partnership agreement', '12 '.($isFr?'Mai':'May').' 2024'],
        ['pdf', $isFr?'Accord de collaboration':'Collaboration agreement', '12 '.($isFr?'Mai':'May').' 2024'],
        ['doc', $isFr?'Plan d\'action 2024-2027':'Action plan 2024-2027', '15 '.($isFr?'Mai':'May').' 2024'],
        ['pdf', $isFr?'Charte de partenariat':'Partnership charter', '20 '.($isFr?'Mai':'May').' 2024'],
    ];
    $historique = [
        [$isFr?'Statut changé à Actif':'Status changed to Active', 'Admin Super', '12 '.($isFr?'Mai':'May').' 2024 à 14:30'],
        [$isFr?'Accord de partenariat ajouté':'Partnership agreement added', 'Admin Super', '12 '.($isFr?'Mai':'May').' 2024 à 14:25'],
        [$isFr?'Partenaire créé':'Partner created', 'Admin Super', '12 '.($isFr?'Mai':'May').' 2024 à 14:20'],
    ];
    $avantages = [
        ['globe', $isFr?'Visibilité nationale':'National visibility', $isFr?'Promotion sur la plateforme et réseaux partenaires':'Promotion on the platform and partner networks'],
        ['users', $isFr?'Accès aux artisans':'Access to artisans', $isFr?'Connexion directe avec les artisans vérifiés':'Direct connection with verified artisans'],
        ['calendar-check', $isFr?'Événements exclusifs':'Exclusive events', $isFr?'Participation aux événements et foires organisés':'Participation in organised events and fairs'],
        ['git-merge', 'Co-branding', $isFr?'Utilisation conjointe de marque et communication':'Joint brand and communication use'],
        ['bar-chart-3', 'Rapports & Analytics', $isFr?'Rapports détaillés sur l\'impact et la visibilité':'Detailed impact and visibility reports'],
    ];
@endphp
@section('content')
            @if(session('success'))<div class="mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]"><i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>{{ session('success') }}</div>@endif

            {{-- Actions row --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.partners', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour à la liste':'Back to list' }}</a>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('admin.partners', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#14652F]"><i data-lucide="pencil" class="w-4 h-4"></i>{{ $isFr?'Modifier':'Edit' }}</a>
                    <a href="{{ route('admin.partners', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#EAD9AC] hover:border-[#C9942E] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#C97A16]"><i data-lucide="pause" class="w-4 h-4"></i>{{ $isFr?'Suspendre':'Suspend' }}</a>
                    <a href="{{ route('admin.partners', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#F5C9C9] hover:border-[#DC2626] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#DC2626]"><i data-lucide="trash-2" class="w-4 h-4"></i>{{ $isFr?'Supprimer':'Delete' }}</a>
                </div>
            </div>

            {{-- Partner card --}}
            <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 grid grid-cols-1 lg:grid-cols-[auto_1fr_auto] gap-6 items-start">
                <div class="w-[130px] h-[130px] rounded-full border-4 border-[#F0EBDD] flex items-center justify-center bg-white shrink-0 overflow-hidden">
                    @if($logoFile)<img src="{{ asset('images/landing/'.$logoFile) }}" alt="{{ $pName }}" class="w-[100px] h-[100px] object-contain">
                    @else<span class="text-[32px] font-bold text-[#14652F]">{{ mb_strtoupper(mb_substr($pName,0,2)) }}</span>@endif
                </div>
                <div class="min-w-0">
                    <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">{{ $pName }} <span class="rounded-md px-2.5 py-0.5 text-[11.5px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr?'Actif':'Active' }}</span> <i data-lucide="award" class="w-5 h-5 text-[#C9942E]"></i></h1>
                    <span class="mt-2 inline-block rounded-md px-2.5 py-1 text-[11px] font-semibold bg-[#EFF6F1] text-[#157A43]">{{ $partner->partnership_type ?? 'Institutionnel' }}</span>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1.5 text-[12.5px] text-[#3B382F]">
                        <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#8A857A]"></i>{{ $partner->contact_email }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="briefcase" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr?'Secteur':'Sector' }} : {{ $partner->sector_fr }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#8A857A]"></i>{{ $partner->contact_phone }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="flag" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr?'Pays':'Country' }} : {{ $partner->country }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="globe" class="w-4 h-4 text-[#8A857A]"></i>{{ $partner->website }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr?'Date de partenariat':'Partnership date' }} : {{ $dt($partner->start_date) }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>{{ $partner->address }}</p>
                        <p class="flex items-center gap-2"><i data-lucide="hash" class="w-4 h-4 text-[#8A857A]"></i>ID {{ $isFr?'Partenaire':'Partner' }} : {{ $partner->partner_ref }}</p>
                    </div>
                </div>
                <div class="border-l border-[#F0F1F0] pl-6 hidden lg:block">
                    <p class="text-[12px] font-bold text-[#1B1B18]">{{ $isFr?'Responsable du partenariat':'Partnership manager' }}</p>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-full bg-[#14652F] text-white text-[13px] font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($partner->responsible_name,0,1)) }}</span>
                        <div><p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $partner->responsible_name }}</p><p class="text-[11px] text-[#6F6B60]">{{ $partner->responsible_title }}</p></div>
                    </div>
                    <p class="mt-2.5 flex items-center gap-2 text-[11.5px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#8A857A]"></i>{{ $partner->responsible_email }}</p>
                    <p class="mt-1 flex items-center gap-2 text-[11.5px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#8A857A]"></i>{{ $partner->contact_phone }}</p>
                </div>
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div>
                    {{-- Tabs --}}
                    <div class="flex items-center gap-6 border-b border-[#EAE7DE] overflow-x-auto">@foreach($tabs as [$tLabel, $tActive])<span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}">{{ $tLabel }}</span>@endforeach</div>

                    <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1B1B18] uppercase">{{ $isFr?'À propos du partenaire':'About the partner' }}</h2>
                            <p class="mt-3 text-[12.5px] text-[#55524A] leading-relaxed">{{ $isFr ? ($partner->description_fr ?? 'Institution partenaire de la plateforme.') : ($partner->description_en ?? $partner->description_fr ?? 'Platform partner institution.') }}</p>
                            <div class="mt-4 bg-[#F7F9F7] border border-[#EAF0EB] rounded-xl px-4 py-3.5">
                                <p class="flex items-center gap-2 text-[12px] font-bold text-[#157A43]"><i data-lucide="target" class="w-4 h-4"></i>{{ $isFr?'Objectif du partenariat':'Partnership objective' }}</p>
                                <p class="mt-1.5 text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Promouvoir l\'artisanat camerounais, soutenir les artisans locaux et préserver le patrimoine culturel à travers des initiatives conjointes.' : 'Promote Cameroonian craftsmanship, support local artisans and preserve cultural heritage through joint initiatives.' }}</p>
                            </div>
                        </section>
                        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                            <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1B1B18] uppercase">{{ $isFr?'Informations clés':'Key information' }}</h2>
                            <dl class="mt-3.5 space-y-2.5 text-[12px]">
                                @foreach($infosCles as [$l, $v, $kind])
                                <div class="flex items-center justify-between gap-3 border-b border-[#F4F5F4] pb-2"><dt class="text-[#6F6B60]">{{ $l }}</dt>
                                    <dd class="text-right">
                                        @if($kind==='pill')<span class="rounded-md px-2 py-0.5 text-[11px] font-semibold bg-[#EFF6F1] text-[#157A43]">{{ $v }}</span>
                                        @elseif($kind==='level')<span class="inline-flex items-center gap-1 font-semibold text-[#C9942E]"><i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>{{ $v }}</span>
                                        @elseif($kind==='toggle')<span class="inline-flex items-center gap-1.5 font-semibold text-[#157A43]"><span class="w-8 h-4 rounded-full bg-[#157A43] relative"><span class="absolute right-0.5 top-0.5 w-3 h-3 rounded-full bg-white"></span></span>{{ $v }}</span>
                                        @elseif($kind==='verified')<span class="inline-flex items-center gap-1 font-semibold text-[#157A43]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $v }}</span>
                                        @else<span class="font-semibold text-[#1B1B18]">{{ $v }}</span>@endif
                                    </dd>
                                </div>
                                @endforeach
                            </dl>
                        </section>
                    </div>

                    {{-- Indicateurs --}}
                    <section class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1B1B18] uppercase">{{ $isFr?'Indicateurs de performance':'Performance indicators' }}</h2>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach($indicateurs as [$iIcon, $iColor, $iTile, $iVal, $iLabel, $iSub])
                            <div class="border border-[#EFF0EF] rounded-xl px-3.5 py-3"><span class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: {{ $iTile }}"><i data-lucide="{{ $iIcon }}" class="w-[18px] h-[18px]" style="color: {{ $iColor }}"></i></span><p class="mt-2 text-[20px] font-bold text-[#1B1B18] leading-none">{{ $iVal }}</p><p class="text-[11px] font-semibold text-[#3B382F]">{{ $iLabel }}</p><p class="text-[10px] text-[#157A43]">{{ $iSub }}</p></div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center justify-between text-[11.5px]"><span class="text-[#6F6B60]">{{ $isFr?'Niveau d\'engagement':'Engagement level' }}</span><span class="font-semibold text-[#1B1B18]">85%</span></div>
                            <div class="mt-1.5 h-2 rounded-full bg-[#F0EFEA] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:85%"></span></div>
                            <p class="mt-1.5 flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]"><i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>{{ $isFr?'Excellent partenariat':'Excellent partnership' }}</p>
                        </div>
                    </section>

                    {{-- Avantages --}}
                    <section class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[13px] font-bold tracking-[0.05em] text-[#1B1B18] uppercase">{{ $isFr?'Avantages du partenariat':'Partnership benefits' }}</h2>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                            @foreach($avantages as [$aIcon, $aTitle, $aDesc])
                            <div><span class="w-9 h-9 rounded-lg bg-[#F3F0E6] flex items-center justify-center"><i data-lucide="{{ $aIcon }}" class="w-[18px] h-[18px] text-[#14652F]"></i></span><p class="mt-2 text-[12px] font-bold text-[#1B1B18]">{{ $aTitle }}</p><p class="mt-0.5 text-[10.5px] text-[#6F6B60] leading-snug">{{ $aDesc }}</p></div>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1B1B18] uppercase">{{ $isFr?'Statut du partenaire':'Partner status' }}</h2>
                        <div class="mt-3 flex items-center gap-3"><span class="w-11 h-11 rounded-full bg-[#E2F3E8] flex items-center justify-center"><i data-lucide="check-circle-2" class="w-6 h-6 text-[#157A43]"></i></span><div><p class="text-[13px] font-bold text-[#157A43]">{{ $isFr?'Partenaire Actif':'Active Partner' }}</p><p class="text-[11px] text-[#6F6B60]">{{ $isFr?'Partenariat en cours depuis 1 an':'Partnership ongoing for 1 year' }}</p></div></div>
                        <div class="mt-3 flex items-center justify-between border-t border-[#F0F1F0] pt-3"><span class="text-[11.5px] text-[#6F6B60]">{{ $isFr?'Fiabilité':'Reliability' }}</span><span class="flex items-center gap-1 text-[#C9942E]">@for($i=0;$i<5;$i++)<i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>@endfor<span class="ml-1 text-[12px] font-semibold text-[#1B1B18]">{{ $partner->reliability }}/5</span></span></div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <div class="flex items-center justify-between"><h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1B1B18] uppercase">{{ $isFr?'Documents principaux':'Main documents' }}</h2><a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="text-[11px] font-semibold text-[#157A43]">{{ $isFr?'Voir tout':'View all' }}</a></div>
                        <div class="mt-3 space-y-2.5">
                            @foreach($documents as [$dType, $dName, $dDate])
                            <a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="flex items-center gap-3 group"><span class="w-7 h-8 rounded flex items-center justify-center {{ $dType==='pdf'?'bg-[#FDE8E8] text-[#DC2626]':'bg-[#E8EFFB] text-[#3565DE]' }}"><i data-lucide="file-text" class="w-4 h-4"></i></span><span class="min-w-0 flex-1"><span class="block text-[12px] font-medium text-[#1B1B18] truncate group-hover:text-[#157A43]">{{ $dName }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $dDate }}</span></span></a>
                            @endforeach
                        </div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1B1B18] uppercase">{{ $isFr?'Historique des mises à jour':'Update history' }}</h2>
                        <div class="mt-3 space-y-3">
                            @foreach($historique as [$hEvent, $hBy, $hWhen])
                            <div class="flex gap-3"><span class="w-2 h-2 rounded-full bg-[#157A43] mt-1.5 shrink-0"></span><div><p class="text-[12px] font-semibold text-[#1B1B18]">{{ $hEvent }}</p><p class="text-[10.5px] text-[#8A857A]">{{ $isFr?'Par':'By' }} {{ $hBy }} · {{ $hWhen }}</p></div></div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="mt-3 block text-center border border-[#E5E7E5] hover:border-[#14652F] rounded-lg py-2 text-[12px] font-semibold text-[#3B382F]">{{ $isFr?'Voir tout l\'historique':'View all history' }}</a>
                    </section>
                    <section class="bg-[#0E3D22] rounded-2xl px-5 py-5">
                        <h2 class="text-[12px] font-bold tracking-[0.06em] text-white uppercase">{{ $isFr?'Actions rapides':'Quick actions' }}</h2>
                        <div class="mt-3 space-y-2">
                            <a href="{{ route('messages.inbox', ['lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-white/10 hover:bg-white/15 rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-white"><i data-lucide="mail" class="w-4 h-4"></i>{{ $isFr?'Envoyer un message':'Send a message' }}</a>
                            <a href="{{ route('admin.events', ['lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-white/10 hover:bg-white/15 rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-white"><i data-lucide="calendar-plus" class="w-4 h-4"></i>{{ $isFr?'Planifier une réunion':'Schedule a meeting' }}</a>
                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="flex items-center gap-2.5 bg-white/10 hover:bg-white/15 rounded-lg px-3.5 py-2.5 text-[12.5px] font-semibold text-white"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr?'Télécharger le kit média':'Download media kit' }}</a>
                        </div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
@endsection
