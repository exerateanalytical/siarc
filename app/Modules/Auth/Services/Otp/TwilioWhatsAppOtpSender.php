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
    public function send(string $destination, string $code, string $lang = 'fr'): void
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.whatsapp_from');

        if (! $sid || ! $token || ! $from) {
            if (app()->isProduction()) {
                throw new \RuntimeException('Twilio WhatsApp is not configured (TWILIO_ACCOUNT_SID / TWILIO_AUTH_TOKEN / TWILIO_WHATSAPP_FROM).');
            }
            Log::warning("[OTP:whatsapp] Twilio not configured — code {$code} for {$destination} (logged instead of sent)");
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
