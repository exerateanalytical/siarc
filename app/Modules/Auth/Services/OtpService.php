<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\OtpVerification;
use App\Modules\Auth\Models\User;

class OtpService
{
    public function generate(string $identifier, string $type, ?string $userId = null): OtpVerification
    {
        // Invalidate any existing unexpired OTP for this identifier+type
        OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        return OtpVerification::create([
            'user_id'    => $userId,
            'identifier' => $identifier,
            'code'       => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type'       => $type,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function verify(string $identifier, string $code, string $type): bool
    {
        $otp = OtpVerification::where('identifier', $identifier)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return false;
        }

        $otp->increment('attempt_count');

        if ($otp->attempt_count > 5) {
            return false;
        }

        if ($otp->code !== $code) {
            return false;
        }

        $otp->update(['verified_at' => now()]);

        return true;
    }
}
