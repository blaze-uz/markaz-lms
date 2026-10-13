<?php

namespace App\Livewire\User;

use App\Models\Group;
use App\Models\StudentGroupPayment;
use App\Models\User;
use Livewire\Component;

class DebtStudentsList extends Component
{
    public $teacherId, $teacher;

    public function mount($teacherId)
    {
        $this->teacherId = $teacherId;
        $this->teacher = User::find($teacherId);
    }

    public function render()
    {
        //get all students of the teacher who have debt
        $debtStudents = $this->getDebtStudents();
        $debtCardData = $this->getDebtCardData();
        return view('livewire.user.debt-students-list', [
            'debtStudents' => $debtStudents,
            'debtCardData' => $debtCardData
        ]);
    }

    private function getDebtStudents()
    {
        $groups = Group::with('studentGroupPayments')->where('teacher_id', $this->teacherId)
            ->whereHas('studentGroupPayments', function ($query) {
                $query->where('left_amount', '!=', 0);
            })
            ->get();

        $debtStudents = [];
        foreach ($groups as $group) {
            $studentGroupPayments = StudentGroupPayment::with(['student' => function ($query) {
                $query->withTrashed();
            }, 'payments', 'group'])
                ->where('left_amount', '>', 0)
                ->where('group_id', $group->id)
                ->get();

            foreach ($studentGroupPayments as $studentGroupPayment) {
                $debtStudents[] = $studentGroupPayment;
            }
        }

        return $debtStudents;
    }

    private function getDebtCardData()
    {
        $groups = Group::with('studentGroupPayments')->where('teacher_id', $this->teacherId)
            ->whereHas('studentGroupPayments', function ($query) {
                $query->where('left_amount', '!=', 0);
            })
            ->get();

        $allDebtSum = $groups->sum(function ($group) {
            return $group->studentGroupPayments->sum('amount');
        });

        $leftDebtSum = $groups->sum(function ($group) {
            return $group->studentGroupPayments->sum('left_amount');
        });

        return [
            'allDebtSum' => $allDebtSum,
            'leftDebtSum' => $leftDebtSum
        ];
    }
}
