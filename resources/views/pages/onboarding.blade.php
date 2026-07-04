@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Where the wizard exits into the real flow ("Soumettre mon dossier" on step 10)
    $nextUrl = $siacUser ? route('business.create') : route('onboarding', ['lang' => $lang]);

    $accountTypes = [
        [
            'ob-type-1.png', '#157A43',
            $isFr ? 'Artisan Individuel' : 'Individual Artisan',
            $isFr ? 'Vous êtes un artisan travaillant à titre individuel et souhaitant promouvoir vos créations.' : 'You are an artisan working individually and wishing to promote your creations.',
            $isFr ? ['Vitrine personnelle', 'Gestion de vos produits', 'Accès aux demandes de devis', 'Participation aux événements']
                  : ['Personal showcase', 'Manage your products', 'Access to quote requests', 'Participation in events'],
        ],
        [
            'ob-type-2.png', '#FEB530',
            $isFr ? 'Coopérative / Groupement' : 'Cooperative / Group',
            $isFr ? "Vous représentez une coopérative ou un groupement d'artisans." : 'You represent a cooperative or a group of artisans.',
            $isFr ? ['Vitrine de la coopérative', 'Gestion des membres', 'Gestion collective des produits', "Accès aux marchés et appels d'offres"]
                  : ['Cooperative showcase', 'Member management', 'Collective product management', 'Access to markets and tenders'],
        ],
        [
            'ob-type-3.png', '#9768D8',
            $isFr ? 'PME / Entreprise' : 'SME / Business',
            $isFr ? "Vous dirigez une petite ou moyenne entreprise dans le secteur de l'artisanat." : 'You run a small or medium business in the craft sector.',
            $isFr ? ['Vitrine professionnelle', 'Catalogue illimité', 'Outils marketing avancés', 'Statistiques et analyses']
                  : ['Professional showcase', 'Unlimited catalogue', 'Advanced marketing tools', 'Statistics and analytics'],
        ],
        [
            'ob-type-4.png', '#2E7CE8',
            $isFr ? 'Grande Entreprise' : 'Large Enterprise',
            $isFr ? 'Vous représentez une grande entreprise ou industrie artisanale.' : 'You represent a large company or craft industry.',
            $isFr ? ['Solutions sur mesure', 'Intégrations API', "Gestion d'équipe avancée", 'Support dédié']
                  : ['Tailor-made solutions', 'API integrations', 'Advanced team management', 'Dedicated support'],
        ],
    ];

    $wizardSteps = $isFr ? [
        ['Choisir le type de compte', 'Sélectionnez votre profil'],
        ['Identité', 'Vos informations personnelles'],
        ["Informations de l'entreprise", 'Détails de votre activité'],
        ["Catégories d'artisanat", 'Vos spécialités'],
        ['Atelier / Localisation', 'Où se trouve votre activité ?'],
        ['Produits & Services', 'Présentez ce que vous faites'],
        ['Galerie média', 'Photos, vidéos, documents'],
        ['Certifications & Documents', 'Vos preuves et attestations'],
        ['Vérification', 'Vérification et conformité'],
        ['Revue & Soumission', 'Vérifiez et soumettez votre dossier'],
    ] : [
        ['Choose account type', 'Select your profile'],
        ['Identity', 'Your personal information'],
        ['Business information', 'Details of your activity'],
        ['Craft categories', 'Your specialities'],
        ['Workshop / Location', 'Where is your activity?'],
        ['Products & Services', 'Present what you do'],
        ['Media gallery', 'Photos, videos, documents'],
        ['Certifications & Documents', 'Your proofs and attestations'],
        ['Verification', 'Verification and compliance'],
        ['Review & Submission', 'Check and submit your file'],
    ];

    $advantages = $isFr ? [
        ['ob-adv-1.png', 'Visibilité accrue',   "Présentez vos créations\nà des milliers d'acheteurs\nau Cameroun et dans le\nmonde."],
        ['ob-adv-2.png', 'Demandes de devis',   "Recevez des demandes\nde devis qualifiées et\ndéveloppez votre réseau\nprofessionnel."],
        ['ob-adv-3.png', 'Événements',          "Participez aux foires,\nsalons et expositions\norganisés ou partenaires\nde la plateforme."],
        ['ob-adv-4.png', 'Certification',       "Obtenez votre certificat\nde membre vérifié et\nrenforcez votre crédibilité."],
        ['ob-adv-5.png', 'Outils de gestion',   "Gérez vos produits,\ncommandes, devis et\nperformances depuis\nvotre espace."],
        ['ob-adv-6.png', 'Notifications',       "Restez informé des\nopportunités, appels\nd'offres et nouveautés."],
    ] : [
        ['ob-adv-1.png', 'Increased visibility', "Show your creations\nto thousands of buyers\nin Cameroon and\nworldwide."],
        ['ob-adv-2.png', 'Quote requests',       "Receive qualified quote\nrequests and grow your\nprofessional network."],
        ['ob-adv-3.png', 'Events',               "Take part in fairs,\nshows and exhibitions\norganised by or partnered\nwith the platform."],
        ['ob-adv-4.png', 'Certification',        "Get your verified member\ncertificate and strengthen\nyour credibility."],
        ['ob-adv-5.png', 'Management tools',     "Manage your products,\norders, quotes and\nperformance from\nyour space."],
        ['ob-adv-6.png', 'Notifications',        "Stay informed of\nopportunities, tenders\nand news."],
    ];

    $securityItems = $isFr ? [
        ['ob-sec-1.png', 'Plateforme officielle', "Soutenue par les institutions\nnationales pour la promotion\nde l'artisanat."],
        ['ob-sec-2.png', 'Données sécurisées',    "Vos données sont protégées\net ne sont jamais partagées\nsans votre consentement."],
        ['ob-sec-3.png', 'Conformité',            "Conforme aux normes\nnationales et internationales\nde protection des données."],
        ['ob-sec-4.png', 'Accès global',          "Accédez à de nouveaux\nmarchés et opportunités\ninternationales."],
    ] : [
        ['ob-sec-1.png', 'Official platform',  "Supported by national\ninstitutions promoting\ncraftsmanship."],
        ['ob-sec-2.png', 'Secured data',       "Your data is protected\nand never shared without\nyour consent."],
        ['ob-sec-3.png', 'Compliance',         "Compliant with national\nand international data\nprotection standards."],
        ['ob-sec-4.png', 'Global access',      "Access new international\nmarkets and\nopportunities."],
    ];

    $secondaryCategories = [
        [$isFr ? "Sculpture &\nStatuaire" : "Sculpture &\nStatuary", true],
        [$isFr ? "Art du bois &\nÉbénisterie" : "Woodwork &\nCabinetmaking", true],
        [$isFr ? "Bijouterie &\nOrfèvrerie" : "Jewelry &\nGoldsmithing", true],
        [$isFr ? "Textile & Tissage" : "Textile & Weaving", false],
        [$isFr ? "Cuir & Maroquinerie" : "Leather & Leather goods", false],
        [$isFr ? "Poterie &\nCéramique" : "Pottery &\nCeramics", false],
        [$isFr ? "Vannerie &\nRotin" : "Basketry &\nRattan", true],
        [$isFr ? "Peinture &\nArts visuels" : "Painting &\nVisual arts", false],
        [$isFr ? "Métallerie &\nFerronnerie" : "Metalwork &\nIronwork", false],
        [$isFr ? "Instruments de\nmusique" : "Musical\ninstruments", false],
        [$isFr ? "Architecture\ntraditionnelle" : "Traditional\narchitecture", false],
        [$isFr ? "Produits naturels\n& Cosmétiques" : "Natural products\n& Cosmetics", false],
        [$isFr ? "Agro-transformation\nartisanale" : "Artisanal\nagro-processing", false],
        [$isFr ? "Mode &\nAccessoires" : "Fashion &\nAccessories", false],
        [$isFr ? "Autre\n(spécifiez)" : "Other\n(specify)", false],
    ];

    $step6Products = [
        ['ob6-prod-1.png', $isFr ? 'Masque facial traditionnel Bamiléké' : 'Traditional Bamileke face mask', $isFr ? 'Sculpture & Statuaire' : 'Sculpture & Statuary', '80 000'],
        ['ob6-prod-2.png', $isFr ? 'Tabouret sculpté en bois de Fraké' : 'Carved Frake wood stool',          $isFr ? "Art du bois & Ébénisterie" : 'Woodwork & Cabinetmaking', '120 000'],
        ['ob6-prod-3.png', $isFr ? 'Collier perles et laiton fait main' : 'Handmade bead and brass necklace', $isFr ? 'Bijouterie & Orfèvrerie' : 'Jewelry & Goldsmithing', '25 000'],
        ['ob6-prod-4.png', $isFr ? 'Panier tressé traditionnel' : 'Traditional woven basket',                $isFr ? 'Vannerie & Rotin' : 'Basketry & Rattan', '15 000'],
        ['ob6-prod-5.png', $isFr ? 'Vase décoratif en terre cuite' : 'Decorative terracotta vase',           $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics', '30 000'],
    ];

    $step6Services = [
        ['ob6-srv-1.png', $isFr ? 'Sculpture sur commande' : 'Custom sculpture',      $isFr ? 'Conception et réalisation de sculptures personnalisées sur bois.' : 'Design and creation of custom wood sculptures.', '50 000'],
        ['ob6-srv-2.png', $isFr ? "Formation à l'artisanat" : 'Craft training',       $isFr ? "Ateliers et formations pratiques pour apprendre les techniques artisanales." : 'Practical workshops and training to learn craft techniques.', '20 000'],
        ['ob6-srv-3.png', $isFr ? "Décoration d'intérieur" : 'Interior decoration',   $isFr ? "Création d'objets décoratifs et aménagement d'espaces avec des pièces artisanales." : 'Creation of decorative objects and space design with artisanal pieces.', '100 000'],
    ];

    $step7Photos = [
        ['ob7-ph-1.png', $isFr ? 'Masque traditionnel' : 'Traditional mask',     '2.4 MB'],
        ['ob7-ph-2.png', $isFr ? 'Tabouret en bois' : 'Wooden stool',            '1.8 MB'],
        ['ob7-ph-3.png', $isFr ? 'Collier perles' : 'Bead necklace',             '1.6 MB'],
        ['ob7-ph-4.png', $isFr ? 'Panier traditionnel' : 'Traditional basket',   '1.9 MB'],
        ['ob7-ph-5.png', $isFr ? 'Sculpture sur bois' : 'Wood carving',          '3.2 MB'],
        ['ob7-ph-6.png', $isFr ? 'Poteries décoratives' : 'Decorative pottery',  '2.7 MB'],
        ['ob7-ph-7.png', $isFr ? 'Mon atelier' : 'My workshop',                  '2.1 MB'],
        ['ob7-ph-8.png', $isFr ? 'Détail de sculpture' : 'Sculpture detail',     '1.4 MB'],
    ];

    $step7Uploads = [
        ['ob7-up-1.png', $isFr ? 'Photos de vos produits' : 'Photos of your products', $isFr ? 'Présentez vos créations' : 'Present your creations',     $isFr ? 'Ajouter des photos' : 'Add photos',      true],
        ['ob7-up-2.png', $isFr ? 'Photos de votre atelier' : 'Photos of your workshop', $isFr ? 'Montrez votre espace' : 'Show your space',              $isFr ? 'Ajouter' : 'Add',                        false],
        ['ob7-up-3.png', $isFr ? 'Vidéos de votre travail' : 'Videos of your work',     $isFr ? 'Montrez votre savoir-faire' : 'Show your know-how',     $isFr ? 'Ajouter une vidéo' : 'Add a video',      false],
        ['ob7-up-4.png', 'Documents & Catalogues',                                      $isFr ? 'Brochures, catalogues...' : 'Brochures, catalogues...', $isFr ? 'Ajouter un document' : 'Add a document', false],
    ];

    $step7Practices = $isFr ? [
        'Utilisez des photos claires et bien éclairées',
        'Montrez plusieurs angles de vos produits',
        'Incluez des photos de votre processus de travail',
        'Évitez les photos floues ou sombres',
    ] : [
        'Use clear, well-lit photos',
        'Show several angles of your products',
        'Include photos of your work process',
        'Avoid blurry or dark photos',
    ];

    // "rechreches" is [sic] — the design PNGs bake this typo into both footer bands
    $step7Stats = [
        ['ob7-stat-1.png', '+80%',  $isFr ? 'Visibilité accrue' : 'Increased visibility'],
        ['ob7-stat-2.png', '+70%',  $isFr ? 'Demandes reçues' : 'Requests received'],
        ['ob7-stat-3.png', '+60%',  $isFr ? 'Ventes potentielles' : 'Potential sales'],
        ['ob7-stat-4.png', '+100%', $isFr ? 'Crédibilité' : 'Credibility'],
    ];

    $step8Certs = [
        ['ob8-cert-1.png', $isFr ? 'Registre de Commerce' : 'Trade Register', 'RC/DLA/2016/B/12345'],
        ['ob8-cert-2.png', $isFr ? 'Attestation Fiscale' : 'Tax Certificate', 'N° 00123/2024/DGI'],
        ['ob8-cert-3.png', $isFr ? "Carte d'Artisan" : 'Artisan Card',        'N° CA/OU/2023/0456'],
    ];

    $step8Empty = [
        [$isFr ? 'Certification de Qualité' : 'Quality Certification', 'ISO, HACCP, etc.'],
        [$isFr ? 'Prix ou Récompense' : 'Prize or Award',              $isFr ? 'Documents de prix reçus' : 'Documents of prizes received'],
        [$isFr ? 'Brevet / Innovation' : 'Patent / Innovation',        $isFr ? 'Brevet, modèle déposé, etc.' : 'Patent, registered design, etc.'],
        [$isFr ? 'Attestation de Formation' : 'Training Certificate',  $isFr ? 'Certificats de formation' : 'Training certificates'],
        [$isFr ? 'Autre document' : 'Other document',                  $isFr ? 'Tout autre document utile' : 'Any other useful document'],
    ];

    $step8Docs = [
        ['ob8-file-pdf.png', $isFr ? 'Catalogue produits 2024' : '2024 product catalogue',        'Catalogue',                       'catalogue_2024.pdf',           '2.4 Mo'],
        ['ob8-file-jpg.png', $isFr ? "Présentation de l'atelier" : 'Workshop presentation',       'Image',                           'atelier_presentation.jpg',     '1.8 Mo'],
        ['ob8-file-pdf.png', $isFr ? 'Politique de confidentialité' : 'Privacy policy',           'Document',                        'politique_confidentialite.pdf', '1.2 Mo'],
    ];

    $step8Advantages = [
        ['ob8-adv-1.png', $isFr ? "Renforcer la confiance\ndes acheteurs" : "Strengthen buyer\ntrust"],
        ['ob8-adv-2.png', $isFr ? "Accéder à plus\nd'opportunités" : "Access more\nopportunities"],
        ['ob8-adv-3.png', $isFr ? "Apparaître en tête\ndes rechreches" : "Appear at the top\nof searches"],
        ['ob8-adv-4.png', $isFr ? "Augmenter vos\nventes" : "Increase your\nsales"],
        ['ob8-adv-5.png', $isFr ? "Bénéficier d'un badge\nartisan vérifié" : "Get a verified\nartisan badge"],
    ];

    $step9Checks = $isFr ? [
        'Je certifie que toutes les informations fournies sont exactes et véridiques.',
        "Je m'engage à respecter les %LINK% de la plateforme.",
        "Je m'engage à fournir des produits/services de qualité et à traiter mes clients avec respect.",
        'Je comprends que toute information fausse peut entraîner la suspension de mon compte.',
        "J'autorise la plateforme à vérifier mes informations et documents.",
    ] : [
        'I certify that all the information provided is accurate and truthful.',
        'I undertake to respect the %LINK% of the platform.',
        'I undertake to provide quality products/services and to treat my clients with respect.',
        'I understand that any false information may lead to the suspension of my account.',
        'I authorise the platform to verify my information and documents.',
    ];

    $step9Advantages = [
        ['ob9-adv-1.png', $isFr ? "Badge\nd'artisan vérifié" : "Verified artisan\nbadge"],
        ['ob9-adv-2.png', $isFr ? "Meilleure visibilité\ndans les rechreches" : "Better visibility\nin searches"],
        ['ob9-adv-3.png', $isFr ? "Accès à plus\nd'opportunités" : "Access to more\nopportunities"],
        ['ob9-adv-4.png', $isFr ? "Confiance accrue\ndes acheteurs" : "Increased buyer\ntrust"],
        ['ob9-adv-5.png', $isFr ? "Priorité dans les\névénements & foires" : "Priority in events\n& fairs"],
    ];

    $step10Summary = [
        ['ob10-sum-1.png', $isFr ? 'Type de compte' : 'Account type',                       [$isFr ? 'Artisan Individuel' : 'Individual Artisan']],
        ['ob10-sum-2.png', $isFr ? 'Identité' : 'Identity',                                 ['Jude Nshome', '+237 670 416 238']],
        ['ob10-sum-3.png', $isFr ? 'Entreprise' : 'Business',                               ['Atelier Nshome Artisanat', 'RC/DLA/2016/B/12345']],
        ['ob10-sum-4.png', $isFr ? "Catégories d'artisanat" : 'Craft categories',           [$isFr ? 'Sculpture & Statuaire' : 'Sculpture & Statuary', $isFr ? 'Art du bois & Ébénisterie' : 'Woodwork & Cabinetmaking']],
        ['ob10-sum-5.png', $isFr ? 'Localisation' : 'Location',                             ['Quartier Tougang, Bafoussam', $isFr ? 'Ouest Cameroun' : 'West Cameroon']],
        ['ob10-sum-6.png', $isFr ? 'Produits & Services' : 'Products & Services',           [$isFr ? '5 produits' : '5 products', '3 services']],
        ['ob10-sum-7.png', $isFr ? 'Galerie média' : 'Media gallery',                       [$isFr ? '20 photos, 1 vidéo' : '20 photos, 1 video', '2 documents']],
        ['ob10-sum-8.png', 'Certifications & Documents',                                    [$isFr ? '3 documents téléchargés' : '3 documents uploaded']],
        ['ob10-sum-9.png', $isFr ? 'Vérification' : 'Verification',                         [$isFr ? 'Toutes les vérifications réussies' : 'All verifications passed']],
    ];

    $step10Docs = [
        ['ob10-doc-1.png', $isFr ? 'Registre de Commerce' : 'Trade Register', 'PDF · 2.4 Mo'],
        ['ob10-doc-2.png', $isFr ? 'Attestation Fiscale' : 'Tax Certificate', 'PDF · 1.8 Mo'],
        ['ob10-doc-3.png', $isFr ? "Carte d'Artisan" : 'Artisan Card',        'PDF · 1.2 Mo'],
    ];

    $step10After = $isFr ? [
        ['ob10-step-1.png', '1. Réception du dossier',      'Votre dossier sera enregistré et vous recevrez un accusé de réception.'],
        ['ob10-step-2.png', '2. Vérification & Validation', 'Notre équipe vérifiera vos informations et documents (2 à 5 jours ouvrés).'],
        ['ob10-step-3.png', '3. Approbation',               'Vous serez notifié par email et SMS dès que votre compte sera approuvé.'],
        ['ob10-step-4.png', '4. Publication',               'Votre profil sera publié dans la Galerie Virtuelle et visible par les acheteurs.'],
    ] : [
        ['ob10-step-1.png', '1. File reception',            'Your file will be recorded and you will receive an acknowledgement of receipt.'],
        ['ob10-step-2.png', '2. Verification & Validation', 'Our team will verify your information and documents (2 to 5 working days).'],
        ['ob10-step-3.png', '3. Approval',                  'You will be notified by email and SMS as soon as your account is approved.'],
        ['ob10-step-4.png', '4. Publication',               'Your profile will be published in the Virtual Gallery and visible to buyers.'],
    ];

    $step10Quality = $isFr
        ? ['Vérification humaine', 'Protection des données', 'Transparence totale', 'Confiance garantie']
        : ['Human verification', 'Data protection', 'Total transparency', 'Guaranteed trust'];

    $step10Advantages = [
        ['ob10-adv-1.png', $isFr ? "Visibilité accrue\nauprès d'acheteurs\nlocaux et internationaux" : "Increased visibility\nwith local and\ninternational buyers"],
        ['ob10-adv-2.png', $isFr ? "Opportunités de\ncontrats et de\npartenariats" : "Contract and\npartnership\nopportunities"],
        ['ob10-adv-3.png', $isFr ? "Promotion de\nl'artisanat camerounais\nà l'échelle mondiale" : "Promotion of\nCameroonian crafts\nworldwide"],
        ['ob10-adv-4.png', $isFr ? "Accompagnement\net formation\ncontinue" : "Continuous\nsupport and\ntraining"],
        ['ob10-adv-5.png', $isFr ? "Badge d'artisan\ncertifié et profil\nvérifié" : "Certified artisan\nbadge and verified\nprofile"],
    ];

    // ═══ Success screen (design "onboarding step 12") ═══
    $successContactEmail = $siacUser['email'] ?? 'nshome@opesware.com';
    $successContactPhone = $siacUser['phone'] ?? '+237 670 416 238';

    // [icon, iconColor, circleBg, title, description, status: done|current|upcoming]
    $successTimeline = [
        ['file-text',      '#157A43', '#DFEEE3', $isFr ? 'Dossier soumis' : 'File submitted',              $isFr ? 'Votre dossier a été soumis avec succès.' : 'Your file was submitted successfully.', 'done'],
        ['clock',          '#EE8D0C', '#FDEBC5', $isFr ? 'Vérification en cours' : 'Verification in progress', $isFr ? 'Notre équipe vérifie vos informations et documents.' : 'Our team is checking your information and documents.', 'current'],
        ['triangle-alert', '#6B7280', '#F1F0F3', $isFr ? 'Validation' : 'Validation',                      $isFr ? 'Votre dossier est en attente de validation finale.' : 'Your file is awaiting final validation.', 'upcoming'],
        ['bell',           '#6B7280', '#F1F0F3', $isFr ? 'Approbation' : 'Approval',                       $isFr ? 'Vous recevrez une notification dès approbation.' : 'You will receive a notification upon approval.', 'upcoming'],
        ['mail',           '#6B7280', '#F1F0F3', $isFr ? 'Compte activé' : 'Account activated',            $isFr ? 'Accédez à votre tableau de bord et commencez à gérer votre activité.' : 'Access your dashboard and start managing your activity.', 'upcoming'],
    ];

    $successRecap = $isFr ? [
        ['Identité',       "Informations\npersonnelles"],
        ['Entreprise',     "Informations de\nl'activité"],
        ['Catégories',     "Spécialités\nd'artisanat"],
        ['Atelier',        "Localisation de\nl'activité"],
        ['Produits',       "Produits &\nServices"],
        ['Galerie média',  "Photos, vidéos,\ndocuments"],
        ['Certifications', "Documents &\nattestations"],
        ['Vérification',   "Conformité &\npièces d'identité"],
        ['Review',         "Revue complète\ndu dossier"],
        ['Soumission',     "Dossier soumis\navec succès"],
    ] : [
        ['Identity',       "Personal\ninformation"],
        ['Business',       "Activity\ninformation"],
        ['Categories',     "Craft\nspecialities"],
        ['Workshop',       "Location of\nthe activity"],
        ['Products',       "Products &\nServices"],
        ['Media gallery',  "Photos, videos,\ndocuments"],
        ['Certifications', "Documents &\nattestations"],
        ['Verification',   "Compliance &\nidentity documents"],
        ['Review',         "Complete file\nreview"],
        ['Submission',     "File submitted\nsuccessfully"],
    ];

    // [icon, title, description, buttonLabel, href (null => window.print())]
    $successActions = [
        ['newspaper', $isFr ? 'Voir mon dossier' : 'View my file',
            $isFr ? 'Consultez toutes les informations que vous avez soumises.' : 'Review all the information you have submitted.',
            $isFr ? 'Voir le dossier' : 'View the file', $nextUrl],
        ['download', $isFr ? 'Télécharger le dossier' : 'Download the file',
            $isFr ? 'Téléchargez un PDF récapitulatif de votre soumission.' : 'Download a summary PDF of your submission.',
            $isFr ? 'Télécharger PDF' : 'Download PDF', null],
        ['house', $isFr ? 'Explorer le site' : 'Explore the site',
            $isFr ? 'Découvrez les artisans et produits déjà disponibles sur la plateforme.' : 'Discover the artisans and products already available on the platform.',
            $isFr ? 'Explorer' : 'Explore', route('products.index', ['lang' => $lang])],
        ['file-text', $isFr ? "Retour à l'accueil" : 'Back to home',
            $isFr ? "Retournez à la page d'accueil de la Galerie Virtuelle." : 'Return to the Virtual Gallery home page.',
            $isFr ? 'Accueil' : 'Home', route('home', ['lang' => $lang])],
    ];

    // @json() splits its argument on commas, so multi-entry arrays must live here as variables
    $typeNames = array_map(fn ($t) => $t[2], $accountTypes);
    $sideSecureTitles = $isFr
        ? [1 => 'Sécurisé & Vérifié', 2 => 'Sécurisé & Confidentiel', 3 => 'Sécurisé & Confidentiel', 4 => 'Sécurisé & Confidentiel', 5 => 'Sécurité & Confidentialité', 6 => 'Sécurisé & Confidentiel', 7 => 'Sécurisé & Confidentiel', 8 => 'Pourquoi ces documents ?', 9 => 'Pourquoi la vérification ?', 10 => 'Sécurisé & Confidentiel']
        : [1 => 'Secure & Verified', 2 => 'Secure & Confidential', 3 => 'Secure & Confidential', 4 => 'Secure & Confidential', 5 => 'Security & Confidentiality', 6 => 'Secure & Confidential', 7 => 'Secure & Confidential', 8 => 'Why these documents?', 9 => 'Why verification?', 10 => 'Secure & Confidential'];
    $sideSecureTexts = $isFr
        ? [8 => 'Ces documents renforcent la confiance des acheteurs et améliorent votre visibilité sur la plateforme.', 9 => "La vérification renforce la confiance sur la plateforme, protège les acheteurs et vous donne accès à plus d'opportunités."]
        : [8 => 'These documents strengthen buyer trust and improve your visibility on the platform.', 9 => 'Verification strengthens trust on the platform, protects buyers and gives you access to more opportunities.'];

    $fieldCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg pl-10 pr-4 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
    $plainCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
    $selectCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg pl-10 pr-8 text-[13px] text-[#1B1B18] bg-white appearance-none cursor-pointer focus:outline-none focus:border-[#14532D]';
    $labelCls = 'block text-[12.5px] text-[#3B382F] mb-1.5';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Créez votre compte artisan ou entreprise sur la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.' : 'Create your artisan or business account on the National Virtual Gallery of Cameroonian Crafts.' }}">
    <title>{{ $isFr ? 'Créer mon compte artisan / entreprise — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Create my artisan / business account — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        obside: '#012915',
                        obact:  '#01602D',
                        obdeep: '#0A2E1C',
                        leaf:   '#164C28',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
</head>
<body class="bg-[#F2F3F4] text-[#1B1B18] antialiased">

<!-- Header -->
<header class="bg-white">
    <div class="max-w-[1024px] mx-auto px-4 flex items-center justify-between gap-4 py-2.5">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain">
            <span class="leading-tight">
                <span class="block text-[12px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                <span class="block text-[12px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                <span class="block text-[10px] text-[#2E7D4F] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </a>
        <div class="flex items-center gap-4">
            <a href="{{ route('contact', ['lang' => $lang]) }}" class="hidden sm:flex items-center gap-2.5">
                <i data-lucide="headphones" class="w-5 h-5 text-[#14532D]" style="stroke-width:1.7"></i>
                <span class="leading-tight text-left">
                    <span class="block text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</span>
                    <span class="block text-[11px] text-[#6F6B60]">{{ $isFr ? 'Contactez-nous' : 'Contact us' }}</span>
                </span>
            </a>
            <span id="ob-bell" class="relative hidden">
                <i data-lucide="bell" class="w-6 h-6 text-[#8A8F98]" style="stroke-width:1.7"></i>
                <span class="absolute -top-0.5 -left-1 w-[7px] h-[7px] rounded-full bg-[#DC2626]"></span>
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#157A43] text-white text-[10px] font-bold flex items-center justify-center">2</span>
            </span>
            <div class="relative group">
                <button class="flex items-center gap-1.5 border border-[#E5E3E0] rounded-lg px-3.5 py-2 text-[13px] font-semibold text-[#1B1B18]">
                    {{ strtoupper($lang) }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                </button>
                <div class="absolute right-0 top-full w-28 bg-white rounded-lg shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-leaf' : 'text-[#262521]' }}">FR — Français</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-leaf' : 'text-[#262521]' }}">EN — English</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Tricolor strip -->
<div class="flex h-[7px]">
    <div class="w-[46%] bg-[#094F2B]"></div>
    <div class="w-[26%] bg-[#B61012]"></div>
    <div class="flex-1 bg-[#E9A411]"></div>
</div>

<div class="max-w-[1024px] mx-auto px-2 sm:px-4 pb-6">
    <div id="wizard-flex" class="flex flex-col lg:flex-row items-stretch gap-0">

        <!-- Wizard sidebar -->
        <aside class="relative lg:w-[245px] shrink-0 bg-obside rounded-b-2xl lg:rounded-bl-2xl lg:rounded-br-none overflow-hidden">
            <div class="absolute inset-0 opacity-[0.07] bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative px-5 pt-7 pb-6">
                <h2 class="text-[15px] font-bold tracking-[0.02em] text-white uppercase leading-snug">
                    {{ $isFr ? 'Créer mon compte' : 'Create my account' }}<br>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}
                </h2>
                <p id="side-sub" class="mt-2 text-[11.5px] text-[#B9CBBE] leading-relaxed">
                    {{ $isFr ? 'Rejoignez la plus grande vitrine de l\'artisanat camerounais' : 'Join the largest showcase of Cameroonian craftsmanship' }}
                </p>

                <ol class="mt-6">
                    @foreach($wizardSteps as $wsIdx => [$wsTitle, $wsSub])
                    <li class="relative wizard-step" data-step="{{ $wsIdx + 1 }}">
                        @if($wsIdx < 9)<span class="absolute left-[17px] top-[38px] bottom-0 w-px bg-white/20"></span>@endif
                        <button type="button" onclick="goToStep({{ $wsIdx + 1 }})" class="w-full text-left flex items-start gap-3.5 rounded-xl px-2 py-2.5 step-row">
                            <span class="step-circle relative z-10 w-[34px] h-[34px] shrink-0 rounded-full border border-white/40 bg-obside flex items-center justify-center text-[13px] font-semibold text-white">{{ $wsIdx + 1 }}</span>
                            <span class="pt-0.5 min-w-0">
                                <span class="step-title block text-[13px] font-semibold text-white leading-snug">{{ $wsTitle }}</span>
                                <span class="step-sub block mt-0.5 text-[11px] text-[#B9CBBE] leading-snug">{{ $wsSub }}</span>
                            </span>
                        </button>
                    </li>
                    @endforeach
                </ol>

                <div class="mt-5 rounded-xl border border-white/15 p-4">
                    <p class="flex items-center gap-2.5 text-[12.5px] font-bold text-white">
                        <img src="{{ asset('images/landing/ob-shield.png') }}" alt="" class="w-[22px] h-[24px]" aria-hidden="true">
                        <span id="side-secure-title">{{ $isFr ? 'Sécurisé & Vérifié' : 'Secure & Verified' }}</span>
                    </p>
                    <p id="side-secure-text" class="mt-2.5 text-[11.5px] text-[#B9CBBE] leading-relaxed">
                        {{ $isFr
                            ? 'Vos données sont protégées et utilisées uniquement pour la vérification et la gestion de votre compte.'
                            : 'Your data is protected and used only for the verification and management of your account.'
                        }}
                    </p>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 min-w-0 bg-white rounded-2xl lg:rounded-l-none lg:rounded-tr-none shadow-sm mt-3 lg:mt-0 px-5 sm:px-8 py-7">

            <!-- ═══════ Step 1 — account type ═══════ -->
            <div id="panel-1" class="ob-panel">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Étape 1 sur 10' : 'Step 1 of 10' }}</p>
                        <h1 class="mt-1 text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Choisissez votre type de compte' : 'Choose your account type' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[430px]">
                            {{ $isFr ? 'Sélectionnez le profil qui correspond le mieux à votre activité sur la plateforme.' : 'Select the profile that best matches your activity on the platform.' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11.5px] text-[#3B382F]">10% {{ $isFr ? 'terminé' : 'complete' }}</p>
                        <div class="mt-1.5 w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[10%] rounded-full bg-[#10592E]"></div></div>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($accountTypes as $atIdx => [$atIcon, $atColor, $atTitle, $atDesc, $atPerks])
                    <label class="cursor-pointer">
                        <input type="radio" name="account_type" value="{{ $atIdx }}" class="sr-only peer" @checked($atIdx === 0)>
                        <div class="relative h-full rounded-xl border border-[#E7E9E7] bg-white p-6 transition-all peer-checked:border-[#0F5132] peer-checked:shadow-[0_0_0_1px_#0F5132] hover:border-[#C9CFC9]">
                            <span class="absolute top-6 right-6 w-[24px] h-[24px] rounded-full border-2 border-[#C9CFC9] flex items-center justify-center at-radio">
                                <span class="hidden w-[12px] h-[12px] rounded-full bg-[#0F5132] at-dot"></span>
                            </span>
                            <img src="{{ asset('images/landing/' . $atIcon) }}" alt="" class="w-[60px] h-[60px]" aria-hidden="true">
                            <h3 class="mt-5 text-[16.5px] font-bold text-[#1B1B18]">{{ $atTitle }}</h3>
                            <p class="mt-2 text-[12.5px] text-[#55524A] leading-relaxed">{{ $atDesc }}</p>
                            <ul class="mt-4 space-y-2.5">
                                @foreach($atPerks as $perk)
                                <li class="flex items-center gap-2.5 text-[12px] text-[#3B382F]">
                                    <span class="w-[17px] h-[17px] shrink-0 rounded-full flex items-center justify-center" style="background:{{ $atColor }}">
                                        <i data-lucide="check" class="w-2.5 h-2.5 text-white" style="stroke-width:3.5"></i>
                                    </span>
                                    {{ $perk }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="relative mt-5 rounded-xl border border-[#DFEDE2] bg-[#F3F8F3] overflow-hidden">
                    <img src="{{ asset('images/landing/ob-vases.png') }}" alt="" class="absolute right-4 bottom-0 h-[88%] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                    <div class="relative flex items-start gap-4 p-5 sm:pr-[130px]">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-full bg-white border border-[#CFE3D4] flex items-center justify-center">
                            <i data-lucide="star" class="w-[18px] h-[18px] text-[#14532D]" style="stroke-width:1.8"></i>
                        </span>
                        <div>
                            <h3 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Pourquoi devenir membre ?' : 'Why become a member?' }}</h3>
                            <p class="mt-1 text-[12.5px] text-[#3B382F] leading-relaxed">
                                {{ $isFr
                                    ? 'En devenant membre, vous accédez à une visibilité nationale et internationale, aux demandes de devis qualifiées et à des outils pour développer votre activité.'
                                    : 'By becoming a member, you gain national and international visibility, qualified quote requests and tools to grow your business.'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Continuer' : 'Continue' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>

                <p class="mt-6 pt-5 border-t border-[#F0F0EE] text-center text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Vous avez déjà un compte ?' : 'Already have an account?' }}
                    <a href="/login?lang={{ $lang }}" class="ml-1.5 font-semibold text-[#14532D] hover:underline">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                </p>
            </div>

            <!-- ═══════ Step 2 — identity ═══════ -->
            <div id="panel-2" class="ob-panel hidden">
                <button type="button" onclick="goToStep(1)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos informations personnelles' : 'Your personal information' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60]">
                            {{ $isFr ? 'Ces informations nous permettent de vous identifier et de sécuriser votre compte.' : 'This information lets us identify you and secure your account.' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 2 sur 10' : 'Step 2 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[20%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">20% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations d\'identité' : 'Identity information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Prénom(s)' : 'First name(s)' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" id="ob-first-name" name="first_name" value="{{ old('first_name') }}" placeholder="Aristide" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom' : 'Last name' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" id="ob-last-name" name="last_name" value="{{ old('last_name') }}" placeholder="Ndop" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Date de naissance' : 'Date of birth' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="12 / 05 / 1988" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Genre' : 'Gender' }} *</label>
                            <div class="relative"><i data-lucide="users" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Masculin' : 'Male' }}</option><option>{{ $isFr ? 'Féminin' : 'Female' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nationalité' : 'Nationality' }} *</label>
                            <div class="relative"><i data-lucide="book-user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Camerounaise' : 'Cameroonian' }}</option><option>{{ $isFr ? 'Autre' : 'Other' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Numéro de pièce d\'identité' : 'ID number' }} *</label>
                            <div class="relative"><i data-lucide="id-card" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="000123456" class="{{ $fieldCls }}">
                                <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Type de pièce d\'identité' : 'ID type' }} *</label>
                            <div class="relative"><i data-lucide="credit-card" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Carte Nationale d\'Identité' : 'National Identity Card' }}</option><option>{{ $isFr ? 'Passeport' : 'Passport' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Date d\'expiration' : 'Expiry date' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="12 / 05 / 2033" class="{{ $fieldCls }}"></div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Téléphone & Email' : 'Phone & Email' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone principal' : 'Main phone' }} *</label>
                            <div class="flex gap-2">
                                <span class="flex items-center gap-1.5 h-[46px] border border-[#E5E3E0] rounded-lg px-3 text-[13px] text-[#1B1B18] shrink-0">
                                    <img src="{{ asset('images/landing/ob-flag.png') }}" alt="" class="w-[20px] h-[14px] rounded-[2px]">
                                    +237
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                                </span>
                                <div class="relative flex-1">
                                    <input type="tel" id="ob-phone" name="phone" value="{{ old('phone') }}" placeholder="6 90 12 34 56" class="w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 pr-10 text-[13px] focus:outline-none focus:border-[#14532D]">
                                    <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">Email *</label>
                            <div class="relative"><i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="email" id="ob-email" name="email" value="{{ old('email') }}" placeholder="aristide.ndop@gmail.com" class="{{ $fieldCls }}">
                                <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i></div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Sécurité du compte' : 'Account security' }}</h2>
                    <p class="mt-1 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Choisissez le mot de passe qui protégera votre compte.' : 'Choose the password that will protect your account.' }}</p>
                    @if($errors->any())
                    <div class="mt-3 bg-[#FDE8E8] border border-[#F5C9C9] rounded-lg px-4 py-3 text-[12.5px] text-[#B42025]">{{ $errors->first() }}</div>
                    @endif
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Mot de passe' : 'Password' }} *</label>
                            <input type="password" id="ob-password" name="password" autocomplete="new-password" placeholder="********" class="{{ $fieldCls }}">
                            <p class="mt-1.5 text-[11px] text-[#8A857A]">{{ $isFr ? '8 caractères minimum.' : 'At least 8 characters.' }}</p>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Confirmer le mot de passe' : 'Confirm password' }} *</label>
                            <input type="password" id="ob-password-confirm" name="password_confirmation" autocomplete="new-password" placeholder="********" class="{{ $fieldCls }}">
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Photo de profil' : 'Profile photo' }}</h2>
                    <p class="mt-1 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Ajoutez une photo claire de vous ou de votre logo.' : 'Add a clear photo of yourself or your logo.' }}</p>
                    <div class="mt-4 flex items-center gap-6">
                        <img id="ob-photo-preview" src="{{ asset('images/landing/ob-photo.png') }}" alt="" class="w-[104px] h-[104px] rounded-full object-cover">
                        <label class="cursor-pointer border border-[#E5E3E0] hover:border-[#14532D] rounded-xl px-8 py-5 text-center transition-colors">
                            <input id="ob-photo-input" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                            <i data-lucide="user-plus" class="w-6 h-6 mx-auto text-[#3B382F]" style="stroke-width:1.6"></i>
                            <span class="mt-2 block text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Changer la photo' : 'Change the photo' }}</span>
                            <span class="mt-0.5 block text-[11px] text-[#8A857A]">PNG, JPG {{ $isFr ? 'ou' : 'or' }} WEBP. Max 2MB</span>
                        </label>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Adresse personnelle' : 'Personal address' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }} *</label>
                            <div class="relative"><i data-lucide="globe" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Région' : 'Region' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}">
                                    @foreach(['Centre', 'Littoral', 'Ouest', 'Nord-Ouest', 'Sud-Ouest', 'Adamaoua', 'Est', 'Extrême-Nord', 'Nord', 'Sud'] as $reg)
                                    <option>{{ $reg }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Ville' : 'City' }} *</label>
                            <div class="relative"><i data-lucide="building-2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Yaoundé" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Quartier' : 'District' }}</label>
                            <div class="relative"><i data-lucide="building" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Mvog-Ada" class="{{ $fieldCls }}"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Adresse complète' : 'Full address' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Rue 8.123, Mvog-Ada, Yaoundé, Cameroun" class="{{ $fieldCls }}"></div>
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(1)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(3)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 3 — business information ═══════ -->
            <div id="panel-3" class="ob-panel hidden">
                <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations de l\'entreprise' : 'Business information' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Parlez-nous de votre activité et de votre structure.' : 'Tell us about your activity and your structure.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 3 sur 10' : 'Step 3 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[30%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">30% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations générales' : 'General information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-[1.55fr_1fr] gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom de l\'entreprise / Nom commercial' : 'Business / trade name' }} *</label>
                            <input type="text" value="Artisanat Ndop" class="{{ $plainCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Année de création' : 'Year founded' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>2018</option>@for($y = 2024; $y >= 1990; $y--)<option>{{ $y }}</option>@endfor</select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Statut juridique' : 'Legal status' }} *</label>
                            <div class="relative"><i data-lucide="scale" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Entreprise Individuelle' : 'Sole proprietorship' }}</option><option>SARL</option><option>{{ $isFr ? 'Coopérative' : 'Cooperative' }}</option><option>GIC</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Numéro de registre (RCCM / CNPS / MINEPIA)' : 'Registry number (RCCM / CNPS / MINEPIA)' }}</label>
                            <input type="text" value="RC/DLA/2018/A/012345" class="{{ $plainCls }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Description de votre activité' : 'Description of your activity' }} *</label>
                            <textarea rows="3" class="w-full border border-[#E5E3E0] rounded-lg px-4 py-3 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] resize-y">{{ $isFr ? "Fabrication et vente d'objets d'art traditionnels Bamileké : masques, sculptures, statuettes, bijoux et accessoires faits main." : 'Making and selling traditional Bamileke art objects: masks, sculptures, statuettes, jewellery and handmade accessories.' }}</textarea>
                            <p class="mt-1 text-[11px] text-[#8A857A]">123/500 {{ $isFr ? 'caractères' : 'characters' }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations de contact de l\'entreprise' : 'Business contact information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone entreprise' : 'Business phone' }} *</label>
                            <div class="flex gap-2">
                                <span class="flex items-center gap-1.5 h-[46px] border border-[#E5E3E0] rounded-lg px-3 text-[13px] shrink-0">
                                    <img src="{{ asset('images/landing/ob-flag.png') }}" alt="" class="w-[20px] h-[14px] rounded-[2px]">
                                    +237
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                                </span>
                                <div class="relative flex-1">
                                    <input type="tel" value="6 90 55 65 43" class="w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 pr-10 text-[13px] focus:outline-none focus:border-[#14532D]">
                                    <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Email professionnel' : 'Professional email' }} *</label>
                            <div class="relative"><i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="email" value="contact@artisanatndop.com" class="{{ $fieldCls }}">
                                <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Site web (si disponible)' : 'Website (if available)' }}</label>
                            <div class="relative"><i data-lucide="globe" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="www.artisanatndop.com" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Réseaux sociaux' : 'Social networks' }}</label>
                            <div class="flex items-center gap-2.5">
                                @for($s = 1; $s <= 5; $s++)
                                <img src="{{ asset('images/landing/ob3-soc-' . $s . '.png') }}" alt="" class="w-[38px] h-[42px]" aria-hidden="true">
                                @endfor
                                <span class="w-[38px] h-[42px] border border-[#E5E3E0] rounded-lg flex items-center justify-center text-[#3B382F]">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Adresse de l\'entreprise' : 'Business address' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Pays' : 'Country' }} *</label>
                            <div class="relative"><i data-lucide="globe" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Région' : 'Region' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>Ouest</option><option>Centre</option><option>Littoral</option><option>Nord-Ouest</option><option>Sud-Ouest</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Ville' : 'City' }} *</label>
                            <div class="relative"><i data-lucide="building-2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Bafoussam" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Quartier / Localité' : 'District / Locality' }}</label>
                            <div class="relative"><i data-lucide="building" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Tougang" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Adresse complète' : 'Full address' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Quartier Tougang, Bafoussam, Ouest Cameroun" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Code postal (si disponible)' : 'Postal code (if available)' }}</label>
                            <input type="text" value="BP 123" class="{{ $plainCls }}">
                        </div>
                    </div>
                </section>

                <section class="relative mt-5 border border-[#EDEDEB] rounded-xl p-5 overflow-hidden">
                    <img src="{{ asset('images/landing/ob3-vase.png') }}" alt="" class="absolute right-5 bottom-2 h-[130px] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Langue(s) parlée(s)' : 'Language(s) spoken' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Sélectionnez les langues que vous maîtrisez pour communiquer avec vos clients.' : 'Select the languages you master to communicate with your clients.' }}</p>
                    <div class="mt-3.5 flex flex-wrap items-center gap-2.5">
                        @foreach([['Français', true], ['English', true], ['Bamileké', true], ['Pidgin', false]] as [$lgName, $lgOn])
                        <button type="button" class="lang-chip flex items-center gap-2 border rounded-lg px-3.5 py-2 text-[12.5px] font-medium transition-colors {{ $lgOn ? 'border-[#BFDCC8] bg-[#F3F8F3] text-[#1B1B18]' : 'border-[#E5E3E0] text-[#3B382F]' }}" data-on="{{ $lgOn ? 1 : 0 }}">
                            {{ $lgName }}
                            <span class="chip-check {{ $lgOn ? '' : 'hidden' }} w-[16px] h-[16px] rounded-full bg-[#157A43] flex items-center justify-center"><i data-lucide="check" class="w-2.5 h-2.5 text-white" style="stroke-width:3.5"></i></span>
                            <span class="chip-off {{ $lgOn ? 'hidden' : '' }} w-[16px] h-[16px] rounded-full border border-[#C9CFC9]"></span>
                        </button>
                        @endforeach
                        <span class="flex items-center gap-2 border border-[#E5E3E0] rounded-lg px-3.5 py-2 text-[12.5px] font-medium text-[#3B382F]">
                            {{ $isFr ? 'Autre' : 'Other' }}
                        </span>
                    </div>
                    <div class="mt-4 rounded-lg bg-[#F3F8F3] p-4 sm:mr-[120px] flex items-start gap-3">
                        <i data-lucide="lightbulb" class="w-5 h-5 shrink-0 text-[#14532D]" style="stroke-width:1.7"></i>
                        <div>
                            <p class="text-[12.5px] font-bold text-[#14532D]">{{ $isFr ? 'Conseil' : 'Tip' }}</p>
                            <p class="mt-1 text-[12px] text-[#3B382F] leading-relaxed">
                                {{ $isFr ? 'Des informations complètes et précises augmentent la confiance des acheteurs et améliorent votre visibilité sur la plateforme.' : 'Complete and accurate information increases buyer trust and improves your visibility on the platform.' }}
                            </p>
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(4)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 4 — craft categories ═══════ -->
            <div id="panel-4" class="ob-panel hidden">
                <button type="button" onclick="goToStep(3)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos catégories d\'artisanat' : 'Your craft categories' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Sélectionnez vos spécialités principales et secondaires.' : 'Select your main and secondary specialities.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 4 sur 10' : 'Step 4 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[40%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">40% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="grid grid-cols-1 md:grid-cols-[1.6fr_1fr] gap-6 items-start">
                        <div>
                            <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Votre spécialité principale' : 'Your main speciality' }} *</h2>
                            <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Sélectionnez la catégorie qui représente le mieux votre activité principale.' : 'Select the category that best represents your main activity.' }}</p>
                            <div class="relative mt-3.5">
                                <img src="{{ asset('images/landing/ob4-selicon.png') }}" alt="" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-[24px] h-[24px]">
                                <select class="w-full h-[50px] border border-[#E5E3E0] rounded-lg pl-12 pr-8 text-[13.5px] font-medium bg-white appearance-none cursor-pointer focus:outline-none focus:border-[#14532D]">
                                    <option>{{ $isFr ? 'Sculpture & Statuaire' : 'Sculpture & Statuary' }}</option>
                                    @foreach($secondaryCategories as [$scName, $scOn])<option>{{ str_replace("\n", ' ', $scName) }}</option>@endforeach
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i>
                            </div>
                            <div class="mt-4 rounded-lg border border-[#E5EEE7] bg-[#F7FAF7] p-4 flex items-start gap-3">
                                <i data-lucide="star" class="w-4 h-4 shrink-0 mt-0.5 text-[#14532D]" style="stroke-width:1.8"></i>
                                <p class="text-[12px] text-[#3B382F] leading-relaxed">
                                    <span class="font-bold text-[#14532D]">{{ $isFr ? 'Conseil :' : 'Tip:' }}</span>
                                    {{ $isFr ? 'Choisissez la catégorie qui représente au moins' : 'Choose the category representing at least' }} <span class="font-bold">70%</span> {{ $isFr ? 'de votre activité.' : 'of your activity.' }}<br>
                                    {{ $isFr ? 'Vous pourrez ajouter d\'autres spécialités secondaires.' : 'You can add more secondary specialities.' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('images/landing/ob4-mask.png') }}" alt="" class="w-[190px] mx-auto" aria-hidden="true">
                            <p class="mt-2 text-[12.5px] font-bold text-[#1B1B18]">Sculpture Bamiléké</p>
                            <div class="mt-2 flex items-center justify-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#157A43]"></span>
                                <span class="w-2 h-2 rounded-full bg-[#D4D6D4]"></span>
                                <span class="w-2 h-2 rounded-full bg-[#D4D6D4]"></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos spécialités secondaires' : 'Your secondary specialities' }} <span class="font-normal text-[12.5px] text-[#6F6B60]">({{ $isFr ? 'jusqu\'à 5' : 'up to 5' }})</span></h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Sélectionnez d\'autres domaines dans lesquels vous excellez.' : 'Select other areas in which you excel.' }}</p>
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach($secondaryCategories as $scIdx => [$scName, $scOn])
                        <label class="cursor-pointer">
                            <input type="checkbox" class="sr-only sc-check" @checked($scOn)>
                            <div class="relative h-full rounded-xl border p-3 pt-4 text-center transition-all {{ $scOn ? 'border-[#0F5132] shadow-[0_0_0_1px_#0F5132]' : 'border-[#E7E9E7] hover:border-[#C9CFC9]' }} sc-card">
                                <span class="absolute top-2.5 right-2.5 sc-on {{ $scOn ? '' : 'hidden' }} w-[18px] h-[18px] rounded-full bg-[#157A43] flex items-center justify-center"><i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3.5"></i></span>
                                <span class="absolute top-2.5 right-2.5 sc-off {{ $scOn ? 'hidden' : '' }} w-[16px] h-[16px] rounded-[4px] border border-[#C9CFC9]"></span>
                                <img src="{{ asset('images/landing/ob4-cat-' . ($scIdx + 1) . '.png') }}" alt="" class="w-[58px] h-[56px] mx-auto" aria-hidden="true">
                                <span class="mt-2.5 block text-[11.5px] font-semibold text-[#1B1B18] leading-tight whitespace-pre-line">{{ $scName }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="mt-4 text-[12px] text-[#3B382F]">{{ $isFr ? 'Catégories sélectionnées :' : 'Selected categories:' }} <span id="sc-count" class="font-bold text-[#14532D]">4 / 5</span></p>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Votre style et vos techniques' : 'Your style and techniques' }} <span class="font-normal text-[12.5px] text-[#6F6B60]">({{ $isFr ? 'optionnel' : 'optional' }})</span></h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Décrivez brièvement votre style et les techniques que vous utilisez.' : 'Briefly describe your style and the techniques you use.' }}</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">Style / Inspiration</label>
                            <div class="relative"><i data-lucide="palette" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Traditionnel' : 'Traditional' }}</option><option>{{ $isFr ? 'Moderne' : 'Modern' }}</option><option>{{ $isFr ? 'Contemporain' : 'Contemporary' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Techniques principales' : 'Main techniques' }}</label>
                            <div class="relative flex flex-wrap items-center gap-1.5 min-h-[46px] border border-[#E5E3E0] rounded-lg px-3 py-2 pr-8">
                                @foreach([$isFr ? 'Sculpture sur bois' : 'Wood carving', $isFr ? 'Gravure' : 'Engraving', 'Assemblage'] as $tech)
                                <span class="flex items-center gap-1.5 bg-[#F3F8F3] border border-[#DCEBE0] rounded-md px-2 py-1 text-[11.5px] text-[#14532D] font-medium">
                                    {{ $tech }}
                                    <button type="button" class="tech-x text-[#14532D] hover:text-red-600" aria-label="{{ $isFr ? 'Retirer' : 'Remove' }}"><i data-lucide="x" class="w-3 h-3"></i></button>
                                </span>
                                @endforeach
                                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Description de votre style (optionnel)' : 'Description of your style (optional)' }}</label>
                            <textarea rows="2" class="w-full border border-[#E5E3E0] rounded-lg px-4 py-3 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] resize-y">{{ $isFr ? "Sculptures traditionnelles d'inspiration Bamiléké. Utilisation de bois locaux (ébène, iroko) et techniques de gravure ancestrales transmises de génération en génération." : 'Traditional Bamileke-inspired sculptures. Use of local woods (ebony, iroko) and ancestral engraving techniques passed down from generation to generation.' }}</textarea>
                            <p class="mt-1 text-right text-[11px] text-[#8A857A]">142/500 {{ $isFr ? 'caractères' : 'characters' }}</p>
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(3)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(5)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 5 — workshop / location ═══════ -->
            <div id="panel-5" class="ob-panel hidden">
                <button type="button" onclick="goToStep(4)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Votre atelier / Localisation' : 'Your workshop / Location' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[440px]">{{ $isFr ? 'Indiquez où vous exercez votre activité. Vous pouvez ajouter plusieurs lieux si nécessaire.' : 'Tell us where you carry out your activity. You can add several places if necessary.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 5 sur 10' : 'Step 5 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[50%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">50% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="flex items-center gap-2.5 text-[14.5px] font-bold text-[#1B1B18]">
                        <span class="w-[26px] h-[26px] rounded-md bg-[#F3F8F3] border border-[#DCEBE0] flex items-center justify-center"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#14532D]"></i></span>
                        {{ $isFr ? 'Lieu principal de l\'activité' : 'Main place of activity' }}
                    </h2>
                    <p class="mt-3.5 text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Type de lieu' : 'Type of place' }} *</p>
                    <div class="mt-2.5 grid grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach([
                            ['store',   $isFr ? 'Atelier / Boutique' : 'Workshop / Shop', $isFr ? "Lieu fixe de production\nou de vente" : "Fixed production or\nsales place"],
                            ['house',   $isFr ? 'Domicile' : 'Home',                      $isFr ? "Activité exercée\nà domicile" : "Activity carried out\nat home"],
                            ['users',   $isFr ? 'Coopérative / Groupement' : 'Cooperative / Group', $isFr ? "Atelier collectif\nou partagé" : "Collective or shared\nworkshop"],
                            ['map-pin', $isFr ? 'Autre' : 'Other',                        $isFr ? "Autre type\nde lieu" : "Other type\nof place"],
                        ] as $ptIdx => [$ptIcon, $ptTitle, $ptSub])
                        <label class="cursor-pointer">
                            <input type="radio" name="place_type" class="sr-only pt-check" @checked($ptIdx === 0)>
                            <div class="relative h-full rounded-xl border p-4 transition-all pt-card {{ $ptIdx === 0 ? 'border-[#0F5132] shadow-[0_0_0_1px_#0F5132]' : 'border-[#E7E9E7] hover:border-[#C9CFC9]' }}">
                                <span class="absolute top-3.5 right-3.5 pt-on {{ $ptIdx === 0 ? '' : 'hidden' }} w-[18px] h-[18px] rounded-full bg-[#157A43] flex items-center justify-center"><span class="w-[7px] h-[7px] rounded-full bg-white"></span></span>
                                <span class="absolute top-3.5 right-3.5 pt-off {{ $ptIdx === 0 ? 'hidden' : '' }} w-[18px] h-[18px] rounded-full border border-[#C9CFC9]"></span>
                                <span class="w-[38px] h-[38px] rounded-lg bg-[#F3F6F3] flex items-center justify-center"><i data-lucide="{{ $ptIcon }}" class="w-5 h-5 text-[#1B4332]" style="stroke-width:1.6"></i></span>
                                <span class="mt-3 block text-[13px] font-bold text-[#1B1B18] leading-snug">{{ $ptTitle }}</span>
                                <span class="mt-1 block text-[11px] text-[#6F6B60] leading-snug whitespace-pre-line">{{ $ptSub }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="mt-5 grid grid-cols-1 md:grid-cols-[1.55fr_1fr] gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Nom de l\'atelier / Boutique' : 'Workshop / Shop name' }} *</label>
                            <input type="text" value="Atelier Ndop Créations" class="{{ $plainCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Année d\'installation' : 'Year established' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>2016</option>@for($y = 2024; $y >= 1990; $y--)<option>{{ $y }}</option>@endfor</select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Adresse complète' : 'Full address' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Quartier Tougang, Bafoussam, Ouest Cameroun" class="{{ $fieldCls }}"></div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
                        @foreach([[$isFr ? 'Région' : 'Region', 'Ouest'], [$isFr ? 'Département' : 'Department', 'Mifi'], ['Arrondissement', 'Bafoussam 1er']] as [$geoLabel, $geoVal])
                        <div>
                            <label class="{{ $labelCls }}">{{ $geoLabel }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $geoVal }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-[1.55fr_1fr] gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Quartier / Localité' : 'District / Locality' }} *</label>
                            <input type="text" value="Tougang" class="{{ $plainCls }}">
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Code postal (si disponible)' : 'Postal code (if available)' }}</label>
                            <input type="text" value="BP 123" class="{{ $plainCls }}">
                        </div>
                    </div>

                    <p class="mt-5 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Localiser votre atelier sur la carte' : 'Locate your workshop on the map' }} *</p>
                    <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Déplacez le marqueur pour indiquer la position exacte de votre atelier.' : 'Move the marker to indicate the exact position of your workshop.' }}</p>
                    <img src="{{ asset('images/landing/ob5-map.png') }}" alt="" class="mt-3 w-full rounded-xl border border-[#E5E3E0]">
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-[#55524A]">{{ $isFr ? 'Coordonnées :' : 'Coordinates:' }} <span id="ob-geo-coords">5.4773° N, 10.4175° E</span></p>
                        <button type="button" id="ob-geo-btn" class="inline-flex items-center gap-2 border border-[#E5E3E0] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[12.5px] font-semibold text-[#1B1B18]">
                            <i data-lucide="locate-fixed" class="w-4 h-4"></i>
                            {{ $isFr ? 'Utiliser ma position actuelle' : 'Use my current position' }}
                        </button>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="flex items-center gap-2.5 text-[14.5px] font-bold text-[#1B1B18]">
                        <span class="w-[26px] h-[26px] rounded-md bg-[#F3F8F3] border border-[#DCEBE0] flex items-center justify-center"><i data-lucide="info" class="w-3.5 h-3.5 text-[#14532D]"></i></span>
                        {{ $isFr ? 'Informations pratiques' : 'Practical information' }}
                    </h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Téléphone de l\'atelier / Boutique' : 'Workshop / Shop phone' }}</label>
                            <div class="flex gap-2">
                                <span class="flex items-center gap-1.5 h-[46px] border border-[#E5E3E0] rounded-lg px-3 text-[13px] shrink-0">
                                    <img src="{{ asset('images/landing/ob-flag.png') }}" alt="" class="w-[20px] h-[14px] rounded-[2px]">
                                    +237
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                                </span>
                                <div class="relative flex-1">
                                    <input type="tel" value="6 70 11 22 33" class="w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 pr-10 text-[13px] focus:outline-none focus:border-[#14532D]">
                                    <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Email de l\'atelier / Boutique (optionnel)' : 'Workshop / Shop email (optional)' }}</label>
                            <div class="relative"><i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="email" value="contact@ndopcreations.cm" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Horaires d\'ouverture' : 'Opening hours' }}</label>
                            <div class="relative"><i data-lucide="clock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>Lun - Sam : 08h00 - 18h00</option><option>Lun - Ven : 08h00 - 17h00</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Jours de fermeture' : 'Closing days' }}</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Dimanche' : 'Sunday' }}</option><option>{{ $isFr ? 'Aucun' : 'None' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="{{ $labelCls }}">{{ $isFr ? 'Repères / Informations complémentaires (optionnel)' : 'Landmarks / Additional information (optional)' }}</label>
                            <textarea rows="2" class="w-full border border-[#E5E3E0] rounded-lg px-4 py-3 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] resize-y">{{ $isFr ? 'Près de l\'école publique de Tougang, derrière la pharmacie Sainte Famille.' : 'Near the Tougang public school, behind the Sainte Famille pharmacy.' }}</textarea>
                            <p class="mt-1 text-right text-[11px] text-[#8A857A]">68/300 {{ $isFr ? 'caractères' : 'characters' }}</p>
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(4)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(6)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 6 — products & services ═══════ -->
            <div id="panel-6" class="ob-panel hidden">
                <button type="button" onclick="goToStep(5)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos produits & services' : 'Your products & services' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Présentez vos créations, produits et services pour attirer plus de clients.' : 'Present your creations, products and services to attract more clients.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 6 sur 10' : 'Step 6 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[60%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">60% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Type d\'offre' : 'Type of offer' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Sélectionnez ce que vous proposez.' : 'Select what you offer.' }}</p>
                    <div class="mt-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        @foreach([
                            ['ob6-offer-1.png', $isFr ? 'Produits physiques' : 'Physical products', $isFr ? "Objets artisanaux, articles\nmatériels à vendre" : "Craft objects, material\nitems for sale"],
                            ['ob6-offer-2.png', 'Services', $isFr ? "Prestations artisanales,\nmain d'œuvre, formations" : "Craft services,\nlabour, training"],
                            ['ob6-offer-3.png', $isFr ? 'Produits & Services' : 'Products & Services', $isFr ? "Une combinaison des\ndeux" : "A combination of\nboth"],
                        ] as $ofIdx => [$ofIcon, $ofTitle, $ofSub])
                        <label class="cursor-pointer">
                            <input type="radio" name="offer_type" class="sr-only of-check" @checked($ofIdx === 0)>
                            <div class="relative h-full rounded-xl border p-5 transition-all of-card {{ $ofIdx === 0 ? 'border-[#0F5132] shadow-[0_0_0_1px_#0F5132]' : 'border-[#E7E9E7] hover:border-[#C9CFC9]' }}">
                                <span class="absolute top-4 right-4 of-on {{ $ofIdx === 0 ? '' : 'hidden' }} w-[18px] h-[18px] rounded-full bg-[#157A43] flex items-center justify-center"><span class="w-[7px] h-[7px] rounded-full bg-white"></span></span>
                                <span class="absolute top-4 right-4 of-off {{ $ofIdx === 0 ? 'hidden' : '' }} w-[18px] h-[18px] rounded-full border border-[#C9CFC9]"></span>
                                <img src="{{ asset('images/landing/' . $ofIcon) }}" alt="" class="w-[46px] h-[46px]" aria-hidden="true">
                                <span class="mt-3 block text-[14px] font-bold text-[#1B1B18]">{{ $ofTitle }}</span>
                                <span class="mt-1 block text-[11.5px] text-[#6F6B60] leading-snug whitespace-pre-line">{{ $ofSub }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos produits phares' : 'Your flagship products' }} <span class="font-normal text-[12.5px] text-[#6F6B60]">({{ $isFr ? 'jusqu\'à 10' : 'up to 10' }})</span></h2>
                        <button type="button" id="add-product" class="inline-flex items-center gap-2 bg-obdeep hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
                        </button>
                    </div>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Ajoutez les produits qui représentent le mieux votre savoir-faire.' : 'Add the products that best represent your know-how.' }}</p>
                    <div id="product-rows" class="mt-4 divide-y divide-[#F0F0EE]">
                        @foreach($step6Products as [$p6Img, $p6Name, $p6Cat, $p6Price])
                        <div class="ob-prod-row flex flex-wrap items-center gap-3 py-3.5">
                            <i data-lucide="grip-vertical" class="w-4 h-4 text-[#B4B0A6] shrink-0 hidden sm:block"></i>
                            <img src="{{ asset('images/landing/' . $p6Img) }}" alt="" class="w-[56px] h-[56px] rounded-lg object-cover shrink-0 bg-[#F5F5F3]">
                            <input type="text" value="{{ $p6Name }}" class="flex-1 min-w-[170px] h-[42px] border border-[#E5E3E0] rounded-lg px-3.5 text-[12.5px] focus:outline-none focus:border-[#14532D]">
                            <div class="w-[190px]">
                                <label class="block text-[10.5px] text-[#8A857A] mb-1">{{ $isFr ? 'Catégorie' : 'Category' }}</label>
                                <div class="relative">
                                    <select class="w-full h-[38px] border border-[#E5E3E0] rounded-lg px-3 pr-7 text-[12px] bg-white appearance-none cursor-pointer focus:outline-none">
                                        <option>{{ $p6Cat }}</option>
                                        @foreach($secondaryCategories as [$scName, $scOn])<option>{{ str_replace("\n", ' ', $scName) }}</option>@endforeach
                                    </select>
                                    <i data-lucide="chevron-down" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#8A857A] pointer-events-none"></i>
                                </div>
                            </div>
                            <div class="w-[92px]">
                                <label class="block text-[10.5px] text-[#8A857A] mb-1">{{ $isFr ? 'Prix (FCFA)' : 'Price (FCFA)' }}</label>
                                <input type="text" value="{{ $p6Price }}" class="w-full h-[38px] border border-[#E5E3E0] rounded-lg px-3 text-[12px] focus:outline-none focus:border-[#14532D]">
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer shrink-0 select-none">
                                <input type="checkbox" checked class="sr-only avail-check">
                                <span class="avail-track relative w-[38px] h-[21px] rounded-full bg-[#157A43] transition-colors"><span class="avail-thumb absolute top-[2.5px] left-[19.5px] w-[16px] h-[16px] rounded-full bg-white shadow transition-all"></span></span>
                                <span class="text-[11px] text-[#3B382F]">Disponible</span>
                            </label>
                            <button type="button" class="row-del w-[34px] h-[34px] shrink-0 rounded-lg border border-[#F5D9D9] bg-[#FDF3F3] flex items-center justify-center text-[#D43C3C] hover:bg-[#FBE4E4]" aria-label="{{ $isFr ? 'Supprimer' : 'Delete' }}">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-center text-[12.5px] font-semibold text-[#14532D]"><span id="prod-count">{{ $isFr ? 'Afficher 5/5 produits' : 'Showing 5/5 products' }}</span></p>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos services' : 'Your services' }} <span class="font-normal text-[12.5px] text-[#6F6B60]">({{ $isFr ? 'optionnel' : 'optional' }})</span></h2>
                        <button type="button" id="add-service" class="inline-flex items-center gap-2 bg-obdeep hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ $isFr ? 'Ajouter un service' : 'Add a service' }}
                        </button>
                    </div>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Dites-nous les services que vous proposez.' : 'Tell us the services you offer.' }}</p>
                    <div id="service-rows" class="mt-4 space-y-3">
                        @foreach($step6Services as $svIdx => [$svIcon, $svTitle, $svDesc, $svPrice])
                        <div class="ob-svc-row flex flex-wrap items-stretch gap-3">
                            <div class="flex-1 min-w-[260px] flex items-start gap-3.5 border border-[#EDEDEB] rounded-xl p-4">
                                <img src="{{ asset('images/landing/' . $svIcon) }}" alt="" class="w-[42px] h-[42px] shrink-0" aria-hidden="true">
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold text-[#1B1B18]">{{ $svTitle }}</p>
                                    <p class="mt-0.5 text-[11.5px] text-[#6F6B60] leading-snug">{{ $svDesc }}</p>
                                </div>
                            </div>
                            <div class="w-[160px] shrink-0">
                                @if($svIdx === 0)<label class="block text-[10.5px] text-[#8A857A] mb-1">{{ $isFr ? 'Prix à partir de (FCFA)' : 'Price from (FCFA)' }}</label>@endif
                                <div class="flex items-center gap-2">
                                    <input type="text" value="{{ $svPrice }}" class="flex-1 min-w-0 h-[42px] border border-[#E5E3E0] rounded-lg px-3 text-[12.5px] focus:outline-none focus:border-[#14532D]">
                                    <button type="button" class="row-del w-[34px] h-[34px] shrink-0 rounded-lg border border-[#F5D9D9] bg-[#FDF3F3] flex items-center justify-center text-[#D43C3C] hover:bg-[#FBE4E4]" aria-label="{{ $isFr ? 'Supprimer' : 'Delete' }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="relative mt-5 rounded-xl bg-[#F3F6F3] overflow-hidden">
                        <img src="{{ asset('images/landing/ob6-vases.png') }}" alt="" class="absolute right-4 bottom-0 h-[92%] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                        <div class="relative flex items-start gap-3 p-4 sm:pr-[140px]">
                            <i data-lucide="lightbulb" class="w-5 h-5 shrink-0 text-[#14532D]" style="stroke-width:1.7"></i>
                            <div>
                                <p class="text-[12.5px] font-bold text-[#14532D]">{{ $isFr ? 'Conseil' : 'Tip' }}</p>
                                <p class="mt-1 text-[12px] text-[#3B382F] leading-relaxed">
                                    {{ $isFr ? "Ajoutez des produits de qualité avec des photos claires pour augmenter vos chances d'être trouvé par les acheteurs." : 'Add quality products with clear photos to increase your chances of being found by buyers.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(5)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(7)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 7 — media gallery ═══════ -->
            <div id="panel-7" class="ob-panel hidden">
                <button type="button" onclick="goToStep(6)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18] flex flex-wrap items-center gap-3">
                            {{ $isFr ? 'Galerie média' : 'Media gallery' }}
                            <span class="inline-flex items-center gap-1.5 bg-[#ECF4EE] rounded-full px-3.5 py-1.5 text-[11.5px] font-semibold text-[#1B1B18]">
                                {{ $isFr ? 'Étape importante' : 'Important step' }}
                                <i data-lucide="star" class="w-3.5 h-3.5" style="color:#F2B32E;fill:#F2B32E"></i>
                            </span>
                        </h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[430px]">{{ $isFr ? 'Montrez votre savoir-faire ! Ajoutez des photos et vidéos de qualité pour inspirer confiance aux acheteurs.' : 'Show your know-how! Add quality photos and videos to inspire buyer confidence.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 7 sur 10' : 'Step 7 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[70%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">70% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Ajouter des photos et vidéos' : 'Add photos and videos' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Formats acceptés : JPG, PNG, WEBP, MP4 (Max 20 Mo par fichier)' : 'Accepted formats: JPG, PNG, WEBP, MP4 (Max 20 MB per file)' }}</p>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @foreach($step7Uploads as [$upIcon, $upTitle, $upSub, $upBtn, $upActive])
                        <div class="rounded-xl border p-4 text-center {{ $upActive ? 'border-[#157A43] bg-[#F7FBF8]' : 'border-[#E7E9E7] bg-white' }}">
                            <img src="{{ asset('images/landing/' . $upIcon) }}" alt="" class="w-[52px] h-[52px] mx-auto" aria-hidden="true">
                            <h3 class="mt-3 text-[13px] font-bold text-[#1B1B18]">{{ $upTitle }}</h3>
                            <p class="mt-1 text-[11.5px] text-[#6F6B60]">{{ $upSub }}</p>
                            <button type="button" class="ob-upload mt-3.5 text-[12px] font-semibold px-4 py-2 rounded-lg transition-colors {{ $upActive ? 'bg-obdeep hover:bg-leaf text-white' : 'border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18]' }}">{{ $upBtn }}</button>
                        </div>
                        @endforeach
                    </div>
                </section>

                <div class="relative mt-4 rounded-xl bg-[#EFF5F0] overflow-visible">
                    <img src="{{ asset('images/landing/ob7-tip-art.png') }}" alt="" class="absolute right-3 bottom-0 h-[71px] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                    <div class="relative flex items-center gap-3.5 px-4 py-3 sm:pr-[145px]">
                        <img src="{{ asset('images/landing/ob7-bulb.png') }}" alt="" class="w-[38px] h-[38px] shrink-0" aria-hidden="true">
                        <p class="text-[12px] text-[#3B382F] leading-relaxed">
                            <span class="font-bold text-[#14532D]">{{ $isFr ? 'Conseil :' : 'Tip:' }}</span>
                            {{ $isFr ? "Des visuels de qualité augmentent jusqu'à 80% de chances d'être contacté par des acheteurs." : 'Quality visuals increase your chances of being contacted by buyers by up to 80%.' }}
                        </p>
                    </div>
                </div>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos photos' : 'Your photos' }} <span class="font-normal text-[13px]">(8/20)</span></h2>
                    <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($step7Photos as [$phImg, $phName, $phSize])
                        <div class="bg-white border border-[#F0F0EE] rounded-xl shadow-sm overflow-hidden">
                            <img src="{{ asset('images/landing/' . $phImg) }}" alt="{{ $phName }}" class="w-full h-[124px] object-cover">
                            <div class="px-2.5 py-2.5 flex items-center justify-between gap-1.5">
                                <div class="min-w-0">
                                    <p class="text-[11.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $phName }}</p>
                                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $phSize }}</p>
                                </div>
                                <span class="w-[20px] h-[20px] shrink-0 rounded-full bg-[#14532D] flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3"></i>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex flex-col lg:flex-row gap-4">
                        <div class="lg:w-[225px] shrink-0 border-2 border-dashed border-[#D8DED9] rounded-xl p-5 flex flex-col items-center justify-center text-center">
                            <span class="w-12 h-12 rounded-full bg-[#E9F2EB] flex items-center justify-center">
                                <i data-lucide="plus" class="w-5 h-5 text-[#14532D]" style="stroke-width:2.2"></i>
                            </span>
                            <p class="mt-3 text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Ajouter plus de photos' : 'Add more photos' }}</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'ou glisser-déposer ici' : 'or drag & drop here' }}</p>
                        </div>
                        <div class="relative flex-1 bg-[#F6F8F7] rounded-xl p-4 sm:pr-[150px]">
                            <img src="{{ asset('images/landing/ob7-cam.png') }}" alt="" class="absolute right-3 bottom-3 h-[100px] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                            <h3 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Bonnes pratiques' : 'Good practices' }}</h3>
                            <ul class="mt-2.5 space-y-2">
                                @foreach($step7Practices as $bp)
                                <li class="flex items-center gap-2.5">
                                    <span class="w-[15px] h-[15px] shrink-0 rounded-full bg-[#157A43] flex items-center justify-center">
                                        <i data-lucide="check" class="w-2.5 h-2.5 text-white" style="stroke-width:3.5"></i>
                                    </span>
                                    <span class="text-[12px] text-[#3B382F]">{{ $bp }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos vidéos' : 'Your videos' }} <span class="font-normal text-[13px]">(1/5)</span></h2>
                        <button type="button" id="add-video" class="inline-flex items-center gap-1.5 border border-[#9DC3AA] hover:border-[#14532D] text-[#14532D] text-[12.5px] font-semibold px-3.5 py-2 rounded-lg transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ $isFr ? 'Ajouter une vidéo' : 'Add a video' }}
                        </button>
                    </div>
                    <div class="ob-video-row mt-4 flex flex-wrap items-center gap-3.5">
                        <img src="{{ asset('images/landing/ob7-vid.png') }}" alt="" class="w-[143px] h-[71px] rounded-lg object-cover shrink-0">
                        <div class="flex-1 min-w-[220px] border border-[#F0F0EE] rounded-xl px-4 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? "Sculpture d'un masque traditionnel" : 'Carving a traditional mask' }}</p>
                                <p class="mt-1.5 text-[11.5px] text-[#6F6B60]">MP4 · 18.6 MB</p>
                            </div>
                            <i data-lucide="circle-check" class="w-6 h-6 shrink-0 text-[#157A43]" style="stroke-width:1.7"></i>
                        </div>
                        <button type="button" class="vid-del w-[34px] h-[34px] shrink-0 rounded-lg border border-[#F5D9D9] bg-[#FDF3F3] flex items-center justify-center text-[#D43C3C] hover:bg-[#FBE4E4]" aria-label="{{ $isFr ? 'Supprimer' : 'Delete' }}">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(6)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(8)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 8 — certifications & documents ═══════ -->
            <div id="panel-8" class="ob-panel hidden">
                <button type="button" onclick="goToStep(7)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">Certifications & Documents</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[460px]">{{ $isFr ? 'Ajoutez vos certificats, attestations et documents officiels pour inspirer confiance aux acheteurs et partenaires.' : 'Add your certificates, attestations and official documents to inspire trust among buyers and partners.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 8 sur 10' : 'Step 8 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[80%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">80% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-xl bg-[#F3F7F4] px-4 py-3.5 flex items-start gap-3.5">
                    <img src="{{ asset('images/landing/ob8-shield.png') }}" alt="" class="w-[34px] h-[34px] shrink-0" aria-hidden="true">
                    <div>
                        <p class="text-[12.5px] text-[#1B1B18]">{{ $isFr ? 'Vos documents sont sécurisés et utilisés uniquement pour la vérification et la mise en valeur de votre profil.' : 'Your documents are secure and used only for the verification and enhancement of your profile.' }}</p>
                        <p class="mt-1 text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Formats acceptés : PDF, JPG, PNG (Max 10 Mo par fichier)' : 'Accepted formats: PDF, JPG, PNG (Max 10 MB per file)' }}</p>
                    </div>
                </div>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos certificats et attestations' : 'Your certificates and attestations' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Téléversez les documents qui prouvent votre légitimité et votre expertise.' : 'Upload the documents that prove your legitimacy and expertise.' }}</p>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @foreach($step8Certs as [$ctImg, $ctName, $ctNum])
                        <div class="ob-del-target border border-[#DCEBE0] rounded-xl p-3 flex flex-col">
                            <img src="{{ asset('images/landing/' . $ctImg) }}" alt="{{ $ctName }}" class="w-full h-[112px] object-contain">
                            <h3 class="mt-2.5 text-[12px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $ctName }}</h3>
                            <p class="mt-1 text-[11px] text-[#6F6B60] whitespace-nowrap">{{ $ctNum }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <a href="{{ asset('images/landing/' . $ctImg) }}" target="_blank" rel="noopener" class="border border-[#9DC3AA] hover:border-[#14532D] text-[#14532D] text-[12.5px] font-semibold px-5 py-1.5 rounded-lg transition-colors">{{ $isFr ? 'Voir' : 'View' }}</a>
                                <button type="button" class="ob-card-del w-[32px] h-[32px] rounded-lg border border-[#F5D9D9] bg-[#FDF3F3] flex items-center justify-center text-[#D43C3C] hover:bg-[#FBE4E4]" aria-label="{{ $isFr ? 'Supprimer' : 'Delete' }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @foreach($step8Empty as $emIdx => [$emTitle, $emSub])
                        @if($emIdx === 0)
                        <div class="border-2 border-dashed border-[#D8DED9] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                            <span class="w-[52px] h-[52px] rounded-full bg-[#E9F2EB] flex items-center justify-center">
                                <i data-lucide="plus" class="w-5 h-5 text-[#14532D]" style="stroke-width:2.2"></i>
                            </span>
                            <h3 class="mt-4 text-[12px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $emTitle }}</h3>
                            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $emSub }}</p>
                            <button type="button" class="ob-upload mt-3.5 border border-[#9DC3AA] hover:border-[#14532D] text-[#14532D] text-[12.5px] font-semibold px-5 py-2 rounded-lg transition-colors">{{ $isFr ? 'Téléverser' : 'Upload' }}</button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <div class="mt-3.5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @foreach($step8Empty as $emIdx => [$emTitle, $emSub])
                        @if($emIdx > 0)
                        <div class="border-2 border-dashed border-[#D8DED9] rounded-xl p-4 py-6 flex flex-col items-center justify-center text-center">
                            <span class="w-[52px] h-[52px] rounded-full bg-[#E9F2EB] flex items-center justify-center">
                                <i data-lucide="plus" class="w-5 h-5 text-[#14532D]" style="stroke-width:2.2"></i>
                            </span>
                            <h3 class="mt-4 text-[12px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $emTitle }}</h3>
                            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $emSub }}</p>
                            <button type="button" class="ob-upload mt-3.5 border border-[#9DC3AA] hover:border-[#14532D] text-[#14532D] text-[12.5px] font-semibold px-5 py-2 rounded-lg transition-colors">{{ $isFr ? 'Téléverser' : 'Upload' }}</button>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex-1 min-w-[280px]">
                            <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Autres documents utiles' : 'Other useful documents' }} <span class="font-normal text-[12.5px] text-[#6F6B60]">({{ $isFr ? 'optionnel' : 'optional' }})</span></h2>
                            <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Ajoutez tout document complémentaire qui peut aider les acheteurs à mieux vous connaître.' : 'Add any additional document that can help buyers get to know you better.' }}</p>
                        </div>
                        <button type="button" class="ob-upload inline-flex items-center gap-2 bg-obdeep hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ $isFr ? 'Ajouter un document' : 'Add a document' }}
                        </button>
                    </div>
                    <div class="mt-4 border border-[#EFEFED] rounded-xl overflow-hidden">
                        <div class="hidden sm:grid grid-cols-[minmax(0,1.4fr)_90px_minmax(0,1.2fr)_58px_78px] gap-2 items-center bg-[#F7F7F8] px-4 py-3 text-[12px] font-semibold text-[#1B1B18]">
                            <span>{{ $isFr ? 'Nom du document' : 'Document name' }}</span>
                            <span>Type</span>
                            <span>{{ $isFr ? 'Fichier' : 'File' }}</span>
                            <span>{{ $isFr ? 'Taille' : 'Size' }}</span>
                            <span>Action</span>
                        </div>
                        @foreach($step8Docs as [$fdIcon, $fdName, $fdType, $fdFile, $fdSize])
                        <div class="ob-del-target grid grid-cols-1 sm:grid-cols-[minmax(0,1.4fr)_90px_minmax(0,1.2fr)_58px_78px] gap-2 items-center px-4 py-3 border-t border-[#F0F0EE] text-[12px] text-[#1B1B18]">
                            <span class="flex items-center gap-3 min-w-0">
                                <img src="{{ asset('images/landing/' . $fdIcon) }}" alt="" class="w-[23px] h-[25px] shrink-0" aria-hidden="true">
                                <span class="truncate">{{ $fdName }}</span>
                            </span>
                            <span>{{ $fdType }}</span>
                            <span class="truncate">{{ $fdFile }}</span>
                            <span>{{ $fdSize }}</span>
                            <span class="flex items-center gap-2">
                                <span class="w-[32px] h-[32px] rounded-lg border border-[#E5E9E5] bg-white flex items-center justify-center text-[#14532D]">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </span>
                                <button type="button" class="ob-card-del w-[32px] h-[32px] rounded-lg border border-[#F5D9D9] bg-[#FDF3F3] flex items-center justify-center text-[#D43C3C] hover:bg-[#FBE4E4]" aria-label="{{ $isFr ? 'Supprimer' : 'Delete' }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </span>
                        </div>
                        @endforeach
                    </div>
                </section>

                <div class="relative mt-4 rounded-xl bg-[#F1F6F2] overflow-hidden">
                    <img src="{{ asset('images/landing/ob8-docs-art.png') }}" alt="" class="absolute right-4 bottom-0 h-[92%] pointer-events-none select-none hidden sm:block" aria-hidden="true">
                    <div class="relative flex items-start gap-3.5 p-4 sm:pr-[190px]">
                        <img src="{{ asset('images/landing/ob8-bulb.png') }}" alt="" class="w-[36px] h-[38px] shrink-0" aria-hidden="true">
                        <div>
                            <h3 class="text-[13px] font-bold text-[#14532D]">{{ $isFr ? 'Conseils pour de bons documents' : 'Tips for good documents' }}</h3>
                            <ul class="mt-2 space-y-1.5">
                                @foreach(($isFr ? [
                                    'Assurez-vous que les documents sont lisibles et à jour.',
                                    'Utilisez des fichiers de bonne qualité et non flous.',
                                    'Les documents authentiques augmentent votre crédibilité et vos ventes.',
                                ] : [
                                    'Make sure the documents are legible and up to date.',
                                    'Use good-quality, non-blurry files.',
                                    'Authentic documents increase your credibility and your sales.',
                                ]) as $tip)
                                <li class="flex items-center gap-2.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5 shrink-0 text-[#157A43]" style="stroke-width:3"></i>
                                    <span class="text-[12px] text-[#3B382F]">{{ $tip }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(7)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(9)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 9 — verification & compliance ═══════ -->
            <div id="panel-9" class="ob-panel hidden">
                <button type="button" onclick="goToStep(8)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18] flex flex-wrap items-center gap-3">
                            {{ $isFr ? 'Vérification & Conformité' : 'Verification & Compliance' }}
                            <span class="inline-flex items-center gap-1.5 bg-[#EFF6F1] rounded-full px-3.5 py-1.5 text-[11.5px] font-semibold text-[#14532D]">
                                {{ $isFr ? 'Étape importante' : 'Important step' }}
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#14532D]" style="stroke-width:2"></i>
                            </span>
                        </h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[440px]">{{ $isFr ? 'Aidez-nous à vérifier votre identité et votre activité pour renforcer la confiance des acheteurs et débloquer tous les avantages.' : 'Help us verify your identity and your activity to strengthen buyer trust and unlock all the advantages.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 9 sur 10' : 'Step 9 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[90%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">90% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">1. {{ $isFr ? "Vérification d'identité" : 'Identity verification' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Votre identité doit être vérifiée pour sécuriser votre compte.' : 'Your identity must be verified to secure your account.' }}</p>
                    <div class="mt-3.5 border border-[#F0F0EE] rounded-xl p-3.5 flex flex-wrap items-center gap-3">
                        <img src="{{ asset('images/landing/ob9-idcard.png') }}" alt="" class="w-[49px] h-[50px] shrink-0" aria-hidden="true">
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $isFr ? "Carte Nationale d'Identité" : 'National Identity Card' }}</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60]">Recto / Verso</p>
                        </div>
                        <span class="flex items-center gap-2 ml-1">
                            <span class="bg-[#EBF5EB] text-[#14532D] text-[11.5px] font-semibold rounded-md px-2.5 py-1">{{ $isFr ? 'Vérifié' : 'Verified' }}</span>
                            <i data-lucide="circle-check" class="w-[18px] h-[18px] text-[#157A43]" style="stroke-width:1.8"></i>
                        </span>
                        <span class="ml-auto flex items-center gap-2">
                            <img src="{{ asset('images/landing/ob9-cni-1.png') }}" alt="" class="w-[105px] h-[66px] rounded-md object-cover">
                            <img src="{{ asset('images/landing/ob9-cni-2.png') }}" alt="" class="w-[107px] h-[66px] rounded-md object-cover">
                        </span>
                    </div>
                    <div class="mt-3.5 rounded-lg bg-[#F3F7F3] px-4 py-3 flex items-center gap-2.5">
                        <i data-lucide="circle-check" class="w-[18px] h-[18px] shrink-0 text-[#157A43]" style="stroke-width:1.8"></i>
                        <p class="text-[12.5px] text-[#14532D]">{{ $isFr ? 'Identité vérifiée avec succès le 12 Mai 2024 à 14:32' : 'Identity successfully verified on 12 May 2024 at 14:32' }}</p>
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">2. {{ $isFr ? "Vérification de l'entreprise" : 'Business verification' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? "Nous vérifions l'existence légale de votre activité." : 'We verify the legal existence of your activity.' }}</p>
                    <div class="mt-3.5 grid grid-cols-1 lg:grid-cols-[1fr_1fr_0.52fr] gap-3">
                        @foreach([
                            [$isFr ? 'Registre de Commerce' : 'Trade Register', 'RC/DLA/2016/B/12345'],
                            [$isFr ? 'Attestation Fiscale' : 'Tax Certificate', 'N° 00123/2024/DGI'],
                        ] as [$bvName, $bvNum])
                        <div class="border border-[#F0F0EE] rounded-xl p-3.5 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[12.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $bvName }}</p>
                                <p class="mt-1 text-[11.5px] text-[#6F6B60] whitespace-nowrap">{{ $bvNum }}</p>
                            </div>
                            <span class="flex items-center gap-1.5 shrink-0">
                                <span class="bg-[#EBF5EB] text-[#14532D] text-[11px] font-semibold rounded-md px-2 py-1">{{ $isFr ? 'Vérifié' : 'Verified' }}</span>
                                <i data-lucide="circle-check" class="w-[17px] h-[17px] text-[#157A43]" style="stroke-width:1.8"></i>
                            </span>
                        </div>
                        @endforeach
                        <div class="border-2 border-dashed border-[#D8DED9] rounded-xl p-3 flex flex-col items-center justify-center text-center">
                            <span class="w-9 h-9 rounded-full bg-[#E9F2EB] flex items-center justify-center">
                                <i data-lucide="plus" class="w-4 h-4 text-[#14532D]" style="stroke-width:2.2"></i>
                            </span>
                            <p class="mt-2 text-[12px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $isFr ? 'Autre document' : 'Other document' }}</p>
                            <p class="text-[11px] text-[#6F6B60]">({{ $isFr ? 'optionnel' : 'optional' }})</p>
                        </div>
                    </div>
                    <div class="mt-3.5 rounded-lg bg-[#F3F7F3] px-4 py-3 flex items-center gap-2.5">
                        <i data-lucide="circle-check" class="w-[18px] h-[18px] shrink-0 text-[#157A43]" style="stroke-width:1.8"></i>
                        <p class="text-[12.5px] text-[#14532D]">{{ $isFr ? 'Entreprise vérifiée avec succès le 12 Mai 2024 à 14:45' : 'Business successfully verified on 12 May 2024 at 14:45' }}</p>
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">3. {{ $isFr ? 'Preuve de localisation' : 'Proof of location' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? "Confirmez l'emplacement réel de votre atelier ou boutique." : 'Confirm the real location of your workshop or shop.' }}</p>
                    <div class="mt-3.5 border border-[#F0F0EE] rounded-xl overflow-hidden flex flex-wrap items-center gap-4 pr-4">
                        <img src="{{ asset('images/landing/ob9-map.png') }}" alt="" class="w-[124px] h-[84px] object-cover shrink-0">
                        <div class="min-w-0 py-3">
                            <p class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Adresse de votre atelier' : 'Address of your workshop' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#3B382F]">Quartier Tougang, Bafoussam, {{ $isFr ? 'Ouest Cameroun' : 'West Cameroon' }}</p>
                        </div>
                        <span class="flex items-center gap-2">
                            <span class="bg-[#EBF5EB] text-[#14532D] text-[11.5px] font-semibold rounded-md px-2.5 py-1">{{ $isFr ? 'Vérifié' : 'Verified' }}</span>
                            <i data-lucide="circle-check" class="w-[18px] h-[18px] text-[#157A43]" style="stroke-width:1.8"></i>
                        </span>
                        <button type="button" class="ob-upload ml-auto shrink-0 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">{{ $isFr ? 'Mettre à jour' : 'Update' }}</button>
                    </div>
                    <div class="mt-3.5 rounded-lg bg-[#F3F7F3] px-4 py-3 flex items-center gap-2.5">
                        <i data-lucide="circle-check" class="w-[18px] h-[18px] shrink-0 text-[#157A43]" style="stroke-width:1.8"></i>
                        <p class="text-[12.5px] text-[#14532D]">{{ $isFr ? 'Localisation confirmée le 12 Mai 2024 à 15:02' : 'Location confirmed on 12 May 2024 at 15:02' }}</p>
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">4. {{ $isFr ? 'Conformité & engagements' : 'Compliance & commitments' }}</h2>
                    <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Veuillez confirmer que vous respectez les règles de la plateforme.' : 'Please confirm that you respect the platform rules.' }}</p>
                    <div class="mt-4 space-y-3">
                        @foreach($step9Checks as $ckText)
                        <label class="flex items-start gap-3 cursor-pointer select-none ob9-line">
                            <input type="checkbox" checked class="sr-only ob9-check">
                            <span class="ob9-box mt-0.5 w-[17px] h-[17px] shrink-0 rounded border-[1.5px] border-[#157A43] flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3 text-[#157A43]" style="stroke-width:3.2"></i>
                            </span>
                            <span class="text-[12.5px] text-[#3B382F] leading-relaxed">
                                @if(str_contains($ckText, '%LINK%'))
                                    {{ Str::before($ckText, '%LINK%') }}<a href="{{ route('terms') }}" target="_blank" class="text-[#157A43] underline underline-offset-2">{{ $isFr ? "conditions générales d'utilisation" : 'general terms of use' }}</a>{{ Str::after($ckText, '%LINK%') }}
                                @else
                                    {{ $ckText }}
                                @endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-5 rounded-xl bg-[#F3F7F3] px-4 py-4 flex items-center gap-4">
                        <i data-lucide="shield-check" class="w-[34px] h-[34px] shrink-0 text-[#14532D]" style="stroke-width:1.5"></i>
                        <p class="text-[12.5px] text-[#3B382F] leading-relaxed max-w-[560px]">
                            {{ $isFr ? 'Vos données' : 'Your data' }} <span class="font-semibold text-[#157A43]">{{ $isFr ? 'sont sécurisées' : 'is secure' }}</span> {{ $isFr ? 'et utilisées' : 'and used' }} <span class="font-semibold text-[#157A43]">{{ $isFr ? 'uniquement' : 'only' }}</span> {{ $isFr ? 'pour' : 'for' }} <span class="font-semibold text-[#157A43]">{{ $isFr ? 'la vérification' : 'the verification' }}</span> {{ $isFr ? 'de votre compte et la mise en valeur de votre profil.' : 'of your account and the enhancement of your profile.' }}
                        </p>
                    </div>
                </section>

                <div class="mt-6 flex items-center justify-between gap-4">
                    <button type="button" onclick="goToStep(8)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                    <button type="button" onclick="goToStep(10)" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- ═══════ Step 10 — review & submission ═══════ -->
            <div id="panel-10" class="ob-panel hidden">
                <button type="button" onclick="goToStep(9)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18] flex flex-wrap items-center gap-3">
                            {{ $isFr ? 'Revue & Soumission' : 'Review & Submission' }}
                            <span class="inline-flex items-center bg-[#EBF4ED] rounded-full px-3.5 py-1.5 text-[11.5px] font-semibold text-[#14532D]">{{ $isFr ? 'Dernière étape' : 'Last step' }}</span>
                        </h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[460px]">{{ $isFr ? 'Veuillez passer en revue toutes les informations que vous avez fournies avant de soumettre votre dossier. Vous pourrez modifier certaines informations après la soumission.' : 'Please review all the information you have provided before submitting your file. You will be able to edit some information after submission.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 10 sur 10' : 'Step 10 of 10' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-full rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">100% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de votre profil' : 'Summary of your profile' }}</h2>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                        @foreach($step10Summary as $smIdx => [$smIcon, $smTitle, $smLines])
                        <div class="border border-[#EFEFED] rounded-xl p-4 flex flex-col">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ asset('images/landing/' . $smIcon) }}" alt="" class="w-[38px] h-[38px] shrink-0" aria-hidden="true">
                                <h3 class="text-[12px] font-bold text-[#1B1B18] flex-1 min-w-0 whitespace-nowrap">{{ $smTitle }}</h3>
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5 shrink-0 text-[#8A857A]"></i>
                            </div>
                            <div class="mt-3 flex-1">
                                @foreach($smLines as $smLine)
                                <p class="text-[12.5px] text-[#3B382F] leading-relaxed">{{ $smLine }}</p>
                                @endforeach
                            </div>
                            <button type="button" onclick="goToStep({{ $smIdx + 1 }})" class="mt-3 self-start text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir / Modifier' : 'View / Edit' }}</button>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aperçu des documents clés' : 'Preview of key documents' }}</h2>
                        <button type="button" onclick="goToStep(8)" class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">
                            {{ $isFr ? 'Tout voir' : 'See all' }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        @foreach($step10Docs as [$kdImg, $kdName, $kdMeta])
                        <div class="border border-[#F0F0EE] rounded-xl shadow-sm p-2.5">
                            <img src="{{ asset('images/landing/' . $kdImg) }}" alt="{{ $kdName }}" class="w-full h-[114px] object-contain">
                            <div class="mt-2.5 flex items-center justify-between gap-1.5">
                                <div class="min-w-0">
                                    <p class="text-[11.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $kdName }}</p>
                                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $kdMeta }}</p>
                                </div>
                                <span class="w-[20px] h-[20px] shrink-0 rounded-full bg-[#14532D] flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3"></i>
                                </span>
                            </div>
                        </div>
                        @endforeach
                        <div class="border-2 border-dashed border-[#D8DED9] rounded-xl p-4 flex flex-col items-center justify-center text-center">
                            <img src="{{ asset('images/landing/ob10-docplus.png') }}" alt="" class="w-[52px] h-[52px]" aria-hidden="true">
                            <p class="mt-3 text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? '+2 autres documents' : '+2 other documents' }}</p>
                            <button type="button" onclick="goToStep(8)" class="mt-1.5 text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tous' : 'See all' }}</button>
                        </div>
                    </div>
                </section>

                <div class="mt-4 flex flex-col lg:flex-row gap-4">
                    <section class="flex-1 border border-[#EDEDEB] rounded-xl p-5">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Après la soumission' : 'After submission' }}</h2>
                        <ol class="mt-4">
                            @foreach($step10After as $afIdx => [$afIcon, $afTitle, $afDesc])
                            <li class="relative flex items-start gap-3.5 {{ $afIdx > 0 ? 'mt-4' : '' }}">
                                @if($afIdx < 3)<span class="absolute left-[16px] top-[38px] h-[26px] w-px bg-[#E3EBE4]"></span>@endif
                                <img src="{{ asset('images/landing/' . $afIcon) }}" alt="" class="w-[34px] h-[34px] shrink-0" aria-hidden="true">
                                <div class="min-w-0 pt-0.5">
                                    <p class="text-[13px] font-bold text-[#1B1B18]">{{ $afTitle }}</p>
                                    <p class="mt-1 text-[12px] text-[#6F6B60] leading-relaxed">{{ $afDesc }}</p>
                                </div>
                            </li>
                            @endforeach
                        </ol>
                    </section>
                    <section class="lg:w-[285px] shrink-0 bg-[#F3F6F3] rounded-xl p-4">
                        <h2 class="text-[13.5px] font-bold text-[#14532D]">{{ $isFr ? 'Engagement de qualité' : 'Quality commitment' }}</h2>
                        <div class="mt-3 flex items-start gap-3">
                            <img src="{{ asset('images/landing/ob10-quality.png') }}" alt="" class="w-[54px] h-[54px] shrink-0" aria-hidden="true">
                            <p class="text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? "Nous nous engageons à vérifier chaque profil avec rigueur afin d'assurer la confiance des acheteurs et la valorisation des artisans camerounais." : 'We are committed to rigorously verifying every profile to ensure buyer trust and showcase Cameroonian artisans.' }}</p>
                        </div>
                        <ul class="mt-3.5 space-y-2">
                            @foreach($step10Quality as $q)
                            <li class="flex items-center gap-2.5">
                                <i data-lucide="check" class="w-3.5 h-3.5 shrink-0 text-[#157A43]" style="stroke-width:3"></i>
                                <span class="text-[12px] text-[#3B382F]">{{ $q }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </section>
                </div>

                <div class="relative mt-4">
                    <div class="rounded-xl bg-[#F3F7F3] px-4 py-4 lg:pr-[250px] flex items-start gap-3">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" checked class="sr-only ob9-check">
                            <span class="ob9-box mt-0.5 w-[17px] h-[17px] shrink-0 rounded border-[1.5px] border-[#157A43] flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3 text-[#157A43]" style="stroke-width:3.2"></i>
                            </span>
                            <span class="text-[12.5px] text-[#3B382F] leading-relaxed">
                                {{ $isFr ? "Je certifie que toutes les informations fournies sont exactes et que j'accepte" : 'I certify that all the information provided is accurate and that I accept' }}
                                {{ $isFr ? 'les' : 'the' }} <a href="{{ route('terms') }}" target="_blank" class="text-[#157A43] underline underline-offset-2">{{ $isFr ? "conditions générales d'utilisation" : 'general terms of use' }}</a> {{ $isFr ? 'de la plateforme.' : 'of the platform.' }}
                            </span>
                        </label>
                    </div>
                    <button type="button" id="ob-submit" class="lg:absolute lg:right-0 lg:top-[26px] mt-3 lg:mt-0 inline-flex items-center gap-3 bg-[#025127] hover:bg-leaf text-white text-[14px] font-semibold px-7 py-3.5 rounded-lg shadow-md transition-colors">
                        {{ $isFr ? 'Soumettre mon dossier' : 'Submit my file' }}
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="mt-5">
                    <button type="button" onclick="goToStep(9)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- ═══════ Success screen (after step-10 submission) ═══════ -->
    <div id="success-screen" class="hidden flex-col lg:flex-row items-start gap-6 pt-6">

        <!-- White sidebar -->
        <aside class="lg:w-[230px] shrink-0 lg:pl-3">
            <h2 class="text-[15px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase leading-snug">
                {{ $isFr ? 'Créer mon compte' : 'Create my account' }}<br>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}
            </h2>
            <p class="mt-3 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Étapes complétées' : 'Completed steps' }}</p>
            <p class="text-[15px] font-bold text-[#157A43]">10 {{ $isFr ? 'sur' : 'of' }} 10</p>

            <ol class="mt-7">
                @foreach($wizardSteps as $wsIdx => [$wsTitle, $wsSub])
                <li class="relative {{ $wsIdx > 0 ? 'mt-5' : '' }}">
                    @if($wsIdx < 9)<span class="absolute left-[13px] top-[32px] -bottom-5 w-px bg-[#D9E6DC]"></span>@endif
                    <div class="flex items-start gap-3.5">
                        <span class="relative z-10 w-[27px] h-[27px] shrink-0 rounded-full bg-[#14532D] flex items-center justify-center text-[12px] font-semibold text-white">{{ $wsIdx + 1 }}</span>
                        <span class="pt-0.5 min-w-0">
                            <span class="block text-[13px] font-bold text-[#1B1B18] leading-snug">{{ $wsIdx === 0 ? ($isFr ? 'Type de compte' : 'Account type') : $wsTitle }}</span>
                            <span class="block mt-1 text-[12px] text-[#55524A] leading-snug {{ $wsIdx === 0 ? 'success-type-name' : '' }}">{{ $wsIdx === 0 ? $typeNames[0] : $wsSub }}</span>
                        </span>
                    </div>
                </li>
                @endforeach
            </ol>

            <div class="mt-10 bg-[#F4F9F6] rounded-xl p-4">
                <p class="flex items-start gap-3 text-[12.5px] font-bold text-[#14532D] leading-snug">
                    <img src="{{ asset('images/landing/ob12-shield.png') }}" alt="" class="w-[19px] h-[26px] shrink-0" aria-hidden="true">
                    {{ $isFr ? 'Votre confiance est notre priorité' : 'Your trust is our priority' }}
                </p>
                <p class="mt-3 text-[12px] text-[#3B382F] leading-relaxed">
                    {{ $isFr
                        ? 'Vos données sont sécurisées et utilisées uniquement pour la vérification et la gestion de votre compte.'
                        : 'Your data is secured and used only for the verification and management of your account.'
                    }}
                </p>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 min-w-0">

            <!-- Congratulations card -->
            <section class="bg-white border border-[#ECECEA] rounded-2xl px-6 py-7 flex flex-col sm:flex-row items-center gap-6">
                <img src="{{ asset('images/landing/ob12-check.png') }}" alt="" class="w-[140px] shrink-0" aria-hidden="true">
                <div class="flex-1 min-w-0 text-center sm:text-left">
                    <p class="text-[16px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Félicitations !' : 'Congratulations!' }} 🎉</p>
                    <h1 class="mt-1.5 text-[26px] font-bold text-[#1B1B18] leading-snug">{{ $isFr ? 'Votre dossier a été soumis avec succès.' : 'Your file has been submitted successfully.' }}</h1>
                    <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed max-w-[480px]">
                        {{ $isFr
                            ? "Merci de rejoindre la Galerie Virtuelle Nationale de l'Artisanat du Cameroun. Nous avons bien reçu votre dossier et il est en cours de vérification."
                            : 'Thank you for joining the National Virtual Gallery of Cameroonian Crafts. We have received your file and it is being verified.'
                        }}
                    </p>
                </div>
                <img src="{{ asset('images/landing/ob12-mail.png') }}" alt="" class="w-[165px] shrink-0 hidden sm:block" aria-hidden="true">
            </section>

            <!-- File info row -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-y-4 sm:divide-x divide-[#EDEDEB]">
                <div>
                    <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Numéro de dossier' : 'File number' }}</p>
                    <p class="mt-1 flex items-center gap-2.5 text-[17px] font-bold text-[#14652F]">
                        GVNA-2024-000158
                        <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('GVNA-2024-000158')" title="{{ $isFr ? 'Copier' : 'Copy' }}" class="text-[#157A43] hover:text-[#14532D]">
                            <i data-lucide="copy" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                        </button>
                    </p>
                </div>
                <div class="sm:pl-8">
                    <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Date de soumission' : 'Submission date' }}</p>
                    <p class="mt-1 text-[16px] font-bold text-[#1B1B18]">{{ $isFr ? '12 Mai 2024 à 14:32' : '12 May 2024 at 14:32' }}</p>
                </div>
                <div class="sm:pl-8 flex items-center gap-3.5">
                    <span class="w-[46px] h-[46px] shrink-0 rounded-full bg-[#E3F0E7] flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-[#14532D] fill-[#14532D]"></i>
                    </span>
                    <span>
                        <span class="block text-[12.5px] text-[#55524A]">{{ $isFr ? 'Type de compte' : 'Account type' }}</span>
                        <span class="success-type-name block mt-0.5 text-[16px] font-bold text-[#1B1B18]">{{ $typeNames[0] }}</span>
                    </span>
                </div>
            </section>

            <!-- Timeline + processing time -->
            <div class="mt-4 flex flex-col lg:flex-row gap-4 items-stretch">
                <section class="flex-1 min-w-0 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Où en est votre dossier ?' : 'Where does your file stand?' }}</h2>
                    <ol class="mt-6">
                        @foreach($successTimeline as $tlIdx => [$tlIcon, $tlColor, $tlBg, $tlTitle, $tlDesc, $tlStatus])
                        <li class="relative flex items-start gap-4 {{ $tlIdx > 0 ? 'mt-8' : '' }}">
                            @if($tlIdx < 4)<span class="absolute left-[19px] top-[44px] h-[52px] w-px bg-[#E8ECE9]"></span>@endif
                            <span class="relative z-10 w-[39px] h-[39px] shrink-0 rounded-full flex items-center justify-center" style="background:{{ $tlBg }}">
                                <i data-lucide="{{ $tlIcon }}" class="w-[18px] h-[18px]" style="stroke-width:2;color:{{ $tlColor }}"></i>
                            </span>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <p class="text-[13.5px] font-bold text-[#1B1B18]">{{ $tlTitle }}</p>
                                <p class="mt-1 text-[12.5px] text-[#55524A] leading-relaxed max-w-[300px]">{{ $tlDesc }}</p>
                            </div>
                            @if($tlStatus === 'done')
                            <div class="shrink-0 flex items-start gap-2.5 pt-0.5">
                                <p class="text-[12px] text-[#55524A] text-right leading-relaxed">{{ $isFr ? '12 Mai 2024' : '12 May 2024' }}<br>14:32</p>
                                <span class="w-[21px] h-[21px] rounded-full bg-[#14652F] flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3.2"></i>
                                </span>
                            </div>
                            @elseif($tlStatus === 'current')
                            <span class="shrink-0 rounded-lg bg-[#FDF3E3] text-[#E8890C] text-[12.5px] font-semibold px-3.5 py-1.5">{{ $isFr ? 'En cours' : 'In progress' }}</span>
                            @else
                            <span class="shrink-0 rounded-lg bg-[#F0F1F2] text-[#55524A] text-[12.5px] px-3.5 py-1.5">{{ $isFr ? 'À venir' : 'Upcoming' }}</span>
                            @endif
                        </li>
                        @endforeach
                    </ol>
                </section>

                <section class="lg:w-[320px] shrink-0 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6">
                    <h2 class="text-[14.5px] font-bold text-[#14532D]">{{ $isFr ? 'Délai de traitement estimé' : 'Estimated processing time' }}</h2>
                    <p class="mt-3.5 flex items-center gap-3">
                        <i data-lucide="clock" class="w-[26px] h-[26px] text-[#1B1B18]" style="stroke-width:1.8"></i>
                        <span class="text-[19px] font-bold text-[#1B1B18]">{{ $isFr ? '24 à 72 heures' : '24 to 72 hours' }}</span>
                    </p>
                    <p class="mt-3 text-[12.5px] text-[#55524A] leading-relaxed">
                        {{ $isFr ? 'Nous vous informerons par email et SMS dès que votre dossier sera validé.' : 'We will inform you by email and SMS as soon as your file is validated.' }}
                    </p>

                    <div class="mt-7 flex items-center gap-4">
                        <span class="w-[46px] h-[46px] shrink-0 rounded-full bg-[#E3F0E7] flex items-center justify-center">
                            <i data-lucide="mail" class="w-5 h-5 text-[#14652F]" style="stroke-width:1.8"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Par email' : 'By email' }}</span>
                            <span class="block mt-0.5 text-[13.5px] font-semibold text-[#14652F] break-all">{{ $successContactEmail }}</span>
                        </span>
                    </div>
                    <div class="mt-5 flex items-center gap-4">
                        <span class="w-[46px] h-[46px] shrink-0 rounded-full bg-[#E3F0E7] flex items-center justify-center">
                            <i data-lucide="phone" class="w-5 h-5 text-[#14652F]" style="stroke-width:1.8"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Par SMS' : 'By SMS' }}</span>
                            <span class="block mt-0.5 text-[13.5px] font-semibold text-[#14652F]">{{ $successContactPhone }}</span>
                        </span>
                    </div>

                    <div class="mt-8 bg-[#EEF3EF] rounded-xl px-4 py-4">
                        <p class="text-[12.5px] text-[#3B382F] leading-relaxed">
                            {{ $isFr ? 'Pensez à vérifier vos spams ou courriers indésirables si vous ne voyez pas nos emails.' : 'Remember to check your spam or junk mail if you do not see our emails.' }}
                        </p>
                    </div>
                </section>
            </div>

            <!-- Submission recap -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Récapitulatif de votre soumission' : 'Summary of your submission' }}</h2>
                <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Toutes les sections requises ont été complétées.' : 'All required sections have been completed.' }}</p>
                <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach($successRecap as [$rcTitle, $rcSub])
                    <div class="border border-[#EFEFED] rounded-xl px-3.5 py-4">
                        <p class="flex items-center gap-2.5">
                            <span class="w-[21px] h-[21px] shrink-0 rounded-full bg-[#14652F] flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3.2"></i>
                            </span>
                            <span class="text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $rcTitle }}</span>
                        </p>
                        <p class="mt-2.5 ml-[31px] text-[12px] text-[#55524A] leading-relaxed whitespace-pre-line">{{ $rcSub }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- What can you do now? -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Que pouvez-vous faire maintenant ?' : 'What can you do now?' }}</h2>
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($successActions as [$acIcon, $acTitle, $acDesc, $acBtn, $acHref])
                    <div class="relative">
                        <div class="absolute inset-x-4 -top-1.5 bottom-4 rounded-2xl border border-[#F0F0EE] rotate-[1.4deg]" aria-hidden="true"></div>
                        <div class="relative bg-white border border-[#EFEFED] rounded-2xl p-5 flex flex-col h-full">
                            <span class="w-[64px] h-[60px] rounded-2xl bg-[#E8F2EC] flex items-center justify-center">
                                <i data-lucide="{{ $acIcon }}" class="w-[26px] h-[26px] text-[#14652F]" style="stroke-width:1.8"></i>
                            </span>
                            <h3 class="mt-5 text-[14.5px] font-bold text-[#1B1B18]">{{ $acTitle }}</h3>
                            <p class="mt-2.5 text-[12.5px] text-[#55524A] leading-relaxed flex-1">{{ $acDesc }}</p>
                            @if($acHref)
                            <a href="{{ $acHref }}" class="mt-5 inline-flex items-center justify-center border border-[#BFD4C6] hover:border-[#14652F] rounded-lg px-5 py-2.5 text-[13.5px] font-semibold text-[#14652F] transition-colors">{{ $acBtn }}</a>
                            @else
                            <button type="button" onclick="window.print()" class="mt-5 inline-flex items-center justify-center border border-[#BFD4C6] hover:border-[#14652F] rounded-lg px-5 py-2.5 text-[13.5px] font-semibold text-[#14652F] transition-colors">{{ $acBtn }}</button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Help bar -->
            <section class="mt-5 bg-[#EFF5F1] rounded-xl px-6 py-4 flex items-center gap-5">
                <i data-lucide="headphones" class="w-[30px] h-[30px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
                <p class="text-[12.5px] text-[#3B382F] leading-relaxed">
                    <span class="font-bold text-[#1B1B18]">{{ $isFr ? "Besoin d'aide ?" : 'Need help?' }}</span>
                    {{ $isFr ? 'Notre équipe est là pour vous accompagner.' : 'Our team is here to support you.' }}<br>
                    {{ $isFr ? 'Contactez-nous par email à' : 'Contact us by email at' }}
                    <a href="mailto:support@galerie-artisanat.cm" class="font-semibold text-[#14652F]">support@galerie-artisanat.cm</a>
                    {{ $isFr ? 'ou par téléphone au' : 'or by phone on' }}
                    <a href="tel:+237690123456" class="font-semibold text-[#14652F]">+237 690 123 456</a>.
                </p>
            </section>

            <!-- Closing quote -->
            <p class="mt-6 mb-2 text-center text-[15px] font-semibold italic text-[#14532D]">
                “{{ $isFr ? 'Ensemble, valorisons le savoir-faire des artisans camerounais et ouvrons-leur de nouvelles opportunités.' : 'Together, let us showcase the know-how of Cameroonian artisans and open up new opportunities for them.' }}”
                <img src="{{ asset('images/landing/ob12-heart.png') }}" alt="" class="inline-block w-[22px] h-[22px] ml-2 align-text-bottom" aria-hidden="true">
            </p>
        </div>
    </div>

    <!-- Step-1 extras -->
    <div id="step1-extras">
        <section class="mt-4 bg-white rounded-2xl shadow-sm px-6 py-7">
            <h2 class="text-center text-[17.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Les avantages de la plateforme' : 'The platform\'s advantages' }}</h2>
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-4 gap-y-7">
                @foreach($advantages as [$advIcon, $advTitle, $advDesc])
                <div class="text-center">
                    <img src="{{ asset('images/landing/' . $advIcon) }}" alt="" class="w-[54px] h-[54px] mx-auto" aria-hidden="true">
                    <h3 class="mt-3.5 text-[12.5px] font-bold text-[#1B1B18]">{{ $advTitle }}</h3>
                    <p class="mt-2 text-[11px] text-[#6F6B60] leading-relaxed whitespace-pre-line">{{ $advDesc }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <section class="relative mt-4 bg-[#0F3323] rounded-2xl overflow-hidden px-6 py-8">
            <div class="absolute inset-0 opacity-[0.06] bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <h2 class="relative text-center text-[16px] font-bold text-white">{{ $isFr ? 'Une plateforme officielle et sécurisée' : 'An official and secure platform' }}</h2>
            <div class="relative mt-7 grid grid-cols-2 lg:grid-cols-4 gap-y-7 lg:divide-x divide-white/10">
                @foreach($securityItems as [$secIcon, $secTitle, $secDesc])
                <div class="text-center px-4">
                    <img src="{{ asset('images/landing/' . $secIcon) }}" alt="" class="w-[52px] h-[52px] mx-auto" aria-hidden="true">
                    <h3 class="mt-3 text-[12.5px] font-bold text-[#F3C246]">{{ $secTitle }}</h3>
                    <p class="mt-2 text-[11px] text-[#C6D4C9] leading-relaxed whitespace-pre-line">{{ $secDesc }}</p>
                </div>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Per-step bottom strips -->
    <section id="strip-12" class="ob-strip mt-4 bg-[#FEFAF3] border border-[#F2E8D8] rounded-2xl px-6 py-4 flex flex-wrap items-center gap-4">
        <img src="{{ asset('images/landing/ob-help.png') }}" alt="" class="w-[46px] h-[46px] shrink-0" aria-hidden="true">
        <div class="min-w-0">
            <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Besoin d\'aide pour vous inscrire ?' : 'Need help signing up?' }}</h2>
            <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? 'Notre équipe est disponible pour vous accompagner à chaque étape.' : 'Our team is available to support you at every step.' }}</p>
        </div>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="ml-auto shrink-0 inline-flex items-center gap-2.5 border border-[#14532D] text-[#14532D] hover:bg-[#14532D]/5 text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            {{ $isFr ? 'Contactez-nous' : 'Contact us' }}
        </a>
    </section>

    @foreach([
        3 => [null, $isFr ? 'Une plateforme officielle et sécurisée' : 'An official and secure platform', $isFr ? 'Vos informations sont protégées et ne seront jamais partagées sans votre consentement.' : 'Your information is protected and will never be shared without your consent.', [
            ['lock',        $isFr ? "Données\nprotégées" : "Protected\ndata"],
            ['badge-check', $isFr ? "Vérification\nofficielle" : "Official\nverification"],
            ['shield-check',$isFr ? "Confiance\ngarantie" : "Guaranteed\ntrust"],
        ]],
        4 => ['shield-check', $isFr ? 'Pourquoi bien choisir vos catégories ?' : 'Why choose your categories well?', $isFr ? 'Cela permet aux acheteurs de vous trouver facilement et de recevoir des demandes de devis pertinentes pour votre activité.' : 'It lets buyers find you easily and brings you quote requests relevant to your activity.', [
            ['eye',            $isFr ? "Meilleure\nvisibilité" : "Better\nvisibility"],
            ['message-circle', $isFr ? "Demandes de\ndevis ciblées" : "Targeted quote\nrequests"],
            ['chart-column',   $isFr ? "Référencement\noptimal" : "Optimal\nranking"],
            ['pencil-line',    $isFr ? "Développement\nde votre activité" : "Growth of\nyour business"],
        ]],
        5 => ['lightbulb', $isFr ? 'Conseils pour bien renseigner votre localisation' : 'Tips for filling in your location well', $isFr ? 'Une localisation précise permet aux acheteurs et partenaires de vous trouver facilement et renforce votre crédibilité.' : 'A precise location lets buyers and partners find you easily and strengthens your credibility.', [
            ['locate',          $isFr ? "Visibilité\nlocale accrue" : "Increased local\nvisibility"],
            ['shield-check',    $isFr ? "Confiance\ndes acheteurs" : "Buyer\ntrust"],
            ['truck',           $isFr ? "Livraisons\nfacilitées" : "Easier\ndeliveries"],
            ['heart-handshake', $isFr ? "Partenariats\net opportunités" : "Partnerships\nand opportunities"],
        ]],
        6 => ['shield-check', $isFr ? 'Pourquoi présenter vos produits & services ?' : 'Why present your products & services?', $isFr ? 'Une présentation complète attire plus de visiteurs et augmente vos opportunités de vente.' : 'A complete presentation attracts more visitors and increases your sales opportunities.', [
            ['eye',          $isFr ? "Plus de visibilité\nauprès des acheteurs" : "More visibility\nwith buyers"],
            ['shield-check', $isFr ? "Confiance et\ncrédibilité accrues" : "Increased trust\nand credibility"],
            ['hand-coins',   $isFr ? "Opportunités de\nventes élevées" : "High sales\nopportunities"],
            ['tags',         $isFr ? "Référencement optimal\nsur la plateforme" : "Optimal ranking\non the platform"],
        ]],
    ] as $stripNo => [$stripIcon, $stripTitle, $stripSub, $stripItems])
    <section id="strip-{{ $stripNo }}" class="ob-strip hidden mt-4 bg-white rounded-2xl shadow-sm px-6 py-5 flex flex-wrap items-center gap-x-6 gap-y-4">
        <div class="flex items-start gap-3.5 flex-1 min-w-[280px]">
            @if($stripIcon)
            <span class="w-[42px] h-[42px] shrink-0 rounded-full bg-[#F3F8F3] border border-[#DCEBE0] flex items-center justify-center">
                <i data-lucide="{{ $stripIcon }}" class="w-5 h-5 text-[#B8860B]" style="stroke-width:1.7;color:{{ $stripNo === 5 ? '#D79326' : '#14532D' }}"></i>
            </span>
            @else
            <span class="w-[42px] h-[42px] shrink-0 rounded-full bg-[#F3F8F3] border border-[#DCEBE0] flex items-center justify-center">
                <i data-lucide="shield-check" class="w-5 h-5 text-[#14532D]" style="stroke-width:1.7"></i>
            </span>
            @endif
            <div>
                <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $stripTitle }}</h2>
                <p class="mt-0.5 text-[11.5px] text-[#55524A] leading-relaxed max-w-[360px]">{{ $stripSub }}</p>
            </div>
        </div>
        <div class="flex items-start gap-6 lg:gap-8 ml-auto">
            @foreach($stripItems as [$siIcon, $siLabel])
            <div class="text-center w-[92px]">
                <span class="w-[38px] h-[38px] mx-auto rounded-full bg-[#F3F8F3] flex items-center justify-center">
                    <i data-lucide="{{ $siIcon }}" class="w-[18px] h-[18px] text-[#14532D]" style="stroke-width:1.7"></i>
                </span>
                <p class="mt-1.5 text-[10.5px] font-semibold text-[#3B382F] leading-tight whitespace-pre-line">{{ $siLabel }}</p>
            </div>
            @endforeach
        </div>
    </section>
    @endforeach

    <!-- Step 7 strip — gallery stats -->
    <section id="strip-7" class="ob-strip hidden mt-4 bg-[#F9FAF9] rounded-2xl px-6 py-4 flex flex-wrap items-center gap-x-6 gap-y-4">
        <div class="flex items-center gap-4 flex-1 min-w-[300px]">
            <img src="{{ asset('images/landing/ob7-shield.png') }}" alt="" class="w-[47px] h-[47px] shrink-0" aria-hidden="true">
            <div>
                <h2 class="text-[13.5px] font-bold text-[#14532D]">{{ $isFr ? 'Une galerie attractive = plus de visibilité, plus de clients !' : 'An attractive gallery = more visibility, more clients!' }}</h2>
                <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? 'Les acheteurs font confiance aux artisans qui montrent leur travail.' : 'Buyers trust artisans who show their work.' }}</p>
            </div>
        </div>
        <div class="hidden lg:block w-px self-stretch bg-[#E8EAE8]"></div>
        <div class="flex flex-wrap items-start gap-x-9 gap-y-4 ml-auto">
            @foreach($step7Stats as [$stIcon, $stPct, $stLabel])
            <div class="text-center">
                <span class="flex items-center justify-center gap-2">
                    <img src="{{ asset('images/landing/' . $stIcon) }}" alt="" class="h-[24px]" aria-hidden="true">
                    <span class="text-[15px] font-bold text-[#157A43]">{{ $stPct }}</span>
                </span>
                <p class="mt-1 text-[12px] text-[#3B382F]">{{ $stLabel }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Step 8 strip — verified profile advantages -->
    <section id="strip-8" class="ob-strip hidden mt-4 bg-[#F9FAF9] rounded-2xl px-6 py-5 flex flex-wrap items-center gap-x-5 gap-y-4">
        <div class="w-full lg:w-[235px]">
            <h2 class="text-[14px] font-bold text-[#14532D]">{{ $isFr ? "Les avantages d'un profil vérifié" : 'The advantages of a verified profile' }}</h2>
            <p class="mt-1 text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Un profil complet et vérifié vous permet de :' : 'A complete, verified profile lets you:' }}</p>
        </div>
        <div class="flex-1 flex flex-wrap items-center justify-between gap-x-4 gap-y-4 lg:divide-x lg:divide-[#E8EAE8]">
            @foreach($step8Advantages as $a8Idx => [$a8Icon, $a8Label])
            <div class="flex {{ $a8Idx === 4 ? 'flex-col items-center text-center' : 'items-center' }} gap-2.5 lg:px-4 first:lg:pl-0 last:lg:pr-0">
                <img src="{{ asset('images/landing/' . $a8Icon) }}" alt="" class="h-[34px] {{ $a8Idx === 4 ? 'h-[30px]' : '' }}" aria-hidden="true">
                <p class="text-[11.5px] text-[#3B382F] leading-snug whitespace-pre-line">{{ $a8Label }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Step 9 strip — verified account advantages -->
    <section id="strip-9" class="ob-strip hidden mt-4 bg-[#F9FAF9] rounded-2xl px-6 py-5">
        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? "Les avantages d'un compte vérifié" : 'The advantages of a verified account' }}</h2>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-4 lg:divide-x lg:divide-[#E8EAE8]">
            @foreach($step9Advantages as [$a9Icon, $a9Label])
            <div class="flex items-center gap-3 lg:px-5 first:lg:pl-0 last:lg:pr-0">
                <img src="{{ asset('images/landing/' . $a9Icon) }}" alt="" class="w-[44px] h-[44px] shrink-0" aria-hidden="true">
                <p class="text-[12px] text-[#3B382F] leading-snug whitespace-pre-line">{{ $a9Label }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Step 10 strip — platform benefits -->
    <section id="strip-10" class="ob-strip hidden mt-4 bg-[#F9FAF9] rounded-2xl px-6 py-4 flex flex-wrap items-center gap-x-5 gap-y-4">
        <div class="w-full lg:w-[225px]">
            <p class="text-[13.5px] font-bold text-[#14532D]">{{ $isFr ? 'En rejoignant la Galerie Virtuelle,' : 'By joining the Virtual Gallery,' }}</p>
            <p class="mt-0.5 text-[13px] text-[#1B1B18]">{{ $isFr ? 'vous bénéficiez de nombreux avantages' : 'you benefit from many advantages' }}</p>
        </div>
        <div class="hidden lg:block w-px self-stretch bg-[#E8EAE8]"></div>
        <div class="flex-1 flex flex-wrap items-center justify-between gap-x-3 gap-y-4">
            @foreach($step10Advantages as [$a10Icon, $a10Label])
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/landing/' . $a10Icon) }}" alt="" class="w-[42px] h-[46px] shrink-0 object-contain" aria-hidden="true">
                <p class="text-[11px] text-[#3B382F] leading-snug whitespace-pre-line">{{ $a10Label }}</p>
            </div>
            @endforeach
        </div>
    </section>
</div>

<script>
    lucide.createIcons();

    const typeNames = @json($typeNames);
    const stepSub2 = @json($isFr ? 'Étape %N sur 10' : 'Step %N of 10');
    const sideSubDefault = @json($isFr ? 'Rejoignez la plus grande vitrine de l\'artisanat camerounais' : 'Join the largest showcase of Cameroonian craftsmanship');
    const secureTitles = @json($sideSecureTitles);
    const secureTextDefault = @json($isFr
        ? 'Vos données sont protégées et utilisées uniquement pour la vérification et la gestion de votre compte.'
        : 'Your data is protected and used only for the verification and management of your account.');
    const secureTexts = @json($sideSecureTexts);
    const defaultSub1 = @json($wizardSteps[0][1]);
    const defaultTitle1 = @json($wizardSteps[0][0]);
    const doneTitle1 = @json($isFr ? 'Type de compte' : 'Account type');

    // Account-type radio visuals
    function refreshRadios() {
        document.querySelectorAll('input[name="account_type"]').forEach(r => {
            const card = r.nextElementSibling;
            card.querySelector('.at-dot').classList.toggle('hidden', !r.checked);
            card.querySelector('.at-radio').style.borderColor = r.checked ? '#0F5132' : '#C9CFC9';
        });
    }
    document.querySelectorAll('input[name="account_type"]').forEach(r => r.addEventListener('change', refreshRadios));
    refreshRadios();

    let currentStep = 1;
    // ── Real signup: "Soumettre mon dossier" creates the account ──
    const obAlready = @json((bool) $siacUser);
    document.getElementById('ob-submit').addEventListener('click', () => {
        if (obAlready) { goToStep(11); return; }
        const fields = ['ob-first-name', 'ob-last-name', 'ob-email', 'ob-password', 'ob-password-confirm'];
        let firstMissing = null;
        fields.forEach(id => {
            const el = document.getElementById(id);
            el.classList.remove('border-[#E5484D]');
            if (!el.value.trim()) { el.classList.add('border-[#E5484D]'); firstMissing = firstMissing || el; }
        });
        if (firstMissing) { goToStep(2); firstMissing.focus(); return; }

        const form = document.getElementById('ob-signup-form');
        form.querySelector('[name="first_name"]').value = document.getElementById('ob-first-name').value;
        form.querySelector('[name="last_name"]').value = document.getElementById('ob-last-name').value;
        form.querySelector('[name="email"]').value = document.getElementById('ob-email').value;
        form.querySelector('[name="phone"]').value = document.getElementById('ob-phone').value;
        form.querySelector('[name="password"]').value = document.getElementById('ob-password').value;
        form.querySelector('[name="password_confirmation"]').value = document.getElementById('ob-password-confirm').value;
        const chosen = document.querySelector('input[name="account_type"]:checked');
        form.querySelector('[name="account_type"]').value = chosen ? chosen.value : '';
        form.submit();
    });

    function goToStep(n) {
        currentStep = n;

        // Step 11 = post-submission success screen (own white sidebar + layout)
        const success = n === 11;
        document.getElementById('wizard-flex').classList.toggle('hidden', success);
        const sc = document.getElementById('success-screen');
        sc.classList.toggle('hidden', !success);
        sc.classList.toggle('flex', success);
        document.getElementById('ob-bell').classList.toggle('hidden', !success);
        document.body.style.background = success ? '#FBFCFC' : '';
        if (success) {
            const chosenType = document.querySelector('input[name="account_type"]:checked');
            if (chosenType) document.querySelectorAll('.success-type-name').forEach(el => el.textContent = typeNames[parseInt(chosenType.value, 10)]);
            document.getElementById('step1-extras').classList.add('hidden');
            document.querySelectorAll('.ob-strip').forEach(s => s.classList.add('hidden'));
            lucide.createIcons();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        for (let i = 1; i <= 10; i++) {
            const p = document.getElementById('panel-' + i);
            if (p) p.classList.toggle('hidden', i !== n);
        }
        document.getElementById('step1-extras').classList.toggle('hidden', n !== 1);
        document.getElementById('strip-12').classList.toggle('hidden', n > 2);
        [3, 4, 5, 6, 7, 8, 9, 10].forEach(s => document.getElementById('strip-' + s).classList.toggle('hidden', s !== n));

        document.getElementById('side-sub').textContent = n === 1 ? sideSubDefault : stepSub2.replace('%N', String(n));
        document.getElementById('side-secure-title').textContent = secureTitles[n] || secureTitles[2];
        document.getElementById('side-secure-text').textContent = secureTexts[n] || secureTextDefault;

        const chosen = document.querySelector('input[name="account_type"]:checked');
        document.querySelectorAll('.wizard-step').forEach(li => {
            const s = parseInt(li.dataset.step, 10);
            const circle = li.querySelector('.step-circle');
            const row = li.querySelector('.step-row');
            const sub = li.querySelector('.step-sub');
            const title = li.querySelector('.step-title');
            const done = s < n, active = s === n;
            circle.innerHTML = done ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-4 h-4"><path d="M20 6 9 17l-5-5"/></svg>' : String(s);
            circle.style.background = active ? '#FFFFFF' : 'transparent';
            circle.style.color = active ? '#014622' : '#FFFFFF';
            circle.style.borderColor = active ? '#FFFFFF' : 'rgba(255,255,255,0.4)';
            if (row) row.style.background = active ? '#01602D' : 'transparent';
            if (s === 1 && sub) sub.textContent = (n > 1 && chosen) ? typeNames[parseInt(chosen.value, 10)] : defaultSub1;
            if (s === 1 && title) title.textContent = n > 1 ? doneTitle1 : defaultTitle1;
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    // Initial step: success screen after signup, identity step on validation
    // errors, otherwise the wizard start.
    @if(request('submitted') && $siacUser)
    goToStep(11);
    @elseif($errors->any())
    goToStep(2);
    @else
    goToStep(1);
    @endif

    // Steps 9-10: compliance checkboxes
    document.querySelectorAll('.ob9-check').forEach(c => c.addEventListener('change', () => {
        const box = c.nextElementSibling;
        box.style.opacity = '1';
        box.querySelector('svg, i').style.visibility = c.checked ? 'visible' : 'hidden';
        box.style.borderColor = c.checked ? '#157A43' : '#C9CFC9';
    }));

    // Step 7: delete the demo video row
    document.querySelectorAll('.vid-del').forEach(b => b.addEventListener('click', () => {
        const row = b.closest('.ob-video-row');
        if (row) row.remove();
    }));

    // Profile photo live preview
    document.getElementById('ob-photo-input').addEventListener('change', function () {
        const f = this.files && this.files[0];
        if (f) document.getElementById('ob-photo-preview').src = URL.createObjectURL(f);
    });

    // Language chips (step 3)
    document.querySelectorAll('.lang-chip').forEach(chip => chip.addEventListener('click', () => {
        const on = chip.dataset.on !== '1';
        chip.dataset.on = on ? '1' : '0';
        chip.querySelector('.chip-check').classList.toggle('hidden', !on);
        chip.querySelector('.chip-off').classList.toggle('hidden', on);
        chip.classList.toggle('border-[#BFDCC8]', on);
        chip.classList.toggle('bg-[#F3F8F3]', on);
        chip.classList.toggle('border-[#E5E3E0]', !on);
    }));

    // Secondary categories (step 4): toggle + 5-max counter
    function refreshCats() {
        const checks = Array.from(document.querySelectorAll('.sc-check'));
        const count = checks.filter(c => c.checked).length;
        document.getElementById('sc-count').textContent = count + ' / 5';
        checks.forEach(c => {
            const card = c.nextElementSibling;
            card.classList.toggle('border-[#0F5132]', c.checked);
            card.classList.toggle('shadow-[0_0_0_1px_#0F5132]', c.checked);
            card.classList.toggle('border-[#E7E9E7]', !c.checked);
            card.querySelector('.sc-on').classList.toggle('hidden', !c.checked);
            card.querySelector('.sc-off').classList.toggle('hidden', c.checked);
        });
    }
    document.querySelectorAll('.sc-check').forEach(c => c.addEventListener('change', () => {
        if (c.checked && document.querySelectorAll('.sc-check:checked').length > 5) c.checked = false;
        refreshCats();
    }));
    refreshCats();

    // Technique chips (step 4)
    document.querySelectorAll('.tech-x').forEach(x => x.addEventListener('click', () => x.closest('span').remove()));

    // Place / offer type radios (steps 5-6)
    function bindRadioCards(checkCls, onCls, offCls, cardCls) {
        document.querySelectorAll('.' + checkCls).forEach(r => r.addEventListener('change', () => {
            document.querySelectorAll('.' + checkCls).forEach(rr => {
                const card = rr.nextElementSibling;
                card.classList.toggle('border-[#0F5132]', rr.checked);
                card.classList.toggle('shadow-[0_0_0_1px_#0F5132]', rr.checked);
                card.classList.toggle('border-[#E7E9E7]', !rr.checked);
                card.querySelector('.' + onCls).classList.toggle('hidden', !rr.checked);
                card.querySelector('.' + offCls).classList.toggle('hidden', rr.checked);
            });
        }));
    }
    bindRadioCards('pt-check', 'pt-on', 'pt-off');
    bindRadioCards('of-check', 'of-on', 'of-off');

    // Step 6: availability toggles, row delete, add product, live count
    function bindAvail(scope) {
        (scope || document).querySelectorAll('.avail-check').forEach(c => c.addEventListener('change', () => {
            const track = c.nextElementSibling;
            track.style.background = c.checked ? '#157A43' : '#C9CFC9';
            track.querySelector('.avail-thumb').style.left = c.checked ? '19.5px' : '2.5px';
        }));
    }
    bindAvail();
    function refreshProdCount() {
        const rowsNow = document.querySelectorAll('#product-rows .ob-prod-row').length;
        document.getElementById('prod-count').textContent = @json($isFr ? 'Afficher' : 'Showing') + ' ' + rowsNow + '/' + rowsNow + ' ' + @json($isFr ? 'produits' : 'products');
    }
    function bindDel(scope) {
        (scope || document).querySelectorAll('.row-del').forEach(b => b.addEventListener('click', () => {
            const row = b.closest('.ob-prod-row') || b.closest('.flex.flex-wrap.items-stretch');
            if (row) { row.remove(); refreshProdCount(); }
        }));
    }
    bindDel();
    // Generic client-side upload buttons: pick a file, confirm on the button
    document.querySelectorAll('.ob-upload').forEach(btn => btn.addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.addEventListener('change', () => {
            if (!input.files.length) return;
            const name = input.files[0].name;
            btn.textContent = '\u2713 ' + (name.length > 24 ? name.slice(0, 21) + '\u2026' : name);
            btn.classList.add('bg-obdeep', 'text-white');
            btn.classList.remove('text-[#14532D]', 'text-[#1B1B18]');
        });
        input.click();
    }));

    // Card/row delete buttons in the documents sections
    document.querySelectorAll('.ob-card-del').forEach(btn => btn.addEventListener('click', () => {
        const target = btn.closest('.ob-del-target');
        if (target) target.remove();
    }));

    // Real browser geolocation for the workshop address step
    const geoBtn = document.getElementById('ob-geo-btn');
    if (geoBtn) geoBtn.addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(pos => {
            const la = pos.coords.latitude, lo = pos.coords.longitude;
            document.getElementById('ob-geo-coords').textContent =
                Math.abs(la).toFixed(4) + '\u00B0 ' + (la >= 0 ? 'N' : 'S') + ', ' +
                Math.abs(lo).toFixed(4) + '\u00B0 ' + (lo >= 0 ? 'E' : 'W');
        });
    });

    // Add a service row (mirrors the add-product pattern)
    const addService = document.getElementById('add-service');
    if (addService) addService.addEventListener('click', () => {
        const rows = document.getElementById('service-rows');
        const first = rows.querySelector('.ob-svc-row');
        if (!first || rows.querySelectorAll('.ob-svc-row').length >= 10) return;
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
        rows.appendChild(clone);
        bindDel(clone);
        lucide.createIcons();
    });

    // Add a video: pick a file, clone the row with its name and size
    const addVideo = document.getElementById('add-video');
    if (addVideo) addVideo.addEventListener('click', () => {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'video/*';
        input.addEventListener('change', () => {
            if (!input.files.length) return;
            const f = input.files[0];
            const first = document.querySelector('.ob-video-row');
            if (!first) return;
            const clone = first.cloneNode(true);
            const title = clone.querySelector('p');
            if (title) title.textContent = f.name;
            const meta = clone.querySelectorAll('p')[1];
            if (meta) meta.textContent = (f.name.split('.').pop() || '').toUpperCase() + ' \u00B7 ' + (f.size / 1048576).toFixed(1) + ' MB';
            first.parentNode.insertBefore(clone, first.nextSibling);
            clone.querySelectorAll('.vid-del').forEach(b => b.addEventListener('click', () => clone.remove()));
            lucide.createIcons();
        });
        input.click();
    });

    document.getElementById('add-product').addEventListener('click', () => {
        const rows = document.getElementById('product-rows');
        if (rows.querySelectorAll('.ob-prod-row').length >= 10) return;
        const first = rows.querySelector('.ob-prod-row');
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input[type="text"]').forEach(i => i.value = '');
        rows.appendChild(clone);
        bindDel(clone); bindAvail(clone);
        lucide.createIcons();
        refreshProdCount();
    });
</script>

<form id="ob-signup-form" method="POST" action="{{ route('onboarding.store') }}" class="hidden" aria-hidden="true">
    @csrf
    <input type="hidden" name="lang" value="{{ $lang }}">
    <input type="hidden" name="first_name"><input type="hidden" name="last_name">
    <input type="hidden" name="email"><input type="hidden" name="phone">
    <input type="hidden" name="password"><input type="hidden" name="password_confirmation">
    <input type="hidden" name="account_type">
</form>
</body>
</html>
