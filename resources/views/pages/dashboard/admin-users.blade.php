@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Utilisateurs' : 'Users';
$roleLabels = [
    'buyer'               => $lang === 'fr' ? 'Acheteur' : 'Buyer',
    'business_owner'      => $lang === 'fr' ? 'Entrepreneur' : 'Business Owner',
    'regional_rep'        => $lang === 'fr' ? 'Représentant régional' : 'Regional Rep',
    'ministry'            => $lang === 'fr' ? 'Ministère' : 'Ministry',
    'technical_reviewer'  => $lang === 'fr' ? 'Département technique' : 'Technical Dept.',
    'moderator'           => $lang === 'fr' ? 'Modérateur' : 'Moderator',
    'admin'               => $lang === 'fr' ? 'Administrateur' : 'Admin',
    'super_admin'         => $lang === 'fr' ? 'Super Admin' : 'Super Admin',
];
@endphp

@section('content')
<div class="max-w-3xl">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3.5 mb-4">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <form method="GET" class="flex gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="{{ $lang === 'fr' ? 'Rechercher par nom ou email...' : 'Search by name or email...' }}" class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
        <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Chercher' : 'Search' }}</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($users as $user)
        @php $currentRole = $user->roles->first()?->name ?? 'buyer'; @endphp
        <div class="px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-forest-50 flex items-center justify-center shrink-0 text-forest-700 text-sm font-bold">
                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('admin.users.detail', ['id' => $user->id]) }}" class="text-sm font-medium text-gray-900 truncate hover:text-forest-600 hover:underline">{{ $user->name }}</a>
                    <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                </div>
                <span @class([
                    'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                    'bg-green-100 text-green-700' => $user->status === 'active',
                    'bg-red-100 text-red-700' => $user->status === 'suspended',
                    'bg-gray-100 text-gray-500' => $user->status === 'deleted',
                ])>{{ ucfirst($user->status) }}</span>

                @if($user->status !== 'suspended')
                <form method="POST" action="{{ route('admin.users.update-status', ['id' => $user->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600 shrink-0" title="{{ $lang === 'fr' ? 'Suspendre' : 'Suspend' }}">
                        <i data-lucide="ban" class="w-4 h-4"></i>
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.users.update-status', ['id' => $user->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="p-2 rounded-lg hover:bg-green-50 text-green-600 shrink-0" title="{{ $lang === 'fr' ? 'Réactiver' : 'Reactivate' }}">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.users.update-role', ['id' => $user->id]) }}" class="flex items-center gap-2 mt-2.5 ml-12" onsubmit="return {{ $user->id === session('siac_user')['id'] ? 'false' : 'true' }}">
                @csrf
                <i data-lucide="key-round" class="w-3.5 h-3.5 text-gray-300 shrink-0"></i>
                <select name="role" onchange="this.closest('form').querySelector('.region-select-wrap').classList.toggle('hidden', this.value !== 'regional_rep')" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-forest-400" {{ $user->id === (session('siac_user')['id'] ?? null) ? 'disabled' : '' }}>
                    @foreach($roleLabels as $val => $label)
                        @if(in_array($val, ['buyer','business_owner','regional_rep','ministry','technical_reviewer','moderator','admin']))
                        <option value="{{ $val }}" {{ $currentRole === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endif
                    @endforeach
                </select>
                <div class="region-select-wrap {{ $currentRole !== 'regional_rep' ? 'hidden' : '' }}">
                    <select name="region_id" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:border-forest-400">
                        <option value="">{{ $lang === 'fr' ? 'Région...' : 'Region...' }}</option>
                        @foreach($regions as $region)
                        <option value="{{ $region->id }}" {{ $user->assigned_region_id === $region->id ? 'selected' : '' }}>{{ $lang === 'fr' ? $region->name_fr : $region->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                @if($user->id !== (session('siac_user')['id'] ?? null))
                <button type="submit" class="text-xs text-forest-600 hover:underline font-medium">{{ $lang === 'fr' ? 'Appliquer' : 'Apply' }}</button>
                @else
                <span class="text-xs text-gray-300">{{ $lang === 'fr' ? '(vous)' : '(you)' }}</span>
                @endif
            </form>
        </div>
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun utilisateur trouvé.' : 'No users found.' }}</div>
        @endforelse
    </div>

    @if($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
