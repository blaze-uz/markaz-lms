<?php

namespace App\Livewire\Dashboard;

use App\Models\Group;
use Illuminate\Support\Carbon;
use Livewire\Component;

class GroupAttendanceChart extends Component
{
    public function render()
    {
        $chartData = $this->getChartData();
        dd($chartData);
        return view('livewire.dashboard.group-attendance-chart', [
            'chartData' => $chartData,
        ]);
    }

    private function getChartData()
    {
        // Initialize the series array
        $series = [
            'attended' => 0,
            'missed' => 0,
        ];

        $days = [];
        for ($i = 1; $i <= 7; $i++) {
            $days[] = Carbon::now()->startOfWeek()->addDays($i)->format('l');
        }

        foreach ($days as $day) {
            $groups = Group::whereRelation('groupTimes', function ($query) use ($day) {
                $query->where('name', strtolower($day));
            })->get();

            foreach ($groups as $group) {
                $attended = $group->attendances()
                    ->whereDate('date', '>=', dateFormat($day, 'Y-m-d'))
                    ->exists();

                $missed = $group->attendances()
                    ->whereDate('date', '>=', dateFormat($day, 'Y-m-d'))
                    ->doesntExist();

                $series['attended'] += $attended;
                $series['missed'] += $missed;

            }
        }

        return [
            'labels' => $days,
            'series' => $series,
        ];
    }
}
//        $data = [
//            'labels' => [],
//            'series' => [
//                'attended' => [],
//                'missed' => [],
//            ],
//        ];
//
//        $query = Group::whereRelation('groupTimes', function ($query) use ($startDate, $endDate) {
//            $query->where('name', strtolower(Carbon::parse($startDate)->format('l')));
//        });
//
//        $attendedCount = $query->clone()
//            ->whereHas('attendances', function ($query) use ($startDate, $endDate) {
//                $query->whereDate('date', '>=', $startDate)
//                    ->whereDate('date', '<=', $endDate);
//            })
//            ->count();
//
//        $missedCount = $query->clone()
//            ->whereDoesntHave('attendances', function ($query) use ($startDate, $endDate) {
//                $query->whereDate('date', '>=', $startDate)
//                    ->whereDate('date', '<=', $endDate);
//            })
//            ->count();
//
//        $data['labels'] = dateFormat($startDate);
//        $data['series']['attended'] = [$attendedCount];
//        $data['series']['missed'] = [$missedCount];
//        dd($data);
//        return $data;
