@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $socialIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];

    $steps = $isFr
        ? ['Type de compte', 'Informations', 'Vérification']
        : ['Account type', 'Information', 'Verification'];

    $businessPerks = $isFr
        ? ['Créer votre boutique', 'Gérer vos produits', 'Suivre vos commandes', 'Développer votre marque']
        : ['Create your shop', 'Manage your products', 'Track your orders', 'Grow your brand'];
    $buyerPerks = $isFr
        ? ['Découvrir des produits uniques', 'Contacter les artisans', 'Passer des commandes', 'Acheter en toute sécurité']
        : ['Discover unique products', 'Contact the artisans', 'Place orders', 'Buy with confidence'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Créez votre compte sur la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.' : 'Create your account on the National Virtual Gallery of Cameroonian Crafts.' }}">
    <title>{{ $isFr ? 'Créer un compte — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Create an account — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#F7F2EC',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pine:   '#0E1D13',
                        panel:  '#091C10',
                        deep:   '#0A331C',
                        gold:   '#C9942E',
                        goldlt: '#D9A439',
                        goldbt: '#E0A52F',
                        muted:  '#8A857A',
                        sage:   '#A8B8AC',
                    },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
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
<body class="bg-[#F6F4F2] text-[#1D1B16] antialiased">

<!-- Signup panel -->
<section class="pt-4 px-4 pb-2">
    <div class="relative max-w-[1280px] mx-auto rounded-xl border border-sand bg-[#F4F0ED] shadow-sm overflow-hidden flex items-stretch">

        <div class="flex-1 min-w-0 px-6 sm:px-10 lg:px-16 pt-9 pb-10">
            <h1 class="text-center font-serif text-[38px] lg:text-[46px] text-[#1D1B16] leading-tight">
                {{ $isFr ? 'Créer un compte' : 'Create an account' }}
            </h1>
            <p class="mt-1.5 text-center text-[17px] text-[#6F6B60]">
                {{ $isFr ? 'Rejoignez la communauté de l\'artisanat camerounais' : 'Join the Cameroonian craft community' }}
            </p>

            <!-- Step indicator -->
            <div class="mt-9 flex items-start justify-center">
                @foreach($steps as $stepIdx => $stepLabel)
                    @if($stepIdx > 0)
                    <div class="step-line flex-1 max-w-[220px] h-px bg-[#DDD8CF] mt-[21px]" data-line="{{ $stepIdx }}"></div>
                    @endif
                    <div class="flex flex-col items-center gap-2.5 px-3">
                        <span class="step-circle w-[42px] h-[42px] rounded-full flex items-center justify-center text-[16px] transition-colors {{ $stepIdx === 0 ? 'bg-deep text-white font-semibold' : 'border border-[#C9C5BE] text-[#6F6B60]' }}" data-circle="{{ $stepIdx + 1 }}">
                            {{ $stepIdx + 1 }}
                        </span>
                        <span class="step-label text-[14.5px] whitespace-nowrap {{ $stepIdx === 0 ? 'font-semibold text-[#1D1B16]' : 'text-[#6F6B60]' }}" data-label="{{ $stepIdx + 1 }}">{{ $stepLabel }}</span>
                    </div>
                @endforeach
            </div>

            @if($errors->any())
                <div class="mt-6 max-w-[720px] mx-auto flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-[14px] text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/inscription" id="register-form">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                <!-- Step 1 — Type de compte -->
                <div class="step-panel" id="step-1">
                    <p class="mt-8 text-center text-[19px] text-[#3B382F]">
                        {{ $isFr ? 'Choisissez le type de compte qui vous correspond' : 'Choose the account type that suits you' }}
                    </p>

                    <div class="mt-7 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 max-w-[860px] mx-auto">
                        <!-- Business -->
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="business_owner" class="sr-only peer" @checked(old('role') === 'business_owner')>
                            <div class="relative h-full rounded-[16px] border border-[#DEDCD9] bg-[#F8F5F1] shadow-sm p-7 transition-all peer-checked:border-leaf peer-checked:ring-2 peer-checked:ring-leaf/30 hover:border-[#C9C5BE]">
                                <span class="absolute top-6 right-6 w-[26px] h-[26px] rounded-full border border-[#C9C5BE] bg-white flex items-center justify-center">
                                    <span class="role-dot hidden w-[14px] h-[14px] rounded-full bg-leaf" data-role-dot="business_owner"></span>
                                </span>
                                <p class="text-[18px] text-[#26251F]">{{ $isFr ? 'Je suis un' : 'I am a' }}</p>
                                <p class="mt-0.5 font-serif text-[32px] tracking-wide text-leaf leading-none">BUSINESS</p>
                                <div class="mt-6 flex items-start gap-5">
                                    <span class="w-[86px] h-[86px] shrink-0 rounded-full bg-[#0B301A] flex items-center justify-center">
                                        <i data-lucide="store" class="w-10 h-10 text-white" style="stroke-width:1.4"></i>
                                    </span>
                                    <p class="text-[15.5px] text-[#3B382F] leading-[1.55]">
                                        {{ $isFr
                                            ? 'Pour les artisans, entreprises et organisations qui créent, produisent et vendent des produits artisanaux.'
                                            : 'For artisans, businesses and organisations that create, produce and sell craft products.'
                                        }}
                                    </p>
                                </div>
                                <ul class="mt-7 space-y-4">
                                    @foreach($businessPerks as $perk)
                                    <li class="flex items-center gap-3.5">
                                        <span class="w-[24px] h-[24px] shrink-0 rounded-full bg-[#0D3A20] flex items-center justify-center">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-white" style="stroke-width:3"></i>
                                        </span>
                                        <span class="text-[16.5px] text-[#26251F]">{{ $perk }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </label>

                        <!-- Buyer -->
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="buyer" class="sr-only peer" @checked(old('role') === 'buyer')>
                            <div class="relative h-full rounded-[16px] border border-[#DEDCD9] bg-[#F8F5F1] shadow-sm p-7 transition-all peer-checked:border-gold peer-checked:ring-2 peer-checked:ring-gold/30 hover:border-[#C9C5BE]">
                                <span class="absolute top-6 right-6 w-[26px] h-[26px] rounded-full border border-[#C9C5BE] bg-white flex items-center justify-center">
                                    <span class="role-dot hidden w-[14px] h-[14px] rounded-full bg-gold" data-role-dot="buyer"></span>
                                </span>
                                <p class="text-[18px] text-[#26251F]">{{ $isFr ? 'Je suis un' : 'I am a' }}</p>
                                <p class="mt-0.5 font-serif text-[32px] tracking-wide text-gold leading-none">BUYER</p>
                                <div class="mt-6 flex items-start gap-5">
                                    <span class="w-[86px] h-[86px] shrink-0 rounded-full bg-[#C9932E] flex items-center justify-center">
                                        <i data-lucide="shopping-bag" class="w-10 h-10 text-white" style="stroke-width:1.4"></i>
                                    </span>
                                    <p class="text-[15.5px] text-[#3B382F] leading-[1.55]">
                                        {{ $isFr
                                            ? 'Pour les particuliers, entreprises et organisations qui recherchent et achètent des produits artisanaux.'
                                            : 'For individuals, businesses and organisations looking to find and buy craft products.'
                                        }}
                                    </p>
                                </div>
                                <ul class="mt-7 space-y-4">
                                    @foreach($buyerPerks as $perk)
                                    <li class="flex items-center gap-3.5">
                                        <span class="w-[24px] h-[24px] shrink-0 rounded-full bg-[#C9932E] flex items-center justify-center">
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-white" style="stroke-width:3"></i>
                                        </span>
                                        <span class="text-[16.5px] text-[#26251F]">{{ $perk }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </label>
                    </div>

                    <div class="mt-9 text-center">
                        <button type="button" id="to-step-2" disabled
                            class="inline-flex items-center gap-3 h-[52px] px-14 rounded-[12px] bg-deep text-white text-[16.5px] font-semibold transition-colors enabled:hover:bg-leaf disabled:opacity-40 disabled:cursor-not-allowed">
                            {{ $isFr ? 'Continuer' : 'Continue' }}
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="mt-9 max-w-[1010px] mx-auto rounded-[12px] bg-[#EFEAE1] px-6 py-4 flex items-center justify-center gap-4">
                        <i data-lucide="shield-check" class="w-8 h-8 shrink-0 text-[#26251F]" style="stroke-width:1.4"></i>
                        <p class="text-[15.5px] text-[#3B382F] leading-snug">
                            {{ $isFr ? 'Tous les comptes bénéficient d\'un accès sécurisé' : 'All accounts benefit from secure access' }}<br>
                            {{ $isFr ? 'et d\'un support dédié.' : 'and dedicated support.' }}
                        </p>
                    </div>
                </div>

                <!-- Step 2 — Informations -->
                <div class="step-panel hidden" id="step-2">
                    <div class="mt-8 max-w-[560px] mx-auto">
                        <label class="block text-[16.5px] font-medium text-[#26251F]">
                            {{ $isFr ? 'Nom complet' : 'Full name' }}
                        </label>
                        <input name="name" type="text" value="{{ old('name') }}" required
                            placeholder="{{ $isFr ? 'Entrez votre nom' : 'Enter your name' }}"
                            class="mt-2.5 w-full h-[52px] rounded-[12px] border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-[#E4E0D8] bg-[#FBF9F6]' }} px-5 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition">

                        <label class="mt-6 block text-[16.5px] font-medium text-[#26251F]">Email</label>
                        <input name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="{{ $isFr ? 'Entrez votre email' : 'Enter your email' }}"
                            class="mt-2.5 w-full h-[52px] rounded-[12px] border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-[#E4E0D8] bg-[#FBF9F6]' }} px-5 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition">

                        <label class="mt-6 block text-[16.5px] font-medium text-[#26251F]">
                            {{ $isFr ? 'Téléphone (optionnel)' : 'Phone (optional)' }}
                        </label>
                        <input name="phone" type="tel" value="{{ old('phone') }}"
                            placeholder="+237 6xx xxx xxx"
                            class="mt-2.5 w-full h-[52px] rounded-[12px] border border-[#E4E0D8] bg-[#FBF9F6] px-5 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition">

                        <label class="mt-6 block text-[16.5px] font-medium text-[#26251F]">
                            {{ $isFr ? 'Mot de passe' : 'Password' }}
                        </label>
                        <input name="password" type="password" required autocomplete="new-password"
                            placeholder="{{ $isFr ? 'Minimum 8 caractères' : 'Minimum 8 characters' }}"
                            class="mt-2.5 w-full h-[52px] rounded-[12px] border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-[#E4E0D8] bg-[#FBF9F6]' }} px-5 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition">

                        <label class="mt-6 block text-[16.5px] font-medium text-[#26251F]">
                            {{ $isFr ? 'Confirmer le mot de passe' : 'Confirm password' }}
                        </label>
                        <input name="password_confirmation" type="password" required autocomplete="new-password"
                            placeholder="••••••••"
                            class="mt-2.5 w-full h-[52px] rounded-[12px] border border-[#E4E0D8] bg-[#FBF9F6] px-5 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition">

                        <div class="mt-9 flex items-center justify-between gap-4">
                            <button type="button" data-back
                                class="inline-flex items-center gap-2.5 h-[52px] px-8 rounded-[12px] border border-[#E4E0D8] bg-white hover:bg-[#FBF9F6] text-[16px] font-semibold text-[#26251F] transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                                {{ $isFr ? 'Retour' : 'Back' }}
                            </button>
                            <button type="button" id="to-step-3"
                                class="inline-flex items-center gap-3 h-[52px] px-12 rounded-[12px] bg-deep hover:bg-leaf text-white text-[16.5px] font-semibold transition-colors">
                                {{ $isFr ? 'Continuer' : 'Continue' }}
                                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3 — Vérification -->
                <div class="step-panel hidden" id="step-3">
                    <div class="mt-8 max-w-[560px] mx-auto">
                        <p class="text-center text-[19px] text-[#3B382F]">
                            {{ $isFr ? 'Vérifiez vos informations avant de créer votre compte' : 'Check your details before creating your account' }}
                        </p>
                        <dl class="mt-7 rounded-[16px] border border-[#DEDCD9] bg-[#F8F5F1] divide-y divide-[#EAE6DE] text-[16px]">
                            <div class="flex items-center justify-between px-6 py-4">
                                <dt class="text-[#6F6B60]">{{ $isFr ? 'Type de compte' : 'Account type' }}</dt>
                                <dd class="font-semibold text-[#26251F]" id="recap-role">—</dd>
                            </div>
                            <div class="flex items-center justify-between px-6 py-4">
                                <dt class="text-[#6F6B60]">{{ $isFr ? 'Nom complet' : 'Full name' }}</dt>
                                <dd class="font-semibold text-[#26251F]" id="recap-name">—</dd>
                            </div>
                            <div class="flex items-center justify-between px-6 py-4">
                                <dt class="text-[#6F6B60]">Email</dt>
                                <dd class="font-semibold text-[#26251F]" id="recap-email">—</dd>
                            </div>
                            <div class="flex items-center justify-between px-6 py-4">
                                <dt class="text-[#6F6B60]">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt>
                                <dd class="font-semibold text-[#26251F]" id="recap-phone">—</dd>
                            </div>
                        </dl>
                        <p class="mt-5 text-center text-[13px] text-muted leading-relaxed">
                            {{ $isFr
                                ? 'Un email de vérification pourra vous être envoyé après la création de votre compte.'
                                : 'A verification email may be sent to you after your account is created.'
                            }}
                        </p>
                        <div class="mt-8 flex items-center justify-between gap-4">
                            <button type="button" data-back
                                class="inline-flex items-center gap-2.5 h-[52px] px-8 rounded-[12px] border border-[#E4E0D8] bg-white hover:bg-[#FBF9F6] text-[16px] font-semibold text-[#26251F] transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                                {{ $isFr ? 'Retour' : 'Back' }}
                            </button>
                            <button type="submit"
                                class="inline-flex items-center gap-3 h-[52px] px-12 rounded-[12px] bg-deep hover:bg-leaf text-white text-[16.5px] font-semibold transition-colors">
                                {{ $isFr ? 'Créer mon compte' : 'Create my account' }}
                                <i data-lucide="check" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <p class="mt-9 text-center text-[16px] text-[#26251F]">
                {{ $isFr ? 'Vous avez déjà un compte ?' : 'Already have an account?' }}
                <a href="/login?lang={{ $lang }}" class="ml-1.5 font-semibold text-leaf hover:text-deep transition-colors">
                    {{ $isFr ? 'Se connecter' : 'Sign in' }}
                </a>
            </p>
        </div>

        <!-- Basket photo strip (desktop) -->
        <div class="hidden lg:block w-[17.5%] shrink-0 self-stretch">
            <img src="{{ asset('images/landing/auth-baskets.png') }}" alt="" class="w-full h-full object-cover">
        </div>
    </div>
</section>

@include('auth.partials.replica-bottom')

<script>
    lucide.createIcons();

    const roleLabels = @json($isFr
        ? ['business_owner' => 'Business', 'buyer' => 'Acheteur (Buyer)']
        : ['business_owner' => 'Business', 'buyer' => 'Buyer']);

    const form = document.getElementById('register-form');
    let currentStep = 1;

    function goTo(step) {
        currentStep = step;
        document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');
        document.querySelectorAll('.step-circle').forEach(c => {
            const n = parseInt(c.dataset.circle, 10);
            c.classList.toggle('bg-deep', n <= step);
            c.classList.toggle('text-white', n <= step);
            c.classList.toggle('font-semibold', n <= step);
            c.classList.toggle('border', n > step);
            c.classList.toggle('border-[#C9C5BE]', n > step);
            c.classList.toggle('text-[#6F6B60]', n > step);
        });
        document.querySelectorAll('.step-label').forEach(l => {
            const n = parseInt(l.dataset.label, 10);
            l.classList.toggle('font-semibold', n === step);
            l.classList.toggle('text-[#1D1B16]', n === step);
            l.classList.toggle('text-[#6F6B60]', n !== step);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Role selection: show dot, enable continue
    function refreshRole() {
        const chosen = form.querySelector('input[name="role"]:checked');
        document.querySelectorAll('.role-dot').forEach(d => {
            d.classList.toggle('hidden', !chosen || d.dataset.roleDot !== chosen.value);
        });
        document.getElementById('to-step-2').disabled = !chosen;
    }
    form.querySelectorAll('input[name="role"]').forEach(r => r.addEventListener('change', refreshRole));
    refreshRole();

    document.getElementById('to-step-2').addEventListener('click', () => goTo(2));

    document.getElementById('to-step-3').addEventListener('click', () => {
        // Let the browser flag missing/invalid required fields of step 2
        const fields = document.querySelectorAll('#step-2 input');
        for (const f of fields) {
            if (!f.checkValidity()) { f.reportValidity(); return; }
        }
        const chosen = form.querySelector('input[name="role"]:checked');
        document.getElementById('recap-role').textContent  = chosen ? roleLabels[chosen.value] : '—';
        document.getElementById('recap-name').textContent  = form.querySelector('input[name="name"]').value || '—';
        document.getElementById('recap-email').textContent = form.querySelector('input[name="email"]').value || '—';
        document.getElementById('recap-phone').textContent = form.querySelector('input[name="phone"]').value || '—';
        goTo(3);
    });

    document.querySelectorAll('[data-back]').forEach(b => b.addEventListener('click', () => goTo(currentStep - 1)));

    // After a server-side validation error, reopen the information step
    @if($errors->any())
        goTo({{ $errors->has('role') ? 1 : 2 }});
    @endif
</script>
</body>
</html>
