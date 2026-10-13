<?php

namespace App\Livewire\Report;

use App\Models\Group;
use Livewire\Component;

class GroupPaymentStatistics extends Component
{
    public $search = '';

    public function placeholder($params = [])
    {
        return view('livewire.placeholders.skeleton', $params);
    }

    public function render()
    {
        $groups = Group::select('id', 'name', 'account_id')
            ->with(['account:id,balance']) // Assuming you need only 'id' and 'name' from the 'account'
            ->where('name', 'ilike', '%'.$this->search.'%')
            ->get();
        $groupPaymentsChartData = $this->getGroupPaymentsChartData();

        return view('livewire.report.group-payment-statistics', [
            'groups' => $groups,
            'groupPaymentsChartData' => $groupPaymentsChartData,
        ]);
    }

    public function getGroupPaymentsChartData(): array
    {
        $data = [];
        $groups = Group::select('id', 'name', 'account_id')->with('account:id,balance')->get();
        foreach ($groups as $group) {
            $data[$group->name] = (int) ($group->account?->balance ?? 0);
        }

        return $data;
    }
}
