@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Where "Suivant" exits into the real flow (steps 3-10 designs not provided yet)
    $nextUrl = $siacUser ? route('business.create') : '/inscription?lang=' . $lang;

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
    <div class="flex flex-col lg:flex-row items-stretch gap-0">

        <!-- Wizard sidebar -->
        <aside class="relative lg:w-[245px] shrink-0 bg-obside rounded-b-2xl lg:rounded-bl-2xl lg:rounded-br-none overflow-hidden">
            <div class="absolute inset-0 opacity-[0.07] bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative px-5 pt-7 pb-6">
                <h2 class="text-[15px] font-bold tracking-[0.02em] text-white uppercase leading-snug">
                    {{ $isFr ? 'Créer mon compte' : 'Create my account' }}<br>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}
                </h2>
                <p class="mt-2 text-[11.5px] text-[#B9CBBE] leading-relaxed">
                    <span id="side-sub-1">{{ $isFr ? 'Rejoignez la plus grande vitrine de l\'artisanat camerounais' : 'Join the largest showcase of Cameroonian craftsmanship' }}</span>
                    <span id="side-sub-2" class="hidden">{{ $isFr ? 'Étape 2 sur 10' : 'Step 2 of 10' }}</span>
                </p>

                <ol class="mt-6">
                    @foreach($wizardSteps as $wsIdx => [$wsTitle, $wsSub])
                    <li class="relative wizard-step" data-step="{{ $wsIdx + 1 }}">
                        @if($wsIdx < 9)<span class="absolute left-[17px] top-[38px] bottom-0 w-px bg-white/20"></span>@endif
                        @if($wsIdx < 2)
                        <button type="button" onclick="goToStep({{ $wsIdx + 1 }})" class="w-full text-left flex items-start gap-3.5 rounded-xl px-2 py-2.5 step-row">
                        @else
                        <div class="flex items-start gap-3.5 px-2 py-2.5">
                        @endif
                            <span class="step-circle relative z-10 w-[34px] h-[34px] shrink-0 rounded-full border border-white/40 bg-obside flex items-center justify-center text-[13px] font-semibold text-white">{{ $wsIdx + 1 }}</span>
                            <span class="pt-0.5 min-w-0">
                                <span class="block text-[13px] font-semibold text-white leading-snug">{{ $wsTitle }}</span>
                                <span class="step-sub block mt-0.5 text-[11px] text-[#B9CBBE] leading-snug">{{ $wsSub }}</span>
                            </span>
                        @if($wsIdx < 2)
                        </button>
                        @else
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ol>

                <div class="mt-5 rounded-xl border border-white/15 p-4">
                    <p class="flex items-center gap-2.5 text-[12.5px] font-bold text-white">
                        <img src="{{ asset('images/landing/ob-shield.png') }}" alt="" class="w-[22px] h-[24px]" aria-hidden="true">
                        <span id="side-secure-1">{{ $isFr ? 'Sécurisé & Vérifié' : 'Secure & Verified' }}</span>
                        <span id="side-secure-2" class="hidden">{{ $isFr ? 'Sécurisé & Confidentiel' : 'Secure & Confidential' }}</span>
                    </p>
                    <p class="mt-2.5 text-[11.5px] text-[#B9CBBE] leading-relaxed">
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
            <div id="panel-1">
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
                            <span class="absolute top-6 right-6 w-[24px] h-[24px] rounded-full border-2 border-[#C9CFC9] peer-checked:border-[#0F5132] flex items-center justify-center at-radio">
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
            <div id="panel-2" class="hidden">
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

                @php
                    $fieldCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg pl-10 pr-4 text-[13px] text-[#1B1B18] focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition';
                    $selectCls = 'w-full h-[46px] border border-[#E5E3E0] rounded-lg pl-10 pr-8 text-[13px] text-[#1B1B18] bg-white appearance-none cursor-pointer focus:outline-none focus:border-[#14532D]';
                @endphp

                <!-- Informations d'identité -->
                <section class="mt-6 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations d\'identité' : 'Identity information' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Prénom(s)' : 'First name(s)' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Aristide" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Nom' : 'Last name' }} *</label>
                            <div class="relative"><i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Ndop" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Date de naissance' : 'Date of birth' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="12 / 05 / 1988" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Genre' : 'Gender' }} *</label>
                            <div class="relative"><i data-lucide="users" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Masculin' : 'Male' }}</option><option>{{ $isFr ? 'Féminin' : 'Female' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Nationalité' : 'Nationality' }} *</label>
                            <div class="relative"><i data-lucide="book-user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Camerounaise' : 'Cameroonian' }}</option><option>{{ $isFr ? 'Autre' : 'Other' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Numéro de pièce d\'identité' : 'ID number' }} *</label>
                            <div class="relative"><i data-lucide="id-card" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="000123456" class="{{ $fieldCls }}">
                                <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Type de pièce d\'identité' : 'ID type' }} *</label>
                            <div class="relative"><i data-lucide="credit-card" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Carte Nationale d\'Identité' : 'National Identity Card' }}</option><option>{{ $isFr ? 'Passeport' : 'Passport' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Date d\'expiration' : 'Expiry date' }} *</label>
                            <div class="relative"><i data-lucide="calendar" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="12 / 05 / 2033" class="{{ $fieldCls }}"></div>
                        </div>
                    </div>
                </section>

                <!-- Téléphone & Email -->
                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Téléphone & Email' : 'Phone & Email' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Téléphone principal' : 'Main phone' }} *</label>
                            <div class="flex gap-2">
                                <span class="flex items-center gap-1.5 h-[46px] border border-[#E5E3E0] rounded-lg px-3 text-[13px] text-[#1B1B18] shrink-0">
                                    <img src="{{ asset('images/landing/ob-flag.png') }}" alt="" class="w-[20px] h-[14px] rounded-[2px]">
                                    +237
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                                </span>
                                <div class="relative flex-1">
                                    <input type="tel" value="6 90 12 34 56" class="w-full h-[46px] border border-[#E5E3E0] rounded-lg px-4 pr-10 text-[13px] focus:outline-none focus:border-[#14532D]">
                                    <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">Email *</label>
                            <div class="relative"><i data-lucide="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="email" value="aristide.ndop@gmail.com" class="{{ $fieldCls }}">
                                <i data-lucide="circle-check" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-[18px] h-[18px] text-white pointer-events-none" style="fill:#157A43"></i></div>
                        </div>
                    </div>
                </section>

                <!-- Photo de profil -->
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

                <!-- Adresse personnelle -->
                <section class="mt-5 border border-[#EDEDEB] rounded-xl p-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Adresse personnelle' : 'Personal address' }}</h2>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Pays' : 'Country' }} *</label>
                            <div class="relative"><i data-lucide="globe" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}"><option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option></select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Région' : 'Region' }} *</label>
                            <div class="relative"><i data-lucide="map-pin" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <select class="{{ $selectCls }}">
                                    @foreach(['Centre', 'Littoral', 'Ouest', 'Nord-Ouest', 'Sud-Ouest', 'Adamaoua', 'Est', 'Extrême-Nord', 'Nord', 'Sud'] as $reg)
                                    <option>{{ $reg }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Ville' : 'City' }} *</label>
                            <div class="relative"><i data-lucide="building-2" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Yaoundé" class="{{ $fieldCls }}"></div>
                        </div>
                        <div>
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Quartier' : 'District' }}</label>
                            <div class="relative"><i data-lucide="building" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                                <input type="text" value="Mvog-Ada" class="{{ $fieldCls }}"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[12.5px] text-[#3B382F] mb-1.5">{{ $isFr ? 'Adresse complète' : 'Full address' }} *</label>
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
                    <a href="{{ $nextUrl }}" class="inline-flex items-center gap-3 bg-obdeep hover:bg-leaf text-white text-[14px] font-semibold px-9 py-3.5 rounded-lg transition-colors">
                        {{ $isFr ? 'Suivant' : 'Next' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- Step-1 extras -->
    <div id="step1-extras">
        <!-- Les avantages de la plateforme -->
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

        <!-- Une plateforme officielle et sécurisée -->
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

    <!-- Help strip -->
    <section class="mt-4 bg-[#FEFAF3] border border-[#F2E8D8] rounded-2xl px-6 py-4 flex flex-wrap items-center gap-4">
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
</div>

<script>
    lucide.createIcons();

    const typeNames = @json(array_map(fn ($t) => $t[2], $accountTypes));

    // Radio dot visuals (peer can't reach into the absolute span reliably across browsers)
    function refreshRadios() {
        document.querySelectorAll('input[name="account_type"]').forEach(r => {
            const card = r.nextElementSibling;
            card.querySelector('.at-dot').classList.toggle('hidden', !r.checked);
            card.querySelector('.at-radio').style.borderColor = r.checked ? '#0F5132' : '#C9CFC9';
        });
    }
    document.querySelectorAll('input[name="account_type"]').forEach(r => r.addEventListener('change', refreshRadios));
    refreshRadios();

    function goToStep(n) {
        document.getElementById('panel-1').classList.toggle('hidden', n !== 1);
        document.getElementById('panel-2').classList.toggle('hidden', n !== 2);
        document.getElementById('step1-extras').classList.toggle('hidden', n !== 1);
        document.getElementById('side-sub-1').classList.toggle('hidden', n !== 1);
        document.getElementById('side-sub-2').classList.toggle('hidden', n === 1);
        document.getElementById('side-secure-1').classList.toggle('hidden', n !== 1);
        document.getElementById('side-secure-2').classList.toggle('hidden', n === 1);

        const chosen = document.querySelector('input[name="account_type"]:checked');
        document.querySelectorAll('.wizard-step').forEach(li => {
            const s = parseInt(li.dataset.step, 10);
            const circle = li.querySelector('.step-circle');
            const row = li.querySelector('.step-row');
            const sub = li.querySelector('.step-sub');
            const done = s < n, active = s === n;
            circle.innerHTML = done ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="w-4 h-4"><path d="M20 6 9 17l-5-5"/></svg>' : String(s);
            circle.style.background = active ? '#FFFFFF' : 'transparent';
            circle.style.color = active ? '#014622' : '#FFFFFF';
            circle.style.borderColor = active ? '#FFFFFF' : 'rgba(255,255,255,0.4)';
            if (row) row.style.background = active ? '#01602D' : 'transparent';
            if (s === 1 && sub) sub.textContent = (n > 1 && chosen) ? typeNames[parseInt(chosen.value, 10)] : @json($wizardSteps[0][1]);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    goToStep(1);

    // Profile photo live preview
    document.getElementById('ob-photo-input').addEventListener('change', function () {
        const f = this.files && this.files[0];
        if (f) document.getElementById('ob-photo-preview').src = URL.createObjectURL(f);
    });
</script>
</body>
</html>
