@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $siacUser = session('siac_user');
    $isWorkshop = !empty($workshop);
    $done = session('siarc_registered');
    $err = session('siarc_error');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isWorkshop ? ($isFr ? 'Inscription atelier' : 'Workshop registration') : ($isFr ? 'Inscription visiteur' : 'Visitor registration') }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config={theme:{extend:{colors:{leaf:'#164C28',gold:'#C9942E'},fontFamily:{sans:['Poppins','system-ui','sans-serif'],serif:['"Playfair Display"','Georgia','serif']}}}}</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FEFDFC] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<div class="max-w-[640px] mx-auto px-4 sm:px-6 pt-8 pb-16">
    <nav class="flex items-center gap-2 text-[13px] mb-4">
        <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-[#166534] hover:underline">SIARC 2026</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
        <span class="text-[#6F6B60]">{{ $isWorkshop ? ($isFr ? 'Inscription atelier' : 'Workshop registration') : ($isFr ? 'Inscription' : 'Registration') }}</span>
    </nav>

    <h1 class="font-serif text-[30px] font-bold text-[#1D1B16] leading-tight">
        {{ $isWorkshop ? ($isFr ? 'Inscription à l\'atelier' : 'Workshop registration') : ($isFr ? 'Inscription Visiteur' : 'Visitor Registration') }}
    </h1>
    @if($isWorkshop)<p class="mt-2 text-[15px] font-semibold text-[#157A43]">{{ $workshop->title_fr }}</p>@endif
    <div class="mt-2.5 h-[3.5px] w-[100px] bg-gradient-to-r from-[#D9991F] via-[#E9C989] to-transparent rounded-full mb-6"></div>

    @if($done)
    <div class="flex items-start gap-2.5 bg-[#E9F6EE] border border-[#BFE3CD] rounded-xl px-4 py-3.5 mb-6 text-[13px] text-[#0F5B30]">
        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
        <span>{{ $isFr ? 'Inscription enregistrée. Votre badge sera disponible à l\'accueil du salon.' : 'Registration recorded. Your badge will be available at the salon reception.' }}</span>
    </div>
    @endif

    @if($err)
    <div class="flex items-start gap-2.5 bg-[#FDECEC] border border-[#F3C7C7] rounded-xl px-4 py-3.5 mb-6 text-[13px] text-[#B4231E]">
        <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
        <span>{{ $isFr ? 'Les inscriptions ne sont pas encore ouvertes. Réessayez plus tard.' : 'Registration is not open yet. Please try again later.' }}</span>
    </div>
    @endif

    <form method="POST" action="{{ $isWorkshop ? route('siarc.workshop.register.store', ['id' => $workshop->id]) : route('siarc.register.store') }}" class="bg-white border border-[#ECE9E2] rounded-2xl p-6 space-y-4">
        @csrf
        <input type="hidden" name="lang" value="{{ $lang }}">

        @if($isWorkshop)
        <div>
            <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Nom complet' : 'Full name' }} *</label>
            <input name="name" required class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
        </div>
        <div>
            <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">Email</label>
            <input name="email" type="email" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Prénom' : 'First name' }} *</label>
                <input name="first_name" required class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Nom' : 'Last name' }}</label>
                <input name="last_name" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">Email</label>
                <input name="email" type="email" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
            </div>
            <div>
                <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                <input name="phone" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
            </div>
        </div>
        <div>
            <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Organisation' : 'Organization' }}</label>
            <input name="organization" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3.5 text-[13px] focus:outline-none focus:border-gold">
        </div>
        <div>
            <label class="block text-[12.5px] font-semibold text-[#1D1B16] mb-1.5">{{ $isFr ? 'Profil' : 'Profile' }}</label>
            <select name="type" class="w-full h-[42px] bg-white border border-[#E3E3E1] rounded-lg px-3 text-[13px] focus:outline-none focus:border-gold">
                <option value="visitor">{{ $isFr ? 'Visiteur' : 'Visitor' }}</option>
                <option value="buyer">{{ $isFr ? 'Acheteur professionnel' : 'Professional buyer' }}</option>
                <option value="press">{{ $isFr ? 'Presse' : 'Press' }}</option>
            </select>
        </div>
        @endif

        @error('first_name')<p class="text-[12px] text-[#DC2626]">{{ $message }}</p>@enderror
        @error('name')<p class="text-[12px] text-[#DC2626]">{{ $message }}</p>@enderror

        <button type="submit" class="w-full h-[46px] bg-[#02331C] hover:bg-leaf text-white text-[13.5px] font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors">
            <i data-lucide="ticket" class="w-4 h-4"></i>{{ $isFr ? 'Valider mon inscription' : 'Confirm registration' }}
        </button>
    </form>
</div>

@include('pages.partials.directory-footer')
<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn'); const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
