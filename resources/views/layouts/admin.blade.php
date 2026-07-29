<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administration — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Legacy tints still used by migrated content sections
                        brand:  { 50:'#fef9ee',100:'#fdf0d3',200:'#fada9a',300:'#f7c062',400:'#f4a32a',500:'#e8880e',600:'#cc6a09',700:'#a84e0b',800:'#873d10',900:'#6e3311' },
                        forest: { 50:'#f0f9f4',100:'#dbf0e3',200:'#b8e0c9',300:'#8cc9a8',400:'#5ba883',500:'#2d6a4f',600:'#1b4332',700:'#0d2b1e',800:'#082018',900:'#03130e' },
                        leaf:   '#14652F',
                    },
                    fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F8F4EC] dark:bg-[#0A0C09] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

@php
    $siacUser = session('siac_user') ?? [];
    $lang = request()->query('lang', request()->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    $isFr = $lang === 'fr';

    // Sidebar active key: a page may set $adminActive itself; otherwise derive from the route.
    $adminActive = ($adminActive ?? null) ?: (collect([
        'dashboard.admin'      => 'dashboard',
        'admin.businesses'     => 'businesses',
        'admin.businesses.*'   => 'businesses',
        'admin.products'       => 'products',
        'admin.industries'     => 'industries',
        'admin.industries.*'   => 'industries',
        'admin.regions'        => 'regions',
        'admin.regions.*'      => 'regions',
        'admin.collections'    => 'collections',
        'admin.collections.*'  => 'collections',
        'admin.news'           => 'news',
        'admin.news.*'         => 'news',
        'admin.media'          => 'media',
        'admin.events'         => 'events',
        'admin.artisans'       => 'artisans',
        'admin.artisans.*'     => 'artisans',
        'admin.users'          => 'users',
        'admin.users.*'        => 'users',
        'admin.roles'          => 'roles',
        'admin.verifications'  => 'kyc',
        'admin.verifications.*'=> 'kyc',
        'admin.kyc'            => 'kyc',
        'admin.kyc.*'          => 'kyc',
        'admin.certificates'   => 'certificates',
        'admin.certificates.*' => 'certificates',
        'admin.quotes'         => 'quotes',
        'admin.orders'         => 'orders',
        'admin.analytics'      => 'analytics',
        'admin.reviews'        => 'moderation',
        'admin.reviews.*'      => 'moderation',
        'admin.awards.*'       => 'moderation',
        'admin.orders.*'       => 'orders',
        'admin.payments'       => 'payments',
        'admin.payments.*'     => 'payments',
        'admin.subscriptions'  => 'subscriptions',
        'admin.subscriptions.*'=> 'subscriptions',
        'admin.api-consumers'  => 'subscriptions',
        'admin.reports'        => 'reports',
        'admin.reports.*'      => 'reports',
        'admin.cms'            => 'pages',
        'admin.cms.*'          => 'pages',
        'admin.settings'       => 'settings',
        'admin.settings.*'     => 'settings',
        'admin.audit-log'      => 'logs',
        'admin.backups'        => 'backups',
        'admin.backups.*'      => 'backups',
        'admin.notifications'  => 'notifications',
        'admin.notifications.*'=> 'notifications',
        'admin.exports'        => 'exports',
        'admin.partners'       => 'partners',
        'admin.partners.*'     => 'partners',
        'admin.moderation'     => 'moderation',
        'admin.support'        => 'adminsupport',
        'admin.support.*'      => 'adminsupport',
    ])->first(fn ($key, $pattern) => request()->routeIs($pattern)) ?? 'dashboard');
@endphp

<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')

    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', [
            'pageTitle' => $pageTitle ?? ($isFr ? 'Tableau de Bord' : 'Dashboard'),
            'pageSubtitle' => $pageSubtitle ?? '',
            'pageSearchPlaceholder' => $pageSearchPlaceholder ?? ($isFr ? 'Rechercher un artisan, un produit, une commande...' : 'Search an artisan, a product, an order...'),
            'pageBreadcrumb' => $pageBreadcrumb ?? null,
        ])

        <main class="px-5 lg:px-7 pt-5 pb-8">
            {{-- Failure flash, rendered once for every admin screen.
                 Nothing in this console rendered session('error') — an admin
                 redirected away from a record that cannot be shown simply
                 arrived back at a list with no explanation, which is precisely
                 how a working guard reads as a broken link.

                 Deliberately only `error`. session('success') is already
                 rendered by 18 of these pages individually and $errors by 6, so
                 repeating either here would print the same alert twice. Folding
                 those into this block is a separate, mechanical pass. --}}
            @if(session('error'))
                <div class="ui-alert ui-alert-danger mb-4 flex items-start gap-2.5" role="alert">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
