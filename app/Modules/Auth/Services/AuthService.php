<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'password'            => $data['password'],
            'language_preference' => $data['language_preference'] ?? 'fr',
        ]);

        $user->assignRole('buyer');

        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }

    public function login(array $credentials): array
    {
        $identifier = $credentials['email'] ?? $credentials['phone'];
        $field      = isset($credentials['email']) ? 'email' : 'phone';

        $user = User::where($field, $identifier)->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => [__('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'credentials' => [__('auth.suspended')],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        // Revoke all old tokens and issue a fresh one
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return compact('user', 'token');
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
