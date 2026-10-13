<?php

namespace App\Http\Requests\Group;

use App\Http\Requests\BaseRequest;

class DebtRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required|date|date_format:Y-m-d',
            'amount' => 'required',
            'students' => 'required|array|min:1', // Ensure 'students' is present, is an array, and has at least one element
            'students.*' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Sana maydoni to\'ldirilishi shart',
            'date.date' => 'Sana maydoni to\'g\'ri formatda kiritilishi shart',
            'amount.required' => 'Summa maydoni to\'ldirilishi shart',
            'students.array' => 'Students must be an array',
            'students.min' => 'Kamida bir o\'quvchi tanlang',
            'students.*.required' => 'O\'quvchi tanlanishi shart',
        ];
    }
}
