@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$pageTitle = $lang === 'fr' ? 'Mon profil' : 'My Profile';
$roleLabels = [
    'super_admin'        => ['fr' => 'Administrateur', 'en' => 'Administrator'],
    'admin'              => ['fr' => 'Administrateur', 'en' => 'Administrator'],
    'moderator'          => ['fr' => 'Modérateur', 'en' => 'Moderator'],
    'business_owner'     => ['fr' => 'Entrepreneur', 'en' => 'Business Owner'],
    'regional_rep'       => ['fr' => 'Représentant régional', 'en' => 'Regional Rep'],
    'technical_reviewer' => ['fr' => 'Département technique', 'en' => 'Technical Department'],
];
$roleLabel = $roleLabels[$siacUser['role'] ?? ''][$lang] ?? ($lang === 'fr' ? 'Acheteur' : 'Buyer');
@endphp

@section('content')
<div class="max-w-2xl space-y-6">

    @if(session('success'))
        <div class="ui-alert ui-alert-ok">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="ui-alert ui-alert-danger">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Informations --}}
    <div class="ui-card ui-card--flush">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-[#F1EDE4]">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="user-cog" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Informations' : 'Information' }}</h2>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="ui-label" for="name">
                    {{ $lang === 'fr' ? 'Nom complet' : 'Full name' }}
                </label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" class="ui-field">
            </div>
            <div>
                <label class="ui-label" for="language_preference">
                    {{ $lang === 'fr' ? 'Langue préférée' : 'Preferred language' }}
                </label>
                <select id="language_preference" name="language_preference" class="ui-field ui-select">
                    <option value="fr" {{ ($user->language_preference ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                    <option value="en" {{ ($user->language_preference ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
            <div>
                <label class="ui-label">Email</label>
                <div class="flex items-center gap-2">
                    <input type="email" value="{{ $user->email }}" disabled class="ui-field">
                    <span class="ui-pill ui-pill-neutral">
                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                        {{ $roleLabel }}
                    </span>
                </div>
            </div>
            <div class="pt-1">
                <button type="submit" class="ui-btn ui-btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Enregistrer' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Mot de passe --}}
    <div class="ui-card ui-card--flush">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-[#F1EDE4]">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="lock" class="w-4 h-4 text-forest-600"></i>
            </div>
            <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Mot de passe' : 'Password' }}</h2>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="ui-label" for="current_password">
                    {{ $lang === 'fr' ? 'Mot de passe actuel' : 'Current password' }}
                </label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="ui-field">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="ui-label" for="password">
                        {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'New password' }}
                    </label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="ui-field">
                </div>
                <div>
                    <label class="ui-label" for="password_confirmation">
                        {{ $lang === 'fr' ? 'Confirmer' : 'Confirm' }}
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="ui-field">
                </div>
            </div>
            <div class="pt-1">
                <button type="submit" class="ui-btn ui-btn-primary">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Changer le mot de passe' : 'Change password' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
