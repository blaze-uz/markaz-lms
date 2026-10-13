<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Livewire\Component;

class HomeworkChart extends Component
{
    public $startDate;

    public $endDate;

    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        if (! $this->startDate || ! $this->endDate) {
            $this->startDate = now()->subDays(30);
            $this->endDate = now();
        }

        $branchId = auth()->user()->current_branch_id;

        $hasHomeworkData = Attendance::query()
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$this->startDate, $this->endDate])
            ->whereNotNull('attendances.homework')
            ->where('attendances.homework', true)
            ->whereNull('attendances.deleted_at')
            ->exists();

        if (! $hasHomeworkData) {
            return view('livewire.report.homework-chart', [
                'chartData' => null,
                'hasHomeworkData' => false,
            ]);
        }

        $chartData = $this->getChartData($this->startDate, $this->endDate);

        return view('livewire.report.homework-chart', [
            'chartData' => $chartData,
            'hasHomeworkData' => true,
        ]);
    }

    private function getChartData($startDate, $endDate): array
    {
        $data = [
            'labels' => [],
            'series' => [
                'completed' => [],
                'not_completed' => [],
            ],
        ];

        $branchId = auth()->user()->current_branch_id;

        $attendances = Attendance::query()
            ->selectRaw('attendances.date, attendances.homework, count(attendances.student_id) as count')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('branch_student', 'students.id', '=', 'branch_student.student_id')
            ->where('branch_student.branch_id', $branchId)
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->whereNotNull('attendances.homework')
            ->whereNull('attendances.deleted_at')
            ->groupBy('attendances.date', 'attendances.homework')
            ->get();

        $dates = [];
        while (Carbon::parse($startDate)->lte(Carbon::parse($endDate))) {
            $dates[Carbon::parse($startDate)->format('Y-m-d')] = [
                'completed' => 0,
                'not_completed' => 0,
            ];
            $startDate = Carbon::parse($startDate)->addDay();
        }

        foreach ($attendances as $attendance) {
            $key = $attendance->homework ? 'completed' : 'not_completed';
            $dates[$attendance->date->format('Y-m-d')][$key] = $attendance->count;
        }

        foreach ($dates as $date => $homework) {
            $data['labels'][] = $date;
            $data['series']['completed'][] = $homework['completed'];
            $data['series']['not_completed'][] = $homework['not_completed'];
        }

        return $data;
    }
}
