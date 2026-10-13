<?php

namespace App\Livewire\Leads;

use App\Models\KanbanCollection;
use Livewire\Component;

class CollectionTable extends Component
{
    public $search = '';
    public function render()
    {
        $items = KanbanCollection::search($this->search)->latest()->paginate(20);
        return view('livewire.leads.collection-table', compact('items'));
    }
}
