@php
    $isFr = $lang === 'fr';
    $adminActive = 'notifications';
    $fmt = fn($n)=> number_format($n, 0, ',', ' ');
    $dtf = fn($v)=> $v ? \Carbon\Carbon::parse($v)->format('d M Y, H:i') : '—';
    $cards = [
        ['bell', '#157A43', '#E8F2EC', $fmt($stats['total']), $isFr?'Total Notifications':'Total Notifications', '+18 '.($isFr?'ce mois':'this month')],
        ['mail', '#C97A16', '#FDF3E0', $fmt($stats['unread']), $isFr?'Non lues':'Unread', '+5 '.($isFr?'ce mois':'this month')],
        ['check-circle-2', '#157A43', '#E8F2EC', $fmt($stats['read']), $isFr?'Lues':'Read', '+13 '.($isFr?'ce mois':'this month')],
        ['send', '#3565DE', '#E8EFFB', '1 238', $isFr?'Envoyées':'Sent', '+46 '.($isFr?'ce mois':'this month')],
        ['calendar-days', '#7C4FE0', '#F0EAFB', '24', $isFr?'Planifiées':'Scheduled', '+6 '.($isFr?'ce mois':'this month')],
    ];
    $tabs = [[$isFr?'Toutes':'All',true],[$isFr?'Non lues':'Unread '.'('.$stats['unread'].')',false],[$isFr?'Lues':'Read',false],[$isFr?'Envoyées':'Sent',false],[$isFr?'Planifiées':'Scheduled',false],[$isFr?'Brouillons':'Drafts',false]];
    $typeMeta = ['support'=>['file-text','#157A43','#E8F2EC'],'message'=>['user','#C97A16','#FDF3E0'],'article'=>['book-open','#7C4FE0','#F0EAFB'],'announcement'=>['megaphone','#157A43','#E8F2EC'],'account'=>['user-plus','#3565DE','#E8EFFB'],'reminder'=>['clock','#C97A16','#FDF3E0']];
    $canaux = [['Email','256','53%','#157A43'],['In-App','142','29%','#3565DE'],['SMS','54','11%','#C97A16'],['WhatsApp','30','7%','#7C4FE0']];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $isFr?'Centre de Notifications':'Notifications Centre' }} — Administration</title>
<script src="{{ asset('vendor/tailwindcss.js') }}"></script>
<script>tailwind.config={theme:{extend:{colors:{leaf:'#14652F'},fontFamily:{sans:['Poppins','system-ui','sans-serif']}}}}</script>
<script src="{{ asset('vendor/lucide.min.js') }}"></script><link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
<style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}#ad-sidebar{display:none}#ad-sidebar.ad-open{display:flex;position:fixed;inset:0 auto 0 0;width:270px;z-index:60;overflow-y:auto}@media(min-width:1024px){#ad-sidebar,#ad-sidebar.ad-open{display:flex;position:sticky;top:0;height:100vh;width:250px}}</style></head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">
<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', ['pageTitle' => $isFr?'CENTRE DE NOTIFICATIONS':'NOTIFICATIONS CENTRE', 'pageBreadcrumb'=>[['Tableau de bord', route('dashboard.admin')],['Notifications', null]], 'pageSearchPlaceholder'=>$isFr?'Rechercher une notification...':'Search a notification...'])
        <main class="px-5 lg:px-7 pt-5 pb-8">
            {{-- Stat cards --}}
            <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($cards as [$cI,$cC,$cT,$cV,$cL,$cS])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4 text-center">
                    <span class="w-[46px] h-[46px] mx-auto rounded-full flex items-center justify-center" style="background-color:{{ $cT }}"><i data-lucide="{{ $cI }}" class="w-[22px] h-[22px]" style="color:{{ $cC }}"></i></span>
                    <p class="mt-2 text-[11px] text-[#6F6B60]">{{ $cL }}</p>
                    <p class="text-[22px] font-bold text-[#1B1B18] leading-none">{{ $cV }}</p>
                    <p class="mt-1 text-[10.5px] text-[#157A43]">↗ {{ $cS }}</p>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_300px] gap-5 items-start">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#EAE7DE] pb-3">
                        <div class="flex items-center gap-5 overflow-x-auto">@foreach($tabs as [$tL,$tA])<span class="whitespace-nowrap text-[13px] font-semibold {{ $tA?'text-[#14652F] border-b-2 border-[#14652F] pb-3 -mb-3':'text-[#8A857A]' }}">{{ $tL }}</span>@endforeach</div>
                        <a href="{{ route('admin.cms', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[36px] text-[12px] font-semibold text-white"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr?'Nouvelle notification':'New notification' }}</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[720px]">
                            <thead><tr class="text-left border-b border-[#F0F1F0]">
                                <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr?'Titre / Message':'Title / Message' }}</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Canal</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Statut</th>
                                <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Date</th>
                                <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase"></th>
                            </tr></thead>
                            <tbody class="divide-y divide-[#F4F5F4]">
                                @foreach($notifications as $n)
                                @php [$nI,$nC,$nT] = $typeMeta[$n->type] ?? ['bell','#157A43','#E8F2EC']; @endphp
                                <tr>
                                    <td class="py-3.5 pr-3"><div class="flex items-start gap-3"><span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background-color:{{ $nT }}"><i data-lucide="{{ $nI }}" class="w-[18px] h-[18px]" style="color:{{ $nC }}"></i></span><span class="min-w-0"><a href="{{ route('notifications.show', ['id'=>$n->id, 'lang'=>$lang]) }}" class="block text-[12.5px] font-semibold text-[#1B1B18] hover:text-[#157A43]">{{ $n->title }}</a><span class="block text-[11px] text-[#8A857A] line-clamp-1">{{ $n->body }}</span></span></div></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F]">Email</td>
                                    <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $n->read_at ? 'bg-[#EEECE6] text-[#6F6B60]' : 'bg-[#E2F3E8] text-[#157A43]' }}">{{ $n->read_at ? ($isFr?'Lue':'Read') : ($isFr?'Envoyée':'Sent') }}</span></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $dtf($n->created_at) }}</td>
                                    <td class="py-3.5 pl-2 text-right"><a href="{{ route('notifications.show', ['id'=>$n->id, 'lang'=>$lang]) }}" class="inline-flex w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] items-center justify-center text-[#55524A]"><i data-lucide="more-horizontal" class="w-4 h-4"></i></a></td>
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
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Résumé par canal':'By channel' }}</h2>
                        <div class="mt-3.5 space-y-3">@foreach($canaux as [$cnL,$cnN,$cnP,$cnC])<div><div class="flex items-center justify-between text-[12px]"><span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="{{ $cnL==='WhatsApp'?'message-circle':($cnL==='SMS'?'message-square':'mail') }}" class="w-3.5 h-3.5" style="color:{{ $cnC }}"></i>{{ $cnL }}</span><span class="font-semibold text-[#1B1B18]">{{ $cnN }} ({{ $cnP }})</span></div><div class="mt-1 h-1.5 rounded-full bg-[#F0EFEA] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $cnP }};background-color:{{ $cnC }}"></span></div></div>@endforeach</div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Paramètres rapides':'Quick settings' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            @foreach([['sliders-horizontal', $isFr?'Préférences de notification':'Notification preferences'],['layout-template', $isFr?'Modèles de notification':'Notification templates'],['users', $isFr?'Groupes de notification':'Notification groups']] as [$qI,$qL])
                            <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="{{ $qI }}" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qL }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></a>
                            @endforeach
                        </div>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script></body></html>
