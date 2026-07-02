@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Paramètres & Intégrations' : 'Settings & Integrations';
$groupMeta = [
    'general' => ['icon' => 'sliders-horizontal', 'fr' => 'Général',            'en' => 'General'],
    'limits'  => ['icon' => 'gauge',              'fr' => 'Limites plateforme', 'en' => 'Platform limits'],
    'siac'    => ['icon' => 'calendar-days',      'fr' => 'Événement SIAC',     'en' => 'SIAC event'],
    'auth'    => ['icon' => 'shield-check',       'fr' => 'Authentification',   'en' => 'Authentication'],
];
$labelize = fn ($key) => ucfirst(str_replace('_', ' ', $key));
@endphp

@section('content')
<div class="max-w-3xl space-y-6">

    @if(session('success'))
        <div class="flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Platform settings --}}
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @foreach($groups as $group => $settings)
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex items-center gap-2.5 px-5 py-4 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i data-lucide="{{ $groupMeta[$group]['icon'] ?? 'settings-2' }}" class="w-4 h-4 text-forest-600"></i>
                </div>
                <h2 class="text-sm font-semibold text-gray-900">{{ $groupMeta[$group][$lang] ?? ucfirst($group) }}</h2>
            </div>
            <div class="p-5 grid sm:grid-cols-2 gap-4">
                @foreach($settings as $setting)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $labelize($setting['key']) }}</label>
                    @if($setting['type'] === 'boolean')
                        <select name="settings[{{ $setting['key'] }}]"
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400 bg-white">
                            <option value="true"  @selected($setting['value'])>{{ $lang === 'fr' ? 'Activé' : 'Enabled' }}</option>
                            <option value="false" @selected(! $setting['value'])>{{ $lang === 'fr' ? 'Désactivé' : 'Disabled' }}</option>
                        </select>
                    @elseif($setting['type'] === 'integer')
                        <input type="number" name="settings[{{ $setting['key'] }}]" value="{{ $setting['value'] }}"
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    @else
                        <input type="text" name="settings[{{ $setting['key'] }}]" value="{{ $setting['value'] }}"
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $lang === 'fr' ? 'Enregistrer les paramètres' : 'Save settings' }}
        </button>
    </form>

    {{-- Twilio WhatsApp integration --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i data-lucide="message-circle" class="w-4 h-4 text-forest-600"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Twilio — WhatsApp OTP</h2>
                    <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Envoi des codes de connexion via WhatsApp' : 'Delivers login codes over WhatsApp' }}</p>
                </div>
            </div>
            @if($twilio['configured'])
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                <i data-lucide="check" class="w-3 h-3"></i>
                {{ $twilio['from_env'] ? ($lang === 'fr' ? 'Configuré (.env)' : 'Configured (.env)') : ($lang === 'fr' ? 'Configuré' : 'Configured') }}
            </span>
            @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">
                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                {{ $lang === 'fr' ? 'Non configuré' : 'Not configured' }}
            </span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.settings.twilio') }}" class="p-5 space-y-4 border-b border-gray-100">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Account SID</label>
                    <input type="text" name="sid" autocomplete="off"
                        placeholder="{{ $twilio['sid_masked'] ?? 'ACxxxxxxxxxxxxxxxx' }}"
                        class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Auth Token</label>
                    <input type="password" name="token" autocomplete="new-password"
                        placeholder="{{ $twilio['token_masked'] ?? '••••••••••••••••' }}"
                        class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $lang === 'fr' ? 'Numéro expéditeur WhatsApp' : 'WhatsApp sender number' }}</label>
                    <input type="tel" name="whatsapp_from" value="{{ $twilio['from'] }}" placeholder="+14155238886"
                        class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                </div>
            </div>
            <p class="text-xs text-gray-400">
                {{ $lang === 'fr'
                    ? 'Les champs laissés vides conservent leur valeur actuelle. Les identifiants sont chiffrés en base et affichés masqués. Les valeurs saisies ici priment sur le fichier .env.'
                    : 'Blank fields keep their current value. Credentials are encrypted at rest and shown masked. Values saved here override the .env file.' }}
            </p>
            <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                <i data-lucide="key-round" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Enregistrer les identifiants' : 'Save credentials' }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.settings.twilio.test') }}" class="p-5 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1.5">{{ $lang === 'fr' ? 'Tester la connexion — envoyer un message WhatsApp à' : 'Test connection — send a WhatsApp message to' }}</label>
                <input type="tel" name="test_phone" required placeholder="+2376XXXXXXXX"
                    class="w-full px-3.5 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
            </div>
            <button type="submit" class="px-3.5 py-2 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold hover:bg-gray-200 transition-colors whitespace-nowrap">
                {{ $lang === 'fr' ? 'Envoyer le test' : 'Send test' }}
            </button>
        </form>
    </div>
</div>
@endsection
