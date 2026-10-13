<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use App\Models\SmsTemplate;
use Livewire\Component;

class SetPaymentTemplates extends Component
{
    public function render()
    {
        $templates = SmsTemplate::orderBy('name')->select('id', 'name', 'content')->get();
        $paymentSmsTemplate = Settings::where('key', 'payment_sms_template')->first();
        $paymentSmsStatus = Settings::where('key', 'payment_sms_status')->first();

        return view('livewire.settings.set-payment-templates', [
            'templates' => $templates,
            'paymentSmsTemplate' => $paymentSmsTemplate,
            'paymentSmsStatus' => $paymentSmsStatus
        ]);
    }
}
