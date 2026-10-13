<?php

namespace App\Http\Requests\Branch;

use App\Http\Requests\BaseRequest;

class BranchRequest extends BaseRequest
{
    public function rules(): array
    {
        $name = [];
        if ($this->getMethod() == 'POST') {
            $name = ['required', 'string', 'max:255', 'unique:branches,name'];
        } elseif ($this->getMethod() == 'PUT') {
            $name = ['required', 'string', 'max:255', 'unique:branches,name,' . $this->branch];
        }
        return [
            'name' => $name,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'account_id' => 'nullable|integer',
        ];
    }
}
