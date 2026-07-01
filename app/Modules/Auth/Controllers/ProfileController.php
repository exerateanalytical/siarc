<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Resources\UserResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => new UserResource($request->user()->load('business'))]);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name'                => ['sometimes', 'string', 'max:100'],
            'language_preference' => ['sometimes', 'in:fr,en'],
        ]);

        $request->user()->update($data);

        return response()->json(['data' => new UserResource($request->user()->fresh())]);
    }

    public function changePassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        if (! \Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => $request->password]);
        $request->user()->tokens()->delete();

        $token = $request->user()->createToken('api')->plainTextToken;

        return response()->json(['message' => 'Password updated.', 'token' => $token]);
    }
}
