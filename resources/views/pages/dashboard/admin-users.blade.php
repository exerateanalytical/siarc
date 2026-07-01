@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-6">
        <a href="/tableau-de-bord/admin" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-8 h-8 bg-forest-100 rounded-lg flex items-center justify-center">
            <i data-lucide="users" class="w-4 h-4 text-forest-600"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'Utilisateurs' : 'Users' }}</h1>
    </div>

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
        <div class="flex items-center gap-3 px-4 py-3.5 border-b border-gray-50 last:border-0">
            <div class="w-9 h-9 rounded-full bg-forest-50 flex items-center justify-center shrink-0 text-forest-700 text-sm font-bold">
                {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $user->email }} — {{ $user->roles->pluck('name')->implode(', ') ?: ($lang === 'fr' ? 'Aucun rôle' : 'No role') }}</p>
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
        @empty
        <div class="text-center py-10 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun utilisateur trouvé.' : 'No users found.' }}</div>
        @endforelse
    </div>

    @if($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
    @endif
</div>
@endsection
