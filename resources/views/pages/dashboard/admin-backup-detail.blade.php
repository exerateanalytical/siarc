@php
    $isFr = $lang === 'fr';
    $adminActive = 'backups';
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $dt = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%02d %s %d, %s', $d->day, $monthsFr[$d->month], $d->year, $d->format('H:i:s')) : $d->format('d M Y, H:i:s'); };
    $created = \Carbon\Carbon::parse($backup->created_at);
    $sizeGb = number_format($backup->size_mb / 1024, 1);
    $octets = number_format($backup->size_mb * 1024 * 1024, 0, '', ',');
    $checksum = substr(hash('sha256', $backup->filename . $backup->created_at), 0, 60);
    $usedGb = $settings['storage_used_gb'] ?? '256.8'; $totalGb = $settings['storage_total_gb'] ?? '500';

    $tabs = [[$isFr?'Informations générales':'General info', true], ['Contenu', false], ['Historique', false], [$isFr?'Logs associés':'Related logs', false]];
    $metaRow = [
        ['calendar', $dt($backup->created_at), $isFr?'Date & heure':'Date & time'],
        ['server', $settings['backup_server'] ?? 'GVNA-Server-01', $isFr?'Serveur':'Server'],
        ['tag', $backup->type === 'full' ? ($isFr?'Complet':'Full') : ($isFr?'Base de données':'Database'), $isFr?'Type de sauvegarde':'Backup type'],
        ['database', $settings['backup_db'] ?? 'MySQL 8.0', $isFr?'Base de données':'Database'],
        ['user', $backup->mode === 'manual' ? ($isFr?'Manuel':'Manual') : ($isFr?'Système':'System'), $isFr?'Créé par':'Created by'],
    ];
    $infoLeft = [
        [$isFr?'Nom du fichier':'Filename', $backup->filename],
        ['Type', $backup->type === 'full' ? ($isFr?'Sauvegarde complète':'Full backup') : ($isFr?'Base de données':'Database')],
        [$isFr?'Taille':'Size', $sizeGb.' GB ('.$octets.' '.($isFr?'octets':'bytes').')'],
        ['Statut', $isFr?'Réussi':'Success'],
        [$isFr?'Durée':'Duration', '00:18:42'],
        [$isFr?'Méthode':'Method', $isFr?'Dump + Fichiers':'Dump + Files'],
        [$isFr?'Chemin de stockage':'Storage path', ($settings['backup_path'] ?? '/backups/gvna').'/'.$created->format('Y/m/d').'/'],
    ];
    $infoRight = [
        [$isFr?'Base de données':'Database', 'gvna_production'],
        [$isFr?'Version DB':'DB Version', $settings['backup_db'] ?? 'MySQL 8.0'],
        [$isFr?'Encodage':'Encoding', 'UTF-8'],
        ['Compression', $isFr?'ZIP (Déflation)':'ZIP (Deflate)'],
        [$isFr?'Espace utilisé':'Storage used', $usedGb.' GB / '.$totalGb.' GB (51%)'],
        [$isFr?'Rétention':'Retention', $settings['backup_retention'] ?? '30 jours'],
        [$isFr?'Prochain backup':'Next backup', $dt($created->copy()->addDay())],
    ];
    $bkLogs = [
        ['02:30:00', 'info', $isFr?'Démarrage de la sauvegarde':'Backup started', $isFr?'Initialisation du processus':'Process initialisation', '00:00:02'],
        ['02:30:05', 'info', $isFr?'Vérification système':'System check', $isFr?'Vérification de l\'espace disque':'Disk space check', '00:00:03'],
        ['02:30:08', 'info', $isFr?'Sauvegarde base de données':'Database backup', $isFr?'Dump de la base en cours':'Database dump running', '00:12:45'],
        ['02:42:53', 'info', $isFr?'Sauvegarde fichiers':'Files backup', $isFr?'Copie des fichiers et médias':'Copying files and media', '00:04:32'],
        ['02:47:25', 'info', 'Compression', $isFr?'Compression en archive ZIP':'ZIP archive compression', '00:01:05'],
        ['02:48:30', 'success', $isFr?'Sauvegarde terminée':'Backup complete', $isFr?'Sauvegarde complète créée avec succès':'Full backup created successfully', '00:00:12'],
    ];
    $apercu = [
        [$isFr?'Base de données':'Database', '12.4 GB', '67%', '#157A43'],
        [$isFr?'Fichiers & Médias':'Files & Media', '4.2 GB', '23%', '#C9942E'],
        ['Configurations', '1.2 GB', '6%', '#3565DE'],
        [$isFr?'Autres données':'Other data', '0.8 GB', '4%', '#C4C0B6'],
    ];
    $storLoc = [
        [$isFr?'Stockage':'Storage', $isFr?'Serveur local':'Local server'],
        [$isFr?'Chemin':'Path', ($settings['backup_path'] ?? '/backups/gvna').'/'.$created->format('Y/m/d').'/'],
        [$isFr?'Disque':'Disk', '/dev/sda2 (SSD)'],
        [$isFr?'Espace libre':'Free space', '243.2 GB (49%)'],
    ];
    $sysInfo = [
        [$isFr?'Serveur':'Server', $settings['backup_server'] ?? 'GVNA-Server-01'],
        [$isFr?'Système d\'exploitation':'OS', $settings['backup_os'] ?? 'Ubuntu 22.04 LTS'],
        ['PHP Version', PHP_VERSION],
        [$isFr?'Fuseau horaire':'Timezone', 'Africa/Douala (UTC+1)'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $backup->filename }} — Administration</title>
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

            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.backups', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-3.5 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour':'Back' }}</a>
                    <div>
                        <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr?'Détail de la sauvegarde':'Backup detail' }}</h1>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60]"><a href="{{ route('admin.backups', ['lang'=>$lang]) }}" class="hover:text-[#157A43]">Backups &amp; Logs</a> <span class="mx-1">/</span> {{ $isFr?'Sauvegardes':'Backups' }} <span class="mx-1">/</span> <span class="text-[#1B1B18]">{{ $isFr?'Détail':'Detail' }}</span></p>
                    </div>
                </div>
                <img src="{{ asset('images/landing/hh-statue.png') }}" alt="" class="hidden lg:block w-[120px] h-[100px] object-cover rounded-xl opacity-95" aria-hidden="true">
            </div>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                <div class="space-y-5">
                    {{-- Title card --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <span class="w-[54px] h-[54px] rounded-xl bg-[#E8F2EC] flex items-center justify-center"><i data-lucide="database" class="w-7 h-7 text-[#157A43]"></i></span>
                                <div>
                                    <span class="inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr?'Réussi':'Success' }}</span>
                                    <p class="mt-1 text-[18px] font-bold text-[#1B1B18]">{{ $backup->filename }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr?'Télécharger':'Download' }}</a>
                                <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#14652F]"><i data-lucide="shield-check" class="w-4 h-4"></i>{{ $isFr?'Vérifier':'Verify' }}</a>
                                <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                    <button type="submit" class="inline-flex items-center gap-2 bg-white border border-[#F5C9C9] hover:border-[#DC2626] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#DC2626]"><i data-lucide="trash-2" class="w-4 h-4"></i>{{ $isFr?'Supprimer':'Delete' }}</button>
                                </form>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-3 border-t border-[#F0F1F0] pt-4">
                            @foreach($metaRow as [$mIcon, $mVal, $mLabel])
                            <div class="flex items-start gap-2.5"><i data-lucide="{{ $mIcon }}" class="w-4 h-4 mt-0.5 text-[#C9942E] shrink-0"></i><div><p class="text-[12px] font-semibold text-[#1B1B18]">{{ $mVal }}</p><p class="text-[10.5px] text-[#8A857A]">{{ $mLabel }}</p></div></div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Info tabs --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <div class="flex items-center gap-6 border-b border-[#EAE7DE] overflow-x-auto">
                            @foreach($tabs as [$tLabel, $tActive])<span class="pb-3 whitespace-nowrap text-[13px] font-semibold {{ $tActive ? 'text-[#14652F] border-b-2 border-[#14652F]' : 'text-[#8A857A]' }}">{{ $tLabel }}</span>@endforeach
                        </div>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
                            @foreach($infoLeft as [$l, $v])<div class="flex items-start justify-between gap-3 border-b border-[#F4F5F4] pb-2.5"><dt class="text-[12px] text-[#6F6B60]">{{ $l }} :</dt><dd class="text-[12px] font-semibold text-[#1B1B18] text-right">{{ $v }}</dd></div>@endforeach
                            @foreach($infoRight as [$l, $v])<div class="flex items-start justify-between gap-3 border-b border-[#F4F5F4] pb-2.5"><dt class="text-[12px] text-[#6F6B60]">{{ $l }} :</dt><dd class="text-[12px] font-semibold text-[#1B1B18] text-right">{{ $v }}</dd></div>@endforeach
                        </div>
                        <div class="mt-4">
                            <p class="text-[12px] text-[#6F6B60]">{{ $isFr?'Somme de contrôle (SHA256)':'Checksum (SHA256)' }} :</p>
                            <p class="mt-1.5 bg-[#F7F8F7] border border-[#EFF0EF] rounded-lg px-3.5 py-2.5 text-[11.5px] font-mono text-[#3B382F] break-all">{{ $checksum }}</p>
                        </div>
                    </section>

                    {{-- Logs of this backup --}}
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr?'Logs de cette sauvegarde':'Logs of this backup' }}</h2>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[640px]">
                                <thead><tr class="text-left border-b border-[#F0F1F0]">
                                    <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr?'Heure':'Time' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr?'Niveau':'Level' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr?'Événement':'Event' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">Description</th>
                                    <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr?'Durée':'Duration' }}</th>
                                </tr></thead>
                                <tbody class="divide-y divide-[#F4F5F4]">
                                    @foreach($bkLogs as [$lTime, $lLevel, $lEvent, $lDesc, $lDur])
                                    <tr>
                                        <td class="py-3 pr-3 text-[11.5px] text-[#3B382F] whitespace-nowrap">{{ $created->format('d M Y') }}, {{ $lTime }}</td>
                                        <td class="py-3 px-2"><span class="inline-block rounded-md px-2 py-0.5 text-[10.5px] font-bold {{ $lLevel === 'success' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#E8EFFB] text-[#3565DE]' }}">{{ strtoupper($lLevel) }}</span></td>
                                        <td class="py-3 px-2 text-[12px] font-medium text-[#1B1B18]">{{ $lEvent }}</td>
                                        <td class="py-3 px-2 text-[12px] text-[#3B382F]">{{ $lDesc }}</td>
                                        <td class="py-3 pl-2 text-right text-[11.5px] text-[#6F6B60] whitespace-nowrap">{{ $lDur }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#157A43]">{{ $isFr?'Voir tous les logs détaillés':'View all detailed logs' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                    </section>
                </div>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Aperçu de la sauvegarde':'Backup overview' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="relative w-[92px] h-[92px] rounded-full shrink-0" style="background: conic-gradient(#157A43 0deg 241deg, #C9942E 241deg 324deg, #3565DE 324deg 346deg, #C4C0B6 346deg 360deg)"><span class="absolute inset-[14px] rounded-full bg-white flex flex-col items-center justify-center"><span class="text-[15px] font-bold text-[#1B1B18] leading-none">{{ $sizeGb }}</span><span class="text-[9px] text-[#8A857A]">GB</span></span></span>
                            <div class="flex-1 space-y-1.5">
                                @foreach($apercu as [$aLabel, $aVal, $aPct, $aColor])
                                <div class="flex items-center justify-between text-[11px]"><span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full" style="background-color: {{ $aColor }}"></span>{{ $aLabel }}</span><span class="font-semibold text-[#1B1B18]">{{ $aVal }} ({{ $aPct }})</span></div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Emplacement de stockage':'Storage location' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">@foreach($storLoc as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] text-right truncate">{{ $v }}</dd></div>@endforeach</dl>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Actions rapides':'Quick actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            <a href="{{ route('admin.exports', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="download" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr?'Télécharger cette sauvegarde':'Download this backup' }}</span></a>
                            <a href="{{ route('admin.audit-log', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="rotate-ccw" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr?'Restaurer cette sauvegarde':'Restore this backup' }}</span></a>
                            <a href="{{ route('admin.backups', ['lang'=>$lang]) }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="copy" class="w-[16px] h-[16px] text-[#157A43]"></i><span class="text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $isFr?'Cloner cette sauvegarde':'Clone this backup' }}</span></a>
                            <form method="POST" action="{{ route('admin.backups.clean') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                <button type="submit" class="w-full flex items-center gap-3 py-2.5 group text-left"><i data-lucide="trash-2" class="w-[16px] h-[16px] text-[#DC2626]"></i><span class="text-[12.5px] text-[#DC2626]">{{ $isFr?'Supprimer cette sauvegarde':'Delete this backup' }}</span></button>
                            </form>
                        </div>
                    </section>
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr?'Informations système':'System information' }}</h2>
                        <dl class="mt-3 space-y-2.5 text-[12px]">@foreach($sysInfo as [$l,$v])<div class="flex items-center justify-between gap-3"><dt class="text-[#6F6B60]">{{ $l }} :</dt><dd class="font-semibold text-[#1B1B18] text-right">{{ $v }}</dd></div>@endforeach</dl>
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
