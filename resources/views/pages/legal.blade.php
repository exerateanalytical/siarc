@extends('layouts.app')

@php
$isFr  = $lang === 'fr';
$title = $doc['title'][$lang] . ' — Artisan Hub 237';
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 sm:py-12">

    {{-- Cross-links: every legal document reaches every other one. --}}
    <nav class="flex flex-wrap gap-1.5 mb-7">
        @foreach($allDocs as $slug => $d)
        <a href="{{ route('legal.show', ['doc' => $slug, 'lang' => $lang]) }}"
           class="ui-tap px-3 py-1.5 rounded-lg text-[12px] font-semibold border transition-colors
                  {{ $slug === $activeSlug
                     ? 'bg-[#14532D] border-[#14532D] text-white'
                     : 'bg-white dark:bg-[#12150F] border-[#ECECEA] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] hover:border-[#14652F] hover:text-[#14652F] hover:dark:text-[#339B56]' }}">
            {{ $d['title'][$lang] }}
        </a>
        @endforeach
    </nav>

    <h1 class="text-[24px] sm:text-[28px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight">{{ $doc['title'][$lang] }}</h1>
    <p class="mt-1.5 text-[12px] text-[#A8A296]">
        {{ $isFr ? 'Dernière mise à jour :' : 'Last updated:' }} {{ $updatedAt }}
    </p>

    @if(! empty($doc['intro'][$lang]))
    <p class="mt-5 text-[14px] text-[#3B382F] dark:text-[#F3EFE7] leading-relaxed">{{ $doc['intro'][$lang] }}</p>
    @endif

    <div class="mt-8 space-y-7">
        @foreach($doc['sections'] as $i => $section)
        <section>
            <h2 class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] mb-2">{{ $i + 1 }}. {{ $section['heading'][$lang] }}</h2>
            @foreach((array) $section['body'][$lang] as $para)
            <p class="text-[13.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed mb-2 last:mb-0">{{ $para }}</p>
            @endforeach
            @if(! empty($section['list'][$lang]))
            <ul class="mt-2 space-y-1.5">
                @foreach($section['list'][$lang] as $item)
                <li class="flex gap-2.5 text-[13.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
                    <span class="mt-[7px] w-1.5 h-1.5 rounded-full bg-[#C9942E] shrink-0"></span>
                    <span>{{ $item }}</span>
                </li>
                @endforeach
            </ul>
            @endif
        </section>
        @endforeach
    </div>

    {{-- The affiliation disclaimer appears on every legal page, not just one. --}}
    <div class="mt-10 rounded-xl border border-[#F1E7D2] dark:border-[#6A5210] bg-[#FBF6EC] dark:bg-[#0A0C09] px-5 py-4">
        <p class="flex items-center gap-2 text-[12.5px] font-bold text-[#8A6D1F] dark:text-[#EDB33A]">
            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
            {{ $isFr ? 'Indépendance' : 'Independence' }}
        </p>
        <p class="mt-1.5 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
            {{ $isFr
               ? 'Artisan Hub 237 est une société privée. La plateforme n\'est ni un service public, ni une émanation d\'une administration, et n\'est affiliée à, mandatée par, ou approuvée par aucun ministère, organisme public ou autorité gouvernementale.'
               : 'Artisan Hub 237 is a private company. The platform is not a public service or a government body, and is not affiliated with, mandated by, or endorsed by any ministry, public agency or government authority.' }}
        </p>
    </div>

    <div class="mt-8 pt-6 border-t border-[#ECECEA] dark:border-[#262B21] flex flex-wrap items-center gap-4">
        <p class="text-[12.5px] text-[#8A857A] dark:text-[#868778]">
            {{ $isFr ? 'Une question sur ce document ?' : 'A question about this document?' }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="ui-tap-inset inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#14652F] dark:text-[#339B56] hover:text-[#14532D] hover:dark:text-[#339B56]">
            <i data-lucide="mail" class="w-4 h-4"></i>
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
</div>
@endsection
