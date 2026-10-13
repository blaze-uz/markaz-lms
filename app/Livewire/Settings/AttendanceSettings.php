<?php

namespace App\Livewire\Settings;

use App\Models\ValidationSettings;
use Livewire\Component;

class AttendanceSettings extends Component
{
    public function render()
    {
        $attendanceValidationSettings = ValidationSettings::where('group', 'attendance')->orderBy('id')->get();
        return view('livewire.settings.attendance-settings', compact('attendanceValidationSettings'));
    }
}
