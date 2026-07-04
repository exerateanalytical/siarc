@extends('layouts.admin')

@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $selfId = session('siac_user')['id'] ?? null;

    // Graceful fallback while the route still runs the legacy controller
    $roleCounts = $roleCounts ?? [
        'tous' => $users->total(), 'artisans' => 0, 'entreprises' => 0, 'visiteurs' => 0, 'administrateurs' => 0,
    ];

    $currentTab = request('role', 'tous');
    if (! array_key_exists($currentTab, $roleCounts)) { $currentTab = 'tous'; }

    $tabs = [
        'tous'            => $isFr ? 'Tous' : 'All',
        'artisans'        => 'Artisans',
        'entreprises'     => $isFr ? 'Entreprises' : 'Companies',
        'visiteurs'       => $isFr ? 'Visiteurs' : 'Visitors',
        'administrateurs' => $isFr ? 'Administrateurs' : 'Administrators',
    ];

    // Assignable roles for the "more" menu (preserved role-update action)
    $assignableRoleLabels = [
        'buyer'              => $isFr ? 'Acheteur' : 'Buyer',
        'business_owner'     => $isFr ? 'Entrepreneur' : 'Business Owner',
        'regional_rep'       => $isFr ? 'Représentant régional' : 'Regional Rep',
        'ministry'           => $isFr ? 'Ministère' : 'Ministry',
        'technical_reviewer' => $isFr ? 'Département technique' : 'Technical Dept.',
        'moderator'          => $isFr ? 'Modérateur' : 'Moderator',
        'admin'              => $isFr ? 'Administrateur' : 'Admin',
    ];

    $moisFr = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $monthsEn = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
@endphp

@section('content')

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Gestion des Utilisateurs' : 'User Management' }}</h1>
        <p class="mt-0.5 text-[12px] text-[#6F6B60]">
            <a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43] hover:underline">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <span class="mx-1 text-[#B8B2A4]">/</span>
            <span class="text-[#3B382F]">{{ $isFr ? 'Utilisateurs' : 'Users' }}</span>
        </p>
    </div>
    <div class="flex items-center gap-2.5 shrink-0">
        <a href="{{ route('onboarding') }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un utilisateur' : 'Add a user' }}
        </a>
        <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-2 bg-white border border-[#DCEBDD] hover:border-[#157A43] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#157A43] transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}
        </a>
    </div>
</div>

@if(session('success'))
<div class="mt-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 flex items-start gap-2">
    <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mt-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3.5">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

{{-- Role tabs (real counts) --}}
<div class="mt-4 flex flex-wrap items-stretch gap-1 bg-[#F1EDE3] border border-[#EAE5D8] rounded-xl p-1 overflow-x-auto">
    @foreach($tabs as $key => $label)
    <a href="{{ route('admin.users', array_merge(request()->except('page', 'role'), $key === 'tous' ? [] : ['role' => $key])) }}"
       class="whitespace-nowrap rounded-lg px-4 py-2 text-[12.5px] transition-colors {{ $currentTab === $key ? 'bg-white border border-[#E3DFD3] shadow-sm font-bold text-[#1B1B18]' : 'font-semibold text-[#C97A16] hover:bg-white/60' }}">
        {{ $label }} ({{ number_format($roleCounts[$key] ?? 0) }})
    </a>
    @endforeach
</div>

{{-- Search + filters --}}
<form method="GET" action="{{ route('admin.users') }}" class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl p-3 flex flex-wrap items-center gap-2.5">
    <div class="relative flex-1 min-w-[220px]">
        <i data-lucide="search" class="w-4 h-4 text-[#B8B2A4] absolute left-3 top-1/2 -translate-y-1/2"></i>
        <input name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un utilisateur...' : 'Search for a user...' }}"
               class="w-full text-[12.5px] border border-[#EAE5D8] rounded-lg pl-9 pr-3 h-[38px] focus:outline-none focus:border-[#157A43]">
    </div>
    <select name="role" class="text-[12.5px] border border-[#EAE5D8] rounded-lg px-3 h-[38px] bg-white focus:outline-none focus:border-[#157A43]">
        <option value="">{{ $isFr ? 'Rôle' : 'Role' }}</option>
        @foreach($tabs as $key => $label)
            @if($key !== 'tous')
            <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endif
        @endforeach
    </select>
    <select name="statut" class="text-[12.5px] border border-[#EAE5D8] rounded-lg px-3 h-[38px] bg-white focus:outline-none focus:border-[#157A43]">
        <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
        <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>{{ $isFr ? 'Actif' : 'Active' }}</option>
        <option value="suspendu" {{ request('statut') === 'suspendu' ? 'selected' : '' }}>{{ $isFr ? 'Suspendu' : 'Suspended' }}</option>
    </select>
    <select name="kyc" class="text-[12.5px] border border-[#EAE5D8] rounded-lg px-3 h-[38px] bg-white focus:outline-none focus:border-[#157A43]">
        <option value="">{{ $isFr ? 'Vérification KYC' : 'KYC Verification' }}</option>
        <option value="verifie" {{ request('kyc') === 'verifie' ? 'selected' : '' }}>{{ $isFr ? 'Vérifié' : 'Verified' }}</option>
        <option value="en_attente" {{ request('kyc') === 'en_attente' ? 'selected' : '' }}>{{ $isFr ? 'En attente' : 'Pending' }}</option>
    </select>
    <button type="submit" class="inline-flex items-center gap-2 bg-white border border-[#DCEBDD] hover:border-[#157A43] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#157A43] transition-colors">
        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>{{ $isFr ? 'Filtrer' : 'Filter' }}
    </button>
</form>

{{-- Users table --}}
<section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px]">
            <thead>
                <tr class="bg-[#F8F4EC] text-left">
                    <th class="pl-5 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Utilisateur' : 'User' }}</th>
                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Rôle' : 'Role' }}</th>
                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">KYC</th>
                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Inscrit le' : 'Joined' }}</th>
                    <th class="px-2 pr-5 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F5F1E8]">
                @forelse($users as $u)
                @php
                    $roleName = $u->role_name ?? null;
                    $ownedVendorType = $u->owned_vendor_type ?? null;
                    $ownedTier = $u->owned_verification_tier ?? null;
                    $isVisitor = ! $roleName || $roleName === 'buyer';

                    if (in_array($roleName, ['super_admin', 'admin'])) {
                        $roleLabel = $isFr ? 'Administrateur' : 'Administrator';
                    } elseif ($roleName === 'moderator') {
                        $roleLabel = $isFr ? 'Modérateur' : 'Moderator';
                    } elseif ($roleName === 'business_owner') {
                        $roleLabel = in_array($ownedVendorType, ['entreprise', 'cooperative'])
                            ? ($isFr ? 'Entreprise' : 'Company')
                            : ($isFr ? 'Artisan' : 'Artisan');
                    } elseif ($roleName === 'regional_rep') {
                        $roleLabel = $isFr ? 'Représentant régional' : 'Regional Rep';
                    } elseif ($roleName === 'ministry') {
                        $roleLabel = $isFr ? 'Ministère' : 'Ministry';
                    } elseif ($roleName === 'technical_reviewer') {
                        $roleLabel = 'Support';
                    } else {
                        $roleLabel = $isFr ? 'Visiteur' : 'Visitor';
                    }

                    $kycVerified = in_array($ownedTier, ['verified', 'certified']) || ! empty($u->is_email_verified);
                    $created = \Carbon\Carbon::parse($u->created_at);
                    $joinedLabel = $isFr
                        ? sprintf('%02d %s %d', $created->day, $moisFr[$created->month], $created->year)
                        : sprintf('%02d %s %d', $created->day, $monthsEn[$created->month], $created->year);
                    $isSelf = $selfId !== null && $u->id === $selfId;
                @endphp
                <tr>
                    <td class="pl-5 pr-2 py-3.5">
                        <div class="flex items-center gap-3">
                            @if(!empty($u->avatar))
                            <img src="{{ asset($u->avatar) }}" alt="{{ $u->name }}" class="w-9 h-9 rounded-full object-cover shrink-0">
                            @else
                            <div class="w-9 h-9 rounded-full bg-[#E2F3E8] text-[#157A43] flex items-center justify-center shrink-0 text-[13px] font-bold">{{ strtoupper(substr($u->name ?? '?', 0, 1)) }}</div>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.detail', ['id' => $u->id]) }}" class="block text-[12.5px] font-bold text-[#1B1B18] truncate hover:text-[#157A43] hover:underline">{{ $u->name }}</a>
                                <p class="text-[11px] text-[#8A857A] truncate">{{ $u->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-3.5 text-[12px] text-[#3B382F]">{{ $roleLabel }}</td>
                    <td class="px-2 py-3.5">
                        @if($u->status === 'active')
                        <span class="inline-block rounded-md bg-[#E2F3E8] px-2.5 py-1 text-[10.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Actif' : 'Active' }}</span>
                        @elseif($u->status === 'suspended')
                        <span class="inline-block rounded-md bg-[#FDE8E8] px-2.5 py-1 text-[10.5px] font-semibold text-[#C0392B]">{{ $isFr ? 'Suspendu' : 'Suspended' }}</span>
                        @else
                        <span class="inline-block rounded-md bg-[#F1EDE3] px-2.5 py-1 text-[10.5px] font-semibold text-[#8A857A]">{{ ucfirst($u->status) }}</span>
                        @endif
                    </td>
                    <td class="px-2 py-3.5">
                        @if($isVisitor)
                        <span class="text-[12px] text-[#8A857A]">-</span>
                        @elseif($kycVerified)
                        <span class="inline-block rounded-md bg-[#E2F3E8] px-2.5 py-1 text-[10.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Vérifié' : 'Verified' }}</span>
                        @else
                        <span class="inline-block rounded-md bg-[#FDF0DC] px-2.5 py-1 text-[10.5px] font-semibold text-[#C97A16]">{{ $isFr ? 'En attente' : 'Pending' }}</span>
                        @endif
                    </td>
                    <td class="px-2 py-3.5 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $joinedLabel }}</td>
                    <td class="px-2 pr-5 py-3.5 text-right whitespace-nowrap">
                        <a href="{{ route('admin.users.detail', ['id' => $u->id]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[#6F6B60] hover:border-[#157A43] hover:text-[#157A43] transition-colors align-middle">
                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                        </a>
                        <details class="relative inline-block align-middle ml-1.5">
                            <summary class="list-none cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[#6F6B60] hover:border-[#157A43] hover:text-[#157A43] transition-colors [&::-webkit-details-marker]:hidden">
                                <i data-lucide="more-vertical" class="w-3.5 h-3.5"></i>
                            </summary>
                            <div class="absolute right-0 top-9 z-20 w-64 bg-white border border-[#EAE5D8] rounded-xl shadow-lg p-3 text-left">
                                <a href="{{ route('admin.users.detail', ['id' => $u->id]) }}" class="flex items-center gap-2 text-[12px] font-semibold text-[#3B382F] hover:text-[#157A43] py-1.5">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir le profil' : 'View profile' }}
                                </a>
                                @if($isSelf)
                                <p class="text-[11px] text-[#B8B2A4] py-1.5">{{ $isFr ? '(vous) — actions désactivées' : '(you) — actions disabled' }}</p>
                                @else
                                @if($u->status !== 'suspended')
                                <form method="POST" action="{{ route('admin.users.update-status', ['id' => $u->id]) }}" class="border-t border-[#F5F1E8] mt-1 pt-1">
                                    @csrf
                                    <input type="hidden" name="status" value="suspended">
                                    <button type="submit" class="flex items-center gap-2 w-full text-[12px] font-semibold text-[#C0392B] hover:text-red-700 py-1.5">
                                        <i data-lucide="ban" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Suspendre' : 'Suspend' }}
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.users.update-status', ['id' => $u->id]) }}" class="border-t border-[#F5F1E8] mt-1 pt-1">
                                    @csrf
                                    <input type="hidden" name="status" value="active">
                                    <button type="submit" class="flex items-center gap-2 w-full text-[12px] font-semibold text-[#157A43] hover:text-[#14532D] py-1.5">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Réactiver' : 'Reactivate' }}
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.update-role', ['id' => $u->id]) }}" class="border-t border-[#F5F1E8] mt-1 pt-2">
                                    @csrf
                                    <p class="text-[10.5px] font-bold uppercase tracking-[0.05em] text-[#8A6D1F] mb-1.5">{{ $isFr ? 'Changer le rôle' : 'Change role' }}</p>
                                    <select name="role" onchange="this.closest('form').querySelector('.region-select-wrap').classList.toggle('hidden', this.value !== 'regional_rep')" class="w-full text-[12px] border border-[#EAE5D8] rounded-lg px-2 py-1.5 mb-1.5 focus:outline-none focus:border-[#157A43]">
                                        @foreach($assignableRoleLabels as $val => $label)
                                        <option value="{{ $val }}" {{ ($roleName ?? 'buyer') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="region-select-wrap {{ $roleName !== 'regional_rep' ? 'hidden' : '' }} mb-1.5">
                                        <select name="region_id" class="w-full text-[12px] border border-[#EAE5D8] rounded-lg px-2 py-1.5 focus:outline-none focus:border-[#157A43]">
                                            <option value="">{{ $isFr ? 'Région...' : 'Region...' }}</option>
                                            @foreach($regions ?? [] as $region)
                                            <option value="{{ $region->id }}" {{ ($u->assigned_region_id ?? null) === $region->id ? 'selected' : '' }}>{{ $isFr ? $region->name_fr : $region->name_en }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full bg-[#0F4824] hover:bg-[#14652F] text-white text-[12px] font-semibold rounded-lg py-1.5 transition-colors">{{ $isFr ? 'Appliquer' : 'Apply' }}</button>
                                </form>
                                @endif
                            </div>
                        </details>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun utilisateur trouvé.' : 'No users found.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#F5F1E8] px-5 py-3.5">
        <p class="text-[12px] text-[#6F6B60]">
            {{ $isFr
                ? 'Affichage de ' . ($users->firstItem() ?? 0) . ' à ' . ($users->lastItem() ?? 0) . ' sur ' . number_format($users->total()) . ' utilisateurs'
                : 'Showing ' . ($users->firstItem() ?? 0) . ' to ' . ($users->lastItem() ?? 0) . ' of ' . number_format($users->total()) . ' users' }}
        </p>
        @if($users->hasPages())
        @php
            $last = $users->lastPage();
            $cur = $users->currentPage();
            $start = max(1, min($cur - 2, $last - 4));
            $end = min($last, $start + 4);
        @endphp
        <div class="flex items-center gap-1.5">
            <a href="{{ $cur > 1 ? $users->url($cur - 1) : '#' }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[#6F6B60] {{ $cur > 1 ? 'hover:border-[#157A43] hover:text-[#157A43]' : 'opacity-40 pointer-events-none' }}">
                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
            </a>
            @if($start > 1)
            <a href="{{ $users->url(1) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[12px] font-semibold text-[#3B382F] hover:border-[#157A43] hover:text-[#157A43]">1</a>
            @if($start > 2)<span class="text-[12px] text-[#8A857A] px-0.5">...</span>@endif
            @endif
            @for($p = $start; $p <= $end; $p++)
            @if($p === $cur)
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[#0F4824] text-[12px] font-bold text-white">{{ $p }}</span>
            @else
            <a href="{{ $users->url($p) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[12px] font-semibold text-[#3B382F] hover:border-[#157A43] hover:text-[#157A43]">{{ $p }}</a>
            @endif
            @endfor
            @if($end < $last)
            @if($end < $last - 1)<span class="text-[12px] text-[#8A857A] px-0.5">...</span>@endif
            <a href="{{ $users->url($last) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[12px] font-semibold text-[#3B382F] hover:border-[#157A43] hover:text-[#157A43]">{{ number_format($last) }}</a>
            @endif
            <a href="{{ $cur < $last ? $users->url($cur + 1) : '#' }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-[#EAE5D8] text-[#6F6B60] {{ $cur < $last ? 'hover:border-[#157A43] hover:text-[#157A43]' : 'opacity-40 pointer-events-none' }}">
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        @endif
    </div>
</section>

<p class="mt-8 text-center text-[11.5px] text-[#8A857A]">© {{ date('Y') }} Galerie Virtuelle Nationale de l'Artisanat du Cameroun. {{ $isFr ? 'Tous droits réservés.' : 'All rights reserved.' }}</p>

@endsection
