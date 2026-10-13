<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Jobs\PayWithBalance;
use App\Models\Account;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupStudentLog;
use App\Models\Lead;
use App\Models\PaymentType;
use App\Models\Student;
use App\Models\StudentDeleteInfo;
use App\Models\StudentGroupPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class StudentService extends BaseService
{
    public function __construct(Student $student)
    {
        $this->model = $student;
    }

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $data = $data->all();
            $data['account_id'] = $this->createAccount($data);
            $data['phone'] = $this->formatPhone($data['phone']);
            $data['phone2'] = $this->formatNullablePhone($data['phone2'] ?? null);
            if (isset($data['parent_phone'])) {
                $data['parent_phone'] = $this->formatPhone($data['parent_phone']);
            }
            $entity = $this->model::create($data);

            // Use safelyAttachBranch instead of direct attach
            $currentBranchId = auth()->user()->current_branch_id;
            $entity->safelyAttachBranch($currentBranchId);

            // Force cache clearing for this branch
            StudentCacheService::clearCache($currentBranchId);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    public function update($dto, $entity)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $existingGroupIds = $entity->groups()->pluck('groups.id')->all();
            $data['phone'] = $this->formatPhone($data['phone']);
            $data['phone2'] = $this->formatNullablePhone($data['phone2'] ?? null);
            if (isset($data['parent_phone'])) {
                $data['parent_phone'] = $this->formatPhone($data['parent_phone']);
            }
            $entity->update($data);

            // Store current branch ID before clearing
            $currentBranchId = auth()->user()->current_branch_id;

            // Instead of detach and attach, check if branch exists first
            if (! $entity->branches()->where('branch_id', $currentBranchId)->exists()) {
                $entity->branches()->attach($currentBranchId);
            }

            $this->updateAccount($entity);

            if (isset($data['attached_at'])) {
                $entity->groups()->detach();
                foreach ($data['attached_at'] as $group_id => $attached_at) {
                    $entity->groups()->attach($group_id, ['attached_at' => $attached_at]);
                }

                $affectedGroupIds = array_values(array_unique([
                    ...$existingGroupIds,
                    ...array_keys($data['attached_at']),
                ]));

                $affectedGroups = Group::whereIn('id', $affectedGroupIds)->get();
                StudentCacheService::invalidateAfterStudentGroupMembershipChange($entity->fresh(), $affectedGroups);
            }

            // Force cache clearing for this branch
            StudentCacheService::clearCache($currentBranchId);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    public function restore($id): void
    {
        $item = $this->model::withTrashed()->findOrFail($id);
        StudentDeleteInfo::where('student_id', $id)->delete();

        // Activate student account
        if ($item->account) {
            $item->account->update([
                'status' => 'active',
            ]);
        }
        $item->restore();

        // Clear cache for all branches this student belongs to
        foreach ($item->branches as $branch) {
            StudentCacheService::clearCache($branch->id);
        }

        // Also clear cache for current branch
        if (auth()->check()) {
            StudentCacheService::clearCache(auth()->user()->current_branch_id);
        }
    }

    public function getGroups()
    {
        $query = Group::with('teacher')
            ->where('state', '!=', 'completed')
            ->orderBy('name');

        if (auth()->user()->hasRole('Teacher')) {
            $query->where('teacher_id', auth()->id());
        }

        return $query->get(['id', 'name', 'teacher_id']);
    }

    public function getUnpaidMonths($id, $method = null): \Illuminate\Database\Eloquent\Collection|array
    {
        if ($method === 'paymentEdit') {
            return StudentGroupPayment::with(['student', 'group' => function ($query) {
                $query->withTrashed()->with(['teacher']);
            }])
                ->where('student_id', $id)
                ->where('left_amount', '>', 0)
                ->get();
        } else {
            return StudentGroupPayment::with(['student', 'group' => function ($query) {
                $query->withTrashed()->with(['teacher']);
            }])
                ->where('student_id', $id)
                ->where('left_amount', '>', 0)
                ->get();
        }
    }

    public function getAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return Account::all();
    }

    public function getThisMonthAttendance($id): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $data['attendanceCount'] = Attendance::where('student_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->count();
        $data['absentCount'] = Attendance::where('student_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'absent')
            ->count();
        $data['presentCount'] = Attendance::where('student_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', 'present')
            ->count();

        return $data;
    }

    public function getStudentGroupPayments($id): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return StudentGroupPayment::with(['group' => function ($query) {
            $query->withTrashed()->with(['teacher']);
        }])
            ->where('student_id', $id)
            ->latest()
            ->paginate(12);
    }

    public function getGroupLogs($id)
    {
        $query = GroupStudentLog::where('student_id', $id)
            ->with(['group' => function ($query) {
                $query->withTrashed(); // Include soft deleted groups
            }]);

        if (auth()->user()->hasRole('Teacher')) {
            $teacherGroups = auth()->user()->groups()->pluck('id');
            $query->whereIn('group_id', $teacherGroups);
        }

        return $query->latest('id')->get();
    }

    public function getPaymentTypes()
    {
        return PaymentType::all();
    }

    public function getStudentGroupAttachedTimes($id): \Illuminate\Support\Collection
    {
        return $this->find($id)->groups()->with(['groupTimes'])->get();
    }

    private function createAccount($data)
    {
        $account = Account::create([
            'name' => $data['name'].' '.$data['surname'],
            'account_code' => AccountEnum::STUDENT->value,
        ]);

        return $account->id;
    }

    private function updateAccount($entity): void
    {
        if ($entity->account) {
            $entity->account->update([
                'name' => $entity->name.' '.$entity->surname,
            ]);
        }
    }

    public function getBranches(): \Illuminate\Database\Eloquent\Collection
    {
        return Branch::all();
    }

    public function getSms($id)
    {
        return $this->findWithTrashed($id)->sms()->latest()->paginate(12);
    }

    public function getSmsCount($id)
    {
        return $this->findWithTrashed($id)->sms()->count();
    }

    public function getFamiliarsCount($phone)
    {
        return Student::where('phone', $phone)->count();
    }

    private function formatPhone($phone)
    {
        //remove + () - from phone
        //remove if length is greater than 9 first 998
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) > 9) {
            $phone = substr($phone, 3);
        }

        return $phone;
    }

    private function formatNullablePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        return $this->formatPhone($phone);
    }

    public function destroy($id, $comment): void
    {
        $entity = $this->model::findOrFail($id);

        // Store branch IDs before deleting
        $branchIds = $entity->branches ? $entity->branches->pluck('id')->toArray() : [];

        $entity->deleteInfo()->create([
            'deleted_by' => auth()->id(),
            'comment' => $comment,
            'deleted_at' => now(),
        ]);

        // Disable student account if it exists
        if ($entity->account) {
            $entity->account->update([
                'status' => 'inactive',
            ]);
        }

        $entity->delete();

        // Clear cache for all branches this student belonged to
        foreach ($branchIds as $branchId) {
            StudentCacheService::clearCache($branchId);
        }

        // Also clear cache for current branch
        if (auth()->check()) {
            StudentCacheService::clearCache(auth()->user()->current_branch_id);
        }
    }

    public function forceDelete($id): void
    {
        $item = $this->model::withTrashed()->findOrFail($id);

        // Store branch IDs before deleting
        $branchIds = $item->branches ? $item->branches->pluck('id')->toArray() : [];

        $item->deleteInfo()->delete();
        $item->attendances()->withTrashed()->forceDelete();
        $item->groupPayments()->withTrashed()->forceDelete();
        if ($item->account) {
            $item->account->delete();
        }
        $item->forceDelete();

        // Clear cache for all branches this student belonged to
        foreach ($branchIds as $branchId) {
            StudentCacheService::clearCache($branchId);
        }

        // Also clear cache for current branch
        if (auth()->check()) {
            StudentCacheService::clearCache(auth()->user()->current_branch_id);
        }
    }

    public function getDeleteInfo($id)
    {
        return StudentDeleteInfo::with('deletedBy')->where('student_id', $id)->first();
    }

    public function isTodayBirthday($birthday)
    {
        return date('m-d', strtotime($birthday)) === date('m-d');
    }

    public function refreshBalance($id)
    {
        PayWithBalance::dispatch([$id]);
    }

    public function getStudentGroups($id)
    {
        $query = $this->findWithTrashed($id)->groups()->with(['groupTimes']);

        if (auth()->user()->hasRole('Teacher')) {
            $query->where('teacher_id', auth()->id());
        }

        return $query->get();
    }

    public function getDebtAmount($id)
    {
        $query = StudentGroupPayment::select('left_amount')
            ->where('student_id', $id)
            ->where('left_amount', '>', 0);

        if (auth()->user()->hasRole('Teacher')) {
            $teacherGroups = auth()->user()->groups()->pluck('id');
            $query->whereIn('group_id', $teacherGroups);
        }

        return $query->sum('left_amount') ?? 0;
    }

    public function getLeadData($id)
    {
        $student = $this->findWithTrashed($id);
        $lead = Lead::with([
            'comments.user',
            'calls.causer',
            'reminders',
            'causer',
            'collection',
        ])->withTrashed()->where('id', $student->lead_id)->first();

        return $lead;
    }

    public function checkPermission($id)
    {
        if (! is_numeric($id)) {
            abort(404, 'Invalid student ID');
        }

        $student = Student::withTrashed()->withoutGlobalScope('branch')->find($id);

        if (! $student) {
            abort(404, 'Student not found');
        }

        $user = auth()->user();

        if ($user->hasRole('Teacher')) {
            // Check if the student is in any of the teacher's groups
            $teacherGroupIds = $user->groups()->pluck('id')->toArray();
            $studentInTeacherGroup = $student->groups()->whereIn('group_id', $teacherGroupIds)->exists();

            // Check if the teacher created this student
            $teacherCreatedStudent = $user->id === $student->created_by_id;

            if (! $studentInTeacherGroup && ! $teacherCreatedStudent) {
                abort(403, 'This student does not belong to any of your groups and was not created by you');
            }
        } else {
            // For admin users, check if they have access to the student's branch
            $studentBranches = $student->branches()->pluck('branch_id')->toArray();

            // If the student has no branches, check if the user created this student
            if (empty($studentBranches) && $user->id !== $student->created_by_id) {
                abort(403, 'This student does not belong to any branch and was not created by you');
            }

            // Check if the user has access to any of the student's branches
            $userHasAccessToBranch = false;

            if (! empty($studentBranches)) {
                $userBranches = $user->branches()->pluck('branch_id')->toArray();
                $userHasAccessToBranch = ! empty(array_intersect($studentBranches, $userBranches));

                if (! $userHasAccessToBranch) {
                    abort(403, 'You do not have access to this student\'s branch');
                }

                // Update user's current branch to match the student's branch if different
                $studentBranch = $student->branches()->first();
                if ($studentBranch && $user->current_branch_id !== $studentBranch->id) {
                    $user->update(['current_branch_id' => $studentBranch->id]);
                    session()->flash('success_alert', __('translation.alert_messages.branch_changed'));
                }
            }
        }

        return true;
    }

    public function createDebt($studentId, array $data): void
    {
        DB::beginTransaction();

        try {
            $student = $this->model::findOrFail($studentId);
            $group = Group::findOrFail($data['group_id']);

            // Check if student is in this group
            if (! $student->groups->contains($group)) {
                throw new Exception(__('Student is not in this group'));
            }

            $amountValue = removeComma($data['amount']) ?? $data['amount'];
            $amount = (int) $amountValue;
            $range = $group->payment_type === 'one_time'
                ? 'one_time/'.$student->id.'/'.now()->format('Y-m-d')
                : $data['range'];

            StudentGroupPayment::create([
                'student_id' => $student->id,
                'group_id' => $group->id,
                'date' => now(),
                'amount' => $amount,
                'left_amount' => $amount,
                'range' => $range,
            ]);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function getGroupPaymentRanges($groupId): array
    {
        $group = Group::findOrFail($groupId);

        // Get existing payment ranges for this group
        $existingRanges = StudentGroupPayment::where('group_id', $groupId)
            ->distinct()
            ->orderBy('range', 'desc')
            ->pluck('range')
            ->toArray();

        // Calculate next available range based on payment type
        $nextRange = $this->calculateNextDebtRange($group);

        // Combine existing ranges with next range if not already present
        $allRanges = array_unique(array_merge([$nextRange], $existingRanges));

        // Format ranges for display
        $formattedRanges = [];
        foreach ($allRanges as $range) {
            if ($range === 'one_time' || str_starts_with($range, 'one_time/')) {
                $formattedRanges[] = [
                    'value' => $range,
                    'label' => __('translation.groups.one_time'),
                ];

                continue;
            }

            $parts = explode('/', $range);
            if (is_numeric($parts[0])) {
                // Lesson count range (e.g., "1/10")
                $formattedRanges[] = [
                    'value' => $range,
                    'label' => $range.' ('.__('translation.groups.lessons').')',
                ];
            } else {
                // Date range (e.g., "2024-01-01/2024-02-01")
                try {
                    $startDate = \Carbon\Carbon::parse($parts[0]);
                    $endDate = \Carbon\Carbon::parse($parts[1]);
                    $formattedRanges[] = [
                        'value' => $range,
                        'label' => dateFormat($startDate, 'd F').' - '.dateFormat($endDate, 'd F'),
                    ];
                } catch (\Exception $e) {
                    $formattedRanges[] = [
                        'value' => $range,
                        'label' => $range,
                    ];
                }
            }
        }

        return $formattedRanges;
    }

    private function calculateNextDebtRange(Group $group): string
    {
        try {
            return match ($group->payment_type) {
                'by_count_lessons' => $this->getDebtRangeByCountLessons($group->lesson_count, $group->getCurrentLesson()),
                'one_time' => 'one_time',
                'date_to_date' => $this->getDebtRangeByDateToDate($group->start_date, now()),
                default => $this->getDebtRangeByDateToDate($group->start_date ?? now(), now()),
            };
        } catch (\Exception $e) {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();

            return $startDate->format('Y-m-d').'/'.$endDate->format('Y-m-d');
        }
    }

    private function getDebtRangeByCountLessons($groupLessonCount, $currentLessonCount): string
    {
        if (! $groupLessonCount) {
            return now()->startOfMonth()->format('Y-m-d').'/'.now()->endOfMonth()->format('Y-m-d');
        }

        $startCount = (intval(($currentLessonCount) / $groupLessonCount) * $groupLessonCount) + 1;
        $endCount = $startCount + $groupLessonCount - 1;

        return "$startCount/$endCount";
    }

    private function getDebtRangeByDateToDate($groupStartDate, $currentDate): string
    {
        if (! $groupStartDate) {
            $groupStartDate = $currentDate->copy()->startOfMonth();
        }

        $startDay = $groupStartDate->day;
        $startDate = $currentDate->copy()->day($startDay);

        if ($startDate->greaterThan($currentDate) && $startDate->greaterThan($groupStartDate)) {
            $startDate = $startDate->subMonth();
        }

        $endDate = $startDate->copy()->addMonth();

        return $startDate->format('Y-m-d').'/'.$endDate->format('Y-m-d');
    }
}
