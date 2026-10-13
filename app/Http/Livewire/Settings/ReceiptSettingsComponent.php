<?php

namespace App\Http\Livewire\Settings;

use Livewire\Component;

class ReceiptSettings extends Component
{
    public $settings = [
        'show_logo' => true,
        'show_branch' => true,
        'show_address' => true,
        'show_phone' => true,
        'show_date' => true,
        'show_student_name' => true,
        'show_group_name' => true,
        'show_amount' => true,
        'show_payment_type' => true,
        'show_cashier' => true,
        'header_text' => '',
        'footer_text' => '',
    ];

    public function mount()
    {
        // TODO: Load settings from database
    }

    public function updateSetting($key)
    {
        $this->settings[$key] = !$this->settings[$key];
        // TODO: Save settings to database
    }

    public function updateText($key, $value)
    {
        $this->settings[$key] = $value;
        // TODO: Save settings to database
    }

    public function render()
    {
        return view('livewire.settings.receipt-settings');
    }
}
