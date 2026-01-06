<?php

namespace Modules\OTP\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\OTP\Models\PhonePasswordResetToken;
use Modules\OTP\Services\OtpService;
use Modules\OTP\Services\PhonePasswordResetService;

class OTPController extends Controller
{
    public function requestOtp(Request $request, OtpService $otpService)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'purpose' => 'required|in:register,forgot_password',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if ($data['purpose'] === 'register') {
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please register first.',
                ], 404);
            }

            if ($user->phone_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone already verified.',
                ], 422);
            }
        }

        if ($data['purpose'] === 'forgot_password' && !$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        try {
            $result = $otpService->generate(
                phone: $data['phone'],
                purpose: $data['purpose'],
                userId: $user?->id,
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent',
            'data' => $result,
        ]);
    }

    public function verifyOtp(Request $request, OtpService $otpService, PhonePasswordResetService $passwordResetService)
    {
        $data = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'purpose' => 'required|in:register,forgot_password',
            'otp' => 'required|digits:6',
        ]);

        try {
            $otpRecord = $otpService->verify(
                $data['phone'],
                $data['purpose'],
                $data['otp']
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 422);
        }

        if ($data['purpose'] === 'register') {
            $user = $otpRecord->user;

            $user->update([
                'phone_verified_at' => now(),
                'is_active' => true,
            ]);

            $user->refresh();

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('user');
            }

            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration completed',
                'token' => $token,
                'user' => $user,
            ]);
        }

        // forgot_password => return reset_token
        $user = $otpRecord->user;
        $reset = $passwordResetService->issue($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. You may reset password.',
            'data' => $reset,
        ]);
    }
}
