@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'backups';
    $pageTitle = 'Backups & Logs';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Paramètres' : 'Settings', null]];
    $fmt = fn ($n) => number_format($n, 0, ',', ' ');
    $sizeGb = fn ($mb) => number_format($mb / 1024, 1) . ' GB';

    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) {
        if (!$v) return '—';
        $d = \Carbon\Carbon::parse($v);
        return $isFr ? sprintf('%02d %s %d, %s', $d->day, $monthsFr[$d->month], $d->year, $d->format('H:i')) : $d->format('d M Y, H:i');
    };

    $lastAt   = $stats['last_at'] ? \Carbon\Carbon::parse($stats['last_at']) : null;
    $nextAt   = $lastAt?->copy()->addDay();
    // Only real, operator-recorded quota figures; no quota means no storage tile.
    $hasQuota = $stats['used_gb'] !== null && $stats['total_gb'] !== null && $stats['total_gb'] > 0;
    $usedPct  = $hasQuota ? round($stats['used_gb'] / $stats['total_gb'] * 100) : 0;
    $availGb  = $hasQuota ? $stats['total_gb'] - $stats['used_gb'] : null;

    $statCards = array_values(array_filter([
        $lastAt ? ['calendar-check', '#157A43', '#E8F2EC', $isFr ? 'Dernier backup' : 'Last backup', $dt($stats['last_at']), $lastAt->diffForHumans(), $isFr?'Réussi':'Success', 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'] : null,
        $nextAt ? ['clock', '#3565DE', '#E8EFFB', $isFr ? 'Prochain backup' : 'Next backup', $dt($nextAt), $nextAt->diffForHumans(), $isFr?'Planifié':'Scheduled', 'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5]'] : null,
        ['database', '#C97A16', '#FDF3E0', $isFr ? 'Backups disponibles' : 'Available backups', $fmt($stats['total']), $isFr ? 'Total des sauvegardes' : 'Total backups', $isFr?'Sain':'Healthy', 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        $hasQuota ? ['pie-chart', '#7C4FE0', '#F0EAFB', $isFr ? 'Espace utilisé' : 'Storage used', $stats['used_gb'].' GB', $isFr ? 'sur '.$stats['total_gb'].' GB ('.$usedPct.'%)' : 'of '.$stats['total_gb'].' GB ('.$usedPct.'%)', 'Normal', 'bg-[#FDF3E0] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'] : null,
    ]));

    $tabs = [[$isFr?'Sauvegardes':'Backups', true], [$isFr?'Planification':'Scheduling', false], ['Restauration', false], [$isFr?'Logs système':'System logs', false], [$isFr?'Logs d\'activité':'Activity logs', false]];

    // Infrastructure facts are only shown when an operator has recorded them in
    // platform_settings — the platform cannot know its own host otherwise.
    $sysInfo = array_values(array_filter([
        [$isFr?'Serveur':'Server', $settings['backup_server'] ?? null],
        [$isFr?'Système d\'exploitation':'OS', $settings['backup_os'] ?? null],
        ['PHP Version', PHP_VERSION],
        [$isFr?'Base de données':'Database', $settings['backup_db'] ?? null],
        [$isFr?'Chemin des sauvegardes':'Backup path', $settings['backup_path'] ?? null],
        [$isFr?'Rétention des backups':'Retention', $settings['backup_retention'] ?? null],
    ], fn ($row) => $row[1] !== null && $row[1] !== ''));
@endphp

@section('content')

            {{-- Stat cards --}}
            <section class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($statCards as [$cIcon, $cColor, $cTile, $cLabel, $cValue, $cSub, $cBadge, $cBadgeCls])
                <div class="ui-card">
                    <div class="flex items-start gap-3.5">
                        <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $cTile }}"><i data-lucide="{{ $cIcon }}" class="w-[22px] h-[22px]" style="color: {{ $cColor }};stroke-width:1.7"></i></span>
                        <div class="min-w-0">
                            <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $cLabel }}</p>
                            <p class="text-[17px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight">{{ $cValue }}</p>
                            <p class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $cSub }}</p>
                        </div>
                    </div>
                    <span class="mt-2.5 inline-block rounded-md px-2 py-0.5 text-[12px] md:text-[10.5px] font-semibold {{ $cBadgeCls }}">{{ $cBadge }}</span>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_330px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Tabs + backups --}}
                    <section class="ui-card">
                        <div class="flex items-center gap-6 border-b border-[#EFEBE2] dark:border-[#262B21] overflow-x-auto">
                            @foreach($tabs as [$tLabel, $tActive])
                            <span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250] ' : 'text-[#8A857A] dark:text-[#868778] ' }}">{{ $tLabel }}</span>
                            @endforeach
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <h2 class="ui-card-title">{{ $isFr ? 'Liste des sauvegardes' : 'Backups list' }}</h2>
                            <div class="flex items-center gap-2.5">
                                <form method="POST" action="{{ route('admin.backups.create') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                    <button type="submit" class="ui-btn ui-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Créer une sauvegarde' : 'Create a backup' }}</button>
                                </form>
                                <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="settings" class="w-4 h-4"></i>{{ $isFr ? 'Paramètres' : 'Settings' }}</a>
                            </div>
                        </div>

                        <div class="ui-table-wrap mt-4">
                            <table class="ui-table min-w-[720px]">
                                <thead><tr class="border-b border-[#EFEBE2] dark:border-[#262B21]">
                                    <th>{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                                    <th>{{ $isFr ? 'Nom du fichier' : 'Filename' }}</th>
                                    <th>Type</th>
                                    <th>{{ $isFr ? 'Taille' : 'Size' }}</th>
                                    <th>Statut</th>
                                    <th class="text-right">Actions</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($backups as $b)
                                    <tr>
                                        <td class="text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ $dt($b->created_at) }}<br><span class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">({{ $b->mode === 'manual' ? ($isFr?'Manuel':'Manual') : ($isFr?'Automatique':'Automatic') }})</span></td>
                                        <td><a href="{{ route('admin.backups.detail', ['id'=>$b->id, 'lang'=>$lang]) }}" class="text-[15px] md:text-[12.5px] font-medium text-[#1B1B18] dark:text-[#F3EFE7] hover:text-[#157A43] dark:hover:text-[#339B56]">{{ $b->filename }}</a><br><span class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $b->contents }}</span></td>
                                        <td><span class="inline-block rounded-md px-2 py-0.5 text-[13px] md:text-[11px] font-semibold {{ $b->type === 'full' ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5] ' }}">{{ $b->type === 'full' ? ($isFr?'Complet':'Full') : ($isFr?'Base de données':'Database') }}</span></td>
                                        <td>{{ $sizeGb($b->size_mb) }}</td>
                                        <td><span class="inline-flex items-center gap-1.5 text-[14px] md:text-[12px] font-semibold text-[#157A43] dark:text-[#339B56]"><i data-lucide="circle-check" class="w-3.5 h-3.5"></i>{{ $isFr?'Réussi':'Success' }}</span></td>
                                        <td><div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="w-8 h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] hover:border-[#14652F] dark:hover:border-[#2E9250] flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]" title="{{ $isFr?'Télécharger':'Download' }}"><i data-lucide="download" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.backups.detail', ['id'=>$b->id, 'lang'=>$lang]) }}" class="w-8 h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] hover:border-[#14652F] dark:hover:border-[#2E9250] flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]" title="Actions"><i data-lucide="more-vertical" class="w-4 h-4"></i></a>
                                        </div></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($backups->hasPages())
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $backups->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $backups->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ $backups->total() }} {{ $isFr ? 'sauvegardes' : 'backups' }}</p>
                            <div class="flex items-center gap-1.5">
                                @if($backups->onFirstPage())<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9] dark:text-[#868778]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @else<a href="{{ $backups->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>@endif
                                @foreach($backups->getUrlRange(1, $backups->lastPage()) as $pn => $url)
                                @if($pn === $backups->currentPage())<span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] dark:bg-[#0C3B1E] text-white text-[14px] md:text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>
                                @else<a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[14px] md:text-[12.5px] text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md">{{ $pn }}</a>@endif
                                @endforeach
                                @if($backups->hasMorePages())<a href="{{ $backups->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                                @else<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9] dark:text-[#868778]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>@endif
                            </div>
                        </div>
                        @endif
                    </section>

                    {{-- System logs --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Derniers logs système' : 'Recent system logs' }}</h2>
                        <div class="ui-table-wrap mt-3">
                            <table class="ui-table min-w-[680px]">
                                <thead><tr class="border-b border-[#EFEBE2] dark:border-[#262B21]">
                                    <th>{{ $isFr ? 'Date & Heure' : 'Date & Time' }}</th>
                                    <th>{{ $isFr ? 'Niveau' : 'Level' }}</th>
                                    <th>{{ $isFr ? 'Événement' : 'Event' }}</th>
                                    <th>Description</th>
                                    <th>{{ $isFr ? 'Utilisateur' : 'User' }}</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($logs as $log)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($log->logged_at)->format('d M Y, H:i:s') }}</td>
                                        <td><span class="inline-block rounded-md px-2 py-0.5 text-[12px] md:text-[10.5px] font-bold {{ $log->level === 'warning' ? 'bg-[#FDF3E0] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A] ' : ($log->level === 'error' ? 'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#DC2626] dark:text-[#F0555C] ' : 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]') }}">{{ strtoupper($log->level) }}</span></td>
                                        <td class="font-medium text-[#1B1B18] dark:text-[#F3EFE7]">{{ $log->event }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->actor }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[14px] md:text-[12.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:text-[#14532D] dark:hover:text-[#339B56]">{{ $isFr ? 'Voir tous les logs' : 'View all logs' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    @if($hasQuota)
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Utilisation du stockage' : 'Storage usage' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="relative w-[100px] h-[100px] rounded-full shrink-0" style="background: conic-gradient(#157A43 0deg {{ $usedPct * 3.6 }}deg, #C9942E {{ $usedPct * 3.6 }}deg {{ ($usedPct + ($availGb/$stats['total_gb']*100)) * 3.6 }}deg, #E5E7E5 {{ ($usedPct + ($availGb/$stats['total_gb']*100)) * 3.6 }}deg 360deg)">
                                <span class="absolute inset-[16px] rounded-full bg-white dark:bg-[#12150F] flex flex-col items-center justify-center"><span class="text-[18px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ $usedPct }}%</span><span class="text-[12px] md:text-[9px] text-[#8A857A] dark:text-[#868778]">{{ $isFr?'Utilisé':'Used' }}</span></span>
                            </span>
                            <div class="flex-1 space-y-1.5 text-[13px] md:text-[11.5px]">
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2 h-2 rounded-full bg-[#157A43] dark:bg-[#2E9250]"></span>{{ $isFr?'Utilisé':'Used' }}</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $stats['used_gb'] }} GB</span></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2 h-2 rounded-full bg-[#C9942E] dark:bg-[#3A2B06]"></span>{{ $isFr?'Disponible':'Available' }}</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($availGb, 1) }} GB</span></div>
                                <div class="flex items-center justify-between"><span class="flex items-center gap-1.5 text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2 h-2 rounded-full bg-[#E5E7E5] dark:bg-[#1A1E16]"></span>Total</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $stats['total_gb'] }} GB</span></div>
                            </div>
                        </div>
                        <div class="mt-3 bg-[#FBF6EA] dark:bg-[#1A1E16] border border-[#EAD9AC] dark:border-[#4A3A12] rounded-xl px-3.5 py-2.5 flex items-start gap-2.5 text-[13px] md:text-[11px] text-[#7A5A12] dark:text-[#EDB33A]"><i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i><span>{{ $isFr ? 'Nous vous recommandons de garder au moins 20% d\'espace libre pour de meilleures performances.' : 'We recommend keeping at least 20% free space for better performance.' }}</span></div>
                    </section>
                    @endif

                    @if($sysInfo)
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Informations du système' : 'System information' }}</h2>
                        <dl class="mt-3.5 space-y-2.5 text-[14px] md:text-[12px]">
                            @foreach($sysInfo as [$sLabel, $sVal])
                            <div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $sLabel }}</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $sVal }}</dd></div>
                            @endforeach
                        </dl>
                        <a href="{{ route('admin.settings', ['lang'=>$lang]) }}" class="mt-4 block text-center bg-[#0F4824] dark:bg-[#2E9250] hover:bg-[#14652F] dark:hover:bg-[#2E9250] rounded-lg py-2.5 text-[14px] md:text-[12.5px] font-semibold text-white dark:text-[#04150A] transition-colors">{{ $isFr ? 'Modifier les paramètres' : 'Edit settings' }}</a>
                    </section>
                    @endif

                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F5F1E8] dark:divide-[#262B21]">
                            <form method="POST" action="{{ route('admin.backups.create') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 group text-left"><i data-lucide="database-backup" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="flex-1 text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr ? 'Créer une sauvegarde maintenant' : 'Create a backup now' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9] dark:text-[#868778]"></i></button>
                            </form>
                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="download" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="flex-1 text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr ? 'Télécharger tous les backups' : 'Download all backups' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9] dark:text-[#868778]"></i></a>
                            <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 group text-left"><i data-lucide="trash-2" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="flex-1 text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr ? 'Nettoyer les backups anciens' : 'Clean old backups' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9] dark:text-[#868778]"></i></button>
                            </form>
                            <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="shield-check" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="flex-1 text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr ? 'Vérifier l\'intégrité des backups' : 'Verify backup integrity' }}</span><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9] dark:text-[#868778]"></i></a>
                        </div>
                    </section>
                </aside>
            </div>

            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
