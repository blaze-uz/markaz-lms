<?php

namespace App\Http\Requests\Group;

use App\Http\Requests\BaseRequest;

class StartGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'start_payment_amount' => 'required',
            'payment_type' => 'required|in:date_to_date,by_count_lessons,one_time',
            'start_date' => ['required_if:payment_type,date_to_date,one_time', 'nullable', 'date', 'date_format:Y-m-d'],
            'lesson_count' => ['required_if:payment_type,by_count_lessons'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'students.*' => 'required',
        ];
    }
}
