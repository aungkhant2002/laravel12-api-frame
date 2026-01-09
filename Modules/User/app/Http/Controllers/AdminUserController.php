<?php

namespace Modules\User\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\AdminUpdateUserRequest;
use Modules\User\Transformers\UserResource;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $query = User::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string)$request->get('q'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })->orderByDesc('id');

        $users = $query->paginate($perPage);

        return ApiResponse::success(
            data: UserResource::collection($users),
            message: "Users fetched successfully.",
            meta: [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ]
            ]
        );
    }

    public function show(User $user): JsonResponse
    {
        return ApiResponse::success(
            data: new UserResource($user),
            message: "User fetched successfully.",
        );
    }

    public function update(AdminUpdateUserRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        $user->fill($validated);
        $user->save();

        if (is_array($roles)) {
            $user->syncRoles($roles);
        }

        return ApiResponse::success(
            data: new UserResource($user->fresh()),
            message: "User updated successfully.",
        );
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return ApiResponse::error(
                code: 'CANNOT_DELETE_SELF',
                message: 'You cannot delete your own account.',
                status: 422
            );
        }

        $user->delete();

        return ApiResponse::success(
            data: (object)[],
            message: "User deleted successfully.",
        );
    }
}
