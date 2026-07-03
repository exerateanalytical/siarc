@php
    $isFr = $lang === 'fr';

    $name = $isFr ? $event->name_fr : ($event->name_en ?? $event->name_fr);
    $descriptionText = $isFr ? $event->description_fr : ($event->description_en ?? $event->description_fr);
    $location = $isFr ? $event->location_fr : ($event->location_en ?? $event->location_fr);

    $frMonths = [1 => 'JANV', 'FÉVR', 'MARS', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOÛT', 'SEPT', 'OCT', 'NOV', 'DÉC'];
    $enMonths = [1 => 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    $day = $event->starts_at->format('d');
    $monthAbbr = ($isFr ? $frMonths : $enMonths)[(int) $event->starts_at->format('n')];
    $year = $event->starts_at->format('Y');
    $timeFrom = $event->starts_at->format('H:i');
    $timeTo = $event->ends_at?->format('H:i') ?? '18:00';

    $eventMeta = [
        'journees-nationales-artisanat-camerounais-2025' => [
            'city' => 'Yaoundé, Centre', 'venue' => 'Palais des Congrès de Yaoundé',
            'badge' => $isFr ? 'Événement national' : 'National event', 'badgeColor' => '#C1272D',
            'free' => true, 'price' => null,
        ],
        'festival-arts-traditions-bamoun' => [
            'city' => 'Foumban, Ouest', 'venue' => 'Palais Royal de Foumban',
            'badge' => $isFr ? 'Festival culturel' : 'Cultural festival', 'badgeColor' => '#E9A825',
            'free' => false, 'price' => '2 000 FCFA',
        ],
        'atelier-poterie-traditionnelle' => [
            'city' => 'Maroua, Extrême-Nord', 'venue' => 'Centre d\'Artisanat de Maroua',
            'badge' => $isFr ? 'Atelier & Formation' : 'Workshop & Training', 'badgeColor' => '#0E5A2F',
            'free' => false, 'price' => '5 000 FCFA',
        ],
        'marche-createurs-eco-responsables' => [
            'city' => 'Douala, Littoral', 'venue' => 'Place des Fêtes de Douala',
            'badge' => $isFr ? 'Marché & Foire' : 'Market & Fair', 'badgeColor' => '#E9A825',
            'free' => true, 'price' => null,
        ],
        'conference-artisanat-developpement-durable' => [
            'city' => 'Yaoundé, Centre', 'venue' => 'Institut Français du Cameroun',
            'badge' => $isFr ? 'Conférence' : 'Conference', 'badgeColor' => '#C1272D',
            'free' => false, 'price' => '3 000 FCFA',
        ],
        'prix-national-jeune-artisan-2025' => [
            'city' => 'Yaoundé, Centre', 'venue' => 'Palais des Congrès de Yaoundé',
            'badge' => $isFr ? 'Concours & Prix' : 'Competition & Award', 'badgeColor' => '#E9A825',
            'free' => true, 'price' => null,
        ],
    ];
    $locParts = array_map('trim', explode(',', (string) $location));
    $meta = $eventMeta[$event->slug] ?? [
        'city' => count($locParts) > 1 ? end($locParts) : ($location ?: '—'),
        'venue' => $locParts[0] ?? ($location ?: '—'),
        'badge' => $isFr ? 'Événement' : 'Event', 'badgeColor' => '#0E5A2F',
        'free' => true, 'price' => null,
    ];

    $ticketIds = ['journees-nationales-artisanat-camerounais-2025' => 'GVC-2025-00012345'];
    $ticketId = $ticketIds[$event->slug] ?? ('GVC-' . $year . '-' . str_pad((string) ($event->id * 617), 8, '0', STR_PAD_LEFT));

    $chips = [
        ['ticket-chip-1.png', 'EXPOSITIONS'],
        ['ticket-chip-2.png', $isFr ? 'CONFÉRENCES' : 'CONFERENCES'],
        ['ticket-chip-3.png', 'ATELIERS'],
        ['ticket-chip-4.png', 'NETWORKING'],
        ['ticket-chip-5.png', $isFr ? "CONCOURS\n& PRIX" : "AWARDS &\nCONTESTS"],
    ];
    $ticketStats = [
        ['500+', $isFr ? 'Participants attendus' : 'Expected participants'],
        ['50+',  $isFr ? 'Exposants' : 'Exhibitors'],
        ['20+',  $isFr ? 'Ateliers & Conférences' : 'Workshops & Conferences'],
        ['10+',  $isFr ? 'Régions représentées' : 'Regions represented'],
    ];
    $features = [
        ['ticket-feat-1.png', $isFr ? 'Imprimable' : 'Printable',
         $isFr ? "Imprimez ce ticket et\nprésentez-le à l'entrée" : "Print this ticket and\npresent it at the entrance"],
        ['ticket-feat-2.png', 'Mobile',
         $isFr ? "Présentez ce QR code\ndepuis votre téléphone" : "Show this QR code\nfrom your phone"],
        ['ticket-feat-3.png', $isFr ? 'Sécurisé' : 'Secure',
         $isFr ? "Ticket unique et\ninfalsifiable" : "Unique, tamper-proof\nticket"],
        ['ticket-feat-4.png', $isFr ? 'Éco-responsable' : 'Eco-friendly',
         $isFr ? "Préférez le digital,\npréservons notre planète" : "Go digital,\nlet's protect our planet"],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Billet' : 'Ticket' }} — {{ $name }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { leaf: '#164C28', gold: '#E5A82E' },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        .barcode-dark {
            background-image: repeating-linear-gradient(90deg,
                #1D1B16 0 2px, transparent 2px 4px,
                #1D1B16 4px 5px, transparent 5px 8px,
                #1D1B16 8px 11px, transparent 11px 13px,
                #1D1B16 13px 14px, transparent 14px 18px);
        }
        .perforation {
            background-image: repeating-linear-gradient(180deg,
                #C9C4BA 0 7px, transparent 7px 14px);
            width: 2px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            #ticket-card { box-shadow: none !important; border: 1px solid #DDD; }
        }
    </style>
</head>
<body class="bg-[#FDFDFC] text-[#1D1B16] antialiased">

<div class="max-w-[1420px] mx-auto px-4 sm:px-6 py-8">

    <!-- Actions -->
    <div class="no-print flex items-center justify-center gap-4">
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2.5 bg-[#0B3D26] hover:bg-leaf text-white text-[13px] font-semibold tracking-[0.04em] uppercase px-6 h-[46px] rounded-lg transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i>
            {{ $isFr ? 'Télécharger (PDF)' : 'Download (PDF)' }}
        </button>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2.5 bg-white border border-[#DDD9D0] hover:border-leaf hover:text-leaf text-[#1D1B16] text-[13px] font-semibold tracking-[0.04em] uppercase px-6 h-[46px] rounded-lg transition-colors">
            <i data-lucide="printer" class="w-4 h-4"></i>
            {{ $isFr ? 'Imprimer' : 'Print' }}
        </button>
    </div>

    <!-- Ticket -->
    <div id="ticket-card" class="mt-7 flex flex-col xl:flex-row rounded-[22px] overflow-hidden shadow-[0_8px_30px_rgba(0,0,0,0.14)] bg-white">

        <!-- Main panel -->
        <div class="flex-1 min-w-0 flex flex-col">
            <div class="relative flex-1 flex">
                <!-- Left date column -->
                <div class="relative w-[128px] sm:w-[168px] shrink-0 bg-[#06301A] overflow-hidden">
                    <div class="absolute inset-0 bg-repeat opacity-90" style="background-image:url('{{ asset('images/landing/ticket-pattern.png') }}')"></div>
                    <img src="{{ asset('images/landing/ticket-swoosh.png') }}" alt="" class="absolute bottom-0 left-0 w-full h-auto" aria-hidden="true">
                    <div class="relative px-4 sm:px-6 pt-24 text-center sm:text-left">
                        <p class="text-[44px] sm:text-[52px] font-bold leading-none text-white">{{ $day }}</p>
                        <p class="mt-1 text-[20px] sm:text-[24px] font-bold tracking-[0.04em] text-white">{{ $monthAbbr }}</p>
                        <p class="text-[20px] sm:text-[24px] font-bold text-[#E5A82E]">{{ $year }}</p>
                        <span class="block mt-4 w-[64px] h-[2px] bg-[#C9942E] mx-auto sm:mx-0"></span>
                        <p class="mt-4 flex items-center justify-center sm:justify-start gap-2 text-[14px] font-semibold text-white">
                            <i data-lucide="clock" class="w-4 h-4 text-[#E5A82E]"></i>
                            {{ $timeFrom }}
                        </p>
                        <p class="text-[12px] text-white/85 sm:pl-6">à {{ $timeTo }}</p>
                        <p class="mt-4 flex items-start justify-center sm:justify-start gap-2 text-[11.5px] font-bold text-white uppercase leading-snug">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#E5A82E] mt-px shrink-0"></i>
                            {{ $meta['city'] }}
                        </p>
                        <p class="mt-1 text-[11px] text-white/85 leading-snug sm:pl-6">{{ $meta['venue'] }}</p>
                    </div>
                </div>

                <!-- Curved divider -->
                <svg class="absolute left-[120px] sm:left-[158px] inset-y-0 h-full w-[60px] text-white hidden sm:block" viewBox="0 0 60 100" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M60 0 H30 C0 20 0 80 30 100 H60 Z" fill="currentColor"/>
                </svg>

                <!-- Center content -->
                <div class="relative flex-1 min-w-0 bg-white">
                    <img src="{{ asset('images/landing/ticket-art.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto object-cover hidden md:block pointer-events-none select-none" aria-hidden="true">
                    <div class="relative p-6 sm:p-8 max-w-[560px]">
                        <div class="flex items-center gap-3.5">
                            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain">
                            <span class="leading-tight">
                                <span class="block text-[12.5px] font-bold tracking-[0.04em] text-[#1D1B16] uppercase">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                                <span class="block text-[12.5px] font-bold tracking-[0.04em] text-[#1D1B16] uppercase">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                                <span class="block text-[10px] text-[#6F6B60] mt-0.5">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                            </span>
                        </div>

                        <span class="mt-5 inline-block text-[11px] font-bold tracking-[0.08em] uppercase text-white rounded-md px-3.5 py-1.5" style="background-color: {{ $meta['badgeColor'] }}">{{ $meta['badge'] }}</span>
                        <h1 class="mt-3.5 text-[26px] sm:text-[31px] font-bold uppercase leading-tight text-[#0B3D26]">{{ $name }}</h1>
                        @if($descriptionText)
                        <p class="mt-3 text-[13px] text-[#3A3A35] leading-relaxed">{{ $descriptionText }}</p>
                        @endif

                        <div class="mt-6 flex items-start divide-x divide-[#E7E3DA]">
                            @foreach($chips as [$chipImg, $chipLabel])
                            <div class="px-3 first:pl-0 flex flex-col items-center text-center">
                                <img src="{{ asset('images/landing/' . $chipImg) }}" alt="" class="h-[40px] w-auto object-contain">
                                <p class="mt-2 text-[8.5px] font-bold tracking-[0.05em] text-[#1D1B16] whitespace-pre-line leading-tight">{{ $chipLabel }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-6 bg-white border border-[#E3DFD6] rounded-xl px-4 py-3 shadow-sm">
                            <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-[#EBE7DE]">
                                @foreach($ticketStats as [$tsValue, $tsLabel])
                                <div class="px-3 first:pl-1 text-center">
                                    <p class="text-[17px] font-bold text-[#1D1B16]">{{ $tsValue }}</p>
                                    <p class="mt-0.5 text-[9.5px] text-[#6F6B60] leading-tight">{{ $tsLabel }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact bar -->
            <div class="bg-[#06301A] px-6 py-3.5 flex flex-wrap items-center gap-x-8 gap-y-2">
                <span class="flex items-center gap-2.5 text-[12px] text-white"><i data-lucide="globe" class="w-4 h-4 text-[#E5A82E]"></i>www.galerieartisanat.cm</span>
                <span class="flex items-center gap-2.5 text-[12px] text-white"><i data-lucide="mail" class="w-4 h-4 text-[#E5A82E]"></i>contact@galerieartisanat.cm</span>
                <span class="flex items-center gap-2.5 text-[12px] text-white"><i data-lucide="phone" class="w-4 h-4 text-[#E5A82E]"></i>+237 670 416 238</span>
                <span class="ml-auto flex items-center gap-2">
                    @foreach(['facebook' => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>', 'instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>', 'linkedin' => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>', 'youtube' => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>'] as $sKey => $sPath)
                    <span class="w-7 h-7 rounded-full border border-white/35 flex items-center justify-center text-white">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">{!! $sPath !!}</svg>
                    </span>
                    @endforeach
                </span>
            </div>
        </div>

        <!-- Perforation -->
        <div class="hidden xl:block perforation my-4" aria-hidden="true"></div>
        <div class="xl:hidden h-[2px] mx-4 barcode-dark opacity-20" aria-hidden="true"></div>

        <!-- Stub -->
        <div class="xl:w-[352px] shrink-0 flex flex-col bg-white">
            <div class="bg-[#06301A] px-5 py-3 flex items-center justify-center gap-3 rounded-tr-[22px]">
                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-[#E5A82E]"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
                <span class="text-[13.5px] font-bold tracking-[0.08em] text-white uppercase">{{ $isFr ? 'Votre ticket' : 'Your ticket' }}</span>
                <svg viewBox="0 0 24 24" class="w-3.5 h-3.5 fill-[#E5A82E]"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
            </div>
            <div class="flex-1 px-5 py-5">
                <div class="flex items-center gap-4">
                    <div id="qr-box" class="w-[96px] h-[96px] shrink-0"></div>
                    <span class="self-stretch w-px bg-[#E3DFD6]"></span>
                    <div>
                        <p class="text-[12px] font-bold tracking-[0.06em] uppercase text-[#1D1B16]">{{ $isFr ? 'Entrée' : 'Entry' }}</p>
                        @if($meta['free'])
                        <p class="text-[21px] font-bold uppercase text-[#C1272D] leading-tight">{{ $isFr ? 'Gratuite' : 'Free' }}</p>
                        @else
                        <p class="text-[21px] font-bold text-[#C1272D] leading-tight">{{ $meta['price'] }}</p>
                        @endif
                        <p class="mt-1 text-[10.5px] text-[#6F6B60] leading-snug">{{ $isFr ? 'Inscription obligatoire en ligne' : 'Online registration required' }}</p>
                    </div>
                </div>

                <div class="mt-4 border-t border-dashed border-[#D8D4CA]"></div>

                <ul class="mt-4 space-y-3.5">
                    <li class="flex items-start gap-3">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#0B3D26] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[10.5px] font-bold tracking-[0.06em] uppercase text-[#0B3D26]">Date</p>
                            <p class="text-[12.5px] text-[#1D1B16]">{{ $day }} {{ $monthAbbr }} {{ $year }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-[#0B3D26] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[10.5px] font-bold tracking-[0.06em] uppercase text-[#0B3D26]">{{ $isFr ? 'Heure' : 'Time' }}</p>
                            <p class="text-[12.5px] text-[#1D1B16]">{{ $timeFrom }} - {{ $timeTo }} (GMT+1)</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#0B3D26] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[10.5px] font-bold tracking-[0.06em] uppercase text-[#0B3D26]">{{ $isFr ? 'Lieu' : 'Venue' }}</p>
                            <p class="text-[12.5px] text-[#1D1B16] leading-snug">{{ $meta['venue'] }}<br>{{ $meta['city'] }} - {{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="ticket" class="w-4 h-4 text-[#0B3D26] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[10.5px] font-bold tracking-[0.06em] uppercase text-[#0B3D26]">Type</p>
                            <p class="text-[12.5px] text-[#1D1B16] uppercase">{{ $isFr ? 'Entrée générale' : 'General admission' }}</p>
                        </div>
                    </li>
                </ul>

                <p class="mt-4 text-[10.5px] font-bold tracking-[0.06em] uppercase text-[#6F6B60]">Ticket ID</p>
                <p class="text-[15px] font-bold text-[#1D1B16] tracking-[0.02em]">{{ $ticketId }}</p>

                <div class="mt-3 h-[46px] barcode-dark" aria-hidden="true"></div>
            </div>
            <div class="bg-[#06301A] px-5 py-3 flex items-center justify-center gap-3 rounded-br-[22px] xl:rounded-br-[22px]">
                <svg viewBox="0 0 24 24" class="w-3 h-3 fill-[#E5A82E]"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
                <span class="text-[10px] font-bold tracking-[0.08em] text-white uppercase text-center leading-snug">{{ $isFr ? "Merci de soutenir\nl'artisanat camerounais" : "Thank you for supporting\nCameroonian craftsmanship" }}</span>
                <svg viewBox="0 0 24 24" class="w-3 h-3 fill-[#E5A82E]"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
            </div>
        </div>
    </div>

    <!-- Feature row -->
    <div class="no-print mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-[1240px] mx-auto">
        @foreach($features as [$ftImg, $ftTitle, $ftSub])
        <div class="flex items-start gap-4 justify-center">
            <img src="{{ asset('images/landing/' . $ftImg) }}" alt="" class="w-[46px] h-auto object-contain shrink-0">
            <div class="leading-tight">
                <p class="text-[13px] font-bold tracking-[0.04em] uppercase text-[#1D1B16]">{{ $ftTitle }}</p>
                <p class="mt-1.5 text-[11.5px] text-[#6F6B60] whitespace-pre-line leading-relaxed">{{ $ftSub }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="no-print mt-8 text-center">
        <a href="{{ route('events.show', ['slug' => $event->slug, 'lang' => $lang]) }}" class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#14532D] hover:underline">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ $isFr ? 'Retour à l\'événement' : 'Back to the event' }}
        </a>
    </div>
</div>

<script>
    lucide.createIcons();
    new QRCode(document.getElementById('qr-box'), {
        text: @json(route('events.show', ['slug' => $event->slug]) . '?ticket=' . $ticketId),
        width: 96, height: 96,
        colorDark: '#1D1B16', colorLight: '#FFFFFF',
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
</body>
</html>
