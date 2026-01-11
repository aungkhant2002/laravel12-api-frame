<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardMetricsController extends Controller
{
    public function index() : JsonResponse
    {
        return ApiResponse::success(
            data: [
                'users_total' => User::count(),
                'booking_total' => 0,
            ],
            message: 'Dashboard Metrics fetched successfully.',
        );
    }
}
