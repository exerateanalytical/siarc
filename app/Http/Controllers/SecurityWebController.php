<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Services\OtpService;
use App\Modules\Auth\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use lbuchs\WebAuthn\WebAuthn;

class SecurityWebController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly OtpService $otp,
    ) {}

    private function lang(Request $request): string
    {
        $lang = $request->cookie('lang', 'fr');
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    private function currentUser(Request $request): ?object
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return null;
        }
        return DB::table('users')->where('id', $siacUser['id'])->whereNull('deleted_at')->first();
    }

    private function webAuthn(Request $request): WebAuthn
    {
        return new WebAuthn('Galerie Artisanat Cameroun', $request->getHost(), ['none']);
    }

    // ─────────────────────────────────────────
    // Page
    // ─────────────────────────────────────────
    public function show(Request $request)
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang = $this->lang($request);

        $passkeys = DB::table('user_passkeys')->where('user_id', $user->id)->orderByDesc('created_at')->get();

        $recoveryCodesLeft = 0;
        if ($user->two_factor_recovery_codes) {
            try {
                $recoveryCodesLeft = count(json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true) ?: []);
            } catch (\Throwable $e) {
            }
        }

        $pendingTotpSecret = session('totp_pending_secret');
        $pendingTotpUri = $pendingTotpSecret ? $this->totp->otpauthUri($pendingTotpSecret, $user->email) : null;

        return view('pages.dashboard.security', [
            'lang'              => $lang,
            'user'              => $user,
            'siacUser'          => session('siac_user'),
            'totpEnabled'       => (bool) $user->two_factor_confirmed_at,
            'channel'           => $user->two_factor_channel,
            'channels'          => config('otp.enabled_channels', []),
            'pendingChannel'    => session('channel_pending'),
            'pendingTotpSecret' => $pendingTotpSecret,
            'pendingTotpUri'    => $pendingTotpUri,
            'passkeys'          => $passkeys,
            'recoveryCodesLeft' => $recoveryCodesLeft,
            'freshRecoveryCodes' => session('fresh_recovery_codes'),
        ]);
    }

    // ─────────────────────────────────────────
    // TOTP (authenticator app)
    // ─────────────────────────────────────────
    public function startTotp(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        session(['totp_pending_secret' => $this->totp->generateSecret()]);

        return redirect()->route('security.show');
    }

    public function confirmTotp(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang   = $this->lang($request);
        $secret = session('totp_pending_secret');
        $data   = $request->validate(['code' => ['required', 'string']]);

        if (! $secret || ! $this->totp->verify($secret, $data['code'])) {
            return back()->withErrors(['code' => $lang === 'fr' ? 'Code invalide. Réessayez.' : 'Invalid code. Try again.']);
        }

        $codes = $this->issueRecoveryCodes($user->id);

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_secret'       => Crypt::encryptString($secret),
            'two_factor_confirmed_at' => now(),
            'updated_at'              => now(),
        ]);

        session()->forget('totp_pending_secret');

        return redirect()->route('security.show')
            ->with('fresh_recovery_codes', $codes)
            ->with('success', $lang === 'fr' ? 'Authentification à deux facteurs activée.' : 'Two-factor authentication enabled.');
    }

    public function disableTotp(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang = $this->lang($request);
        $data = $request->validate(['password' => ['required']]);

        if (! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => $lang === 'fr' ? 'Mot de passe incorrect.' : 'Incorrect password.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_secret'         => null,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => $user->two_factor_channel ? $user->two_factor_recovery_codes : null,
            'updated_at'                => now(),
        ]);

        return redirect()->route('security.show')
            ->with('success', $lang === 'fr' ? 'Application d\'authentification désactivée.' : 'Authenticator app disabled.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang = $this->lang($request);
        $data = $request->validate(['password' => ['required']]);

        if (! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => $lang === 'fr' ? 'Mot de passe incorrect.' : 'Incorrect password.']);
        }

        $codes = $this->issueRecoveryCodes($user->id);

        return redirect()->route('security.show')
            ->with('fresh_recovery_codes', $codes)
            ->with('success', $lang === 'fr' ? 'Nouveaux codes de récupération générés.' : 'New recovery codes generated.');
    }

    /** Generate 10 recovery codes; store sha256 hashes encrypted; return plaintext. */
    private function issueRecoveryCodes(string $userId): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(Str::random(5) . '-' . Str::random(5));
        }

        DB::table('users')->where('id', $userId)->update([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_map(fn ($c) => hash('sha256', $c), $codes))),
            'updated_at'                => now(),
        ]);

        return $codes;
    }

    // ─────────────────────────────────────────
    // OTP channel (email / sms / whatsapp)
    // ─────────────────────────────────────────
    public function startChannel(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang = $this->lang($request);
        $data = $request->validate([
            'channel' => ['required', 'in:' . implode(',', config('otp.enabled_channels', []))],
            'phone'   => ['nullable', 'string', 'max:30'],
        ]);

        if (in_array($data['channel'], ['sms', 'whatsapp'])) {
            $phone = trim((string) ($data['phone'] ?? $user->phone));
            if ($phone === '') {
                return back()->withErrors(['phone' => $lang === 'fr' ? 'Un numéro de téléphone est requis pour ce canal.' : 'A phone number is required for this channel.']);
            }
            if ($phone !== $user->phone) {
                DB::table('users')->where('id', $user->id)->update(['phone' => $phone, 'updated_at' => now()]);
                $user->phone = $phone;
            }
            $identifier = $phone;
        } else {
            $identifier = $user->email;
        }

        $sent = $this->otp->send($identifier, 'enroll', $data['channel'], $user->id, $lang);
        if (! $sent) {
            return back()->withErrors(['channel' => $lang === 'fr' ? 'Trop de codes demandés. Réessayez plus tard.' : 'Too many codes requested. Try again later.']);
        }

        session(['channel_pending' => $data['channel']]);

        return redirect()->route('security.show')
            ->with('success', $lang === 'fr' ? 'Code envoyé. Saisissez-le pour confirmer.' : 'Code sent. Enter it to confirm.');
    }

    public function confirmChannel(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang    = $this->lang($request);
        $channel = session('channel_pending');
        $data    = $request->validate(['code' => ['required', 'string']]);

        if (! $channel) {
            return redirect()->route('security.show');
        }

        $identifier = $channel === 'email' ? $user->email : (string) $user->phone;

        if (! $this->otp->verify($identifier, $data['code'], 'enroll')) {
            return back()->withErrors(['code' => $lang === 'fr' ? 'Code invalide ou expiré.' : 'Invalid or expired code.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_channel' => $channel,
            'updated_at'         => now(),
        ]);
        if ($channel !== 'email') {
            DB::table('users')->where('id', $user->id)->update(['is_phone_verified' => 1]);
        }

        // Users without an authenticator app still need recovery codes
        $freshCodes = null;
        if (! $user->two_factor_recovery_codes) {
            $freshCodes = $this->issueRecoveryCodes($user->id);
        }

        session()->forget('channel_pending');

        $redirect = redirect()->route('security.show')
            ->with('success', $lang === 'fr' ? 'Vérification par code activée.' : 'Code verification enabled.');

        return $freshCodes ? $redirect->with('fresh_recovery_codes', $freshCodes) : $redirect;
    }

    public function disableChannel(Request $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        $lang = $this->lang($request);
        $data = $request->validate(['password' => ['required']]);

        if (! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => $lang === 'fr' ? 'Mot de passe incorrect.' : 'Incorrect password.']);
        }

        DB::table('users')->where('id', $user->id)->update([
            'two_factor_channel'        => null,
            'two_factor_recovery_codes' => $user->two_factor_confirmed_at ? $user->two_factor_recovery_codes : null,
            'updated_at'                => now(),
        ]);

        return redirect()->route('security.show')
            ->with('success', $lang === 'fr' ? 'Vérification par code désactivée.' : 'Code verification disabled.');
    }

    // ─────────────────────────────────────────
    // Passkeys (WebAuthn)
    // ─────────────────────────────────────────
    public function passkeyRegisterOptions(Request $request)
    {
        $user = $this->currentUser($request);
        abort_unless((bool) $user, 401);

        $webAuthn = $this->webAuthn($request);
        $args = $webAuthn->getCreateArgs(
            \hex2bin(str_replace('-', '', $user->id)) ?: $user->id,
            $user->email,
            $user->name ?? $user->email,
            240,
            true,       // resident key: allows usernameless login
            'required'  // user verification (biometric / PIN)
        );

        session(['webauthn_challenge' => (string) $webAuthn->getChallenge()]);

        return response()->json($args);
    }

    public function passkeyRegister(Request $request)
    {
        $user = $this->currentUser($request);
        abort_unless((bool) $user, 401);

        $data = $request->validate([
            'name'              => ['nullable', 'string', 'max:100'],
            'clientDataJSON'    => ['required', 'string'],
            'attestationObject' => ['required', 'string'],
        ]);

        $challenge = session('webauthn_challenge');
        abort_unless((bool) $challenge, 422, 'No registration in progress.');

        try {
            $webAuthn = $this->webAuthn($request);
            $result = $webAuthn->processCreate(
                base64_decode($data['clientDataJSON']),
                base64_decode($data['attestationObject']),
                new \lbuchs\WebAuthn\Binary\ByteBuffer($challenge),
                true,  // user verification required
                true   // fail if rootCert missing not relevant for 'none'
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Passkey registration failed: ' . $e->getMessage()], 422);
        }

        session()->forget('webauthn_challenge');

        DB::table('user_passkeys')->insert([
            'user_id'       => $user->id,
            'name'          => $data['name'] ?: 'Passkey',
            'credential_id' => rtrim(strtr(base64_encode($result->credentialId), '+/', '-_'), '='),
            'public_key'    => $result->credentialPublicKey,
            'sign_count'    => (int) ($result->signCount ?? 0),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['message' => 'ok']);
    }

    public function passkeyDelete(Request $request, int $id): RedirectResponse
    {
        $user = $this->currentUser($request);
        if (! $user) return redirect('/login');

        DB::table('user_passkeys')->where('id', $id)->where('user_id', $user->id)->delete();

        return redirect()->route('security.show')
            ->with('success', $this->lang($request) === 'fr' ? 'Passkey supprimée.' : 'Passkey deleted.');
    }

    // ─────────────────────────────────────────
    // Passkey login (guest)
    // ─────────────────────────────────────────
    public function passkeyLoginOptions(Request $request)
    {
        $webAuthn = $this->webAuthn($request);
        // Empty allow-list: discoverable (resident) credentials let the
        // browser offer whatever passkeys it holds for this site.
        $args = $webAuthn->getGetArgs([], 240, true, true, true, true, 'required');

        session(['webauthn_login_challenge' => (string) $webAuthn->getChallenge()]);

        return response()->json($args);
    }

    public function passkeyLogin(Request $request)
    {
        $data = $request->validate([
            'id'                => ['required', 'string'], // base64url credential id
            'clientDataJSON'    => ['required', 'string'],
            'authenticatorData' => ['required', 'string'],
            'signature'         => ['required', 'string'],
        ]);

        $challenge = session('webauthn_login_challenge');
        abort_unless((bool) $challenge, 422, 'No login in progress.');

        $passkey = DB::table('user_passkeys')->where('credential_id', $data['id'])->first();
        if (! $passkey) {
            return response()->json(['message' => 'Unknown passkey.'], 422);
        }

        try {
            $webAuthn = $this->webAuthn($request);
            $webAuthn->processGet(
                base64_decode($data['clientDataJSON']),
                base64_decode($data['authenticatorData']),
                base64_decode($data['signature']),
                $passkey->public_key,
                new \lbuchs\WebAuthn\Binary\ByteBuffer($challenge),
                null,
                'required'
            );
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Passkey verification failed.'], 422);
        }

        session()->forget('webauthn_login_challenge');

        $user = DB::table('users')->where('id', $passkey->user_id)->whereNull('deleted_at')->first();
        if (! $user || ($user->status ?? 'active') === 'suspended') {
            return response()->json(['message' => 'Account unavailable.'], 403);
        }

        DB::table('user_passkeys')->where('id', $passkey->id)->update(['last_used_at' => now(), 'updated_at' => now()]);

        // Passkeys are inherently multi-factor (possession + biometric/PIN):
        // no additional 2FA challenge.
        establishSiacSession($user, $request);

        return response()->json(['redirect' => '/tableau-de-bord']);
    }
}
