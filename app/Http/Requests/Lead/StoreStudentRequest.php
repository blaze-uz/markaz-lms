<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\BaseRequest;

class StoreStudentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'lead_id' => 'required',
            'name' => 'required',
            'surname' => 'required',
            'phone' => 'required',
            'comment' => 'nullable',
            'phone2' => 'nullable',
            'address' => 'nullable',
        ];
    }
}
