<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email'    => 'test@example.cm',
            'password' => bcrypt('Password1!'),
            'status'   => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.cm',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.cm', 'password' => bcrypt('Password1!')]);

        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'wrong'])
             ->assertStatus(401);
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->create(['email' => 'test@example.cm', 'password' => bcrypt('Password1!'), 'status' => 'suspended']);

        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'Password1!'])
             ->assertStatus(403);
    }
}
