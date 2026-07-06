@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr';
    $isFr = $lang === 'fr';
    $workshop = $workshop ?? null;
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang' => $lang], $params)) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Inscription des visiteurs — SIARC 2026, Salon International de l\'Artisanat du Cameroun.' : 'Visitor registration — SIARC 2026, International Craft Fair of Cameroon.' }}">
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
    <style>
        body{font-family:'Poppins',system-ui,sans-serif} html,body{overflow-x:clip}
        .si-input{width:100%;border:1px solid #E3E0D8;border-radius:12px;background:#fff;
            padding:.72rem .95rem;font-size:13.5px;color:#1D1B16;transition:border-color .15s,box-shadow .15s;}
        .si-input::placeholder{color:#A8A498;}
        .si-input:focus{outline:none;border-color:#157A43;box-shadow:0 0 0 3px rgba(21,122,67,.12);}
        select.si-input{appearance:none;-webkit-appearance:none;background-repeat:no-repeat;
            background-position:right .8rem center;background-size:16px;padding-right:2.4rem;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%238A857A' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");}
        .si-label{display:block;font-size:12.5px;font-weight:600;color:#3A372F;margin-bottom:.42rem;}
        .si-req{color:#C0010C;}
        /* phone prefix group */
        .si-phone{display:flex;align-items:stretch;border:1px solid #E3E0D8;border-radius:12px;background:#fff;overflow:hidden;
            transition:border-color .15s,box-shadow .15s;}
        .si-phone:focus-within{border-color:#157A43;box-shadow:0 0 0 3px rgba(21,122,67,.12);}
        .si-phone-flag{display:flex;align-items:center;gap:.4rem;padding:0 .7rem;background:#F7F5EF;border-right:1px solid #EAE7DE;
            font-size:12.5px;font-weight:600;color:#3A372F;white-space:nowrap;}
        .si-phone input{flex:1;border:0;background:transparent;padding:.72rem .8rem;font-size:13.5px;color:#1D1B16;min-width:0;}
        .si-phone input:focus{outline:none;}
        .si-phone input::placeholder{color:#A8A498;}
        /* type option cards */
        .si-type input:checked + .si-type-box{border-color:#157A43;background:#F1F8F3;box-shadow:0 0 0 1px #157A43;}
        .si-type input:checked + .si-type-box .si-type-check{opacity:1;}
        .si-type input:checked + .si-type-box .si-type-radio{opacity:0;}
        .si-type input:focus-visible + .si-type-box{box-shadow:0 0 0 3px rgba(21,122,67,.25);}
        /* wizard */
        .si-step-line{height:2px;flex:1;background:#E7E3DA;border-radius:2px;}
    </style>
</head>
<body class="bg-[#FBFAF7] text-[#1D1B16] antialiased">

@include('pages.siarc.partials.siarc-header')

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 py-8">
        <nav class="flex items-center gap-2 text-[12.5px] mb-3" aria-label="Breadcrumb">
            <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-siarc-green hover:underline font-medium">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
            <span class="text-[#8A857A]">{{ $isFr ? 'Inscription des visiteurs' : 'Visitor Registration' }}</span>
        </nav>
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-[30px] sm:text-[34px] font-bold text-[#0F2E1A] leading-tight">
                    {{ $workshop ? ($isFr ? 'Inscription à l\'atelier' : 'Workshop Registration') : ($isFr ? 'Inscription des visiteurs' : 'Visitor Registration') }}
                </h1>
                <p class="mt-2 text-[14px] text-[#55524A] leading-relaxed max-w-[760px]">
                    {{ $workshop
                        ? ($isFr ? 'Réservez votre place pour cette session du SIARC 2026.' : 'Reserve your seat for this SIARC 2026 session.')
                        : ($isFr ? 'Créez votre compte pour participer au SIARC 2026.' : 'Create your account to take part in SIARC 2026.') }}
                </p>
            </div>
            <div class="hidden sm:flex flex-col gap-1.5 text-[13px] text-[#55524A]">
                <span class="inline-flex items-center gap-2"><i data-lucide="calendar-days" class="w-4 h-4 text-siarc-green"></i><span class="font-semibold text-[#1A1712]">27 Juillet – 05 Août 2026</span></span>
                <span class="inline-flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-siarc-green"></i>Musée National de Yaoundé</span>
            </div>
        </div>
    </div>
</section>

<main class="max-w-[1300px] mx-auto px-4 sm:px-6 py-9">

    {{-- ── FLASH BANNERS ── --}}
    @if(session('siarc_registered'))
    <div class="siarc-in mb-6 flex items-start gap-3 rounded-2xl border border-[#BFE3CC] bg-[#EEF8F1] px-5 py-4">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-siarc-green shrink-0 mt-0.5"></i>
        <div>
            <p class="text-[13.5px] text-[#0F4824] leading-relaxed font-semibold">{{ $isFr ? 'Inscription confirmée !' : 'Registration confirmed!' }}</p>
            <p class="text-[12.5px] text-[#3F6B4F] leading-relaxed mt-0.5">{{ $isFr ? 'Votre badge vous sera envoyé par email. Nous avons hâte de vous accueillir au SIARC 2026.' : 'Your badge will be emailed to you. We look forward to welcoming you to SIARC 2026.' }}</p>
            @if(session('siarc_badge'))
            <p class="text-[12.5px] text-[#0F4824] mt-2">{{ $isFr ? 'Votre numéro de badge :' : 'Your badge number:' }} <b class="font-bold tracking-wide">{{ session('siarc_badge') }}</b></p>
            <div class="flex flex-wrap items-center gap-2.5 mt-2.5">
                <a href="{{ route('siarc.badge.print', ['code' => session('siarc_badge'), 'lang' => $lang]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-siarc-green text-white text-[12px] font-semibold px-3.5 py-2"><i data-lucide="printer" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir & imprimer mon badge' : 'View & print my badge' }}</a>
                <a href="{{ route('siarc.verify', ['code' => session('siarc_badge'), 'lang' => $lang]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#BFE3CC] bg-white text-siarc-green text-[12px] font-semibold px-3.5 py-2"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Vérifier mon badge' : 'Verify my badge' }}</a>
                <a href="{{ route('siarc.visitor.dashboard', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-[#BFE3CC] bg-white text-siarc-green text-[12px] font-semibold px-3.5 py-2"><i data-lucide="user-round" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Accéder à mon espace' : 'Go to my space' }}</a>
            </div>
            @endif
        </div>
    </div>
    @endif
    @if(session('siarc_error'))
    <div class="siarc-in mb-6 flex items-start gap-3 rounded-2xl border border-[#F1C3C6] bg-[#FDECED] px-5 py-4">
        <i data-lucide="circle-dot" class="w-5 h-5 text-siarc-red shrink-0 mt-0.5"></i>
        <p class="text-[13.5px] text-[#8A1015] leading-relaxed font-medium">{{ $isFr ? "Une erreur est survenue. Merci de réessayer dans un instant." : 'Something went wrong. Please try again shortly.' }}</p>
    </div>
    @endif
    @if($errors->any())
    <div class="siarc-in mb-6 flex items-start gap-3 rounded-2xl border border-[#F1C3C6] bg-[#FDECED] px-5 py-4">
        <i data-lucide="circle-dot" class="w-5 h-5 text-siarc-red shrink-0 mt-0.5"></i>
        <div>
            <p class="text-[13.5px] text-[#8A1015] leading-relaxed font-semibold">{{ $isFr ? 'Merci de vérifier les champs suivants :' : 'Please review the following fields:' }}</p>
            <ul class="mt-1 text-[12.5px] text-[#8A1015] list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    </div>
    @endif

    @if($workshop)
    {{-- ─────── WORKSHOP REGISTRATION (compact) ─────── --}}
    <div class="max-w-[720px]">
        <div class="siarc-card siarc-shadow overflow-hidden">
            <div class="siarc-kente"></div>
            <div class="p-6 sm:p-8">
                <div class="rounded-2xl siarc-adire text-white p-6 mb-7 relative overflow-hidden">
                    <span class="siarc-kicker text-siarc-gold mb-2">{{ $isFr ? 'Atelier' : 'Workshop' }}</span>
                    <h2 class="font-display text-[21px] font-bold leading-snug mb-3">{{ $workshop->title_fr ?? ($isFr ? 'Session' : 'Session') }}</h2>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-[12.5px] text-white/85">
                        @if(!empty($workshop->starts_at))
                        <span class="inline-flex items-center gap-2"><i data-lucide="calendar-clock" class="w-4 h-4 text-siarc-gold"></i>{{ \Illuminate\Support\Str::of($workshop->starts_at)->replace('T', ' ') }}</span>
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
                            <input class="si-input" id="ws_name" name="name" type="text" required value="{{ old('name') }}" placeholder="{{ $isFr ? 'Votre nom complet' : 'Your full name' }}">
                        </div>
                        <div>
                            <label class="si-label" for="ws_email">Email</label>
                            <input class="si-input" id="ws_email" name="email" type="email" value="{{ old('email') }}" placeholder="exemple@email.com">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-7 pt-6 border-t border-[#EFEDE6]">
                        <a href="{{ route('siarc.programme', ['lang' => $lang]) }}" class="siarc-btn px-6 py-3 text-[13px] border border-[#DAD6CC] text-[#55524A] hover:bg-[#F3F0E7]">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
                        <button type="submit" class="siarc-btn siarc-btn-green px-7 py-3 text-[13px]">{{ $isFr ? "S'inscrire" : 'Register' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @else
    {{-- ─────── VISITOR REGISTRATION (multi-step wizard, step 1 visible) ─────── --}}
    @php
        $wizard = [
            [$isFr ? 'Informations personnelles'     : 'Personal information',      true],
            [$isFr ? 'Informations professionnelles' : 'Professional information',  false],
            [$isFr ? 'Intérêts & Activités'          : 'Interests & Activities',    false],
            [$isFr ? 'Révision & Confirmation'       : 'Review & Confirmation',     false],
        ];
    @endphp

    {{-- Wizard progress bar (purely visual) --}}
    <div class="siarc-card siarc-shadow px-5 sm:px-7 py-5 mb-6">
        <ol class="flex items-center gap-2 sm:gap-3">
            @foreach($wizard as $i => [$slabel, $active])
            <li class="flex items-center gap-2 sm:gap-3 {{ $loop->last ? '' : 'flex-1' }}">
                <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-[13px] font-bold
                    {{ $active ? 'bg-siarc-green text-white' : 'bg-[#F1EEE6] text-[#9A9688]' }}">{{ $i + 1 }}</span>
                <span class="hidden md:block text-[12.5px] font-semibold leading-tight {{ $active ? 'text-[#1A1712]' : 'text-[#9A9688]' }}">{{ $slabel }}</span>
                @unless($loop->last)<i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C4B8] hidden md:block"></i><span class="si-step-line md:hidden"></span>@endunless
            </li>
            @endforeach
        </ol>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-start">

        {{-- ══════════ LEFT — FORM CARD ══════════ --}}
        <form action="{{ route('siarc.register.store') }}" method="POST" class="lg:col-span-2 siarc-card siarc-shadow overflow-hidden">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">

            <div class="p-6 sm:p-8">
                {{-- ══ Informations personnelles ══ --}}
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-9 h-9 rounded-full bg-siarc-green flex items-center justify-center"><i data-lucide="user" class="w-[18px] h-[18px] text-white"></i></span>
                    <h2 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Informations personnelles' : 'Personal information' }}</h2>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-5">
                    <div>
                        <label class="si-label" for="first_name">{{ $isFr ? 'Prénom' : 'First name' }} <span class="si-req">*</span></label>
                        <input class="si-input" id="first_name" name="first_name" type="text" required value="{{ old('first_name') }}" placeholder="{{ $isFr ? 'Entrez votre prénom' : 'Enter your first name' }}">
                    </div>
                    <div>
                        <label class="si-label" for="last_name">{{ $isFr ? 'Nom' : 'Last name' }} <span class="si-req">*</span></label>
                        <input class="si-input" id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="{{ $isFr ? 'Entrez votre nom' : 'Enter your last name' }}">
                    </div>
                    <div>
                        <label class="si-label" for="gender">{{ $isFr ? 'Genre' : 'Gender' }} <span class="si-req">*</span></label>
                        <select class="si-input" id="gender" name="gender">
                            <option value="">{{ $isFr ? 'Sélectionnez' : 'Select' }}</option>
                            <option value="f">{{ $isFr ? 'Femme' : 'Female' }}</option>
                            <option value="m">{{ $isFr ? 'Homme' : 'Male' }}</option>
                            <option value="x">{{ $isFr ? 'Autre / Préfère ne pas répondre' : 'Other / Prefer not to say' }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="si-label" for="birthdate">{{ $isFr ? 'Date de naissance' : 'Date of birth' }} <span class="si-req">*</span></label>
                        <div class="relative">
                            <input class="si-input pr-10" id="birthdate" name="birthdate" type="text" placeholder="JJ / MM / AAAA" onfocus="this.type='date'" onblur="if(!this.value)this.type='text'">
                            <i data-lucide="calendar" class="w-[18px] h-[18px] text-[#A8A498] absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>
                    <div>
                        <label class="si-label" for="nationality">{{ $isFr ? 'Nationalité' : 'Nationality' }} <span class="si-req">*</span></label>
                        <select class="si-input" id="nationality" name="nationality">
                            <option value="">{{ $isFr ? 'Sélectionnez votre nationalité' : 'Select your nationality' }}</option>
                            <option>{{ $isFr ? 'Camerounaise' : 'Cameroonian' }}</option>
                            <option>{{ $isFr ? 'Autre' : 'Other' }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="si-label" for="country">{{ $isFr ? 'Pays de résidence' : 'Country of residence' }} <span class="si-req">*</span></label>
                        <select class="si-input" id="country" name="country">
                            <option value="">{{ $isFr ? 'Sélectionnez votre pays' : 'Select your country' }}</option>
                            <option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option>
                            <option>{{ $isFr ? 'Autre' : 'Other' }}</option>
                        </select>
                    </div>
                    {{-- Pièce d'identité : type + numéro --}}
                    <div class="md:col-span-1">
                        <label class="si-label" for="id_type">{{ $isFr ? "Pièce d'identité" : 'ID document' }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            <select class="si-input" id="id_type" name="id_type">
                                <option value="">{{ $isFr ? 'Type de pièce' : 'Document type' }}</option>
                                <option>{{ $isFr ? "Carte nationale d'identité" : 'National ID card' }}</option>
                                <option>{{ $isFr ? 'Passeport' : 'Passport' }}</option>
                            </select>
                            <input class="si-input" id="id_number" name="id_number" type="text" placeholder="{{ $isFr ? 'Numéro de pièce' : 'Document number' }}">
                        </div>
                    </div>
                    {{-- Photo d'identité --}}
                    <div>
                        <label class="si-label">{{ $isFr ? "Photo d'identité" : 'ID photo' }} <span class="si-req">*</span></label>
                        <label class="flex items-center gap-3 rounded-xl border border-dashed border-[#D8D4CA] bg-white px-4 py-[13px] cursor-pointer hover:border-[#C7D9CD] transition-colors" for="photo">
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg" class="sr-only">
                            <span class="w-10 h-10 rounded-full bg-[#F1F8F3] flex items-center justify-center shrink-0"><i data-lucide="upload" class="w-[18px] h-[18px] text-siarc-green"></i></span>
                            <span class="leading-tight">
                                <span class="block text-[13px] font-semibold text-[#1A1712]">{{ $isFr ? 'Télécharger une photo' : 'Upload a photo' }}</span>
                                <span class="block text-[11.5px] text-[#8A857A]">JPG, PNG – Max 2 Mo</span>
                            </span>
                        </label>
                    </div>
                </div>

                {{-- ══ Informations de contact ══ --}}
                <div class="flex items-center gap-3 mt-9 mb-6">
                    <span class="w-9 h-9 rounded-full bg-siarc-green flex items-center justify-center"><i data-lucide="phone" class="w-[18px] h-[18px] text-white"></i></span>
                    <h2 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h2>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-5">
                    <div>
                        <label class="si-label" for="email">Email <span class="si-req">*</span></label>
                        <input class="si-input" id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="exemple@email.com">
                    </div>
                    <div>
                        <label class="si-label" for="phone">{{ $isFr ? 'Téléphone' : 'Phone' }} <span class="si-req">*</span></label>
                        <div class="si-phone">
                            <span class="si-phone-flag"><span aria-hidden="true">🇨🇲</span> +237</span>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="6 12 34 56 78">
                        </div>
                    </div>
                    <div>
                        <label class="si-label" for="whatsapp">WhatsApp</label>
                        <div class="si-phone">
                            <span class="si-phone-flag"><span aria-hidden="true">🇨🇲</span> +237</span>
                            <input id="whatsapp" name="whatsapp" type="tel" placeholder="6 12 34 56 78">
                        </div>
                    </div>
                    <div>
                        <label class="si-label" for="address">{{ $isFr ? 'Adresse' : 'Address' }}</label>
                        <input class="si-input" id="address" name="address" type="text" placeholder="{{ $isFr ? 'Votre adresse complète' : 'Your full address' }}">
                    </div>
                    <div>
                        <label class="si-label" for="city">{{ $isFr ? 'Ville' : 'City' }}</label>
                        <input class="si-input" id="city" name="city" type="text" placeholder="{{ $isFr ? 'Ville' : 'City' }}">
                    </div>
                    <div>
                        <label class="si-label" for="postal_code">{{ $isFr ? 'Code postal' : 'Postal code' }}</label>
                        <input class="si-input" id="postal_code" name="postal_code" type="text" placeholder="{{ $isFr ? 'Code postal' : 'Postal code' }}">
                    </div>
                </div>

                {{-- ══ Informations additionnelles ══ --}}
                <div class="flex items-center gap-3 mt-9 mb-6">
                    <span class="w-9 h-9 rounded-full bg-siarc-green flex items-center justify-center"><i data-lucide="id-card" class="w-[18px] h-[18px] text-white"></i></span>
                    <h2 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Informations additionnelles' : 'Additional information' }}</h2>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-5">
                    <div>
                        <label class="si-label" for="visitor_profile">{{ $isFr ? 'Type de visiteur' : 'Visitor type' }} <span class="si-req">*</span></label>
                        <select class="si-input" id="visitor_profile" name="visitor_profile">
                            <option value="">{{ $isFr ? 'Sélectionnez votre profil' : 'Select your profile' }}</option>
                            <option>{{ $isFr ? 'Particulier' : 'Individual' }}</option>
                            <option>{{ $isFr ? 'Professionnel' : 'Professional' }}</option>
                            <option>{{ $isFr ? 'Acheteur' : 'Buyer' }}</option>
                            <option>{{ $isFr ? 'Presse' : 'Press' }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="si-label" for="organization">{{ $isFr ? 'Organisation / Entreprise' : 'Organization / Company' }}</label>
                        <input class="si-input" id="organization" name="organization" type="text" value="{{ old('organization') }}" placeholder="{{ $isFr ? 'Nom de votre organisation' : 'Your organization name' }}">
                    </div>
                    <div>
                        <label class="si-label" for="role">{{ $isFr ? 'Fonction / Poste' : 'Role / Position' }}</label>
                        <input class="si-input" id="role" name="role" type="text" placeholder="{{ $isFr ? 'Votre fonction' : 'Your position' }}">
                    </div>
                </div>

                {{-- ══ Options d'inscription — plain section title (no icon) ══ --}}
                <h2 class="text-[15px] font-bold text-[#1A1712] mt-9 mb-4">{{ $isFr ? "Options d'inscription" : 'Registration options' }}</h2>
                @php
                    // [radioId, real value, icon, title, description]
                    $options = [
                        ['opt_general', 'visitor', 'user',
                            $isFr ? 'Accès Général' : 'General Access',
                            $isFr ? 'Accès aux expositions, au village artisanal et aux espaces d\'animation.' : 'Access to exhibitions, the craft village and animation areas.'],
                        ['opt_pro', 'buyer', 'briefcase',
                            $isFr ? 'Accès Professionnel' : 'Professional Access',
                            $isFr ? 'Accès aux conférences, ateliers et B2B meetings.' : 'Access to conferences, workshops and B2B meetings.'],
                        ['opt_delegation', 'visitor', 'users-round',
                            $isFr ? 'Délégation Officielle' : 'Official Delegation',
                            $isFr ? 'Pour les délégations ministérielles et institutionnelles.' : 'For ministerial and institutional delegations.'],
                        ['opt_press', 'press', 'megaphone',
                            $isFr ? 'Presse / Média' : 'Press / Media',
                            $isFr ? 'Accès presse, conférences de presse et interviews.' : 'Press access, press conferences and interviews.'],
                    ];
                    $oldType = old('type', 'visitor');
                @endphp
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($options as $idx => [$rid, $val, $icon, $title, $desc])
                    <label class="si-type cursor-pointer" for="{{ $rid }}">
                        <input type="radio" id="{{ $rid }}" name="type" value="{{ $val }}" class="sr-only" {{ ($idx === 0 && $oldType === 'visitor') || ($idx > 0 && $oldType === $val && $oldType !== 'visitor') ? 'checked' : '' }}>
                        <div class="si-type-box relative h-full rounded-2xl border border-[#E3E0D8] bg-white p-4 transition-all hover:border-[#C7D9CD]">
                            <span class="si-type-radio absolute top-3.5 right-3.5 w-[18px] h-[18px] rounded-full border-2 border-[#D8D4CA] transition-opacity"></span>
                            <i data-lucide="check-circle-2" class="si-type-check absolute top-3.5 right-3.5 w-[19px] h-[19px] text-siarc-green opacity-0 transition-opacity"></i>
                            <span class="w-10 h-10 rounded-xl bg-[#F1F8F3] flex items-center justify-center mb-3"><i data-lucide="{{ $icon }}" class="w-5 h-5 text-siarc-green"></i></span>
                            <p class="text-[14px] font-bold text-[#1A1712] leading-tight">{{ $title }}</p>
                            <p class="text-[11.5px] text-[#8A857A] leading-relaxed mt-1.5">{{ $desc }}</p>
                            <p class="text-[12px] font-semibold text-siarc-green mt-3">{{ $isFr ? 'Gratuit' : 'Free' }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Consent + actions --}}
                <div class="flex flex-wrap items-center justify-between gap-4 mt-8 pt-6 border-t border-[#EFEDE6]">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" required class="mt-0.5 w-4 h-4 accent-siarc-green rounded">
                        <span class="text-[12.5px] text-[#55524A] leading-relaxed">
                            {{ $isFr ? "J'accepte les" : 'I accept the' }}
                            <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-siarc-green font-medium hover:underline">{{ $isFr ? "Conditions d'utilisation" : 'Terms of use' }}</a>
                            {{ $isFr ? 'et la' : 'and the' }}
                            <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="text-siarc-green font-medium hover:underline">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
                            {{ $isFr ? 'du SIARC 2026' : 'of SIARC 2026' }} <span class="si-req">*</span>
                        </span>
                    </label>
                    <div class="flex items-center gap-3 ml-auto">
                        <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="siarc-btn px-6 py-3 text-[13px] border border-[#DAD6CC] text-[#55524A] hover:bg-[#F3F0E7]">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
                        <button type="submit" class="siarc-btn siarc-btn-green px-7 py-3 text-[13px]">{{ $isFr ? 'Suivant' : 'Next' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ══════════ RIGHT — SIDEBAR ══════════ --}}
        <aside class="space-y-6">

            {{-- Résumé de l'inscription — badge visual --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? "Résumé de l'inscription" : 'Registration summary' }}</h3>
                <div class="flex items-start gap-5">
                    <img src="{{ asset('images/siarc/register-badge-1.png') }}" alt="{{ $isFr ? 'Badge visiteur SIARC 2026' : 'SIARC 2026 visitor badge' }}" class="w-[112px] shrink-0 select-none" draggable="false">
                    <ul class="space-y-3 text-[12.5px] text-[#3A372F] pt-1">
                        @php
                            $badgePerks = [
                                ['store',        $isFr ? 'Accès à plus de 800 exposants' : 'Access to 800+ exhibitors'],
                                ['presentation', $isFr ? 'Conférences & Ateliers'         : 'Conferences & Workshops'],
                                ['handshake',    $isFr ? 'Rencontres B2B'                 : 'B2B meetings'],
                                ['store',        $isFr ? 'Village artisanal'              : 'Craft village'],
                                ['star',         $isFr ? "Espaces d'innovation"           : 'Innovation spaces'],
                                ['activity',     $isFr ? 'Animations culturelles'         : 'Cultural performances'],
                            ];
                        @endphp
                        @foreach($badgePerks as [$icon, $label])
                        <li class="flex items-center gap-2.5"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-siarc-green shrink-0"></i>{{ $label }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Événement --}}
                <div class="mt-5 rounded-2xl bg-[#F1F8F3] p-4">
                    <p class="text-[11px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Événement' : 'Event' }}</p>
                    <div class="space-y-2.5 text-[12.5px]">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="calendar-days" class="w-4 h-4 text-siarc-green shrink-0"></i>
                            <span class="font-semibold text-[#1A1712]">27 Juillet – 05 Août 2026</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-siarc-green shrink-0"></i>
                            <span class="text-[#3A372F]">Musée National de Yaoundé, Cameroun</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pourquoi s'inscrire ? --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? "Pourquoi s'inscrire ?" : 'Why register?' }}</h3>
                <ul class="space-y-4">
                    @php
                        $reasons = [
                            ['store',    $isFr ? "Vivez la plus grande vitrine de l'artisanat camerounais et africain." : 'Experience the largest showcase of Cameroonian and African crafts.'],
                            ['users',    $isFr ? "Rencontrez des artisans, acheteurs et investisseurs du monde entier." : 'Meet artisans, buyers and investors from around the world.'],
                            ['presentation', $isFr ? "Participez à des conférences, ateliers et démonstrations exclusives." : 'Attend exclusive conferences, workshops and demonstrations.'],
                            ['briefcase', $isFr ? "Développez votre réseau et saisissez de nouvelles opportunités d'affaires." : 'Grow your network and seize new business opportunities.'],
                        ];
                    @endphp
                    @foreach($reasons as [$icon, $text])
                    <li class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-xl bg-[#F1F8F3] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] text-siarc-green"></i></span>
                        <p class="text-[12.5px] text-[#3A372F] leading-relaxed pt-0.5">{{ $text }}</p>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Besoin d'aide ? --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-2">{{ $isFr ? "Besoin d'aide ?" : 'Need help?' }}</h3>
                <p class="text-[12.5px] text-[#55524A] leading-relaxed mb-4">{{ $isFr ? 'Notre équipe est à votre disposition pour vous accompagner dans votre inscription.' : 'Our team is here to help you register.' }}</p>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-2 text-[12.5px] text-[#3A372F]">
                        <span class="flex items-center gap-2.5"><i data-lucide="phone" class="w-4 h-4 text-siarc-green"></i>+237 222 22 22 22</span>
                        <span class="flex items-center gap-2.5"><i data-lucide="mail" class="w-4 h-4 text-siarc-green"></i>contact@siarc-cameroun.cm</span>
                    </div>
                    <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[12.5px]"><i data-lucide="info" class="w-4 h-4"></i>{{ $isFr ? "Centre d'aide" : 'Help center' }}</a>
                </div>
            </div>
        </aside>
    </div>
    @endif
</main>

@include('pages.siarc.partials.siarc-footer')

<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-mnav-btn'),m=document.getElementById('si-mnav');
        if(b&&m)b.addEventListener('click',function(){m.classList.toggle('hidden');});
    })();
</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
@stack('scripts')
</body>
</html>
