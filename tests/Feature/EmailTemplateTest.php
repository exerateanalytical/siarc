<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Mail\PlatformNotificationMail;
use App\Mail\VerificationCodeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the branded email templates.
 *
 * Every email except the password reset used to go out as Mail::raw — unstyled
 * plain text, including the verification code, which is the first thing a new
 * member ever receives from the platform. These assertions keep the branding,
 * the plain-text alternative and the mobile-safe width from quietly regressing.
 */
class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_code_email_is_branded_and_carries_the_code(): void
    {
        $html = (new VerificationCodeMail('482913', 'fr'))->render();

        $this->assertStringContainsString('482913', $html);
        $this->assertStringContainsString('Artisan Hub 237', $html);
        $this->assertStringContainsString('#02301B', $html, 'Heritage green is missing.');
        $this->assertStringContainsString('Confirmez votre adresse email', $html);
    }

    public function test_emails_shrink_on_a_phone(): void
    {
        // A fixed width:600px would overflow a 375px screen. The percentage plus
        // max-width is what actually constrains it; the width="600" attribute is
        // only there because Outlook ignores max-width.
        foreach ([new VerificationCodeMail('111111'), new PasswordResetMail('Jude', 'https://example.test/r/1')] as $mail) {
            $html = $mail->render();
            $this->assertStringContainsString('width:100%; max-width:600px', $html);
            $this->assertStringContainsString('width="600"', $html);
            $this->assertStringNotContainsString('style="width:600px', $html);
        }
    }

    public function test_every_member_facing_email_has_a_plain_text_alternative(): void
    {
        $mailables = [
            'verification' => new VerificationCodeMail('482913', 'fr'),
            'notification' => new PlatformNotificationMail('Sujet', 'Corps du message', 'https://example.test/x', 'Titre', 'fr'),
            'reset'        => new PasswordResetMail('Jude', 'https://example.test/reset/abc', 'fr'),
        ];

        foreach ($mailables as $name => $mailable) {
            $this->assertNotNull(
                $mailable->content()->text,
                "The {$name} email has no text part. Text-only clients would see an empty message, and a missing text part raises the spam score."
            );
        }
    }

    public function test_notification_email_renders_a_button_when_given_a_link(): void
    {
        $withLink = (new PlatformNotificationMail('S', 'Body', 'https://example.test/devis', 'Titre', 'fr'))->render();
        $this->assertStringContainsString('https://example.test/devis', $withLink);
        $this->assertStringContainsString('border-radius:8px', $withLink);

        // …and no empty button when there is nowhere to send them.
        $withoutLink = (new PlatformNotificationMail('S', 'Body', null, null, 'fr'))->render();
        $this->assertStringNotContainsString('border-radius:8px', $withoutLink);
    }

    public function test_emails_render_in_english_too(): void
    {
        $html = (new VerificationCodeMail('729140', 'en'))->render();

        $this->assertStringContainsString('Confirm your email address', $html);
        $this->assertStringContainsString('729140', $html);
        $this->assertStringNotContainsString('Confirmez', $html);
    }

    /**
     * Email clients strip <style> blocks and do not support flexbox or grid, so
     * the layout has to stay table-based with inline styles.
     */
    public function test_layout_avoids_css_email_clients_discard(): void
    {
        $html = (new VerificationCodeMail('482913'))->render();

        $this->assertStringNotContainsString('display:flex', $html);
        $this->assertStringNotContainsString('display:grid', $html);
        $this->assertStringContainsString('<table', $html);
    }
}
