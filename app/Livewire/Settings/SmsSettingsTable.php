<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use Livewire\Component;

class SmsSettingsTable extends Component
{
    public $error = '';

    public $success = '';

    public $smsStatus;

    public $paymentSmsStatus;

    public $attendanceSmsStatus;

    public $debtSmsStatus;

    public function mount()
    {
        $this->retrieveSettings();
    }

    public function render()
    {
        return view('livewire.settings.sms-settings-table');
    }

    private function retrieveSettings()
    {
        $this->smsStatus = $this->getSettingValue('send_sms');
        $this->paymentSmsStatus = $this->getSettingValue('payment_sms_status');
        $this->attendanceSmsStatus = $this->getSettingValue('attendance_notice_status');
        $this->debtSmsStatus = $this->getSettingValue('debt_sms_status');
    }

    private function getSettingValue($key)
    {
        $setting = Settings::where('key', $key)->first();

        return $setting ? $setting->value : null;
    }

    public function changeStatus(): void
    {
        $this->toggleSetting('send_sms', $this->smsStatus);
    }

    public function changePaymentStatus()
    {
        $this->toggleSetting('payment_sms_status', $this->paymentSmsStatus);
    }

    public function changeAttendanceStatus(): void
    {
        $this->toggleSetting('attendance_notice_status', $this->attendanceSmsStatus);
    }

    public function changeDebtStatus(): void
    {
        $this->toggleSetting('debt_sms_status', $this->debtSmsStatus);
    }

    private function toggleSetting($key, &$status)
    {
        $status = ($status === 'enable') ? 'disable' : 'enable';

        Settings::updateOrCreate(
            ['key' => $key],
            ['value' => $status]
        );

        cache()->forget('send_sms');
        cache()->forget('payment_sms_status');
        cache()->forget('attendance_notice_status');
        cache()->forget('debt_sms_status');
        cache()->forget('settings.'.$key);
        session()->flash('success_alert', __('translation.alert_messages.settings_updated'));
    }
}
