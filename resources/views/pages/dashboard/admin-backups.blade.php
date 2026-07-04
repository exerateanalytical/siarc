@php
    $isFr = $lang === 'fr';
    $adminActive = 'backups';
    $fmt = fn ($n) => number_format($n, 0, ',', ' ');
    $sizeGb = fn ($mb) => number_format($mb / 1024, 1) . ' GB';

    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) {
        if (!$v) return '—';
        $d = \Carbon\Carbon::parse($v);
        return $isFr ? sprintf('%02d %s %d, %s', $d->day, $monthsFr[$d->month], $d->year, $d->format('H:i')) : $d->format('d M Y, H:i');
    };

    $lastAt   = $stats['last_at'] ? \Carbon\Carbon::parse($stats['last_at']) : now();
    $nextAt   = $lastAt->copy()->addDay();
    $usedPct  = $stats['total_gb'] > 0 ? round($stats['used_gb'] / $stats['total_gb'] * 100) : 0;
    $availGb  = $stats['total_gb'] - $stats['used_gb'];

    $statCards = [
        ['calendar-check', '#157A43', '#E8F2EC', $isFr ? 'Dernier backup' : 'Last backup', $dt($stats['last_at']), $isFr ? 'Il y a '.$lastAt->diffForHumans(now(), true) : $lastAt->diffForHumans(), $isFr?'Réussi':'Success', 'bg-[#E2F3E8] text-[#157A43]'],
        ['clock', '#3565DE', '#E8EFFB', $isFr ? 'Prochain backup' : 'Next backup', $dt($nextAt), $isFr ? 'Dans 18 heures' : 'In 18 hours', $isFr?'Planifié':'Scheduled', 'bg-[#E8EFFB] text-[#3565DE]'],
        ['database', '#C97A16', '#FDF3E0', $isFr ? 'Backups disponibles' : 'Available backups', $fmt($stats['total']), $isFr ? 'Total des sauvegardes' : 'Total backups', $isFr?'Sain':'Healthy', 'bg-[#E2F3E8] text-[#157A43]'],
        ['pie-chart', '#7C4FE0', '#F0EAFB', $isFr ? 'Espace utilisé' : 'Storage used', $stats['used_gb'].' GB', $isFr ? 'sur '.$stats['total_gb'].' GB ('.$usedPct.'%)' : 'of '.$stats['total_gb'].' GB ('.$usedPct.'%)', 'Normal', 'bg-[#FDF3E0] text-[#C97A16]'],
    ];

    $tabs = [[$isFr?'Sauvegardes':'Backups', true], [$isFr?'Planification':'Scheduling', false], ['Restauration', false], [$isFr?'Logs système':'System logs', false], [$isFr?'Logs d\'activité':'Activity logs', false]];

    $sysInfo = [
        [$isFr?'Serveur':'Server', $settings['backup_server'] ?? 'GVNA-Server-01'],
        [$isFr?'Système d\'exploitation':'OS', $settings['backup_os'] ?? 'Ubuntu 22.04 LTS'],
        ['PHP Version', PHP_VERSION],
        [$isFr?'Base de données':'Database', $settings['backup_db'] ?? 'MySQL 8.0'],
        [$isFr?'Chemin des sauvegardes':'Backup path', $settings['backup_path'] ?? '/backups/gvna'],
        [$isFr?'Rétention des backups':'Retention', $settings['backup_retention'] ?? '30 jours'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Backups & Logs — Administration' : 'Backups & Logs — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#14652F' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}#ad-sidebar{display:none}#ad-sidebar.ad-open{display:flex;position:fixed;inset:0 auto 0 0;width:270px;z-index:60;overflow-y:auto}@media(min-width:1024px){#ad-sidebar,#ad-sidebar.ad-open{display:flex;position:sticky;top:0;height:100vh;width:250px}}</style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">
<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')
        <main class="px-5 lg:px-7 pb-8">

            @if(session('success'))
            <div class="mt-1 mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]"><i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>{{ session('success') }}</div>
            @endif

            {{-- Title + heritage statue --}}
            <div class="relative overflow-hidden rounded-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[24px] font-bold text-[#1B1B18]">Backups &amp; Logs</h1>
                        <p class="mt-0.5 text-[12.5px] text-[#6F6B60]"><a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43]">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a> <span class="mx-1">/</span> {{ $isFr ? 'Paramètres' : 'Settings' }} <span class="mx-1">/</span> <span class="text-[#1B1B18]">Backups &amp; Logs</span></p>
                    </div>
                    <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="hidden lg:block w-[130px] h-[110px] object-cover rounded-xl opacity-95" aria-hidden="true">
                </div>
            </div>

            {{-- Stat cards --}}
            <section class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($statCards as [$cIcon, $cColor, $cTile, $cLabel, $cValue, $cSub, $cBadge, $cBadgeCls])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4">
                    <div class="flex items-start gap-3.5">
                        <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $cTile }}"><i data-lucide="{{ $cIcon }}" class="w-[22px] h-[22px]" style="color: {{ $cColor }};stroke-width:1.7"></i></span>
                        <div class="min-w-0">
                            <p class="text-[12px] text-[#6F6B60]">{{ $cLabel }}</p>
                            <p class="text-[17px] font-bold text-[#1B1B18] leading-tight">{{ $cValue }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $cSub }}</p>
                        </div>
                    </div>
                    <span class="mt-2.5 inline-block rounded-md px-2 py-0.5 text-[10.5px] font-semibold {{ $cBadgeCls }}">{{ $cBadge }}</span>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_330px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Tabs + backups --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <div class="flex items-center gap-6 border-b border-[#EAE7DE] overflow-x-auto">
                            @foreach($tabs as [$tLabel, $tActive])
                            <span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}">{{ $tLabel }}</span>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Liste des sauvegardes' : 'Backups list' }}</h2>
                            <div class="flex items-center gap-2.5">
                                <form method="POST" action="{{ route('admin.backups.create') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                    <button type="submit" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-white transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer une sauvegarde' : 'Create a backup' }}</button>
                                </form>
                                <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="settings" class="w-4 h-4"></i>{{ $isFr ? 'Paramètres' : 'Settings' }}</a>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[720px]">
                                <thead><tr class="text-left border-b border-[#F0F1F0]">
                                    <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Nom du fichier' : 'Filename' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Type</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Taille' : 'Size' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Statut</th>
                                    <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Actions</th>
                                </tr></thead>
                                <tbody class="divide-y divide-[#F4F5F4]">
                                    @foreach($backups as $b)
                                    <tr>
                                        <td class="py-3.5 pr-3 text-[12px] text-[#1B1B18] whitespace-nowrap">{{ $dt($b->created_at) }}<br><span class="text-[11px] text-[#8A857A]">({{ $b->mode === 'manual' ? ($isFr?'Manuel':'Manual') : ($isFr?'Automatique':'Automatic') }})</span></td>
                                        <td class="py-3.5 px-2"><a href="{{ route('admin.backups.detail', ['id'=>$b->id, 'lang'=>$lang]) }}" class="text-[12.5px] font-medium text-[#1B1B18] hover:text-[#157A43]">{{ $b->filename }}</a><br><span class="text-[11px] text-[#8A857A]">{{ $b->contents }}</span></td>
                                        <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $b->type === 'full' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#E8EFFB] text-[#3565DE]' }}">{{ $b->type === 'full' ? ($isFr?'Complet':'Full') : ($isFr?'Base de données':'Database') }}</span></td>
                                        <td class="py-3.5 px-2 text-[12.5px] text-[#3B382F]">{{ $sizeGb($b->size_mb) }}</td>
                                        <td class="py-3.5 px-2"><span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43]"><i data-lucide="circle-check" class="w-3.5 h-3.5"></i>{{ $isFr?'Réussi':'Success' }}</span></td>
                                        <td class="py-3.5 pl-2"><div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr?'Télécharger':'Download' }}"><i data-lucide="download" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.backups.detail', ['id'=>$b->id, 'lang'=>$lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="Actions"><i data-lucide="more-vertical" class="w-4 h-4"></i></a>
                                        </div></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($backups->hasPages())
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $backups->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $backups->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $backups->total() }} {{ $isFr ? 'sauvegardes' : 'backups' }}</p>
                            <div class="flex items-center gap-1.5">
                                @if($backups->onFirstPage())<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @else<a href="{{ $backups->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>@endif
                                @foreach($backups->getUrlRange(1, $backups->lastPage()) as $pn => $url)
                                @if($pn === $backups->currentPage())<span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>
                                @else<a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pn }}</a>@endif
                                @endforeach
                                @if($backups->hasMorePages())<a href="{{ $backups->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                                @else<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>@endif
                            </div>
                        </div>
                        @endif
                    </section>

                    {{-- System logs --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Derniers logs système' : 'Recent system logs' }}</h2>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[680px]">
                                <thead><tr class="text-left border-b border-[#F0F1F0]">
                                    <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Niveau' : 'Level' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Événement' : 'Event' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Description</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Utilisateur' : 'User' }}</th>
                                </tr></thead>
                                <tbody class="divide-y divide-[#F4F5F4]">
                                    @foreach($logs as $log)
                                    <tr>
                                        <td class="py-3 pr-3 text-[11.5px] text-[#3B382F] whitespace-nowrap">{{ \Carbon\Carbon::parse($log->logged_at)->format('d M Y, H:i:s') }}</td>
                                        <td class="py-3 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[10.5px] font-bold {{ $log->level === 'warning' ? 'bg-[#FDF3E0] text-[#C97A16]' : ($log->level === 'error' ? 'bg-[#FDE8E8] text-[#DC2626]' : 'bg-[#E2F3E8] text-[#157A43]') }}">{{ strtoupper($log->level) }}</span></td>
                                        <td class="py-3 px-2 text-[12px] font-medium text-[#1B1B18]">{{ $log->event }}</td>
                                        <td class="py-3 px-2 text-[12px] text-[#3B382F]">{{ $log->description }}</td>
                                        <td class="py-3 px-2 text-[12px] text-[#6F6B60]">{{ $log->actor }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tous les logs' : 'View all logs' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Utilisation du stockage' : 'Storage usage' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="relative w-[100px] h-[100px] rounded-full shrink-0" style="background: conic-gradient(#157A43 0deg {{ $usedPct * 3.6 }}deg, #C9942E {{ $usedPct * 3.6 }}deg {{ ($usedPct + ($availGb/$stats['total_gb']*100)) * 3.6 }}deg, #E5E7E5 {{ ($usedPct + ($availGb/$stats['total_gb']*100)) * 3.6 }}deg 360deg)">
                                <span class="absolute inset-[16px] rounded-full bg-white flex flex-col items-center justify-center"><span class="text-[18px] font-bold text-[#1B1B18] leading-none">{{ $usedPct }}%</span><span class="text-[9px] text-[#8A857A]">{{ $isFr?'Utilisé':'Used' }}</span></span>
                            </span>
                            <div class="flex-1 space-y-1.5 text-[11.5px]">
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>{{ $isFr?'Utilisé':'Used' }}</span><span class="font-semibold text-[#1B1B18]">{{ $stats['used_gb'] }} GB</span></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#C9942E]"></span>{{ $isFr?'Disponible':'Available' }}</span><span class="font-semibold text-[#1B1B18]">{{ number_format($availGb, 1) }} GB</span></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#E5E7E5]"></span>Total</span><span class="font-semibold text-[#1B1B18]">{{ $stats['total_gb'] }} GB</span></div>
                            </div>
                        </div>
                        <div class="mt-3 bg-[#FBF6EA] border border-[#EAD9AC] rounded-xl px-3.5 py-2.5 flex items-start gap-2.5 text-[11px] text-[#7A5A12]"><i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i><span>{{ $isFr ? 'Nous vous recommandons de garder au moins 20% d\'espace libre pour de meilleures performances.' : 'We recommend keeping at least 20% free space for better performance.' }}</span></div>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations du système' : 'System information' }}</h2>
                        <dl class="mt-3.5 space-y-2.5 text-[12px]">
                            @foreach($sysInfo as [$sLabel, $sVal])
                            <div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60]">{{ $sLabel }}</dt><dd class="font-semibold text-[#1B1B18] text-right">{{ $sVal }}</dd></div>
                            @endforeach
                        </dl>
                        <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="mt-4 block text-center bg-[#0F4824] hover:bg-[#14652F] rounded-lg py-2.5 text-[12.5px] font-semibold text-white transition-colors">{{ $isFr ? 'Modifier les paramètres' : 'Edit settings' }}</a>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            <form method="POST" action="{{ route('admin.backups.create') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 group text-left"><i data-lucide="database-backup" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr ? 'Créer une sauvegarde maintenant' : 'Create a backup now' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></button>
                            </form>
                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="download" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr ? 'Télécharger tous les backups' : 'Download all backups' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></a>
                            <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 group text-left"><i data-lucide="trash-2" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr ? 'Nettoyer les backups anciens' : 'Clean old backups' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></button>
                            </form>
                            <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="shield-check" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="flex-1 text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr ? 'Vérifier l\'intégrité des backups' : 'Verify backup integrity' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i></a>
                        </div>
                    </section>
                </aside>
            </div>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
