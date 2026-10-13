<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class UpdateProfileRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . auth()->id(),
            'phone' => 'sometimes',
            'birthday' => 'sometimes',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ];
    }
}
