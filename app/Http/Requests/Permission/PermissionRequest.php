<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}
