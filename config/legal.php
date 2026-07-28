<?php

/*
|--------------------------------------------------------------------------
| Legal & policy documents
|--------------------------------------------------------------------------
|
| Artisan Hub 237 is a private marketplace operator, not a public body. These
| documents describe what the company actually does: it introduces buyers to
| artisans, hosts the quotation exchange between them, and takes no part in
| the sale itself. Keeping them in config (rather than hardcoded in Blade)
| means one template renders them all and the wording stays reviewable in one
| place.
|
| Company identity below is intentionally kept in env so a deployment can set
| its real registration details without a code change.
|
*/

return [

    'updated_at' => '2026-07',

    'company' => [
        'name'    => env('LEGAL_COMPANY_NAME', 'Artisan Hub 237'),
        'form'    => env('LEGAL_COMPANY_FORM', 'SARL'),
        'reg'     => env('LEGAL_COMPANY_REG', ''),          // RCCM
        'tax'     => env('LEGAL_COMPANY_TAX', ''),          // Numéro de contribuable
        'address' => env('LEGAL_COMPANY_ADDRESS', 'Douala, Littoral, Cameroun'),
        'email'   => env('LEGAL_COMPANY_EMAIL', 'contact@artisanhub237.com'),
        'phone'   => env('LEGAL_COMPANY_PHONE', ''),
        'host'    => env('LEGAL_HOSTING_PROVIDER', ''),
    ],

    /*
     * Social profiles. Left blank the icons simply do not render — better than
     * linking members to an account that does not exist.
     */
    'social' => [
        'facebook'  => env('SOCIAL_FACEBOOK'),
        'instagram' => env('SOCIAL_INSTAGRAM'),
        'linkedin'  => env('SOCIAL_LINKEDIN'),
        'youtube'   => env('SOCIAL_YOUTUBE'),
        'x'         => env('SOCIAL_X'),
    ],

    'documents' => [

        // ─────────────────────────────────────────────────────────────
        'conditions' => [
            'title' => ['fr' => "Conditions d'utilisation", 'en' => 'Terms of Service'],
            'intro' => [
                'fr' => "Ces conditions régissent l'utilisation d'Artisan Hub 237. En créant un compte ou en utilisant la plateforme, vous les acceptez.",
                'en' => 'These terms govern the use of Artisan Hub 237. By creating an account or using the platform, you accept them.',
            ],
            'sections' => [
                [
                    'heading' => ['fr' => 'Qui nous sommes', 'en' => 'Who we are'],
                    'body' => [
                        'fr' => "Artisan Hub 237 est une plateforme éditée par une société privée de droit camerounais. Nous ne sommes ni une administration, ni un service public, et nous n'agissons pour le compte d'aucun ministère ou organisme public.",
                        'en' => 'Artisan Hub 237 is a platform operated by a private company incorporated in Cameroon. We are not a public administration or public service, and we do not act on behalf of any ministry or public body.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Ce que fait la plateforme', 'en' => 'What the platform does'],
                    'body' => [
                        'fr' => "Artisan Hub 237 met en relation des acheteurs et des artisans, entreprises et producteurs camerounais. La plateforme héberge les profils, les catalogues de produits, la messagerie, et le circuit de devis (demande de devis, proposition, bon de commande, facture).",
                        'en' => 'Artisan Hub 237 connects buyers with Cameroonian artisans, businesses and producers. The platform hosts profiles, product catalogues, messaging, and the quotation flow (request for quote, proposal, purchase order, invoice).',
                    ],
                    'list' => [
                        'fr' => [
                            "Nous ne sommes pas partie au contrat de vente conclu entre l'acheteur et le vendeur.",
                            "Nous ne recevons pas le prix de la vente : les documents générés (bon de commande, facture) sont des documents commerciaux entre les parties, et le règlement se fait directement entre elles. Seuls nos propres frais de service nous sont réglés.",
                            "Nous ne fabriquons, ne stockons, n'inspectons et n'expédions aucun produit.",
                        ],
                        'en' => [
                            'We are not a party to the sales contract concluded between buyer and seller.',
                            'We do not receive the price of the sale: the documents generated (purchase order, invoice) are commercial documents between the parties, and settlement happens directly between them. Only our own service fees are paid to us.',
                            'We do not manufacture, store, inspect or ship any product.',
                        ],
                    ],
                ],
                [
                    'heading' => ['fr' => 'Comptes', 'en' => 'Accounts'],
                    'body' => [
                        'fr' => "Vous devez fournir des informations exactes et garder vos identifiants confidentiels. Vous êtes responsable de toute activité effectuée depuis votre compte. Un compte vendeur doit correspondre à une activité réelle et à une identité vérifiable.",
                        'en' => 'You must provide accurate information and keep your credentials confidential. You are responsible for all activity carried out from your account. A seller account must correspond to a real business and a verifiable identity.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Contenu publié par les vendeurs', 'en' => 'Seller-published content'],
                    'body' => [
                        'fr' => "Les vendeurs sont seuls responsables de l'exactitude de leurs fiches, prix, descriptions, photos et délais. Nous pouvons suspendre ou retirer tout contenu signalé comme trompeur, contrefait, illégal ou inapproprié, sans préavis.",
                        'en' => 'Sellers are solely responsible for the accuracy of their listings, prices, descriptions, photos and lead times. We may suspend or remove any content reported as misleading, counterfeit, unlawful or inappropriate, without notice.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Badges de vérification', 'en' => 'Verification badges'],
                    'body' => [
                        'fr' => "Un badge « vérifié » indique que nous avons reçu et contrôlé certains documents fournis par le vendeur. Ce n'est ni une garantie de qualité des produits, ni une garantie de bonne exécution d'une commande, ni une caution financière.",
                        'en' => 'A "verified" badge means we have received and checked certain documents supplied by the seller. It is not a guarantee of product quality, of proper performance of an order, or a financial guarantee.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Utilisations interdites', 'en' => 'Prohibited uses'],
                    'list' => [
                        'fr' => [
                            'Fournir des informations fausses, trompeuses ou usurper une identité',
                            "Publier des contrefaçons ou des produits dont la vente est réglementée ou interdite",
                            'Extraire ou copier automatiquement les données de la plateforme sans autorisation écrite',
                            'Utiliser la messagerie pour du spam, du démarchage massif ou du harcèlement',
                            "Tenter de contourner les contrôles de sécurité ou d'accéder aux données d'autrui",
                        ],
                        'en' => [
                            'Providing false or misleading information, or impersonating others',
                            'Listing counterfeit goods or products whose sale is restricted or prohibited',
                            'Automatically scraping or copying platform data without written permission',
                            'Using messaging for spam, mass solicitation or harassment',
                            "Attempting to bypass security controls or access other people's data",
                        ],
                    ],
                    'body' => ['fr' => '', 'en' => ''],
                ],
                [
                    'heading' => ['fr' => 'Suspension et résiliation', 'en' => 'Suspension and termination'],
                    'body' => [
                        'fr' => "Nous pouvons suspendre ou fermer un compte en cas de manquement à ces conditions. Vous pouvez fermer votre compte à tout moment en nous contactant ; les documents commerciaux déjà émis restent conservés selon les durées légales.",
                        'en' => 'We may suspend or close an account in the event of a breach of these terms. You may close your account at any time by contacting us; commercial documents already issued are retained for the legally required periods.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Responsabilité', 'en' => 'Liability'],
                    'body' => [
                        'fr' => "La plateforme est fournie « en l'état ». Dans la limite permise par la loi, notre responsabilité ne peut être engagée pour les litiges commerciaux entre acheteurs et vendeurs, la qualité ou la conformité des produits, les retards de livraison, ou les pertes indirectes.",
                        'en' => 'The platform is provided "as is". To the extent permitted by law, we are not liable for commercial disputes between buyers and sellers, product quality or conformity, delivery delays, or indirect losses.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Droit applicable', 'en' => 'Governing law'],
                    'body' => [
                        'fr' => "Ces conditions sont régies par le droit camerounais. Nous privilégions un règlement amiable de tout différend avant toute procédure judiciaire.",
                        'en' => 'These terms are governed by Cameroonian law. We favour an amicable resolution of any dispute before any legal proceedings.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Modifications', 'en' => 'Changes'],
                    'body' => [
                        'fr' => "Nous pouvons modifier ces conditions. La date de dernière mise à jour figure en haut de cette page ; l'usage continu de la plateforme après modification vaut acceptation.",
                        'en' => 'We may amend these terms. The last-updated date appears at the top of this page; continued use of the platform after a change constitutes acceptance.',
                    ],
                ],
            ],
        ],

        // ─────────────────────────────────────────────────────────────
        'confidentialite' => [
            'title' => ['fr' => 'Politique de confidentialité', 'en' => 'Privacy Policy'],
            'intro' => [
                'fr' => "Cette politique explique quelles données nous collectons, pourquoi, et quels sont vos droits.",
                'en' => 'This policy explains what data we collect, why, and what your rights are.',
            ],
            'sections' => [
                [
                    'heading' => ['fr' => 'Données que nous collectons', 'en' => 'Data we collect'],
                    'list' => [
                        'fr' => [
                            "Compte : nom, adresse email, numéro de téléphone, mot de passe (stocké sous forme de hachage, jamais en clair)",
                            "Entreprise : raison sociale, secteur, région, logo, documents de vérification que vous téléversez",
                            "Activité : produits publiés, demandes de devis, propositions, commandes, messages",
                            "Technique : adresse IP, type de navigateur, pages consultées, à des fins de sécurité et de statistiques",
                        ],
                        'en' => [
                            'Account: name, email address, phone number, password (stored hashed, never in clear text)',
                            'Business: legal name, sector, region, logo, verification documents you upload',
                            'Activity: published products, quote requests, proposals, orders, messages',
                            'Technical: IP address, browser type, pages visited, for security and analytics purposes',
                        ],
                    ],
                    'body' => ['fr' => '', 'en' => ''],
                ],
                [
                    'heading' => ['fr' => 'Pourquoi nous les utilisons', 'en' => 'Why we use it'],
                    'body' => [
                        'fr' => "Pour faire fonctionner votre compte, afficher votre boutique, acheminer vos messages et devis, vérifier les entreprises, prévenir la fraude, et vous notifier d'événements qui vous concernent. Nous ne vendons pas vos données.",
                        'en' => 'To run your account, display your shop, route your messages and quotes, verify businesses, prevent fraud, and notify you of events that concern you. We do not sell your data.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Ce qui est public', 'en' => 'What is public'],
                    'body' => [
                        'fr' => "Une fiche entreprise publiée et ses produits sont visibles par tous, y compris les moteurs de recherche. Vos messages privés, vos devis, vos commandes et vos documents de vérification ne le sont pas.",
                        'en' => 'A published business profile and its products are visible to everyone, including search engines. Your private messages, quotes, orders and verification documents are not.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Partage', 'en' => 'Sharing'],
                    'body' => [
                        'fr' => "Nous partageons vos données uniquement avec : l'autre partie d'une transaction que vous engagez (un vendeur voit le nom de l'acheteur qui lui écrit), nos prestataires techniques (hébergement, envoi d'emails), et les autorités lorsque la loi l'exige.",
                        'en' => 'We share your data only with: the other party to a transaction you initiate (a seller sees the name of the buyer who contacts them), our technical providers (hosting, email delivery), and authorities where required by law.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Conservation', 'en' => 'Retention'],
                    'body' => [
                        'fr' => "Nous conservons vos données tant que votre compte est actif. Après fermeture, les documents commerciaux (bons de commande, factures) sont conservés pour la durée légale, puis supprimés.",
                        'en' => 'We keep your data while your account is active. After closure, commercial documents (purchase orders, invoices) are retained for the legally required period, then deleted.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Vos droits', 'en' => 'Your rights'],
                    'body' => [
                        'fr' => "Vous pouvez consulter et corriger vos données depuis votre profil, demander une copie de vos données, ou demander la suppression de votre compte en nous écrivant. Nous répondons dans un délai raisonnable.",
                        'en' => 'You can view and correct your data from your profile, request a copy of your data, or request deletion of your account by writing to us. We respond within a reasonable time.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Sécurité', 'en' => 'Security'],
                    'body' => [
                        'fr' => "Les mots de passe sont hachés, les sessions sont protégées, et l'accès aux données est restreint. Aucun système n'est infaillible : signalez-nous immédiatement toute activité suspecte sur votre compte.",
                        'en' => 'Passwords are hashed, sessions are protected, and data access is restricted. No system is infallible: report any suspicious activity on your account to us immediately.',
                    ],
                ],
            ],
        ],

        // ─────────────────────────────────────────────────────────────
        'avertissement' => [
            'title' => ['fr' => 'Avertissement', 'en' => 'Disclaimer'],
            'intro' => [
                'fr' => "À lire avant d'engager une transaction sur Artisan Hub 237.",
                'en' => 'Please read before entering into a transaction on Artisan Hub 237.',
            ],
            'sections' => [
                [
                    'heading' => ['fr' => 'Nous sommes un intermédiaire', 'en' => 'We are an intermediary'],
                    'body' => [
                        'fr' => "Artisan Hub 237 met en relation des acheteurs et des vendeurs indépendants. Chaque vente est un contrat direct entre eux. Nous n'en sommes pas partie, nous ne la garantissons pas, et nous n'en recevons pas le prix. Nos propres frais de service sont distincts et nous sont réglés directement.",
                        'en' => 'Artisan Hub 237 connects buyers with independent sellers. Each sale is a direct contract between them. We are not a party to it, we do not guarantee it, and we do not receive the price. Our own service fees are separate and are paid to us directly.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Aucune affiliation publique', 'en' => 'No public affiliation'],
                    'body' => [
                        'fr' => "Nous sommes une entreprise privée. Nous ne sommes affiliés à, mandatés par, ni approuvés par un ministère, une agence publique ou une autorité gouvernementale. Aucun contenu de ce site ne doit être interprété comme une position officielle.",
                        'en' => 'We are a private company. We are not affiliated with, mandated by, or approved by any ministry, public agency or government authority. No content on this site should be read as an official position.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Paiements', 'en' => 'Payments'],
                    'body' => [
                        'fr' => "La plateforme ne reçoit pas le prix de la vente et ne propose aucun service de séquestre. Les bons de commande et factures générés servent à formaliser l'accord entre les parties ; le règlement s'effectue directement entre acheteur et vendeur, par les moyens qu'ils choisissent. Nos propres frais de service (inscription, adhésion, vérification) sont distincts et nous sont réglés directement.",
                        'en' => 'The platform does not receive the price of the sale and offers no escrow service. Purchase orders and invoices generated serve to formalise the agreement between the parties; settlement takes place directly between buyer and seller, by whatever means they choose. Our own service fees (registration, membership, verification) are separate and are paid to us directly.',
                    ],
                    'list' => [
                        'fr' => [
                            "Vérifiez l'identité et les coordonnées de votre interlocuteur avant tout versement.",
                            "Méfiez-vous de toute demande de paiement vers un compte différent de celui convenu.",
                            "Conservez une trace écrite de vos échanges : la messagerie de la plateforme y sert.",
                        ],
                        'en' => [
                            'Verify the identity and details of the other party before making any payment.',
                            'Be wary of any request to pay into an account other than the one agreed.',
                            'Keep a written record of your exchanges: the platform messaging is there for that.',
                        ],
                    ],
                ],
                [
                    'heading' => ['fr' => 'Exactitude des informations', 'en' => 'Accuracy of information'],
                    'body' => [
                        'fr' => "Les descriptions, photos, prix, délais et disponibilités sont fournis par les vendeurs. Nous ne les contrôlons pas un par un et ne pouvons en garantir l'exactitude. Vérifiez toujours directement auprès du vendeur avant de commander.",
                        'en' => 'Descriptions, photos, prices, lead times and availability are supplied by sellers. We do not check them one by one and cannot guarantee their accuracy. Always verify directly with the seller before ordering.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Liens externes', 'en' => 'External links'],
                    'body' => [
                        'fr' => "Certains contenus renvoient vers des sites tiers que nous ne contrôlons pas. Nous n'assumons aucune responsabilité quant à leur contenu ou à leurs pratiques.",
                        'en' => 'Some content links to third-party sites we do not control. We accept no responsibility for their content or their practices.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Signaler un problème', 'en' => 'Reporting a problem'],
                    'body' => [
                        'fr' => "Si une annonce vous semble frauduleuse ou trompeuse, ou si une transaction tourne mal, signalez-le nous. Nous pouvons suspendre un compte, mais nous ne pouvons pas récupérer des fonds versés hors de la plateforme.",
                        'en' => 'If a listing looks fraudulent or misleading, or a transaction goes wrong, report it to us. We can suspend an account, but we cannot recover funds paid outside the platform.',
                    ],
                ],
            ],
        ],

        // ─────────────────────────────────────────────────────────────
        'mentions-legales' => [
            'title' => ['fr' => 'Mentions légales', 'en' => 'Legal Notice'],
            'intro' => [
                'fr' => "Informations sur l'éditeur et l'hébergeur de ce site.",
                'en' => 'Information about the publisher and host of this site.',
            ],
            'sections' => [
                [
                    'heading' => ['fr' => 'Éditeur du site', 'en' => 'Site publisher'],
                    'body' => [
                        'fr' => "Ce site est édité par une société privée de droit camerounais. Les coordonnées de contact figurent sur la page Contact.",
                        'en' => 'This site is published by a private company incorporated in Cameroon. Contact details appear on the Contact page.',
                    ],
                    'identity' => true,
                ],
                [
                    'heading' => ['fr' => 'Hébergement', 'en' => 'Hosting'],
                    'body' => [
                        'fr' => "Le site est hébergé par un prestataire technique tiers. Les coordonnées de l'hébergeur peuvent être obtenues sur demande via la page Contact.",
                        'en' => 'The site is hosted by a third-party technical provider. Host details can be obtained on request via the Contact page.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Propriété intellectuelle', 'en' => 'Intellectual property'],
                    'body' => [
                        'fr' => "La structure du site, son code, sa charte graphique et ses textes sont protégés. Les noms, logos, photos et descriptions des entreprises et produits restent la propriété de leurs titulaires respectifs, qui nous autorisent à les afficher sur la plateforme.",
                        'en' => 'The site structure, code, visual identity and texts are protected. Business and product names, logos, photos and descriptions remain the property of their respective owners, who authorise us to display them on the platform.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Signalement de contenu', 'en' => 'Content reporting'],
                    'body' => [
                        'fr' => "Pour signaler un contenu illicite, une contrefaçon ou une atteinte à vos droits, écrivez-nous via la page Contact en précisant l'URL concernée et le motif. Nous traitons les signalements dans les meilleurs délais.",
                        'en' => 'To report unlawful content, counterfeiting or an infringement of your rights, write to us via the Contact page, giving the URL concerned and the reason. We handle reports as quickly as possible.',
                    ],
                ],
                [
                    'heading' => ['fr' => 'Cookies', 'en' => 'Cookies'],
                    'body' => [
                        'fr' => "Ce site utilise uniquement des cookies nécessaires à son fonctionnement : maintien de votre session une fois connecté, protection contre la falsification de requêtes, et mémorisation de votre langue. Aucun cookie publicitaire ou de suivi tiers n'est déposé.",
                        'en' => 'This site uses only cookies necessary for it to function: keeping you signed in, protecting against request forgery, and remembering your language. No advertising or third-party tracking cookies are set.',
                    ],
                ],
            ],
        ],
    ],
];
