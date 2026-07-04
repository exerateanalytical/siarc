@extends('layouts.admin')

@php
$isFr = ($lang ?? 'fr') === 'fr';
$pageTitle = $isFr ? 'Paramètres Généraux' : 'General Settings';

// Platform key/value settings (prefill); the GET route passes $platform, but fall
// back to a direct query so the page still works with the legacy controller.
$platform = ($platform ?? null) ?: \Illuminate\Support\Facades\DB::table('platform_settings')->pluck('value', 'key');

// Active left-nav section
$validSections = ['general', 'branding', 'social', 'payments', 'notifications', 'seo', 'security', 'languages', 'maintenance', 'backups'];
$section = request()->query('section', 'general');
if (! in_array($section, $validSections, true)) $section = 'general';

$navItems = [
    'general'       => ['icon' => 'info',            'fr' => 'Informations Générales',  'en' => 'General Information'],
    'branding'      => ['icon' => 'palette',         'fr' => 'Identité Visuelle',       'en' => 'Visual Identity'],
    'social'        => ['icon' => 'share-2',         'fr' => 'Réseaux Sociaux',         'en' => 'Social Networks'],
    'payments'      => ['icon' => 'credit-card',     'fr' => 'Paiements',               'en' => 'Payments'],
    'notifications' => ['icon' => 'mail',            'fr' => 'Emails & Notifications',  'en' => 'Emails & Notifications'],
    'seo'           => ['icon' => 'search',          'fr' => 'SEO & Métadonnées',       'en' => 'SEO & Metadata'],
    'security'      => ['icon' => 'shield',          'fr' => 'Sécurité',                'en' => 'Security'],
    'languages'     => ['icon' => 'languages',       'fr' => 'Langues',                 'en' => 'Languages'],
    'maintenance'   => ['icon' => 'wrench',          'fr' => 'Maintenance',             'en' => 'Maintenance'],
    'backups'       => ['icon' => 'database-backup', 'fr' => 'Sauvegardes',             'en' => 'Backups'],
];

$sectionMeta = [
    'general'       => ['fr' => ['Informations Générales', 'Gérez les informations générales de la plateforme'],
                        'en' => ['General Information', 'Manage the general information of the platform']],
    'branding'      => ['fr' => ['Identité Visuelle', 'Logo et favicon affichés sur la plateforme'],
                        'en' => ['Visual Identity', 'Logo and favicon displayed across the platform']],
    'social'        => ['fr' => ['Réseaux Sociaux', 'Liens vers les profils officiels de la plateforme'],
                        'en' => ['Social Networks', 'Links to the platform\'s official profiles']],
    'payments'      => ['fr' => ['Paiements', 'Frais, taxes, transactions et limites de la plateforme'],
                        'en' => ['Payments', 'Fees, taxes, trading and platform limits']],
    'notifications' => ['fr' => ['Emails & Notifications', 'Canaux d\'envoi des notifications et codes de connexion'],
                        'en' => ['Emails & Notifications', 'Delivery channels for notifications and login codes']],
    'seo'           => ['fr' => ['SEO & Métadonnées', 'Métadonnées utilisées par les moteurs de recherche'],
                        'en' => ['SEO & Metadata', 'Metadata used by search engines']],
    'security'      => ['fr' => ['Sécurité', 'Paramètres d\'authentification et de protection des comptes'],
                        'en' => ['Security', 'Authentication and account protection settings']],
    'languages'     => ['fr' => ['Langues', 'Langue par défaut de la plateforme'],
                        'en' => ['Languages', 'Default language of the platform']],
    'maintenance'   => ['fr' => ['Maintenance', 'Mode maintenance, inscriptions et paramètres système'],
                        'en' => ['Maintenance', 'Maintenance mode, registrations and system settings']],
    'backups'       => ['fr' => ['Sauvegardes', 'Sauvegardes de la base de données et traçabilité'],
                        'en' => ['Backups', 'Database backups and audit trail']],
];

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

// Which system_settings groups render inside which left-nav section; any group
// not claimed below falls back to the Maintenance section so nothing is lost.
$sysGroupsFor = [
    'general'     => ['general'],
    'payments'    => ['payments', 'trading', 'limits'],
    'security'    => ['auth'],
    'maintenance' => array_values(array_unique(array_merge(
        ['app', 'siac'],
        $groups->keys()->reject(fn ($g) => in_array($g, ['general', 'payments', 'trading', 'limits', 'auth', 'app', 'siac'], true))->values()->all()
    ))),
];

$labelize = fn ($key) => ucfirst(str_replace('_', ' ', $key));
$inputCls = 'w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400';
$p = fn (string $key, string $default = '') => old('settings.' . $key, $platform[$key] ?? $default);

$logoUrl    = isset($platform['branding_logo'])    ? asset('storage/' . $platform['branding_logo'])    : asset('images/landing/logo.png');
$faviconUrl = isset($platform['branding_favicon']) ? asset('storage/' . $platform['branding_favicon']) : asset('images/landing/logo.png');

$defaultLocale = optional($groups->get('app'))?->firstWhere('key', 'default_locale')['value'] ?? 'fr';
@endphp

@section('content')

{{-- Breadcrumb : Accueil / Paramètres --}}
<nav class="flex items-center gap-1.5 text-xs text-gray-500 -mt-2 mb-5" aria-label="Breadcrumb">
    <a href="{{ route('dashboard.admin') }}" class="hover:text-forest-600">{{ $isFr ? 'Accueil' : 'Home' }}</a>
    <span class="text-gray-300">/</span>
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

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden flex flex-col lg:flex-row">

    {{-- Left sub-navigation --}}
    <aside class="lg:w-64 shrink-0 border-b lg:border-b-0 lg:border-r border-gray-100 p-3">
        <nav class="space-y-0.5">
            @foreach($navItems as $key => $item)
            <a href="{{ route('admin.settings', array_filter(['section' => $key, 'lang' => request()->query('lang')])) }}"
               class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium transition-colors {{ $section === $key ? 'bg-forest-50 text-forest-700' : 'text-gray-600 hover:bg-gray-50' }}">
                <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 shrink-0 {{ $section === $key ? 'text-forest-600' : 'text-gray-400' }}"></i>
                {{ $isFr ? $item['fr'] : $item['en'] }}
            </a>
            @endforeach
        </nav>
    </aside>

    {{-- Right panel --}}
    <div class="flex-1 min-w-0 p-6 space-y-6">

        <div>
            <h2 class="text-base font-bold text-gray-900">{{ $sectionMeta[$section][$isFr ? 'fr' : 'en'][0] }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $sectionMeta[$section][$isFr ? 'fr' : 'en'][1] }}</p>
        </div>

        {{-- ───────────── Informations Générales ───────────── --}}
        @if($section === 'general')
        <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Nom de la plateforme' : 'Platform name' }} <span class="text-red-500">*</span></label>
                <input type="text" name="settings[platform_name]" required class="{{ $inputCls }}"
                       value="{{ $p('platform_name', 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun') }}">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Slogan' : 'Tagline' }} <span class="text-red-500">*</span></label>
                <input type="text" name="settings[platform_slogan]" required class="{{ $inputCls }}"
                       value="{{ $p('platform_slogan', 'Notre Héritage, Notre Fierté, Notre Avenir') }}">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Description' : 'Description' }}</label>
                <textarea name="settings[platform_description]" rows="4" class="{{ $inputCls }}">{{ $p('platform_description', 'Plateforme officielle de promotion, de valorisation et de commercialisation des produits artisanaux du Cameroun. Nous connectons les artisans, les entreprises, les acheteurs et les passionnés d\'artisanat.') }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Email de contact' : 'Contact email' }} <span class="text-red-500">*</span></label>
                    <input type="email" name="settings[contact_email]" required class="{{ $inputCls }}"
                           value="{{ $p('contact_email', 'contact@galerie-artisanat.cm') }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                    <input type="tel" name="settings[contact_phone]" class="{{ $inputCls }}"
                           value="{{ $p('contact_phone', '+237 6 70 41 62 38') }}">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Adresse' : 'Address' }}</label>
                <input type="text" name="settings[contact_address]" class="{{ $inputCls }}"
                       value="{{ $p('contact_address', 'Yaoundé, Cameroun') }}">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Fuseau horaire' : 'Timezone' }}</label>
                    <select name="settings[timezone]" class="{{ $inputCls }} bg-white">
                        @foreach([
                            'Africa/Douala'    => $isFr ? '(UTC+01:00) Afrique/Cameroun' : '(UTC+01:00) Africa/Cameroon',
                            'UTC'              => '(UTC+00:00) UTC',
                            'Europe/Paris'     => $isFr ? '(UTC+01:00) Europe/Paris' : '(UTC+01:00) Europe/Paris',
                            'America/New_York' => $isFr ? '(UTC-05:00) Amérique/New York' : '(UTC-05:00) America/New York',
                        ] as $tz => $tzLabel)
                        <option value="{{ $tz }}" @selected($p('timezone', 'Africa/Douala') === $tz)>{{ $tzLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Devise' : 'Currency' }}</label>
                    <select name="settings[currency]" class="{{ $inputCls }} bg-white">
                        @foreach([
                            'XAF' => $isFr ? 'Franc CFA (XAF)' : 'CFA Franc (XAF)',
                            'EUR' => 'Euro (EUR)',
                            'USD' => $isFr ? 'Dollar US (USD)' : 'US Dollar (USD)',
                        ] as $cur => $curLabel)
                        <option value="{{ $cur }}" @selected($p('currency', 'XAF') === $cur)>{{ $curLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Logo de la plateforme --}}
            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $isFr ? 'Logo de la plateforme' : 'Platform logo' }}</h3>
                <div class="flex items-center gap-5">
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-24 h-24 rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
                    <div class="min-w-0">
                        <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-forest-300 text-forest-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-forest-50 transition-colors">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            {{ $isFr ? 'Changer le logo' : 'Change logo' }}
                            <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" class="hidden"
                                   onchange="document.getElementById('logo-file-name').textContent = this.files.length ? this.files[0].name : ''">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">{{ $isFr ? 'PNG, JPG ou SVG. Taille recommandée : 512x512px' : 'PNG, JPG or SVG. Recommended size: 512x512px' }}</p>
                        <p id="logo-file-name" class="text-xs text-forest-600 mt-1 truncate"></p>
                    </div>
                </div>
            </div>

            {{-- Logo favicon --}}
            <div class="pt-5 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ $isFr ? 'Logo favicon' : 'Favicon logo' }}</h3>
                <div class="flex items-center gap-5">
                    <img src="{{ $faviconUrl }}" alt="Favicon" class="w-10 h-10 rounded-full object-cover border border-gray-200 bg-gray-50 shrink-0">
                    <div class="min-w-0">
                        <label class="inline-flex items-center gap-2 px-3.5 py-2 border border-forest-300 text-forest-700 rounded-lg text-xs font-semibold cursor-pointer hover:bg-forest-50 transition-colors">
                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                            {{ $isFr ? 'Changer le favicon' : 'Change favicon' }}
                            <input type="file" name="favicon" accept=".png,.ico,.svg" class="hidden"
                                   onchange="document.getElementById('favicon-file-name').textContent = this.files.length ? this.files[0].name : ''">
                        </label>
                        <p class="text-xs text-gray-400 mt-2">{{ $isFr ? 'PNG, ICO ou SVG. Taille recommandée : 32x32px' : 'PNG, ICO or SVG. Recommended size: 32x32px' }}</p>
                        <p id="favicon-file-name" class="text-xs text-forest-600 mt-1 truncate"></p>
                    </div>
                </div>
            </div>

            {{-- Footer actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.settings', array_filter(['section' => 'general', 'lang' => request()->query('lang')])) }}"
                   class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                </button>
            </div>
        </form>
        @endif

        {{-- ───────────── Identité Visuelle ───────────── --}}
        @if($section === 'branding')
        <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data" class="space-y-6">
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
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                </button>
            </div>
        </form>
        @endif

        {{-- ───────────── Réseaux Sociaux ───────────── --}}
        @if($section === 'social')
        <form method="POST" action="{{ route('admin.settings.general') }}" class="space-y-4">
            @csrf
            @foreach([
                'social_facebook'  => ['facebook',  'Facebook',  'https://facebook.com/...'],
                'social_instagram' => ['instagram', 'Instagram', 'https://instagram.com/...'],
                'social_twitter'   => ['twitter',   'X (Twitter)', 'https://x.com/...'],
                'social_youtube'   => ['youtube',   'YouTube',   'https://youtube.com/...'],
                'social_linkedin'  => ['linkedin',  'LinkedIn',  'https://linkedin.com/company/...'],
                'social_whatsapp'  => ['message-circle', 'WhatsApp', '+237 6XX XX XX XX'],
            ] as $key => [$icon, $label, $placeholder])
            <div>
                <label class="flex items-center gap-1.5 text-xs font-medium text-gray-600 mb-1.5">
                    <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5 text-gray-400"></i> {{ $label }}
                </label>
                <input type="text" name="settings[{{ $key }}]" placeholder="{{ $placeholder }}" class="{{ $inputCls }}"
                       value="{{ $p($key) }}">
            </div>
            @endforeach

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.settings', array_filter(['section' => 'social', 'lang' => request()->query('lang')])) }}"
                   class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                </button>
            </div>
        </form>
        @endif

        {{-- ───────────── SEO & Métadonnées ───────────── --}}
        @if($section === 'seo')
        <form method="POST" action="{{ route('admin.settings.general') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Titre méta (balise <title>)' : 'Meta title (<title> tag)' }}</label>
                <input type="text" name="settings[seo_meta_title]" class="{{ $inputCls }}"
                       value="{{ $p('seo_meta_title', 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun') }}">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Description méta' : 'Meta description' }}</label>
                <textarea name="settings[seo_meta_description]" rows="3" class="{{ $inputCls }}">{{ $p('seo_meta_description', $isFr ? 'Découvrez et achetez les produits de l\'artisanat camerounais auprès d\'artisans et de coopératives vérifiés.' : 'Discover and buy Cameroonian craft products from verified artisans and cooperatives.') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Mots-clés (séparés par des virgules)' : 'Keywords (comma separated)' }}</label>
                <input type="text" name="settings[seo_keywords]" class="{{ $inputCls }}"
                       value="{{ $p('seo_keywords', $isFr ? 'artisanat, Cameroun, artisans, produits artisanaux' : 'crafts, Cameroon, artisans, handmade products') }}">
            </div>

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.settings', array_filter(['section' => 'seo', 'lang' => request()->query('lang')])) }}"
                   class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                </button>
            </div>
        </form>
        @endif

        {{-- ───────────── Emails & Notifications (Twilio — preserved) ───────────── --}}
        @if($section === 'notifications')
        <div class="border border-gray-200 rounded-xl overflow-hidden">
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
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
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

        {{-- ───────────── Langues ───────────── --}}
        @if($section === 'languages')
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf
            <div class="max-w-sm">
                <label class="block text-xs font-medium text-gray-600 mb-1.5">{{ $isFr ? 'Langue par défaut' : 'Default language' }}</label>
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

            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('admin.settings', array_filter(['section' => 'languages', 'lang' => request()->query('lang')])) }}"
                   class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold hover:bg-gray-200 transition-colors">
                    {{ $isFr ? 'Annuler' : 'Cancel' }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    {{ $isFr ? 'Enregistrer les modifications' : 'Save changes' }}
                </button>
            </div>
        </form>
        @endif

        {{-- ───────────── Sauvegardes ───────────── --}}
        @if($section === 'backups')
        <div class="border border-gray-200 rounded-xl p-5 space-y-4">
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
            <a href="{{ route('admin.audit-log') }}" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                <i data-lucide="scroll-text" class="w-4 h-4"></i>
                {{ $isFr ? 'Consulter le journal d\'audit' : 'View audit log' }}
            </a>
        </div>
        @endif

        {{-- ───────────── system_settings group cards (existing admin.settings.update mechanism) ───────────── --}}
        @foreach(collect($sysGroupsFor[$section] ?? [])->filter(fn ($g) => $groups->has($g)) as $g)
        @php
            $rows = $g === 'app'
                ? $groups[$g]->reject(fn ($row) => $row['key'] === 'default_locale')
                : $groups[$g];
        @endphp
        @if($rows->isNotEmpty())
        <form method="POST" action="{{ route('admin.settings.update') }}"
              class="border border-gray-200 rounded-xl overflow-hidden {{ $section === 'general' ? 'mt-2' : '' }}">
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
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    {{ $isFr ? 'Enregistrer' : 'Save' }}
                </button>
            </div>
        </form>
        @endif
        @endforeach

    </div>
</div>
@endsection
