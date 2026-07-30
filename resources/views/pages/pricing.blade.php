@php
    /*
     * The public tarifs/pricing page. Every price and name below comes straight
     * off `subscription_plans` via PlatformFees::plans() — see routes/web.php.
     * Nothing here invents a feature list or a limit for a tier: the table has
     * no column for either, so none is shown. The only per-plan text that is
     * not a database value is the one-line role hint, and it is inferred only
     * from the plan's own name (an individual-sounding name gets an
     * individual-sounding hint), never from a capability nothing enforces.
     */
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');   // the shared header/footer expect it

    $roleHints = [
        'basic'       => $isFr ? 'Pour un artisan qui travaille seul.' : 'For an artisan working alone.',
        'standard'    => $isFr ? "Pour un atelier en développement." : 'For a growing workshop.',
        'cooperative' => $isFr ? 'Pour une coopérative ou un groupe d\'artisans.' : 'For a cooperative or a group of artisans.',
        'centre'      => $isFr ? 'Pour un centre artisanal ou une structure plus large.' : 'For a craft centre or a larger organisation.',
    ];

    $pageTitle = $isFr ? 'Tarifs' : 'Pricing';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Artisan Hub 237</title>
    <meta name="description" content="{{ $isFr
        ? 'La cotisation annuelle qui permet à un artisan ou à un centre artisanal d\'apparaître dans l\'annuaire public Artisan Hub 237.'
        : 'The yearly subscription that lets an artisan or a craft centre appear in the public Artisan Hub 237 directory.' }}">
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
    <div class="max-w-[1040px] mx-auto px-5 py-10">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Pour les artisans' : 'For artisans' }}</span>
        <h1 class="mt-3 text-[24px] sm:text-[28px] font-bold text-white leading-snug">
            {{ $isFr ? 'Une cotisation annuelle, pour apparaître dans l\'annuaire public' : 'One yearly subscription, to appear in the public directory' }}
        </h1>
        <p class="mt-3 max-w-[640px] text-[15px] md:text-[13.5px] text-[#B9C4BC] leading-relaxed">
            {{ $isFr
               ? "Créer un compte et récupérer ou compléter votre fiche est gratuit. Ce que la cotisation ci-dessous achète, c'est la publication de cette fiche dans l'annuaire public : sans elle, votre boutique reste un brouillon visible de vous seul."
               : 'Creating an account and claiming or completing your profile is free. What the subscription below buys is publishing that profile in the public directory: without it, your shop stays a draft visible only to you.' }}
        </p>
    </div>
</section>

<main class="max-w-[1040px] mx-auto px-5 py-10 pb-24">

    @if(empty($plans))
    {{-- No priced plan exists to show. Say so rather than render an empty grid
         or invent a figure. --}}
    <div class="ui-card text-center py-14 px-4">
        <i data-lucide="badge-euro" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="ui-body max-w-md mx-auto">
            {{ $isFr
               ? "Aucune formule n'est configurée pour le moment. Écrivez-nous pour connaître le tarif en vigueur."
               : 'No plan is configured at the moment. Write to us to learn the current rate.' }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary mt-4">
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" data-pricing-plans="{{ count($plans) }}">
        @foreach($plans as $plan)
        <section class="ui-card flex flex-col" data-plan-slug="{{ $plan['slug'] }}">
            <div class="min-w-0">
                <h2 class="text-[16px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $plan['name'] }}</h2>
                @if(isset($roleHints[$plan['slug']]))
                <p class="mt-1 text-[14px] md:text-[12.5px] text-[#8A857A] dark:text-[#868778] leading-snug">{{ $roleHints[$plan['slug']] }}</p>
                @endif
            </div>

            <div class="mt-4 flex items-baseline gap-1.5">
                <span class="text-[26px] font-bold text-[#02301B] dark:text-[#339B56]">
                    {{ number_format($plan['price_yearly'], 0, ',', ' ') }}
                </span>
                <span class="text-[14px] md:text-[13px] font-semibold text-[#55524A] dark:text-[#B4B5A6]">
                    {{ $plan['currency'] }} {{ $isFr ? '/ an' : '/ year' }}
                </span>
            </div>

            <div class="mt-5 pt-4 border-t border-[#EFEBE2] dark:border-[#262B21]">
                @if($siacUser)
                <a href="{{ route('dashboard.payments', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary w-full justify-center">
                    {{ $isFr ? 'Choisir cette formule' : 'Choose this plan' }}
                </a>
                @else
                <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary w-full justify-center">
                    {{ $isFr ? 'Devenir membre' : 'Become a member' }}
                </a>
                @endif
            </div>
        </section>
        @endforeach
    </div>

    <p class="mt-6 text-[14px] md:text-[12.5px] text-[#8A857A] dark:text-[#868778] leading-relaxed max-w-[640px]">
        {{ $isFr
           ? 'La formule se choisit depuis votre tableau de bord, une fois votre compte créé. Le règlement se fait par mobile money et n\'est enregistré qu\'après vérification par un administrateur ; rien n\'est débité automatiquement.'
           : 'The plan is chosen from your dashboard, once your account exists. Payment is by mobile money and is only recorded once an administrator has checked it; nothing is charged automatically.' }}
    </p>
    @endif

    <div class="mt-10 bg-[#EFF5F0] dark:bg-[#0A0C09] rounded-2xl px-5 sm:px-6 py-5">
        <h3 class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">
            {{ $isFr ? 'Une question sur les tarifs ?' : 'A question about pricing?' }}
        </h3>
        <p class="mt-2 text-[15px] md:text-[13px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">
            {{ $isFr
               ? "La plateforme n'est pas partie aux ventes entre acheteurs et artisans et n'en reçoit pas le prix ; seule cette cotisation annuelle lui est réglée."
               : 'The platform is not a party to sales between buyers and artisans and does not receive the price; only this yearly subscription is paid to it.' }}
        </p>
        <div class="mt-4 flex flex-wrap gap-2.5">
            <a href="{{ route('contact', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
            <a href="{{ route('faq', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary">FAQ</a>
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
