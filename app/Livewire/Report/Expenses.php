<?php

namespace App\Livewire\Report;

use Livewire\Component;

class Expenses extends Component
{
    public $start;
    public $end;

    public function render()
    {
        if (!$this->start || !$this->end) {
            $this->start = now()->subDays(30);
            $this->end = now();
        }
        //get top 5 expenses
        $expenses = \App\Models\Expense::selectRaw('expense_items.name as item_name, sum(expenses.price) as total')
            ->join('expense_items', 'expenses.item_id', '=', 'expense_items.id')
            ->groupBy('expenses.item_id', 'expense_items.name')
            ->whereDate('expenses.created_at', '>=', $this->start)
            ->whereDate('expenses.created_at', '<=', $this->end)
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $chartData = [
            'labels' => [],
            'data' => [],
        ];

        foreach ($expenses as $expense) {
            //set the label
            $chartData['labels'][] = $expense->item_name;
            //set the data
            $chartData['data'][] = $expense->total;
        }

        return view('livewire.report.expenses', compact('expenses', 'chartData'));
    }
}
