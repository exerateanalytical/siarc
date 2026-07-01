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

        $otp = $this->otpService->generate(
            $request->identifier,
            $request->type,
            $user?->id
        );

        // In production: dispatch email/SMS job. For now log.
        logger("OTP for {$request->identifier}: {$otp->code}");

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
