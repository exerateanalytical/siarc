@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');   // the shared header/footer expect it

    /*
     | Every entry in $today was checked against the code before being written
     | here as a present-tense fact — the file that implements it is named in
     | the comment above each block. Anything the owner asked for that has no
     | implementation sits in $planned instead, labelled as not yet available.
     | This page is read by artisans deciding whether to trust us with their
     | work; a claim we cannot back is worse than no claim at all.
     */
    $today = $isFr ? [
        // app/Modules/Businesses/Services/VerificationService.php — documents are
        // uploaded, reviewed by a human, and set businesses.verification_tier.
        ['badge-check', 'Statut d\'artisan vérifié',
         'Vous téléversez vos documents officiels depuis votre tableau de bord. Une personne de notre équipe les examine, puis votre fiche porte le niveau accordé. Le badge dit que nous avons vu vos documents — il ne garantit ni la qualité d\'un produit ni la bonne fin d\'une commande.'],

        // routes/web.php /verification-certificat — real lookup on
        // businesses.certificate_no returning active/expired/revoked/suspended.
        ['shield-check', 'Certificat d\'adhésion vérifiable',
         'Votre certificat porte un numéro unique. N\'importe qui — un acheteur, une banque, un salon — peut saisir ce numéro sur notre page de vérification et voir s\'il est actif, expiré, suspendu ou révoqué. Le numéro renvoie à votre fiche, pas à une image que l\'on peut copier.'],

        // app/Modules/Products/Services/ProductImageService.php — images are
        // re-encoded to WebP and kept; products/product_images carry timestamps.
        ['file-clock', 'Fiche produit datée et conservée',
         'Chaque produit que vous publiez est enregistré avec sa date de création, son auteur, ses photos et ses descriptions. Les photos sont ré-encodées et stockées chez nous : même si vous modifiez la fiche plus tard, l\'enregistrement de votre publication d\'origine existe.'],

        // app/Http/Controllers/MessagingWebController.php — conversations are
        // stored server-side between a buyer and a business.
        ['message-square-lock', 'Messagerie interne conservée',
         'Vos échanges avec un acheteur restent sur la plateforme, rattachés à votre compte et au produit concerné. Si un désaccord survient, vous disposez d\'une trace écrite de ce qui a été dit et quand.'],

        // ProductActionsWebController::report → ProductReport, triaged in
        // /tableau-de-bord/admin/signalements.
        ['flag', 'Signalement et retrait',
         'Si vous trouvez sur la plateforme une annonce qui copie votre travail, vous pouvez la signaler en indiquant le motif. Le signalement arrive dans la file de modération de notre équipe, qui peut retirer la fiche ou suspendre le compte.'],

        // app/Support/SiarcClaim.php — imported SIARC 2026 profiles are offered,
        // never auto-assigned.
        ['user-check', 'Reprise de votre profil SIARC 2026',
         'Si votre profil a été importé du SIARC 2026, il reste non publié et appartient à personne tant que vous ne le revendiquez pas. Nous ne vous l\'attribuons jamais automatiquement : nous vous le proposons, et c\'est vous qui confirmez.'],

        // Laravel session + hashed passwords; cf. config/legal.php privacy doc.
        ['lock', 'Compte protégé',
         'Votre mot de passe est stocké haché, jamais en clair, et personne chez nous ne peut le lire. Les formulaires sont protégés contre la falsification de requêtes et le site ne dépose aucun cookie publicitaire.'],
    ] : [
        ['badge-check', 'Verified artisan status',
         'You upload your official documents from your dashboard. Someone on our team reviews them, and your profile then carries the tier granted. The badge says we have seen your documents — it guarantees neither product quality nor the proper completion of an order.'],
        ['shield-check', 'Verifiable membership certificate',
         'Your certificate carries a unique number. Anyone — a buyer, a bank, a trade fair — can enter that number on our verification page and see whether it is active, expired, suspended or revoked. The number resolves to your profile, not to an image anyone can copy.'],
        ['file-clock', 'Dated, retained product record',
         'Every product you publish is stored with its creation date, its author, its photos and its descriptions. Photos are re-encoded and stored by us: even if you edit the listing later, the record of your original publication exists.'],
        ['message-square-lock', 'Retained in-platform messaging',
         'Your exchanges with a buyer stay on the platform, attached to your account and the product concerned. If a disagreement arises, you have a written record of what was said and when.'],
        ['flag', 'Reporting and takedown',
         'If you find a listing on the platform that copies your work, you can report it with a reason. The report lands in our team\'s moderation queue, and we can remove the listing or suspend the account.'],
        ['user-check', 'Taking over your SIARC 2026 profile',
         'If your profile was imported from SIARC 2026, it stays unpublished and belongs to nobody until you claim it. We never assign it to you automatically: we offer it, and you confirm.'],
        ['lock', 'Protected account',
         'Your password is stored hashed, never in clear text, and nobody here can read it. Forms are protected against request forgery and the site sets no advertising cookies.'],
    ];

    /*
     | Asked for by the owner, but there is no code behind any of them today —
     | no hashing, no watermarking, no ledger, no NFC, no registry. They are
     | listed as intentions so nobody reads them as a live protection.
     */
    $planned = $isFr ? [
        'Empreinte visuelle par IA',
        'Hachage perceptuel des photos',
        'Filigrane invisible',
        'Traçabilité par registre distribué',
        'Certificat de transfert de propriété',
        'Signature numérique des certificats',
        'Détection d\'altération d\'un document',
        'Détection de fraude par IA',
        'Étiquettes NFC sur les pièces',
        'Registre des pièces perdues ou volées',
        'Vérification douane et exportateurs',
        'Vérification par API pour les marketplaces',
        'Passeport numérique de produit',
    ] : [
        'AI visual fingerprinting',
        'Perceptual image hashing',
        'Invisible watermarking',
        'Distributed-ledger provenance',
        'Ownership-transfer certificate',
        'Digitally signed certificates',
        'Document tamper detection',
        'AI fraud detection',
        'NFC tags on pieces',
        'Lost or stolen piece registry',
        'Customs and exporter verification',
        'Marketplace verification API',
        'Digital product passport',
    ];

    $cannot = $isFr ? [
        'Empêcher quelqu\'un de faire une capture d\'écran de votre fiche',
        'Empêcher la copie de vos photos une fois publiées',
        'Empêcher l\'imitation physique de vos modèles par un autre atelier',
        'Empêcher la mise en vente de contrefaçons sur d\'autres sites',
        'Agir à votre place contre un tiers qui vous copie hors de la plateforme',
    ] : [
        'Prevent someone from screenshotting your listing',
        'Prevent your photos from being copied once published',
        'Prevent another workshop from physically imitating your designs',
        'Prevent counterfeits being listed on other websites',
        'Act on your behalf against a third party who copies you off the platform',
    ];

    $dataFacts = $isFr ? [
        ['eye-off', 'Une boutique en brouillon est privée',
         'Tant que vous n\'avez pas publié votre fiche, elle n\'est visible que par vous et par notre équipe. Rien n\'apparaît dans les résultats de recherche.'],
        ['globe', 'Une boutique publiée est publique',
         'Une fois publiée, votre fiche et vos produits sont visibles par tous, y compris les moteurs de recherche. Vos messages, vos devis, vos commandes et vos documents de vérification ne le sont jamais.'],
        ['wallet', 'Nous ne touchons pas à l\'argent',
         'La plateforme n\'est pas partie à la vente et n\'en reçoit pas le prix ; seuls ses propres frais de service lui sont réglés. Le bon de commande et la facture sont vos documents ; le règlement se fait directement entre l\'acheteur et vous.'],
        ['user-round-cog', 'Vos données vous appartiennent',
         'Vous pouvez consulter et corriger vos informations depuis votre profil, demander une copie de vos données ou la fermeture de votre compte en nous écrivant. Nous ne vendons vos données à personne.'],
    ] : [
        ['eye-off', 'A draft shop is private',
         'Until you publish your profile, only you and our team can see it. Nothing appears in search results.'],
        ['globe', 'A published shop is public',
         'Once published, your profile and products are visible to everyone, including search engines. Your messages, quotes, orders and verification documents never are.'],
        ['wallet', 'We never touch the money',
         'The platform is not a party to the sale and does not receive the price; only its own service fees are paid to it. The purchase order and invoice are your documents; settlement happens directly between the buyer and you.'],
        ['user-round-cog', 'Your data is yours',
         'You can view and correct your details from your profile, request a copy of your data or the closure of your account by writing to us. We sell your data to nobody.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Comment nous protégeons votre travail — Artisan Hub 237' : 'How We Protect Your Work — Artisan Hub 237' }}</title>
    <meta name="description" content="{{ $isFr ? 'Ce que la plateforme fait aujourd\'hui pour protéger le travail des artisans, ce qui est encore en développement, et ce qu\'aucune plateforme ne peut garantir.' : 'What the platform does today to protect artisans\' work, what is still in development, and what no platform can guarantee.' }}">
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 217 164 57;
            --c-goldbt: 233 168 48;
            --c-leaf: 22 76 40;
        }
    </style>
    @include('pages.partials.icons')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F8F6F2] dark:bg-[#0A0C09] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[820px] mx-auto px-5 py-10">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Pour les artisans' : 'For artisans' }}</span>
        <h1 class="mt-3 text-[24px] sm:text-[28px] font-bold text-white leading-snug">
            {{ $isFr ? 'Comment nous protégeons votre travail' : 'How we protect your work' }}
        </h1>
        <p class="mt-3 text-[15px] md:text-[13.5px] text-[#B9C4BC] leading-relaxed">
            {{ $isFr
               ? 'Cette page dit exactement ce que la plateforme fait aujourd\'hui, ce qui n\'existe pas encore, et ce qu\'aucune plateforme au monde ne peut vous promettre. Nous préférons vous décevoir ici que sur une fausse promesse.'
               : 'This page says exactly what the platform does today, what does not exist yet, and what no platform anywhere can promise you. We would rather disappoint you here than on a false promise.' }}
        </p>
    </div>
</section>

<main class="max-w-[820px] mx-auto px-5 py-10 pb-24">

    {{-- Part 1 — verified, working today --}}
    <div class="flex items-center gap-2.5">
        <i data-lucide="circle-check" class="w-5 h-5 shrink-0 text-[#157A43] dark:text-[#339B56]"></i>
        <h2 class="text-[17px] font-bold">{{ $isFr ? 'Ce que nous faisons aujourd\'hui' : 'What we do today' }}</h2>
    </div>
    <p class="mt-1.5 text-[14px] md:text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">
        {{ $isFr ? 'Chacun de ces points fonctionne dès maintenant sur la plateforme.' : 'Every point below works on the platform right now.' }}
    </p>

    <div class="mt-5 space-y-3">
        @foreach($today as [$icon, $title, $body])
        <section class="ui-card">
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-9 h-9 rounded-lg bg-[#E2F3E8] dark:bg-[#0C3D1D] flex items-center justify-center">
                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] text-[#157A43] dark:text-[#339B56]"></i>
                </span>
                <div class="min-w-0">
                    <h3 class="ui-card-title">{{ $title }}</h3>
                    <p class="mt-1.5 text-[15px] md:text-[13px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">{{ $body }}</p>
                </div>
            </div>
        </section>
        @endforeach
    </div>

    <div class="mt-4 ui-alert ui-alert-ok">
        <i data-lucide="search-check" class="w-[18px] h-[18px]"></i>
        <span>
            {{ $isFr ? 'Vérifiez un certificat vous-même :' : 'Check a certificate yourself:' }}
            <a href="{{ route('certificate.verify', ['lang' => $lang]) }}" class="font-semibold underline">{{ $isFr ? 'page de vérification' : 'verification page' }}</a>
        </span>
    </div>

    {{-- Part 2 — asked for, not built. Kept visually quieter than part 1 so it
         cannot be mistaken for a live feature at a glance. --}}
    <div class="mt-12 flex items-center gap-2.5">
        <i data-lucide="hammer" class="w-5 h-5 shrink-0 text-[#C9942E]"></i>
        <h2 class="text-[17px] font-bold">{{ $isFr ? 'En développement' : 'Planned' }}</h2>
    </div>
    <p class="mt-1.5 text-[14px] md:text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
        {{ $isFr
           ? 'Ces protections sont à notre feuille de route. Aucune n\'est disponible aujourd\'hui : ne comptez sur aucune d\'entre elles pour une pièce que vous vendez maintenant.'
           : 'These protections are on our roadmap. None of them is available today: do not rely on any of them for a piece you are selling now.' }}
    </p>

    <div class="mt-5 grid grid-cols-2 md:grid-cols-3 gap-2.5">
        @foreach($planned as $item)
        <div class="bg-white dark:bg-[#12150F] border border-dashed border-[#E3DECF] dark:border-[#262B21] rounded-xl px-3.5 py-3 flex items-start gap-2">
            <i data-lucide="clock" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-[#B8B2A4]"></i>
            <span class="text-[14px] md:text-[12.5px] text-[#8A857A] dark:text-[#868778] leading-snug">{{ $item }}</span>
        </div>
        @endforeach
    </div>

    {{-- Part 3 — the limits. Verbatim in substance from the source copy. --}}
    <div class="mt-12 flex items-center gap-2.5">
        <i data-lucide="triangle-alert" class="w-5 h-5 shrink-0 text-[#B42025] dark:text-[#F0555C]"></i>
        <h2 class="text-[17px] font-bold">{{ $isFr ? 'Ce qu\'aucune plateforme ne peut garantir' : 'What no platform can guarantee' }}</h2>
    </div>
    <section class="mt-4 ui-card">
        <p class="text-[15px] md:text-[13px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
            {{ $isFr
               ? 'Soyons clairs, parce que d\'autres ne le sont pas : une fois qu\'une image est en ligne, elle peut être vue, et ce qui peut être vu peut être copié. Aucune technologie ne change cela. Voici ce que nous ne pouvons pas faire :'
               : 'Let us be clear, because others are not: once an image is online it can be seen, and what can be seen can be copied. No technology changes that. Here is what we cannot do:' }}
        </p>
        <ul class="mt-4 space-y-2.5">
            @foreach($cannot as $item)
            <li class="flex items-start gap-2.5 text-[15px] md:text-[13px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">
                <i data-lucide="x" class="w-4 h-4 mt-0.5 shrink-0 text-[#B42025] dark:text-[#F0555C]"></i>
                <span>{{ $item }}</span>
            </li>
            @endforeach
        </ul>
    </section>

    {{-- Part 4 — the IP note. The single point artisans most often misread. --}}
    <div class="mt-12 flex items-center gap-2.5">
        <i data-lucide="scale" class="w-5 h-5 shrink-0 text-[#14652F] dark:text-[#339B56]"></i>
        <h2 class="text-[17px] font-bold">{{ $isFr ? 'Enregistrer ici n\'est pas déposer un droit' : 'Registering here is not filing a right' }}</h2>
    </div>
    <section class="mt-4 ui-card">
        <p class="text-[15px] md:text-[13px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? 'L\'enregistrement de votre travail sur Artisan Hub 237 ne remplace pas le droit d\'auteur, une marque déposée, un dessin ou modèle industriel, ni aucune autre protection légale. Il ne vous confère aucun droit de propriété intellectuelle.'
               : 'Registering your work on Artisan Hub 237 does not replace copyright, a registered trademark, an industrial design, or any other legal protection. It grants you no intellectual property right.' }}
        </p>
        <p class="mt-3 text-[15px] md:text-[13px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? 'Ce qu\'il crée est autre chose : un enregistrement indépendant et horodaté de votre qualité d\'auteur, de la date de création, des métadonnées de la pièce et de l\'historique de son enregistrement. Cet enregistrement peut servir d\'élément à l\'appui d\'une revendication future — il ne la remplace pas.'
               : 'What it does create is something else: an independent, time-stamped record of your authorship, the creation date, the piece\'s metadata and its registration history. That record may support a future claim — it does not replace one.' }}
        </p>
        <div class="mt-4 ui-alert ui-alert-warn">
            <i data-lucide="info" class="w-[18px] h-[18px]"></i>
            <span>{{ $isFr
                ? 'Pour une protection juridique réelle, adressez-vous à l\'organisme compétent ou à un conseil en propriété intellectuelle. Nous ne sommes pas habilités à le faire pour vous.'
                : 'For real legal protection, approach the competent office or an intellectual property adviser. We are not qualified to do it for you.' }}</span>
        </div>
    </section>

    {{-- Part 5 — data handling, in the artisan's terms rather than a lawyer's --}}
    <div class="mt-12 flex items-center gap-2.5">
        <i data-lucide="database" class="w-5 h-5 shrink-0 text-[#14652F] dark:text-[#339B56]"></i>
        <h2 class="text-[17px] font-bold">{{ $isFr ? 'Vos données : qui voit quoi' : 'Your data: who sees what' }}</h2>
    </div>
    <div class="mt-4 space-y-3">
        @foreach($dataFacts as [$icon, $title, $body])
        <section class="ui-card">
            <div class="flex items-start gap-3">
                <span class="shrink-0 w-9 h-9 rounded-lg bg-[#FBF1DD] dark:bg-[#3A2B06] flex items-center justify-center">
                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] text-[#C9942E]"></i>
                </span>
                <div class="min-w-0">
                    <h3 class="ui-card-title">{{ $title }}</h3>
                    <p class="mt-1.5 text-[15px] md:text-[13px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">{{ $body }}</p>
                </div>
            </div>
        </section>
        @endforeach
    </div>

    <div class="mt-8 bg-[#EFF5F0] dark:bg-[#0A0C09] rounded-2xl px-5 sm:px-6 py-5">
        <h3 class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Demander vos données, ou signaler une copie' : 'Request your data, or report a copy' }}</h3>
        <p class="mt-2 text-[15px] md:text-[13px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? 'Écrivez-nous en précisant ce que vous demandez : une copie de vos données, une correction, la fermeture de votre compte, ou l\'URL d\'une fiche qui copie votre travail. Nous répondons dans un délai raisonnable.'
               : 'Write to us saying what you want: a copy of your data, a correction, the closure of your account, or the URL of a listing that copies your work. We reply within a reasonable time.' }}
        </p>
        <div class="mt-4 flex flex-wrap gap-2.5">
            <a href="{{ route('contact', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
            <a href="{{ route('legal.show', ['doc' => 'confidentialite', 'lang' => $lang]) }}" class="ui-btn ui-btn-secondary">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
        </div>
    </div>

</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
