<?php

namespace App\Livewire\Report;

use Livewire\Component;

class GroupDebtChart extends Component
{
    public function render()
    {
        $chartData = $this->getChartData();
        return view('livewire.report.group-debt-chart', compact('chartData'));
    }

    private function getChartData()
    {
        $data = [];
        $groups = \App\Models\Group::whereHas('studentGroupPayments', function ($query) {
            $query->where('left_amount', '!=', 0);
        })
            ->orderBy('name')
            ->get();

        foreach ($groups as $group) {
            $totalDebt = $group->studentGroupPayments()->sum('left_amount');
            $data[$group->name] = intval($totalDebt);
        }
        return $data;
    }
}
