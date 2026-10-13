<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ScheduleSmsDateRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // date format: Y-m-d H:i and should be greater than today
        if (strtotime($value) < strtotime(now())) {
            $fail(__('translation.sms.schedule_date_invalid'));
        }
    }
}
