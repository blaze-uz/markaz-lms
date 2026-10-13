<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\BaseRequest;

class AccountRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'account_code' => 'required',
        ];
    }
}
