<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class PasswordRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'password' => 'required|min:8',
        ];
    }
}
