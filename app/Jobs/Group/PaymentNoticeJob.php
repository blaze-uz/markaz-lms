<?php

namespace App\Jobs\Group;

use App\Jobs\BaseJob;
use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsGroup;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use App\Services\SmsBulkDispatchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentNoticeJob extends BaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $group,
        protected $student,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($secondsRemaining = isRateLimited('sms-api-limit')) {
            Log::info('PaymentNoticeJob: Rate limit active, releasing job', [
                'group_id' => $this->group->id ?? 'unknown',
                'student_id' => $this->student->id ?? 'unknown',
                'seconds' => $secondsRemaining,
            ]);
            $this->release($secondsRemaining);

            return;
        }

        Log::info('PaymentNoticeJob started', [
            'group_id' => $this->group->id,
            'group_name' => $this->group->name,
            'student_id' => $this->student->id,
            'student_name' => $this->student->getFullName(),
        ]);

        DB::beginTransaction();
        try {
            $this->sendPaymentNotice();
            DB::commit();

            Log::info('PaymentNoticeJob completed successfully', [
                'group_id' => $this->group->id,
                'student_id' => $this->student->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PaymentNoticeJob failed', [
                'group_id' => $this->group->id ?? 'unknown',
                'student_id' => $this->student->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Send payment notice SMS to student
     */
    private function sendPaymentNotice(): void
    {
        if (! $this->isSmsEnabled()) {
            return;
        }

        $template = $this->getDebtSmsTemplate();
        if (! $template) {
            throw new \Exception('Debt SMS template not configured');
        }

        $studentGroupPayment = $this->getStudentGroupPayment();
        if (! $studentGroupPayment) {
            throw new \Exception('No payment record found for student');
        }

        $smsGroup = SmsGroup::create();
        $this->createAndDispatchSms($studentGroupPayment, $template, $smsGroup);
    }

    /**
     * Check if SMS sending is enabled
     */
    private function isSmsEnabled(): bool
    {
        $sendSms = settingsFromDb('send_sms');

        return $sendSms === 'enable';
    }

    /**
     * Get the configured debt SMS template
     */
    private function getDebtSmsTemplate(): ?SmsTemplate
    {
        $templateId = Settings::where('key', 'debt_sms_template')->first()?->value;
        if (! $templateId) {
            return null;
        }

        return SmsTemplate::find($templateId);
    }

    /**
     * Get student group payment with debt
     */
    private function getStudentGroupPayment(): ?StudentGroupPayment
    {
        return StudentGroupPayment::where('group_id', $this->group->id)
            ->where('student_id', $this->student->id)
            ->where('left_amount', '>', 0)
            ->first();
    }

    /**
     * Create SMS record and dispatch SendSms job
     */
    private function createAndDispatchSms(
        StudentGroupPayment $studentGroupPayment,
        SmsTemplate $template,
        SmsGroup $smsGroup
    ): void {
        $month = $this->getPaymentMonth($studentGroupPayment);
        $message = $this->replaceTemplateVariables($template->content, $studentGroupPayment, $month);
        $phone = $this->getStudentPhone();

        if (! $phone) {
            throw new \Exception('No valid phone number found for student');
        }

        $sms = Sms::create([
            'phone' => $phone,
            'content' => $message,
            'status' => 'pending',
            'smsable_id' => $this->student->id,
            'smsable_type' => Student::class,
            'character_count' => strlen($message),
            'sms_group_id' => $smsGroup->id,
            'sms_template_id' => $template->id,
            'channel' => config('sms.driver'),
        ]);

        $branchId = $this->student->branch_id ?? 1;
        try {
            $sms->branches()->attach($branchId);
        } catch (\Exception $e) {
            Log::warning('PaymentNoticeJob: Failed to attach SMS to branch', [
                'sms_id' => $sms->id,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
            ]);
        }

        SmsBulkDispatchService::dispatchFromSmsIds([$sms->id]);
    }

    /**
     * Get payment month based on group payment type
     */
    private function getPaymentMonth(StudentGroupPayment $studentGroupPayment): string
    {
        try {
            if ($this->group->payment_type === 'one_time') {
                return __('translation.groups.one_time');
            }

            if ($this->group->payment_type === 'date_to_date' && $studentGroupPayment->range) {
                $rangeParts = explode('/', $studentGroupPayment->range);
                if (count($rangeParts) >= 2) {
                    // Format: MM/YYYY, convert to proper date
                    $monthNum = str_pad($rangeParts[0], 2, '0', STR_PAD_LEFT);
                    $year = $rangeParts[1];
                    $dateString = $year.'-'.$monthNum.'-01';

                    return strtolower(dateFormat($dateString, 'F', 'uz_Latn'));
                }
            }

            return strtolower(dateFormat($studentGroupPayment->created_at, 'F', 'uz_Latn'));
        } catch (\Exception $e) {
            Log::warning('PaymentNoticeJob: Error calculating payment month', [
                'error' => $e->getMessage(),
                'range' => $studentGroupPayment->range,
                'created_at' => $studentGroupPayment->created_at,
            ]);

            // Fallback to current month
            return strtolower(dateFormat(now(), 'F', 'uz_Latn'));
        }
    }

    /**
     * Replace template variables with actual values
     */
    private function replaceTemplateVariables(
        string $content,
        StudentGroupPayment $studentGroupPayment,
        string $month
    ): string {
        $studyCenter = settingsFromDb('name') ?? 'Markaz LMS';
        $phone = settingsFromDb('phone');
        $courseName = $this->group->course?->name ?? '-';
        $amount = number_format($studentGroupPayment->left_amount, 0, '', ' ');
        $formattedPhone = $phone ? '+998'.$phone : '-';
        $date = now()->format('d.m.Y');
        $time = now()->format('H:i');
        $studentName = $this->student->getFullName();

        $replacements = [
            '{STUDENT}' => $studentName,
            '{student}' => $studentName,
            '{COURSE}' => $courseName,
            '{course}' => $courseName,
            '{MONTH}' => $month,
            '{month}' => $month,
            '{AMOUNT}' => $amount,
            '{amount}' => $amount,
            '{STUDY_CENTER}' => $studyCenter,
            '{study_center}' => $studyCenter,
            '{PHONE}' => $formattedPhone,
            '{phone}' => $formattedPhone,
            '{DATE}' => $date,
            '{date}' => $date,
            '{TIME}' => $time,
            '{time}' => $time,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Get student's phone number (prefer parent phone for minors)
     */
    private function getStudentPhone(): ?string
    {
        $phone = null;

        if ($this->student->getAge() < 18 && $this->student->parent_phone) {
            $phone = $this->student->parent_phone;
        } elseif ($this->student->phone) {
            $phone = $this->student->phone;
        } elseif ($this->student->parent_phone) {
            $phone = $this->student->parent_phone;
        }

        return $phone ? $this->formatPhone($phone) : null;
    }

    /**
     * Format phone number for SMS sending
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 9) {
            return '998'.$phone;
        }

        if (strlen($phone) === 12 && ! str_starts_with($phone, '998')) {
            return '998'.substr($phone, -9);
        }

        return $phone;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('PaymentNoticeJob: Job failed', [
            'group_id' => $this->group->id ?? 'unknown',
            'student_id' => $this->student->id ?? 'unknown',
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
