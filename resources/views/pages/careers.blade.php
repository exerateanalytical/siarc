@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $careerValues = [
        ['heart-handshake', $isFr ? 'Impact réel' : 'Real impact',       $isFr ? 'Valorisez le savoir-faire de milliers d\'artisans camerounais.' : 'Showcase the know-how of thousands of Cameroonian artisans.'],
        ['users',           $isFr ? 'Équipe passionnée' : 'Passionate team', $isFr ? 'Travaillez avec des personnes engagées pour le patrimoine.' : 'Work with people committed to heritage.'],
        ['trending-up',     $isFr ? 'Croissance' : 'Growth',             $isFr ? 'Une plateforme nationale en plein développement.' : 'A fast-growing national platform.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Carrières — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Careers — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#164C28', gold: '#D9A439', goldbt: '#E9A830' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[820px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Carrières' : 'Careers' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'Rejoignez l\'équipe qui met l\'artisanat camerounais à l\'honneur.' : 'Join the team putting Cameroonian craftsmanship in the spotlight.' }}</p>
    </div>
</section>

<main class="max-w-[820px] mx-auto px-5 py-10 pb-20">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach($careerValues as [$cvIcon, $cvTitle, $cvDesc])
        <div class="bg-white border border-[#ECECEA] rounded-2xl p-5 text-center">
            <span class="w-[44px] h-[44px] mx-auto rounded-xl bg-[#E4F1E8] flex items-center justify-center">
                <i data-lucide="{{ $cvIcon }}" class="w-[20px] h-[20px] text-[#14652F]" style="stroke-width:1.7"></i>
            </span>
            <h2 class="mt-3 text-[13.5px] font-bold text-[#1B1B18]">{{ $cvTitle }}</h2>
            <p class="mt-1.5 text-[12px] text-[#55524A] leading-relaxed">{{ $cvDesc }}</p>
        </div>
        @endforeach
    </div>

    <div class="mt-6 bg-white border border-[#ECECEA] rounded-2xl px-6 py-8 text-center">
        <i data-lucide="briefcase" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aucune offre ouverte pour le moment' : 'No open positions at the moment' }}</h2>
        <p class="mt-2 text-[13px] text-[#55524A] max-w-[480px] mx-auto leading-relaxed">
            {{ $isFr
                ? 'Nous publions ici nos offres d\'emploi et de stage. Vous pouvez néanmoins nous envoyer une candidature spontanée : nous étudions chaque profil avec attention.'
                : 'Job and internship offers are published here. You can still send us a spontaneous application: we review every profile carefully.'
            }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-5 inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-6 py-3 rounded-lg transition-colors">
            {{ $isFr ? 'Candidature spontanée' : 'Spontaneous application' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
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
