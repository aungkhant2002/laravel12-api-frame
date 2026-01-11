<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\DashboardController;
use Modules\Dashboard\Http\Controllers\DashboardMetricsController;

Route::middleware(['auth:sanctum', 'role:admin|coach'])
    ->prefix('admin')
    ->group(function () {
        Route::get('dashboard/metrics', [DashboardMetricsController::class, 'index']);
    });
