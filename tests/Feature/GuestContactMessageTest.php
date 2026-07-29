<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * A guest's contact message is written down before anyone is thanked for it.
 *
 * The defect: POST /contact mailed the message and swallowed the send failure,
 * then told the visitor it had been sent. Nothing was persisted. With
 * MAIL_MAILER=log — the fallback DEPLOY.md recommends if the relay does not
 * come up — every logged-out enquiry went to a log file while its sender was
 * told it had arrived.
 *
 * The mail-throws case is the one that matters, so it is the one that is
 * asserted hardest: it is precisely the state the site is expected to launch in.
 */
class GuestContactMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<string,string> */
    private array $payload = [
        'name'    => 'Awa Mbarga',
        'email'   => 'awa@example.cm',
        'subject' => 'Question sur une commande',
        'message' => 'Bonjour, je voudrais commander six masques en bois.',
        'consent' => '1',
        'lang'    => 'fr',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // The handler rate-limits on the client IP and every test here posts
        // from the same one.
        RateLimiter::clear('contact:127.0.0.1');
    }

    public function test_the_message_is_stored_even_when_the_mail_transport_throws(): void
    {
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('Connection to mail.artisanhub237.com refused'));

        $this->post('/contact', $this->payload)->assertRedirect();

        $row = DB::table('contact_messages')->latest('id')->first();

        $this->assertNotNull($row, 'A guest enquiry was lost because the mail relay was down.');
        $this->assertSame($this->payload['email'], $row->email);
        $this->assertSame($this->payload['subject'], $row->subject);
        $this->assertSame($this->payload['message'], $row->message);
        $this->assertNull($row->mailed_at, 'mailed_at must stay null when the send failed.');
        $this->assertStringContainsString('refused', (string) $row->mail_error);
    }

    public function test_the_visitor_is_still_thanked_when_only_the_mail_failed(): void
    {
        // True, now: the message really is saved, so the receipt is honest.
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('relay down'));

        $this->post('/contact', $this->payload)
            ->assertRedirect(route('contact', ['lang' => 'fr']))
            ->assertSessionHas('success');
    }

    public function test_a_successful_send_is_recorded_against_the_row(): void
    {
        Mail::fake();

        $this->post('/contact', $this->payload)->assertRedirect();

        $row = DB::table('contact_messages')->latest('id')->first();

        $this->assertNotNull($row);
        $this->assertNotNull($row->mailed_at);
        $this->assertNull($row->mail_error);
        $this->assertSame('new', $row->status);
    }

    public function test_consent_is_still_required_and_nothing_is_stored_without_it(): void
    {
        Mail::fake();

        $this->post('/contact', array_merge($this->payload, ['consent' => null]))
            ->assertSessionHasErrors('consent');

        $this->assertSame(0, DB::table('contact_messages')->count());
    }

    public function test_validation_still_rejects_a_malformed_submission(): void
    {
        Mail::fake();

        $this->post('/contact', array_merge($this->payload, ['email' => 'not-an-email', 'message' => '']))
            ->assertSessionHasErrors(['email', 'message']);

        $this->assertSame(0, DB::table('contact_messages')->count());
    }

    public function test_the_visitor_is_told_when_the_message_could_not_be_stored(): void
    {
        // The whole point of the change: never claim delivery of something that
        // was not written down. Dropping the table makes the insert fail for a
        // real reason rather than a mocked one.
        \Illuminate\Support\Facades\Schema::drop('contact_messages');

        $this->post('/contact', $this->payload)
            ->assertSessionHasErrors('message')
            ->assertSessionMissing('success');
    }
}
