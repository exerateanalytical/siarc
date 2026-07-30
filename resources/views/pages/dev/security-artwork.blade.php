@php
    // A local-only QA surface. It exists so the security artwork can be judged
    // at the sizes it will actually be used at, and so the microtext claim can
    // be checked rather than taken on trust — the zoom panels below are the
    // same <text> nodes scaled up, not a different asset.
    $lang     = $lang ?? 'fr';
    $isFr     = $lang === 'fr';
    $siacUser = session('siac_user');
    $dfShowHelp = true;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu des éléments de sécurité — Artisan Hub 237</title>
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 217 164 57;
            --c-goldbt: 233 168 48;
            --c-leaf: 22 76 40;
        }
    </style>
    @include('pages.partials.icons')
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')
@include('pages.partials.coa-ornaments')
@include('pages.partials.coa-security')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[980px] mx-auto px-5 py-9">
        <p class="text-[11px] uppercase tracking-[.22em] text-[#D9A439]">Local uniquement</p>
        <h1 class="mt-1 text-[26px] font-bold text-white">Éléments de sécurité des certificats</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC] max-w-[640px]">
            Guilloché, microtexte, trame anti-copie et traitement irisé. Tout ce qui est
            montré ici est réellement ce que son nom indique. Les procédés d'impression
            physiques (encre UV, holographie, gaufrage, taille-douce, fil de sécurité,
            papier filigrané) ne figurent pas sur cette page : voir docs/PRINT-SECURITY-SPEC.md.
        </p>
    </div>
</section>

<main class="max-w-[980px] mx-auto px-5 py-10 pb-24 space-y-8">

    {{-- Guilloché band, at frame size and at the size it is used on a ribbon. --}}
    <section class="bg-white border border-[#E8E0CB] rounded-2xl p-6">
        <h2 class="text-[15px] font-semibold">Guilloché — bandeau (coaGuillocheBorder)</h2>
        <p class="mt-1 text-[12.5px] text-[#6B6659]">Hypotrochoïde déroulée : somme de deux sinusoïdes de fréquences premières entre elles (13 et 23), dix brins déphasés.</p>
        <div class="mt-4 space-y-4">
            <svg viewBox="0 0 1000 44" class="w-full block"><use href="#coaGuillocheBorder"/></svg>
            <svg viewBox="0 0 1000 44" class="w-[480px] max-w-full block"><use href="#coaGuillocheBorder"/></svg>
            {{-- A third of the tile at 8× so the crossings can be inspected. --}}
            <div class="overflow-x-auto border border-[#EEE8DA] rounded-lg"><svg viewBox="0 0 330 44" width="2640" height="352" class="block"><use href="#coaGuillocheBorder"/></svg></div>
        </div>
    </section>

    {{-- Rosette, three sizes, plus one behind a seal disc. --}}
    <section class="bg-white border border-[#E8E0CB] rounded-2xl p-6">
        <h2 class="text-[15px] font-semibold">Guilloché — rosace (coaGuillocheRosette)</h2>
        <p class="mt-1 text-[12.5px] text-[#6B6659]">Deux familles superposées : R=19/r=6/d=4,6 et R=13/r=5/d=3,2 en sens inverse, six brins chacune. C'est le croisement des deux réseaux qui donne le tissage.</p>
        <div class="mt-4 flex flex-wrap items-end gap-8">
            <svg viewBox="0 0 200 200" width="320" height="320"><use href="#coaGuillocheRosette"/></svg>
            <svg viewBox="0 0 200 200" width="190" height="190"><use href="#coaGuillocheRosette"/></svg>
            <svg viewBox="0 0 200 200" width="96" height="96"><use href="#coaGuillocheRosette"/></svg>
            <svg viewBox="0 0 200 200" width="200" height="200">
                <use href="#coaGuillocheRosette"/>
                <circle cx="100" cy="100" r="46" fill="url(#coaHoloGradient)"/>
                <circle cx="100" cy="100" r="46" fill="none" stroke="#9C6E1B" stroke-width="1.5"/>
                <text x="100" y="104" text-anchor="middle" font-size="12" font-weight="700" fill="#0A3A22">SCEAU</text>
            </svg>
        </div>
    </section>

    {{-- Microtext: illegible, then proven legible. --}}
    <section class="bg-white border border-[#E8E0CB] rounded-2xl p-6">
        <h2 class="text-[15px] font-semibold">Microtexte (coaMicrotextLine / coaMicrotextPath)</h2>
        <p class="mt-1 text-[12.5px] text-[#6B6659]">Corps 1,6 px. Illisible à 100 %, lisible au zoom — c'est exactement l'effet recherché.</p>

        {{-- "À taille réelle" means true 1:1 scale, so this must not shrink to fit
             the viewport like a responsive graphic would — that would misrepresent
             the actual size being demonstrated. It scrolls instead, same pattern
             as the ×14 zoom panel just below. --}}
        <p class="mt-4 text-[11px] uppercase tracking-[.18em] text-[#6B6659]">À taille réelle</p>
        <div class="mt-1 overflow-x-auto border border-[#EEE8DA] rounded-lg bg-[#FEFDF7]">
            <svg viewBox="0 0 320 4" width="320" height="4" class="block"><use href="#coaMicrotextLine"/></svg>
        </div>

        <p class="mt-5 text-[11px] uppercase tracking-[.18em] text-[#6B6659]">Zoom ×14 — le texte se résout</p>
        <div class="mt-1 overflow-x-auto border border-[#EEE8DA] rounded-lg bg-[#FEFDF7]">
            <svg viewBox="0 0 90 4" width="1260" height="56" class="block"><use href="#coaMicrotextLine"/></svg>
        </div>

        <p class="mt-5 text-[11px] uppercase tracking-[.18em] text-[#6B6659]">Anneau de sceau, à taille réelle puis ×4</p>
        <div class="mt-1 flex items-end gap-8">
            <svg viewBox="0 0 200 200" width="200" height="200"><use href="#coaMicrotextPath"/></svg>
            <svg viewBox="0 0 200 200" width="800" height="800" class="max-w-full"><use href="#coaMicrotextPath"/></svg>
        </div>
    </section>

    {{-- Anti-copy screen. --}}
    <section class="bg-white border border-[#E8E0CB] rounded-2xl p-6">
        <h2 class="text-[15px] font-semibold">Trame anti-copie (coaAntiCopy)</h2>
        <p class="mt-1 text-[12.5px] text-[#6B6659]">Lignes de 0,25 px au pas de 3 px, inclinées à 38°. Elle ne bloque pas la copie : elle la dégrade visiblement.</p>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <svg viewBox="0 0 400 160" class="w-full block border border-[#EEE8DA] rounded-lg">
                <rect width="400" height="160" fill="#FEFDF7"/>
                <rect width="400" height="160" fill="url(#coaAntiCopy)"/>
            </svg>
            <svg viewBox="0 0 400 160" class="w-full block border border-[#EEE8DA] rounded-lg">
                <rect width="400" height="160" fill="#FEFDF7"/>
                <rect width="400" height="160" fill="url(#coaAntiCopy)"/>
                <rect x="24" y="34" width="352" height="92" fill="none" stroke="url(#coaGoldRule)" stroke-width="2"/>
                <text x="200" y="88" text-anchor="middle" font-size="17" font-weight="700" fill="#0A3A22">CERTIFICAT</text>
            </svg>
        </div>
        {{-- At 1:1 the screen is almost invisible, which is the point; this panel
             enlarges a 60-unit square so the line pitch can actually be judged. --}}
        <p class="mt-4 text-[11px] uppercase tracking-[.18em] text-[#6B6659]">Zoom ×6 — le pas de trame</p>
        <div class="mt-1 overflow-x-auto border border-[#EEE8DA] rounded-lg">
            <svg viewBox="0 0 60 60" width="360" height="360" class="block">
                <rect width="60" height="60" fill="#FEFDF7"/>
                <rect width="60" height="60" fill="url(#coaAntiCopy)"/>
            </svg>
        </div>
    </section>

    {{-- Iridescent treatment — named honestly. --}}
    <section class="bg-white border border-[#E8E0CB] rounded-2xl p-6">
        <h2 class="text-[15px] font-semibold">Traitement irisé (coaHoloGradient)</h2>
        <p class="mt-1 text-[12.5px] text-[#6B6659]">
            Effet visuel irisé — <strong>pas</strong> un hologramme. Aucun élément d'écran ne porte
            de propriété physique et ce dégradé ne doit jamais être légendé comme un procédé de sécurité.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-6">
            <svg viewBox="0 0 200 200" width="150" height="150"><circle cx="100" cy="100" r="92" fill="url(#coaHoloGradient)"/></svg>
            <svg viewBox="0 0 300 60" width="300" height="60"><rect width="300" height="60" rx="8" fill="url(#coaHoloGradient)"/></svg>
        </div>
    </section>

</main>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
