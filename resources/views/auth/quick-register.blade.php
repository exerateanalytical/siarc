@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Inscription rapide' : 'Quick signup' }} — Galerie Virtuelle</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}</style>
</head>
<body class="min-h-screen bg-[#F3EFE7] flex items-center justify-center p-5">
    <main class="w-full max-w-[440px] bg-white rounded-3xl shadow-[0_24px_60px_-24px_rgba(2,48,27,.35)] p-8">
        <a href="{{ url('/galerie') }}" class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#0F4824] mb-4"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la galerie' : 'Back to the gallery' }}</a>
        <h1 class="text-[24px] font-extrabold text-[#131313] leading-tight">{{ $isFr ? 'Créer un compte en 30 secondes' : 'Create an account in 30 seconds' }}</h1>
        <p class="text-[13px] text-[#6F6B60] mt-1.5">{{ $isFr ? 'Email et mot de passe suffisent — vous complèterez votre profil plus tard, à votre rythme.' : 'Email and password are enough — complete your profile later, at your own pace.' }}</p>

        @if($errors->any())
        <div class="mt-4 rounded-xl border border-[#F1C3C6] bg-[#FDECED] px-4 py-3">
            @foreach($errors->all() as $error)<p class="text-[12.5px] text-[#8A1015]">{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register.quick.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div>
                <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">{{ $isFr ? 'Je suis…' : 'I am…' }}</label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([['buyer','shopping-bag',$isFr ? 'Acheteur / Visiteur' : 'Buyer / Visitor'],['artisan','store',$isFr ? 'Artisan / Vendeur' : 'Artisan / Vendor']] as [$val,$ic,$lbl])
                    <label class="cursor-pointer">
                        <input type="radio" name="account_type" value="{{ $val }}" class="peer sr-only" {{ old('account_type', 'buyer') === $val ? 'checked' : '' }}>
                        <span class="flex items-center justify-center gap-2 rounded-xl border-2 border-[#E4E0D8] peer-checked:border-[#157A43] peer-checked:bg-[#EEF8F1] px-3 py-3 text-[13px] font-semibold text-[#26251F]">
                            <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 text-[#157A43]"></i>{{ $lbl }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="vous@exemple.cm"
                       class="w-full h-[50px] text-[14px] rounded-xl border border-[#E4E0D8] px-4 focus:outline-none focus:border-[#157A43] text-[#26251F]">
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">{{ $isFr ? 'Mot de passe (8 caractères min.)' : 'Password (min. 8 characters)' }}</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full h-[50px] text-[14px] rounded-xl border border-[#E4E0D8] px-4 focus:outline-none focus:border-[#157A43] text-[#26251F]">
            </div>
            <button type="submit" class="relative w-full h-[52px] bg-[#02301B] hover:bg-[#157A43] text-white text-[15.5px] font-semibold rounded-xl transition-colors">
                {{ $isFr ? 'Créer mon compte' : 'Create my account' }}
                <i data-lucide="arrow-right" class="absolute right-5 top-1/2 -translate-y-1/2 w-4.5 h-4.5"></i>
            </button>
        </form>
        <p class="mt-4 text-center text-[12.5px] text-[#6F6B60]">
            {{ $isFr ? 'Déjà inscrit ?' : 'Already registered?' }} <a href="/login?lang={{ $lang }}" class="font-bold text-[#157A43]">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
            · <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="font-bold text-[#157A43]">{{ $isFr ? 'Inscription complète' : 'Full signup' }}</a>
        </p>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
