<?php

namespace App\Services;

use App\Models\GroupAttendanceSettings;

class GroupAttendanceSettingsService
{
    public function update($id, $data)
    {
        $attendanceSettings = GroupAttendanceSettings::where('group_id', $id)->first();

        $auto_send = isset($data['auto_send']) && $data['auto_send'] == 'on';
        $after_minutes = $data['after_minutes'] ?? 0;
        if (!$attendanceSettings) {
            GroupAttendanceSettings::create([
                'group_id' => $id,
                'auto_send' => $auto_send,
                'after_minutes' => $after_minutes,
                'send_to' => $data['send_to'],
            ]);
        } else {
            $attendanceSettings->update([
                'auto_send' => $auto_send,
                'after_minutes' => $after_minutes,
                'send_to' => $data['send_to'],
            ]);
        }
    }
}