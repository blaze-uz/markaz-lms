<?php

namespace App\Livewire\Report;

use App\Models\Group;
use Livewire\Component;

class GroupDebt extends Component
{
    public function placeholder()
    {
        return view('livewire.placeholders.skeleton');
    }

    public function render()
    {
        $groups = Group::with('studentGroupPayments')
            ->withCount(['studentGroupPayments as debts_count' => function ($query) {
                $query->where('left_amount', '!=', 0)
                    ->whereRelation('student', 'deleted_at', null);
            }])
            ->withSum(['studentGroupPayments as total_debt' => function ($query) {
                $query->whereRelation('student', 'deleted_at', null);
            }], 'left_amount')
            ->whereHas('studentGroupPayments', function ($query) {
                $query->where('left_amount', '!=', 0)
                    ->whereRelation('student', 'deleted_at', null);
            })
            ->orderBy('name')
            ->get();

        $allDebtSum = $groups->sum('total_debt');
        return view('livewire.report.group-debt', compact('groups', 'allDebtSum'));
    }
}
