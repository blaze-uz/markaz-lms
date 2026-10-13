<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SmsTemplateRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'content' => 'required|string|min:10',
        ];

        // For update requests, make name unique except for current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] .= '|unique:sms_templates,name,'.$this->route('sms_template');
        } else {
            $rules['name'] .= '|unique:sms_templates,name';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('translation.validation.name_required'),
            'name.unique' => __('translation.validation.name_unique'),
            'name.max' => __('translation.validation.name_max'),
            'content.required' => __('translation.validation.content_required'),
            'content.min' => __('translation.validation.content_min'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('translation.sms.template_name'),
            'content' => __('translation.sms.content'),
        ];
    }
}
