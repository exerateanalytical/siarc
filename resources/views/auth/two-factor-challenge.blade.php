@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Vérification en deux étapes' : 'Two-step verification' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $lang === 'fr' ? 'Confirmez votre identité pour continuer.' : 'Confirm your identity to continue.' }}
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if(session('success'))
                <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
                    <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if($hasTotp)
            {{-- Authenticator app code --}}
            <form method="POST" action="{{ route('login.challenge.verify') }}" class="mb-5">
                @csrf
                <input type="hidden" name="method" value="totp">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    {{ $lang === 'fr' ? 'Code de votre application d\'authentification' : 'Code from your authenticator app' }}
                </label>
                <div class="flex items-center gap-2">
                    <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000" autofocus
                        class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm font-mono tracking-widest text-center focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-forest-500 text-white text-sm font-semibold hover:bg-forest-600 transition-colors">
                        {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                    </button>
                </div>
            </form>
            @endif

            @if($channel)
            {{-- OTP channel --}}
            <div class="{{ $hasTotp ? 'pt-5 border-t border-gray-100' : '' }} mb-5">
                <p class="text-sm text-gray-600 mb-3">
                    {{ $lang === 'fr' ? 'Recevoir un code' : 'Receive a code' }}
                    <span class="font-semibold">{{ ['email' => 'par email', 'whatsapp' => 'via WhatsApp'][$channel] ?? $channel }}</span>
                    @if($maskedDest)<span class="text-gray-400">({{ $maskedDest }})</span>@endif
                </p>
                <form method="POST" action="{{ route('login.challenge.send') }}" class="mb-3">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-colors">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        {{ $lang === 'fr' ? 'Envoyer le code' : 'Send code' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('login.challenge.verify') }}">
                    @csrf
                    <input type="hidden" name="method" value="channel">
                    <div class="flex items-center gap-2">
                        <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000"
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm font-mono tracking-widest text-center focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-forest-500 text-white text-sm font-semibold hover:bg-forest-600 transition-colors">
                            {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Recovery code --}}
            <div class="pt-5 border-t border-gray-100">
                <details>
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                        {{ $lang === 'fr' ? 'Vous n\'avez pas accès à vos appareils ? Utilisez un code de récupération.' : 'Lost access to your devices? Use a recovery code.' }}
                    </summary>
                    <form method="POST" action="{{ route('login.challenge.verify') }}" class="mt-3 flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="method" value="recovery">
                        <input name="code" maxlength="20" required placeholder="XXXXX-XXXXX"
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm font-mono text-center focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900 transition-colors">
                            {{ $lang === 'fr' ? 'Utiliser' : 'Use' }}
                        </button>
                    </form>
                </details>
            </div>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <a href="/login" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Retour à la connexion' : 'Back to login' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
