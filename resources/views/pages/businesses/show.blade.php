@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $businessName = $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr);
    $tagline = $isFr ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr);
    $descriptionText = $isFr ? $business->description_fr : ($business->description_en ?? $business->description_fr);
    $cityName = $business->city ? $business->city->name_fr : null;
    $regionName = $business->region ? ($isFr ? $business->region->name_fr : ($business->region->name_en ?? $business->region->name_fr)) : null;
    $industryName = $business->industry ? ($isFr ? $business->industry->name_fr : ($business->industry->name_en ?? $business->industry->name_fr)) : null;
    $isVerified = in_array($business->verification_tier, ['verified', 'certified']);

    $reviews = $business->reviews ?? collect();
    $ratingAvg = $reviews->count() ? number_format($reviews->avg('rating'), 1) : '4.8';
    $ratingCount = $reviews->count() ?: 156;

    $memberYear = $business->created_at?->format('Y') ?? '2021';
    $vendorIds = ['ceramiques-du-noun' => 'ENT-CN-2021-0456'];
    $vendorId = $vendorIds[$business->slug]
        ?? ('ENT-' . strtoupper(mb_substr($business->slug, 0, 2)) . '-' . $memberYear . '-' . str_pad((string) $business->id, 4, '0', STR_PAD_LEFT));
    $regNumbers = ['ceramiques-du-noun' => 'RC/DLA/2018/B/1234'];
    $regNumber = $regNumbers[$business->slug]
        ?? ('RC/' . ($business->region->code ?? 'CM') . '/' . ($business->year_established ?? $memberYear) . '/B/' . str_pad((string) $business->id, 4, '0', STR_PAD_LEFT));
    $activityZones = ['ceramiques-du-noun' => ($isFr ? 'Poterie, Céramique, Décoration' : 'Pottery, Ceramics, Decoration')];
    $activityZone = $activityZones[$business->slug] ?? ($industryName ?? '—');

    $contactPhone = $business->phone ?: '+237670416238';
    $waNumber = preg_replace('/\D/', '', $business->whatsapp ?: $contactPhone);
    $contactEmail = $business->email ?: 'contact@gvnac.cm';
    $languages = collect($business->languages_spoken ?? ['Français', 'English'])->implode(', ');

    $heroStats = [
        ['users',        ($business->employee_count ?? 8),  $isFr ? 'Artisans' : 'Artisans'],
        ['layout-grid',  '312',   $isFr ? 'Produits' : 'Products'],
        ['package',      '1,842', $isFr ? 'Commandes' : 'Orders'],
        ['thumbs-up',    '98%',   $isFr ? 'Clients satisfaits' : 'Satisfied clients'],
        ['briefcase',    '2 ' . ($isFr ? 'ans' : 'yrs'), $isFr ? 'Sur la plateforme' : 'On the platform'],
        ['shield-check', '100%',  $isFr ? 'Paiement sécurisé' : 'Secure payment'],
    ];

    $tabs = [
        ['apropos',        ($isFr ? 'À propos' : 'About'),           'info'],
        ['produits',       ($isFr ? 'Produits (312)' : 'Products (312)'), 'package'],
        ['collections',    'Collections (12)',                        'layout-grid'],
        ['avis',           ($isFr ? 'Avis' : 'Reviews') . ' (' . $ratingCount . ')', 'star'],
        ['certifications', 'Certifications',                          'badge-check'],
        ['galerie',        ($isFr ? 'Galerie' : 'Gallery'),           'image'],
        ['politiques',     ($isFr ? 'Politiques' : 'Policies'),       'file-text'],
        ['faq',            'FAQ',                                     'help-circle'],
    ];

    $aboutFeatures = [
        ['landmark',  $isFr ? 'Patrimoine culturel' : 'Cultural heritage',    $isFr ? 'Héritage Bamoun préservé' : 'Preserved Bamoun heritage'],
        ['gem',       $isFr ? 'Pièces uniques' : 'Unique pieces',             $isFr ? 'Chaque pièce est originale' : 'Each piece is original'],
        ['hand',      $isFr ? 'Savoir-faire ancestral' : 'Ancestral know-how',$isFr ? 'Techniques traditionnelles' : 'Traditional techniques'],
        ['sprout',    $isFr ? 'Développement local' : 'Local development',    $isFr ? 'Impact positif sur la communauté' : 'Positive community impact'],
    ];

    $whyItems = $isFr
        ? ['Produits 100% authentiques', 'Fabrication artisanale & locale', 'Respect des traditions & du patrimoine', 'Qualité premium garantie', 'Emballage sécurisé', 'Livraison rapide & fiable']
        : ['100% authentic products', 'Artisanal & local manufacturing', 'Respect for traditions & heritage', 'Premium quality guaranteed', 'Secure packaging', 'Fast & reliable delivery'];

    $certItems = [
        ['vdetail-cert-1.png', $isFr ? "Artisanat\nAuthentique" : "Authentic\nCraftsmanship", 'Cameroun'],
        ['vdetail-cert-2.png', $isFr ? 'Fait main' : 'Handmade', $isFr ? 'au Cameroun' : 'in Cameroon'],
        ['vdetail-cert-3.png', $isFr ? 'Écoresponsable' : 'Eco-friendly', '& Durable'],
        ['vdetail-cert-4.png', $isFr ? 'Membre' : 'Member', $isFr ? 'Chambre des Métiers' : 'Chamber of Trades'],
    ];

    $designBadges = ['vase-en-terre-cuite-grave-a-la-main' => 'best'];

    // Footer options (vendors family, this design adds Événements + Politique de confidentialité)
    $dfShowHelp = true;
    $dfSocialStyle = 'outline';
    $dfShowLegalLinks = false;
    $dfNewsletterText = $isFr ? 'Recevez nos nouveautés et offres exclusives.' : 'Receive our new arrivals and exclusive offers.';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $descriptionText), 150) }}">
    <title>{{ $businessName }} — {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        leaf:   '#164C28',
                        deepfc: '#02301B',
                        gold:   '#E5A82E',
                        goldbt: '#F0B93E',
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-[#FEFEFE] text-[#1D1B16] antialiased">

@php
    $dirIconVariant = 'vdetail';
    $dirNavActive = 'businesses';
    $dirSearchPlaceholder = $isFr ? 'Rechercher un artisan, une entreprise, un produit...' : 'Search an artisan, a business, a product...';
@endphp
@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4 pb-12">

    <nav class="flex flex-wrap items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Annuaire des entreprises' : 'Business directory' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16]">{{ $businessName }}</span>
    </nav>

    <div class="mt-4 grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)_312px] gap-6">

        <!-- Left rail -->
        <aside class="space-y-4">
            <!-- Profile card -->
            <div class="bg-[#FBFAF7] border border-[#ECECEA] rounded-xl p-5">
                <div class="flex items-start gap-4">
                    @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="" class="w-[100px] h-[100px] rounded-full object-cover border border-[#ECECEA] shrink-0">
                    @else
                    <span class="w-[100px] h-[100px] rounded-full bg-[#F4F1EC] flex items-center justify-center text-[#8A857A] shrink-0"><i data-lucide="store" class="w-9 h-9"></i></span>
                    @endif
                    <div class="min-w-0">
                        @if($isVerified)
                        <span class="inline-flex items-center gap-1.5 bg-[#0E3D26] text-white text-[10.5px] font-semibold rounded-md px-2.5 py-1">
                            <i data-lucide="shield-check" class="w-3 h-3"></i>
                            {{ $isFr ? 'Artisan Authentique' : 'Authentic Artisan' }}
                        </span>
                        @endif
                        <h1 class="mt-2 flex items-center gap-2 text-[18px] font-bold text-[#1D1B16]">
                            <span class="truncate">{{ $businessName }}</span>
                            @if($isVerified)
                            <svg viewBox="0 0 16 16" class="w-[18px] h-[18px] shrink-0"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </h1>
                        @if($tagline)<p class="mt-1 text-[12.5px] text-[#55524A]">{{ $tagline }}</p>@endif
                        <p class="mt-1.5 flex items-center gap-1.5 text-[12px] text-[#6F6B60]">
                            <i data-lucide="map-pin" class="w-[12px] h-[12px]"></i>
                            {{ $cityName }}{{ $cityName && $regionName ? ', ' : '' }}{{ $regionName }} – {{ $isFr ? 'Cameroun' : 'Cameroon' }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach([['hand', $isFr ? 'Fait main' : 'Handmade'], ['leaf', $isFr ? 'Écoresponsable' : 'Eco-friendly'], ['landmark', $isFr ? 'Patrimoine culturel' : 'Cultural heritage']] as [$pcIcon, $pcLabel])
                    <span class="inline-flex items-center gap-1.5 bg-white border border-[#E7E3DA] rounded-md px-2.5 py-1.5 text-[11px] text-[#3A3A35]">
                        <i data-lucide="{{ $pcIcon }}" class="w-[12px] h-[12px] text-[#8A6D1F]"></i>
                        {{ $pcLabel }}
                    </span>
                    @endforeach
                </div>

                <p class="mt-4 flex items-center gap-2">
                    <span class="flex items-center gap-0.5">
                        @for($i = 0; $i < 5; $i++)
                        <svg viewBox="0 0 20 20" class="w-4 h-4 fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                        @endfor
                    </span>
                    <span class="text-[14px] font-bold text-[#1D1B16]">{{ $ratingAvg }}</span>
                    <span class="text-[12.5px] text-[#6F6B60]">({{ $ratingCount }} {{ $isFr ? 'avis' : 'reviews' }})</span>
                </p>
                <p class="mt-2 text-[12px] text-[#6F6B60]">
                    {{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberYear }}
                    <span class="mx-2 text-[#D8D4CA]">|</span>
                    ID&nbsp;: {{ $vendorId }}
                </p>

                <a href="{{ $siacUser ? route('messages.compose', ['business' => $business->slug, 'lang' => $lang]) : '/login?lang=' . $lang }}"
                    class="mt-4 w-full h-[42px] bg-[#02301B] hover:bg-leaf text-white rounded-lg flex items-center justify-center gap-2.5 text-[12.5px] font-semibold transition-colors">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    {{ $isFr ? 'Envoyer une demande (Enquiry)' : 'Send an enquiry' }}
                </a>
                <a href="{{ $siacUser ? route('messages.compose', ['business' => $business->slug, 'lang' => $lang]) : '/login?lang=' . $lang }}"
                    class="mt-2.5 w-full h-[42px] bg-white border border-[#E0B453] hover:bg-[#FBF6E8] rounded-lg flex items-center justify-center gap-2.5 text-[12.5px] font-semibold text-[#8A6D1F] transition-colors">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    {{ $isFr ? 'Envoyer un message' : 'Send a message' }}
                </a>

                <div class="mt-4 grid grid-cols-4 divide-x divide-[#EEEBE4] border border-[#EEEBE4] rounded-lg overflow-hidden">
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-1 py-3 hover:bg-[#F8F6F1] transition-colors">
                        <svg viewBox="0 0 24 24" fill="#22C05C" class="w-[18px] h-[18px]"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.2-3.5-.7-2.9-1.2-4.8-4.1-4.9-4.3-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.2-.3.5-.4.7-.4h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.4.9-.9 1-.7 1.4.9 1.5 2 2.4 3.3 3 .3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1s1.8.8 2.1 1c.3.1.5.2.6.4 0 .1 0 .7-.2 1z"/></svg>
                        <span class="text-[10.5px] text-[#3A3A35]">WhatsApp</span>
                    </a>
                    <a href="tel:{{ $contactPhone }}" class="flex flex-col items-center gap-1 py-3 hover:bg-[#F8F6F1] transition-colors">
                        <i data-lucide="phone" class="w-[17px] h-[17px] text-[#3A3A35]"></i>
                        <span class="text-[10.5px] text-[#3A3A35]">{{ $isFr ? 'Appeler' : 'Call' }}</span>
                    </a>
                    <a href="mailto:{{ $contactEmail }}" class="flex flex-col items-center gap-1 py-3 hover:bg-[#F8F6F1] transition-colors">
                        <i data-lucide="mail" class="w-[17px] h-[17px] text-[#E8542F]"></i>
                        <span class="text-[10.5px] text-[#3A3A35]">Email</span>
                    </a>
                    <a href="{{ $business->website ?: '#' }}" @if($business->website) target="_blank" rel="noopener" @endif class="flex flex-col items-center gap-1 py-3 hover:bg-[#F8F6F1] transition-colors">
                        <i data-lucide="globe" class="w-[17px] h-[17px] text-[#3A3A35]"></i>
                        <span class="text-[10.5px] text-[#3A3A35]">{{ $isFr ? 'Site web' : 'Website' }}</span>
                    </a>
                </div>
            </div>

            <!-- Practical info card -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5 space-y-4">
                <div class="flex items-start gap-3">
                    <i data-lucide="clock" class="w-4 h-4 text-[#55524A] mt-0.5 shrink-0"></i>
                    <div>
                        <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Temps de réponse' : 'Response time' }}</p>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'En moyenne ' . ($business->response_time_hours ?? 2) . ' heures' : 'On average ' . ($business->response_time_hours ?? 2) . ' hours' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i data-lucide="users" class="w-4 h-4 text-[#55524A] mt-0.5 shrink-0"></i>
                    <div>
                        <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Langues parlées' : 'Languages spoken' }}</p>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $languages }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i data-lucide="truck" class="w-4 h-4 text-[#55524A] mt-0.5 shrink-0"></i>
                    <div>
                        <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Livraison' : 'Delivery' }}</p>
                        <p class="mt-0.5 text-[12px] text-[#6F6B60]">National & International</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <i data-lucide="credit-card" class="w-4 h-4 text-[#55524A] mt-0.5 shrink-0"></i>
                    <div>
                        <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Moyens de paiement' : 'Payment methods' }}</p>
                        <p class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11.5px] text-[#6F6B60]">
                            <span class="flex items-center gap-1.5"><i data-lucide="smartphone" class="w-3 h-3"></i>Mobile Money</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="landmark" class="w-3 h-3"></i>{{ $isFr ? 'Virement' : 'Transfer' }}</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="banknote" class="w-3 h-3"></i>{{ $isFr ? 'Espèces' : 'Cash' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Center column -->
        <section class="min-w-0">
            <!-- Hero banner -->
            <div class="relative rounded-xl overflow-hidden bg-[#0D0B04]">
                <img src="{{ asset('images/landing/vdetail-banner.png') }}" alt="" class="w-full h-[290px] object-cover" aria-hidden="true">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0D0B04]/95 via-[#0D0B04]/60 to-transparent"></div>
                <div class="absolute inset-x-0 top-0 p-6 max-w-[420px]">
                    <h2 class="font-serif text-[23px] leading-snug text-white font-semibold">
                        {{ $isFr ? 'L\'art ancestral, façonné avec passion & authenticité.' : 'Ancestral art, shaped with passion & authenticity.' }}
                    </h2>
                    <p class="mt-3.5 text-[12.5px] text-white/85 leading-relaxed">
                        {{ $isFr ? 'Nous créons des pièces uniques en terre cuite inspirées de la richesse culturelle Bamoun. Chaque création raconte une histoire, chaque motif transmet un héritage.' : 'We create unique terracotta pieces inspired by the wealth of Bamoun culture. Each creation tells a story, each pattern passes on a heritage.' }}
                    </p>
                </div>
                <div class="absolute inset-x-0 bottom-0 bg-[#1C1809]/95 px-4 py-2.5">
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        @foreach($heroStats as [$hsIcon, $hsValue, $hsLabel])
                        <div class="flex items-center gap-2">
                            <i data-lucide="{{ $hsIcon }}" class="w-[16px] h-[16px] text-[#E5A82E] shrink-0" stroke-width="1.8"></i>
                            <div class="leading-tight">
                                <p class="text-[13px] font-bold text-white">{{ $hsValue }}</p>
                                <p class="text-[9.5px] text-white/70 whitespace-nowrap">{{ $hsLabel }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-4 bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar border-b border-[#EFEDEA] px-2">
                    @foreach($tabs as $ti => [$tabKey, $tabLabel, $tabIcon])
                    <button type="button" data-tab="{{ $tabKey }}"
                        class="tab-btn relative shrink-0 flex items-center gap-2 px-3 py-3.5 text-[12px] {{ $ti === 0 ? 'font-semibold text-[#14532D]' : 'font-medium text-[#55524A] hover:text-[#1D1B16]' }} transition-colors">
                        <i data-lucide="{{ $tabIcon }}" class="w-[13px] h-[13px]"></i>
                        {{ $tabLabel }}
                        <span class="tab-bar absolute left-2 right-2 bottom-0 h-[3px] bg-[#E7A320] {{ $ti === 0 ? '' : 'hidden' }}"></span>
                    </button>
                    @endforeach
                </div>
                <div class="p-6">
                    <div class="tab-panel" data-panel="apropos">
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_340px] gap-6">
                            <div>
                                <h3 class="text-[16px] font-bold text-[#1D1B16]">{{ $isFr ? 'À propos de' : 'About' }} {{ $businessName }}</h3>
                                <p class="mt-3 text-[12.5px] text-[#3A3A35] leading-relaxed">
                                    {{ $business->slug === 'ceramiques-du-noun'
                                        ? ($isFr
                                            ? 'Céramiques du Noun est un atelier artisanal spécialisé dans la création de poteries et d\'objets décoratifs faits à la main selon les techniques traditionnelles transmises de génération en génération dans la région de l\'Ouest Cameroun.'
                                            : 'Céramiques du Noun is an artisanal workshop specialising in the creation of pottery and decorative objects handmade using traditional techniques passed down from generation to generation in Cameroon\'s West region.')
                                        : $descriptionText }}
                                </p>
                                <p class="mt-3 text-[12.5px] text-[#3A3A35] leading-relaxed">
                                    {{ $isFr
                                        ? 'Nos créations allient tradition et esthétique contemporaine pour sublimer vos espaces et transmettre le riche patrimoine culturel Bamoun.'
                                        : 'Our creations combine tradition and contemporary aesthetics to enhance your spaces and pass on rich Cameroonian cultural heritage.' }}
                                </p>
                                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($aboutFeatures as [$afIcon, $afTitle, $afSub])
                                    <div class="flex items-start gap-3">
                                        <span class="w-9 h-9 rounded-lg bg-[#FBF4E0] flex items-center justify-center shrink-0">
                                            <i data-lucide="{{ $afIcon }}" class="w-4 h-4 text-[#B07C14]"></i>
                                        </span>
                                        <div>
                                            <p class="text-[12px] font-semibold text-[#1D1B16]">{{ $afTitle }}</p>
                                            <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $afSub }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <img src="{{ asset('images/landing/vdetail-about.png') }}" alt="" class="w-full h-[200px] object-cover rounded-xl">
                                <a href="{{ route('products.index', ['lang' => $lang]) }}" class="mt-3 flex items-center justify-end gap-2 text-[12px] font-semibold text-[#14532D] hover:underline">
                                    {{ $isFr ? 'Voir tous les produits (312)' : 'See all products (312)' }}
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tab-panel hidden" data-panel="produits">
                        <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Retrouvez les produits phares ci-dessous, ou parcourez l\'annuaire complet des produits.' : 'Find the featured products below, or browse the full product directory.' }}
                            <a href="{{ route('products.index', ['lang' => $lang]) }}" class="font-semibold text-[#14532D] hover:underline">{{ $isFr ? 'Voir l\'annuaire' : 'View the directory' }}</a></p>
                    </div>
                    <div class="tab-panel hidden" data-panel="collections">
                        <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Les collections thématiques de cet artisan seront bientôt disponibles.' : 'This artisan\'s themed collections will be available soon.' }}</p>
                    </div>
                    <div class="tab-panel hidden" data-panel="avis">
                        @if($reviews->count())
                        <div class="space-y-4">
                            @foreach($reviews->take(6) as $review)
                            <div class="border-b border-[#F0EEE9] pb-3">
                                <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $review->reviewer->name ?? 'Client' }}</p>
                                <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $review->comment ?? '' }}</p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Connectez-vous pour consulter et laisser des avis.' : 'Sign in to view and leave reviews.' }}</p>
                        @endif
                    </div>
                    <div class="tab-panel hidden" data-panel="certifications">
                        <ul class="space-y-2 text-[12.5px] text-[#3A3A35]">
                            @foreach($certItems as [$ciImg, $ciTitle, $ciSub])
                            <li class="flex items-center gap-2.5"><i data-lucide="badge-check" class="w-4 h-4 text-[#17A34A]"></i>{{ str_replace("\n", ' ', $ciTitle) }} {{ $ciSub }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tab-panel hidden" data-panel="galerie">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($featuredProducts->take(6) as $gp)
                            @if($gp->primaryImage)
                            <img src="{{ asset('storage/' . $gp->primaryImage->file_path) }}" alt="" class="w-full h-[110px] object-cover rounded-lg">
                            @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="tab-panel hidden" data-panel="politiques">
                        <p class="text-[13px] text-[#3A3A35] leading-relaxed">{{ $isFr ? 'Livraison nationale et internationale, emballage sécurisé et écoresponsable. Retours acceptés sous 14 jours. Paiements : Mobile Money, virement, espèces.' : 'National and international delivery, secure and eco-friendly packaging. Returns accepted within 14 days. Payments: Mobile Money, bank transfer, cash.' }}</p>
                    </div>
                    <div class="tab-panel hidden" data-panel="faq">
                        <p class="text-[13px] text-[#55524A]">{{ $isFr ? 'Une question ? Contactez directement l\'artisan via « Envoyer un message ».' : 'A question? Contact the artisan directly via "Send a message".' }}</p>
                    </div>
                </div>
            </div>

            <!-- Featured products -->
            <div class="mt-6">
                <h2 class="text-[16px] font-bold text-[#1D1B16]">{{ $isFr ? 'Produits phares' : 'Featured products' }}</h2>
                <div class="relative mt-3">
                    <button type="button" id="ph-prev" aria-label="{{ $isFr ? 'Précédent' : 'Previous' }}"
                        class="absolute -left-3 top-[70px] z-10 w-8 h-8 bg-white border border-[#E3E3E1] rounded-full flex items-center justify-center text-[#3A3A35] hover:text-leaf shadow-sm">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <div id="ph-track" class="flex gap-3.5 overflow-x-auto no-scrollbar scroll-smooth">
                        @foreach($featuredProducts as $fp)
                        @php
                            $fpName = $isFr ? $fp->name_fr : ($fp->name_en ?? $fp->name_fr);
                            $fpCat = $fp->category ? ($isFr ? $fp->category->name_fr : ($fp->category->name_en ?? $fp->category->name_fr)) : ($industryName ?? '');
                            $fpBadge = $designBadges[$fp->slug] ?? null;
                        @endphp
                        <article class="shrink-0 w-[152px] bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                            <div class="relative">
                                <a href="{{ route('products.show', ['slug' => $fp->slug, 'lang' => $lang]) }}">
                                    @if($fp->primaryImage)
                                    <img src="{{ asset('storage/' . $fp->primaryImage->file_path) }}" alt="{{ $fpName }}" class="w-full h-[86px] object-cover">
                                    @else
                                    @php
                                        $fpIndSlug = $fp->category?->sector?->industry?->slug ?? $fp->business?->industry?->slug ?? $business->industry?->slug;
                                        $fpDefault = in_array($fpIndSlug, ['arts-decoration','textile-mode','bois-sculpture','poterie-ceramique','bijouterie-accessoires','cuir-maroquinerie','musique-instruments','produits-naturels','agroalimentaire','technologies-innovation'])
                                            ? $fpIndSlug : (['artisanat' => 'arts-decoration', 'aquaculture' => 'produits-naturels', 'agriculture' => 'produits-naturels'][$fpIndSlug] ?? 'arts-decoration');
                                    @endphp
                                    <img src="{{ asset('images/landing/default-product-' . $fpDefault . '.png') }}" alt="{{ $fpName }}" class="w-full h-[86px] object-contain p-1.5 bg-[#F9F5EE]">
                                    @endif
                                </a>
                                @if($fpBadge === 'best')
                                <span class="absolute bottom-1.5 left-1.5 bg-[#EFA912] text-white text-[8px] font-bold tracking-[0.05em] uppercase rounded px-1.5 py-0.5">Best-seller</span>
                                @endif
                                @if($siacUser)
                                <form method="POST" action="{{ route('products.toggle-save', $fp->slug) }}" class="absolute top-1.5 right-1.5">
                                    @csrf
                                    <input type="hidden" name="return_to" value="{{ url()->full() }}">
                                    <button type="submit" aria-label="{{ $isFr ? 'Favoris' : 'Favorites' }}" class="w-6 h-6 bg-white/95 rounded-full flex items-center justify-center text-[#1D1B16]">
                                        <i data-lucide="heart" class="w-3 h-3"></i>
                                    </button>
                                </form>
                                @else
                                <a href="/login?lang={{ $lang }}" aria-label="{{ $isFr ? 'Favoris' : 'Favorites' }}"
                                    class="absolute top-1.5 right-1.5 w-6 h-6 bg-white/95 rounded-full flex items-center justify-center text-[#1D1B16]">
                                    <i data-lucide="heart" class="w-3 h-3"></i>
                                </a>
                                @endif
                            </div>
                            <div class="p-2.5">
                                <h3 class="text-[11.5px] font-bold text-[#1D1B16] truncate">
                                    <a href="{{ route('products.show', ['slug' => $fp->slug, 'lang' => $lang]) }}" class="hover:text-leaf transition-colors">{{ $fpName }}</a>
                                </h3>
                                <p class="mt-0.5 text-[10px] text-[#6F6B60] truncate">{{ $fpCat }}</p>
                            </div>
                        </article>
                        @endforeach
                    </div>
                    <button type="button" id="ph-next" aria-label="{{ $isFr ? 'Suivant' : 'Next' }}"
                        class="absolute -right-3 top-[70px] z-10 w-8 h-8 bg-white border border-[#E3E3E1] rounded-full flex items-center justify-center text-[#3A3A35] hover:text-leaf shadow-sm">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- Right rail -->
        <aside class="space-y-4">
            <!-- Key info -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Informations clés' : 'Key information' }}</h2>
                <ul class="mt-4 space-y-3 text-[11.5px]">
                    @foreach([
                        ['scale',      $isFr ? 'Statut juridique' : 'Legal status',            $isFr ? 'Entreprise artisanale' : 'Artisanal business'],
                        ['calendar',   $isFr ? 'Date de création' : 'Founded',                 (string) ($business->year_established ?? '2018')],
                        ['file-text',  $isFr ? 'Numéro d\'enregistrement' : 'Registration no.', $regNumber],
                        ['map-pin',    $isFr ? 'Région' : 'Region',                            ($regionName ?? '—') . ($cityName ? ' – ' . $cityName : '')],
                        ['layout-grid',$isFr ? 'Zone d\'activité' : 'Activity area',           $activityZone],
                        ['users',      $isFr ? 'Effectif' : 'Workforce',                       ($business->employee_count ?? 8) . ' ' . ($isFr ? 'Artisans qualifiés' : 'Qualified artisans')],
                    ] as [$kiIcon, $kiLabel, $kiValue])
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="{{ $kiIcon }}" class="w-[13px] h-[13px] text-[#55524A] mt-0.5 shrink-0"></i>
                        <span class="w-[118px] shrink-0 text-[#6F6B60]">{{ $kiLabel }}</span>
                        <span class="text-[#1D1B16] font-medium">{{ $kiValue }}</span>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('certificate.verify', ['lang' => $lang]) }}" class="mt-4 flex items-center gap-2 text-[12px] font-semibold text-[#14532D] hover:underline">
                    {{ $isFr ? 'Voir le document d\'enregistrement' : 'View the registration document' }}
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Why work with us -->
            <div class="relative bg-[#02301B] rounded-xl overflow-hidden p-5">
                <img src="{{ asset('images/landing/vdetail-why-pattern.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto object-cover opacity-90 pointer-events-none select-none" aria-hidden="true">
                <h2 class="relative text-[14px] font-bold text-white">{{ $isFr ? 'Pourquoi travailler avec nous ?' : 'Why work with us?' }}</h2>
                <ul class="relative mt-4 space-y-2.5">
                    @foreach($whyItems as $whyItem)
                    <li class="flex items-center gap-2.5 text-[12px] text-[#DCE5DE]">
                        <span class="w-[16px] h-[16px] rounded bg-[#E5A82E]/20 border border-[#E5A82E]/60 flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-[10px] h-[10px] text-[#E5A82E]"></i>
                        </span>
                        {{ $whyItem }}
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Certifications & Labels -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">Certifications & Labels</h2>
                <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                    @foreach($certItems as [$ciImg, $ciTitle, $ciSub])
                    <div>
                        <img src="{{ asset('images/landing/' . $ciImg) }}" alt="" class="h-[38px] w-auto mx-auto object-contain">
                        <p class="mt-2 text-[9.5px] font-semibold text-[#1D1B16] leading-tight whitespace-pre-line">{{ $ciTitle }}</p>
                        <p class="text-[9px] text-[#6F6B60] leading-tight">{{ $ciSub }}</p>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('certificate.verify', ['lang' => $lang]) }}" class="mt-4 w-full h-[36px] bg-[#F6F4EF] hover:bg-[#EFECe4] rounded-lg flex items-center justify-center gap-2 text-[12px] font-semibold text-[#3A3A35] transition-colors">
                    {{ $isFr ? 'Voir tous les certificats' : 'See all certificates' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <!-- Testimonial -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Ce que disent nos clients' : 'What our clients say' }}</h2>
                <p class="mt-3 text-[12px] text-[#3A3A35] leading-relaxed">
                    “{{ $isFr ? 'Des pièces magnifiques, livraison rapide et service client exceptionnel. Je recommande vivement !' : 'Magnificent pieces, fast delivery and exceptional customer service. I highly recommend!' }}”
                </p>
                <div class="mt-3.5 flex items-center gap-3">
                    <img src="{{ asset('images/landing/vdetail-client.png') }}" alt="" class="w-10 h-10 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-[#1D1B16]">Marie-Louise T.</p>
                        <p class="text-[10.5px] text-[#6F6B60]">{{ $isFr ? 'Client vérifié' : 'Verified client' }}</p>
                    </div>
                    <span class="flex items-center gap-0.5">
                        @for($i = 0; $i < 5; $i++)
                        <svg viewBox="0 0 20 20" class="w-3 h-3 fill-[#EFA912]"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                        @endfor
                    </span>
                </div>
            </div>
        </aside>
    </div>
</div>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                const active = b === btn;
                b.classList.toggle('font-semibold', active);
                b.classList.toggle('text-[#14532D]', active);
                b.classList.toggle('font-medium', !active);
                b.classList.toggle('text-[#55524A]', !active);
                b.querySelector('.tab-bar').classList.toggle('hidden', !active);
            });
            document.querySelectorAll('.tab-panel').forEach(p => {
                p.classList.toggle('hidden', p.dataset.panel !== btn.dataset.tab);
            });
        });
    });

    // Featured products carousel
    const phTrack = document.getElementById('ph-track');
    document.getElementById('ph-prev')?.addEventListener('click', () => phTrack.scrollBy({left: -340, behavior: 'smooth'}));
    document.getElementById('ph-next')?.addEventListener('click', () => phTrack.scrollBy({left: 340, behavior: 'smooth'}));
</script>
</body>
</html>
