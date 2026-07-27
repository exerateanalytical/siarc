<?php

namespace App\Jobs;

use App\Mail\PlatformNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends a platform notification email off the request path.
 *
 * These are the "someone quoted your request" / "your order shipped" emails —
 * the recipient is not sitting waiting for them, but the *sender* was: every
 * one of these used to be a synchronous SMTP round trip inside a POST, so a
 * slow relay made accepting a quote feel broken.
 *
 * Deliberately safe with or without a worker. On QUEUE_CONNECTION=sync it runs
 * inline exactly as before; on `database` it needs `queue:work` (see DEPLOY.md).
 * Either way a transport failure is logged and swallowed rather than surfacing
 * as a 500 to a user whose action already succeeded.
 *
 * NOT used for verification codes — the member is waiting for those, so
 * OtpService still sends them inline.
 */
class SendNotificationEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Back off 1m, 5m, 15m — a relay that just rejected us needs a moment. */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $email,
        public string $subject,
        public string $body,
        public ?string $ctaUrl = null,
        public ?string $heading = null,
        public string $lang = 'fr',
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new PlatformNotificationMail(
            $this->subject,
            $this->body,
            $this->ctaUrl,
            $this->heading,
            $this->lang,
        ));
    }

    public function failed(\Throwable $e): void
    {
        // The in-app notification already reached the user; losing the email
        // copy must never break anything downstream.
        Log::warning('Notification email gave up after retries', [
            'to'      => $this->email,
            'subject' => $this->subject,
            'error'   => $e->getMessage(),
        ]);
    }
}
