<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class GroupAttendanceSettingsRequest extends FormRequest
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
            'auto_send' => 'sometimes',
            'after_minutes' => 'required_if:auto_send,on',
            'send_to' => 'required_if:auto_send,on|in:with_parents,student',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'after_minutes.required_if' => 'The field is required',
            'send_to.required_if' => 'The field is required',
            'send_to.in' => 'The send to field must be with parents or student.',
        ];
    }
}
