@extends('layouts.app')

@php
$statusLabels = [
    'draft' => $lang === 'fr' ? 'Brouillon' : 'Draft',
    'published' => $lang === 'fr' ? 'Publiée' : 'Published',
    'suspended' => $lang === 'fr' ? 'Suspendue' : 'Suspended',
    'rejected' => $lang === 'fr' ? 'Rejetée' : 'Rejected',
];
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-6">
        <a href="/tableau-de-bord/admin" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-8 h-8 bg-forest-100 rounded-lg flex items-center justify-center">
            <i data-lucide="building-2" class="w-4 h-4 text-forest-600"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input name="q" value="{{ request('q') }}" placeholder="{{ $lang === 'fr' ? 'Rechercher...' : 'Search...' }}" class="flex-1 min-w-[160px] text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
        <select name="status" onchange="this.form.submit()" class="text-sm border border-gray-200 rounded-lg px-3 py-2">
            <option value="">{{ $lang === 'fr' ? 'Tous statuts' : 'All statuses' }}</option>
            @foreach($statusLabels as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Filtrer' : 'Filter' }}</button>
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @forelse($businesses as $business)
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                <i data-lucide="{{ $business->industry->icon ?? 'building-2' }}" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $business->name_fr }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $business->user?->email }} — {{ $business->industry->name_fr ?? '' }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-green-100 text-green-700' => $business->status === 'published',
                'bg-gray-100 text-gray-500' => $business->status === 'draft',
                'bg-red-100 text-red-700' => in_array($business->status, ['suspended', 'rejected']),
            ])>{{ $statusLabels[$business->status] ?? $business->status }}</span>

            <div class="flex items-center gap-1 shrink-0">
                @if($business->status !== 'published')
                <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="published">
                    <button type="submit" class="p-2 rounded-lg hover:bg-green-50 text-green-600" title="{{ $lang === 'fr' ? 'Publier' : 'Publish' }}">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
                @if($business->status !== 'suspended')
                <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-600" title="{{ $lang === 'fr' ? 'Suspendre' : 'Suspend' }}">
                        <i data-lucide="ban" class="w-4 h-4"></i>
                    </button>
                </form>
                @endif
                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" target="_blank" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400" title="{{ $lang === 'fr' ? 'Voir' : 'View' }}">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune entreprise trouvée.' : 'No businesses found.' }}</div>
        @endforelse
    </div>

    @if($businesses->hasPages())
    <div class="mt-4">{{ $businesses->links() }}</div>
    @endif
</div>
@endsection
