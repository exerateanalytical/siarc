@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $id = request()->route()?->parameter('id');
    $s = null;
    try {
        $s = DB::table('speakers')->where('id', $id)->first(['name','role_fr','organization','photo','bio_fr','is_featured']);
    } catch (\Throwable $e) { $s = null; }

    // Resolve display fields — prefer live DB row, fall back to route-provided vars.
    $name = $sTitle ?? ($s->name ?? 'Intervenant');
    $role = $s->role_fr ?? ($sIntro ?? null);
    $org  = $s->organization ?? null;
    $bio  = $s->bio_fr ?? null;
    $featured = (bool)($s->is_featured ?? false);

    // Initials for the heritage avatar.
    $parts = preg_split('/\s+/', trim(preg_replace('/(Dr\.?|Pr\.?|Mme|M\.)/i', '', $name)));
    $initials = strtoupper(mb_substr($parts[0] ?? 'S', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : ''));

    // Interventions from $sTables (ONE table: cols[Activité,Horaire,Lieu]).
    $interv = [];
    if (isset($sTables) && is_array($sTables)) {
        $t = $sTables[0] ?? null;
        if ($t && !empty($t['rows'])) $interv = $t['rows'];
    }
@endphp

{{-- ══════════════════ HERO BAND ══════════════════ --}}
<section class="siarc-adire text-white relative overflow-hidden border-b border-[#0A2E17]">
    <div class="siarc-kente-v absolute right-0 top-0 bottom-0 opacity-80" style="transform:scaleX(-1)"></div>
    <div class="max-w-[1180px] mx-auto px-6 sm:px-10 pt-8 pb-14">
        {{-- breadcrumb --}}
        <nav class="flex items-center gap-2 text-[12px] text-white/60 mb-8 siarc-in">
            <a href="{{ $h('siarc.home') }}" class="hover:text-white transition-colors">SIARC 2026</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ $h('siarc.speakers') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Intervenants' : 'Speakers' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="text-white/90">{{ $name }}</span>
        </nav>

        <div class="grid lg:grid-cols-[220px_1fr] gap-8 lg:gap-10 items-start siarc-in">
            {{-- avatar --}}
            <div class="relative">
                @if(!empty($s->photo))
                    <img src="{{ asset('images/siarc/'.$s->photo) }}" alt="{{ $name }}"
                         class="w-full max-w-[220px] aspect-[4/5] object-cover rounded-2xl siarc-shadow-lg border-4 border-white/10">
                @else
                    <div class="w-full max-w-[220px] aspect-[4/5] rounded-2xl siarc-shadow-lg border-4 border-white/10 bg-gradient-to-br from-[#157A43] to-[#0B3A1E] flex items-center justify-center">
                        <span class="font-display text-[64px] font-extrabold text-white/90">{{ $initials }}</span>
                    </div>
                @endif
                @if($featured)
                <span class="absolute -bottom-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 bg-siarc-gold text-[#3a2a00] text-[11px] font-bold px-3 py-1.5 rounded-full siarc-shadow whitespace-nowrap">
                    <i data-lucide="star" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Intervenant vedette' : 'Featured speaker' }}
                </span>
                @endif
            </div>

            {{-- identity --}}
            <div class="pt-1">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <h1 class="font-display text-[34px] sm:text-[42px] font-extrabold leading-[1.05] tracking-tight">{{ $name }}</h1>
                    <span class="inline-flex items-center gap-1.5 bg-[#0F3D22] text-[#7EDCA0] border border-[#1E5C36] text-[11px] font-semibold px-3 py-1 rounded-full">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Confirmé' : 'Confirmed' }}
                    </span>
                </div>
                @if($role)
                    <p class="text-[16px] font-semibold text-siarc-gold mb-1">{{ $role }}</p>
                @endif
                @if($org)
                    <p class="inline-flex items-center gap-2 text-[13px] text-white/75">
                        <i data-lucide="building-2" class="w-4 h-4 text-white/50"></i>{{ $org }}
                    </p>
                @endif

                {{-- meta chips --}}
                <div class="flex flex-wrap gap-3 mt-7">
                    <span class="inline-flex items-center gap-2.5 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5">
                        <i data-lucide="mic" class="w-5 h-5 text-siarc-gold shrink-0"></i>
                        <span class="leading-tight"><span class="block text-[10px] text-white/50 tracking-wide">{{ $isFr ? 'Interventions' : 'Sessions' }}</span><span class="block text-[13px] font-semibold">{{ count($interv) }} {{ $isFr ? 'session'.(count($interv) > 1 ? 's' : '') : 'session'.(count($interv) > 1 ? 's' : '') }}</span></span>
                    </span>
                    <span class="inline-flex items-center gap-2.5 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-siarc-gold shrink-0"></i>
                        <span class="leading-tight"><span class="block text-[10px] text-white/50 tracking-wide">{{ $isFr ? 'Statut' : 'Status' }}</span><span class="block text-[13px] font-semibold">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</span></span>
                    </span>
                    @if($org)
                    <span class="inline-flex items-center gap-2.5 bg-white/5 border border-white/10 rounded-xl px-4 py-2.5">
                        <i data-lucide="handshake" class="w-5 h-5 text-siarc-gold shrink-0"></i>
                        <span class="leading-tight"><span class="block text-[10px] text-white/50 tracking-wide">{{ $isFr ? 'Organisation' : 'Organization' }}</span><span class="block text-[13px] font-semibold">{{ $org }}</span></span>
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ BODY ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1180px] mx-auto px-6 sm:px-10 py-14 grid lg:grid-cols-[1fr_360px] gap-6">

        {{-- LEFT: Biographie + Interventions --}}
        <div class="space-y-6">
            {{-- Biographie --}}
            <div class="siarc-card siarc-shadow p-7 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center"><i data-lucide="user" class="w-5 h-5 text-siarc-green"></i></span>
                    <h2 class="font-display text-[22px] font-bold text-[#1A1712]">{{ $isFr ? 'Biographie' : 'Biography' }}</h2>
                </div>
                @if($bio)
                    <p class="text-[14px] text-[#55524A] leading-[1.75]">{{ $bio }}</p>
                @else
                    <p class="text-[13.5px] text-[#8A857A] italic">{{ $isFr ? 'Biographie à venir.' : 'Biography coming soon.' }}</p>
                @endif
            </div>

            {{-- Interventions --}}
            <div class="siarc-card siarc-shadow p-7 sm:p-8">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center"><i data-lucide="calendar-days" class="w-5 h-5 text-siarc-green"></i></span>
                        <h2 class="font-display text-[22px] font-bold text-[#1A1712]">{{ $isFr ? 'Interventions' : 'Sessions' }}</h2>
                    </div>
                    <span class="text-[12px] font-semibold text-siarc-green bg-[#EAF4EE] px-3 py-1 rounded-full">{{ count($interv) }}</span>
                </div>

                @if(count($interv))
                    <div class="space-y-4">
                        @foreach($interv as $row)
                        @php
                            $cells = is_array($row) ? ($row['cells'] ?? array_values($row)) : (array)$row;
                            $activite = $cells[0] ?? '';
                            $horaire  = is_array($cells[1] ?? null) ? '' : ($cells[1] ?? '');
                            $lieu     = is_array($cells[2] ?? null) ? '' : ($cells[2] ?? '');
                        @endphp
                        <div class="flex gap-4 rounded-xl border border-[#EFEDE6] p-4 siarc-lift bg-white">
                            <span class="w-11 h-11 rounded-xl bg-[#F3F0E7] flex items-center justify-center shrink-0">
                                <i data-lucide="presentation" class="w-5 h-5 text-siarc-ochre"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[14.5px] font-semibold text-[#1A1712] leading-snug">{{ $activite }}</p>
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-2">
                                    @if($horaire)
                                    <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#8A857A]"><i data-lucide="clock" class="w-4 h-4 text-siarc-green"></i>{{ $horaire }}</span>
                                    @endif
                                    @if($lieu)
                                    <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#8A857A]"><i data-lucide="map-pin" class="w-4 h-4 text-siarc-green"></i>{{ $lieu }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <span class="w-14 h-14 mx-auto rounded-2xl bg-[#F3F0E7] flex items-center justify-center mb-3"><i data-lucide="calendar-clock" class="w-7 h-7 text-[#B7B2A6]"></i></span>
                        <p class="text-[13.5px] font-semibold text-[#55524A]">{{ $isFr ? 'Aucune intervention programmée' : 'No scheduled sessions' }}</p>
                        <p class="text-[12.5px] text-[#8A857A] mt-1">{{ $isFr ? 'Le programme sera bientôt disponible.' : 'The programme will be available soon.' }}</p>
                    </div>
                @endif
            </div>

            <a href="{{ $h('siarc.speakers') }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-siarc-green hover:gap-3 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour aux intervenants' : 'Back to speakers' }}
            </a>
        </div>

        {{-- RIGHT: side rail --}}
        <aside class="space-y-6">
            @if($org)
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Organisation' : 'Organization' }}</h3>
                <div class="flex items-center gap-3">
                    <span class="w-11 h-11 rounded-xl siarc-adire flex items-center justify-center shrink-0"><i data-lucide="building-2" class="w-5 h-5 text-siarc-gold"></i></span>
                    <p class="text-[14px] font-semibold text-[#1A1712] leading-snug">{{ $org }}</p>
                </div>
            </div>
            @endif

            {{-- CTA card --}}
            <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-[#14652F] to-[#042B15] text-white p-6 relative">
                <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
                <h3 class="font-display text-[19px] font-bold mb-2 mt-3">{{ $isFr ? 'Ne manquez pas ses interventions' : 'Don\'t miss the sessions' }}</h3>
                <p class="text-[12.5px] text-white/75 leading-relaxed mb-5">{{ $isFr ? 'Réservez votre place et vivez le SIARC 2026 au Musée National de Yaoundé.' : 'Book your place and experience SIARC 2026 at the National Museum of Yaoundé.' }}</p>
                <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-primary px-5 py-2.5 text-[12.5px]">{{ $isFr ? 'S\'inscrire' : 'Register' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>

            {{-- event info --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations' : 'Information' }}</h3>
                <div class="space-y-3.5 text-[13px]">
                    <span class="flex items-center gap-3 text-[#55524A]"><i data-lucide="calendar" class="w-4.5 h-4.5 text-siarc-ochre shrink-0"></i>27 Juillet – 05 Août 2026</span>
                    <span class="flex items-center gap-3 text-[#55524A]"><i data-lucide="map-pin" class="w-4.5 h-4.5 text-siarc-ochre shrink-0"></i>Musée National de Yaoundé</span>
                    <span class="flex items-center gap-3 text-[#55524A]"><i data-lucide="mic" class="w-4.5 h-4.5 text-siarc-ochre shrink-0"></i>120+ {{ $isFr ? 'sessions & ateliers' : 'sessions & workshops' }}</span>
                </div>
            </div>
        </aside>
    </div>
</section>
