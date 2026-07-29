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
        'kyc'          => $isFr ? 'KYC & Vérifications' : 'KYC & Verifications',
        'medias'       => $isFr ? 'Médias & Ressources' : 'Media & Resources',
        'evenements'   => $isFr ? 'Événements & Actualités' : 'Events & News',
    ];
    $formatPill = [
        'csv'  => ['CSV',  'bg-[#E8F2DC] dark:bg-[#0C3D1D] text-[#2E6B34] dark:text-[#339B56]'],
        'xlsx' => ['XLSX', 'bg-[#E8F2DC] dark:bg-[#0C3D1D] text-[#2E6B34] dark:text-[#339B56]'],
        'pdf'  => ['PDF',  'bg-[#FAE7E3] dark:bg-[#3A1013] text-[#C4392B] dark:text-[#F0555C]'],
        'zip'  => ['ZIP',  'bg-[#ECE6F7] dark:bg-[#1E1733] text-[#7C4FE0] dark:text-[#BCA4F0]'],
    ];
    // [lucide name, light colour, dark colour] — the design's coloured file
    // badges as stroked glyphs, so the format still reads by shape and colour
    // without a raster tile that keeps its own white edge on the dark theme.
    $fileIcon = [
        'csv'  => ['file-spreadsheet', '#0A6220', '#4FB06C'],
        'xlsx' => ['file-chart-column', '#084512', '#4FB06C'],
        'pdf'  => ['file-text', '#9A080E', '#F0555C'],
        'zip'  => ['file-archive', '#562E93', '#BCA4F0'],
    ];
    $statusMeta = [
        'reussi'   => [$isFr ? 'Réussi' : 'Success',      'text-[#157A43] dark:text-[#339B56]', 'circle-check'],
        'en_cours' => [$isFr ? 'En cours' : 'Running',    'text-[#DF860A] dark:text-[#EDB33A]', 'dot'],
        'echoue'   => [$isFr ? 'Échoué' : 'Failed',       'text-[#B0070F] dark:text-[#F0555C]', 'circle-x'],
        'planifie' => [$isFr ? 'Planifié' : 'Scheduled',  'text-[#1B62BC] dark:text-[#8FB6F5]', 'dot'],
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

    // KPI cards — real registry counts. The design's "+18 ce mois" / "+2.8 GB ce mois"
    // deltas had no prior-period source and are not rendered.
    $dexTotal   = (int) $exportStats['total'];
    $dexPct     = fn ($n) => $dexTotal ? number_format($n / $dexTotal * 100, 1) : '0.0';
    $dexOfTotal = fn ($n) => $dexPct($n) . '% ' . ($isFr ? 'du total' : 'of total');
    $dexOk      = (int) ($exportByStatus['reussi'] ?? 0);
    $dexRun     = (int) ($exportByStatus['en_cours'] ?? 0);
    $dexFail    = (int) ($exportByStatus['echoue'] ?? 0);
    $dexPlan    = (int) ($exportByStatus['planifie'] ?? 0);
    // [lucide name, tile fill] — the design's raster tiles redrawn as lucide
    // glyphs on the same fills. A stroked glyph inherits its colour, so nothing
    // here carries a baked white edge onto the dark theme.
    $cards = [
        [['file-down', '#04411C'],    number_format($dexTotal),        $isFr ? 'Exports Totaux' : 'Total Exports',       ''],
        [['shield-check', '#6F3E09'], number_format($dexOk),           $isFr ? 'Exports Réussis' : 'Successful Exports', $dexOfTotal($dexOk)],
        [['clock', '#9B4503'],        number_format($dexRun),          $isFr ? 'En Cours' : 'Running',                   $dexOfTotal($dexRun)],
        [['circle-x', '#850D0E'],     number_format($dexFail),         $isFr ? 'Échoués' : 'Failed',                     $dexOfTotal($dexFail)],
        [['database', '#543186'],     $dexSize($exportStats['bytes']), $isFr ? 'Données Exportées' : 'Data Exported',    ''],
    ];

    // Donut — real status split of the registry.
    $dexStatusSlices = [
        ['#157A43', '#0B5726', $isFr ? 'Réussis' : 'Success',     $dexOk],
        ['#E8930C', '#D07105', $isFr ? 'En cours' : 'Running',    $dexRun],
        ['#B0070F', '#B0070F', $isFr ? 'Échoués' : 'Failed',      $dexFail],
        ['#1B62BC', '#1B62BC', $isFr ? 'Planifiés' : 'Scheduled', $dexPlan],
    ];
    $dexLegend = []; $dexSegs = []; $dexAngle = 0;
    foreach ($dexStatusSlices as [$dsDot, $dsArc, $dsLabel, $dsN]) {
        if ($dsN > 0) {
            $dsTo = $dexAngle + ($dsN / max(1, $dexTotal)) * 360;
            $dexSegs[] = sprintf('%s %.2fdeg %.2fdeg', $dsArc, $dexAngle, $dsTo);
            $dexAngle = $dsTo;
        }
        $dexLegend[] = [$dsDot, $dsLabel, $dexPct($dsN) . '% (' . $dsN . ')'];
    }
    $dexConic = $dexSegs ? 'conic-gradient(' . implode(', ', $dexSegs) . ')' : '#F3E9DA';

    // Type bars — real per-dataset counts (icons/colours cycle through the design set).
    $dexTypeIcons  = ['file-text', 'users', 'package', 'receipt', 'folder-tree'];
    $dexTypeColors = ['#10562C', '#E48C16', '#5887BD', '#B84A45', '#9882BB'];
    $typeBars = [];
    foreach ($exportByDataset as $ti => $td) {
        $typeBars[] = [
            $dexTypeIcons[$ti % 5], $datasets[$td->dataset] ?? $td->dataset,
            $dexPct((int) $td->n) . '% (' . (int) $td->n . ')',
            $dexTypeColors[$ti % 5], (float) $dexPct((int) $td->n),
        ];
    }

    $quickActions = [
        ['plus',            $isFr ? 'Créer un nouvel export' : 'Create a new export', '#dex-new'],
        ['calendar-clock',  $isFr ? 'Export planifié' : 'Scheduled export',           route('admin.exports', ['lang' => $lang, 'statut' => 'planifie'])],
        ['layout-template', $isFr ? 'Modèles d\'export' : 'Export templates',         route('admin.settings', ['lang' => $lang])],
        ['settings',        $isFr ? 'Paramètres d\'export' : 'Export settings',       route('admin.settings', ['lang' => $lang])],
    ];

    $lastPage = $exports->lastPage();
    $pageItems = [];
    if ($lastPage > 5) {
        $pageItems = [1, 2, 3, '…', $lastPage];
    } else {
        for ($i = 1; $i <= $lastPage; $i++) $pageItems[] = $i;
    }
    $perOptions = [10, 25, 50];
    $perUrl = fn ($n) => request()->fullUrlWithQuery(['per' => $n, 'page' => 1]);
@endphp

@section('content')
            @if(session('status'))
            <div class="mb-4 bg-[#E9F3DE] dark:bg-[#0C3D1D] border border-[#BFDCAA] dark:border-[#39402F] text-[#2E6B34] dark:text-[#339B56] text-[14px] md:text-[12.5px] font-medium rounded-xl px-4 py-2.5">{{ session('status') }}</div>
            @endif
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_270px] gap-5 items-start">

                {{-- ============ MAIN COLUMN ============ --}}
                <div class="min-w-0">
                    {{-- 5 stat cards --}}
                    <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                        @foreach($cards as [$cIcon, $cValue, $cLabel, $cSub])
                        <div class="sub-card px-4 py-4">
                            <div class="flex items-start gap-3">
                                <span class="w-[46px] h-[46px] shrink-0 rounded-xl grid place-items-center text-white" style="background:{{ $cIcon[1] }}" aria-hidden="true"><i data-lucide="{{ $cIcon[0] }}" class="w-[23px] h-[23px]" stroke-width="1.9"></i></span>
                                <div class="min-w-0">
                                    <p class="text-[20px] font-bold text-[#23231F] dark:text-[#F3EFE7] leading-tight">{{ $cValue }}</p>
                                    <p class="text-[13px] md:text-[11.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6] leading-snug">{{ $cLabel }}</p>
                                    @if($cSub !== '')<p class="mt-1.5 text-[13px] md:text-[11px] font-semibold text-[#6F6B60] dark:text-[#868778]">{{ $cSub }}</p>@endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </section>

                    {{-- Filters --}}
                    <form method="GET" class="mt-5 flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        {{-- Blank until the operator picks a period; an unfiltered list is not
                             "01 Mai 2025 – 12 Mai 2025". --}}
                        <div class="ui-field-group">
                            <input type="text" name="du" value="{{ $filters['du'] }}" placeholder="{{ $isFr ? 'Du' : 'From' }}" class="ui-field-bare w-[74px]" aria-label="{{ $isFr ? 'Du' : 'From' }}">
                            <input type="text" name="au" value="{{ $filters['au'] }}" placeholder="{{ $isFr ? 'Au' : 'To' }}" class="ui-field-bare w-[74px]" aria-label="{{ $isFr ? 'Au' : 'To' }}">
                            <button type="submit" class="shrink-0 text-[#B06E12] dark:text-[#EDB33A]" aria-label="{{ $isFr ? 'Filtrer par période' : 'Filter by period' }}"><i data-lucide="calendar-days" class="w-[16px] h-[16px]"></i></button>
                        </div>
                        <select name="type" onchange="this.form.submit()" class="ui-field ui-select w-[142px]">
                            <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                            @foreach($datasets as $dk => $dl)
                            <option value="{{ $dk }}" @selected($filters['type']===$dk)>{{ $dl }}</option>
                            @endforeach
                        </select>
                        <select name="statut" onchange="this.form.submit()" class="ui-field ui-select w-[138px]">
                            <option value="">{{ $isFr ? 'Tous les status' : 'All statuses' }}</option>
                            @foreach($statusMeta as $sv => $sm)
                            <option value="{{ $sv }}" @selected($filters['statut']===$sv)>{{ $sm[0] }}</option>
                            @endforeach
                        </select>
                        <div class="ui-field-group flex-1 min-w-[120px]">
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher un export...' : 'Search an export...' }}" class="ui-field-bare flex-1 min-w-0">
                            <button type="submit" class="shrink-0 text-[#B06E12] dark:text-[#EDB33A]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"><i data-lucide="search" class="w-[17px] h-[17px]"></i></button>
                        </div>
                        {{-- Nouvel Export: real POST — exports the currently filtered dataset (artisans by default) --}}
                        <button type="submit" form="dex-new" class="ui-btn ui-btn-primary ui-btn-lg"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Nouvel Export' : 'New Export' }}</button>
                    </form>
                    <form id="dex-new" method="POST" action="{{ route('admin.exports.create') }}" class="hidden">
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="dataset" value="{{ $filters['type'] !== '' ? $filters['type'] : 'artisans' }}">
                        <input type="hidden" name="format" value="csv">
                    </form>

                    {{-- Table --}}
                    <section class="sub-card mt-4 px-5 py-5">
                        <div class="ui-table-wrap">
                            <table class="ui-table min-w-[880px]">
                                <thead>
                                    <tr class="border-b border-[#F5EBDA] dark:border-[#262B21]">
                                        <th>{{ $isFr ? 'Nom de l\'export' : 'Export name' }}</th>
                                        <th>{{ $isFr ? 'Type de données' : 'Data type' }}</th>
                                        <th>Format</th>
                                        <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th>{{ $isFr ? 'Taille' : 'Size' }}</th>
                                        <th>{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                        <th>Expiration</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exports as $e)
                                    @php
                                        [$stLabel, $stText, $stIcon] = $statusMeta[$e->status] ?? [$e->status, 'text-[#6E6B63] dark:text-[#868778]', 'dot'];
                                        [$fpLabel, $fpCls] = $formatPill[$e->format] ?? [strtoupper($e->format), 'bg-[#EFEFEA] dark:bg-[#1A1E16] text-[#55524A] dark:text-[#B4B5A6]'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @php [$fiName, $fiLight, $fiDark] = $fileIcon[$e->format] ?? $fileIcon['csv']; @endphp
                                                <i data-lucide="{{ $fiName }}" class="w-[26px] h-[26px] shrink-0 text-[{{ $fiLight }}] dark:text-[{{ $fiDark }}]" stroke-width="1.7" role="img" aria-label="{{ strtoupper($e->format) }}"></i>
                                                <span class="min-w-0">
                                                    <span class="block text-[15px] md:text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7] truncate">{{ $e->name }}</span>
                                                    <span class="block text-[13px] md:text-[11.5px] text-[#8A8578] dark:text-[#868778]">{{ number_format($e->records) }} {{ $e->counts_files ? ($isFr ? 'fichiers' : 'files') : ($isFr ? 'enregistrements' : 'records') }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $datasets[$e->dataset] ?? $e->dataset }}</td>
                                        <td><span class="inline-block rounded-md px-2.5 py-1 text-[12px] md:text-[10.5px] font-bold tracking-[0.04em] {{ $fpCls }}">{{ $fpLabel }}</span></td>
                                        <td class="whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 text-[14px] md:text-[12px] font-semibold {{ $stText }}">
                                                @if($stIcon === 'dot')<span class="w-2 h-2 rounded-full shrink-0 {{ $e->status === 'en_cours' ? 'bg-[#DF860A] dark:bg-[#3A2B06] ' : 'bg-[#1B62BC]' }}"></span>
                                                @else<i data-lucide="{{ $stIcon }}" class="w-[15px] h-[15px] shrink-0"></i>@endif
                                                {{ $stLabel }}
                                            </span>
                                        </td>
                                        <td class="font-semibold text-[#23231F] dark:text-[#F3EFE7] whitespace-nowrap">{{ $dexSize($e->size_bytes) }}</td>
                                        <td class="whitespace-nowrap"><span class="block text-[14px] md:text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">{{ $dexDate($e->created_at) }}</span><span class="block text-[13px] md:text-[11.5px] text-[#8A8578] dark:text-[#868778]">{{ \Carbon\Carbon::parse($e->created_at)->format('H:i') }}</span></td>
                                        <td class="whitespace-nowrap">
                                            @if($e->expires_at)<span class="block text-[14px] md:text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">{{ $dexDate($e->expires_at) }}</span><span class="block text-[13px] md:text-[11.5px] text-[#8A8578] dark:text-[#868778]">{{ \Carbon\Carbon::parse($e->expires_at)->format('H:i') }}</span>
                                            @else<span class="text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]">–</span>@endif
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($e->status === 'en_cours')
                                                <form method="POST" action="{{ route('admin.exports.status', ['id' => $e->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}"><input type="hidden" name="statut" value="planifie">
                                                    <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#DF860A] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#DF860A] dark:text-[#EDB33A]" title="{{ $isFr ? 'Mettre en pause' : 'Pause' }}"><i data-lucide="pause" class="w-[15px] h-[15px]"></i></button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.exports.status', ['id' => $e->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}"><input type="hidden" name="statut" value="echoue">
                                                    <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#B0070F] dark:hover:border-[#F0555C] flex items-center justify-center text-[#B0070F] dark:text-[#F0555C]" title="{{ $isFr ? 'Annuler' : 'Cancel' }}"><i data-lucide="x" class="w-[15px] h-[15px]"></i></button>
                                                </form>
                                                @else
                                                    @if($e->status === 'reussi')
                                                    <a href="{{ route('admin.exports.download', ['id' => $e->id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#8A6B3F] dark:text-[#EDB33A]" title="{{ $isFr ? 'Télécharger' : 'Download' }}"><i data-lucide="download" class="w-[15px] h-[15px]"></i></a>
                                                    @endif
                                                    <a href="{{ route('admin.exports', ['lang' => $lang, 'type' => $e->dataset]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#8A6B3F] dark:text-[#EDB33A]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-[15px] h-[15px]"></i></a>
                                                    <form method="POST" action="{{ route('admin.exports.delete', ['id' => $e->id]) }}" onsubmit="return confirm('{{ $isFr ? 'Supprimer cet export ?' : 'Delete this export?' }}')">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                                                        <button type="submit" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#B0070F] dark:hover:border-[#F0555C] flex items-center justify-center text-[#C4756B] dark:text-[#F0555C]" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"><i data-lucide="trash-2" class="w-[15px] h-[15px]"></i></button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    {{-- data_exports is empty. The "new export" form on this
                                         screen writes the row, so this is a queue nobody has
                                         used yet rather than a disconnected screen. --}}
                                    <tr><td colspan="8" class="p-0">
                                        @include('pages.partials.empty-state', [
                                            'icon'  => 'download',
                                            'state' => 'empty',
                                            'title' => $isFr ? 'Aucun export demandé' : 'No export requested',
                                            'body'  => $isFr
                                                ? 'Chaque export que vous demandez apparaît ici avec son état et son lien de téléchargement. Aucun n\'a encore été demandé.'
                                                : 'Every export you request appears here with its state and download link. None has been requested yet.',
                                        ])
                                    </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 pt-4 border-t border-[#F5EBDA] dark:border-[#262B21] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[14px] md:text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]">
                                {{ $isFr ? 'Affichage de' : 'Showing' }} {{ $exports->firstItem() ?? 0 }} {{ $isFr ? 'à' : 'to' }} {{ $exports->lastItem() ?? 0 }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($exports->total()) }} {{ $isFr ? 'exports' : 'exports' }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                @if($exports->currentPage() > 1)
                                <a href="{{ $exports->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></a>
                                <a href="{{ $exports->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @endif
                                @foreach($pageItems as $pi)
                                    @if($pi === '…')
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[13px] text-[#3B382F] dark:text-[#B4B5A6]">...</span>
                                    @elseif($pi === $exports->currentPage())
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#093F1F] dark:bg-[#0C3B1E] text-white text-[13px] font-semibold rounded-[10px]">{{ $pi }}</span>
                                    @else
                                    <a href="{{ $exports->url($pi) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[13px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]">{{ $pi }}</a>
                                    @endif
                                @endforeach
                                @if($exports->hasMorePages())
                                <a href="{{ $exports->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#B06E12] dark:text-[#EDB33A] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                                <a href="{{ $exports->url($lastPage) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#B06E12] dark:text-[#EDB33A] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></span>
                                @endif
                            </div>
                            <select onchange="window.location = this.value" class="ui-field ui-select">
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
                        <h2 class="ui-card-title">{{ $isFr ? 'Activité d\'export' : 'Export activity' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-[88px] h-[88px] rounded-full shrink-0" style="background: {{ $dexConic }}"><span class="block w-[48px] h-[48px] rounded-full bg-[#FEFAF4] dark:bg-[#12150F] m-[20px]"></span></span>
                            <div class="flex-1 space-y-2">
                                @foreach($dexLegend as [$dlColor, $dlName, $dlValue])
                                <div class="flex items-center justify-between gap-2 text-[13px] md:text-[11px]">
                                    <span class="flex items-center gap-1.5 font-medium text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $dlColor }}"></span>{{ $dlName }}</span>
                                    <span class="font-semibold text-[#23231F] dark:text-[#F3EFE7] whitespace-nowrap">{{ $dlValue }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Exports par type de données --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="ui-card-title">{{ $isFr ? 'Exports par type de données' : 'Exports by data type' }}</h2>
                        <div class="mt-4 space-y-4">
                            @forelse($typeBars as [$tIcon, $tLabel, $tValue, $tColor, $tPct])
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-2 text-[14px] md:text-[12px] font-medium text-[#3B382F] dark:text-[#B4B5A6]"><i data-lucide="{{ $tIcon }}" class="w-[18px] h-[18px] shrink-0" style="color:{{ $tColor }}" aria-hidden="true"></i>{{ $tLabel }}</span>
                                    <span class="text-[13px] md:text-[11.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7] whitespace-nowrap">{{ $tValue }}</span>
                                </div>
                                <div class="mt-1.5 h-[6px] rounded-full bg-[#F3E9DA] dark:bg-[#1A1E16] overflow-hidden"><span class="block h-full rounded-full" style="width: {{ $tPct }}%; background-color: {{ $tColor }}"></span></div>
                            </div>
                            @empty
                            <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</p>
                            @endforelse
                        </div>
                    </section>

                    {{-- Actions rapides --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3 space-y-1">
                            @foreach($quickActions as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-1.5 min-h-[44px] md:min-h-0 group"><i data-lucide="{{ $qIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#8A5A16] dark:text-[#EDB33A]" stroke-width="1.9" aria-hidden="true"></i><span class="text-[14px] md:text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $qLabel }}</span></a>
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
