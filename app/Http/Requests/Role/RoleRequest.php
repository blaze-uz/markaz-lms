<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'permissions.*' => 'sometimes',
        ];
    }
}
