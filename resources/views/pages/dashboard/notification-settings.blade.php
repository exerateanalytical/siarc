@php $dashLayout = !empty(session('siac_user')['is_admin']) ? 'layouts.admin' : 'layouts.dashboard'; @endphp
@extends($dashLayout)

@php
$pageTitle = $lang === 'fr' ? 'Préférences de notification' : 'Notification Preferences';
$categories = [
    'messages'     => ['icon' => 'message-circle', 'fr' => 'Messages', 'en' => 'Messages', 'desc_fr' => 'Nouveaux messages et réponses', 'desc_en' => 'New messages and replies'],
    'verification' => ['icon' => 'badge-check', 'fr' => 'Vérification', 'en' => 'Verification', 'desc_fr' => 'Statut de vos demandes de vérification', 'desc_en' => 'Status of your verification applications'],
    'business'     => ['icon' => 'building-2', 'fr' => 'Entreprise', 'en' => 'Business', 'desc_fr' => 'Changements de statut de votre entreprise', 'desc_en' => 'Your business status changes'],
    'events'       => ['icon' => 'calendar-days', 'fr' => 'Événements', 'en' => 'Events', 'desc_fr' => 'Rappels et confirmations d\'événements', 'desc_en' => 'Event reminders and confirmations'],
];
$channels = ['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'];
// Anything not stored yet defaults to enabled (matches the DB column default).
$isOn = fn (string $cat, string $ch) => !isset($stored[$cat . '.' . $ch]) || $stored[$cat . '.' . $ch]->is_enabled;
@endphp

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
        <div class="mb-4 flex items-start gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('notifications.settings.save') }}">
        @csrf
        <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#F1EDE4]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                        <i data-lucide="bell-ring" class="w-4 h-4 text-forest-600"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Canaux par type de notification' : 'Channels per notification type' }}</h2>
                </div>
                <div class="hidden sm:flex items-center gap-6 pr-1">
                    @foreach($channels as $key => $label)
                    <span class="text-[10px] font-semibold text-[#A8A296] uppercase tracking-wide w-10 text-center">{{ $label }}</span>
                    @endforeach
                </div>
            </div>

            @foreach($categories as $catKey => $cat)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-[#FBF9F4] last:border-0">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[#FBF9F4] flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $cat['icon'] }}" class="w-4 h-4 text-[#8A857A]"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-[#1B1B18]">{{ $cat[$lang] }}</p>
                        <p class="text-xs text-[#A8A296] truncate">{{ $cat['desc_' . $lang] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 pl-11 sm:pl-0">
                    @foreach($channels as $chKey => $chLabel)
                    <label class="flex flex-col items-center gap-1 w-10 cursor-pointer">
                        <span class="sm:hidden text-[9px] font-semibold text-[#A8A296] uppercase">{{ $chLabel }}</span>
                        <input type="checkbox" name="prefs[{{ $catKey }}][{{ $chKey }}]" value="1"
                            {{ $isOn($catKey, $chKey) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-[#E4DECF] text-forest-500 focus:ring-forest-400">
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Enregistrer' : 'Save preferences' }}
            </button>
        </div>
    </form>
</div>
@endsection
