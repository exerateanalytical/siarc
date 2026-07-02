<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\OtpVerification;
use App\Modules\Auth\Services\Otp\OtpSender;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    private const MAX_ATTEMPTS  = 5;
    private const TTL_MINUTES   = 10;
    private const SENDS_PER_10M = 3;

    /**
     * Generate a code, store its hash, and dispatch it over the channel.
     * Returns false when the per-identifier send limit is hit.
     *
     * @param string $channel email | whatsapp
     */
    public function send(string $identifier, string $type, string $channel, ?string $userId = null, string $lang = 'fr'): bool
    {
        $limiterKey = 'otp-send:' . sha1($identifier . '|' . $type);
        if (RateLimiter::tooManyAttempts($limiterKey, self::SENDS_PER_10M)) {
            return false;
        }
        RateLimiter::hit($limiterKey, 600);

        $code = $this->generate($identifier, $type, $userId, $channel);
        $this->sender($channel)->send($identifier, $code, $lang);

        return true;
    }

    /**
     * Create the OTP row (hashed) and return the PLAIN code — callers
     * that need custom delivery use this; the plain code is never stored.
     */
    public function generate(string $identifier, string $type, ?string $userId = null, ?string $channel = null): string
    {
        OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'user_id'    => $userId,
            'identifier' => $identifier,
            'code'       => hash('sha256', $code),
            'type'       => $type,
            'channel'    => $channel,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        return $code;
    }

    public function verify(string $identifier, string $code, string $type): bool
    {
        $otp = OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired() || $otp->attempt_count >= self::MAX_ATTEMPTS) {
            return false;
        }

        $code = preg_replace('/\D/', '', $code);
        if (! hash_equals($otp->code, hash('sha256', $code))) {
            $otp->increment('attempt_count');
            return false;
        }

        $otp->update(['verified_at' => now()]);

        return true;
    }

    public function sender(string $channel): OtpSender
    {
        $class = config("otp.senders.{$channel}");
        abort_unless($class && class_exists($class), 500, "No OTP sender configured for channel [{$channel}]");

        return app($class);
    }
}
