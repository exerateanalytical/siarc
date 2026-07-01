@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'À propos' : 'About') . ' — Galerie Artisanat SIAC Cameroun';
@endphp

@section('content')
<div class="bg-gradient-to-br from-forest-600 to-forest-700 text-white">
    <div class="max-w-3xl mx-auto px-4 py-12 text-center">
        <h1 class="text-2xl sm:text-3xl font-bold mb-3">
            {{ $lang === 'fr' ? 'Galerie Virtuelle Nationale de l\'Artisanat' : 'National Virtual Gallery of Crafts' }}
        </h1>
        <p class="text-white/80 text-sm sm:text-base">
            {{ $lang === 'fr'
                ? 'La plateforme officielle de découverte des entreprises camerounaises, préparée pour le SIAC 2026.'
                : 'The official discovery platform for Cameroonian businesses, built for SIAC 2026.' }}
        </p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-10 space-y-10">

    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-forest-600">{{ number_format(\DB::table('businesses')->where('status','published')->count()) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-forest-600">{{ number_format(\DB::table('products')->where('status','published')->count()) }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-forest-600">10</p>
            <p class="text-xs text-gray-500 mt-1">{{ $lang === 'fr' ? 'Régions' : 'Regions' }}</p>
        </div>
    </div>

    <div>
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i data-lucide="target" class="w-4 h-4 text-forest-600"></i>
            {{ $lang === 'fr' ? 'Notre mission' : 'Our mission' }}
        </h2>
        <p class="text-sm text-gray-600 leading-relaxed mb-3">
            {{ $lang === 'fr'
                ? 'Permettre à chaque entrepreneur, coopérative et artisan camerounais de présenter ses produits à des acheteurs, investisseurs et distributeurs partout dans le monde — et à la diaspora camerounaise de découvrir et contacter directement les producteurs du pays.'
                : 'Let every Cameroonian entrepreneur, cooperative, and artisan showcase their products to buyers, investors, and distributors worldwide — and let the Cameroonian diaspora discover and contact producers back home directly.' }}
        </p>
        <p class="text-sm text-gray-600 leading-relaxed">
            {{ $lang === 'fr'
                ? 'Préparée pour le Salon International Interprofessionnel de l\'Aquaculture du Cameroun (SIAC), organisé sous la supervision du MINEPIA et de l\'OIDAC, la plateforme couvre aussi l\'artisanat, l\'agriculture, le textile et l\'agroalimentaire.'
                : 'Built for the Salon International Interprofessionnel de l\'Aquaculture du Cameroun (SIAC), organized under MINEPIA and OIDAC supervision, the platform also covers crafts, agriculture, textiles, and food processing.' }}
        </p>
    </div>

    <div>
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4 text-forest-600"></i>
            {{ $lang === 'fr' ? 'Ce que nous proposons' : 'What we offer' }}
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @php
            $offers = [
                ['building-2', $lang === 'fr' ? 'Annuaire d\'entreprises' : 'Business directory', $lang === 'fr' ? 'Parcourez les entreprises par secteur, région et niveau de vérification.' : 'Browse businesses by industry, region, and verification tier.'],
                ['package', $lang === 'fr' ? 'Vitrine produits' : 'Product showcase', $lang === 'fr' ? 'Fiches produits détaillées avec photos, spécifications et documents.' : 'Detailed product pages with photos, specifications, and documents.'],
                ['message-circle', $lang === 'fr' ? 'Contact direct' : 'Direct contact', $lang === 'fr' ? 'Messagerie, appel et WhatsApp — sans intermédiaire, sans transaction en ligne.' : 'Messaging, call, and WhatsApp — no middleman, no online transaction.'],
                ['code-2', $lang === 'fr' ? 'API publique' : 'Public API', $lang === 'fr' ? 'Nos données sont accessibles via une API REST documentée pour les développeurs.' : 'Our data is accessible via a documented REST API for developers.'],
            ];
            @endphp
            @foreach($offers as [$icon, $heading, $desc])
            <div class="border-l-2 border-forest-500 bg-white rounded-r-lg p-3.5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-1.5 mb-1"><i data-lucide="{{ $icon }}" class="w-3.5 h-3.5 text-forest-600"></i>{{ $heading }}</h3>
                <p class="text-xs text-gray-500">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div>
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4 text-forest-600"></i>
            {{ $lang === 'fr' ? 'Ce que nous ne faisons pas' : 'What we don\'t do' }}
        </h2>
        <p class="text-sm text-gray-600 leading-relaxed">
            {{ $lang === 'fr'
                ? 'La plateforme n\'effectue aucun paiement, aucune transaction et n\'agit jamais comme intermédiaire commercial. Les prix ne sont pas affichés publiquement : chaque échange commercial se fait directement entre l\'acheteur et le vendeur, hors plateforme.'
                : 'The platform never processes payments or transactions, and never acts as a commercial intermediary. Prices are not shown publicly: every commercial exchange happens directly between buyer and seller, off-platform.' }}
        </p>
    </div>

    <div>
        <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <i data-lucide="mail" class="w-4 h-4 text-forest-600"></i>
            {{ $lang === 'fr' ? 'Contact' : 'Contact' }}
        </h2>
        <p class="text-sm text-gray-600">
            Email: <a href="mailto:contact@artisanatcameroun.cm" class="text-forest-600 hover:underline">contact@artisanatcameroun.cm</a><br>
            {{ $lang === 'fr' ? 'Adresse' : 'Address' }}: Yaoundé, Cameroun
        </p>
    </div>

</div>
@endsection
