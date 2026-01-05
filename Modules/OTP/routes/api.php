<?php

use Illuminate\Support\Facades\Route;
use Modules\OTP\Http\Controllers\OTPController;

Route::prefix('otp')->group(function () {
    Route::post('request', [OtpController::class, 'requestOtp']);
    Route::post('verify', [OtpController::class, 'verifyOtp']);
});
