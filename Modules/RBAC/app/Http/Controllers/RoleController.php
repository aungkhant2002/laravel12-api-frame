<?php

namespace Modules\RBAC\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            data: Role::query()->latest()->get(),
            message: 'Roles fetched successfully.',
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        return ApiResponse::success(
            data: $role,
            message: 'Role created successfully.',
            status: 201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return ApiResponse::error(
                code: 'ROLE_NOT_FOUND',
                message: 'The role not found.',
                status: 404
            );
        }

        return ApiResponse::success(
            data: $role,
            message: 'Role fetched successfully.',
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return ApiResponse::error(
                code: 'ROLE_NOT_FOUND',
                message: 'The role not found.',
                status: 404
            );
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $validated['name']]);

        return ApiResponse::success(
            data: $role->fresh(),
            message: 'Role updated successfully.',
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return ApiResponse::error(
                code: 'ROLE_NOT_FOUND',
                message: 'The role not found.',
                status: 404
            );
        }
        $role->delete();

        return ApiResponse::success(
            data: (object)[],
            message: 'Role deleted successfully.',
        );
    }
}
