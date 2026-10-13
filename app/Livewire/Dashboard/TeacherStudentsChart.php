<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class TeacherStudentsChart extends Component
{
    public function render()
    {
        $series = $this->getData();
        $labels = $this->getLabels();
        return view('livewire.dashboard.teacher-students-chart', [
            'series' => $series,
            'labels' => $labels
        ]);
    }

    private function getData(): array
    {
        // Initialize the series array
        $series = [];

        // Define the months from start of the year to current month
        $months = [];
        for ($i = 1; $i <= date('m'); $i++) {
            $months[] = $i;
        }

        // Cache key prefix
        $cacheKeyPrefix = 'teacher_user_counts_';

        // Retrieve user counts for each month
        foreach ($months as $month) {
            $cacheKey = $cacheKeyPrefix . $month;
            $count = Cache::remember($cacheKey, now()->addHours(1), function () use ($month) {
                return $this->getUserCountForMonth($month);
            });

            $series[] = $count;
        }

        return $series;
    }

    private function getUserCountForMonth(mixed $month)
    {
        $groups = auth()->user()->groups()->pluck('id');

        return Student::whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('group_id', $groups);
        })
            ->whereYear('students.created_at', date('Y'))
            ->whereMonth('students.created_at', $month)
            ->count();
    }

    private function getLabels()
    {
        return collect(range(1, date('m')))->map(function ($month) {
            return __('translation.months.' . strtolower(date('F', mktime(0, 0, 0, $month, 1))));
        });
    }
}
