@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Inscription rapide' : 'Quick signup' }} — Artisan Hub 237</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        /* Nothing may scroll the page sideways on a phone; wide content
           (tables, diagrams) scrolls inside its own container instead. */
        html, body { overflow-x: clip; }
body{font-family:'Poppins',system-ui,sans-serif}</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="min-h-screen bg-[#F3EFE7] dark:bg-[#0A0C09] flex items-center justify-center p-5">
    <main class="w-full max-w-[440px] bg-white dark:bg-[#12150F] rounded-3xl shadow-[0_24px_60px_-24px_rgba(2,48,27,.35)] p-8">
        <a href="{{ url('/galerie') }}" class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#0F4824] dark:text-[#339B56] mb-4"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la galerie' : 'Back to the gallery' }}</a>
        <h1 class="text-[24px] font-extrabold text-[#131313] dark:text-[#F3EFE7] leading-tight">{{ $isFr ? 'Créer un compte en 30 secondes' : 'Create an account in 30 seconds' }}</h1>
        <p class="text-[13px] text-[#6F6B60] dark:text-[#868778] mt-1.5">{{ $isFr ? 'Email et mot de passe suffisent — vous complèterez votre profil plus tard, à votre rythme.' : 'Email and password are enough — complete your profile later, at your own pace.' }}</p>

        @if($errors->any())
        <div class="mt-4 ui-alert ui-alert-danger flex-col">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register.quick.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">
            {{-- One vocabulary, shared with the onboarding wizard via
                 App\Support\AccountTypes. Signup used to offer two kinds here
                 while the wizard offered four, so the same question had two
                 different answers depending on which door you came through. --}}
            <div>
                <label class="ui-label">{{ $isFr ? 'Je suis…' : 'I am…' }}</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    @foreach(\App\Support\AccountTypes::options($isFr) as [$val, $ic, $lbl])
                    <label class="cursor-pointer">
                        <input type="radio" name="account_type" value="{{ $val }}" class="peer sr-only" {{ old('account_type', 'buyer') === $val ? 'checked' : '' }}>
                        <span class="flex items-center gap-2.5 rounded-xl border-2 border-[#E4E0D8] dark:border-[#262B21] peer-checked:border-[#157A43] peer-checked:bg-[#EEF8F1] peer-checked:dark:bg-[#0C3D1D] px-3 py-3 text-[13px] font-semibold text-[#26251F] dark:text-[#F3EFE7]">
                            <i data-lucide="{{ $ic }}" class="w-[18px] h-[18px] shrink-0 text-[#157A43] dark:text-[#339B56]"></i>
                            <span class="leading-tight">{{ $lbl }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="ui-label">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="vous@exemple.cm"
                       class="ui-field ui-field--lg">
            </div>
            <div>
                <label class="ui-label">{{ $isFr ? 'Mot de passe (8 caractères min.)' : 'Password (min. 8 characters)' }}</label>
                <input type="password" name="password" required minlength="8" class="ui-field ui-field--lg">
            </div>
            <button type="submit" class="relative ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
                {{ $isFr ? 'Créer mon compte' : 'Create my account' }}
                <i data-lucide="arrow-right" class="absolute right-5 top-1/2 -translate-y-1/2 w-4.5 h-4.5"></i>
            </button>
        </form>
        <p class="mt-4 text-center text-[12.5px] text-[#6F6B60] dark:text-[#868778]">
            {{ $isFr ? 'Déjà inscrit ?' : 'Already registered?' }} <a href="{{ route('login', ['lang' => $lang]) }}" class="font-bold text-[#157A43] dark:text-[#339B56]">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
            · <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="font-bold text-[#157A43] dark:text-[#339B56]">{{ $isFr ? 'Inscription complète' : 'Full signup' }}</a>
        </p>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
