@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $dirNavActive = 'centres';
    $cName = $isFr ? $centre->name_fr : ($centre->name_en ?? $centre->name_fr);
    $regionName = $isFr ? $centre->region_fr : ($centre->region_en ?? $centre->region_fr);
    $fmt = fn ($n) => number_format($n, 0, ',', ' ');

    $regionBiz  = \App\Modules\Businesses\Models\Business::whereNull('deleted_at')->where('status', 'published')->where('region_id', $centre->region_id)->count();
    $regionProd = DB::table('products as p')->join('businesses as b', 'b.id', '=', 'p.business_id')->where('b.region_id', $centre->region_id)->where('p.status', 'published')->count();

    $chiffres = [
        ['users', '#157A43', $centre->artisans_count, 'Artisans'],
        ['shopping-basket', '#C9942E', $regionProd, $isFr ? 'Produits & Services' : 'Products & Services'],
        ['hammer', '#3565DE', $regionBiz, $isFr ? 'Ateliers' : 'Workshops'],
        ['calendar-days', '#9B1C31', DB::table('events')->count(), $isFr ? 'Événements / an' : 'Events / year'],
        ['store', '#7C4FE0', max(1, intdiv($regionBiz, 3)), 'Boutiques'],
        ['ruler', '#0E9F9F', ($centre->region_id ? ($centre->chef_lieu ? '68 953 m²' : '—') : '—'), $isFr ? 'Superficie' : 'Area'],
    ];
    $missions = $isFr
        ? [['award', 'Valoriser', 'le savoir-faire ancestral'], ['sparkles', 'Promouvoir', 'la créativité locale'], ['heart-handshake', 'Préserver', 'notre patrimoine vivant']]
        : [['award', 'Enhance', 'ancestral know-how'], ['sparkles', 'Promote', 'local creativity'], ['heart-handshake', 'Preserve', 'our living heritage']];
    $timeline = [
        ['1983', $isFr ? 'Création du Centre Artisanal' : 'Centre founded'],
        ['1996', $isFr ? 'Extension et nouvelles infrastructures' : 'Extension and new facilities'],
        ['2010', $isFr ? 'Modernisation et digitalisation' : 'Modernisation and digitalisation'],
        ['2020+', $isFr ? 'Rayonnement africain et international' : 'African and international outreach'],
    ];
    $specialites = array_filter(array_map('trim', explode(',', $centre->specialties_fr ?? '')));
    $specIcons = ['Sculpture' => 'axe', 'Vannerie' => 'shopping-basket', 'Bijouterie' => 'gem', 'Tissage' => 'shirt', 'Poterie' => 'amphora', 'Bois' => 'trees', 'Cuir' => 'wallet', 'Métal' => 'wrench', 'Peinture' => 'palette', 'Bronze' => 'gem', 'Perles' => 'gem', 'Couture' => 'shirt', 'Calebasses' => 'amphora', 'Coquillages' => 'shell', 'Élevage' => 'beef'];
    $infosCles = [
        ['tag', $isFr ? 'Type de centre' : 'Centre type', $centre->type === 'principal' ? ($isFr ? 'Principal' : 'Main') : ($isFr ? 'Secondaire' : 'Secondary')],
        ['badge-check', 'Statut', $isFr ? 'Actif' : 'Active'],
        ['calendar', $isFr ? 'Année de création' : 'Founded', '1983'],
        ['users', 'Artisans', $fmt($centre->artisans_count)],
        ['map-pin', $isFr ? 'Ville' : 'City', $centre->city ?? $centre->chef_lieu],
        ['clock', $isFr ? 'Heures d\'ouverture' : 'Opening hours', 'Lun - Sam : 08:00 - 17:00'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $cName }} — {{ $regionName }}, Cameroun.">
    <title>{{ $cName }} — {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#164C28', gold:'#C9942E', cream:'#F8F3ED', sand:'#E7E1D4' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FBF8F2] text-[#1D1B16] antialiased">
@include('pages.partials.directory-header')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-[#0E2C1A] to-[#123D24] overflow-hidden">
    <img src="{{ asset('images/landing/hh-kente.png') }}" alt="" class="absolute inset-x-0 bottom-0 h-[16px] w-full object-cover opacity-80" aria-hidden="true">
    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8 grid grid-cols-1 lg:grid-cols-[1fr_1.1fr] gap-8 items-center">
        <div>
            <nav class="flex items-center gap-2 text-[12px] text-[#CFE3D5]">
                <a href="{{ route('home', ['lang'=>$lang]) }}" class="hover:text-white">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><span>{{ $isFr ? 'Centres d\'Artisanat' : 'Craft Centres' }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><span class="text-[#E9C25A]">{{ $cName }}</span>
            </nav>
            <span class="mt-4 inline-block bg-[#0A3B22] border border-[#E9C25A]/40 rounded-md px-3 py-1 text-[10.5px] font-bold tracking-[0.12em] text-[#E9C25A] uppercase">{{ $isFr ? 'Centre Artisanal' : 'Craft Centre' }}</span>
            <h1 class="mt-4 font-serif text-[34px] sm:text-[44px] leading-[1.05] font-bold text-[#F3E7C9] uppercase">{{ $cName }}</h1>
            <p class="mt-2 text-[#E9C25A] text-[15px] tracking-[0.3em]">❈ ❈ ❈ ❈ ❈</p>
            <p class="mt-4 text-[13.5px] text-[#DCEAE0] leading-relaxed max-w-[420px]">{{ $isFr ? $centre->description_fr : ($centre->description_en ?? $centre->description_fr) }}</p>
            <div class="mt-4 flex items-center gap-2 text-white">
                <span class="flex items-center gap-1 text-[#E9C25A]">@for($i=0;$i<5;$i++)<i data-lucide="star" class="w-4 h-4 fill-current"></i>@endfor</span>
                <span class="text-[13px] font-semibold">4.8</span><span class="text-[12px] text-[#CFE3D5]">(128 {{ $isFr ? 'avis' : 'reviews' }})</span>
            </div>
            <p class="mt-2 flex items-center gap-2 text-[12.5px] text-[#DCEAE0]"><i data-lucide="map-pin" class="w-4 h-4 text-[#E9C25A]"></i>{{ $centre->city ?? $centre->chef_lieu }}, {{ $regionName }}, {{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
            <div class="mt-5 flex items-center gap-3">
                <a href="{{ route('businesses.index', ['lang'=>$lang, 'region'=>$centre->region_code]) }}" class="inline-flex items-center gap-2 bg-[#0F7A3D] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 h-[44px] rounded-lg"><i data-lucide="navigation" class="w-4 h-4"></i>{{ $isFr ? 'Itinéraire' : 'Directions' }}</a>
                <a href="{{ route('contact', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-[#F5EEDD] text-[#1D1B16] text-[13px] font-semibold px-5 h-[44px] rounded-lg"><i data-lucide="phone" class="w-4 h-4"></i>{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
                <a href="{{ $siacUser ? route('saved.index', ['lang'=>$lang]) : '/login?lang='.$lang }}" class="w-[44px] h-[44px] rounded-lg bg-white/10 border border-white/20 flex items-center justify-center text-white"><i data-lucide="heart" class="w-4 h-4"></i></a>
            </div>
        </div>
        <div class="relative rounded-2xl overflow-hidden border-4 border-[#8A5A2B]/40">
            <img src="{{ asset('images/landing/centre-hero.png') }}" alt="{{ $cName }}" class="w-full h-full object-cover">
        </div>
    </div>
</section>

<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8 grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 items-start">
    <div class="space-y-6">
        {{-- À propos + chiffres --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
                <h2 class="flex items-center gap-2 text-[14px] font-bold text-[#1D1B16]"><i data-lucide="landmark" class="w-4 h-4 text-[#C9942E]"></i>{{ $isFr ? 'À propos du centre' : 'About the centre' }}</h2>
                <p class="mt-3 text-[12.5px] text-[#55524A] leading-relaxed">{{ $isFr ? $centre->description_fr : ($centre->description_en ?? $centre->description_fr) }}</p>
                <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                    @foreach($missions as [$mIcon, $mTitle, $mSub])
                    <div><span class="w-11 h-11 mx-auto rounded-full bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="{{ $mIcon }}" class="w-5 h-5 text-[#C9942E]"></i></span><p class="mt-2 text-[12px] font-bold text-[#1D1B16]">{{ $mTitle }}</p><p class="text-[10.5px] text-[#6F6B60] leading-snug">{{ $mSub }}</p></div>
                    @endforeach
                </div>
            </section>
            <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Chiffres clés' : 'Key figures' }}</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach($chiffres as [$chIcon, $chColor, $chVal, $chLabel])
                    <div class="border border-[#EDE6D6] rounded-xl px-3.5 py-3"><i data-lucide="{{ $chIcon }}" class="w-5 h-5" style="color: {{ $chColor }}"></i><p class="mt-1.5 text-[18px] font-bold text-[#1D1B16] leading-none">{{ is_numeric($chVal) ? $fmt($chVal) : $chVal }}</p><p class="text-[10.5px] text-[#6F6B60]">{{ $chLabel }}</p></div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Heritage timeline --}}
        <section class="relative bg-gradient-to-br from-[#0E2C1A] to-[#123D24] rounded-2xl px-6 py-6 overflow-hidden">
            <h2 class="flex items-center gap-2 text-[13px] font-bold tracking-[0.1em] text-[#E9C25A] uppercase"><i data-lucide="scroll-text" class="w-4 h-4"></i>{{ $isFr ? 'Notre Héritage, Notre Histoire' : 'Our Heritage, Our History' }}</h2>
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($timeline as [$tYear, $tLabel])
                <div class="text-center"><span class="w-9 h-9 mx-auto rounded-full bg-[#E9C25A]/15 border border-[#E9C25A]/40 flex items-center justify-center"><i data-lucide="milestone" class="w-4 h-4 text-[#E9C25A]"></i></span><p class="mt-2 text-[15px] font-bold text-white">{{ $tYear }}</p><p class="text-[10.5px] text-[#CFE3D5] leading-snug">{{ $tLabel }}</p></div>
                @endforeach
            </div>
        </section>

        {{-- Spécialités --}}
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Spécialités du centre' : 'Centre specialties' }}</h2>
            <div class="mt-4 flex flex-wrap gap-x-8 gap-y-4">
                @foreach($specialites as $spec)
                @php $ic = 'sparkles'; foreach($specIcons as $k=>$v){ if(stripos($spec,$k)!==false){$ic=$v;break;} } @endphp
                <div class="text-center w-[80px]"><span class="w-12 h-12 mx-auto rounded-full bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="{{ $ic }}" class="w-5 h-5 text-[#C9942E]"></i></span><p class="mt-2 text-[11px] font-medium text-[#3B382F] leading-snug">{{ $spec }}</p></div>
                @endforeach
            </div>
        </section>

        {{-- Artisans à la une --}}
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-6 py-5">
            <div class="flex items-center justify-between"><h2 class="text-[14px] font-bold text-[#1D1B16]">{{ $isFr ? 'Artisans à la une' : 'Featured artisans' }}</h2><a href="{{ route('businesses.index', ['lang'=>$lang, 'region'=>$centre->region_code]) }}" class="text-[11.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir tous les artisans' : 'View all artisans' }} →</a></div>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                @forelse($businesses as $b)
                <a href="{{ route('businesses.show', ['slug'=>$b->slug, 'lang'=>$lang]) }}" class="text-center group">
                    <div class="w-full h-[110px] rounded-xl overflow-hidden bg-[#F1EDE2]">
                        <img src="{{ $b->cover_image ? asset('storage/'.$b->cover_image) : asset('images/landing/biz-'.(($loop->index%6)+1).'.png') }}" alt="{{ $b->name_fr }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    </div>
                    <p class="mt-2 text-[12px] font-semibold text-[#1D1B16] truncate">{{ $b->name_fr }}</p>
                    <p class="text-[10.5px] text-[#6F6B60] truncate">{{ $b->industry->name_fr ?? '' }}</p>
                </a>
                @empty
                <p class="text-[12px] text-[#6F6B60] col-span-full">{{ $isFr ? 'Artisans bientôt disponibles.' : 'Artisans coming soon.' }}</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Right rail --}}
    <aside class="space-y-5">
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.08em] text-[#1D1B16] uppercase">{{ $isFr ? 'Informations clés' : 'Key information' }}</h2>
            <dl class="mt-3.5 space-y-2.5 text-[12px]">
                @foreach($infosCles as [$iIcon, $iLabel, $iVal])
                <div class="flex items-center justify-between border-b border-[#F1EDE2] pb-2"><dt class="flex items-center gap-2 text-[#6F6B60]"><i data-lucide="{{ $iIcon }}" class="w-3.5 h-3.5 text-[#C9942E]"></i>{{ $iLabel }}</dt><dd class="font-semibold text-[#1D1B16]">{{ $iVal }}</dd></div>
                @endforeach
            </dl>
        </section>
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5">
            <h2 class="text-[13px] font-bold tracking-[0.08em] text-[#1D1B16] uppercase">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</h2>
            <div class="mt-3 space-y-2.5 text-[12px] text-[#3B382F]">
                <p class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4 text-[#C9942E]"></i>{{ $centre->contact_phone }}</p>
                <p class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4 text-[#C9942E]"></i>{{ $centre->contact_email ?? 'contact@artisanat.cm' }}</p>
                <p class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4 text-[#C9942E]"></i>{{ $centre->address ?? ($centre->city . ', ' . $regionName) }}</p>
            </div>
            <a href="{{ route('contact', ['lang'=>$lang]) }}" class="mt-4 block text-center bg-[#0F7A3D] hover:bg-[#14652F] text-white text-[12.5px] font-semibold py-2.5 rounded-lg">{{ $isFr ? 'Envoyer un message' : 'Send a message' }}</a>
        </section>
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5 text-center">
            <h2 class="text-[13px] font-bold tracking-[0.08em] text-[#1D1B16] uppercase">{{ $isFr ? 'Labels & Reconnaissances' : 'Labels & Recognition' }}</h2>
            <div class="mt-3 flex items-center justify-center gap-3">
                <span class="w-14 h-14 rounded-full bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="award" class="w-6 h-6 text-[#C9942E]"></i></span>
                <span class="w-14 h-14 rounded-full bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="globe" class="w-6 h-6 text-[#3565DE]"></i></span>
                <span class="w-14 h-14 rounded-full bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="badge-check" class="w-6 h-6 text-[#157A43]"></i></span>
            </div>
            <p class="mt-3 text-[10.5px] text-[#6F6B60] leading-snug">{{ $isFr ? 'Patrimoine Culturel National · UNESCO · Qualité Artisanale Certifiée' : 'National Cultural Heritage · UNESCO · Certified Craft Quality' }}</p>
        </section>
    </aside>
</div>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
