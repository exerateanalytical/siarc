@php
    $lang = $lang ?? 'fr';
    $isFr = $lang === 'fr';
    $workshop = $workshop ?? null;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inscription des visiteurs — SIARC 2026, Salon International de l'Artisanat du Cameroun.">
    <title>{{ $isFr ? 'Inscription des visiteurs' : 'Visitor Registration' }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { siarc:{green:'#157A43',dark:'#0B3A1E',darker:'#042B15',gold:'#E6B201',ochre:'#C97A16',red:'#C0010C'}, cream:'#F8F4EC' },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'], display:['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    <style>body{font-family:'Poppins',system-ui,sans-serif} html,body{overflow-x:clip}
        .si-input{width:100%;border:1px solid #E3E0D8;border-radius:12px;background:#fff;
            padding:.72rem .95rem;font-size:13.5px;color:#1D1B16;transition:border-color .15s,box-shadow .15s;}
        .si-input::placeholder{color:#A8A498;}
        .si-input:focus{outline:none;border-color:#157A43;box-shadow:0 0 0 3px rgba(21,122,67,.12);}
        .si-label{display:block;font-size:12.5px;font-weight:600;color:#3A372F;margin-bottom:.42rem;}
        .si-req{color:#C0010C;}
        .si-type input:checked + .si-type-box{border-color:#157A43;background:#F2F8F4;box-shadow:0 0 0 1px #157A43;}
        .si-type input:checked + .si-type-box .si-type-check{opacity:1;}
    </style>
</head>
<body class="bg-[#FBFAF7] text-[#1D1B16] antialiased">

@include('pages.siarc.partials.siarc-header')

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-9">
        <nav class="flex items-center gap-2 text-[12.5px] mb-3" aria-label="Breadcrumb">
            <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-siarc-green hover:underline font-medium">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
            <span class="text-[#8A857A]">{{ $isFr ? 'Inscription des visiteurs' : 'Visitor Registration' }}</span>
        </nav>
        <h1 class="font-display text-[30px] sm:text-[38px] font-bold text-[#0F2E1A] leading-tight">
            {{ $workshop ? ($isFr ? 'Inscription à l\'atelier' : 'Workshop Registration') : ($isFr ? 'Inscription des visiteurs' : 'Visitor Registration') }}
        </h1>
        <div class="mt-3 h-[3.5px] w-[104px] bg-gradient-to-r from-siarc-gold via-[#F1D48A] to-transparent rounded-full"></div>
        <p class="mt-4 text-[14px] text-[#55524A] leading-relaxed max-w-[760px]">
            {{ $isFr ? 'Créez votre compte pour participer au SIARC 2026.' : 'Create your account to take part in SIARC 2026.' }}
        </p>
    </div>
</section>

<main class="max-w-[1240px] mx-auto px-4 sm:px-6 py-10">

    {{-- ── FLASH BANNERS ── --}}
    @if(session('siarc_registered'))
    <div class="siarc-in mb-6 flex items-start gap-3 rounded-2xl border border-[#BFE3CC] bg-[#EEF8F1] px-5 py-4">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-siarc-green shrink-0 mt-0.5"></i>
        <p class="text-[13.5px] text-[#0F4824] leading-relaxed font-medium">{{ session('siarc_registered') }}</p>
    </div>
    @endif
    @if(session('siarc_error'))
    <div class="siarc-in mb-6 flex items-start gap-3 rounded-2xl border border-[#F1C3C6] bg-[#FDECED] px-5 py-4">
        <i data-lucide="circle-dot" class="w-5 h-5 text-siarc-red shrink-0 mt-0.5"></i>
        <p class="text-[13.5px] text-[#8A1015] leading-relaxed font-medium">{{ session('siarc_error') }}</p>
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6 items-start">

        {{-- ══════════ LEFT — FORM CARD ══════════ --}}
        <div class="lg:col-span-2">

            @if($workshop)
            {{-- ─────── WORKSHOP REGISTRATION ─────── --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="siarc-kente"></div>
                <div class="p-6 sm:p-8">
                    <div class="rounded-2xl siarc-adire text-white p-5 mb-7 relative overflow-hidden">
                        <span class="siarc-kicker text-siarc-gold mb-2">{{ $isFr ? 'Atelier' : 'Workshop' }}</span>
                        <h2 class="font-display text-[21px] font-bold leading-snug mb-3">{{ $workshop->title_fr }}</h2>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-[12.5px] text-white/85">
                            @if(!empty($workshop->starts_at))
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar-clock" class="w-4 h-4 text-siarc-gold"></i>{{ \Illuminate\Support\Str::of($workshop->starts_at)->replace('T',' ') }}</span>
                            @endif
                            @if(!empty($workshop->room))
                            <span class="inline-flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-siarc-gold"></i>{{ $workshop->room }}</span>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('siarc.workshop.register.store', ['id' => $workshop->id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="si-label" for="ws_name">{{ $isFr ? 'Nom complet' : 'Full name' }} <span class="si-req">*</span></label>
                                <input class="si-input" id="ws_name" name="name" type="text" required placeholder="{{ $isFr ? 'Votre nom complet' : 'Your full name' }}">
                            </div>
                            <div>
                                <label class="si-label" for="ws_email">Email</label>
                                <input class="si-input" id="ws_email" name="email" type="email" placeholder="exemple@email.com">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 mt-7 pt-6 border-t border-[#EFEDE6]">
                            <a href="{{ route('siarc.programme', ['lang' => $lang]) }}" class="siarc-btn px-6 py-3 text-[13px] border border-[#DAD6CC] text-[#55524A] hover:bg-[#F3F0E7]">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
                            <button type="submit" class="siarc-btn siarc-btn-green px-7 py-3 text-[13px]">{{ $isFr ? 'S\'inscrire' : 'Register' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            @else
            {{-- ─────── VISITOR REGISTRATION ─────── --}}
            <form action="{{ route('siarc.register.store') }}" method="POST" class="siarc-card siarc-shadow overflow-hidden">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="siarc-kente"></div>

                <div class="p-6 sm:p-8">
                    {{-- Informations personnelles --}}
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center"><i data-lucide="user" class="w-5 h-5 text-siarc-green"></i></span>
                        <h2 class="font-display text-[19px] font-bold text-[#1A1712]">{{ $isFr ? 'Informations personnelles' : 'Personal information' }}</h2>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="si-label" for="first_name">{{ $isFr ? 'Prénom' : 'First name' }} <span class="si-req">*</span></label>
                            <input class="si-input" id="first_name" name="first_name" type="text" required placeholder="{{ $isFr ? 'Entrez votre prénom' : 'Enter your first name' }}">
                        </div>
                        <div>
                            <label class="si-label" for="last_name">{{ $isFr ? 'Nom' : 'Last name' }}</label>
                            <input class="si-input" id="last_name" name="last_name" type="text" placeholder="{{ $isFr ? 'Entrez votre nom' : 'Enter your last name' }}">
                        </div>
                    </div>

                    {{-- Informations de contact --}}
                    <div class="flex items-center gap-3 mt-9 mb-6">
                        <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center"><i data-lucide="phone" class="w-5 h-5 text-siarc-green"></i></span>
                        <h2 class="font-display text-[19px] font-bold text-[#1A1712]">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h2>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="si-label" for="email">Email <span class="si-req">*</span></label>
                            <input class="si-input" id="email" name="email" type="email" required placeholder="exemple@email.com">
                        </div>
                        <div>
                            <label class="si-label" for="phone">{{ $isFr ? 'Téléphone' : 'Phone' }}</label>
                            <input class="si-input" id="phone" name="phone" type="tel" placeholder="+237 6 12 34 56 78">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="si-label" for="organization">{{ $isFr ? 'Organisation / Entreprise' : 'Organization / Company' }}</label>
                            <input class="si-input" id="organization" name="organization" type="text" placeholder="{{ $isFr ? 'Nom de votre organisation' : 'Your organization name' }}">
                        </div>
                    </div>

                    {{-- Type selector --}}
                    <div class="flex items-center gap-3 mt-9 mb-6">
                        <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center"><i data-lucide="id-card" class="w-5 h-5 text-siarc-green"></i></span>
                        <h2 class="font-display text-[19px] font-bold text-[#1A1712]">{{ $isFr ? 'Type de visiteur' : 'Visitor type' }} <span class="si-req align-top text-[13px]">*</span></h2>
                    </div>
                    @php
                        $types = [
                            ['visitor','user','Visiteur','Visitor',$isFr ? 'Accès aux expositions et au village artisanal.' : 'Access to exhibitions and the craft village.'],
                            ['buyer','handshake','Acheteur','Buyer',$isFr ? 'Accès aux conférences, ateliers et B2B meetings.' : 'Access to conferences, workshops and B2B meetings.'],
                            ['press','megaphone','Presse','Press',$isFr ? 'Accès presse, conférences de presse et interviews.' : 'Press access, press conferences and interviews.'],
                        ];
                    @endphp
                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach($types as [$val,$icon,$fr,$en,$desc])
                        <label class="si-type cursor-pointer">
                            <input type="radio" name="type" value="{{ $val }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                            <div class="si-type-box relative h-full rounded-2xl border border-[#E3E0D8] bg-white p-4 transition-all hover:border-[#C7D9CD]">
                                <i data-lucide="check-circle-2" class="si-type-check absolute top-3 right-3 w-5 h-5 text-siarc-green opacity-0 transition-opacity"></i>
                                <span class="w-10 h-10 rounded-xl bg-[#F3F0E7] flex items-center justify-center mb-3"><i data-lucide="{{ $icon }}" class="w-5 h-5 text-siarc-green"></i></span>
                                <p class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? $fr : $en }}</p>
                                <p class="text-[11.5px] text-[#8A857A] leading-relaxed mt-1">{{ $desc }}</p>
                                <p class="text-[11px] font-semibold text-siarc-green mt-2">{{ $isFr ? 'Gratuit' : 'Free' }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Consent + actions --}}
                    <label class="flex items-start gap-2.5 mt-8 cursor-pointer">
                        <input type="checkbox" required class="mt-0.5 w-4 h-4 accent-siarc-green rounded">
                        <span class="text-[12.5px] text-[#55524A] leading-relaxed">
                            {{ $isFr ? "J'accepte les" : 'I accept the' }}
                            <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="text-siarc-green font-medium hover:underline">{{ $isFr ? "Conditions d'utilisation" : 'Terms of use' }}</a>
                            {{ $isFr ? 'et la' : 'and the' }}
                            <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="text-siarc-green font-medium hover:underline">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
                            {{ $isFr ? 'du SIARC 2026' : 'of SIARC 2026' }} <span class="si-req">*</span>
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-3 mt-7 pt-6 border-t border-[#EFEDE6]">
                        <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="siarc-btn px-6 py-3 text-[13px] border border-[#DAD6CC] text-[#55524A] hover:bg-[#F3F0E7]">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
                        <button type="submit" class="siarc-btn siarc-btn-green px-7 py-3 text-[13px]">{{ $isFr ? "S'inscrire" : 'Register' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </form>
            @endif
        </div>

        {{-- ══════════ RIGHT — BENEFITS SIDEBAR ══════════ --}}
        <aside class="space-y-6">
            {{-- Event summary --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="siarc-adire text-white p-6 relative overflow-hidden">
                    <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
                    <span class="siarc-kicker text-siarc-gold mb-3 mt-2">{{ $isFr ? "Résumé de l'inscription" : 'Registration summary' }}</span>
                    <h3 class="font-display text-[22px] font-bold leading-tight mb-4">SIARC 2026</h3>
                    <div class="space-y-3 text-[13px]">
                        <div class="flex items-start gap-3">
                            <i data-lucide="calendar-days" class="w-5 h-5 text-siarc-gold shrink-0 mt-0.5"></i>
                            <div><p class="font-semibold">27 Juillet – 05 Août 2026</p><p class="text-white/70 text-[12px]">{{ $isFr ? 'Dix jours de célébration' : 'Ten days of celebration' }}</p></div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 text-siarc-gold shrink-0 mt-0.5"></i>
                            <div><p class="font-semibold">Musée National de Yaoundé</p><p class="text-white/70 text-[12px]">Cameroun</p></div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i data-lucide="ticket" class="w-5 h-5 text-siarc-gold shrink-0 mt-0.5"></i>
                            <div><p class="font-semibold">{{ $isFr ? 'Entrée libre' : 'Free entry' }}</p><p class="text-white/70 text-[12px]">{{ $isFr ? 'Inscription gratuite' : 'Free registration' }}</p></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- What you get --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[18px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Ce que vous obtenez' : 'What you get' }}</h3>
                <ul class="space-y-3.5">
                    @php
                        $benefits = [
                            ['id-card','Badge personnel', $isFr ? 'Votre badge d\'accès nominatif au salon.' : 'Your personal access badge to the fair.'],
                            ['map','Plan interactif', $isFr ? 'Trouvez stands, pavillons et services.' : 'Find stands, pavilions and services.'],
                            ['calendar-days','Programme complet', $isFr ? 'Conférences, ateliers et démonstrations.' : 'Conferences, workshops and demonstrations.'],
                            ['handshake','Networking B2B', $isFr ? 'Rencontrez artisans, acheteurs et investisseurs.' : 'Meet artisans, buyers and investors.'],
                        ];
                    @endphp
                    @foreach($benefits as [$icon,$title,$desc])
                    <li class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-5 h-5 text-siarc-green"></i></span>
                        <div>
                            <p class="text-[13.5px] font-semibold text-[#1A1712] leading-tight">{{ $title }}</p>
                            <p class="text-[12px] text-[#8A857A] leading-relaxed mt-0.5">{{ $desc }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Help --}}
            <div class="rounded-2xl bg-gradient-to-br from-[#14652F] to-[#042B15] text-white p-6 relative overflow-hidden">
                <h3 class="font-display text-[18px] font-bold mb-2">{{ $isFr ? "Besoin d'aide ?" : 'Need help?' }}</h3>
                <p class="text-[12.5px] text-white/75 leading-relaxed mb-4">{{ $isFr ? 'Notre équipe est à votre disposition pour vous accompagner dans votre inscription.' : 'Our team is here to help you register.' }}</p>
                <div class="space-y-2 text-[13px]">
                    <span class="flex items-center gap-2.5"><i data-lucide="phone" class="w-4 h-4 text-siarc-gold"></i>+237 222 22 22 22</span>
                    <span class="flex items-center gap-2.5"><i data-lucide="mail" class="w-4 h-4 text-siarc-gold"></i>contact@siarc-cameroun.cm</span>
                </div>
            </div>
        </aside>
    </div>
</main>

@include('pages.siarc.partials.siarc-footer')

<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-mnav-btn'),m=document.getElementById('si-mnav');
        if(b&&m)b.addEventListener('click',function(){m.classList.toggle('hidden');});
    })();
</script>
@stack('scripts')
</body>
</html>
