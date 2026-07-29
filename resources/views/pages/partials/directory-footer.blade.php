{{-- Canonical platform chrome — footer.
     Brought up to `certificates/artisan profile v2 desktop.png` (2026-07-28):
     #011E13 green-black, the gold certification seal and brand paragraph left,
     QUICK LINKS in two columns, a CONNECT column carrying the social discs,
     phone and email, and — only where it means something — the
     "SCAN TO VERIFY THIS ARTISAN" QR block on the right. Bottom strip: the
     copyright, a working language select and the country.

     Expects: $lang, $isFr, $siacUser

     Optional:
       $dfVerifyUrl     the URL the QR block should encode. Supply it and the
                        block renders; leave it out and the whole column is
                        absent. On an artisan profile it is filled in for you
                        from $business below, because a QR captioned "verify
                        this artisan" has to be about an actual artisan — on the
                        contact page it would encode nothing.
       $dfVerifyName    name shown under the QR (defaults to the business name).
       $dfQuickLinksOne / $dfQuickLinksTwo   label => href, the two QUICK LINKS
                        columns.
       $dfShowLegalLinks (bool, default true), $dfBgColor.

     Every href below resolves to a route this platform actually serves; there
     are no placeholders. --}}

@php
    // Deliberately NOT overridable: the footer is the same colour on every
    // public page. Pages used to pass their own $dfBgColor and the chrome drifted.
    $dfBgColor = '#011E13';
    $dfShowLegalLinks = $dfShowLegalLinks ?? true;

    // --- QUICK LINKS -------------------------------------------------------
    // The artwork draws two columns of five. This platform has more real pages
    // than that, and dropping any of them would orphan it, so the two columns
    // carry every footer destination the site had: the artwork's structure, the
    // site's own contents.
    // "Shipping Policy" and "Return Policy" from the artwork are deliberately
    // absent: this platform ships nothing and is not a party to any sale, so
    // those pages do not and must not exist.
    $dfQuickLinksOne = $dfQuickLinksOne ?? array_filter([
        ($isFr ? 'À propos' : 'About us')                            => route('about', ['lang' => $lang]),
        ($isFr ? 'Comment ça marche' : 'How it works')               => route('guide.artisan', ['lang' => $lang]),
        ($isFr ? 'Vérifier un certificat' : 'Verify certificate')    => route('certificate.verify', ['lang' => $lang]),
        ($isFr ? 'Autorité de certification' : 'Certification Authority') => route('ca.page', ['lang' => $lang]),
        ($isFr ? 'Certificats révoqués' : 'Revoked certificates')    => route('revocation.list', ['lang' => $lang]),
        ($isFr ? 'Protéger mon travail' : 'Trust & security')        => route('protection', ['lang' => $lang]),
        ($isFr ? "Conditions d'utilisation" : 'Terms & conditions')  => route('legal.show', ['doc' => 'conditions', 'lang' => $lang]),
    ]);
    $dfQuickLinksTwo = $dfQuickLinksTwo ?? array_filter([
        ($isFr ? 'Confidentialité' : 'Privacy policy')      => route('legal.show', ['doc' => 'confidentialite', 'lang' => $lang]),
        ($isFr ? "Centre d'aide" : 'Help center')           => route('support.index'),
        'FAQ'                                               => route('faq', ['lang' => $lang]),
        ($isFr ? 'Devenir membre' : 'Become a member')      => route('onboarding', ['lang' => $lang]),
        ($isFr ? 'Carrières' : 'Careers')                   => route('careers', ['lang' => $lang]),
        ($isFr ? 'Presse' : 'Press')                        => route('press', ['lang' => $lang]),
        ($isFr ? 'API & Développeurs' : 'API & developers') => url('/docs/api'),
        ($isFr ? 'Nous contacter' : 'Contact us')           => route('contact', ['lang' => $lang]),
    ]);

    // --- Social discs ------------------------------------------------------
    // Only profiles that are actually configured render; an icon linking to an
    // account that does not exist is worse than no icon.
    $socialProfileUrls = array_filter([
        'Facebook'  => config('legal.social.facebook'),
        'Instagram' => config('legal.social.instagram'),
        'YouTube'   => config('legal.social.youtube'),
        'LinkedIn'  => config('legal.social.linkedin'),
        'X'         => config('legal.social.x'),
    ]);
    $dfSocialIcons = [
        'Facebook'  => ['#1877F2', '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>'],
        'Instagram' => ['#C13584', '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>'],
        'YouTube'   => ['#FF0000', '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>'],
        'LinkedIn'  => ['#0A66C2', '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>'],
        'X'         => ['#111111', '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>'],
    ];

    // --- The conditional QR ------------------------------------------------
    // "SCAN TO VERIFY THIS ARTISAN" is only meaningful on a page that IS about
    // one artisan, so the block is opt-in. An artisan profile fills it in
    // automatically from the record already in scope; every other page leaves
    // $dfVerifyUrl unset and the column simply is not rendered.
    //
    // The URL has to actually resolve. /certificat-artisan/{slug} 404s for an
    // artisan whose identity is not established yet, so the level is checked
    // first — levelFor() is the same gate the route applies, and unlike
    // forBusiness() it is read-only: a footer must not issue a certificate as a
    // side effect of being rendered. Below level 1 there is nothing to verify,
    // and no QR is drawn.
    if (! isset($dfVerifyUrl) && request()->routeIs('businesses.show') && isset($business) && ! empty($business->slug)
        && \App\Support\ArtisanVerification::levelFor($business) >= 1) {
        $dfVerifyUrl  = route('artisan.verification.certificate', ['slug' => $business->slug, 'lang' => $lang]);
        $dfVerifyName = $dfVerifyName ?? ($business->name_fr ?? $business->name ?? null);
    }
    $dfVerifyUrl  = $dfVerifyUrl ?? null;
    $dfVerifyName = $dfVerifyName ?? null;

    $dfPhone = config('legal.company.phone');
    $dfEmail = config('legal.company.email');

    // Column template. Measured off the artwork at 1024 and scaled ×1.25:
    // brand 25→490, quick links 526→770, connect 810→1050, QR 1090→1255.
    $dfCols = $dfVerifyUrl
        ? 'lg:grid-cols-[1.55fr_0.78fr_0.78fr_0.92fr_0.62fr]'
        : 'lg:grid-cols-[1.55fr_0.78fr_0.78fr_0.92fr]';
@endphp

{{-- `overflow-x: clip` rather than `hidden`: the old footer clipped the
     "Cameroon, land of creativity and innovation" block off the right edge at
     1280 and the page scrolled sideways behind it. Nothing in here is allowed
     to be wider than its column now — every list wraps, every column is
     min-w-0 — and the clip is a backstop, not the layout. --}}
<footer class="relative" style="background-color: {{ $dfBgColor }}; overflow-x: clip">
    {{-- Gutter 16px on a phone, the artwork's 25px from `sm` up
         (docs/RESPONSIVE-CONTRACT.md). --}}
    <div class="relative max-w-[1280px] mx-auto px-4 sm:px-[25px] pt-6 pb-[76px] sm:pb-4">
        {{-- Two columns on a phone, not one. A single stacked column turned the
             footer into a 15-screen scroll of one-word rows; the two QUICK LINKS
             lists are the natural pair and sit side by side at 360. The wide
             blocks (brand, connect, QR) span both. --}}
        <div class="grid grid-cols-2 {{ $dfCols }} gap-x-5 sm:gap-x-8 gap-y-8 items-start">

            <!-- Brand: the gold certification seal + paragraph -->
            <div class="col-span-2 lg:col-span-1 min-w-0">
                <div class="flex items-start gap-4">
                    <a href="{{ route('ca.page', ['lang' => $lang]) }}" class="shrink-0">
                        <img src="{{ brand_asset('mark') }}" alt="" class="w-[64px] h-[64px] sm:w-[87px] sm:h-[87px] object-contain">
                    </a>
                    <div class="min-w-0">
                        <a href="{{ route('ca.page', ['lang' => $lang]) }}" class="flex items-center min-h-[44px] md:min-h-0 md:block text-[14px] font-semibold text-white leading-snug hover:underline">
                            {{-- The artwork letters this seal "AHCA". That acronym is not
                                 this platform's — ProductCertificateTest lists it among the
                                 foreign identifiers no page may carry — so the block uses
                                 the certification authority's real name and links to it. --}}
                            {{ $isFr ? 'Artisan Hub 237 — Autorité de certification' : 'Artisan Hub 237 Certification Authority' }}
                        </a>
                        <p class="mt-1.5 text-[14px] text-[#E4EAE4] leading-snug">
                            {{ $isFr ? 'Un registre. Une vérité. Une confiance à vie.' : 'One Registry. One Truth. A Lifetime of Trust.' }}
                        </p>
                        <p class="mt-3.5 text-[12px] md:text-[10.5px] text-[#93A79B] leading-relaxed">
                            {{ $isFr
                               ? "Artisan Hub 237 est une plateforme privée dédiée à l'artisanat africain authentique. Nous vérifions, protégeons et faisons connaître les artisans et leur héritage culturel."
                               : 'Artisan Hub 237 is a private platform for authentic African craftsmanship. We verify, protect, and promote artisans and their cultural heritage.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- QUICK LINKS, column one -->
            <div class="min-w-0 lg:border-l lg:border-white/10 lg:pl-7">
                <h4 class="text-[13px] font-bold tracking-[0.06em] text-white uppercase mb-4">{{ $isFr ? 'Liens rapides' : 'Quick links' }}</h4>
                {{-- Row height, not just line height: a 12px link in a 21px row
                     is a 21px tap target. `min-h-[44px]` below `md` gives the
                     thumb the contract's floor; the artwork's tight 9px rhythm
                     returns on a pointer device. --}}
                <ul class="md:space-y-[9px] text-[13px] md:text-[12px] text-[#C6D0C7]">
                    @foreach($dfQuickLinksOne as $dfLabel => $dfHref)
                    <li><a href="{{ $dfHref }}" class="flex items-center min-h-[44px] md:min-h-0 hover:text-white transition-colors">{{ $dfLabel }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- QUICK LINKS, column two (no heading — it is the same list) -->
            <div class="min-w-0">
                {{-- Spacer heading: it keeps the two columns' first rows level
                     on the wide layout without repeating the word. On the
                     stacked layout it is gone entirely. --}}
                <h4 class="hidden lg:block lg:invisible text-[13px] font-bold tracking-[0.06em] text-white uppercase mb-4" aria-hidden="true">{{ $isFr ? 'Liens rapides' : 'Quick links' }}</h4>
                <ul class="md:space-y-[9px] text-[13px] md:text-[12px] text-[#C6D0C7]">
                    @foreach($dfQuickLinksTwo as $dfLabel => $dfHref)
                    <li><a href="{{ $dfHref }}" class="flex items-center min-h-[44px] md:min-h-0 hover:text-white transition-colors">{{ $dfLabel }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- CONNECT WITH US -->
            <div class="col-span-2 lg:col-span-1 min-w-0 lg:border-l lg:border-white/10 lg:pl-7">
                <h4 class="text-[13px] font-bold tracking-[0.06em] text-white uppercase mb-4">{{ $isFr ? 'Nous suivre' : 'Connect with us' }}</h4>

                @if(count($socialProfileUrls))
                <div class="flex flex-wrap items-center gap-2.5">
                    @foreach($socialProfileUrls as $socialName => $socialUrl)
                    @php [$socialBg, $socialPath] = $dfSocialIcons[$socialName]; @endphp
                    {{-- 44px disc on a phone, the artwork's 28px from `md` up. --}}
                    <a href="{{ $socialUrl }}" target="_blank" rel="noopener" aria-label="{{ $socialName }}"
                        class="w-11 h-11 md:w-[28px] md:h-[28px] rounded-full flex items-center justify-center text-white hover:opacity-85 transition-opacity"
                        style="background-color: {{ $socialBg }}">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 md:w-[15px] md:h-[15px]">{!! $socialPath !!}</svg>
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Phone and email come from config/legal.php. A blank one is
                     simply absent — the artwork's placeholder number is not a
                     number this company has. --}}
                @php $dfContactRow = 'flex items-center md:items-start gap-2.5 min-h-[44px] md:min-h-0 text-[13px] md:text-[12px] text-[#C6D0C7] hover:text-white transition-colors'; @endphp
                <div class="mt-2 md:mt-4 md:space-y-3">
                    @if($dfPhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $dfPhone) }}" class="{{ $dfContactRow }}">
                        <i data-lucide="phone" class="w-[15px] h-[15px] md:mt-px shrink-0 text-[#C9942E]"></i>
                        <span class="min-w-0 break-words">{{ $dfPhone }}</span>
                    </a>
                    @endif
                    @if($dfEmail)
                    <a href="mailto:{{ $dfEmail }}" class="{{ $dfContactRow }}">
                        <i data-lucide="mail" class="w-[15px] h-[15px] md:mt-px shrink-0 text-[#C9942E]"></i>
                        <span class="min-w-0 break-all">{{ $dfEmail }}</span>
                    </a>
                    @endif
                    <a href="{{ route('contact', ['lang' => $lang]) }}" class="{{ $dfContactRow }}">
                        <i data-lucide="message-circle" class="w-[15px] h-[15px] md:mt-px shrink-0 text-[#C9942E]"></i>
                        <span class="min-w-0">{{ $isFr ? 'Formulaire de contact' : 'Contact form' }}</span>
                    </a>
                </div>

                {{-- The newsletter this platform really runs. The artwork has no
                     newsletter block; removing a working subscription form from
                     every page to match a drawing would be a regression. --}}
                @if(session('newsletter_ok'))
                <p class="mt-4 flex items-start gap-2 text-[12px] font-semibold text-[#8FDCA8]">
                    <i data-lucide="circle-check" class="w-4 h-4 shrink-0"></i>
                    {{ session('newsletter_ok') }}
                </p>
                @else
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre email' : 'Your email' }}"
                        class="ui-field ui-field--invert ui-field--sm flex-1 min-w-0">
                    <button type="submit" class="inline-flex items-center justify-center min-h-[44px] md:min-h-0 bg-[#925104] hover:bg-[#A2700F] text-white text-[13px] md:text-[12px] font-semibold px-3.5 md:py-2 rounded-md transition-colors shrink-0">
                        {{ $isFr ? "S'abonner" : 'Subscribe' }}
                    </button>
                </form>
                @endif
            </div>

            {{-- SCAN TO VERIFY THIS ARTISAN.
                 Rendered only when a page supplied a real verification URL. The
                 code is generated by the vendored qrcode library from that exact
                 URL — it is never a picture of a QR. --}}
            @if($dfVerifyUrl)
            <div class="col-span-2 lg:col-span-1 min-w-0 lg:justify-self-end">
                <p class="text-[12.5px] font-bold tracking-[0.06em] text-white uppercase leading-tight text-center lg:text-right">
                    {{ $isFr ? 'Scannez pour vérifier' : 'Scan to verify' }}<br>
                    {{ $isFr ? 'cet artisan' : 'this artisan' }}
                </p>
                <a href="{{ $dfVerifyUrl }}" class="mt-2.5 block w-[105px] mx-auto lg:mr-0 bg-white dark:bg-[#12150F] rounded-md p-1.5"
                    aria-label="{{ $isFr ? 'Vérifier cet artisan' : 'Verify this artisan' }}">
                    <span id="df-verify-qr" class="block w-[93px] h-[93px]"
                          data-verify-url="{{ $dfVerifyUrl }}"></span>
                </a>
                @if($dfVerifyName)
                <p class="mt-2 text-[12px] md:text-[10.5px] text-[#93A79B] text-center lg:text-right break-words">{{ $dfVerifyName }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Bottom strip: copyright · language · country -->
        <div class="mt-6 pt-4 border-t border-white/10 flex flex-col lg:flex-row items-center justify-between gap-3 text-[12px] text-[#93A79B]">
            <span class="text-center lg:text-left">&copy; {{ date('Y') }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</span>
            <span class="text-center">{{ $isFr ? "Porté par l'artisanat africain. Protégé par la technologie." : 'Powered by African Craft. Protected by Technology.' }}</span>
            <div class="flex flex-wrap items-center justify-center gap-3">
                {{-- A real switch: each option is the current page in that
                     language, and choosing one navigates there. --}}
                <select aria-label="{{ $isFr ? 'Langue' : 'Language' }}"
                    onchange="if(this.value) window.location.href = this.value"
                    class="ui-field ui-select ui-field--invert ui-field--sm w-auto pr-8 cursor-pointer">
                    <option value="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" @selected($isFr)>Français</option>
                    <option value="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" @selected(! $isFr)>English</option>
                </select>
                <span class="flex items-center gap-2 whitespace-nowrap">
                    <span class="inline-flex h-[13px] w-[20px] overflow-hidden rounded-[2px]" aria-hidden="true">
                        <span class="flex-1 bg-[#007A5E]"></span><span class="flex-1 bg-[#CE1126]"></span><span class="flex-1 bg-[#FCD116]"></span>
                    </span>
                    {{ $isFr ? 'Cameroun' : 'Cameroon' }}
                </span>
            </div>
        </div>

        @if($dfShowLegalLinks)
        <div class="mt-3 flex flex-wrap items-center justify-center lg:justify-start gap-x-3 gap-y-1 text-[12px] md:text-[11.5px] text-[#93A79B]">
            @foreach(config('legal.documents') as $lgSlug => $lgDoc)
            @if(! $loop->first)<span class="text-white/20" aria-hidden="true">|</span>@endif
            <a href="{{ route('legal.show', ['doc' => $lgSlug, 'lang' => $lang]) }}" class="inline-flex items-center min-h-[44px] md:min-h-0 hover:text-white transition-colors">{{ $lgDoc['title'][$lang] }}</a>
            @endforeach
        </div>
        @endif

        {{-- Standing disclosure. Artisan Hub 237 is a private operator and is
             not a party to any sale made through the platform. --}}
        <p class="mt-3 text-[12px] md:text-[11px] text-[#7C917F] leading-relaxed">
            {{ $isFr
               ? 'Artisan Hub 237 est une plateforme privée et indépendante. Elle n\'est affiliée à aucun ministère ni organisme public, n\'est pas partie aux ventes conclues entre acheteurs et artisans et n\'en reçoit pas le prix ; seuls ses propres frais de service lui sont réglés.'
               : 'Artisan Hub 237 is a private, independent platform. It is not affiliated with any ministry or public body, is not a party to sales concluded between buyers and artisans and does not receive the price; only its own service fees are paid to it.' }}
        </p>
    </div>
</footer>

@if($dfVerifyUrl)
{{-- Real QR, drawn from the real URL by the vendored library. --}}
<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    (function () {
        var box = document.getElementById('df-verify-qr');
        if (!box || !window.QRCode) return;
        new QRCode(box, {
            text: box.dataset.verifyUrl,
            width: 93,
            height: 93,
            colorDark: '#0B2C1E',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M,
        });
    })();
</script>
@endif

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-[#12150F] border-t border-[#EEEDEA] dark:border-[#262B21] flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $dfBottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home'],
            ['href' => route('businesses.index', ['lang' => $lang]), 'icon' => 'compass', 'label' => $isFr ? 'Explorer' : 'Explore'],
            ['href' => $siacUser ? route('saved.index') : route('login', ['lang' => $lang]), 'icon' => 'heart', 'label' => $isFr ? 'Favoris' : 'Wishlist'],
            ['href' => $siacUser ? route('messages.inbox') : route('login', ['lang' => $lang]), 'icon' => 'message-circle', 'label' => 'Messages'],
            ['href' => $siacUser ? route('dashboard.siac') : route('login', ['lang' => $lang]), 'icon' => 'user', 'label' => $isFr ? 'Profil' : 'Profile'],
        ];
    @endphp
    @foreach($dfBottomTabs as $tab)
    {{-- 10px was below the contract's 12px mobile floor, and the row was under
         44px tall. Both are fixed here; the labels still fit five across at 360. --}}
    <a href="{{ $tab['href'] }}" class="flex-1 min-w-0 flex flex-col items-center justify-center gap-0.5 min-h-[52px] py-1.5 text-[#8A857A] dark:text-[#868778]">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5 shrink-0"></i>
        <span class="max-w-full truncate text-[12px] leading-none font-medium">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>
