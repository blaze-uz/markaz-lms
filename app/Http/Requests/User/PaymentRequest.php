<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class PaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'amount' => 'required_if:payment_type,salary,withdraw|min:0',
            'payment_type' => 'required',
            'account_id' => 'required_if:payment_type,salary',
            'payment_month' => 'required_if:payment_type,salary,withdraw|date_format:Y-m',
        ];
    }
}
