<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Resources\UserResource;
use App\Modules\Auth\Services\AuthService;

class RegisterController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function __invoke(RegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        ['user' => $user, 'token' => $token] = $this->authService->register($request->validated());

        return response()->json([
            'data'    => new UserResource($user),
            'token'   => $token,
            'message' => $request->input('language_preference', 'fr') === 'fr'
                ? 'Compte créé avec succès.'
                : 'Account created successfully.',
        ], 201);
    }
}
