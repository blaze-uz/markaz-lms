<?php

namespace App\Http\Requests\Student;

use App\Enums\AccountEnum;
use App\Http\Requests\BaseRequest;
use App\Models\Account;
use App\Models\StudentGroupPayment;

class PaymentRequest extends BaseRequest
{
    public function rules(): array
    {
        //parse amount to be number with remove comma helper
        $this->merge(['amount' => removeComma($this->amount)]);
        $this->merge(['discount_amount' => removeComma($this->discount_amount)]);
        $this->merge(['debt_amount' => removeComma($this->debt_amount)]);

        $mainAccount = Account::where('account_code', AccountEnum::MAIN->value)->first();
        $rules = [
            'debt' => 'sometimes',
            'month' => 'required_if:debt,==,1|not_in:0',
            'amount' => 'required|numeric|min:1000',
            'payment_type_id' => 'required',
            'discount_type' => 'required_with:discount_amount|in:both,main,teacher,other',
            'discount_account_id' => [
                'required_if:discount_type,other',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($this->input('discount_type') === 'other' && !$value) {
                        $fail('Chegirma hisobi tanlanishi shart');
                    }
                }
            ],
            'discount_amount' => 'nullable|numeric|min:1000',
            'debt_amount' => 'nullable|numeric|min:1',
            'date' => 'nullable|date|date_format:Y-m-d',
            'method_type' => 'sometimes',
            'comment' => 'sometimes',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'discount_type.required_with' => 'Chegirma turi tanlanishi shart',
            'discount_type.in' => 'Noto\'g\'ri chegirma turi tanlandi',
            'discount_amount.min' => 'Chegirma summasi kamida 1000 so\'m bo\'lishi kerak',
            'amount.min' => 'To\'lov summasi kamida 1000 so\'m bo\'lishi kerak',
            'month.required_if' => 'Oy tanlanishi shart',
            'month.not_in' => 'Oy tanlanishi shart',
        ];
    }

    protected function prepareForValidation()
    {
        // Convert discount_account_id based on discount_type
        if ($this->has('discount_type') && $this->has('discount_amount')) {
            $discountType = $this->input('discount_type');

            switch ($discountType) {
                case 'both':
                    $this->merge(['discount_account_id' => 'both']);
                    break;
                case 'main':
                    $mainAccount = Account::where('account_code', AccountEnum::MAIN->value)->first();
                    $this->merge(['discount_account_id' => $mainAccount?->id]);
                    break;
                case 'teacher':
                    if ($this->has('month')) {
                        $studentGroupPayment = StudentGroupPayment::find($this->input('month'));
                        if ($studentGroupPayment && $studentGroupPayment->group?->teacher?->account) {
                            $this->merge(['discount_account_id' => $studentGroupPayment->group->teacher->account->id]);
                        }
                    }
                    break;
                // For 'other', keep the original discount_account_id
            }
        }
    }
}
