<?php

namespace Modules\RBAC\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        return response()->json(Role::all(), 200);
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
        return response()->json([$role, 201]);
    }

    /**
     * Show the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            return response()->json([$role, 200]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'The role not found'
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);
        $role->update(['name' => $validated['name']]);

        return response()->json([$role, 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ], 200);
    }
}
