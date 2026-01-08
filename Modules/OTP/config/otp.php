<?php

return [
    'expires_seconds' => env('OTP_EXPIRES_SECONDS', 30),
    'cooldown_seconds' => env('OTP_COOLDOWN_SECONDS', 60),
    'max_per_day' => env('OTP_MAX_PER_DAY', 5),
];
