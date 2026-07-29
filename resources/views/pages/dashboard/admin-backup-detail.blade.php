@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'backups';
    $pageTitle = $isFr?'Détail de la sauvegarde':'Backup detail';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr?'Détail':'Detail', null]];
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%02d %s %d, %s', $d->day, $monthsFr[$d->month], $d->year, $d->format('H:i:s')) : $d->format('d M Y, H:i:s'); };
    $created = \Carbon\Carbon::parse($backup->created_at);
    $sizeGb = number_format($backup->size_mb / 1024, 1);
    $octets = number_format($backup->size_mb * 1024 * 1024, 0, '', ',');
    $checksum = substr(hash('sha256', $backup->filename . $backup->created_at), 0, 60);
    // Disk quota is an operator-recorded fact, not something the app can measure:
    // absent from platform_settings, every storage figure below is dropped.
    $usedGb  = $settings['storage_used_gb'] ?? null;
    $totalGb = $settings['storage_total_gb'] ?? null;
    $hasQuota = $usedGb !== null && $totalGb !== null && (float) $totalGb > 0;
    $usedPct = $hasQuota ? round((float) $usedGb / (float) $totalGb * 100) : 0;
    $freeGb  = $hasQuota ? number_format(max(0, (float) $totalGb - (float) $usedGb), 1) : null;

    // Facts the runtime genuinely knows about itself.
    $dbName   = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    $dbDriver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

    // Rows whose value is null are dropped rather than filled with a plausible one.
    $onlyKnown = fn (array $rows, int $i = 1) => array_values(array_filter(
        $rows, fn ($r) => $r !== null && $r[$i] !== null && $r[$i] !== ''
    ));

    $tabs = [[$isFr?'Informations générales':'General info', true], ['Contenu', false], ['Historique', false], [$isFr?'Logs associés':'Related logs', false]];
    $metaRow = $onlyKnown([
        ['calendar', $dt($backup->created_at), $isFr?'Date & heure':'Date & time'],
        ['server', $settings['backup_server'] ?? null, $isFr?'Serveur':'Server'],
        ['tag', $backup->type === 'full' ? ($isFr?'Complet':'Full') : ($isFr?'Base de données':'Database'), $isFr?'Type de sauvegarde':'Backup type'],
        ['database', $settings['backup_db'] ?? null, $isFr?'Base de données':'Database'],
        ['user', $backup->mode === 'manual' ? ($isFr?'Manuel':'Manual') : ($isFr?'Système':'System'), $isFr?'Créé par':'Created by'],
    ]);
    $infoLeft = $onlyKnown([
        [$isFr?'Nom du fichier':'Filename', $backup->filename],
        ['Type', $backup->type === 'full' ? ($isFr?'Sauvegarde complète':'Full backup') : ($isFr?'Base de données':'Database')],
        [$isFr?'Taille':'Size', $sizeGb.' GB ('.$octets.' '.($isFr?'octets':'bytes').')'],
        ['Statut', $backup->status === 'success' ? ($isFr?'Réussi':'Success') : ucfirst($backup->status)],
        [$isFr?'Méthode':'Method', $backup->contents],
        isset($settings['backup_path'])
            ? [$isFr?'Chemin de stockage':'Storage path', rtrim($settings['backup_path'], '/').'/'.$created->format('Y/m/d').'/']
            : null,
    ]);
    $infoRight = $onlyKnown([
        [$isFr?'Base de données':'Database', $dbName],
        [$isFr?'Moteur':'Engine', $dbDriver],
        [$isFr?'Version DB':'DB Version', $settings['backup_db'] ?? null],
        [$isFr?'Encodage':'Encoding', config('database.connections.'.config('database.default').'.charset')],
        $hasQuota ? [$isFr?'Espace utilisé':'Storage used', $usedGb.' GB / '.$totalGb.' GB ('.$usedPct.'%)'] : null,
        [$isFr?'Rétention':'Retention', $settings['backup_retention'] ?? null],
    ]);
    // Real system log entries (not backup-specific — backup_logs has no per-backup link)
    $bkLogs = ($logs ?? collect())->map(fn ($l) => [
        \Illuminate\Support\Carbon::parse($l->logged_at)->format('H:i:s'), $l->level, $l->event, $l->description, null,
    ]);
    // The design broke the archive down into DB / media / config / other GB figures;
    // backup_records stores only a total size, so the overview reports real facts instead.
    $apercu = $onlyKnown([
        [$isFr?'Taille totale':'Total size',  $sizeGb.' GB'],
        [$isFr?'Contenu':'Contents',          $backup->contents],
        [$isFr?'Type':'Type',                 $backup->type === 'full' ? ($isFr?'Complet':'Full') : ($isFr?'Base de données':'Database')],
        [$isFr?'Déclenchement':'Trigger',     $backup->mode === 'manual' ? ($isFr?'Manuel':'Manual') : ($isFr?'Automatique':'Automatic')],
    ]);
    $storLoc = $onlyKnown([
        isset($settings['backup_path'])
            ? [$isFr?'Chemin':'Path', rtrim($settings['backup_path'], '/').'/'.$created->format('Y/m/d').'/']
            : null,
        $hasQuota ? [$isFr?'Espace libre':'Free space', $freeGb.' GB ('.(100 - $usedPct).'%)'] : null,
    ]);
    $sysInfo = $onlyKnown([
        [$isFr?'Serveur':'Server', $settings['backup_server'] ?? null],
        [$isFr?'Système d\'exploitation':'OS', $settings['backup_os'] ?? null],
        ['PHP Version', PHP_VERSION],
        [$isFr?'Fuseau horaire':'Timezone', config('app.timezone')],
    ]);
@endphp

@section('content')
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.backups', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour':'Back' }}</a>
                </div>
                <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="hidden lg:block w-[120px] h-[100px] object-cover rounded-xl opacity-95" aria-hidden="true">
            </div>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Title card --}}
                    <section class="ui-card">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <span class="w-[54px] h-[54px] rounded-xl bg-[#E8F2EC] dark:bg-[#1A1E16] flex items-center justify-center"><i data-lucide="database" class="w-7 h-7 text-[#157A43] dark:text-[#339B56]"></i></span>
                                <div>
                                    <span class="ui-pill ui-pill-ok">{{ $backup->status === 'success' ? ($isFr?'Réussi':'Success') : ucfirst($backup->status) }}</span>
                                    <p class="mt-1 text-[18px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $backup->filename }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr?'Télécharger':'Download' }}</a>
                                <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="shield-check" class="w-4 h-4"></i>{{ $isFr?'Vérifier':'Verify' }}</a>
                                <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                    <button type="submit" class="ui-btn ui-btn-danger"><i data-lucide="trash-2" class="w-4 h-4"></i>{{ $isFr?'Supprimer':'Delete' }}</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3 border-t border-[#EFEBE2] dark:border-[#262B21] pt-4">
                            @foreach($metaRow as [$mIcon, $mVal, $mLabel])
                            <div class="flex items-start gap-2.5"><i data-lucide="{{ $mIcon }}" class="w-4 h-4 mt-0.5 text-[#C9942E] dark:text-[#EDB33A] shrink-0"></i><div><p class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $mVal }}</p><p class="text-[12px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $mLabel }}</p></div></div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Info tabs --}}
                    <section class="ui-card">
                        <div class="flex items-center gap-6 border-b border-[#EFEBE2] dark:border-[#262B21] overflow-x-auto">
                            @foreach($tabs as [$tLabel, $tActive])<span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] dark:text-[#339B56] border-b-2 border-[#14652F] dark:border-[#2E9250] ' : 'text-[#8A857A] dark:text-[#868778] ' }}">{{ $tLabel }}</span>@endforeach
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
                            @foreach($infoLeft as [$l, $v])<div class="flex items-start justify-between gap-3 border-b border-[#EFEBE2] dark:border-[#262B21] pb-2.5"><dt class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach
                            @foreach($infoRight as [$l, $v])<div class="flex items-start justify-between gap-3 border-b border-[#EFEBE2] dark:border-[#262B21] pb-2.5"><dt class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach
                        </div>
                        <div class="mt-4">
                            <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr?'Somme de contrôle (SHA256)':'Checksum (SHA256)' }} :</p>
                            <p class="mt-1.5 bg-[#F7F8F7] dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] rounded-lg px-3.5 py-2.5 text-[13px] md:text-[11.5px] font-mono text-[#3B382F] dark:text-[#B4B5A6] break-all">{{ $checksum }}</p>
                        </div>
                    </section>

                    {{-- Logs of this backup --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Logs de cette sauvegarde':'Logs of this backup' }}</h2>
                        <div class="ui-table-wrap mt-3">
                            <table class="ui-table min-w-[640px]">
                                <thead><tr class="border-b border-[#EFEBE2] dark:border-[#262B21]">
                                    <th>{{ $isFr?'Heure':'Time' }}</th>
                                    <th>{{ $isFr?'Niveau':'Level' }}</th>
                                    <th>{{ $isFr?'Événement':'Event' }}</th>
                                    <th>Description</th>
                                    <th class="text-right">{{ $isFr?'Durée':'Duration' }}</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($bkLogs as [$lTime, $lLevel, $lEvent, $lDesc, $lDur])
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $created->format('d M Y') }}, {{ $lTime }}</td>
                                        <td><span class="inline-block rounded-md px-2 py-0.5 text-[12px] md:text-[10.5px] font-bold {{ $lLevel === 'success' ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#E8EFFB] dark:bg-[#101C33] text-[#3565DE] dark:text-[#8FB6F5] ' }}">{{ strtoupper($lLevel) }}</span></td>
                                        <td class="font-medium text-[#1B1B18] dark:text-[#F3EFE7]">{{ $lEvent }}</td>
                                        <td>{{ $lDesc }}</td>
                                        <td class="text-right whitespace-nowrap">{{ $lDur }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[14px] md:text-[12.5px] font-semibold text-[#157A43] dark:text-[#339B56]">{{ $isFr?'Voir tous les logs détaillés':'View all detailed logs' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Aperçu de la sauvegarde':'Backup overview' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="relative w-[92px] h-[92px] rounded-full shrink-0 bg-[#157A43] dark:bg-[#2E9250]"><span class="absolute inset-[14px] rounded-full bg-white dark:bg-[#12150F] flex flex-col items-center justify-center"><span class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ $sizeGb }}</span><span class="text-[11px] md:text-[9px] text-[#8A857A] dark:text-[#868778]">GB</span></span></span>
                            <div class="flex-1 space-y-1.5">
                                @foreach($apercu as [$aLabel, $aVal])
                                <div class="flex items-center justify-between gap-2 text-[13px] md:text-[11px]"><span class="text-[#3B382F] dark:text-[#B4B5A6]">{{ $aLabel }}</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $aVal }}</span></div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    @if($storLoc)
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Emplacement de stockage':'Storage location' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[14px] md:text-[12px]">@foreach($storLoc as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right truncate">{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                    @endif
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Actions rapides':'Quick actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F5F1E8] dark:divide-[#262B21]">
                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 min-h-[44px] md:min-h-0 group"><i data-lucide="download" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr?'Télécharger cette sauvegarde':'Download this backup' }}</span></a>
                            <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 min-h-[44px] md:min-h-0 group"><i data-lucide="rotate-ccw" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr?'Restaurer cette sauvegarde':'Restore this backup' }}</span></a>
                            <a href="{{ route('admin.backups', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 min-h-[44px] md:min-h-0 group"><i data-lucide="copy" class="w-[16px] h-[16px] text-[#157A43] dark:text-[#339B56]"></i><span class="text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $isFr?'Cloner cette sauvegarde':'Clone this backup' }}</span></a>
                            <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 min-h-[44px] md:min-h-0 group text-left"><i data-lucide="trash-2" class="w-[16px] h-[16px] text-[#DC2626] dark:text-[#F0555C]"></i><span class="text-[14px] md:text-[12.5px] text-[#DC2626] dark:text-[#F0555C]">{{ $isFr?'Supprimer cette sauvegarde':'Delete this backup' }}</span></button>
                            </form>
                        </div>
                    </section>
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr?'Informations système':'System information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[14px] md:text-[12px]">@foreach($sysInfo as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60] dark:text-[#868778]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                </aside>
            </div>
            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
