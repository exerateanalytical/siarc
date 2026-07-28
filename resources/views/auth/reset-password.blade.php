@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-[#F3EFE7]">
                {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'Set New Password' }}
            </h1>
            <p class="text-gray-500 dark:text-[#868778] text-sm mt-1">
                {{ $lang === 'fr' ? 'Entrez votre nouveau mot de passe ci-dessous.' : 'Enter your new password below.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="ui-card">

            @if($errors->any())
                <div class="mb-4 ui-alert ui-alert-danger">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if($tokenValid)
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="lang" value="{{ $lang }}">

                <div class="mb-5">
                    <label class="ui-label">
                        {{ $lang === 'fr' ? 'Adresse email' : 'Email address' }}
                    </label>
                    {{-- Leading lock/mail icons dropped across this card: they were decoration,
                         and a plain kit field also greys itself out when disabled. --}}
                    <input type="email" value="{{ $email }}" disabled class="ui-field ui-field--lg">
                </div>

                <div class="mb-5">
                    <label class="ui-label" for="password">
                        {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'New Password' }}
                    </label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                        placeholder="{{ $lang === 'fr' ? 'Min. 8 caractères' : 'Min. 8 characters' }}"
                        class="ui-field ui-field--lg {{ $errors->has('password') ? 'ui-field--invalid' : '' }}"
                        required autofocus>
                </div>

                <div class="mb-6">
                    <label class="ui-label" for="password_confirmation">
                        {{ $lang === 'fr' ? 'Confirmer le mot de passe' : 'Confirm New Password' }}
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                        placeholder="{{ $lang === 'fr' ? 'Répétez le mot de passe' : 'Repeat password' }}"
                        class="ui-field ui-field--lg"
                        required>
                </div>

                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Définir le mot de passe' : 'Set New Password' }}
                </button>
            </form>
            @else
            <div class="text-center py-4">
                <p class="text-sm text-gray-500 dark:text-[#868778] mb-4">
                    {{ $lang === 'fr'
                        ? 'Ce lien de réinitialisation est invalide ou a expiré (les liens expirent après 60 minutes).'
                        : 'This password reset link is invalid or has expired (links expire after 60 minutes).' }}
                </p>
                <a href="{{ route('password.request', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Demander un nouveau lien' : 'Request a new link' }}
                </a>
            </div>
            @endif

            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-[#262B21] text-center">
                <a href="{{ route('login', ['lang' => $lang]) }}" class="inline-flex items-center gap-1 text-sm text-forest-500 dark:text-[#339B56] font-semibold hover:text-forest-600 hover:dark:text-[#339B56] transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Retour à la connexion' : 'Back to login' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
