<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Password reset link email (bilingual).
 *
 * Deliberately not ShouldQueue yet: QUEUE_CONNECTION=database with no
 * worker process would strand reset emails. Add the interface once a
 * queue worker runs in every environment.
 */
class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $resetUrl,
        public string $lang = 'fr',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->lang === 'fr'
                ? 'Réinitialisez votre mot de passe — Galerie virtuelle de l\'artisanat du Cameroun'
                : 'Reset your password — Virtual gallery of Cameroonian crafts',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.password-reset',
        );
    }
}
