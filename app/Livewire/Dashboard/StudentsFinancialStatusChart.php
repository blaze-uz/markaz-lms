<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsFinancialStatusChart extends Component
{
    public function render()
    {
        [$labels, $series] = $this->getData();
        return view('livewire.dashboard.students-financial-status-chart', [
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    public function getData(): array
    {
        $userId = auth()->id();
        $cacheKey = 'student_payment_counts_' . $userId;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            return $this->computeData($userId);
        });
    }

    private function computeData($userId): array
    {
        $user = User::with('groups')->find($userId);
        $groupIds = $user->groups->pluck('id');

        $students = Student::with(['groupPayments' => function ($query) use ($groupIds) {
            $query->whereIn('group_id', $groupIds);
        }])->whereHas('groups', function ($query) use ($groupIds) {
            $query->whereIn('group_id', $groupIds);
        })->get();

        $paid = 0;
        $unpaid = 0;

        foreach ($students as $student) {
            $leftAmountSum = $student->groupPayments->sum('left_amount');
            if ($leftAmountSum > 0) {
                $unpaid++;
            } else {
                $paid++;
            }
        }

        $labels = [__("attr.not_debtor"), __("attr.debtor")];
        $series = [$paid, $unpaid];

        return [$labels, $series];
    }
}
