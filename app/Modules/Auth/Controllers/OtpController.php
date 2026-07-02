<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\OtpService;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function send(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'type'       => ['required', 'in:email_verification,phone_verification,password_reset'],
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        $channel = str_contains($request->identifier, '@') ? 'email' : 'sms';

        $sent = $this->otpService->send(
            $request->identifier,
            $request->type,
            $channel,
            $user?->id
        );

        if (! $sent) {
            return response()->json(['message' => 'Too many codes requested. Try again later.'], 429);
        }

        return response()->json(['message' => 'OTP sent.']);
    }

    public function verify(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'code'       => ['required', 'string', 'size:6'],
            'type'       => ['required', 'in:email_verification,phone_verification,password_reset,login'],
        ]);

        $verified = $this->otpService->verify(
            $request->identifier,
            $request->code,
            $request->type
        );

        if (! $verified) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        // Mark email/phone as verified on user
        if (in_array($request->type, ['email_verification', 'phone_verification'])) {
            $field = $request->type === 'email_verification' ? 'email' : 'phone';
            User::where($field, $request->identifier)
                ->update(["is_{$field}_verified" => true]);
        }

        return response()->json(['message' => 'Verified successfully.']);
    }
}
