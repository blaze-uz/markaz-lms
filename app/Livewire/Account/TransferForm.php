<?php

namespace App\Livewire\Account;

use App\Models\Account;
use Livewire\Component;

class TransferForm extends Component
{
    public $searchTerm = '';
    public $selectedItem = null;
    public $items = [];

    public function mount()
    {
        $this->items = Account::select('id', 'name')
            ->get();
    }

    public function render()
    {
        return view('livewire.account.transfer-form');
    }
}
