<?php

namespace App\Livewire\Group;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class AttendanceTable extends Component
{
    public const MAX_RANGE_DAYS = 62;

    public $group;

    public string $dateFrom = '';

    public string $dateTo = '';

    public $user;

    public $selectedGroup;

    public bool $dateRangeClamped = false;

    protected $queryString = [
        'dateFrom' => ['except' => '', 'as' => 'attendanceDateFrom'],
        'dateTo' => ['except' => '', 'as' => 'attendanceDateTo'],
        'selectedGroup' => ['except' => '', 'as' => 'attendanceGroup'],
    ];

    public function mount($group = null, $user = null): void
    {
        $this->group = $group;
        $this->user = $user;

        if ($this->dateFrom === '' || $this->dateFrom === null) {
            $this->dateFrom = Carbon::now()->subDays(29)->format('Y-m-d');
        }
        if ($this->dateTo === '' || $this->dateTo === null) {
            $this->dateTo = Carbon::now()->format('Y-m-d');
        }

        $this->normalizeDateRange();

        if ($this->selectedGroup !== null && $this->selectedGroup !== '') {
            return;
        }

        if ($group !== null) {
            $this->selectedGroup = (string) $group->id;

            return;
        }

        if ($user !== null) {
            $firstGroupId = Group::query()
                ->where('teacher_id', $user->id)
                ->orderBy('id')
                ->value('id');
            if ($firstGroupId !== null) {
                $this->selectedGroup = (string) $firstGroupId;
            }
        }
    }

    public function updatedDateFrom(): void
    {
        $this->normalizeDateRange();
    }

    public function updatedDateTo(): void
    {
        $this->normalizeDateRange();
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        [$startDate, $endDate] = $this->getDateRangeBounds();

        $groups = collect();
        if ($this->user) {
            $groups = Group::query()
                ->where('teacher_id', $this->user->id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }
        if (! $this->group) {
            $this->group = $groups->first();
        }
        if ($this->selectedGroup) {
            $this->group = Group::find($this->selectedGroup);
        }
        if (! $this->group) {
            return view('livewire.group.attendance-table', [
                'uniqueDays' => collect(),
                'tableRows' => collect(),
                'groups' => $groups,
                'attendanceTakenTimes' => collect(),
                'attendanceStats' => $this->emptyAttendanceStats(),
            ]);
        }

        $attendanceData = Attendance::query()
            ->select(['student_id', 'date', 'status', 'theme', 'score', 'comment', 'created_at'])
            ->where('group_id', $this->group->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $uniqueDays = $this->buildUniqueDays($attendanceData);
        $attendanceTakenTimes = $this->buildAttendanceTakenTimes($attendanceData);
        $studentIds = $attendanceData->pluck('student_id')->unique()->filter()->values();

        if ($studentIds->isEmpty()) {
            return view('livewire.group.attendance-table', [
                'uniqueDays' => $uniqueDays,
                'tableRows' => collect(),
                'groups' => $groups,
                'attendanceTakenTimes' => $attendanceTakenTimes,
                'attendanceStats' => $this->emptyAttendanceStats(),
            ]);
        }

        $students = Student::withTrashed()
            ->whereIn('id', $studentIds)
            ->get()
            ->sortBy(fn (Student $student) => $student->getFullName())
            ->values();

        $attendanceLookup = $this->buildAttendanceLookup($attendanceData);
        $tableRows = $this->buildTableRows($students, $uniqueDays, $attendanceLookup);
        $attendanceStats = $this->buildAttendanceStats($attendanceData, $students->count(), $uniqueDays->count());

        return view('livewire.group.attendance-table', [
            'uniqueDays' => $uniqueDays,
            'tableRows' => $tableRows,
            'groups' => $groups,
            'attendanceTakenTimes' => $attendanceTakenTimes,
            'attendanceStats' => $attendanceStats,
        ]);
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function getDateRangeBounds(): array
    {
        try {
            $start = Carbon::parse($this->dateFrom)->startOfDay();
            $end = Carbon::parse($this->dateTo)->endOfDay();
            if ($start->gt($end)) {
                return [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [$start, $end];
        } catch (\Throwable) {
            return [Carbon::now()->subDays(29)->startOfDay(), Carbon::now()->endOfDay()];
        }
    }

    private function normalizeDateRange(): void
    {
        $this->dateRangeClamped = false;

        try {
            $from = Carbon::parse($this->dateFrom)->startOfDay();
            $to = Carbon::parse($this->dateTo)->startOfDay();

            if ($from->gt($to)) {
                $this->dateTo = $from->format('Y-m-d');
                $to = $from;
            }

            if ($from->diffInDays($to) > self::MAX_RANGE_DAYS) {
                $this->dateTo = $from->copy()->addDays(self::MAX_RANGE_DAYS)->format('Y-m-d');
                $this->dateRangeClamped = true;
            }
        } catch (\Throwable) {
            //
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function buildUniqueDays(Collection $attendanceData): Collection
    {
        return $attendanceData
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * @return array<string, Attendance>
     */
    private function buildAttendanceLookup(Collection $attendanceData): array
    {
        $lookup = [];

        foreach ($attendanceData as $row) {
            $lookup[$row->student_id.'|'.Carbon::parse($row->date)->toDateString()] = $row;
        }

        return $lookup;
    }

    /**
     * @return Collection<string, \Illuminate\Support\Carbon>
     */
    private function buildAttendanceTakenTimes(Collection $attendanceData): Collection
    {
        return $attendanceData
            ->filter(fn (Attendance $attendance) => $attendance->created_at !== null)
            ->groupBy(fn (Attendance $attendance) => Carbon::parse($attendance->date)->toDateString())
            ->map(fn (Collection $attendances) => $attendances->sortBy('created_at')->first()->created_at);
    }

    /**
     * @param  array<string, Attendance>  $attendanceLookup
     * @return Collection<int, array{student: Student, cells: array<string, Attendance|null>, present: int, absent: int, marked: int, rate: float}>
     */
    private function buildTableRows(Collection $students, Collection $uniqueDays, array $attendanceLookup): Collection
    {
        return $students->map(function (Student $student) use ($uniqueDays, $attendanceLookup) {
            $cells = [];
            $rowPresent = 0;
            $rowAbsent = 0;

            foreach ($uniqueDays as $day) {
                $attendance = $attendanceLookup[$student->id.'|'.$day] ?? null;
                $cells[$day] = $attendance;

                if ($attendance?->status?->value === 'present') {
                    $rowPresent++;
                } elseif ($attendance?->status?->value === 'absent') {
                    $rowAbsent++;
                }
            }

            $rowMarked = $rowPresent + $rowAbsent;

            return [
                'student' => $student,
                'cells' => $cells,
                'present' => $rowPresent,
                'absent' => $rowAbsent,
                'marked' => $rowMarked,
                'rate' => $rowMarked > 0 ? round(($rowPresent / $rowMarked) * 100, 1) : 0.0,
            ];
        });
    }

    /**
     * @return array{present: int, absent: int, marked: int, rate: float, students_count: int, days_count: int}
     */
    private function buildAttendanceStats(Collection $attendanceData, int $studentsCount, int $daysCount): array
    {
        $present = 0;
        $absent = 0;

        foreach ($attendanceData as $row) {
            if ($row->status?->value === 'present') {
                $present++;
            } elseif ($row->status?->value === 'absent') {
                $absent++;
            }
        }

        $marked = $present + $absent;

        return [
            'present' => $present,
            'absent' => $absent,
            'marked' => $marked,
            'rate' => $marked > 0 ? round(($present / $marked) * 100, 1) : 0.0,
            'students_count' => $studentsCount,
            'days_count' => $daysCount,
        ];
    }

    /**
     * @return array{present: int, absent: int, marked: int, rate: float, students_count: int, days_count: int}
     */
    private function emptyAttendanceStats(): array
    {
        return [
            'present' => 0,
            'absent' => 0,
            'marked' => 0,
            'rate' => 0.0,
            'students_count' => 0,
            'days_count' => 0,
        ];
    }
}
