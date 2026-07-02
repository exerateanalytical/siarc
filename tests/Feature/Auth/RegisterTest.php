<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jean Dupont',
            'email'                 => 'jean@example.cm',
            'phone'                 => '+237612345678',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'language_preference'   => 'fr',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['data' => ['id', 'email'], 'token', 'message']);

        $this->assertDatabaseHas('users', ['email' => 'jean@example.cm']);
        $this->assertTrue(User::where('email', 'jean@example.cm')->first()->hasRole('buyer'));
    }

    public function test_register_validates_duplicate_email(): void
    {
        User::factory()->create(['email' => 'jean@example.cm']);

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jean Dupont',
            'email'                 => 'jean@example.cm',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_register_requires_strong_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jean Dupont',
            'email'                 => 'new@example.cm',
            'password'              => '123',
            'password_confirmation' => '123',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    public function test_register_requires_email_or_phone(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Jean Dupont',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])->assertStatus(422);
    }
}
