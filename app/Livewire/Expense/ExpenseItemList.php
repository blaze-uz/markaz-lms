<?php

namespace App\Livewire\Expense;

use App\Models\ExpenseItem;
use Livewire\Component;

class ExpenseItemList extends Component
{
    public $search = '';

    public function render()
    {
        $items = ExpenseItem::search($this->search)->orderBy('id', 'desc')->paginate(10);
        return view('livewire.expense.expense-item-list', compact('items'));
    }
}
