@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Mon profil' : 'My Profile';
$roleLabels = [
    'super_admin'        => ['fr' => 'Administrateur', 'en' => 'Administrator'],
    'admin'              => ['fr' => 'Administrateur', 'en' => 'Administrator'],
    'moderator'          => ['fr' => 'Modérateur', 'en' => 'Moderator'],
    'business_owner'     => ['fr' => 'Entrepreneur', 'en' => 'Business Owner'],
    'regional_rep'       => ['fr' => 'Représentant régional', 'en' => 'Regional Rep'],
    'ministry'           => ['fr' => 'Ministère', 'en' => 'Ministry'],
    'technical_reviewer' => ['fr' => 'Département technique', 'en' => 'Technical Department'],
];
$roleLabel = $roleLabels[$siacUser['role'] ?? ''][$lang] ?? ($lang === 'fr' ? 'Acheteur' : 'Buyer');
@endphp

@section('content')
<div class="max-w-2xl space-y-6">

    @if(session('success'))
        <div class="flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Informations --}}
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-[#F1EDE4]">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="user-cog" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Informations' : 'Information' }}</h2>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-[#3B382F] mb-1.5" for="name">
                    {{ $lang === 'fr' ? 'Nom complet' : 'Full name' }}
                </label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255"
                    class="w-full px-3.5 py-2.5 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#3B382F] mb-1.5" for="language_preference">
                    {{ $lang === 'fr' ? 'Langue préférée' : 'Preferred language' }}
                </label>
                <select id="language_preference" name="language_preference"
                    class="w-full px-3.5 py-2.5 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition bg-white">
                    <option value="fr" {{ ($user->language_preference ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                    <option value="en" {{ ($user->language_preference ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-[#3B382F] mb-1.5">Email</label>
                <div class="flex items-center gap-2">
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full px-3.5 py-2.5 border border-[#EFEBE2] bg-[#FBF9F4] rounded-lg text-sm text-[#8A857A] cursor-not-allowed">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-[#F1EDE4] text-[#6F6B60] text-xs font-semibold whitespace-nowrap">
                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>
            <div class="pt-1">
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Enregistrer' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Mot de passe --}}
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-[#F1EDE4]">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="lock" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Mot de passe' : 'Password' }}</h2>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-[#3B382F] mb-1.5" for="current_password">
                    {{ $lang === 'fr' ? 'Mot de passe actuel' : 'Current password' }}
                </label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                    class="w-full px-3.5 py-2.5 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#3B382F] mb-1.5" for="password">
                        {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'New password' }}
                    </label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#3B382F] mb-1.5" for="password_confirmation">
                        {{ $lang === 'fr' ? 'Confirmer' : 'Confirm' }}
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                        class="w-full px-3.5 py-2.5 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition">
                </div>
            </div>
            <div class="pt-1">
                <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Changer le mot de passe' : 'Change password' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
