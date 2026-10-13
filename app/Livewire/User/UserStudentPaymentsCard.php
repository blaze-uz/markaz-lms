<?php

namespace App\Livewire\User;

use App\Models\Group;
use App\Models\StudentGroupPayment;
use Livewire\Component;

class UserStudentPaymentsCard extends Component
{
    public $user;

    public $selectedDate = 'this_month';

    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        $studentsPaymentsData = $this->getStudentsPaymentsData($this->user);

        return view('livewire.user.user-student-payments-card', [
            'studentsPaymentsData' => $studentsPaymentsData,
        ]);
    }

    public function getStudentsPaymentsData($item): array
    {
        // Fixed: get proper last month date range
        $startDate = match ($this->selectedDate) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->startOfMonth()->subMonth(), // This gives previous month's first day
            default => null,
        };

        $endDate = match ($this->selectedDate) {
            'this_month' => now()->endOfMonth(),
            'last_month' => now()->startOfMonth()->subDay(), // This gives previous month's last day
            default => null,
        };

        $groups = Group::withTrashed()
            ->where('teacher_id', $item->id)
            ->whereIn('state', ['active', 'completed'])
            ->get(['id', 'name', 'teacher_percentage']);

        foreach ($groups as $group) {
            $group->allDebtSum = StudentGroupPayment::where('group_id', $group->id)
                ->when($startDate, function ($query) use ($startDate, $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })
                ->where('deleted_at', null)
                ->sum('amount');

            $group->leftDebtSum = StudentGroupPayment::where('group_id', $group->id)
                ->when($startDate, function ($query) use ($startDate, $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                })
                ->where('deleted_at', null)
                ->sum('left_amount');

            $percentage = $group->teacher_percentage ?? $this->user->percentage;
            $group->teacherProfit = ($group->allDebtSum - $group->leftDebtSum) * $percentage / 100;
        }

        return [
            'groups' => $groups,
        ];
    }
}
