<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsChart extends Component
{
    public function render()
    {
        $series = $this->getData();
        $labels = $this->getLabels();
        return view('livewire.dashboard.students-chart', [
            'series' => $series,
            'labels' => $labels,
        ]);
    }

    private function getData(): array
    {
        // Cache the data to avoid unnecessary repeated queries
        $cacheKey = 'user_counts_' . date('Y');
        $series = Cache::remember($cacheKey, now()->addHours(1), function () {
            return $this->fetchStudentCounts();
        });

        // Ensure we have an array for each month
        return array_values(array_replace(array_fill(1, 12, 0), $series));
    }

    private function fetchStudentCounts(): array
    {
        $query = Student::whereYear('created_at', date('Y'));

        // If user is a teacher, limit to their groups
        if (auth()->user()->hasRole('Teacher')) {
            $groups = auth()->user()->groups()->pluck('id');
            $query->whereHas('groups', function ($q) use ($groups) {
                $q->whereIn('group_id', $groups);
            });
        }

        // Fetch counts grouped by month using EXTRACT for PostgreSQL
        return $query->selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();
    }

    private function getLabels()
    {
        return collect(range(1, date('m')))->map(function ($month) {
            return __('translation.months.' . strtolower(date('F', mktime(0, 0, 0, $month, 1))));
        });
    }
}
