<?php

namespace App\Livewire\Settings;

use App\Models\ReceiptSetting;
use App\Models\Settings;
use Livewire\Component;

class ReceiptSettings extends Component
{
    public $settings = [
        'show_logo' => true,
        'show_header_text' => true,
        'show_payment_id' => true,
        'show_student_info' => true,
        'show_group_info' => true,
        'show_payment_type' => true,
        'show_cashier' => true,
        'show_amount' => true,
        'show_footer' => true,
    ];

    public $header_text;

    public $footer_text;

    public $logo_path;

    public $logo_position = 'bottom';

    public $receiptSetting;

    public function mount()
    {
        $settings = Settings::whereIn('key', ['address', 'phone'])->get();
        $address = $settings->where('key', 'address')->first()?->value ?: 'Sample Address';
        $phone = $settings->where('key', 'phone')->first()?->value ?: '+998 90 123 45 67';

        $defaultHeaderText = __('translation.address').": {$address}\n".__('translation.phone').": {$phone}";

        $this->receiptSetting = ReceiptSetting::firstOrCreate(
            [], // empty condition to find first
            [
                'header_text' => $defaultHeaderText,
                'show_logo' => true,
                'show_header_text' => true,
                'show_payment_id' => true,
                'show_student_info' => true,
                'show_group_info' => true,
                'show_payment_type' => true,
                'show_cashier' => true,
                'show_amount' => true,
                'show_footer' => true,
                'logo_position' => 'bottom',
            ]
        );

        $this->header_text = $this->receiptSetting->header_text;
        $this->footer_text = $this->receiptSetting->footer_text;
        $this->logo_position = $this->receiptSetting->logo_position;
        $this->settings = [
            'show_logo' => $this->receiptSetting->show_logo,
            'show_header_text' => $this->receiptSetting->show_header_text,
            'show_payment_id' => $this->receiptSetting->show_payment_id,
            'show_student_info' => $this->receiptSetting->show_student_info,
            'show_group_info' => $this->receiptSetting->show_group_info,
            'show_payment_type' => $this->receiptSetting->show_payment_type,
            'show_cashier' => $this->receiptSetting->show_cashier,
            'show_amount' => $this->receiptSetting->show_amount,
            'show_footer' => $this->receiptSetting->show_footer,
        ];

        // Check logo in general settings
        $settingsLogo = Settings::where('key', 'logo')->first();
        $this->logo_path = $settingsLogo?->getFirstMediaUrl() ?: asset('images/markaz-lms.svg');
    }

    public function updateSetting($key, $value)
    {
        if (! array_key_exists($key, $this->settings)) {
            return;
        }

        $this->settings[$key] = $value;
        $this->receiptSetting->$key = $value;
        $this->receiptSetting->save();

        session()->flash('success', __('translation.receipts.saved'));
    }

    public function updateLogoPosition($position)
    {
        $this->logo_position = $position;
        $this->receiptSetting->logo_position = $position;
        $this->receiptSetting->save();
    }

    public function saveTexts()
    {
        $this->receiptSetting->header_text = $this->header_text;
        $this->receiptSetting->footer_text = $this->footer_text;
        $this->receiptSetting->save();

        session()->flash('success', __('translation.receipts.saved'));
    }

    public function render()
    {
        return view('livewire.settings.receipt-settings');
    }
}
