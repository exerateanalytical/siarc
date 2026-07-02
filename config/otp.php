<?php

use App\Modules\Auth\Services\Otp\EmailOtpSender;
use App\Modules\Auth\Services\Otp\LogOtpSender;

return [

    /*
    |--------------------------------------------------------------------------
    | OTP delivery channels
    |--------------------------------------------------------------------------
    | Map each channel to a class implementing
    | App\Modules\Auth\Services\Otp\OtpSender.
    |
    | SMS and WhatsApp currently use the log stub — when a provider is
    | chosen (Twilio, Vonage, Infobip, Meta WhatsApp Cloud API, ...),
    | implement OtpSender for it and swap the class here. Nothing else
    | in the platform needs to change.
    */

    'senders' => [
        'email'    => EmailOtpSender::class,
        'sms'      => LogOtpSender::class,
        'whatsapp' => LogOtpSender::class,
    ],

    // Channels users may enrol as their login second factor.
    'enabled_channels' => ['email', 'sms', 'whatsapp'],
];
