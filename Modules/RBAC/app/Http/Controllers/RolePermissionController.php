<?php

namespace Modules\RBAC\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function assign(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $role->syncPermissions($data['permissions']);

        return ApiResponse::success(
            data: [
                'role' => $role->name,
                'permissions' => $role->getPermissionNames(),
            ],
            message: 'Permissions assigned to role successfully!'
        );
    }

    public function show(Role $role): JsonResponse
    {
        return ApiResponse::success(
            data: [
                'role' => $role->name,
                'permissions' => $role->getAllPermissions()->pluck('name'),
            ],
            message: 'Role permissions fetched successfully'
        );
    }
}

