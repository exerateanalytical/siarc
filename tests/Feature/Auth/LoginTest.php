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
            'password' => 'Password1!',
            'status'   => 'active',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.cm',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['id', 'email'], 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'test@example.cm', 'password' => 'Password1!']);

        // Failed credentials surface as a validation error (422), never a token
        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'wrong'])
             ->assertStatus(422)
             ->assertJsonMissingPath('token');
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->suspended()->create(['email' => 'test@example.cm', 'password' => 'Password1!']);

        $this->postJson('/api/v1/auth/login', ['email' => 'test@example.cm', 'password' => 'Password1!'])
             ->assertStatus(422)
             ->assertJsonMissingPath('token');
    }

    public function test_login_can_use_phone(): void
    {
        User::factory()->create(['phone' => '+237699000001', 'password' => 'Password1!']);

        $this->postJson('/api/v1/auth/login', ['phone' => '+237699000001', 'password' => 'Password1!'])
             ->assertStatus(200)
             ->assertJsonStructure(['data', 'token']);
    }
}
