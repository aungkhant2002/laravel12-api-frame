<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AdminUserController;
use Modules\User\Http\Controllers\MeController;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    // user self endpoints
    Route::get('me', [MeController::class, 'show']);
    Route::patch('me', [MeController::class, 'update']);

    //admin endpoints
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('users', [AdminUserController::class, 'index']);
        Route::get('users/{user}', [AdminUserController::class, 'show']);
        Route::patch('users/{user}', [AdminUserController::class, 'update']);
        Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
    });
});
