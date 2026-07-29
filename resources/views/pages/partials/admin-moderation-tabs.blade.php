{{-- The moderation desk's two registers, on one strip.

     They were two unrelated screens: /moderation carried a "Signalements" tab
     and an "Avis" tab, and /avis was a separate, far more capable reviews queue
     (publish, refuse, withdraw, delete, plus the distinctions register) that
     nothing in the navigation pointed at. The weaker copy of the reviews list
     has been removed; this strip is what makes the two surviving screens read
     as one desk. Both routes still exist, so nobody's bookmark breaks.

     Expects: $isFr. Optional: $modTab ('reports' | 'reviews'). --}}
@php
    $modTab = $modTab ?? (request()->routeIs('admin.reviews') ? 'reviews' : 'reports');

    $modCounts = \Illuminate\Support\Facades\Cache::remember('admin_moderation_tab_counts', 60, function () {
        return [
            'reports' => (int) \Illuminate\Support\Facades\DB::table('product_reports')->where('status', 'open')->count(),
            'reviews' => (int) \Illuminate\Support\Facades\DB::table('business_reviews')
                ->where('status', \App\Support\ArtisanReviews::PENDING)->count(),
        ];
    });

    $modTabs = [
        ['reports', 'flag', $isFr ? 'Signalements' : 'Reports',      route('admin.moderation'), $modCounts['reports']],
        ['reviews', 'star', $isFr ? 'Avis & distinctions' : 'Reviews & distinctions', route('admin.reviews'), $modCounts['reviews']],
    ];
@endphp

<div class="flex items-center gap-2 mb-5">
    @foreach($modTabs as [$mtKey, $mtIcon, $mtLabel, $mtUrl, $mtCount])
    <a href="{{ $mtUrl }}"
       @if($mtKey === $modTab) aria-current="page" @endif
       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $mtKey === $modTab ? 'bg-[#157A43] dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'bg-white dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] hover:bg-[#F8F4EC] dark:hover:bg-[#242A1E]' }}">
        <i data-lucide="{{ $mtIcon }}" class="w-3.5 h-3.5"></i>
        {{ $mtLabel }}
        <span class="{{ $mtKey === $modTab ? 'bg-white/20 dark:bg-black/15' : 'bg-[#F1EDE3] dark:bg-[#1A1E16]' }} px-1.5 rounded-full text-[10px]">{{ $mtCount }}</span>
    </a>
    @endforeach
</div>
