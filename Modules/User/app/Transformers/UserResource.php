<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => ucfirst($this->name),
            'email' => $this->email,
            'phone' => $this->phone,

            'roles' => method_exists($this->resource, 'getRoleNames') ? $this->getRoleNames()->values() : [],
            'permissions' => method_exists($this->resource, 'getPermissionNames') ? $this->getAllPermissions()->pluck('name')->values() : [],
        ];
    }
}
