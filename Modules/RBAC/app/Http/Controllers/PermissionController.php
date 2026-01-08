<?php

namespace Modules\RBAC\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            data: Permission::query()->latest()->get(),
            message: 'Permissions fetched successfully.',
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $validated['name']]);
        return ApiResponse::success(
            data: $permission,
            message: 'Permission created successfully.',
            status: 201
        );
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return ApiResponse::error(
                code: 'PERMISSION_NOT_FOUND',
                message: 'Permission not found',
                status: 404
            );
        }

        return ApiResponse::success(
            data: $permission,
            message: 'Permission fetched successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return ApiResponse::error(
                code: 'PERMISSION_NOT_FOUND',
                message: 'Permission not found',
                status: 404
            );
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $validated['name']]);

        return ApiResponse::success(
            data: $permission->fresh(),
            message: 'Permission updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return ApiResponse::error(
                code: 'PERMISSION_NOT_FOUND',
                message: 'Permission not found',
                status: 404
            );
        }

        $permission->delete();

        return ApiResponse::success(
            data: (object)[],
            message: 'Permission deleted successfully'
        );
    }

}
