<?php

namespace App\Livewire\Report;

use App\Exports\StudentsChartExport;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class StudentsChart extends Component
{
    public $startDate, $endDate;

    public function render()
    {
        if (!$this->startDate || !$this->endDate) {
            $this->startDate = now()->subDays(30);
            $this->endDate = now();
        }

        $chartData = $this->getChartData($this->startDate, $this->endDate);

        return view('livewire.report.students-chart', compact('chartData'));
    }

    private function getChartData($startDate, $endDate): array
    {
        $data = [
            'labels' => [],
            'series' => [
                'students' => [],
                'deleted_students' => [],
            ],
        ];

        $students = Student::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->select('created_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('Y-m-d');
            })
            ->map(function ($item) {
                return $item->count();
            });

        $deleted_students = Student::onlyTrashed()
            ->whereDate('deleted_at', '>=', $startDate)
            ->whereDate('deleted_at', '<=', $endDate)
            ->select('deleted_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->deleted_at)->format('Y-m-d');
            })
            ->map(function ($item) {
                return $item->count();
            });

        //make days from start to end date

        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);

        $dates = [];

        while ($startTimestamp <= $endTimestamp) {
            $dates[] = date('Y-m-d', $startTimestamp);
            $startTimestamp = strtotime('+1 day', $startTimestamp);
        }

        foreach ($dates as $date) {
            $data['labels'][] = $date;
            $data['series']['students'][] = $students->get($date, 0);
            $data['series']['deleted_students'][] = $deleted_students->get($date, 0);
        }

        return $data;
    }

    public function exportToExcel()
    {
        $startDate = $this->startDate ?? now()->subDays(30);
        $endDate = $this->endDate ?? now();

        $fileName = 'students_report_' . Carbon::parse($startDate)->format('Y-m-d') . '_to_' . Carbon::parse($endDate)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new StudentsChartExport($startDate, $endDate, auth()->user()->current_branch_id),
            $fileName
        );
    }
}
