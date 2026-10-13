<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class StudentsFinancialStatusChart extends Component
{
    public function render()
    {
        [$labels, $series] = $this->getData();
        return view('livewire.student.students-financial-status-chart', [
            'labels' => $labels,
            'series' => $series,
        ]);
    }

    public function getData(): array
    {
        $branchId = auth()->user()->current_branch_id;
        $cacheKey = 'student_financial_status'.($branchId ? "_branch_{$branchId}" : '_all');

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($branchId) {
            $query = Student::query();

            if ($branchId) {
                $query->whereHas('branches', fn ($q) => $q->where('branch_id', $branchId));
            }

            $unpaid = (clone $query)
                ->whereHas('groupPayments', fn ($q) => $q->where('left_amount', '!=', 0))
                ->count();

            $paid = (clone $query)
                ->whereDoesntHave('groupPayments', fn ($q) => $q->where('left_amount', '!=', 0))
                ->count();

            return [[__('attr.not_debtor'), __('attr.debtor')], [$paid, $unpaid]];
        });
    }
}
