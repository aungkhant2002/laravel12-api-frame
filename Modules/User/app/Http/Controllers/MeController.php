<?php

namespace Modules\User\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\UpdateMeRequest;
use Modules\User\Transformers\UserResource;

class MeController extends Controller
{
    public function show(): JsonResponse
    {
        $user = request()->user();
        return ApiResponse::success(
            data: new UserResource($user),
            message: "Profile fetched successfully.",
        );
    }

    public function update(UpdateMeRequest $request): JsonResponse
    {
        $user = request()->user();
        $user->update($request->validated());

        return ApiResponse::success(
            data: new UserResource($user->fresh()),
            message: "Profile updated successfully.",
        );
    }
}
