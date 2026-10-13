<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Livewire\Component;

class AttendanceChart extends Component
{
    public $startDate;
    public $endDate;

    public function render()
    {
        if (!$this->startDate || !$this->endDate) {
            $this->startDate = Carbon::now()->subDays(7)->format('Y-m-d');
            $this->endDate = Carbon::now()->format('Y-m-d');
        }
        $chartData = $this->getChartData($this->startDate, $this->endDate);
        return view('livewire.dashboard.attendance-chart', [
            'chartData' => $chartData,
        ]);
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

        // Initialize dates with empty data
        $dates = [];
        $currentDate = Carbon::parse($startDate);
        while ($currentDate->lte(Carbon::parse($endDate))) {
            $formattedDate = $currentDate->format('Y-m-d');
            $dates[$formattedDate] = [
                'present' => 0,
                'absent' => 0,
            ];
            $currentDate->addDay();
        }

        // Fetch attendance data with grouping and counting
        $attendances = Attendance::whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereRelation('student.branches', function ($query) {
                $query->where('branch_id', auth()->user()->current_branch_id);
            })
            ->selectRaw('DATE(date) as date, status, COUNT(student_id) as count')
            ->groupBy('date', 'status')
            ->get();

        // Populate the attendance data
        foreach ($attendances as $attendance) {
            $formattedDate = Carbon::parse($attendance->date)->format('Y-m-d');
            if (isset($dates[$formattedDate])) {
                $dates[$formattedDate][$attendance->status->value] = $attendance->count;
            }
        }

        // Prepare data for the chart
        foreach ($dates as $date => $attendance) {
            $data['labels'][] = $date;
            $data['series']['present'][] = $attendance['present'];
            $data['series']['absent'][] = $attendance['absent'];
        }

        return $data;
    }
}
