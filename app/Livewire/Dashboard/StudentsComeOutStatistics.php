<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentsComeOutStatistics extends Component
{
    public function render()
    {
        $series = $this->getData();
        $labels = $this->getLabels();
        return view('livewire.dashboard.students-come-out-statistics', [
            'series' => $series,
            'labels' => $labels
        ]);
    }

    private function getData()
    {
        $days = range(1, date('d')); // Get all days from the 1st to the current day
        $series = [
            'come' => $this->getStudentCounts($days, 'come'),
            'out' => $this->getStudentCounts($days, 'out'),
        ];

        return $series;
    }

    private function getLabels(): \Illuminate\Support\Collection
    {
        $labels = [];
        for ($i = 1; $i <= date('d'); $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . '-' . __('translation.months.' . strtolower(date('F')));
        }

        return collect($labels);
    }

    private function getStudentCounts(array $days, string $type): array
    {
        // Determine if the user is a teacher and their associated groups
        $isTeacher = auth()->user()->hasRole('Teacher');
        $groups = $isTeacher ? auth()->user()->groups()->pluck('id') : null;

        $cacheKeyPrefix = "{$type}_counts_" . implode('_', $days);
        return Cache::remember($cacheKeyPrefix, now()->addHours(1), function () use ($days, $type, $groups, $isTeacher) {
            if ($type === 'come') {
                return $this->getComeCounts($days, $groups, $isTeacher);
            } else {
                return $this->getOutCounts($days, $groups, $isTeacher);
            }
        });
    }

    private function getComeCounts(array $days, $groups, bool $isTeacher): array
    {
        $query = Student::whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->whereIn(DB::raw('EXTRACT(DAY FROM created_at)'), $days);

        if ($isTeacher) {
            $query->whereHas('groups', function ($query) use ($groups) {
                $query->whereIn('group_id', $groups);
            });
        }

        $counts = $query->selectRaw('EXTRACT(DAY FROM created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        return $this->fillMissingDays($days, $counts);
    }

    private function getOutCounts(array $days, $groups, bool $isTeacher): array
    {
        $query = Student::onlyTrashed()
            ->whereYear('deleted_at', date('Y'))
            ->whereMonth('deleted_at', date('m'))
            ->whereIn(DB::raw('EXTRACT(DAY FROM deleted_at)'), $days);

        if ($isTeacher) {
            $query->whereHas('groups', function ($query) use ($groups) {
                $query->whereIn('group_id', $groups);
            });
        }

        $counts = $query->selectRaw('EXTRACT(DAY FROM deleted_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        return $this->fillMissingDays($days, $counts);
    }

    private function fillMissingDays(array $days, array $counts): array
    {
        // Initialize array with 0 counts for missing days
        $result = [];
        foreach ($days as $day) {
            $result[] = $counts[$day] ?? 0;
        }

        return $result;
    }
}
