<?php

namespace Modules\RBAC\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function assign(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name'
        ]);

        $user->syncRoles($data['roles']);

        return ApiResponse::success(
            data: [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames(),
            ],
            message: 'Roles assigned successfully'
        );
    }

    public function show(User $user): JsonResponse
    {
        return ApiResponse::success(
            data: [
                'user_id' => $user->id,
                'roles' => $user->getRoleNames(),
            ],
            message: 'User roles fetched successfully'
        );
    }
}
