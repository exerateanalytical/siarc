@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="key-round" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-[#F3EFE7]">
                {{ $lang === 'fr' ? 'Mot de passe oublié' : 'Forgot Password' }}
            </h1>
            <p class="text-gray-500 dark:text-[#868778] text-sm mt-1">
                {{ $lang === 'fr' ? 'Nous vous enverrons un lien de réinitialisation.' : 'We will send you a link to reset your password.' }}
            </p>
        </div>

        {{-- Card --}}
        <div class="ui-card">

            @if(session('status'))
                <div class="mb-4 ui-alert ui-alert-ok">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if(session('dev_reset_url'))
                <div class="mb-4 ui-alert ui-alert-warn break-all">
                    <i data-lucide="flask-conical" class="w-4 h-4"></i>
                    <div>
                        <strong class="block mb-1">{{ $lang === 'fr' ? 'Mode développement — lien de réinitialisation :' : 'Dev mode — reset link:' }}</strong>
                        <a href="{{ session('dev_reset_url') }}" class="underline break-all">{{ session('dev_reset_url') }}</a>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 ui-alert ui-alert-danger">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(!session('status'))
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                <div class="mb-6">
                    <label class="ui-label" for="email">
                        {{ $lang === 'fr' ? 'Adresse email' : 'Email address' }}
                    </label>
                    {{-- The old leading mail icon was decoration only; the kit field carries
                         the meaning on its own, as on the admin dashboard. --}}
                    <input id="email" name="email" type="email" autocomplete="email"
                        value="{{ old('email') }}"
                        placeholder="{{ $lang === 'fr' ? 'votre@email.cm' : 'your@email.com' }}"
                        class="ui-field ui-field--lg {{ $errors->has('email') ? 'ui-field--invalid' : '' }}"
                        required autofocus>
                </div>

                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Envoyer le lien' : 'Send Reset Link' }}
                </button>
            </form>
            @endif

            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-[#262B21] text-center">
                <a href="{{ route('login', ['lang' => $lang]) }}" class="inline-flex items-center justify-center min-h-[44px] lg:min-h-0 gap-1 text-sm text-forest-500 dark:text-[#339B56] font-semibold hover:text-forest-600 hover:dark:text-[#339B56] transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    {{ $lang === 'fr' ? 'Retour à la connexion' : 'Back to login' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
