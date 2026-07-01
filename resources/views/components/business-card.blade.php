<a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
    class="group bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-gray-300 hover:shadow-md transition-all flex flex-col">

    <!-- Cover / header area -->
    <div class="relative bg-gradient-to-br from-gray-100 to-gray-200 h-32 overflow-hidden">
        @if($business->cover_path)
        <img src="{{ Storage::url($business->cover_path) }}" alt="" class="w-full h-full object-cover">
        @else
        <div class="w-full h-full flex items-center justify-center">
            <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-12 h-12 text-gray-300"></i>
        </div>
        @endif

        <!-- Tier badge -->
        <div class="absolute top-2 right-2">
            @if($business->verification_tier === 'certified')
            <span class="inline-flex items-center gap-1 bg-brand-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                <i data-lucide="shield-check" class="w-2.5 h-2.5"></i>
                {{ $lang === 'fr' ? 'Certifié' : 'Certified' }}
            </span>
            @elseif($business->verification_tier === 'verified')
            <span class="inline-flex items-center gap-1 bg-forest-500 text-white text-xs font-medium px-2 py-0.5 rounded-full">
                <i data-lucide="shield" class="w-2.5 h-2.5"></i>
                {{ $lang === 'fr' ? 'Vérifié' : 'Verified' }}
            </span>
            @endif
        </div>

        <!-- Logo -->
        <div class="absolute -bottom-5 left-4">
            <div class="w-12 h-12 rounded-xl bg-white shadow border border-gray-200 flex items-center justify-center overflow-hidden">
                @if($business->logo_path)
                <img src="{{ Storage::url($business->logo_path) }}" alt="{{ $business->name_fr }}" class="w-full h-full object-cover">
                @else
                <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-6 h-6 text-gray-400"></i>
                @endif
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="pt-8 pb-4 px-4 flex-1 flex flex-col">
        <div class="flex items-start justify-between gap-2 mb-1">
            <h3 class="font-semibold text-gray-900 text-sm leading-tight line-clamp-2 group-hover:text-brand-600 transition-colors">
                {{ $lang === 'fr' ? $business->name_fr : ($business->name_en ?? $business->name_fr) }}
            </h3>
        </div>

        @if($business->tagline_fr)
        <p class="text-xs text-gray-500 line-clamp-2 mb-3">
            {{ $lang === 'fr' ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr) }}
        </p>
        @endif

        <div class="mt-auto flex items-center justify-between gap-2">
            <div class="flex items-center gap-1 text-xs text-gray-400 min-w-0 truncate">
                <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                <span class="truncate">{{ $business->city->name_fr ?? ($business->region->name_fr ?? '') }}</span>
            </div>
            <div class="flex items-center gap-1 text-xs text-brand-600 font-medium group-hover:gap-2 transition-all shrink-0">
                {{ $lang === 'fr' ? 'Voir' : 'View' }}
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </div>
        </div>
    </div>
</a>
