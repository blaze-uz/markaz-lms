<?php

use App\Models\Settings;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add attendance_notice_template and attendance_notice_status to settings table
        $attendanceNoticeTemplate = [
            'key' => 'attendance_notice_template',
            'value' => null,
        ];
        $attendanceNoticeStatus = [
            'key' => 'attendance_notice_status',
            'value' => 'disable',
        ];

        Settings::create($attendanceNoticeTemplate);
        Settings::create($attendanceNoticeStatus);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
