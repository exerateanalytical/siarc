@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">

        {{-- Brand --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-forest-500 rounded-2xl mb-4 shadow-lg">
                <i data-lucide="user-plus" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $lang === 'fr' ? 'Créer un compte' : 'Create Account' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">SIAC Cameroun 2026</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">

            @if($errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/inscription">
                @csrf
                <input type="hidden" name="lang" value="{{ $lang }}">

                {{-- Role selector --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $lang === 'fr' ? 'Je suis...' : 'I am...' }}
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="buyer" class="sr-only peer" checked>
                            <div class="peer-checked:border-forest-500 peer-checked:bg-forest-50 border-2 border-gray-200 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                <i data-lucide="shopping-bag" class="w-5 h-5 mx-auto mb-1 text-gray-500"></i>
                                <p class="text-xs font-semibold text-gray-700">{{ $lang === 'fr' ? 'Acheteur / Investisseur' : 'Buyer / Investor' }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $lang === 'fr' ? 'Je cherche des produits' : 'I browse & contact' }}</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="business_owner" class="sr-only peer">
                            <div class="peer-checked:border-forest-500 peer-checked:bg-forest-50 border-2 border-gray-200 rounded-xl p-3 text-center transition-all hover:border-gray-300">
                                <i data-lucide="briefcase" class="w-5 h-5 mx-auto mb-1 text-gray-500"></i>
                                <p class="text-xs font-semibold text-gray-700">{{ $lang === 'fr' ? 'Entrepreneur' : 'Business Owner' }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $lang === 'fr' ? 'Je vends mes produits' : 'I sell my products' }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $lang === 'fr' ? 'Nom complet' : 'Full name' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input name="name" type="text" value="{{ old('name') }}"
                            placeholder="{{ $lang === 'fr' ? 'Votre nom' : 'Your name' }}"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input name="email" type="email" value="{{ old('email') }}"
                            placeholder="{{ $lang === 'fr' ? 'votre@email.cm' : 'your@email.com' }}"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $lang === 'fr' ? 'Téléphone (optionnel)' : 'Phone (optional)' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input name="phone" type="tel" value="{{ old('phone') }}"
                            placeholder="+237 6xx xxx xxx"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $lang === 'fr' ? 'Mot de passe' : 'Password' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input name="password" type="password"
                            placeholder="{{ $lang === 'fr' ? 'Minimum 8 caractères' : 'Minimum 8 characters' }}"
                            class="w-full pl-10 pr-4 py-2.5 border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        {{ $lang === 'fr' ? 'Confirmer le mot de passe' : 'Confirm password' }}
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        <input name="password_confirmation" type="password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 transition"
                            required>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Créer mon compte' : 'Create Account' }}
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    {{ $lang === 'fr' ? 'Déjà un compte ?' : 'Already have an account?' }}
                    <a href="/login?lang={{ $lang }}" class="text-forest-500 font-semibold hover:text-forest-600 ml-1">
                        {{ $lang === 'fr' ? 'Se connecter' : 'Sign In' }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
