@extends('layouts.admin')

@php
$isFr = ($lang ?? 'fr') === 'fr';
$pageTitle = $isFr ? 'Paramètres Généraux' : 'General Settings';

// Platform key/value settings (prefill); the GET route passes $platform, but fall
// back to a direct query so the page still works with the legacy controller.
$platform = ($platform ?? null) ?: \Illuminate\Support\Facades\DB::table('platform_settings')->pluck('value', 'key');

// ── Top tab navigation (full design) ─────────────────────────────────────────
$tabs = [
    'general'       => ['icon' => 'layout-grid',  'fr' => 'Général',            'en' => 'General'],
    'seo'           => ['icon' => 'globe',        'fr' => 'Site & SEO',         'en' => 'Site & SEO'],
    'security'      => ['icon' => 'shield-check', 'fr' => 'Comptes & Sécurité', 'en' => 'Accounts & Security'],
    'payments'      => ['icon' => 'credit-card',  'fr' => 'Paiements',          'en' => 'Payments'],
    'notifications' => ['icon' => 'bell',         'fr' => 'Notifications',      'en' => 'Notifications'],
    'branding'      => ['icon' => 'paintbrush',   'fr' => 'Apparence',          'en' => 'Appearance'],
    'integrations'  => ['icon' => 'workflow',     'fr' => 'Intégrations',       'en' => 'Integrations'],
    'system'        => ['icon' => 'settings',     'fr' => 'Système',            'en' => 'System'],
];

// Legacy ?section= values keep working: alias them onto the new tabs.
$sectionAliases = [
    'social'      => 'general',
    'languages'   => 'system',
    'maintenance' => 'system',
    'backups'     => 'system',
];
$section = request()->query('section', 'general');
$section = $sectionAliases[$section] ?? $section;
if (! array_key_exists($section, $tabs)) $section = 'general';

// system_settings group cards (existing admin.settings.update mechanism)
$groupMeta = [
    'general'  => ['icon' => 'sliders-horizontal', 'fr' => 'Paramètres système (hérités)', 'en' => 'System settings (legacy)'],
    'limits'   => ['icon' => 'gauge',              'fr' => 'Limites plateforme',            'en' => 'Platform limits'],
    'siac'     => ['icon' => 'calendar-days',      'fr' => 'Événement SIARC',               'en' => 'SIARC event'],
    'auth'     => ['icon' => 'shield-check',       'fr' => 'Authentification',              'en' => 'Authentication'],
    'payments' => ['icon' => 'credit-card',        'fr' => 'Frais & taxes',                 'en' => 'Fees & taxes'],
    'trading'  => ['icon' => 'arrow-left-right',   'fr' => 'Transactions',                  'en' => 'Trading'],
    'app'      => ['icon' => 'monitor-cog',        'fr' => 'Application',                   'en' => 'Application'],
];
$groups = $groups ?? collect();

// Which system_settings groups render inside which tab; any group not claimed
// below falls back to the Système tab so nothing is lost.
$sysGroupsFor = [
    'payments' => ['payments', 'trading', 'limits'],
    'security' => ['auth'],
    'system'   => array_values(array_unique(array_merge(
        ['general', 'app', 'siac'],
        $groups->keys()->reject(fn ($g) => in_array($g, ['general', 'payments', 'trading', 'limits', 'auth', 'app', 'siac'], true))->values()->all()
    ))),
];

$labelize = fn ($key) => ucfirst(str_replace('_', ' ', $key));
$inputCls = 'w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400';
$p = fn (string $key, string $default = '') => old('settings.' . $key, $platform[$key] ?? $default);

$logoVal    = $platform['logo_path']    ?? $platform['branding_logo']    ?? null;
$faviconVal = $platform['favicon_path'] ?? $platform['branding_favicon'] ?? null;
$logoUrl    = filled($logoVal)    ? asset('storage/' . $logoVal)    : asset('images/landing/logo.png');
$faviconUrl = filled($faviconVal) ? asset('storage/' . $faviconVal) : asset('images/landing/logo.png');

$defaultLocale = optional($groups->get('app'))?->firstWhere('key', 'default_locale')['value'] ?? 'fr';

// Site toggles (Paramètres du Site card)
$siteToggles = [
    'site_maintenance'          => [false, ['Site en maintenance', 'Rendre le site inaccessible au public'],
                                            ['Maintenance mode', 'Make the site unavailable to the public']],
    'artisan_registrations'     => [true,  ['Inscriptions des artisans', 'Autoriser les nouveaux artisans à s\'inscrire'],
                                            ['Artisan registrations', 'Allow new artisans to sign up']],
    'business_registrations'    => [true,  ['Inscriptions des entreprises', 'Autoriser les nouvelles entreprises à s\'inscrire'],
                                            ['Business registrations', 'Allow new businesses to sign up']],
    'public_comments'           => [true,  ['Commentaires publics', 'Autoriser les commentaires sur le site'],
                                            ['Public comments', 'Allow comments on the site']],
    'auto_account_verification' => [false, ['Vérification automatique des comptes', 'Approuver automatiquement après inscription'],
                                            ['Automatic account verification', 'Approve automatically after registration']],
    'demo_mode'                 => [false, ['Mode démo', 'Afficher le contenu de démonstration'],
                                            ['Demo mode', 'Display demonstration content']],
];

// Réseaux sociaux rows (brand SVGs — lucide bundle has no brand icons)
$socialRows = [
    'social_facebook'  => ['Facebook',    '#1877F2', 'https://facebook.com/galerie.artisanat.cm',
        '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>'],
    'social_instagram' => ['Instagram',   '#E4405F', 'https://instagram.com/galerie.artisanat.cm',
        '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12s.014 3.668.072 4.948c.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24s3.668-.014 4.948-.072c4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>'],
    'social_twitter'   => ['Twitter (X)', '#0F1419', 'https://twitter.com/GalerieArtisanat',
        '<path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>'],
    'social_youtube'   => ['YouTube',     '#FF0000', 'https://youtube.com/@galerieartisanatcm',
        '<path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>'],
    'social_linkedin'  => ['LinkedIn',    '#0A66C2', 'https://linkedin.com/company/galerie-artisanat',
        '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/>'],
];

// Mentions légales rows
$legalRows = [
    [$isFr ? 'Conditions d\'utilisation'        : 'Terms of use',        route('terms',   array_filter(['lang' => request()->query('lang')]))],
    [$isFr ? 'Politique de confidentialiité'    : 'Privacy policy',      route('privacy', array_filter(['lang' => request()->query('lang')]))],
    [$isFr ? 'Politique de remboursement'       : 'Refund policy',       route('admin.cms', array_filter(['lang' => request()->query('lang')]))],
    [$isFr ? 'Mentions légales'                 : 'Legal notice',        route('admin.cms', array_filter(['lang' => request()->query('lang')]))],
    ['Cookies Policy',                                                    route('admin.cms', array_filter(['lang' => request()->query('lang')]))],
];

$cardCls  = 'bg-white border border-gray-200 rounded-xl p-5';
$labelCls = 'block text-xs font-semibold text-gray-700 mb-1.5';
$greenBtn = 'inline-flex items-center gap-2 bg-[#0A5A2A] hover:bg-[#0d6b33] text-white font-semibold px-4 py-2.5 rounded-lg text-[13px] transition-colors';
$tinyBtn  = 'px-3 py-1.5 rounded-lg bg-[#E8F3EC] text-[#0A5A2A] text-xs font-semibold hover:bg-[#d7ebde] transition-colors whitespace-nowrap';
@endphp

@section('content')

{{-- Breadcrumb : Accueil / Paramètres --}}
<nav class="flex items-center gap-1.5 text-xs text-gray-500 -mt-2 mb-5" aria-label="Breadcrumb">
    <a href="{{ route('dashboard.admin') }}" class="hover:text-forest-600">{{ $isFr ? 'Accueil' : 'Home' }}</a>
    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
    <span class="text-gray-700">{{ $isFr ? 'Paramètres' : 'Settings' }}</span>
</nav>

@if(session('success'))
    <div class="flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800 mb-5">
        <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800 mb-5">
        <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
        {{ $errors->first() }}
    </div>
@endif

{{-- ── Top tabs ─────────────────────────────────────────────────────────── --}}
<div class="border-b border-gray-200 mb-6 overflow-x-auto">
    <nav class="flex items-center gap-7 min-w-max">
        @foreach($tabs as $key => $tab)
        <a href="{{ route('admin.settings', array_filter(['section' => $key === 'general' ? null : $key, 'lang' => request()->query('lang')])) }}"
           class="flex items-center gap-2 pb-3 pt-1 text-[13px] font-semibold border-b-2 -mb-px transition-colors {{ $section === $key ? 'border-[#0A5A2A] text-[#0A5A2A]' : 'border-transparent text-gray-600 hover:text-gray-900' }}">
            <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4 {{ $section === $key ? 'text-[#0A5A2A]' : 'text-gray-500' }}"></i>
            {{ $isFr ? $tab['fr'] : $tab['en'] }}
        </a>
        @endforeach
    </nav>
</div>

{{-- ══════════════════════ Onglet Général (design intégral) ══════════════════════ --}}
@if($section === 'general')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 items-start">

    {{-- 1. Informations Générales --}}
    <form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Informations Générales' : 'General Information' }}</h2>

        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom de la plateforme' : 'Platform name' }}</label>
            <input type="text" name="settings[platform_name]" required class="{{ $inputCls }}"
                   value="{{ $p('platform_name', 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Slogan' : 'Tagline' }}</label>
            <input type="text" name="settings[platform_slogan]" required class="{{ $inputCls }}"
                   value="{{ $p('platform_slogan', 'Notre Héritage, Notre Fierté, Notre Avenir') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Adresse email de contact' : 'Contact email address' }}</label>
            <input type="email" name="settings[contact_email]" required class="{{ $inputCls }}"
                   value="{{ $p('contact_email', 'contact@galerie-artisanat.cm') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone principal' : 'Main phone number' }}</label>
            <div class="flex items-center gap-2 px-3.5 py-2 border border-gray-300 rounded-lg focus-within:border-forest-400 focus-within:ring-1 focus-within:ring-forest-400">
                <svg viewBox="0 0 30 20" class="w-5 h-3.5 shrink-0 rounded-[2px]" aria-hidden="true">
                    <rect width="10" height="20" fill="#007A5E"/><rect x="10" width="10" height="20" fill="#CE1126"/><rect x="20" width="10" height="20" fill="#FCD116"/>
                    <path d="M15 6l1 2.6h2.7l-2.2 1.7.9 2.7-2.4-1.7-2.4 1.7.9-2.7-2.2-1.7H14z" fill="#FCD116"/>
                </svg>
                <i data-lucide="chevron-down" class="w-3 h-3 text-gray-400 shrink-0"></i>
                <input type="tel" name="settings[contact_phone]" class="flex-1 min-w-0 text-sm focus:outline-none"
                       value="{{ $p('contact_phone', '+237 6 70 41 62 38') }}">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }}</label>
                <select name="settings[country]" class="{{ $inputCls }} bg-white">
                    @foreach(['Cameroun' => $isFr ? 'Cameroun' : 'Cameroon', 'Nigeria' => 'Nigeria', 'Tchad' => $isFr ? 'Tchad' : 'Chad', 'Gabon' => 'Gabon'] as $c => $cLabel)
                    <option value="{{ $c }}" @selected($p('country', 'Cameroun') === $c)>{{ $cLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Devise par défaut' : 'Default currency' }}</label>
                <select name="settings[currency]" class="{{ $inputCls }} bg-white">
                    @foreach(['XAF' => $isFr ? 'Franc CFA (XAF)' : 'CFA Franc (XAF)', 'EUR' => 'Euro (EUR)', 'USD' => $isFr ? 'Dollar US (USD)' : 'US Dollar (USD)'] as $cur => $curLabel)
                    <option value="{{ $cur }}" @selected($p('currency', 'XAF') === $cur)>{{ $curLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Fuseau horaire' : 'Timezone' }}</label>
                <select name="settings[timezone]" class="{{ $inputCls }} bg-white">
                    @foreach([
                        'Africa/Douala'    => $isFr ? '(UTC+01:00) Douala, Yaoundé' : '(UTC+01:00) Douala, Yaounde',
                        'UTC'              => '(UTC+00:00) UTC',
                        'Europe/Paris'     => '(UTC+01:00) Europe/Paris',
                        'America/New_York' => $isFr ? '(UTC-05:00) Amérique/New York' : '(UTC-05:00) America/New York',
                    ] as $tz => $tzLabel)
                    <option value="{{ $tz }}" @selected($p('timezone', 'Africa/Douala') === $tz)>{{ $tzLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Langue par défaut' : 'Default language' }}</label>
                <select name="settings[default_language]" class="{{ $inputCls }} bg-white">
                    <option value="fr" @selected($p('default_language', 'fr') === 'fr')>{{ $isFr ? 'Français' : 'French' }}</option>
                    <option value="en" @selected($p('default_language', 'fr') === 'en')>{{ $isFr ? 'Anglais' : 'English' }}</option>
                </select>
            </div>
        </div>
        <button type="submit" class="{{ $greenBtn }}">
            <i data-lucide="check" class="w-4 h-4"></i>
            {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
        </button>
    </form>

    {{-- 2. Logo & Identité Visuelle --}}
    <div class="{{ $cardCls }} space-y-4">
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Logo & Identité Visuelle' : 'Logo & Visual Identity' }}</h2>

        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Logo actuel' : 'Current logo' }}</label>
            <div class="flex items-start gap-4">
                <img src="{{ $logoUrl }}" alt="Logo" class="w-[88px] h-[88px] rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
                <div class="space-y-2 min-w-0">
                    <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-gray-50 transition-colors">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            {{ $isFr ? 'Changer le logo' : 'Change logo' }}
                            <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                    <form method="POST" action="{{ route('admin.settings.general') }}">
                        @csrf
                        <input type="hidden" name="settings[logo_path]" value="">
                        <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 border border-red-200 text-red-500 rounded-lg text-xs font-semibold hover:bg-red-50 transition-colors">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            {{ $isFr ? 'Supprimer' : 'Delete' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <label class="{{ $labelCls }}">Favicon</label>
            <div class="flex items-center gap-3">
                <img src="{{ $faviconUrl }}" alt="Favicon" class="w-9 h-9 rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
                <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-gray-50 transition-colors">
                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                        {{ $isFr ? 'Changer le favicon' : 'Change favicon' }}
                        <input type="file" name="favicon" accept=".png,.ico,.svg" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.general') }}" class="space-y-4">
            @csrf
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Couleur principale' : 'Primary colour' }}</label>
                <div class="flex items-center gap-2 px-2.5 py-2 border border-gray-300 rounded-lg">
                    <span class="w-6 h-6 rounded-md shrink-0 border border-gray-200" style="background: {{ $p('primary_color', '#0A5A2A') }}"></span>
                    <input type="text" name="settings[primary_color]" value="{{ $p('primary_color', '#0A5A2A') }}"
                           onchange="this.form.submit()" class="flex-1 min-w-0 text-sm font-medium focus:outline-none">
                </div>
            </div>
            <div>
                <label class="{{ $labelCls }}">{{ $isFr ? 'Couleur secondaire' : 'Secondary colour' }}</label>
                <div class="flex items-center gap-2 px-2.5 py-2 border border-gray-300 rounded-lg">
                    <span class="w-6 h-6 rounded-md shrink-0 border border-gray-200" style="background: {{ $p('secondary_color', '#F3B700') }}"></span>
                    <input type="text" name="settings[secondary_color]" value="{{ $p('secondary_color', '#F3B700') }}"
                           onchange="this.form.submit()" class="flex-1 min-w-0 text-sm font-medium focus:outline-none">
                </div>
            </div>
        </form>
    </div>

    {{-- 3. Paramètres du Site --}}
    <form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-5">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Paramètres du Site' : 'Site Settings' }}</h2>

        @foreach($siteToggles as $key => [$default, $frTexts, $enTexts])
        @php $on = $p($key, $default ? 'true' : 'false') === 'true'; @endphp
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="hidden" name="settings[{{ $key }}]" value="false">
            <span class="relative inline-flex shrink-0 mt-0.5">
                <input type="checkbox" name="settings[{{ $key }}]" value="true" class="sr-only peer" @checked($on)>
                <span class="w-10 h-[22px] bg-gray-200 rounded-full transition-colors peer-checked:bg-[#0A9B4A]
                             after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:w-4 after:h-4
                             after:bg-white after:rounded-full after:shadow after:transition-all peer-checked:after:translate-x-[18px]"></span>
            </span>
            <span class="min-w-0">
                <span class="block text-[13px] font-semibold text-gray-900">{{ $isFr ? $frTexts[0] : $enTexts[0] }}</span>
                <span class="block text-xs text-gray-500 mt-0.5">{{ $isFr ? $frTexts[1] : $enTexts[1] }}</span>
            </span>
        </label>
        @endforeach

        <button type="submit" class="{{ $greenBtn }}">
            <i data-lucide="check" class="w-4 h-4"></i>
            {{ $isFr ? 'Enregistrer les préférences' : 'Save preferences' }}
        </button>
    </form>

    {{-- 4. Paramètres de Contenu --}}
    <form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Paramètres de Contenu' : 'Content Settings' }}</h2>

        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Éléments par page' : 'Items per page' }}</label>
            <input type="number" min="1" name="settings[items_per_page]" class="{{ $inputCls }}" value="{{ $p('items_per_page', '10') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Durée de modération (jours)' : 'Moderation window (days)' }}</label>
            <input type="number" min="0" name="settings[moderation_days]" class="{{ $inputCls }}" value="{{ $p('moderation_days', '7') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Taille max. de fichier (Mo)' : 'Max file size (MB)' }}</label>
            <input type="number" min="1" name="settings[max_file_size_mb]" class="{{ $inputCls }}" value="{{ $p('max_file_size_mb', '10') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Formats d\'images autorisés' : 'Allowed image formats' }}</label>
            <div class="flex items-center gap-1.5 flex-wrap px-2.5 py-2 border border-gray-300 rounded-lg min-h-[40px]"
                 data-chips="image_formats">
                @foreach(array_filter(array_map('trim', explode(',', $p('image_formats', 'JPG,PNG,WEBP')))) as $chip)
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-1 rounded" data-chip="{{ $chip }}">
                    {{ $chip }}
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="removeChip(this)" aria-label="{{ $isFr ? 'Retirer' : 'Remove' }} {{ $chip }}">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
                @endforeach
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 ml-auto"></i>
                <input type="hidden" name="settings[image_formats]" value="{{ $p('image_formats', 'JPG,PNG,WEBP') }}">
            </div>
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Qualité d\'image par défaut' : 'Default image quality' }}</label>
            <select name="settings[image_quality]" class="{{ $inputCls }} bg-white">
                @foreach([
                    'high'   => $isFr ? 'Haute (80%)'   : 'High (80%)',
                    'medium' => $isFr ? 'Moyenne (60%)' : 'Medium (60%)',
                    'low'    => $isFr ? 'Basse (40%)'   : 'Low (40%)',
                ] as $q => $qLabel)
                <option value="{{ $q }}" @selected($p('image_quality', 'high') === $q)>{{ $qLabel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Langues activées' : 'Enabled languages' }}</label>
            <div class="flex items-center gap-1.5 flex-wrap px-2.5 py-2 border border-gray-300 rounded-lg min-h-[40px]"
                 data-chips="enabled_languages">
                @foreach(array_filter(array_map('trim', explode(',', $p('enabled_languages', 'Français,English')))) as $chip)
                <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-1 rounded" data-chip="{{ $chip }}">
                    {{ $chip }}
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="removeChip(this)" aria-label="{{ $isFr ? 'Retirer' : 'Remove' }} {{ $chip }}">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </span>
                @endforeach
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 ml-auto"></i>
                <input type="hidden" name="settings[enabled_languages]" value="{{ $p('enabled_languages', 'Français,English') }}">
            </div>
        </div>

        <button type="submit" class="{{ $greenBtn }}">
            <i data-lucide="check" class="w-4 h-4"></i>
            {{ $isFr ? 'Enregistrer les paramètres' : 'Save settings' }}
        </button>
    </form>

    {{-- 5. À Propos de la Plateforme --}}
    <form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'À Propos de la Plateforme' : 'About the Platform' }}</h2>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Titre' : 'Title' }}</label>
            <input type="text" name="settings[about_title]" class="{{ $inputCls }}"
                   value="{{ $p('about_title', $isFr ? 'À propos de la Galerie Virtuelle' : 'About the Virtual Gallery') }}">
        </div>
        <div>
            <label class="{{ $labelCls }}">{{ $isFr ? 'Description' : 'Description' }}</label>
            <textarea name="settings[about_description]" rows="6" class="{{ $inputCls }}">{{ $p('about_description', $isFr
                ? 'La Galerie Virtuelle Nationale de l\'Artisanat du Cameroun est une plateforme digitale officielle qui valorise, promeut et connecte les artisans, leurs œuvres et le patrimoine culturel camerounais au monde entier.'
                : 'The National Virtual Gallery of Cameroonian Crafts is an official digital platform that showcases, promotes and connects artisans, their works and Cameroonian cultural heritage to the whole world.') }}</textarea>
        </div>
        <button type="submit" class="{{ $greenBtn }}">
            {{ $isFr ? 'Enregistrer' : 'Save' }}
        </button>
    </form>

    {{-- 6. Réseaux Sociaux --}}
    <form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Réseaux Sociaux' : 'Social Networks' }}</h2>

        @foreach($socialRows as $key => [$label, $color, $placeholder, $svgPath])
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-full flex items-center justify-center shrink-0" style="background: {{ $color }}1a">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="{{ $color }}" aria-hidden="true">{!! $svgPath !!}</svg>
            </span>
            <span class="text-xs font-semibold text-gray-700 w-[74px] shrink-0">{{ $label }}</span>
            <input type="text" name="settings[{{ $key }}]" placeholder="{{ $placeholder }}"
                   class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400"
                   value="{{ $p($key, $placeholder) }}">
        </div>
        @endforeach

        <button type="submit" class="{{ $greenBtn }}">
            <i data-lucide="check" class="w-4 h-4"></i>
            {{ $isFr ? 'Enregistrer les liens' : 'Save links' }}
        </button>
    </form>

    {{-- 7. Mentions Légales --}}
    <div class="{{ $cardCls }}">
        <h2 class="text-[15px] font-bold text-gray-900 mb-2">{{ $isFr ? 'Mentions Légales' : 'Legal Notices' }}</h2>
        <div class="divide-y divide-gray-100">
            @foreach($legalRows as [$label, $href])
            <div class="flex items-center justify-between gap-3 py-3">
                <span class="text-[13px] font-medium text-gray-800">{{ $label }}</span>
                <a href="{{ $href }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#0A5A2A] hover:underline whitespace-nowrap">
                    {{ $isFr ? 'Voir / Modifier' : 'View / Edit' }}
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            @endforeach
        </div>
        <a href="{{ route('admin.cms', array_filter(['lang' => request()->query('lang')])) }}" class="{{ $greenBtn }} mt-3">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Gérer les pages légales' : 'Manage legal pages' }}
        </a>
    </div>

    {{-- 8. Sauvegarde & Maintenance --}}
    <div class="{{ $cardCls }} space-y-4">
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Sauvegarde & Maintenance' : 'Backup & Maintenance' }}</h2>

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-gray-900">{{ $isFr ? 'Dernière sauvegarde' : 'Last backup' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $p('backup_last_run', $isFr ? '05 Mai 2025 à 02:30' : '05 May 2025 at 02:30') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.general') }}">
                @csrf
                <input type="hidden" name="settings[backup_last_run]"
                       value="{{ $isFr ? now()->format('d') . ' ' . ucfirst(now()->locale('fr')->translatedFormat('F Y')) . ' à ' . now()->format('H:i') : now()->format('d M Y \a\t H:i') }}">
                <button type="submit" class="{{ $tinyBtn }}">{{ $isFr ? 'Sauvegarder maintenant' : 'Back up now' }}</button>
            </form>
        </div>

        <div class="flex items-start justify-between gap-3">
            <p class="text-[13px] font-semibold text-gray-900">{{ $isFr ? 'Sauvegarde automatique' : 'Automatic backup' }}</p>
            <span class="text-xs font-semibold text-[#0A9B4A] whitespace-nowrap">{{ $isFr ? 'Activée (quotidienne)' : 'Enabled (daily)' }}</span>
        </div>

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-gray-900">{{ $isFr ? 'Nettoyage du cache' : 'Cache cleanup' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $p('cache_size_mb', '125') }} {{ $isFr ? 'Mo' : 'MB' }}</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.general') }}">
                @csrf
                <input type="hidden" name="settings[cache_size_mb]" value="0">
                <button type="submit" class="{{ $tinyBtn }}">{{ $isFr ? 'Vider le cache' : 'Clear cache' }}</button>
            </form>
        </div>

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-gray-900">{{ $isFr ? 'Optimisation de la base de données' : 'Database optimisation' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $p('db_optimization_status', 'OK') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.general') }}">
                @csrf
                <input type="hidden" name="settings[db_optimization_status]" value="OK">
                <button type="submit" class="{{ $tinyBtn }}">{{ $isFr ? 'Optimiser' : 'Optimise' }}</button>
            </form>
        </div>

        <a href="{{ route('admin.audit-log', array_filter(['lang' => request()->query('lang')])) }}" class="{{ $greenBtn }} w-full justify-center">
            <i data-lucide="scroll-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Voir les logs système' : 'View system logs' }}
        </a>
    </div>

</div>

<script>
function removeChip(btn) {
    const chip = btn.closest('[data-chip]');
    const box  = btn.closest('[data-chips]');
    chip.remove();
    const hidden = box.querySelector('input[type="hidden"]');
    hidden.value = Array.from(box.querySelectorAll('[data-chip]')).map(el => el.getAttribute('data-chip')).join(',');
}
</script>
@endif

{{-- ══════════════════════ Site & SEO ══════════════════════ --}}
@if($section === 'seo')
<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
    @csrf
    <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'SEO & Métadonnées' : 'SEO & Metadata' }}</h2>
    <div>
        <label class="{{ $labelCls }}">{{ $isFr ? 'Titre méta (balise <title>)' : 'Meta title (<title> tag)' }}</label>
        <input type="text" name="settings[seo_meta_title]" class="{{ $inputCls }}"
               value="{{ $p('seo_meta_title', 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun') }}">
    </div>
    <div>
        <label class="{{ $labelCls }}">{{ $isFr ? 'Description méta' : 'Meta description' }}</label>
        <textarea name="settings[seo_meta_description]" rows="3" class="{{ $inputCls }}">{{ $p('seo_meta_description', $isFr ? 'Découvrez et achetez les produits de l\'artisanat camerounais auprès d\'artisans et de coopératives vérifiés.' : 'Discover and buy Cameroonian craft products from verified artisans and cooperatives.') }}</textarea>
    </div>
    <div>
        <label class="{{ $labelCls }}">{{ $isFr ? 'Mots-clés (séparés par des virgules)' : 'Keywords (comma separated)' }}</label>
        <input type="text" name="settings[seo_keywords]" class="{{ $inputCls }}"
               value="{{ $p('seo_keywords', $isFr ? 'artisanat, Cameroun, artisans, produits artisanaux' : 'crafts, Cameroon, artisans, handmade products') }}">
    </div>
    <button type="submit" class="{{ $greenBtn }}">
        <i data-lucide="check" class="w-4 h-4"></i>
        {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
    </button>
</form>
</div>
@endif

{{-- ══════════════════════ Notifications (Twilio — preserved) ══════════════════════ --}}
@if($section === 'notifications')
<div class="max-w-3xl bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="message-circle" class="w-4 h-4 text-forest-600"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Twilio — WhatsApp OTP</h3>
                <p class="text-xs text-gray-400">{{ $isFr ? 'Envoi des codes de connexion via WhatsApp' : 'Delivers login codes over WhatsApp' }}</p>
            </div>
        </div>
        @if(($twilio['configured'] ?? false))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
            <i data-lucide="check" class="w-3 h-3"></i>
            {{ ($twilio['from_env'] ?? false) ? ($isFr ? 'Configuré (.env)' : 'Configured (.env)') : ($isFr ? 'Configuré' : 'Configured') }}
        </span>
        @else
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
            {{ $isFr ? 'Non configuré' : 'Not configured' }}
        </span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.settings.twilio') }}" class="p-5 space-y-4 border-b border-gray-100">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Account SID</label>
                <input type="text" name="sid" autocomplete="off"
                    placeholder="{{ $twilio['sid_masked'] ?? 'ACxxxxxxxxxxxxxxxx' }}"
                    class="{{ $inputCls }} font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Auth Token</label>
                <input type="password" name="token" autocomplete="new-password"
                    placeholder="{{ $twilio['token_masked'] ?? '••••••••••••••••' }}"
                    class="{{ $inputCls }} font-mono">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $isFr ? 'Numéro expéditeur WhatsApp' : 'WhatsApp sender number' }}</label>
                <input type="tel" name="whatsapp_from" value="{{ $twilio['from'] ?? '' }}" placeholder="+14155238886"
                    class="{{ $inputCls }} font-mono">
            </div>
        </div>
        <p class="text-xs text-gray-400">
            {{ $isFr
                ? 'Les champs laissés vides conservent leur valeur actuelle. Les identifiants sont chiffrés en base et affichés masqués. Les valeurs saisies ici priment sur le fichier .env.'
                : 'Blank fields keep their current value. Credentials are encrypted at rest and shown masked. Values saved here override the .env file.' }}
        </p>
        <button type="submit" class="{{ $greenBtn }}">
            <i data-lucide="key-round" class="w-4 h-4"></i>
            {{ $isFr ? 'Enregistrer les identifiants' : 'Save credentials' }}
        </button>
    </form>

    <form method="POST" action="{{ route('admin.settings.twilio.test') }}" class="p-5 flex items-end gap-3">
        @csrf
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $isFr ? 'Tester la connexion — envoyer un message WhatsApp à' : 'Test connection — send a WhatsApp message to' }}</label>
            <input type="tel" name="test_phone" required placeholder="+2376XXXXXXXX"
                class="{{ $inputCls }} font-mono">
        </div>
        <button type="submit" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-colors whitespace-nowrap">
            {{ $isFr ? 'Envoyer le test' : 'Send test' }}
        </button>
    </form>
</div>
@endif

{{-- ══════════════════════ Apparence (logo / favicon — preserved) ══════════════════════ --}}
@if($section === 'branding')
<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data" class="{{ $cardCls }} space-y-6">
    @csrf

    <div>
        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $isFr ? 'Logo de la plateforme' : 'Platform logo' }}</h3>
        <div class="flex items-center gap-5">
            <img src="{{ $logoUrl }}" alt="Logo" class="w-24 h-24 rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
            <div class="min-w-0">
                <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-forest-300 text-forest-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-forest-50 transition-colors">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Changer le logo' : 'Change logo' }}
                    <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" class="hidden"
                           onchange="document.getElementById('branding-logo-name').textContent = this.files.length ? this.files[0].name : ''">
                </label>
                <p class="text-xs text-gray-400 mt-2">{{ $isFr ? 'PNG, JPG ou SVG. Taille recommandée : 512x512px' : 'PNG, JPG or SVG. Recommended size: 512x512px' }}</p>
                <p id="branding-logo-name" class="text-xs text-forest-600 mt-1 truncate"></p>
            </div>
        </div>
    </div>

    <div class="pt-5 border-t border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $isFr ? 'Logo favicon' : 'Favicon logo' }}</h3>
        <div class="flex items-center gap-5">
            <img src="{{ $faviconUrl }}" alt="Favicon" class="w-10 h-10 rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
            <div class="min-w-0">
                <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-forest-300 text-forest-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-forest-50 transition-colors">
                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Changer le favicon' : 'Change favicon' }}
                    <input type="file" name="favicon" accept=".png,.ico,.svg" class="hidden"
                           onchange="document.getElementById('branding-favicon-name').textContent = this.files.length ? this.files[0].name : ''">
                </label>
                <p class="text-xs text-gray-400 mt-2">{{ $isFr ? 'PNG, ICO ou SVG. Taille recommandée : 32x32px' : 'PNG, ICO or SVG. Recommended size: 32x32px' }}</p>
                <p id="branding-favicon-name" class="text-xs text-forest-600 mt-1 truncate"></p>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
        <a href="{{ route('admin.settings', array_filter(['section' => 'branding', 'lang' => request()->query('lang')])) }}"
           class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
            {{ $isFr ? 'Annuler' : 'Cancel' }}
        </a>
        <button type="submit" class="{{ $greenBtn }}">
            {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
        </button>
    </div>
</form>
</div>
@endif

{{-- ══════════════════════ Intégrations ══════════════════════ --}}
@if($section === 'integrations')
<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.settings.general') }}" class="{{ $cardCls }} space-y-4">
    @csrf
    <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Intégrations & Services Tiers' : 'Integrations & Third-party Services' }}</h2>
    <p class="text-xs text-gray-500">
        {{ $isFr
            ? 'Identifiants des services externes connectés à la plateforme. Les identifiants Twilio (WhatsApp OTP) se gèrent dans l\'onglet Notifications.'
            : 'Credentials for external services connected to the platform. Twilio credentials (WhatsApp OTP) are managed under the Notifications tab.' }}
    </p>
    <div>
        <label class="{{ $labelCls }}">Google Analytics ID</label>
        <input type="text" name="settings[integration_ga_id]" placeholder="G-XXXXXXXXXX" class="{{ $inputCls }} font-mono"
               value="{{ $p('integration_ga_id') }}">
    </div>
    <div>
        <label class="{{ $labelCls }}">Facebook Pixel ID</label>
        <input type="text" name="settings[integration_fb_pixel]" placeholder="123456789012345" class="{{ $inputCls }} font-mono"
               value="{{ $p('integration_fb_pixel') }}">
    </div>
    <div>
        <label class="{{ $labelCls }}">{{ $isFr ? 'Clé API Google Maps' : 'Google Maps API key' }}</label>
        <input type="text" name="settings[integration_maps_key]" placeholder="AIza..." class="{{ $inputCls }} font-mono"
               value="{{ $p('integration_maps_key') }}">
    </div>
    <button type="submit" class="{{ $greenBtn }}">
        <i data-lucide="check" class="w-4 h-4"></i>
        {{ $isFr ? 'Enregistrer les intégrations' : 'Save integrations' }}
    </button>
</form>
</div>
@endif

{{-- ══════════════════════ Système (langues + sauvegardes + system_settings hérités) ══════════════════════ --}}
@if($section === 'system')
<div class="max-w-3xl space-y-6">

    {{-- Langue par défaut (legacy admin.settings.update — preserved) --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ $cardCls }} space-y-4">
        @csrf
        <h2 class="text-[15px] font-bold text-gray-900">{{ $isFr ? 'Langues' : 'Languages' }}</h2>
        <div class="max-w-sm">
            <label class="{{ $labelCls }}">{{ $isFr ? 'Langue par défaut' : 'Default language' }}</label>
            <select name="settings[default_locale]" class="{{ $inputCls }} bg-white">
                <option value="fr" @selected($defaultLocale === 'fr')>{{ $isFr ? 'Français' : 'French' }}</option>
                <option value="en" @selected($defaultLocale === 'en')>{{ $isFr ? 'Anglais' : 'English' }}</option>
            </select>
            <p class="text-xs text-gray-400 mt-2">
                {{ $isFr
                    ? 'La plateforme est bilingue : chaque visiteur peut basculer entre français et anglais à tout moment.'
                    : 'The platform is bilingual: every visitor can switch between French and English at any time.' }}
            </p>
        </div>
        <button type="submit" class="{{ $greenBtn }}">
            {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
        </button>
    </form>

    {{-- Sauvegardes / audit (preserved) --}}
    <div class="{{ $cardCls }} space-y-4">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="database-backup" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">{{ $isFr ? 'Sauvegardes de la base de données' : 'Database backups' }}</h3>
        </div>
        <p class="text-sm text-gray-600">
            {{ $isFr
                ? 'Les sauvegardes de la base de données sont gérées au niveau de l\'infrastructure (sauvegarde quotidienne automatique du serveur). Toutes les actions d\'administration sont tracées dans le journal d\'audit et peuvent être consultées à tout moment.'
                : 'Database backups are handled at the infrastructure level (automatic daily server backup). Every admin action is recorded in the audit log and can be reviewed at any time.' }}
        </p>
        <a href="{{ route('admin.audit-log') }}" class="{{ $greenBtn }}">
            <i data-lucide="scroll-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Consulter le journal d\'audit' : 'View audit log' }}
        </a>
    </div>

</div>
@endif

{{-- ── system_settings group cards (existing admin.settings.update mechanism) ── --}}
@if(!empty($sysGroupsFor[$section] ?? []))
<div class="max-w-3xl space-y-6 {{ $section === 'system' ? 'mt-6' : '' }}">
    @foreach(collect($sysGroupsFor[$section])->filter(fn ($g) => $groups->has($g)) as $g)
    @php
        $rows = $g === 'app'
            ? $groups[$g]->reject(fn ($row) => $row['key'] === 'default_locale')
            : $groups[$g];
    @endphp
    @if($rows->isNotEmpty())
    <form method="POST" action="{{ route('admin.settings.update') }}"
          class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @csrf
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="{{ $groupMeta[$g]['icon'] ?? 'settings-2' }}" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h3 class="text-sm font-semibold text-gray-900">{{ $groupMeta[$g][$isFr ? 'fr' : 'en'] ?? ucfirst($g) }}</h3>
        </div>
        <div class="p-5 grid sm:grid-cols-2 gap-4">
            @foreach($rows as $setting)
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $labelize($setting['key']) }}</label>
                @if($setting['type'] === 'boolean')
                    <select name="settings[{{ $setting['key'] }}]" class="{{ $inputCls }} bg-white">
                        <option value="true"  @selected($setting['value'])>{{ $isFr ? 'Activé' : 'Enabled' }}</option>
                        <option value="false" @selected(! $setting['value'])>{{ $isFr ? 'Désactivé' : 'Disabled' }}</option>
                    </select>
                @elseif($setting['type'] === 'integer')
                    <input type="number" name="settings[{{ $setting['key'] }}]" value="{{ $setting['value'] }}" class="{{ $inputCls }}">
                @else
                    <input type="text" name="settings[{{ $setting['key'] }}]" value="{{ $setting['value'] }}" class="{{ $inputCls }}">
                @endif
            </div>
            @endforeach
        </div>
        <div class="px-5 pb-5">
            <button type="submit" class="{{ $greenBtn }}">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ $isFr ? 'Enregistrer' : 'Save' }}
            </button>
        </div>
    </form>
    @endif
    @endforeach
</div>
@endif

{{-- ── Footer (design) ─────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200 mt-8 pt-5 text-xs text-gray-500">
    <p>© 2025 Galerie Virtuelle Nationale de l'Artisanat du Cameroun. {{ $isFr ? 'Tous droits réservés.' : 'All rights reserved.' }}</p>
    <div class="flex items-center gap-6">
        <a href="{{ route('admin.support', array_filter(['lang' => request()->query('lang')])) }}" class="inline-flex items-center gap-1.5 hover:text-gray-700">
            <i data-lucide="circle-help" class="w-4 h-4"></i>
            {{ $isFr ? 'Centre d\'assistance' : 'Help centre' }}
        </a>
        <a href="{{ route('contact', array_filter(['lang' => request()->query('lang')])) }}" class="inline-flex items-center gap-1.5 hover:text-gray-700">
            <i data-lucide="mail" class="w-4 h-4"></i>
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
</div>

@endsection
