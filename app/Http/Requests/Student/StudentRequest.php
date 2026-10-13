<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;

class StudentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'surname' => 'required',
            'phone' => 'required',
            'phone2' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'parent_phone' => 'sometimes',
            'gender' => 'required',
            'birthday' => 'nullable|date|date_format:Y-m-d',
            'comment' => 'sometimes',
            'attached_at.*' => 'nullable|date|date_format:Y-m-d',
        ];
    }
}
