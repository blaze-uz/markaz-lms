<?php

namespace App\Livewire\Report;

use App\Models\User;
use Livewire\Component;

class TeacherBalanceStatistics extends Component
{
    public $search = '';

    public function render()
    {
        $users = User::role('Teacher')->with('account')->where('name', 'ilike', '%' . $this->search . '%')->orderBy('name')->get();
        $chartData = $this->getChartData();
        return view('livewire.report.teacher-balance-statistics', compact('users', 'chartData'));
    }

    private function getChartData(): array
    {
        //get 5 teachers with the highest balance
        $teachers = User::role('Teacher')->with(['account.income_transactions', 'account.expense_transactions'])
            ->get();
        $teachers = $teachers->sortByDesc(function ($teacher) {
            return $teacher->account->balance;
        })->take(5);

        $labels = [];
        $data = [];
        foreach ($teachers as $teacher) {
            $labels[] = $teacher->name;
            $data[] = $teacher->account->income_transactions->sum('amount') - $teacher->account->expense_transactions->sum('amount');
        }
        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
