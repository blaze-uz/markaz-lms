<?php

namespace App\Console\Commands;

use App\Jobs\AttendanceNoticeJob;
use App\Models\Group;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttendanceNoticeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:attendance-notice-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send attendance notice to students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $attendanceNoticeStatus = $this->getAttendanceNoticeValue('attendance_notice_status');
        if (!$attendanceNoticeStatus) {
            $this->info('Attendance notice is disabled ❌');
            return;
        }

        $groups = Group::with('attendanceSettings')
            ->whereRelation('attendanceSettings', 'auto_send', true)
            ->whereRelation('groupTimes', function ($query) {
                $query->where('name', strtolower(Carbon::today()->format('l')))
                    ->where('end', '<=', Carbon::now()->format('H:i'));
            })
            ->get();

        foreach ($groups as $group) {
            $time = Carbon::now()->format('H:i');
            $groupTimes = $group->groupTimes()->where('name', strtolower(Carbon::today()->format('l')))->first();
            if ($groupTimes) {
                $attendances = $group->attendances()
                    ->whereDate('date', Carbon::today())
                    ->where('status', 'absent')
                    ->where(function ($query) {
                        $query->where('is_notified', false)->orWhereNull('is_notified');
                    })                    ->get();
                foreach ($attendances as $attendance) {
                    if ($attendance && !$attendance->is_notified && $time >= $attendance->notify_time) {
                        AttendanceNoticeJob::dispatch($attendance->student, $group, $group->attendanceSettings->send_to);
                        $attendance->update(['is_notified' => true]);
                    }
                }
            }
        }

        $this->info('Attendance notice command run successfully ✅');
    }

    private function getAttendanceNoticeValue()
    {
        $attendanceNoticeTemplate = cache()->rememberForever('attendance_notice_template', function () {
            return Settings::where('key', 'attendance_notice_template')->first();
        });
        $attendanceNoticeStatus = cache()->rememberForever('attendance_notice_status', function () {
            return Settings::where('key', 'attendance_notice_status')->first();
        });

        if (!empty($attendanceNoticeTemplate->value) && $attendanceNoticeStatus->value == 'enable') {
            return true;
        }

        return false;
    }
}
