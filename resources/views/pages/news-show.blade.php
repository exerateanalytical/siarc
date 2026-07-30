@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $dirNavActive = 'news';
    $aTitle = $isFr ? $article->title_fr : ($article->title_en ?? $article->title_fr);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($isFr ? ($article->excerpt_fr ?? '') : ($article->excerpt_en ?? $article->excerpt_fr ?? '')), 160) }}">
    <title>{{ $aTitle }} — {{ $isFr ? 'Actualités' : 'News' }}</title>
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-cream: 248 243 237;
            --c-gold: 201 148 46;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>
    @include('pages.partials.icons')
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#FBF8F2] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">
@include('pages.partials.directory-header')
<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8">
    @include('pages.partials.article-reader', ['publicMode' => true])
</div>
@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
