<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Administration — Artisan Hub 237' }}</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-brand-100: 253 240 211;
            --c-brand-200: 250 218 154;
            --c-brand-300: 247 192 98;
            --c-brand-400: 244 163 42;
            --c-brand-50: 254 249 238;
            --c-brand-500: 232 136 14;
            --c-brand-600: 204 106 9;
            --c-brand-700: 168 78 11;
            --c-brand-800: 135 61 16;
            --c-brand-900: 110 51 17;
            --c-forest-100: 219 240 227;
            --c-forest-200: 184 224 201;
            --c-forest-400: 91 168 131;
            --c-forest-50: 240 249 244;
            --c-forest-500: 45 106 79;
            --c-forest-600: 27 67 50;
            --c-forest-700: 13 43 30;
            --c-forest-800: 8 32 24;
            --c-forest-900: 3 19 14;
            --c-leaf: 20 101 47;
        }
    </style>
    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
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
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
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
            {{-- Every flash this console can raise, rendered once, here.
                 An admin redirected away from a record that cannot be shown
                 arrived back at a list with no explanation, which is precisely
                 how a working guard reads as a broken link.

                 These three were previously hand-rolled per page — success on
                 18 screens, $errors on 6 — in four different icon-and-spacing
                 dialects, and absent everywhere else. Any admin page that
                 flashes one of these keys now gets the alert whether or not
                 its author remembered to write the markup. Pages must not
                 repeat these blocks; a page-level copy prints twice.

                 Page-specific flash keys (payment_reviewed, review_moderated)
                 stay with their pages: they are not this layout's vocabulary. --}}
            @if(session('success'))
                <div class="ui-alert ui-alert-ok mb-4 flex items-start gap-2.5" role="status">
                    <i data-lucide="check-circle-2" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="ui-alert ui-alert-danger mb-4 flex items-start gap-2.5" role="alert">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Every message, not just ->first(). Half the pages this replaces
                 showed only the first, so a form failing three rules told the
                 admin about one and appeared to reject the other two silently. --}}
            @if($errors->any())
                <div class="ui-alert ui-alert-danger mb-4 flex items-start gap-2.5" role="alert">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span>
                        @foreach($errors->all() as $error)
                            <span class="block">{{ $error }}</span>
                        @endforeach
                    </span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@include('pages.partials.admin-bulk-select')
<script>lucide.createIcons();</script>
</body>
</html>
