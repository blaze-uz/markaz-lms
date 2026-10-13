<?php

namespace App\Livewire\Group;

use App\Models\Attendance;
use App\Models\Student;
use Livewire\Component;

class AttendanceCalendar extends Component
{
    public $group;

    public $startDate = '';

    public $endDate = '';

    public function mount($group = null): void
    {
        $this->group = $group;

        if (empty($this->startDate)) {
            $this->startDate = now()->startOfWeek()->format('Y-m-d');
        }

        if (empty($this->endDate)) {
            $this->endDate = now()->endOfWeek()->format('Y-m-d');
        }
    }

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    #[\Livewire\Attributes\On('calendar-range-changed')]
    public function updateDateRange($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function render()
    {
        if (! $this->group) {
            return view('livewire.group.attendance-calendar', [
                'attendanceData' => [],
                'uniqueDays' => [],
                'students' => [],
                'hasHomework' => false,
            ]);
        }

        $attendanceData = Attendance::select('student_id', 'date', 'status', 'score', 'comment', 'homework', 'theme')
            ->where('group_id', $this->group->id)
            ->whereDate('date', '>=', $this->startDate)
            ->whereDate('date', '<=', $this->endDate)
            ->orderBy('date')
            ->get();

        $uniqueDays = $attendanceData->pluck('date')->unique();
        $students = Student::withTrashed()->whereIn('id', $attendanceData->pluck('student_id'))->orderBy('name')->get();

        // Check if any homework values exist
        $hasHomework = $attendanceData->whereNotNull('homework')->where('homework', true)->isNotEmpty();

        return view('livewire.group.attendance-calendar', [
            'attendanceData' => $attendanceData,
            'uniqueDays' => $uniqueDays,
            'students' => $students,
            'hasHomework' => $hasHomework,
        ]);
    }
}
