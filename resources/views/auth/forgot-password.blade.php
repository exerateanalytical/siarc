@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="key-round" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Mot de passe oublié' : 'Forgot Password' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $lang === 'fr' ? 'Nous vous enverrons un lien de réinitialisation.' : 'We will send you a link to reset your password.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if(session('status'))
                <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
                    <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('dev_reset_url'))
                <div class="mb-4 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800 break-all">
                    <i data-lucide="flask-conical" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <div>
                        <strong class="block mb-1">{{ $lang === 'fr' ? 'Mode développement — lien de réinitialisation :' : 'Dev mode — reset link:' }}</strong>
                        <a href="{{ session('dev_reset_url') }}" class="text-amber-900 underline break-all">{{ session('dev_reset_url') }}</a>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(!session('status'))
            <form method="POST" action="/forgot-password">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">
                        {{ $lang === 'fr' ? 'Adresse email' : 'Email address' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input id="email" name="email" type="email" autocomplete="email"
                            value="{{ old('email') }}"
                            placeholder="{{ $lang === 'fr' ? 'votre@email.cm' : 'your@email.com' }}"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required autofocus>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Envoyer le lien' : 'Send Reset Link' }}
                </button>
            </form>
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
