<?php

namespace App\Services;

use App\Models\Settings;

class SmsSettingsService
{
    /**
     * Set debt SMS template and related settings
     */
    public function setDebtTemplate(array $data)
    {
        // Update debt notice template
        if (isset($data['debt_sms_template'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_sms_template'],
                ['value' => $data['debt_sms_template']]
            );
            cache()->forget('debt_sms_template');
        }

        // Update auto send enabled status
        if (isset($data['debt_auto_send_enabled'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_auto_send_enabled'],
                ['value' => $data['debt_auto_send_enabled']]
            );

            // If enabling auto send, record the current time as last sent
            if ($data['debt_auto_send_enabled'] === '1') {
                Settings::updateOrCreate(
                    ['key' => 'debt_sms_last_sent'],
                    ['value' => now()->toDateTimeString()]
                );
            }
        }

        // Update scheduled day
        if (isset($data['debt_sms_scheduled_day'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_sms_scheduled_day'],
                ['value' => $data['debt_sms_scheduled_day']]
            );
        }

        // Update scheduled time
        if (isset($data['debt_sms_scheduled_time'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_sms_scheduled_time'],
                ['value' => $data['debt_sms_scheduled_time']]
            );
        }

        // Update send type
        if (isset($data['debt_sms_send_type'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_sms_send_type'],
                ['value' => $data['debt_sms_send_type']]
            );
        }

        // Update selected groups
        if (isset($data['debt_sms_selected_groups'])) {
            Settings::updateOrCreate(
                ['key' => 'debt_sms_selected_groups'],
                ['value' => $data['debt_sms_selected_groups']]
            );
            // Clear groups cache when groups are updated
            cache()->forget('groups_with_student_count');
        }

        // Clear relevant caches
        cache()->forget('debt_auto_send_enabled');
        cache()->forget('debt_sms_scheduled_day');
        cache()->forget('debt_sms_scheduled_time');
        cache()->forget('debt_sms_send_type');
        cache()->forget('debt_sms_selected_groups');
    }
}
