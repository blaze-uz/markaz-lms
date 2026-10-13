<?php

namespace App\Livewire\Settings;

use App\Models\Settings;
use App\Models\SmsTemplate;
use Livewire\Component;

class SetSmsTemplates extends Component
{
    public function render()
    {
        $templates = SmsTemplate::orderBy('name')->get();
        $attendanceNoticeTemplate = Settings::where('key', 'attendance_notice_template')->first();
        return view('livewire.settings.set-sms-templates', [
            'templates' => $templates,
            'attendanceNoticeTemplate' => $attendanceNoticeTemplate
        ]);
    }
}
