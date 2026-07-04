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
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf:'#164C28', gold:'#C9942E', cream:'#F8F3ED', sand:'#E7E1D4' }, fontFamily: { sans:['Poppins','system-ui','sans-serif'], serif:['"Playfair Display"','Georgia','serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body{font-family:'Poppins',system-ui,sans-serif}html,body{overflow-x:clip}</style>
</head>
<body class="bg-[#FBF8F2] text-[#1D1B16] antialiased">
@include('pages.partials.directory-header')
<div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8">
    @include('pages.partials.article-reader', ['publicMode' => true])
</div>
@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
