@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $navLinks = [
        [$isFr ? 'Explorer' : 'Explore',        route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Collections' : 'Collections', route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Artisans' : 'Artisans',       route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Régions' : 'Regions',         route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Secteurs' : 'Sectors',        route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Événements' : 'Events',       route('events.index'), true],
        [$isFr ? 'À propos' : 'About',          route('about'), false],
    ];

    // Filled dark-green circles in the "Suivez-nous" column (design order: FB, IG, LinkedIn, X)
    $socialProfileUrls = array_filter([
        'Facebook'  => config('legal.social.facebook'),
        'Instagram' => config('legal.social.instagram'),
        'LinkedIn'  => config('legal.social.linkedin'),
        'YouTube'   => config('legal.social.youtube'),
        'X'         => config('legal.social.x'),
        'Twitter'   => config('legal.social.x'),
    ]);
    $followIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];

    // Outline circles in the footer (design order: FB, IG, LinkedIn, YouTube, X)
    $footerIcons = $followIcons;
    $footerIcons = [
        'Facebook'  => $followIcons['Facebook'],
        'Instagram' => $followIcons['Instagram'],
        'LinkedIn'  => $followIcons['LinkedIn'],
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => $followIcons['X'],
    ];

    // A card with no line left is dropped rather than shown empty — the phone card
    // disappears entirely until a real number is configured.
    $infoCards = array_values(array_filter([
        ['icon' => 'contact-info-1.png', 'title' => $isFr ? 'Adresse' : 'Address',
         'lines' => $isFr ? ['Artisan Hub 237', 'Douala, Littoral', 'Cameroun']
                          : ['Artisan Hub 237', 'Douala, Littoral', 'Cameroon']],
        ['icon' => 'contact-info-2.png', 'title' => $isFr ? 'Téléphone' : 'Phone',
         'lines' => array_values(array_filter([config('legal.company.phone')]))],
        ['icon' => 'contact-info-3.png', 'title' => 'Email',
         'lines' => array_values(array_filter([config('legal.company.email')]))],
        ['icon' => 'contact-info-4.png', 'title' => $isFr ? "Heures d'ouverture" : 'Opening hours',
         'lines' => $isFr ? ['Lundi - Vendredi : 8h00 - 17h00', 'Samedi : 9h00 - 13h00']
                          : ['Monday - Friday: 8am - 5pm', 'Saturday: 9am - 1pm']],
    ], fn ($c) => $c['lines'] !== []));

    $helpCards = [
        ['contact-help-1.png', $isFr ? 'Partenariats' : 'Partnerships',
         $isFr ? "Collaborations, sponsoring\net projets communs" : "Collaborations, sponsoring\nand joint projects"],
        ['contact-help-2.png', $isFr ? 'Exposer vos produits' : 'Showcase your products',
         $isFr ? "Devenez artisan ou entreprise\nréférencée sur notre plateforme" : "Become an artisan or business\nlisted on our platform"],
        ['contact-help-3.png', $isFr ? 'Assistance' : 'Assistance',
         $isFr ? "Besoin d'aide ? Notre équipe\nvous accompagne" : "Need help? Our team\nis here for you"],
        ['contact-help-4.png', $isFr ? 'Presse et médias' : 'Press and media',
         $isFr ? "Demandes d'interview,\ncommuniqués et ressources" : "Interview requests,\npress releases and resources"],
        ['contact-help-5.png', $isFr ? 'Autres demandes' : 'Other requests',
         $isFr ? "Toute autre demande\nou suggestion" : "Any other request\nor suggestion"],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Contactez la Artisan Hub 237 — une question, une suggestion ou un partenariat ? Notre équipe est à votre écoute.' : 'Contact Artisan Hub 237 — a question, a suggestion or a partnership? Our team is here to listen.' }}">
    <title>{{ $isFr ? 'Nous contacter — Artisan Hub 237' : 'Contact us — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#FAF7F2',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pinefc: '#0A2415',
                        night:  '#131110',
                        gold:   '#E5A82E',
                        goldbt: '#E9AC33',
                        sage:   '#A8B8AC',
                        muted:  '#8A857A',
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
<body class="bg-cream text-[#1D1B16] antialiased">

{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">

<!-- Hero -->
<section class="relative bg-[#0C1C11] overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-[center_right]" style="background-image:url('{{ asset('images/landing/contact-hero.png') }}')"></div>
    <div class="absolute inset-0 bg-black/40 sm:bg-transparent"></div>
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-9 lg:py-11 min-h-[300px] lg:min-h-[364px]">
        <nav class="flex items-center gap-2 text-[13px]" aria-label="Breadcrumb">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#D9A439] hover:text-gold transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/60"></i>
            <span class="text-white/85">Contact</span>
        </nav>
        <h1 class="mt-7 font-serif text-[42px] sm:text-[52px] lg:text-[58px] leading-[1.05] text-white font-medium">
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </h1>
        <div class="mt-5 h-[3px] w-[88px] bg-[#D9991F]"></div>
        <p class="mt-6 text-[15px] lg:text-[16.5px] text-white/90 leading-[1.9]">
            {{ $isFr ? 'Une question, une suggestion ou un partenariat ?' : 'A question, a suggestion or a partnership?' }}<br>
            {{ $isFr ? 'Notre équipe est à votre écoute.' : 'Our team is here to listen.' }}
        </p>
        <img src="{{ asset('images/landing/contact-ornament.png') }}" alt="" class="mt-8 h-[34px] w-auto" aria-hidden="true">
    </div>
</section>

<!-- Contact info strip -->
<section class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 py-11">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.05fr_1fr_0.95fr_1.25fr_1fr] gap-y-10 lg:divide-x lg:divide-[#E9E4DB]">
            @foreach($infoCards as $card)
            <div class="flex flex-col items-center text-center px-3">
                <img src="{{ asset('images/landing/' . $card['icon']) }}" alt="" class="h-[38px] w-auto object-contain">
                <h3 class="mt-4 text-[16.5px] font-semibold text-[#20211D]">{{ $card['title'] }}</h3>
                <div class="mt-3 space-y-1.5 text-[12.5px] text-[#4A4840] leading-relaxed">
                    @foreach($card['lines'] as $line)
                    <p>{{ $line }}</p>
                    @endforeach
                </div>
                <span class="mt-auto pt-5 block"><span class="block w-[46px] h-[2.5px] bg-[#E0980F]"></span></span>
            </div>
            @endforeach
            <!-- Suivez-nous -->
            <div class="flex flex-col items-center text-center px-3">
                <img src="{{ asset('images/landing/contact-info-5.png') }}" alt="" class="h-[38px] w-auto object-contain">
                <h3 class="mt-4 text-[16.5px] font-semibold text-[#20211D]">{{ $isFr ? 'Suivez-nous' : 'Follow us' }}</h3>
                <div class="mt-5 flex items-center gap-3">
                    @foreach(array_intersect_key($followIcons, $socialProfileUrls) as $socialName => $socialPath)
                    <a href="{{ $socialProfileUrls[$socialName] }}" target="_blank" rel="noopener" aria-label="{{ $socialName }}" class="w-[38px] h-[38px] rounded-full bg-[#14472B] hover:bg-leaf flex items-center justify-center text-white transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">{!! $socialPath !!}</svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Form + map -->
<section class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-[0.8fr_1fr] gap-5 items-stretch">
            <!-- Form card -->
            <div class="bg-[#FCF9F5] border border-[#ECE7DF] rounded-xl p-6 sm:p-9">
                <h2 class="font-serif text-[26px] sm:text-[30px] text-[#20211D] font-medium">{{ $isFr ? 'Envoyez-nous un message' : 'Send us a message' }}</h2>
                <div class="mt-3.5 h-[3px] w-[70px] bg-[#E0980F]"></div>

                @if(session('success'))
                <div class="mt-6 bg-[#EAF3EC] border border-[#BFDCC7] text-leaf text-[13px] rounded-lg px-4 py-3">
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="mt-7">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact-name" class="ui-label">{{ $isFr ? 'Votre nom' : 'Your name' }}<span class="ui-req">*</span></label>
                            <input id="contact-name" name="name" type="text" required value="{{ old('name') }}" class="ui-field ui-field--lg @error('name') ui-field--invalid @enderror">
                            @error('name')<p class="ui-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contact-email" class="ui-label">{{ $isFr ? 'Votre email' : 'Your email' }}<span class="ui-req">*</span></label>
                            <input id="contact-email" name="email" type="email" required value="{{ old('email') }}" class="ui-field ui-field--lg @error('email') ui-field--invalid @enderror">
                            @error('email')<p class="ui-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="contact-subject" class="ui-label">{{ $isFr ? 'Sujet' : 'Subject' }}<span class="ui-req">*</span></label>
                        <input id="contact-subject" name="subject" type="text" required value="{{ old('subject') }}" class="ui-field ui-field--lg @error('subject') ui-field--invalid @enderror">
                        @error('subject')<p class="ui-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4">
                        <label for="contact-message" class="ui-label">{{ $isFr ? 'Votre message' : 'Your message' }}<span class="ui-req">*</span></label>
                        <textarea id="contact-message" name="message" required rows="7" class="ui-field ui-textarea @error('message') ui-field--invalid @enderror">{{ old('message') }}</textarea>
                        @error('message')<p class="ui-error">{{ $message }}</p>@enderror
                    </div>
                    <label class="ui-check-row mt-4 cursor-pointer select-none">
                        <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }} class="ui-check mt-0.5">
                        <span>{{ $isFr ? 'J\'accepte d\'être contacté(e) par l\'équipe d\'Artisan Hub 237.' : 'I agree to be contacted by the Artisan Hub 237 team.' }}</span>
                    </label>
                    @error('consent')<p class="ui-error">{{ $message }}</p>@enderror
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg mt-6">
                        <i data-lucide="send" class="w-[17px] h-[17px]"></i>
                        {{ $isFr ? 'Envoyer le message' : 'Send the message' }}
                    </button>
                </form>
            </div>

            <!-- Location card -->
            <div class="relative rounded-xl overflow-hidden border border-[#ECE7DF] min-h-[320px] bg-[#F6F1EB] flex flex-col items-center justify-center text-center px-6 py-10"
                role="img" aria-label="{{ $isFr ? 'Localisation — Artisan Hub 237, Douala, Cameroun' : 'Location — Artisan Hub 237, Douala, Cameroon' }}">
                <i data-lucide="map-pin" class="w-10 h-10 text-[#123D27]"></i>
                <p class="mt-4 text-[15px] font-semibold text-[#1D1B16]">Artisan Hub 237</p>
                <p class="mt-1 text-[13px] text-[#6B665C]">{{ $isFr ? 'Douala, Littoral, Cameroun' : 'Douala, Littoral, Cameroon' }}</p>
                <a href="https://www.google.com/maps/search/?api=1&query=Douala%2C+Cameroun"
                    target="_blank" rel="noopener"
                    aria-label="{{ $isFr ? 'Itinéraire vers Artisan Hub 237, Douala' : 'Directions to Artisan Hub 237, Douala' }}"
                    class="mt-6 inline-flex items-center gap-2 border border-[#123D27] text-[#123D27] hover:bg-[#123D27]/5 text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
                    <i data-lucide="map" class="w-4 h-4"></i>
                    {{ $isFr ? 'Voir sur la carte' : 'View on the map' }}
                </a>
            </div>
        </div>
    </div>
</section>

<!-- How can we help -->
<section class="relative bg-[#F6F1EB] overflow-hidden">
    <img src="{{ asset('images/landing/contact-help-left.png') }}" alt="" class="absolute left-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/contact-help-right.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-12">
        <h2 class="font-serif text-[27px] sm:text-[31px] text-[#20211D] font-medium text-center">{{ $isFr ? 'Comment pouvons-nous vous aider ?' : 'How can we help you?' }}</h2>
        <div class="mt-4 h-[2.5px] w-[62px] bg-[#D9991F] mx-auto"></div>
        <div class="mt-11 grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-y-9 lg:divide-x lg:divide-[#E5DFD3]">
            @foreach($helpCards as [$helpIcon, $helpTitle, $helpSub])
            <div class="flex flex-col items-center text-center px-4">
                <img src="{{ asset('images/landing/' . $helpIcon) }}" alt="" class="h-[46px] w-auto object-contain">
                <h3 class="mt-5 text-[15.5px] font-semibold text-[#20211D]">{{ $helpTitle }}</h3>
                <p class="mt-2.5 text-[12.5px] text-[#5D5A51] leading-relaxed whitespace-pre-line">{{ $helpSub }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA band -->
<section class="relative bg-pinefc overflow-hidden">
    <img src="{{ asset('images/landing/contact-cta-left.png') }}" alt="" class="absolute left-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/contact-cta-right.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-9 text-center">
        <p class="font-serif text-[22px] sm:text-[26px] text-[#F4F1E8] font-medium">
            {{ $isFr ? 'Restons connectés pour valoriser ensemble l\'artisanat camerounais.' : 'Let\'s stay connected to promote Cameroonian craftsmanship together.' }}
        </p>
        <a href="{{ route('industries.index', ['lang' => $lang]) }}"
            class="mt-6 inline-flex items-center gap-2.5 bg-goldbt hover:bg-[#D99B22] text-[#3A2E08] text-[13.5px] font-semibold px-6 h-[46px] rounded-lg transition-colors">
            {{ $isFr ? 'Découvrir nos collections' : 'Discover our collections' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

<!-- Kente strip -->
<img src="{{ asset('images/landing/contact-kente.png') }}" alt="" class="block w-full h-[30px] object-cover" aria-hidden="true">

</main>

<!-- Footer -->
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $bottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home', 'active' => false],
            ['href' => route('businesses.index', ['lang' => $lang]), 'icon' => 'compass', 'label' => $isFr ? 'Explorer' : 'Explore', 'active' => false],
            ['href' => $siacUser ? route('saved.index') : '/login', 'icon' => 'bookmark', 'label' => $isFr ? 'Favoris' : 'Saved', 'active' => false],
            ['href' => $siacUser ? route('messages.inbox') : '/login', 'icon' => 'message-circle', 'label' => 'Messages', 'active' => false],
            ['href' => $siacUser ? route('dashboard.siac') : route('login'), 'icon' => 'user', 'label' => $isFr ? 'Profil' : 'Profile', 'active' => false],
        ];
    @endphp
    @foreach($bottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-leaf' : 'text-gray-400' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5"></i>
        <span class="text-[10px] font-medium">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
