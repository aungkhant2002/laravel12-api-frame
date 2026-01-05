<?php

namespace Modules\OTP\Services;

use App\Models\User;
use Illuminate\Http\Request;
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
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(3),
        ]);

        //  DEBUG OTP
        if (app()->environment('local')) {
            \Log::info("OTP for {$phone}: {$otp}");
        }

        return (string)$otp;
    }

    public function verify(string $phone, string $purpose, string $otp)
    {
        $record = Otp::where('target', $phone)
            ->where('purpose', $purpose)
            ->valid()
            ->latest()
            ->first();

        if (! $record) {
            throw new \Exception('Invalid or expired OTP');
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            throw new \Exception('Invalid or expired OTP');
        }

        $record->markAsVerified();

        return $record;
    }


}
