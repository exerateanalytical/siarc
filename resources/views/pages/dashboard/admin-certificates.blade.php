@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'certificates';
    $pageTitle = $isFr ? 'Certificats d\'Adhésion' : 'Membership Certificates';

    $stateTone = [
        'active'    => ['#E2F3E8', '#157A43', $isFr ? 'Actif' : 'Active'],
        'expired'   => ['#FDF3E0', '#C97A16', $isFr ? 'Expiré' : 'Expired'],
        'revoked'   => ['#FDE8E8', '#DC2626', $isFr ? 'Révoqué' : 'Revoked'],
        'suspended' => ['#FDE8E8', '#DC2626', $isFr ? 'Suspendu' : 'Suspended'],
    ];
    $cards = [
        ['award', '#157A43', '#E2F3E8', $kpis['total'], $isFr ? 'Certificats émis' : 'Certificates issued'],
        ['check-circle-2', '#157A43', '#E2F3E8', $kpis['active'], $isFr ? 'Actifs' : 'Active'],
        ['pause-circle', '#C97A16', '#FDF3E0', $kpis['suspended'], $isFr ? 'Suspendus' : 'Suspended'],
        ['ban', '#DC2626', '#FDE8E8', $kpis['revoked'], $isFr ? 'Révoqués' : 'Revoked'],
    ];
    $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->translatedFormat('d M Y') : '—';
@endphp

@section('content')
<div class="max-w-[1400px]">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="flex items-center gap-1.5 text-[11.5px] text-[#8A857A]">
            <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i><span class="text-[#55524A]">{{ $pageTitle }}</span>
        </p>
        <a href="{{ route('certificate.verify') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white border border-[#E7E7E5] hover:border-[#14652F] text-[#3B382F] text-[12.5px] font-semibold px-3.5 py-2 rounded-lg"><i data-lucide="shield-check" class="w-4 h-4"></i>{{ $isFr ? 'Page de vérification' : 'Verification page' }}</a>
    </div>

    @if(session('cert_updated'))
    <div class="mb-4 flex items-center gap-2 bg-[#E9F6EE] border border-[#BFE3CD] rounded-xl px-4 py-2.5 text-[12.5px] text-[#0F5B30]"><i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ $isFr ? 'Certificat mis à jour.' : 'Certificate updated.' }}</div>
    @endif

    <section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        @foreach($cards as [$ccIcon, $ccColor, $ccTile, $ccValue, $ccLabel])
        <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
            <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $ccTile }}"><i data-lucide="{{ $ccIcon }}" class="w-[19px] h-[19px]" style="color: {{ $ccColor }};stroke-width:1.8"></i></span>
            <p class="mt-3 text-[22px] font-extrabold text-[#1B1B18] leading-none">{{ number_format($ccValue) }}</p>
            <p class="mt-1 text-[11.5px] font-semibold text-[#3B382F]">{{ $ccLabel }}</p>
        </div>
        @endforeach
    </section>

    <div class="bg-white border border-[#EFF0EF] rounded-2xl overflow-hidden">
        <form method="GET" class="px-5 py-3.5 flex flex-wrap items-center gap-2.5 border-b border-[#F1F1EF]">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="flex items-center gap-2 bg-[#F8F8F6] border border-[#E7E7E5] rounded-lg px-3 h-[38px] flex-1 min-w-[180px]">
                <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher un artisan ou un numéro...' : 'Search an artisan or number...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none">
                <button type="submit"><i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i></button>
            </div>
            <select name="status" onchange="this.form.submit()" class="h-[38px] text-[12.5px] border border-[#E7E7E5] rounded-lg px-2.5 bg-white">
                <option value="">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                @foreach(['active' => $isFr ? 'Actifs' : 'Active', 'expired' => $isFr ? 'Expirés' : 'Expired', 'revoked' => $isFr ? 'Révoqués' : 'Revoked', 'suspended' => $isFr ? 'Suspendus' : 'Suspended'] as $sv => $sl)
                <option value="{{ $sv }}" @selected($filter === $sv)>{{ $sl }}</option>
                @endforeach
            </select>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead><tr class="text-[10.5px] font-bold text-[#8A857A]">
                    <th class="px-5 py-2.5">{{ $isFr ? 'N° DE CERTIFICAT' : 'CERTIFICATE No.' }}</th>
                    <th class="px-3 py-2.5">{{ $isFr ? 'ARTISAN' : 'ARTISAN' }}</th>
                    <th class="px-3 py-2.5">{{ $isFr ? 'MÉTIER' : 'TRADE' }}</th>
                    <th class="px-3 py-2.5">{{ $isFr ? 'ÉMIS LE' : 'ISSUED' }}</th>
                    <th class="px-3 py-2.5">{{ $isFr ? 'EXPIRE LE' : 'EXPIRES' }}</th>
                    <th class="px-3 py-2.5">{{ $isFr ? 'STATUT' : 'STATUS' }}</th>
                    <th class="px-5 py-2.5 text-right">{{ $isFr ? 'ACTION' : 'ACTION' }}</th>
                </tr></thead>
                <tbody>
                    @forelse($rows as $r)
                    @php $t = $stateTone[$r->state] ?? $stateTone['active']; @endphp
                    <tr class="border-t border-[#F1F1EF] hover:bg-[#FAFAF8]">
                        <td class="px-5 py-3"><span class="text-[12px] font-semibold text-[#1B1B18] font-mono">{{ $r->certificate_no }}</span></td>
                        <td class="px-3 py-3 text-[12.5px] font-semibold text-[#1B1B18]">{{ $r->name_fr }}</td>
                        <td class="px-3 py-3 text-[12.5px] text-[#3B382F]">{{ $r->trade ?? '—' }}</td>
                        <td class="px-3 py-3 text-[12px] text-[#6F6B60]">{{ $fmt($r->certificate_issued_at) }}</td>
                        <td class="px-3 py-3 text-[12px] text-[#6F6B60]">{{ $fmt($r->certificate_expires_at) }}</td>
                        <td class="px-3 py-3"><span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" style="background-color: {{ $t[0] }};color: {{ $t[1] }}">{{ $t[2] }}</span></td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('certificate.verify', ['numero' => $r->certificate_no]) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-[#E2F3E8] text-[#157A43]" title="{{ $isFr ? 'Vérifier' : 'Verify' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <form method="POST" action="{{ route('admin.certificates.revoke', $r->id) }}">
                                    @csrf
                                    @if($r->certificate_revoked_at)
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43] border border-[#CFE0D4] hover:bg-[#E2F3E8] px-2.5 py-1 rounded-lg" title="{{ $isFr ? 'Rétablir' : 'Restore' }}"><i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Rétablir' : 'Restore' }}</button>
                                    @else
                                    <button type="submit" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#DC2626] border border-[#F3C7C7] hover:bg-[#FDE8E8] px-2.5 py-1 rounded-lg" title="{{ $isFr ? 'Révoquer' : 'Revoke' }}"><i data-lucide="ban" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Révoquer' : 'Revoke' }}</button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-10 text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucun certificat trouvé.' : 'No certificate found.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3.5 border-t border-[#F1F1EF]">
            <p class="text-[12px] text-[#8A857A]">{{ number_format($rows->count()) }} {{ $isFr ? 'certificat(s) affiché(s)' : 'certificate(s) shown' }}</p>
        </div>
    </div>
</div>
@endsection
