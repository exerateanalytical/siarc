@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="mail-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-[#F3EFE7]">
                {{ $lang === 'fr' ? 'Vérifiez votre email' : 'Verify your email' }}
            </h1>
            <p class="text-gray-500 dark:text-[#868778] text-sm mt-1">
                {{ $lang === 'fr'
                    ? 'Un code à 6 chiffres sera envoyé à ' . $email . '.'
                    : 'A 6-digit code will be sent to ' . $email . '.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="ui-card">

            {{-- The kit has no "info" tone; the amber notice is the closest neutral one. --}}
            @if(session('info'))
                <div class="mb-4 ui-alert ui-alert-warn">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    {{ session('info') }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 ui-alert ui-alert-ok">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 ui-alert ui-alert-danger">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 ui-alert ui-alert-danger">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Send code --}}
            <form method="POST" action="{{ route('email.verify.send') }}" class="mb-6">
                @csrf
                <button type="submit" class="ui-btn ui-btn-secondary ui-btn-lg ui-btn-block">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    {{ session('status')
                        ? ($lang === 'fr' ? 'Renvoyer le code' : 'Resend code')
                        : ($lang === 'fr' ? 'Envoyer le code' : 'Send code') }}
                </button>
            </form>

            {{-- Confirm code --}}
            <form method="POST" action="{{ route('email.verify.confirm') }}">
                @csrf
                <div class="mb-6">
                    <label class="ui-label" for="code">
                        {{ $lang === 'fr' ? 'Code de vérification' : 'Verification code' }}
                    </label>
                    {{-- Leading shield icon was decoration only. --}}
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                        placeholder="123456" maxlength="10"
                        class="ui-field ui-field--lg tracking-widest {{ $errors->has('code') ? 'ui-field--invalid' : '' }}"
                        required>
                </div>

                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-[#262B21] text-center">
                <a href="{{ route('dashboard.siac') }}" class="inline-flex items-center justify-center min-h-[44px] lg:min-h-0 gap-1 text-sm text-forest-500 dark:text-[#339B56] font-semibold hover:text-forest-600 hover:dark:text-[#339B56] transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Retour au tableau de bord' : 'Back to dashboard' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
