<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsGenderChart extends Component
{
    public ?int $branchId = null;

    public function render()
    {
        [$labels, $series] = $this->getData();

        return view('livewire.dashboard.students-gender-chart', compact('labels', 'series'));
    }

    private function getData(): array
    {
        $branchId = $this->branchId ?? auth()->user()->current_branch_id;
        $cacheKey = 'dashboard_gender_chart'.($branchId ? "_branch_{$branchId}" : '_all');

        $data = Cache::remember($cacheKey, now()->addHours(1), function () use ($branchId) {
            $query = Student::query();

            if ($branchId) {
                $query->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId));
            }

            $men = (clone $query)->where('gender', 'male')->count();
            $women = (clone $query)->where('gender', 'female')->count();

            return [
                'labels' => [__('attr.male'), __('attr.female')],
                'series' => [$men, $women],
            ];
        });

        return [
            $data['labels'] ?? [__('attr.male'), __('attr.female')],
            $data['series'] ?? [0, 0],
        ];
    }
}
