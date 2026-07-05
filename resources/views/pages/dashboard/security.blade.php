@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Sécurité du compte' : 'Account Security';
$channelMeta = [
    'email'    => ['icon' => 'mail',           'fr' => 'Email',    'en' => 'Email'],
    'whatsapp' => ['icon' => 'message-circle', 'fr' => 'WhatsApp', 'en' => 'WhatsApp'],
];
@endphp

@section('content')
<div class="max-w-2xl space-y-6">

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

    {{-- Fresh recovery codes — shown exactly once --}}
    @if($freshRecoveryCodes)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="life-buoy" class="w-4 h-4 text-amber-600"></i>
            <h2 class="text-sm font-bold text-amber-800">{{ $lang === 'fr' ? 'Codes de récupération — copiez-les maintenant' : 'Recovery codes — copy them now' }}</h2>
        </div>
        <p class="text-xs text-amber-700 mb-3">
            {{ $lang === 'fr'
                ? 'Chaque code ne fonctionne qu\'une fois. Ils ne seront plus jamais affichés. Conservez-les en lieu sûr.'
                : 'Each code works only once. They will never be shown again. Store them somewhere safe.' }}
        </p>
        <div class="grid grid-cols-2 gap-2 font-mono text-sm text-amber-900 bg-white border border-amber-100 rounded-lg p-4">
            @foreach($freshRecoveryCodes as $code)
            <span>{{ $code }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Passkeys --}}
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#F1EDE4]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i data-lucide="fingerprint" class="w-4 h-4 text-forest-600"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-[#1B1B18]">Passkeys</h2>
                    <p class="text-xs text-[#A8A296]">{{ $lang === 'fr' ? 'Connexion sans mot de passe (empreinte, visage, code PIN)' : 'Passwordless sign-in (fingerprint, face, PIN)' }}</p>
                </div>
            </div>
            <button id="add-passkey" class="inline-flex items-center gap-1.5 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-3 py-2 rounded-lg text-xs transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                {{ $lang === 'fr' ? 'Ajouter' : 'Add passkey' }}
            </button>
        </div>
        <div id="passkey-error" class="hidden px-5 py-3 text-xs text-red-600 border-b border-[#FBF9F4]"></div>
        @forelse($passkeys as $pk)
        <div class="flex items-center gap-3 px-5 py-3 border-b border-[#FBF9F4] last:border-0">
            <i data-lucide="key-round" class="w-4 h-4 text-[#A8A296] shrink-0"></i>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-[#1B1B18] truncate">{{ $pk->name }}</p>
                <p class="text-[11px] text-[#A8A296]">
                    {{ $lang === 'fr' ? 'Créée' : 'Created' }} {{ \Carbon\Carbon::parse($pk->created_at)->diffForHumans() }}
                    @if($pk->last_used_at) · {{ $lang === 'fr' ? 'utilisée' : 'used' }} {{ \Carbon\Carbon::parse($pk->last_used_at)->diffForHumans() }}@endif
                </p>
            </div>
            <form method="POST" action="{{ route('security.passkeys.delete', $pk->id) }}"
                onsubmit="return confirm('{{ $lang === 'fr' ? 'Supprimer cette passkey ?' : 'Delete this passkey?' }}')">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-600">
                    {{ $lang === 'fr' ? 'Supprimer' : 'Delete' }}
                </button>
            </form>
        </div>
        @empty
        <p class="px-5 py-6 text-center text-sm text-[#A8A296]">{{ $lang === 'fr' ? 'Aucune passkey enregistrée.' : 'No passkeys registered yet.' }}</p>
        @endforelse
    </div>

    {{-- Authenticator app (TOTP) --}}
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#F1EDE4]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4 text-forest-600"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Application d\'authentification' : 'Authenticator app' }}</h2>
                    <p class="text-xs text-[#A8A296]">Google Authenticator, Authy, 1Password…</p>
                </div>
            </div>
            @if($totpEnabled)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                <i data-lucide="check" class="w-3 h-3"></i> {{ $lang === 'fr' ? 'Activée' : 'Enabled' }}
            </span>
            @endif
        </div>
        <div class="p-5">
            @if($totpEnabled)
                <form method="POST" action="{{ route('security.totp.disable') }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-[#8A857A] mb-1.5">{{ $lang === 'fr' ? 'Mot de passe (pour désactiver)' : 'Password (to disable)' }}</label>
                        <input name="password" type="password" required autocomplete="current-password"
                            class="w-full px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors whitespace-nowrap">
                        {{ $lang === 'fr' ? 'Désactiver' : 'Disable' }}
                    </button>
                </form>
            @elseif($pendingTotpSecret)
                <div class="grid sm:grid-cols-2 gap-5 items-start">
                    <div class="text-center">
                        <div id="totp-qr" class="inline-block bg-white p-2 border border-[#EFEBE2] rounded-lg"></div>
                        <p class="text-[11px] text-[#A8A296] mt-2 font-mono break-all">{{ $pendingTotpSecret }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#8A857A] mb-3">
                            {{ $lang === 'fr'
                                ? '1. Scannez le QR code avec votre application. 2. Saisissez le code à 6 chiffres généré.'
                                : '1. Scan the QR code with your app. 2. Enter the 6-digit code it generates.' }}
                        </p>
                        <form method="POST" action="{{ route('security.totp.confirm') }}" class="flex items-center gap-2">
                            @csrf
                            <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000"
                                class="w-32 px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm font-mono tracking-widest text-center focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                            <button type="submit" class="px-3.5 py-2 rounded-lg bg-forest-500 text-white text-xs font-semibold hover:bg-forest-600 transition-colors">
                                {{ $lang === 'fr' ? 'Confirmer' : 'Confirm' }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('security.totp.start') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Configurer' : 'Set up' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- OTP channel --}}
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[#F1EDE4]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i data-lucide="message-square-lock" class="w-4 h-4 text-forest-600"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Code à usage unique (OTP)' : 'One-time code (OTP)' }}</h2>
                    <p class="text-xs text-[#A8A296]">{{ $lang === 'fr' ? 'Recevez un code par email ou WhatsApp à chaque connexion' : 'Receive a code by email or WhatsApp at each login' }}</p>
                </div>
            </div>
            @if($channel)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                <i data-lucide="{{ $channelMeta[$channel]['icon'] ?? 'check' }}" class="w-3 h-3"></i>
                {{ $channelMeta[$channel][$lang] ?? $channel }}
            </span>
            @endif
        </div>
        <div class="p-5">
            @if($channel)
                <form method="POST" action="{{ route('security.channel.disable') }}" class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-[#8A857A] mb-1.5">{{ $lang === 'fr' ? 'Mot de passe (pour désactiver)' : 'Password (to disable)' }}</label>
                        <input name="password" type="password" required autocomplete="current-password"
                            class="w-full px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors whitespace-nowrap">
                        {{ $lang === 'fr' ? 'Désactiver' : 'Disable' }}
                    </button>
                </form>
            @elseif($pendingChannel)
                <p class="text-xs text-[#8A857A] mb-3">
                    {{ $lang === 'fr'
                        ? 'Un code vous a été envoyé via ' . ($channelMeta[$pendingChannel][$lang] ?? $pendingChannel) . '. Saisissez-le pour confirmer.'
                        : 'A code was sent via ' . ($channelMeta[$pendingChannel][$lang] ?? $pendingChannel) . '. Enter it to confirm.' }}
                    @if($pendingChannel === 'whatsapp' && ! config('services.twilio.sid'))
                    <span class="block mt-1 text-amber-600">{{ $lang === 'fr' ? '(Twilio non configuré : le code est visible dans storage/logs/laravel.log)' : '(Twilio not configured yet: the code is written to storage/logs/laravel.log)' }}</span>
                    @endif
                </p>
                <form method="POST" action="{{ route('security.channel.confirm') }}" class="flex items-center gap-2">
                    @csrf
                    <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required placeholder="000000"
                        class="w-32 px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm font-mono tracking-widest text-center focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    <button type="submit" class="px-3.5 py-2 rounded-lg bg-forest-500 text-white text-xs font-semibold hover:bg-forest-600 transition-colors">
                        {{ $lang === 'fr' ? 'Confirmer' : 'Confirm' }}
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('security.channel.start') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($channels as $ch)
                        <label class="channel-option flex flex-col items-center gap-1.5 py-3 px-2 rounded-xl border-2 border-[#EFEBE2] has-[:checked]:border-forest-400 has-[:checked]:bg-forest-50 cursor-pointer transition-all">
                            <input type="radio" name="channel" value="{{ $ch }}" class="sr-only" required>
                            <i data-lucide="{{ $channelMeta[$ch]['icon'] ?? 'send' }}" class="w-4 h-4 text-[#8A857A]"></i>
                            <span class="text-xs font-semibold text-[#3B382F]">{{ $channelMeta[$ch][$lang] ?? $ch }}</span>
                        </label>
                        @endforeach
                    </div>
                    <div id="phone-field" class="hidden">
                        <label class="block text-xs font-medium text-[#8A857A] mb-1.5">{{ $lang === 'fr' ? 'Numéro de téléphone (format international)' : 'Phone number (international format)' }}</label>
                        <input name="phone" type="tel" value="{{ $user->phone }}" placeholder="+2376XXXXXXXX"
                            class="w-full px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 bg-forest-500 hover:bg-forest-600 text-white font-semibold px-4 py-2.5 rounded-lg text-sm transition-colors">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Envoyer le code de confirmation' : 'Send confirmation code' }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Recovery codes --}}
    @if($totpEnabled || $channel)
    <div class="bg-white border border-[#EFEBE2] rounded-xl overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-4 border-b border-[#F1EDE4]">
            <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                <i data-lucide="life-buoy" class="w-4 h-4 text-forest-600"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-[#1B1B18]">{{ $lang === 'fr' ? 'Codes de récupération' : 'Recovery codes' }}</h2>
                <p class="text-xs text-[#A8A296]">{{ $recoveryCodesLeft }} {{ $lang === 'fr' ? 'codes restants' : 'codes remaining' }}</p>
            </div>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('security.recovery.regenerate') }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-medium text-[#8A857A] mb-1.5">{{ $lang === 'fr' ? 'Mot de passe (pour régénérer)' : 'Password (to regenerate)' }}</label>
                    <input name="password" type="password" required autocomplete="current-password"
                        class="w-full px-3.5 py-2 border border-[#E4DECF] rounded-lg text-sm focus:outline-none focus:border-forest-400 focus:ring-1 focus:ring-forest-400">
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#F1EDE4] text-[#3B382F] text-xs font-semibold hover:bg-[#EFEBE2] transition-colors whitespace-nowrap">
                    {{ $lang === 'fr' ? 'Régénérer' : 'Regenerate' }}
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
// TOTP QR code
@if($pendingTotpSecret)
new QRCode(document.getElementById('totp-qr'), { text: @json($pendingTotpUri), width: 160, height: 160 });
@endif

// Show phone field for whatsapp
document.querySelectorAll('input[name="channel"]').forEach(function (r) {
    r.addEventListener('change', function () {
        document.getElementById('phone-field').classList.toggle('hidden', this.value === 'email');
    });
});

// Passkey registration
function b64ToBuf(obj) {
    // lbuchs/webauthn serializes binary as "=?BINARY?B?<base64>?="
    if (typeof obj === 'string' && obj.startsWith('=?BINARY?B?')) {
        const b64 = obj.substring(11, obj.length - 2);
        const bin = atob(b64);
        const buf = new Uint8Array(bin.length);
        for (let i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
        return buf.buffer;
    }
    if (obj && typeof obj === 'object') {
        for (const k of Object.keys(obj)) obj[k] = b64ToBuf(obj[k]);
    }
    return obj;
}
function bufToB64(buf) {
    return btoa(String.fromCharCode(...new Uint8Array(buf)));
}
function bufToB64Url(buf) {
    return bufToB64(buf).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

const csrf = @json(csrf_token());

document.getElementById('add-passkey')?.addEventListener('click', async function () {
    const errBox = document.getElementById('passkey-error');
    errBox.classList.add('hidden');
    try {
        if (!window.PublicKeyCredential) throw new Error(@json($lang === 'fr' ? "Votre navigateur ne supporte pas les passkeys." : "Your browser does not support passkeys."));

        const optRes = await fetch(@json(route('security.passkeys.options')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        });
        if (!optRes.ok) throw new Error('options: ' + optRes.status);
        const args = b64ToBuf(await optRes.json());

        const cred = await navigator.credentials.create(args);

        const name = prompt(@json($lang === 'fr' ? 'Nom de cette passkey (ex. "PC du bureau") :' : 'Name this passkey (e.g. "Work laptop"):')) || 'Passkey';

        const res = await fetch(@json(route('security.passkeys.register')), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: name,
                clientDataJSON: bufToB64(cred.response.clientDataJSON),
                attestationObject: bufToB64(cred.response.attestationObject),
            }),
        });
        if (!res.ok) {
            const j = await res.json().catch(() => ({}));
            throw new Error(j.message || ('register: ' + res.status));
        }
        window.location.reload();
    } catch (e) {
        errBox.textContent = e.message || e;
        errBox.classList.remove('hidden');
    }
});
</script>
@endsection
