@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
    $nav = [
        ['Accueil','Home','siarc.home',['siarc.home']],
        ['Pavillons','Pavilions','siarc.pavilions',['siarc.pavilions']],
        ['Exposants','Exhibitors','siarc.exhibitors',['siarc.exhibitors','siarc.exhibitor']],
        ['Programme','Programme','siarc.programme',['siarc.programme']],
        ['Intervenants','Speakers','siarc.speakers',['siarc.speakers','siarc.speaker']],
        ['Visiteurs','Visitors','siarc.register',['siarc.register','siarc.workshop.register']],
        ['Actualités','News','siarc.home',['siarc.news']],
    ];
@endphp
<div class="siarc-kente"></div>
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-[#EFEDE6]">
    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 h-[76px] flex items-center gap-4">
        @include('pages.siarc.partials.logo', ['onDark' => false, 'tag' => true])

        <nav class="hidden xl:flex items-center gap-6 ml-8">
            @foreach($nav as [$fr,$en,$route,$patterns])
                @php $active = request()->routeIs(...$patterns); @endphp
                <a href="{{ $h($route) }}" class="relative text-[13.5px] font-semibold tracking-wide uppercase transition-colors {{ $active ? 'text-[#0F4824]' : 'text-[#55524A] hover:text-[#0F4824]' }}">
                    {{ $isFr ? $fr : $en }}
                    @if($active)<span class="absolute -bottom-[27px] left-0 right-0 h-[3px] bg-siarc-gold rounded-full"></span>@endif
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            <div class="hidden sm:flex items-center gap-1.5 text-[13px] font-semibold text-[#3B382F] px-2">
                <i data-lucide="globe" class="w-[16px] h-[16px] text-[#8A857A]"></i>
                <a href="?lang=fr" class="{{ $isFr ? 'text-siarc-green' : 'hover:text-siarc-green' }}">FR</a>
                <span class="text-[#D5D2CA]">·</span>
                <a href="?lang=en" class="{{ !$isFr ? 'text-siarc-green' : 'hover:text-siarc-green' }}">EN</a>
            </div>
            <a href="{{ $h('siarc.exhibitors') }}" class="hidden sm:flex w-9 h-9 rounded-full hover:bg-[#F1F1EF] items-center justify-center" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}">
                <i data-lucide="search" class="w-[18px] h-[18px] text-[#3B382F]"></i>
            </a>
            <a href="{{ $h('siarc.visitor.dashboard') }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2.5">
                <i data-lucide="user" class="w-4 h-4"></i><span class="hidden sm:inline">{{ $isFr ? 'MON ESPACE' : 'MY SPACE' }}</span>
            </a>
            <button id="si-mnav-btn" class="xl:hidden w-9 h-9 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center">
                <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
            </button>
        </div>
    </div>
    {{-- mobile menu --}}
    <div id="si-mnav" class="hidden xl:hidden border-t border-[#EFEDE6] bg-white px-4 py-3">
        @foreach($nav as [$fr,$en,$route,$patterns])
            <a href="{{ $h($route) }}" class="block px-2 py-2.5 text-[14px] font-semibold uppercase tracking-wide text-[#3B382F] border-b border-[#F4F2EC] last:border-0">{{ $isFr ? $fr : $en }}</a>
        @endforeach
    </div>
</header>
