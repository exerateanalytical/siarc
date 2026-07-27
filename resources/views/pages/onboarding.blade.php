@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Real creation timestamp for the confirmation screen (creation happens right
    // before this page renders, so "now" is accurate).
    $submittedAt = now();
    $submittedAtLabel = $isFr ? $submittedAt->translatedFormat('d M Y \à H:i') : $submittedAt->format('d M Y \a\t H:i');

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

    // Only the steps that actually persist. Everything else (entreprise, catégories,
    // localisation, produits, documents) lives on its own real page in the dashboard.
    $wizardSteps = $isFr ? [
        ['Choisir le type de compte', 'Sélectionnez votre profil'],
        ['Identité & sécurité', 'Vos informations personnelles'],
        ['Vérification & création', 'Relisez puis créez le compte'],
    ] : [
        ['Choose account type', 'Select your profile'],
        ['Identity & security', 'Your personal information'],
        ['Review & creation', 'Check, then create the account'],
    ];

    $stepCount = count($wizardSteps);

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
        ['ob-sec-1.png', 'Plateforme indépendante', "Une entreprise privée\nau service de la promotion\nde l'artisanat."],
        ['ob-sec-2.png', 'Données sécurisées',    "Vos données sont protégées\net ne sont jamais partagées\nsans votre consentement."],
        ['ob-sec-3.png', 'Conformité',            "Conforme aux normes\nnationales et internationales\nde protection des données."],
        ['ob-sec-4.png', 'Accès global',          "Accédez à de nouveaux\nmarchés et opportunités\ninternationales."],
    ] : [
        ['ob-sec-1.png', 'Independent platform',  "A private company\nworking to promote\ncraftsmanship."],
        ['ob-sec-2.png', 'Secured data',       "Your data is protected\nand never shared without\nyour consent."],
        ['ob-sec-3.png', 'Compliance',         "Compliant with national\nand international data\nprotection standards."],
        ['ob-sec-4.png', 'Global access',      "Access new international\nmarkets and\nopportunities."],
    ];

    // ═══ What is left to do once the account exists — each entry is a REAL page ═══
    $nextSteps = [
        ['store', route('business.create'),
            $isFr ? 'Créer votre entreprise' : 'Create your business',
            $isFr ? "Nom commercial, catégories d'artisanat, région, logo et description de votre activité."
                  : 'Trade name, craft categories, region, logo and description of your activity.',
            $isFr ? "Renseigner l'entreprise" : 'Fill in the business'],
        ['package', route('products.web-create'),
            $isFr ? 'Publier vos produits' : 'Publish your products',
            $isFr ? 'Ajoutez vos créations une par une, avec photos, prix et disponibilité.'
                  : 'Add your creations one by one, with photos, price and availability.',
            $isFr ? 'Ajouter un produit' : 'Add a product'],
        ['badge-check', route('verification.show'),
            $isFr ? 'Faire vérifier votre profil' : 'Get your profile verified',
            $isFr ? "Déposez vos pièces (registre de commerce, attestation, carte d'artisan) pour obtenir le badge vérifié."
                  : 'Upload your documents (trade register, tax certificate, artisan card) to get the verified badge.',
            $isFr ? 'Déposer mes documents' : 'Upload my documents'],
    ];

    // @json() splits its argument on commas, so multi-entry arrays must live here as variables
    $typeNames = array_map(fn ($t) => $t[2], $accountTypes);
    $sideSecureTitles = $isFr
        ? [1 => 'Sécurisé & Vérifié', 2 => 'Sécurisé & Confidentiel', 3 => 'Sécurisé & Confidentiel']
        : [1 => 'Secure & Verified', 2 => 'Secure & Confidential', 3 => 'Secure & Confidential'];
    $sideSecureTexts = $isFr
        ? [3 => "Seules les informations affichées ici sont enregistrées à la création du compte. Le reste de votre profil se complète ensuite, à votre rythme."]
        : [3 => 'Only the information shown here is saved when the account is created. The rest of your profile is completed afterwards, at your own pace.'];

    $fieldCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg pl-10 pr-4 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
    $plainCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
    $labelCls = 'block text-[12.5px] text-[#3B382F] mb-1.5';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Créez votre compte artisan ou entreprise sur Artisan Hub 237.' : 'Create your artisan or business account on Artisan Hub 237.' }}">
    <title>{{ $isFr ? 'Créer mon compte artisan / entreprise — Artisan Hub 237' : 'Create my artisan / business account — Artisan Hub 237' }}</title>

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
                <span class="block text-[12px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">Artisan Hub 237</span>
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
                        @if($wsIdx < $stepCount - 1)<span class="absolute left-[17px] top-[38px] bottom-0 w-px bg-white/20"></span>@endif
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

                <div class="mt-4 rounded-xl border border-white/15 p-4">
                    <p class="text-[12.5px] font-bold text-[#E5A82E]">{{ $isFr ? 'Et le reste de mon profil ?' : 'What about the rest of my profile?' }}</p>
                    <p class="mt-2 text-[11.5px] text-[#B9CBBE] leading-relaxed">
                        {{ $isFr
                            ? "Entreprise, catégories, atelier, produits et documents se renseignent depuis votre tableau de bord, juste après la création du compte."
                            : 'Business, categories, workshop, products and documents are filled in from your dashboard, right after the account is created.'
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
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Étape 1 sur 3' : 'Step 1 of 3' }}</p>
                        <h1 class="mt-1 text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Choisissez votre type de compte' : 'Choose your account type' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[430px]">
                            {{ $isFr ? 'Sélectionnez le profil qui correspond le mieux à votre activité sur la plateforme.' : 'Select the profile that best matches your activity on the platform.' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[11.5px] text-[#3B382F]">33% {{ $isFr ? 'terminé' : 'complete' }}</p>
                        <div class="mt-1.5 w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[33%] rounded-full bg-[#10592E]"></div></div>
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

            <!-- ═══════ Step 2 — identity & security ═══════ -->
            <div id="panel-2" class="ob-panel hidden">
                <button type="button" onclick="goToStep(1)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vos informations personnelles' : 'Your personal information' }}</h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[460px]">
                            {{ $isFr ? 'Ces informations nous permettent de vous identifier et de sécuriser votre compte.' : 'This information lets us identify you and secure your account.' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 2 sur 3' : 'Step 2 of 3' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-[66%] rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">66% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations d\'identité' : 'Identity information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="ob-first-name" class="{{ $labelCls }}">{{ $isFr ? 'Prénom(s)' : 'First name(s)' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" id="ob-first-name" name="first_name" value="{{ old('first_name') }}" placeholder="Aristide" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label for="ob-last-name" class="{{ $labelCls }}">{{ $isFr ? 'Nom' : 'Last name' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" id="ob-last-name" name="last_name" value="{{ old('last_name') }}" placeholder="Ndop" class="{{ $fieldCls }}"></div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Téléphone & Email' : 'Phone & Email' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label for="ob-phone" class="{{ $labelCls }}">{{ $isFr ? 'Téléphone principal' : 'Main phone' }}</label>
                            <div class="flex gap-2">
                                <span class="flex items-center gap-1.5 h-[46px] border border-[#E5E3E0] rounded-lg px-3 text-[13px] text-[#1B1B18] shrink-0">
                                    <img src="{{ asset('images/landing/ob-flag.png') }}" alt="" class="w-[20px] h-[14px] rounded-[2px]">
                                    +237
                                </span>
                                <div class="relative flex-1 min-w-0">
                                    <input type="tel" id="ob-phone" name="phone" value="{{ old('phone') }}" placeholder="6 90 12 34 56" class="{{ $plainCls }}">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="ob-email" class="{{ $labelCls }}">Email *</label>
                            <div class="relative"><i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="email" id="ob-email" name="email" value="{{ old('email') }}" placeholder="aristide.ndop@gmail.com" class="{{ $fieldCls }}"></div>
                            <p class="mt-1.5 text-[11px] text-[#8A857A]">{{ $isFr ? 'Un code de vérification y sera envoyé.' : 'A verification code will be sent there.' }}</p>
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
                            <label for="ob-password" class="{{ $labelCls }}">{{ $isFr ? 'Mot de passe' : 'Password' }} *</label>
                            <input type="password" id="ob-password" name="password" autocomplete="new-password" placeholder="********" class="{{ $plainCls }}">
                            <p class="mt-1.5 text-[11px] text-[#8A857A]">{{ $isFr ? '8 caractères minimum.' : 'At least 8 characters.' }}</p>
                        </div>
                        <div>
                            <label for="ob-password-confirm" class="{{ $labelCls }}">{{ $isFr ? 'Confirmer le mot de passe' : 'Confirm password' }} *</label>
                            <input type="password" id="ob-password-confirm" name="password_confirmation" autocomplete="new-password" placeholder="********" class="{{ $plainCls }}">
                        </div>
                    </div>
                </section>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
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

            <!-- ═══════ Step 3 — review & account creation ═══════ -->
            <div id="panel-3" class="ob-panel hidden">
                <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#1B1B18] hover:text-leaf">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retour' : 'Back' }}
                </button>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-[23px] font-bold text-[#1B1B18] flex flex-wrap items-center gap-3">
                            {{ $isFr ? 'Vérification & création' : 'Review & creation' }}
                            <span class="inline-flex items-center bg-[#EBF4ED] rounded-full px-3.5 py-1.5 text-[11.5px] font-semibold text-[#14532D]">{{ $isFr ? 'Dernière étape' : 'Last step' }}</span>
                        </h1>
                        <p class="mt-1.5 text-[13px] text-[#6F6B60] max-w-[460px]">{{ $isFr ? 'Relisez les informations que vous venez de saisir. Elles pourront être modifiées à tout moment depuis votre tableau de bord.' : 'Check the information you have just entered. You will be able to edit it at any time from your dashboard.' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Étape 3 sur 3' : 'Step 3 of 3' }}</p>
                        <div class="mt-1.5 flex items-center gap-2.5">
                            <div class="w-[150px] h-[7px] rounded-full bg-[#E8EAE9]"><div class="h-full w-full rounded-full bg-[#10592E]"></div></div>
                            <span class="text-[11.5px] text-[#3B382F] whitespace-nowrap">100% {{ $isFr ? 'terminé' : 'complete' }}</span>
                        </div>
                    </div>
                </div>

                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Ce qui sera enregistré' : 'What will be saved' }}</h2>
                        <button type="button" onclick="goToStep(2)" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Modifier' : 'Edit' }}</button>
                    </div>
                    <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div class="border border-[#EFEFED] rounded-xl p-4">
                            <dt class="flex items-center gap-2.5 text-[12px] font-bold text-[#1B1B18]">
                                <i data-lucide="user-round" class="w-4 h-4 shrink-0 text-[#157A43]" style="stroke-width:1.9"></i>
                                {{ $isFr ? 'Type de compte' : 'Account type' }}
                            </dt>
                            <dd id="sum-type" class="mt-2 text-[12.5px] text-[#3B382F] break-words">—</dd>
                        </div>
                        <div class="border border-[#EFEFED] rounded-xl p-4">
                            <dt class="flex items-center gap-2.5 text-[12px] font-bold text-[#1B1B18]">
                                <i data-lucide="id-card" class="w-4 h-4 shrink-0 text-[#157A43]" style="stroke-width:1.9"></i>
                                {{ $isFr ? 'Identité' : 'Identity' }}
                            </dt>
                            <dd id="sum-name" class="mt-2 text-[12.5px] text-[#3B382F] break-words">—</dd>
                        </div>
                        <div class="border border-[#EFEFED] rounded-xl p-4">
                            <dt class="flex items-center gap-2.5 text-[12px] font-bold text-[#1B1B18]">
                                <i data-lucide="mail" class="w-4 h-4 shrink-0 text-[#157A43]" style="stroke-width:1.9"></i>
                                Email
                            </dt>
                            <dd id="sum-email" class="mt-2 text-[12.5px] text-[#3B382F] break-all">—</dd>
                        </div>
                        <div class="border border-[#EFEFED] rounded-xl p-4">
                            <dt class="flex items-center gap-2.5 text-[12px] font-bold text-[#1B1B18]">
                                <i data-lucide="phone" class="w-4 h-4 shrink-0 text-[#157A43]" style="stroke-width:1.9"></i>
                                {{ $isFr ? 'Téléphone' : 'Phone' }}
                            </dt>
                            <dd id="sum-phone" class="mt-2 text-[12.5px] text-[#3B382F] break-words">—</dd>
                        </div>
                    </dl>
                </section>

                <section class="mt-4 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Et juste après ?' : 'And right after?' }}</h2>
                    <p class="mt-1 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Votre compte est créé immédiatement. Vous complétez ensuite votre profil depuis votre tableau de bord, dans l\'ordre que vous voulez.' : 'Your account is created immediately. You then complete your profile from your dashboard, in whatever order you like.' }}</p>
                    <ol class="mt-4 space-y-3">
                        @foreach($nextSteps as $nsIdx => [$nsIcon, $nsHref, $nsTitle, $nsDesc, $nsBtn])
                        <li class="flex items-start gap-3.5">
                            <span class="w-[30px] h-[30px] shrink-0 rounded-full bg-[#EBF4ED] flex items-center justify-center text-[12px] font-bold text-[#14532D]">{{ $nsIdx + 1 }}</span>
                            <div class="min-w-0 pt-0.5">
                                <p class="text-[13px] font-bold text-[#1B1B18]">{{ $nsTitle }}</p>
                                <p class="mt-1 text-[12px] text-[#6F6B60] leading-relaxed">{{ $nsDesc }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </section>

                <div class="relative mt-4">
                    <div class="rounded-xl bg-[#F3F7F3] px-4 py-4 lg:pr-[250px] flex items-start gap-3">
                        <label class="flex items-start gap-3 cursor-pointer select-none">
                            <input type="checkbox" checked class="sr-only ob-terms-check">
                            <span class="ob-terms-box mt-0.5 w-[17px] h-[17px] shrink-0 rounded border-[1.5px] border-[#157A43] flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3 text-[#157A43]" style="stroke-width:3.2"></i>
                            </span>
                            <span class="text-[12.5px] text-[#3B382F] leading-relaxed">
                                {{ $isFr ? "Je certifie que toutes les informations fournies sont exactes et que j'accepte" : 'I certify that all the information provided is accurate and that I accept' }}
                                {{ $isFr ? 'les' : 'the' }} <a href="{{ route('terms') }}" target="_blank" class="text-[#157A43] underline underline-offset-2">{{ $isFr ? "conditions générales d'utilisation" : 'general terms of use' }}</a> {{ $isFr ? 'de la plateforme.' : 'of the platform.' }}
                            </span>
                        </label>
                    </div>
                    <button type="button" id="ob-submit" class="lg:absolute lg:right-0 lg:top-[26px] mt-3 lg:mt-0 w-full lg:w-auto inline-flex items-center justify-center gap-3 bg-[#025127] hover:bg-leaf text-white text-[14px] font-semibold px-7 py-3.5 rounded-lg shadow-md transition-colors">
                        {{ $isFr ? 'Créer mon compte' : 'Create my account' }}
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="mt-5">
                    <button type="button" onclick="goToStep(2)" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        {{ $isFr ? 'Précédent' : 'Previous' }}
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- ═══════ Confirmation screen (after the account is created) ═══════ -->
    <div id="success-screen" class="hidden flex-col lg:flex-row items-start gap-6 pt-6">

        <!-- White sidebar -->
        <aside class="hidden lg:block lg:w-[230px] shrink-0 lg:pl-3">
            <h2 class="text-[15px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase leading-snug">
                {{ $isFr ? 'Créer mon compte' : 'Create my account' }}<br>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}
            </h2>
            <p class="mt-3 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Étapes complétées' : 'Completed steps' }}</p>
            <p class="text-[15px] font-bold text-[#157A43]">{{ $stepCount }} {{ $isFr ? 'sur' : 'of' }} {{ $stepCount }}</p>

            <ol class="mt-7">
                @foreach($wizardSteps as $wsIdx => [$wsTitle, $wsSub])
                <li class="relative {{ $wsIdx > 0 ? 'mt-5' : '' }}">
                    @if($wsIdx < $stepCount - 1)<span class="absolute left-[13px] top-[32px] -bottom-5 w-px bg-[#D9E6DC]"></span>@endif
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
                <img src="{{ asset('images/landing/ob12-check.png') }}" alt="" class="w-[120px] sm:w-[140px] shrink-0" aria-hidden="true">
                <div class="flex-1 min-w-0 text-center sm:text-left">
                    <p class="text-[16px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Félicitations !' : 'Congratulations!' }} 🎉</p>
                    <h1 class="mt-1.5 text-[22px] sm:text-[26px] font-bold text-[#1B1B18] leading-snug">{{ $isFr ? 'Votre compte a été créé.' : 'Your account has been created.' }}</h1>
                    <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed max-w-[480px]">
                        {{ $isFr
                            ? "Bienvenue sur Artisan Hub 237. Vous êtes déjà connecté : il ne reste plus qu'à compléter votre profil pour être visible des acheteurs."
                            : 'Welcome to Artisan Hub 237. You are already signed in: all that is left is to complete your profile so buyers can find you.'
                        }}
                    </p>
                </div>
                <img src="{{ asset('images/landing/ob12-mail.png') }}" alt="" class="w-[165px] shrink-0 hidden lg:block" aria-hidden="true">
            </section>

            <!-- Account info row -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-y-4 sm:divide-x divide-[#EDEDEB]">
                <div class="min-w-0">
                    <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Compte' : 'Account' }}</p>
                    <p class="mt-1 text-[15px] font-bold text-[#14652F] break-all">{{ $siacUser['email'] ?? '—' }}</p>
                </div>
                <div class="sm:pl-8 min-w-0">
                    <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Date de création' : 'Creation date' }}</p>
                    <p class="mt-1 text-[15px] font-bold text-[#1B1B18]">{{ $submittedAtLabel }}</p>
                </div>
                <div class="sm:pl-8 flex items-center gap-3.5 min-w-0">
                    <span class="w-[46px] h-[46px] shrink-0 rounded-full bg-[#E3F0E7] flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-[#14532D]"></i>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-[12.5px] text-[#55524A]">{{ $isFr ? 'Type de compte' : 'Account type' }}</span>
                        <span class="success-type-name block mt-0.5 text-[15px] font-bold text-[#1B1B18]">{{ $typeNames[0] }}</span>
                    </span>
                </div>
            </section>

            <!-- Next steps: the real pages that do the rest -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Les prochaines étapes' : 'The next steps' }}</h2>
                <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Chaque étape se fait sur sa propre page et peut être reprise plus tard.' : 'Each step has its own page and can be resumed later.' }}</p>
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($nextSteps as $nsIdx => [$nsIcon, $nsHref, $nsTitle, $nsDesc, $nsBtn])
                    <div class="border border-[#EFEFED] rounded-2xl p-5 flex flex-col">
                        <span class="w-[54px] h-[54px] rounded-2xl bg-[#E8F2EC] flex items-center justify-center">
                            <i data-lucide="{{ $nsIcon }}" class="w-[24px] h-[24px] text-[#14652F]" style="stroke-width:1.8"></i>
                        </span>
                        <p class="mt-4 text-[11.5px] font-semibold text-[#8A857A]">{{ $isFr ? 'Étape' : 'Step' }} {{ $nsIdx + 1 }}</p>
                        <h3 class="mt-0.5 text-[14.5px] font-bold text-[#1B1B18]">{{ $nsTitle }}</h3>
                        <p class="mt-2.5 text-[12.5px] text-[#55524A] leading-relaxed flex-1">{{ $nsDesc }}</p>
                        <a href="{{ $nsHref }}" class="mt-5 inline-flex items-center justify-center gap-2 border border-[#BFD4C6] hover:border-[#14652F] hover:bg-[#F3F8F3] rounded-lg px-5 py-2.5 text-[13.5px] font-semibold text-[#14652F] transition-colors">
                            {{ $nsBtn }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="/tableau-de-bord" class="inline-flex items-center gap-2.5 bg-obdeep hover:bg-leaf text-white text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        {{ $isFr ? 'Aller à mon tableau de bord' : 'Go to my dashboard' }}
                    </a>
                    <a href="{{ route('products.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 border border-[#E5E3E0] hover:border-[#14532D] text-[#1B1B18] text-[13.5px] font-semibold px-6 py-3 rounded-lg transition-colors">
                        <i data-lucide="compass" class="w-4 h-4"></i>
                        {{ $isFr ? 'Explorer la plateforme' : 'Explore the platform' }}
                    </a>
                </div>
            </section>

            <!-- Email verification notice -->
            <section class="mt-4 bg-white border border-[#ECECEA] rounded-2xl px-6 py-6 flex flex-col sm:flex-row items-start gap-5">
                <span class="w-[46px] h-[46px] shrink-0 rounded-full bg-[#E3F0E7] flex items-center justify-center">
                    <i data-lucide="mail" class="w-5 h-5 text-[#14652F]" style="stroke-width:1.8"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Vérifiez votre adresse email' : 'Verify your email address' }}</h2>
                    <p class="mt-1.5 text-[12.5px] text-[#55524A] leading-relaxed">
                        {{ $isFr ? 'Un code de vérification vient d\'être envoyé à' : 'A verification code has just been sent to' }}
                        <span class="font-semibold text-[#14652F] break-all">{{ $siacUser['email'] ?? '—' }}</span>.
                        {{ $isFr ? 'Pensez à vérifier vos spams si vous ne le voyez pas.' : 'Remember to check your spam folder if you do not see it.' }}
                    </p>
                </div>
            </section>

            <!-- Help bar -->
            <section class="mt-4 bg-[#EFF5F1] rounded-xl px-6 py-4 flex items-start gap-5">
                <i data-lucide="headphones" class="w-[30px] h-[30px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
                <p class="text-[12.5px] text-[#3B382F] leading-relaxed">
                    <span class="font-bold text-[#1B1B18]">{{ $isFr ? "Besoin d'aide ?" : 'Need help?' }}</span>
                    {{ $isFr ? 'Notre équipe est là pour vous accompagner.' : 'Our team is here to support you.' }}<br>
                    {{ $isFr ? 'Contactez-nous par email à' : 'Contact us by email at' }}
                    <a href="mailto:support@galerie-artisanat.cm" class="font-semibold text-[#14652F] break-all">support@galerie-artisanat.cm</a>
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
            <h2 class="relative text-center text-[16px] font-bold text-white">{{ $isFr ? 'Une plateforme fiable et sécurisée' : 'A trusted and secure platform' }}</h2>
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

    <!-- Help strip (shown on every wizard step) -->
    <section id="strip-help" class="mt-4 bg-[#FEFAF3] border border-[#F2E8D8] rounded-2xl px-6 py-4 flex flex-wrap items-center gap-4">
        <img src="{{ asset('images/landing/ob-help.png') }}" alt="" class="w-[46px] h-[46px] shrink-0" aria-hidden="true">
        <div class="min-w-0">
            <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Besoin d\'aide pour vous inscrire ?' : 'Need help signing up?' }}</h2>
            <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? 'Notre équipe est disponible pour vous accompagner à chaque étape.' : 'Our team is available to support you at every step.' }}</p>
        </div>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="sm:ml-auto shrink-0 inline-flex items-center gap-2.5 border border-[#14532D] text-[#14532D] hover:bg-[#14532D]/5 text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            {{ $isFr ? 'Contactez-nous' : 'Contact us' }}
        </a>
    </section>
</div>

<script>
    lucide.createIcons();

    const typeNames = @json($typeNames);
    const stepCount = @json($stepCount);
    const stepSubTpl = @json($isFr ? 'Étape %N sur %T' : 'Step %N of %T');
    const sideSubDefault = @json($isFr ? 'Rejoignez la plus grande vitrine de l\'artisanat camerounais' : 'Join the largest showcase of Cameroonian craftsmanship');
    const secureTitles = @json($sideSecureTitles);
    const secureTextDefault = @json($isFr
        ? 'Vos données sont protégées et utilisées uniquement pour la vérification et la gestion de votre compte.'
        : 'Your data is protected and used only for the verification and management of your account.');
    const secureTexts = @json($sideSecureTexts);
    const defaultSub1 = @json($wizardSteps[0][1]);
    const defaultTitle1 = @json($wizardSteps[0][0]);
    const doneTitle1 = @json($isFr ? 'Type de compte' : 'Account type');
    const notProvided = @json($isFr ? 'Non renseigné' : 'Not provided');
    // Sentinel step for the post-creation confirmation screen (not a wizard step).
    const SUCCESS_STEP = 11;

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

    // Step 3 summary — echoes back ONLY what the user typed in this wizard.
    function refreshSummary() {
        const val = id => (document.getElementById(id).value || '').trim();
        const chosen = document.querySelector('input[name="account_type"]:checked');
        const name = (val('ob-first-name') + ' ' + val('ob-last-name')).trim();
        const phone = val('ob-phone');
        document.getElementById('sum-type').textContent = chosen ? typeNames[parseInt(chosen.value, 10)] : notProvided;
        document.getElementById('sum-name').textContent = name || notProvided;
        document.getElementById('sum-email').textContent = val('ob-email') || notProvided;
        document.getElementById('sum-phone').textContent = phone ? ('+237 ' + phone) : notProvided;
    }

    let currentStep = 1;

    // ── Real signup: "Créer mon compte" creates the account ──
    const obAlready = @json((bool) $siacUser);
    document.getElementById('ob-submit').addEventListener('click', () => {
        if (obAlready) { goToStep(SUCCESS_STEP); return; }
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

        // SUCCESS_STEP = post-creation confirmation screen (own white sidebar + layout)
        const success = n === SUCCESS_STEP;
        document.getElementById('wizard-flex').classList.toggle('hidden', success);
        const sc = document.getElementById('success-screen');
        sc.classList.toggle('hidden', !success);
        sc.classList.toggle('flex', success);
        document.body.style.background = success ? '#FBFCFC' : '';
        if (success) {
            const chosenType = document.querySelector('input[name="account_type"]:checked');
            if (chosenType) document.querySelectorAll('.success-type-name').forEach(el => el.textContent = typeNames[parseInt(chosenType.value, 10)]);
            document.getElementById('step1-extras').classList.add('hidden');
            document.getElementById('strip-help').classList.add('hidden');
            lucide.createIcons();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        for (let i = 1; i <= stepCount; i++) {
            const p = document.getElementById('panel-' + i);
            if (p) p.classList.toggle('hidden', i !== n);
        }
        document.getElementById('step1-extras').classList.toggle('hidden', n !== 1);
        document.getElementById('strip-help').classList.remove('hidden');

        if (n === stepCount) refreshSummary();

        document.getElementById('side-sub').textContent = n === 1
            ? sideSubDefault
            : stepSubTpl.replace('%N', String(n)).replace('%T', String(stepCount));
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

    // Terms checkbox on the last step
    document.querySelectorAll('.ob-terms-check').forEach(c => c.addEventListener('change', () => {
        const box = c.nextElementSibling;
        box.style.opacity = '1';
        const mark = box.querySelector('svg, i');
        if (mark) mark.style.visibility = c.checked ? 'visible' : 'hidden';
        box.style.borderColor = c.checked ? '#157A43' : '#C9CFC9';
    }));

    // Initial step: confirmation screen after creation, identity step on validation
    // errors, otherwise the wizard start.
    @if(request('submitted') && $siacUser)
    goToStep(11);
    @elseif($errors->any())
    goToStep(2);
    @else
    goToStep(1);
    @endif
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
