@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
@endphp

{{-- ── Newsletter band ────────────────────────────────────────────────────── --}}
<section class="relative bg-gradient-to-r from-[#C97A16] to-[#E6B201] overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 opacity-60"></div>
    <div class="siarc-kente-v absolute right-0 top-0 opacity-60"></div>
    <div class="max-w-[1240px] mx-auto px-6 py-7 flex flex-col md:flex-row items-center gap-5">
        <div class="flex items-center gap-4 md:w-[42%]">
            <span class="w-12 h-12 rounded-2xl bg-white/25 flex items-center justify-center shrink-0"><i data-lucide="mail" class="w-6 h-6 text-white"></i></span>
            <div>
                <p class="font-display text-[20px] font-bold text-white leading-tight">{{ $isFr ? 'Restez informé' : 'Stay informed' }}</p>
                <p class="text-[12.5px] text-white/85">{{ $isFr ? 'Abonnez-vous pour recevoir les actualités du SIARC 2026.' : 'Subscribe for SIARC 2026 news.' }}</p>
            </div>
        </div>
        <form id="si-news" class="flex-1 flex gap-2.5 w-full" onsubmit="event.preventDefault();this.innerHTML='<p class=\'text-white font-semibold py-3\'>{{ $isFr ? 'Merci ! Vous êtes abonné·e.' : 'Thanks! You are subscribed.' }}</p>';">
            <input type="email" required placeholder="{{ $isFr ? 'Votre adresse e-mail' : 'Your email address' }}" class="flex-1 h-12 rounded-xl px-4 text-[14px] bg-white text-[#1D1B16] placeholder-[#9C978C] outline-none focus:ring-2 focus:ring-white/70">
            <button type="submit" class="siarc-btn bg-[#0F4824] text-white px-6 h-12 text-[13px] shrink-0">{{ $isFr ? "S'abonner" : 'Subscribe' }}</button>
        </form>
    </div>
</section>

{{-- ── Dark footer ────────────────────────────────────────────────────────── --}}
<footer class="bg-[#0B3A1E] text-white">
    <div class="max-w-[1240px] mx-auto px-6 py-12 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
        <div class="col-span-2">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
            <p class="mt-4 text-[12.5px] text-white/60 leading-relaxed max-w-[240px]">{{ $isFr ? "Le rendez-vous international qui valorise la créativité et l'excellence de l'artisanat camerounais." : 'The international event celebrating the creativity and excellence of Cameroonian craft.' }}</p>
            <div class="flex items-center gap-2.5 mt-5">
                @foreach([
                    ['Facebook','M14 8.5h2.2V5.6C15.8 5.5 14.9 5.4 13.9 5.4c-2.1 0-3.5 1.3-3.5 3.6v2h-2.3v3h2.3V21h2.9v-6.9h2.2l.4-3h-2.6V9.3c0-.5.3-.8 1-.8Z'],
                    ['Instagram','instagram'],['LinkedIn','linkedin'],['YouTube','M21 8.5a2.8 2.8 0 0 0-2-2C17.2 6 12 6 12 6s-5.2 0-7 .5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2.7 12 29 29 0 0 0 3 15.5a2.8 2.8 0 0 0 2 2c1.8.5 7 .5 7 .5s5.2 0 7-.5a2.8 2.8 0 0 0 2-2 29 29 0 0 0 .3-3.5 29 29 0 0 0-.3-3.5ZM10.5 14.5v-5l4 2.5-4 2.5Z']
                ] as [$net,$path])
                <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" aria-label="{{ $net }}" class="w-9 h-9 rounded-full bg-white/10 hover:bg-siarc-gold hover:text-[#0B3A1E] flex items-center justify-center transition-colors">
                    @if($net === 'Instagram')
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1.1" fill="currentColor" stroke="none"/></svg>
                    @elseif($net === 'LinkedIn')
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M6.2 9H3.5v11h2.7V9ZM4.85 4.5A1.6 1.6 0 1 0 4.85 7.7a1.6 1.6 0 0 0 0-3.2ZM20.5 20v-6c0-3.2-1.7-4.7-4-4.7-1.8 0-2.6 1-3.1 1.7V9H10.7v11h2.7v-6.1c0-1.6.9-2.2 1.9-2.2s1.8.7 1.8 2.2V20h2.7Z"/></svg>
                    @else
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        @php $cols = [
            ['NAVIGATION', [['Accueil','siarc.home'],['Pavillons','siarc.pavilions'],['Exposants','siarc.exhibitors'],['Programme','siarc.programme'],['Intervenants','siarc.speakers']]],
            [$isFr?'VISITEURS':'VISITORS', [['Infos pratiques','siarc.register'],['Plan du salon','siarc.pavilions'],['S\'inscrire','siarc.register'],['Mon espace','siarc.visitor.dashboard']]],
            [$isFr?'EXPOSANTS':'EXHIBITORS', [['Devenir exposant','siarc.register'],['Annuaire','siarc.exhibitors'],['Programme','siarc.programme']]],
        ]; @endphp
        @foreach($cols as [$title,$links])
        <div>
            <p class="text-[11px] font-bold tracking-[0.12em] text-siarc-gold mb-3.5">{{ $title }}</p>
            <ul class="space-y-2.5">
                @foreach($links as [$lbl,$rt])
                <li><a href="{{ $h($rt) }}" class="text-[12.5px] text-white/70 hover:text-white transition-colors">{{ $lbl }}</a></li>
                @endforeach
            </ul>
        </div>
        @endforeach

        <div>
            <p class="text-[11px] font-bold tracking-[0.12em] text-siarc-gold mb-3.5">CONTACT</p>
            <ul class="space-y-3 text-[12.5px] text-white/70">
                <li class="flex gap-2"><i data-lucide="map-pin" class="w-4 h-4 mt-0.5 shrink-0 text-siarc-gold"></i>Musée National de Yaoundé, Cameroun</li>
                <li class="flex gap-2"><i data-lucide="mail" class="w-4 h-4 mt-0.5 shrink-0 text-siarc-gold"></i>contact@siarc2026.cm</li>
                <li class="flex gap-2"><i data-lucide="phone" class="w-4 h-4 mt-0.5 shrink-0 text-siarc-gold"></i>+237 6 00 00 00 00</li>
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="max-w-[1240px] mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[12px] text-white/55">© 2026 SIARC — {{ $isFr ? 'Tous droits réservés' : 'All rights reserved' }}</p>
            <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wide text-white/70">
                <span>{{ $isFr ? 'FIERS DE NOTRE PATRIMOINE' : 'PROUD OF OUR HERITAGE' }}</span>
                {{-- Cameroon flag --}}
                <svg width="26" height="17" viewBox="0 0 30 20" class="rounded-sm shrink-0"><rect width="10" height="20" fill="#007A5E"/><rect x="10" width="10" height="20" fill="#CE1126"/><rect x="20" width="10" height="20" fill="#FCD116"/><path d="M15 8l.9 2.7h2.8l-2.3 1.7.9 2.7-2.3-1.7-2.3 1.7.9-2.7-2.3-1.7h2.8Z" fill="#FCD116"/></svg>
            </div>
        </div>
    </div>
</footer>
