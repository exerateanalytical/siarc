@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Mon espace SIARC' : 'My SIARC space' }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}</style>
</head>
<body class="min-h-screen bg-[#EFEDE6] flex items-center justify-center p-5">
    <main class="w-full max-w-[440px] bg-white rounded-3xl shadow-[0_24px_60px_-24px_rgba(6,43,21,.4)] overflow-hidden">
        <div class="px-7 py-6 text-center text-white" style="background:linear-gradient(160deg,#0B3A1E 0%,#052A15 100%)">
            <b class="text-[20px]">SIARC <i class="not-italic text-[#E6B201]">2026</i></b>
            <p class="text-[10px] font-semibold tracking-[0.14em] text-white/75 mt-0.5">{{ $isFr ? 'MON ESPACE VISITEUR' : 'MY VISITOR SPACE' }}</p>
        </div>
        <div class="p-7">
            <h1 class="text-[19px] font-extrabold text-[#131313]">{{ $isFr ? 'Accéder à mon espace' : 'Access my space' }}</h1>
            <p class="text-[12.5px] text-[#6F6B60] mt-1">{{ $isFr ? "Entrez l'email et le numéro de badge reçus lors de votre inscription au salon." : 'Enter the email and badge number from your salon registration.' }}</p>

            @if(session('siarc_access_ko'))
            <div class="mt-4 rounded-xl border border-[#F1C3C6] bg-[#FDECED] px-4 py-3">
                <p class="text-[12.5px] text-[#8A1015]">{{ $isFr ? 'Aucune inscription trouvée avec cet email et ce badge. Vérifiez vos informations.' : 'No registration found for this email and badge. Please check your details.' }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('siarc.visitor.access') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="vous@exemple.cm"
                           class="w-full h-[48px] text-[14px] rounded-xl border border-[#E4E0D8] px-4 focus:outline-none focus:border-[#157A43] text-[#26251F]">
                </div>
                <div>
                    <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">{{ $isFr ? 'Numéro de badge' : 'Badge number' }}</label>
                    <input type="text" name="badge_code" required value="{{ old('badge_code') }}" placeholder="SIARC-VIS-0001"
                           class="w-full h-[48px] text-[14px] rounded-xl border border-[#E4E0D8] px-4 focus:outline-none focus:border-[#157A43] text-[#26251F] uppercase">
                </div>
                <button type="submit" class="relative w-full h-[50px] bg-[#157A43] hover:bg-[#0F5F33] text-white text-[15px] font-semibold rounded-xl transition-colors">
                    {{ $isFr ? 'Ouvrir mon espace' : 'Open my space' }}
                    <i data-lucide="arrow-right" class="absolute right-5 top-1/2 -translate-y-1/2 w-4.5 h-4.5"></i>
                </button>
            </form>
            <p class="mt-4 text-center text-[12.5px] text-[#6F6B60]">
                {{ $isFr ? 'Pas encore inscrit ?' : 'Not registered yet?' }}
                <a href="{{ route('siarc.register', ['lang' => $lang]) }}" class="font-bold text-[#157A43]">{{ $isFr ? "S'inscrire au SIARC 2026" : 'Register for SIARC 2026' }}</a>
            </p>
        </div>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
