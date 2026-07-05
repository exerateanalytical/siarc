@extends('layouts.admin')

@php
    $isFr = $fr ?? (($lang ?? 'fr') === 'fr');
    $adminActive = 'siarc-plan';
    $pageTitle = $isFr ? 'Plan Interactif' : 'Interactive Floor Plan';
    $pageSubtitle = $isFr ? 'Pavillons & stands du SIARC 2026' : 'SIARC 2026 pavilions & stands';
    $standsByPav = $stands->groupBy('pavilion_id');
    $stTone = [
        'available' => ['#F5F5F2', '#B9B4A9', '#6F6B60'],
        'allocated' => ['#E2F3E8', '#9AD1B0', '#157A43'],
        'reserved'  => ['#E8EFFB', '#B3C7F0', '#3565DE'],
    ];
@endphp

@section('content')
<div class="max-w-[1400px]">

    {{-- Legend + stats --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-4 text-[12px] text-[#3B382F]">
            @foreach(['allocated' => ($isFr ? 'Alloué' : 'Allocated'), 'reserved' => ($isFr ? 'Réservé' : 'Reserved'), 'available' => ($isFr ? 'Disponible' : 'Available')] as $st => $lbl)
            <span class="flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded" style="background: {{ $stTone[$st][0] }}; border: 1px solid {{ $stTone[$st][1] }}"></span>{{ $lbl }}</span>
            @endforeach
        </div>
        <span class="text-[12px] text-[#8A857A]">{{ $stands->where('status', 'allocated')->count() }}/{{ $stands->count() }} {{ $isFr ? 'stands alloués' : 'stands allocated' }}</span>
    </div>

    @forelse($pavs as $p)
    <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5 mb-4">
        <div class="flex items-center gap-2.5 mb-3.5">
            <span class="w-4 h-4 rounded" style="background-color: {{ $p->color ?? '#0F4824' }}"></span>
            <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr) }}</h2>
            <span class="text-[11px] text-[#8A857A]">{{ optional($standsByPav->get($p->id))->count() ?? 0 }} stands</span>
        </div>
        <div class="flex flex-wrap gap-2">
            @forelse($standsByPav->get($p->id, collect()) as $s)
            @php $t = $stTone[$s->status] ?? $stTone['available']; @endphp
            <a href="{{ route('siarc.admin.stand', $s->id) }}" title="{{ $s->code }} — {{ $s->name_fr ?? ($isFr ? 'Libre' : 'Free') }}"
               class="w-[62px] h-[46px] rounded-lg border flex flex-col items-center justify-center hover:ring-2 hover:ring-[#157A43]/30 transition"
               style="background: {{ $t[0] }}; border-color: {{ $t[1] }}">
                <span class="text-[10px] font-bold" style="color: {{ $t[2] }}">{{ $s->code }}</span>
                @if($s->name_fr)<span class="text-[7.5px] leading-tight px-1 truncate max-w-[58px]" style="color: {{ $t[2] }}">{{ \Illuminate\Support\Str::limit($s->name_fr, 14) }}</span>@endif
            </a>
            @empty
            <p class="text-[12px] text-[#8A857A] py-2">{{ $isFr ? 'Aucun stand dans ce pavillon.' : 'No stands in this pavilion.' }}</p>
            @endforelse
        </div>
    </div>
    @empty
    <div class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-12 text-center text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucun pavillon configuré.' : 'No pavilion configured.' }}</div>
    @endforelse

    <div class="bg-white border border-[#EFF0EF] rounded-2xl p-5 mb-5">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('siarc.admin.stands') }}" class="inline-flex items-center gap-2 bg-[#F6F7F6] hover:bg-[#E2F3E8] text-[12.5px] font-medium text-[#3B382F] px-3.5 py-2 rounded-lg"><i data-lucide="grid-3x3" class="w-4 h-4 text-[#157A43]"></i>{{ $isFr ? 'Allocation des stands' : 'Stand allocation' }}</a>
            <a href="{{ route('siarc.admin.pavilions') }}" class="inline-flex items-center gap-2 bg-[#F6F7F6] hover:bg-[#E2F3E8] text-[12.5px] font-medium text-[#3B382F] px-3.5 py-2 rounded-lg"><i data-lucide="layout-grid" class="w-4 h-4 text-[#157A43]"></i>{{ $isFr ? 'Pavillons' : 'Pavilions' }}</a>
        </div>
    </div>

    <div class="flex items-start gap-2.5 bg-[#FBF7EC] border border-[#EFE4C8] rounded-xl px-4 py-3 text-[12px] text-[#7A5A12]">
        <i data-lucide="palette" class="w-4 h-4 mt-0.5 shrink-0"></i>
        <span>{{ $isFr ? 'Plan fonctionnel connecté aux stands réels — le plan interactif glisser-déposer sera finalisé avec la maquette.' : 'Functional plan wired to real stands — the drag-and-drop interactive map will be finalised with the mockup.' }}</span>
    </div>
</div>
@endsection
