<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|array',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        session()->flash("error_alert",__("translation.Please select at least one checkbox"));
        parent::failedValidation($validator);
    }
}
