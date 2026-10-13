<?php

namespace App\Http\Requests\Group;

use App\Http\Requests\BaseRequest;

class AttachGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'group_id' => 'required',
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['required'],
            'attached_at' => 'nullable|date|date_format:Y-m-d',
            'group_agreed_amount' => 'nullable',
            'first_month_debt_amount' => 'nullable',
            'comment' => 'nullable',
            'create_old_period_debts' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => 'Guruh tanlanmagan',
            'student_ids.required' => 'Student tanlanmagan',
            'attached_at.date' => 'Sana noto\'g\'ri kiritilgan',
            'attached_at.date_format' => 'Sana noto\'g\'ri kiritilgan',
        ];
    }
}
