@php
    $isFr = $lang === 'fr';
    $adminActive = 'kyc';

    // Status → pill (label + classes) matching the design
    $kycStatusMeta = [
        'submitted'    => [$isFr ? 'En attente' : 'Pending',       'bg-[#FDF3E0] text-[#C97A16]', '#E9A83A'],
        'draft'        => [$isFr ? 'En attente' : 'Pending',       'bg-[#FDF3E0] text-[#C97A16]', '#E9A83A'],
        'under_review' => [$isFr ? 'En vérification' : 'In review', 'bg-[#E8EFFB] text-[#3565DE]', '#3565DE'],
        'approved'     => [$isFr ? 'Approuvée' : 'Approved',        'bg-[#E2F3E8] text-[#157A43]', '#157A43'],
        'rejected'     => [$isFr ? 'Rejetée' : 'Rejected',         'bg-[#FDE8E8] text-[#DC2626]', '#DC2626'],
    ];

    // Vendor type → rôle pill of the applicant
    $kycRoleMeta = [
        'artisan'     => [$isFr ? 'Artisan' : 'Artisan',       'bg-[#E2F3E8] text-[#157A43]'],
        'entreprise'  => [$isFr ? 'Boutique' : 'Shop',         'bg-[#FDF3E0] text-[#C97A16]'],
        'cooperative' => [$isFr ? 'Coopérative' : 'Cooperative','bg-[#F0EAFB] text-[#7C4FE0]'],
    ];

    $kycMonthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $kycDate = function ($value) use ($isFr, $kycMonthsFr) {
        if (! $value) return '—';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $kycMonthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    // Five stat cards [icon, iconColor, tileBg, cardBg, value, label, sub, spark]
    $kycCards = [
        ['users-round', '#157A43', '#E8F2EC', 'bg-white border-[#EFF0EF]',        $kycStats['total'],        $isFr ? 'Total Demandes' : 'Total requests',   '+' . $kycStats['this_month'] . ($isFr ? ' ce mois' : ' this month'), '#3FA96A'],
        ['hourglass',   '#C97A16', '#FDF3E0', 'bg-white border-[#EFF0EF]',        $kycStats['pending'],      $isFr ? 'En Attente' : 'Pending',              $kycStats['pct_pending'] . ($isFr ? '% du total' : '% of total'),   '#E9A83A'],
        ['shield-check','#3565DE', '#E8EFFB', 'bg-[#F4F8FF] border-[#CFE0F7]',    $kycStats['approved'],     $isFr ? 'Approuvées' : 'Approved',             $kycStats['pct_approved'] . ($isFr ? '% du total' : '% of total'),  '#3565DE'],
        ['shield-x',    '#DC2626', '#FDECEC', 'bg-white border-[#EFF0EF]',        $kycStats['rejected'],     $isFr ? 'Rejetées' : 'Rejected',              $kycStats['pct_rejected'] . ($isFr ? '% du total' : '% of total'),  '#DC2626'],
        ['clock',       '#7C4FE0', '#F0EAFB', 'bg-white border-[#EFF0EF]',        $kycStats['in_review'],    $isFr ? 'En Vérification' : 'In verification', $kycStats['pct_review'] . ($isFr ? '% du total' : '% of total'),    '#7C4FE0'],
    ];

    // Right rail — répartition par rôle (real distribution) [label, count, pct, color]
    $kycRoleTotal = max(1, array_sum(array_column($kycRoleDist, 'count')));
    $kycProcess = $isFr ? [
        ['upload',       'Soumission', "L'utilisateur soumet\nses informations\net documents"],
        ['search',       'Revue',      "Vérification manuelle\net automatique des\ninformations"],
        ['shield-check', 'Décision',   "Approbation ou refus\nbasé sur les critères\nde conformité"],
        ['bell',         'Notification', "L'utilisateur est informé\npar email et dans son\nespace"],
    ] : [
        ['upload',       'Submission',  "The user submits\ntheir information\nand documents"],
        ['search',       'Review',      "Manual and automatic\nverification of the\ninformation"],
        ['shield-check', 'Decision',    "Approval or refusal\nbased on the compliance\ncriteria"],
        ['bell',         'Notification', "The user is notified\nby email and in their\nworkspace"],
    ];
    $kycDocs = $isFr
        ? ['Pièce d\'identité valide', 'Justificatif de domicile', 'Photo ou selfie', 'Autres documents (si nécessaire)']
        : ['Valid ID document', 'Proof of address', 'Photo or selfie', 'Other documents (if needed)'];

    $kycQuickLinks = [
        ['user-check',   $isFr ? 'Vérifier un utilisateur' : 'Verify a user',        route('admin.verifications', ['lang' => $lang])],
        ['scan-line',    $isFr ? 'Scanner un document' : 'Scan a document',           route('admin.verifications', ['lang' => $lang])],
        ['search',       $isFr ? 'Rechercher une demande' : 'Search a request',       route('admin.verifications', ['lang' => $lang])],
        ['file-text',    $isFr ? 'Liste des documents' : 'Document list',             route('admin.verifications', ['lang' => $lang])],
        ['settings',     $isFr ? 'Paramètres KYC' : 'KYC settings',                   route('admin.settings', ['lang' => $lang])],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'KYC Centre — Administration' : 'KYC Centre — Administration' }}</title>
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

            {{-- Branded KYC hero band --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0E3D22] to-[#12522C] px-6 py-5 flex items-center gap-4">
                <img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="absolute inset-x-0 bottom-0 h-[8px] w-full object-cover opacity-90" aria-hidden="true">
                <span class="w-[52px] h-[52px] rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-7 h-7 text-[#E9C25A]" style="stroke-width:1.7"></i>
                </span>
                <div class="min-w-0">
                    <h1 class="flex items-center gap-2 text-[22px] font-bold text-white tracking-tight">
                        {{ $isFr ? 'KYC CENTRE' : 'KYC CENTRE' }}
                        <span class="text-[#E9C25A] text-[13px]">◇ ◈ ◇</span>
                    </h1>
                    <p class="mt-0.5 text-[12.5px] text-[#CFE3D5]">{{ $isFr ? 'Know Your Customer — Sécurisé. Vérifié. Fiable.' : 'Know Your Customer — Secure. Verified. Reliable.' }}</p>
                </div>
            </div>

            {{-- Stat cards --}}
            <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($kycCards as [$kcIcon, $kcColor, $kcTile, $kcCard, $kcValue, $kcLabel, $kcSub, $kcSpark])
                <div class="{{ $kcCard }} border rounded-2xl px-4 py-4 flex flex-col">
                    <div class="flex items-start justify-between">
                        <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center" style="background-color: {{ $kcTile }}">
                            <i data-lucide="{{ $kcIcon }}" class="w-[22px] h-[22px]" style="color: {{ $kcColor }};stroke-width:1.8"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-[26px] font-bold text-[#1B1B18] leading-none">{{ number_format($kcValue) }}</p>
                    <p class="mt-1 text-[12.5px] font-semibold text-[#3B382F]">{{ $kcLabel }}</p>
                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $kcSub }}</p>
                    <svg viewBox="0 0 120 28" class="mt-2 w-full h-[26px]" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="0,20 15,16 30,18 45,10 60,14 75,7 90,11 105,5 120,8" fill="none" stroke="{{ $kcSpark }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
                    </svg>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                {{-- Requests table --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Demandes KYC récentes' : 'Recent KYC requests' }}</h2>

                    <form method="GET" class="mt-4 flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex-1 min-w-[180px] flex items-center gap-2 h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3.5">
                            <i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i>
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}" class="flex-1 min-w-0 text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                        </div>
                        <select name="statut" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                            @foreach(['submitted' => ($isFr?'En attente':'Pending'), 'under_review' => ($isFr?'En vérification':'In review'), 'approved' => ($isFr?'Approuvée':'Approved'), 'rejected' => ($isFr?'Rejetée':'Rejected')] as $sv => $sl)
                            <option value="{{ $sv }}" @selected($filters['statut'] === $sv)>{{ $sl }}</option>
                            @endforeach
                        </select>
                        <select name="role" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les rôles' : 'All roles' }}</option>
                            @foreach(['artisan' => ($isFr?'Artisan':'Artisan'), 'entreprise' => ($isFr?'Boutique':'Shop'), 'cooperative' => ($isFr?'Coopérative':'Cooperative')] as $rv => $rl)
                            <option value="{{ $rv }}" @selected($filters['role'] === $rv)>{{ $rl }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="h-[40px] inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 text-[12.5px] font-semibold text-white transition-colors">
                            <i data-lucide="filter" class="w-4 h-4"></i>{{ $isFr ? 'Filtrer' : 'Filter' }}
                        </button>
                        <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="h-[40px] inline-flex items-center gap-2 bg-[#FBF6EA] border border-[#EAD9AC] hover:border-[#C9942E] rounded-lg px-4 text-[12.5px] font-semibold text-[#7A5A12] transition-colors">
                            <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}
                        </a>
                    </form>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[760px]">
                            <thead>
                                <tr class="text-left border-b border-[#F0F1F0]">
                                    <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Utilisateur' : 'User' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Rôle' : 'Role' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Type</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Soumis le' : 'Submitted' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Dernière MàJ' : 'Last update' }}</th>
                                    <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F4F5F4]">
                                @forelse($applications as $app)
                                @php
                                    [$stLabel, $stCls] = $kycStatusMeta[$app->status] ?? [$app->status, 'bg-[#EEECE6] text-[#6F6B60]'];
                                    [$roLabel, $roCls] = $kycRoleMeta[$app->vendor_type] ?? [$isFr ? 'Artisan' : 'Artisan', 'bg-[#E2F3E8] text-[#157A43]'];
                                    $initial = mb_strtoupper(mb_substr($app->owner_name ?? $app->business_name ?? '?', 0, 1));
                                @endphp
                                <tr>
                                    <td class="py-3.5 pr-3">
                                        <div class="flex items-center gap-3">
                                            @if($app->logo)
                                            <img src="{{ asset('storage/' . $app->logo) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                            @else
                                            <span class="w-9 h-9 rounded-full bg-[#14652F] text-white text-[13px] font-bold flex items-center justify-center shrink-0">{{ $initial }}</span>
                                            @endif
                                            <span class="min-w-0">
                                                <span class="block text-[13px] font-semibold text-[#1B1B18] truncate">{{ $app->owner_name ?? $app->business_name }}</span>
                                                <span class="block text-[11px] text-[#8A857A] truncate">{{ $app->owner_email ?? '—' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $roCls }}">{{ $roLabel }}</span></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F]">{{ $app->vendor_type === 'artisan' ? ($isFr ? 'Individuel' : 'Individual') : ($isFr ? 'Entreprise' : 'Business') }}</td>
                                    <td class="py-3.5 px-2"><span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}"><span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $stLabel }}</span></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $kycDate($app->submitted_at ?? $app->created_at) }}<br><span class="text-[11px] text-[#8A857A]">{{ \Carbon\Carbon::parse($app->submitted_at ?? $app->created_at)->format('H:i') }}</span></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $kycDate($app->updated_at) }}<br><span class="text-[11px] text-[#8A857A]">{{ \Carbon\Carbon::parse($app->updated_at)->format('H:i') }}</span></td>
                                    <td class="py-3.5 pl-2">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.verifications', ['lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.verifications', ['lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                            @if($app->status === 'approved')
                                            <span class="w-8 h-8 rounded-lg bg-[#E2F3E8] flex items-center justify-center text-[#157A43]" title="{{ $isFr ? 'Vérifié' : 'Verified' }}"><i data-lucide="shield-check" class="w-4 h-4"></i></span>
                                            @else
                                            <a href="{{ route('admin.verifications', ['lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="Actions"><i data-lucide="more-vertical" class="w-4 h-4"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="py-10 text-center text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucune demande KYC ne correspond à ces critères.' : 'No KYC request matches these criteria.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($applications->hasPages())
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $applications->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $applications->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($applications->total()) }} {{ $isFr ? 'demandes' : 'requests' }}</p>
                        <div class="flex items-center gap-1.5">
                            @if($applications->onFirstPage())
                            <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                            @else
                            <a href="{{ $applications->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                            @endif
                            @foreach($applications->getUrlRange(1, $applications->lastPage()) as $pn => $url)
                            @if($pn === $applications->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>
                            @else
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pn }}</a>
                            @endif
                            @endforeach
                            @if($applications->hasMorePages())
                            <a href="{{ $applications->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                            @else
                            <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                            @endif
                        </div>
                    </div>
                    @endif
                </section>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="flex items-center gap-2 text-[13px] font-bold text-[#1B1B18]"><i data-lucide="pie-chart" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'Répartition par rôle' : 'Distribution by role' }}</h2>
                        <div class="mt-4 space-y-3.5">
                            @foreach($kycRoleDist as $rd)
                            @php $pct = round($rd['count'] / $kycRoleTotal * 100, 1); @endphp
                            <div>
                                <div class="flex items-center justify-between text-[12px]">
                                    <span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2 h-2 rounded-full" style="background-color: {{ $rd['color'] }}"></span>{{ $isFr ? $rd['fr'] : $rd['en'] }}</span>
                                    <span class="font-semibold text-[#1B1B18]">{{ $pct }}% ({{ $rd['count'] }})</span>
                                </div>
                                <div class="mt-1.5 h-1.5 rounded-full bg-[#F0EFEA] overflow-hidden">
                                    <span class="block h-full rounded-full" style="width: {{ $pct }}%; background-color: {{ $rd['color'] }}"></span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="flex items-center gap-2 text-[13px] font-bold text-[#1B1B18]"><i data-lucide="zap" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'Vérification rapide' : 'Quick verification' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            @foreach($kycQuickLinks as [$qlIcon, $qlLabel, $qlUrl])
                            <a href="{{ $qlUrl }}" class="flex items-center gap-3 py-2.5 group">
                                <i data-lucide="{{ $qlIcon }}" class="w-[17px] h-[17px] text-[#55524A] group-hover:text-[#14652F]"></i>
                                <span class="text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qlLabel }}</span>
                            </a>
                            @endforeach
                        </div>
                    </section>

                    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-[#0E3D22] to-[#12522C] px-5 py-6 text-center">
                        <p class="font-serif text-[15px] leading-relaxed text-white italic">{{ $isFr ? 'Vérifier aujourd\'hui, protéger notre héritage pour demain.' : 'Verify today, protect our heritage for tomorrow.' }}</p>
                        <p class="mt-3 text-[#E9C25A] text-[12px]">◇ ◈ ◇</p>
                    </section>
                </aside>
            </div>

            {{-- Process + documents --}}
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-5">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="flex items-center gap-2 text-[14px] font-bold text-[#1B1B18]"><i data-lucide="git-branch" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'Étapes du processus KYC' : 'KYC process steps' }}</h2>
                    <div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach($kycProcess as $i => [$pIcon, $pTitle, $pDesc])
                        <div class="flex flex-col">
                            <span class="w-[42px] h-[42px] rounded-full bg-[#F3F0E6] flex items-center justify-center"><i data-lucide="{{ $pIcon }}" class="w-5 h-5 text-[#14652F]" style="stroke-width:1.7"></i></span>
                            <p class="mt-2.5 text-[12.5px] font-bold text-[#1B1B18]">{{ $i + 1 }}. {{ $pTitle }}</p>
                            <p class="mt-1 text-[11px] text-[#6F6B60] leading-relaxed whitespace-pre-line">{{ $pDesc }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="flex items-center gap-2 text-[14px] font-bold text-[#1B1B18]"><i data-lucide="file-check" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'Documents requis' : 'Required documents' }}</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach($kycDocs as $doc)
                        <li class="flex items-center gap-2.5 text-[12.5px] text-[#3B382F]"><i data-lucide="check-circle-2" class="w-4 h-4 text-[#157A43] shrink-0"></i>{{ $doc }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
