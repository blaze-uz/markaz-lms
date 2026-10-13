<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use Livewire\Component;

class AttendanceTable extends Component
{
    public $student;
    public $startDate, $endDate, $date;
    public $selectedGroupId, $groups;
    public $attendances = [];

    public function mount($student): void
    {
        $this->student = $student;
        $this->getData();
    }

    public function render()
    {
        return view('livewire.student.attendance-table');
    }

    public function filter(): void
    {
        $this->getAttendance();
    }

    public function updatedSelectedGroupId(): void
    {
        $this->getAttendance();
    }

    public function getData(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->date = $this->startDate . ' to ' . $this->endDate;
        $this->selectedGroupId = null; // Don't select any group by default to show all
        $this->groups = $this->student->groups()->get();
        $this->getAttendance();
    }

    public function getAttendance(): void
    {
        $date = explode(' to ', $this->date);
        $this->startDate = $date[0];

        $query = Attendance::where('student_id', $this->student->id);

        // Only filter by group if a specific group is selected
        if ($this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        if (isset($date[1])) {
            $this->endDate = $date[1];
            $this->attendances = $query->whereBetween('date', [$this->startDate, $this->endDate])
                ->with('group')
                ->orderByDesc('date')
                ->get();
        } else {
            $this->attendances = $query->whereDate('date', $this->startDate)
                ->with('group')
                ->orderByDesc('date')
                ->get();
        }
    }
}
