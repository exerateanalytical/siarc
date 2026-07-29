{{-- Marketplace sidebar: browse the official taxonomy by sector → filière.
     Expects $lang (from the host view) and $navSectors (shared by AppServiceProvider). --}}
@php $sbLang = $lang ?? app()->getLocale(); $sbFr = $sbLang === 'fr'; @endphp
@if(!empty($navSectors) && $navSectors->count())
<div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden">
    <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[38px]">
        <i data-lucide="layers" class="w-4 h-4 text-white"></i>
        <span class="text-[14px] md:text-[11.5px] font-bold tracking-[0.1em] text-white uppercase">{{ $sbFr ? 'Parcourir par secteur' : 'Browse by sector' }}</span>
    </div>
    <div class="p-2">
        @foreach($navSectors as $sec)
        <details class="group border-b border-[#F4F3EF] dark:border-[#262B21] last:border-0">
            <summary class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-[#F6F7F6] hover:dark:bg-[#0A0C09] cursor-pointer list-none marker:hidden">
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#8A857A] dark:text-[#868778] shrink-0 transition-transform group-open:rotate-90"></i>
                <span class="text-[14px] md:text-[12.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] flex-1 truncate">{{ $sbFr ? $sec->name_fr : ($sec->name_en ?? $sec->name_fr) }}</span>
                <span class="text-[14px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778] shrink-0">{{ $sec->filieres->count() }}</span>
            </summary>
            <div class="pl-[26px] pr-1 pb-1.5">
                <a href="{{ route('industries.index', ['lang' => $sbLang, 'cat' => $sec->slug]) }}" class="block px-2 py-1.5 text-[14px] md:text-[11.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">{{ $sbFr ? 'Tout le secteur →' : 'Whole sector →' }}</a>
                @foreach($sec->filieres as $fil)
                @php $sbFilName = $sbFr ? $fil->name_fr : ($fil->name_en ?? $fil->name_fr); @endphp
                <a href="{{ route('industries.index', ['lang' => $sbLang, 'cat' => $fil->slug]) }}" title="{{ $sbFilName }}" class="block px-2 py-1.5 text-[14px] md:text-[12px] text-[#3A3A35] dark:text-[#F3EFE7] hover:text-leaf hover:dark:text-[#339B56] hover:bg-[#FAF8F2] hover:dark:bg-[#0A0C09] rounded truncate">{{ $sbFilName }}</a>
                @endforeach
            </div>
        </details>
        @endforeach
        <a href="{{ route('industries.index', ['lang' => $sbLang]) }}" class="mt-1 flex items-center gap-1.5 px-2 py-2 text-[14px] md:text-[12px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">
            {{ $sbFr ? 'Toutes les catégories' : 'All categories' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>
@endif
