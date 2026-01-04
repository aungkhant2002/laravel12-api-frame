<?php

namespace Modules\OTP\Services;

use Illuminate\Support\Facades\Hash;
use Modules\OTP\Models\Otp;

class OtpService
{
    public function generate(string $phone, string $purpose, ?int $userId = null): string
    {
        $otp = random_int(100000, 999999);
        Otp::create([
            'user_id' => $userId ?? auth()->id(),
            'target' => $phone,
            'purpose' => $purpose,
            'otp' => Hash::make($otp),
            'expired_at' => now()->addMinutes(3),
        ]);

        return (string) $otp;
    }

    public function verify(string $phone, string $purpose, string $otp): bool
    {
        $record = Otp::where('target', $phone)
            ->where('purpose', $purpose)
            ->valid()
            ->latest()
            ->first();

        if (! $record || ! Hash::check($otp, $record->otp)) {
            throw new \Exception('Invalid or expired OTP');
        }

        $record->markAsVerified();

        return $record;
    }
}
