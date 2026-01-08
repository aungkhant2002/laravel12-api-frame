<?php

namespace Modules\OTP\Services;

use Illuminate\Support\Facades\Hash;
use Modules\OTP\Exceptions\OtpCooldownException;
use Modules\OTP\Exceptions\OtpDailyLimitException;
use Modules\OTP\Exceptions\OtpInvalidException;
use Modules\OTP\Models\Otp;
use Modules\OTP\Models\OtpRequest;

class OtpService
{
    private int $expiresSeconds;
    private int $cooldownSeconds;
    private int $maxPerDay;

    public function __construct()
    {
        $this->expiresSeconds = (int)config('otp.expires_seconds');
        $this->cooldownSeconds = (int)config('otp.cooldown_seconds');
        $this->maxPerDay = (int)config('otp.max_per_day');
    }

    public function generate(string $phone, string $purpose, ?int $userId = null): array
    {
        // (1) Daily limit
        $todayCount = OtpRequest::where('target', $phone)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayCount >= $this->maxPerDay) {
            throw new OtpDailyLimitException('OTP daily limit exceeded. Please try again tomorrow.');
        }

        // (2) Cooldown
        $lastRequest = OtpRequest::where('target', $phone)
            ->where('purpose', $purpose)
            ->latest('created_at')
            ->first();

        if ($lastRequest) {
            $nextAllowedAt = $lastRequest->created_at->copy()->addSeconds($this->cooldownSeconds);

            if (now()->lt($nextAllowedAt)) {
                $remaining = (int)now()->diffInSeconds($nextAllowedAt);
                $remaining = min($remaining, $this->cooldownSeconds);

                throw new OtpCooldownException($remaining);
            }
        }

        // (3) Find active OTP row (same phone+purpose, not verified)
        $otpRow = Otp::where('target', $phone)
            ->where('purpose', $purpose)
            ->active()
            ->latest()
            ->first();

        $otpCode = (string)random_int(100000, 999999);

        $payload = [
            'user_id' => $userId ?? auth()->id(),
            'otp_hash' => Hash::make($otpCode),
            'expires_at' => now()->addSeconds($this->expiresSeconds),
            'last_sent_at' => now(),
        ];

        if ($otpRow) {
            $otpRow->update([
                ...$payload,
                'attempts' => min(255, ($otpRow->attempts ?? 0) + 1),
            ]);
        } else {
            $otpRow = Otp::create([
                'target' => $phone,
                'purpose' => $purpose,
                ...$payload,
                'attempts' => 1,
            ]);
        }

        // (4) Log request
        OtpRequest::create([
            'target' => $phone,
            'purpose' => $purpose,
        ]);

        // (5) Send SMS (queue)
//        $message = "Your OTP is {$otpCode}. It expires in 5 minutes.";
//        SendSms::dispatch($phone, $message)->afterCommit();

        // (6) Dev-only logging
        if (app()->environment('local')) {
            \Log::info("OTP for {$phone} ({$purpose}): {$otpCode}");
        }

        return [
            'otp' => app()->environment('local') ? $otpCode : null,
            'expires_in' => $this->expiresSeconds,
            'cooldown_in' => $this->cooldownSeconds,
            'remaining_today' => max(0, $this->maxPerDay - ($todayCount + 1)),
        ];
    }

    public function verify(string $phone, string $purpose, string $otp): Otp
    {
        $record = Otp::where('target', $phone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->valid()
            ->latest()
            ->first();

        if (!$record || !Hash::check($otp, $record->otp_hash)) {
            throw new OtpInvalidException('Invalid or expired OTP');
        }

        $record->markAsVerified();

        return $record;
    }
}
