@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = ($lang === 'fr');
    $on = function_exists('siarcStandalone') ? siarcStandalone() : false;
    $modes = [
        [false, $isFr ? 'Module de la Galerie Nationale' : 'National Gallery module',
         $isFr ? 'SIARC est une section de la Galerie Virtuelle Nationale de l\'Artisanat. La page d\'accueil reste celle de la galerie ; le salon vit sous /siarc.'
               : 'SIARC is a section of the National Virtual Craft Gallery. The gallery stays the landing page; the fair lives under /siarc.',
         'layout-grid'],
        [true, $isFr ? 'SIARC Intégral (100%)' : 'SIARC Standalone (100%)',
         $isFr ? 'Toute la plateforme devient SIARC 2026 : la page d\'accueil (/) affiche le site du salon et SIARC est l\'expérience principale.'
               : 'The whole platform becomes SIARC 2026: the landing page (/) shows the fair site and SIARC is the primary experience.',
         'sparkles'],
    ];
@endphp

<div class="max-w-[980px] mx-auto siarc-in">

    @if(session('siarc_mode_saved'))
    <div class="flex items-center gap-2.5 bg-[#E7F4EC] border border-[#BFE3CD] text-[#0F5A31] rounded-xl px-4 py-3 text-[13px] font-medium mb-5">
        <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>{{ $isFr ? 'Mode de la plateforme mis à jour.' : 'Platform mode updated.' }}
    </div>
    @endif

    <div class="siarc-card siarc-shadow overflow-hidden mb-6">
        <div class="siarc-kente"></div>
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <span class="w-12 h-12 rounded-2xl bg-[#E7F1EA] flex items-center justify-center shrink-0"><i data-lucide="toggle-right" class="w-6 h-6 text-siarc-green"></i></span>
                <div>
                    <h2 class="font-display text-[22px] font-bold text-[#161513]">{{ $isFr ? 'Mode de la plateforme' : 'Platform mode' }}</h2>
                    <p class="text-[13px] text-[#8A857A] mt-1 max-w-[640px]">{{ $isFr ? 'Choisissez si SIARC 2026 fonctionne comme un module de la galerie nationale, ou prend la totalité de la plateforme.' : 'Choose whether SIARC 2026 runs as a module of the national gallery, or takes over the whole platform.' }}</p>
                </div>
                <span class="ml-auto shrink-0 inline-flex items-center gap-1.5 text-[12px] font-semibold px-3 py-1.5 rounded-full {{ $on ? 'bg-[#E7F4EC] text-siarc-green' : 'bg-[#F1F1EF] text-[#6F6B60]' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $on ? 'bg-siarc-green siarc-pulse' : 'bg-[#B0AB9F]' }}"></span>
                    {{ $on ? ($isFr ? 'SIARC Intégral actif' : 'SIARC standalone on') : ($isFr ? 'Mode module' : 'Module mode') }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 mb-6">
        @foreach($modes as [$val, $title, $desc, $icon])
        @php $active = ($on === $val); @endphp
        <div class="siarc-card siarc-shadow p-6 relative {{ $active ? 'ring-2 ring-siarc-green' : '' }}">
            @if($active)<span class="absolute top-4 right-4 inline-flex items-center gap-1 text-[11px] font-bold text-siarc-green"><i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ $isFr ? 'Actif' : 'Active' }}</span>@endif
            <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 {{ $val ? 'bg-[#FDF3E0]' : 'bg-[#E8EFFB]' }}"><i data-lucide="{{ $icon }}" class="w-6 h-6 {{ $val ? 'text-siarc-ochre' : 'text-[#3565DE]' }}"></i></span>
            <h3 class="font-display text-[17px] font-bold text-[#161513] mb-1.5">{{ $title }}</h3>
            <p class="text-[12.5px] text-[#6F6B60] leading-relaxed">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    <div class="siarc-card siarc-shadow p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-[14px] font-semibold text-[#161513]">{{ $isFr ? 'Activer le mode SIARC Intégral' : 'Enable SIARC standalone mode' }}</p>
            <p class="text-[12.5px] text-[#8A857A] mt-0.5">{{ $isFr ? 'La racine du site (/) redirigera vers l\'accueil SIARC 2026.' : 'The site root (/) will redirect to the SIARC 2026 home.' }}</p>
        </div>
        <form method="POST" action="{{ route('siarc.admin.mode.set', ['lang' => $lang]) }}" class="shrink-0">
            @csrf
            <input type="hidden" name="standalone" value="{{ $on ? '0' : '1' }}">
            <button type="submit" class="siarc-btn {{ $on ? 'bg-[#F1F1EF] text-[#3B382F] hover:bg-[#E7E5DF]' : 'siarc-btn-green' }} px-6 py-3 text-[13px]">
                <i data-lucide="{{ $on ? 'toggle-left' : 'toggle-right' }}" class="w-5 h-5"></i>
                {{ $on ? ($isFr ? 'Revenir au mode module' : 'Switch back to module mode') : ($isFr ? 'Activer SIARC Intégral' : 'Turn on SIARC standalone') }}
            </button>
        </form>
    </div>
</div>
