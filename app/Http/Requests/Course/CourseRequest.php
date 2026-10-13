<?php

namespace App\Http\Requests\Course;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'nullable',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'branches' => 'required|array|min:1',
            'branches.*' => 'exists:branches,id',
        ];
    }

    public function messages(): array
    {
        return [
            'branches.required' => 'Please select at least one branch.',
            'branches.min' => 'Please select at least one branch.',
            'branches.*.exists' => 'Selected branch does not exist.',
        ];
    }
}
