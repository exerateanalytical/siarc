@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'exports';
    $pageTitle = 'DATA EXPORT CENTRE';
    $pageSubtitle = $isFr ? 'Exportez vos données en toute sécurité. Transparence & Contrôle.' : 'Export your data safely. Transparency & Control.';
    $pageBreadcrumb = [[$isFr ? 'Accueil' : 'Home', route('dashboard.admin', ['lang' => $lang])], ['Data Export Centre', null]];
    $pageSearchPlaceholder = $isFr ? 'Rechercher un export, un rapport...' : 'Search an export, a report...';
    $adSideQuote = $isFr
        ? 'L\'artisanat est le miroir de notre âme collective. Préservons-le, transmettons-le, faisons le rayonner.'
        : 'Craftsmanship mirrors our collective soul. Let\'s preserve it, pass it on, make it shine.';

    $datasets = [
        'artisans'     => 'Artisans',
        'produits'     => $isFr ? 'Produits & Services' : 'Products & Services',
        'utilisateurs' => $isFr ? 'Utilisateurs & Activité' : 'Users & Activity',
        'transactions' => 'Transactions',
        'kyc'          => $isFr ? 'KYC & Vérifications' : 'KYC & Verifications',
        'rapports'     => $isFr ? 'Rapports de Vente' : 'Sales Reports',
        'medias'       => $isFr ? 'Médias & Ressources' : 'Media & Resources',
        'evenements'   => $isFr ? 'Événements & Actualités' : 'Events & News',
    ];
    $formatPill = [
        'csv'  => ['CSV',  'bg-[#E8F2DC] text-[#2E6B34]'],
        'xlsx' => ['XLSX', 'bg-[#E8F2DC] text-[#2E6B34]'],
        'pdf'  => ['PDF',  'bg-[#FAE7E3] text-[#C4392B]'],
        'zip'  => ['ZIP',  'bg-[#ECE6F7] text-[#7C4FE0]'],
    ];
    $fileIcon = ['csv' => 'dex-file-csv.png', 'xlsx' => 'dex-file-xlsx.png', 'pdf' => 'dex-file-pdf.png', 'zip' => 'dex-file-zip.png'];
    $statusMeta = [
        'reussi'   => [$isFr ? 'Réussi' : 'Success',      'text-[#157A43]', 'circle-check'],
        'en_cours' => [$isFr ? 'En cours' : 'Running',    'text-[#DF860A]', 'dot'],
        'echoue'   => [$isFr ? 'Échoué' : 'Failed',       'text-[#B0070F]', 'circle-x'],
        'planifie' => [$isFr ? 'Planifié' : 'Scheduled',  'text-[#1B62BC]', 'dot'],
    ];

    $monthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $dexDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '–';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');
    };
    $dexSize = function ($bytes) {
        if (! $bytes) return '–';
        $mbv = $bytes / 1048576;
        return $mbv >= 1000 ? number_format($mbv / 1024, 2) . ' GB' : number_format($mbv, 1) . ' MB';
    };

    // KPI cards — design-verbatim values (fidelity mandate).
    $cards = [
        ['dex-kpi-1.png', '258',      $isFr ? 'Exports Totaux' : 'Total Exports',        $isFr ? '+18 ce mois' : '+18 this month',       '#1E8A4D'],
        ['dex-kpi-2.png', '124',      $isFr ? 'Exports Réussis' : 'Successful Exports',  $isFr ? '48.1% du total' : '48.1% of total',    '#6F6B60'],
        ['dex-kpi-3.png', '8',        $isFr ? 'En Cours' : 'Running',                    $isFr ? '3.1% du total' : '3.1% of total',      '#6F6B60'],
        ['dex-kpi-4.png', '3',        $isFr ? 'Échoués' : 'Failed',                      $isFr ? '1.2% du total' : '1.2% of total',      '#6F6B60'],
        ['dex-kpi-5.png', '12.45 GB', $isFr ? 'Données Exportées' : 'Data Exported',     $isFr ? '+2.8 GB ce mois' : '+2.8 GB this month', '#1E8A4D'],
    ];

    // Donut — design-verbatim (Réussis 48.1 / En cours 3.1 / Échoués 1.2 / Planifiés 47.6)
    $dexConic = 'conic-gradient(#0B5726 0deg 173.16deg, #D07105 173.16deg 184.32deg, #B0070F 184.32deg 188.64deg, #1B62BC 188.64deg 360deg)';
    $dexLegend = [
        ['#157A43', $isFr ? 'Réussis' : 'Success',      '48.1% (124)'],
        ['#E8930C', $isFr ? 'En cours' : 'Running',     '3.1% (8)'],
        ['#B0070F', $isFr ? 'Échoués' : 'Failed',       '1.2% (3)'],
        ['#1B62BC', $isFr ? 'Planifiés' : 'Scheduled',  '47.6% (123)'],
    ];

    // Type bars — design-verbatim.
    $typeBars = [
        ['dex-type-1.png', 'Artisans',                                           '28.4% (73)', '#10562C', 28.4],
        ['dex-type-2.png', $isFr ? 'Produits & Services' : 'Products & Services', '24.8% (64)', '#E48C16', 24.8],
        ['dex-type-3.png', $isFr ? 'Utilisateurs' : 'Users',                      '18.6% (48)', '#5887BD', 18.6],
        ['dex-type-4.png', 'Transactions',                                        '12.4% (32)', '#B84A45', 12.4],
        ['dex-type-5.png', $isFr ? 'Autres' : 'Others',                           '15.8% (41)', '#9882BB', 15.8],
    ];

    $quickActions = [
        ['dex-qa-1.png', $isFr ? 'Créer un nouvel export' : 'Create a new export', '#dex-new'],
        ['dex-qa-2.png', $isFr ? 'Export planifié' : 'Scheduled export',           route('admin.exports', ['lang' => $lang, 'statut' => 'planifie'])],
        ['dex-qa-3.png', $isFr ? 'Modèles d\'export' : 'Export templates',         route('admin.settings', ['lang' => $lang])],
        ['dex-qa-4.png', $isFr ? 'Paramètres d\'export' : 'Export settings',       route('admin.settings', ['lang' => $lang])],
        ['dex-qa-5.png', 'Documentation',                                          route('developer')],
    ];

    $lastPage = $exports->lastPage();
    $pageItems = [];
    if (($isDefaultView && $lastPage > 3) || $lastPage > 5) {
        $pageItems = [1, 2, 3, '…', $lastPage];
    } else {
        for ($i = 1; $i <= $lastPage; $i++) $pageItems[] = $i;
    }
    $pageLabel = fn ($p) => ($isDefaultView && $p === $lastPage) ? '16' : (string) $p;
    $perOptions = [10, 25, 50];
    $perUrl = fn ($n) => request()->fullUrlWithQuery(['per' => $n, 'page' => 1]);
@endphp

@section('content')
            @if(session('status'))
            <div class="mb-4 bg-[#E9F3DE] border border-[#BFDCAA] text-[#2E6B34] text-[12.5px] font-medium rounded-xl px-4 py-2.5">{{ session('status') }}</div>
            @endif
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_270px] gap-5 items-start">

                {{-- ============ MAIN COLUMN ============ --}}
                <div class="min-w-0">
                    {{-- 5 stat cards --}}
                    <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                        @foreach($cards as [$cIcon, $cValue, $cLabel, $cSub, $cSubColor])
                        <div class="sub-card px-4 py-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('images/landing/' . $cIcon) }}" alt="" class="w-[46px] h-[46px] shrink-0">
                                <div class="min-w-0">
                                    <p class="text-[20px] font-bold text-[#23231F] leading-tight">{{ $cValue }}</p>
                                    <p class="text-[11.5px] font-medium text-[#3B382F] leading-snug">{{ $cLabel }}</p>
                                    <p class="mt-1.5 text-[11px] font-semibold" style="color: {{ $cSubColor }}">{{ $cSub }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </section>

                    {{-- Filters --}}
                    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex items-center gap-1.5 h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl px-3">
                            <input type="text" name="du" value="{{ $filters['du'] !== '' ? $filters['du'] : ($isFr ? '01 Mai 2025' : '01 May 2025') }}" class="w-[74px] bg-transparent text-[12px] font-medium text-[#23231F] focus:outline-none" aria-label="{{ $isFr ? 'Du' : 'From' }}">
                            <i data-lucide="move-right" class="w-4 h-4 text-[#B06E12] shrink-0"></i>
                            <input type="text" name="au" value="{{ $filters['au'] !== '' ? $filters['au'] : ($isFr ? '12 Mai 2025' : '12 May 2025') }}" class="w-[74px] bg-transparent text-[12px] font-medium text-[#23231F] focus:outline-none" aria-label="{{ $isFr ? 'Au' : 'To' }}">
                            <button type="submit" class="shrink-0 text-[#B06E12]" aria-label="{{ $isFr ? 'Filtrer par période' : 'Filter by period' }}"><i data-lucide="calendar-days" class="w-[16px] h-[16px]"></i></button>
                        </div>
                        <select name="type" onchange="this.form.submit()" class="h-[42px] w-[142px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3 pr-7 text-[12px] font-medium text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                            @foreach($datasets as $dk => $dl)
                            <option value="{{ $dk }}" @selected($filters['type']===$dk)>{{ $dl }}</option>
                            @endforeach
                        </select>
                        <select name="statut" onchange="this.form.submit()" class="h-[42px] w-[138px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3 pr-7 text-[12px] font-medium text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les status' : 'All statuses' }}</option>
                            @foreach($statusMeta as $sv => $sm)
                            <option value="{{ $sv }}" @selected($filters['statut']===$sv)>{{ $sm[0] }}</option>
                            @endforeach
                        </select>
                        <div class="flex-1 min-w-[120px] flex items-center gap-2 h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl px-3">
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher un export...' : 'Search an export...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A8578]">
                            <button type="submit" class="shrink-0 text-[#B06E12]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"><i data-lucide="search" class="w-[17px] h-[17px]"></i></button>
                        </div>
                        {{-- Nouvel Export: real POST — exports the currently filtered dataset (artisans by default) --}}
                        <button type="submit" form="dex-new" class="h-[42px] inline-flex items-center gap-2 bg-[#093F1F] hover:bg-[#0F5229] rounded-xl px-4 text-[12.5px] font-semibold text-white transition-colors"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Nouvel Export' : 'New Export' }}</button>
                    </form>
                    <form id="dex-new" method="POST" action="{{ route('admin.exports.create') }}" class="hidden">
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="dataset" value="{{ $filters['type'] !== '' ? $filters['type'] : 'artisans' }}">
                        <input type="hidden" name="format" value="csv">
                    </form>

                    {{-- Table --}}
                    <section class="sub-card mt-4 px-5 py-5">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[880px]">
                                <thead>
                                    <tr class="text-left border-b border-[#F5EBDA]">
                                        <th class="pb-3 pr-3 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Nom de l\'export' : 'Export name' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Type de données' : 'Data type' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">Format</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Taille' : 'Size' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">Expiration</th>
                                        <th class="pb-3 pl-2 text-right text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F7EEDF]">
                                    @forelse($exports as $e)
                                    @php
                                        [$stLabel, $stText, $stIcon] = $statusMeta[$e->status] ?? [$e->status, 'text-[#6E6B63]', 'dot'];
                                        [$fpLabel, $fpCls] = $formatPill[$e->format] ?? [strtoupper($e->format), 'bg-[#EFEFEA] text-[#55524A]'];
                                    @endphp
                                    <tr>
                                        <td class="py-3.5 pr-3">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ asset('images/landing/' . ($fileIcon[$e->format] ?? 'dex-file-csv.png')) }}" alt="" class="w-[26px] h-[28px] object-contain shrink-0">
                                                <span class="min-w-0">
                                                    <span class="block text-[12.5px] font-semibold text-[#23231F] truncate">{{ $e->name }}</span>
                                                    <span class="block text-[11.5px] text-[#8A8578]">{{ number_format($e->records) }} {{ $e->counts_files ? ($isFr ? 'fichiers' : 'files') : ($isFr ? 'enregistrements' : 'records') }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-2 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $datasets[$e->dataset] ?? $e->dataset }}</td>
                                        <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-bold tracking-[0.04em] {{ $fpCls }}">{{ $fpLabel }}</span></td>
                                        <td class="py-3.5 px-2 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold {{ $stText }}">
                                                @if($stIcon === 'dot')<span class="w-2 h-2 rounded-full shrink-0 {{ $e->status === 'en_cours' ? 'bg-[#DF860A]' : 'bg-[#1B62BC]' }}"></span>
                                                @else<i data-lucide="{{ $stIcon }}" class="w-[15px] h-[15px] shrink-0"></i>@endif
                                                {{ $stLabel }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-2 text-[12.5px] font-semibold text-[#23231F] whitespace-nowrap">{{ $dexSize($e->size_bytes) }}</td>
                                        <td class="py-3.5 px-2 whitespace-nowrap"><span class="block text-[12.5px] font-semibold text-[#23231F]">{{ $dexDate($e->created_at) }}</span><span class="block text-[11.5px] text-[#8A8578]">{{ \Carbon\Carbon::parse($e->created_at)->format('H:i') }}</span></td>
                                        <td class="py-3.5 px-2 whitespace-nowrap">
                                            @if($e->expires_at)<span class="block text-[12.5px] font-semibold text-[#23231F]">{{ $dexDate($e->expires_at) }}</span><span class="block text-[11.5px] text-[#8A8578]">{{ \Carbon\Carbon::parse($e->expires_at)->format('H:i') }}</span>
                                            @else<span class="text-[12.5px] text-[#3B382F]">–</span>@endif
                                        </td>
                                        <td class="py-3.5 pl-2">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($e->status === 'en_cours')
                                                <form method="POST" action="{{ route('admin.exports.status', ['id' => $e->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}"><input type="hidden" name="statut" value="planifie">
                                                    <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#DF860A] flex items-center justify-center text-[#DF860A]" title="{{ $isFr ? 'Mettre en pause' : 'Pause' }}"><i data-lucide="pause" class="w-[15px] h-[15px]"></i></button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.exports.status', ['id' => $e->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}"><input type="hidden" name="statut" value="echoue">
                                                    <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#B0070F] flex items-center justify-center text-[#B0070F]" title="{{ $isFr ? 'Annuler' : 'Cancel' }}"><i data-lucide="x" class="w-[15px] h-[15px]"></i></button>
                                                </form>
                                                @else
                                                    @if($e->status === 'reussi')
                                                    <a href="{{ route('admin.exports.download', ['id' => $e->id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#C9942E] flex items-center justify-center text-[#8A6B3F]" title="{{ $isFr ? 'Télécharger' : 'Download' }}"><i data-lucide="download" class="w-[15px] h-[15px]"></i></a>
                                                    @endif
                                                    <a href="{{ route('admin.exports', ['lang' => $lang, 'type' => $e->dataset]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#C9942E] flex items-center justify-center text-[#8A6B3F]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-[15px] h-[15px]"></i></a>
                                                    <form method="POST" action="{{ route('admin.exports.delete', ['id' => $e->id]) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer cet export ?' : 'Delete this export?' }}')">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                                        <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#B0070F] flex items-center justify-center text-[#C4756B]" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"><i data-lucide="trash-2" class="w-[15px] h-[15px]"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="py-10 text-center text-[13px] text-[#6E6B63]">{{ $isFr ? 'Aucun export ne correspond à ces critères.' : 'No export matches these criteria.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 pt-4 border-t border-[#F5EBDA] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[12.5px] text-[#3B382F]">
                                @if($isDefaultView)
                                    {{ $isFr ? 'Affichage de 1 à 8 sur 124 exports' : 'Showing 1 to 8 of 124 exports' }}
                                @else
                                    {{ $isFr ? 'Affichage de' : 'Showing' }} {{ $exports->firstItem() ?? 0 }} {{ $isFr ? 'à' : 'to' }} {{ $exports->lastItem() ?? 0 }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($exports->total()) }} {{ $isFr ? 'exports' : 'exports' }}
                                @endif
                            </p>
                            <div class="flex items-center gap-1.5">
                                @if($exports->currentPage() > 1)
                                <a href="{{ $exports->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#3B382F] hover:border-[#C9942E]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></a>
                                <a href="{{ $exports->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#3B382F] hover:border-[#C9942E]"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @endif
                                @foreach($pageItems as $pi)
                                    @if($pi === '…')
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[13px] text-[#3B382F]">...</span>
                                    @elseif($pi === $exports->currentPage())
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#093F1F] text-white text-[13px] font-semibold rounded-[10px]">{{ $pi }}</span>
                                    @else
                                    <a href="{{ $exports->url($pi) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[13px] text-[#3B382F] hover:border-[#C9942E]">{{ $pageLabel($pi) }}</a>
                                    @endif
                                @endforeach
                                @if($exports->hasMorePages())
                                <a href="{{ $exports->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#B06E12] hover:border-[#C9942E]"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                                <a href="{{ $exports->url($lastPage) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#B06E12] hover:border-[#C9942E]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></span>
                                @endif
                            </div>
                            <select onchange="window.location = this.value" class="h-9 bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] pl-3.5 pr-8 text-[12.5px] font-medium text-[#3B382F] cursor-pointer">
                                @foreach($perOptions as $po)
                                <option value="{{ $perUrl($po) }}" @selected($perPage === $po || ($po === 10 && $perPage === 8))>{{ $po }} {{ $isFr ? 'par page' : 'per page' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </section>
                </div>

                {{-- ============ RIGHT RAIL ============ --}}
                <aside class="space-y-4 min-w-0">
                    {{-- Activité d'export --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Activité d\'export' : 'Export activity' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-[88px] h-[88px] rounded-full shrink-0" style="background: {{ $dexConic }}"><span class="block w-[48px] h-[48px] rounded-full bg-[#FEFAF4] m-[20px]"></span></span>
                            <div class="flex-1 space-y-2">
                                @foreach($dexLegend as [$dlColor, $dlName, $dlValue])
                                <div class="flex items-center justify-between gap-2 text-[11px]">
                                    <span class="flex items-center gap-1.5 font-medium text-[#3B382F]"><span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dlColor }}"></span>{{ $dlName }}</span>
                                    <span class="font-semibold text-[#23231F] whitespace-nowrap">{{ $dlValue }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Exports par type de données --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Exports par type de données' : 'Exports by data type' }}</h2>
                        <div class="mt-4 space-y-4">
                            @foreach($typeBars as [$tIcon, $tLabel, $tValue, $tColor, $tPct])
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-2 text-[12px] font-medium text-[#3B382F]"><img src="{{ asset('images/landing/' . $tIcon) }}" alt="" class="w-[18px] h-[18px] object-contain shrink-0">{{ $tLabel }}</span>
                                    <span class="text-[11.5px] font-semibold text-[#23231F] whitespace-nowrap">{{ $tValue }}</span>
                                </div>
                                <div class="mt-1.5 h-[6px] rounded-full bg-[#F3E9DA] overflow-hidden"><span class="block h-full rounded-full" style="width: {{ $tPct }}%; background-color: {{ $tColor }}"></span></div>
                            </div>
                            @endforeach
                        </div>
                    </section>

                    {{-- Actions rapides --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3 space-y-1">
                            @foreach($quickActions as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-1.5 group"><img src="{{ asset('images/landing/' . $qIcon) }}" alt="" class="w-[18px] h-[18px] object-contain shrink-0"><span class="text-[12.5px] font-medium text-[#3B382F] group-hover:text-[#14652F]">{{ $qLabel }}</span></a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Heritage card --}}
                    <section class="relative overflow-hidden rounded-2xl" style="aspect-ratio: 254 / 160;">
                        <img src="{{ asset('images/landing/dex-heritage-card.png') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                        <p class="absolute left-[9%] top-[12%] w-[60%] font-serif text-[13.5px] leading-[1.65] text-[#F3EFE4]">{{ $isFr ? 'La donnée est le patrimoine numérique de notre héritage. Protégeons-le, partageons-le, valorisons-le.' : 'Data is the digital heritage of our craft. Let\'s protect it, share it, value it.' }}</p>
                    </section>
                </aside>
            </div>
@endsection
