<?php

use App\Modules\Auth\Services\Otp\EmailOtpSender;
use App\Modules\Auth\Services\Otp\TwilioWhatsAppOtpSender;

return [

    /*
    |--------------------------------------------------------------------------
    | OTP delivery channels
    |--------------------------------------------------------------------------
    | Map each channel to a class implementing
    | App\Modules\Auth\Services\Otp\OtpSender.
    |
    | WhatsApp goes through Twilio (credentials in config/services.php).
    */

    'senders' => [
        'email'    => EmailOtpSender::class,
        'whatsapp' => TwilioWhatsAppOtpSender::class,
    ],

    // Channels users may enrol as their login second factor.
    'enabled_channels' => ['email', 'whatsapp'],
];
