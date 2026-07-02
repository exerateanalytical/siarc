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

    $demoAccounts = [
        ['email' => 'admin@artisanatcameroun.cm', 'password' => 'Admin@SIAC2026', 'label' => ['fr' => 'Admin', 'en' => 'Admin']],
        ['email' => 'entrepreneur@siac2026.cm',   'password' => 'Demo@SIAC2026',  'label' => ['fr' => 'Entrepreneur', 'en' => 'Business']],
        ['email' => 'acheteur@siac2026.cm',       'password' => 'Demo@SIAC2026',  'label' => ['fr' => 'Acheteur', 'en' => 'Buyer']],
        ['email' => 'regional@siac2026.cm',       'password' => 'Demo@SIAC2026',  'label' => ['fr' => 'Rép. régional', 'en' => 'Regional Rep']],
        ['email' => 'ministry@siac2026.cm',       'password' => 'Demo@SIAC2026',  'label' => ['fr' => 'Ministère', 'en' => 'Ministry']],
        ['email' => 'technique@siac2026.cm',      'password' => 'Demo@SIAC2026',  'label' => ['fr' => 'Technique', 'en' => 'Technical']],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Connectez-vous à la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.' : 'Sign in to the National Virtual Gallery of Cameroonian Crafts.' }}">
    <title>{{ $isFr ? 'Se connecter — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Sign in — National Virtual Gallery of Cameroonian Crafts' }}</title>

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

<!-- Hero: brand panel + login card + photo -->
<section class="pt-4 px-4 pb-2">
    <div class="relative max-w-[1280px] mx-auto bg-panel rounded-xl overflow-hidden lg:aspect-[784/546]">

        <!-- Photo (desktop) -->
        <div class="hidden lg:block absolute inset-y-0 left-[43.4%] right-0">
            <img src="{{ asset('images/landing/auth-hero.png') }}" alt="" class="w-full h-full object-cover">
        </div>

        <!-- Brand panel -->
        <div class="relative lg:absolute lg:inset-y-0 lg:left-0 lg:w-[43.5%] overflow-hidden">
            <div class="absolute inset-0 opacity-70 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative px-8 py-9 lg:px-12 lg:pt-[4.2rem] lg:pb-10">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-start gap-4">
                    <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[64px] h-[70px] object-contain shrink-0">
                    <span class="pt-1">
                        <span class="block text-[16px] font-bold tracking-[0.05em] text-white uppercase leading-[1.35]">
                            {{ $isFr ? 'Galerie Virtuelle' : 'National Virtual' }}<br>
                            {{ $isFr ? 'Nationale de l\'Artisanat' : 'Gallery of Crafts' }}<br>
                            {{ $isFr ? 'du Cameroun' : 'of Cameroon' }}
                        </span>
                        <span class="mt-3.5 block text-[14.5px] text-sage leading-[1.5]">
                            {{ $isFr ? "Notre héritage, notre fierté," : 'Our heritage, our pride,' }}<br>
                            {{ $isFr ? 'notre avenir' : 'our future' }}
                        </span>
                    </span>
                </a>

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

        <!-- Login card -->
        <div class="relative z-10 mx-4 mb-6 lg:m-0 lg:absolute lg:left-[44%] lg:top-1/2 lg:-translate-y-1/2 lg:w-[39.8%] bg-[#F9F6F1] rounded-2xl shadow-[0_18px_50px_rgba(0,0,0,0.35)] px-7 py-8 lg:px-11 lg:py-10">
            <h2 class="font-serif text-[36px] lg:text-[40px] text-[#1D1B16] leading-tight">
                {{ $isFr ? 'Se connecter' : 'Sign in' }}
            </h2>
            <p class="mt-1 text-[18px] text-[#6F6B60]">
                {{ $isFr ? 'Accédez à votre compte' : 'Access your account' }}
            </p>

            @if(session('success'))
                <div class="mt-5 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-[14px] text-green-800">
                    <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mt-5 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-[14px] text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" id="login-form" class="mt-7">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">
                @if(request('next'))
                    <input type="hidden" name="next" value="{{ request('next') }}">
                @endif

                <label class="block text-[16.5px] font-medium text-[#26251F]" for="email">
                    {{ $isFr ? 'Email ou numéro de téléphone' : 'Email or phone number' }}
                </label>
                <div class="relative mt-2.5">
                    <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}"
                        placeholder="{{ $isFr ? 'Entrez votre email ou téléphone' : 'Enter your email or phone' }}"
                        class="w-full h-[52px] rounded-[12px] border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-[#E4E0D8] bg-[#FBF9F6]' }} px-5 pr-12 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition"
                        required autofocus>
                    <i data-lucide="user" class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-[#8A857A] pointer-events-none"></i>
                </div>

                <label class="mt-6 block text-[16.5px] font-medium text-[#26251F]" for="password">
                    {{ $isFr ? 'Mot de passe' : 'Password' }}
                </label>
                <div class="relative mt-2.5">
                    <input id="password" name="password" type="password" autocomplete="current-password"
                        placeholder="{{ $isFr ? 'Entrez votre mot de passe' : 'Enter your password' }}"
                        class="w-full h-[52px] rounded-[12px] border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-[#E4E0D8] bg-[#FBF9F6]' }} px-5 pr-12 text-[16px] placeholder-[#A09B8F] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/50 transition"
                        required>
                    <button type="button" id="password-toggle" aria-label="{{ $isFr ? 'Afficher le mot de passe' : 'Show password' }}"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8A857A] hover:text-[#26251F] transition-colors">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mt-5 flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="w-[18px] h-[18px] rounded border-[#C9C4BA] text-deep accent-[#0A331C]">
                        <span class="text-[15.5px] font-medium text-[#26251F]">{{ $isFr ? 'Se souvenir de moi' : 'Remember me' }}</span>
                    </label>
                    <a href="{{ route('password.request', ['lang' => $lang]) }}" class="text-[15.5px] font-semibold text-gold hover:text-goldlt transition-colors">
                        {{ $isFr ? 'Mot de passe oublié ?' : 'Forgot password?' }}
                    </a>
                </div>

                <button type="submit"
                    class="relative mt-8 w-full h-[54px] bg-deep hover:bg-leaf text-white text-[17px] font-semibold rounded-[12px] transition-colors">
                    {{ $isFr ? 'Se connecter' : 'Sign in' }}
                    <i data-lucide="arrow-right" class="absolute right-6 top-1/2 -translate-y-1/2 w-5 h-5"></i>
                </button>
            </form>

            <div class="mt-7 flex items-center gap-4">
                <span class="flex-1 h-px bg-[#E4E0D8]"></span>
                <span class="text-[14.5px] text-muted">{{ $isFr ? 'ou continuer avec' : 'or continue with' }}</span>
                <span class="flex-1 h-px bg-[#E4E0D8]"></span>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <button type="button" data-soon
                    class="h-[54px] rounded-[12px] border border-[#E4E0D8] bg-white hover:bg-[#FBF9F6] flex items-center justify-center gap-3 text-[16.5px] font-semibold text-[#26251F] transition-colors">
                    <svg viewBox="0 0 24 24" class="w-[22px] h-[22px]" aria-hidden="true">
                        <path fill="#4285F4" d="M23.5 12.27c0-.85-.08-1.66-.22-2.45H12v4.64h6.45a5.52 5.52 0 0 1-2.39 3.62v3h3.87c2.26-2.09 3.57-5.16 3.57-8.81z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.93-2.91l-3.87-3c-1.07.72-2.44 1.14-4.06 1.14-3.12 0-5.77-2.11-6.71-4.95H1.29v3.1A12 12 0 0 0 12 24z"/>
                        <path fill="#FBBC05" d="M5.29 14.28a7.2 7.2 0 0 1 0-4.56v-3.1H1.29a12 12 0 0 0 0 10.76l4-3.1z"/>
                        <path fill="#EA4335" d="M12 4.77c1.76 0 3.34.6 4.58 1.79l3.44-3.44A11.98 11.98 0 0 0 12 0 12 12 0 0 0 1.29 6.62l4 3.1C6.23 6.88 8.88 4.77 12 4.77z"/>
                    </svg>
                    Google
                </button>
                <button type="button" data-soon
                    class="h-[54px] rounded-[12px] border border-[#E4E0D8] bg-white hover:bg-[#FBF9F6] flex items-center justify-center gap-3 text-[16.5px] font-semibold text-[#26251F] transition-colors">
                    <svg viewBox="0 0 24 24" class="w-[22px] h-[22px]" aria-hidden="true">
                        <circle cx="12" cy="12" r="12" fill="#1877F2"/>
                        <path fill="#fff" d="M16.4 15.4l.53-3.4h-3.28V9.75c0-.93.46-1.84 1.93-1.84h1.49V5.01S15.71 4.8 14.42 4.8c-2.7 0-4.46 1.62-4.46 4.56V12H7v3.4h2.96v8.23a11.9 11.9 0 0 0 3.69 0V15.4h2.75z"/>
                    </svg>
                    Facebook
                </button>
            </div>
            <p id="social-soon" class="hidden mt-2 text-[12.5px] text-muted text-center">
                {{ $isFr ? 'Bientôt disponible.' : 'Coming soon.' }}
            </p>

            <button type="button" id="passkey-login"
                class="mt-4 w-full h-[48px] rounded-[12px] border border-[#E4E0D8] bg-white hover:bg-[#FBF9F6] flex items-center justify-center gap-3 text-[15px] font-semibold text-[#26251F] transition-colors">
                <i data-lucide="fingerprint" class="w-5 h-5 text-gold"></i>
                {{ $isFr ? 'Se connecter avec une passkey' : 'Sign in with a passkey' }}
            </button>
            <p id="passkey-login-error" class="hidden mt-2 text-[12.5px] text-red-600 text-center"></p>

            <p class="mt-7 text-center text-[14.5px] text-[#26251F] lg:whitespace-nowrap">
                {{ $isFr ? 'Vous n\'avez pas encore de compte ?' : 'Don\'t have an account yet?' }}
                <a href="/inscription?lang={{ $lang }}" class="ml-1 font-semibold text-gold hover:text-goldlt transition-colors">
                    {{ $isFr ? 'Créer un compte' : 'Create an account' }}
                </a>
            </p>

            <details class="mt-5 text-center">
                <summary class="text-[12.5px] text-muted cursor-pointer hover:text-[#26251F] transition-colors list-none">
                    {{ $isFr ? 'Comptes de démonstration' : 'Demo accounts' }}
                </summary>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach($demoAccounts as $acc)
                    <button type="button" onclick="demoLogin('{{ $acc['email'] }}','{{ $acc['password'] }}')"
                        class="py-2 px-1 rounded-lg border border-[#E4E0D8] bg-white hover:border-gold hover:bg-[#FBF9F6] text-[11.5px] font-semibold text-[#26251F] transition-colors">
                        {{ $acc['label'][$lang] }}
                    </button>
                    @endforeach
                </div>
                <p class="mt-2 text-[11px] text-muted">
                    {{ $isFr ? 'Un clic pour se connecter.' : 'One click to log in.' }}
                </p>
            </details>

            <p class="mt-4 text-center">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] text-muted hover:text-[#26251F] transition-colors">
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

    function demoLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        document.getElementById('login-form').submit();
    }

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

    // Social providers are not wired up yet
    document.querySelectorAll('[data-soon]').forEach(btn => {
        btn.addEventListener('click', () => document.getElementById('social-soon').classList.remove('hidden'));
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
            window.location.href = j.redirect || '/tableau-de-bord';
        } catch (e) {
            errBox.textContent = e.message || e;
            errBox.classList.remove('hidden');
        }
    });
</script>
</body>
</html>
