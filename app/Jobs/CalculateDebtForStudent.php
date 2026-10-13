<?php

namespace App\Jobs;

use App\Models\Group;
use App\Models\StudentGroupPayment;

class CalculateDebtForStudent extends BaseJob
{
    /**
     * Create a new job instance.
     */
    public function __construct(
        private $groups = null,
        private $amount = null,
    ) {
        info('CalculateDebtForStudent');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->groups == null) {
            $this->groups = Group::where('state', 'active')->get();
        }

        foreach ($this->groups as $group) {
            if ($group->state !== 'active') {
                continue;
            }

            if ($group->start_date && $group->start_date->greaterThan(now())) {
                continue;
            }

            if ($group->payment_type === 'one_time') {
                $this->calculateOneTimeDebt($group);

                continue;
            }

            $this->calculateDebt($group);
        }
    }

    public function calculateDebt($group): void
    {
        $range = $this->getDebtRange($group);
        $students = $group->students;
        foreach ($students as $student) {
            $has_debt = StudentGroupPayment::where('group_id', $group->id)
                ->where('student_id', $student->id)
                ->where('range', $range)->count();
            if (! $has_debt) {
                $this->createDebt($student, $group, $range);
            }
        }
    }

    public function calculateOneTimeDebt($group): void
    {
        $students = $group->students;
        foreach ($students as $student) {
            $hasDebt = StudentGroupPayment::where('group_id', $group->id)
                ->where('student_id', $student->id)
                ->exists();

            if (! $hasDebt) {
                $this->createDebt($student, $group, $this->getOneTimeDebtRange($student->id));
            }
        }
    }

    public function createDebt($student, $group, $range): StudentGroupPayment
    {
        if (! $this->amount) {
            $amount = $student->pivot->amount ?? $group->price;
        } else {
            $amount = $this->amount;
        }

        return StudentGroupPayment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'date' => now(),
            'amount' => $amount,
            'left_amount' => $amount,
            'range' => $range,
        ]);
    }

    public function getOneTimeDebtRange(int|string $studentId): string
    {
        return 'one_time/'.$studentId.'/'.now()->format('Y-m-d');
    }

    public function getDebtRange($group): string
    {
        // Determine payment type if not set
        $paymentType = $this->determinePaymentType($group);

        return match ($paymentType) {
            'by_count_lessons' => $this->getDebtRangeByCountLessons($group->lesson_count, $group->getCurrentLesson()),
            'one_time' => 'one_time',
            default => $this->getDebtRangeByDateToDate($group->start_date, now()),
        };
    }

    private function determinePaymentType($group): string
    {
        // If payment_type is not null, use it
        if ($group->payment_type !== null) {
            return $group->payment_type;
        }

        // Payment type is null, determine it based on other fields
        $paymentType = 'date_to_date'; // default
        $startDate = now()->startOfMonth(); // default start date

        // Check if start_date exists
        if ($group->start_date) {
            $paymentType = 'date_to_date';
        }
        // Check if lessons_count is not null
        elseif ($group->lesson_count) {
            $paymentType = 'by_count_lessons';
        }
        // If nothing is set, use date_to_date and set start_date to current month first date
        else {
            $paymentType = 'date_to_date';
            $startDate = now()->startOfMonth();
        }

        // Update the group with determined payment type and start date
        $group->update([
            'payment_type' => $paymentType,
            'start_date' => $startDate,
        ]);

        return $paymentType;
    }

    public function getDebtRangeByCountLessons($group_lesson_count, $current_lesson_count): string
    {
        $start_count = (intval(($current_lesson_count) / $group_lesson_count) * $group_lesson_count) + 1;
        $end_count = $start_count + $group_lesson_count - 1;

        return "$start_count/$end_count";
    }

    public function getDebtRangeByDateToDate($group_start_date, $current_date): string
    {
        $start_day = $group_start_date->day;
        $start_date = $current_date->copy()->day($start_day);
        if ($start_date->greaterThan($current_date) && $start_date->greaterThan($group_start_date)) {
            $start_date = $start_date->subMonth();
        }
        $end_date = $start_date->copy()->addMonth();

        return $start_date->format('Y-m-d').'/'.$end_date->format('Y-m-d');
    }
}
