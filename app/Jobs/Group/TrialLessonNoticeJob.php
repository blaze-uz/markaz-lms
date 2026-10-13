<?php

namespace App\Jobs\Group;

use App\Jobs\BaseJob;
use App\Jobs\BulkSendSms;
use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsGroup;
use App\Models\Student;
use Illuminate\Support\Facades\Bus;

class TrialLessonNoticeJob extends BaseJob
{
    public $queue = 'sms';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $data
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sendSms = settingsFromDb('send_sms');
        $studyCenterName = settingsFromDb('name') ?? config('app.name');

        if (! $sendSms || $sendSms != 'enable') {
            return;
        }

        // 20-december && lower case
        $date = strtolower(dateFormat($this->data['date'], 'd-F'));
        // 14:00
        $time = dateFormat($this->data['date'], 'H:i');

        $phone = Settings::where('key', 'phone')->first();

        // Construct message text
        $messageText = sprintf(
            "Hurmatli ota-ona, %s kuni soat %s da \"%s\" fanidan sinov darsi bo'lib o'tadi.\n\"%s\".\nMa'lumot uchun: +998%s",
            $date,
            $time,
            $this->data['course_name'],
            $studyCenterName,
            $phone->value ?? ''
        );

        $smsGroup = SmsGroup::create();

        $sms = Sms::create([
            'phone' => '998'.$this->data['phone_number'],
            'content' => $messageText,
            'status' => 'pending',
            'smsable_id' => $this->data['student_id'],
            'smsable_type' => Student::class,
            'character_count' => strlen($messageText),
            'sms_group_id' => $smsGroup->id,
            'channel' => config('sms.driver'),
        ]);

        $student = Student::find($this->data['student_id']);
        if ($student) {
            $sms->branches()->attach($student->branch_id);
        }

        Bus::dispatchSync(new BulkSendSms([$sms->id], true));
    }
}
