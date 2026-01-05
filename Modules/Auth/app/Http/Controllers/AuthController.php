<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\OTP\Services\OtpService;
use Modules\User\Transformers\UserResource;

class AuthController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|unique:users,phone|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => false,
        ]);

        // Send OTP (register)
        app(OtpService::class)->generate(
            phone: $validated['phone'],
            purpose: 'register',
            userId: $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your phone. Please verify.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login'    => 'required|string', // email OR phone
            'password' => 'required|string|min:6',
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
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('phone', $data['phone'])->firstOrFail();

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful',
        ]);
    }

}
