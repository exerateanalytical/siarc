@extends('layouts.admin')

@php
$pageTitle = $lang === 'fr' ? 'Partenaires & Sponsors' : 'Partners & Sponsors';
$tierLabels = [
    'institutional' => $lang === 'fr' ? 'Institutionnel' : 'Institutional',
    'platinum'      => 'Platinum',
    'gold'          => 'Gold',
    'silver'        => 'Silver',
    'partner'       => $lang === 'fr' ? 'Partenaire' : 'Partner',
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

    <!-- Existing partners -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-6">
        @forelse($partners as $partner)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center shrink-0 overflow-hidden">
                @if($partner->logo)
                <img src="{{ asset('storage/' . $partner->logo) }}" alt="" class="w-full h-full object-contain">
                @else
                <i data-lucide="building-2" class="w-4 h-4 text-gray-300"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $lang === 'fr' ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}</p>
                <p class="text-xs text-gray-400">{{ $tierLabels[$partner->tier] ?? $partner->tier }} @if(!$partner->is_active) · {{ $lang === 'fr' ? 'Inactif' : 'Inactive' }} @endif</p>
            </div>
            <a href="{{ route('admin.partners.detail', ['id' => $partner->id, 'lang' => $lang]) }}" class="p-2 rounded-lg hover:bg-green-50 text-[#157A43] shrink-0" title="{{ $lang === 'fr' ? 'Voir le détail' : 'View detail' }}">
                <i data-lucide="eye" class="w-4 h-4"></i>
            </a>
            <form method="POST" action="{{ route('admin.partners.destroy', ['id' => $partner->id]) }}" onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer ce partenaire ?' : 'Remove this partner?' }}')">
                @csrf
                <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-500 shrink-0">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun partenaire pour l\'instant.' : 'No partners yet.' }}</div>
        @endforelse
    </div>

    <!-- Add new -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">{{ $lang === 'fr' ? 'Ajouter un partenaire' : 'Add a partner' }}</h2>
        <form method="POST" action="{{ route('admin.partners.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input name="name_fr" required placeholder="{{ $lang === 'fr' ? 'Nom (français)' : 'Name (French)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                <input name="name_en" placeholder="{{ $lang === 'fr' ? 'Nom (anglais)' : 'Name (English)' }}" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <select name="tier" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                    @foreach($tierLabels as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="url" name="website" placeholder="https://" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
            </div>
            <textarea name="description_fr" rows="2" placeholder="{{ $lang === 'fr' ? 'Description courte (français)' : 'Short description (French)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400 resize-none"></textarea>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Logo' : 'Logo' }}</label>
                <input type="file" name="logo" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-forest-50 file:text-forest-700 file:text-xs">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-forest-600">
                {{ $lang === 'fr' ? 'Actif (visible publiquement)' : 'Active (publicly visible)' }}
            </label>
            <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Ajouter' : 'Add' }}
            </button>
        </form>
    </div>
</div>
@endsection
