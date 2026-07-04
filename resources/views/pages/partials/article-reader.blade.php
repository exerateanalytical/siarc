{{-- Shared article reader (news/announcement detail). Expects: $lang, $isFr,
     $article, $related, $categoryCounts, $totalArticles, $publicMode (bool). --}}
@php
    $publicMode = $publicMode ?? false;
    $aTitle = $isFr ? $article->title_fr : ($article->title_en ?? $article->title_fr);
    $aExcerpt = $isFr ? ($article->excerpt_fr ?? '') : ($article->excerpt_en ?? $article->excerpt_fr ?? '');
    $aBody = $isFr ? ($article->body_fr ?? '') : ($article->body_en ?? $article->body_fr ?? '');
    $monthsFr = [1=>'Jan',2=>'Fév',3=>'Mars',4=>'Avr',5=>'Mai',6=>'Juin',7=>'Juil',8=>'Août',9=>'Sept',10=>'Oct',11=>'Nov',12=>'Déc'];
    $adate = function ($v) use ($isFr, $monthsFr) { if(!$v) return '—'; $d=\Carbon\Carbon::parse($v); return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y'); };
    $cover = $article->cover_image ? (str_contains($article->cover_image, '/') ? asset('storage/'.$article->cover_image) : asset('images/landing/'.$article->cover_image)) : asset('images/landing/event-1.png');
    $paragraphs = array_filter(array_map('trim', preg_split('/\n+/', $aBody ?: $aExcerpt)));
    $catColors = ['Événements'=>'#3565DE','Artisanat'=>'#157A43','Annonces'=>'#C97A16','Culture'=>'#7C4FE0','Programmes'=>'#0E9F9F','Portraits'=>'#9B1C31'];
    $tags = array_filter([$article->category, $article->type, 'SIARC 2026', 'Cameroun']);
    $linkFor = fn ($rel) => $publicMode ? route('news.show', ['slug' => $rel->slug, 'lang' => $lang]) : route('admin.news.detail', ['id' => $rel->id, 'lang' => $lang]);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-7 items-start">
    {{-- Article --}}
    <article>
        <nav class="flex items-center gap-2 text-[12px] text-[#8A857A]">
            <a href="{{ route('home', ['lang'=>$lang]) }}" class="hover:text-[#14652F]">{{ $isFr?'Accueil':'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ $publicMode ? route('news.index', ['lang'=>$lang]) : route('admin.news', ['lang'=>$lang]) }}" class="hover:text-[#14652F]">{{ $isFr?'Actualités':'News' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i><span class="text-[#55524A]">{{ $isFr?'Détail de l\'actualité':'Article detail' }}</span>
        </nav>

        <span class="mt-4 inline-block rounded-md px-3 py-1 text-[10.5px] font-bold tracking-[0.08em] uppercase text-white" style="background-color: {{ $catColors[$article->category] ?? '#157A43' }}">{{ $article->category ?? ($isFr?'Actualité':'News') }}</span>
        <h1 class="mt-3 font-serif text-[30px] sm:text-[38px] font-bold text-[#0E3D22] leading-[1.1]">{{ $aTitle }}</h1>
        <p class="mt-2 text-[#C9942E] text-[13px] tracking-[0.25em]">❈ ❈ ❈ ❈ ❈ ❈</p>

        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-[12px] text-[#55524A]">
            <span class="flex items-center gap-2"><span class="w-7 h-7 rounded-full bg-[#14652F] text-white text-[11px] font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr($article->author_name ?? 'A',0,1)) }}</span>{{ $isFr?'Par':'By' }} <b class="text-[#1D1B16]">{{ $article->author_name ?? 'Admin' }}</b></span>
            <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-4 h-4 text-[#C9942E]"></i>{{ $adate($article->published_at ?? $article->created_at) }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4 text-[#C9942E]"></i>{{ \Carbon\Carbon::parse($article->published_at ?? $article->created_at)->format('H:i') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="eye" class="w-4 h-4 text-[#C9942E]"></i>{{ number_format($article->views_count ?? 0, 0, ',', ' ') }} {{ $isFr?'vues':'views' }}</span>
        </div>

        <div class="mt-5 relative rounded-2xl overflow-hidden border-4 border-[#8A5A2B]/30">
            <img src="{{ $cover }}" alt="{{ $aTitle }}" class="w-full h-[300px] object-cover">
        </div>

        @if($aExcerpt)
        <p class="mt-6 text-[14px] text-[#3B382F] leading-relaxed font-medium">{{ $aExcerpt }}</p>
        @endif

        @foreach($paragraphs as $i => $para)
        @if($i === 1)
        <div class="my-5 bg-[#F6F1E4] border-l-4 border-[#C9942E] rounded-r-xl px-5 py-4"><p class="text-[12.5px] text-[#3B382F] leading-relaxed italic">{{ $para }}</p></div>
        @else
        <p class="mt-4 text-[13px] text-[#3B382F] leading-relaxed">{{ $para }}</p>
        @endif
        @endforeach

        {{-- Share + tags --}}
        @php
            $shareUrl = urlencode(url()->current());
            $shareIcons = [
                ['https://www.facebook.com/sharer/sharer.php?u='.$shareUrl, 'Facebook', '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>'],
                ['https://twitter.com/intent/tweet?url='.$shareUrl, 'X', '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>'],
                ['https://wa.me/?text='.$shareUrl, 'WhatsApp', '<path d="M10 2a8 8 0 0 0-6.8 12.2L2 18l3.9-1.2A8 8 0 1 0 10 2zm4.6 11.3c-.2.6-1 1.1-1.6 1.2-.4.1-1 .1-2.8-.6-2.3-.9-3.8-3.3-3.9-3.4-.1-.2-1-1.3-1-2.4s.6-1.7.8-1.9c.2-.2.4-.3.6-.3h.4c.1 0 .3 0 .5.4l.7 1.6c0 .1.1.3 0 .4l-.6.8c-.1.2-.2.3-.1.5.5.9 1.6 1.7 2.5 2.1.2.1.3.1.4-.1l.7-.9c.1-.2.3-.1.4-.1l1.6.7c.2.1.4.2.4.3 0 .1 0 .6-.2 1z"/>'],
                ['https://t.me/share/url?url='.$shareUrl, 'Telegram', '<path d="M17.5 3 2.5 8.9c-.9.4-.9 1 0 1.3l3.8 1.2 1.5 4.5c.2.5.4.6.8.3l2.1-1.9 3.9 2.9c.5.3.9.1 1-.5l2.5-11.7c.2-.7-.3-1.1-.9-.7z"/>'],
                ['https://www.linkedin.com/sharing/share-offsite/?url='.$shareUrl, 'LinkedIn', '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z"/>'],
            ];
        @endphp
        <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-[#EDE6D6] pt-5">
            <span class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr?'Partager cet article':'Share this article' }}</span>
            @foreach($shareIcons as [$sUrl, $sNet, $sPath])
            <a href="{{ $sUrl }}" target="_blank" rel="noopener" aria-label="{{ $sNet }}" class="w-8 h-8 rounded-full bg-[#F3F0E6] flex items-center justify-center text-[#14652F] hover:bg-[#E2F3E8]"><svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">{!! $sPath !!}</svg></a>
            @endforeach
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr?'Mots-clés':'Tags' }} :</span>
            @foreach($tags as $tag)<a href="{{ $publicMode ? route('news.index', ['lang'=>$lang]) : route('admin.news', ['lang'=>$lang]) }}" class="rounded-md bg-[#F3F0E6] px-2.5 py-1 text-[11px] font-medium text-[#3B382F] hover:text-[#14652F]">{{ $tag }}</a>@endforeach
        </div>

        {{-- Prev / next --}}
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            @php $prev = $related->first(); $next = $related->skip(1)->first(); @endphp
            @if($prev)<a href="{{ $linkFor($prev) }}" class="flex items-center gap-3 border border-[#EDE6D6] rounded-xl px-4 py-3 hover:border-[#14652F] group"><i data-lucide="chevron-left" class="w-4 h-4 text-[#8A857A]"></i><span><span class="block text-[10px] text-[#8A857A] uppercase">{{ $isFr?'Article précédent':'Previous' }}</span><span class="block text-[12px] font-semibold text-[#1D1B16] group-hover:text-[#14652F] line-clamp-1">{{ $isFr ? $prev->title_fr : ($prev->title_en ?? $prev->title_fr) }}</span></span></a>@endif
            @if($next)<a href="{{ $linkFor($next) }}" class="flex items-center justify-end gap-3 text-right border border-[#EDE6D6] rounded-xl px-4 py-3 hover:border-[#14652F] group"><span><span class="block text-[10px] text-[#8A857A] uppercase">{{ $isFr?'Article suivant':'Next' }}</span><span class="block text-[12px] font-semibold text-[#1D1B16] group-hover:text-[#14652F] line-clamp-1">{{ $isFr ? $next->title_fr : ($next->title_en ?? $next->title_fr) }}</span></span><i data-lucide="chevron-right" class="w-4 h-4 text-[#8A857A]"></i></a>@endif
        </div>
    </article>

    {{-- Rail --}}
    <aside class="space-y-5">
        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5">
            <h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1D1B16] uppercase">{{ $isFr?'À propos du SIARC 2026':'About SIARC 2026' }}</h2>
            <div class="mt-3 flex items-center gap-3"><span class="w-11 h-11 rounded-lg bg-[#F6F1E4] flex items-center justify-center"><i data-lucide="sparkles" class="w-5 h-5 text-[#C9942E]"></i></span><div><p class="text-[13px] font-bold text-[#1D1B16]">SIARC 2026</p><p class="text-[10px] text-[#6F6B60] leading-tight">{{ $isFr?'Salon International de l\'Artisanat du Cameroun':'International Craft Fair of Cameroon' }}</p></div></div>
            <p class="mt-3 text-[11.5px] text-[#55524A] leading-relaxed">{{ $isFr ? 'Un carrefour international dédié à la promotion, à la valorisation et à la commercialisation de l\'artisanat camerounais et africain.' : 'An international hub dedicated to promoting and commercialising Cameroonian and African craftsmanship.' }}</p>
            <a href="{{ route('events.show', ['slug'=>'siarc-2026', 'lang'=>$lang]) }}" class="mt-3 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr?'Visiter le site officiel':'Visit official site' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </section>

        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5">
            <h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1D1B16] uppercase">{{ $isFr?'Articles similaires':'Related articles' }}</h2>
            <div class="mt-3 space-y-3">
                @foreach($related as $rel)
                <a href="{{ $linkFor($rel) }}" class="flex items-center gap-3 group">
                    <img src="{{ $rel->cover_image ? (str_contains($rel->cover_image,'/') ? asset('storage/'.$rel->cover_image) : asset('images/landing/'.$rel->cover_image)) : asset('images/landing/event-2.png') }}" alt="" class="w-14 h-14 rounded-lg object-cover shrink-0">
                    <span class="min-w-0"><span class="block text-[12px] font-semibold text-[#1D1B16] leading-snug line-clamp-2 group-hover:text-[#14652F]">{{ $isFr ? $rel->title_fr : ($rel->title_en ?? $rel->title_fr) }}</span><span class="block mt-1 text-[10.5px] text-[#8A857A]"><i data-lucide="calendar" class="inline w-3 h-3"></i> {{ $adate($rel->published_at ?? $rel->created_at) }}</span></span>
                </a>
                @endforeach
            </div>
            <a href="{{ $publicMode ? route('news.index', ['lang'=>$lang]) : route('admin.news', ['lang'=>$lang]) }}" class="mt-3 block text-center border border-[#E5E7E5] hover:border-[#14652F] rounded-lg py-2 text-[12px] font-semibold text-[#3B382F]">{{ $isFr?'Voir toutes les actualités':'View all news' }}</a>
        </section>

        <section class="bg-white border border-[#EDE6D6] rounded-2xl px-5 py-5">
            <h2 class="text-[12px] font-bold tracking-[0.06em] text-[#1D1B16] uppercase">{{ $isFr?'Catégories':'Categories' }}</h2>
            <div class="mt-3 space-y-1">
                <a href="{{ $publicMode ? route('news.index', ['lang'=>$lang]) : route('admin.news', ['lang'=>$lang]) }}" class="flex items-center justify-between py-1.5 text-[12.5px]"><span class="font-semibold text-[#1D1B16]">{{ $isFr?'Tous les articles':'All articles' }}</span><span class="text-[#8A857A]">{{ $totalArticles }}</span></a>
                @foreach($categoryCounts as $cat => $n)
                <a href="{{ $publicMode ? route('news.index', ['lang'=>$lang]) : route('admin.news', ['lang'=>$lang, 'categorie'=>$cat]) }}" class="flex items-center justify-between py-1.5 text-[12.5px] text-[#3B382F] hover:text-[#14652F]"><span>{{ $cat }}</span><span class="text-[#8A857A]">{{ $n }}</span></a>
                @endforeach
            </div>
        </section>

        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-[#0E3D22] to-[#123D24] px-5 py-6 text-center">
            <p class="font-serif text-[15px] leading-relaxed text-white italic">{{ $isFr ? 'L\'artisanat est le miroir de notre âme collective. Préservons-le, transmettons-le, faisons-le rayonner.' : 'Craftsmanship is the mirror of our collective soul. Let us preserve it, pass it on, make it shine.' }}</p>
            <p class="mt-3 text-[#E9C25A] text-[12px]">◇ ❈ ◇</p>
        </section>
    </aside>
</div>
