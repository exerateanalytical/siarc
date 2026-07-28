@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'kyc';
    $ref = 'KYC-ART-' . \Carbon\Carbon::parse($app->created_at)->format('Y') . '-' . str_pad((string)$app->id, 6, '0', STR_PAD_LEFT);
    $statusMeta = ['submitted'=>[$isFr?'En attente':'Pending','#C97A16'],'under_review'=>[$isFr?'En vérification':'In review','#3565DE'],'approved'=>[$isFr?'Approuvée':'Approved','#157A43'],'rejected'=>[$isFr?'Rejetée':'Rejected','#DC2626'],'draft'=>[$isFr?'Brouillon':'Draft','#8A857A']];
    [$stLabel,$stColor] = $statusMeta[$app->status] ?? [$app->status,'#8A857A'];
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y à H:i') : '—';
    $docStatusMeta = ['accepted'=>[$isFr?'Validé':'Validated','#157A43','check-circle-2'],'pending'=>[$isFr?'En attente':'Pending','#C97A16','clock'],'rejected'=>[$isFr?'Rejeté':'Rejected','#DC2626','x-circle']];
    $steps = [[$isFr?'Soumis':'Submitted',true],[$isFr?'Vérification Document':'Document check',true],[$isFr?'Vérification Identité':'Identity check',$app->status!=='submitted'],[$isFr?'Vérification Artisanat':'Craft check',in_array($app->status,['approved'])],[$isFr?'Décision':'Decision',$app->status==='approved'||$app->status==='rejected']];
    $infoPerso = [
        [$isFr?'Nom complet':'Full name', $app->owner_name ?? '—'],
        [$isFr?'Nationalité':'Nationality', $isFr?'Camerounaise':'Cameroonian'],
        [$isFr?'Email':'Email', $app->owner_email ?? '—'],
        [$isFr?'Téléphone':'Phone', $app->owner_phone ?? '—'],
        [$isFr?'Région':'Region', $app->region_fr ?? '—'],
    ];
    $infoPro = [
        [$isFr?'Spécialité principale':'Main specialty', $app->industry_fr ?? '—'],
        [$isFr?'Atelier/Boutique':'Workshop/Shop', $app->business_name ?? '—'],
        [$isFr?'Niveau demandé':'Requested tier', ucfirst($app->tier_requested ?? 'verified')],
        [$isFr?'Type':'Type', $app->vendor_type === 'artisan' ? ($isFr?'Individuel':'Individual') : ($isFr?'Entreprise':'Business')],
    ];
    $resume = [
        ['Documents', $isFr?'Validé':'Validated','#157A43'],
        [$isFr?'Identité':'Identity', $isFr?'En cours':'In progress','#C97A16'],
        [$isFr?'Preuve d\'activité':'Activity proof', $isFr?'En attente':'Pending','#8A857A'],
        [$isFr?'Références':'References', $isFr?'En attente':'Pending','#8A857A'],
    ];
    $pageTitle = 'KYC CENTRE';
    $pageBreadcrumb = [['KYC Centre', route('admin.kyc')],[$isFr?'Demandes':'Requests', route('admin.kyc')],[$isFr?'Détail de Vérification':'Verification detail', null]];
    $pageSearchPlaceholder = $isFr?'Rechercher un utilisateur, une demande...':'Search a user, a request...';
@endphp

@section('content')
            @if(session('success'))<div class="ui-alert ui-alert-ok mb-4"><i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43] dark:text-[#339B56]"></i>{{ session('success') }}</div>@endif

            {{-- Title + actions --}}
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="flex items-center gap-3 text-[20px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr?'DÉTAIL DE VÉRIFICATION ARTISAN':'ARTISAN VERIFICATION DETAIL' }} <span class="rounded-md px-2.5 py-0.5 text-[11px] font-semibold" style="background-color:{{ $stColor }}1a;color:{{ $stColor }}">{{ $stLabel }}</span></h1>
                    <p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778]">ID {{ $isFr?'Demande':'Request' }} : {{ $ref }}</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <form method="POST" action="{{ route('admin.verifications.approve', ['id'=>$app->id]) }}">@csrf
                        <button type="submit" class="ui-btn ui-btn-secondary"><i data-lucide="check" class="w-4 h-4"></i>{{ $isFr?'Approuver':'Approve' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.verifications.reject', ['id'=>$app->id]) }}">@csrf
                        <button type="submit" class="ui-btn ui-btn-danger"><i data-lucide="x" class="w-4 h-4"></i>{{ $isFr?'Rejeter':'Reject' }}</button>
                    </form>
                    <a href="{{ route('admin.kyc', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="chevron-down" class="w-4 h-4"></i>{{ $isFr?'Plus d\'actions':'More' }}</a>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Identity card --}}
                    <section class="ui-card flex flex-wrap items-start gap-5">
                        @if($app->logo)<img src="{{ asset('storage/'.$app->logo) }}" alt="" class="w-[96px] h-[96px] rounded-full object-cover shrink-0">
                        @else<span class="w-[96px] h-[96px] rounded-full bg-[#14652F] dark:bg-[#2E9250] text-white dark:text-[#04150A] text-[28px] font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($app->owner_name ?? 'A',0,1)) }}</span>@endif
                        <div class="min-w-0">
                            <h2 class="flex items-center gap-2 text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $app->owner_name ?? $app->business_name }} <i data-lucide="badge-check" class="w-5 h-5 text-[#157A43] dark:text-[#339B56]"></i></h2>
                            <span class="ui-pill ui-pill-ok mt-1">Artisan</span>
                            <p class="mt-2 text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]"><i data-lucide="briefcase" class="inline w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i> {{ $isFr?'Spécialité':'Specialty' }} : {{ $app->industry_fr ?? '—' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]"><i data-lucide="map-pin" class="inline w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i> {{ $app->region_fr ?? 'Cameroun' }}, Cameroun</p>
                            <p class="mt-1 text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]"><i data-lucide="mail" class="inline w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i> {{ $app->owner_email ?? '—' }}</p>
                        </div>
                        <div class="ml-auto text-[12px] text-[#3B382F] dark:text-[#B4B5A6] space-y-1 hidden lg:block">
                            <p>{{ $isFr?'Soumis le':'Submitted' }} : <b>{{ $dtf($app->submitted_at ?? $app->created_at) }}</b></p>
                            <p>{{ $isFr?'Dernière mise à jour':'Last update' }} : <b>{{ $dtf($app->updated_at) }}</b></p>
                            <p>Canal : <b>Web</b></p>
                        </div>
                    </section>

                    {{-- Info perso + pro --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        <section class="ui-card"><h2 class="ui-card-title">{{ $isFr?'Informations Personnelles':'Personal Information' }}</h2><dl class="mt-3.5 space-y-2.5 text-[12px]">@foreach($infoPerso as [$l,$v])<div class="flex items-center justify-between gap-3 border-b border-[#EFEBE2] dark:border-[#262B21] pb-2"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach</dl></section>
                        <section class="ui-card"><h2 class="ui-card-title">{{ $isFr?'Informations Professionnelles':'Professional Information' }}</h2><dl class="mt-3.5 space-y-2.5 text-[12px]">@foreach($infoPro as [$l,$v])<div class="flex items-center justify-between gap-3 border-b border-[#EFEBE2] dark:border-[#262B21] pb-2"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach</dl></section>
                    </div>

                    {{-- Documents --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Documents Soumis':'Submitted Documents' }}</h2>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                            @forelse($documents as $doc)
                            @php [$dsL,$dsC,$dsI] = $docStatusMeta[$doc->status] ?? [$doc->status,'#8A857A','clock']; @endphp
                            <div class="border border-[#EFEBE2] dark:border-[#262B21] rounded-xl overflow-hidden">
                                <div class="h-[70px] bg-[#F3F0E6] dark:bg-[#1A1E16] flex items-center justify-center"><i data-lucide="file-text" class="w-7 h-7 text-[#B9B4A9] dark:text-[#868778]"></i></div>
                                <div class="p-2.5"><p class="text-[11px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight line-clamp-2">{{ $doc->original_name }}</p><p class="mt-1.5 flex items-center gap-1 text-[10.5px] font-semibold" style="color:{{ $dsC }}"><i data-lucide="{{ $dsI }}" class="w-3 h-3"></i>{{ $dsL }}</p></div>
                            </div>
                            @empty
                            <p class="col-span-full text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Aucun document soumis.':'No documents submitted.' }}</p>
                            @endforelse
                        </div>
                        <a href="{{ route('admin.kyc', ['lang'=>$lang]) }}" class="mt-4 inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43] dark:text-[#339B56]"><i data-lucide="eye" class="w-4 h-4"></i>{{ $isFr?'Voir tous les documents':'View all documents' }}</a>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Statut de vérification':'Verification status' }}</h2>
                        <div class="mt-4 space-y-3">
                            @foreach($steps as $i => [$sLabel, $sDone])
                            <div class="flex items-center gap-3"><span class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold {{ $sDone ? 'bg-[#157A43] dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'bg-[#F0EFEA] dark:bg-[#1A1E16] text-[#8A857A] dark:text-[#868778] ' }}">{{ $sDone ? '' : ($i+1) }}@if($sDone)<i data-lucide="check" class="w-3.5 h-3.5"></i>@endif</span><span class="text-[12px] {{ $sDone?'font-semibold text-[#1B1B18] dark:text-[#F3EFE7]':'text-[#6F6B60] dark:text-[#868778] ' }}">{{ $sLabel }}</span></div>
                            @endforeach
                        </div>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Résumé de la vérification':'Verification summary' }}</h2>
                        <dl class="mt-3.5 space-y-2.5 text-[12px]">@foreach($resume as [$l,$v,$c])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }}</dt><dd class="inline-flex items-center gap-1 font-semibold" style="color:{{ $c }}"><i data-lucide="{{ $c==='#157A43'?'check-circle-2':'clock' }}" class="w-3.5 h-3.5"></i>{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Notes de l\'agent':'Agent notes' }}</h2>
                        <p class="mt-3 text-[12px] text-[#3B382F] dark:text-[#B4B5A6] leading-relaxed">{{ $app->reviewer_notes ?? ($isFr?'Documents d\'identité valides. Vérification d\'identité en cours. L\'atelier semble authentique, visite recommandée.':'Valid ID documents. Identity check in progress. The workshop looks authentic, a visit is recommended.') }}</p>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
