<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsGalleryData;
use Tests\TestCase;

/**
 * The platform must never make a member retype what it already knows.
 *
 * Signup collects name, email and phone; the downstream forms (business
 * creation, contact, 2FA phone) start from the stored values — editable,
 * and always losing to old() so a validation round-trip re-shows what the
 * member actually typed, never the stored value.
 */
class FormPrefillTest extends TestCase
{
    use BuildsGalleryData, RefreshDatabase;

    private function asWebUser(User $user): static
    {
        return $this->withSession(['siac_user' => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'role'     => 'business_owner',
            'is_admin' => false,
        ]]);
    }

    // ── Business creation ────────────────────────────────────────

    public function test_business_create_form_prefills_owner_phone_and_email(): void
    {
        $user = $this->makeUser([
            'name'  => 'Aristide Ndop',
            'email' => 'aristide.prefill@example.cm',
            'phone' => '+237 690 000 001',
        ]);

        $html = $this->asWebUser($user)->get('/tableau-de-bord/entreprise/creer')
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="+237 690 000 001"', $html);
        $this->assertStringContainsString('value="aristide.prefill@example.cm"', $html);
    }

    public function test_old_input_beats_the_stored_value_after_a_failed_validation(): void
    {
        $user = $this->makeUser([
            'email' => 'stored.email@example.cm',
            'phone' => '+237 690 000 001',
        ]);

        $html = $this->asWebUser($user)
            ->withSession(['_old_input' => ['phone' => '+237 655 555 555', 'email' => 'typed@example.cm']])
            ->get('/tableau-de-bord/entreprise/creer')
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="+237 655 555 555"', $html);
        $this->assertStringContainsString('value="typed@example.cm"', $html);
        $this->assertStringNotContainsString('value="+237 690 000 001"', $html);
        $this->assertStringNotContainsString('value="stored.email@example.cm"', $html);
    }

    // ── Contact page ─────────────────────────────────────────────

    public function test_contact_form_prefills_for_a_logged_in_member(): void
    {
        $user = $this->makeUser([
            'name'  => 'Aristide Ndop',
            'email' => 'aristide.contact@example.cm',
        ]);

        $html = $this->asWebUser($user)->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('value="Aristide Ndop"', $html);
        $this->assertStringContainsString('value="aristide.contact@example.cm"', $html);
    }

    public function test_contact_form_stays_empty_for_a_guest(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('id="contact-name" name="name" type="text" required value=""', $html);
        $this->assertStringContainsString('id="contact-email" name="email" type="email" required value=""', $html);
    }

    // ── 2FA phone (security page) ────────────────────────────────

    public function test_security_page_keeps_the_typed_phone_after_a_failed_validation(): void
    {
        $user = $this->makeUser(['phone' => '+237690000001']);

        $html = $this->asWebUser($user)
            ->withSession(['_old_input' => ['phone' => '+237655555555']])
            ->get('/tableau-de-bord/securite')
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="+237655555555"', $html);
    }
}
