<?php

namespace Modules\RBAC\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function assign(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned to role successfully!',
            'permissions' => $role->getPermissionNames()
        ]);
    }

    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'permissions' => $role->getAllPermissions()->pluck('name')
        ]);
    }
}

