<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AdminUserController;
use Modules\User\Http\Controllers\MeController;
use Modules\User\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    // user self endpoints
    Route::get('me', [MeController::class, 'show']);
    Route::patch('me', [MeController::class, 'update']);

    Route::middleware(['permission:users.view'])
        ->get('/admin/users', [AdminUserController::class, 'index']);

    Route::middleware(['permission:users.view'])
        ->get('/admin/users/{user}', [AdminUserController::class, 'show']);

    Route::middleware(['permission:users.update'])
        ->patch('/admin/users/{user}', [AdminUserController::class, 'update']);

    Route::middleware(['permission:users.delete'])
        ->delete('/admin/users/{user}', [AdminUserController::class, 'destroy']);
});
