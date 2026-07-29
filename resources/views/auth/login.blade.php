@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $loginFeatures = $isFr ? [
        ['shield-check', 'Accès sécurisé',           "Vos informations sont\nprotégées"],
        ['users-round',  'Communauté vérifiée',      "Rejoignez des milliers\nd'artisans et acheteurs"],
        ['user-cog',     'Opportunités illimitées',  "Découvrez, connectez\net développez votre activité"],
    ] : [
        ['shield-check', 'Secure access',            "Your information is\nprotected"],
        ['users-round',  'Verified community',       "Join thousands of\nartisans and buyers"],
        ['user-cog',     'Unlimited opportunities',  "Discover, connect\nand grow your business"],
    ];

    $socialIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];

@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Connectez-vous à Artisan Hub 237.' : 'Sign in to Artisan Hub 237.' }}">
    <title>{{ $isFr ? 'Se connecter — Artisan Hub 237' : 'Sign in — Artisan Hub 237' }}</title>

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
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F6F4F2] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

<!-- Hero: brand panel + login card + photo -->
<section class="pt-4 px-4 pb-2">
    <div class="relative max-w-[1280px] mx-auto bg-panel rounded-xl overflow-hidden lg:aspect-[784/546]">

        <!-- Photo (desktop) -->
        <div class="hidden lg:block absolute inset-y-0 left-[43.4%] right-0">
            <img src="{{ asset('images/landing/auth-hero.png') }}" alt="" class="w-full h-full object-cover">
        </div>

        {{-- Brand panel.

             On desktop this is the left half of the hero. On a phone the full
             version pushed the email field 1040px down — past the fold, behind a
             screenful of marketing — so mobile gets a compact lockup instead and
             the sign-in card lands immediately below it. The headline, blurb and
             the three feature bullets are desktop-only. --}}
        <div class="relative lg:absolute lg:inset-y-0 lg:left-0 lg:w-[43.5%] overflow-hidden">
            <div class="absolute inset-0 opacity-70 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative px-5 py-5 lg:px-12 lg:pt-[4.2rem] lg:pb-10">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center lg:items-start gap-3 lg:gap-4">
                    <img src="{{ brand_asset('mark') }}" alt="" class="w-[48px] h-[48px] lg:w-[70px] lg:h-[70px] object-contain shrink-0">
                    <span class="lg:pt-1">
                        <span class="block text-[13.5px] lg:text-[16px] font-bold tracking-[0.05em] text-white uppercase leading-[1.35]">
                            Artisan Hub 237<span class="lg:hidden"> — {{ $isFr ? 'L\'artisanat camerounais' : 'Cameroonian craftsmanship' }}</span>
                            <span class="hidden lg:inline"><br>{{ $isFr ? 'L\'artisanat' : 'Cameroonian' }}<br>{{ $isFr ? 'camerounais' : 'craftsmanship' }}</span>
                        </span>
                        <span class="mt-1 lg:mt-3.5 block text-[13px] md:text-[11.5px] lg:text-[14.5px] text-sage leading-[1.4] lg:leading-[1.5]">
                            {{ $isFr ? "Notre héritage, notre fierté," : 'Our heritage, our pride,' }}<span class="lg:hidden"> </span><br class="hidden lg:inline">{{ $isFr ? 'notre avenir' : 'our future' }}
                        </span>
                    </span>
                </a>

                <div class="hidden lg:block">
                    <h1 class="mt-14 lg:mt-[4.4rem] font-serif text-[30px] lg:text-[38px] text-white leading-tight lg:whitespace-nowrap">
                        {{ $isFr ? 'Bienvenue de retour !' : 'Welcome back!' }}
                    </h1>
                    <div class="mt-5 w-[58px] h-[4px] bg-goldlt"></div>

                    <p class="mt-8 text-[17.5px] text-sage leading-[1.75] max-w-[420px]">
                        {{ $isFr
                            ? "Connectez-vous pour accéder à la plus grande vitrine de l'artisanat camerounais."
                            : 'Sign in to access the largest showcase of Cameroonian craftsmanship.'
                        }}
                    </p>

                    <div class="mt-12 lg:mt-[3.8rem] space-y-9">
                        @foreach($loginFeatures as [$featIcon, $featTitle, $featDesc])
                        <div class="flex items-start gap-5">
                            <span class="w-[62px] h-[62px] shrink-0 rounded-full border border-gold/60 flex items-center justify-center">
                                <i data-lucide="{{ $featIcon }}" class="w-7 h-7 text-goldlt" style="stroke-width:1.5"></i>
                            </span>
                            <span class="pt-0.5">
                                <span class="block text-[17px] font-semibold text-white">{{ $featTitle }}</span>
                                <span class="mt-1 block text-[15.5px] text-sage leading-[1.5] whitespace-pre-line">{{ $featDesc }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Login card -->
        <div class="relative z-10 mx-3 -mt-3 mb-5 lg:m-0 lg:absolute lg:left-[44%] lg:top-1/2 lg:-translate-y-1/2 lg:w-[39.8%] bg-[#F9F6F1] dark:bg-[#0A0C09] rounded-2xl shadow-[0_18px_50px_rgba(0,0,0,0.35)] px-5 py-6 lg:px-11 lg:py-10">
            <h2 class="font-serif text-[28px] lg:text-[40px] text-[#1D1B16] dark:text-[#F3EFE7] leading-tight">
                {{ $isFr ? 'Se connecter' : 'Sign in' }}
            </h2>
            <p class="mt-1 text-[14.5px] lg:text-[18px] text-[#6F6B60] dark:text-[#868778]">
                {{ $isFr ? 'Accédez à votre compte' : 'Access your account' }}
            </p>

            @if(session('success'))
                <div class="mt-5 ui-alert ui-alert-ok">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-5 ui-alert ui-alert-danger">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form" class="mt-7">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">
                @if(request('next'))
                    <input type="hidden" name="next" value="{{ request('next') }}">
                @endif

                <label class="ui-label" for="email">
                    {{ $isFr ? 'Email ou numéro de téléphone' : 'Email or phone number' }}
                </label>
                {{-- pr-* stays: the trailing icon is overlaid, so the field keeps its clearance. --}}
                <div class="relative">
                    <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}"
                        placeholder="{{ $isFr ? 'Entrez votre email ou téléphone' : 'Enter your email or phone' }}"
                        class="ui-field ui-field--lg pr-11 {{ $errors->has('email') ? 'ui-field--invalid' : '' }}"
                        required autofocus>
                    <i data-lucide="user" class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#8A857A] dark:text-[#868778] pointer-events-none"></i>
                </div>

                <label class="mt-5 ui-label" for="password">
                    {{ $isFr ? 'Mot de passe' : 'Password' }}
                </label>
                <div class="relative">
                    <input id="password" name="password" type="password" autocomplete="current-password"
                        placeholder="{{ $isFr ? 'Entrez votre mot de passe' : 'Enter your password' }}"
                        class="ui-field ui-field--lg pr-11 {{ $errors->has('password') ? 'ui-field--invalid' : '' }}"
                        required>
                    {{-- 44x44 hit area, drawn from right-1 so the 20px eye still lands
                         exactly 16px from the field's edge: 4 + (44-20)/2 = 16. The
                         control looks identical and is now thumb-sized. --}}
                    <button type="button" id="password-toggle" aria-label="{{ $isFr ? 'Afficher le mot de passe' : 'Show password' }}"
                        class="absolute right-1 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center text-[#8A857A] dark:text-[#868778] hover:text-[#26251F] hover:dark:text-[#F3EFE7] transition-colors">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </button>
                </div>

                {{-- Both controls carry a 44px min-height below lg so the row is
                     thumb-sized on a phone; `lg:min-h-0` hands the desktop replica
                     back its drawn height untouched. Type size and colour are
                     unchanged — only the invisible box around them grows. --}}
                <div class="mt-4 lg:mt-5 flex items-center justify-between">
                    <label class="ui-check-row items-center cursor-pointer select-none min-h-[44px] lg:min-h-0">
                        <input type="checkbox" name="remember" value="1" class="ui-check">
                        <span class="text-[13px] font-medium text-[#26251F] dark:text-[#F3EFE7]">{{ $isFr ? 'Se souvenir de moi' : 'Remember me' }}</span>
                    </label>
                    <a href="{{ route('password.request', ['lang' => $lang]) }}" class="inline-flex items-center min-h-[44px] lg:min-h-0 text-[13px] font-semibold text-gold dark:text-[#EDB33A] hover:text-goldlt transition-colors">
                        {{ $isFr ? 'Mot de passe oublié ?' : 'Forgot password?' }}
                    </a>
                </div>

                <button type="submit" class="relative mt-7 ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
                    {{ $isFr ? 'Se connecter' : 'Sign in' }}
                    <i data-lucide="arrow-right" class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5"></i>
                </button>
            </form>

            @if(config('app.demo_login'))
            <div class="mt-7 flex items-center gap-4">
                <span class="flex-1 h-px bg-[#E4E0D8] dark:bg-[#0A0C09]"></span>
                <span class="text-[14.5px] text-muted dark:text-[#868778]">{{ $isFr ? 'comptes de démonstration' : 'demo accounts' }}</span>
                <span class="flex-1 h-px bg-[#E4E0D8] dark:bg-[#0A0C09]"></span>
            </div>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach([
                    ['admin',  'shield-check', $isFr ? 'Admin' : 'Admin', $isFr ? 'Administrateur' : 'Administrator', '#157A43'],
                    ['vendor', 'store',        $isFr ? 'Vendeur' : 'Vendor', $isFr ? 'Compte artisan' : 'Artisan account', '#C97A16'],
                    ['buyer',  'shopping-bag', $isFr ? 'Acheteur' : 'Buyer', $isFr ? 'Compte acheteur' : 'Buyer account', '#3565DE'],
                ] as [$key, $icon, $label, $who, $col])
                <form method="POST" action="{{ url('/demo-login/'.$key) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-[12px] border border-[#E4E0D8] dark:border-[#262B21] bg-white dark:bg-[#12150F] hover:bg-[#FBF9F6] hover:dark:bg-[#0A0C09] px-3 py-3 text-center transition-colors">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5 mx-auto mb-1" style="color:{{ $col }}"></i>
                        <span class="block text-[13.5px] font-bold text-[#26251F] dark:text-[#F3EFE7]">{{ $label }}</span>
                        <span class="block text-[12px] md:text-[10.5px] text-muted dark:text-[#868778] leading-tight mt-0.5">{{ $who }}</span>
                    </button>
                </form>
                @endforeach
            </div>
            <p class="mt-2 text-center text-[13px] md:text-[11px] text-muted dark:text-[#868778]">{{ $isFr ? 'Un clic connecte directement le compte de test correspondant.' : 'One click signs the matching test account straight in.' }}</p>
            @endif

            {{-- Google and Facebook sign-in removed for launch: neither provider
                 is configured, so the buttons only ever said "coming soon". The
                 passkey option below is real and stays. --}}

            <button type="button" id="passkey-login"
                class="mt-4 w-full h-[48px] rounded-[12px] border border-[#E4E0D8] dark:border-[#262B21] bg-white dark:bg-[#12150F] hover:bg-[#FBF9F6] hover:dark:bg-[#0A0C09] flex items-center justify-center gap-3 text-[15px] font-semibold text-[#26251F] dark:text-[#F3EFE7] transition-colors">
                <i data-lucide="fingerprint" class="w-5 h-5 text-gold dark:text-[#EDB33A]"></i>
                {{ $isFr ? 'Se connecter avec une passkey' : 'Sign in with a passkey' }}
            </button>
            <p id="passkey-login-error" class="hidden mt-2 text-[12.5px] text-red-600 dark:text-[#F0555C] text-center"></p>

            <p class="mt-7 text-center text-[14.5px] text-[#26251F] dark:text-[#F3EFE7] lg:whitespace-nowrap">
                {{ $isFr ? 'Vous n\'avez pas encore de compte ?' : 'Don\'t have an account yet?' }}
                <a href="{{ route('inscription', ['lang' => $lang]) }}" class="ml-1 font-semibold text-gold dark:text-[#EDB33A] hover:text-goldlt transition-colors">
                    {{ $isFr ? 'Créer un compte' : 'Create an account' }}
                </a>
            </p>

            <p class="mt-4 text-center">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="inline-flex items-center justify-center gap-1.5 min-h-[44px] lg:min-h-0 text-[12.5px] text-muted dark:text-[#868778] hover:text-[#26251F] hover:dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Continuer sans compte' : 'Browse as guest' }}
                </a>
            </p>
        </div>
    </div>
</section>

@include('auth.partials.replica-bottom')

<script>
    lucide.createIcons();

    // Password visibility toggle
    document.getElementById('password-toggle').addEventListener('click', function () {
        const input = document.getElementById('password');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.innerHTML = '';
        const icon = document.createElement('i');
        icon.setAttribute('data-lucide', show ? 'eye-off' : 'eye');
        icon.className = 'w-5 h-5';
        this.appendChild(icon);
        lucide.createIcons();
    });


    // ── Passkey login ──
    function pkB64ToBuf(obj) {
        if (typeof obj === 'string' && obj.startsWith('=?BINARY?B?')) {
            const bin = atob(obj.substring(11, obj.length - 2));
            const buf = new Uint8Array(bin.length);
            for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
            return buf.buffer;
        }
        if (obj && typeof obj === 'object') {
            for (const k of Object.keys(obj)) obj[k] = pkB64ToBuf(obj[k]);
        }
        return obj;
    }
    function pkBufToB64(buf) { return btoa(String.fromCharCode(...new Uint8Array(buf))); }

    document.getElementById('passkey-login')?.addEventListener('click', async function () {
        const errBox = document.getElementById('passkey-login-error');
        errBox.classList.add('hidden');
        try {
            if (!window.PublicKeyCredential) throw new Error(@json($isFr ? "Votre navigateur ne supporte pas les passkeys." : "Your browser does not support passkeys."));

            const optRes = await fetch(@json(route('webauthn.login.options')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
            });
            if (!optRes.ok) throw new Error('options: ' + optRes.status);
            const args = pkB64ToBuf(await optRes.json());

            const cred = await navigator.credentials.get(args);

            const res = await fetch(@json(route('webauthn.login')), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: cred.id,
                    clientDataJSON: pkBufToB64(cred.response.clientDataJSON),
                    authenticatorData: pkBufToB64(cred.response.authenticatorData),
                    signature: pkBufToB64(cred.response.signature),
                }),
            });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.message || ('login: ' + res.status));
            window.location.href = j.redirect || '{{ route('dashboard.siac') }}';
        } catch (e) {
            errBox.textContent = e.message || e;
            errBox.classList.remove('hidden');
        }
    });
</script>
</body>
</html>
