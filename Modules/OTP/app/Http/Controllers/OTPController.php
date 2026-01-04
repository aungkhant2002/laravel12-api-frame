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
            'purpose' => 'required|in:login,forgot_password',
        ]);

        $user = User::where('phone', $data['phone'])->first();
        if ($data['purpose'] == 'forgot_password' && ! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $code = $otpService->generate(
            $data['phone'],
            $data['purpose'],
            $user?->id
        );

        // Send sms here
        // SmsService::send($data['phone'], "Your OTP is $code");

        return response()->json([
            'success' => true,
            'message' => 'OTP sent'
        ]);
    }

    public function verifyOtp(Request $request, OtpService $otpService)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'purpose' => 'required|in:login,forgot_password',
            'otp' => 'required|digits:6',
        ]);

        $record = $otpService->verify($data['phone'], $data['purpose'], $data['otp']);

        $user = User::firstOrCreate(
            ['phone' => $data['phone']],
            ['name' => 'OTP User']
        );

        if ($data['purpose'] === 'forgot_password') {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. You may reset password.',
            ]);
        }

        // LOGIN
        $token = $user->createToken('otp_login')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);

    }
}
