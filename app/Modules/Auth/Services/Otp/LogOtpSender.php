<?php

namespace App\Modules\Auth\Services\Otp;

use Illuminate\Support\Facades\Log;

/**
 * Placeholder sender used until an SMS/WhatsApp provider is chosen.
 * Writes the code to laravel.log so the flow can be exercised end to
 * end in development. Swap for a real OtpSender in config/otp.php.
 */
class LogOtpSender implements OtpSender
{
    public function __construct(private readonly string $channel) {}

    public function send(string $destination, string $code, string $lang = 'fr'): void
    {
        Log::info("[OTP:{$this->channel}] code {$code} for {$destination} (provider not configured — stub sender)");
    }
}
