@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'notifications';
    $pageTitle = $isFr?'CENTRE DE NOTIFICATIONS':'NOTIFICATIONS CENTRE';
    $pageBreadcrumb = [['Tableau de bord', route('dashboard.admin')],['Notifications', null]];
    $pageSearchPlaceholder = $isFr?'Rechercher une notification...':'Search a notification...';
    $fmt = fn($n)=> number_format($n, 0, ',', ' ');
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    $cards = [
        ['bell', '#157A43', '#E8F2EC', $fmt($stats['total']), $isFr?'Total Notifications':'Total Notifications', '+'.$stats['this_month'].' '.($isFr?'ce mois':'this month')],
        ['mail', '#C97A16', '#FDF3E0', $fmt($stats['unread']), $isFr?'Non lues':'Unread', null],
        ['check-circle-2', '#157A43', '#E8F2EC', $fmt($stats['read']), $isFr?'Lues':'Read', null],
    ];
    $typeMeta = ['support'=>['file-text','#157A43','#E8F2EC'],'message'=>['user','#C97A16','#FDF3E0'],'article'=>['book-open','#7C4FE0','#F0EAFB'],'announcement'=>['megaphone','#157A43','#E8F2EC'],'account'=>['user-plus','#3565DE','#E8EFFB'],'reminder'=>['clock','#C97A16','#FDF3E0']];
    // Real per-type breakdown (no channel/send-status data exists to fabricate)
    $typeLabels = ['support'=>$isFr?'Support':'Support','message'=>'Messages','article'=>$isFr?'Articles':'Articles','announcement'=>$isFr?'Annonces':'Announcements','account'=>$isFr?'Compte':'Account','reminder'=>$isFr?'Rappels':'Reminders'];
    $typeTotal = max(1, $typeCounts->sum());
    $canaux = collect($typeCounts)->map(function ($count, $type) use ($typeLabels, $typeMeta, $typeTotal) {
        $label = $typeLabels[$type] ?? ucfirst($type);
        $icon = ($typeMeta[$type] ?? ['bell'])[0];
        $color = ($typeMeta[$type] ?? [null, '#8A857A'])[1];
        return [$label, number_format($count), round($count / $typeTotal * 100) . '%', $color, $icon];
    })->values();
    $tabs = [[$isFr?'Toutes':'All',true],[($isFr?'Non lues':'Unread').' ('.$stats['unread'].')',false],[$isFr?'Lues':'Read',false]];
@endphp

@section('content')
            {{-- Stat cards --}}
            <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($cards as [$cI,$cC,$cT,$cV,$cL,$cS])
                <div class="ui-card text-center">
                    <span class="w-[46px] h-[46px] mx-auto rounded-full flex items-center justify-center" style="background-color:{{ $cT }}"><i data-lucide="{{ $cI }}" class="w-[22px] h-[22px]" style="color:{{ $cC }}"></i></span>
                    <p class="mt-2 text-[11px] text-[#6F6B60]">{{ $cL }}</p>
                    <p class="text-[22px] font-bold text-[#1B1B18] leading-none">{{ $cV }}</p>
                    @if($cS)<p class="mt-1 text-[10.5px] text-[#157A43]">↗ {{ $cS }}</p>@endif
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_300px] gap-5 items-start">
                <section class="ui-card">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#EFEBE2] pb-3">
                        <div class="flex items-center gap-5 overflow-x-auto">@foreach($tabs as [$tL,$tA])<span class="whitespace-nowrap text-[13px] font-semibold {{ $tA?'text-[#14652F] border-b-2 border-[#14652F] pb-3 -mb-3':'text-[#8A857A]' }}">{{ $tL }}</span>@endforeach</div>
                        <a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="ui-btn ui-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr?'Nouvelle notification':'New notification' }}</a>
                    </div>
                    <div class="ui-table-wrap mt-4">
                        <table class="ui-table min-w-[720px]">
                            <thead><tr class="border-b border-[#EFEBE2]">
                                <th>{{ $isFr?'Titre / Message':'Title / Message' }}</th>
                                <th>Canal</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th class="text-right"></th>
                            </tr></thead>
                            <tbody>
                                @foreach($notifications as $n)
                                @php [$nI,$nC,$nT] = $typeMeta[$n->type] ?? ['bell','#157A43','#E8F2EC']; @endphp
                                <tr>
                                    <td><div class="flex items-start gap-3"><span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background-color:{{ $nT }}"><i data-lucide="{{ $nI }}" class="w-[18px] h-[18px]" style="color:{{ $nC }}"></i></span><span class="min-w-0"><a href="{{ route('notifications.show', ['id'=>$n->id, 'lang'=>$lang]) }}" class="block text-[12.5px] font-semibold text-[#1B1B18] hover:text-[#157A43]">{{ $n->title }}</a><span class="block text-[11px] text-[#8A857A] line-clamp-1">{{ $n->body }}</span></span></div></td>
                                    <td>Email</td>
                                    <td><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $n->read_at ? 'bg-[#EEECE6] text-[#6F6B60]' : 'bg-[#E2F3E8] text-[#157A43]' }}">{{ $n->read_at ? ($isFr?'Lue':'Read') : ($isFr?'Envoyée':'Sent') }}</span></td>
                                    <td class="whitespace-nowrap">{{ $dtf($n->created_at) }}</td>
                                    <td class="text-right"><a href="{{ route('notifications.show', ['id'=>$n->id, 'lang'=>$lang]) }}" class="inline-flex w-8 h-8 rounded-lg border border-[#EAE5D8] hover:border-[#14652F] items-center justify-center text-[#55524A]"><i data-lucide="more-horizontal" class="w-4 h-4"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($notifications->hasPages())
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr?'Affichage de':'Showing' }} {{ $notifications->firstItem() }} {{ $isFr?'à':'to' }} {{ $notifications->lastItem() }} {{ $isFr?'sur':'of' }} {{ $notifications->total() }} notifications</p>
                        <div class="flex items-center gap-1.5">
                            @if($notifications->onFirstPage())<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>@else<a href="{{ $notifications->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>@endif
                            @foreach($notifications->getUrlRange(1, $notifications->lastPage()) as $pn=>$url)@if($pn===$notifications->currentPage())<span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>@else<a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pn }}</a>@endif @endforeach
                            @if($notifications->hasMorePages())<a href="{{ $notifications->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>@else<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>@endif
                        </div>
                    </div>
                    @endif
                </section>

                <aside class="space-y-4">
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Résumé par type':'By type' }}</h2>
                        <div class="mt-3.5 space-y-3">@forelse($canaux as [$cnL,$cnN,$cnP,$cnC,$cnI])<div><div class="flex items-center justify-between text-[12px]"><span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="{{ $cnI }}" class="w-3.5 h-3.5" style="color:{{ $cnC }}"></i>{{ $cnL }}</span><span class="font-semibold text-[#1B1B18]">{{ $cnN }} ({{ $cnP }})</span></div><div class="mt-1 h-1.5 rounded-full bg-[#F0EFEA] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $cnP }};background-color:{{ $cnC }}"></span></div></div>@empty<p class="text-[12px] text-[#8A857A]">{{ $isFr?'Aucune notification.':'No notifications.' }}</p>@endforelse</div>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Paramètres rapides':'Quick settings' }}</h2>
                        <div class="mt-2 divide-y divide-[#F5F1E8]">
                            @foreach([['sliders-horizontal', $isFr?'Préférences de notification':'Notification preferences'],['layout-template', $isFr?'Modèles de notification':'Notification templates'],['users', $isFr?'Groupes de notification':'Notification groups']] as [$qI,$qL])
                            <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="{{ $qI }}" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qL }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></a>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
