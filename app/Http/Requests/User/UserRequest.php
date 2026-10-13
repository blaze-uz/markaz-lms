<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    public function rules(): array
    {
        $email = '';
        $password = '';

        if ($this->getMethod() == 'POST') {
            $email = [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email'),
            ];
            $password = 'required|min:4';
        } elseif ($this->getMethod() == 'PUT') {

            $model = 'user';

            $id = is_numeric($this->$model) ? $this->$model : $this->$model->getAttribute('id');

            $email = [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ];
        }

        return [
            'name' => 'required',
            'phone' => 'required',
            'avatar' => 'sometimes',
            'birthday' => 'nullable|date|date_format:Y-m-d',
            'role' => 'required',
            'email' => $email,
            'password' => $password,
            'percentage' => 'sometimes',
            'stars' => 'nullable|integer|min:0',
            'branches' => 'required|array',
        ];
    }
}
