<?php

namespace App\Console\Commands;

use App\Models\Settings;
use App\Models\Sms;
use App\Models\SmsGroup;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Services\SmsBulkDispatchService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDebtSmsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:send-debt {--force : Force send regardless of schedule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send debt SMS notifications to students based on configured settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting debt SMS sending process...');

        // Check if auto send is enabled
        if (! $this->isAutoSendEnabled()) {
            $this->info('Auto send SMS is disabled. Exiting...');
            info('SendDebtSmsCommand: Auto send disabled');

            return 0;
        }

        // Check if it's the right time to send (unless forced)
        if (! $this->option('force') && ! $this->isTimeToSend()) {
            $this->info('Not the scheduled time to send SMS. Use --force to override.');
            info('SendDebtSmsCommand: Not scheduled time', [
                'current_day' => now()->day,
                'current_time' => now()->format('H:i'),
                'scheduled_day' => Settings::where('key', 'debt_sms_scheduled_day')->first()?->value,
                'scheduled_time' => Settings::where('key', 'debt_sms_scheduled_time')->first()?->value,
            ]);

            return 0;
        }

        // Get debt SMS template
        $template = $this->getDebtSmsTemplate();
        if (! $template) {
            $this->error('No debt SMS template configured. Please set up a template first.');
            info('SendDebtSmsCommand: No template configured');

            return 1;
        }

        // Get students with debt
        $studentsWithDebt = $this->getStudentsWithDebt();
        if ($studentsWithDebt->isEmpty()) {
            $this->info('No students with debt found. Nothing to send.');
            info('SendDebtSmsCommand: No students with debt found');

            return 0;
        }

        $this->info("Found {$studentsWithDebt->count()} students with debt.");

        info('SendDebtSmsCommand: Students with debt found', [
            'count' => $studentsWithDebt->count(),
            'student_ids' => $studentsWithDebt->pluck('id')->toArray(),
            'student_names' => $studentsWithDebt->map(function ($student) {
                return $student->getFullName();
            })->toArray(),
            'send_type' => Settings::where('key', 'debt_sms_send_type')->first()?->value ?? 'all',
        ]);

        // Debug: Log each student's groups and debts
        foreach ($studentsWithDebt as $student) {
            $activeGroups = $student->groups()->where('state', 'active')->get();
            $debts = $student->groupPayments()->where('left_amount', '>', 0)->with('group')->get();

            info('SendDebtSmsCommand: Student debug info', [
                'student_id' => $student->id,
                'student_name' => $student->getFullName(),
                'active_groups' => $activeGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'state' => $group->state,
                    ];
                })->toArray(),
                'all_debts' => $debts->map(function ($debt) {
                    return [
                        'id' => $debt->id,
                        'amount' => $debt->left_amount,
                        'group_id' => $debt->group_id,
                        'group_name' => $debt->group?->name,
                        'group_state' => $debt->group?->state,
                    ];
                })->toArray(),
            ]);
        }

        // Create SMS group for this batch
        $smsGroup = $this->createSmsGroup();

        info('SendDebtSmsCommand: SMS group created', [
            'sms_group_id' => $smsGroup->id,
        ]);

        $sentCount = 0;
        $errorCount = 0;
        $queuedSmsIds = [];

        foreach ($studentsWithDebt as $student) {
            try {
                info('SendDebtSmsCommand: Processing student', [
                    'student_id' => $student->id,
                    'student_name' => $student->getFullName(),
                    'total_debt' => $student->groupPayments->sum('left_amount'),
                ]);

                $smsId = $this->createDebtSmsForStudent($student, $template, $smsGroup);
                if ($smsId !== null) {
                    $queuedSmsIds[] = $smsId;
                    $sentCount++;
                    $this->line("✓ SMS queued for {$student->name} {$student->surname}");

                    info('SendDebtSmsCommand: SMS queued successfully', [
                        'student_id' => $student->id,
                    ]);
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("✗ Failed to queue SMS for {$student->name} {$student->surname}: {$e->getMessage()}");

                info('SendDebtSmsCommand: Failed to queue SMS', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        if ($queuedSmsIds !== []) {
            SmsBulkDispatchService::dispatchFromSmsIds($queuedSmsIds);
        }

        // Update last sent time
        $this->updateLastSentTime();

        $this->info('SMS sending completed!');
        $this->info("Successfully queued: {$sentCount}");
        if ($errorCount > 0) {
            $this->warn("Errors: {$errorCount}");
        }

        info('SendDebtSmsCommand: Execution completed', [
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'total_students' => $studentsWithDebt->count(),
        ]);

        return 0;
    }

    /**
     * Check if auto send SMS is enabled
     */
    private function isAutoSendEnabled(): bool
    {
        $setting = Settings::where('key', 'debt_auto_send_enabled')->first();

        return $setting && $setting->value === '1';
    }

    /**
     * Check if it's time to send SMS based on scheduled day and time
     */
    private function isTimeToSend(): bool
    {
        $scheduledDay = Settings::where('key', 'debt_sms_scheduled_day')->first()?->value;
        $scheduledTime = Settings::where('key', 'debt_sms_scheduled_time')->first()?->value;

        if (! $scheduledDay || ! $scheduledTime) {
            return false;
        }

        $now = Carbon::now();
        $currentDay = $now->day;
        $currentHour = $now->hour;

        // Check if it's the right day of the month
        if ($currentDay != $scheduledDay) {
            return false;
        }

        // Extract hour from scheduled time (format: HH:MM or H:MM)
        $scheduledHour = (int) explode(':', $scheduledTime)[0];

        // Check if it's the right hour (any minute within that hour)
        return $currentHour == $scheduledHour;
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
     * Get students with debt based on send type and selected groups
     */
    private function getStudentsWithDebt()
    {
        $sendType = Settings::where('key', 'debt_sms_send_type')->first()?->value ?? 'all';

        // First, get only students who are currently in active groups
        $query = Student::whereHas('groups', function ($q) {
            $q->where('state', 'active');
        });

        // Then filter to only those who have debts from those active groups
        $query->whereHas('groupPayments', function ($q) {
            $q->where('left_amount', '>', 0)
                ->whereHas('group', function ($groupQuery) {
                    $groupQuery->where('state', 'active');
                });
        });

        // Eager load the relationships we need
        $query->with([
            'groupPayments' => function ($q) {
                $q->where('left_amount', '>', 0)
                    ->whereHas('group', function ($groupQuery) {
                        $groupQuery->where('state', 'active');
                    });
            },
            'groupPayments.group',
            'branches',
            'groups' => function ($q) {
                $q->where('state', 'active');
            },
        ]);

        if ($sendType === 'custom') {
            $selectedGroups = $this->getSelectedGroups();
            if (! empty($selectedGroups)) {
                $query->whereHas('groups', function ($q) use ($selectedGroups) {
                    $q->whereIn('groups.id', $selectedGroups)
                        ->where('state', 'active');
                });
            }
        }

        return $query->get();
    }

    /**
     * Get selected groups for custom send type
     */
    private function getSelectedGroups(): array
    {
        $setting = Settings::where('key', 'debt_sms_selected_groups')->first();
        if (! $setting || ! $setting->value) {
            return [];
        }

        $decoded = json_decode($setting->value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Create SMS group for this batch
     */
    private function createSmsGroup(): SmsGroup
    {
        return SmsGroup::create();
    }

    /**
     * Create pending debt SMS for a student; bulk dispatch runs after the command loop.
     */
    private function createDebtSmsForStudent(Student $student, SmsTemplate $template, SmsGroup $smsGroup): ?int
    {
        info('SendDebtSmsCommand: Starting SMS creation for student', [
            'student_id' => $student->id,
            'template_id' => $template->id,
            'sms_group_id' => $smsGroup->id,
        ]);

        // Calculate total debt amount (only from active groups)
        $totalDebt = $student->groupPayments->filter(function ($payment) {
            return $payment->group && $payment->group->state === 'active';
        })->sum('left_amount');

        // Get student's active groups with debt
        $debtGroups = $student->groups()->where('state', 'active')->whereHas('studentGroupPayments', function ($q) use ($student) {
            $q->where('student_id', $student->id)
                ->where('left_amount', '>', 0);
        })->pluck('name')->implode(', ');

        info('SendDebtSmsCommand: Debt calculation completed', [
            'student_id' => $student->id,
            'total_debt' => $totalDebt,
            'debt_groups' => $debtGroups,
        ]);

        // Replace template variables
        $message = $this->replaceTemplateVariables($template->content, $student, $totalDebt, $debtGroups);

        info('SendDebtSmsCommand: Template variables replaced', [
            'student_id' => $student->id,
            'original_template' => $template->content,
            'final_message' => $message,
        ]);

        // Determine phone number (prefer parent phone for minors)
        $phone = $this->getStudentPhone($student);
        if (! $phone) {
            info('SendDebtSmsCommand: No valid phone number found', [
                'student_id' => $student->id,
                'student_phone' => $student->phone,
                'parent_phone' => $student->parent_phone,
                'student_age' => $student->getAge(),
            ]);
            throw new \Exception('No valid phone number found');
        }

        info('SendDebtSmsCommand: Phone number determined', [
            'student_id' => $student->id,
            'phone' => $phone,
        ]);

        // Create SMS record
        $sms = Sms::create([
            'phone' => $phone,
            'content' => $message,
            'status' => 'pending',
            'smsable_id' => $student->id,
            'smsable_type' => Student::class,
            'character_count' => strlen($message),
            'sms_group_id' => $smsGroup->id,
            'sms_template_id' => $template->id,
            'channel' => config('sms.driver'),
        ]);

        info('SendDebtSmsCommand: SMS record created', [
            'student_id' => $student->id,
            'sms_id' => $sms->id,
            'phone' => $sms->phone,
            'character_count' => $sms->character_count,
        ]);

        // Attach to student's first branch
        try {
            $studentBranchId = $student->branches()->first()?->id;
            if ($studentBranchId) {
                $sms->branches()->attach($studentBranchId);
                info('SendDebtSmsCommand: SMS attached to student branch', [
                    'student_id' => $student->id,
                    'sms_id' => $sms->id,
                    'branch_id' => $studentBranchId,
                ]);
            } else {
                info('SendDebtSmsCommand: No branch found for student', [
                    'student_id' => $student->id,
                    'sms_id' => $sms->id,
                ]);
            }
        } catch (\Exception $e) {
            info('SendDebtSmsCommand: Failed to attach SMS to branch', [
                'student_id' => $student->id,
                'sms_id' => $sms->id,
                'error' => $e->getMessage(),
            ]);
        }

        info('SendDebtSmsCommand: SMS record ready for bulk dispatch', [
            'student_id' => $student->id,
            'sms_id' => $sms->id,
            'phone' => $sms->phone,
        ]);

        return $sms->id;
    }

    /**
     * Replace template variables with actual values
     */
    private function replaceTemplateVariables(string $content, Student $student, int $totalDebt, string $debtGroups): string
    {
        $studyCenter = Settings::where('key', 'name')->first()?->value ?? config('app.name');
        $phone = Settings::where('key', 'phone')->first()?->value;

        // Replace uppercase variables (primary format)
        $content = str_replace('{STUDENT}', $student->getFullName(), $content);
        $content = str_replace('{AMOUNT}', number_format($totalDebt, 0, '', ' '), $content);
        $content = str_replace('{GROUPS}', $debtGroups, $content);
        $content = str_replace('{STUDY_CENTER}', $studyCenter, $content);
        $content = str_replace('{PHONE}', $phone ? '+998'.$phone : '-', $content);
        $content = str_replace('{DATE}', Carbon::now()->format('d.m.Y'), $content);
        $content = str_replace('{TIME}', Carbon::now()->format('H:i'), $content);

        // Support lowercase variants for backward compatibility
        $content = str_replace('{student}', $student->getFullName(), $content);
        $content = str_replace('{amount}', number_format($totalDebt, 0, '', ' '), $content);
        $content = str_replace('{groups}', $debtGroups, $content);
        $content = str_replace('{study_center}', $studyCenter, $content);
        $content = str_replace('{phone}', $phone ? '+998'.$phone : '-', $content);
        $content = str_replace('{date}', Carbon::now()->format('d.m.Y'), $content);
        $content = str_replace('{time}', Carbon::now()->format('H:i'), $content);

        return $content;
    }

    /**
     * Get student's phone number (prefer parent phone for minors)
     */
    private function getStudentPhone(Student $student): ?string
    {
        // If student is under 18 and has parent phone, use parent phone
        if ($student->getAge() < 18 && $student->parent_phone) {
            return $this->formatPhone($student->parent_phone);
        }

        // Otherwise use student's phone
        if ($student->phone) {
            return $this->formatPhone($student->phone);
        }

        // Fallback to parent phone if available
        if ($student->parent_phone) {
            return $this->formatPhone($student->parent_phone);
        }

        return null;
    }

    /**
     * Format phone number for SMS sending
     */
    private function formatPhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ensure it starts with 998 (Uzbekistan country code)
        if (strlen($phone) === 9) {
            $phone = '998'.$phone;
        } elseif (strlen($phone) === 12 && ! str_starts_with($phone, '998')) {
            $phone = '998'.substr($phone, -9);
        }

        return $phone;
    }

    /**
     * Update the last sent time
     */
    private function updateLastSentTime(): void
    {
        Settings::updateOrCreate(
            ['key' => 'debt_sms_last_sent'],
            ['value' => Carbon::now()->toDateTimeString()]
        );
    }
}
