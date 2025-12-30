<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->prefix('users')->group(function () {
    Route::get('/user', [UserController::class, 'user'])->name('user');
});
