<?php

use Illuminate\Support\Facades\Route;
use Modules\RBAC\Http\Controllers\PermissionController;
use Modules\RBAC\Http\Controllers\RoleController;

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('rbac')->group(function () {
    Route::apiResource('roles', RoleController::class)->names('roles');
    Route::apiResource('permissions', PermissionController::class)->names('permissions')->only(['index', 'store']);
});
