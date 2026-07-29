@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'news';
    $aTitle = $isFr ? $article->title_fr : ($article->title_en ?? $article->title_fr);
    $pageTitle = $isFr ? 'ACTUALITÉ' : 'ARTICLE';
    $pageBreadcrumb = [['Dashboard', route('dashboard.admin')], [$isFr?'Actualités':'News', route('admin.news')], [$isFr?'Détail':'Detail', null]];
    $pageSearchPlaceholder = $isFr ? 'Rechercher une actualité...' : 'Search an article...';
@endphp

@section('content')
            {{-- Admin actions --}}
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.news', ['lang'=>$lang]) }}" class="ui-btn ui-btn-secondary"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr?'Retour aux actualités':'Back to news' }}</a>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('news.show', ['slug'=>$article->slug, 'lang'=>$lang]) }}" target="_blank" class="ui-btn ui-btn-secondary"><i data-lucide="external-link" class="w-4 h-4"></i>{{ $isFr?'Voir en public':'View public' }}</a>
                    <form method="POST" action="{{ route('admin.news.toggle', ['id'=>$article->id]) }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}">
                        <button type="submit" class="inline-flex items-center gap-2 {{ $article->status === 'published' ? 'border-[#EAD9AC] dark:border-[#4A3A12] text-[#C97A16] dark:text-[#EDB33A] ' : 'border-[#CFE0D4] dark:border-[#39402F] text-[#157A43] dark:text-[#339B56] ' }} bg-white dark:bg-[#12150F] border hover:opacity-80 rounded-lg px-4 h-[38px] text-[12px] font-semibold"><i data-lucide="{{ $article->status === 'published' ? 'eye-off' : 'eye' }}" class="w-4 h-4"></i>{{ $article->status === 'published' ? ($isFr?'Dépublier':'Unpublish') : ($isFr?'Publier':'Publish') }}</button>
                    </form>
                </div>
            </div>

            @include('pages.partials.article-reader', ['publicMode' => false])

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A] dark:text-[#868778]">© {{ now()->year }} {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
@endsection
