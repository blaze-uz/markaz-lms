<?php

namespace App\Livewire\Group;

use App\Models\Group;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class AttachStudent extends Component
{
    public $selectedGroupId;

    public $selectedStudentId;

    public $selectedStudentsIds;

    public $groups;

    public $students;

    public $selectedGroup;

    public $selectedGroupDebtRange;

    public $selectedGroupCurrentLessonCount;

    public $selectedGroupLessonsCount;

    public $selectedGroupLeftLessonsCount;

    public $selectedGroupLessonDays = [];

    public $selectedGroupAmountPerLesson;

    public $selectedGroupDebtAmount;

    public $editGroupAgreedAmount = false;

    public $agreedDebtAmount;

    public $firstMonthDebtAmountSwitcher = false;

    public $firstMonthDebtAmountWarning = false;

    public $studentAttachedAt;

    public $showOldPeriodDebtOption = false;

    public $createOldPeriodDebts = false;

    public $oldPeriodsCount = 0;

    public function mount($selectedGroupId = null, $selectedStudentId = null, $selectedStudentsIds = null)
    {
        $this->selectedGroupId = $selectedGroupId;
        $this->selectedStudentId = $selectedStudentId;
        $this->groups = $this->getGroups();
        $this->students = $this->getStudents($selectedGroupId);
        $this->agreedDebtAmount = $this->selectedGroupDebtAmount;
        if ($this->selectedStudentsIds) {
            $this->selectedStudentsIds = $selectedStudentsIds;
        }
        if (! $this->selectedStudentsIds) {
            $this->selectedStudentsIds = [];
        }
        if ($selectedGroupId) {
            $this->getSelectedGroup($selectedGroupId);
        }
        $this->studentAttachedAt = now()->format('Y-m-d');
        $this->dispatch('select2Hydrate');
    }

    public function hydrate(): void
    {
        $this->dispatch('select2Hydrate');
    }

    #[On('select-group')]
    public function updateSelectedGroup($group_id = null): void
    {
        if ($group_id) {
            $this->selectedGroup = Group::with(['teacher', 'course', 'students'])
                ->withCount('students')
                ->where('id', $group_id)
                ->where('state', '!=', 'completed')
                ->firstOrFail();

            if ($this->firstMonthDebtAmountSwitcher) {
                $this->selectedGroupDebtAmount = $this->selectedGroup->price;
            }
            $this->getDebtRange($group_id);
            $this->getFirstMonthDebtAmount($this->selectedGroup->id);
            if ($this->selectedGroup || $this->selectedGroup->state === 'active') {
                $this->getSelectedGroupLessonsCount();
            }
        } else {
            $this->selectedGroup = null;
        }

        $this->dispatch('groupChanged');
    }

    #[On('calendar-date-changed')]
    public function calendarDateChanged($date)
    {
        if ($this->selectedGroup->payment_type === 'by_count_lessons' || $this->selectedGroup->payment_type === 'one_time') {
            return;
        }
        $this->firstMonthDebtAmountFunction($this->selectedGroup->id, $date);
        $this->studentAttachedAt = $date;
        $this->checkOldPeriodDebts($date);
    }

    public function firstMonthDebtAmountSwitcherToggle(): void
    {
        $this->selectedGroupDebtAmount = $this->selectedGroup->price;
        $this->getFirstMonthDebtAmount($this->selectedGroup->id);
        $this->studentAttachedAt = now()->format('Y-m-d');
        if (! $this->firstMonthDebtAmountSwitcher) {
            $this->selectedGroupDebtAmount = null;
        }
    }

    private function firstMonthDebtAmountFunction(mixed $selectedGroupId, $groupStartDate = null): void
    {
        $group = Group::find($selectedGroupId);
        if (! $group || $group->state !== 'active') {
            return;
        }

        if ($group->payment_type === 'by_count_lessons' || $group->payment_type === 'one_time') {
            return;
        }

        $studentAttachedAt = $groupStartDate ? Carbon::parse($groupStartDate) : Carbon::parse($this->studentAttachedAt);

        $periodDates = $this->getPaymentPeriodDates($group, $studentAttachedAt);
        if (! $periodDates) {
            return;
        }

        $periodStart = $periodDates['start'];
        $periodEnd = $periodDates['end'];

        $totalLessonDaysInPeriod = $this->getGroupLessonDays($group, $periodStart, $periodEnd);

        if (count($totalLessonDaysInPeriod) === 0) {
            return;
        }

        $groupPrice = $group->price;
        $pricePerLesson = intval($groupPrice / count($totalLessonDaysInPeriod));

        $remainingLessonDays = $this->getRemainingLessonDaysInPeriod($totalLessonDaysInPeriod, $studentAttachedAt, $periodEnd);

        $this->selectedGroupDebtAmount = intval($pricePerLesson * $remainingLessonDays);

        $this->firstMonthDebtAmountWarning = $remainingLessonDays < count($totalLessonDaysInPeriod);

        if ($remainingLessonDays === count($totalLessonDaysInPeriod)) {
            $this->selectedGroupDebtAmount = $groupPrice;
            $this->firstMonthDebtAmountWarning = false;
        }
    }

    public function render()
    {
        return view('livewire.group.attach-student');
    }

    private function getGroups()
    {
        $query = Group::with(['teacher', 'course'])
            ->where('state', '!=', 'completed')
            ->orderBy('name');

        if (auth()->user()->hasRole('Teacher')) {
            $query->where('teacher_id', auth()->id());
        }

        return $query->get(['id', 'name', 'teacher_id', 'course_id']);
    }

    private function getStudents($selectedGroupId)
    {
        if ($selectedGroupId) {
            // Find the group with its students eagerly loaded
            $group = Group::with('students')->find($selectedGroupId);

            if (! $group) {
                abort(404);
            }

            // Exclude the students already in the group
            return Student::whereNotIn('id', $group->students->pluck('id'))
                ->select(['id', 'name', 'surname', 'phone'])
                ->orderBy('name')
                ->get();
        } else {
            return Student::orderBy('name')->select(['id', 'name', 'surname', 'phone'])->get();
        }
    }

    private function getDebtRange($group_id)
    {
        $group = Group::with('studentGroupPayments')->findOrFail($group_id);

        if ($group->state !== 'active') {
            return;
        }

        if ($group->payment_type === 'one_time') {
            $this->selectedGroupDebtRange = __('translation.groups.one_time');

            return;
        }

        $latestPayment = $group->studentGroupPayments->last();

        if (! $latestPayment) {
            $this->selectedGroupDebtRange = __('translation.groups.not_set');

            return;
        }

        $range = explode('/', $latestPayment->range);
        if (empty($range)) {
            $this->selectedGroupDebtRange = __('translation.groups.not_set');

            return;
        }

        if (is_numeric($range[0])) {
            $this->selectedGroupDebtRange = $range[0].'/'.$range[1];
            $this->selectedGroupCurrentLessonCount = $group->attendances()->count();
        } else {
            if (! empty($range[0]) && ! empty($range[1])) {
                $start = dateFormat($range[0]);
                $end = dateFormat($range[1]);
                $this->selectedGroupDebtRange = $start.' - '.$end;
            }
        }
    }

    private function getSelectedGroup($selectedGroupId)
    {
        $this->selectedGroup = Group::with(['teacher', 'course', 'students'])
            ->withCount('students')
            ->where('state', '!=', 'completed')
            ->find($selectedGroupId);
        if ($this->selectedGroup) {
            $this->selectedGroupDebtAmount = $this->selectedGroup->price;
            $this->getDebtRange($selectedGroupId);
            if ($this->selectedGroup->state === 'active') {
                $this->getSelectedGroupLessonsCount();
            }
        }
    }

    private function getFirstMonthDebtAmount(mixed $group_id): void
    {
        $group = Group::findOrFail($group_id);
        if ($group->state !== 'active') {
            return;
        }

        if ($group->payment_type === 'by_count_lessons' || $group->payment_type === 'one_time') {
            return;
        }

        $studentAttachedAt = Carbon::parse($this->studentAttachedAt);

        $periodDates = $this->getPaymentPeriodDates($group, $studentAttachedAt);
        if (! $periodDates) {
            return;
        }

        $periodStart = $periodDates['start'];
        $periodEnd = $periodDates['end'];

        $totalLessonDaysInPeriod = $this->getGroupLessonDays($group, $periodStart, $periodEnd);

        if (count($totalLessonDaysInPeriod) === 0) {
            return;
        }

        $groupPrice = $group->price;
        $pricePerLesson = intval($groupPrice / count($totalLessonDaysInPeriod));

        $remainingLessonDays = $this->getRemainingLessonDaysInPeriod($totalLessonDaysInPeriod, $studentAttachedAt, $periodEnd);

        if ($remainingLessonDays === count($totalLessonDaysInPeriod)) {
            $this->selectedGroupDebtAmount = $groupPrice;
            $this->firstMonthDebtAmountWarning = false;
        } else {
            $this->selectedGroupDebtAmount = intval($pricePerLesson * $remainingLessonDays);
            $this->firstMonthDebtAmountWarning = true;
        }
    }

    private function getGroupLessonDays($group, $startDate = null, $endDate = null): array
    {
        $groupTimes = $group->groupTimes;

        $dayMap = [
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            'sunday' => Carbon::SUNDAY,
        ];

        $daysOfWeek = collect($groupTimes)
            ->map(fn ($groupTime) => $dayMap[strtolower($groupTime->name)])
            ->unique()
            ->toArray();

        $endInclusive = false;
        if ($startDate && $endDate) {
            $periodStart = Carbon::parse($startDate);
            $periodEnd = Carbon::parse($endDate);
            // Davr 1–1 bo'lganda periodEnd keyingi davrning boshidir, davr ichiga kiritilmaydi
            $endInclusive = false;
        } else {
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $periodStart = Carbon::create($currentYear, $currentMonth, 1);
            $periodEnd = Carbon::create($currentYear, $currentMonth, now()->daysInMonth);
            $endInclusive = true;
        }

        $lessonDates = [];
        $currentDate = $periodStart->copy();

        while ($endInclusive ? $currentDate->lte($periodEnd) : $currentDate->lt($periodEnd)) {
            if (in_array($currentDate->dayOfWeek, $daysOfWeek)) {
                $lessonDates[] = $currentDate->format('Y-m-d');
            }
            $currentDate->addDay();
        }

        return $lessonDates;
    }

    private function getLeftGroupLessonDays(array $groupLessonDays, $groupStartDate = null)
    {
        $startDate = $groupStartDate ?? now()->format('Y-m-d');

        $leftGroupLessonDays = array_filter($groupLessonDays, function ($date) use ($startDate) {
            return $date >= $startDate;
        });

        return count($leftGroupLessonDays);
    }

    private function getPaymentPeriodDates($group, Carbon $attachedDate): ?array
    {
        if (! $group->start_date) {
            return null;
        }

        $groupStartDate = Carbon::parse($group->start_date);
        $startDay = $groupStartDate->day;

        $periodStart = $attachedDate->copy()->day($startDay);

        if ($periodStart->greaterThan($attachedDate) && $periodStart->greaterThan($groupStartDate)) {
            $periodStart->subMonth();
        }

        $periodEnd = $periodStart->copy()->addMonth();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
        ];
    }

    /**
     * Davrdagi qolgan dars kunlari soni. Tanlangan sana (studentAttachedAt) kuni ham hisobga olinadi (shu kun dars kuni bo'lsa).
     */
    private function getRemainingLessonDaysInPeriod(array $totalLessonDays, Carbon $studentAttachedAt, Carbon $periodEnd): int
    {
        $attachedDateString = $studentAttachedAt->format('Y-m-d');
        $periodEndString = $periodEnd->format('Y-m-d');

        $remainingDays = array_filter($totalLessonDays, function ($date) use ($attachedDateString, $periodEndString) {
            return $date >= $attachedDateString && $date < $periodEndString;
        });

        return count($remainingDays);
    }

    private function getSelectedGroupLessonsCount(): void
    {
        $groupLessonDays = $this->getGroupLessonDays($this->selectedGroup);
        $this->selectedGroupLessonsCount = count($groupLessonDays);
        $this->selectedGroupLeftLessonsCount = $this->getLeftGroupLessonDays($groupLessonDays);
        $this->selectedGroupLessonDays = $this->getAmountForEachLesson();
        if ($this->selectedGroupLessonsCount === 0) {
            return;
        }
        $this->selectedGroupAmountPerLesson = intval($this->selectedGroup->price / $this->selectedGroupLessonsCount);
    }

    private function getAmountForEachLesson(): array
    {
        $groupLessonDays = $this->getGroupLessonDays($this->selectedGroup);
        $lessonDays = [];
        $amountForPerLesson = 0;

        if (count($groupLessonDays) > 0) {
            $amountForPerLesson = intval($this->selectedGroup->price / count($groupLessonDays));
        }

        $reverseGroupLessonDays = array_reverse($groupLessonDays);
        foreach ($reverseGroupLessonDays as $key => $day) {
            $lessonDays[$day] = $amountForPerLesson * ($key + 1);
        }

        return array_reverse($lessonDays);
    }

    private function checkOldPeriodDebts($date): void
    {
        if (! $this->selectedGroup || $this->selectedGroup->payment_type !== 'date_to_date') {
            $this->showOldPeriodDebtOption = false;
            $this->createOldPeriodDebts = false;
            $this->oldPeriodsCount = 0;

            return;
        }

        $attachedDate = Carbon::parse($date);
        $currentDate = now();

        // Check if the attached date is in a previous month
        if ($attachedDate->isSameMonth($currentDate)) {
            $this->showOldPeriodDebtOption = false;
            $this->createOldPeriodDebts = false;
            $this->oldPeriodsCount = 0;

            return;
        }

        // Calculate the number of old periods (months)
        $this->oldPeriodsCount = $this->calculateOldPeriodsCount($attachedDate, $currentDate);

        if ($this->oldPeriodsCount > 0) {
            $this->showOldPeriodDebtOption = true;
        } else {
            $this->showOldPeriodDebtOption = false;
            $this->createOldPeriodDebts = false;
        }
    }

    private function calculateOldPeriodsCount($attachedDate, $currentDate): int
    {
        $count = 0;
        $startDate = $attachedDate->copy();

        while ($startDate->lessThan($currentDate->startOfMonth())) {
            $count++;
            $startDate->addMonth();
        }

        return $count;
    }
}
