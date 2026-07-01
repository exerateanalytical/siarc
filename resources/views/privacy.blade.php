@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Politique de confidentialité' : 'Privacy Policy') . ' — Galerie Artisanat SIAC';
@endphp

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $lang === 'fr' ? 'Politique de confidentialité' : 'Privacy Policy' }}</h1>
    <p class="text-xs text-gray-400 mb-6">{{ $lang === 'fr' ? 'Dernière mise à jour : Juillet 2026' : 'Last updated: July 2026' }}</p>

    <div class="space-y-6 text-sm text-gray-600 leading-relaxed">
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">1. {{ $lang === 'fr' ? 'Données que nous collectons' : 'Data we collect' }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <tr class="border-b border-gray-200 font-semibold text-gray-700"><td class="py-1.5 pr-3">{{ $lang === 'fr' ? 'Catégorie' : 'Category' }}</td><td class="py-1.5">{{ $lang === 'fr' ? 'Exemples' : 'Examples' }}</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 pr-3">{{ $lang === 'fr' ? 'Compte' : 'Account' }}</td><td class="py-1.5">{{ $lang === 'fr' ? 'Nom, email, téléphone, mot de passe' : 'Name, email, phone, password' }}</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 pr-3">{{ $lang === 'fr' ? 'Entreprise' : 'Business' }}</td><td class="py-1.5">{{ $lang === 'fr' ? 'Nom commercial, adresse, logo, produits' : 'Business name, address, logo, products' }}</td></tr>
                    <tr class="border-b border-gray-100"><td class="py-1.5 pr-3">{{ $lang === 'fr' ? 'Messages' : 'Messages' }}</td><td class="py-1.5">{{ $lang === 'fr' ? 'Contenu des échanges entre acheteurs et vendeurs' : 'Content of exchanges between buyers and sellers' }}</td></tr>
                    <tr><td class="py-1.5 pr-3">{{ $lang === 'fr' ? 'Utilisation' : 'Usage' }}</td><td class="py-1.5">{{ $lang === 'fr' ? 'Pages visitées, recherches, type d\'appareil' : 'Pages visited, searches, device type' }}</td></tr>
                </table>
            </div>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">2. {{ $lang === 'fr' ? 'Utilisation des données' : 'How we use your data' }}</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>{{ $lang === 'fr' ? 'Faire fonctionner votre compte et votre profil d\'entreprise' : 'Operate your account and business profile' }}</li>
                <li>{{ $lang === 'fr' ? 'Transmettre les messages entre acheteurs et vendeurs' : 'Deliver messages between buyers and sellers' }}</li>
                <li>{{ $lang === 'fr' ? 'Envoyer des notifications liées à votre activité (messages, vérification)' : 'Send notifications related to your activity (messages, verification)' }}</li>
                <li>{{ $lang === 'fr' ? 'Améliorer la plateforme via des statistiques agrégées' : 'Improve the platform using aggregated analytics' }}</li>
            </ul>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">3. {{ $lang === 'fr' ? 'Partage des données' : 'Data sharing' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Nous ne vendons aucune donnée personnelle. Les informations de contact d\'une entreprise (téléphone, email, WhatsApp) sont visibles publiquement sur son profil, car la mise en relation directe est la fonction principale de la Plateforme.'
                : 'We do not sell personal data. A business\'s contact information (phone, email, WhatsApp) is publicly visible on its profile, since direct connection is the Platform\'s core function.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">4. {{ $lang === 'fr' ? 'Conservation des données' : 'Data retention' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Les données de compte et d\'entreprise sont conservées tant que le compte est actif. Les journaux d\'utilisation sont conservés 12 mois.'
                : 'Account and business data is retained while the account is active. Usage logs are retained for 12 months.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">5. {{ $lang === 'fr' ? 'Vos droits' : 'Your rights' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Vous pouvez demander l\'accès, la correction ou la suppression de vos données personnelles à tout moment en nous contactant.'
                : 'You may request access, correction, or deletion of your personal data at any time by contacting us.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">6. {{ $lang === 'fr' ? 'Sécurité' : 'Security' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Les mots de passe sont hachés (bcrypt) et les communications sont chiffrées (TLS). L\'accès aux documents privés est journalisé.'
                : 'Passwords are hashed (bcrypt) and communications are encrypted (TLS). Access to private documents is logged.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">7. {{ $lang === 'fr' ? 'Cookies' : 'Cookies' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Nous utilisons uniquement des cookies de session strictement nécessaires (authentification, préférence de langue). Aucun cookie publicitaire tiers.'
                : 'We only use strictly necessary session cookies (authentication, language preference). No third-party advertising cookies.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-gray-900 mb-1.5">8. {{ $lang === 'fr' ? 'Contact' : 'Contact' }}</h2>
            <p>{{ $lang === 'fr' ? 'Protection des données' : 'Data protection' }}: <a href="mailto:contact@artisanatcameroun.cm" class="text-forest-600 hover:underline">contact@artisanatcameroun.cm</a></p>
        </section>
    </div>
</div>
@endsection
