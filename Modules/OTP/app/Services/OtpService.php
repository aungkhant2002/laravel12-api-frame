<?php

namespace Modules\OTP\Services;

use Modules\OTP\Models\Otp;

class OtpService
{
    public function generate(string $target, string $type, string $purpose): string
    {
        $opt = random_int(100000, 999999);
        Otp::create([
            'target' => $target,
            'target_type' => $type,
            'purpose' => $purpose,
            'otp_hash' => password_hash($opt, PASSWORD_BCRYPT),
            'expires_at' => now()->addMinutes(10),
        ]);
    }
}
