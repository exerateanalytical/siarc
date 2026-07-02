<?php

namespace App\Modules\Auth\Services\Otp;

/**
 * Contract for OTP delivery channels. To integrate a real provider
 * (Twilio, Vonage, Infobip, Meta WhatsApp Cloud API, ...), implement
 * this interface and bind it in config/otp.php — nothing else changes.
 */
interface OtpSender
{
    /**
     * @param string $destination E.164 phone number or email address
     * @param string $code        The one-time code (plain, 6 digits)
     * @param string $lang        fr | en
     */
    public function send(string $destination, string $code, string $lang = 'fr'): void;
}
