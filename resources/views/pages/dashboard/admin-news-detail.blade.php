@php
    $isFr = $lang === 'fr';
    $adminActive = 'news';
    $aTitle = $isFr ? $article->title_fr : ($article->title_en ?? $article->title_fr);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $aTitle }} — Administration</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#14652F' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}#ad-sidebar{display:none}#ad-sidebar.ad-open{display:flex;position:fixed;inset:0 auto 0 0;width:270px;z-index:60;overflow-y:auto}@media(min-width:1024px){#ad-sidebar,#ad-sidebar.ad-open{display:flex;position:sticky;top:0;height:100vh;width:250px}}</style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">
<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', [
            'pageTitle' => $isFr ? 'ACTUALITÉ' : 'ARTICLE',
            'pageBreadcrumb' => [['Dashboard', route('dashboard.admin')], [$isFr?'Actualités':'News', route('admin.news')], [$isFr?'Détail':'Detail', null]],
            'pageSearchPlaceholder' => $isFr ? 'Rechercher une actualité...' : 'Search an article...',
        ])
        <main class="px-5 lg:px-7 pt-5 pb-8">
            @if(session('success'))<div class="mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]"><i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>{{ session('success') }}</div>@endif

            {{-- Admin actions --}}
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.news', ['lang'=>$lang]) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#3B382F]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour aux actualités':'Back to news' }}</a>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('news.show', ['slug'=>$article->slug, 'lang'=>$lang]) }}" target="_blank" class="inline-flex items-center gap-2 bg-white border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 h-[38px] text-[12px] font-semibold text-[#14652F]"><i data-lucide="external-link" class="w-4 h-4"></i>{{ $isFr?'Voir en public':'View public' }}</a>
                    <form method="POST" action="{{ route('admin.news.toggle', ['id'=>$article->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                        <button type="submit" class="inline-flex items-center gap-2 {{ $article->status === 'published' ? 'border-[#EAD9AC] text-[#C97A16]' : 'border-[#CFE0D4] text-[#157A43]' }} bg-white border hover:opacity-80 rounded-lg px-4 h-[38px] text-[12px] font-semibold"><i data-lucide="{{ $article->status === 'published' ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>{{ $article->status === 'published' ? ($isFr?'Dépublier':'Unpublish') : ($isFr?'Publier':'Publish') }}</button>
                    </form>
                </div>
            </div>

            @include('pages.partials.article-reader', ['publicMode' => false])

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
