<?php

namespace App\Livewire\User;

use App\Models\Group;
use Livewire\Component;

class UserStudentPaymentsTable extends Component
{
    public $selectedDate = 'this_month';

    public function render()
    {
        $groups = $this->getStudentsPaymentsData();
        return view('livewire.user.user-student-payments-table', [
            'groups' => $groups,
        ]);
    }

    private function getStudentsPaymentsData(): \Illuminate\Support\Collection
    {
        $startDate = match ($this->selectedDate) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => null,
        };

        $endDate = match ($this->selectedDate) {
            'this_month' => now()->endOfMonth(),
            'last_month' => now()->subMonth()->endOfMonth(),
            default => null,
        };

        $groups = Group::where('teacher_id', auth()->id())
            ->where('state', 'active')
            ->withCount('students')
            ->orderBy('name')
            ->get();

        foreach ($groups as $group) {
            $group->all_debt_sum = $group->studentGroupPayments()
                ->when($startDate, function ($query) use ($startDate, $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })
                ->sum('amount');

            $group->left_debt_sum = $group->studentGroupPayments()
                ->when($startDate, function ($query) use ($startDate, $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })
                ->sum('left_amount');
        }

        return $groups;
    }
}
