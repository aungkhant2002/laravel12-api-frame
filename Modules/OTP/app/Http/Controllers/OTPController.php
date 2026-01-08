<?php

namespace Modules\OTP\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\OTP\Services\OtpService;
use Modules\OTP\Services\PhonePasswordResetService;
use Modules\User\Transformers\UserResource;

class OTPController extends Controller
{
    public function requestOtp(Request $request, OtpService $otpService): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'purpose' => 'required|in:register,forgot_password',
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if ($data['purpose'] === 'register') {
            if (!$user) {
                return ApiResponse::error(
                    code: 'USER_NOT_FOUND',
                    message: 'User not found. Please register first.',
                    status: 404
                );
            }

            if ($user->phone_verified_at) {
                return ApiResponse::error(
                    code: 'PHONE_ALREADY_REGISTERED',
                    message: 'Phone already verified.',
                    status: 422
                );
            }
        }

        if ($data['purpose'] === 'forgot_password' && !$user) {
            return ApiResponse::error(
                code: 'USER_NOT_FOUND',
                message: 'User not found.',
                status: 404
            );
        }

        try {
            $result = $otpService->generate(
                phone: $data['phone'],
                purpose: $data['purpose'],
                userId: $user?->id,
            );
        } catch (\Throwable $e) {
            return ApiResponse::error(
                code: 'OTP_RATE_LIMIT',
                message: $e->getMessage(),
                status: 429
            );
        }

        return ApiResponse::success(
            data: $result,
            message: 'OTP has been sent to your registered phone number.',
            status: 200
        );
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
        } catch (\Throwable $e) {
            return ApiResponse::error(
                code: 'INVALID_OTP',
                message: 'Invalid or expired OTP',
                status: 422
            );
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

            return ApiResponse::success(
                data: [
                    'token' => $token,
                    'user' => new UserResource($user),
                ],
                message: 'Registration completed',
                status: 200
            );
        }

        // forgot_password => return reset_token
        $user = $otpRecord->user;
        $reset = $passwordResetService->issue($user);

        return ApiResponse::success(
            data: $reset,
            message: 'OTP verified. You may reset password.',
            status: 200
        );
    }
}
