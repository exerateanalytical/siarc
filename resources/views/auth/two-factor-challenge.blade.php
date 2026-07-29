@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-[#F3EFE7]">
                {{ $lang === 'fr' ? 'Vérification en deux étapes' : 'Two-step verification' }}
            </h1>
            <p class="text-gray-500 dark:text-[#868778] text-sm mt-1">
                {{ $lang === 'fr' ? 'Confirmez votre identité pour continuer.' : 'Confirm your identity to continue.' }}
            </p>
        </div>

        <div class="ui-card">

            @if(session('success'))
                <div class="mb-4 ui-alert ui-alert-ok">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    {{ session('success') }}
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

            @if($hasTotp)
            {{-- Authenticator app code --}}
            <form method="POST" action="{{ route('login.challenge.verify') }}" class="mb-5">
                @csrf
                <input type="hidden" name="method" value="totp">
                <label class="ui-label">
                    {{ $lang === 'fr' ? 'Code de votre application d\'authentification' : 'Code from your authenticator app' }}
                </label>
                <div class="flex items-center gap-2">
                    <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000" autofocus
                        class="ui-field ui-field--lg flex-1 font-mono tracking-widest text-center">
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg">
                        {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                    </button>
                </div>
            </form>
            @endif

            @if($channel)
            {{-- OTP channel --}}
            <div class="{{ $hasTotp ? 'pt-5 border-t border-gray-100 dark:border-[#262B21]' : '' }} mb-5">
                <p class="text-sm text-gray-600 dark:text-[#B4B5A6] mb-3">
                    {{ $lang === 'fr' ? 'Recevoir un code' : 'Receive a code' }}
                    <span class="font-semibold">{{ ['email' => 'par email', 'whatsapp' => 'via WhatsApp'][$channel] ?? $channel }}</span>
                    @if($maskedDest)<span class="text-gray-400">({{ $maskedDest }})</span>@endif
                </p>
                <form method="POST" action="{{ route('login.challenge.send') }}" class="mb-3">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-secondary ui-btn-sm">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        {{ $lang === 'fr' ? 'Envoyer le code' : 'Send code' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('login.challenge.verify') }}">
                    @csrf
                    <input type="hidden" name="method" value="channel">
                    <div class="flex items-center gap-2">
                        <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000"
                            class="ui-field ui-field--lg flex-1 font-mono tracking-widest text-center">
                        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg">
                            {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Recovery code --}}
            <div class="pt-5 border-t border-gray-100 dark:border-[#262B21]">
                <details>
                    <summary class="flex items-center min-h-[44px] lg:min-h-0 text-xs text-gray-500 dark:text-[#868778] cursor-pointer hover:text-gray-700 hover:dark:text-[#B4B5A6]">
                        {{ $lang === 'fr' ? 'Vous n\'avez pas accès à vos appareils ? Utilisez un code de récupération.' : 'Lost access to your devices? Use a recovery code.' }}
                    </summary>
                    <form method="POST" action="{{ route('login.challenge.verify') }}" class="mt-3 flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="method" value="recovery">
                        <input name="code" maxlength="20" required placeholder="XXXXX-XXXXX"
                            class="ui-field ui-field--lg flex-1 font-mono text-center">
                        <button type="submit" class="ui-btn ui-btn-secondary ui-btn-lg">
                            {{ $lang === 'fr' ? 'Utiliser' : 'Use' }}
                        </button>
                    </form>
                </details>
            </div>

            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-[#262B21] text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center min-h-[44px] lg:min-h-0 gap-1 text-xs text-gray-400 hover:text-gray-600 hover:dark:text-[#B4B5A6] transition-colors">
                    <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Retour à la connexion' : 'Back to login' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
