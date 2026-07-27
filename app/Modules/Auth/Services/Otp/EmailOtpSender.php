<?php

namespace App\Modules\Auth\Services\Otp;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;

class EmailOtpSender implements OtpSender
{
    public function send(string $destination, string $code, string $lang = 'fr'): void
    {
        // This is the first email a new member ever receives from the platform,
        // so it carries the branding rather than arriving as raw text.
        Mail::to($destination)->send(new VerificationCodeMail($code, $lang));
    }
}
