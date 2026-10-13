<?php

namespace App\Http\Requests\Lead;

use App\Http\Requests\BaseRequest;
use App\Services\LeadDynamicFieldService;

class LeadFormRequest extends BaseRequest
{
    public function rules(): array
    {
        return array_merge([
            'name' => 'required',
            'phone' => 'required',
            'course_id' => 'nullable|exists:courses,id',
            'comment' => 'sometimes|max:255',
        ], app(LeadDynamicFieldService::class)->customAttributeValidationRules());
    }

    protected function passedValidation(): void
    {
        $this->merge([
            'custom_attributes' => app(LeadDynamicFieldService::class)->normalizeValidated(
                $this->input('custom_attributes')
            ),
        ]);
    }
}
