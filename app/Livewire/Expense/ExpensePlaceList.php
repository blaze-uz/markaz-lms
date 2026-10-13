<?php

namespace App\Livewire\Expense;

use App\Models\ExpensePlace;
use Livewire\Component;

class ExpensePlaceList extends Component
{
    public $search = '';

    public function render()
    {
        $items = ExpensePlace::search($this->search)->orderByDesc('created_at')->paginate(10);
        return view('livewire.expense.expense-place-list', compact('items'));
    }
}
