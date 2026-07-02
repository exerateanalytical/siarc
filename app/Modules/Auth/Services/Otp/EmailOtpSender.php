<?php

namespace App\Modules\Auth\Services\Otp;

use Illuminate\Support\Facades\Mail;

class EmailOtpSender implements OtpSender
{
    public function send(string $destination, string $code, string $lang = 'fr'): void
    {
        $subject = $lang === 'fr'
            ? 'Votre code de vérification'
            : 'Your verification code';

        $body = $lang === 'fr'
            ? "Votre code de vérification est : {$code}\n\nIl expire dans 10 minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n— Galerie virtuelle de l'artisanat du Cameroun"
            : "Your verification code is: {$code}\n\nIt expires in 10 minutes. If you did not request this, ignore this message.\n\n— Galerie virtuelle de l'artisanat du Cameroun";

        Mail::raw($body, function ($mail) use ($destination, $subject) {
            $mail->to($destination)->subject($subject);
        });
    }
}
