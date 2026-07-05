{{-- Vendor card in the new-design style (vendors directory replica card anatomy):
     cover with gold type pill + heart, name + green verified check, category,
     location, tagline, "Voir le profil" + message buttons.
     Expects: $business, $lang. --}}
@php
    $bcIsFr = ($lang ?? 'fr') === 'fr';
    $bcUser = session('siac_user');
    $bcUrl = route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]);
    $bcTypes = [
        'cooperative' => 'COOPÉRATIVE',
        'public'      => $bcIsFr ? 'ENTREPRISE' : 'ENTERPRISE',
        'private'     => 'ARTISAN',
    ];
    $bcType = $bcTypes[$business->ownership_type ?? 'private'] ?? 'ARTISAN';
    $bcVerified = in_array($business->verification_tier ?? null, ['verified', 'certified', 'gold']);
@endphp
<article class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)] flex flex-col">
    <div class="relative">
        <a href="{{ $bcUrl }}" class="block bg-[#F5F1E9]">
            @if($business->cover_image)
            <img src="{{ asset('storage/' . $business->cover_image) }}" alt="{{ $business->name_fr }}" class="w-full h-[140px] object-cover">
            @else
            <div class="w-full h-[140px] flex items-center justify-center">
                <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-10 h-10 text-[#D9CFBB]"></i>
            </div>
            @endif
        </a>
        <span class="absolute top-2 left-2 bg-[#EFA912] text-[#3A2A03] text-[10px] font-bold tracking-[0.04em] px-2.5 py-1 rounded-md">{{ $bcType }}</span>
        @if($bcUser)
        <form method="POST" action="{{ route('businesses.toggle-save', $business->slug) }}" class="absolute top-2 right-2">
            @csrf
            <input type="hidden" name="return_to" value="{{ url()->full() }}">
            <button type="submit" aria-label="{{ $bcIsFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                class="w-8 h-8 rounded-full bg-white/90 hover:bg-white shadow flex items-center justify-center text-[#55524A] hover:text-[#C0010C] transition-colors">
                <i data-lucide="heart" class="w-4 h-4" style="stroke-width:1.8"></i>
            </button>
        </form>
        @else
        <a href="/login?lang={{ $lang }}" aria-label="{{ $bcIsFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
            class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 hover:bg-white shadow flex items-center justify-center text-[#55524A] hover:text-[#C0010C] transition-colors">
            <i data-lucide="heart" class="w-4 h-4" style="stroke-width:1.8"></i>
        </a>
        @endif
    </div>
    <div class="p-3.5 flex-1 flex flex-col">
        <h3 class="flex items-center gap-1.5 text-[13.5px] font-bold text-[#1D1B16]">
            <a href="{{ $bcUrl }}" class="truncate hover:text-leaf transition-colors">
                {{ $bcIsFr ? $business->name_fr : ($business->name_en ?? $business->name_fr) }}
            </a>
            @if($bcVerified)
            <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0" aria-label="{{ $bcIsFr ? 'Vérifié' : 'Verified' }}">
                <circle cx="8" cy="8" r="8" fill="#17A34A"/>
                <path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            @endif
        </h3>
        @if($business->industry)
        <p class="mt-1 text-[11.5px] text-[#55524A]">{{ $bcIsFr ? $business->industry->name_fr : ($business->industry->name_en ?? $business->industry->name_fr) }}</p>
        @endif
        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#6F6B60]">
            <i data-lucide="map-pin" class="w-[12px] h-[12px]"></i>
            {{ $business->city->name_fr ?? ($business->region->name_fr ?? ($bcIsFr ? 'Cameroun' : 'Cameroon')) }}
        </p>
        @if($business->tagline_fr)
        <p class="mt-2 text-[11.5px] text-[#55524A] leading-relaxed line-clamp-2">
            {{ $bcIsFr ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr) }}
        </p>
        @endif
        <div class="mt-auto pt-3.5 flex items-center gap-2">
            <a href="{{ $bcUrl }}"
                class="flex-1 h-[34px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[12px] font-semibold text-[#1D1B16] transition-colors">
                {{ $bcIsFr ? 'Voir le profil' : 'View profile' }}
            </a>
            <a href="{{ $bcUser ? route('messages.compose', ['business' => $business->slug, 'lang' => $lang]) : '/login?lang=' . $lang }}" aria-label="{{ $bcIsFr ? 'Envoyer un message' : 'Send a message' }}"
                class="w-[38px] h-[34px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[#55524A] transition-colors">
                <i data-lucide="message-square" class="w-[15px] h-[15px]"></i>
            </a>
        </div>
    </div>
</article>
