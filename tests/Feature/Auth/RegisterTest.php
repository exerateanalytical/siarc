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
            'first_name'            => 'Jean',
            'last_name'             => 'Dupont',
            'email'                 => 'jean@example.cm',
            'phone'                 => '+237612345678',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
            'locale'                => 'fr',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'data' => ['user' => ['id','email','first_name'], 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'jean@example.cm']);
    }

    public function test_register_validates_duplicate_email(): void
    {
        User::factory()->create(['email' => 'jean@example.cm']);

        $response = $this->postJson('/api/v1/auth/register', [
            'first_name'            => 'Jean',
            'last_name'             => 'Dupont',
            'email'                 => 'jean@example.cm',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_requires_strong_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name'            => 'Jean',
            'last_name'             => 'Dupont',
            'email'                 => 'new@example.cm',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
    }
}
