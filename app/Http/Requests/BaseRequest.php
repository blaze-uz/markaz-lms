<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return trans('validation');
    }

    public function attributes()
    {
        return trans('attr');
    }
}
