<?php

namespace App\Modules\Auth\Services\Otp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Delivers OTP codes over WhatsApp via the Twilio Messages API.
 *
 * Requires TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN and TWILIO_WHATSAPP_FROM
 * in .env (see config/services.php). Until credentials are set, sends are
 * logged in non-production so the enrolment flow stays testable; in
 * production a missing configuration throws instead of failing silently.
 */
class TwilioWhatsAppOtpSender implements OtpSender
{
    /** Resolved credentials: admin-saved settings win, .env is the fallback. */
    public static function credentials(): array
    {
        return [
            'sid'   => \App\Modules\Admin\Services\SystemSettings::get('twilio.sid') ?: config('services.twilio.sid'),
            'token' => \App\Modules\Admin\Services\SystemSettings::get('twilio.token') ?: config('services.twilio.token'),
            'from'  => \App\Modules\Admin\Services\SystemSettings::get('twilio.whatsapp_from') ?: config('services.twilio.whatsapp_from'),
        ];
    }

    public function send(string $destination, string $code, string $lang = 'fr'): void
    {
        ['sid' => $sid, 'token' => $token, 'from' => $from] = static::credentials();

        if (! $sid || ! $token || ! $from) {
            if (app()->isProduction()) {
                throw new \RuntimeException('Twilio WhatsApp is not configured (TWILIO_ACCOUNT_SID / TWILIO_AUTH_TOKEN / TWILIO_WHATSAPP_FROM).');
            }
            // The plain code is only ever written to local/testing logs
            if (app()->environment('local', 'testing')) {
                Log::warning("[OTP:whatsapp] Twilio not configured — code {$code} for {$destination} (logged instead of sent)");
            } else {
                Log::warning("[OTP:whatsapp] Twilio not configured — OTP for {$destination} was NOT delivered");
            }
            return;
        }

        $body = $lang === 'fr'
            ? "Votre code de vérification est : {$code}. Il expire dans 10 minutes."
            : "Your verification code is: {$code}. It expires in 10 minutes.";

        Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => 'whatsapp:' . $from,
                'To'   => 'whatsapp:' . $destination,
                'Body' => $body,
            ])
            ->throw();
    }
}
