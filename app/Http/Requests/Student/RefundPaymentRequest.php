<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;

class RefundPaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required',
            'payment_type_id' => 'required',
            'date' => 'sometimes',
            'group_id' => 'required',
        ];
    }
}
