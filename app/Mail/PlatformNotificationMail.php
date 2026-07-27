<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The courtesy copy of an in-app notification — "someone quoted your request",
 * "your order shipped", "you have a new message".
 *
 * The in-app record is the one the member relies on; this is the nudge that
 * brings them back. Dispatched from App\Jobs\SendNotificationEmail, which is
 * what puts it on the queue.
 */
class PlatformNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $body,
        public ?string $ctaUrl = null,
        public ?string $heading = null,
        public string $lang = 'fr',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            text: 'emails.text.notification',
            with: [
                'subject' => $this->subjectLine,
                'heading' => $this->heading,
                'body'    => $this->body,
                'ctaUrl'  => $this->ctaUrl,
                'lang'    => $this->lang,
            ],
        );
    }
}
