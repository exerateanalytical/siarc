@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Conditions d\'utilisation' : 'Terms of Service') . ' — Galerie Artisanat SIARC';
@endphp

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-xl font-bold text-[#1B1B18] mb-1">{{ $lang === 'fr' ? 'Conditions d\'utilisation' : 'Terms of Service' }}</h1>
    <p class="text-xs text-[#A8A296] mb-6">{{ $lang === 'fr' ? 'Dernière mise à jour : Juillet 2026' : 'Last updated: July 2026' }}</p>

    <div class="space-y-6 text-sm text-[#6F6B60] leading-relaxed">
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">1. {{ $lang === 'fr' ? 'Acceptation' : 'Acceptance' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'En accédant à la Galerie Virtuelle Nationale de l\'Artisanat (« la Plateforme »), vous acceptez ces conditions d\'utilisation et les lois applicables au Cameroun.'
                : 'By accessing the National Virtual Gallery of Crafts ("the Platform"), you agree to these Terms of Service and applicable Cameroonian law.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">2. {{ $lang === 'fr' ? 'Description de la plateforme' : 'Platform description' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'La Plateforme est un annuaire d\'entreprises et une vitrine de produits camerounais. Elle ne propose ni paiement, ni panier, ni transaction en ligne, ni service de courtage ou d\'investissement. Les prix ne sont pas affichés publiquement ; toute négociation se fait directement entre acheteur et vendeur, en dehors de la Plateforme.'
                : 'The Platform is a business directory and product showcase for Cameroonian businesses. It offers no payments, shopping cart, online transactions, brokerage, or investment services. Prices are not shown publicly; all negotiation happens directly between buyer and seller, off-platform.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">3. {{ $lang === 'fr' ? 'Comptes et contenu' : 'Accounts and content' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Les entreprises sont responsables de l\'exactitude des informations et images qu\'elles publient. La Plateforme se réserve le droit de suspendre tout profil ou produit signalé comme trompeur, frauduleux, ou inapproprié.'
                : 'Businesses are responsible for the accuracy of the information and images they publish. The Platform reserves the right to suspend any profile or product reported as misleading, fraudulent, or inappropriate.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">4. {{ $lang === 'fr' ? 'Utilisations interdites' : 'Prohibited uses' }}</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>{{ $lang === 'fr' ? 'Fournir des informations fausses ou trompeuses' : 'Providing false or misleading information' }}</li>
                <li>{{ $lang === 'fr' ? 'Copier ou extraire les données du site sans autorisation écrite' : 'Scraping or copying platform data without written permission' }}</li>
                <li>{{ $lang === 'fr' ? 'Utiliser la messagerie à des fins de spam ou de harcèlement' : 'Using the messaging system for spam or harassment' }}</li>
            </ul>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">5. {{ $lang === 'fr' ? 'Propriété intellectuelle' : 'Intellectual property' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'Le contenu soumis par une entreprise (textes, photos, produits) reste sa propriété. La marque, le design et le logiciel de la Plateforme appartiennent à ses éditeurs.'
                : 'Content submitted by a business (text, photos, products) remains its property. The Platform\'s brand, design, and software belong to its operators.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">6. {{ $lang === 'fr' ? 'Limitation de responsabilité' : 'Limitation of liability' }}</h2>
            <p>{{ $lang === 'fr'
                ? 'La Plateforme ne participe à aucune transaction et ne peut être tenue responsable des accords conclus entre acheteurs et vendeurs hors plateforme.'
                : 'The Platform is not party to any transaction and cannot be held responsible for agreements reached between buyers and sellers off-platform.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">7. {{ $lang === 'fr' ? 'Droit applicable' : 'Governing law' }}</h2>
            <p>{{ $lang === 'fr' ? 'Ces conditions sont régies par le droit camerounais.' : 'These Terms are governed by the laws of Cameroon.' }}</p>
        </section>
        <section>
            <h2 class="text-sm font-semibold text-[#1B1B18] mb-1.5">8. {{ $lang === 'fr' ? 'Contact' : 'Contact' }}</h2>
            <p>{{ $lang === 'fr' ? 'Questions juridiques' : 'Legal inquiries' }}: <a href="mailto:contact@artisanatcameroun.cm" class="text-forest-600 hover:underline">contact@artisanatcameroun.cm</a></p>
        </section>
    </div>
</div>
@endsection
