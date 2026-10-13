<?php

namespace App\Livewire\Group;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceScoreTable extends Component
{
    public $group;

    public string $dateFrom = '';

    public string $dateTo = '';

    public $user;

    public $selectedGroup;

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

        $groups = [];
        if ($this->user) {
            $groups = Group::where('teacher_id', $this->user->id)->get();
        }
        if (! $this->group) {
            $this->group = $groups->first();
        }
        if ($this->selectedGroup) {
            $this->group = Group::find($this->selectedGroup);
        }
        if (! $this->group) {
            return view('livewire.group.attendance-table', [
                'attendanceData' => collect(),
                'uniqueDays' => collect(),
                'students' => collect(),
                'groups' => $groups,
                'attendanceStats' => [
                    'present' => 0,
                    'absent' => 0,
                    'marked' => 0,
                    'rate' => 0.0,
                    'students_count' => 0,
                    'days_count' => 0,
                ],
            ]);
        }
        $attendanceData = Attendance::select('student_id', 'date', 'score')
            ->where('group_id', $this->group->id)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date')
            ->get();

        $uniqueDays = $attendanceData->pluck('date')->unique();
        $students = Student::withTrashed()->whereIn('id', $attendanceData->pluck('student_id'))->get();

        return view('livewire.group.attendance-score-table', [
            'attendanceData' => $attendanceData,
            'uniqueDays' => $uniqueDays,
            'students' => $students,
            'groups' => $groups,
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
        try {
            $from = Carbon::parse($this->dateFrom);
            $to = Carbon::parse($this->dateTo);
            if ($from->gt($to)) {
                $this->dateTo = $from->format('Y-m-d');
            }
        } catch (\Throwable) {
            //
        }
    }
}
