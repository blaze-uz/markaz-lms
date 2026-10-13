<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupStudentLog;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class GroupAttachService
{
    /**
     * @return array|bool
     */
    public function attachGroup(array $data)
    {
        DB::beginTransaction();

        try {
            $first_month_debt_amount = (isset($data['first_month_debt_amount']) && $data['first_month_debt_amount']) ? removeComma($data['first_month_debt_amount']) : null;
            $group_agreed_amount = (isset($data['group_agreed_amount']) && $data['group_agreed_amount']) ? removeComma($data['group_agreed_amount']) : null;

            if ($first_month_debt_amount && ! is_numeric($first_month_debt_amount)) {
                DB::rollBack();
                info('GroupAttachService: Debt amount must be numeric', [
                    'first_month_debt_amount' => $first_month_debt_amount,
                ]);

                return ['success' => false, 'message' => __('Debt amount must be numeric')];
            }
            if ($group_agreed_amount && ! is_numeric($group_agreed_amount)) {
                DB::rollBack();
                info('GroupAttachService: Agreed debt amount must be numeric', [
                    'group_agreed_amount' => $group_agreed_amount,
                ]);

                return ['success' => false, 'message' => __('Agreed debt amount must be numeric')];
            }

            $students = Student::whereIn('id', $data['student_ids'])->get();
            $group = Group::find($data['group_id']);

            if (! $group) {
                DB::rollBack();
                info('GroupAttachService: Group not found', [
                    'group_id' => $data['group_id'],
                ]);

                return ['success' => false, 'message' => __('Group not found')];
            }

            // Validate group has required fields for debt calculation
            if (is_null($group->payment_type)) {
                info('GroupAttachService: Group payment_type is null, defaulting to date_to_date', [
                    'group_id' => $group->id,
                    'group_name' => $group->name ?? 'Unknown',
                ]);
            }

            // Check if any student is already in the group
            $alreadyAttachedStudents = $students->filter(function ($student) use ($group) {
                return $student->groups->contains($group);
            });

            if ($alreadyAttachedStudents->isNotEmpty()) {
                $studentNames = $alreadyAttachedStudents->pluck('name')->implode(', ');
                DB::rollBack();
                info('GroupAttachService: Students already in group', [
                    'student_names' => $studentNames,
                    'group_id' => $group->id,
                ]);

                return ['success' => false, 'message' => __('Students already in group: :studentNames', ['studentNames' => $studentNames])];
            }

            // Attach students to group
            foreach ($students as $student) {
                $student->groups()->attach($group, [
                    'attached_at' => $data['attached_at'] ?? now(),
                    'amount' => $group_agreed_amount,
                    'comment' => $data['comment'] ?? null,
                ]);

                GroupStudentLog::create([
                    'group_id' => $group->id,
                    'student_id' => $student->id,
                    'action' => 'attach',
                    'date' => $data['attached_at'] ?? now(),
                    'causer_id' => auth()->id(),
                ]);

                // Create old period debts if requested
                if (isset($data['create_old_period_debts']) && $data['create_old_period_debts']) {
                    $this->createOldPeriodDebts($student, $group, $data['attached_at'] ?? now(), $group_agreed_amount);
                }

                // Create current period debt
                if (isset($data['first_month_debt_amount']) && $data['first_month_debt_amount']) {
                    $this->addDebt($student, $group, removeComma($data['first_month_debt_amount']));
                } else {
                    $payment_amount = $group_agreed_amount ?? $group->price;
                    $this->addDebt($student, $group, $payment_amount);
                }
            }

            DB::commit();

            StudentCacheService::invalidateAfterStudentGroupMembershipChange($students, $group);

            return ['success' => true, 'message' => __('Students attached successfully')];
        } catch (Exception $exception) {
            DB::rollBack();
            info('GroupAttachService: Error attaching students', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * @return array|bool
     */
    public function detachGroupWithoutDebts(array $data)
    {
        DB::beginTransaction();

        try {
            $student = Student::with('groups')->find($data['student_id']);
            $group = Group::find($data['group_id']);

            if (! $student || ! $group) {
                DB::rollBack();

                return ['success' => false, 'message' => __('Student or group not found')];
            }

            // Detach the group from the student
            $student->groups()->detach($group->id);

            // Create a log entry
            GroupStudentLog::create([
                'group_id' => $group->id,
                'student_id' => $student->id,
                'action' => 'detach',
                'date' => now(),
                'causer_id' => auth()->id(),
            ]);

            DB::commit();

            StudentCacheService::invalidateAfterStudentGroupMembershipChange($student, $group);

            return ['success' => true, 'message' => __('Student detached successfully')];
        } catch (Exception $exception) {
            DB::rollBack();

            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    private function addDebt(mixed $student, $group, $amount): void
    {
        if ($group->state !== 'active') {
            return;
        }

        // For one_time groups each student gets exactly one debt; do not copy range from other students
        if ($group->payment_type === 'one_time') {
            if ($group->start_date && $group->start_date->greaterThan(now())) {
                return;
            }

            $hasDebt = StudentGroupPayment::where('group_id', $group->id)
                ->where('student_id', $student->id)
                ->exists();

            if ($hasDebt) {
                return;
            }

            $range = 'one_time/'.$student->id.'/'.now()->format('Y-m-d');
        } else {
            $lastStudentGroupPayment = StudentGroupPayment::where('group_id', $group->id)->latest()->first();
            $range = $lastStudentGroupPayment?->range ?? $this->getDebtRange($group);
        }

        StudentGroupPayment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'date' => now(),
            'amount' => $amount,
            'left_amount' => $amount,
            'range' => $range,
        ]);
    }

    private function getDebtRange($group): string
    {
        // payment_type: date_to_date, by_count_lessons, one_time
        try {
            return match ($group->payment_type) {
                'by_count_lessons' => $this->getDebtRangeByCountLessons($group->lesson_count, $group->getCurrentLesson()),
                'one_time' => 'one_time',
                default => $this->getDebtRangeByDateToDate($group->start_date ?? now(), now()),
            };
        } catch (Exception $e) {
            info('GroupAttachService: Error calculating debt range, using fallback', [
                'group_id' => $group->id,
                'payment_type' => $group->payment_type,
                'error' => $e->getMessage(),
            ]);

            // Fallback to a simple date range
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();

            return $startDate->format('Y-m-d').'/'.$endDate->format('Y-m-d');
        }
    }

    private function getDebtRangeByCountLessons($group_lesson_count, $current_lesson_count): string
    {
        $start_count = (intval(($current_lesson_count) / $group_lesson_count) * $group_lesson_count) + 1;
        $end_count = $start_count + $group_lesson_count - 1;

        return "$start_count/$end_count";
    }

    private function getDebtRangeByDateToDate($group_start_date, $current_date): string
    {
        // Handle null group_start_date
        if (! $group_start_date) {
            $group_start_date = $current_date->copy()->startOfMonth();
        }

        $start_day = $group_start_date->day;
        $start_date = $current_date->copy()->day($start_day);

        if ($start_date->greaterThan($current_date) && $start_date->greaterThan($group_start_date)) {
            $start_date = $start_date->subMonth();
        }

        $end_date = $start_date->copy()->addMonth();

        return $start_date->format('Y-m-d').'/'.$end_date->format('Y-m-d');
    }

    public function checkDebtExists(array $data): bool
    {
        $studentGroupPayment = StudentGroupPayment::where('student_id', $data['student_id'])
            ->where('group_id', $data['group_id'])
            ->where('left_amount', '>', 0)
            ->first();

        if ($studentGroupPayment) {
            return true;
        }

        return false;
    }

    public function getDebts(array $data)
    {
        return StudentGroupPayment::where('student_id', $data['student_id'])
            ->where('group_id', $data['group_id'])
            ->where('left_amount', '>', 0)
            ->get();
    }

    public function getGroup(array $data)
    {
        $group = Group::find($data['group_id']);
        if (! $group) {
            return null;
        }

        return $group;
    }

    public function getStudent(array $data)
    {
        return Student::findOrFail($data['student_id']);
    }

    public function detachGroupWithDebts(array $data): void
    {
        if (isset($data['debts'])) {
            // Delete the selected debts
            StudentGroupPayment::whereIn('id', $data['debts'])->delete();
        }

        // Detach the group from the student
        $this->detachGroupWithoutDebts($data);
    }

    /**
     * Create debts for old periods (previous months)
     *
     * @param  mixed  $student
     * @param  string  $attachedAt
     * @param  int|null  $groupAgreedAmount
     */
    private function createOldPeriodDebts($student, Group $group, $attachedAt, $groupAgreedAmount = null): void
    {
        if ($group->payment_type !== 'date_to_date') {
            return;
        }

        $attachedDate = \Carbon\Carbon::parse($attachedAt);
        $currentDate = now();

        // If attached in the current month, no old period debts needed
        if ($attachedDate->isSameMonth($currentDate)) {
            return;
        }

        $amount = $groupAgreedAmount ?? $group->price;
        $startDay = $group->start_date->day;
        $periodStart = $attachedDate->copy()->day($startDay);

        // Adjust if the start day is after the attached date in the same month
        if ($periodStart->greaterThan($attachedDate) && $periodStart->greaterThan($group->start_date)) {
            $periodStart->subMonth();
        }

        // Create debts for each old period (month)
        $periods = [];
        $tempPeriodStart = $periodStart->copy();
        $endOfOldPeriods = $currentDate->copy()->startOfMonth();

        while ($tempPeriodStart->lessThan($endOfOldPeriods)) {
            $periods[] = $tempPeriodStart->copy();
            $tempPeriodStart->addMonth();
        }
        foreach ($periods as $periodStartItem) {
            $periodEnd = $periodStartItem->copy()->addMonth();
            $range = $periodStartItem->format('Y-m-d').'/'.$periodEnd->format('Y-m-d');

            // Check if debt already exists for this range
            $existingDebt = StudentGroupPayment::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->where('range', $range)
                ->first();

            if (! $existingDebt) {
                StudentGroupPayment::create([
                    'student_id' => $student->id,
                    'group_id' => $group->id,
                    'date' => $periodStartItem->copy(),
                    'amount' => $amount,
                    'left_amount' => $amount,
                    'range' => $range,
                ]);
            }
        }
    }
}
