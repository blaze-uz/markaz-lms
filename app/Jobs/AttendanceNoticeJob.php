<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Models\SmsTemplate;
use App\Services\SmsBulkDispatchService;
use Illuminate\Support\Facades\Log;

class AttendanceNoticeJob extends BaseJob
{
    public $queue = 'sms';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public $student,
        public $group,
        public $sendTo
    ) {
        // Convert objects to arrays to avoid serialization issues
        if (is_object($this->student)) {
            $this->student = $this->student->toArray();
        }
        if (is_object($this->group)) {
            $this->group = $this->group->toArray();
        }

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($secondsRemaining = isRateLimited('sms-api-limit')) {
            info('AttendanceNoticeJob: Rate limit active, releasing job', [
                'student_id' => $this->student['id'] ?? 'unknown',
                'seconds' => $secondsRemaining,
            ]);
            $this->release($secondsRemaining);

            return;
        }

        $attendanceNoticeTemplate = settingsFromDb('attendance_notice_template');
        if (! $attendanceNoticeTemplate) {
            info('AttendanceNoticeJob: Template not configured', [
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        $template = SmsTemplate::find($attendanceNoticeTemplate);
        if (! $template) {
            info('AttendanceNoticeJob: Template not found', [
                'template_id' => $attendanceNoticeTemplate,
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        if (empty($this->student['phone'])) {
            info('AttendanceNoticeJob: No student phone', [
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        $sendSms = settingsFromDb('send_sms');

        if (! $sendSms || $sendSms != 'enable') {
            info('AttendanceNoticeJob: SMS sending is disabled', [
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        $studyCenter = settingsFromDb('name');
        $phone = settingsFromDb('phone');
        $teacherName = $this->group['teacher']['name'] ?? ($this->group['teacher'] ?? null);
        if (is_array($teacherName)) {
            $teacherName = $teacherName['name'] ?? '';
        }

        $data = [
            'study_center' => $studyCenter ?? '',
            'student' => $this->student['name'] ?? '',
            'phone' => formattedPhone($phone),
            'teacher' => $teacherName ?? '',
        ];

        $message = str_replace(
            ['{STUDY_CENTER}', '{STUDENT}', '{TEACHER}', '{PHONE}'],
            [
                $data['study_center'],
                $data['student'],
                $data['teacher'],
                $data['phone'],
            ],
            $template->content
        );

        $smsIdsToSend = [];

        if ($this->sendTo == 'with_parents' && ! empty($this->student['parent_phone'])) {
            $smsForParents = Sms::create([
                'phone' => '998'.unformattedPhone($this->student['parent_phone']),
                'content' => $message,
                'status' => 'pending',
                'smsable_id' => $this->student['id'],
                'smsable_type' => 'App\Models\Student',
                'character_count' => strlen($message),
                'sms_template_id' => $template->id,
                'channel' => config('sms.driver'),
            ]);
            $smsForParents->branches()->attach($this->group['branch_id'] ?? 1);
            $smsIdsToSend[] = $smsForParents->id;
        }

        $sms = Sms::create([
            'phone' => '998'.unformattedPhone($this->student['phone']),
            'content' => $message,
            'status' => 'pending',
            'smsable_id' => $this->student['id'],
            'smsable_type' => 'App\Models\Student',
            'character_count' => strlen($message),
            'sms_template_id' => $template->id,
            'channel' => config('sms.driver'),
        ]);
        $sms->branches()->attach($this->group['branch_id'] ?? 1);
        $smsIdsToSend[] = $sms->id;

        if ($smsIdsToSend !== []) {
            SmsBulkDispatchService::dispatchFromSmsIds($smsIdsToSend);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AttendanceNoticeJob: Job failed', [
            'student_id' => $this->student['id'] ?? 'unknown',
            'group_id' => $this->group['id'] ?? 'unknown',
            'send_to' => $this->sendTo,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
