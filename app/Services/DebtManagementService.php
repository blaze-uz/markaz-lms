<?php

namespace App\Services;

use App\Jobs\CreateGroupDebtsJob;
use App\Models\Group;
use App\Models\StudentGroupPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtManagementService
{
    /**
     * Update group start date and handle all debt operations
     */
    public function updateGroupStartDate(Group $group, Request $request): array
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'create_old_debts' => 'sometimes|boolean',
            'delete_current_debts' => 'sometimes|boolean'
        ]);

        $oldStartDate = $group->start_date;
        $newStartDate = $request->start_date;

        DB::beginTransaction();
        try {
            // Update the group start date
            $group->start_date = $newStartDate;
            $group->save();

            $deletedCount = 0;
            $createdCount = 0;

            // Delete current debts if requested
            if ($request->has('delete_current_debts') && $request->delete_current_debts) {
                $deletedCount = $this->deleteCurrentDebts($group);
            }

            // Dispatch job to create debts for the new start date period
            CreateGroupDebtsJob::dispatch($group, $newStartDate, [
                'create_old_debts' => $request->has('create_old_debts') && $request->create_old_debts,
                'delete_current_debts' => $request->has('delete_current_debts') && $request->delete_current_debts
            ]);

            // Create old debts if requested (for dates before the new start date)
            if ($request->has('create_old_debts') && $request->create_old_debts) {
                $oldCreatedCount = $this->createOldDebts($group, $oldStartDate, $newStartDate);
            }

            DB::commit();

            // Get success message
            $messageKey = $this->getSuccessMessageKey($request);

            return [
                'success' => true,
                'message_key' => $messageKey,
                'deleted_count' => $deletedCount,
                'created_count' => 0, // Will be created by job
                'job_dispatched' => true
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get appropriate success message based on operations
     */
    private function getSuccessMessageKey(Request $request): string
    {
        $hasDelete = $request->has('delete_current_debts') && $request->delete_current_debts;
        $hasCreate = $request->has('create_old_debts') && $request->create_old_debts;

        if ($hasDelete && $hasCreate) {
            return "translation.alert_messages.start_date_updated_debts_reset_queued";
        } elseif ($hasDelete) {
            return "translation.alert_messages.start_date_updated_debts_deleted_queued";
        } elseif ($hasCreate) {
            return "translation.alert_messages.start_date_updated_with_debts_queued";
        } else {
            return "translation.alert_messages.start_date_updated_queued";
        }
    }
    /**
     * Delete current unpaid debts for the group
     */
    public function deleteCurrentDebts(Group $group): int
    {
        return StudentGroupPayment::where('group_id', $group->id)
            ->where('left_amount', '>', 0)
            ->delete();
    }

    /**
     * Create debts for the new start date period
     */
    public function createDebtsForNewStartDate(Group $group, $newStartDate): int
    {
        $newDate = Carbon::parse($newStartDate);
        $createdCount = 0;
        $students = $group->students;

        // Create debt from exact start date to same date next month
        $rangeStart = $newDate->copy();
        $rangeEnd = $newDate->copy()->addMonth(); // Same date next month

        foreach ($students as $student) {
            $createdCount += $this->createStudentDebtForMonth($group, $student, $rangeStart, $rangeEnd);
        }

        return $createdCount;
    }

    /**
     * Create debts for old date range
     */
    public function createOldDebts(Group $group, $oldStartDate, $newStartDate): int
    {
        if (!$oldStartDate || !$newStartDate) {
            return 0;
        }

        $oldDate = Carbon::parse($oldStartDate);
        $newDate = Carbon::parse($newStartDate);

        // Only create debts if new date is older than old date
        if ($newDate->gte($oldDate)) {
            return 0;
        }

        $createdCount = 0;
        $students = $group->students;

        // Calculate the date range for debt creation
        $startDate = $newDate->copy();
        $endDate = $oldDate->copy()->subDay(); // Day before the old start date

        // Create monthly debts for the period
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            $rangeStart = $currentDate->copy();
            $rangeEnd = $currentDate->copy()->addMonth(); // Same date next month

            // Don't create debts for future months
            if ($rangeStart->gt(now())) {
                $currentDate->addMonth();
                continue;
            }

            foreach ($students as $student) {
                $createdCount += $this->createStudentDebtForMonth($group, $student, $rangeStart, $rangeEnd);
            }

            $currentDate->addMonth();
        }

        return $createdCount;
    }

    /**
     * Create debt for a specific student and month
     */
    private function createStudentDebtForMonth(Group $group, $student, Carbon $monthStart, Carbon $monthEnd): int
    {
        // Check if debt already exists for this month
        $existingDebt = StudentGroupPayment::where('group_id', $group->id)
            ->where('student_id', $student->id)
            ->where('range', $monthStart->format('Y-m-d') . '/' . $monthEnd->format('Y-m-d'))
            ->first();

        if ($existingDebt) {
            return 0;
        }

        $amount = $student->pivot->amount ?? $group->price;

        StudentGroupPayment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'date' => $monthStart,
            'amount' => $amount,
            'left_amount' => $amount,
            'range' => $monthStart->format('Y-m-d') . '/' . $monthEnd->format('Y-m-d')
        ]);

        return 1;
    }

    /**
     * Get debtor students for a group
     */
    public function getDebtorStudents(Group $group)
    {
        return $group->students()->whereHas('groupPayments', function ($query) use ($group) {
            $query->where('group_id', $group->id)
                ->where('left_amount', '>', 0);
        })->get();
    }

    /**
     * Get debt count for a group
     */
    public function getDebtCount(Group $group): int
    {
        return StudentGroupPayment::where('group_id', $group->id)
            ->where('left_amount', '>', 0)
            ->count();
    }
}
