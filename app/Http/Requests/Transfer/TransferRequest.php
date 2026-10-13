<?php

namespace App\Http\Requests\Transfer;

use App\Http\Requests\BaseRequest;

class TransferRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'transfers' => 'required|array',
            'transfers.*.income_account_id' => 'required|exists:accounts,id',
            'transfers.*.expense_account_id' => 'required|exists:accounts,id|different:transfers.*.income_account_id',
            'transfers.*.amount' => 'required',
//            'transfers.*.date' => 'required|date',
        ];
    }
}
