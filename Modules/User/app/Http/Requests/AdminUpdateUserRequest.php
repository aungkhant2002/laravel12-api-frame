<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user?->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . $userId,
            'phone' => 'sometimes|required|string|max:20|unique:users,phone,' . $userId,

            'is_active' => 'sometimes|required|boolean',
            'phone_verified_at' => 'sometimes|nullable|date',

            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
