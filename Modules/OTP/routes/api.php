<?php

use Illuminate\Support\Facades\Route;
use Modules\OTP\Http\Controllers\OTPController;

Route::prefix('otp')->group(function () {
    Route::post('request', [OTPController::class, 'requestOtp']);
    Route::post('verify', [OTPController::class, 'verifyOtp']);
});
