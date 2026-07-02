@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="mail-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Vérifiez votre email' : 'Verify your email' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $lang === 'fr'
                    ? 'Un code à 6 chiffres sera envoyé à ' . $email . '.'
                    : 'A 6-digit code will be sent to ' . $email . '.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if(session('info'))
                <div class="mb-4 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800">
                    <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('info') }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
                    <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('status') }}
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

            {{-- Send code --}}
            <form method="POST" action="{{ route('email.verify.send') }}" class="mb-6">
                @csrf
                <button type="submit"
                    class="w-full bg-white border border-forest-500 text-forest-600 hover:bg-forest-50 font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="code">
                        {{ $lang === 'fr' ? 'Code de vérification' : 'Verification code' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="shield-check" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                            placeholder="123456" maxlength="10"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('code') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm tracking-widest focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <a href="/tableau-de-bord" class="inline-flex items-center gap-1 text-sm text-forest-500 font-semibold hover:text-forest-600 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Retour au tableau de bord' : 'Back to dashboard' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
