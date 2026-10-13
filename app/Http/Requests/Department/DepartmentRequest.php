<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\BaseRequest;

class DepartmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ];
    }
}
