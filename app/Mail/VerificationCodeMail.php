<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one-time code that confirms a new member's email address.
 *
 * Deliberately NOT ShouldQueue: the member is staring at the code entry screen
 * waiting for this. Everything else the platform sends goes through
 * App\Jobs\SendNotificationEmail instead.
 */
class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $lang = 'fr',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->lang === 'fr'
                ? 'Votre code de vérification — Artisan Hub 237'
                : 'Your verification code — Artisan Hub 237',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            // Plain-text alternative. Some members read mail in clients that
            // show text only, and a text part also keeps spam scores down.
            text: 'emails.text.verification-code',
            with: [
                'code'    => $this->code,
                'lang'    => $this->lang,
                'subject' => $this->envelope()->subject,
            ],
        );
    }
}
