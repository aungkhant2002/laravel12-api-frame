<?php

namespace Modules\Auth\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\OTP\Services\PhonePasswordResetService;
use Modules\User\Transformers\UserResource;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:50|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => false,
        ]);

        // Auto-send OTP for registration
        // $otpMeta = $otpService->generate($validated['phone'], 'register', $user->id);

        return ApiResponse::success(
            data: (object) [],
            message: "Registered. Please request OTP to verify your phone.",
            meta: [],
            status: 201
        );
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => 'required|string|max:50',
            'password' => 'required|string|min:6|max:30',
        ]);

        $user = User::where('email', $data['login'])
            ->orWhere('phone', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->phone && ! $user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your phone number first.',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => new UserResource($user),
                'token' => $token,
            ],
            message: "Login successfully.",
            status: 200
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(
            data: (object) [],
            message: "Logged out successfully.",
            status: 200
        );
    }

    public function resetPassword(Request $request, PhonePasswordResetService $passwordResetService): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('phone', $data['phone'])->firstOrFail();

        try {
            $passwordResetService->consume($user, $data['reset_token']);
        } catch (\Throwable $e) {
            return ApiResponse::error(
                code: 'INVALID_RESET_TOKEN',
                message: $e->getMessage(),
                details: (object) [],
                status: 422
            );
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        $user->tokens()->delete();

        return ApiResponse::success(
            data: (object) [],
            message: "Password reset successfully.",
            status: 200
        );
    }
}
