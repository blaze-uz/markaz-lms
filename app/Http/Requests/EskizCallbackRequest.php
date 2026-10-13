<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EskizCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        $token = (string) config('sms.eskiz.callback_token');
        abort_if($token === '', 503);
        abort_unless(hash_equals($token, (string) $this->query('token', '')), 401);

        return true;
    }

    public function rules(): array
    {
        return [
            'user_sms_id' => 'nullable|integer',
            'message_id' => 'nullable',
            'request_id' => 'nullable',
            'status' => 'required|string',
            'status_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'parts_count' => 'nullable|integer|min:1',
        ];
    }
}
