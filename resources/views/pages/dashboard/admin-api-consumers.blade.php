@extends('layouts.admin')

@php
$pageTitle = $lang === 'fr' ? 'API & Développeurs' : 'API & Developers';
$statusMeta = [
    'pending'   => ['color' => 'amber', 'fr' => 'En attente', 'en' => 'Pending'],
    'approved'  => ['color' => 'green', 'fr' => 'Approuvé', 'en' => 'Approved'],
    'suspended' => ['color' => 'red',   'fr' => 'Suspendu', 'en' => 'Suspended'],
];
@endphp

@section('content')
<div class="max-w-4xl">

    @if(session('success'))
        <div class="ui-alert ui-alert-ok mb-4">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Status filter pills --}}
    <div class="flex items-center gap-2 mb-5 flex-wrap">
        <a href="{{ route('admin.api-consumers') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ !request('status') ? 'bg-[#157A43] text-white' : 'bg-white border border-[#EFEBE2] text-[#55524A] hover:bg-[#F8F4EC]' }}">
            {{ $lang === 'fr' ? 'Tous' : 'All' }}
        </a>
        @foreach($statusMeta as $key => $meta)
        <a href="{{ route('admin.api-consumers', ['status' => $key]) }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-colors {{ request('status') === $key ? 'bg-[#157A43] text-white' : 'bg-white border border-[#EFEBE2] text-[#55524A] hover:bg-[#F8F4EC]' }}">
            {{ $meta[$lang] }}
            @if($key === 'pending' && $pendingCount > 0)
            <span class="{{ request('status') === $key ? 'bg-white/20' : 'bg-amber-100 text-amber-700' }} px-1.5 rounded-full text-[10px]">{{ $pendingCount }}</span>
            @endif
        </a>
        @endforeach
    </div>

    <div class="ui-card ui-card--flush">
        @forelse($consumers as $consumer)
        @php $meta = $statusMeta[$consumer->status] ?? $statusMeta['pending']; @endphp
        <div class="px-5 py-3.5 border-b border-[#F5F1E8] last:border-0">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-{{ $meta['color'] }}-50 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="key-round" class="w-4 h-4 text-{{ $meta['color'] }}-500"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-[#1B1B18]">{{ $consumer->name }}</span>
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-{{ $meta['color'] }}-50 text-{{ $meta['color'] }}-700 text-[10px] font-semibold">
                            {{ $meta[$lang] }}
                        </span>
                        <span class="ui-dt">
                            {{ $consumer->active_keys_count }}/{{ $consumer->keys_count }} {{ $lang === 'fr' ? 'clés actives' : 'active keys' }}
                        </span>
                    </div>
                    <p class="text-xs text-[#8A857A] mt-0.5">
                        {{ $consumer->email }}
                        @if($consumer->company) · {{ $consumer->company }}@endif
                        @if($consumer->website) · <a href="{{ $consumer->website }}" target="_blank" rel="noopener" class="text-[#157A43] hover:underline">{{ $consumer->website }}</a>@endif
                    </p>
                    @if($consumer->purpose)
                    <p class="text-xs text-[#B8B2A4] mt-1">{{ Str::limit($consumer->purpose, 180) }}</p>
                    @endif
                    <p class="text-[11px] text-[#B8B2A4] mt-1">
                        {{ $lang === 'fr' ? 'Demandé' : 'Requested' }} {{ $consumer->created_at->diffForHumans() }}
                        @if($consumer->approved_at) · {{ $lang === 'fr' ? 'approuvé le' : 'approved' }} {{ $consumer->approved_at->format('d/m/Y') }}@endif
                    </p>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($consumer->status !== 'approved')
                    <form method="POST" action="{{ route('admin.api-consumers.update-status', $consumer->id) }}">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100 transition-colors">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Approuver' : 'Approve' }}
                        </button>
                    </form>
                    @endif
                    @if($consumer->status !== 'suspended')
                    <form method="POST" action="{{ route('admin.api-consumers.update-status', $consumer->id) }}"
                        onsubmit="return confirm('{{ $lang === 'fr' ? 'Suspendre ce consommateur API ? Ses clés cesseront de fonctionner.' : 'Suspend this API consumer? Their keys will stop working.' }}')">
                        @csrf
                        <input type="hidden" name="status" value="suspended">
                        <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">
                            <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Suspendre' : 'Suspend' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-14 px-4">
            <i data-lucide="key-round" class="w-10 h-10 text-[#DCD5C6] mx-auto mb-3"></i>
            <p class="ui-card-sub">{{ $lang === 'fr' ? 'Aucun consommateur API.' : 'No API consumers.' }}</p>
        </div>
        @endforelse
    </div>

    @if($consumers->hasPages())
    <div class="mt-4">{{ $consumers->links() }}</div>
    @endif
</div>
@endsection
