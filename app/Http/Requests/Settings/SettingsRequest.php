<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;

class SettingsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'send_sms' => 'sometimes',
            'payment_sms_status' => 'sometimes',
            'daily_review_command_run_time' => 'sometimes',
        ];
    }
}
