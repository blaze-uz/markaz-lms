<?php

namespace App\Http\Requests\Group;

use App\Http\Requests\BaseRequest;

class GroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'price' => 'required',
            'course_id' => 'required',
            'teacher_id' => 'required',
            'branch_id' => 'required',
            'teacher_percentage' => 'required_if:teacher_percentage_switch,on|nullable|numeric|min:0|max:100',
            'payment_type' => 'sometimes',
            'lesson_count' => 'sometimes',
            'start_date' => 'sometimes',
            'monday.*' => 'sometimes',
            'tuesday.*' => 'sometimes',
            'wednesday.*' => 'sometimes',
            'thursday.*' => 'sometimes',
            'friday.*' => 'sometimes',
            'saturday.*' => 'sometimes',
            'sunday.*' => 'sometimes',
        ];
    }
}
