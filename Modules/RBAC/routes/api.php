<?php

use Illuminate\Support\Facades\Route;
use Modules\RBAC\Http\Controllers\PermissionController;
use Modules\RBAC\Http\Controllers\RoleController;
use Modules\RBAC\Http\Controllers\RolePermissionController;
use Modules\RBAC\Http\Controllers\UserRoleController;

Route::middleware(['auth:sanctum', 'role:Super Admin|Court Manager'])->prefix('rbac')->group(function () {
    // Assign Role to user
    Route::post('users/{user}/roles', [UserRoleController::class, 'assign']);
    Route::get('users/{user}/roles', [UserRoleController::class, 'show']);

    // Assign Permission to role
    Route::post('roles/{role}/permissions', [RolePermissionController::class, 'assign']);
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'show']);

    Route::apiResource('roles', RoleController::class)->names('roles');
    Route::apiResource('permissions', PermissionController::class)->names('permissions');
});
