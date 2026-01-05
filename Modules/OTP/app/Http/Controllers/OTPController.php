<?php

namespace Modules\OTP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\OTP\Models\Otp;
use Modules\OTP\Services\OtpService;

class OTPController extends Controller
{
    public function requestOtp(Request $request, OtpService $otpService)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'purpose' => 'required|in:register,forgot_password',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if ($data['purpose'] === 'register' && $user) {
            return response()->json([
                'message' => 'Phone number already registered',
            ], 422);
        }

        if ($data['purpose'] === 'forgot_password' && !$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $otpCode = $otpService->generate(
            phone: $data['phone'],
            purpose: $data['purpose'],
            userId: $user?->id,
        );

        // TODO: send SMS here
//         SmsService::send($data['phone'], "Your OTP is $otpCode");

        return response()->json([
            'success' => true,
            'otp' => $otpCode,
        ]);
    }

    public function verifyOtp(Request $request, OtpService $otpService)
    {
        $data = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'purpose' => 'required|in:register,forgot_password',
            'otp' => 'required|digits:6',
        ]);

        $otpRecord = $otpService->verify(
            $data['phone'],
            $data['purpose'],
            $data['otp']
        );

        if ($data['purpose'] === 'register') {

            $user = $otpRecord->user;

            $user->update([
                'phone_verified_at' => now(),
                'is_active' => true,
            ]);

            $user->refresh();

            $user->assignRole('user');

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration completed',
                'token' => $token,
                'user' => $user,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. You may reset password.',
        ]);
    }

}
