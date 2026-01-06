<?php

namespace Modules\OTP\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\OTP\Models\PhonePasswordResetToken;

class PhonePasswordResetService
{
    private int $expiresSeconds = 600; // 10 minutes

    public function issue(User $user): array
    {
        // Remove old tokens for this user
        PhonePasswordResetToken::where('user_id', $user->id)->delete();

        $raw = Str::random(64);

        PhonePasswordResetToken::create([
            'user_id' => $user->id,
            'token_hash' => Hash::make($raw),
            'expires_at' => now()->addSeconds($this->expiresSeconds),
        ]);

        return [
            'reset_token' => $raw,
            'expires_in' => $this->expiresSeconds,
        ];
    }

    public function consume(User $user, string $rawToken): void
    {
        $row = PhonePasswordResetToken::where('user_id', $user->id)
            ->valid()
            ->latest()
            ->first();

        if (! $row || ! Hash::check($rawToken, $row->token_hash)) {
            throw new \Exception('Invalid or expired reset token.');
        }

        $row->markUsed();
    }
}
