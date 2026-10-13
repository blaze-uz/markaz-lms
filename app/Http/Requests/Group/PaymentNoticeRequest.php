<?php

namespace App\Http\Requests\Group;


use App\Http\Requests\BaseRequest;

class PaymentNoticeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'students' => 'required|array|min:1', // Ensure 'students' is present, is an array, and has at least one element
            'students.*' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'students.array' => 'Students must be an array',
            'students.min' => 'Kamida bir o\'quvchi tanlang',
            'students.*.required' => 'O\'quvchi tanlanishi shart',
        ];
    }
}
