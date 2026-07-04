@php
    $isFr = $lang === 'fr';
    $adminActive = 'media';

    // Per-kind chip/legend meta (labels FR/EN, lucide icon, tint colors).
    $mediaKinds = [
        'image'    => ['label' => 'Images',    'icon' => 'image',       'text' => '#14652F', 'bg' => '#E8F3EC', 'dot' => '#14652F'],
        'document' => ['label' => 'Documents', 'icon' => 'file-text',   'text' => '#2563EB', 'bg' => '#E7EEFB', 'dot' => '#2563EB'],
        'video'    => ['label' => $isFr ? 'Vidéos' : 'Videos', 'icon' => 'file-video', 'text' => '#7C3AED', 'bg' => '#F1EAFB', 'dot' => '#7C3AED'],
        'audio'    => ['label' => 'Audio',     'icon' => 'audio-lines', 'text' => '#DC2626', 'bg' => '#FBEDE7', 'dot' => '#C9A227'],
    ];
    $mediaTotal  = $mediaStats['all']['count'];
    $othersCount = max(0, $mediaTotal - collect(array_keys($mediaKinds))->sum(fn ($k) => $mediaStats[$k]['count']));
    $pctOf = fn (int $n) => $mediaTotal > 0 ? round($n / $mediaTotal * 100, 1) : 0.0;

    // Donut conic-gradient stops from the REAL distribution.
    $donutStops = [];
    $acc = 0.0;
    foreach ($mediaKinds as $k => $meta) {
        $p = $pctOf($mediaStats[$k]['count']);
        if ($p > 0) { $donutStops[] = $meta['dot'] . ' ' . $acc . '% ' . ($acc + $p) . '%'; $acc += $p; }
    }
    $donutStops[] = '#D7D3C8 ' . $acc . '% 100%'; // Autres / remainder
    $donutCss = 'conic-gradient(' . implode(', ', $donutStops) . ')';

    // Friendly names for real top-level storage folders.
    $folderNames = [
        'products'   => $isFr ? 'Produits Artisanaux' : 'Artisan Products',
        'businesses' => $isFr ? 'Entreprises & Boutiques' : 'Businesses & Shops',
    ];

    // Bar chart scale (real per-month counts).
    $chartMax = max(1, (int) $mediaMonths->max('count'));
    $chartTop = max(5, (int) (ceil($chartMax / 5) * 5));
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Médias & Documents — Administration' : 'Media & Documents — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">
            {{-- Title / breadcrumb / actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Gestion des Médias & Documents' : 'Media & Documents Management' }}</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">
                        <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                        <span class="mx-1 text-[#B9B4A7]">›</span>
                        <span class="text-[#3B382F]">{{ $isFr ? 'Médias & Documents' : 'Media & Documents' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('products.web-create') }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter un média' : 'Add a media' }}
                    </a>
                    <button type="button" class="inline-flex items-center gap-2 bg-white border border-[#DED9CC] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#14652F] transition-colors">
                        <i data-lucide="folder-plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Nouveau dossier' : 'New folder' }}
                    </button>
                </div>
            </div>

            {{-- Stat chips — REAL counts, chips double as ?type= filters --}}
            <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                @php
                    $chipDefs = [
                        ['all',      $isFr ? 'Total fichiers' : 'Total files', 'files',       '#14652F', '#E8F3EC'],
                        ['image',    'Images',                                  'image',       '#14652F', '#E8F3EC'],
                        ['document', 'Documents',                               'file-text',   '#2563EB', '#E7EEFB'],
                        ['video',    $isFr ? 'Vidéos' : 'Videos',               'file-video',  '#7C3AED', '#F1EAFB'],
                        ['audio',    'Audio',                                   'audio-lines', '#DC2626', '#FBEDE7'],
                    ];
                @endphp
                @foreach($chipDefs as [$ck, $clabel, $cicon, $ctext, $cbg])
                @php $trend = $mediaStats[$ck]['trend']; @endphp
                <a href="{{ route('admin.media', array_filter(['lang' => $lang, 'type' => $ck === 'all' ? null : $ck])) }}"
                   class="bg-white border {{ $mediaType === $ck || ($ck === 'all' && $mediaType === 'all') ? 'border-[#14652F]' : 'border-[#EFEBE2]' }} rounded-2xl p-4 flex items-start gap-3 hover:border-[#14652F] transition-colors">
                    <span class="shrink-0 w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background:{{ $cbg }}">
                        <i data-lucide="{{ $cicon }}" class="w-[21px] h-[21px]" style="color:{{ $ctext }}"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[10px] font-bold tracking-[0.06em] text-[#8A857A] uppercase truncate">{{ $clabel }}</span>
                        <span class="flex items-baseline gap-1.5">
                            <span class="text-[19px] font-bold text-[#1B1B18]">{{ number_format($mediaStats[$ck]['count']) }}</span>
                            @if($trend !== null)
                            <span class="inline-flex items-center gap-0.5 text-[11px] font-semibold {{ $trend >= 0 ? 'text-[#157A43]' : 'text-[#DC2626]' }}">
                                <i data-lucide="{{ $trend >= 0 ? 'arrow-up' : 'arrow-down' }}" class="w-3 h-3"></i>{{ number_format(abs($trend), 1) }}%
                            </span>
                            @else
                            <span class="text-[11px] font-semibold text-[#157A43]">+{{ number_format($mediaStats[$ck]['this_month']) }}</span>
                            @endif
                        </span>
                        <span class="block text-[10.5px] text-[#8A857A]">{{ $isFr ? 'vs mois dernier' : 'vs last month' }}</span>
                    </span>
                </a>
                @endforeach
                {{-- Espace utilisé — real sizes summed for the CURRENT PAGE of files --}}
                <div class="bg-white border border-[#EFEBE2] rounded-2xl p-4 flex items-start gap-3">
                    <span class="shrink-0 w-[42px] h-[42px] rounded-xl bg-[#E8F3EC] flex items-center justify-center">
                        <i data-lucide="chart-pie" class="w-[21px] h-[21px] text-[#14652F]"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[10px] font-bold tracking-[0.06em] text-[#8A857A] uppercase truncate">{{ $isFr ? 'Espace utilisé' : 'Space used' }}</span>
                        <span class="block text-[15px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $storage['page_label'] }} / {{ $storage['quota_label'] }}</span>
                        <span class="block text-[10.5px] text-[#8A857A]">{{ number_format($storage['pct'], 1) }}% {{ $isFr ? 'utilisé (page actuelle)' : 'used (current page)' }}</span>
                    </span>
                </div>
            </section>

            <div class="mt-4 flex flex-col xl:flex-row items-start gap-4">
                {{-- ═══════════ Left column ═══════════ --}}
                <div class="flex-1 min-w-0 w-full">
                    {{-- Filter bar --}}
                    <form method="GET" action="{{ route('admin.media') }}" class="bg-white border border-[#EFEBE2] rounded-2xl p-3 flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex items-center gap-2 bg-[#F8F4EC] border border-[#EFEBE2] rounded-lg px-3 h-[36px] flex-1 min-w-[180px]">
                            <i data-lucide="search" class="w-[15px] h-[15px] text-[#8A857A] shrink-0"></i>
                            <input type="text" name="q" value="{{ $mediaQ }}" placeholder="{{ $isFr ? 'Rechercher un fichier...' : 'Search a file...' }}" class="flex-1 min-w-0 bg-transparent text-[12px] focus:outline-none placeholder-[#8A857A]">
                        </div>
                        <select name="type" onchange="this.form.submit()" class="h-[36px] bg-white border border-[#E2DDD0] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="all" {{ $mediaType === 'all' ? 'selected' : '' }}>{{ $isFr ? 'Type de média' : 'Media type' }}</option>
                            @foreach($mediaKinds as $k => $meta)
                            <option value="{{ $k }}" {{ $mediaType === $k ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <select name="folder" onchange="this.form.submit()" class="h-[36px] bg-white border border-[#E2DDD0] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Dossier' : 'Folder' }}</option>
                            @foreach($mediaFolders as $f)
                            <option value="{{ $f->folder }}" {{ $mediaFolder === $f->folder ? 'selected' : '' }}>{{ $folderNames[$f->folder] ?? ucfirst($f->folder) }}</option>
                            @endforeach
                        </select>
                        <select class="h-[36px] bg-white border border-[#E2DDD0] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none" disabled>
                            <option>{{ $isFr ? 'Téléversé par' : 'Uploaded by' }}</option>
                        </select>
                        <select class="h-[36px] bg-white border border-[#E2DDD0] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none" disabled>
                            <option>Date</option>
                        </select>
                        <button type="submit" class="inline-flex items-center gap-1.5 h-[36px] bg-[#E8F3EC] hover:bg-[#DCEEE3] border border-[#CBE3D4] rounded-lg px-3.5 text-[12px] font-semibold text-[#14652F] transition-colors">
                            <i data-lucide="funnel" class="w-[14px] h-[14px]"></i>
                            {{ $isFr ? 'Filtres' : 'Filters' }}
                        </button>
                        <span class="ml-auto flex items-center gap-1.5">
                            <span class="w-[34px] h-[34px] rounded-lg border border-[#E2DDD0] flex items-center justify-center text-[#8A857A]"><i data-lucide="layout-grid" class="w-[15px] h-[15px]"></i></span>
                            <span class="w-[34px] h-[34px] rounded-lg bg-[#0F4824] flex items-center justify-center text-white"><i data-lucide="list" class="w-[15px] h-[15px]"></i></span>
                        </span>
                    </form>

                    {{-- Files grid --}}
                    <p class="mt-4 mb-2 text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Fichiers récents' : 'Recent files' }}</p>
                    @if($mediaItems->count())
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                        @foreach($mediaItems as $m)
                        <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
                            <div class="relative h-[110px] bg-[#F3EFE5]">
                                @if($m->kind === 'image' && $m->path)
                                <img src="{{ asset('storage/' . $m->path) }}" alt="{{ $m->name }}" class="w-full h-full object-cover" loading="lazy">
                                @elseif($m->kind === 'video')
                                <div class="w-full h-full bg-[#20301F] flex items-center justify-center">
                                    <span class="w-11 h-11 rounded-full bg-white/25 flex items-center justify-center"><i data-lucide="play" class="w-5 h-5 text-white"></i></span>
                                </div>
                                @elseif($m->kind === 'audio')
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="w-14 h-14 rounded-full bg-[#E7E2D5] flex items-center justify-center"><i data-lucide="audio-lines" class="w-6 h-6 text-[#3B382F]"></i></span>
                                </div>
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="w-12 h-12 rounded-xl bg-[#14652F] flex items-center justify-center"><i data-lucide="file-text" class="w-6 h-6 text-white"></i></span>
                                </div>
                                @endif
                                <span class="absolute bottom-1.5 left-1.5 rounded-md bg-black/60 px-2 py-0.5 text-[9.5px] font-semibold text-white">{{ $m->badge }}</span>
                            </div>
                            <div class="p-2.5">
                                <p class="text-[11.5px] font-bold text-[#1B1B18] truncate" title="{{ $m->name }}">{{ $m->name }}</p>
                                <p class="mt-0.5 text-[10.5px] text-[#8A857A]">{{ $m->size_label }} · {{ $m->ext }}</p>
                                <p class="mt-0.5 text-[10px] text-[#B0AA9B] truncate" title="{{ $m->owner }}">{{ $m->owner }}</p>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-[10px] text-[#8A857A]">{{ $m->created_at ? ucfirst($m->created_at->locale($lang)->isoFormat('DD MMM YYYY')) : '—' }}</span>
                                    <i data-lucide="ellipsis-vertical" class="w-[14px] h-[14px] text-[#B0AA9B]"></i>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-white border border-[#EFEBE2] rounded-2xl px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun fichier ne correspond à ces filtres.' : 'No files match these filters.' }}</div>
                    @endif

                    {{-- Pagination --}}
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[11.5px] text-[#6F6B60]">
                            {{ $isFr
                                ? 'Affichage de ' . number_format($mediaItems->count() ? $mediaItems->firstItem() : 0) . ' à ' . number_format($mediaItems->count() ? $mediaItems->lastItem() : 0) . ' sur ' . number_format($mediaItems->total()) . ' fichiers'
                                : 'Showing ' . number_format($mediaItems->count() ? $mediaItems->firstItem() : 0) . ' to ' . number_format($mediaItems->count() ? $mediaItems->lastItem() : 0) . ' of ' . number_format($mediaItems->total()) . ' files' }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ $mediaItems->onFirstPage() ? '#' : $mediaItems->previousPageUrl() }}" class="w-[30px] h-[30px] rounded-lg border border-[#E2DDD0] bg-white flex items-center justify-center {{ $mediaItems->onFirstPage() ? 'text-[#CFC9BA] pointer-events-none' : 'text-[#3B382F] hover:border-[#14652F]' }}"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></a>
                            @for($p = 1; $p <= $mediaItems->lastPage(); $p++)
                                @if($mediaItems->lastPage() > 9 && $p > 5 && $p < $mediaItems->lastPage())
                                    @if($p === 6)<span class="px-1 text-[11.5px] text-[#8A857A]">…</span>@endif
                                    @continue
                                @endif
                                <a href="{{ $mediaItems->url($p) }}" class="min-w-[30px] h-[30px] px-1.5 rounded-lg flex items-center justify-center text-[11.5px] font-semibold {{ $p === $mediaItems->currentPage() ? 'bg-[#0F4824] text-white' : 'border border-[#E2DDD0] bg-white text-[#3B382F] hover:border-[#14652F]' }}">{{ number_format($p) }}</a>
                            @endfor
                            <a href="{{ $mediaItems->hasMorePages() ? $mediaItems->nextPageUrl() : '#' }}" class="w-[30px] h-[30px] rounded-lg border border-[#E2DDD0] bg-white flex items-center justify-center {{ $mediaItems->hasMorePages() ? 'text-[#3B382F] hover:border-[#14652F]' : 'text-[#CFC9BA] pointer-events-none' }}"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></a>
                        </div>
                        <form method="GET" action="{{ route('admin.media') }}">
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            @if($mediaType !== 'all')<input type="hidden" name="type" value="{{ $mediaType }}">@endif
                            @if($mediaFolder !== '')<input type="hidden" name="folder" value="{{ $mediaFolder }}">@endif
                            @if($mediaQ !== '')<input type="hidden" name="q" value="{{ $mediaQ }}">@endif
                            <select name="per_page" onchange="this.form.submit()" class="h-[32px] bg-white border border-[#E2DDD0] rounded-lg px-2 text-[11.5px] text-[#3B382F] focus:outline-none">
                                @foreach([10, 20, 50] as $pp)
                                <option value="{{ $pp }}" {{ $perPage === $pp ? 'selected' : '' }}>{{ $pp }} / page</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Bottom panels --}}
                    <div class="mt-5 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        {{-- Activités récentes — latest REAL uploads --}}
                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                            <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h2>
                            <div class="mt-3 divide-y divide-[#F5F1E8]">
                                @forelse($mediaRecent as $r)
                                <div class="py-2.5 flex items-center gap-3">
                                    <span class="shrink-0 w-[30px] h-[30px] rounded-lg bg-[#E8F3EC] flex items-center justify-center">
                                        <i data-lucide="{{ $mediaKinds[$r->kind]['icon'] ?? 'file' }}" class="w-[14px] h-[14px] text-[#14652F]"></i>
                                    </span>
                                    <p class="flex-1 min-w-0 text-[11.5px] text-[#3B382F] truncate">
                                        <span class="font-bold text-[#1B1B18]">{{ $r->name }}</span>
                                        {{ $isFr ? 'a été téléversé pour' : 'was uploaded for' }} {{ $r->owner }}
                                    </p>
                                    <span class="shrink-0 text-[10.5px] text-[#8A857A]">{{ $r->created_at ? $r->created_at->locale($lang)->diffForHumans(null, true, true) : '—' }}</span>
                                </div>
                                @empty
                                <p class="py-3 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucune activité.' : 'No activity.' }}</p>
                                @endforelse
                            </div>
                        </section>

                        {{-- Téléversements par mois — real 6-month counts --}}
                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                            <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Téléversements par mois' : 'Uploads per month' }}</h2>
                            <div class="mt-3 flex gap-2">
                                <div class="flex flex-col justify-between items-end text-[9.5px] text-[#8A857A] h-[130px] pb-[18px]">
                                    @foreach([1, 0.75, 0.5, 0.25, 0] as $tick)
                                    <span>{{ number_format((int) round($chartTop * $tick)) }}</span>
                                    @endforeach
                                </div>
                                <div class="flex-1 grid grid-cols-6 gap-2 items-end h-[130px]">
                                    @foreach($mediaMonths as $mm)
                                    <div class="flex flex-col items-center justify-end h-full gap-1">
                                        <div class="w-full max-w-[26px] rounded-t bg-gradient-to-b from-[#1C7A3C] to-[#0F4824]" style="height:{{ max(2, (int) round($mm->count / $chartTop * 100)) }}%" title="{{ number_format($mm->count) }}"></div>
                                        <span class="text-[9.5px] text-[#8A857A]">{{ ucfirst($mm->label) }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        {{-- Types de fichiers les plus téléversés --}}
                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4 md:col-span-2 xl:col-span-1">
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Types de fichiers les plus téléversés' : 'Most uploaded file types' }}</h2>
                                <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#8A6D1F] hover:text-[#14652F]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                            </div>
                            <div class="mt-3 space-y-3">
                                @foreach($mediaKinds as $k => $meta)
                                <div class="flex items-center gap-3">
                                    <span class="shrink-0 w-[28px] h-[28px] rounded-lg flex items-center justify-center" style="background:{{ $meta['bg'] }}">
                                        <i data-lucide="{{ $meta['icon'] }}" class="w-[13px] h-[13px]" style="color:{{ $meta['text'] }}"></i>
                                    </span>
                                    <span class="w-[80px] text-[11.5px] text-[#3B382F]">{{ $meta['label'] }}</span>
                                    <span class="flex-1 h-[3px] rounded-full bg-[#EFEBE2] overflow-hidden"><span class="block h-full rounded-full bg-[#14652F]" style="width:{{ $pctOf($mediaStats[$k]['count']) }}%"></span></span>
                                    <span class="shrink-0 w-[52px] text-right text-[11.5px] font-semibold text-[#1B1B18]">{{ number_format($mediaStats[$k]['count']) }}</span>
                                </div>
                                @endforeach
                                <div class="flex items-center gap-3">
                                    <span class="shrink-0 w-[28px] h-[28px] rounded-lg bg-[#F1EFE8] flex items-center justify-center"><i data-lucide="file" class="w-[13px] h-[13px] text-[#8A857A]"></i></span>
                                    <span class="w-[80px] text-[11.5px] text-[#3B382F]">{{ $isFr ? 'Autres' : 'Others' }}</span>
                                    <span class="flex-1 h-[3px] rounded-full bg-[#EFEBE2] overflow-hidden"><span class="block h-full rounded-full bg-[#8A857A]" style="width:{{ $pctOf($othersCount) }}%"></span></span>
                                    <span class="shrink-0 w-[52px] text-right text-[11.5px] font-semibold text-[#1B1B18]">{{ number_format($othersCount) }}</span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                {{-- ═══════════ Right column ═══════════ --}}
                <aside class="w-full xl:w-[320px] shrink-0 space-y-4">
                    {{-- Répartition par type — real donut --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Répartition par type' : 'Breakdown by type' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#8A6D1F] hover:text-[#14652F]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="relative shrink-0 w-[120px] h-[120px] rounded-full" style="background:{{ $donutCss }}">
                                <div class="absolute inset-[16px] rounded-full bg-white flex flex-col items-center justify-center leading-tight">
                                    <span class="text-[17px] font-bold text-[#1B1B18]">{{ number_format($mediaTotal) }}</span>
                                    <span class="text-[9px] text-[#14652F] font-semibold">{{ $isFr ? 'Total fichiers' : 'Total files' }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 space-y-2">
                                @foreach($mediaKinds as $k => $meta)
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="shrink-0 w-[9px] h-[9px] rounded-[3px]" style="background:{{ $meta['dot'] }}"></span>
                                    <span class="flex-1 text-[#3B382F] truncate">{{ $meta['label'] }}</span>
                                    <span class="shrink-0 text-[#6F6B60]">{{ number_format($pctOf($mediaStats[$k]['count']), 1) }}% ({{ number_format($mediaStats[$k]['count']) }})</span>
                                </div>
                                @endforeach
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="shrink-0 w-[9px] h-[9px] rounded-[3px] bg-[#D7D3C8]"></span>
                                    <span class="flex-1 text-[#3B382F] truncate">{{ $isFr ? 'Autres' : 'Others' }}</span>
                                    <span class="shrink-0 text-[#6F6B60]">{{ number_format($pctOf($othersCount), 1) }}% ({{ number_format($othersCount) }})</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Espace de stockage — real sizes for the current page --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Espace de stockage' : 'Storage space' }}</h2>
                            <a href="{{ route('admin.audit-log') }}" class="shrink-0 text-[11px] font-semibold text-[#8A6D1F] hover:text-[#14652F]">{{ $isFr ? 'Voir détails' : 'View details' }} →</a>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <p class="text-[12px] text-[#1B1B18]"><span class="font-bold">{{ $storage['page_label'] }}</span> {{ $isFr ? 'utilisés sur' : 'used of' }} {{ $storage['quota_label'] }} <span class="text-[#8A857A]">({{ $isFr ? 'page actuelle' : 'current page' }})</span></p>
                            <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ number_format($storage['pct'], 1) }}%</p>
                        </div>
                        <div class="mt-2 h-[8px] rounded-full bg-[#EFEBE2] overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-[#1C7A3C] to-[#0F4824]" style="width:{{ max($storage['pct'], $storage['page_bytes'] > 0 ? 1 : 0) }}%"></div>
                        </div>
                        <p class="mt-2 text-[11.5px]"><span class="font-bold text-[#8A6D1F]">{{ $storage['available_label'] }}</span> <span class="text-[#8A6D1F]">{{ $isFr ? 'disponibles' : 'available' }}</span></p>
                    </section>

                    {{-- Dossiers principaux — real storage folders --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">{{ $isFr ? 'Dossiers principaux' : 'Main folders' }}</h2>
                            <a href="{{ route('admin.media', ['lang' => $lang]) }}" class="shrink-0 text-[11px] font-semibold text-[#8A6D1F] hover:text-[#14652F]">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                        </div>
                        <div class="mt-2 divide-y divide-[#F5F1E8]">
                            @forelse($mediaFolders as $f)
                            <a href="{{ route('admin.media', ['lang' => $lang, 'folder' => $f->folder]) }}" class="py-2.5 flex items-center gap-3 group">
                                <span class="shrink-0 w-[32px] h-[32px] rounded-lg bg-[#FBF3DC] flex items-center justify-center"><i data-lucide="folder" class="w-[15px] h-[15px] text-[#C9902E]"></i></span>
                                <span class="flex-1 min-w-0 text-[12px] font-semibold text-[#1B1B18] truncate group-hover:text-[#14652F]">{{ $folderNames[$f->folder] ?? ucfirst($f->folder) }}</span>
                                <span class="shrink-0 text-[11px] text-[#8A857A]">{{ number_format($f->count) }} {{ $isFr ? 'fichiers' : 'files' }}</span>
                            </a>
                            @empty
                            <p class="py-3 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucun dossier.' : 'No folders.' }}</p>
                            @endforelse
                        </div>
                    </section>

                    {{-- Conseils --}}
                    <section class="bg-[#FBF6E8] border border-[#F0E6C8] rounded-2xl p-4">
                        <h2 class="text-[11px] font-bold tracking-[0.08em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Conseils' : 'Tips' }}</h2>
                        <div class="mt-2 flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-[11.5px] text-[#55524A] leading-relaxed">{{ $isFr ? 'Organisez vos fichiers par dossiers pour un meilleur accès et une meilleure gestion.' : 'Organise your files into folders for better access and easier management.' }}</p>
                                <button type="button" class="mt-3 inline-flex items-center bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-3.5 h-[32px] text-[11.5px] font-semibold text-white transition-colors">{{ $isFr ? 'En savoir plus' : 'Learn more' }}</button>
                            </div>
                            <span class="shrink-0 w-[52px] h-[52px] rounded-xl bg-[#F3E7C6] flex items-center justify-center"><i data-lucide="folder-open" class="w-6 h-6 text-[#C9902E]"></i></span>
                        </div>
                    </section>
                </aside>
            </div>

            {{-- Footer strip --}}
            <div class="mt-8 pt-4 border-t border-[#EBE6DA] flex flex-wrap items-center justify-between gap-3">
                <span class="hidden md:block w-[120px]"></span>
                <p class="text-[11.5px] text-[#6F6B60] text-center flex-1">© 2025 {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
                <span class="flex items-center gap-4 shrink-0">
                    <a href="{{ route('admin.support') }}" class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="circle-help" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Centre d\'assistance' : 'Help centre' }}</a>
                    <a href="{{ route('messages.inbox') }}" class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="mail" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
                </span>
            </div>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
