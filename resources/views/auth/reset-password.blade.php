@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'Set New Password' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $lang === 'fr' ? 'Entrez votre nouveau mot de passe ci-dessous.' : 'Enter your new password below.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if($tokenValid)
            <form method="POST" action="/reset-password">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="lang" value="{{ $lang }}">

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $lang === 'fr' ? 'Adresse email' : 'Email address' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input type="email" value="{{ $email }}" disabled
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password">
                        {{ $lang === 'fr' ? 'Nouveau mot de passe' : 'New Password' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                            placeholder="{{ $lang === 'fr' ? 'Min. 8 caractères' : 'Min. 8 characters' }}"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required autofocus>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="password_confirmation">
                        {{ $lang === 'fr' ? 'Confirmer le mot de passe' : 'Confirm New Password' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                            placeholder="{{ $lang === 'fr' ? 'Répétez le mot de passe' : 'Repeat password' }}"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Définir le mot de passe' : 'Set New Password' }}
                </button>
            </form>
            @else
            <div class="text-center py-4">
                <p class="text-sm text-gray-500 mb-4">
                    {{ $lang === 'fr'
                        ? 'Ce lien de réinitialisation est invalide ou a expiré (les liens expirent après 60 minutes).'
                        : 'This password reset link is invalid or has expired (links expire after 60 minutes).' }}
                </p>
                <a href="/forgot-password?lang={{ $lang }}"
                    class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 px-5 rounded-lg text-sm transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Demander un nouveau lien' : 'Request a new link' }}
                </a>
            </div>
            @endif

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <a href="/login?lang={{ $lang }}" class="inline-flex items-center gap-1 text-sm text-forest-500 font-semibold hover:text-forest-600 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Retour à la connexion' : 'Back to login' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
