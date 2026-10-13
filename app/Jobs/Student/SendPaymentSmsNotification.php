<?php

namespace App\Jobs\Student;

use App\Jobs\BaseJob;
use App\Jobs\SendSms;
use App\Models\Branch;
use App\Models\Sms;
use App\Models\SmsGroup;
use App\Models\SmsTemplate;
use App\Models\Student;
use Illuminate\Support\Facades\Log;

class SendPaymentSmsNotification extends BaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        public $student,
        public $payment,
        public $date = null
    ) {
        // Convert objects to arrays to avoid serialization issues
        if (is_object($this->student)) {
            $this->student = $this->student->toArray();
        }
        if (is_object($this->payment)) {
            $this->payment = $this->payment->toArray();
        }

    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendPaymentSmsNotification failed', [
            'student_id' => $this->student['id'] ?? 'unknown',
            'payment_id' => is_object($this->payment) ? $this->payment->id : ($this->payment['id'] ?? 'unknown'),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        logger('SendPaymentSmsNotification started', [
            'student_id' => $this->student['id'] ?? 'unknown',
            'payment_id' => $this->payment['id'] ?? 'unknown',
        ]);

        if ($secondsRemaining = isRateLimited('sms-api-limit')) {
            info('SendPaymentSmsNotification: Skipped - SMS API rate limit active, releasing job');
            $this->release($secondsRemaining);

            return;
        }

        // Check if SMS is enabled
        $sendSmsStatus = settingsFromDb('send_sms');
        if (! $sendSmsStatus || ($sendSmsStatus !== 'enable' && $sendSmsStatus !== '1')) {
            info('SendPaymentSmsNotification: Skipped - SMS sending is disabled', [
                'send_sms_status' => $sendSmsStatus,
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        // Check if payment SMS is enabled
        $paymentSmsStatus = settingsFromDb('payment_sms_status');
        if (! $paymentSmsStatus || ($paymentSmsStatus !== 'enable' && $paymentSmsStatus !== '1')) {
            info('SendPaymentSmsNotification: Skipped - Payment SMS is disabled', [
                'payment_sms_status' => $paymentSmsStatus,
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        // Get payment SMS template
        $settingsPaymentSmsTemplate = settingsFromDb('payment_sms_template');
        $smsTemplate = SmsTemplate::find($settingsPaymentSmsTemplate);

        if (! $smsTemplate) {
            info('SendPaymentSmsNotification: Skipped - Payment SMS template not found', [
                'template_id' => $settingsPaymentSmsTemplate,
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        // Get SMS settings
        $senderName = settingsFromDb('name');
        $phone = settingsFromDb('phone');

        if (! $phone) {
            info('SendPaymentSmsNotification: Skipped - Missing SMS configuration', [
                'has_phone' => ! empty($phone),
                'student_id' => $this->student['id'] ?? 'unknown',
            ]);

            return;
        }

        $date = $this->date ?? now();

        // Check student's phone number if number's length is less than 9, then return
        $receiverPhone = preg_replace('/[^0-9]/', '', $this->student['phone']);
        if (strlen($receiverPhone) != 9) {
            info('SendPaymentSmsNotification: Skipped - Invalid student phone number', [
                'student_id' => $this->student['id'] ?? 'unknown',
                'phone' => $this->student['phone'] ?? 'unknown',
                'cleaned_phone' => $receiverPhone,
                'phone_length' => strlen($receiverPhone),
            ]);

            return;
        }

        $smsGroup = $this->createSmsGroup();

        // Process template content - replace variables with consistent naming
        $messageContent = $this->prepareMessageContent($smsTemplate, $date, $phone, $senderName);

        // Create SMS record first (status: pending) - same pattern as SendSms.php
        $sms = Sms::create([
            'phone' => '998'.$receiverPhone,
            'content' => $messageContent,
            'status' => 'pending',
            'smsable_id' => $this->student['id'],
            'smsable_type' => Student::class,
            'character_count' => strlen($messageContent),
            'sms_group_id' => $smsGroup,
            'sms_template_id' => $smsTemplate->id,
            'channel' => config('sms.driver'),
        ]);

        $sms->branches()->attach($this->student['branch_id'] ?? Branch::first()->id);

        SendSms::dispatch($sms->id, $sms->phone, $sms->content);
    }

    public function createSmsGroup()
    {
        $smsGroup = SmsGroup::create();

        return $smsGroup->id;
    }

    /**
     * Prepare message content by replacing template variables
     */
    private function prepareMessageContent(SmsTemplate $smsTemplate, $date, string $phone, string $senderName): string
    {
        $messageContent = $smsTemplate->content;
        $formattedAmount = number_format($this->payment['amount'], 0, '', ' ');
        $teacherName = $this->getTeacherName();

        // Replace uppercase variables
        $messageContent = str_replace('{DATE}', dateFormat($date, 'd.m.Y H:i'), $messageContent);
        $messageContent = str_replace('{STUDENT}', $this->student['name'], $messageContent);
        $messageContent = str_replace('{AMOUNT}', $formattedAmount, $messageContent);
        $messageContent = str_replace('{PHONE}', '+998'.$phone, $messageContent);
        $messageContent = str_replace('{STUDY_CENTER}', $senderName, $messageContent);
        $messageContent = str_replace('{TEACHER}', $teacherName, $messageContent);

        // Replace lowercase variables for backward compatibility
        $messageContent = str_replace('{date}', dateFormat($date, 'd.m.Y H:i'), $messageContent);
        $messageContent = str_replace('{student}', $this->student['name'], $messageContent);
        $messageContent = str_replace('{amount}', $formattedAmount, $messageContent);
        $messageContent = str_replace('{phone}', '+998'.$phone, $messageContent);
        $messageContent = str_replace('{study_center}', $senderName, $messageContent);
        $messageContent = str_replace('{teacher}', $teacherName, $messageContent);

        return $messageContent;
    }

    /**
     * Get teacher name from payment
     */
    private function getTeacherName(): string
    {
        // Since payment is array, try to get teacher info from it
        if (isset($this->payment['teacher_name']) && isset($this->payment['teacher_surname'])) {
            return trim($this->payment['teacher_name'].' '.$this->payment['teacher_surname']);
        }

        // Try from group relationship if available
        if (isset($this->payment['group']['teacher'])) {
            $teacher = $this->payment['group']['teacher'];

            return trim(($teacher['name'] ?? '').' '.($teacher['surname'] ?? ''));
        }

        // Try from student_group_payment relationship
        if (isset($this->payment['student_group_payment']['group']['teacher'])) {
            $teacher = $this->payment['student_group_payment']['group']['teacher'];

            return trim(($teacher['name'] ?? '').' '.($teacher['surname'] ?? ''));
        }

        return '';
    }
}
