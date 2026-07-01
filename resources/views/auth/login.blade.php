@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Logo / Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="store" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Connexion' : 'Sign In' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $lang === 'fr' ? 'Galerie Virtuelle — SIAC Cameroun 2026' : 'Virtual Gallery — SIAC Cameroon 2026' }}
            </p>
        </div>

        {{-- Demo Login Cards --}}
        <div class="mb-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide text-center mb-3">
                {{ $lang === 'fr' ? 'Comptes de démonstration' : 'Demo accounts' }}
            </p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillDemo('admin@artisanatcameroun.cm','Admin@SIAC2026')"
                    class="demo-btn flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 border-gray-200 hover:border-forest-400 hover:bg-forest-50 transition-all cursor-pointer text-center group">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <i data-lucide="shield-check" class="w-4 h-4 text-red-600"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">Admin</span>
                    <span class="text-[10px] text-gray-400 leading-tight">admin@artisanat<br>cameroun.cm</span>
                </button>
                <button type="button" onclick="fillDemo('entrepreneur@siac2026.cm','Demo@SIAC2026')"
                    class="demo-btn flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 border-gray-200 hover:border-forest-400 hover:bg-forest-50 transition-all cursor-pointer text-center group">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                        <i data-lucide="briefcase" class="w-4 h-4 text-amber-600"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">{{ $lang === 'fr' ? 'Entrepreneur' : 'Business' }}</span>
                    <span class="text-[10px] text-gray-400 leading-tight">entrepreneur<br>@siac2026.cm</span>
                </button>
                <button type="button" onclick="fillDemo('acheteur@siac2026.cm','Demo@SIAC2026')"
                    class="demo-btn flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 border-gray-200 hover:border-forest-400 hover:bg-forest-50 transition-all cursor-pointer text-center group">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-green-600"></i>
                    </div>
                    <span class="text-xs font-semibold text-gray-700">{{ $lang === 'fr' ? 'Acheteur' : 'Buyer' }}</span>
                    <span class="text-[10px] text-gray-400 leading-tight">acheteur<br>@siac2026.cm</span>
                </button>
            </div>
            <p class="text-center text-[10px] text-gray-400 mt-2">
                {{ $lang === 'fr' ? 'Mot de passe demo :' : 'Demo password:' }}
                <code class="bg-gray-100 px-1 rounded font-mono">Admin@SIAC2026</code> /
                <code class="bg-gray-100 px-1 rounded font-mono">Demo@SIAC2026</code>
            </p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if(session('success'))
                <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
                    <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" id="login-form">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                <div class="mb-5">
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

                <div class="mb-6">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-medium text-gray-700" for="password">
                            {{ $lang === 'fr' ? 'Mot de passe' : 'Password' }}
                        </label>
                    </div>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input id="password" name="password" type="password" autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Se connecter' : 'Sign In' }}
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    {{ $lang === 'fr' ? 'Pas encore de compte ?' : 'No account yet?' }}
                    <a href="/inscription?lang={{ $lang }}" class="text-forest-500 font-semibold hover:text-forest-600 ml-1">
                        {{ $lang === 'fr' ? 'S\'inscrire' : 'Register free' }}
                    </a>
                </p>
                <a href="{{ route('home', ['lang' => $lang]) }}" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 mt-3 transition-colors">
                    <i data-lucide="arrow-left" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Continuer sans compte' : 'Browse as guest' }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.querySelectorAll('.demo-btn').forEach(b => b.classList.remove('border-forest-500', 'bg-forest-50'));
    event.currentTarget.classList.add('border-forest-500');
}
</script>
@endsection
