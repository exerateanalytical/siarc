{{-- Buyer quote-flow sidebar (designs: "create un demande.png" / "quote propositions.png" /
     "accepte le devis.png" / "comparison de version.png" / "bonne de demand.png").
     Expects: $lang, $isFr, $siacUser, $messageCount.
     Options: $qbCompanyFirst (bool), $qbNavOverride (rows: [icon, label, url, active, badge, badgeStyle]
     or group rows: [icon, label, 'group', children[]] where children are [label, url, active, badge, badgeStyle]). --}}
@php
    $qbName = $siacUser['name'] ?? 'Jean Dupont';
    $qbInitials = strtoupper(collect(explode(' ', trim($qbName)))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    $qbMsgBadge = ($messageCount ?? 0) > 0 ? $messageCount : 3;
    $qbBadgeCls = [
        'red'    => 'bg-[#E01E1E] text-white',
        'green'  => 'bg-[#DFF0E4] text-[#14652F]',
        'orange' => 'bg-[#FDF0DC] text-[#E8890C]',
    ];
    $qbNav = [
        ['house',          $isFr ? 'Tableau de bord' : 'Dashboard',                 route('dashboard.buyer', ['lang' => $lang]),   false, null, null],
        ['search',         $isFr ? 'Rechercher des produits' : 'Search products',   route('products.index', ['lang' => $lang]),    false, null, null],
        ['building-2',     $isFr ? 'Artisans & Entreprises' : 'Artisans & Businesses', route('businesses.index', ['lang' => $lang]), false, null, null],
        ['heart',          $isFr ? 'Mes Favoris' : 'My Favourites',                 route('saved.index', ['lang' => $lang]),       false, null, null],
        ['file-text',      $isFr ? 'Mes Demandes & Devis' : 'My Requests & Quotes', route('quotes.index', ['lang' => $lang]),      true,  null, null],
        ['shopping-bag',   $isFr ? 'Mes Commandes' : 'My Orders',                   route('messages.inbox', ['lang' => $lang]),    false, null, null],
        ['message-circle', 'Messages',                                              route('messages.inbox', ['lang' => $lang]),    false, (string) $qbMsgBadge, 'green'],
        ['bell',           'Notifications',                                         route('notifications.index', ['lang' => $lang]), false, '12', 'red'],
        ['file-text',      'Documents',                                             route('membership.certificate', ['lang' => $lang]), false, null, null],
        ['map-pin',        $isFr ? 'Adresses' : 'Addresses',                        route('profile.show', ['lang' => $lang]),      false, null, null],
        ['settings',       $isFr ? 'Paramètres du compte' : 'Account settings',     route('profile.show', ['lang' => $lang]),      false, null, null],
    ];
@endphp
<aside id="qb-sidebar" class="lg:w-[264px] shrink-0 bg-white border-r border-[#EEEFEE]">
    <div class="px-4 pt-5 pb-8">
        <div class="border border-[#EDEEED] bg-[#FBFBFA] rounded-2xl px-4 py-4 flex items-center gap-3.5">
            <span class="w-[54px] h-[54px] shrink-0 rounded-full bg-[#DFEDE3] flex items-center justify-center text-[19px] font-semibold text-[#14652F]">{{ $qbInitials }}</span>
            <span class="min-w-0">
                @if(!empty($qbCompanyFirst))
                <span class="block text-[14px] font-bold text-[#1B1B18] leading-snug">Achat Pro SARL</span>
                <span class="block mt-0.5 text-[12px] text-[#6F6B60]">{{ $qbName }}</span>
                @else
                <span class="block text-[14px] font-bold text-[#1B1B18] leading-snug">{{ $qbName }}</span>
                <span class="block mt-0.5 text-[12px] text-[#6F6B60]">Achat Pro SARL</span>
                @endif
                <span class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]">
                    <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Acheteur vérifié' : 'Verified buyer' }}
                </span>
            </span>
        </div>

        <nav class="mt-5 space-y-0.5">
            @foreach($qbNavOverride ?? $qbNav as $qbRow)
            @if(($qbRow[2] ?? null) === 'group')
            <div>
                <span class="flex items-center gap-3.5 rounded-xl px-3.5 py-[11px]">
                    <i data-lucide="{{ $qbRow[0] }}" class="w-[19px] h-[19px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                    <span class="flex-1 text-[13.5px] text-[#3B382F]">{{ $qbRow[1] }}</span>
                    <i data-lucide="chevron-up" class="w-4 h-4 shrink-0 text-[#8A857A]"></i>
                </span>
                <div class="space-y-0.5">
                    @foreach($qbRow[3] as [$qcLabel, $qcUrl, $qcActive, $qcBadge, $qcBadgeStyle])
                    <a href="{{ $qcUrl }}" class="flex items-center gap-3.5 rounded-xl pl-[46px] pr-3.5 py-[9px] {{ $qcActive ? 'bg-[#E7F1EA]' : 'hover:bg-[#F6F7F6]' }}">
                        <span class="flex-1 text-[13px] {{ $qcActive ? 'font-bold text-[#14652F]' : 'text-[#55524A]' }}">{{ $qcLabel }}</span>
                        @if($qcBadge)<span class="shrink-0 min-w-[24px] h-[22px] rounded-full text-[11px] font-bold flex items-center justify-center px-1.5 {{ $qbBadgeCls[$qcBadgeStyle] ?? $qbBadgeCls['green'] }}">{{ $qcBadge }}</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>
            @else
            @php [$qbIcon, $qbLabel, $qbUrl, $qbIsActive, $qbBadge, $qbBadgeStyle] = $qbRow; @endphp
            <a href="{{ $qbUrl }}" class="flex items-center gap-3.5 rounded-xl px-3.5 py-[11px] {{ $qbIsActive ? 'bg-[#E7F1EA]' : 'hover:bg-[#F6F7F6]' }}">
                <i data-lucide="{{ $qbIcon }}" class="w-[19px] h-[19px] shrink-0 {{ $qbIsActive ? 'text-[#14652F]' : 'text-[#3B382F]' }}" style="stroke-width:1.7"></i>
                <span class="flex-1 text-[13.5px] {{ $qbIsActive ? 'font-bold text-[#14652F]' : 'text-[#3B382F]' }}">{{ $qbLabel }}</span>
                @if($qbBadge)
                <span class="shrink-0 min-w-[24px] h-[22px] rounded-full text-[11px] font-bold flex items-center justify-center px-1.5 {{ $qbBadgeCls[$qbBadgeStyle] ?? $qbBadgeCls['green'] }}">{{ $qbBadge }}</span>
                @endif
            </a>
            @endif
            @endforeach
        </nav>

        <div class="mt-8 bg-[#EFF5F0] rounded-2xl px-4 py-4">
            <p class="flex items-center gap-2.5 text-[13.5px] font-bold text-[#14652F]">
                <i data-lucide="shield-check" class="w-[19px] h-[19px]" style="stroke-width:1.8"></i>
                {{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}
            </p>
            <p class="mt-2 text-[12px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Notre équipe est là pour vous accompagner dans vos achats.' : 'Our team is here to support you in your purchases.' }}</p>
            <a href="{{ route('support.index', ['lang' => $lang]) }}" class="mt-3.5 inline-flex items-center gap-2.5 bg-[#0A4D2E] hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[12.5px] font-semibold text-white transition-colors">
                <i data-lucide="headphones" class="w-4 h-4" style="stroke-width:1.7"></i>
                {{ $isFr ? 'Contacter le support' : 'Contact support' }}
            </a>
        </div>
    </div>
</aside>
