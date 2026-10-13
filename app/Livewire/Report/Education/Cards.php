<?php

namespace App\Livewire\Report\Education;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;

class Cards extends Component
{
    public $startDate;
    public $endDate;

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        if (!$this->startDate || !$this->endDate) {
            $this->startDate = now()->subDays(30);
            $this->endDate = now();
        }

        $attendance_percentage = $this->getAttendancePercentage($this->startDate, $this->endDate);
        $students_count = Student::count();
        $teachers_count = User::role('Teacher')->count();
        $deleted_students_count = Student::onlyTrashed()->count();

        return view('livewire.report.education.cards', [
            'attendance_percentage' => $attendance_percentage,
            'students_count' => $students_count,
            'teachers_count' => $teachers_count,
            'deleted_students_count' => $deleted_students_count,
        ]);
    }

    private function getAttendancePercentage($startDate, $endDate): float|int
    {
        $present_attendances = Attendance::whereRelation('student', function ($query) {
            $query->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            });
        })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('status', 'present')
            ->select('date', 'status')
            ->get()
            ->groupBy('date')
            ->map(function ($item) {
                return $item->count();
            });

        $absent_attendances = Attendance::whereRelation('student', function ($query) {
            $query->whereHas('branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            });
        })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('status', 'absent')
            ->select('date', 'status')
            ->get()
            ->groupBy('date')
            ->map(function ($item) {
                return $item->count();
            });

        $total_present_count = $present_attendances->sum();
        $total_absent_count = $absent_attendances->sum();
        $total_attendance = $total_present_count + $total_absent_count;

        $attendance_overall_percentage = 0;

        if ($total_attendance > 0) {
            $attendance_overall_percentage = ($total_present_count / $total_attendance) * 100;
            $attendance_overall_percentage = round($attendance_overall_percentage, 2);
        }

        return intval($attendance_overall_percentage);
    }
}