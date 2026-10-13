<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use Livewire\Component;

class StaffSettings extends Component
{
    public $auto_payment;

    public function mount()
    {
        $this->auto_payment = $this->getSettingValue('auto_payment_for_staff');
    }

    public function changeStatus(): void
    {
        $this->toggleSetting('auto_payment_for_staff', $this->auto_payment);
    }

    private function toggleSetting($key, $status)
    {
        $status = ($status === 'enable') ? 'disable' : 'enable';

        Settings::updateOrCreate(
            ['key' => $key],
            ['value' => $status]
        );

        $this->dispatch('settings-updated'); 
    }

    public function render()
    {
        return view('livewire.settings.staff-settings');
    }

    private function getSettingValue($key)
    {
        $setting = Settings::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }
}
