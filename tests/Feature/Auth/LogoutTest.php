<?php

namespace Tests\Feature\Auth;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user  = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson('/api/v1/auth/logout')
             ->assertStatus(200)
             ->assertJson(['message' => 'Logged out.']);

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertStatus(401);
    }
}
