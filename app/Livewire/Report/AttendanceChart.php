<?php

namespace App\Livewire\Report;

use App\Exports\AttendanceExport;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceChart extends Component
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
        $chartData = $this->getChartData($this->startDate, $this->endDate);
        return view('livewire.report.attendance-chart', compact('chartData'));
    }

    private function getChartData($startDate, $endDate)
    {
        $data = [
            'labels' => [],
            'series' => [
                'present' => [],
                'absent' => [],
            ],
        ];

        $branchId = auth()->user()->current_branch_id;

        $attendances = Attendance::query()
            ->selectRaw('attendances.date, attendances.status, count(attendances.student_id) as count')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->whereNull('attendances.deleted_at')
            ->groupBy('attendances.date', 'attendances.status')
            ->get();
        $dates = [];
        while (Carbon::parse($startDate)->lte(Carbon::parse($endDate))) {
            $dates[Carbon::parse($startDate)->format('Y-m-d')] = [
                'present' => 0,
                'absent' => 0,
            ];
            $startDate = Carbon::parse($startDate)->addDay();
        }

        foreach ($attendances as $attendance) {
            $dates[$attendance->date->format('Y-m-d')][$attendance->status->value] = $attendance->count;
        }
        foreach ($dates as $date => $attendance) {
            $data['labels'][] = $date;
            $data['series']['present'][] = $attendance['present'];
            $data['series']['absent'][] = $attendance['absent'];
        }

        return $data;
    }

    public function exportToExcel()
    {
        $startDate = $this->startDate ?? now()->subDays(30);
        $endDate = $this->endDate ?? now();

        $fileName = 'attendance_report_' . Carbon::parse($startDate)->format('Y-m-d') . '_to_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new AttendanceExport($startDate, $endDate, auth()->user()->current_branch_id),
            $fileName
        );
    }
}
