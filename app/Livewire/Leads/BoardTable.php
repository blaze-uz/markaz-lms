<?php

namespace App\Livewire\Leads;

use Livewire\Component;
use App\Models\KanbanBoard as KanbanBoardModel;
use Livewire\WithPagination;

class BoardTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $search = '';

    public function render()
    {
        $items = KanbanBoardModel::where('name', 'ilike', '%' . $this->search . '%')->paginate(10);
        return view('livewire.leads.board-table', compact('items'));
    }
}
