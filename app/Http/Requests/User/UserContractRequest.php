<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'auto_salary_status' => 'sometimes',
            'amount' => 'required_if:auto_salary_status,on',
            'payment_day' => 'required_if:auto_salary_status,on',
        ];
    }

    public function messages()
    {
        return [
            'amount.required_if' => 'Avtomatik ish haqi holati yoqilganda miqdor maydoni talab qilinadi',
            'payment_day.required_if' => "Avtomatik ish haqi holati yoqilgan bo'lsa, to'lov kuni maydoni talab qilinadi",
        ];
    }
}
