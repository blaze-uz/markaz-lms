<?php

namespace App\Services;

use App\Enums\AccountEnum;
use App\Jobs\CalculateDebtForStudent;
use App\Jobs\Group\PaymentNoticeJob;
use App\Jobs\Group\TrialLessonNoticeJob;
use App\Models\Account;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Group;
use App\Models\GroupAttendanceSettings;
use App\Models\GroupStudentLog;
use App\Models\GroupTime;
use App\Models\KanbanBoard;
use App\Models\KanbanCollection;
use App\Models\Settings;
use App\Models\Student;
use App\Models\StudentGroupPayment;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class GroupService extends BaseService
{
    public function __construct(Group $group)
    {
        $this->model = $group;
    }

    public function baseQuery(): Builder
    {
        return $this->model::query()->with(['course', 'teacher']);
    }

    public function find($id, $with = [])
    {
        if (! is_numeric($id)) {
            abort(404, 'Invalid ID');
        }

        return $this->model::with($with)->withTrashed()->findOrFail($id);
    }

    /**
     * @throws Exception
     */
    public function create($dto)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();

            $data['lesson_count'] = null;

            // The start_date field was removed from the create form, but the column is NOT NULL.
            // Fall back to the first day of the current month, matching the form's previous default.
            if (empty($data['start_date'])) {
                $data['start_date'] = now()->startOfMonth()->format('Y-m-d');
            }

            $account = $this->createAccount($data['name']);
            $data['account_id'] = $account->id;

            if ($data['teacher_percentage_switch'] !== 'on') {
                $data['teacher_percentage'] = null;
            }
            $entity = $this->model::create($data);

            $entity->branches()->attach($data['branch_id']);

            if ($data['create_lead_board_switch'] == 'on') {
                $this->createLeadBoard($entity);
            }

            // Check if day-specific time data is provided in the DTO
            $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $this->extractedGroupTime($daysOfWeek, $data, $entity);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    /**
     * Update group and related data
     *
     * @throws Exception
     */
    public function update($dto, $entity)
    {
        DB::beginTransaction();

        try {
            $data = $dto->all();
            $this->updateGroupData($data, $entity);
            $this->syncGroupBranches($data, $entity);
            $this->updateGroupDebtAndTime($data, $entity);
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }

        DB::commit();

        return $entity;
    }

    /**
     * Update basic group data and payment settings
     */
    private function updateGroupData(array $data, $entity): void
    {
        $data = $this->updatePaymentSettings($data, $entity);
        $entity->update($data);
        $this->updateAccount($entity);
    }

    private function syncGroupBranches(array $data, $entity): void
    {
        $entity->branches()->sync([$data['branch_id']]);

        // Update each student's branches, including students currently visible in another branch.
        $students = $entity->students()->withoutGlobalScopes()->get();
        foreach ($students as $student) {
            $studentBranchIds = $this->getStudentAllBranchIds($student, $entity->id, $data['branch_id']);
            $student->branches()->sync($studentBranchIds);
        }
    }

    /**
     * Get all branch IDs for a student including current group's branch
     */
    private function getStudentAllBranchIds($student, int $currentGroupId, int $primaryBranchId): array
    {
        $branchIds = [$primaryBranchId]; // Start with current group's branch

        // Get branches from other groups
        $otherGroups = $student->groups()
            ->withoutGlobalScopes()
            ->where('groups.id', '!=', $currentGroupId)
            ->with('branches')
            ->get();

        foreach ($otherGroups as $group) {
            $branchIds = array_merge($branchIds, $group->branches->pluck('id')->toArray());
        }

        return array_unique($branchIds);
    }

    /**
     * Update group debt calculations and time settings
     */
    private function updateGroupDebtAndTime(array $data, $entity): void
    {
        dispatch(new CalculateDebtForStudent([$entity]));

        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $entity->groupTimes()->delete();
        $this->extractedGroupTime($daysOfWeek, $data, $entity);
    }

    public function updatePaymentSettings($data, $entity)
    {
        if (empty($data['payment_type'])) {
            $data['payment_type'] = $entity->payment_type;
            $data['start_date'] = $entity->start_date;
            $data['lesson_count'] = $entity->lesson_count;
            $data['duration_months'] = $entity->duration_months;

            return $data;
        }

        // check if payment type is changed
        if ($entity->payment_type != $data['payment_type'] || $entity->start_date != $data['start_date'] || $entity->lesson_count != $data['lesson_count']) {
            // check has attendances or payments
            if ($entity->attendances()->count() || $entity->studentGroupPayments()->where('left_amount', '<>', $entity->price)->count()) {
                return $data;
            }
        }
        $entity->studentGroupPayments()->delete();

        if ($data['payment_type'] == 'date_to_date') {
            $data['start_date'] = $data['start_date'] ?? now();
            $data['lesson_count'] = null;
            $data['duration_months'] = null;
        } elseif ($data['payment_type'] == 'one_time') {
            $data['start_date'] = $data['start_date'] ?? now();
            $data['lesson_count'] = null;
        } else {
            $data['start_date'] = now();
            $data['duration_months'] = null;
        }

        return $data;
    }

    /**
     * Switch a group between recurring (monthly / by lesson count) and one-time debt writing.
     * Reuses updatePaymentSettings' existing guard against changing already-billed periods.
     */
    public function updateBillingFrequency(Group $group, string $frequency): void
    {
        if ($frequency === 'one_time') {
            $paymentType = 'one_time';
        } else {
            $paymentType = in_array($group->payment_type, ['date_to_date', 'by_count_lessons'], true)
                ? $group->payment_type
                : 'date_to_date';
        }

        if ($group->payment_type === $paymentType) {
            return;
        }

        $data = $this->updatePaymentSettings([
            'payment_type' => $paymentType,
            'start_date' => optional($group->start_date)->format('Y-m-d'),
            'lesson_count' => $group->lesson_count,
            'duration_months' => $group->duration_months,
        ], $group);

        $group->update($data);

        dispatch(new CalculateDebtForStudent([$group]));
    }

    protected function extractedGroupTime(array $daysOfWeek, $data, $entity): void
    {
        foreach ($daysOfWeek as $day) {
            if (isset($data[$day])) {
                if (! $data[$day]['start'] || ! $data[$day]['end']) {
                    continue;
                }
                // Create a new GroupTime record for the day
                $entity->groupTimes()->create([
                    'name' => $day,
                    'start' => $data[$day]['start'],
                    'end' => $data[$day]['end'],
                    'room_id' => $data[$day]['room_id'],
                ]);
            }
        }
    }

    public function getTeachers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Teacher');
            })
            ->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            })
            ->with('media')
            ->get();
    }

    public function getTodayAttendance($id): bool
    {
        $attendance = Attendance::where('group_id', $id)->whereDate('date', date('Y-m-d'))->first();
        if ($attendance) {
            return true;
        }

        return false;
    }

    public function getCalendarEvents($id): array
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $colors = [
            'monday' => '#ff0000',
            'tuesday' => '#00ff00',
            'wednesday' => '#0000ff',
            'thursday' => '#ffff00',
            'friday' => '#ff00ff',
            'saturday' => '#00fff',
            'sunday' => '#000000',
        ];

        $textColors = [
            'monday' => '#ffffff',
            'tuesday' => '#ffffff',
            'wednesday' => '#ffffff',
            'thursday' => '#000000',
            'friday' => '#ffffff',
            'saturday' => '#000000',
            'sunday' => '#ffffff',
        ];

        $events = [];

        // Loop through the days in the specified date range
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            // Determine the day of the week (e.g., Monday, Tuesday) based on the date
            $dayOfWeek = date('l', strtotime($currentDate));

            // Retrieve the data for the specific day of the week
            $data = GroupTime::where('group_id', $id)
                ->where('name', strtolower($dayOfWeek)) // 'name' contains the day of the week
                ->first();

            if ($data) {
                $events[] = [
                    'id' => rand(1, 100),
                    'start' => "$currentDate $data->start:00",
                    'end' => "$currentDate $data->end:00",
                    'title' => $data->start.' - '.$data->end,
                ];
            }

            // Move to the next day
            $currentDate = date('Y-m-d', strtotime($currentDate.' +1 day'));
        }

        return $events;
    }

    /**
     * @throws Exception
     */
    public function storeDebt(array $data, $id)
    {
        return 'ishlab chiqish jarayonida!!!';
        $date = date('Y-m-d', strtotime($data['date']));
        $studentGroupPayment = removeComma($data['amount']);
        $amount = removeComma($data['amount']);
        $students = Student::whereIn('id', $data['students'])->get();
        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $amount = $studentGroupPayment;
                $amount = $this->extractedDebtStore($student, $amount);

                $student->groupPayments()->create([
                    'group_id' => $id,
                    'amount' => $studentGroupPayment,
                    'left_amount' => $amount,
                    'date' => $date,
                ]);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function getStudents($group_id = null)
    {
        if ($group_id) {
            $group = Group::findOrFail($group_id);
            //students must be not in this group
            $students = Student::whereNotIn('id', $group->students->pluck('id'))->get();
        } else {
            $students = Student::all();
        }

        return $students;
    }

    /**
     * @throws Exception
     */
    public function startGroup(array $data, $id): void
    {
        $group = $this->find($id);
        $price = removeComma($data['start_payment_amount']);

        //delete attached boards
        $group->boards()->delete();

        //already attached students
        $groupStudentsIds = $group->students->pluck('id');

        //remaining students
        $studentsIds = $data['students'];

        DB::beginTransaction();
        try {
            // detach students which are not in the remaining students
            $detachStudentsIds = $groupStudentsIds->diff($studentsIds);
            $detachedStudents = Student::whereIn('id', $detachStudentsIds)->get();
            foreach ($detachStudentsIds as $studentId) {
                $group->students()->detach($studentId);

                //group student log
                GroupStudentLog::create([
                    'group_id' => $group->id,
                    'student_id' => $studentId,
                    'action' => 'detach',
                    'date' => date('Y-m-d'),
                ]);
            }

            //update group amount and payment settings
            $group->update([
                'payment_type' => $data['payment_type'],
                'start_date' => $data['start_date'] ?? null,
                'lesson_count' => $data['lesson_count'] ?? null,
                'duration_months' => $data['duration_months'] ?? null,
                'state' => 'active',
            ]);

            cache()->forget('groupCounts');

            if ($data['payment_type'] === 'one_time') {
                if ($group->start_date?->lessThanOrEqualTo(now())) {
                    $this->createOneTimeDebtsForStartingGroup($group, $studentsIds, $price);
                }
            } elseif ($group->start_date?->lessThanOrEqualTo(now())) {
                CalculateDebtForStudent::dispatch([$group], $price);
            }

            DB::commit();

            if ($detachedStudents->isNotEmpty()) {
                StudentCacheService::invalidateAfterStudentGroupMembershipChange($detachedStudents, $group);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * One-time debts are normally created on attach, but {@see GroupAttachService::addDebt}
     * skips while the group is not active (e.g. demo). On start, create the single row for each
     * remaining student that still has no group payment, using the same amount as other types on start.
     *
     * @param  array|\Illuminate\Support\Collection  $studentIds
     */
    private function createOneTimeDebtsForStartingGroup(Group $group, $studentIds, mixed $amount): void
    {
        $ids = collect($studentIds)->filter()->values();
        if ($ids->isEmpty()) {
            return;
        }

        foreach ($ids as $studentId) {
            if (StudentGroupPayment::query()
                ->where('group_id', $group->id)
                ->where('student_id', $studentId)
                ->exists()) {
                continue;
            }

            $range = 'one_time/'.$studentId.'/'.now()->format('Y-m-d');

            StudentGroupPayment::create([
                'student_id' => $studentId,
                'group_id' => $group->id,
                'date' => now(),
                'amount' => $amount,
                'left_amount' => $amount,
                'range' => $range,
            ]);
        }
    }

    public function getDebtStudentsCount($id): int
    {
        $item = $this->find($id, ['students.groupPayments' => function ($query) use ($id) {
            $query->where('group_id', $id);
        }, 'students.account']);

        return $item->students->filter(function ($student) use ($id) {
            $debt = $student->groupPayments->where('group_id', $id)->sum('left_amount');
            $balance = $student->account->balance;

            return $debt > 0 || $balance < 0;
        })->count();
    }

    protected function extractedDebtStore(mixed $student, ?string $amount): string|int|null
    {
        if ($student->balance >= $amount) {
            $student->update([
                'balance' => $student->balance - $amount,
            ]);
            $amount = 0;
        } elseif ($student->balance > 0) {
            $debtAmount = $amount;
            $amount = $amount - $student->balance;
            $student->update([
                'balance' => $student->balance - $debtAmount,
            ]);
        } else {
            $student->update([
                'balance' => $student->balance - $amount,
            ]);
        }

        return $amount;
    }

    public function deleteDebt($studentGroupPaymentId)
    {
        $studentGroupPayment = StudentGroupPayment::findOrFail($studentGroupPaymentId);
        if ($studentGroupPayment->left_amount != $studentGroupPayment->amount) {
            return [
                'success' => false,
                'message' => 'Bu qarzdorlikni o\'chirib bo\'lmaydi.',
            ];
        }
        $studentGroupPayment->delete();

        return [
            'success' => true,
            'message' => 'Qarzdorlik o\'chirildi',
        ];
    }

    public function createAccount($name)
    {
        return Account::create([
            'name' => $name,
            'account_code' => AccountEnum::GROUP->value,
        ]);
    }

    private function updateAccount($entity): void
    {
        $entity->account->update([
            'name' => $entity->name,
        ]);
    }

    public function getUnpaidStudents($groupId)
    {
        $studentGroupPayments = StudentGroupPayment::with(['student' => function ($query) {
            $query->withTrashed();
        }])
            ->where('left_amount', '>', 0)
            ->where('group_id', $groupId)
            ->get();

        return $studentGroupPayments->groupBy('student_id')->map(function ($payments) {
            $user = $payments->first()->student;
            $user->left_amount = $payments->sum('left_amount');

            return $user;
        })->values();
    }

    public function paymentNotice(array $data, $id): void
    {
        info('GroupService: Starting payment notice process', [
            'group_id' => $id,
            'student_ids' => $data['students'] ?? [],
        ]);

        // Validate SMS configuration
        $this->validateSmsConfiguration();

        $students = Student::whereIn('id', $data['students'])->get();
        // Load course relationship for template variables
        $group = Group::with('course')->findOrFail($id);

        info('GroupService: Students and group loaded', [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'students_count' => $students->count(),
            'student_names' => $students->pluck('name')->toArray(),
        ]);

        if ($students->isEmpty()) {
            info('GroupService: No students selected');
            throw new \Exception(__('translation.validation.no_students_selected'));
        }

        foreach ($students as $student) {
            info('GroupService: Dispatching PaymentNoticeJob', [
                'group_id' => $group->id,
                'student_id' => $student->id,
                'student_name' => $student->getFullName(),
            ]);

            PaymentNoticeJob::dispatch($group, $student);
        }

        info('GroupService: All PaymentNoticeJobs dispatched', [
            'group_id' => $group->id,
            'jobs_count' => $students->count(),
        ]);
    }

    /**
     * Validate SMS configuration before sending
     */
    private function validateSmsConfiguration(): void
    {
        // Check if SMS is enabled
        $sendSmsEnabled = settings('send_sms');
        if (! $sendSmsEnabled || $sendSmsEnabled !== 'enable') {
            throw new \Exception(__('translation.sms.disabled_description'));
        }

        // Check if debt SMS template is configured
        $debtSmsTemplate = Settings::where('key', 'debt_sms_template')->first();
        if (! $debtSmsTemplate || ! $debtSmsTemplate->value) {
            throw new \Exception(__('translation.sms.debt_sms_template_not_set'));
        }
    }

    public function getBranches()
    {
        $branchIds = auth()->user()->branches()->pluck('branch_id');

        return Branch::whereIn('id', $branchIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function findDebt($studentGroupPaymentId)
    {
        return StudentGroupPayment::with(['payments' => function ($query) {
            $query->doesntHave('refunds');
        }, 'student' => function ($query) {
            $query->withTrashed();
        }])->findOrFail($studentGroupPaymentId);
    }

    public function findStudentGroupPaymentGroup($studentPaymentId)
    {
        return StudentGroupPayment::findOrFail($studentPaymentId)->group_id;
    }

    public function updateDebt(array $data, $studentGroupPaymentId): void
    {
        $studentGroupPayment = StudentGroupPayment::findOrFail($studentGroupPaymentId);
        $studentGroupPaymentPaymentsSum = $studentGroupPayment->payments()->doesntHave('refunds')->sum('amount');
        if (removeComma($data['amount']) < $studentGroupPaymentPaymentsSum) {
            throw new \Exception('Kiritilgan summa to\'langan summadan kichik bo\'lishi mumkin emas.');
        }
        $studentGroupPayment->update([
            'amount' => removeComma($data['amount']),
            'left_amount' => removeComma($data['amount']) - $studentGroupPaymentPaymentsSum,
        ]);
    }

    public function trialLessonNotice(array $data, $id): void
    {
        if (Settings::where('key', 'send_sms')->first()->value !== 'enable') {
            throw new \Exception(__('translation.sms.disabled_description'));
        }

        $group = Group::findOrFail($id);
        $students = Student::whereIn('id', $data['students'])->get();
        $parent_sms_status = (isset($data['parent_sms_status']) && $data['parent_sms_status'] == 'on');

        foreach ($students as $student) {
            $parent_phone = null;
            if ($student->parent_phone) {
                $parent_phone = substr(preg_replace('/[^0-9]/', '', $student->parent_phone), 3);
            }

            $data_arr = [
                'course_name' => $group->course->name ?? '-',
                'student_id' => $student->id,
                'phone_number' => $student->phone,
                'parent_sms_status' => $parent_sms_status,
                'date' => $data['date'],
            ];

            TrialLessonNoticeJob::dispatch($data_arr);

            if ($parent_sms_status && $parent_phone) {
                $data_arr['phone_number'] = $parent_phone;
                TrialLessonNoticeJob::dispatch($data_arr);
            }
        }
    }

    public function delete($id): void
    {
        DB::beginTransaction();
        try {
            //remove student group payments where left amount greater than 0 and group_id in id
            StudentGroupPayment::where('left_amount', '>', 0)->where('group_id', $id)->delete();
            //remove group times which are group_id is equal to id
            GroupTime::where('group_id', $id)->delete();
            $item = $this->find($id);

            //distribute any remaining balance to the teacher and main account before
            //deleting the account, otherwise the money is stranded in a deleted account
            if ($item && $item->account && $item->account->balance > 0) {
                $causerId = auth()->id() ?? $item->teacher_id ?? 0;
                (new GroupBalanceDistributor)->distribute($item, (int) $causerId);
            }

            //delete group account
            $item->account->delete();

            if ($item) {
                $item->delete();
            }

            //remove groupCounts cache
            cache()->forget('groupCounts');
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
        DB::commit();
    }

    public function complete($id): void
    {
        $group = $this->find($id);

        //inactive group account
        $group->account->update([
            'status' => 'inactive',
        ]);

        //remove groupCounts cache
        cache()->forget('groupCounts');

        $group->update([
            'state' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getStudyCenterName()
    {
        return Settings::where('key', 'name')->first()->value;
    }

    private function createLeadBoard($entity)
    {
        $kanbanCollection = KanbanCollection::orderBy('id')->first();
        if (! $kanbanCollection) {
            $kanbanCollection = KanbanCollection::create([
                'name' => 'Default',
            ]);
        }
        $kanbanCollection = KanbanBoard::create([
            'name' => $entity->name,
            'group_id' => $entity->id,
            'kanban_collection_id' => $kanbanCollection->id,
        ]);

        $branch = auth()->user()->current_branch_id;
        $kanbanCollection->branches()->attach($branch);
    }

    public function getStudentIds($id)
    {
        return Student::whereHas('groups', function ($query) use ($id) {
            $query->where('group_id', $id);
        })->pluck('id')->toArray();
    }

    public function getAttendanceSettings($id)
    {
        return GroupAttendanceSettings::where('group_id', $id)->first();
    }

    public function getAttendanceNoticeStatus(): bool
    {
        $attendanceNoticeTemplate = Settings::where('key', 'attendance_notice_template')->first();
        $attendanceNoticeStatus = Settings::where('key', 'attendance_notice_status')->first();
        if (! empty($attendanceNoticeTemplate->value) && $attendanceNoticeStatus->value == 'enable') {
            return true;
        }

        return false;
    }

    public function getEditDebtActivities($student_group_payment_id)
    {
        return Activity::where('subject_id', $student_group_payment_id)
            ->where('log_name', 'student_group_payment')
            ->latest('id')
            ->paginate(10);
    }

    public function deleteAgreement($id, $studentId)
    {
        $group = Group::findOrFail($id);
        $group->students()->updateExistingPivot($studentId, [
            'amount' => null,
        ]);
    }

    public function findStudent($student_id)
    {
        return Student::where('id', $student_id)->first();
    }

    public function agreement($id, $student_id, $amount, $comment)
    {
        $group = Group::findOrFail($id);
        $amount = removeComma($amount);
        $group->students()->updateExistingPivot($student_id, [
            'amount' => $amount,
            'comment' => $comment,
        ]);
    }

    public function checkPermission($id)
    {
        if (! is_numeric($id)) {
            abort(404, 'Invalid group ID');
        }

        $group = Group::withoutGlobalScope('branch')->withTrashed()->findOrFail($id);
        $user = auth()->user();

        if ($user->hasRole('Teacher')) {
            if ($user->id !== $group->teacher_id) {
                abort(403, 'This group does not belong to you');
            }
        }

        // Update user's current branch to match the group's branch only if different
        $groupBranch = $group->branches()->first();
        if ($groupBranch && $user->current_branch_id !== $groupBranch->id) {
            $user->update(['current_branch_id' => $groupBranch->id]);
            session()->flash('success_alert', __('translation.alert_messages.branch_changed'));
        }

        return true;
    }
}
