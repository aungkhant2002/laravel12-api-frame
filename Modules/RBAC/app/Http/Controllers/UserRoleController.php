<?php

namespace Modules\RBAC\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    public function assign(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name'
        ]);

        $user->syncRoles($data['roles']);

        return response()->json([
            'success' => true,
            'message' => 'Roles assigned successfully',
            'role' => $user->getRoleNames()
        ]);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'roles' => $user->getRoleNames()
        ]);
    }
}
