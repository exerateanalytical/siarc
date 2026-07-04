@php
    $isFr = $lang === 'fr';
    $adminActive = 'news';

    // Category pill colours (table) — from "gestion d'actualites et annonces.png"
    $newsCatPill = [
        'Événements' => 'bg-[#FDF0DC] text-[#C97A16]',
        'Artisanat'  => 'bg-[#E3EDFB] text-[#2563EB]',
        'Annonces'   => 'bg-[#E7E9FC] text-[#4F46E5]',
        'Culture'    => 'bg-[#F1E8FB] text-[#8B3FC9]',
        'Programmes' => 'bg-[#FDE5E7] text-[#DC2646]',
        'Portraits'  => 'bg-[#F3E8FD] text-[#7C3AED]',
    ];
    // Donut / legend colours (right panel)
    $newsCatDot = [
        'Événements' => '#14652F',
        'Artisanat'  => '#0E9F6E',
        'Culture'    => '#2563EB',
        'Annonces'   => '#DC2626',
        'Programmes' => '#E9B23C',
        'Portraits'  => '#7C3AED',
    ];
    $newsTypeIcon = ['Actualité' => 'newspaper', 'Annonce' => 'megaphone', 'Article' => 'file-text'];
    $newsTypeEn   = ['Actualité' => 'News item', 'Annonce' => 'Announcement', 'Article' => 'Article'];

    $frMonths = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $frMonthsFull = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    $newsDate = function ($value, $withTime = false) use ($isFr, $frMonthsFull) {
        if (!$value) return '—';
        $d = \Illuminate\Support\Carbon::parse($value);
        $base = $isFr ? $d->format('d') . ' ' . $frMonthsFull[(int) $d->format('n')] . ' ' . $d->format('Y') : $d->format('d M Y');
        return $withTime && ($d->format('H:i') !== '00:00') && $d->isFuture() ? $base . ' ' . $d->format('H:i') : $base;
    };

    $statusPill = [
        'published' => ['bg-[#E2F3E8] text-[#157A43]', $isFr ? 'Publié' : 'Published'],
        'scheduled' => ['bg-[#E3EDFB] text-[#2563EB]', $isFr ? 'Planifiée' : 'Scheduled'],
        'draft'     => ['bg-[#FDF0DC] text-[#C97A16]', $isFr ? 'Brouillon' : 'Draft'],
    ];

    $curStatut = request('statut');
    $curCat    = request('categorie');
    $curQ      = request('q');

    // Donut segments (conic-gradient) from real per-category counts
    $catTotal = max(1, $newsByCategory->sum('total'));
    $donutStops = [];
    $acc = 0;
    foreach ($newsByCategory as $c) {
        $color = $newsCatDot[$c->category] ?? '#9CA3AF';
        $from = $acc / $catTotal * 360;
        $acc += $c->total;
        $to = $acc / $catTotal * 360;
        $donutStops[] = sprintf('%s %.2fdeg %.2fdeg', $color, $from, $to);
    }
    $donutCss = 'conic-gradient(' . implode(', ', $donutStops) . ')';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Actualités & Annonces — Administration' : 'News & Announcements — Administration' }}</title>
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
            {{-- Title + breadcrumb + primary actions --}}
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Gestion des Actualités & Annonces' : 'News & Announcements Management' }}</h1>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">
                        <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                        <span class="mx-1 text-[#B8B2A4]">›</span>
                        <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Actualités & Annonces' : 'News & Announcements' }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Créer une actualité' : 'Create a news item' }}
                    </a>
                    <a href="{{ route('admin.industries') }}" class="inline-flex items-center gap-2 bg-white border border-[#CFE3D5] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#14652F] transition-colors">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        {{ $isFr ? 'Catégories' : 'Categories' }}
                    </a>
                    <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-2 bg-white border border-[#CFE3D5] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#14652F] transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Exporter' : 'Export' }}
                    </a>
                </div>
            </div>

            {{-- Stat chips --}}
            @php
                $chips = [
                    ['file-text',   '#157A43', '#E2F3E8', $isFr ? 'Total publications' : 'Total publications', number_format($newsStats['total']),     '+18.2%'],
                    ['send',        '#2563EB', '#E3EDFB', $isFr ? 'Publiées' : 'Published',                    number_format($newsStats['published']), '+16.4%'],
                    ['pencil',      '#C97A16', '#FDF0DC', $isFr ? 'Brouillons' : 'Drafts',                     number_format($newsStats['draft']),     '+9.1%'],
                    ['calendar',    '#7C3AED', '#F3E8FD', $isFr ? 'Planifiées' : 'Scheduled',                  number_format($newsStats['scheduled']), '+20.0%'],
                    ['eye',         '#157A43', '#E2F3E8', $isFr ? 'Vues totales' : 'Total views',              number_format($newsStats['views']),     '+22.7%'],
                    ['chart-line',  '#DC2646', '#FDE5E7', $isFr ? 'Taux d\'engagement' : 'Engagement rate',    '8.4%',                                 '+1.3%'],
                ];
            @endphp
            <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                @foreach($chips as [$cIcon, $cColor, $cBg, $cLabel, $cValue, $cTrend])
                <div class="bg-white border border-[#EFEBE2] rounded-2xl px-4 py-3.5 flex items-start gap-3">
                    <span class="shrink-0 w-[38px] h-[38px] rounded-xl flex items-center justify-center" style="background: {{ $cBg }}">
                        <i data-lucide="{{ $cIcon }}" class="w-[18px] h-[18px]" style="color: {{ $cColor }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold tracking-[0.06em] text-[#8A857A] uppercase truncate">{{ $cLabel }}</p>
                        <p class="mt-0.5 text-[18px] font-bold text-[#1B1B18] leading-none">{{ $cValue }}
                            <span class="text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="arrow-up-right" class="inline w-3 h-3 -mt-0.5"></i> {{ $cTrend }}</span>
                        </p>
                        <p class="mt-1 text-[10px] text-[#8A857A]">{{ $isFr ? 'vs mois dernier' : 'vs last month' }}</p>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="mt-4 grid grid-cols-1 2xl:grid-cols-[1fr_340px] gap-4 items-start">
                {{-- LEFT: filters + table + pagination --}}
                <section class="bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden min-w-0">
                    <form method="GET" class="flex flex-wrap items-center gap-2.5 px-4 pt-4 pb-3">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex items-center gap-2.5 bg-[#FBF9F4] border border-[#E9E4D8] rounded-lg px-3.5 h-[38px] w-full sm:w-[230px]">
                            <i data-lucide="search" class="w-[15px] h-[15px] shrink-0 text-[#8A857A]"></i>
                            <input type="text" name="q" value="{{ $curQ }}" placeholder="{{ $isFr ? 'Rechercher une actualité...' : 'Search a news item...' }}" class="flex-1 min-w-0 bg-transparent text-[12px] focus:outline-none placeholder-[#8A857A]">
                        </div>
                        <select name="statut" onchange="this.form.submit()" class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                            <option value="published" @selected($curStatut === 'published')>{{ $isFr ? 'Publié' : 'Published' }}</option>
                            <option value="scheduled" @selected($curStatut === 'scheduled')>{{ $isFr ? 'Planifiée' : 'Scheduled' }}</option>
                            <option value="draft" @selected($curStatut === 'draft')>{{ $isFr ? 'Brouillon' : 'Draft' }}</option>
                        </select>
                        <select name="categorie" onchange="this.form.submit()" class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                            @foreach($newsCategories as $cat)
                            <option value="{{ $cat }}" @selected($curCat === $cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        <select class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12px] text-[#3B382F] focus:outline-none" disabled>
                            <option>Type</option>
                        </select>
                        <select class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12px] text-[#3B382F] focus:outline-none" disabled>
                            <option>Date</option>
                        </select>
                        <select class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12px] text-[#3B382F] focus:outline-none" disabled>
                            <option>{{ $isFr ? 'Auteur' : 'Author' }}</option>
                        </select>
                        <button type="submit" class="inline-flex items-center gap-2 h-[38px] bg-[#FBF9F4] border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-3.5 text-[12px] font-semibold text-[#3B382F] transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-[15px] h-[15px]"></i>
                            {{ $isFr ? 'Filtres' : 'Filters' }}
                        </button>
                        <span class="ml-auto hidden lg:flex items-center gap-1.5">
                            <span class="w-[34px] h-[34px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#8A857A]"><i data-lucide="layout-grid" class="w-4 h-4"></i></span>
                            <span class="w-[34px] h-[34px] rounded-lg bg-[#0F4824] flex items-center justify-center text-white"><i data-lucide="list" class="w-4 h-4"></i></span>
                        </span>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px]">
                            <thead>
                                <tr class="bg-[#F8F4EC] text-left">
                                    <th class="pl-4 pr-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Actualité' : 'News item' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Type</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Auteur' : 'Author' }}</th>
                                    <th class="px-2 py-3 text-center text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th class="px-2 py-3 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Vues' : 'Views' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase whitespace-nowrap">{{ $isFr ? 'Date de publication' : 'Publication date' }}</th>
                                    <th class="px-2 pr-4 py-3 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F5F1E8]">
                                @forelse($announcements as $a)
                                @php [$stCls, $stLabel] = $statusPill[$a->status] ?? ['bg-[#EFEFED] text-[#55524A]', $a->status]; @endphp
                                <tr>
                                    <td class="pl-4 pr-2 py-3">
                                        <div class="flex items-center gap-3 min-w-[240px] max-w-[300px]">
                                            @if($a->cover_image)
                                            <img src="{{ asset($a->cover_image) }}" alt="" class="shrink-0 w-[52px] h-[44px] rounded-lg object-cover">
                                            @else
                                            <span class="shrink-0 w-[52px] h-[44px] rounded-lg bg-[#F1ECE0] flex items-center justify-center text-[#B8A66B]"><i data-lucide="image" class="w-5 h-5"></i></span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-[12px] font-bold text-[#1B1B18] leading-snug">{{ $isFr ? $a->title_fr : ($a->title_en ?? $a->title_fr) }}</p>
                                                <p class="mt-0.5 text-[10.5px] text-[#8A857A] truncate">{{ $isFr ? $a->excerpt_fr : ($a->excerpt_en ?? $a->excerpt_fr) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3">
                                        <span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $newsCatPill[$a->category] ?? 'bg-[#EFEFED] text-[#55524A]' }}">{{ $a->category ?? '—' }}</span>
                                    </td>
                                    <td class="px-2 py-3">
                                        <span class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F] whitespace-nowrap">
                                            <i data-lucide="{{ $newsTypeIcon[$a->type] ?? 'file' }}" class="w-[14px] h-[14px] text-[#8A857A]"></i>
                                            {{ $isFr ? ($a->type ?? '—') : ($newsTypeEn[$a->type] ?? ($a->type ?? '—')) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-3 text-[11.5px] text-[#3B382F] whitespace-nowrap">{{ $a->author_name ?? '—' }}</td>
                                    <td class="px-2 py-3 text-center">
                                        <span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold {{ $stCls }}">{{ $stLabel }}</span>
                                    </td>
                                    <td class="px-2 py-3 text-right text-[11.5px] text-[#3B382F]">{{ $a->status === 'published' ? number_format($a->views_count) : '-' }}</td>
                                    <td class="px-2 py-3 text-[11.5px] text-[#3B382F] whitespace-nowrap">{{ $a->status === 'draft' ? '-' : $newsDate($a->published_at, $a->status === 'scheduled') }}</td>
                                    <td class="px-2 pr-4 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.news.detail', ['id' => $a->id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="inline-flex w-[28px] h-[28px] rounded-md border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#157A43] hover:border-[#157A43] transition-colors align-middle"><i data-lucide="eye" class="w-[14px] h-[14px]"></i></a>
                                        <a href="{{ route('admin.news.detail', ['id' => $a->id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-md border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#C97A16] hover:border-[#C97A16] transition-colors align-middle"><i data-lucide="pencil" class="w-[14px] h-[14px]"></i></a>
                                        <a href="{{ route('admin.news.detail', ['id' => $a->id, 'lang' => $lang]) }}" title="Plus" class="ml-1 inline-flex w-[28px] h-[28px] rounded-md border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#1B1B18] transition-colors align-middle"><i data-lucide="more-vertical" class="w-[14px] h-[14px]"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune publication trouvée.' : 'No publications found.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 border-t border-[#F5F1E8]">
                        <p class="text-[11.5px] text-[#6F6B60]">
                            {{ $isFr
                                ? 'Affichage de ' . ($announcements->firstItem() ?? 0) . ' à ' . ($announcements->lastItem() ?? 0) . ' sur ' . $announcements->total() . ' publications'
                                : 'Showing ' . ($announcements->firstItem() ?? 0) . ' to ' . ($announcements->lastItem() ?? 0) . ' of ' . $announcements->total() . ' publications' }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if($announcements->onFirstPage())
                            <span class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C4B8]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                            @else
                            <a href="{{ $announcements->previousPageUrl() }}" class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#55524A] hover:border-[#14652F]"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                            @endif
                            @for($p = 1; $p <= min($announcements->lastPage(), 5); $p++)
                                @if($p === $announcements->currentPage())
                                <span class="w-[30px] h-[30px] rounded-lg bg-[#0F4824] text-white text-[12px] font-bold flex items-center justify-center">{{ $p }}</span>
                                @else
                                <a href="{{ $announcements->url($p) }}" class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] text-[12px] text-[#55524A] flex items-center justify-center hover:border-[#14652F]">{{ $p }}</a>
                                @endif
                            @endfor
                            @if($announcements->lastPage() > 5)
                            <span class="px-1 text-[12px] text-[#8A857A]">…</span>
                            <a href="{{ $announcements->url($announcements->lastPage()) }}" class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] text-[12px] text-[#55524A] flex items-center justify-center hover:border-[#14652F]">{{ $announcements->lastPage() }}</a>
                            @endif
                            @if($announcements->hasMorePages())
                            <a href="{{ $announcements->nextPageUrl() }}" class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#55524A] hover:border-[#14652F]"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                            @else
                            <span class="w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C4B8]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                            @endif
                        </div>
                        <span class="hidden md:inline-flex items-center gap-1.5 h-[30px] rounded-lg border border-[#E9E4D8] px-2.5 text-[11.5px] text-[#55524A]">10 / page <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></span>
                    </div>
                </section>

                {{-- RIGHT: side panels --}}
                <aside class="space-y-4 min-w-0">
                    {{-- Répartition par catégorie --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Répartition par catégorie' : 'Breakdown by category' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="text-[11px] font-semibold text-[#157A43] hover:text-[#14532D] whitespace-nowrap">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="relative shrink-0 w-[118px] h-[118px] rounded-full" style="background: {{ $donutCss }}">
                                <div class="absolute inset-[17px] bg-white rounded-full flex flex-col items-center justify-center">
                                    <span class="text-[19px] font-bold text-[#1B1B18] leading-none">{{ number_format($newsStats['total']) }}</span>
                                    <span class="mt-0.5 text-[10px] text-[#8A857A]">Total</span>
                                </div>
                            </div>
                            <ul class="flex-1 min-w-0 space-y-1.5">
                                @foreach($newsByCategory as $c)
                                <li class="flex items-center gap-2 text-[11px]">
                                    <span class="shrink-0 w-[9px] h-[9px] rounded-[3px]" style="background: {{ $newsCatDot[$c->category] ?? '#9CA3AF' }}"></span>
                                    <span class="flex-1 truncate text-[#3B382F]">{{ $c->category }}</span>
                                    <span class="shrink-0 text-[#6F6B60]">{{ $c->total }} ({{ number_format($c->total / $catTotal * 100, 1) }}%)</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>

                    {{-- Top articles par vues --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Top articles par vues' : 'Top articles by views' }}</h2>
                            <a href="{{ route('news.index') }}" class="text-[11px] font-semibold text-[#157A43] hover:text-[#14532D] whitespace-nowrap">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                        </div>
                        <ul class="mt-3 divide-y divide-[#F5F1E8]">
                            @forelse($topAnnouncements as $t)
                            <li class="py-2 flex items-center gap-3">
                                @if($t->cover_image)
                                <img src="{{ asset($t->cover_image) }}" alt="" class="shrink-0 w-[34px] h-[30px] rounded-md object-cover">
                                @else
                                <span class="shrink-0 w-[34px] h-[30px] rounded-md bg-[#F1ECE0] flex items-center justify-center text-[#B8A66B]"><i data-lucide="image" class="w-4 h-4"></i></span>
                                @endif
                                <p class="flex-1 min-w-0 text-[11.5px] font-medium text-[#1B1B18] leading-snug truncate">{{ $isFr ? $t->title_fr : ($t->title_en ?? $t->title_fr) }}</p>
                                <span class="shrink-0 text-[11.5px] font-bold text-[#1B1B18]">{{ number_format($t->views_count) }}</span>
                            </li>
                            @empty
                            <li class="py-3 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucun article publié.' : 'No published article.' }}</li>
                            @endforelse
                        </ul>
                    </section>

                    {{-- Activités récentes --}}
                    @php
                        $recentActivity = [
                            ['check-circle-2', '#157A43', '#E2F3E8', $isFr ? 'Nouvelle actualité publiée' : 'New news item published', 'Lancement du Festival International de l\'Artisanat 2025', $isFr ? 'Il y a 15 min' : '15 min ago'],
                            ['calendar-clock', '#2563EB', '#E3EDFB', $isFr ? 'Actualité planifiée' : 'News item scheduled', 'Formation des jeunes artisans de l\'Adamaoua', $isFr ? 'Il y a 1 h' : '1 h ago'],
                            ['megaphone', '#C97A16', '#FDF0DC', $isFr ? 'Annonce mise à jour' : 'Announcement updated', 'Salon des Métiers d\'Art et du Design', $isFr ? 'Il y a 3 h' : '3 h ago'],
                            ['pencil-line', '#7C3AED', '#F3E8FD', $isFr ? 'Brouillon enregistré' : 'Draft saved', 'Portrait : Femme artisan de la semaine', $isFr ? 'Il y a 5 h' : '5 h ago'],
                            ['tag', '#0E9F6E', '#E2F3E8', $isFr ? 'Catégorie créée' : 'Category created', 'Portraits', $isFr ? 'Il y a 1 jour' : '1 day ago'],
                        ];
                    @endphp
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h2>
                            <a href="{{ route('admin.audit-log') }}" class="text-[11px] font-semibold text-[#157A43] hover:text-[#14532D] whitespace-nowrap">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                        </div>
                        <ul class="mt-3 space-y-3">
                            @foreach($recentActivity as [$raIcon, $raColor, $raBg, $raTitle, $raSub, $raWhen])
                            <li class="flex items-start gap-3">
                                <span class="shrink-0 w-[28px] h-[28px] rounded-lg flex items-center justify-center" style="background: {{ $raBg }}">
                                    <i data-lucide="{{ $raIcon }}" class="w-[14px] h-[14px]" style="color: {{ $raColor }}"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11.5px] font-semibold text-[#1B1B18] leading-snug">{{ $raTitle }}</p>
                                    <p class="mt-0.5 text-[10.5px] text-[#8A857A] truncate">{{ $raSub }}</p>
                                </div>
                                <span class="shrink-0 text-[10px] text-[#8A857A] whitespace-nowrap">{{ $raWhen }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </section>
                </aside>
            </div>

            {{-- Footer strip --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-[#EAE4D6] pt-4">
                <p class="text-[11px] text-[#6F6B60]">© 2025 {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
                <div class="flex items-center gap-5">
                    <a href="{{ route('admin.support') }}" class="inline-flex items-center gap-1.5 text-[11px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="circle-help" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Centre d\'assistance' : 'Help centre' }}</a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-1.5 text-[11px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="mail" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
                </div>
            </div>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
