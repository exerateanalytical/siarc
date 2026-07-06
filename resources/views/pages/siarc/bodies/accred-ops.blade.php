@php
    // ══ Spec-driven accreditation operations page (see config/siarc_accred_ops.php).
    //    Renders in the approved Accreditation design language; swap for a bespoke
    //    body once the page's design PNG is approved. ══
    $spec = config('siarc_accred_ops')[request()->route()?->getName() ?? ''] ?? null;
    abort_if(! $spec, 404);
    $resolve = function ($action) use ($lang) {
        if (str_starts_with($action, 'route:')) return ['href', route(substr($action, 6), ['lang' => $lang])];
        if ($action === 'reload') return ['reload', null];
        return ['toast', substr($action, 6)];
    };
    $rowToasts = [
        'reader' => 'Détail du lecteur (démo)', 'gate' => 'Détail de la porte (démo)', 'rule' => 'Détail de la règle (démo)',
        'failure' => 'Dossier du refus (démo)', 'override' => 'Dossier d\'override (démo)', 'lost' => 'Dossier du badge (démo)',
        'activation' => 'Changement d\'état réservé à la production', 'replace' => 'Dossier de remplacement (démo)',
    ];
    $rowLinks = ['reader' => 'siarc.admin.accred.reader', 'gate' => 'siarc.admin.accred.gate', 'rule' => 'siarc.admin.accred.rule'];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">{{ $spec['title'] }}</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">
            @foreach($spec['crumb'] as $c)
                @if(!$loop->first)<i data-lucide="chevron-right" class="w-3 h-3"></i>@endif
                <span class="{{ $loop->last ? 'text-[#3B382F]' : '' }}">{{ $c }}</span>
            @endforeach
        </p>
    </div>
    <div class="flex items-center gap-2.5">
        @foreach($spec['actions'] as [$ic,$lbl,$action,$style])
            @php [$kind,$val] = $resolve($action); @endphp
            @if($kind === 'href')
                <a href="{{ $val }}" class="siarc-btn text-[13px] px-4 py-2.5 rounded-lg {{ $style === 'green' ? 'siarc-btn-green' : 'text-[#3B382F] border border-[#EFEDE6] bg-white hover:border-[#D8E5DC]' }}"><i data-lucide="{{ $ic }}" class="w-4 h-4 {{ $style === 'green' ? '' : 'text-[#8A857A]' }}"></i>{{ $lbl }}</a>
            @elseif($kind === 'reload')
                <button onclick="window.location.reload()" class="siarc-btn text-[13px] px-4 py-2.5 rounded-lg {{ $style === 'green' ? 'siarc-btn-green' : 'text-[#3B382F] border border-[#EFEDE6] bg-white hover:border-[#D8E5DC]' }}"><i data-lucide="{{ $ic }}" class="w-4 h-4 {{ $style === 'green' ? '' : 'text-[#8A857A]' }}"></i>{{ $lbl }}</button>
            @else
                <button data-toast="{{ $val }}" class="siarc-btn text-[13px] px-4 py-2.5 rounded-lg {{ $style === 'green' ? 'siarc-btn-green' : 'text-[#3B382F] border border-[#EFEDE6] bg-white hover:border-[#D8E5DC]' }}"><i data-lucide="{{ $ic }}" class="w-4 h-4 {{ $style === 'green' ? '' : 'text-[#8A857A]' }}"></i>{{ $lbl }}</button>
            @endif
        @endforeach
    </div>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    @foreach($spec['stats'] as [$icon,$col,$tile,$value,$label,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[19px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }} <span class="text-[#B0AB9F]">{{ $sub }}</span></span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5 items-start">

    {{-- ══ LEFT: table card ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <div class="px-5 pt-4 pb-3 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[12px] font-bold tracking-[0.08em] text-[#131313] uppercase">{{ $spec['tableTitle'] ?? $spec['title'] }}</p>
            <div class="relative min-w-[210px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#opsScope" placeholder="Rechercher..." class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[760px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach($spec['heads'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last && $h === 'ACTIONS' ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="opsScope">
                    @foreach($spec['rows'] as $row)
                    <tr data-filter-item class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        @foreach($row as $cell)
                        @php $ct = $cell[0]; @endphp
                        <td class="px-4 py-3 {{ $ct === 'act' ? 'text-right' : '' }}">
                            @if($ct === 't')
                                <span class="text-[12px] text-[#3B382F] whitespace-nowrap">{{ $cell[1] }}</span>
                            @elseif($ct === 't2')
                                <span class="block text-[13px] font-semibold text-[#131313]">{{ $cell[1] }}</span>
                                <span class="block text-[11px] text-[#8A857A]">{{ $cell[2] ?? '' }}</span>
                            @elseif($ct === 'chip')
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md whitespace-nowrap" style="color:{{ $cell[2] }};background:{{ $cell[3] }}">{{ $cell[1] }}</span>
                            @elseif($ct === 'dot')
                                <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold whitespace-nowrap" style="color:{{ $cell[2] }}"><span class="w-2 h-2 rounded-full" style="background:{{ $cell[2] }}"></span>{{ $cell[1] }}</span>
                            @elseif($ct === 'ok')
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#157A43]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $cell[1] }}</span>
                            @elseif($ct === 'ko')
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#C0010C]"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i>{{ $cell[1] }}</span>
                            @elseif($ct === 'prog')
                                <span class="flex items-center gap-2 min-w-[90px]">
                                    <span class="flex-1 h-[5px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $cell[1] }}%;background:{{ $cell[2] }}"></span></span>
                                    <span class="text-[10.5px] text-[#8A857A]">{{ $cell[1] }}%</span>
                                </span>
                            @elseif($ct === 'act')
                                <span class="flex items-center justify-end gap-1">
                                    @if(isset($rowLinks[$cell[1]]))
                                        <a href="{{ route($rowLinks[$cell[1]], ['lang' => $lang]) }}" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></a>
                                    @else
                                        <button data-toast="{{ $rowToasts[$cell[1]] ?? 'Détail (démo)' }}" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></button>
                                    @endif
                                    <button data-toast="Menu contextuel à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-4 h-4 text-[#8A857A]"></i></button>
                                </span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div data-page="#opsScope" data-page-size="10" class="px-5 py-3.5 border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]"></p>
        </div>
    </div>

    {{-- ══ RIGHT RAIL ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">{{ $spec['railTitle'] }}</p>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">{{ $spec['railBadge'] }}</span>
        </div>
        <div class="space-y-4">
            @foreach($spec['rail'] as $section)
                @if($section[0] === 'dl')
                    <dl class="space-y-2.5 text-[12px]">
                        @foreach($section[1] as [$k,$v])
                        <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#3B382F] text-right">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                @elseif($section[0] === 'chips')
                    <div>
                        <p class="text-[11.5px] text-[#8A857A] mb-1.5">{{ $section[1] }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($section[2] as [$lbl,$c,$bg])
                            <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md" style="color:{{ $c }};background:{{ $bg }}">{{ $lbl }}</span>
                            @endforeach
                        </div>
                    </div>
                @elseif($section[0] === 'bars')
                    <div class="space-y-2.5">
                        @foreach($section[1] as [$lbl,$pct,$c])
                        <div>
                            <div class="flex items-center justify-between text-[11.5px] mb-1"><span class="text-[#3B382F]">{{ $lbl }}</span><span class="text-[#8A857A]">{{ $pct }}{{ is_int($pct) && $pct <= 100 ? '%' : '' }}</span></div>
                            <span class="block h-[6px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ min(100, $pct) }}%;background:{{ $c }}"></span></span>
                        </div>
                        @endforeach
                    </div>
                @elseif($section[0] === 'steps')
                    <div class="space-y-3">
                        @foreach($section[1] as [$n,$t,$s])
                        <div class="flex items-start gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-[#E8F5EC] text-[#157A43] text-[11px] font-bold flex items-center justify-center shrink-0">{{ $n }}</span>
                            <span><span class="block text-[12.5px] font-semibold text-[#131313]">{{ $t }}</span><span class="block text-[11px] text-[#8A857A]">{{ $s }}</span></span>
                        </div>
                        @endforeach
                    </div>
                @elseif($section[0] === 'alert')
                    <div class="rounded-lg border px-3.5 py-3" style="background:{{ $section[4] }};border-color:{{ $section[3] }}33">
                        <p class="text-[12px] font-bold" style="color:{{ $section[3] }}">{{ $section[1] }}</p>
                        <p class="text-[11.5px] text-[#3B382F] mt-0.5 leading-snug">{{ $section[2] }}</p>
                    </div>
                @elseif($section[0] === 'btn')
                    @php [$kind,$val] = $resolve($section[3]); @endphp
                    @if($kind === 'href')
                        <a href="{{ $val }}" class="w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="{{ $section[1] }}" class="w-4 h-4"></i>{{ $section[2] }}</a>
                    @else
                        <button data-toast="{{ $val }}" class="w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="{{ $section[1] }}" class="w-4 h-4"></i>{{ $section[2] }}</button>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
</div>
