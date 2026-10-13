<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PaymentTypeValidationRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $paymentType = request('payment_type');

        if ($paymentType === 'date_to_date') {
            return !empty($value);
        } elseif ($paymentType === 'by_count_lessons') {
            return !empty(request('lesson_count'));
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute is required based on the selected payment type.';
    }
}
